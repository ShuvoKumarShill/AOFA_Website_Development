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
		self::register_member();
		self::register_ec_member();
		self::register_notice();
		self::register_article();
	}

	// ── aofa_member ──────────────────────────────────────────────────────────

	/**
	 * Regular & Honorary Members.
	 *
	 * Source: archive_recovery/10_PEOPLE/members_list_complete.md
	 * 92 regular + 14 honorary members with full contact details.
	 *
	 * @since 1.0.0
	 */
	private static function register_member(): void {
		$labels = array(
			'name'                  => _x( 'Members', 'Post type general name', 'aofa-core' ),
			'singular_name'         => _x( 'Member', 'Post type singular name', 'aofa-core' ),
			'menu_name'             => _x( 'Members', 'Admin Menu text', 'aofa-core' ),
			'name_admin_bar'        => _x( 'Member', 'Add New on Toolbar', 'aofa-core' ),
			'add_new'               => __( 'Add New Member', 'aofa-core' ),
			'add_new_item'          => __( 'Add New Member', 'aofa-core' ),
			'new_item'              => __( 'New Member', 'aofa-core' ),
			'edit_item'             => __( 'Edit Member', 'aofa-core' ),
			'view_item'             => __( 'View Member', 'aofa-core' ),
			'all_items'             => __( 'All Members', 'aofa-core' ),
			'search_items'          => __( 'Search Members', 'aofa-core' ),
			'not_found'             => __( 'No members found.', 'aofa-core' ),
			'not_found_in_trash'    => __( 'No members found in Trash.', 'aofa-core' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'members' ),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-groups',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt' ),
			'show_in_rest'        => true, // Enables Gutenberg editor & REST API.
			'taxonomies'          => array( 'aofa_member_type' ),
		);

		register_post_type( 'aofa_member', $args );
	}

	// ── aofa_ec_member ───────────────────────────────────────────────────────

	/**
	 * Executive Committee Members across different terms.
	 *
	 * Source: archive_recovery/10_PEOPLE/executive_committees.md
	 * EC 2022-2023 (14 pos), 2024-2025 (13 pos), 2026-2027 (14 pos).
	 *
	 * @since 1.0.0
	 */
	private static function register_ec_member(): void {
		$labels = array(
			'name'               => _x( 'EC Members', 'Post type general name', 'aofa-core' ),
			'singular_name'      => _x( 'EC Member', 'Post type singular name', 'aofa-core' ),
			'menu_name'          => _x( 'EC Members', 'Admin Menu text', 'aofa-core' ),
			'add_new'            => __( 'Add EC Member', 'aofa-core' ),
			'add_new_item'       => __( 'Add New EC Member', 'aofa-core' ),
			'edit_item'          => __( 'Edit EC Member', 'aofa-core' ),
			'view_item'          => __( 'View EC Member', 'aofa-core' ),
			'all_items'          => __( 'All EC Members', 'aofa-core' ),
			'search_items'       => __( 'Search EC Members', 'aofa-core' ),
			'not_found'          => __( 'No EC members found.', 'aofa-core' ),
			'not_found_in_trash' => __( 'No EC members found in Trash.', 'aofa-core' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'executive-committee' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 6,
			'menu_icon'          => 'dashicons-businessman',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt', 'page-attributes' ),
			'show_in_rest'       => true,
			'taxonomies'         => array( 'aofa_committee_term' ),
		);

		register_post_type( 'aofa_ec_member', $args );
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
			'has_archive'        => true,
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
			'has_archive'        => true,
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
