#!/usr/bin/env python3
import ssl, json, urllib.request, urllib.parse, secrets, os
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

def upload_file(local_path, remote_dir):
    boundary = "----WebKitFormBoundary" + secrets.token_hex(8)
    body = bytearray()
    body.extend(f"--{boundary}\r\nContent-Disposition: form-data; name=\"dir\"\r\n\r\n{remote_dir}\r\n".encode())
    body.extend(f"--{boundary}\r\nContent-Disposition: form-data; name=\"overwrite\"\r\n\r\n1\r\n".encode())
    filename = Path(local_path).name
    body.extend(f"--{boundary}\r\nContent-Disposition: form-data; name=\"file-0\"; filename=\"{filename}\"\r\nContent-Type: application/octet-stream\r\n\r\n".encode())
    with open(local_path, "rb") as f:
        body.extend(f.read())
    body.extend(f"\r\n--{boundary}--\r\n".encode())

    url_upload = f"{host}/execute/Fileman/upload_files"
    headers = {
        "Authorization": f"cpanel {user}:{token}",
        "Content-Type": f"multipart/form-data; boundary={boundary}"
    }
    req = urllib.request.Request(url_upload, data=bytes(body), headers=headers, method="POST")
    with urllib.request.urlopen(req, context=ctx, timeout=60) as resp:
        return json.loads(resp.read().decode('utf-8'))

print("1. Updating .htaccess with WordPress Rewrite Rules...")
htaccess_content = '''# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress

# ── Security Headers ──
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# ── Prevent directory browsing ──
Options -Indexes

# ── Block access to sensitive files ──
<FilesMatch "^(wp-config\\.php|\\.htaccess|readme\\.html|license\\.txt)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# ── Disable XML-RPC (common attack vector) ──
<Files xmlrpc.php>
    Order deny,allow
    Deny from all
</Files>
'''

res_ht = cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': '.htaccess', 'content': htaccess_content})
print(".htaccess update status:", res_ht.get('status'))

print("\n2. Uploading missing images to public_html/wp-content/uploads...")
local_uploads_dir = Path('/home/shuvo/github/AOFA_Website_Development/wp-content/uploads')
for img_file in local_uploads_dir.glob('*.*'):
    if img_file.is_file():
        print(f"Uploading {img_file.name} to /public_html/wp-content/uploads...")
        res_up = upload_file(img_file, '/public_html/wp-content/uploads')
        print(f"  Result: {res_up.get('status')}")

# Flush rewrite_rules in database
php_flush = '''<?php
require_once 'wp-config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$mysqli->query("DELETE FROM wp_options WHERE option_name='rewrite_rules'");
echo "Flushed rewrite_rules from database.";
'''
cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'flush_rules.php', 'content': php_flush})

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'}
req = urllib.request.Request('https://aofabd.org/flush_rules.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print("\n3. Trigger flush_rules.php response:", resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/flush_rules.php'})

print("\nAll fixes applied successfully!")
