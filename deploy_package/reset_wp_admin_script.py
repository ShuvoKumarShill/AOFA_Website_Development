#!/usr/bin/env python3
import ssl, json, urllib.request, urllib.parse

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

php_code = '''<?php
require_once 'wp-config.php';
require_once 'wp-includes/class-phpass.php';

$password = 'AOFA@Admin2026!';
$hasher = new PasswordHash(8, true);
$hash = $hasher->HashPassword($password);

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$stmt = $mysqli->prepare("UPDATE wp_users SET user_pass=?, user_email='aofa.bd21@gmail.com' WHERE user_login='admin'");
$stmt->bind_param("s", $hash);
$stmt->execute();

echo json_encode([
    'status' => 'success',
    'username' => 'admin',
    'email' => 'aofa.bd21@gmail.com',
    'password' => $password
]);
'''

cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'reset_wp_admin.php', 'content': php_code})

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'}
req = urllib.request.Request('https://aofabd.org/reset_wp_admin.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print(resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/reset_wp_admin.php'})
