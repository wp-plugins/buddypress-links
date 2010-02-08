<?php

/* Register widgets for links component */
function bp_links_register_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("BP_Links_Widget");') );
}
add_action( 'bp_init', 'bp_links_register_widgets' );

/*** LINKS WIDGET *****************/

class BP_Links_Widget extends WP_Widget {
	function bp_links_widget() {
		parent::WP_Widget( false, __( 'Links', 'buddypress-links' ), array( 'description' => __( 'Your BuddyPress Links', 'buddypress-links' ) ) );

		if ( is_active_widget( false, false, $this->id_base ) ) {
			wp_enqueue_script( 'bp-links-ajax', get_stylesheet_directory_uri() . '/links/_inc/js/ajax.js' );
			wp_enqueue_script( 'bp-links-widget-link-list', get_stylesheet_directory_uri() . '/links/_inc/js/widgets.js', array('jquery', 'jquery-livequery-pack') );
			wp_enqueue_style( 'bp-links-widget-members', get_stylesheet_directory_uri() . '/links/_inc/css/widgets.css' );
		}
	}

	function widget($args, $instance) {
		global $bp;
		
	    extract( $args );
		
		echo $before_widget;
		echo $before_title
		   . '<div class="name">' . $widget_name . '</div>'
		   . ' <a class="rss-image" href="' . bp_get_directory_links_feed_link() . '" title="' . __( 'Most Recent Links RSS Feed', 'buddypress-links' ) . '">' . __( '[RSS]', 'buddypress-links' ) . '</a>'
		   . $after_title; ?>
		
		<?php if ( bp_has_site_links( 'type=most-popular&per_page=' . $instance['max_links'] . '&max=' . $instance['max_links'] ) ) : ?>
			<div class="item-options" id="link-list-options">
				<span class="ajax-loader" id="ajax-loader-links"></span>
				<a href="<?php echo site_url() . '/' . $bp->links->slug ?>" id="newest-links"><?php _e("Newest", 'buddypress-links') ?></a> |
				<a href="<?php echo site_url() . '/' . $bp->links->slug ?>" id="recently-active-links"><?php _e("Active", 'buddypress-links') ?></a> |
				<a href="<?php echo site_url() . '/' . $bp->links->slug ?>" id="most-votes"><?php _e("Votes", 'buddypress-links') ?></a> |
				<a href="<?php echo site_url() . '/' . $bp->links->slug ?>" id="high-votes"><?php _e("Rating", 'buddypress-links') ?></a> |
				<a href="<?php echo site_url() . '/' . $bp->links->slug ?>" id="popular-links" class="selected"><?php _e("Popular", 'buddypress-links') ?></a>
			</div>
			<?php wp_nonce_field( 'bp_links_widget_links_list', '_wpnonce-links' ); ?>
			<input type="hidden" name="links_widget_max" id="links_widget_max" value="<?php echo attribute_escape( $instance['max_links'] ); ?>" />
			<input type="hidden" name="links_avatar_size" id="links_avatar_size" value="<?php echo attribute_escape( $instance['avatar_size'] ); ?>" />

			<?php bp_link_list( array('avatar_size' => $instance['avatar_size'] ) ) ?>
			
		<?php else: ?>

			<div class="widget-error">
				<?php _e('There are no links to display.', 'buddypress-links') ?>
			</div>

		<?php endif; ?>
			
		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['max_links'] = strip_tags( $new_instance['max_links'] );
		$instance['avatar_size'] = strip_tags( $new_instance['avatar_size'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'max_links' => 5, 'avatar_size' => 100 ) );
		$max_links = strip_tags( $instance['max_links'] );
		$avatar_size = strip_tags( $instance['avatar_size'] );
		?>

		<p>
			<label for="bp-links-widget-links-max">
				<?php _e('Max links to show:', 'buddypress-links'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'max_links' ); ?>" name="<?php echo $this->get_field_name( 'max_links' ); ?>" type="text" value="<?php echo attribute_escape( $max_links ); ?>" style="width: 30%" />
			</label>
		</p>
		<p>
			<label for="bp-links-widget-avatar-type">
				<?php _e('Avatar Size:', 'buddypress-links'); ?>
				<select id="<?php echo $this->get_field_id( 'avatar_size' ); ?>" name="<?php echo $this->get_field_name( 'avatar_size' ); ?>">
					<?php foreach ( range(50, 130, 10) as $pixels ): ?>
						<option value="<?php echo $pixels ?>"<?php echo ( attribute_escape( $avatar_size ) == $pixels ) ? ' selected="selected"' : null; ?>><?php echo $pixels ?> Pixels</option>
					<?php endforeach; ?>
				</select>
			</label>
		</p>
	<?php
	}
}

function bp_links_ajax_widget_links_list() {
	global $bp;
		
	check_ajax_referer('bp_links_widget_links_list');

	switch ( $_POST['filter'] ) {
		case 'newest-links':
			$type = 'newest';
		break;
		case 'recently-active-links':
			$type = 'recently-active';
		break;
		case 'popular-links':
			$type = 'most-popular';
		break;
		case 'most-votes':
			$type = 'most-votes';
		break;
		case 'high-votes':
			$type = 'high-votes';
		break;
	}

	if ( bp_has_site_links( 'type=' . $type . '&per_page=' . $_POST['max_links'] . '&max=' . $_POST['max_links'] ) ) : ?>
		<?php echo "0[[SPLIT]]"; ?>
				
		<?php bp_link_list( array('avatar_size' => $_POST['avatar_size'] ) ) ?>
		
	<?php else: ?>

		<?php echo "-1[[SPLIT]]<li>" . __( 'No links matched the current filter.', 'buddypress-links'); ?>

	<?php endif;
	
}
add_action( 'wp_ajax_widget_links_list', 'bp_links_ajax_widget_links_list' );
?>
