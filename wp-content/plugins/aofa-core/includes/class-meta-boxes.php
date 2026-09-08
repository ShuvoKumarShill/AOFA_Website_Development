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
		// ── Member meta ──────────────────────────────────────────────────────
		add_meta_box(
			'aofa_member_details',
			__( 'Member Contact Details', 'aofa-core' ),
			array( self::class, 'render_member_meta_box' ),
			'aofa_member',
			'normal',
			'high'
		);

		// ── EC Member meta ────────────────────────────────────────────────────
		add_meta_box(
			'aofa_ec_member_details',
			__( 'EC Member Details', 'aofa-core' ),
			array( self::class, 'render_ec_member_meta_box' ),
			'aofa_ec_member',
			'normal',
			'high'
		);

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

	// ── Member Meta Box ───────────────────────────────────────────────────────

	/**
	 * Render the Member Contact Details meta box.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public static function render_member_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'aofa_save_member_meta', 'aofa_member_meta_nonce' );

		$phone   = get_post_meta( $post->ID, '_aofa_phone', true );
		$email   = get_post_meta( $post->ID, '_aofa_email', true );
		$address = get_post_meta( $post->ID, '_aofa_address', true );
		$serial  = get_post_meta( $post->ID, '_aofa_serial', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="aofa_serial"><?php esc_html_e( 'Serial No.', 'aofa-core' ); ?></label>
				</th>
				<td>
					<input type="number" id="aofa_serial" name="aofa_serial"
						value="<?php echo esc_attr( $serial ); ?>"
						class="small-text" min="1" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="aofa_phone"><?php esc_html_e( 'Phone', 'aofa-core' ); ?></label>
				</th>
				<td>
					<input type="tel" id="aofa_phone" name="aofa_phone"
						value="<?php echo esc_attr( $phone ); ?>"
						class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="aofa_email"><?php esc_html_e( 'Email', 'aofa-core' ); ?></label>
				</th>
				<td>
					<input type="email" id="aofa_email" name="aofa_email"
						value="<?php echo esc_attr( $email ); ?>"
						class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="aofa_address"><?php esc_html_e( 'Address', 'aofa-core' ); ?></label>
				</th>
				<td>
					<textarea id="aofa_address" name="aofa_address" rows="3"
						class="large-text"><?php echo esc_textarea( $address ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save Member meta on post save.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public static function save_member_meta( int $post_id ): void {
		if ( ! self::can_save( $post_id, 'aofa_save_member_meta', 'aofa_member_meta_nonce' ) ) {
			return;
		}

		$fields = array(
			'_aofa_serial'  => array( 'key' => 'aofa_serial',  'sanitize' => 'intval' ),
			'_aofa_phone'   => array( 'key' => 'aofa_phone',   'sanitize' => 'sanitize_text_field' ),
			'_aofa_email'   => array( 'key' => 'aofa_email',   'sanitize' => 'sanitize_email' ),
			'_aofa_address' => array( 'key' => 'aofa_address', 'sanitize' => 'sanitize_textarea_field' ),
		);

		self::save_fields( $post_id, $fields );
	}

	// ── EC Member Meta Box ────────────────────────────────────────────────────

	/**
	 * Render the EC Member Details meta box.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public static function render_ec_member_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'aofa_save_ec_member_meta', 'aofa_ec_member_meta_nonce' );

		$position = get_post_meta( $post->ID, '_aofa_position', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="aofa_position"><?php esc_html_e( 'Position / Title', 'aofa-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="aofa_position" name="aofa_position"
						value="<?php echo esc_attr( $position ); ?>"
						class="regular-text"
						placeholder="<?php esc_attr_e( 'e.g. President, Secretary General', 'aofa-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'Official position in the Executive Committee.', 'aofa-core' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save EC Member meta on post save.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public static function save_ec_member_meta( int $post_id ): void {
		if ( ! self::can_save( $post_id, 'aofa_save_ec_member_meta', 'aofa_ec_member_meta_nonce' ) ) {
			return;
		}

		$fields = array(
			'_aofa_position' => array( 'key' => 'aofa_position', 'sanitize' => 'sanitize_text_field' ),
		);

		self::save_fields( $post_id, $fields );
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
