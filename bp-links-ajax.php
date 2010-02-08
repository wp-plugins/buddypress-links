<?php
/***
 * AJAX Functions
 */

/**
 * Helper function for echoing AJAX responses
 */
function bp_links_ajax_response_string() {
	$args = func_get_args();
	echo join( '[[split]]', $args );
	die();
}

/**
 * Handle voting on a link
 */
function bp_links_ajax_link_vote() {
	global $bp;

	if ( ( $bp->loggedin_user->id ) && ( check_ajax_referer( 'link_vote', false, false ) ) ) {

		$link = bp_links_cast_vote( $_REQUEST['link_id'], substr( $_REQUEST['up_or_down'], 0, 4 ) );

		if ( !empty( $link ) ) {
			if ( $link instanceof BP_Links_Link ) {
				bp_links_ajax_response_string( 1, __( 'Vote recorded.', 'buddypress-links' ), sprintf( '%1$+d', $link->vote_total), $link->vote_count );
			} else {
				bp_links_ajax_response_string( 0, __( 'You have already voted.', 'buddypress-links' ) );
			}
		} else {
			bp_links_ajax_response_string( -1, __( 'There was a problem recording your vote. Please try again.', 'buddypress-links' ) );
		}

	} else {
		// sorry, not logged in
		bp_links_ajax_response_string( -1, __( 'You must be logged in to vote!', 'buddypress-links' ) );
	}
}
add_action( 'wp_ajax_link_vote', 'bp_links_ajax_link_vote' );

/**
 * Display auto-embed panel on the create/admin form
 */
function bp_links_ajax_link_auto_embed_url() {

	check_ajax_referer( 'bp_links_save_link-auto-embed' );

	try {
		// try to load a service
		$embed_service = BP_Links_Embed::FromUrl( $_POST['url'] );

		// did we get a rich media service?
		if ( $embed_service instanceof BP_Links_Embed_From_Url ) {
			// output response
			bp_links_ajax_response_string(
				1, // 0
				$embed_service->title(), // 1
				$embed_service->description(), // 2
				bp_get_links_auto_embed_panel_content( $embed_service ) // 3
			);
		}

		// NOT rich media, fall back to page parser
		$page_parser = BP_Links_Embed_Page_Parser::GetInstance();

		if ( $page_parser->from_url( $_POST['url'] ) ) {

			$page_title = $page_parser->title();
			$page_desc = $page_parser->description();

			if ( !empty( $page_title ) || !empty( $page_desc ) ) {
				// output response
				bp_links_ajax_response_string( 2, $page_title, $page_desc );
			}
		}

	} catch ( BP_Links_Embed_User_Exception $e ) {
		bp_links_ajax_response_string( -1, esc_html( $e->getMessage() ) );
	} catch ( Exception $e ) {
		// fall through to generic error for all other exceptions
		// TODO log these or what?
		// TODO comment out this debug line before tagging a version
//		bp_links_ajax_response_string( -1, esc_html( $e->getMessage() ) );
	}

	// if all else fails, just spit out generic warning message
	bp_links_ajax_response_string( -2, __( 'Auto-fill not available for this URL.', 'buddypress-links' ) );
}
add_action( 'wp_ajax_link_auto_embed_url', 'bp_links_ajax_link_auto_embed_url' );

/**
 * Return lightbox content for a link
 */
function bp_links_ajax_link_lightbox() {
	global $bp;

	if ( !empty( $_POST['link_id'] ) && is_numeric( $_POST['link_id'] ) ) {

		$link = new BP_Links_Link( (int) $_POST['link_id'] );

		if ( $link instanceof BP_Links_Link && $link->embed() instanceof BP_Links_Embed_Has_Html ) {
			bp_links_ajax_response_string( 1, $link->embed()->html() );
		}
	}

	bp_links_ajax_response_string( -1, __( 'Invalid request', 'buddypress-links' ) );
}
add_filter( 'wp_ajax_link_lightbox', 'bp_links_ajax_link_lightbox' );

// TODO figure out a way to move these functions to the theme

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
