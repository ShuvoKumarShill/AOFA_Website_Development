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

print("1. Updating aofa-core.php locally and on live server with mobile navigation drawer CSS...")

core_php_paths = [
    Path('/home/shuvo/github/AOFA_Website_Development/wp-content/plugins/aofa-core/aofa-core.php'),
    Path('/home/shuvo/github/AOFA_Website_Development/aofa_site_ready/public_html/wp-content/plugins/aofa-core/aofa-core.php')
]

# Replacement CSS for mobile nav and header
old_nav_css_target = """.ast-primary-header-bar .site-navigation {
			display: flex !important;
			justify-content: flex-end !important;
		}
		.main-header-menu {
			display: flex !important;
			flex-wrap: nowrap !important;
			align-items: center !important;
			gap: 0.85rem !important;
		}
		.main-header-menu .menu-item > a {
			padding: 0 8px !important;
			font-size: 0.92rem !important;
			font-weight: 600 !important;
			color: #002B49 !important;
			white-space: nowrap !important;
			transition: color 0.25s ease !important;
		}
		.main-header-menu .menu-item > a:hover,
		.main-header-menu .menu-item.current-menu-item > a {
			color: #C5A059 !important;
		}"""

new_nav_css_replacement = """/* ── Desktop Navigation Header ── */
		@media (min-width: 922px) {
			.ast-primary-header-bar .site-navigation {
				display: flex !important;
				justify-content: flex-end !important;
			}
			.main-header-menu {
				display: flex !important;
				flex-wrap: nowrap !important;
				align-items: center !important;
				gap: 0.85rem !important;
			}
			.main-header-menu .menu-item > a {
				padding: 0 8px !important;
				font-size: 0.92rem !important;
				font-weight: 600 !important;
				color: #002B49 !important;
				white-space: nowrap !important;
				transition: color 0.25s ease !important;
			}
			.main-header-menu .menu-item > a:hover,
			.main-header-menu .menu-item.current-menu-item > a {
				color: #C5A059 !important;
			}
		}

		/* ── Mobile Navigation Drawer & Hamburger Styling ── */
		@media (max-width: 921px) {
			.ast-mobile-header-wrap,
			.ast-mobile-menu-buttons {
				z-index: 999999 !important;
			}
			.ast-mobile-popup-drawer,
			.ast-mobile-header-drawer,
			.ast-desktop-header-content,
			.ast-mobile-menu-drawer,
			.main-navigation,
			.site-navigation {
				background: linear-gradient(180deg, #001222 0%, #002B49 100%) !important;
			}
			.ast-mobile-popup-drawer .main-header-menu,
			.ast-mobile-menu-drawer .main-header-menu,
			.site-navigation .main-header-menu,
			ul.main-header-menu {
				display: flex !important;
				flex-direction: column !important;
				width: 100% !important;
				padding: 15px 20px !important;
				margin: 0 !important;
				gap: 0 !important;
			}
			.ast-mobile-popup-drawer .main-header-menu .menu-item,
			.ast-mobile-menu-drawer .main-header-menu .menu-item,
			.site-navigation .main-header-menu .menu-item,
			ul.main-header-menu > li {
				width: 100% !important;
				border-bottom: 1px solid rgba(197, 160, 89, 0.2) !important;
			}
			.ast-mobile-popup-drawer .main-header-menu .menu-item > a,
			.ast-mobile-menu-drawer .main-header-menu .menu-item > a,
			.site-navigation .main-header-menu .menu-item > a,
			ul.main-header-menu > li > a {
				display: block !important;
				padding: 14px 16px !important;
				font-size: 1rem !important;
				font-weight: 600 !important;
				color: #ffffff !important;
				text-align: left !important;
				border-left: 3px solid transparent !important;
				transition: all 0.2s ease !important;
			}
			.ast-mobile-popup-drawer .main-header-menu .menu-item:hover > a,
			.ast-mobile-popup-drawer .main-header-menu .menu-item.current-menu-item > a,
			.site-navigation .main-header-menu .menu-item.current-menu-item > a,
			ul.main-header-menu > li:hover > a {
				color: #C5A059 !important;
				border-left-color: #C5A059 !important;
				background: rgba(197, 160, 89, 0.08) !important;
				padding-left: 22px !important;
			}
			.ast-mobile-menu-buttons .menu-toggle,
			.main-header-menu-toggle {
				background: #002B49 !important;
				border: 1px solid #C5A059 !important;
				color: #C5A059 !important;
				border-radius: 6px !important;
				padding: 6px 12px !important;
			}
		}"""

