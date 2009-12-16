<?php
/***
 * AJAX Functions
 */
function bp_links_ajax_response_string() {
	$args = func_get_args();
	return join( '[[split]]', $args );
}

function bp_links_ajax_link_filter() {
	check_ajax_referer( 'link-filter-box' );
	locate_template( array( 'links/link-loop.php' ), true );
}
add_action( 'wp_ajax_link_filter', 'bp_links_ajax_link_filter' );


function bp_links_ajax_directory_links() {
	check_ajax_referer('directory_links');
	locate_template( array( 'links/directory/links-loop.php' ), true );
}
add_action( 'wp_ajax_directory_links', 'bp_links_ajax_directory_links' );


function bp_links_ajax_link_vote() {
	global $bp;

	if ( ( $bp->loggedin_user->id ) && ( check_ajax_referer( 'link_vote', false, false ) ) ) {
		
		$link = bp_links_cast_vote( $_REQUEST['link_id'], substr( $_REQUEST['up_or_down'], 0, 4 ) );

		if ( !empty( $link ) ) {
			if ( $link instanceof BP_Links_Link ) {
				echo bp_links_ajax_response_string( 1, __( 'Vote recorded.', 'buddypress-links' ), sprintf( '%1$+d', $link->vote_total), $link->vote_count );
			} else {
				echo bp_links_ajax_response_string( 0, __( 'You have already voted.', 'buddypress-links' ) );
			}
		} else {
			echo bp_links_ajax_response_string( -1, __( 'There was a problem recording your vote. Please try again.', 'buddypress-links' ) );
		}

	} else {
		// sorry, not logged in
		echo bp_links_ajax_response_string( -1, __( 'You must be logged in to vote!', 'buddypress-links' ) );
		return false;
	}
}
add_action( 'wp_ajax_link_vote', 'bp_links_ajax_link_vote' );

function bp_links_ajax_link_auto_embed_url() {

	check_ajax_referer( 'bp_links_save_link-auto-embed' );

	try {
		// try to load a service
		$embed_service = BP_Links_Embed::FromUrl( $_POST['url'] );
		// did we get a service?
		if ( $embed_service instanceof BP_Links_Embed_From_Url ) {
			// output response
			echo
				bp_links_ajax_response_string(
					1, // 0
					bp_get_links_auto_embed_panel_content( $embed_service ), // 1
					$embed_service->title(), // 2
					$embed_service->description()
				);
			return;
		}
	} catch ( BP_Links_Embed_User_Exception $e ) {
		echo bp_links_ajax_response_string( -1, esc_html( $e->getMessage() ) );
		return;
	} catch ( Exception $e ) {
		// TODO log these or what?
		// fall through to generic error for all other exceptions
	}

	// if all else fails, just spit out generic error message response
	echo bp_links_ajax_response_string( -1, __( 'Failed to auto embed this URL.', 'buddypress-links' ) );
}
add_action( 'wp_ajax_link_auto_embed_url', 'bp_links_ajax_link_auto_embed_url' );
?>