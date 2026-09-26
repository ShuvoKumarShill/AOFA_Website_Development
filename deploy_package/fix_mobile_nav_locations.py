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

print("1. Updating aofa-core.php locally with nav locations PHP filters & enhanced mobile styling...")

aofa_core_local = Path('/home/shuvo/github/AOFA_Website_Development/wp-content/plugins/aofa-core/aofa-core.php')
aofa_core_site_ready = Path('/home/shuvo/github/AOFA_Website_Development/aofa_site_ready/public_html/wp-content/plugins/aofa-core/aofa-core.php')

new_mobile_css = """		/* ── Mobile Navigation Drawer & Hamburger Styling ── */
		@media (max-width: 921px) {
			.ast-mobile-header-wrap,
			.ast-mobile-menu-buttons {
				z-index: 999999 !important;
			}

			.ast-mobile-header-content,
			.ast-mobile-popup-drawer,
			.ast-mobile-header-drawer,
			.ast-desktop-header-content,
			.ast-mobile-menu-drawer,
			#ast-mobile-header,
			.main-navigation,
			.site-navigation,
			#ast-mobile-site-navigation,
			#ast-hf-mobile-menu {
				background: #001222 !important;
			}

			.ast-mobile-header-content {
				background: linear-gradient(180deg, #001222 0%, #002B49 100%) !important;
				padding: 10px 0 !important;
				border-top: 2px solid #C5A059 !important;
				box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
			}

			.ast-mobile-header-content .main-header-menu,
			.ast-mobile-popup-drawer .main-header-menu,
			.ast-mobile-menu-drawer .main-header-menu,
			#ast-mobile-site-navigation .main-header-menu,
			#ast-hf-mobile-menu .main-header-menu,
			ul.main-header-menu {
				display: flex !important;
				flex-direction: column !important;
				width: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
				gap: 0 !important;
				list-style: none !important;
			}

			.ast-mobile-header-content .main-header-menu .menu-item,
			.ast-mobile-popup-drawer .main-header-menu .menu-item,
			.ast-mobile-menu-drawer .main-header-menu .menu-item,
			#ast-mobile-site-navigation .menu-item,
			#ast-hf-mobile-menu .menu-item,
			ul.main-header-menu > li,
			li.page_item {
				width: 100% !important;
				border-bottom: 1px solid rgba(197, 160, 89, 0.25) !important;
				margin: 0 !important;
				padding: 0 !important;
			}

			.ast-mobile-header-content .main-header-menu .menu-item > a,
			.ast-mobile-popup-drawer .main-header-menu .menu-item > a,
			.ast-mobile-menu-drawer .main-header-menu .menu-item > a,
			#ast-mobile-site-navigation .menu-item > a,
			#ast-hf-mobile-menu .menu-item > a,
			ul.main-header-menu > li > a,
			li.page_item > a {
				display: block !important;
				padding: 14px 20px !important;
				font-size: 1rem !important;
				font-weight: 600 !important;
				color: #ffffff !important;
				text-align: left !important;
				border-left: 4px solid transparent !important;
				text-decoration: none !important;
				transition: all 0.2s ease-in-out !important;
			}

			.ast-mobile-header-content .main-header-menu .menu-item:hover > a,
			.ast-mobile-header-content .main-header-menu .menu-item.current-menu-item > a,
			.ast-mobile-popup-drawer .main-header-menu .menu-item.current-menu-item > a,
			#ast-mobile-site-navigation .menu-item.current-menu-item > a,
			#ast-hf-mobile-menu .menu-item.current-menu-item > a,
			ul.main-header-menu > li:hover > a,
			ul.main-header-menu > li.current-menu-item > a {
				color: #C5A059 !important;
				border-left-color: #C5A059 !important;
				background: rgba(197, 160, 89, 0.12) !important;
				padding-left: 26px !important;
			}

			.ast-mobile-menu-buttons .menu-toggle,
			.main-header-menu-toggle,
			button.menu-toggle {
				background: #002B49 !important;
				border: 1.5px solid #C5A059 !important;
				color: #C5A059 !important;
				border-radius: 6px !important;
				padding: 6px 12px !important;
			}

			.ast-mobile-menu-buttons .menu-toggle svg,
			.main-header-menu-toggle svg,
			button.menu-toggle svg {
				fill: #C5A059 !important;
				color: #C5A059 !important;
			}
		}"""

