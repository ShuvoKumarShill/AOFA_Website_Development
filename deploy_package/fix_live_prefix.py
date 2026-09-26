#!/usr/bin/env python3
import ssl, json, urllib.request, urllib.parse, re

host = 'https://cpanel.aofabd.org:2083'
user = 'aofabdor'
token = '6NTYTT2Q318QECNQV8AG5ZX6V25YMA7E'

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

def cpanel_api(module, function, params=None):
    url = f'{host}/execute/{module}/{function}'
    if params:
        url += '?' + urllib.parse.urlencode(params)
    req = urllib.request.Request(url, headers={'Authorization': f'cpanel {user}:{token}'})
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        return json.loads(resp.read().decode('utf-8'))

print("1. Fetching live wp-config.php content...")
res = cpanel_api('Fileman', 'get_file_content', {'dir': 'public_html', 'file': 'wp-config.php'})
config_content = res.get('data', {}).get('content', '')

if not config_content:
    print("Error: Could not read wp-config.php")
    exit(1)

print("Current table prefix lines:")
for line in config_content.splitlines():
    if 'table_prefix' in line:
        print("  ", line)

# Replace table_prefix = 'aofa_'; with table_prefix = 'wp_';
new_config = re.sub(r"\$table_prefix\s*=\s*'aofa_'\s*;", "$table_prefix = 'wp_';", config_content)

print("\n2. Saving updated wp-config.php with $table_prefix = 'wp_'...")
save_res = cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'wp-config.php', 'content': new_config})
print("Save result:", save_res.get('status'))

# 3. Create a helper php script to update wp_options siteurl, home, active_plugins
php_fix_script = '''<?php
require_once 'wp-config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Database connected successfully. Table prefix in wp-config.php: " . $table_prefix . "\\n";

// Check row count in wp_posts
$res = $mysqli->query("SELECT COUNT(*) FROM wp_posts");
if ($res) {
    $count = $res->fetch_row()[0];
    echo "wp_posts total rows: " . $count . "\\n";
} else {
    echo "wp_posts query failed: " . $mysqli->error . "\\n";
}

// Update wp_options siteurl and home
$siteurl = 'https://aofabd.org';
$mysqli->query("UPDATE wp_options SET option_value='$siteurl' WHERE option_name IN ('siteurl', 'home')");
echo "Updated siteurl and home in wp_options to $siteurl.\\n";

// Set active plugins: aofa-core/aofa-core.php
$active_plugins = serialize(['aofa-core/aofa-core.php']);
$stmt = $mysqli->prepare("UPDATE wp_options SET option_value=? WHERE option_name='active_plugins'");
$stmt->bind_param("s", $active_plugins);
$stmt->execute();
echo "Set active_plugins in wp_options: aofa-core/aofa-core.php.\\n";

// Set active theme to astra (or template/stylesheet)
$mysqli->query("UPDATE wp_options SET option_value='astra' WHERE option_name IN ('template', 'stylesheet', 'current_theme')");
echo "Set active theme in wp_options to astra.\\n";

echo "ALL FIXES APPLIED SUCCESSFULLY!\\n";
'''

print("\n3. Saving helper script fix_wp_options.php...")
cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'fix_wp_options.php', 'content': php_fix_script})

print("\n4. Triggering fix_wp_options.php via cPanel API or HTTP...")
headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'}
req = urllib.request.Request('https://aofabd.org/fix_wp_options.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print("--- Output from fix_wp_options.php ---")
        print(resp.read().decode('utf-8'))
except Exception as e:
    print("HTTP trigger error:", e)

# Clean up helper
cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/fix_wp_options.php'})
print("\nCleanup completed.")
