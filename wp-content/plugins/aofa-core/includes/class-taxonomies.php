<?php
/**
 * AOFA Core — Custom Taxonomies
 *
 * Registers AOFA-specific taxonomies linked to their CPTs.
 * Spec Item: 002-content-types
 *
 * @package AofaCore
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Aofa_Taxonomies
 */
class Aofa_Taxonomies {

	/**
	 * Register all custom taxonomies.
	 * Called on the 'init' hook via the main plugin file.
	 *
	 * @since 1.0.0
	 */
	public static function register(): void {
		self::register_member_type();
		self::register_committee_term();
		self::register_article_category();
	}

	// ── aofa_member_type ─────────────────────────────────────────────────────

	/**
	 * Member Type taxonomy for aofa_member CPT.
	 * Terms: Regular Member, Honorary Member.
	 *
	 * @since 1.0.0
	 */
	private static function register_member_type(): void {
		$labels = array(
			'name'              => _x( 'Member Types', 'taxonomy general name', 'aofa-core' ),
			'singular_name'     => _x( 'Member Type', 'taxonomy singular name', 'aofa-core' ),
			'search_items'      => __( 'Search Member Types', 'aofa-core' ),
			'all_items'         => __( 'All Member Types', 'aofa-core' ),
			'edit_item'         => __( 'Edit Member Type', 'aofa-core' ),
			'update_item'       => __( 'Update Member Type', 'aofa-core' ),
			'add_new_item'      => __( 'Add New Member Type', 'aofa-core' ),
			'new_item_name'     => __( 'New Member Type Name', 'aofa-core' ),
			'menu_name'         => __( 'Member Types', 'aofa-core' ),
		);

		register_taxonomy(
			'aofa_member_type',
			array( 'aofa_member' ),
			array(
				'labels'            => $labels,
				'hierarchical'      => true, // Like categories — Regular / Honorary.
				'public'            => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'member-type' ),
			)
		);
	}

	// ── aofa_committee_term ──────────────────────────────────────────────────

	/**
	 * Committee Term taxonomy for aofa_ec_member CPT.
	 * Terms: EC 2022-2023, EC 2024-2025, EC 2026-2027.
	 *
	 * @since 1.0.0
	 */
	private static function register_committee_term(): void {
		$labels = array(
			'name'          => _x( 'Committee Terms', 'taxonomy general name', 'aofa-core' ),
			'singular_name' => _x( 'Committee Term', 'taxonomy singular name', 'aofa-core' ),
			'search_items'  => __( 'Search Committee Terms', 'aofa-core' ),
			'all_items'     => __( 'All Committee Terms', 'aofa-core' ),
			'edit_item'     => __( 'Edit Committee Term', 'aofa-core' ),
			'update_item'   => __( 'Update Committee Term', 'aofa-core' ),
			'add_new_item'  => __( 'Add New Term', 'aofa-core' ),
			'new_item_name' => __( 'New Term Name', 'aofa-core' ),
			'menu_name'     => __( 'Committee Terms', 'aofa-core' ),
		);

		register_taxonomy(
			'aofa_committee_term',
			array( 'aofa_ec_member' ),
			array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'committee-term' ),
			)
		);
	}

	// ── aofa_article_category ────────────────────────────────────────────────

	/**
	 * Article Category taxonomy for aofa_article CPT.
	 * Terms: Article, Book Review.
	 *
	 * @since 1.0.0
	 */
	private static function register_article_category(): void {
		$labels = array(
			'name'          => _x( 'Article Categories', 'taxonomy general name', 'aofa-core' ),
			'singular_name' => _x( 'Article Category', 'taxonomy singular name', 'aofa-core' ),
			'search_items'  => __( 'Search Article Categories', 'aofa-core' ),
			'all_items'     => __( 'All Categories', 'aofa-core' ),
			'edit_item'     => __( 'Edit Category', 'aofa-core' ),
			'update_item'   => __( 'Update Category', 'aofa-core' ),
			'add_new_item'  => __( 'Add New Category', 'aofa-core' ),
			'new_item_name' => __( 'New Category Name', 'aofa-core' ),
			'menu_name'     => __( 'Categories', 'aofa-core' ),
		);

		register_taxonomy(
			'aofa_article_category',
			array( 'aofa_article' ),
			array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'article-category' ),
			)
		);
	}
}
