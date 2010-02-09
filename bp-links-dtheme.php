<?php
/* 
 * Display functions that are specific to the bp-links-default theme
 */

function bp_links_is_default_theme() {
	return ( BP_LINKS_THEME == BP_LINKS_DEFAULT_THEME );
}

//
// Template Actions / Filters
//

function bp_links_add_js() {
	global $bp;

	if ( !bp_links_is_default_theme() )
		return false;

	if ( $bp->current_component == $bp->links->slug ) {
		// load global ajax scripts
		wp_enqueue_script( 'bp-links-ajax', BP_LINKS_THEME_URL . '/_inc/global.js', array('jquery') );
		// load color box JS if single item
		wp_enqueue_script( 'bp-links-ajax-colorbox', BP_LINKS_THEME_URL . '/_inc/jquery.colorbox-min.js', array('jquery') );
		// load create forms ajax scripts if necessary
		if ( $bp->current_action == 'create' || bp_links_is_link_admin_page() ) {
			wp_enqueue_script( 'bp-links-ajax-forms', BP_LINKS_THEME_URL . '/_inc/forms.js', array('jquery') );
		}
	}
}
add_action( 'wp', 'bp_links_add_js');

function bp_links_add_css() {

	if ( !bp_links_is_default_theme() )
		return false;

	if ( $bp->current_component == $bp->links->slug ) {
		wp_enqueue_style( 'bp-links-screen', BP_LINKS_THEME_URL . '/style.css' );
	}
}
add_action( 'wp_print_styles', 'bp_links_add_css' );

function bp_links_dtheme_header_nav_setup() {
	global $bp;

	if ( !bp_links_is_default_theme() )
		return false;

	$selected = ( bp_is_page( BP_LINKS_SLUG ) ) ? ' class="selected"' : '';
	$title = __( 'Links', 'buddypress-links' );

	echo sprintf('<li%s><a href="%s/%s" title="%s">%s</a></li>', $selected, get_option('home'), BP_LINKS_SLUG, $title, $title );
}
add_action( 'bp_nav_items', 'bp_links_dtheme_header_nav_setup');

function bp_links_dtheme_activity_type_tabs_setup() {
	global $bp;

	if ( !bp_links_is_default_theme() )
		return false;

	if ( is_user_logged_in() && bp_links_total_links_for_user( bp_loggedin_user_id() ) ) {
		echo sprintf(
			'<li id="activity-links"><a href="%s" title="%s">%s</a></li>',
			site_url( BP_ACTIVITY_SLUG . '/#links/' ),
			__( 'The activity of links I created.', 'buddypress-links' ),
			sprintf(
				__( 'My Links (%s)', 'buddypress-links' ),
				bp_links_total_links_for_user( bp_loggedin_user_id() )
			)
		);
	}
}
add_action( 'bp_before_activity_type_tab_mentions', 'bp_links_dtheme_activity_type_tabs_setup' );

function bp_links_dtheme_activity_filter_options_setup() {

	if ( !bp_links_is_default_theme() )
		return false;
	
	echo sprintf( '<option value="created_link">%s</option>', __( 'Show Created Link', 'buddypress-links' ) );
	echo sprintf( '<option value="voted_on_link">%s</option>', __( 'Show Voted on Link', 'buddypress-links' ) );
}
add_action( 'bp_activity_filter_options', 'bp_links_dtheme_activity_filter_options_setup' );

function bp_links_dtheme_screen_notification_settings() {

	if ( !bp_links_is_default_theme() )
		return false;
	
	echo bp_links_notification_settings();
}
add_action( 'bp_notification_settings', 'bp_links_dtheme_screen_notification_settings' );

//
// Template Tags
//

function bp_links_dtheme_search_form() {
	global $bp; ?>
	<form action="" method="get" id="search-links-form">
		<label><input type="text" name="s" id="links_search" value="<?php if ( isset( $_GET['s'] ) ) { echo attribute_escape( $_GET['s'] ); } else { _e( 'Search anything...', 'buddypress-links' ); } ?>"  onfocus="if (this.value == '<?php _e( 'Search anything...', 'buddypress-links' ) ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e( 'Search anything...', 'buddypress-links' ) ?>';}" /></label>
		<input type="submit" id="links_search_submit" name="links_search_submit" value="<?php _e( 'Search', 'buddypress-links' ) ?>" />
	</form>
<?php
}

