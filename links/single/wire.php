<?php get_header() ?>

	<div class="content-header">
	
	</div>

	<div id="content">	
		<?php do_action( 'template_notices' ) // (error/success feedback) ?>
	
		<?php if ( bp_has_links() ) : while ( bp_links() ) : bp_the_link(); ?>

			<?php do_action( 'bp_before_link_wire_content' ) ?>
	
			<div class="left-menu">
				<?php //load_template( get_stylesheet_directory() . '/links/single/menu.php' ) ?>
				<?php locate_template( array( 'links/single/menu.php' ), true ) ?>
			</div>

			<div class="main-column">
				<div class="inner-tube">
			
					<div id="link-name">
						<h1><a href="<?php bp_link_permalink() ?>"><?php bp_link_name() ?></a></h1>
						<p class="status"><?php bp_link_type() ?></p>
					</div>

					<div class="bp-widget">
						<?php if ( function_exists('bp_wire_get_post_list') ) : ?>
							
							<?php bp_wire_get_post_list( bp_get_link_id( false, false), __( 'Link Wire', 'buddypress-links' ), sprintf( __( 'There are no wire posts for %1$s', 'buddypress-links' ), bp_get_link_name() ), bp_link_is_visible(), true ) ?>
						
						<?php endif; ?>
					</div>
			
				</div>
			</div>
	
		<?php endwhile; endif; ?>

	</div>

<?php get_footer() ?>