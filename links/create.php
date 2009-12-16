<?php get_header() ?>

	<div class="content-header">
		<!-- just a place holder -->
	</div>

	<div id="content">
		<h2><?php _e( 'Create a Link', 'buddypress-links' ) ?></h2>
		<?php
			do_action( 'template_notices' ); // (error/success feedback)
			do_action( 'bp_before_link_creation_content' );
			require_once 'single/forms/details.php';
			do_action( 'bp_after_link_creation_content' );
		?>
	</div>

<?php get_footer() ?>