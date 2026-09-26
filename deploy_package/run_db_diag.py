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
    with urllib.request.urlopen(req, context=ctx) as resp:
        return json.loads(resp.read().decode('utf-8'))

# Upload db_diag.php
php_code = '''<?php
require_once 'wp-config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    die(json_encode(['error' => $mysqli->connect_error]));
}

$tables_res = $mysqli->query('SHOW TABLES');
$table_counts = [];
while ($row = $tables_res->fetch_array()) {
    $t = $row[0];
    $count_res = $mysqli->query("SELECT COUNT(*) FROM `$t`");
    $count = $count_res ? $count_res->fetch_row()[0] : 0;
    $table_counts[$t] = $count;
}

echo json_encode([
    'wp_config_prefix' => $table_prefix,
    'tables' => $table_counts
], JSON_PRETTY_PRINT);
'''

cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'db_diag.php', 'content': php_code})

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'}
req = urllib.request.Request('https://aofabd.org/db_diag.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx) as resp:
        print(resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/db_diag.php'})
