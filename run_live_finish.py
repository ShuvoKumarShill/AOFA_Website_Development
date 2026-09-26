#!/usr/bin/env python3
import os
import sys
import json
import ssl
import urllib.request
import urllib.parse
from pathlib import Path

def main():
    host = os.environ.get("CPANEL_HOST", "cpanel.aofabd.org")
    user = os.environ.get("CPANEL_USER", "aofabdor")
    token = os.environ.get("CPANEL_TOKEN", "6NTYTT2Q318QECNQV8AG5ZX6V25YMA7E")

    if not host.startswith("http"):
        host = f"https://{host}:2083"

    ssl_ctx = ssl.create_default_context()
    ssl_ctx.check_hostname = False
    ssl_ctx.verify_mode = ssl.CERT_NONE

    local_deploy_script = Path(__file__).parent / "deploy_package" / "do_deploy.php"
    if not local_deploy_script.exists():
        print(f"Error: {local_deploy_script} does not exist.")
        sys.exit(1)

    print("1. Uploading do_deploy.php to cPanel /public_html...")
    import secrets
    boundary = "----WebKitFormBoundary" + secrets.token_hex(8)
    body = bytearray()
    body.extend(f"--{boundary}\r\nContent-Disposition: form-data; name=\"dir\"\r\n\r\n/public_html\r\n".encode())
    body.extend(f"--{boundary}\r\nContent-Disposition: form-data; name=\"overwrite\"\r\n\r\n1\r\n".encode())
    body.extend(f"--{boundary}\r\nContent-Disposition: form-data; name=\"file-0\"; filename=\"do_deploy.php\"\r\nContent-Type: application/octet-stream\r\n\r\n".encode())
    with open(local_deploy_script, "rb") as f:
        body.extend(f.read())
    body.extend(f"\r\n--{boundary}--\r\n".encode())

    url_upload = f"{host}/execute/Fileman/upload_files"
    headers = {
        "Authorization": f"cpanel {user}:{token}",
        "Content-Type": f"multipart/form-data; boundary={boundary}"
    }

    req = urllib.request.Request(url_upload, data=bytes(body), headers=headers, method="POST")
    try:
        with urllib.request.urlopen(req, context=ssl_ctx, timeout=60) as resp:
            res = json.loads(resp.read().decode('utf-8'))
            print("Upload result:", res)
    except Exception as e:
        print(f"Upload failed: {e}")
        sys.exit(1)

    # Trigger do_deploy.php
    print("\n2. Executing do_deploy.php on live domain https://aofabd.org/do_deploy.php...")
    
    def call_deploy(url):
        req_deploy = urllib.request.Request(url, headers={"User-Agent": "AOFA-Deployer/1.0"})
        with urllib.request.urlopen(req_deploy, context=ssl_ctx, timeout=300) as resp:
            return resp.read().decode('utf-8')

    url_base = "https://aofabd.org/do_deploy.php"
    output = call_deploy(url_base)
    print("--- Output from server ---")
    print(output)

    if "CHUNK_START" in output or "CHUNK_NEXT:" in output:
        start = 0
        if "CHUNK_NEXT:" in output:
            start = int(output.split("CHUNK_NEXT:")[1].split()[0])
        
        while True:
            chunk_url = f"{url_base}?chunked=1&start={start}"
            print(f"Processing chunk starting at index {start}...")
            out = call_deploy(chunk_url)
            print(out)
            if "CHUNK_NEXT:" in out:
                start = int(out.split("CHUNK_NEXT:")[1].split()[0])
            else:
                break

    # Clean up do_deploy.php via UAPI
    url_del = f"{host}/execute/Fileman/file_op"
    params = urllib.parse.urlencode({"op": "unlink", "file": "/public_html/do_deploy.php"}).encode('utf-8')
    headers_uapi = {"Authorization": f"cpanel {user}:{token}", "Content-Type": "application/x-www-form-urlencoded"}
    try:
        req_del = urllib.request.Request(url_del, data=params, headers=headers_uapi, method="POST")
        with urllib.request.urlopen(req_del, context=ssl_ctx, timeout=30) as resp:
            print("Cleanup do_deploy.php status:", resp.read().decode('utf-8'))
    except Exception as e:
        print(f"Cleanup notice: {e}")

if __name__ == "__main__":
    main()
