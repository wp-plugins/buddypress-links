<?php
/**
 * BP Links admin list categories
 */

// don't load directly
if ( !defined('ABSPATH') ) {
	die('-1');
}

// handle deletions
if ( isset( $_POST['categories_admin_delete']) && isset( $_POST['allcategories'] ) ) {

	if ( !check_admin_referer('bp-links-categories-admin') ) {
		return false;
	}

	foreach ( $_POST['allcategories'] as $category_id ) {
		$category = new BP_Links_Category( $category_id );

		if ( $category->get_link_count($category_id) == 0 ) {
			if ( $category->delete() ) {
				$message = __( 'Categories deleted successfully', 'buddypress-links' );
				$message_type = 'updated';
			} else {
				$message = sprintf( '%s %s', __( 'There were errors when deleting categories.', 'buddypress-links' ), __( 'Please try again.', 'buddypress-links' ) );
				$message_type = 'error';
			}
		} else {
			// TODO move to a default category instead of throwing error?
			$message = __( 'Unable to delete a category because it is assigned to one or more links', 'buddypress-links' );
			$message_type = 'error';
			break;
		}
	}
}
?>

<?php if ( isset( $message ) ) { ?>
	<div id="message" class="<?php echo $message_type ?> fade">
		<p><?php echo $message ?></p>
	</div>
<?php } ?>


<div class="wrap nosubsub" style="position: relative">
	<div id="icon-link-manager" class="icon32"><br /></div>
	<h2><?php _e( 'Manage BP Link Categories', 'buddypress-links' ) ?></h2>

	<form id="search-form" method="post" action="">
		<p class="search-box">
			<label class="screen-reader-text" for="link-category-search-input">Search Categories:</label>
			<input type="text" id="link-category-search-input" value="<?php echo attribute_escape( stripslashes( $_REQUEST['s'] ) ); ?>" name="s" />
			<input id="submit" class="button" type="submit" value="<?php _e( 'Search Categories', 'buddypress-links' ) ?>" />
		</p>
	</form>

	<?php if ( bp_has_site_link_categories( 'type=recently-active&per_page=10' ) ) : ?>
		<form id="bp-category-admin-list" method="post" action="">
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php bp_site_link_categories_pagination_count() ?> <?php bp_site_link_categories_pagination_links() ?>
				</div>
				<div class="alignleft">
					<input class="button-secondary delete" type="submit" name="categories_admin_delete" value="<?php _e( 'Delete', 'buddypress-links' ) ?>" onclick="if ( !confirm('<?php _e( 'Are you sure?', 'buddypress-links' ) ?>') ) return false"/>
					<?php wp_nonce_field('bp-links-categories-admin') ?>
					<br class="clear"/>
				</div>
			</div>

			<br class="clear"/>

			<?php if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) { ?>
				<p><?php echo sprintf( '%1$s &quot;%2$s&quot;', __( 'Categories matching:', 'buddypress-links' ), $_REQUEST['s'] ) ?></p>
			<?php } ?>


			<table class="widefat" cellspacing="3" cellpadding="3">
				<thead>
					<tr>
						<th class="check-column" scope="col">
							<input id="category_check_all" type="checkbox" value="0" name="category_check_all" onclick="if ( jQuery(this).attr('checked') ) { jQuery('#category-list input[@type=checkbox]').attr('checked', 'checked'); } else { jQuery('#category-list input[@type=checkbox]').attr('checked', ''); }" />
						</th>
						<th scope="col">
								ID
						</th>
						<th scope="col">
								<?php _e( 'Name', 'buddypress-links' ) ?>
						</th>
						<th scope="col">
								<?php _e( 'Description', 'buddypress-links' ) ?>
						</th>
						<th scope="col">
								<?php _e( 'Slug', 'buddypress-links' ) ?>
						</th>
						<th scope="col">
								<?php _e( 'Priority', 'buddypress-links' ) ?>
						</th>
						<th scope="col">
								<?php _e( 'Links', 'buddypress-links' ) ?>
						</th>
						<th scope="col">
								<?php _e( 'Created', 'buddypress-links' ) ?>
						</th>
						<th scope="col">
								<?php _e( 'Updated', 'buddypress-links' ) ?>
						</th>
						<th scope="col">
						</th>
					</tr>
				</thead>
				<tbody id="category-list" class="list:categories category-list">
				<?php $counter = 0 ?>
				<?php while ( bp_site_link_categories() ) : bp_the_site_link_categories_category(); ?>
					<tr<?php if ( 1 == $counter % 2 ) { ?> class="alternate"<?php }?>>
						<th class="check-column" scope="row">
							<input id="category_<?php bp_the_site_link_categories_category_id() ?>" type="checkbox" value="<?php bp_the_site_link_categories_category_id() ?>" name="allcategories[<?php bp_the_site_link_categories_category_id() ?>]" />
						</th>
						<td><?php bp_the_site_link_categories_category_id() ?></td>
						<td><?php bp_the_site_link_categories_category_name() ?></td>
						<td><?php bp_the_site_link_categories_category_description() ?></td>
						<td><?php bp_the_site_link_categories_category_slug() ?></td>
						<td><?php bp_the_site_link_categories_category_priority() ?></td>
						<td><?php bp_the_site_link_categories_category_link_count() ?></td>
						<td><?php bp_the_site_link_categories_category_date_created() ?></td>
						<td><?php bp_the_site_link_categories_category_date_updated() ?></td>
						<td><a href="?page=<?php echo BP_LINKS_PLUGIN_NAME ?>/admin/category-manager.php&category_id=<?php bp_the_site_link_categories_category_id() ?>"><?php _e( 'Edit', 'buddypress-links') ?></a></td>
					</tr>
					<?php $counter++ ?>
				<?php endwhile; ?>
				</tbody>
			</table>

	<?php else: ?>

		<div id="message" class="info">
			<p><?php _e( 'No categories found.', 'buddypress-links' ) ?></p>
		</div>

	<?php endif; ?>

	<?php bp_the_site_link_categories_hidden_fields() ?>
	</form>

	<form action="admin.php" method="get">
		<p class="submit">
			<input type="hidden" name="page" value="<?php echo BP_LINKS_PLUGIN_NAME ?>/admin/category-manager.php" />
			<input type="hidden" name="category_id" value="" />
			<input type="submit" class="button" value="New Category" />
		</p>
	</form>
</div>