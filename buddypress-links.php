<?php
/*
Plugin Name: BuddyPress Links
Plugin URI: http://wordpress.org/extend/plugins/buddypress-links/
Description: BuddyPress Links is a link sharing component for BuddyPress.
Author: Marshall Sorenson (MrMaz)
Author URI: http://buddypress.org/developers/mrmaz/
License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
Version: 0.3-bleeding
Text Domain: buddypress-links
Site Wide Only: false
*/

/*** Make sure BuddyPress is loaded ********************************
if ( !function_exists( 'bp_core_install' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'buddypress/bp-loader.php' ) )
		require_once ( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' );
	else
		deactivate_plugins( __FILE__, true );
}
/*******************************************************************/

//
// You can override the following constants in
// wp-config.php if you feel the need to.
//

// Define the slug for the component
// At this time, it is not recommended that you try to override this!
if ( !defined( 'BP_LINKS_SLUG' ) )
	define ( 'BP_LINKS_SLUG', 'links' );

// Define the default avatar size for link lists
// Allowed values are 50, 60, 70, 80, 90, 100, 110, 120, 130
// *** Widget avatar size can be customized via the admin dashboard ***
if ( !defined( 'BP_LINKS_LIST_AVATAR_SIZE' ) )
	define ( 'BP_LINKS_LIST_AVATAR_SIZE', 100 );

// The default behavior is to allow members to change their vote.
// Override this constant and set to false to prevent vote changing.
if ( !defined( 'BP_LINKS_VOTE_ALLOW_CHANGE' ) )
	define( 'BP_LINKS_VOTE_ALLOW_CHANGE', true );

// The default behavior is to record vote activity (if it is their original vote).
// Override this constant and set to false to prevent ANY vote activity recording.
if ( !defined( 'BP_LINKS_VOTE_RECORD_ACTIVITY' ) )
	define( 'BP_LINKS_VOTE_RECORD_ACTIVITY', true );

// The following three constants are used by the create/edit link validation
// code to limit the number of characters allowed for url, name and description.
// Any value over 255 (varchar limit) for url and name will be truncated by MySQL. UTF8 string
// lengths are supported if your PHP install has mbstring support enabled.
if ( !defined( 'BP_LINKS_MAX_CHARACTERS_URL' ) )
	define( 'BP_LINKS_MAX_CHARACTERS_URL', 255 );
if ( !defined( 'BP_LINKS_MAX_CHARACTERS_NAME' ) )
	define( 'BP_LINKS_MAX_CHARACTERS_NAME', 125 );
if ( !defined( 'BP_LINKS_MAX_CHARACTERS_DESCRIPTION' ) )
	define( 'BP_LINKS_MAX_CHARACTERS_DESCRIPTION', 500 );

//
// If you have a Fotoglif account you may want to change this so
// you get credit for any revenue generated from embedded images.
//
// If you leave this like it is, I will get the credit, which is an
// easy way for you to support the continued development of this plugin :)
if ( !defined( 'BP_LINKS_EMBED_FOTOGLIF_PUBID' ) )
	define( 'BP_LINKS_EMBED_FOTOGLIF_PUBID', 'ncnz5fx9z1h9' );

/**
 * Handle plugin loading
 */
function bp_links_autoloader() {

	// Define the active theme for this install
	if ( !defined( 'BP_LINKS_THEME' ) ) {
		if ( 'bp-default' == get_blog_option( BP_ROOT_BLOG, 'stylesheet' ) ) {
			define ( 'BP_LINKS_THEME', 'bp-links-default' );
		} else {
			define ( 'BP_LINKS_THEME', 'bp-links-custom' );
		}
	}

	/////////
	// Important Internal Constants
	// *** DO NOT MODIFY THESE ***
	define ( 'BP_LINKS_VERSION', '0.3' );
	define ( 'BP_LINKS_DB_VERSION', '4' );
	define ( 'BP_LINKS_PLUGIN_NAME', 'buddypress-links' );
	define ( 'BP_LINKS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . BP_LINKS_PLUGIN_NAME );
	define ( 'BP_LINKS_PLUGIN_URL', WP_PLUGIN_URL . '/' . BP_LINKS_PLUGIN_NAME );
	define ( 'BP_LINKS_THEMES_DIR', BP_LINKS_PLUGIN_DIR . '/themes' );
	define ( 'BP_LINKS_THEMES_URL', BP_LINKS_PLUGIN_URL . '/themes' );
	define ( 'BP_LINKS_THEME_DIR', BP_LINKS_THEMES_DIR . '/' . BP_LINKS_THEME );
	define ( 'BP_LINKS_THEME_URL', BP_LINKS_THEMES_URL . '/' . BP_LINKS_THEME );
	/////////

	// ignition, start
	require_once 'bp-links.php';
}

//
// Hook into BuddyPress!
//
if ( defined( 'BP_VERSION' ) ) {
	bp_links_autoloader();
} else {
	add_action( 'bp_init', 'bp_links_autoloader' );
}

?>
