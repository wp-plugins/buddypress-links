<?php if ( bp_has_site_links( 'type=most-popular&per_page=10' ) ) : ?>
	
	<div class="pagination">
		
		<div class="pag-count" id="link-dir-count">
			<?php bp_site_links_pagination_count() ?>
		</div>

		<div class="pagination-links" id="link-dir-pag">
			<?php bp_site_links_pagination_links() ?>
		</div>
	
	</div>
	
	<?php do_action( 'bp_before_directory_links_list' ) ?>

	<?php bp_the_site_link_list() ?>

	<?php do_action( 'bp_after_directory_links_list' ) ?>
			
<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'There were no links found.', 'buddypress-links' ) ?></p>
	</div>

<?php endif; ?>

<?php bp_the_site_link_hidden_fields() ?>