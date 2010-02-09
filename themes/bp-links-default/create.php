<?php get_header() ?>

	<div id="content">
		<div class="padder">

			<h2><?php _e( 'Create a Link', 'buddypress-links' ) ?> &nbsp;<a class="button" href="<?php echo bp_get_root_domain() . '/' . BP_LINKS_SLUG . '/' ?>"><?php _e( 'Links Directory', 'buddypress-links' ) ?></a></h2>

			<div class="item-list-tabs no-ajax" id="link-create-tabs">
				<ul>
					<?php bp_links_dtheme_creation_tabs() ?>
				</ul>
			</div>

			<?php
				do_action( 'template_notices' ); // (error/success feedback)
				do_action( 'bp_before_link_creation_content' );
				require_once 'single/forms/details.php';
				do_action( 'bp_after_link_creation_content' );
			?>
		</div>
	</div>

<?php get_footer() ?>