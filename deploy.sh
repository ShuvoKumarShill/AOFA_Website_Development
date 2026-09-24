#!/bin/bash
# ═══════════════════════════════════════════════════════════════════════════════
# AOFA Website — Production Deployment Script
# Transfers full WordPress site + database to live server via SSH/SCP
# Live domain: https://aofabd.org
# ═══════════════════════════════════════════════════════════════════════════════

set -euo pipefail

# ── Configuration (FILL THESE IN) ────────────────────────────────────────────
REMOTE_HOST="${REMOTE_HOST:-YOUR_SERVER_IP_OR_HOSTNAME}"
REMOTE_USER="${REMOTE_USER:-YOUR_SSH_USER}"
REMOTE_PORT="${REMOTE_PORT:-22}"
SSH_KEY="${SSH_KEY:-$HOME/.ssh/id_rsa}"

# Remote paths (adjust to match your hosting)
REMOTE_WP_ROOT="${REMOTE_WP_ROOT:-/var/www/aofabd.org/html}"
REMOTE_DB_NAME="${REMOTE_DB_NAME:-aofa_wp}"
REMOTE_DB_USER="${REMOTE_DB_USER:-aofa_user}"
REMOTE_DB_PASS="${REMOTE_DB_PASS:-CHANGE_ME_STRONG_PASSWORD}"
REMOTE_DB_HOST="${REMOTE_DB_HOST:-localhost}"

# Local Docker
LOCAL_COMPOSE="docker compose"
LOCAL_WPCLI="${LOCAL_COMPOSE} run --rm wpcli wp"
LOCAL_DB_CONTAINER="aofa_db"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# ── Helper Functions ─────────────────────────────────────────────────────────

log()  { echo -e "${CYAN}[DEPLOY]${NC} $1"; }
ok()   { echo -e "${GREEN}[  OK  ]${NC} $1"; }
warn() { echo -e "${YELLOW}[ WARN ]${NC} $1"; }
fail() { echo -e "${RED}[FAIL!]${NC} $1"; exit 1; }

ssh_cmd() {
    ssh -i "$SSH_KEY" -p "$REMOTE_PORT" -o StrictHostKeyChecking=accept-new \
        "${REMOTE_USER}@${REMOTE_HOST}" "$@"
}

scp_cmd() {
    scp -i "$SSH_KEY" -P "$REMOTE_PORT" -o StrictHostKeyChecking=accept-new "$@"
}

# ── Pre-flight Checks ───────────────────────────────────────────────────────

preflight() {
    log "Running pre-flight checks..."

    if [ "$REMOTE_HOST" = "YOUR_SERVER_IP_OR_HOSTNAME" ]; then
        fail "REMOTE_HOST not configured. Set it in this script or export REMOTE_HOST=..."
    fi

    if [ ! -f "$SSH_KEY" ]; then
        fail "SSH key not found at: $SSH_KEY"
    fi

    # Test SSH connection
    log "Testing SSH connection to ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PORT}..."
    if ssh_cmd "echo 'SSH connection OK'" 2>/dev/null; then
        ok "SSH connection successful"
    else
        fail "Cannot connect via SSH. Check host, user, port, and key."
    fi

    # Check local Docker is running
    if ! ${LOCAL_COMPOSE} ps --quiet 2>/dev/null; then
        fail "Local Docker containers not running. Run 'docker compose up -d' first."
    fi
    ok "Local Docker containers running"
}

# ── Step 1: Export Local Database ────────────────────────────────────────────

export_database() {
    log "Exporting local WordPress database..."
    
    local DUMP_DIR="./deploy_tmp"
    mkdir -p "$DUMP_DIR"
    local DUMP_FILE="${DUMP_DIR}/aofa_db_export.sql"

    # Export via WP-CLI inside container
    ${LOCAL_WPCLI} db export /var/www/html/aofa_db_export.sql --add-drop-table 2>/dev/null

    # Copy the dump out of the container
    docker cp aofa_wp:/var/www/html/aofa_db_export.sql "$DUMP_FILE"

    # Clean up inside container
    docker exec aofa_wp rm -f /var/www/html/aofa_db_export.sql

    if [ ! -f "$DUMP_FILE" ]; then
        fail "Database export failed — dump file not found"
    fi

    # Search-replace: change localhost:8080 URLs to production domain
    log "Replacing localhost URLs with https://aofabd.org in database dump..."
    sed -i 's|http://localhost:8080|https://aofabd.org|g' "$DUMP_FILE"
    sed -i 's|http://localhost|https://aofabd.org|g' "$DUMP_FILE"
    
    local DUMP_SIZE
    DUMP_SIZE=$(du -sh "$DUMP_FILE" | cut -f1)
    ok "Database exported: ${DUMP_FILE} (${DUMP_SIZE})"
}

# ── Step 2: Export WordPress Files ───────────────────────────────────────────

export_wp_files() {
    log "Exporting WordPress files from container..."

    local FILES_DIR="./deploy_tmp/wp_files"
    mkdir -p "$FILES_DIR"

    # Copy entire wp-content from container (includes uploads, themes, plugins)
    docker cp aofa_wp:/var/www/html/wp-content "$FILES_DIR/wp-content"

    # Our custom theme and plugin are bind-mounted, ensure they're in the export
    # (they may already be there, but let's make sure the latest code is included)
    cp -r ./wp-content/plugins/aofa-core "$FILES_DIR/wp-content/plugins/aofa-core"

    ok "WordPress files exported to ${FILES_DIR}"
}

