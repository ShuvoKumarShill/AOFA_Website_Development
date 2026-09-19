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
		// ── Member meta ────────────────────────────────────────────────────────
		add_meta_box(
			'aofa_member_details',
			__( 'Member Details', 'aofa-core' ),
			array( self::class, 'render_member_meta_box' ),
			'aofa_member',
			'normal',
			'high'
		);

		// ── EC Member meta ──────────────────────────────────────────────────────
		add_meta_box(
			'aofa_ec_member_details',
			__( 'Executive Committee Details', 'aofa-core' ),
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

		// ── Photo Gallery meta (Multiple Photos) ──────────────────────────────
		add_meta_box(
			'aofa_gallery_photos',
			__( 'Gallery Album Photos (Upload Multiple)', 'aofa-core' ),
			array( self::class, 'render_gallery_meta_box' ),
			'aofa_gallery',
			'normal',
			'high'
		);
	}

	// ── Member Meta Box ───────────────────────────────────────────────────────

	/**
	 * Render the Member Details meta box.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public static function render_member_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'aofa_save_member_meta', 'aofa_member_meta_nonce' );

		$no      = get_post_meta( $post->ID, '_aofa_member_no', true );
		$address = get_post_meta( $post->ID, '_aofa_address', true );
		$phone   = get_post_meta( $post->ID, '_aofa_phone', true );
		$email   = get_post_meta( $post->ID, '_aofa_email', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="aofa_member_no"><?php esc_html_e( 'Member No / Serial', 'aofa-core' ); ?></label></th>
				<td><input type="text" id="aofa_member_no" name="aofa_member_no" value="<?php echo esc_attr( $no ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aofa_address"><?php esc_html_e( 'Address', 'aofa-core' ); ?></label></th>
				<td><textarea id="aofa_address" name="aofa_address" class="large-text" rows="3"><?php echo esc_textarea( $address ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="aofa_phone"><?php esc_html_e( 'Phone', 'aofa-core' ); ?></label></th>
				<td><input type="text" id="aofa_phone" name="aofa_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aofa_email"><?php esc_html_e( 'Email', 'aofa-core' ); ?></label></th>
				<td><input type="email" id="aofa_email" name="aofa_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text" /></td>
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
			'_aofa_member_no' => array( 'key' => 'aofa_member_no', 'sanitize' => 'sanitize_text_field' ),
			'_aofa_address'   => array( 'key' => 'aofa_address',   'sanitize' => 'sanitize_textarea_field' ),
			'_aofa_phone'     => array( 'key' => 'aofa_phone',     'sanitize' => 'sanitize_text_field' ),
			'_aofa_email'     => array( 'key' => 'aofa_email',     'sanitize' => 'sanitize_email' ),
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

		$serial   = get_post_meta( $post->ID, '_aofa_ec_serial', true );
		$position = get_post_meta( $post->ID, '_aofa_ec_position', true );
		$term     = get_post_meta( $post->ID, '_aofa_ec_term', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="aofa_ec_serial"><?php esc_html_e( 'Serial No', 'aofa-core' ); ?></label></th>
				<td><input type="text" id="aofa_ec_serial" name="aofa_ec_serial" value="<?php echo esc_attr( $serial ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aofa_ec_position"><?php esc_html_e( 'Position', 'aofa-core' ); ?></label></th>
				<td><input type="text" id="aofa_ec_position" name="aofa_ec_position" value="<?php echo esc_attr( $position ); ?>" class="regular-text" placeholder="e.g. President, Vice President-I" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aofa_ec_term"><?php esc_html_e( 'Term', 'aofa-core' ); ?></label></th>
				<td><input type="text" id="aofa_ec_term" name="aofa_ec_term" value="<?php echo esc_attr( $term ); ?>" class="regular-text" placeholder="e.g. 2026-2027" /></td>
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
			'_aofa_ec_serial'   => array( 'key' => 'aofa_ec_serial',   'sanitize' => 'sanitize_text_field' ),
			'_aofa_ec_position' => array( 'key' => 'aofa_ec_position', 'sanitize' => 'sanitize_text_field' ),
			'_aofa_ec_term'     => array( 'key' => 'aofa_ec_term',     'sanitize' => 'sanitize_text_field' ),
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

	// ── Gallery Meta Box ──────────────────────────────────────────────────────

	/**
	 * Render the Gallery Album Photos (Multiple Image Upload) meta box.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public static function render_gallery_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'aofa_save_gallery_meta', 'aofa_gallery_meta_nonce' );

		$gallery_ids = get_post_meta( $post->ID, '_aofa_gallery_ids', true );
		$ids_array   = ! empty( $gallery_ids ) ? array_map( 'intval', explode( ',', $gallery_ids ) ) : array();
		?>
		<div class="aofa-gallery-meta-wrapper">
			<p class="description">
				<?php esc_html_e( 'Click the button below to upload or select multiple photos for this gallery album at once.', 'aofa-core' ); ?>
			</p>

			<div id="aofa_gallery_container" style="display:flex;flex-wrap:wrap;gap:12px;margin:15px 0;">
				<?php
				if ( ! empty( $ids_array ) ) {
					foreach ( $ids_array as $img_id ) {
						$thumb = wp_get_attachment_image_url( $img_id, 'thumbnail' );
						if ( $thumb ) {
							echo '<div class="aofa-gallery-thumb" data-id="' . esc_attr( $img_id ) . '" style="position:relative;width:90px;height:90px;border-radius:6px;overflow:hidden;border:1px solid #CBD5E1;">';
							echo '<img src="' . esc_url( $thumb ) . '" style="width:100%;height:100%;object-fit:cover;"/>';
							echo '<button type="button" class="aofa-remove-thumb" style="position:absolute;top:2px;right:2px;background:#EF4444;color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;font-size:11px;line-height:1;">&times;</button>';
							echo '</div>';
						}
					}
				}
				?>
			</div>

			<input type="hidden" id="aofa_gallery_ids" name="aofa_gallery_ids" value="<?php echo esc_attr( $gallery_ids ); ?>" />
			<button type="button" class="button button-primary button-large" id="aofa_upload_gallery_btn">
				<span class="dashicons dashicons-images-alt2" style="vertical-align:middle;margin-right:5px;"></span>
				<?php esc_html_e( 'Select / Upload Multiple Photos', 'aofa-core' ); ?>
			</button>
		</div>

		<script>
		jQuery(document).ready(function($){
			var frame;
			$('#aofa_upload_gallery_btn').on('click', function(e){
				e.preventDefault();
				if (frame) {
					frame.open();
					return;
				}
				frame = wp.media({
					title: 'Select or Upload Gallery Photos',
					button: { text: 'Add Photos to Gallery' },
					multiple: true
				});

				frame.on('select', function(){
					var selection = frame.state().get('selection');
					var currentIds = $('#aofa_gallery_ids').val() ? $('#aofa_gallery_ids').val().split(',') : [];

					selection.map(function(attachment){
						attachment = attachment.toJSON();
						if (currentIds.indexOf(attachment.id.toString()) === -1) {
							currentIds.push(attachment.id);
							var thumbUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
							var html = '<div class="aofa-gallery-thumb" data-id="' + attachment.id + '" style="position:relative;width:90px;height:90px;border-radius:6px;overflow:hidden;border:1px solid #CBD5E1;">' +
								'<img src="' + thumbUrl + '" style="width:100%;height:100%;object-fit:cover;"/>' +
								'<button type="button" class="aofa-remove-thumb" style="position:absolute;top:2px;right:2px;background:#EF4444;color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;font-size:11px;line-height:1;">&times;</button>' +
								'</div>';
							$('#aofa_gallery_container').append(html);
						}
					});

					$('#aofa_gallery_ids').val(currentIds.join(','));
				});

				frame.open();
			});

			$(document).on('click', '.aofa-remove-thumb', function(){
				var parent = $(this).closest('.aofa-gallery-thumb');
				var id = parent.data('id').toString();
				parent.remove();

				var ids = $('#aofa_gallery_ids').val().split(',');
				var index = ids.indexOf(id);
				if (index > -1) {
					ids.splice(index, 1);
				}
				$('#aofa_gallery_ids').val(ids.join(','));
			});
		});
		</script>
		<?php
	}

	/**
	 * Save Gallery meta on post save.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public static function save_gallery_meta( int $post_id ): void {
		if ( ! self::can_save( $post_id, 'aofa_save_gallery_meta', 'aofa_gallery_meta_nonce' ) ) {
			return;
		}

		$ids = isset( $_POST['aofa_gallery_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['aofa_gallery_ids'] ) ) : '';
		update_post_meta( $post_id, '_aofa_gallery_ids', $ids );
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
