<?php
/*
Plugin Name: BuddyPress Links
Plugin URI: http://wordpress.org/extend/plugins/buddypress-links/
Description: BuddyPress Links is a link sharing component for BuddyPress.
Author: Marshall Sorenson (MrMaz)
Author URI: http://buddypress.org/developers/mrmaz/
License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
Version: 0.3
Text Domain: buddypress-links
Site Wide Only: false
*/

//
// You can override the following constants in
// wp-config.php if you feel the need to.
//
// ***** DO NOT EDIT THIS FILE *****
//

// Define the slug for the component
// At this time, it is not recommended that you try to override this!
if ( !defined( 'BP_LINKS_SLUG' ) )
	define( 'BP_LINKS_SLUG', 'links' );

// Define a custom theme name to completely bypass any core links themes
// For example, if your active WordPress theme is 'bluesky', and you wanted
// to define your links theme as 'links-custom', you would put your files in:
// /../../wp-content/themes/bluesky/links-custom
if ( !defined( 'BP_LINKS_CUSTOM_THEME' ) ) {
	define( 'BP_LINKS_CUSTOM_THEME', false );
}

// Define the default avatar size for link lists
// Allowed values are 50, 60, 70, 80, 90, 100, 110, 120, 130
// *** Widget avatar size can be customized via the admin dashboard ***
if ( !defined( 'BP_LINKS_LIST_AVATAR_SIZE' ) )
	define( 'BP_LINKS_LIST_AVATAR_SIZE', 100 );

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


////////////////////////////////
// Important Internal Constants
// *** DO NOT MODIFY THESE ***

// Configuration
define( 'BP_LINKS_VERSION', '0.3' );
define( 'BP_LINKS_DB_VERSION', '4' );
define( 'BP_LINKS_PLUGIN_NAME', 'buddypress-links' );
define( 'BP_LINKS_THEMES_PATH', 'themes' );
define( 'BP_LINKS_DEFAULT_THEME', 'bp-links-default' );
define( 'BP_LINKS_ADMIN_THEME', 'bp-links-admin' );
define( 'BP_LINKS_ACTIVITY_ACTION_CREATE', 'bp_link_create' );
define( 'BP_LINKS_ACTIVITY_ACTION_VOTE', 'bp_link_vote' );
define( 'BP_LINKS_ACTIVITY_ACTION_COMMENT', 'bp_link_comment' );

// Core Paths
define( 'BP_LINKS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . BP_LINKS_PLUGIN_NAME );
define( 'BP_LINKS_PLUGIN_URL', WP_PLUGIN_URL . '/' . BP_LINKS_PLUGIN_NAME );

// Sub Paths
define( 'BP_LINKS_THEMES_DIR', BP_LINKS_PLUGIN_DIR . '/' . BP_LINKS_THEMES_PATH );
define( 'BP_LINKS_THEMES_URL', BP_LINKS_PLUGIN_URL . '/' . BP_LINKS_THEMES_PATH );
define( 'BP_LINKS_ADMIN_THEME_DIR', BP_LINKS_THEMES_DIR . '/' . BP_LINKS_ADMIN_THEME );
define( 'BP_LINKS_ADMIN_THEME_URL', BP_LINKS_THEMES_URL . '/' . BP_LINKS_ADMIN_THEME );
define( 'BP_LINKS_ADMIN_THEME_URL_INC', BP_LINKS_ADMIN_THEME_URL . '/_inc' );

// ***************************
///////////////////////////////


//
// Plugin Bootstrap Functions
//

/**
 * Set up root component
 */
function bp_links_setup_root_component() {
	// Register 'links' as a root component
	bp_core_add_root_component( BP_LINKS_SLUG );
}

/**
 * Set up globals
 */
function bp_links_setup_globals() {
	global $bp, $wpdb;

	/* For internal identification NEVER, EVER, CHANGE THIS */
	$bp->links->id = 'links';

	$bp->links->table_name = $wpdb->base_prefix . 'bp_links';
	$bp->links->table_name_categories = $wpdb->base_prefix . 'bp_links_categories';
	$bp->links->table_name_votes = $wpdb->base_prefix . 'bp_links_votes';
	$bp->links->table_name_linkmeta = $wpdb->base_prefix . 'bp_links_linkmeta';
	$bp->links->format_notification_function = 'bp_links_format_notifications';
	$bp->links->slug = BP_LINKS_SLUG;

	/* Register this in the active components array */
	$bp->active_components[$bp->links->slug] = $bp->links->id;

	$bp->links->forbidden_names = apply_filters( 'bp_links_forbidden_names', array( 'links', 'my-links', 'link-finder', 'create', 'delete', 'add', 'admin', 'popular', 'most-votes', 'high-votes', 'active', 'newest', 'all', 'submit', 'feed' ) );

}

/**
 * Handle special BP initialization
 */
function bp_links_init() {
	// If we ever need to execute BP code outside of
	// any WP action or filter scope, it will go here!
	return true;
}

//
// Hook into BuddyPress!
//
if ( defined( 'BP_VERSION' ) ) {
	bp_links_setup_root_component();
	bp_links_setup_globals();
	bp_links_init();
} else {
	add_action( 'bp_setup_root_components', 'bp_links_setup_root_component' );
	add_action( 'bp_setup_globals', 'bp_links_setup_globals' );
	add_action( 'bp_init', 'bp_links_init' );
}

//
// Load everything
//
require_once 'bp-links-core.php';

?>
