<?php
/**
 * AOFA Core — Custom Meta Boxes
 *
 * Registers and renders meta boxes for all AOFA CPTs.
 * All inputs are sanitized on save and escaped on output.
 * Uses nonces to prevent CSRF (WCAG/Security rule).
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
 * Class Aofa_Meta_Boxes
 */
class Aofa_Meta_Boxes {

	/**
	 * Register all meta boxes on the 'add_meta_boxes' hook.
	 *
	 * @since 1.0.0
	 */
	public static function register(): void {
		// ── Article meta ──────────────────────────────────────────────────────
		add_meta_box(
			'aofa_article_details',
			__( 'Article Details', 'aofa-core' ),
			array( self::class, 'render_article_meta_box' ),
			'aofa_article',
			'normal',
			'high'
		);
	}

	// ── Article Meta Box ──────────────────────────────────────────────────────

	/**
	 * Render the Article Details meta box.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public static function render_article_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'aofa_save_article_meta', 'aofa_article_meta_nonce' );

		$author      = get_post_meta( $post->ID, '_aofa_author', true );
		$publication = get_post_meta( $post->ID, '_aofa_publication', true );
		$language    = get_post_meta( $post->ID, '_aofa_language', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="aofa_author"><?php esc_html_e( 'Author / Reviewer', 'aofa-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="aofa_author" name="aofa_author"
						value="<?php echo esc_attr( $author ); ?>"
						class="regular-text"
						placeholder="<?php esc_attr_e( 'Amb. Full Name', 'aofa-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="aofa_publication"><?php esc_html_e( 'Originally Published In', 'aofa-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="aofa_publication" name="aofa_publication"
						value="<?php echo esc_attr( $publication ); ?>"
						class="regular-text"
						placeholder="<?php esc_attr_e( 'e.g. The Daily Star', 'aofa-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="aofa_language"><?php esc_html_e( 'Language', 'aofa-core' ); ?></label>
				</th>
				<td>
					<select id="aofa_language" name="aofa_language">
						<option value="English" <?php selected( $language, 'English' ); ?>><?php esc_html_e( 'English', 'aofa-core' ); ?></option>
						<option value="Bengali" <?php selected( $language, 'Bengali' ); ?>><?php esc_html_e( 'Bengali', 'aofa-core' ); ?></option>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save Article meta on post save.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public static function save_article_meta( int $post_id ): void {
		if ( ! self::can_save( $post_id, 'aofa_save_article_meta', 'aofa_article_meta_nonce' ) ) {
			return;
		}

		$allowed_languages = array( 'English', 'Bengali' );
		$language          = isset( $_POST['aofa_language'] ) ? sanitize_text_field( wp_unslash( $_POST['aofa_language'] ) ) : 'English';
		if ( ! in_array( $language, $allowed_languages, true ) ) {
			$language = 'English';
		}

		$fields = array(
			'_aofa_author'      => array( 'key' => 'aofa_author',      'sanitize' => 'sanitize_text_field' ),
			'_aofa_publication' => array( 'key' => 'aofa_publication', 'sanitize' => 'sanitize_text_field' ),
		);

		self::save_fields( $post_id, $fields );
		update_post_meta( $post_id, '_aofa_language', $language );
	}

	// ── Shared Helpers ────────────────────────────────────────────────────────

	/**
	 * Verify nonce, autosave, and user capability before saving.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  string $action  Nonce action string.
	 * @param  string $nonce   Nonce field name in $_POST.
	 * @return bool            True if allowed to save.
	 */
	private static function can_save( int $post_id, string $action, string $nonce ): bool {
		// Bail on autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		// Verify nonce.
		if (
			! isset( $_POST[ $nonce ] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), $action )
		) {
			return false;
		}

		// Check user capabilities.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Save a set of post meta fields using their sanitisation callback.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $fields  Map of meta_key => ['key' => post_key, 'sanitize' => callable].
	 */
	private static function save_fields( int $post_id, array $fields ): void {
		foreach ( $fields as $meta_key => $field ) {
			$raw_value = isset( $_POST[ $field['key'] ] )
				? wp_unslash( $_POST[ $field['key'] ] )
				: '';

			$value = call_user_func( $field['sanitize'], $raw_value );
			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}
