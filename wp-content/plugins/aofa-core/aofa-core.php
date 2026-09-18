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
					<div class="aofa-gallery-img-wrapper" style="position:relative;">
						<img src="<?php echo esc_url( $album['cover'] ); ?>" alt="<?php echo esc_attr( $album['title'] ); ?>"/>
						<div style="position:absolute;bottom:10px;right:10px;background:rgba(0,43,73,0.85);color:#C5A059;padding:4px 10px;border-radius:4px;font-size:0.8rem;font-weight:700;">
							<span class="dashicons dashicons-images-alt2" style="vertical-align:middle;margin-right:4px;"></span>
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
			
			<button class="aofa-lightbox-arrow aofa-lightbox-prev" onclick="changeAofaLightboxPhoto(-1)">&#10094;</button>
			<button class="aofa-lightbox-arrow aofa-lightbox-next" onclick="changeAofaLightboxPhoto(1)">&#10095;</button>

			<div class="aofa-lightbox-body">
				<div class="aofa-lightbox-header-info">
					<h3 id="aofaLightboxTitle" style="color:#ffffff;margin:0 0 4px;font-size:1.3rem;"></h3>
					<span id="aofaLightboxCounter" style="color:#C5A059;font-size:0.9rem;font-weight:600;"></span>
				</div>

				<div class="aofa-lightbox-main-stage">
					<img id="aofaLightboxImg" src="" alt=""/>
				</div>

				<p id="aofaLightboxCaption" style="color:#CBD5E1;font-size:0.95rem;margin:12px 0 0;text-align:center;max-width:800px;margin-left:auto;margin-right:auto;"></p>
				
				<div id="aofaLightboxThumbs" class="aofa-lightbox-thumbs-strip"></div>
			</div>
		</div>
	</div>

	<style>
	.aofa-lightbox-modal {
		position: fixed;
		top: 0; left: 0; width: 100vw; height: 100vh;
		z-index: 99999;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.aofa-lightbox-backdrop {
		position: absolute;
		top: 0; left: 0; width: 100%; height: 100%;
		background: rgba(0, 25, 48, 0.92);
		backdrop-filter: blur(8px);
	}
	.aofa-lightbox-content {
		position: relative;
		z-index: 10;
		width: 92%;
		max-width: 1050px;
		max-height: 90vh;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	.aofa-lightbox-close {
		position: absolute;
		top: -45px; right: 0;
		background: transparent;
		color: #ffffff;
		border: none;
		font-size: 2.2rem;
		cursor: pointer;
		line-height: 1;
		transition: color 0.2s ease;
	}
	.aofa-lightbox-close:hover { color: #C5A059; }
	.aofa-lightbox-arrow {
		position: absolute;
		top: 50%; transform: translateY(-50%);
		background: rgba(0,43,73,0.8);
		color: #C5A059;
		border: 1px solid #C5A059;
		width: 50px; height: 50px;
		border-radius: 50%;
		font-size: 1.5rem;
		display: flex; align-items: center; justify-content: center;
		cursor: pointer;
		transition: all 0.3s ease;
		z-index: 20;
	}
	.aofa-lightbox-arrow:hover {
		background: #C5A059;
		color: #002B49;
	}
	.aofa-lightbox-prev { left: -25px; }
	.aofa-lightbox-next { right: -25px; }
	.aofa-lightbox-body {
		width: 100%;
		text-align: center;
	}
	.aofa-lightbox-main-stage {
		max-height: 60vh;
		display: flex;
		align-items: center;
		justify-content: center;
		margin: 15px 0;
	}
	.aofa-lightbox-main-stage img {
		max-width: 100%;
		max-height: 60vh;
		object-fit: contain;
		border-radius: 8px;
		box-shadow: 0 15px 35px rgba(0,0,0,0.5);
		border: 1px solid rgba(197,160,89,0.3);
	}
	.aofa-lightbox-thumbs-strip {
		display: flex;
		gap: 8px;
		justify-content: center;
		margin-top: 15px;
		overflow-x: auto;
		padding-bottom: 5px;
	}
	.aofa-lightbox-thumb-item {
		width: 60px; height: 60px;
		border-radius: 4px;
		overflow: hidden;
		opacity: 0.6;
		cursor: pointer;
		border: 2px solid transparent;
		transition: all 0.25s ease;
	}
	.aofa-lightbox-thumb-item.active,
	.aofa-lightbox-thumb-item:hover {
		opacity: 1;
		border-color: #C5A059;
	}
	.aofa-lightbox-thumb-item img {
		width: 100%; height: 100%; object-fit: cover;
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
		renderAofaLightboxPhoto();
		document.getElementById('aofaLightboxModal').style.display = 'flex';
		document.body.style.overflow = 'hidden';
	}

	function closeAofaLightbox() {
		document.getElementById('aofaLightboxModal').style.display = 'none';
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
		document.getElementById('aofaLightboxCaption').innerText = decodeHtml(album.excerpt);

		var thumbsContainer = document.getElementById('aofaLightboxThumbs');
		thumbsContainer.innerHTML = '';
		album.photos.forEach(function(pt, pIdx){
			var div = document.createElement('div');
			div.className = 'aofa-lightbox-thumb-item' + (pIdx === activePhotoIdx ? ' active' : '');
			div.onclick = function() { setAofaLightboxPhoto(pIdx); };
			div.innerHTML = '<img src="' + pt.url + '" alt=""/>';
			thumbsContainer.appendChild(div);
		});
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