function bp_links_dtheme_creation_tabs() {
	global $bp;

	$href = sprintf( '%s/%s/create/', $bp->root_domain, $bp->links->slug );
?>
	<li class="current"<a href="<?php echo $href ?>"><?php _e( 'Create', 'buddypress-links' ) ?></a></li>
	<li><a href="<?php echo $href ?>"><?php _e( 'Start Over', 'buddypress-links' ) ?></a></li>
<?php
	do_action( 'bp_links_dtheme_creation_tabs' );
}

//
// AJAX Actions and Filters
//

/**
 * Helper function to return selected category cookie
 */
function bp_links_dtheme_selected_category() {
	if ( isset( $_COOKIE['bp-links-extras'] ) && preg_match('/^category-\d$/', $_COOKIE['bp-links-extras'] ) ) {
		$parts = split( '-', $_COOKIE['bp-links-extras'] );
		if ( $parts[1] > 0 ) {
			return $parts[1];
		}
	}

	return null;
}

/**
 * Links Directory Hook
 */
function bp_links_dtheme_template_loader() {
	bp_links_locate_template( array( 'links-loop.php' ), true );
}
add_action( 'wp_ajax_links_filter', 'bp_links_dtheme_template_loader' );

/**
 * Filter all AJAX bp_filter_request() calls for the 'links' object
 *
 * @param string $query_string
 * @param string $object
 * @param string $filter
 * @param string $scope
 * @param integer $page
 * @param string $search_terms
 * @param string $extras
 * @return string
 */
function bp_links_dtheme_ajax_querystring_content_filter( $query_string, $object, $filter, $scope, $page, $search_terms, $extras ) {
	global $bp;

	if ( $bp->links->slug != $bp->current_component || 'links' != $object )
		return $query_string;

	$selected_category = bp_links_dtheme_selected_category();

	if ( !empty( $selected_category ) ) {
		$args = array();
		parse_str( $query_string, $args );
		$args['category'] = $selected_category;
		return http_build_query( $args );
	}

	return $query_string;
}
add_filter( 'bp_dtheme_ajax_querystring', 'bp_links_dtheme_ajax_querystring_content_filter', 1, 7 );

/**
 * Filter all AJAX bp_activity_request() calls for the 'activity' object with the 'links' scope
 *
 * @param string $query_string
 * @param string $object
 * @param string $filter
 * @param string $scope
 * @param integer $page
 * @param string $search_terms
 * @param string $extras
 * @return string
 */
function bp_links_dtheme_ajax_querystring_activity_filter( $query_string, $object, $filter, $scope, $page, $search_terms, $extras ) {
	global $bp;

	if ( 'activity' != $object )
		return $query_string;

	if ( $bp->links->id != $bp->current_component ) {
		if ( $bp->activity->id != $bp->current_component && 'links' != $scope ) {
			return $query_string;
		}
	}

	// parse query string
	$args = array();
	parse_str( $query_string, $args );

	// override with links object
	$args['object'] = $bp->links->id;

	// set primary id to current link id if applicable
	if ( $bp->links->current_link ) {
		$args['primary_id'] = $bp->links->current_link->id;
	}

	// return modified query string
	return http_build_query( $args );
}
add_filter( 'bp_dtheme_ajax_querystring', 'bp_links_dtheme_ajax_querystring_activity_filter', 1, 7 );

/**
 * Return "my links" feed URL on activity home page
 *
 * @param string $feed_url
 * @param string $scope
 * @return string
 */
function bp_links_dtheme_activity_feed_url( $feed_url, $scope ) {
	global $bp;

	if ( empty( $scope ) || $scope != $bp->links->id )
		return $feed_url;

	return $bp->loggedin_user->domain . BP_ACTIVITY_SLUG . '/my-links/feed/';
}
add_filter( 'bp_dtheme_activity_feed_url', 'bp_links_dtheme_activity_feed_url', 11, 2 );

/**
 * Handle creating a custom update to a Link
 *
 * @param string $object
 * @param integer $item_id
 * @param string $content
 * @return integer|false Activity id that was created
 */
function bp_links_dtheme_activity_custom_update( $object, $item_id, $content ) {
	// object MUST be links
	if ( 'links' == $object ) {
		return bp_links_post_update( array( 'link_id' => $item_id, 'content' => $content ) );
	} else {
		return false;
	}
}
add_filter( 'bp_activity_custom_update', 'bp_links_dtheme_activity_custom_update', 10, 3 );

?>
