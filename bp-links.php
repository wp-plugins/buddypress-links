<?php

require ( BP_LINKS_PLUGIN_DIR . '/bp-links-embed.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-classes.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-ajax.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-templatetags.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-widgets.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-filters.php' );

function bp_links_install() {
	global $wpdb, $bp;
	
	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	
	$sql[] = "CREATE TABLE `{$bp->links->table_name}` (
				`id` bigint unsigned NOT NULL auto_increment,
				`user_id` bigint unsigned NOT NULL,
				`category_id` tinyint NOT NULL,
				`url` varchar(255) NOT NULL default '',
				`url_hash` varchar(32) NOT NULL,
				`target` varchar(25) default NULL,
				`rel` varchar(25) default NULL,
				`slug` varchar(255) NOT NULL,
				`name` varchar(255) NOT NULL,
				`description` text NOT NULL,
				`status` tinyint(1) NOT NULL default '1',
				`enable_wire` tinyint(1) NOT NULL default '1',
				`vote_count` smallint NOT NULL default '0',
				`vote_total` smallint NOT NULL default '0',
				`popularity` mediumint UNSIGNED NOT NULL default '0',
				`embed_service` char(32) default null,
				`embed_status` tinyint(1) default '0',
				`embed_data` text,
				`date_created` datetime NOT NULL,
				`date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			PRIMARY KEY  (`id`),
			KEY `user_id` (`user_id`),
			KEY `category_id` (`category_id`),
			KEY `url_hash` (`url_hash`),
			KEY `slug` (`slug`),
			KEY `name` (`name`(20)),
			KEY `status` (`status`),
			KEY `vote_count` (`vote_count`),
			KEY `vote_total` (`vote_total`),
			KEY `popularity` (`popularity`),
			KEY `date_created` (`date_created`),
			KEY `date_updated` (`date_updated`)
			) {$charset_collate};";

	$sql[] = "CREATE TABLE `{$bp->links->table_name_categories}` (
				`id` tinyint(4) NOT NULL auto_increment,
				`slug` varchar(50) NOT NULL,
				`name` varchar(50) NOT NULL,
				`description` varchar(255) default NULL,
				`priority` smallint NOT NULL,
				`date_created` datetime NOT NULL,
				`date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			PRIMARY KEY  (`id`),
			KEY `slug` (`slug`),
			KEY `priority` (`priority`)
			) {$charset_collate};";

	// if initial install, add default categories
	if ( !get_site_option( 'bp-links-db-version' ) ) {
		$sql[] = "INSERT INTO `{$bp->links->table_name_categories}`
					( slug, name, description, priority, date_created )
					VALUES  ( 'news', 'News', NULL, 10, NOW() ),
							( 'humor', 'Humor', NULL, 20, NOW() ),
							( 'other', 'Other', NULL, 30, NOW() );";
	}

	$sql[] = "CREATE TABLE `{$bp->links->table_name_votes}` (
				`link_id` bigint unsigned NOT NULL,
				`user_id` bigint unsigned NOT NULL,
				`vote` tinyint(1) NOT NULL,
				`date_created` datetime NOT NULL,
				`date_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			PRIMARY KEY  (`user_id`,`link_id`),
			KEY `user_id` (`user_id`),
			KEY `link_id` (`link_id`),
			KEY `date_created` (`date_created`)
			) {$charset_collate};";

	$sql[] = "CREATE TABLE `{$bp->links->table_name_linkmeta}` (
				`id` bigint NOT NULL auto_increment,
				`link_id` bigint unsigned NOT NULL,
				`meta_key` varchar(255) default NULL,
				`meta_value` longtext,
			PRIMARY KEY  (`id`),
			KEY `meta_key` (`meta_key`),
			KEY `link_id` (`link_id`)
			) {$charset_collate};";
	
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);

	// install wire
	if ( function_exists('bp_wire_install') )
		bp_links_wire_install();

	// update site version
	update_site_option( 'bp-links-db-version', BP_LINKS_DB_VERSION );
}

function bp_links_wire_install() {
	global $wpdb, $bp;
	
	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	
	$sql[] = "CREATE TABLE `{$bp->links->table_name_wire}` (
				`id` bigint NOT NULL auto_increment,
				`item_id` bigint unsigned NOT NULL,
				`user_id` bigint unsigned NOT NULL,
				`content` longtext NOT NULL,
				`date_posted` datetime NOT NULL,
			PRIMARY KEY  (`id`),
			KEY `item_id` (`item_id`),
			KEY `user_id` (`user_id`)
			) {$charset_collate};";

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);
}

function bp_links_add_cron_schedules() {
	return array(
		'5_min' => array( 'interval' => 5*60, 'display' => sprintf( __( 'Every %1$d minutes', 'buddypress-links' ), 5 ) ),
		'10_min' => array( 'interval' => 10*60, 'display' => sprintf( __( 'Every %1$d minutes', 'buddypress-links' ), 10 ) ),
		'15_min' => array( 'interval' => 15*60, 'display' => sprintf( __( 'Every %1$d minutes', 'buddypress-links' ), 15 ) ),
		'20_min' => array( 'interval' => 20*60, 'display' => sprintf( __( 'Every %1$d minutes', 'buddypress-links' ), 20 ) ),
		'30_min' => array( 'interval' => 30*60, 'display' => sprintf( __( 'Every %1$d minutes', 'buddypress-links' ), 30 ) )
	);
}
add_filter( 'cron_schedules', 'bp_links_add_cron_schedules' );

function bp_links_load_textdomain() {

	// try to get locale
	$locale = apply_filters( 'bp_links_load_textdomain_get_locale', get_locale() );

	// if we found a locale, try to load .mo file
	if ( !empty( $locale ) ) {
		// default .mo file path
		$mofile_default = sprintf( '%s/languages/%s-%s.mo', BP_LINKS_PLUGIN_DIR, BP_LINKS_PLUGIN_NAME, $locale );
		// final filtered file path
		$mofile = apply_filters( 'bp_links_load_textdomain_mofile', $mofile_default );
		// make sure file exists, and load it
		if ( file_exists( $mofile ) ) {
			load_textdomain( BP_LINKS_PLUGIN_NAME, $mofile );
		}
	}
}
add_action ( 'plugins_loaded', 'bp_links_load_textdomain', 6 );

function bp_links_setup_globals() {
	global $bp, $wpdb;

	/* For internal identification */
	$bp->links->id = 'links';

	$bp->links->table_name = $wpdb->base_prefix . 'bp_links';
	$bp->links->table_name_categories = $wpdb->base_prefix . 'bp_links_categories';
	$bp->links->table_name_votes = $wpdb->base_prefix . 'bp_links_votes';
	$bp->links->table_name_linkmeta = $wpdb->base_prefix . 'bp_links_linkmeta';
	$bp->links->format_notification_function = 'bp_links_format_notifications';
	$bp->links->slug = BP_LINKS_SLUG;

	/* Register this in the active components array */
	$bp->active_components[$bp->links->slug] = $bp->links->id;

	if ( function_exists('bp_wire_install') )
		$bp->links->table_name_wire = $wpdb->base_prefix . 'bp_links_wire';
	
	$bp->links->forbidden_names = apply_filters( 'bp_links_forbidden_names', array( 'my-links', 'link-finder', 'create', 'invites', 'delete', 'add', 'admin', 'most-popular', 'most-votes', 'high-votes', 'recently-active', 'newest', 'all', 'submit', 'feed' ) );

}
add_action( 'plugins_loaded', 'bp_links_setup_globals', 6 );
add_action( 'admin_menu', 'bp_links_setup_globals', 6 );

function bp_links_setup_root_component() {
	/* Register 'links' as a root component */
	bp_core_add_root_component( BP_LINKS_SLUG );
}
add_action( 'plugins_loaded', 'bp_links_setup_root_component', 2 );

function bp_links_check_installed() {
	global $wpdb, $bp;

	require ( BP_LINKS_PLUGIN_DIR . '/admin/link-manager.php' );
	require ( BP_LINKS_PLUGIN_DIR . '/admin/category-manager.php' );

	/* Need to check db tables exist, activate hook no-worky in mu-plugins folder. */
	if ( get_site_option('bp-links-db-version') < BP_LINKS_DB_VERSION )
		bp_links_install();

	// set up cron for popularity recalc
	if ( !wp_next_scheduled('bp_links_cron_popularity') )
		wp_schedule_event( time(), '15_min', 'bp_links_cron_popularity' );
}
add_action( 'admin_menu', 'bp_links_check_installed' );

function bp_links_add_admin_menu() {
	global $wpdb, $bp, $menu;

	if ( !is_site_admin() )
		return false;

	// Add the administration tab under the "Site Admin" tab for site administrators
	add_object_page(
		__( 'BP Links', 'buddypress-links' ),
		__( 'BP Links', 'buddypress-links' ),
		1,
		BP_LINKS_PLUGIN_NAME . '/admin/link-manager.php',
		'bp_links_admin_manage_links'
	);

	add_submenu_page( BP_LINKS_PLUGIN_NAME . '/admin/link-manager.php', __( 'Manage Links', 'buddypress-links'), __( 'Manage Links', 'buddypress-links' ), 1, BP_LINKS_PLUGIN_NAME . '/admin/link-manager.php', 'bp_links_admin_manage_links' );
	add_submenu_page( BP_LINKS_PLUGIN_NAME . '/admin/link-manager.php', __( 'Manage Categories', 'buddypress-links'), __( 'Manage Categories', 'buddypress-links' ), 1, BP_LINKS_PLUGIN_NAME . '/admin/category-manager.php', 'bp_links_admin_manage_categories' );
}
add_action( 'admin_menu', 'bp_links_add_admin_menu' );

function bp_links_setup_nav() {
	global $bp, $current_blog, $link_obj;

	if ( $link_id = BP_Links_Link::link_exists($bp->current_action) ) {
		
		/* This is a single link page. */
		$bp->is_single_item = true;
		$bp->links->current_link = &new BP_Links_Link( $link_id );

		/* Using "item" not "link" for generic support in other components. */
		if ( is_site_admin() ) {
			$bp->is_item_admin = 1;
		} else {
			$bp->is_item_admin = ( $bp->loggedin_user->id == $bp->links->current_link->user_id ) ? true : false;
		}
		
		/* Pre 1.1 backwards compatibility - use $bp->links->current_link instead */
		$link_obj = &$bp->links->current_link;

		/* Should this link be visible to the logged in user? */
		$bp->links->current_link->is_link_visible_to_member = bp_links_is_link_visibile( $bp->links->current_link, $bp->loggedin_user->id );
	}

	/* Add 'Links' to the main navigation */
	bp_core_new_nav_item( array( 'name' => __('Links', 'buddypress-links'), 'slug' => $bp->links->slug, 'position' => 75, 'screen_function' => 'bp_links_screen_my_links', 'default_subnav_slug' => 'my-links', 'item_css_id' => $bp->links->id ) );

	$links_link = $bp->loggedin_user->domain . $bp->links->slug . '/';
	
	/* Add the subnav items to the links nav item */
	bp_core_new_subnav_item( array( 'name' => __( 'My Links', 'buddypress-links' ), 'slug' => 'my-links', 'parent_url' => $links_link, 'parent_slug' => $bp->links->slug, 'screen_function' => 'bp_links_screen_my_links', 'position' => 10, 'item_css_id' => 'links-my-links' ) );
	bp_core_new_subnav_item( array( 'name' => __( 'Create a Link', 'buddypress-links' ), 'slug' => 'create', 'parent_url' => $links_link, 'parent_slug' => $bp->links->slug, 'screen_function' => 'bp_links_screen_create_link', 'position' => 20, 'user_has_access' => bp_is_home() ) );

	if ( $bp->current_component == $bp->links->slug ) {
		
		if ( bp_is_home() && !$bp->is_single_item ) {
			
			$bp->bp_options_title = __( 'My Links', 'buddypress-links' );
			
		} else if ( !bp_is_home() && !$bp->is_single_item ) {

			$bp->bp_options_avatar = bp_links_fetch_avatar( array( 'type' => 'thumb' ), $bp->links->current_link );
			$bp->bp_options_title = $bp->links->current_link->name;
			
		} else if ( $bp->is_single_item ) {
			// We are viewing a single link, so set up the
			// link navigation menu using the $bp->links->current_link global.
			
			/* When in a single link, the first action is bumped down one because of the
			   link name, so we need to adjust this and set the link name to current_item. */
			$bp->current_item = $bp->current_action;
			$bp->current_action = $bp->action_variables[0];
			array_shift($bp->action_variables);
									
			$bp->bp_options_title = $bp->links->current_link->name;

			$bp->bp_options_avatar = bp_links_fetch_avatar( array( 'type' => 'thumb' ), $bp->links->current_link );
			
			$link_link = $bp->root_domain . '/' . $bp->links->slug . '/' . $bp->links->current_link->slug . '/';
			
			// If this is a friends only or hidden link, does the user have access?
			if ( $bp->links->current_link->is_link_visible_to_member ) {
				$bp->links->current_link->user_has_access = true;
			} else {
				$bp->links->current_link->user_has_access = false;
			}

			/* Reset the existing subnav items */
			bp_core_reset_subnav_items($bp->links->slug);
			
			/* Add a new default subnav item for when the links nav is selected. */
			bp_core_new_nav_default( array( 'parent_slug' => $bp->links->slug, 'screen_function' => 'bp_links_screen_link_home', 'subnav_slug' => 'home' ) );
			
			/* Add the "Home" subnav item, as this will always be present */
			bp_core_new_subnav_item( array( 'name' => __( 'Home', 'buddypress-links' ), 'slug' => 'home', 'parent_url' => $link_link, 'parent_slug' => $bp->links->slug, 'screen_function' => 'bp_links_screen_link_home', 'position' => 10, 'item_css_id' => 'link-home' ) );
			
			/* Add the "Wire" subnav item, as this will always be present */
			if ( $bp->links->current_link->enable_wire && function_exists('bp_wire_install') ) {
				bp_core_new_subnav_item( array( 'name' => __( 'Wire', 'buddypress-links' ), 'slug' => BP_WIRE_SLUG, 'parent_url' => $link_link, 'parent_slug' => $bp->links->slug, 'screen_function' => 'bp_links_screen_link_wire', 'position' => 30, 'user_has_access' => $bp->links->current_link->user_has_access, 'item_css_id' => 'link-wire'  ) );
			}

			/* If the user is a link mod or more, then show the link admin nav item */
			if ( $bp->is_item_admin ) {
				bp_core_new_subnav_item( array( 'name' => __( 'Admin', 'buddypress-links' ), 'slug' => 'admin', 'parent_url' => $link_link, 'parent_slug' => $bp->links->slug, 'screen_function' => 'bp_links_screen_link_admin', 'position' => 20, 'user_has_access' => ( $bp->is_item_admin + (int)$bp->is_item_mod ), 'item_css_id' => 'link-admin' ) );
			}

		}
	}
	
	do_action( 'bp_links_setup_nav', $bp->links->current_link->user_has_access );
}
add_action( 'plugins_loaded', 'bp_links_setup_nav', 99 );
add_action( 'admin_menu', 'bp_links_setup_nav' );

function bp_links_directory_links_setup() {
	global $bp;

	$filter_nav_tabs = array( null, 'most-popular', 'most-votes', 'high-votes', 'recently-active', 'newest', 'all' );

	if ( $bp->current_component == $bp->links->slug && in_array( $bp->current_action, $filter_nav_tabs ) )  {

		$bp->is_directory = true;

		do_action( 'bp_links_directory_links_setup' );
		bp_core_load_template( apply_filters( 'bp_links_template_directory_links', 'links/directory/index' ) );
	}
}
add_action( 'wp', 'bp_links_directory_links_setup', 2 );

function bp_links_setup_adminbar_menu() {
	global $bp;

	if ( !$bp->links->current_link )
		return false;

	/* Don't show this menu to non site admins or if you're viewing your own profile */
	if ( !is_site_admin() )
		return false;
	?>
	<li id="bp-adminbar-adminoptions-menu">
		<a href=""><?php _e( 'Admin Options', 'buddypress-links' ) ?></a>

		<ul>
			<li><a class="confirm" href="<?php echo wp_nonce_url( bp_get_link_permalink( $bp->links->current_link ) . '/admin/delete-link/', 'bp_links_delete_link' ) ?>&amp;delete-link-button=1&amp;delete-link-understand=1"><?php _e( 'Delete Link', 'buddypress-links' ) ?></a></li>

			<?php do_action( 'bp_links_adminbar_menu_items' ) ?>
		</ul>
	</li>
	<?php
}
add_action( 'bp_adminbar_menus', 'bp_links_setup_adminbar_menu', 20 );

function bp_links_header_nav_setup() {
	global $bp;

	$selected = ( bp_is_page( BP_LINKS_SLUG ) ) ? ' class="selected"' : '';
	$title = __( 'Links', 'buddypress-links' );
	
	echo sprintf('<li%s><a href="%s/%s" title="%s">%s</a></li>', $selected, get_option('home'), BP_LINKS_SLUG, $title, $title );
}
add_action( 'bp_nav_items', 'bp_links_header_nav_setup', 99);

function bp_links_adminbar_random_menu_setup() {
	global $bp;
	echo sprintf( '<li><a href="%s/%s/?random-link">%s</a></li>', $bp->root_domain, $bp->links->slug, __( 'Random Link', 'buddypress-links' ) );
}
add_action( 'bp_adminbar_random_menu', 'bp_links_adminbar_random_menu_setup' );

function bp_links_add_meta() {
	global $bp;

	if ( $bp->is_single_item ) {
		printf(
			'<meta name="description" content="%s" />' . PHP_EOL,
			apply_filters( 'bp_links_add_meta_description_single_item', $bp->links->current_link->description )
		);
	}
}
add_action( 'wp_head', 'bp_links_add_meta' );

function bp_links_add_js() {
	global $bp;

	if ( $bp->current_component == $bp->links->slug ) {
		wp_enqueue_script( 'bp-links-ajax', get_stylesheet_directory_uri() . '/links/_inc/js/ajax.js' );
	}
}
add_action( 'template_redirect', 'bp_links_add_js', 1 );

function bp_links_add_css() {
	if ( $bp->current_component == $bp->links->slug ) {
		wp_enqueue_style( 'bp-links-screen', get_stylesheet_directory_uri() . '/links/style.css' );
	}
}
add_action( 'wp_print_styles', 'bp_links_add_css' );


/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */

function bp_links_screen_my_links() {
	global $bp;

	// format for deleting notifications if we ever add any
	//bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->links->slug, 'link_example_notification' );

	do_action( 'bp_links_screen_my_links' );
	
	bp_core_load_template( apply_filters( 'bp_links_template_my_links', 'links/index' ) );
}

function bp_links_screen_create_link() {
	global $bp;
	
	// If the save, upload or skip button is hit, lets calculate what we need to save
	if ( isset( $_POST['save'] ) ) {

		// Check the nonce
		check_admin_referer( 'bp_link_details_form_save' );
		
		// validate the data fields, redirects on error
		$data_valid = bp_links_validate_create_form_input();

		if ( !empty( $data_valid ) ) {

			// try to create the link
			$bp->links->current_link =
				bp_links_manage_link(
					array(
						'category_id' => $data_valid['link-category'],
						'url' => $data_valid['link-url'],
						'name' => $data_valid['link-name'],
						'description' => $data_valid['link-desc'],
						'status' => $data_valid['link-status'],
						'enable_wire' => $data_valid['link-enable-wire'],
						'embed_data' => $data_valid['link-url-embed-data'],
						'embed_thidx' => $data_valid['link-url-embed-thidx']
					)
				);

			if ( bp_links_current_link_exists() ) {

				bp_links_update_linkmeta( $bp->links->current_link->id, 'last_activity', time() );

				bp_links_record_activity( array(
					'item_id' => $bp->links->current_link->id,
					'content' => apply_filters( 'bp_links_activity_created_link', sprintf( __( '%1$s created the link %2$s', 'buddypress-links'), bp_core_get_userlink( $bp->loggedin_user->id ), '<a href="' . bp_get_link_permalink( $bp->links->current_link ) . '">' . attribute_escape( $bp->links->current_link->name ) . '</a>' ) ),
					'primary_link' => apply_filters( 'bp_links_activity_created_link_primary_link', bp_get_link_permalink( $bp->links->current_link ) ),
					'component_action' => 'created_link'
				) );

				do_action( 'bp_links_create_complete', $bp->links->current_link->id );

				if ( $_POST['link-avatar-option'] == 1 ) {
					bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) . '/admin/link-avatar' );
				} else {
					bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) );
				}

			} else {
				bp_core_add_message( sprintf( '%s %s', __( 'There was an error saving link details.', 'buddypress-links' ), __( 'Please try again.', 'buddypress-links' ) ), 'error' );
			}
		}
	}
	
 	bp_core_load_template( apply_filters( 'bp_links_template_create_link', $bp->links->slug . '/create' ) );
}


function bp_links_screen_link_home() {
	global $bp;

	if ( $bp->is_single_item ) {

		// format for deleting notifications if we ever add any
		//if ( isset($_GET['new']) ) {
		//	bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, $bp->links->slug, 'link_example_notification' );
		//}

		do_action( 'bp_links_screen_link_home' );

		if ( '' != locate_template( array( 'links/single/home.php' ), false ) )
			bp_core_load_template( apply_filters( 'bp_links_template_link_home', 'links/single/home' ) );
		else
			bp_core_load_template( apply_filters( 'bp_links_template_link_home', 'links/link-home' ) );
	}
}

function bp_links_screen_link_wire() {
	global $bp;
	
	$wire_action = $bp->action_variables[0];
		
	if ( $bp->is_single_item ) {
		if ( 'post' == $wire_action && bp_links_is_link_visibile( $bp->links->current_link->id ) ) {
			/* Check the nonce first. */
			if ( !check_admin_referer( 'bp_wire_post' ) ) 
				return false;
		
			if ( !bp_links_new_wire_post( $bp->links->current_link->id, $_POST['wire-post-textarea'] ) )
				bp_core_add_message( __('Wire message could not be posted.', 'buddypress-links'), 'error' );
			else
				bp_core_add_message( __('Wire message successfully posted.', 'buddypress-links') );

			if ( !strpos( wp_get_referer(), $bp->wire->slug ) )
				bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) );
			else
				bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) . '/' . $bp->wire->slug );
	
		} else if ( 'delete' == $wire_action && bp_links_is_link_visibile( $bp->links->current_link->id ) ) {
			$wire_message_id = $bp->action_variables[1];

			/* Check the nonce first. */
			if ( !check_admin_referer( 'bp_wire_delete_link' ) )
				return false;
		
			if ( !bp_links_delete_wire_post( $wire_message_id, $bp->links->table_name_wire ) )
				bp_core_add_message( __('There was an error deleting the wire message.', 'buddypress-links'), 'error' );
			else
				bp_core_add_message( __('Wire message successfully deleted.', 'buddypress-links') );
			
			if ( !strpos( wp_get_referer(), $bp->wire->slug ) )
				bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) );
			else
				bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) . '/' . $bp->wire->slug );
		
		} else if ( ( !$wire_action || 'latest' == $bp->action_variables[1] ) ) {
			if ( '' != locate_template( array( 'links/single/wire.php' ), false ) )
				bp_core_load_template( apply_filters( 'bp_links_template_link_wire', 'links/single/wire' ) );
			else	
				bp_core_load_template( apply_filters( 'bp_links_template_link_wire', 'links/wire' ) );
		} else {
			if ( '' != locate_template( array( 'links/single/home.php' ), false ) )
				bp_core_load_template( apply_filters( 'bp_links_template_link_home', 'links/single/home' ) );
			else
				bp_core_load_template( apply_filters( 'bp_links_template_link_home', 'links/link-home' ) );
		}
	}
}

function bp_links_screen_link_admin() {
	global $bp;
	
	if ( $bp->current_component != BP_LINKS_SLUG || 'admin' != $bp->current_action )
		return false;
	
	if ( !empty( $bp->action_variables[0] ) )
		return false;
	
	bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) . '/admin/edit-details' );
}

function bp_links_screen_link_admin_edit_details() {
	global $bp;

	if ( !$bp->is_item_admin && !$bp->is_item_mod ) {
		return false;
	}

	if ( 'edit-details' != bp_links_admin_current_action_variable() ) {
		return false;
	}

	// If the edit form has been submitted, save the edited details
	if ( isset( $_POST['save'] ) ) {
		
		/* Check the nonce first. */
		if ( !check_admin_referer( 'bp_link_details_form_save' ) )
			return false;

		// validate the data fields
		$data_valid = bp_links_validate_create_form_input();

		if ( !empty( $data_valid ) ) {

			// try to update the link
			$link =
				bp_links_manage_link(
					array(
						'link_id' => $bp->links->current_link->id,
						'category_id' => $data_valid['link-category'],
						'url' => $data_valid['link-url'],
						'name' => $data_valid['link-name'],
						'description' => $data_valid['link-desc'],
						'status' => $data_valid['link-status'],
						'enable_wire' => $data_valid['link-enable-wire'],
						'embed_data' => $data_valid['link-url-embed-data'],
						'embed_thidx' => $data_valid['link-url-embed-thidx']
					)
				);

			if ( $link instanceof BP_Links_Link ) {
				$bp->links->current_link = $link;
				do_action( 'bp_links_link_details_edited', $bp->links->current_link->id );
				bp_core_add_message( __( 'Link details were successfully updated.', 'buddypress-links' ) );

				if ( $_POST['link-avatar-option'] == 1 ) {
					bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) . '/admin/link-avatar' );
				} else {
					bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) . '/admin/edit-details' );
				}

			} else {
				bp_core_add_message( sprintf( '%s %s', __( 'There was an error updating link details.', 'buddypress-links' ), __( 'Please try again.', 'buddypress-links' ) ), 'error' );
			}
		}
	}

	do_action( 'bp_links_screen_link_admin_edit_details', $bp->links->current_link->id );

	if ( '' != locate_template( array( 'links/single/admin.php' ), false ) )
		bp_core_load_template( apply_filters( 'bp_links_template_link_admin', 'links/single/admin' ) );
	else
		bp_core_load_template( apply_filters( 'bp_links_template_link_admin', 'links/admin/edit-details' ) );

}
add_action( 'wp', 'bp_links_screen_link_admin_edit_details', 4 );

function bp_links_screen_link_admin_avatar() {
	global $bp;

	if ( !$bp->is_item_admin || 'link-avatar' != bp_links_admin_current_action_variable() ) {
		return false;
	}

	// If the link admin has deleted the admin avatar
	if ( 'delete' == $bp->action_variables[1] ) {

		/* Check the nonce */
		check_admin_referer( 'bp_link_avatar_delete' );

		if ( bp_core_delete_existing_avatar( array( 'item_id' => $bp->links->current_link->id, 'object' => 'link', 'avatar_dir' => 'link-avatars' ) ) ) {
			bp_core_add_message( __( 'Your avatar was deleted successfully!', 'buddypress-links' ) );
		} else {
			bp_core_add_message( sprintf( '%s %s', __( 'There was a problem deleting that avatar', 'buddypress-links' ), __( 'Please try again.', 'buddypress-links' ) ), 'error' );
		}
	}

	$bp->avatar_admin->step = 'upload-image';

	if ( isset( $_POST['avatar-crop-submit'] ) ) {

		// Check the nonce
		check_admin_referer( 'bp_avatar_cropstore' );

		// received crop coords, crop the image and save a full/thumb version
		if ( bp_core_avatar_handle_crop( array( 'object' => 'link', 'avatar_dir' => 'link-avatars', 'item_id' => $bp->links->current_link->id, 'original_file' => $_POST['image_src'], 'crop_x' => $_POST['x'], 'crop_y' => $_POST['y'], 'crop_w' => $_POST['w'], 'crop_h' => $_POST['h'] ) ) ) {
			bp_links_embed_handle_crop( $bp->links->current_link );
			bp_core_add_message( __( 'The link avatar was uploaded successfully!', 'buddypress-links' ) );
		} else {
			bp_core_add_message( sprintf( '%s %s', __( 'There was an error saving link avatar.', 'buddypress-links' ), __( 'Please try again.', 'buddypress-links' ) ), 'error' );
		}
		
	} elseif ( isset( $_POST['upload'] ) || isset( $_POST['embed-submit'] ) ) {

		// Check the nonce
		check_admin_referer( 'bp_avatar_upload' );

		// handle image uploading
		if ( !empty( $_POST['embed-submit'] ) && bp_links_embed_handle_upload( $bp->links->current_link, $_POST['embed-html'] ) ) {
			
			// we are good to crop
			$bp->avatar_admin->step = 'crop-image';

			// Make sure we include the jQuery jCrop file for image cropping
			add_action( 'wp', 'bp_core_add_jquery_cropper' );

		} elseif ( isset( $_POST['upload'] ) && !empty( $_FILES ) ) {

			// Pass the file to the avatar upload handler
			if ( bp_core_avatar_handle_upload( $_FILES, 'bp_links_avatar_upload_dir' ) ) {

				// we are good to crop
				$bp->avatar_admin->step = 'crop-image';

				// Make sure we include the jQuery jCrop file for image cropping
				add_action( 'wp', 'bp_core_add_jquery_cropper' );
			}
		}
	}

	do_action( 'bp_links_screen_link_admin_avatar', $bp->links->current_link->id );

	if ( '' != locate_template( array( 'links/single/admin.php' ), false ) ) {
		bp_core_load_template( apply_filters( 'bp_links_template_link_admin_avatar', 'links/single/admin' ) );
	} else {
		bp_core_load_template( apply_filters( 'bp_links_template_link_admin_avatar', 'links/admin/link-avatar' ) );
	}
		
}
add_action( 'wp', 'bp_links_screen_link_admin_avatar', 4 );

function bp_links_screen_link_admin_delete_link() {
	global $bp;

	if ( !$bp->is_item_admin && !is_site_admin() ) {
		return false;
	}

	if ( 'delete-link' != bp_links_admin_current_action_variable() ) {
		return false;
	}

	if ( isset( $_REQUEST['delete-link-button'] ) && !empty( $_REQUEST['delete-link-understand'] ) ) {

		/* Check the nonce first. */
		if ( !check_admin_referer( 'bp_links_delete_link' ) ) {
			return false;
		}

		// Link admin has deleted the link, now do it.
		if ( bp_links_delete_link( $bp->links->current_link->id ) ) {
			do_action( 'bp_links_link_deleted', $bp->links->current_link->id );
			bp_core_add_message( __( 'The link was deleted successfully', 'buddypress-links' ) );
			bp_core_redirect( $bp->loggedin_user->domain . $bp->links->slug . '/' );
		} else {
			bp_core_add_message( __( 'There was an error deleting the link, please try again.', 'buddypress-links' ), 'error' );
		}

		bp_core_redirect( $bp->loggedin_user->domain . $bp->current_component );
	}

	do_action( 'bp_links_screen_link_admin_delete_link', $bp->links->current_link->id );

	if ( '' != locate_template( array( 'links/single/admin.php' ), false ) ) {
		bp_core_load_template( apply_filters( 'bp_links_template_link_admin_delete_link', 'links/single/admin' ) );
	} else {
		bp_core_load_template( apply_filters( 'bp_links_template_link_admin_delete_link', 'links/admin/delete-link' ) );
	}
}
add_action( 'wp', 'bp_links_screen_link_admin_delete_link', 4 );

function bp_links_screen_notification_settings() {
	global $current_user; ?>
	<table class="notification-settings" id="links-notification-settings">
		<tr>
			<th class="icon"></th>
			<th class="title"><?php _e( 'Links', 'buddypress-links' ) ?></th>
			<th class="yes"><?php _e( 'Yes', 'buddypress-links' ) ?></th>
			<th class="no"><?php _e( 'No', 'buddypress-links' )?></th>
		</tr>
		<?php if ( function_exists('bp_wire_install') ) { ?>
		<tr>
			<td></td>
			<td><?php _e( 'A member posts on the wire of a link you created', 'buddypress-links' ) ?></td>
			<td class="yes"><input type="radio" name="notifications[notification_links_wire_post]" value="yes" <?php if ( !get_usermeta( $current_user->id, 'notification_links_wire_post') || 'yes' == get_usermeta( $current_user->id, 'notification_links_wire_post') ) { ?>checked="checked" <?php } ?>/></td>
			<td class="no"><input type="radio" name="notifications[notification_links_wire_post]" value="no" <?php if ( 'no' == get_usermeta( $current_user->id, 'notification_links_wire_post') ) { ?>checked="checked" <?php } ?>/></td>
		</tr>
		<?php } ?>
		<?php do_action( 'bp_links_screen_notification_settings' ); ?>
	</table>
<?php	
}
add_action( 'bp_notification_settings', 'bp_links_screen_notification_settings' );

function bp_links_validate_create_form_input() {
	
	$message_required = __( 'Please fill in all of the required fields', 'buddypress-links' );

	if ( !empty( $_POST['link-category'] ) ) {
		$bp_new_link_category = stripslashes( $_POST['link-category'] );
		$return_data['link-category'] = $bp_new_link_category;
	} else {
		bp_core_add_message( $message_required, 'error' );
		return false;
	}

	// link url
	if ( !empty( $_POST['link-url'] ) ) {

		$bp_new_link_url = trim( stripslashes( $_POST['link-url'] ) );

		if ( strlen( $bp_new_link_url ) > BP_LINKS_MAX_CHARACTERS_URL ) {
			bp_core_add_message( sprintf( __( 'Link URL must be %1$d characters or less, please make corrections and re-submit.', 'buddypress-links' ), BP_LINKS_MAX_CHARACTERS_URL ), 'error' );
			return false;
		} elseif ( bp_links_is_url_valid( $bp_new_link_url ) !== true ) {
			bp_core_add_message( __( 'The URL you entered is not valid.', 'buddypress-links' ), 'error' );
			return false;
		}

		$return_data['link-url'] = $bp_new_link_url;

	} else {
		bp_core_add_message( $message_required, 'error' );
		return false;
	}

	// link name
	if ( !empty( $_POST['link-name'] ) ) {

		$bp_new_link_name = trim( stripslashes( $_POST['link-name'] ) );

		if ( ( function_exists('mb_strlen') && mb_strlen( $bp_new_link_name ) > BP_LINKS_MAX_CHARACTERS_NAME ) || strlen( $bp_new_link_name ) > BP_LINKS_MAX_CHARACTERS_NAME ) {
			bp_core_add_message( sprintf( __( 'Link Name must be %1$d characters or less, please make corrections and re-submit.', 'buddypress-links' ), BP_LINKS_MAX_CHARACTERS_NAME ), 'error' );
			return false;
		}

		$return_data['link-name'] = $bp_new_link_name;

	} else {
		bp_core_add_message( $message_required, 'error' );
		return false;
	}

	// link description
	if ( !empty( $_POST['link-desc'] ) ) {

		$bp_new_link_description = trim( stripslashes( $_POST['link-desc'] ) );

		if ( ( function_exists('mb_strlen') && mb_strlen( $bp_new_link_description ) > BP_LINKS_MAX_CHARACTERS_DESCRIPTION ) || strlen( $bp_new_link_description ) > BP_LINKS_MAX_CHARACTERS_DESCRIPTION ) {
			bp_core_add_message( sprintf( __( 'Link Description must be %1$d characters or less, please make corrections and re-submit.', 'buddypress-links' ), BP_LINKS_MAX_CHARACTERS_DESCRIPTION ), 'error' );
			return false;
		}

		$return_data['link-desc'] = $bp_new_link_description;

	} else {
		bp_core_add_message( $message_required, 'error' );
		return false;
	}

	// enable wire
	if ( empty($_POST['link-enable-wire']) ) {
		$return_data['link-enable-wire'] = 0;
	} else {
		$return_data['link-enable-wire'] = null;
	}

	// link status
	if ( bp_links_is_valid_status( $_POST['link-status'] ) ) {
		$return_data['link-status'] = (integer) $_POST['link-status'];
	} else {
		$return_data['link-status'] = null;
	}

	// link url embed service (optional)
	if ( !empty( $_POST['link-url-embed-data'] ) ) {
		$return_data['link-url-embed-data'] = trim( $_POST['link-url-embed-data'] );
	} else {
		$return_data['link-url-embed-data'] = null;
	}

	// link url embed service selected image index (optional)
	if ( isset( $_POST['link-url-embed-thidx'] ) ) {
		$return_data['link-url-embed-thidx'] = trim( $_POST['link-url-embed-thidx'] );
	} else {
		$return_data['link-url-embed-thidx'] = null;
	}

	return $return_data;
}

function bp_links_admin_current_action_variable() {
	global $bp;

	if ( $bp->current_component == BP_LINKS_SLUG && 'admin' == $bp->current_action ) {
		return $bp->action_variables[0];
	} else {
		return false;
	}
}

/********************************************************************************
 * Action Functions
 *
 * Action functions are exactly the same as screen functions, however they do not
 * have a template screen associated with them. Usually they will send the user
 * back to the default screen after execution.
 */

function bp_links_action_redirect_to_random_link() {
	global $bp, $wpdb;

	if ( $bp->current_component == $bp->links->slug && isset( $_GET['random-link'] ) ) {
		
		$link = bp_links_get_random_link();

		bp_core_redirect( $bp->root_domain . '/' . $bp->links->slug . '/' . $link['links'][0]->slug );
	}
}
add_action( 'wp', 'bp_links_action_redirect_to_random_link', 6 );

function bp_links_action_directory_feed() {
	global $bp, $wp_query;

	if ( $bp->current_component != $bp->links->slug || $bp->current_action != 'feed' || $bp->displayed_user->id )
		return false;

	$wp_query->is_404 = false;
	status_header( 200 );

	include_once( 'bp-links-feed.php' );
	die();
}
add_action( 'plugins_loaded', 'bp_links_action_directory_feed', 6 );

/********************************************************************************
 * Activity & Notification Functions
 *
 * These functions handle the recording, deleting and formatting of activity and
 * notifications for the user and for this specific component.
 */

function bp_links_register_activity_actions() {
	global $bp;

	if ( !function_exists( 'bp_activity_set_action' ) )
		return false;

	bp_activity_set_action( $bp->links->id, 'created_link', __( 'Created a link', 'buddypress-links' ) );
	bp_activity_set_action( $bp->links->id, 'new_wire_post', __( 'New link wire post', 'buddypress-links' ) );

	do_action( 'bp_links_register_activity_actions' );
}
add_action( 'plugins_loaded', 'bp_links_register_activity_actions' );

function bp_links_record_activity( $args = '' ) {
	global $bp;
	
	if ( !function_exists( 'bp_activity_add' ) )
		return false;

	/* If the link is not public, no recording of activity please. */
	if ( BP_Links_Link::STATUS_PUBLIC != $bp->links->current_link->status )
		return false;

	$defaults = array(
		'user_id' => $bp->loggedin_user->id,
		'content' => false,
		'primary_link' => false,
		'component_name' => $bp->links->id,
		'component_action' => false,
		'item_id' => false,
		'secondary_item_id' => false,
		'recorded_time' => time(),
		'hide_sitewide' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );	
	
	return bp_activity_add( array( 'user_id' => $user_id, 'content' => $content, 'primary_link' => $primary_link, 'component_name' => $component_name, 'component_action' => $component_action, 'item_id' => $item_id, 'secondary_item_id' => $secondary_item_id, 'recorded_time' => $recorded_time, 'hide_sitewide' => $hide_sitewide ) );
}

function bp_links_update_last_activity( $link_id ) {
	bp_links_update_linkmeta( $link_id, 'last_activity', time() );
}
add_action( 'bp_links_deleted_wire_post', 'bp_links_update_last_activity' );
add_action( 'bp_links_new_wire_post', 'bp_links_update_last_activity' );
add_action( 'bp_links_created_link', 'bp_links_update_last_activity' );

function bp_links_format_notifications( $action, $item_id, $secondary_item_id, $total_items ) {
	global $bp;
	
	switch ( $action ) {
		case 'link_example_notification':
			$link_id = $secondary_item_id;
			$requesting_user_id = $item_id;

			$link = new BP_Links_Link( $link_id, false, false );
			
			$link_link = bp_get_link_permalink( $link );

			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_links_multiple_example_notification', '<a href="' . $link_link . '/admin/example-slug/" title="' . __( 'Link Example Event', 'buddypress-links' ) . '">' . sprintf( __('%d number of example events happened for the link "%s"', 'buddypress-links' ), (int)$total_items, $link->name ) . '</a>', $link_link, $total_items, $link->name );
			} else {
				$user_fullname = bp_core_get_user_displayname( $requesting_user_id );
				return apply_filters( 'bp_links_single_example_notification', '<a href="' . $link_link . '/admin/example-slug/" title="' . $user_fullname .' did something">' . sprintf( __('%1$s triggered a notification for the link "%2$s"', 'buddypress-links' ), $user_fullname, $link->name ) . '</a>', $link_link, $user_fullname, $link->name );
			}	
		break;
	}

	do_action( 'bp_links_format_notifications', $action, $item_id, $secondary_item_id, $total_items );
	
	return false;
}


/********************************************************************************
 * Business Functions
 *
 * Business functions are where all the magic happens in BuddyPress. They will
 * handle the actual saving or manipulation of information. Usually they will
 * hand off to a database class for data access, then return
 * true or false on success or failure.
 */

/*** Link Creation, Editing & Deletion *****************************************/

function bp_links_manage_link( $args = '' ) {
	global $bp;

	extract( $args );
	
	/**
	 * Possible parameters (pass as assoc array):
	 *	'link_id'
	 *	'user_id'
	 *	'category_id'
	 *	'url'
	 *	'target'
	 *	'rel'
	 *	'name'
	 *	'description'
	 *	'status'
	 *	'embed_data'
	 *	'embed_thidx'
	 */

	if ( $link_id ) {
		$link = new BP_Links_Link( $link_id );
	} else {
		$link = new BP_Links_Link();
	}
	
	if ( $user_id ) {
		$link->user_id = $user_id;
	} else {
		if ( empty( $link->id ) ) {
			$link->user_id = $bp->loggedin_user->id;
		}
	}
	
	if ( isset( $category_id ) ) {
		$link->category_id = $category_id;
	}

	if ( isset( $url ) ) {
		$link->url = $url;
	}

	if ( isset( $target ) ) {
		$link->target = $target;
	}

	if ( isset( $rel ) ) {
		$link->rel = $rel;
	}

	if ( isset( $name ) ) {
		$link->name = $name;
		if ( empty( $link->id ) ) {
			$link->slug = bp_links_check_slug( sanitize_title_with_dashes( $name ) );
		}
	}
	
	if ( isset( $description ) ) {
		$link->description = $description;
	}
	
	if ( isset( $status ) ) {
		if ( bp_links_is_valid_status( $status ) ) {
			$link->status = $status;
		}
	}
	
	if ( isset( $enable_wire ) ) {
		$link->enable_wire = $enable_wire;
	}

	if ( !empty( $embed_data ) ) {
		try {
			// load service
			$service = BP_Links_Embed::LoadService( $embed_data );
			// try to attach embed service to link
			if ( $service instanceof BP_Links_Embed_Service ) {
				// handle selectable image
				if ( $service instanceof BP_Links_Embed_Has_Selectable_Image ) {
					if ( isset( $embed_thidx ) ) {
						$service->image_set_selected( $embed_thidx );
					}
				}
				// attach and enable service
				$link->embed_attach( $service );
				$link->embed_status_set_enabled();
			}
		} catch ( BP_Links_Embed_Exception $e ) {
			// TODO what should we do with an exception here?
			throw( $e );
		}
	}

	if ( $link->save() ) {
		// all done
		return $link;
	}
	
	return false;
}

function bp_links_edit_base_link_details( $link_id, $link_category_id, $link_url, $link_name, $link_desc ) {
	global $bp;

	if ( empty( $link_category_id ) || empty( $link_url ) || empty( $link_desc ) || empty( $link_desc ) )
		return false;

	$link = new BP_Links_Link( $link_id, false );
	$link->category_id = $link_category_id;
	$link->url = $link_url;
	$link->name = $link_name;
	$link->description = $link_desc;

	if ( !$link->save() )
		return false;

	do_action( 'bp_links_details_updated', $link->id );
	
	return true;
}

function bp_links_edit_link_settings( $link_id, $enable_wire, $status ) {
	global $bp;
	
	$link = new BP_Links_Link( $link_id, false, false );
	$link->enable_wire = $enable_wire;
	$link->status = $status;
	
	if ( !$link->save() )
		return false;
	
	do_action( 'bp_links_settings_updated', $link->id );
	
	return true;
}

function bp_links_delete_link( $link_id ) {
	global $bp;
	
	// Check the user is the link admin.
	if ( !$bp->is_item_admin && !is_site_admin())
		return false;
	
	// Get the link object
	$link = new BP_Links_Link( $link_id );
	
	if ( !$link->delete() )
		return false;

	/* Delete all link activity from activity streams */
	if ( function_exists( 'bp_activity_delete_by_item_id' ) ) {
		bp_activity_delete_by_item_id( array( 'item_id' => $link_id, 'component_name' => $bp->links->id ) );
	}	
 
	// Remove all notifications for any user belonging to this link
	bp_core_delete_all_notifications_by_type( $link_id, $bp->links->slug );
	
	do_action( 'bp_links_delete_link', $link_id );
	
	return true;
}

function bp_links_is_link_visibile( $link_id_or_obj, $user_id = null ) {
	global $bp;

	if ( $link_id_or_obj instanceof BP_Links_Link ) {
		$link = $link_id_or_obj;
	} else {
		$link = new BP_Links_Link( $link_id_or_obj );
	}

	if ( null == $user_id) {
		$user_id = $bp->loggedin_user->id;
	}

	// owners and site admins can always see the link
	if ( $bp->is_item_admin ) {
		return true;
	}

	// who else can see this link?
	// check friendship last because of DB hit
	switch ( $link->status ) {
		case BP_Links_Link::STATUS_PUBLIC:
			return true;
		case BP_Links_Link::STATUS_HIDDEN:
			return false;
		case BP_Links_Link::STATUS_FRIENDS:
			return friends_check_friendship( $user_id, $link->user_id );
		default:
			return false;
	}
}

function bp_links_is_valid_status( $status ) {
	return BP_Links_Link::is_valid_status( $status );
}

function bp_links_check_slug( $slug ) {
	global $bp;

	if ( 'wp' == substr( $slug, 0, 2 ) )
		$slug = substr( $slug, 2, strlen( $slug ) - 2 );
			
	if ( in_array( $slug, (array)$bp->links->forbidden_names ) ) {
		$slug = $slug . '-' . rand();
	}
	
	if ( BP_Links_Link::check_slug( $slug ) ) {
		do {
			$slug = $slug . '-' . rand();
		}
		while ( BP_Links_Link::check_slug( $slug ) );
	}
	
	return $slug;
}

function bp_links_get_slug( $link_id ) {
	$link = new BP_Links_Link( $link_id, false, false );
	return $link->slug;
}

function bp_links_get_last_updated() {
	return apply_filters( 'bp_links_get_last_updated', BP_Links_Link::get_last_updated() );
}

function bp_links_current_link() {
	global $bp;

	return $bp->links->current_link;
}

function bp_links_current_link_exists() {
	global $bp;

	return ( $bp->links->current_link instanceof BP_Links_Link );
}

function bp_links_current_link_embed_enabled() {
	global $bp;

	if ( bp_links_current_link_exists() ) {
		return ( $bp->links->current_link->embed_status_enabled() );
	} else {
		return false;
	}
}

function bp_links_current_link_embed_service() {
	global $bp;

	if ( bp_links_current_link_embed_enabled() ) {
		return $bp->links->current_link->embed();
	} else {
		return false;
	}
}

/*** General Link Functions ****************************************************/

function bp_links_check_link_exists( $link_id ) {
	return BP_Links_Link::link_exists( $link_id );
}

function bp_links_is_url_valid( $url ) {
	return ( preg_match('/^https?:\/\/(([a-z0-9-]+\.)+[a-z]{2,6}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(:[0-9]+)?(\/?|\/\S+)$/iu', $url ) === 1 ) ? true : false;
}

/*** Link Fetching, Filtering & Searching  *************************************/

function bp_links_get_active( $limit = null, $page = 1 ) {
	return BP_Links_Link::get_recently_active_filtered( null, null, null, $limit, $page );
}

function bp_links_get_newest( $limit = null, $page = 1 ) {
	return BP_Links_Link::get_newest_filtered( null, null, null, $limit, $page );
}

function bp_links_get_popular( $limit = null, $page = 1 ) {
	return BP_Links_Link::get_popular_filtered( null, null, null, $limit, $page );
}

function bp_links_get_random_link() {
	return BP_Links_Link::get_random(1,1);
}

function bp_links_get_recently_active_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;
	
	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Links_Link::get_recently_active_for_user( $user_id, $pag_num, $pag_page, $filter );
}

function bp_links_get_newest_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Links_Link::get_newest_for_user( $user_id, $pag_num, $pag_page, $filter );
}

function bp_links_get_most_popular_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Links_Link::get_most_popular_for_user( $user_id, $pag_num, $pag_page, $filter );
}

function bp_links_get_most_votes_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;
	
	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Links_Link::get_most_votes_for_user( $user_id, $pag_num, $pag_page, $filter );
}

function bp_links_get_high_votes_for_user( $user_id = false, $pag_num = false, $pag_page = false, $filter = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->displayed_user->id;

	return BP_Links_Link::get_high_votes_for_user( $user_id, $pag_num, $pag_page, $filter );
}

function bp_links_total_links_for_user( $user_id = false ) {
	global $bp;
	
	if ( !$user_id )
		$user_id = $bp->displayed_user->id;
		
	return BP_Links_Link::get_total_link_count_for_user( $user_id );
}

function bp_links_get_random_links_for_user( $user_id = false, $total_links = 5 ) {
	global $bp;
	
	if ( !$user_id )
		$user_id = $bp->displayed_user->id;
		
	return BP_Links_Link::get_random_for_user( $user_id, $total_links, 1 );
}

function bp_links_search_links( $search_terms, $pag_num_per_page = 5, $pag_page = 1 ) {
	return BP_Links_Link::get_search_filtered( $search_terms, null, null, $pag_num_per_page, $pag_page );
}

/*** Link Avatars *************************************************************/

function bp_links_default_avatar_uri() {
	return apply_filters( 'bp_links_default_avatar_uri', get_stylesheet_directory_uri() . '/links/_inc/images/default_avatar.png' );
}

function bp_links_check_avatar( $item_id ) {

	$params = array(
		'item_id' => $item_id,
		'object' => 'link',
		'avatar_dir' => 'link-avatars',
		'no_grav' => true
	);

	$avatar = bp_core_fetch_avatar( $params );

	return ( empty( $avatar ) ) ? false : true;
}

function bp_links_fetch_avatar( $args = '', $link = false ) {

	$defaults = array(
		'item_id' => false,
		'type' => 'full',
		'width' => false,
		'height' => false,
		'class' => 'avatar',
		'css_id' => false,
		'alt' => __( 'Link Avatar', 'buddypress-links' )
	);

	$params = wp_parse_args( $args, $defaults );

	// hard code these options to prevent tampering
	// DO NOT try to use a gravatar, ever!
	$params['object'] = 'link';
	$params['avatar_dir'] = 'link-avatars';
	$params['no_grav'] = true;

	// try to grab avatar file
	$avatar = bp_core_fetch_avatar( $params );

	if ( !empty( $avatar ) ) {

		// found an avatar file, return html for it
		return $avatar;
		
	} else {
		
		extract( $params, EXTR_SKIP );

		$avatar_url = null;

		// check if we can use thumb from embedded content
		if ( !empty( $link ) && $link->embed_status_enabled() ) {

			$image_thumb_url = $link->embed()->image_thumb_url();

			if ( !empty( $image_thumb_url ) ) {

				// append class avatar-embed
				$class .= ' avatar-embed';

				// check for additional avatar class
				if ( $link->embed()->avatar_class() ) {
					$class .= ' ' . $link->embed()->avatar_class();
				}

				// when avatar type is 'full', check for avatar size limits and special class
				if ( 'full' == $type ) {
					// get large thumb url from service object
					$avatar_url = $link->embed()->image_large_thumb_url();

					// check for custom width and height
					if ( $link->embed()->avatar_max_width() && $link->embed()->avatar_max_height() ) {
						$width = $link->embed()->avatar_max_width();
						$height = $link->embed()->avatar_max_height();
					}
				} else {
					// get standard thumb url from service object
					$avatar_url = $link->embed()->image_thumb_url();
				}
			}
		}

		// have an avatar file yet?
		if ( empty( $avatar_url ) ) {

			// no avatar file found, use the default image
			$avatar_url = bp_links_default_avatar_uri();
			
			// default width/height
			if ( empty( $width ) )
				$width = ( 'thumb' == $type ) ? BP_AVATAR_THUMB_WIDTH : BP_AVATAR_FULL_WIDTH;
			if ( empty( $height ) )
				$height = ( 'thumb' == $type ) ? BP_AVATAR_THUMB_HEIGHT : BP_AVATAR_FULL_HEIGHT;
				
		}

		if ( !$css_id )
			$css_id = $object . '-' . $item_id . '-avatar';

		if ( $width )
			$attr_width = " width='{$width}'";

		if ( $height )
			$attr_height = " height='{$height}'";
	
		return apply_filters( 'bp_links_fetch_avatar_not_found', sprintf( '<img src="%s" alt="%s" id="%s" class="%s"%s%s />', $avatar_url, $alt, $css_id, $class, $attr_width, $attr_height ), $args );
	}
}

function bp_links_avatar_upload_dir( $link_id = false ) {
	global $bp;

	if ( !$link_id )
		$link_id = $bp->links->current_link->id;

	$path  = get_blog_option( BP_ROOT_BLOG, 'upload_path' );
	$newdir = WP_CONTENT_DIR . str_replace( 'wp-content', '', $path );
	$newdir .= '/link-avatars/' . $link_id;

	$newbdir = $newdir;
	
	if ( !file_exists( $newdir ) )
		@wp_mkdir_p( $newdir );

	$newurl = WP_CONTENT_URL . '/blogs.dir/' . BP_ROOT_BLOG . '/files/link-avatars/' . $link_id;
	$newburl = $newurl;
	$newsubdir = '/link-avatars/' . $link_id;

	return apply_filters( 'bp_links_avatar_upload_dir', array( 'path' => $newdir, 'url' => $newurl, 'subdir' => $newsubdir, 'basedir' => $newbdir, 'baseurl' => $newburl, 'error' => false ) );
}

/*** Link Wire ****************************************************************/

function bp_links_new_wire_post( $link_id, $content ) {
	global $bp;
	
	if ( !function_exists( 'bp_wire_new_post' ) )
		return false;
	
	if ( $wire_post = bp_wire_new_post( $link_id, $content, 'links' ) ) {

		/* Post an email notification if settings allow */
		require_once ( BP_LINKS_PLUGIN_DIR . '/bp-links-notifications.php' );
		bp_links_notification_new_wire_post( $link_id, $wire_post->id );

		/* Record this in activity streams */
		$activity_content = sprintf( __( '%1$s wrote on the wire of the link %2$s:', 'buddypress-links'), bp_core_get_userlink( $bp->loggedin_user->id ), '<a href="' . bp_get_link_permalink( $bp->links->current_link ) . '">' . attribute_escape( $bp->links->current_link->name ) . '</a>' );
		$activity_content .= '<blockquote>' . bp_create_excerpt( attribute_escape( $content ) ) . '</blockquote>';
		
		bp_links_record_activity( array(
			'content' => apply_filters( 'bp_links_activity_new_wire_post', $activity_content ),
			'primary_link' => apply_filters( 'bp_links_activity_new_wire_post_primary_link', bp_get_link_permalink( $bp->links->current_link ) ),
			'component_action' => 'new_wire_post',
			'item_id' => $bp->links->current_link->id,
			'secondary_item_id' => $wire_post->item_id
		) );

		do_action( 'bp_links_new_wire_post', $link_id, $wire_post->id );
		
		return true;
	}
	
	return false;
}

function bp_links_delete_wire_post( $wire_post_id, $table_name ) {
	if ( bp_wire_delete_post( $wire_post_id, 'links', $table_name ) ) {
		/* Delete the activity stream item */
		if ( function_exists( 'bp_activity_delete_by_item_id' ) ) {
			bp_activity_delete_by_item_id( array( 'item_id' => $wire_post_id, 'component_name' => 'links', 'component_action' => 'new_wire_post' ) );
		}
			
		do_action( 'bp_links_deleted_wire_post', $wire_post_id );
		return true;
	}
	
	return false;
}

function bp_links_wire_show_email_notify() {
	return false;
}
add_filter('bp_wire_show_email_notify', 'bp_links_wire_show_email_notify');

/*
function bp_links_ban_member( $user_id, $link_id ) {
	global $bp;

	if ( !$bp->is_item_admin )
		return false;
		
	$member = new BP_Links_Member( $user_id, $link_id );

	do_action( 'bp_links_ban_member', $user_id, $link_id );
	
	return $member->ban();
}
*/

/*
function bp_links_unban_member( $user_id, $link_id ) {
	global $bp;
	
	if ( !$bp->is_item_admin )
		return false;
		
	$member = new BP_Links_Member( $user_id, $link_id );
	
	do_action( 'bp_links_unban_member', $user_id, $link_id );
	
	return $member->unban();
}
*/

/*** Link Voting ***/

/**
 * Cast a user vote for a link
 * 
 * Returns a BP_Links_Link object on successful vote
 * in case you need immediate access to the link data
 *
 * @see BP_Links_Link
 * @param integer $link_id
 * @param string $up_or_down "up" or "down"
 * @return BP_Links_Link|false
 */
function bp_links_cast_vote( $link_id, $up_or_down ) {
	global $bp;

	$bp->links->current_link = new BP_Links_Link( $link_id, true );

	if ( false === bp_links_is_link_visibile( $bp->links->current_link ) ) {
		return false;
	}

	$vote = $bp->links->current_link->vote();

	if ( !$vote instanceof BP_Links_Vote ) {
		return false;
	}

	// determine if member has voted for this link already
	$is_first_vote = ( is_numeric( $vote->vote ) ) ? false : true;

	// the default behavior is to allow members to change their vote,
	// which can be overriden with the configuration constant you see passed
	// to the filter below. use this filter to override the `configured` behavior
	// for special circumstances. you must return a boolean value!
	$allow_change = (boolean) apply_filters( 'bp_links_cast_vote_allow_change', (boolean) BP_LINKS_VOTE_ALLOW_CHANGE, $vote );

	// member can vote if its first time, or they are allowed to change vote
	if ( $is_first_vote || $allow_change ) {
		
		// the default behavior is to record vote activity.
		// this can be overriden with the configuration constant you see below.
		if ( (boolean) BP_LINKS_VOTE_RECORD_ACTIVITY === true ) {
			// the default behavior is to only record activity if this is their
			// original vote. use the filter below to override this behavior.
			// you must return a boolean value!
			$record_activity = (boolean) apply_filters( 'bp_links_cast_vote_record_activity', $is_first_vote, $vote );
		} else {
			// do not record activity per configuration constant
			$record_activity = false;
		}

		switch ( $up_or_down ) {
			case 'up':
				$vote->up();
				break;
			case 'down':
				$vote->down();
				break;
			default:
				return false;
		}

		if ( true === $bp->links->current_link->save() ) {

			if ( $record_activity ) {

				// translate up or down string
				$up_or_down_translated = ( 'up' == $up_or_down ) ? __( 'up', 'buddypress-links') : __( 'down', 'buddypress-links');

				// record the activity
				$activity_content = sprintf( __( '%1$s voted %2$s the link %3$s', 'buddypress-links'), bp_core_get_userlink( $bp->loggedin_user->id ), $up_or_down_translated, '<a href="' . bp_get_link_permalink( $bp->links->current_link ) . '">' . attribute_escape( $bp->links->current_link->name ) . '</a>' );

				bp_links_record_activity( array(
					'content' => apply_filters( 'bp_links_activity_voted', $activity_content ),
					'primary_link' => apply_filters( 'bp_links_activity_voted_primary_link', bp_get_link_permalink( $bp->links->current_link ) ),
					'component_action' => 'voted_on_link',
					'item_id' => $bp->links->current_link->id
				) );

			}

			do_action( 'bp_links_cast_vote_success', $bp->links->current_link->id );

			// return the link object
			return $bp->links->current_link;
		} else {
			return false;
		}
	} else {
		// member not allowed change vote
		// this is not an error, so return true!
		return true;
	}
}

function bp_links_recalculate_popularity_for_all() {
	return BP_Links_Link::popularity_recalculate_all();
}
add_action( 'bp_links_cron_popularity', 'bp_links_recalculate_popularity_for_all', 1 );


/*** Link Meta Function ****************************************************/

function bp_links_delete_linkmeta( $link_id, $meta_key = false, $meta_value = false ) {
	global $wpdb, $bp;
	
	if ( !is_numeric( $link_id ) )
		return false;
		
	$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

	if ( is_array($meta_value) || is_object($meta_value) )
		$meta_value = serialize($meta_value);
		
	$meta_value = trim( $meta_value );

	if ( !$meta_key ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d", $link_id ) );
	} else if ( $meta_value ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d AND meta_key = %s AND meta_value = %s", $link_id, $meta_key, $meta_value ) );
	} else {
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d AND meta_key = %s", $link_id, $meta_key ) );
	}
	
	// TODO need to look into using this.
	// wp_cache_delete($link_id, 'links');

	return true;
}

function bp_links_get_linkmeta( $link_id, $meta_key = '') {
	global $wpdb, $bp;
	
	$link_id = (int) $link_id;

	if ( !$link_id )
		return false;

	if ( !empty($meta_key) ) {
		$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);
		
		// TODO need to look into using this.
		//$user = wp_cache_get($user_id, 'users');
		
		// Check the cached user object
		//if ( false !== $user && isset($user->$meta_key) )
		//	$metas = array($user->$meta_key);
		//else
		$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d AND meta_key = %s", $link_id, $meta_key) );
	} else {
		$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d", $link_id) );
	}

	if ( empty($metas) ) {
		if ( empty($meta_key) )
			return array();
		else
			return '';
	}

	$metas = array_map('maybe_unserialize', $metas);

	if ( 1 == count($metas) )
		return $metas[0];
	else
		return $metas;
}

function bp_links_update_linkmeta( $link_id, $meta_key, $meta_value ) {
	global $wpdb, $bp;
	
	if ( !is_numeric( $link_id ) )
		return false;
	
	$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

	if ( is_string($meta_value) )
		$meta_value = stripslashes($wpdb->escape($meta_value));
	
	$meta_value = maybe_serialize($meta_value);
	
	if (empty($meta_value)) {
		return bp_links_delete_linkmeta( $link_id, $meta_key );
	}

	$cur = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d AND meta_key = %s", $link_id, $meta_key ) );

	if ( !$cur ) {
		$wpdb->query( $wpdb->prepare( "INSERT INTO " . $bp->links->table_name_linkmeta . " ( link_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $link_id, $meta_key, $meta_value ) );
	} else if ( $cur->meta_value != $meta_value ) {
		$wpdb->query( $wpdb->prepare( "UPDATE " . $bp->links->table_name_linkmeta . " SET meta_value = %s WHERE link_id = %d AND meta_key = %s", $meta_value, $link_id, $meta_key ) );
	} else {
		return false;
	}

	// TODO need to look into using this.
	// wp_cache_delete($user_id, 'users');

	return true;
}

/*** Link Cleanup Functions ****************************************************/

/**
 * Reset embed fields if avatar is deleted
 *
 * @param array $args
 * @return boolean
 */
function bp_links_delete_existing_avatar( $args ) {
	if ( 'link' == $args['object'] ) {
		$link = new BP_Links_Link( $args['item_id'] );
		if ( $link->embed_status_enabled() && $link->embed()->avatar_only() === true ) {
			return $link->embed_remove(true);
		}
	}
	return true;
}
add_action( 'bp_core_delete_existing_avatar', 'bp_links_delete_existing_avatar' );

function bp_links_remove_data_for_user( $user_id ) {
//  TODO add this data cleanup method
//	BP_Links_Link::delete_all_for_user($user_id);

	do_action( 'bp_links_remove_data_for_user', $user_id );
}
add_action( 'wpmu_delete_user', 'bp_links_remove_data_for_user', 1 );
add_action( 'delete_user', 'bp_links_remove_data_for_user', 1 );
add_action( 'make_spam_user', 'bp_links_remove_data_for_user', 1 );

function bp_links_clear_link_object_cache( $link_id ) {
	wp_cache_delete( 'bp_links_link_nouserdata_' . $link_id, 'bp' );
	wp_cache_delete( 'bp_links_link_' . $link_id, 'bp' );
}

// List actions to clear object caches on
add_action( 'bp_links_link_deleted', 'bp_links_clear_link_object_cache' );
add_action( 'bp_links_settings_updated', 'bp_links_clear_link_object_cache' );
add_action( 'bp_links_details_updated', 'bp_links_clear_link_object_cache' );
add_action( 'bp_links_link_avatar_updated', 'bp_links_clear_link_object_cache' );
add_action( 'bp_links_cast_vote_success', 'bp_links_clear_link_object_cache' );

// List actions to clear super cached pages on, if super cache is installed
add_action( 'bp_links_new_wire_post', 'bp_core_clear_cache' );
add_action( 'bp_links_deleted_wire_post', 'bp_core_clear_cache' );
add_action( 'bp_links_details_updated', 'bp_core_clear_cache' );
add_action( 'bp_links_settings_updated', 'bp_core_clear_cache' );
add_action( 'bp_links_create_complete', 'bp_core_clear_cache' );
add_action( 'bp_links_created_link', 'bp_core_clear_cache' );
add_action( 'bp_links_link_avatar_updated', 'bp_core_clear_cache' );
add_action( 'bp_links_cast_vote_success', 'bp_core_clear_cache' );

?>