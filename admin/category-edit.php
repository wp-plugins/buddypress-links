<?php
/**
 * BP Links admin list categories
 */

// don't load directly
if ( !defined('ABSPATH') ) {
	die('-1');
}

// error handling
$error = false;

// input value defaults
$category_id = null;
$category_name = null;
$category_description = null;
$category_priority = null;

//
// Handle create/update
//
if ( isset($_POST['category_id']) ) {

	if ( is_numeric( $_POST['category_id'] ) ) {
		// edit the category
		$category_id = (int) $_POST['category_id'];
		$category = new BP_Links_Category( $category_id );
	} else {
		// new category
		$category_id = null;
		$category = new BP_Links_Category();
	}

	// new values
	if ( isset( $_POST['name'] ) ) {
		$category_name = $_POST['name'];

		if ( strlen( $category_name ) >= 3 && strlen( $category_name ) <= 50) {
			if ( empty($category->slug) && $category->check_slug_raw( $category_name ) ) {
				$error = true;
				$message = __( 'Link category slug from this name already exists.', 'buddypress-links' ) . ' ' . __( 'Please try again.', 'buddypress-links' );
				$message_type = 'error';
			} else {
				$category->name = $category_name;
			}
		} else {
			$error = true;
			$message = sprintf( __( 'Link category name must be %1$d to %2$d characters in length.', 'buddypress-links' ), 3, 50 ) . ' ' . __( 'Please try again.', 'buddypress-links' );
			$message_type = 'error';
		}
	} else {
		$error = true;
		$message = __( 'Link category name is required.', 'buddypress-links' ) . ' ' . __( 'Please try again.', 'buddypress-links' );
		$message_type = 'error';
	}

	if ( isset( $_POST['description'] ) ) {
		$category_description = $_POST['description'];

		if ( empty( $category_description ) ) {
			$category->description = null;
		} else {
			if ( strlen( $category_description ) >= 5 && strlen( $category_description ) <= 250 ) {
				$category->description = $category_description;
			} else {
				$error = true;
				$message = sprintf( __( 'Link category description must be %1$d to %2$d characters in length.', 'buddypress-links' ), 5, 250 ) . ' ' . __( 'Please try again.', 'buddypress-links' );
				$message_type = 'error';
			}
		}
	}

	if ( isset( $_POST['priority'] ) ) {
		$category_priority = (int) $_POST['priority'];

		if ( $category_priority >= 1 && $category_priority <= 100 ) {
			$category->priority = $category_priority;
		} else {
			$error = true;
			$message = sprintf( __( 'Link category priority must be a number from %1$d to %2$d.', 'buddypress-links' ), 1, 100 ) . ' ' . __( 'Please try again.', 'buddypress-links' );
			$message_type = 'error';
		}
	} else {
		$error = true;
		$message = __( 'Link category priority is required.', 'buddypress-links' ) . ' ' . __( 'Please try again.', 'buddypress-links' );
		$message_type = 'error';
	}

	// try to save
	if ( false === $error ) {
		if ( $category->save() ) {
			$message = sprintf(
				'%1$s <a href="?page=buddypress-links/admin/category-manager.php">%2$s</a> %3$s <a href="?page=buddypress-links/admin/category-manager.php&amp;category_id=">%4$s</a>',
				__( 'Link category saved!', 'buddypress-links' ), // arg 1
				__( 'Return to list', 'buddypress-links' ), // arg 2
				__( 'or', 'buddypress-links' ), // arg 3
				__( 'Create new category', 'buddypress-links' ) // arg 4
			);
			$message_type = 'updated';
		} else {
			$message = __( 'There were errors when saving the link category.', 'buddypress-links' ) . ' ' . __( 'Please try again.', 'buddypress-links' );
			$message_type = 'error';
		}
	}

} else {

	if ( is_numeric($_GET['category_id']) ) {
		// edit the category
		$category_id = (int) $_GET['category_id'];
		$category = new BP_Links_Category( $category_id );
	} else {
		// new category
		$category_id = null;
		$category = new BP_Links_Category();
	}

	// defaults for new category
	$category_name = $category->name;
	$category_description = $category->description;
	$category_priority = $category->priority;
}


//
// Display Page
//

if ( $category_id ) {
	$heading_text = __( 'Edit BP Link Category', 'buddypress-links' );
	$submit_text = __( 'Update Category', 'buddypress-links' );
	$action = 'update';
	$nonce_action = 'update-link-category_' . $category_id;
} else {
	$heading_text = __( 'New BP Link Category', 'buddypress-links' );
	$submit_text = __( 'Add Category', 'buddypress-links' );
	$action = 'create';
	$nonce_action = 'add-link-category';
}
?>

<div class="wrap nosubsub">
	<div id="icon-link-manager" class="icon32"><br /></div>
	<h2><?php echo $heading_text ?></h2>

<?php echo $heading ?>

<?php if ( isset( $message ) ) { ?>
	<div id="message" class="<?php echo $message_type ?> fade">
		<p><?php echo $message ?></p>
	</div>
<?php } ?>

<form name="bp_links_category_form" id="bp_links_category_form" method="post" action="?page=<?php echo BP_LINKS_PLUGIN_NAME ?>/admin/category-manager.php" class="validate">
<?php
	do_action('bp_links_admin_edit_category_form_before', $category);
	wp_original_referer_field(true, 'previous');
	wp_nonce_field($nonce_action);
?>
	<input type="hidden" name="action" value="<?php echo esc_attr($action) ?>" />
	<input type="hidden" name="category_id" value="<?php echo esc_attr($category_id) ?>" />
	<table class="form-table">
		<tr class="form-field form-required">
			<th scope="row" valign="top"><label for="name"><?php _e( 'Link Category Name', 'buddypress-links' ) ?></label></th>
			<td><input name="name" id="name" type="text" value="<?php echo esc_attr($category_name); ?>" size="40" aria-required="true" /></td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="description"><?php _e( 'Description (optional)', 'buddypress-links' ) ?></label></th>
			<td><textarea name="description" id="description" rows="5" cols="50" style="width: 97%;"><?php echo $category_description; ?></textarea></td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="priority"><?php _e( 'Priority (optional)', 'buddypress-links' ) ?></label></th>
			<td><input name="priority" id="priority" type="text" value="<?php echo esc_attr($category_priority); ?>" size="5" aria-required="true" style="width: 50px;" /> (1 <?php _e( 'to','buddypress-links' ) ?> 100)</td>
		</tr>
		<?php do_action('bp_links_admin_edit_category_form_fields', $category); ?>
	</table>
	<p class="submit">
		<input type="submit" class="button-primary" name="submit" value="<?php echo esc_attr($submit_text) ?>" />
	</p>
<?php do_action('bp_links_admin_edit_category_form_after', $category); ?>
</form>
</div>

<?php include('admin-footer.php'); ?>