<?php

function bp_links_admin_manage_categories() {

	if ( !current_user_can('manage_categories') ) {
		wp_die(__('You do not have sufficient permissions to edit link categories for this blog.'));
	}

	if ( isset( $_GET['category_id'] ) || isset( $_POST['category_id'] )) {
		require_once( 'category-edit.php');
	} else {
		require_once( 'category-list.php');
	}
}