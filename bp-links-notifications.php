<?php

function bp_links_notification_new_wire_post( $link_id, $wire_post_id ) {
	global $bp;
	
	$link = new BP_Links_Link( $link_id );
	$wire_post = new BP_Wire_Post( $bp->links->table_name_wire, $wire_post_id );
	
	if ( $link->user_id == $wire_post->user_id || 'no' == get_usermeta( $link->user_id, 'notification_links_wire_post' ) )
		return;

	$poster_name = bp_core_get_user_displayname( $wire_post->user_id );
	$poster_profile_link = bp_core_get_user_domain( $wire_post->user_id ); 

	$subject = '[' . get_blog_option( BP_ROOT_BLOG, 'blogname' ) . '] ' . sprintf( __( 'New wire post on link: %1$s', 'buddypress-links' ), stripslashes( attribute_escape( $link->name ) ) );

	$ud = get_userdata( $link->user_id );

	// Set up and send the message
	$wire_link = site_url( $bp->links->slug . '/' . $link->slug . '/wire/' );
	$link_link = site_url( $bp->links->slug . '/' . $link->slug . '/' );
	$settings_link = bp_core_get_user_domain( $link->user_id ) . 'settings/notifications/';

	$message = sprintf( __(
'%s posted on the wire of the link "%s":

"%s"

To view the link wire: %s

To view the link home: %s

To view %s\'s profile page: %s

---------------------
', 'buddypress-links' ), $poster_name, stripslashes( attribute_escape( $link->name ) ), stripslashes($wire_post->content), $wire_link, $link_link, $poster_name, $poster_profile_link );

	$message .= sprintf( __( 'To disable these notifications please log in and go to: %1$s', 'buddypress-links' ), $settings_link );

	// Send it
	wp_mail( $ud->user_email, $subject, $message );

	unset( $message, $ud->user_email );
}
?>