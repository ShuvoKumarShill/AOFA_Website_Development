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

print("1. Checking .htaccess file on live server...")
res = cpanel_api('Fileman', 'get_file_content', {'dir': 'public_html', 'file': '.htaccess'})
htaccess = res.get('data', {}).get('content', '')
print(".htaccess content:")
print(htaccess if htaccess else "<EMPTY or MISSING>")

print("\n2. Checking files in public_html/wp-content/uploads...")
res_uploads = cpanel_api('Fileman', 'list_files', {'dir': 'public_html/wp-content/uploads'})
print("Uploads dir files/folders:", [f.get('file') for f in res_uploads.get('data', [])])
