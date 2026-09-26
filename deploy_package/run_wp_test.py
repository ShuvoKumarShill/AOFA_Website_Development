#!/usr/bin/env python3
import ssl, json, urllib.request, urllib.parse, secrets, urllib.error

host = 'https://cpanel.aofabd.org:2083'
user = 'aofabdor'
token = '6NTYTT2Q318QECNQV8AG5ZX6V25YMA7E'

ssl_ctx = ssl.create_default_context()
ssl_ctx.check_hostname = False
ssl_ctx.verify_mode = ssl.CERT_NONE

test_php = """<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain');

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "\\n\\n=== FATAL ERROR ===\\n";
        echo "Type: " . $err['type'] . "\\n";
        echo "Message: " . $err['message'] . "\\n";
        echo "File: " . $err['file'] . "\\n";
        echo "Line: " . $err['line'] . "\\n";
    }
});

echo "=== WordPress Load Test ===\\n\\n";
echo "PHP: " . PHP_VERSION . "\\n";
echo "Dir: " . __DIR__ . "\\n\\n";

$idx = file_get_contents(__DIR__ . '/index.php');
echo "index.php content:\\n" . $idx . "\\n\\n";

echo "Loading wp-blog-header.php...\\n";
ob_start();
try {
    require(__DIR__ . '/wp-blog-header.php');
    $out = ob_get_clean();
    echo "WP loaded OK! Output length: " . strlen($out) . "\\n";
    echo substr($out, 0, 1000);
} catch (Throwable $e) {
    $out = ob_get_clean();
    echo "Exception: " . $e->getMessage() . "\\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\\n";
    echo "Trace: " . $e->getTraceAsString() . "\\n";
}
@unlink(__FILE__);
"""

def upload(content_bytes, remote_name, remote_dir='/public_html'):
    boundary = '----AOFA' + secrets.token_hex(8)
    body = bytearray()
    body.extend(f'--{boundary}\r\nContent-Disposition: form-data; name="dir"\r\n\r\n{remote_dir}\r\n'.encode())
    body.extend(f'--{boundary}\r\nContent-Disposition: form-data; name="overwrite"\r\n\r\n1\r\n'.encode())
    body.extend(f'--{boundary}\r\nContent-Disposition: form-data; name="file-0"; filename="{remote_name}"\r\nContent-Type: application/octet-stream\r\n\r\n'.encode())
    body.extend(content_bytes)
    body.extend(f'\r\n--{boundary}--\r\n'.encode())
    req = urllib.request.Request(
        f'{host}/execute/Fileman/upload_files',
        data=bytes(body),
        headers={'Authorization': f'cpanel {user}:{token}', 'Content-Type': f'multipart/form-data; boundary={boundary}'},
        method='POST'
    )
    with urllib.request.urlopen(req, context=ssl_ctx, timeout=60) as r:
        return json.loads(r.read().decode())

res = upload(test_php.encode(), 'wptest.php')
print('Upload wptest.php:', res.get('status'))

req2 = urllib.request.Request(
    'https://aofabd.org/wptest.php',
    headers={
        'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
        'Accept': '*/*',
        'Accept-Encoding': 'identity',
        'Accept-Language': 'en-US'
    }
)
try:
    with urllib.request.urlopen(req2, context=ssl_ctx, timeout=60) as r2:
        print(r2.read().decode('utf-8', errors='ignore'))
except urllib.error.HTTPError as e:
    body_err = e.read().decode('utf-8', errors='ignore')
    print(f'HTTP {e.code}: {body_err[:3000]}')
