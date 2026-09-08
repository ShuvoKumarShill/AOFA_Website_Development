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
	 * Import AOFA members from a CSV file into the aofa_member post type.
	 *
	 * ## OPTIONS
	 *
	 * [--file=<file>]
	 * : Absolute path to the CSV file. Defaults to the bundled members.csv.
	 *
	 * [--dry-run]
	 * : Run without inserting data. Shows what would be imported.
	 *
	 * [--type=<type>]
	 * : Member type to assign: 'regular' or 'honorary'. Default: 'regular'.
	 *
	 * ## EXAMPLES
	 *
	 *     wp aofa import members
	 *     wp aofa import members --file=/path/to/members.csv --dry-run
	 *     wp aofa import members --type=honorary --file=/path/to/honorary.csv
	 *
	 * @subcommand import-members
	 *
	 * @param array $args       Positional arguments (unused).
	 * @param array $assoc_args Associative arguments (flags).
	 */
	public function import_members( array $args, array $assoc_args ): void {

		// ── Resolve file path ────────────────────────────────────────────────
		$default_file = AOFA_CORE_PATH . 'data/members.csv';
		$file         = isset( $assoc_args['file'] ) ? $assoc_args['file'] : $default_file;

		if ( ! file_exists( $file ) ) {
			WP_CLI::error( "CSV file not found: {$file}" );
		}

		$dry_run     = isset( $assoc_args['dry-run'] );
		$member_type = isset( $assoc_args['type'] ) ? $assoc_args['type'] : 'regular';

		if ( ! in_array( $member_type, array( 'regular', 'honorary' ), true ) ) {
			WP_CLI::error( '--type must be "regular" or "honorary".' );
		}

		// ── Open CSV ─────────────────────────────────────────────────────────
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$handle = fopen( $file, 'r' );
		if ( false === $handle ) {
			WP_CLI::error( "Cannot open file: {$file}" );
		}

		// Read header row.
		$headers = fgetcsv( $handle );
		if ( false === $headers ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			WP_CLI::error( 'CSV file is empty or unreadable.' );
		}

		// Normalize header names (lowercase, trim).
		$headers = array_map( 'strtolower', array_map( 'trim', $headers ) );

		$imported = 0;
		$skipped  = 0;
		$progress = WP_CLI\Utils\make_progress_bar( 'Importing members', 0 );

		// ── Process rows ─────────────────────────────────────────────────────
		while ( ( $row = fgetcsv( $handle ) ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition

			if ( count( $row ) !== count( $headers ) ) {
				WP_CLI::warning( 'Skipping malformed row: ' . implode( ',', $row ) );
				++$skipped;
				continue;
			}

			$data = array_combine( $headers, $row );

			// ── Sanitize all input (security-first) ──────────────────────────
			$name    = sanitize_text_field( $data['name'] ?? '' );
			$address = sanitize_textarea_field( $data['address'] ?? '' );
			$phone   = sanitize_text_field( $data['phone'] ?? '' );
			$email   = sanitize_email( $data['email'] ?? '' );

			if ( empty( $name ) ) {
				WP_CLI::warning( 'Skipping row with empty name.' );
				++$skipped;
				continue;
			}

			// ── Check for existing post (avoid duplicates) ───────────────────
			$existing = get_posts(
				array(
					'post_type'      => 'aofa_member',
					'title'          => $name,
					'posts_per_page' => 1,
					'post_status'    => 'any',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $existing ) ) {
				WP_CLI::line( "  Skipping existing member: {$name}" );
				++$skipped;
				continue;
			}

			if ( $dry_run ) {
				WP_CLI::line( "[DRY-RUN] Would import: {$name} | {$phone} | {$email}" );
				++$imported;
				$progress->tick();
				continue;
			}

			// ── Insert post ──────────────────────────────────────────────────
			$post_id = wp_insert_post(
				array(
					'post_title'   => $name,
					'post_status'  => 'publish',
					'post_type'    => 'aofa_member',
					'post_content' => '', // Content can be enriched later.
				),
				true // Return WP_Error on failure.
			);

			if ( is_wp_error( $post_id ) ) {
				WP_CLI::warning( "Failed to insert '{$name}': " . $post_id->get_error_message() );
				++$skipped;
				continue;
			}

			// ── Store contact details as post meta ───────────────────────────
			// Using update_post_meta ensures idempotency.
			update_post_meta( $post_id, '_aofa_address', $address );
			update_post_meta( $post_id, '_aofa_phone',   $phone );
			update_post_meta( $post_id, '_aofa_email',   $email );

			// ── Assign member type taxonomy ──────────────────────────────────
			wp_set_object_terms( $post_id, $member_type, 'aofa_member_type' );

			++$imported;
			$progress->tick();
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		fclose( $handle );
		$progress->finish();

		// ── Summary ──────────────────────────────────────────────────────────
		$mode = $dry_run ? '[DRY-RUN] Would have imported' : 'Imported';
		WP_CLI::success( "{$mode} {$imported} members. Skipped: {$skipped}." );
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
		$member_count  = wp_count_posts( 'aofa_member' )->publish ?? 0;
		$ec_count      = wp_count_posts( 'aofa_ec_member' )->publish ?? 0;
		$notice_count  = wp_count_posts( 'aofa_notice' )->publish ?? 0;
		$article_count = wp_count_posts( 'aofa_article' )->publish ?? 0;

		WP_CLI::line( '╔══════════════════════════════╗' );
		WP_CLI::line( '║      AOFA Core Status        ║' );
		WP_CLI::line( '╚══════════════════════════════╝' );
		WP_CLI::line( "  Plugin version : " . AOFA_CORE_VERSION );
		WP_CLI::line( "  Members        : {$member_count}" );
		WP_CLI::line( "  EC Members     : {$ec_count}" );
		WP_CLI::line( "  Notices        : {$notice_count}" );
		WP_CLI::line( "  Articles       : {$article_count}" );
	}
}
