<?php get_header() ?>

	<div class="content-header">

	</div>
	
	<div id="content">

		<?php do_action( 'template_notices' ) // (error/success feedback) ?>

		<?php if ( bp_has_links() ) : while ( bp_links() ) : bp_the_link(); ?>

			<?php do_action( 'bp_before_link_content' ) ?>
	
			<div class="left-menu">
				<?php //load_template( get_stylesheet_directory() . '/links/single/menu.php' ) ?>
				<?php locate_template( array( 'links/single/menu.php' ), true ) ?>
			</div>

			<div class="main-column">
				<div class="inner-tube">

					<?php do_action( 'bp_before_link_name' ) ?>
			
					<div id="link-name">
						<h1><?php bp_link_category_name() ?>: <a href="<?php bp_link_url() ?>" title="<?php bp_link_name() ?>" target="_blank"><?php bp_link_name() ?></a></h1>
						<p class="status"><?php bp_link_type() ?></p>
					</div>
					
					<?php do_action( 'bp_after_link_name' ) ?>
		
					<?php if ( !bp_link_is_visible() ) : ?>
						
						<?php do_action( 'bp_before_link_status_message' ) ?>
						
						<div id="message" class="info">
							<p><?php bp_link_status_message() ?></p>
						</div>
						
						<?php do_action( 'bp_after_link_status_message' ) ?>
						
					<?php endif; ?>
		
					<?php do_action( 'bp_before_link_description' ) ?>

					<div class="bp-widget">
						<h4><?php _e( 'Description', 'buddypress-links' ); ?></h4>
						<p><span class="domain"><?php bp_link_url_domain() ?> --</span> <?php bp_link_description() ?></p>
					</div>
					
					<?php do_action( 'bp_after_link_description' ) ?>

					<?php if ( bp_link_is_visible() && bp_link_embed_is_enabled() ) : ?>
						<?php do_action( 'bp_before_link_embed' ) ?>

						<div class="bp-widget">
							<h4><?php _e( 'Gallery', 'buddypress-links' ); ?></h4>
							<p><?php bp_link_embed_tags() ?></p>
						</div>
					
						<?php do_action( 'bp_after_link_embed' ) ?>
					<?php endif; ?>
		
					<?php if ( bp_link_is_visible() && bp_link_is_wire_enabled() ) : ?>
						
						<?php if ( function_exists('bp_wire_get_post_list') ) : ?>
							
							<?php do_action( 'bp_before_link_wire_widget' ) ?>
							
							<?php bp_wire_get_post_list( bp_get_link_id(), __( 'Link Wire', 'buddypress-links' ), sprintf( __( 'There are no wire posts for %s', 'buddypress-links' ), bp_get_link_name() ), bp_link_is_visible(), true ) ?>
						
							<?php do_action( 'bp_after_link_wire_widget' ) ?>
							
						<?php endif; ?>
					
					<?php endif; ?>
		
				</div>
				
			</div>

			<?php do_action( 'bp_after_link_content' ) ?>

		<?php endwhile; else: ?>
			
			<div id="message" class="error">
				<p><?php _e("Sorry, the link does not exist.", "buddypress"); ?></p>
			</div>
			
		<?php endif;?>

	</div>

<?php get_footer() ?>