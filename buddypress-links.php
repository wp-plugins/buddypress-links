<?php
/*
Plugin Name: BuddyPress Links
Plugin URI: http://wordpress.org/extend/plugins/buddypress-links/
Description: BuddyPress Links is a link sharing component for BuddyPress.
Author: Marshall Sorenson (MrMaz)
Author URI: http://buddypress.org/developers/mrmaz/
License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
Version: 0.1.1
Text Domain: buddypress-links
Site Wide Only: false
*/

//
// You can override the following constants in
// wp-config.php if you feel the need to.
//

// Define the slug for the component
// At this time, it is not recommended that you try to change this!
if ( !defined( 'BP_LINKS_SLUG' ) )
	define ( 'BP_LINKS_SLUG', 'links' );

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


/////////
// Important Internal Constants
// *** DO NOT MODIFY THESE ***
define ( 'BP_LINKS_IS_INSTALLED', 1 );
define ( 'BP_LINKS_VERSION', '0.1.1' );
define ( 'BP_LINKS_DB_VERSION', '2' );
define ( 'BP_LINKS_PLUGIN_NAME', 'buddypress-links' );
define ( 'BP_LINKS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . BP_LINKS_PLUGIN_NAME );
/////////

// lets do it
require_once 'bp-links.php';
?>
