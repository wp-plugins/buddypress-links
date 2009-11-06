<?php get_header() ?>

	<div class="content-header">
		<ul class="content-header-nav">
			<?php bp_links_header_tabs() ?>
		</ul>
	</div>

	<div id="content">
		
		<h2><?php bp_word_or_name( __( 'My Links', 'buddypress-links' ), __( '%1$s\'s Links', 'buddypress-links' ) ) ?> &raquo; <?php bp_links_filter_title() ?></h2>

		<?php do_action( 'bp_before_my_links_content' ) ?>
	
		<div class="left-menu">
			<?php bp_link_search_form() ?>
		</div>
	
		<div class="main-column">
			<?php do_action( 'template_notices' ) // (error/success feedback) ?>
		
			<?php //load_template( get_stylesheet_directory() . '/links/link-loop.php' )?>
			<?php locate_template( array( 'links/link-loop.php' ), true ) ?>
		</div>

		<?php do_action( 'bp_after_my_links_content' ) ?>

	</div>

<?php get_footer() ?>