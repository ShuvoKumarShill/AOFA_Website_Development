<?php
/**
 * Plugin Name:       AOFA Core
 * Plugin URI:        https://aofabd.com
 * Description:       Core plugin for the Association of Former BCS(FA) Ambassadors website. Registers custom post types, taxonomies, and WP-CLI import commands. No page-builder dependencies.
 * Version:           1.0.0
 * Author:            AOFA Web Team
 * Author URI:        https://aofabd.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aofa-core
 * Domain Path:       /languages
 * Requires at least: 6.6
 * Requires PHP:      8.2
 * Spec Item:         002-content-types
 *
 * @package AofaCore
 */

// Security: prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Constants ────────────────────────────────────────────────────────────────

/** Plugin version. */
define( 'AOFA_CORE_VERSION', '1.0.0' );

/** Absolute path to the plugin directory (with trailing slash). */
define( 'AOFA_CORE_PATH', plugin_dir_path( __FILE__ ) );

/** URL to the plugin directory (with trailing slash). */
define( 'AOFA_CORE_URL', plugin_dir_url( __FILE__ ) );

// ── Autoload ─────────────────────────────────────────────────────────────────

/**
 * Manually require class files.
 * PSR-4 autoloader is overkill for a focused plugin; keep it simple.
 */
require_once AOFA_CORE_PATH . 'includes/class-post-types.php';
require_once AOFA_CORE_PATH . 'includes/class-taxonomies.php';
require_once AOFA_CORE_PATH . 'includes/class-meta-boxes.php';
require_once AOFA_CORE_PATH . 'includes/redirects.php';

// WP-CLI commands are only loaded in the CLI context.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once AOFA_CORE_PATH . 'includes/class-cli-commands.php';
}

// ── Bootstrap ────────────────────────────────────────────────────────────────

/**
 * Initialise the plugin after all plugins are loaded.
 * Using 'init' hook ensures CPTs/taxonomies are registered at the right time.
 */
function aofa_core_init(): void {
	// Load plugin text domain for translations.
	load_plugin_textdomain(
		'aofa-core',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);

	// Register custom post types.
	Aofa_Post_Types::register();

	// Register custom taxonomies.
	Aofa_Taxonomies::register();
}
add_action( 'init', 'aofa_core_init' );

/**
 * Inject Executive Diplomatic Theme Styles to ensure high-end aesthetics.
 */
add_action( 'wp_head', function() {
	?>
	<style id="aofa-executive-styles">
		/* ── Navigation Header Fixes ── */
		.ast-primary-header-bar .site-navigation {
			display: flex !important;
			justify-content: flex-end !important;
		}
		.main-header-menu {
			display: flex !important;
			flex-wrap: nowrap !important;
			align-items: center !important;
			gap: 0.85rem !important;
		}
		.main-header-menu .menu-item > a {
			padding: 0 8px !important;
			font-size: 0.92rem !important;
			font-weight: 600 !important;
			color: #002B49 !important;
			white-space: nowrap !important;
			transition: color 0.25s ease !important;
		}
		.main-header-menu .menu-item > a:hover,
		.main-header-menu .menu-item.current-menu-item > a {
			color: #C5A059 !important;
		}
		.main-header-menu .sub-menu {
			border-top: 3px solid #C5A059 !important;
			border-radius: 0 0 6px 6px !important;
			box-shadow: 0 10px 25px rgba(0, 43, 73, 0.12) !important;
		}

		/* ── Query Loop Cards Uniform Proportions ── */
		.wp-block-query .wp-block-post-template {
			display: grid !important;
			grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
			gap: 1.75rem !important;
		}
		.wp-block-query .wp-block-group.has-white-background-color {
			border-radius: 10px !important;
			border: 1px solid #E2E8F0 !important;
			transition: all 0.3s ease !important;
			background: #ffffff !important;
			box-shadow: 0 4px 14px rgba(0, 43, 73, 0.05) !important;
		}
		.wp-block-query .wp-block-group.has-white-background-color:hover {
			transform: translateY(-4px) !important;
			box-shadow: 0 14px 24px -6px rgba(0, 43, 73, 0.12) !important;
			border-color: #C5A059 !important;
		}

		/* ── Executive Emblem Box ── */
		.wp-block-image img {
			border-radius: 8px;
		}

		/* ── Buttons ── */
		.wp-block-button__link {
			transition: all 0.3s ease !important;
		}
		.wp-block-button__link:hover {
			transform: translateY(-2px) !important;
			box-shadow: 0 8px 18px rgba(0, 43, 73, 0.2) !important;
		}
	</style>
	<?php
}, 999 );

