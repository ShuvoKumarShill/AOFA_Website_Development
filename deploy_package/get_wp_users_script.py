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
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$res = $mysqli->query("SELECT ID, user_login, user_email, user_registered FROM wp_users");
$users = [];
while ($row = $res->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users, JSON_PRETTY_PRINT);
'''

cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'get_wp_users.php', 'content': php_code})

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'}
req = urllib.request.Request('https://aofabd.org/get_wp_users.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print(resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/get_wp_users.php'})
