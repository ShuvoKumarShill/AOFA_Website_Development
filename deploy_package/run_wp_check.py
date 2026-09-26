#!/usr/bin/env python3
import ssl, json, urllib.request, urllib.parse, secrets, urllib.error

host = 'https://cpanel.aofabd.org:2083'
user = 'aofabdor'
token = '6NTYTT2Q318QECNQV8AG5ZX6V25YMA7E'

ssl_ctx = ssl.create_default_context()
ssl_ctx.check_hostname = False
ssl_ctx.verify_mode = ssl.CERT_NONE

check_php = """<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain');

define('SHORTINIT', true);
require(__DIR__ . '/wp-load.php');

echo "=== WordPress Theme & Plugin Check ===\\n\\n";

// Check active theme from DB
global $wpdb;
$theme = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'template' LIMIT 1");
$stylesheet = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'stylesheet' LIMIT 1");
$siteurl = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'siteurl' LIMIT 1");
$home = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'home' LIMIT 1");
$active_plugins = $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'active_plugins' LIMIT 1");

echo "siteurl: $siteurl\\n";
echo "home: $home\\n";
echo "template (theme): $theme\\n";
echo "stylesheet: $stylesheet\\n\\n";

// Check theme files exist
$theme_path = __DIR__ . "/wp-content/themes/$theme";
echo "Theme path: $theme_path\\n";
echo "Theme dir exists: " . (is_dir($theme_path) ? "YES" : "NO") . "\\n";

if (is_dir($theme_path)) {
    $files = scandir($theme_path);
    echo "Theme files: " . implode(', ', array_slice($files, 2, 15)) . "\\n";
    echo "index.php exists: " . (file_exists("$theme_path/index.php") ? "YES" : "NO") . "\\n";
    echo "front-page.php exists: " . (file_exists("$theme_path/front-page.php") ? "YES" : "NO") . "\\n";
    echo "functions.php exists: " . (file_exists("$theme_path/functions.php") ? "YES" : "NO") . "\\n";
}

echo "\\nAvailable themes:\\n";
$themes_dir = __DIR__ . '/wp-content/themes/';
foreach (scandir($themes_dir) as $d) {
    if ($d[0] !== '.' && is_dir("$themes_dir/$d")) {
        echo "  - $d\\n";
    }
}

echo "\\nActive plugins (first 500 chars):\\n" . substr($active_plugins, 0, 500) . "\\n";

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

res = upload(check_php.encode(), 'wpcheck.php')
print('Upload wpcheck.php:', res.get('status'))

req2 = urllib.request.Request(
    'https://aofabd.org/wpcheck.php',
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
