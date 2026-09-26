#!/usr/bin/env python3
import ssl, json, urllib.request, urllib.parse, re
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

print("1. Updating aofa-core.php with usort by menu_order for navigation items...")

aofa_core_local = Path('/home/shuvo/github/AOFA_Website_Development/wp-content/plugins/aofa-core/aofa-core.php')
aofa_core_site_ready = Path('/home/shuvo/github/AOFA_Website_Development/aofa_site_ready/public_html/wp-content/plugins/aofa-core/aofa-core.php')

php_usort_filter = """
/**
 * Ensure Primary Navigation Menu items are sorted strictly by menu_order.
 */
add_filter( 'wp_get_nav_menu_items', function( $items, $menu, $args ) {
	if ( is_array( $items ) && count( $items ) > 1 ) {
		usort( $items, function( $a, $b ) {
			return (int) $a->menu_order - (int) $b->menu_order;
		} );
	}
	return $items;
}, 10, 3 );
"""

for target_file in [aofa_core_local, aofa_core_site_ready]:
    if target_file.exists():
        content = target_file.read_text(encoding='utf-8')
        if 'wp_get_nav_menu_items' not in content:
            content += php_usort_filter
            target_file.write_text(content, encoding='utf-8')
            print(f"Updated {target_file}")

print("\n2. Uploading updated aofa-core.php to live cPanel server...")
cpanel_api('Fileman', 'save_file_content', {
    'dir': 'public_html/wp-content/plugins/aofa-core',
    'file': 'aofa-core.php',
    'content': aofa_core_local.read_text(encoding='utf-8')
})
print("Uploaded successfully!")
