<?php
/**
 * AOFA Core — WP-CLI Commands
 *
 * Provides `wp aofa import members` to bulk-import the recovered member data
 * from CSV into the aofa_member custom post type.
 *
 * Only loaded when WP_CLI is defined (CLI context only).
 * Security: all data sanitized before insert. No direct DB writes — uses
 * wp_insert_post() which handles escaping internally.
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
 * Class Aofa_CLI_Commands
 *
 * Registered as: `wp aofa <subcommand>`
 *
 * @since 1.0.0
 */
class Aofa_CLI_Commands {

	/**
	 * Import AOFA Notices from a CSV file into the aofa_notice post type.
	 *
	 * ## EXAMPLES
	 *
	 *     wp aofa import-notices
	 *
	 * @subcommand import-notices
	 */
	public function import_notices( array $args, array $assoc_args ): void {
		$file = AOFA_CORE_PATH . 'data/notices.csv';
		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "CSV file not found: {$file}" );
		}

		$handle = fopen( $file, 'r' );
		$headers = fgetcsv( $handle );
		$headers = array_map( 'strtolower', array_map( 'trim', $headers ) );

		$imported = 0;
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( count( $row ) !== count( $headers ) ) continue;
			$data = array_combine( $headers, $row );
			
			$title   = sanitize_text_field( $data['title'] ?? '' );
			$content = sanitize_textarea_field( $data['content'] ?? '' );
			
			if ( empty( $title ) ) continue;

			$existing = get_posts( array(
				'post_type'      => 'aofa_notice',
				'title'          => $title,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			) );

			if ( ! empty( $existing ) ) continue;

			wp_insert_post( array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'aofa_notice',
			) );
			++$imported;
		}
		fclose( $handle );
		WP_CLI::success( "Imported {$imported} notices." );
	}

	/**
	 * Import AOFA Articles from a CSV file into the aofa_article post type.
	 *
	 * ## EXAMPLES
	 *
	 *     wp aofa import-articles
	 *
	 * @subcommand import-articles
	 */
	public function import_articles( array $args, array $assoc_args ): void {
		$file = AOFA_CORE_PATH . 'data/articles.csv';
		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "CSV file not found: {$file}" );
		}

		$handle = fopen( $file, 'r' );
		$headers = fgetcsv( $handle );
		$headers = array_map( 'strtolower', array_map( 'trim', $headers ) );

		$imported = 0;
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( count( $row ) !== count( $headers ) ) continue;
			$data = array_combine( $headers, $row );
			
			$title    = sanitize_text_field( $data['title'] ?? '' );
			$author   = sanitize_text_field( $data['author'] ?? '' );
			$content  = sanitize_textarea_field( $data['content'] ?? '' );
			$category = sanitize_text_field( $data['category'] ?? '' );
			
			if ( empty( $title ) ) continue;

			$existing = get_posts( array(
				'post_type'      => 'aofa_article',
				'title'          => $title,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			) );

			if ( ! empty( $existing ) ) continue;

			$post_id = wp_insert_post( array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'aofa_article',
			), true );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_aofa_author', $author );
				if ( ! empty( $category ) ) {
					wp_set_object_terms( $post_id, $category, 'aofa_article_category' );
				}
				++$imported;
			}
		}
		fclose( $handle );
		WP_CLI::success( "Imported {$imported} articles." );
	}

	/**
	 * Display AOFA Core plugin status.
	 *
	 * ## EXAMPLES
	 *
	 *     wp aofa status
	 *
	 * @since 1.0.0
	 */
	public function status(): void {
		$notice_count  = wp_count_posts( 'aofa_notice' )->publish ?? 0;
		$article_count = wp_count_posts( 'aofa_article' )->publish ?? 0;

		WP_CLI::line( '╔══════════════════════════════╗' );
		WP_CLI::line( '║      AOFA Core Status        ║' );
		WP_CLI::line( '╚══════════════════════════════╝' );
		WP_CLI::line( "  Plugin version : " . AOFA_CORE_VERSION );
		WP_CLI::line( "  Notices        : {$notice_count}" );
		WP_CLI::line( "  Articles       : {$article_count}" );
	}
}
