<?php do_action( 'bp_before_my_links_loop' ) ?>

<div id="link-loop">
	
	<?php if ( bp_has_links() ) : ?>
		
		<div class="pagination">
			
			<div class="pag-count">
				<?php bp_link_pagination_count() ?>
			</div>
			
			<div class="pagination-links" id="pag">
				<?php bp_link_pagination() ?>
			</div>
		
		</div>
		
		<?php bp_link_list() ?>
	
	<?php else: ?>

		<?php if ( bp_link_show_no_links_message() ) : ?>

			<div id="message" class="info">
				<p><?php bp_word_or_name( __( "You haven't added any links yet.", 'buddypress-links' ), __( "%s hasn't added any links yet.", 'buddypress-links' ) ) ?></p>
			</div>

		<?php else: ?>

			<div id="message" class="error">
				<p><?php _e( "No matching links found.", 'buddypress-links' ) ?></p>
			</div>

		<?php endif; ?>

	<?php endif;?>
	
</div>

<?php do_action( 'bp_after_my_links_loop' ) ?>