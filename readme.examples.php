<?php
/**
 * Here are some example functions for modifying the behavior of buddypress-links.
 * Put them in functions.php of your theme, or someplace else OUTSIDE of this plugin's dir.
 */

// just in case
die('go away, bad guy!');

/**
 * This hook will not allow members to change their vote
 */
function my_no_change_vote_func( $default ) {
	return false;
}
add_filter( 'bp_links_cast_vote_allow_change', 'my_no_change_vote_func' );

/**
 * This hook will prevent vote activity from being recorded in SWA
 */
function my_no_record_vote_activity_func( $is_first_vote ) {
	return false;
}
add_filter( 'bp_links_cast_vote_record_activity', 'my_no_record_vote_activity_func' );

?>