# ── Step 3: Transfer Files to Remote Server ──────────────────────────────────

transfer_files() {
    log "Transferring files to remote server..."

    # Create remote directory structure
    ssh_cmd "sudo mkdir -p ${REMOTE_WP_ROOT}/wp-content && sudo chown -R ${REMOTE_USER}:www-data ${REMOTE_WP_ROOT}"

    # Transfer database dump
    log "Uploading database dump..."
    scp_cmd "./deploy_tmp/aofa_db_export.sql" "${REMOTE_USER}@${REMOTE_HOST}:/tmp/aofa_db_export.sql"
    ok "Database dump uploaded"

    # Transfer wp-content via rsync (faster for subsequent transfers)
    log "Syncing wp-content to remote server (this may take a while)..."
    rsync -avz --progress \
        -e "ssh -i ${SSH_KEY} -p ${REMOTE_PORT}" \
        --exclude='.git' \
        --exclude='debug.log' \
        --exclude='cache/' \
        --exclude='upgrade/' \
        "./deploy_tmp/wp_files/wp-content/" \
        "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_WP_ROOT}/wp-content/"
    ok "wp-content synced to remote server"
}

# ── Step 4: Import Database on Remote ────────────────────────────────────────

import_remote_database() {
    log "Importing database on remote server..."

    ssh_cmd "mysql -h ${REMOTE_DB_HOST} -u ${REMOTE_DB_USER} -p'${REMOTE_DB_PASS}' ${REMOTE_DB_NAME} < /tmp/aofa_db_export.sql"
    
    # Clean up dump on remote
    ssh_cmd "rm -f /tmp/aofa_db_export.sql"

    ok "Database imported on remote server"
}

# ── Step 5: Set Remote Permissions ───────────────────────────────────────────

set_remote_permissions() {
    log "Setting file permissions on remote server..."

    ssh_cmd "
        sudo chown -R www-data:www-data ${REMOTE_WP_ROOT}/wp-content
        sudo find ${REMOTE_WP_ROOT}/wp-content -type d -exec chmod 755 {} \;
        sudo find ${REMOTE_WP_ROOT}/wp-content -type f -exec chmod 644 {} \;
        sudo chmod 600 ${REMOTE_WP_ROOT}/wp-config.php 2>/dev/null || true
    "

    ok "Remote permissions set"
}

# ── Step 6: Flush & Verify ───────────────────────────────────────────────────

verify_remote() {
    log "Running post-deployment checks..."

    # If WP-CLI is available on remote
    ssh_cmd "
        if command -v wp &>/dev/null; then
            cd ${REMOTE_WP_ROOT}
            wp cache flush --allow-root 2>/dev/null || true
            wp rewrite flush --allow-root 2>/dev/null || true
            wp option update siteurl 'https://aofabd.org' --allow-root 2>/dev/null || true
            wp option update home 'https://aofabd.org' --allow-root 2>/dev/null || true
            echo '✅ WP-CLI flush & URL update done'
        else
            echo '⚠️  WP-CLI not found on remote — update siteurl/home manually in wp-admin or database'
        fi
    "

    # Test HTTP response
    log "Testing live site response..."
    local HTTP_CODE
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 15 "https://aofabd.org" 2>/dev/null || echo "000")
    if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "301" ] || [ "$HTTP_CODE" = "302" ]; then
        ok "Live site responding (HTTP ${HTTP_CODE})"
    else
        warn "Live site returned HTTP ${HTTP_CODE} — DNS or server config may still be propagating"
    fi
}

# ── Cleanup ──────────────────────────────────────────────────────────────────

cleanup() {
    log "Cleaning up temporary files..."
    rm -rf ./deploy_tmp
    ok "Temporary files removed"
}

# ── Main ─────────────────────────────────────────────────────────────────────

main() {
    echo ""
    echo "═══════════════════════════════════════════════════════════════"
    echo "  AOFA Website — Production Deployment"
    echo "  Target: https://aofabd.org"
    echo "  Server: ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PORT}"
    echo "═══════════════════════════════════════════════════════════════"
    echo ""

    preflight
    export_database
    export_wp_files
    transfer_files
    import_remote_database
    set_remote_permissions
    verify_remote
    cleanup

    echo ""
    echo "═══════════════════════════════════════════════════════════════"
    echo -e "${GREEN}  ✅  DEPLOYMENT COMPLETE${NC}"
    echo ""
    echo "  Live site:  https://aofabd.org"
    echo "  WP Admin:   https://aofabd.org/wp-admin"
    echo "  User:       admin"
    echo ""
    echo "  ⚠️  POST-DEPLOYMENT CHECKLIST:"
    echo "    1. Login to wp-admin and verify all pages"
    echo "    2. Regenerate salts in wp-config.php"
    echo "    3. Change admin password from default"
    echo "    4. Set DISALLOW_FILE_MODS to true"
    echo "    5. Install SSL certificate if not already active"
    echo "    6. Set up automated backups"
    echo "═══════════════════════════════════════════════════════════════"
    echo ""
}

# ── CLI Interface ────────────────────────────────────────────────────────────

case "${1:-deploy}" in
    preflight|test)
        preflight
        ;;
    export-db)
        export_database
        ;;
    export-files)
        export_wp_files
        ;;
    transfer)
        transfer_files
        ;;
    import-db)
        import_remote_database
        ;;
    deploy)
        main
        ;;
    *)
        echo "Usage: $0 {deploy|preflight|export-db|export-files|transfer|import-db}"
        exit 1
        ;;
esac
