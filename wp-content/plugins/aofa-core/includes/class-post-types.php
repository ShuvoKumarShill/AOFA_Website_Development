<?php
/**
 * AOFA Core — Custom Post Types
 *
 * Registers all AOFA-specific content types.
 * Each CPT follows WordPress Coding Standards.
 * Security: labels are escaped at output, not here (WPCS best practice).
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
 * Class Aofa_Post_Types
 *
 * All CPT registration lives here as static methods so it can be called
 * from the main plugin file without instantiation.
 */
class Aofa_Post_Types {

	/**
	 * Register all custom post types.
	 * Called on the 'init' hook.
	 *
	 * @since 1.0.0
	 */
	public static function register(): void {
		self::register_notice();
		self::register_article();
	}

	// ── aofa_notice ──────────────────────────────────────────────────────────

	/**
	 * Official Notices (11 recovered from archive).
	 *
	 * Source: archive_recovery/03_NEWS/notices_list.md
	 * Election notices, AGM, picnic, constitutional amendments.
	 *
	 * @since 1.0.0
	 */
	private static function register_notice(): void {
		$labels = array(
			'name'               => _x( 'Notices', 'Post type general name', 'aofa-core' ),
			'singular_name'      => _x( 'Notice', 'Post type singular name', 'aofa-core' ),
			'menu_name'          => _x( 'Notices', 'Admin Menu text', 'aofa-core' ),
			'add_new'            => __( 'Add Notice', 'aofa-core' ),
			'add_new_item'       => __( 'Add New Notice', 'aofa-core' ),
			'edit_item'          => __( 'Edit Notice', 'aofa-core' ),
			'view_item'          => __( 'View Notice', 'aofa-core' ),
			'all_items'          => __( 'All Notices', 'aofa-core' ),
			'search_items'       => __( 'Search Notices', 'aofa-core' ),
			'not_found'          => __( 'No notices found.', 'aofa-core' ),
			'not_found_in_trash' => __( 'No notices found in Trash.', 'aofa-core' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'notice' ),
			'capability_type'    => 'post',
			'has_archive'        => 'notice',
			'hierarchical'       => false,
			'menu_position'      => 7,
			'menu_icon'          => 'dashicons-megaphone',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt' ),
			'show_in_rest'       => true,
		);

		register_post_type( 'aofa_notice', $args );
	}

	// ── aofa_article ─────────────────────────────────────────────────────────

	/**
	 * Member Articles and Book Reviews.
	 *
	 * Source: archive_recovery/07_PUBLICATIONS/articles_list.md
	 * 7 articles by AOFA ambassadors + 6 book reviews.
	 *
	 * @since 1.0.0
	 */
	private static function register_article(): void {
		$labels = array(
			'name'               => _x( 'Articles', 'Post type general name', 'aofa-core' ),
			'singular_name'      => _x( 'Article', 'Post type singular name', 'aofa-core' ),
			'menu_name'          => _x( 'Articles & Books', 'Admin Menu text', 'aofa-core' ),
			'add_new'            => __( 'Add Article', 'aofa-core' ),
			'add_new_item'       => __( 'Add New Article', 'aofa-core' ),
			'edit_item'          => __( 'Edit Article', 'aofa-core' ),
			'view_item'          => __( 'View Article', 'aofa-core' ),
			'all_items'          => __( 'All Articles', 'aofa-core' ),
			'search_items'       => __( 'Search Articles', 'aofa-core' ),
			'not_found'          => __( 'No articles found.', 'aofa-core' ),
			'not_found_in_trash' => __( 'No articles found in Trash.', 'aofa-core' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'article' ),
			'capability_type'    => 'post',
			'has_archive'        => 'article',
			'hierarchical'       => false,
			'menu_position'      => 8,
			'menu_icon'          => 'dashicons-book',
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields', 'excerpt' ),
			'show_in_rest'       => true,
			'taxonomies'         => array( 'aofa_article_category' ),
		);

		register_post_type( 'aofa_article', $args );
	}
}
