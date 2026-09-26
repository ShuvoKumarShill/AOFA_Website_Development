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

// Check site_logo and theme_mods_astra options
$res_opt = $mysqli->query("SELECT option_name, option_value FROM wp_options WHERE option_name IN ('site_logo', 'theme_mods_astra', 'custom_logo')");
$options = [];
while ($row = $res_opt->fetch_assoc()) {
    $options[$row['option_name']] = $row['option_value'];
}

// Search wp_posts and wp_postmeta for crest/logo references
$res_posts = $mysqli->query("SELECT ID, post_title, guid, post_mime_type FROM wp_posts WHERE post_title LIKE '%crest%' OR guid LIKE '%crest%' OR post_title LIKE '%logo%' OR guid LIKE '%logo%'");
$posts = [];
while ($row = $res_posts->fetch_assoc()) {
    $posts[] = $row;
}

echo json_encode([
    'options' => $options,
    'posts' => $posts
], JSON_PRETTY_PRINT);
'''

cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'check_crest.php', 'content': php_code})

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'}
req = urllib.request.Request('https://aofabd.org/check_crest.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print(resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/check_crest.php'})
