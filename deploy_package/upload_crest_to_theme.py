#!/usr/bin/env python3
import ssl, json, urllib.request, urllib.parse, secrets
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

print("1. Creating directory public_html/wp-content/themes/aofa-theme/assets/images...")
cpanel_api('Fileman', 'mkdir', {'dir': 'public_html/wp-content/themes/aofa-theme/assets', 'name': 'images'})
cpanel_api('Fileman', 'mkdir', {'dir': 'public_html/wp-content/themes/astra/assets', 'name': 'images'})

images_to_upload = [
    Path('/home/shuvo/github/AOFA_Website_Development/wp-content/uploads/aofa-crest.png'),
    Path('/home/shuvo/github/AOFA_Website_Development/wp-content/uploads/aofa-gold-emblem.png')
]

target_dirs = [
    '/public_html/wp-content/themes/aofa-theme/assets/images',
    '/public_html/wp-content/themes/astra/assets/images',
    '/public_html/wp-content/uploads'
]

for img_path in images_to_upload:
    if img_path.exists():
        for t_dir in target_dirs:
            print(f"Uploading {img_path.name} to {t_dir}...")
            res = upload_file(img_path, t_dir)
            print("  Result:", res.get('status'))

print("\nCrest and Emblem image upload completed successfully!")
