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
		self::register_article_category();
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
