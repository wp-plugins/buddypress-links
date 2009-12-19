=== Plugin Name ===
Contributors: MrMaz
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8591311
Tags: wpmu, buddypress, social, networking, links, rich media, embed, youtube, flickr, metacafe
Requires at least: PHP 5.2, WordPress MU 2.8.4, BuddyPress 1.1
Tested up to: PHP 5.2.x, WordPress MU 2.8.4, BuddyPress 1.1.3
Stable tag: 0.2

BuddyPress Links is a drop in link and rich media sharing component for BuddyPress 1.1.x

== Description ==

#### Update!

Rich media embedding is now available in version 0.2. Currently there is
support for YouTube, Flickr, and metacafe with more to come.

#### What is BuddyPress Links?

BuddyPress Links is a drop in link and rich media sharing component for BuddyPress 1.1.x

It supports complete integration with...

>Profiles, Directory, Activity Stream, Widgets, Notifications, Admin Bar, Admin Dashboard

Members can:

* Create and manage links from their profile.
* Assign links to a category
* Control the visibility of their links (public, friends only, and hidden).
* Enable a comment wire for a link
* Upload an image "avatar" to show with a link.
* Auto embed rich media from URLs (YouTube, Flickr, and metacafe are supported)
* Embed a PicApp.com image and use as the avatar
* Embed a Fotoglif.com image and use as the avatar
* Vote on other member's links
* Comment on other member's links (if wire is enabled)

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

See it in action at http://primehockey.com
(Please do not create test accounts, thank you!)

== Screenshots ==

1. This is the directory. There is also a widget that looks almost identical, but has configurable thumbs sizes.
2. This is the home page of a link. Most people will probably want to modify the template.
3. This is the create/admin form. You can see a YouTube clip that was auto-detected.

== Installation ==

**Notice: This plugin is under heavy development, and is not recommended for production environments!**

BuddyPress Links requires WordPress MU 2.8.4 or higher with BuddyPress 1.1 or higher installed.

####Plugin:

1. Upload everything into the "/wp-content/plugins" directory of your installation.
1. Activate BuddyPress Links in the "Plugins" admin panel using the "Activate" link (both work).

####Themes:

1. Move "/wp-content/plugins/buddypress-links/links" to "/wp-content/themes/bp-default"
1. If your active theme is not the "bp-default" theme, then subsitute your theme for the above.  In this case you will have to customize the links theme to match your own theme.

####Upgrading from an earlier version:

1. There are no known backwards compatility issues.
1. Please be sure to update the "links" directory in your theme with the files from the new version.

== Upgrade Notice ==

= 0.2 =

This version contains the first support for rich media embedding. *Please make sure that you update the "links" directory in your theme (see Installation).*

== Changelog ==

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

= Where can I get support? =

The support forums can be found here: http://buddypress.org/forums

= Where can I find documentation? =

Coming soon

= Where can I report a bug? =

Look for MrMaz in #buddypress-dev
Or on buddypress.org http://buddypress.org/developers/mrmaz/