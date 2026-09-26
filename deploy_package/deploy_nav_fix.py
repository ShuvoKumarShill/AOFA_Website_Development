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

print("Adding one-time DB menu_order fixer inside aofa-core.php...")

aofa_core_local = Path('/home/shuvo/github/AOFA_Website_Development/wp-content/plugins/aofa-core/aofa-core.php')
aofa_core_site_ready = Path('/home/shuvo/github/AOFA_Website_Development/aofa_site_ready/public_html/wp-content/plugins/aofa-core/aofa-core.php')

php_db_fixer = """
/**
 * One-time database update for menu items order.
 */
add_action( 'init', function() {
	if ( get_option( 'aofa_nav_order_fixed_v2' ) ) {
		return;
	}
	global $wpdb;
	$orders = array(
		201 => 1,
		202 => 2,
		207 => 3,
		208 => 4,
		210 => 5,
		211 => 6,
		212 => 7,
		213 => 8
	);
	foreach ( $orders as $id => $ord ) {
		$wpdb->update( $wpdb->posts, array( 'menu_order' => $ord ), array( 'ID' => $id ) );
	}
	update_option( 'aofa_nav_order_fixed_v2', 1 );
} );
"""

for target_file in [aofa_core_local, aofa_core_site_ready]:
    if target_file.exists():
        content = target_file.read_text(encoding='utf-8')
        if 'aofa_nav_order_fixed_v2' not in content:
            content += php_db_fixer
            target_file.write_text(content, encoding='utf-8')
            print(f"Updated {target_file}")

print("\nUploading updated aofa-core.php to cPanel live server...")
cpanel_api('Fileman', 'save_file_content', {
    'dir': 'public_html/wp-content/plugins/aofa-core',
    'file': 'aofa-core.php',
    'content': aofa_core_local.read_text(encoding='utf-8')
})
print("Uploaded successfully!")

print("\nTriggering WordPress page request to execute the update hook...")
headers = {'User-Agent': 'Mozilla/5.0'}
req = urllib.request.Request('https://aofabd.org/', headers=headers)
with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
    print("Response status:", resp.status)

print("DB menu order update complete!")
