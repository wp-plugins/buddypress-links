<?php
/**
 * RSS2 Feed Template for displaying the most recent links.
 *
 * @package BuddyPress-Links
 */

header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
header('Status: 200 OK');
?>
<?php echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	<?php do_action('bp_directory_links_feed'); ?>
>

<channel>
	<title><?php echo get_site_option( 'site_name' ); ?> - <?php _e( 'Most Recent Links', 'buddypress-links' ) ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php echo site_url() . '/' . $bp->link->slug . '/feed' ?></link>
	<description><?php _e( 'Most Recent Links Feed', 'buddypress-links' ) ?></description>
	<pubDate><?php echo mysql2date('D, d M Y H:i:s O', bp_links_get_last_updated(), false); ?></pubDate>
	<generator>http://buddypress.org/?v=<?php echo BP_VERSION ?></generator>
	<language><?php echo get_option('rss_language'); ?></language>
	<?php do_action('bp_directory_links_feed_head'); ?>
	
	<?php if ( bp_has_site_links( 'type=most-recent&max=50' ) ) : ?>
		<?php while ( bp_site_links() ) : bp_the_site_link(); ?>
			<item>
				<guid><?php //bp_the_site_link_feed_item_guid() ?></guid>
				<title><?php bp_the_site_link_feed_item_title() ?></title>
				<link><?php bp_the_site_link_feed_item_link() ?></link>
				<pubDate><?php echo mysql2date('D, d M Y H:i:s O', bp_the_site_link_feed_item_date(), false); ?></pubDate>

				<description><?php bp_the_site_link_feed_item_description() ?></description>
			<?php do_action('bp_directory_links_feed_item'); ?>
			</item>
		<?php endwhile; ?>

	<?php endif; ?>
</channel>
</rss>