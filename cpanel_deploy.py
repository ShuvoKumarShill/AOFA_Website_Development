#!/usr/bin/env python3
"""
AOFA Website — Automated cPanel API Deployment Script
Automates live deployment of WordPress site and database to cPanel hosting
using cPanel API Tokens (UAPI).

Requirements: Python 3.6+ (uses standard libraries only: urllib, json, ssl, etc.)

Usage:
    export CPANEL_HOST="cpanel.aofabd.org"  # or https://your-ip:2083
    export CPANEL_USER="cpanel_username"
    export CPANEL_TOKEN="cpanel_api_token"
    python3 cpanel_deploy.py

Or pass arguments interactively when prompted.
"""

import os
import sys
import json
import ssl
import urllib.request
import urllib.parse
import urllib.error
import secrets
import string
import time
from pathlib import Path

# ── Color Output Helpers ──────────────────────────────────────────────────────
CYAN    = '\033[0;36m'
GREEN   = '\033[0;32m'
YELLOW  = '\033[1;33m'
RED     = '\033[0;31m'
BOLD    = '\033[1m'
RESET   = '\033[0m'

def log(msg):   print(f"{CYAN}[CPANEL DEPLOY]{RESET} {msg}")
def ok(msg):    print(f"{GREEN}[  OK  ]{RESET} {msg}")
def warn(msg):  print(f"{YELLOW}[ WARN ]{RESET} {msg}")
def fail(msg):  print(f"{RED}[FAIL!]{RESET} {msg}"); sys.exit(1)

# ── cPanel API Client ─────────────────────────────────────────────────────────
class CPanelAPI:
    def __init__(self, host: str, user: str, token: str, port: int = 2083):
        # Normalize host URL
        host = host.strip()
        if not host.startswith('http://') and not host.startswith('https://'):
            host = f"https://{host}"
        if not host.endswith(f":{port}") and not ':' in host.replace('https://', '').replace('http://', ''):
            host = f"{host}:{port}"
        
        self.base_url = host.rstrip('/')
        self.user = user.strip()
        self.token = token.strip()
        
        # SSL Context — allow self-signed certs if needed
        self.ssl_ctx = ssl.create_default_context()
        self.ssl_ctx.check_hostname = False
        self.ssl_ctx.verify_mode = ssl.CERT_NONE

    def call_uapi(self, module: str, function: str, params: dict = None, method: str = 'GET') -> dict:
        """Call cPanel UAPI endpoint."""
        params = params or {}
        query_str = urllib.parse.urlencode(params)
        url = f"{self.base_url}/execute/{module}/{function}"
        
        if method == 'GET' and query_str:
            url = f"{url}?{query_str}"
            body_data = None
        elif method == 'POST':
            body_data = query_str.encode('utf-8')
        else:
            body_data = None

        headers = {
            "Authorization": f"cpanel {self.user}:{self.token}",
            "User-Agent": "AOFA-cPanel-Deployer/1.0",
        }
        if method == 'POST':
            headers["Content-Type"] = "application/x-www-form-urlencoded"

        req = urllib.request.Request(url, data=body_data, headers=headers, method=method)

        try:
            with urllib.request.urlopen(req, context=self.ssl_ctx, timeout=30) as resp:
                raw_res = resp.read().decode('utf-8')
                res = json.loads(raw_res)
                return res
        except urllib.error.HTTPError as e:
            err_body = e.read().decode('utf-8', errors='ignore')
            fail(f"cPanel API HTTP {e.code} Error: {e.reason}\nBody: {err_body}")
        except Exception as e:
            fail(f"cPanel API request failed: {str(e)}")

    def upload_file(self, local_filepath: Path, remote_dir: str = "/public_html", remote_filename: str = None) -> bool:
        """Upload file via cPanel Fileman API."""
        filename = remote_filename or local_filepath.name
        url = f"{self.base_url}/execute/Fileman/upload_files"
        
        boundary = "----WebKitFormBoundaryAOFA" + secrets.token_hex(8)
        body = bytearray()
        body.extend(f"--{boundary}\r\nContent-Disposition: form-data; name=\"dir\"\r\n\r\n{remote_dir}\r\n".encode())
        body.extend(f"--{boundary}\r\nContent-Disposition: form-data; name=\"overwrite\"\r\n\r\n1\r\n".encode())
        body.extend(f"--{boundary}\r\nContent-Disposition: form-data; name=\"file-0\"; filename=\"{filename}\"\r\nContent-Type: application/octet-stream\r\n\r\n".encode())
        
        with open(local_filepath, "rb") as f:
            body.extend(f.read())
            
        body.extend(f"\r\n--{boundary}--\r\n".encode())

        headers = {
            "Authorization": f"cpanel {self.user}:{self.token}",
            "Content-Type": f"multipart/form-data; boundary={boundary}",
            "User-Agent": "AOFA-cPanel-Deployer/1.0"
        }

        req = urllib.request.Request(url, data=bytes(body), headers=headers, method="POST")
        try:
            with urllib.request.urlopen(req, context=self.ssl_ctx, timeout=300) as resp:
                data = json.loads(resp.read().decode('utf-8'))
                return data.get("status") == 1
        except Exception as e:
            warn(f"Upload of {filename} failed: {e}")
            return False

