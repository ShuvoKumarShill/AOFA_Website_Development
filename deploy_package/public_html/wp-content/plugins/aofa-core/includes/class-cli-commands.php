<?php
/**
 * AOFA Core — WP-CLI Commands
 *
 * Provides `wp aofa import-members`, `wp aofa import-ec`, `wp aofa import-notices`,
 * `wp aofa import-articles`, and `wp aofa status`.
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
	 * Import AOFA Members from a CSV file into the aofa_member post type.
	 *
	 * ## OPTIONS
	 *
	 * [--file=<file>]
	 * : Path to the CSV file. Defaults to plugins/aofa-core/data/members.csv.
	 *
	 * [--dry-run]
	 * : Parse and validate CSV without saving to database.
	 *
	 * ## EXAMPLES
	 *
	 *     wp aofa import-members --dry-run
	 *     wp aofa import-members --file=/path/to/members.csv
	 *
	 * @subcommand import-members
	 */
	public function import_members( array $args, array $assoc_args ): void {
		$file    = $assoc_args['file'] ?? ( AOFA_CORE_PATH . 'data/members.csv' );
		$dry_run = isset( $assoc_args['dry-run'] );

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "CSV file not found: {$file}" );
		}

		$handle = fopen( $file, 'r' );
		if ( false === $handle ) {
			WP_CLI::error( "Failed to open CSV file: {$file}" );
		}

		$headers = fgetcsv( $handle );
		$headers = array_map( 'strtolower', array_map( 'trim', (array) $headers ) );

		$imported = 0;
		$skipped  = 0;

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( count( $row ) !== count( $headers ) ) {
				continue;
			}
			$data = array_combine( $headers, $row );

			$no      = sanitize_text_field( $data['no'] ?? '' );
			$name    = sanitize_text_field( $data['name'] ?? '' );
			$address = sanitize_textarea_field( $data['address'] ?? '' );
			$phone   = sanitize_text_field( $data['phone'] ?? '' );
			$email   = sanitize_email( $data['email'] ?? '' );

			if ( empty( $name ) ) {
				continue;
			}

			$existing = get_posts( array(
				'post_type'      => 'aofa_member',
				'title'          => $name,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			) );

			if ( ! empty( $existing ) ) {
				++$skipped;
				continue;
			}

			if ( $dry_run ) {
				++$imported;
				WP_CLI::log( "[DRY-RUN] Would import member #{$no}: {$name}" );
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_title'   => $name,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'aofa_member',
			), true );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_aofa_member_no', $no );
				update_post_meta( $post_id, '_aofa_address', $address );
				update_post_meta( $post_id, '_aofa_phone', $phone );
				update_post_meta( $post_id, '_aofa_email', $email );

				$member_type = ( false !== stripos( $name, 'Honorary' ) ) ? 'Honorary' : 'Regular';
				wp_set_object_terms( $post_id, $member_type, 'aofa_member_type' );

				++$imported;
			}
		}
		fclose( $handle );

		if ( $dry_run ) {
			WP_CLI::success( "[DRY-RUN] Validated {$imported} members." );
		} else {
			WP_CLI::success( "Imported {$imported} members. (Skipped {$skipped} existing)" );
		}
	}

	/**
	 * Import AOFA Executive Committee members from CSV into aofa_ec_member.
	 *
	 * ## OPTIONS
	 *
	 * [--file=<file>]
	 * : Path to the CSV file. Defaults to plugins/aofa-core/data/ec_members.csv.
	 *
	 * [--dry-run]
	 * : Parse and validate CSV without saving to database.
	 *
	 * ## EXAMPLES
	 *
	 *     wp aofa import-ec --dry-run
	 *     wp aofa import-ec --file=/path/to/ec_members.csv
	 *
	 * @subcommand import-ec
	 */
	public function import_ec( array $args, array $assoc_args ): void {
		$file    = $assoc_args['file'] ?? ( AOFA_CORE_PATH . 'data/ec_members.csv' );
		$dry_run = isset( $assoc_args['dry-run'] );

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "CSV file not found: {$file}" );
		}

		$handle = fopen( $file, 'r' );
		if ( false === $handle ) {
			WP_CLI::error( "Failed to open CSV file: {$file}" );
		}

		$headers = fgetcsv( $handle );
		$headers = array_map( 'strtolower', array_map( 'trim', (array) $headers ) );

		$imported = 0;
		$skipped  = 0;

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( count( $row ) !== count( $headers ) ) {
				continue;
			}
			$data = array_combine( $headers, $row );

			$term     = sanitize_text_field( $data['term'] ?? '' );
			$serial   = sanitize_text_field( $data['serial'] ?? '' );
			$name     = sanitize_text_field( $data['name'] ?? '' );
			$position = sanitize_text_field( $data['position'] ?? '' );

			if ( empty( $name ) ) {
				continue;
			}

			$title = "{$name} - {$position}";

			$existing = get_posts( array(
				'post_type'      => 'aofa_ec_member',
				'title'          => $title,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			) );

			if ( ! empty( $existing ) ) {
				++$skipped;
				continue;
			}

			if ( $dry_run ) {
				++$imported;
				WP_CLI::log( "[DRY-RUN] Would import EC Member: {$title} ({$term})" );
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_title'   => $title,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'aofa_ec_member',
			), true );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_aofa_ec_serial', $serial );
				update_post_meta( $post_id, '_aofa_ec_position', $position );
				update_post_meta( $post_id, '_aofa_ec_term', $term );

				if ( ! empty( $term ) ) {
					wp_set_object_terms( $post_id, $term, 'aofa_committee_term' );
				}

				++$imported;
			}
		}
		fclose( $handle );

		if ( $dry_run ) {
			WP_CLI::success( "[DRY-RUN] Validated {$imported} EC members." );
		} else {
			WP_CLI::success( "Imported {$imported} EC members. (Skipped {$skipped} existing)" );
		}
	}

	/**
	 * Import AOFA Notices from a CSV file into the aofa_notice post type.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Parse and validate CSV without saving to database.
	 *
	 * ## EXAMPLES
	 *
	 *     wp aofa import-notices
	 *
	 * @subcommand import-notices
	 */
	public function import_notices( array $args, array $assoc_args ): void {
		$file    = AOFA_CORE_PATH . 'data/notices.csv';
		$dry_run = isset( $assoc_args['dry-run'] );

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "CSV file not found: {$file}" );
		}

		$handle = fopen( $file, 'r' );
		$headers = fgetcsv( $handle );
		$headers = array_map( 'strtolower', array_map( 'trim', (array) $headers ) );

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

			if ( $dry_run ) {
				++$imported;
				WP_CLI::log( "[DRY-RUN] Would import notice: {$title}" );
				continue;
			}

			wp_insert_post( array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'aofa_notice',
			) );
			++$imported;
		}
		fclose( $handle );

		if ( $dry_run ) {
			WP_CLI::success( "[DRY-RUN] Validated {$imported} notices." );
		} else {
			WP_CLI::success( "Imported {$imported} notices." );
		}
	}

	/**
	 * Import AOFA Articles from a CSV file into the aofa_article post type.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Parse and validate CSV without saving to database.
	 *
	 * ## EXAMPLES
	 *
	 *     wp aofa import-articles
	 *
	 * @subcommand import-articles
	 */
	public function import_articles( array $args, array $assoc_args ): void {
		$file    = AOFA_CORE_PATH . 'data/articles.csv';
		$dry_run = isset( $assoc_args['dry-run'] );

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "CSV file not found: {$file}" );
		}

		$handle = fopen( $file, 'r' );
		$headers = fgetcsv( $handle );
		$headers = array_map( 'strtolower', array_map( 'trim', (array) $headers ) );

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

			if ( $dry_run ) {
				++$imported;
				WP_CLI::log( "[DRY-RUN] Would import article: {$title}" );
				continue;
			}

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

		if ( $dry_run ) {
			WP_CLI::success( "[DRY-RUN] Validated {$imported} articles." );
		} else {
			WP_CLI::success( "Imported {$imported} articles." );
		}
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
		$member_count    = wp_count_posts( 'aofa_member' )->publish ?? 0;
		$ec_member_count = wp_count_posts( 'aofa_ec_member' )->publish ?? 0;
		$notice_count    = wp_count_posts( 'aofa_notice' )->publish ?? 0;
		$article_count   = wp_count_posts( 'aofa_article' )->publish ?? 0;

		WP_CLI::line( '╔══════════════════════════════╗' );
		WP_CLI::line( '║      AOFA Core Status        ║' );
		WP_CLI::line( '╚══════════════════════════════╝' );
		WP_CLI::line( "  Plugin version : " . AOFA_CORE_VERSION );
		WP_CLI::line( "  Members        : {$member_count}" );
		WP_CLI::line( "  EC Members     : {$ec_member_count}" );
		WP_CLI::line( "  Notices        : {$notice_count}" );
		WP_CLI::line( "  Articles       : {$article_count}" );
	}
}
