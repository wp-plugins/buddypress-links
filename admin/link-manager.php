<?php

function bp_links_admin_manage_links() {
	
	if ( isset( $_POST['links_admin_delete']) && isset( $_POST['alllinks'] ) ) {
		if ( !check_admin_referer('bp-links-admin') )
			return false;
		
		$errors = false;
		foreach ( $_POST['alllinks'] as $link_id ) {
			$link = new BP_Links_Link( $link_id );
			if ( !$link->delete() ) {
				$errors = true;
			}
		}
		
		if ( $errors ) {
			$message = __( 'There were errors when deleting links.', 'buddypress-links' ) . ' ' . __( 'Please Try Again.', 'buddypress-links' );
			$type = 'error';
		} else {
			$message = __( 'Links deleted successfully', 'buddypress-links' );
			$type = 'updated';
		}
	}
?>
	<?php if ( isset( $message ) ) { ?>
		<div id="message" class="<?php echo $type ?> fade">
			<p><?php echo $message ?></p>
		</div>
	<?php } ?>

	<div class="wrap" style="position: relative">
		<h2><?php _e( 'Links', 'buddypress-links' ) ?></h2>
	
		<form id="wpmu-search" method="post" action="">
			<input type="text" size="17" value="<?php echo attribute_escape( stripslashes( $_REQUEST['s'] ) ); ?>" name="s" />
			<input id="post-query-submit" class="button" type="submit" value="<?php _e( 'Search Links', 'buddypress-links' ) ?>" />
		</form>
		
		<?php if ( bp_has_site_links( 'type=recently-active&per_page=10' ) ) : ?>
			<form id="bp-link-admin-list" method="post" action="">
				<div class="tablenav">
					<div class="tablenav-pages">
						<?php bp_site_links_pagination_count() ?> <?php bp_site_links_pagination_links() ?>
					</div>
					<div class="alignleft">
						<input class="button-secondary delete" type="submit" name="links_admin_delete" value="<?php _e( 'Delete', 'buddypress-links' ) ?>" onclick="if ( !confirm('<?php _e( 'Are you sure?', 'buddypress-links' ) ?>') ) return false"/>
						<?php wp_nonce_field('bp-links-admin') ?>
						<br class="clear"/>
					</div>
				</div>
				
				<br class="clear"/>
				
				<?php if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) { ?>
					<p><?php echo sprintf( __( 'Links matching: "%s"', 'buddypress-links' ), $_REQUEST['s'] ) ?></p>
				<?php } ?>


				<table class="widefat" cellspacing="3" cellpadding="3">
					<thead>
						<tr>
							<th class="check-column" scope="col">
								<input id="link_check_all" type="checkbox" value="0" name="link_check_all" onclick="if ( jQuery(this).attr('checked') ) { jQuery('#link-list input[@type=checkbox]').attr('checked', 'checked'); } else { jQuery('#link-list input[@type=checkbox]').attr('checked', ''); }" />
							</th>
							<th scope="col">
							</th>
							<th scope="col">
									ID
							</th>
							<th scope="col">
									<?php _e( 'Details', 'buddypress-links' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Category', 'buddypress-links' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Type', 'buddypress-links' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Wire Posts', 'buddypress-links' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Owner', 'buddypress-links' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Created', 'buddypress-links' ) ?>
							</th>
							<th scope="col">
									<?php _e( 'Last Active', 'buddypress-links' ) ?>
							</th>
							<th scope="col">
							</th>
						</tr>
					</thead>
					<tbody id="link-list" class="list:links link-list">
					<?php $counter = 0 ?>
					<?php while ( bp_site_links() ) : bp_the_site_link(); ?>
						<tr<?php if ( 1 == $counter % 2 ) { ?> class="alternate"<?php }?>>
							<th class="check-column" scope="row">
								<input id="link_<?php bp_the_site_link_id() ?>" type="checkbox" value="<?php bp_the_site_link_id() ?>" name="alllinks[<?php bp_the_site_link_id() ?>]" />
							</th>
							<td><?php bp_the_site_link_avatar_mini() ?></td>
							<td><?php bp_the_site_link_id() ?></td>
							<td>
								<a href="<?php bp_the_site_link_permalink() ?>"><?php bp_the_site_link_name() ?></a>
								<?php bp_the_site_link_description_excerpt() ?>
							</td>
							<td><?php bp_the_site_link_category_name() ?></td>
							<td><?php bp_the_site_link_type() ?></td>
							<td><a href="<?php bp_the_site_link_permalink() ?>/wire"><?php bp_the_site_link_wire_count() ?></a></td>
							<td align="center"><?php bp_the_site_link_user_avatar_mini() ?><?php bp_the_site_link_userlink() ?></td>
							<td><?php bp_the_site_link_date_created() ?></td>
							<td><?php bp_the_site_link_last_active() ?></td>
							<td><a href="<?php bp_the_site_link_permalink() ?>/admin"><?php _e( 'Edit', 'buddypress-links') ?></a></td>
						</tr>
						<?php $counter++ ?>
					<?php endwhile; ?>
					</tbody>
				</table>	

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e( 'No links found.', 'buddypress-links' ) ?></p>
			</div>

		<?php endif; ?>

		<?php bp_the_site_link_hidden_fields() ?>
		</form>
	</div>
<?php 
}
?>