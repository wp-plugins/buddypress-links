=== Plugin Name ===
Contributors: MrMaz
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8591311
Tags: wpmu, buddypress, social, networking, links, rich media, embed, youtube, flickr, metacafe
Requires at least: PHP 5.2, WordPress MU 2.9.1, BuddyPress 1.2.x
Tested up to: PHP 5.2.x, WordPress 2.9.1, BuddyPress 1.2.x
Stable tag: 0.2.1

BuddyPress Links is a drop in link and rich media sharing component for BuddyPress 1.2.x

== Description ==

#### Update!

Rich media embedding is now available in version 0.2. Currently there is
support for YouTube, Flickr, and metacafe with more to come. As of 0.2.1 there
is now support for embedding regular web pages as rich media!

Check out my blog post, which has a screencast demonstrating all of the new features! http://marshallsorenson.com/post/rich-media-embedding-and-mashing-with-buddypress-links

#### What is BuddyPress Links?

BuddyPress Links is a drop in link and rich media sharing component for BuddyPress 1.2.x

It supports complete integration with...

>Profiles, Directory, Activity Stream, Widgets, Notifications, Admin Bar, Admin Dashboard

Members can:

* Create and manage links from their profile.
* Assign links to a category
* Control the visibility of their links (public, friends only, and hidden).
* Upload an image "avatar" to show with a link.
* Auto embed rich media from URLs (YouTube, Flickr, and metacafe are supported)
* Automatic thumbnail picker available as of 0.2.1
* Embed a PicApp.com or Fotoglif.com image and use as the avatar
* Vote on other member's links
* Comment on other member's links
* @mentions support added in version 3.0

Administrators can:

* Manage all links (modify, delete)
* Manage link categories (create, modify, delete)
* Enable and customize widgets

Other features include:

* "Digg style" popularity algorithm
* Rich profile and directory sorting and filtering
* Most recent links news feed
* Hundreds of action and filter hooks
* Full i18n support (need translators!)

See it in action at http://primehockey.com/
(Please do not create test accounts, thank you!)

== Screenshots ==

1. This is the directory. There is also a widget that looks almost identical, but has configurable thumbs sizes.
2. This is the home page of a link. Most people will probably want to modify the template.
3. This is the create/admin form. You can see a YouTube clip that was auto-detected.

== Installation ==

**Notice: This plugin is under heavy development, and is not recommended for production environments!**

BuddyPress Links 0.3.x requires WordPress 2.9.1 or higher with BuddyPress 1.2.x installed.
BuddyPress Links 0.2.x requires WordPress 2.8.4 or higher with BuddyPress 1.1.x installed.

####Plugin:

1. Upload everything into the "/wp-content/plugins" directory of your installation.
1. Activate BuddyPress Links in the "Plugins" admin panel using the "Activate" link (both work).
1. DO NOT COPY/MOVE THEME FILES TO YOUR CHILD THEME. This is no longer required as of 0.3

####Upgrading from an earlier version:

1. BACK UP ALL OF YOUR DATA.
1. The wire has been deprecated. ALL LINKS WIRE POSTS MAY BE LOST!
1. This version can use data created by previous versions, assuming you are porting your site to the new BP 1.2 default theme!

== Upgrade Notice ==

= 0.3 =

DO NOT attempt to install this version on BP 1.1.X!  DO NOT try to use this plugin with the classic theme!

= 0.2 =

This version contains the first support for rich media embedding. *Please make sure that you update the "links" directory in your theme (see Installation).*

== Changelog ==

= 0.3 =

* Baseline BuddyPress 1.2 support, REQUIRES BP 1.2-RC2 or higher
* Removed classic theme support (may re-support in the future if there is a huge demand)
* Wire support has been dropped and replaced with the activity stream
* Deep and seamless activity stream integration, complete with RSS feeds
* @mentions support, complete with e-mail notifications
* Lightbox for viewing photos and videos without leaving the site
* Moved template files to plugin dir to ease future upgrading
* Added support for template overriding from child theme
* Moved link loop item HTML from hard coded PHP to a template (links-loop-item.php)
* Added the much requested filters for link REL and TARGET
* Completely hooked into default theme AJAX (no duplicate functionality)
* Fixed several trivial bugs

= 0.2.1 =

* Added support for auto embedding standard web pages
* Added automatic thumb picker for rich web pages
* Fixed layout bug that was affecting all webkit browsers
* Some other minor bug fixes

= 0.2 =

* Added support for auto-embedding of rich media (API documentation coming soon!)
* Reduced create/admin form to one page
* Wider selection of thumb sizes for the links widget
* Many CSS improvements and fixes
* Lots of general refactoring
* Some minor bug fixes

= 0.1 =

* First beta versions
* Many, many i18n fixes
* A few bug fixes

== License ==

All original code is Copyright (c) 2009 Marshall Sorenson. All rights reserved.

Released under the GNU GENERAL PUBLIC LICENSE 3.0 (http://www.gnu.org/licenses/gpl.txt)

== Frequently Asked Questions ==

= How do I customize the default templates? =

To override only certain templates from the bp-links-default theme directory,
create a directory named "bp-links-default" in your child theme,
and replace the template using the EXACT same path AND filename.

To create a totally custom theme, create a directory called "bp-links-custom" in your child theme.
To find out which template files are required to exist, do a recursive search for 'bp_links_load_template'

= Where can I get support? =

The support forums can be found here: http://buddypress.org/forums

= Where can I find documentation? =

Coming soon

= Where can I report a bug? =

Look for MrMaz in #buddypress-dev
Or on buddypress.org http://buddypress.org/developers/mrmaz/

Please search the forums first!