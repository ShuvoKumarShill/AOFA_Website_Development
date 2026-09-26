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

// Get nav_menu_item posts sorted by menu_order
$res = $mysqli->query("SELECT ID, post_title, menu_order, post_name FROM wp_posts WHERE post_type='nav_menu_item' AND post_status='publish' ORDER BY menu_order ASC");
$items = [];
while ($row = $res->fetch_assoc()) {
    $id = $row['ID'];
    $meta_res = $mysqli->query("SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id=$id AND meta_key IN ('_menu_item_type', '_menu_item_object', '_menu_item_object_id', '_menu_item_url')");
    $meta = [];
    while ($m = $meta_res->fetch_assoc()) {
        $meta[$m['meta_key']] = $m['meta_value'];
    }
    $row['meta'] = $meta;
    $items[] = $row;
}

echo json_encode($items, JSON_PRETTY_PRINT);
'''

cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'check_nav_items.php', 'content': php_code})

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'}
req = urllib.request.Request('https://aofabd.org/check_nav_items.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print(resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/check_nav_items.php'})
