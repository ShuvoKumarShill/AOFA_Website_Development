<?php
/**
 * Custom archive template for AOFA Executive Committee CPT.
 * Renders the EC table shortcode within the Astra theme header/footer.
 *
 * @package AOFA_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div id="primary" class="content-area aofa-archive-wrap">
	<main id="main" class="site-main" role="main">
		<div class="ast-container">
			<div id="content" class="site-content">
				<div class="entry-content">
					<?php echo do_shortcode( '[aofa_ec_table]' ); ?>
				</div>
			</div>
		</div>
	</main>
</div>

<?php
get_footer();
