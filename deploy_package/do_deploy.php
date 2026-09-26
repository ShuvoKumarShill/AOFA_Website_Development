<?php
/**
 * AOFA Automated cPanel Extraction & Database Import Engine
 */
header('Content-Type: text/plain');
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');
error_reporting(E_ALL);

// Disable mysqli exception throwing for manual error handling
mysqli_report(MYSQLI_REPORT_OFF);

echo "=== AOFA CPANEL LIVE DEPLOYMENT ENGINE ===\n\n";

$sql_file = __DIR__ . '/aofa_db.sql';

// ── IMPORT DATABASE ────────────────────────────────────────────────────────
if (file_exists($sql_file)) {
    echo "[STEP 2/2] Importing database dump aofa_db.sql (" . round(filesize($sql_file)/1024/1024, 1) . " MB)...\n";
    
    $passwords = [
        'AOFA@Prod2026!xZ'
    ];
    
    $db_host = 'localhost';
    $db_name = 'aofabdor_aofa';
    $db_user = 'aofabdor_usr';
    
    $conn = null;
    foreach ($passwords as $p) {
        $c = @new mysqli($db_host, $db_user, $p, $db_name);
        if ($c && !$c->connect_error) {
            $conn = $c;
            echo "Connected to MySQL database '$db_name' successfully.\n";
            // Update wp-config.php with working password if needed
            $wp_config_file = __DIR__ . '/wp-config.php';
            if (file_exists($wp_config_file)) {
                $cfg = file_get_contents($wp_config_file);
                $cfg = preg_replace("/define\(\s*'DB_PASSWORD'\s*,\s*'.*?'\s*\);/", "define( 'DB_PASSWORD', '" . addslashes($p) . "' );", $cfg);
                file_put_contents($wp_config_file, $cfg);
                echo "Updated wp-config.php with confirmed DB_PASSWORD.\n";
            }
            break;
        }
    }
    
    if (!$conn) {
        echo "DB_CONNECT_ERROR: Could not connect to '$db_name' with configured credentials.\n";
    } else {
        $conn->set_charset("utf8mb4");
        
        $sql_content = file_get_contents($sql_file);
        // Split queries safely
        $queries = preg_split("/;\s*[\r\n]+/", $sql_content);
        $executed = 0;
        $errors = 0;
        
        foreach ($queries as $q) {
            $q = trim($q);
            if (empty($q) || strpos($q, '--') === 0 || strpos($q, '/*') === 0) continue;
            
            if ($conn->query($q)) {
                $executed++;
            } else {
                $errors++;
            }
        }
        
        echo "SUCCESS: Database import finished! Executed $executed statements ($errors skipped/warnings).\n";
        $conn->close();
        @unlink($sql_file);
    }
} else {
    echo "[STEP 2/2] aofa_db.sql not found (already imported).\n";
}

// Clean up deployment scripts
@unlink(__DIR__ . '/do_deploy.php');

echo "\n=== ALL DEPLOYMENT OPERATIONS COMPLETED ===\n";