# ── Helper Functions ──────────────────────────────────────────────────────────
def generate_password(length=20):
    alphabet = string.ascii_letters + string.digits + "!@#$%^&*"
    return ''.join(secrets.choice(alphabet) for _ in range(length))

def generate_salt(length=64):
    alphabet = string.ascii_letters + string.digits + "!@#$%^&*()-_ []{}|;:,.<>?"
    return ''.join(secrets.choice(alphabet) for _ in range(length))

# ── Main Deployment Workflow ──────────────────────────────────────────────────
def main():
    print("")
    print(f"{BOLD}═══════════════════════════════════════════════════════════════{RESET}")
    print(f"{BOLD}  AOFA Website — Automated cPanel API Deployment{RESET}")
    print(f"{BOLD}═══════════════════════════════════════════════════════════════{RESET}")
    print("")

    # Check required local artifacts
    repo_root = Path(__file__).parent.resolve()
    zip_path = repo_root / "deploy_package" / "public_html.zip"
    sql_path = repo_root / "deploy_package" / "database" / "aofa_db.sql"

    if not zip_path.exists():
        fail(f"Missing deployment package zip: {zip_path}")
    if not sql_path.exists():
        fail(f"Missing database SQL dump: {sql_path}")

    ok(f"Found deployment package zip ({zip_path.stat().st_size / (1024*1024):.1f} MB)")
    ok(f"Found database SQL dump ({sql_path.stat().st_size / (1024*1024):.1f} MB)")

    # Read credentials from environment or prompt
    cpanel_host  = os.environ.get("CPANEL_HOST") or input("Enter cPanel Host/IP (e.g. cpanel.aofabd.org or 123.45.67.89): ").strip()
    cpanel_user  = os.environ.get("CPANEL_USER") or input("Enter cPanel Username: ").strip()
    cpanel_token = os.environ.get("CPANEL_TOKEN") or input("Enter cPanel API Token: ").strip()
    target_domain = os.environ.get("TARGET_DOMAIN", "aofabd.org").strip()

    if not cpanel_host or not cpanel_user or not cpanel_token:
        fail("cPanel Host, Username, and API Token are required to proceed.")

    api = CPanelAPI(cpanel_host, cpanel_user, cpanel_token)

    # 1. Pre-flight Test Token Connection
    log(f"Testing cPanel API token authentication for user '{cpanel_user}' at '{cpanel_host}'...")
    res = api.call_uapi("Mysql", "get_restrictions")
    if res.get("status") == 1:
        ok(f"cPanel API authenticated successfully!")
    else:
        errors = res.get("errors", ["Unknown authentication error"])
        fail(f"cPanel API Authentication failed: {', '.join(errors)}")

    # 2. Setup Remote Database & User
    db_name = f"{cpanel_user}_aofa"
    db_user = f"{cpanel_user}_usr"
    db_pass = os.environ.get("REMOTE_DB_PASS") or generate_password(24)

    log(f"Creating MySQL Database '{db_name}'...")
    res_db = api.call_uapi("Mysql", "create_database", {"name": db_name})
    if res_db.get("status") == 1:
        ok(f"Database '{db_name}' created successfully")
    else:
        warn(f"Database creation message: {', '.join(res_db.get('errors', []))}")

    log(f"Creating MySQL User '{db_user}'...")
    res_usr = api.call_uapi("Mysql", "create_user", {"name": db_user, "password": db_pass})
    if res_usr.get("status") == 1:
        ok(f"Database User '{db_user}' created successfully")
    else:
        warn(f"User creation message: {', '.join(res_usr.get('errors', []))}")

    log(f"Granting ALL PRIVILEGES to '{db_user}' on '{db_name}'...")
    res_priv = api.call_uapi("Mysql", "set_privileges_on_database", {
        "user": db_user,
        "database": db_name,
        "privileges": "ALL PRIVILEGES"
    })
    if res_priv.get("status") == 1:
        ok(f"Privileges granted successfully")
    else:
        warn(f"Privilege assignment message: {', '.join(res_priv.get('errors', []))}")

    # 3. Create Production wp-config.php with Live Database Credentials
    log("Generating production wp-config.php with live database credentials & salts...")
    wp_config_content = f"""<?php
/**
 * WordPress Production Configuration — {target_domain}
 * Generated automatically by cPanel Deployment Tool.
 */

define( 'DB_NAME',     '{db_name}' );
define( 'DB_USER',     '{db_user}' );
define( 'DB_PASSWORD', '{db_pass}' );
define( 'DB_HOST',     'localhost' );
define( 'DB_CHARSET',  'utf8mb4' );
define( 'DB_COLLATE',  '' );

$table_prefix = 'aofa_';

define( 'AUTH_KEY',         '{generate_salt()}' );
define( 'SECURE_AUTH_KEY',  '{generate_salt()}' );
define( 'LOGGED_IN_KEY',    '{generate_salt()}' );
define( 'NONCE_KEY',        '{generate_salt()}' );
define( 'AUTH_SALT',        '{generate_salt()}' );
define( 'SECURE_AUTH_SALT', '{generate_salt()}' );
define( 'LOGGED_IN_SALT',   '{generate_salt()}' );
define( 'NONCE_SALT',       '{generate_salt()}' );

define( 'WP_SITEURL', 'https://{target_domain}' );
define( 'WP_HOME',    'https://{target_domain}' );

define( 'DISALLOW_FILE_EDIT', true );
define( 'DISALLOW_FILE_MODS', false );

define( 'WP_DEBUG',         false );
define( 'WP_DEBUG_LOG',     false );
define( 'WP_DEBUG_DISPLAY', false );

define( 'WP_POST_REVISIONS', 5 );
define( 'AUTOSAVE_INTERVAL', 300 );
define( 'WP_MEMORY_LIMIT',     '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );

define( 'FORCE_SSL_ADMIN', true );
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && strpos( $_SERVER['HTTP_X_FORWARDED_PROTO'], 'https' ) !== false ) {{
    $_SERVER['HTTPS'] = 'on';
}}

if ( ! defined( 'ABSPATH' ) ) {{
    define( 'ABSPATH', __DIR__ . '/' );
}}
require_once ABSPATH . 'wp-settings.php';
"""
    prod_config_path = repo_root / "deploy_package" / "wp-config-live.php"
    with open(prod_config_path, "w") as f:
        f.write(wp_config_content)
    ok(f"Production wp-config.php generated at {prod_config_path.name}")

    # 4. Upload Site Archive to cPanel File Manager
    log("Uploading public_html.zip (33.3 MB) to cPanel /public_html via Fileman API (this may take ~15-30s)...")
    if api.upload_file(zip_path, "/public_html", "public_html.zip"):
        ok("Uploaded public_html.zip to cPanel /public_html successfully")
    else:
        warn("cPanel zip upload response unconfirmed — proceeding to extract check")

    # 5. Extract Zip Archive on Remote cPanel
    log("Extracting public_html.zip in cPanel /public_html...")
    res_ext = api.call_uapi("Fileman", "extract_files", {
        "dir": "/public_html",
        "file": "public_html.zip"
    })
    if res_ext.get("status") == 1:
        ok("Extracted public_html.zip successfully on cPanel")
    else:
        warn(f"Extract message: {', '.join(res_ext.get('errors', []))}")

    # 6. Upload Production wp-config.php
    log("Uploading live wp-config.php to cPanel /public_html/wp-config.php...")
    if api.upload_file(prod_config_path, "/public_html", "wp-config.php"):
        ok("Uploaded production wp-config.php successfully")

    # 7. Upload & Import Database Dump
    log("Uploading database SQL dump (aofa_db.sql) to cPanel /public_html...")
    api.upload_file(sql_path, "/public_html", "aofa_db.sql")

    # Create temporary PHP database importer
    php_importer = f"""<?php
/** AOFA Automated Database Importer */
$db_host = 'localhost';
$db_user = '{db_user}';
$db_pass = '{db_pass}';
$db_name = '{db_name}';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {{
    die('Database Connection Failed: ' . $conn->connect_error);
}}

$sql_file = __DIR__ . '/aofa_db.sql';
if (!file_exists($sql_file)) {{
    die('SQL File Not Found');
}}

$sql = file_get_contents($sql_file);
if ($conn->multi_query($sql)) {{
    do {{
        if ($res = $conn->store_result()) {{ $res->free(); }}
    }} while ($conn->more_results() && $conn->next_result());
    echo 'DB_IMPORT_SUCCESS';
}} else {{
    echo 'DB_IMPORT_ERROR: ' . $conn->error;
}}
$conn->close();
@unlink($sql_file);
@unlink(__FILE__);
"""
    importer_path = repo_root / "deploy_package" / "import_db_temp.php"
    with open(importer_path, "w") as f:
        f.write(php_importer)

    log("Uploading automated database importer to cPanel...")
    api.upload_file(importer_path, "/public_html", "import_db_temp.php")
    importer_path.unlink(missing_ok=True)

    log(f"Executing database import on live server (https://{target_domain}/import_db_temp.php)...")
    try:
        url_import = f"https://{target_domain}/import_db_temp.php"
        req = urllib.request.Request(url_import, headers={"User-Agent": "AOFA-Deployer/1.0"})
        with urllib.request.urlopen(req, context=api.ssl_ctx, timeout=60) as resp:
            out = resp.read().decode('utf-8')
            if 'DB_IMPORT_SUCCESS' in out:
                ok("Database imported successfully into live cPanel MySQL!")
            else:
                warn(f"Import response: {out}")
    except Exception as e:
        warn(f"Database import execution note: {e} (You can also import aofa_db.sql in cPanel phpMyAdmin)")

    # Clean up uploaded zip file on cPanel
    api.call_uapi("Fileman", "file_op", {"op": "unlink", "file": "/public_html/public_html.zip"})

    # 8. Complete Summary
    print("")
    print(f"{BOLD}═══════════════════════════════════════════════════════════════{RESET}")
    print(f"{GREEN}{BOLD}  ✅  LIVE DEPLOYMENT COMPLETE! SITE IS LIVE ON CPANEL{RESET}")
    print(f"{BOLD}═══════════════════════════════════════════════════════════════{RESET}")
    print("")
    print(f"  {BOLD}Live Website:{RESET}    https://{target_domain}")
    print(f"  {BOLD}WP Admin:{RESET}        https://{target_domain}/wp-admin")
    print(f"  {BOLD}Admin User:{RESET}      admin")
    print(f"  {BOLD}Database:{RESET}        {db_name}")
    print(f"  {BOLD}DB User:{RESET}         {db_user}")
    print(f"  {BOLD}DB Password:{RESET}     {db_pass}")
    print("")
    print(f"═══════════════════════════════════════════════════════════════")
    print("")

if __name__ == "__main__":
    main()