php_filter_code = """
/**
 * Force Primary Navigation Menu for both Desktop and Mobile locations in Astra.
 */
add_filter( 'wp_nav_menu_args', function( $args ) {
	$locations = get_nav_menu_locations();
	$primary_menu_id = $locations['primary'] ?? 21;

	// Force Primary Menu for primary and mobile navigation
	if ( empty( $args['theme_location'] ) || in_array( $args['theme_location'], array( 'primary', 'mobile_menu', 'header_menu' ), true ) ) {
		$args['menu']        = $primary_menu_id;
		$args['fallback_cb'] = false;
	}
	return $args;
}, 999 );

add_filter( 'theme_mod_nav_menu_locations', function( $locations ) {
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	if ( empty( $locations['primary'] ) ) {
		$locations['primary'] = 21;
	}
	$locations['mobile_menu'] = $locations['primary'];
	$locations['header_menu'] = $locations['primary'];
	return $locations;
}, 999 );
"""

for target_file in [aofa_core_local, aofa_core_site_ready]:
    if target_file.exists():
        content = target_file.read_text(encoding='utf-8')
        
        # Replace CSS pattern
        css_pattern = re.compile(r'/\* ── Mobile Navigation Drawer & Hamburger Styling ── \*/.*?}\s*}', re.DOTALL)
        if css_pattern.search(content):
            content = css_pattern.sub(new_mobile_css, content)
        
        # Append PHP filters if not present
        if 'add_filter( \'wp_nav_menu_args\'' not in content:
            content += php_filter_code
            
        target_file.write_text(content, encoding='utf-8')
        print(f"Updated {target_file}")

print("\n2. Uploading updated aofa-core.php to live cPanel server...")
cpanel_api('Fileman', 'save_file_content', {
    'dir': 'public_html/wp-content/plugins/aofa-core',
    'file': 'aofa-core.php',
    'content': aofa_core_local.read_text(encoding='utf-8')
})
print("Uploaded successfully!")

print("\n3. Setting WordPress nav_menu_locations in wp_options database...")
db_update_php = """<?php
require_once 'wp-config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Get nav_menu term_id for Primary Menu
$res = $mysqli->query("SELECT term_id FROM wp_terms WHERE name LIKE '%Primary%' OR name LIKE '%Main%' OR name LIKE '%Nav%' LIMIT 1");
$term_id = 21;
if ($res && $row = $res->fetch_assoc()) {
    $term_id = (int)$row['term_id'];
}

$locations = [
    'primary' => $term_id,
    'mobile_menu' => $term_id,
    'header_menu' => $term_id
];

$serialized = serialize($locations);

// Check if nav_menu_locations option exists in wp_options
$check = $mysqli->query("SELECT option_id FROM wp_options WHERE option_name = 'nav_menu_locations'");
if ($check && $check->num_rows > 0) {
    $mysqli->query("UPDATE wp_options SET option_value = '" . addslashes($serialized) . "' WHERE option_name = 'nav_menu_locations'");
} else {
    $mysqli->query("INSERT INTO wp_options (option_name, option_value, autoload) VALUES ('nav_menu_locations', '" . addslashes($serialized) . "', 'yes')");
}

// Update Astra theme_mods option if present
$res_t = $mysqli->query("SELECT option_value FROM wp_options WHERE option_name = 'theme_mods_astra'");
if ($res_t && $row_t = $res_t->fetch_assoc()) {
    $mods = @unserialize($row_t['option_value']);
    if (is_array($mods)) {
        $mods['nav_menu_locations'] = $locations;
        $new_mods = serialize($mods);
        $stmt = $mysqli->prepare("UPDATE wp_options SET option_value = ? WHERE option_name = 'theme_mods_astra'");
        $stmt->bind_param("s", $new_mods);
        $stmt->execute();
    }
}

// Clear transients
$mysqli->query("DELETE FROM wp_options WHERE option_name LIKE '%_transient_%'");

echo json_encode(['status' => 'success', 'term_id' => $term_id, 'locations' => $locations]);
"""

cpanel_api('Fileman', 'save_file_content', {
    'dir': 'public_html',
    'file': 'set_nav_locs.php',
    'content': db_update_php
})

headers = {'User-Agent': 'Mozilla/5.0'}
req = urllib.request.Request('https://aofabd.org/set_nav_locs.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print("Database update response:", resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/set_nav_locs.php'})

print("\nMobile navigation fix completed!")
