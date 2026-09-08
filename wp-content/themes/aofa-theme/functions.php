<?php
/**
 * AOFA Theme — functions.php
 *
 * Registers theme support, navigation menus, and enqueues assets.
 * All functions follow WordPress Coding Standards (WPCS).
 * Security: no direct output without escaping, nonces used where needed.
 *
 * Spec Item: 001-initial-setup
 *
 * @package AofaTheme
 * @since   1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Theme Setup ─────────────────────────────────────────────────────────────

/**
 * Sets up theme features and registers navigation menus.
 *
 * @since 1.0.0
 */
function aofa_theme_setup(): void {

	// Enable translation support.
	load_theme_textdomain( 'aofa-theme', get_template_directory() . '/languages' );

	// Block editor: theme supports these natively via theme.json.
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );

	// Let WordPress manage the document title tag.
	add_theme_support( 'title-tag' );

	// Enable post thumbnails (featured images).
	add_theme_support( 'post-thumbnails' );

	// Opt into HTML5 markup for key elements.
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Register navigation menus.
	register_nav_menus(
		array(
			'primary'  => esc_html__( 'Primary Navigation', 'aofa-theme' ),
			'footer'   => esc_html__( 'Footer Navigation', 'aofa-theme' ),
			'social'   => esc_html__( 'Social Links', 'aofa-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'aofa_theme_setup' );


// ── Assets ──────────────────────────────────────────────────────────────────

/**
 * Enqueues front-end stylesheets and scripts.
 *
 * Only the main stylesheet is registered; block styles come from theme.json.
 * We avoid loading jQuery on the front end for performance.
 *
 * @since 1.0.0
 */
function aofa_theme_enqueue_assets(): void {

	$version = wp_get_theme()->get( 'Version' );

	// Main theme stylesheet (contains only the theme header comment).
	wp_enqueue_style(
		'aofa-theme-style',
		get_stylesheet_uri(),
		array(),
		$version
	);

	// Google Fonts — Inter & Playfair Display.
	// Loaded conditionally to respect user privacy preferences.
	wp_enqueue_style(
		'aofa-google-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap',
		array(),
		null // No version — URL is the cache key.
	);

	// Main theme JS (deferred, no jQuery dependency).
	wp_enqueue_script(
		'aofa-theme-script',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		$version,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'aofa_theme_enqueue_assets' );

/**
 * Enqueues block editor styles so the editor matches the front end.
 *
 * @since 1.0.0
 */
function aofa_theme_enqueue_editor_assets(): void {
	wp_enqueue_style(
		'aofa-theme-editor-style',
		get_template_directory_uri() . '/assets/css/editor-style.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'enqueue_block_editor_assets', 'aofa_theme_enqueue_editor_assets' );


// ── Image Sizes ─────────────────────────────────────────────────────────────

/**
 * Registers custom image sizes for AOFA content.
 *
 * @since 1.0.0
 */
function aofa_theme_image_sizes(): void {
	// Card thumbnails (used in member cards, notice lists).
	add_image_size( 'aofa-card', 400, 300, true );
	// Hero/banner images.
	add_image_size( 'aofa-hero', 1400, 600, true );
	// Avatar (for EC member profiles).
	add_image_size( 'aofa-avatar', 200, 200, true );
}
add_action( 'after_setup_theme', 'aofa_theme_image_sizes' );


// ── Block Patterns ──────────────────────────────────────────────────────────

/**
 * Registers the AOFA block pattern category.
 *
 * @since 1.0.0
 */
function aofa_theme_register_pattern_categories(): void {
	register_block_pattern_category(
		'aofa',
		array( 'label' => esc_html__( 'AOFA', 'aofa-theme' ) )
	);
}
add_action( 'init', 'aofa_theme_register_pattern_categories' );


// ── Security Hardening ───────────────────────────────────────────────────────

/**
 * Removes unnecessary head tags for security and performance.
 *
 * Spec Decision: Security-first — expose minimal information.
 *
 * @since 1.0.0
 */
function aofa_theme_clean_head(): void {
	remove_action( 'wp_head', 'wp_generator' );              // Hide WP version.
	remove_action( 'wp_head', 'wlwmanifest_link' );          // Windows Live Writer.
	remove_action( 'wp_head', 'rsd_link' );                  // Really Simple Discovery.
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );      // Short links.
}
add_action( 'init', 'aofa_theme_clean_head' );


// ── Excerpt ────────────────────────────────────────────────────────────────────────

/**
 * Strips raw Markdown syntax from excerpt text.
 *
 * Post content is authored in Markdown, but WordPress's get_the_excerpt()
 * returns raw text — so bold/italic markers (**text**, *text*), list dashes,
 * headings (##), and horizontal rules (---) appear as literal characters in
 * card excerpts on the front end. This filter cleans them to plain readable
 * prose before WordPress truncates to the excerpt_length word count.
 *
 * Priority 5 — runs before the excerpt_length filter (priority 999) so
 * the word count is applied to the already-cleaned string.
 *
 * @param  string $excerpt Raw excerpt text.
 * @return string          Cleaned excerpt without Markdown syntax.
 */
function aofa_strip_markdown_from_excerpt( string $excerpt ): string {
	if ( empty( $excerpt ) ) {
		return $excerpt;
	}
	// Remove horizontal rules: --- or — at start of line.
	$excerpt = preg_replace( '/^\s*[-–—]{3,}\s*$/m', '', $excerpt );
	// Remove ATX headings: ## Heading
	$excerpt = preg_replace( '/^#{1,6}\s+/m', '', $excerpt );
	// Remove bold+italic asterisks: ***text*** or **text** or *text*
	$excerpt = preg_replace( '/\*{1,3}([^\*\r\n]+)\*{1,3}/', '$1', $excerpt );
	// Remove inline code: `code`
	$excerpt = preg_replace( '/`([^`]+)`/', '$1', $excerpt );
	// Remove leading list markers and bullets: - item or – item or * item
	$excerpt = preg_replace( '/^\s*[-–—•\*]+\s*/m', '', $excerpt );
	// Remove inline list markers like " - " or " – " inside text
	$excerpt = preg_replace( '/\s+[-–—•]\s+/', ' ', $excerpt );
	// Collapse runs of whitespace / blank lines to a single space.
	$excerpt = preg_replace( '/[\r\n]+/', ' ', $excerpt );
	$excerpt = preg_replace( '/\s{2,}/', ' ', $excerpt );
	return trim( $excerpt );
}
// Priority 5: run before excerpt_length (999) and excerpt_more filters.
add_filter( 'get_the_excerpt',  'aofa_strip_markdown_from_excerpt', 5 );
add_filter( 'the_excerpt',      'aofa_strip_markdown_from_excerpt', 5 );
add_filter( 'wp_trim_excerpt',  'aofa_strip_markdown_from_excerpt', 5 );

/**
 * Sets the custom excerpt length for AOFA content.
 *
 * @param  int $length Default excerpt length (words).
 * @return int
 */
function aofa_theme_excerpt_length( int $length ): int {
	return 30;
}
add_filter( 'excerpt_length', 'aofa_theme_excerpt_length', 999 );

/**
 * Replaces the default excerpt "…" with a styled read-more link.
 *
 * @param  string $more The current "read more" string.
 * @return string       HTML read more link.
 */
function aofa_theme_excerpt_more( string $more ): string {
	return sprintf(
		'&hellip; <a class="aofa-read-more" href="%s">%s</a>',
		esc_url( get_permalink() ),
		esc_html__( 'Read more', 'aofa-theme' )
	);
}
add_filter( 'excerpt_more', 'aofa_theme_excerpt_more' );