for c_path in core_php_paths:
    if c_path.exists():
        c_text = c_path.read_text(encoding='utf-8')
        if old_nav_css_target in c_text:
            c_text = c_text.replace(old_nav_css_target, new_nav_css_replacement)
            c_path.write_text(c_text, encoding='utf-8')
            print(f"  Updated local CSS in {c_path}")

print("\n2. Uploading updated aofa-core.php to live server...")
live_core_file = Path('/home/shuvo/github/AOFA_Website_Development/wp-content/plugins/aofa-core/aofa-core.php')
cpanel_api('Fileman', 'save_file_content', {
    'dir': 'public_html/wp-content/plugins/aofa-core',
    'file': 'aofa-core.php',
    'content': live_core_file.read_text(encoding='utf-8')
})

print("\n3. Cleaning up nav menu items in WordPress database (Removing duplicates & setting order 1 to 8)...")
php_nav_clean = '''<?php
require_once 'wp-config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Define clean 8 menu items
$clean_menu = [
    1 => ['title' => 'Home', 'url' => 'https://aofabd.org/'],
    2 => ['title' => 'About Us', 'url' => 'https://aofabd.org/about/'],
    3 => ['title' => 'Executive Committee', 'url' => 'https://aofabd.org/executive-committee/'],
    4 => ['title' => 'Member Directory', 'url' => 'https://aofabd.org/members/'],
    5 => ['title' => 'Official Notices', 'url' => 'https://aofabd.org/notice/'],
    6 => ['title' => 'Articles & Insights', 'url' => 'https://aofabd.org/article/'],
    7 => ['title' => 'Photo Gallery', 'url' => 'https://aofabd.org/#photo-gallery'],
    8 => ['title' => 'Contact Us', 'url' => 'https://aofabd.org/contact/']
];

// Delete redundant duplicate nav_menu_item posts (IDs 203, 204, 205, 206, 209)
$duplicate_ids = [203, 204, 205, 206, 209];
$mysqli->query("DELETE FROM wp_posts WHERE ID IN (" . implode(',', $duplicate_ids) . ")");
$mysqli->query("DELETE FROM wp_postmeta WHERE post_id IN (" . implode(',', $duplicate_ids) . ")");

// Update active 8 menu items order & URLs
$item_mappings = [
    201 => ['order' => 1, 'title' => 'Home', 'url' => 'https://aofabd.org/'],
    202 => ['order' => 2, 'title' => 'About Us', 'url' => 'https://aofabd.org/about/'],
    207 => ['order' => 3, 'title' => 'Executive Committee', 'url' => 'https://aofabd.org/executive-committee/'],
    208 => ['order' => 4, 'title' => 'Member Directory', 'url' => 'https://aofabd.org/members/'],
    210 => ['order' => 5, 'title' => 'Official Notices', 'url' => 'https://aofabd.org/notice/'],
    211 => ['order' => 6, 'title' => 'Articles & Insights', 'url' => 'https://aofabd.org/article/'],
    212 => ['order' => 7, 'title' => 'Photo Gallery', 'url' => 'https://aofabd.org/#photo-gallery'],
    213 => ['order' => 8, 'title' => 'Contact Us', 'url' => 'https://aofabd.org/contact/']
];

foreach ($item_mappings as $id => $info) {
    $order = $info['order'];
    $title = addslashes($info['title']);
    $url = addslashes($info['url']);
    
    $mysqli->query("UPDATE wp_posts SET menu_order=$order, post_title='$title' WHERE ID=$id");
    $mysqli->query("UPDATE wp_postmeta SET meta_value='$url' WHERE post_id=$id AND meta_key='_menu_item_url'");
}

// Clear any cached nav transients
$mysqli->query("DELETE FROM wp_options WHERE option_name LIKE '%_transient_theme_mods%' OR option_name LIKE '%_transient_nav_menu%'");

echo json_encode([
    'status' => 'success',
    'cleaned_items' => count($item_mappings)
]);
'''

cpanel_api('Fileman', 'save_file_content', {'dir': 'public_html', 'file': 'clean_nav.php', 'content': php_nav_clean})

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36'}
req = urllib.request.Request('https://aofabd.org/clean_nav.php', headers=headers)
try:
    with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
        print("Nav menu database cleanup response:", resp.read().decode('utf-8'))
finally:
    cpanel_api('Fileman', 'file_op', {'op': 'unlink', 'sourcefiles': 'public_html/clean_nav.php'})

print("\nMobile navigation organization and styling completed successfully!")
