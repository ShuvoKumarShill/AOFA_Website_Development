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

php_code = '''<?php
require_once 'wp-config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// 1. Check for localhost/127.0.0.1 image URLs in wp_posts and wp_postmeta
$res = $mysqli->query("SELECT ID, post_title, guid FROM wp_posts WHERE guid LIKE '%localhost%' OR guid LIKE '%127.0.0.1%' OR post_content LIKE '%localhost%' OR post_content LIKE '%127.0.0.1%' LIMIT 10");
$old_urls = [];
while ($row = $res->fetch_assoc()) {
    $old_urls[] = $row;
}

// 2. Check attachments count and guids
$res_att = $mysqli->query("SELECT ID, post_title, guid FROM wp_posts WHERE post_type='attachment'");
$attachments = [];
while ($row = $res_att->fetch_assoc()) {
    $attachments[] = $row;
}

echo json_encode([
    'old_domain_posts' => $old_urls,
    'attachments' => $attachments
], JSON_PRETTY_PRINT);
'''

cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'check_imgs.php', 'content': php_code})

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'}
req = urllib.request.Request('https://aofabd.org/check_imgs.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print(resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/check_imgs.php'})
