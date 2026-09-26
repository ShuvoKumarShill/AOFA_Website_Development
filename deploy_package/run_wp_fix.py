#!/usr/bin/env python3
"""Switch WordPress active theme and deactivate problematic plugins via DB"""
import ssl, json, urllib.request, urllib.parse, secrets, urllib.error

host = 'https://cpanel.aofabd.org:2083'
user = 'aofabdor'
token = '6NTYTT2Q318QECNQV8AG5ZX6V25YMA7E'

ssl_ctx = ssl.create_default_context()
ssl_ctx.check_hostname = False
ssl_ctx.verify_mode = ssl.CERT_NONE

fix_php = """<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain');

// Load WP minimally (no theme/plugins)
define('SHORTINIT', true);
require(__DIR__ . '/wp-load.php');
global $wpdb;

echo "=== AOFA Theme & Plugin Fix ===\\n\\n";

// Fix 1: Set active theme to 'astra' (which exists on disk)
$results = [];
$results[] = $wpdb->update(
    $wpdb->prefix . 'options',
    ['option_value' => 'astra'],
    ['option_name' => 'template']
);
$results[] = $wpdb->update(
    $wpdb->prefix . 'options',
    ['option_value' => 'astra'],
    ['option_name' => 'stylesheet']
);
echo "Theme switched to 'astra': " . ($results[0] !== false && $results[1] !== false ? "OK" : "WARN: " . $wpdb->last_error) . "\\n";

// Fix 2: Deactivate all plugins (safest for fresh deployment)
$results[] = $wpdb->update(
    $wpdb->prefix . 'options',
    ['option_value' => 'a:0:{}'],
    ['option_name' => 'active_plugins']
);
echo "All plugins deactivated: " . ($results[2] !== false ? "OK" : "WARN") . "\\n";

// Fix 3: Delete any cached theme data
$wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '_transient_%'");
$wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '_site_transient_%'");
echo "Transient cache cleared: OK\\n";

// Verify
$theme = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'template'");
$plugins = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'active_plugins'");
echo "\\nVerification:\\n";
echo "Active theme: $theme\\n";
echo "Active plugins: $plugins\\n";

// Check astra theme files exist
$astra_path = __DIR__ . '/wp-content/themes/astra';
echo "Astra theme path exists: " . (is_dir($astra_path) ? "YES" : "NO") . "\\n";
echo "Astra index.php: " . (file_exists("$astra_path/index.php") ? "YES" : "NO") . "\\n";
echo "Astra functions.php: " . (file_exists("$astra_path/functions.php") ? "YES" : "NO") . "\\n";

echo "\\n=== Fix complete — try https://aofabd.org now ===\\n";
@unlink(__FILE__);
"""

def upload(content_bytes, remote_name, remote_dir='/public_html'):
    boundary = '----AOFA' + secrets.token_hex(8)
    body = bytearray()
    body.extend(f'--{boundary}\r\nContent-Disposition: form-data; name="dir"\r\n\r\n{remote_dir}\r\n'.encode())
    body.extend(f'--{boundary}\r\nContent-Disposition: form-data; name="overwrite"\r\n\r\n1\r\n'.encode())
    body.extend(f'--{boundary}\r\nContent-Disposition: form-data; name="file-0"; filename="{remote_name}"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode())
    body.extend(content_bytes)
    body.extend(f'\r\n--{boundary}--\r\n'.encode())
    req = urllib.request.Request(
        f'{host}/execute/Fileman/upload_files',
        data=bytes(body),
        headers={'Authorization': f'cpanel {user}:{token}', 'Content-Type': f'multipart/form-data; boundary={boundary}'},
        method='POST'
    )
    with urllib.request.urlopen(req, context=ssl_ctx, timeout=60) as r:
        return json.loads(r.read().decode())

res = upload(fix_php.encode(), 'wpfix.php')
print('Upload wpfix.php:', res.get('status'))

req2 = urllib.request.Request(
    'https://aofabd.org/wpfix.php',
    headers={
        'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
        'Accept': '*/*',
        'Accept-Encoding': 'identity',
        'Accept-Language': 'en-US'
    }
)
try:
    with urllib.request.urlopen(req2, context=ssl_ctx, timeout=60) as r2:
        print(r2.read().decode('utf-8', errors='ignore'))
except urllib.error.HTTPError as e:
    body_err = e.read().decode('utf-8', errors='ignore')
    print(f'HTTP {e.code}: {body_err[:3000]}')