// ── Meta Boxes ───────────────────────────────────────────────────────────────

/** Register meta boxes in the admin. */
add_action( 'add_meta_boxes', array( 'Aofa_Meta_Boxes', 'register' ) );

/** Save Article meta. */
add_action( 'save_post_aofa_article', array( 'Aofa_Meta_Boxes', 'save_article_meta' ) );

/** Save Gallery meta. */
add_action( 'save_post_aofa_gallery', array( 'Aofa_Meta_Boxes', 'save_gallery_meta' ) );

/** Enqueue WordPress media scripts in admin for photo gallery post type. */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	global $post_type;
	if ( 'aofa_gallery' === $post_type ) {
		wp_enqueue_media();
	}
} );

/**
 * Shortcode [aofa_photo_gallery]
 * Renders album cards and opens full-screen Lightbox Modal Slider when clicked.
 */
function aofa_render_photo_gallery_shortcode( $atts = array() ): string {
	$posts = get_posts( array(
		'post_type'      => 'aofa_gallery',
		'posts_per_page' => 12,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	if ( empty( $posts ) ) {
		return '<p style="text-align:center;color:#64748B;">No photo albums available.</p>';
	}

	$albums_data = array();

	foreach ( $posts as $p ) {
		$photos = array();

		// Featured Image
		$feat_id = get_post_thumbnail_id( $p->ID );
		if ( $feat_id ) {
			$feat_url = wp_get_attachment_image_url( $feat_id, 'large' );
			if ( $feat_url ) {
				$photos[] = array(
					'url'     => $feat_url,
					'caption' => get_the_title( $feat_id ),
				);
			}
		}

		// Additional Gallery IDs from Meta
		$gallery_ids = get_post_meta( $p->ID, '_aofa_gallery_ids', true );
		if ( ! empty( $gallery_ids ) ) {
			$ids = array_map( 'intval', explode( ',', $gallery_ids ) );
			foreach ( $ids as $img_id ) {
				if ( $img_id === (int) $feat_id ) {
					continue; // avoid duplicate
				}
				$img_url = wp_get_attachment_image_url( $img_id, 'large' );
				if ( $img_url ) {
					$photos[] = array(
						'url'     => $img_url,
						'caption' => get_the_title( $img_id ),
					);
				}
			}
		}

		// Fallback if no images attached
		if ( empty( $photos ) ) {
			$photos[] = array(
				'url'     => home_url( '/wp-content/themes/aofa-theme/assets/images/gallery-conference.png' ),
				'caption' => $p->post_title,
			);
		}

		$albums_data[] = array(
			'id'      => $p->ID,
			'title'   => get_the_title( $p ),
			'excerpt' => get_the_excerpt( $p ),
			'photos'  => $photos,
			'cover'   => $photos[0]['url'],
			'count'   => count( $photos ),
		);
	}

	ob_start();
	?>
	<div class="aofa-lightbox-gallery-wrapper">
		<div class="aofa-gallery-grid">
			<?php foreach ( $albums_data as $idx => $album ) : ?>
				<div class="aofa-gallery-item" onclick="openAofaLightbox(<?php echo esc_attr( $idx ); ?>)" style="cursor:pointer;">
					<div class="aofa-gallery-img-wrapper">
						<img src="<?php echo esc_url( $album['cover'] ); ?>" alt="<?php echo esc_attr( $album['title'] ); ?>"/>
						<div class="aofa-photo-count-badge">
							<span class="dashicons dashicons-images-alt2"></span>
							<?php echo esc_html( $album['count'] ); ?> <?php echo ( $album['count'] === 1 ) ? 'Photo' : 'Photos'; ?>
						</div>
					</div>
					<div class="aofa-gallery-caption">
						<span class="aofa-badge">Album</span>
						<h3 class="aofa-gallery-title"><?php echo esc_html( $album['title'] ); ?></h3>
						<p class="aofa-gallery-desc"><?php echo esc_html( $album['excerpt'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- LIGHTBOX SLIDER MODAL OVERLAY -->
	<div id="aofaLightboxModal" class="aofa-lightbox-modal" style="display:none;">
		<div class="aofa-lightbox-backdrop" onclick="closeAofaLightbox()"></div>
		<div class="aofa-lightbox-content">
			<button class="aofa-lightbox-close" onclick="closeAofaLightbox()" aria-label="Close">&times;</button>
			
			<button id="aofaLightboxPrevBtn" class="aofa-lightbox-arrow aofa-lightbox-prev" onclick="changeAofaLightboxPhoto(-1)">&#10094;</button>
			<button id="aofaLightboxNextBtn" class="aofa-lightbox-arrow aofa-lightbox-next" onclick="changeAofaLightboxPhoto(1)">&#10095;</button>

			<div class="aofa-lightbox-body">
				<div class="aofa-lightbox-header-info">
					<h3 id="aofaLightboxTitle"></h3>
					<span id="aofaLightboxCounter"></span>
				</div>

				<div class="aofa-lightbox-main-stage">
					<img id="aofaLightboxImg" src="" alt=""/>
				</div>

				<p id="aofaLightboxCaption"></p>
				
				<div id="aofaLightboxThumbs" class="aofa-lightbox-thumbs-strip"></div>
			</div>
		</div>
	</div>

	<style>
	/* ── Photo Gallery Grid & Cards ── */
	.aofa-gallery-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
		gap: 1.75rem;
	}
	.aofa-gallery-item {
		background: #ffffff;
		border-radius: 10px;
		overflow: hidden;
		box-shadow: 0 6px 18px -4px rgba(0,43,73,0.07);
		border: 1px solid #E2E8F0;
		transition: all 0.3s ease;
		display: flex;
		flex-direction: column;
	}
	.aofa-gallery-item:hover {
		transform: translateY(-5px);
		box-shadow: 0 16px 28px -8px rgba(0,43,73,0.14);
		border-color: #C5A059;
	}
	.aofa-gallery-img-wrapper {
		position: relative;
		height: 180px !important;
		width: 100% !important;
		overflow: hidden;
		background-color: #001930;
	}
	.aofa-gallery-img-wrapper img {
		width: 100% !important;
		height: 180px !important;
		object-fit: cover !important;
		display: block;
		transition: transform 0.4s ease;
	}
	.aofa-gallery-item:hover .aofa-gallery-img-wrapper img {
		transform: scale(1.05);
	}
	.aofa-photo-count-badge {
		position: absolute;
		bottom: 10px;
		right: 10px;
		background: rgba(0, 43, 73, 0.88);
		color: #C5A059;
		padding: 3px 10px;
		border-radius: 4px;
		font-size: 0.78rem;
		font-weight: 700;
		letter-spacing: 0.3px;
		display: flex;
		align-items: center;
		gap: 4px;
		backdrop-filter: blur(4px);
		border: 1px solid rgba(197, 160, 89, 0.3);
	}
	.aofa-gallery-caption {
		padding: 1.25rem;
		background: #ffffff;
		flex: 1;
	}
	.aofa-gallery-title {
		font-size: 1.1rem;
		font-weight: 700;
		color: #002B49;
		margin: 0 0 0.35rem 0;
		line-height: 1.35;
	}
	.aofa-gallery-desc {
		font-size: 0.9rem;
		color: #64748B;
		margin: 0;
		line-height: 1.5;
	}
	.aofa-badge {
		display: inline-block;
		background: #F1F5F9;
		color: #002B49;
		font-size: 0.72rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		padding: 3px 8px;
		border-radius: 4px;
		border-left: 3px solid #C5A059;
		margin-bottom: 0.5rem;
	}

	/* ── Lightbox Modal Slider Overlay (Append-to-body) ── */
	.aofa-lightbox-modal {
		position: fixed !important;
		top: 0 !important;
		left: 0 !important;
		right: 0 !important;
		bottom: 0 !important;
		width: 100vw !important;
		height: 100vh !important;
		z-index: 999999 !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
	}
	.aofa-lightbox-backdrop {
		position: absolute !important;
		top: 0 !important;
		left: 0 !important;
		width: 100% !important;
		height: 100% !important;
		background: rgba(4, 18, 33, 0.96) !important;
		backdrop-filter: blur(10px);
	}
	.aofa-lightbox-content {
		position: relative !important;
		z-index: 10 !important;
		width: 90% !important;
		max-width: 900px !important;
		max-height: 90vh !important;
		display: flex !important;
		flex-direction: column !important;
		align-items: center !important;
		margin: auto !important;
	}
	.aofa-lightbox-close {
		position: fixed !important;
		top: 20px !important;
		right: 30px !important;
		background: rgba(255,255,255,0.1) !important;
		color: #ffffff !important;
		border: 1px solid rgba(255,255,255,0.2) !important;
		width: 44px !important;
		height: 44px !important;
		border-radius: 50% !important;
		font-size: 1.8rem !important;
		cursor: pointer !important;
		line-height: 1 !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		transition: all 0.25s ease !important;
		z-index: 1000000 !important;
	}
	.aofa-lightbox-close:hover {
		background: #C5A059 !important;
		color: #002B49 !important;
		border-color: #C5A059 !important;
	}
	.aofa-lightbox-arrow {
		position: absolute !important;
		top: 50% !important;
		transform: translateY(-50%) !important;
		background: rgba(0, 43, 73, 0.85) !important;
		color: #C5A059 !important;
		border: 1px solid #C5A059 !important;
		width: 48px !important;
		height: 48px !important;
		border-radius: 50% !important;
		font-size: 1.4rem !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		cursor: pointer !important;
		transition: all 0.25s ease !important;
		z-index: 20 !important;
	}
	.aofa-lightbox-arrow:hover {
		background: #C5A059 !important;
		color: #002B49 !important;
	}
	.aofa-lightbox-prev { left: -24px !important; }
	.aofa-lightbox-next { right: -24px !important; }

	.aofa-lightbox-header-info {
		text-align: center;
		margin-bottom: 8px;
	}
	.aofa-lightbox-header-info h3 {
		color: #ffffff !important;
		margin: 0 0 4px !important;
		font-size: 1.25rem !important;
		font-weight: 700 !important;
	}
	.aofa-lightbox-header-info span {
		color: #C5A059 !important;
		font-size: 0.85rem !important;
		font-weight: 600 !important;
		letter-spacing: 0.5px;
	}

	.aofa-lightbox-body {
		width: 100%;
		text-align: center;
	}
	.aofa-lightbox-main-stage {
		max-height: 58vh;
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		margin: 10px 0;
	}
	.aofa-lightbox-main-stage img {
		max-width: 100% !important;
		max-height: 56vh !important;
		width: auto !important;
		height: auto !important;
		object-fit: contain !important;
		border-radius: 8px;
		box-shadow: 0 16px 40px rgba(0,0,0,0.6);
		border: 1px solid rgba(197, 160, 89, 0.35);
	}
	#aofaLightboxCaption {
		color: #CBD5E1 !important;
		font-size: 0.92rem !important;
		margin: 10px auto 0 !important;
		text-align: center !important;
		max-width: 750px !important;
		line-height: 1.5 !important;
	}
	.aofa-lightbox-thumbs-strip {
		display: flex;
		gap: 8px;
		justify-content: center;
		margin-top: 12px;
		overflow-x: auto;
		padding-bottom: 4px;
	}
	.aofa-lightbox-thumb-item {
		width: 54px;
		height: 54px;
		border-radius: 6px;
		overflow: hidden;
		opacity: 0.55;
		cursor: pointer;
		border: 2px solid transparent;
		transition: all 0.25s ease;
		flex-shrink: 0;
	}
	.aofa-lightbox-thumb-item.active,
	.aofa-lightbox-thumb-item:hover {
		opacity: 1;
		border-color: #C5A059;
	}
	.aofa-lightbox-thumb-item img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}
	</style>

	<script>
	var aofaAlbums = <?php echo wp_json_encode( $albums_data ); ?>;
	var activeAlbumIdx = 0;
	var activePhotoIdx = 0;

	function openAofaLightbox(albumIndex) {
		if (!aofaAlbums[albumIndex]) return;
		activeAlbumIdx = albumIndex;
		activePhotoIdx = 0;
		
		var modal = document.getElementById('aofaLightboxModal');
		if (modal && modal.parentElement !== document.body) {
			document.body.appendChild(modal);
		}

		renderAofaLightboxPhoto();
		modal.style.display = 'flex';
		document.body.style.overflow = 'hidden';
	}

	function closeAofaLightbox() {
		var modal = document.getElementById('aofaLightboxModal');
		if (modal) {
			modal.style.display = 'none';
		}
		document.body.style.overflow = '';
	}

	function changeAofaLightboxPhoto(dir) {
		var album = aofaAlbums[activeAlbumIdx];
		if (!album || !album.photos.length) return;
		activePhotoIdx = (activePhotoIdx + dir + album.photos.length) % album.photos.length;
		renderAofaLightboxPhoto();
	}

	function setAofaLightboxPhoto(pIndex) {
		activePhotoIdx = pIndex;
		renderAofaLightboxPhoto();
	}

	function decodeHtml(html) {
		var txt = document.createElement('textarea');
		txt.innerHTML = html;
		return txt.value;
	}

	function renderAofaLightboxPhoto() {
		var album = aofaAlbums[activeAlbumIdx];
		if (!album) return;
		var photo = album.photos[activePhotoIdx];

		document.getElementById('aofaLightboxTitle').innerText = decodeHtml(album.title);
		document.getElementById('aofaLightboxCounter').innerText = 'Photo ' + (activePhotoIdx + 1) + ' of ' + album.photos.length;
		document.getElementById('aofaLightboxImg').src = photo.url;
		
		// Caption handling
		var capText = photo.caption ? photo.caption : album.excerpt;
		document.getElementById('aofaLightboxCaption').innerText = decodeHtml(capText);

		// Hide navigation arrows if only 1 photo in album
		var prevBtn = document.getElementById('aofaLightboxPrevBtn');
		var nextBtn = document.getElementById('aofaLightboxNextBtn');
		if (album.photos.length <= 1) {
			if (prevBtn) prevBtn.style.display = 'none';
			if (nextBtn) nextBtn.style.display = 'none';
		} else {
			if (prevBtn) prevBtn.style.display = 'flex';
			if (nextBtn) nextBtn.style.display = 'flex';
		}

		// Thumbnails strip
		var thumbsContainer = document.getElementById('aofaLightboxThumbs');
		thumbsContainer.innerHTML = '';
		if (album.photos.length > 1) {
			thumbsContainer.style.display = 'flex';
			album.photos.forEach(function(pt, pIdx){
				var div = document.createElement('div');
				div.className = 'aofa-lightbox-thumb-item' + (pIdx === activePhotoIdx ? ' active' : '');
				div.onclick = function() { setAofaLightboxPhoto(pIdx); };
				div.innerHTML = '<img src="' + pt.url + '" alt=""/>';
				thumbsContainer.appendChild(div);
			});
		} else {
			thumbsContainer.style.display = 'none';
		}
	}

	document.addEventListener('keydown', function(e) {
		var modal = document.getElementById('aofaLightboxModal');
		if (modal && modal.style.display === 'flex') {
			if (e.key === 'Escape') closeAofaLightbox();
			if (e.key === 'ArrowLeft') changeAofaLightboxPhoto(-1);
			if (e.key === 'ArrowRight') changeAofaLightboxPhoto(1);
		}
	});
	</script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aofa_photo_gallery', 'aofa_render_photo_gallery_shortcode' );
add_filter( 'query_loop_block_query_vars', function( $query_vars, $block ) {
	$requested_type = $block->context['query']['postType'] ?? $block->parsed_block['attrs']['query']['postType'] ?? '';
	if ( ! empty( $requested_type ) ) {
		$query_vars['post_type']   = sanitize_text_field( $requested_type );
		$query_vars['post_status'] = 'publish';
		$query_vars['nopaging']    = false;
		unset( $query_vars['page_id'], $query_vars['paged'] );
	}

	if ( is_object( $block ) && isset( $block->context['query']['perPage'] ) && ! empty( $block->context['query']['perPage'] ) ) {
		$query_vars['posts_per_page'] = (int) $block->context['query']['perPage'];
	}

	return $query_vars;
}, 10, 2 );

/**
 * Register WP-CLI commands after WP-CLI has loaded.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'aofa', 'Aofa_CLI_Commands' );
}

// ── Activation / Deactivation Hooks ─────────────────────────────────────────

/**
 * Runs on plugin activation: flushes rewrite rules so CPT URLs work immediately.
 */
function aofa_core_activate(): void {
	Aofa_Post_Types::register();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'aofa_core_activate' );

/**
 * Runs on plugin deactivation: flushes rewrite rules to clean up CPT slugs.
 */
function aofa_core_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'aofa_core_deactivate' );
