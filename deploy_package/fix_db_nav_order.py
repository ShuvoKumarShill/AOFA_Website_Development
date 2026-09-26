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

print("Updating database menu_order in wp_posts...")

php_db_order = """<?php
require_once 'wp-config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$orders = [
    201 => 1,
    202 => 2,
    207 => 3,
    208 => 4,
    210 => 5,
    211 => 6,
    212 => 7,
    213 => 8
];

foreach ($orders as $id => $ord) {
    $mysqli->query("UPDATE wp_posts SET menu_order = $ord WHERE ID = $id");
}

// Clear any theme mods cache transients
$mysqli->query("DELETE FROM wp_options WHERE option_name LIKE '%_transient_%'");

echo json_encode(['status' => 'success', 'orders' => $orders]);
"""

cpanel_api('Fileman', 'save_file_content', {
    'dir': 'public_html',
    'file': 'fix_orders.php',
    'content': php_db_order
})

headers = {'User-Agent': 'Mozilla/5.0'}
req = urllib.request.Request('https://aofabd.org/fix_orders.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print("Response:", resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/fix_orders.php'})

print("Database update completed!")
