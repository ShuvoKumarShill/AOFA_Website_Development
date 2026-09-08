<?php
/**
 * AOFA Core — SEO Redirects
 *
 * 301 redirects from old archive URLs to new clean URLs.
 * Preserves link equity from the original aofabd.com site.
 *
 * Old URL pattern → New URL (per implementation_plan.md URL Migration Table)
 *
 * Spec Item: 002-content-types
 *
 * @package AofaCore
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Issue 301 redirects for all known legacy URL patterns.
 *
 * Fires on 'template_redirect' which runs before any output,
 * ensuring headers can still be sent cleanly.
 *
 * @since 1.0.0
 */
function aofa_legacy_redirects(): void {

	// Only act on the front end.
	if ( is_admin() ) {
		return;
	}

	// Resolve the current request path (no query string).
	$request = isset( $_SERVER['REQUEST_URI'] )
		? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH )
		: '/';

	// Normalise: ensure single trailing slash.
	$path = '/' . trim( (string) $request, '/' ) . '/';

	/**
	 * Redirect map: old_path => new_url
	 *
	 * Keys: old URL paths from the recovered archive.
	 * Values: new canonical URLs on this installation.
	 */
	$redirects = array(
		// Members
		'/members-list/'                              => '/members/',
		'/members-list'                               => '/members/',

		// Executive Committee (old single page)
		'/executive-committee/'                       => '/executive-committee/',

		// Constitution
		'/constitution/'                              => '/constitution/',

		// Notices
		'/notice/'                                    => '/notice/',

		// Articles
		'/article/'                                   => '/article/',

		// Books / Book Reviews (merged into articles archive)
		'/books/'                                     => '/article/',
		'/books'                                      => '/article/',

		// Messages
		'/messages/'                                  => '/messages/',

		// Events (old slug)
		'/events-news/'                               => '/events/',
		'/events-news'                                => '/events/',

		// Old EC pages (term-specific slugs from old site)
		'/aofa-executive-committee-2026-2027/'        => '/executive-committee/',
		'/aofa-executive-committee-2026-2027'         => '/executive-committee/',
		'/aofa-executive-committee-2024-2025/'        => '/executive-committee/',
		'/aofa-executive-committee-2024-2025'         => '/executive-committee/',
		'/executive-committee-2022-2023/'             => '/executive-committee/',
	);

	if ( array_key_exists( $path, $redirects ) ) {
		$destination = $redirects[ $path ];

		// Only redirect if the destination is different from current path.
		if ( $path !== $destination ) {
			wp_safe_redirect( home_url( $destination ), 301 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'aofa_legacy_redirects' );
