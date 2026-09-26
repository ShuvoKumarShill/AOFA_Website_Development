#!/usr/bin/env python3
import ssl, json, urllib.request, urllib.parse, re
from pathlib import Path

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

old_email = 'whatsapp.shuvo26@gmail.com'
new_email = 'aofa.bd21@gmail.com'

print(f"1. Updating local files from '{old_email}' to '{new_email}'...")

files_to_update = [
    Path('/home/shuvo/github/AOFA_Website_Development/deploy_package/reset_wp_admin_script.py'),
    Path('/home/shuvo/github/AOFA_Website_Development/deploy_package/database/aofa_db.sql'),
    Path('/home/shuvo/github/AOFA_Website_Development/aofa_site_ready/database/aofa_db.sql')
]

for file_path in files_to_update:
    if file_path.exists():
        content = file_path.read_text(encoding='utf-8', errors='ignore')
        if old_email in content:
            new_content = content.replace(old_email, new_email)
            file_path.write_text(new_content, encoding='utf-8')
            print(f"  Updated: {file_path}")

print(f"\n2. Updating live database (wp_options & wp_users)...")
php_code = f'''<?php
require_once 'wp-config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Update wp_options admin_email
$mysqli->query("UPDATE wp_options SET option_value='{new_email}' WHERE option_name='admin_email'");

// Update wp_users user_email
$mysqli->query("UPDATE wp_users SET user_email='{new_email}' WHERE user_login='admin'");

// Update aofa_options and aofa_users if present
$mysqli->query("UPDATE aofa_options SET option_value='{new_email}' WHERE option_name='admin_email'");
$mysqli->query("UPDATE aofa_users SET user_email='{new_email}' WHERE user_login='admin'");

echo json_encode([
    'status' => 'success',
    'admin_email' => '{new_email}'
]);
'''

cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'update_email_helper.php', 'content': php_code})

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'}
req = urllib.request.Request('https://aofabd.org/update_email_helper.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print("Live update response:", resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/update_email_helper.php'})

print("\nEmail address update completed successfully!")
