<?php
/**
 * Plugin Name:       AOFA Core
 * Plugin URI:        https://aofabd.com
 * Description:       Core plugin for the Association of Former BCS(FA) Ambassadors website. Registers custom post types, taxonomies, and WP-CLI import commands. No page-builder dependencies.
 * Version:           1.0.0
 * Author:            AOFA Web Team
 * Author URI:        https://aofabd.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aofa-core
 * Domain Path:       /languages
 * Requires at least: 6.6
 * Requires PHP:      8.2
 * Spec Item:         002-content-types
 *
 * @package AofaCore
 */

// Security: prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Constants ────────────────────────────────────────────────────────────────

/** Plugin version. */
define( 'AOFA_CORE_VERSION', '1.0.0' );

/** Absolute path to the plugin directory (with trailing slash). */
define( 'AOFA_CORE_PATH', plugin_dir_path( __FILE__ ) );

/** URL to the plugin directory (with trailing slash). */
define( 'AOFA_CORE_URL', plugin_dir_url( __FILE__ ) );

// ── Autoload ─────────────────────────────────────────────────────────────────

/**
 * Manually require class files.
 * PSR-4 autoloader is overkill for a focused plugin; keep it simple.
 */
require_once AOFA_CORE_PATH . 'includes/class-post-types.php';
require_once AOFA_CORE_PATH . 'includes/class-taxonomies.php';

// WP-CLI commands are only loaded in the CLI context.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once AOFA_CORE_PATH . 'includes/class-cli-commands.php';
}

// ── Bootstrap ────────────────────────────────────────────────────────────────

/**
 * Initialise the plugin after all plugins are loaded.
 * Using 'init' hook ensures CPTs/taxonomies are registered at the right time.
 */
function aofa_core_init(): void {
	// Load plugin text domain for translations.
	load_plugin_textdomain(
		'aofa-core',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);

	// Register custom post types.
	Aofa_Post_Types::register();

	// Register custom taxonomies.
	Aofa_Taxonomies::register();
}
add_action( 'init', 'aofa_core_init' );

/**
 * Register WP-CLI commands after WP-CLI has loaded.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'aofa', 'Aofa_CLI_Commands' );
}

// ── Activation / Deactivation Hooks ─────────────────────────────────────────

/**
 * Runs on plugin activation: flushes rewrite rules so CPT URLs work immediately.
 */
function aofa_core_activate(): void {
	Aofa_Post_Types::register();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'aofa_core_activate' );

/**
 * Runs on plugin deactivation: flushes rewrite rules to clean up CPT slugs.
 */
function aofa_core_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'aofa_core_deactivate' );
