<?php

function bp_links_header_tabs() {
	global $bp, $create_link_step, $completed_to_step;
?>
	<li<?php if ( !isset($bp->action_variables[0]) || 'recently-active' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->links->slug ?>/my-links/recently-active"><?php _e( 'Recently Active', 'buddypress-links' ) ?></a></li>
	<li<?php if ( 'newest' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->links->slug ?>/my-links/newest"><?php _e( 'Newest', 'buddypress-links' ) ?></a></li>
	<li<?php if ( 'most-popular' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->links->slug ?>/my-links/most-popular"><?php _e( 'Most Popular', 'buddypress-links' ) ?></a></li>
	<li<?php if ( 'most-votes' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->links->slug ?>/my-links/most-votes"><?php _e( 'Most Votes', 'buddypress-links' ) ?></a></li>
	<li<?php if ( 'high-votes' == $bp->action_variables[0] ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->links->slug ?>/my-links/high-votes"><?php _e( 'Vote Rating', 'buddypress-links' ) ?></a></li>
<?php
	do_action( 'bp_links_header_tabs' );
}

function bp_links_filter_title() {
	global $bp;
	
	$current_filter = $bp->action_variables[0];
	
	switch ( $current_filter ) {
		case 'recently-active': default:
			_e( 'Recently Active', 'buddypress-links' );
			break;
		case 'newest':
			_e( 'Newest', 'buddypress-links' );
			break;
		case 'most-popular':
			_e( 'Most Popular', 'buddypress-links' );
			break;
		case 'most-votes':
			_e( 'Most Votes', 'buddypress-links' );
			break;
		case 'high-votes':
			_e( 'Highest Votes', 'buddypress-links' );
			break;
	}
	do_action( 'bp_links_filter_title' );
}

function bp_links_category_radio_options( $selected_category_id = 1, $element_name = 'category', $element_class = '' ) {

	do_action( 'bp_before_links_category_radio_options' );
	
	// grab all categories
	$categories = BP_Links_Category::get_all();

	foreach ( $categories as $category ) {
		// populate
		$category = new BP_Links_Category( $category->category_id );
		// is this one selected?
		$selected = ( $selected_category_id == $category->id ) ? ' checked="checked"' : null;
		// has class string?
		$class_string = ( empty( $element_class ) ) ? null : sprintf( ' class="%s"', $element_class );
		// output it
		echo sprintf( '<input type="radio" name="%s" value="%d"%s%s />%s ', $element_name, $category->id, $class_string, $selected, $category->name );
	}
	// print newline
	echo PHP_EOL;

	do_action( 'bp_after_links_category_radio_options' );

}

function bp_links_category_radio_options_with_all( $selected_category_id = 1, $element_name = 'category', $element_class = '' ) {

	do_action( 'bp_before_links_category_radio_options_with_all' );

	// is this one selected?
	$selected = ( empty( $selected_category_id ) ) ? ' checked="checked"' : null;
	// has class string?
	$class_string = ( empty( $element_class ) ) ? null : sprintf( ' class="%s"', $element_class );
	// output it
	echo sprintf( '<input type="radio" name="%s" value=""%s%s />%s ', $element_name, $class_string, $selected, __( 'All', 'buddypress-links' ) );

	do_action( 'bp_after_links_category_radio_options_with_all' );

	bp_links_category_radio_options();
}

function bp_get_link_has_avatar() {
	global $bp;
	return bp_links_check_avatar( $bp->links->current_link->id );
}

function bp_link_avatar_delete_link() {
	echo bp_get_link_avatar_delete_link();
}
	function bp_get_link_avatar_delete_link() {
		global $bp;

		return apply_filters( 'bp_get_link_avatar_delete_link', wp_nonce_url( bp_get_link_permalink( $bp->links->current_link ) . '/admin/link-avatar/delete', 'bp_link_avatar_delete' ) );
	}

function bp_link_avatar_edit_form() {
	bp_links_avatar_upload();
}

function bp_get_link_wire_see_all_link( $url = null ) {
	global $bp;

	if ( $bp->current_component == $bp->links->slug ) {
		return apply_filters( 'bp_get_link_wire_see_all_link', $bp->root_domain . '/' . $bp->links->slug . '/' . $bp->current_item . '/' . $bp->wire->slug );
	} else {
		return $url;
	}
}
add_filter( 'bp_get_wire_see_all_link', 'bp_get_link_wire_see_all_link' );

/*****************************************************************************
 * User Links Template Class/Tags
 **/

class BP_Links_User_Links_Template {
	var $current_link = -1;
	var $link_count;
	var $links;
	var $link;
	
	var $in_the_loop;
	
	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_link_count;
	
	var $single_link = false;
	
	var $sort_by;
	var $order;
	
	function bp_links_user_links_template( $user_id, $type, $per_page, $max, $slug, $filter ) {
		global $bp;
		
		if ( !$user_id )
			$user_id = $bp->displayed_user->id;

		$this->pag_page = isset( $_REQUEST['lpage'] ) ? intval( $_REQUEST['lpage'] ) : 1;
		$this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		switch ( $type ) {

			case 'newest':
				$this->links = bp_links_get_newest_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'most-popular':
				$this->links = bp_links_get_most_popular_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'most-votes':
				$this->links = bp_links_get_most_votes_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'high-votes':
				$this->links = bp_links_get_high_votes_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;

			case 'single-link':
				$link = new stdClass;
				$link->link_id = BP_Links_Link::get_id_from_slug( $slug );
				$this->links = array( $link );
				break;

			case 'active':
			default:
				$this->links = bp_links_get_recently_active_for_user( $user_id, $this->pag_num, $this->pag_page, $filter );
				break;
		}
		
		if ( 'single-link' == $type ) {
			$this->single_link = true;
			$this->total_link_count = 1;
			$this->link_count = 1;
		} else {
			if ( !$max || $max >= (int)$this->links['total'] )
				$this->total_link_count = (int)$this->links['total'];
			else
				$this->total_link_count = (int)$max;

			$this->links = $this->links['links'];

			if ( $max ) {
				if ( $max >= count($this->links) )
					$this->link_count = count($this->links);
				else
					$this->link_count = (int)$max;
			} else {
				$this->link_count = count($this->links);
			}
		}

		$this->pag_links = paginate_links( array(
			'base' => add_query_arg( array( 'lpage' => '%#%', 'num' => $this->pag_num, 's' => $_REQUEST['s'], 'sortby' => $this->sort_by, 'order' => $this->order ) ),
			'format' => '',
			'total' => ceil($this->total_link_count / $this->pag_num),
			'current' => $this->pag_page,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'mid_size' => 1
		));
	}

	function has_links() {
		if ( $this->link_count )
			return true;
		
		return false;
	}
	
	function next_link() {
		$this->current_link++;
		$this->link = $this->links[$this->current_link];
			
		return $this->link;
	}
	
	function rewind_links() {
		$this->current_link = -1;
		if ( $this->link_count > 0 ) {
			$this->link = $this->links[0];
		}
	}
	
	function user_links() {
		if ( $this->current_link + 1 < $this->link_count ) {
			return true;
		} elseif ( $this->current_link + 1 == $this->link_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_links();
		}

		$this->in_the_loop = false;
		return false;
	}
	
	function the_link() {
		global $link;

		$this->in_the_loop = true;
		$this->link = $this->next_link();
		
		// If this is a single link then instantiate link meta when creating the object.
		if ( $this->single_link ) {
			if ( !$link = wp_cache_get( 'bp_links_link_' . $this->link->link_id, 'bp' ) ) {
				$link = new BP_Links_Link( $this->link->link_id, true );
				wp_cache_set( 'bp_links_link_' . $this->link->link_id, $link, 'bp' );
			}
		} else {
			if ( !$link = wp_cache_get( 'bp_links_link_nouserdata_' . $this->link->link_id, 'bp' ) ) {
				$link = new BP_Links_Link( $this->link->link_id, false, false );
				wp_cache_set( 'bp_links_link_nouserdata_' . $this->link->link_id, $link, 'bp' );
			}
		}

		$this->link = $link;
		
		if ( 0 == $this->current_link ) // loop has just started
			do_action('loop_start');
	}
}


function bp_has_links( $args = '' ) {
	global $links_template, $bp;
	
	$defaults = array(
		'type' => 'active',
		'user_id' => false,
		'per_page' => 10,
		'max' => false,
		'slug' => false,
		'filter' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
	
	// The following code will auto set parameters based on the page being viewed.
	// for example on example.com/members/marshall/links/my-links/most-popular/
	// $type = 'most-popular'
	//
	if ( 'my-links' == $bp->current_action ) {
		$order = $bp->action_variables[0];
		if ( 'newest' == $order )
			$type = 'newest';
		else if ( 'most-popular' == $order )
			$type = 'most-popular';
		else if ( 'most-votes' == $order )
			$type = 'most-votes';
		else if ( 'high-votes' == $order )
			$type = 'high-votes';
	} else if ( $bp->links->current_link->slug ) {
		$type = 'single-link';
		$slug = $bp->links->current_link->slug;
	}
	
	if ( isset( $_REQUEST['link-filter-box'] ) )
		$filter = $_REQUEST['link-filter-box'];
	
	$links_template = new BP_Links_User_Links_Template( $user_id, $type, $per_page, $max, $slug, $filter );
	return apply_filters( 'bp_has_links', $links_template->has_links(), &$links_template );
}


function bp_links() {
	global $links_template;
	return $links_template->user_links();
}

function bp_the_link() {
	global $links_template;
	return $links_template->the_link();
}

function bp_link_is_visible( $link = false ) {
	global $bp, $links_template;

	if ( !$link )
		$link =& $links_template->link;

	return bp_links_is_link_visibile( $link, $bp->loggedin_user->id );
}

function bp_link_id() {
	echo bp_get_link_id();
}
	function bp_get_link_id( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_id', $link->id );
	}

function bp_link_category_id() {
	echo bp_get_link_category_id();
}
	function bp_get_link_category_id( $link = false ) {
		global $bp, $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$category_id = $link->category_id;

		return apply_filters( 'bp_get_link_category_id', $category_id );
	}

function bp_link_category_slug() {
	echo bp_get_link_category_slug();
}
	function bp_get_link_category_slug( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$category = $link->get_category();

		return apply_filters( 'bp_get_link_category_slug', $category->slug );
	}

function bp_link_category_name() {
	echo bp_get_link_category_name();
}
	function bp_get_link_category_name( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$category = $link->get_category();

		return apply_filters( 'bp_get_link_category_name', $category->name );
	}

function bp_link_url() {
	echo bp_get_link_url();
}
	function bp_get_link_url( $link = false ) {
		global $bp, $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$url = $link->url;

		return apply_filters( 'bp_get_link_url', $url );
	}

function bp_link_url_domain() {
	echo bp_get_link_url_domain();
}
	function bp_get_link_url_domain( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$url_parts = parse_url( $link->url );

		if( isset( $url_parts['host'] ) ) {
			$domain = preg_replace( '/^www\./', '', $url_parts['host'] );
		} else {
			$domain = '';
		}

		return apply_filters( 'bp_get_link_url_domain', $domain );
	}

function bp_link_name() {
	echo bp_get_link_name();
}
	function bp_get_link_name( $link = false ) {
		global $bp, $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$name = $link->name;

		return apply_filters( 'bp_get_link_name', $name );
	}
	
function bp_link_type() {
	echo bp_get_link_type();
}
	function bp_get_link_type( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		if ( BP_Links_Link::STATUS_PUBLIC == $link->status ) {
			$type = __( 'Public Link', 'buddypress-links' );
		} else if ( BP_Links_Link::STATUS_FRIENDS == $link->status ) {
			$type = __( 'Friends Only Link', 'buddypress-links' );
		} else if ( BP_Links_Link::STATUS_HIDDEN == $link->status ) {
			$type = __( 'Hidden Link', 'buddypress-links' );
		} else {
			$type = ucwords( $link->status ) . ' ' . __( 'Link', 'buddypress-links' );
		}

		return apply_filters( 'bp_get_link_type', $type );
	}

function bp_link_avatar( $args = '', $link = null ) {
	echo bp_get_link_avatar( $args, $link );
}
	function bp_get_link_avatar( $args = '', $link = null ) {
		global $links_template;

		if ( !$link ) {
			$link = $links_template->link;
		}

		$defaults = array(
			'item_id' => $link->id
		);

		$new_args = wp_parse_args( $args, $defaults );

		return apply_filters( 'bp_get_link_avatar', bp_links_fetch_avatar( $new_args, $link ) );
	}

function bp_link_avatar_thumb( $link = false ) {
	echo bp_get_link_avatar_thumb( $link );
}
	function bp_get_link_avatar_thumb( $link = false ) {
		return bp_get_link_avatar( 'type=thumb', $link );
	}

function bp_link_avatar_mini() {
	echo bp_get_link_avatar_mini();
}
	function bp_get_link_avatar_mini( $link = false ) {
		return bp_get_link_avatar( 'type=thumb&width=30&height=30' );
	}

function bp_link_user_avatar() {
	echo bp_get_link_user_avatar();
}
	function bp_get_link_user_avatar( $args = '', $link = false ) {
		global $bp, $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$defaults = array(
			'type' => 'full',
			'width' => false,
			'height' => false,
			'class' => 'owner-avatar',
			'id' => false,
			'alt' => false
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'bp_get_link_user_avatar', bp_core_fetch_avatar( array( 'item_id' => $link->user_id, 'type' => $type, 'alt' => $alt, 'class' => $class, 'width' => $width, 'height' => $height ) ) );
	}

function bp_link_user_avatar_thumb() {
	echo bp_get_link_user_avatar_thumb();
}
	function bp_get_link_user_avatar_thumb( $link = false ) {
		return bp_get_link_user_avatar( 'type=thumb', $link );
	}

function bp_link_user_avatar_mini() {
	echo bp_get_link_user_avatar_mini();
}
	function bp_get_link_user_avatar_mini( $link = false ) {
		return bp_get_link_user_avatar( 'type=thumb&width=20&height=20', $link );
	}
	
function bp_link_last_active( $deprecated = true, $deprecated2 = false ) {
	if ( !$deprecated )
		return bp_get_link_last_active();
	else
		echo bp_get_link_last_active();
}
	function bp_get_link_last_active( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$last_active = bp_links_get_linkmeta( $link->id, 'last_activity' );

		if ( empty( $last_active ) ) {
			return __( 'not yet active', 'buddypress-links' );
		} else {
			return apply_filters( 'bp_get_link_last_active', bp_core_time_since( $last_active ) );
		}
	}

function bp_link_permalink( $deprecated = false, $deprecated2 = true ) {
	if ( !$deprecated2 )
		return bp_get_link_permalink();
	else
		echo bp_get_link_permalink();
}
	function bp_get_link_permalink( $link = false ) {
		global $links_template, $bp;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_permalink', $bp->root_domain . '/' . $bp->links->slug . '/' . $link->slug );
	}

function bp_link_wire_permalink() {
	echo bp_get_link_wire_permalink();
}
	function bp_get_link_wire_permalink( $link = false ) {
		global $bp;
		return apply_filters( 'bp_get_link_wire_permalink', bp_get_link_permalink( $link ) . '/' . $bp->wire->slug );
	}

function bp_link_userlink() {
	echo bp_get_link_userlink();
}
	function bp_get_link_userlink( $link = false ) {
		global $links_template, $bp;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_userlink', bp_core_get_userlink( $link->user_id ) );
	}

function bp_link_slug() {
	echo bp_get_link_slug();
}
	function bp_get_link_slug( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_slug', $link->slug );
	}

function bp_link_description() {
	echo bp_get_link_description();
}
	function bp_get_link_description( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_description', stripslashes($link->description) );
	}

function bp_link_description_excerpt( $deprecated = false ) {
	echo bp_get_link_description_excerpt();
}
	function bp_get_link_description_excerpt( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_description_excerpt', bp_create_excerpt( $link->description, 20 ) );
	}

function bp_link_vote_count() {
	echo bp_get_link_vote_count();
}
	function bp_get_link_vote_count( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_vote_count', $link->vote_count );
	}

function bp_link_vote_total() {
	echo bp_get_link_vote_total();
}
	function bp_get_link_vote_total( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_vote_total', $link->vote_total );
	}

function bp_link_popularity() {
	echo bp_get_link_popularity();
}
	function bp_get_link_popularity( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_popularity', $link->popularity );
	}

function bp_link_date_created( $deprecated = false ) {
	echo bp_get_link_date_created();
}
	function bp_get_link_date_created( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_date_created', date( get_option( 'date_format' ), $link->date_created ) );
	}

function bp_link_time_elapsed_text() {
	echo bp_get_link_time_elapsed_text();
}
	function bp_get_link_time_elapsed_text( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$time_elapsed = time() - $link->date_created;

		if( $time_elapsed > 86400 ) {
			// at least one day old
			$ret_number = floor( $time_elapsed / 86400 );
			$ret_text = ( $ret_number > 1 ) ? __( '%1$d days ago', 'buddypress-links' ) : __( '%1$d day ago', 'buddypress-links' );
		} elseif( $time_elapsed > 3600 ) {
			// at least one hour old
			$ret_number = floor( $time_elapsed / 60 / 60 );
			$ret_text = ( $ret_number > 1 ) ? __( '%1$d hours ago', 'buddypress-links' ) : __( '%1$d hour ago', 'buddypress-links' );
		} elseif( $time_elapsed > 60 ) {
			// at least one minute hold
			$ret_number = floor( $time_elapsed / 60 );
			$ret_text = ( $ret_number > 1 ) ? __( '%1$d minutes ago', 'buddypress-links' ) : __( '%1$d minute ago', 'buddypress-links' );
		} else {
			// only seconds old
			$ret_number = $time_elapsed;
			$ret_text = ( $ret_number > 1 ) ? __( '%1$d seconds ago', 'buddypress-links' ) : __( '%1$d second ago', 'buddypress-links' );
		}

		return apply_filters( 'bp_get_link_time_elapsed_text', sprintf( $ret_text, $ret_number ) );
	}

function bp_link_wire_count() {
	echo bp_get_link_wire_count();
}
	function bp_get_link_wire_count( $link = false ) {
		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		return apply_filters( 'bp_get_link_wire_count', $link->get_wire_count() );
	}

function bp_link_play_button( $link = false ) {
	echo bp_get_link_play_button( $link );
}
	function bp_get_link_play_button( $link = false ) {

		global $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$button_html = null;

		if ( $link->embed_status_enabled() ) {

			$class = null;

			if ( $link->embed()->avatar_play_video() === true )
				$class = 'link-play-video';
			elseif ( $link->embed()->avatar_play_photo() === true )
				$class = 'link-play-photo';

			if ( $class )
				$button_html = sprintf( '<a href="%s" class="%s"></a>', bp_get_link_permalink( $link ), $class );
		}

		return apply_filters( 'bp_get_link_play_button', $button_html );
	}

function bp_link_is_admin() {
	global $bp;
	
	return $bp->is_item_admin;
}

// this is for future use
function bp_link_is_mod() {
	global $bp;
	
	return $bp->is_item_mod;
}
		
function bp_link_search_form() {
	global $links_template, $bp;

	$action = $bp->displayed_user->domain . $bp->links->slug . '/my-links/search/';
	$label = __( 'Filter links', 'buddypress-links' );
	$name = 'link-filter-box';
?>
	<form action="<?php echo $action ?>" id="link-search-form" method="post">
		<label for="<?php echo $name ?>" id="<?php echo $name ?>-label"><?php echo $label ?></label>
		<input type="search" name="<?php echo $name ?>" id="<?php echo $name ?>" value="<?php echo $value ?>"<?php echo $disabled ?> />
	
		<?php wp_nonce_field( 'link-filter-box', '_wpnonce_link_filter' ) ?>
	</form>
<?php
}

function bp_link_show_no_links_message() {
	global $bp;
	
	if ( !bp_links_total_links_for_user( $bp->displayed_user->id ) )
		return true;
		
	return false;
}

function bp_link_pagination() {
	echo bp_get_link_pagination();
}

function bp_get_link_pagination() {
	global $links_template;
	
	return apply_filters( 'bp_get_link_pagination', $links_template->pag_links );
}

function bp_link_pagination_count() {
	global $bp, $links_template;

	$from_num = intval( ( $links_template->pag_page - 1 ) * $links_template->pag_num ) + 1;
	$to_num = ( $from_num + ( $links_template->pag_num - 1 ) > $links_template->total_link_count ) ? $links_template->total_link_count : $from_num + ( $links_template->pag_num - 1) ;

	echo sprintf( __( 'Viewing link %1$d to %2$d (of %3$d links)', 'buddypress-links' ), $from_num, $to_num, $links_template->total_link_count ); ?> &nbsp;
	<span class="ajax-loader"></span><?php 
}

function bp_total_link_count() {
	echo bp_get_total_link_count();
}
	function bp_get_total_link_count() {
		global $links_template;
		return apply_filters( 'bp_get_total_link_count', $links_template->total_link_count );
	}

function bp_link_is_wire_enabled( $link = false ) {
	global $links_template;

	if ( $link )
		return (bool) $link->enable_wire;
	else
		return (bool) $links_template->link->enable_wire;
}

function bp_link_admin_tabs( $link = false ) {
	global $bp, $links_template;

	if ( !$link )
		$link = ( $links_template->link ) ? $links_template->link : $bp->links->current_link;
	
	$current_tab = $bp->action_variables[0];
?>
	<?php if ( $bp->is_item_admin ) { ?>
		<li<?php if ( 'edit-details' == $current_tab || empty( $current_tab ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->links->slug ?>/<?php echo $link->slug ?>/admin/edit-details"><?php _e( 'Edit Details', 'buddypress-links' ) ?></a></li>
	<?php } ?>
	
	<?php
		if ( !$bp->is_item_admin )
			return false;
	?>
	<li<?php if ( 'link-avatar' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->links->slug ?>/<?php echo $link->slug ?>/admin/link-avatar"><?php _e( 'Link Avatar', 'buddypress-links' ) ?></a></li>

	<?php do_action( 'bp_links_admin_tabs', $current_tab, $link->slug ) ?>
	
	<li<?php if ( 'delete-link' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->links->slug ?>/<?php echo $link->slug ?>/admin/delete-link"><?php _e( 'Delete Link', 'buddypress-links' ) ?></a></li>
<?php
}

function bp_link_status_message( $link = false ) {
	global $links_template;
	
	if ( !$link )
		$link =& $links_template->link;
	
	if ( BP_Links_Link::STATUS_HIDDEN == $link->status ) {
		_e( 'This is a hidden link. Only the user who owns it can view it.', 'buddypress-links' );
	} elseif ( BP_Links_Link::STATUS_FRIENDS == $link->status ) {
		_e( 'This is a friends only link. Only the owner\'s friends can view it.', 'buddypress-links' );
	} else {
		_e( 'You do not have permission to access this link.', 'buddypress-links' );
	}
}

/***************************************************************************
 * Link Creation Process Template Tags
 **/

function bp_link_details_form_action() {
	echo bp_get_link_details_form_action();
}
	function bp_get_link_details_form_action() {
		global $bp;

		if ( bp_links_current_link_exists() ) {
			$form_action = bp_get_link_admin_form_action();
		} else {
			$form_action = $bp->loggedin_user->domain . $bp->links->slug . '/create';
		}

		return apply_filters( 'bp_get_link_details_form_action', $form_action, $admin_action );
	}

function bp_link_details_form_link_url_readonly() {
	echo bp_get_link_details_form_link_url_readonly();
}
	function bp_get_link_details_form_link_url_readonly() {
		global $bp;

		if ( isset( $_POST['link-url-readonly'] ) ) {
			return ( empty( $_POST['link-url-readonly'] ) ) ? 0 : 1;
		} elseif ( bp_links_current_link_embed_enabled() )  {
			return ( bp_links_current_link_embed_service() instanceof BP_Links_Embed_From_Url ) ? 1 : 0;
		} else {
			return 0;
		}
	}

function bp_link_details_form_name_desc_fields_display() {
	echo bp_get_link_details_form_name_desc_fields_display();
}
	function bp_get_link_details_form_name_desc_fields_display() {
		global $bp;

		if ( isset( $_POST['link-url-embed-data'] ) ) {
			return ( !empty( $_POST['link-url-embed-data'] ) && empty( $_POST['link-url-embed-edit-text'] ) ) ? 0 : 1;
		} elseif ( bp_links_current_link_embed_enabled() )  {
			return ( bp_links_current_link_embed_service() instanceof BP_Links_Embed_From_Url ) ? 0 : 1;
		} else {
			return 0;
		}
	}

function bp_link_details_form_avatar_fields_display() {
	echo bp_get_link_details_form_avatar_fields_display();
}
	function bp_get_link_details_form_avatar_fields_display() {
		return ( empty( $_POST['link-avatar-fields-display'] ) ) ? 0 : 1;
	}

function bp_link_details_form_avatar_option() {
	echo bp_get_link_details_form_avatar_option();
}
	function bp_get_link_details_form_avatar_option() {
		return ( empty( $_POST['link-avatar-option'] ) ) ? 0 : 1;
	}

function bp_link_details_form_settings_fields_display() {
	echo bp_get_link_details_form_settings_fields_display();
}
	function bp_get_link_details_form_settings_fields_display() {
		return ( empty( $_POST['link-settings-fields-display'] ) ) ? 0 : 1;
	}

function bp_link_details_form_category_id() {
	echo bp_get_link_details_form_category_id();
}
	function bp_get_link_details_form_category_id() {
		global $bp;

		if ( !empty( $_POST['link-category'] ) ) {
			$category_id = $_POST['link-category'];
		} else {
			$category_id = $bp->links->current_link->category_id;
		}

		return apply_filters( 'bp_get_link_details_form_category_id', $category_id );
	}

function bp_link_details_form_url() {
	echo bp_get_link_details_form_url();
}
	function bp_get_link_details_form_url() {
		global $bp;

		if ( !empty( $_POST['link-url'] ) ) {
			$link_url = $_POST['link-url'];
		} else {
			$link_url = $bp->links->current_link->url;
		}

		return apply_filters( 'bp_get_link_details_form_url', $link_url );
	}

function bp_get_link_details_form_embed_service() {
	global $bp;

	if ( !empty( $_POST['link-url-embed-data'] ) ) {
		try {
			// load service
			$service = BP_Links_Embed::LoadService( trim( $_POST['link-url-embed-data'] ) );
			// valid service?
			if ( $service instanceof BP_Links_Embed_Service ) {
				return $service;
			}
		} catch ( BP_Links_Embed_Exception $e ) {
			return false;
		}
	} elseif ( bp_links_current_link_embed_enabled() ) {
		return bp_links_current_link_embed_service();
	}

	return false;
}

function bp_link_details_form_url_embed_data() {
	echo bp_get_link_details_form_url_embed_data();
}
	function bp_get_link_details_form_url_embed_data() {

		$embed_data = null;
		$embed_service = bp_get_link_details_form_embed_service();

		if ( $embed_service instanceof BP_Links_Embed_From_Url ) {
			$embed_data = $embed_service->export_data();
		}

		return apply_filters( 'bp_get_link_details_form_url_embed_data', $embed_data );
	}

function bp_link_details_form_name() {
	echo bp_get_link_details_form_name();
}
	function bp_get_link_details_form_name() {
		global $bp;

		if ( !empty( $_POST['link-name'] ) ) {
			$link_name = $_POST['link-name'];
		} else {
			$link_name = $bp->links->current_link->name;
		}

		return apply_filters( 'bp_get_link_details_form_name', $link_name );
	}

function bp_link_details_form_description() {
	echo bp_get_link_details_form_description();
}
	function bp_get_link_details_form_description() {
		global $bp;

		if ( !empty( $_POST['link-desc'] ) ) {
			$link_description = $_POST['link-desc'];
		} else {
			$link_description = $bp->links->current_link->description;
		}

		return apply_filters( 'bp_get_link_details_form_description', $link_description );
	}

function bp_link_details_form_enable_wire() {
	echo bp_get_link_details_form_enable_wire();
}
	function bp_get_link_details_form_enable_wire() {
		global $bp;

		$link_enable_wire = 1;

		if ( isset( $_POST['link-enable-wire'] ) && $_POST['link-enable-wire'] == 0 ) {
			$link_enable_wire = 0;
		} elseif ( isset( $bp->links->current_link->enable_wire ) ) {
			$link_enable_wire = $bp->links->current_link->enable_wire;
		}

		return (int) apply_filters( 'bp_get_link_details_form_enable_wire', $link_enable_wire );
	}

function bp_link_details_form_status() {
	echo bp_get_link_details_form_status();
}
	function bp_get_link_details_form_status() {
		global $bp;

		$link_status = null;

		if ( !empty( $_POST['link-status'] ) ) {
			if ( bp_links_is_valid_status( $_POST['link-status'] ) ) {
				$link_status = (integer) $_POST['link-status'];
			}
		} else {
			$link_status = $bp->links->current_link->status;
		}

		return apply_filters( 'bp_get_link_details_form_status', $link_status );
	}

function bp_link_details_form_avatar_thumb_default( $class = '' ) {
	echo bp_get_link_details_form_avatar_thumb_default( $class );
}
	function bp_get_link_details_form_avatar_thumb_default( $class = '' ) {
		return apply_filters( 'bp_get_link_details_form_avatar_thumb_default', bp_get_link_avatar( array( 'class' => $class, 'height' => 80, 'width' => 80 ) ) );
	}

function bp_link_details_form_avatar_thumb() {
	echo bp_get_link_details_form_avatar_thumb();
}
	function bp_get_link_details_form_avatar_thumb() {

		if ( bp_links_admin_current_action_variable() ) {

			return bp_get_link_avatar( 'width=100&height=100', bp_links_current_link() );

		} else {

			$embed_service = bp_get_link_details_form_embed_service();

			if ( $embed_service instanceof BP_Links_Embed_Service ) {
				return sprintf( '<img src="%1$s" class="avatar-current" alt="%2$s">', $embed_service->image_thumb_url(), $embed_service->title() );
			} else {
				return bp_get_link_details_form_avatar_thumb_default( 'avatar-current' );
			}
		}
	}

function bp_link_admin_form_action() {
	echo bp_get_link_admin_form_action();
}
	function bp_get_link_admin_form_action() {
		global $bp;

		$action = bp_links_admin_current_action_variable();

		if ( $action ) {
			return apply_filters( 'bp_get_link_admin_form_action', bp_get_link_permalink( $bp->links->current_link ) . '/admin/' . $action, $action );
		} else {
			die('Not an admin path!');
		}
	}

function bp_link_avatar_form_avatar() {
	echo bp_get_link_avatar_form_avatar();
}
	function bp_get_link_avatar_form_avatar() {
		return apply_filters( 'bp_get_link_avatar_form_avatar', bp_get_link_avatar( 'size=full', bp_links_current_link() ) );
	}

function bp_link_avatar_form_embed_html() {
	echo bp_get_link_avatar_form_embed_html();
}
	function bp_get_link_avatar_form_embed_html() {

		$html = ( isset( $_POST['embed-html'] ) ) ? $_POST['embed-html'] : null;

		return apply_filters( 'bp_get_link_avatar_form_embed_html', $html );
	}

function bp_link_avatar_form_embed_html_display() {
	echo bp_get_link_avatar_form_embed_html_display();
}
	function bp_get_link_avatar_form_embed_html_display() {
		if ( bp_links_current_link_embed_enabled() ) {
			return ( bp_links_current_link_embed_service()->avatar_only() ) ? 1 : 0;
		} else {
			return 1;
		}
	}


/********************************************************************************
 * Site Links Template Tags
 **/

class BP_Links_Site_Links_Template {
	var $current_link = -1;
	var $link_count;
	var $links;
	var $link;
	
	var $in_the_loop;
	
	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_link_count;
	
	function bp_links_site_links_template( $type, $per_page, $max ) {
		global $bp;

		$this->pag_page = isset( $_REQUEST['lpage'] ) ? intval( $_REQUEST['lpage'] ) : 1;
		$this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;
				
		if ( isset( $_REQUEST['s'] ) )
			$filter = $_REQUEST['s'];

		if ( isset( $_REQUEST['letter'] ) )
			$letter = $_REQUEST['letter'];

		if ( isset( $_REQUEST['category_id'] ) )
			$category_id = $_REQUEST['category_id'];
		else if ( isset( $_COOKIE['bp_directory_links_category_id'] ) )
			$category_id = $_COOKIE['bp_directory_links_category_id'];

		switch ( $type ) {
			case 'random':
				$this->links = BP_Links_Link::get_random( $this->pag_num, $this->pag_page );
				break;

			case 'all':
				$this->links = BP_Links_Link::get_all_filtered( $filter, $letter, $category_id, $this->pag_num, $this->pag_page );
				break;

			case 'search':
				$this->links = BP_Links_Link::get_search_filtered( $filter, $letter, $category_id, $this->pag_num, $this->pag_page );
				break;

			case 'newest':
				$this->links = BP_Links_Link::get_newest_filtered( $filter, $letter, $category_id, $this->pag_num, $this->pag_page );
				break;

			case 'most-popular':
				$this->links = BP_Links_Link::get_most_popular_filtered( $filter, $letter, $category_id, $this->pag_num, $this->pag_page );
				break;

			case 'most-votes':
				$this->links = BP_Links_Link::get_most_votes_filtered( $filter, $letter, $category_id, $this->pag_num, $this->pag_page );
				break;

			case 'high-votes':
				$this->links = BP_Links_Link::get_high_votes_filtered( $filter, $letter, $category_id, $this->pag_num, $this->pag_page );
				break;

			case 'recently-active':
			default:
				$this->links = BP_Links_Link::get_recently_active_filtered( $filter, $letter, $category_id, $this->pag_num, $this->pag_page );
				break;
		}
		
		if ( !$max || $max >= (int)$this->links['total'] )
			$this->total_link_count = (int)$this->links['total'];
		else
			$this->total_link_count = (int)$max;

		$this->links = $this->links['links'];
		
		if ( $max ) {
			if ( $max >= count($this->links) )
				$this->link_count = count($this->links);
			else
				$this->link_count = (int)$max;
		} else {
			$this->link_count = count($this->links);
		}

		if ( (int) $this->total_link_count && (int) $this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base' => add_query_arg( 'lpage', '%#%' ),
				'format' => '',
				'total' => ceil( (int) $this->total_link_count / (int) $this->pag_num ),
				'current' => (int) $this->pag_page,
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'mid_size' => 1
			));
		}
	}
	
	function has_links() {
		if ( $this->link_count )
			return true;
		
		return false;
	}
	
	function next_link() {
		$this->current_link++;
		$this->link = $this->links[$this->current_link];
		
		return $this->link;
	}
	
	function rewind_links() {
		$this->current_link = -1;
		if ( $this->link_count > 0 ) {
			$this->link = $this->links[0];
		}
	}
	
	function links() {
		if ( $this->current_link + 1 < $this->link_count ) {
			return true;
		} elseif ( $this->current_link + 1 == $this->link_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_links();
		}

		$this->in_the_loop = false;
		return false;
	}
	
	function the_link() {
		global $link;

		$this->in_the_loop = true;
		$this->link = $this->next_link();
		
		if ( !$link = wp_cache_get( 'bp_links_link_nouserdata_' . $this->link->link_id, 'bp' ) ) {
			$link = new BP_Links_Link( $this->link->link_id, false, false );
			wp_cache_set( 'bp_links_link_nouserdata_' . $this->link->link_id, $link, 'bp' );
		}
		
		$this->link = $link;
		
		if ( 0 == $this->current_link ) // loop has just started
			do_action('loop_start');
	}
}


function bp_rewind_site_links() {
	global $site_links_template;
	
	$site_links_template->rewind_links();
}

function bp_has_site_links( $args = '' ) {
	global $bp, $site_links_template;

	$defaults = array(
		'type' => 'recently-active',
		'per_page' => 10,
		'max' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
	
	// The following code will auto set parameters based on the page being viewed in the directory.
	// for example on example.com/links/most-popular/
	// $type = 'most-popular'
	//
	if ( BP_LINKS_SLUG == $bp->current_component ) {
		$order = $bp->current_action;
		if ( 'all' == $order )
			$type = 'all';
		else if ( 'newest' == $order )
			$type = 'newest';
		else if ( 'recently-active' == $order )
			$type = 'recently-active';
		else if ( 'most-votes' == $order )
			$type = 'most-votes';
		else if ( 'high-votes' == $order )
			$type = 'high-votes';
		else // default
			$type = 'most-popular';
	}
	
	if ( $max ) {
		if ( $per_page > $max )
			$per_page = $max;
	}
		
	$site_links_template = new BP_Links_Site_Links_Template( $type, $per_page, $max );
	return apply_filters( 'bp_has_site_links', $site_links_template->has_links(), &$site_links_template );
}

function bp_site_links() {
	global $site_links_template;
	
	return $site_links_template->links();
}

function bp_the_site_link() {
	global $site_links_template;
	
	return $site_links_template->the_link();
}

function bp_site_links_pagination_count() {
	global $bp, $site_links_template;
	
	$from_num = intval( ( $site_links_template->pag_page - 1 ) * $site_links_template->pag_num ) + 1;
	$to_num = ( $from_num + ( $site_links_template->pag_num - 1 ) > $site_links_template->total_link_count ) ? $site_links_template->total_link_count : $from_num + ( $site_links_template->pag_num - 1) ;

	echo sprintf( __( 'Viewing link %1$d to %2$d (of %3$d links)', 'buddypress-links' ), $from_num, $to_num, $site_links_template->total_link_count ); ?> &nbsp;
	<span class="ajax-loader"></span><?php 
}

function bp_site_links_pagination_links() {
	echo bp_get_site_links_pagination_links();
}
	function bp_get_site_links_pagination_links() {
		global $site_links_template;
		
		return apply_filters( 'bp_get_site_links_pagination_links', $site_links_template->pag_links );
	}

function bp_the_site_link_id() {
	echo bp_get_the_site_link_id();
}
	function bp_get_the_site_link_id() {
		global $site_links_template;
		
		return apply_filters( 'bp_get_the_site_link_id', $site_links_template->link->id );
	}

function bp_the_site_link_name() {
	echo bp_get_the_site_link_name();
}
	function bp_get_the_site_link_name() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_name', bp_get_link_name( $site_links_template->link ) );
	}

function bp_the_site_link_url() {
	echo bp_get_the_site_link_url();
}
	function bp_get_the_site_link_url() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_url', bp_get_link_url( $site_links_template->link ) );
	}

function bp_the_site_link_url_domain() {
	echo bp_get_the_site_link_url_domain();
}
	function bp_get_the_site_link_url_domain() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_url_domain', bp_get_link_url_domain( $site_links_template->link ) );
	}

function bp_the_site_link_category_name() {
	echo bp_get_the_site_link_category_name();
}
	function bp_get_the_site_link_category_name() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_category_name', bp_get_link_category_name( $site_links_template->link ) );
	}

function bp_the_site_link_avatar() {
	echo bp_get_the_site_link_avatar();
}
	function bp_get_the_site_link_avatar() {
		global $site_links_template;

		return apply_filters( 'bp_the_site_link_avatar', bp_get_link_avatar( array(), $site_links_template->link ) );
	}

function bp_the_site_link_avatar_thumb() {
	echo bp_get_the_site_link_avatar_thumb();
}
	function bp_get_the_site_link_avatar_thumb() {
		global $site_links_template;
		
		return apply_filters( 'bp_get_the_site_link_avatar_thumb', bp_get_link_avatar_thumb( $site_links_template->link ) );
	}

function bp_the_site_link_avatar_mini() {
	echo bp_get_the_site_link_avatar_mini();
}
	function bp_get_the_site_link_avatar_mini() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_avatar_mini', bp_get_link_avatar_mini( $site_links_template->link ) );
	}

function bp_the_site_link_user_avatar() {
	echo bp_get_the_site_link_user_avatar();
}
	function bp_get_the_site_link_user_avatar() {
		global $site_links_template;

		return apply_filters( 'bp_the_site_link_user_avatar', bp_get_link_user_avatar( array(), $site_links_template->link ) );
	}

function bp_the_site_link_user_avatar_thumb() {
	echo bp_get_the_site_link_user_avatar_thumb();
}
	function bp_get_the_site_link_user_avatar_thumb() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_user_avatar_thumb', bp_get_link_user_avatar_thumb( $site_links_template->link ) );
	}

function bp_the_site_link_user_avatar_mini() {
	echo bp_get_the_site_link_user_avatar_mini();
}
	function bp_get_the_site_link_user_avatar_mini() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_user_avatar_mini', bp_get_link_user_avatar_mini( $site_links_template->link ) );
	}

function bp_the_site_link_permalink() {
	echo bp_get_the_site_link_permalink();
}
	function bp_get_the_site_link_permalink() {
		global $site_links_template;
		
		return apply_filters( 'bp_get_the_site_link_permalink', bp_get_link_permalink( $site_links_template->link ) );
	}

function bp_the_site_link_wire_permalink() {
	echo bp_get_the_site_link_wire_permalink();
}
	function bp_get_the_site_link_wire_permalink() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_wire_permalink', bp_get_link_wire_permalink( $site_links_template->link ) );
	}

function bp_the_site_link_userlink() {
	echo bp_get_the_site_link_userlink();
}
	function bp_get_the_site_link_userlink() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_userlink', bp_get_link_userlink( $site_links_template->link ) );
	}

function bp_the_site_link_time_elapsed_text() {
	echo bp_get_the_site_link_time_elapsed_text();
}
	function bp_get_the_site_link_time_elapsed_text() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_time_elapsed_text', bp_get_link_time_elapsed_text( $site_links_template->link ) );
	}

function bp_the_site_link_last_active() {
	echo bp_get_the_site_link_last_active();
}
	function bp_get_the_site_link_last_active() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_last_active', sprintf( __( 'active %1$s ago', 'buddypress-links' ), bp_get_link_last_active( $site_links_template->link ) ) );
	}

/* TODO useful on directory page?
function bp_the_site_link_join_button() {
	global $site_links_template;
	
	echo bp_link_join_button( $site_links_template->link );
}
*/
	
function bp_the_site_link_description() {
	echo bp_get_the_site_link_description();
}
	function bp_get_the_site_link_description() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_description', bp_get_link_description( $site_links_template->link ) );
	}

function bp_the_site_link_description_excerpt() {
	echo bp_get_the_site_link_description_excerpt();
}
	function bp_get_the_site_link_description_excerpt() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_description_excerpt', bp_create_excerpt( bp_get_link_description( $site_links_template->link, false ), 35 ) );
	}

function bp_the_site_link_vote_total() {
	echo bp_get_the_site_link_vote_total();
}
	function bp_get_the_site_link_vote_total() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_vote_total', bp_get_link_vote_total( $site_links_template->link ) );
	}

function bp_the_site_link_vote_count() {
	echo bp_get_the_site_link_vote_count();
}
	function bp_get_the_site_link_vote_count() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_vote_count', bp_get_link_vote_count( $site_links_template->link ) );
	}

function bp_the_site_link_date_created() {
	echo bp_get_the_site_link_date_created();
}
	function bp_get_the_site_link_date_created() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_date_created', bp_core_time_since( $site_links_template->link->date_created ) );
	}

function bp_the_site_link_type() {
	echo bp_get_the_site_link_type();
}
	function bp_get_the_site_link_type() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_type', bp_get_link_type( $site_links_template->link ) );
	}

function bp_the_site_link_wire_count() {
	echo bp_get_the_site_link_wire_count();
}
	function bp_get_the_site_link_wire_count() {
		global $site_links_template;
		return apply_filters( 'bp_get_the_site_link_wire_count', bp_get_link_wire_count( $site_links_template->link ) );
	}

function bp_the_site_link_play_button() {
	echo bp_get_the_site_link_play_button();
}
	function bp_get_the_site_link_play_button() {
		global $site_links_template;
		return apply_filters( 'bp_get_the_site_link_play_button', bp_get_link_play_button( $site_links_template->link ) );
	}

function bp_the_site_link_is_wire_enabled( $link = false ) {
	global $site_links_template;

	if ( $link )
		return (bool) $link->enable_wire;
	else
		return (bool) $site_links_template->link->enable_wire;
}

function bp_the_site_link_hidden_fields() {
	if ( isset( $_REQUEST['s'] ) ) {
		echo '<input type="hidden" id="search_terms" value="' . attribute_escape( $_REQUEST['s'] ) . '" name="search_terms" />';
	}
	
	if ( isset( $_REQUEST['letter'] ) ) {
		echo '<input type="hidden" id="selected_letter" value="' . attribute_escape( $_REQUEST['letter'] ) . '" name="selected_letter" />';
	}
	
	if ( isset( $_REQUEST['links_search'] ) ) {
		echo '<input type="hidden" id="search_terms" value="' . attribute_escape( $_REQUEST['links_search'] ) . '" name="search_terms" />';
	}
}

function bp_the_site_link_feed_item_guid() {
	echo bp_get_the_site_link_feed_item_guid();
}
	function bp_get_the_site_link_feed_item_guid() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_feed_item_guid', bp_get_the_site_link_permalink() );
	}

function bp_the_site_link_feed_item_title() {
	echo bp_get_the_site_link_feed_item_title();
}
	function bp_get_the_site_link_feed_item_title() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_feed_item_title', bp_get_link_name( $site_links_template->link ) );
	}

function bp_the_site_link_feed_item_link() {
	echo bp_get_the_site_link_feed_item_link();
}
	function bp_get_the_site_link_feed_item_link() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_feed_item_link', bp_get_link_permalink( $site_links_template->link ) );
	}

function bp_the_site_link_feed_item_date() {
	echo bp_get_the_site_link_feed_item_date();
}
	function bp_get_the_site_link_feed_item_date() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_feed_item_date', $site_links_template->link->date_created );
	}

function bp_the_site_link_feed_item_description() {
	echo bp_get_the_site_link_feed_item_description();
}
	function bp_get_the_site_link_feed_item_description() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_feed_item_description', bp_get_link_description( $site_links_template->link ) );
	}

/********************************************************************************
 * Site Links Categories Template Tags
 **/

class BP_Links_Site_Categories_Template {
	var $current_category = -1;
	var $category_count;
	var $categories;
	var $category;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_category_count;

	function bp_links_site_categories_template( $type, $per_page, $max ) {
		global $bp;

		$this->pag_page = isset( $_REQUEST['lcpage'] ) ? intval( $_REQUEST['lcpage'] ) : 1;
		$this->pag_num = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		if ( isset( $_REQUEST['s'] ) )
			$filter = $_REQUEST['s'];

		switch ( $type ) {
			case 'all':
			default:
				$this->categories = BP_Links_Category::get_all_filtered( $filter, $this->pag_num, $this->pag_page );
				break;
		}

		if ( !$max || $max >= (int)$this->categories['total'] )
			$this->total_category_count = (int)$this->categories['total'];
		else
			$this->total_category_count = (int)$max;

		$this->categories = $this->categories['categories'];

		if ( $max ) {
			if ( $max >= count($this->categories) )
				$this->category_count = count($this->categories);
			else
				$this->category_count = (int)$max;
		} else {
			$this->category_count = count($this->categories);
		}

		if ( (int) $this->total_category_count && (int) $this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base' => add_query_arg( 'lcpage', '%#%' ),
				'format' => '',
				'total' => ceil( (int) $this->total_category_count / (int) $this->pag_num ),
				'current' => (int) $this->pag_page,
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'mid_size' => 1
			));
		}
	}

	function has_categories() {
		if ( $this->category_count )
			return true;

		return false;
	}

	function next_category() {
		$this->current_category++;
		$this->category = $this->categories[$this->current_category];

		return $this->category;
	}

	function rewind_categories() {
		$this->current_category = -1;
		if ( $this->category_count > 0 ) {
			$this->category = $this->categories[0];
		}
	}

	function categories() {
		if ( $this->current_category + 1 < $this->category_count ) {
			return true;
		} elseif ( $this->current_category + 1 == $this->category_count ) {
			do_action('loop_end');
			// Do some cleaning up after the loop
			$this->rewind_categories();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_category() {
		global $category;

		$this->in_the_loop = true;
		$this->category = $this->next_category();

		if ( !$category = wp_cache_get( 'bp_links_link_category_nouserdata_' . $this->category->category_id, 'bp' ) ) {
			$category = new BP_Links_Category( $this->category->category_id, false, false );
			wp_cache_set( 'bp_links_link_category_nouserdata_' . $this->category->category_id, $category, 'bp' );
		}

		$this->category = $category;

		if ( 0 == $this->current_category ) // loop has just started
			do_action('loop_start');
	}
}

function bp_rewind_site_link_categories() {
	global $site_link_categories_template;

	$site_link_categories_template->rewind_categories();
}

function bp_has_site_link_categories( $args = '' ) {
	global $bp, $site_link_categories_template;

	$defaults = array(
		'type' => 'all',
		'per_page' => 10,
		'max' => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( $max ) {
		if ( $per_page > $max )
			$per_page = $max;
	}

	$site_link_categories_template = new BP_Links_Site_Categories_Template( $type, $per_page, $max );
	return apply_filters( 'bp_has_site_link_categories', $site_link_categories_template->has_categories(), &$site_link_categories_template );
}

function bp_site_link_categories() {
	global $site_link_categories_template;

	return $site_link_categories_template->categories();
}

function bp_the_site_link_categories_category() {
	global $site_link_categories_template;

	return $site_link_categories_template->the_category();
}

function bp_site_link_categories_pagination_count() {
	global $bp, $site_link_categories_template;

	// TODO, this is not displaying properly
	$from_num = intval( ( $site_link_categories_template->pag_page - 1 ) * $site_link_categories_template->pag_num ) + 1;
	$to_num = ( $from_num + ( $site_link_categories_template->pag_num - 1 ) > $site_link_categories_template->total_link_count ) ? $site_link_categories_template->total_link_count : $from_num + ( $site_link_categories_template->pag_num - 1) ;

	echo sprintf( __( 'Viewing category %1$d to %2$d (of %3$d categories)', 'buddypress-links' ), $from_num, $to_num, $site_link_categories_template->total_category_count ); ?> &nbsp;
	<span class="ajax-loader"></span><?php
}

function bp_site_link_categories_pagination_links() {
	echo bp_get_site_link_categories_pagination_links();
}
	function bp_get_site_link_categories_pagination_links() {
		global $site_link_categories_template;

		return apply_filters( 'bp_get_site_link_categories_pagination_links', $site_link_categories_template->pag_links );
	}

function bp_the_site_link_categories_category_id() {
	echo bp_get_the_site_link_categories_category_id();
}
	function bp_get_the_site_link_categories_category_id() {
		global $site_link_categories_template;

		return apply_filters( 'bp_get_the_site_link_categories_category_id', $site_link_categories_template->category->id );
	}

function bp_the_site_link_categories_category_name() {
	echo bp_get_the_site_link_categories_category_name();
}
	function bp_get_the_site_link_categories_category_name() {
		global $site_link_categories_template;

		return apply_filters( 'bp_get_the_site_link_categories_category_name', $site_link_categories_template->category->name );
	}

function bp_the_site_link_categories_category_description() {
	echo bp_get_the_site_link_categories_category_description();
}
	function bp_get_the_site_link_categories_category_description() {
		global $site_link_categories_template;

		return apply_filters( 'bp_get_the_site_link_categories_category_description', $site_link_categories_template->category->description );
	}

function bp_the_site_link_categories_category_slug() {
	echo bp_get_the_site_link_categories_category_slug();
}
	function bp_get_the_site_link_categories_category_slug() {
		global $site_link_categories_template;

		return apply_filters( 'bp_get_the_site_link_categories_category_slug', $site_link_categories_template->category->slug );
	}

function bp_the_site_link_categories_category_priority() {
	echo bp_get_the_site_link_categories_category_priority();
}
	function bp_get_the_site_link_categories_category_priority() {
		global $site_link_categories_template;

		return apply_filters( 'bp_get_the_site_link_categories_category_priority', $site_link_categories_template->category->priority );
	}

function bp_the_site_link_categories_category_link_count() {
	echo bp_get_the_site_link_categories_category_link_count();
}
	function bp_get_the_site_link_categories_category_link_count() {
		global $site_link_categories_template;

		return apply_filters( 'bp_get_the_site_link_categories_category_link_count', BP_Links_Category::get_link_count( $site_link_categories_template->category->id ) );
	}

function bp_the_site_link_categories_category_date_created() {
	echo bp_get_the_site_link_categories_category_date_created();
}
	function bp_get_the_site_link_categories_category_date_created() {
		global $site_link_categories_template;

		return apply_filters( 'bp_get_the_site_link_categories_category_date_created', date( get_option( 'date_format' ), $site_link_categories_template->category->date_created ) );
	}

function bp_the_site_link_categories_category_date_updated() {
	echo bp_get_the_site_link_categories_category_date_updated();
}
	function bp_get_the_site_link_categories_category_date_updated() {
		global $site_link_categories_template;

		return apply_filters( 'bp_get_the_site_link_categories_category_date_updated', date( get_option( 'date_format' ), $site_link_categories_template->category->date_updated ) );
	}

function bp_the_site_link_categories_hidden_fields() {
	if ( isset( $_REQUEST['s'] ) ) {
		echo '<input type="hidden" id="search_terms" value="' . attribute_escape( $_REQUEST['s'] ) . '" name="search_terms" />';
	}
}
	
/*******************************
 * Links Directory Template Tags
 **/

function bp_directory_links_filter_tabs() {
	global $bp;
?>
	<li<?php if ( !isset($bp->current_action) || 'most-popular' == $bp->current_action ) : ?> class="current"<?php endif; ?>><a href="<?php echo site_url('/') . $bp->links->slug ?>/most-popular"><?php _e( 'Most Popular', 'buddypress-links' ) ?></a></li>
	<li<?php if ( 'most-votes' == $bp->current_action ) : ?> class="current"<?php endif; ?>><a href="<?php echo site_url('/') . $bp->links->slug ?>/most-votes"><?php _e( 'Most Votes', 'buddypress-links' ) ?></a></li>
	<li<?php if ( 'high-votes' == $bp->current_action ) : ?> class="current"<?php endif; ?>><a href="<?php echo site_url('/') . $bp->links->slug ?>/high-votes"><?php _e( 'Vote Rating', 'buddypress-links' ) ?></a></li>
	<li<?php if ( 'recently-active' == $bp->current_action ) : ?> class="current"<?php endif; ?>><a href="<?php echo site_url('/') . $bp->links->slug ?>/recently-active"><?php _e( 'Recently Active', 'buddypress-links' ) ?></a></li>
	<li<?php if ( 'newest' == $bp->current_action ) : ?> class="current"<?php endif; ?>><a href="<?php echo site_url('/') . $bp->links->slug ?>/newest"><?php _e( 'Newest', 'buddypress-links' ) ?></a></li>
	<li<?php if ( 'all' == $bp->current_action ) : ?> class="current"<?php endif; ?>><a href="<?php echo site_url('/') . $bp->links->slug ?>/all"><?php _e( 'All', 'buddypress-links' ) ?></a></li>
	<li><div id="filter-cat"><?php _e( 'Category', 'buddypress-links' ) ?>: <?php bp_directory_links_filter_category($_COOKIE['bp_directory_links_category_id'], 'category_id') ?></div></li>
<?php
	do_action( 'bp_directory_links_filter_tabs' );
}

function bp_directory_links_filter_category( $selected_category_id = 1, $element_id = 'category', $element_class = '' ) {

	do_action( 'bp_before_directory_links_filter_category' );

	// grab all categories
	$categories = BP_Links_Category::get_all();

	$class_string = ( empty( $element_class ) ) ? null : sprintf( ' class="%s"', $element_class );

	echo sprintf( '<select id="%1$s"%2$s>', $element_id, $class_string );
	echo sprintf( '<option value="">%1$s</option>', __( 'All', 'buddypress-links' ) );

	foreach ( $categories as $category ) {
		// populate
		$category = new BP_Links_Category( $category->category_id );
		// is this one selected?
		$selected = ( $selected_category_id == $category->id ) ? ' selected="selected"' : null;
		// output it
		echo sprintf( '<option value="%d"%s />%s</option>', $category->id, $selected, $category->name );
	}

	// close tag
	echo '</select>';

	// print newline
	echo PHP_EOL;

	do_action( 'bp_after_directory_links_filter_category' );

}

function bp_directory_links_search_form() {
	global $bp; ?>
	<form action="" method="get" id="search-links-form">
		<label><input type="text" name="s" id="links_search" value="<?php if ( isset( $_GET['s'] ) ) { echo attribute_escape( $_GET['s'] ); } else { _e( 'Search anything...', 'buddypress-links' ); } ?>"  onfocus="if (this.value == '<?php _e( 'Search anything...', 'buddypress-links' ) ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e( 'Search anything...', 'buddypress-links' ) ?>';}" /></label>
		<input type="submit" id="links_search_submit" name="links_search_submit" value="<?php _e( 'Search', 'buddypress-links' ) ?>" />
	</form>
<?php
}

function bp_directory_links_feed_link() {
	echo bp_get_directory_links_feed_link();
}
	function bp_get_directory_links_feed_link() {
		global $bp;

		return apply_filters( 'bp_get_directory_links_feed_link', site_url( $bp->links->slug . '/feed' ) );
	}

/*********************************
 * Links List Template Helper Tags
 **/

function bp_link_list_parse_args( $args ) {

	$defaults = array(
		'the_site' => false,
		'avatar_size' => BP_LINKS_LIST_AVATAR_SIZE
	);

	return wp_parse_args( $args, $defaults );
}

function bp_link_list( $args = array() ) {

	bp_link_list_open();

	while ( bp_links() ) {
		bp_the_link();

		// set item_id
		$args['item_id'] = bp_get_link_id();

		bp_link_list_item_open( $args );
		bp_link_list_item_left( $args );
		bp_link_list_item_right( $args );
		bp_link_list_item_footer( $args );
		bp_link_list_item_close();
	}

	bp_link_list_close();
}

function bp_the_site_link_list( $args = array() ) {

	bp_link_list_open();

	while ( bp_site_links() ) {
		bp_the_site_link();

		// force 'the_site' to true
		$args['the_site'] = true;

		// set item_id
		$args['item_id'] = bp_get_the_site_link_id();

		bp_link_list_item_open( $args );
		bp_link_list_item_left( $args );
		bp_link_list_item_right( $args );
		bp_link_list_item_footer( $args );
		bp_link_list_item_close();
	}

	bp_link_list_close();
}

function bp_link_list_open() {
	do_action( 'bp_before_my_links_list' );
	echo '<ul id="link-list" class="item-list">' . PHP_EOL;
	do_action( 'bp_before_my_links_list_content' );
}

function bp_link_list_close() {
	do_action( 'bp_after_my_links_list_content' );
	echo '</ul>' . PHP_EOL;
	do_action( 'bp_after_my_links_list' );

	// IMPORTANT: this form is required for the AJAX voting to work!
	bp_link_list_vote_form();
}

function bp_link_list_vote_form() {
	bp_link_list_vote_form_open();
	bp_link_list_vote_form_close();
}

function bp_link_list_vote_form_open() {
	echo sprintf( '<form action="%s/" method="post" id="link-vote-form">', site_url() ) . PHP_EOL;
}

function bp_link_list_vote_form_close() {
	wp_nonce_field( 'link_vote', '_wpnonce-link-vote' ) . PHP_EOL;
	echo '</form>' . PHP_EOL;
}

function bp_link_list_item_open( $args = '' ) {

	extract( bp_link_list_parse_args( $args ) );

	switch ( (integer) $avatar_size ) {
		case 50:
		case 60:
		case 70:
		case 80:
		case 90:
		case 100:
		case 110:
		case 120:
		case 130:
			$avmax_class = 'avmax-' . $avatar_size;
			break;
		default:
			$avmax_class = 'avmax-' . BP_LINKS_LIST_AVATAR_SIZE;
	}

	do_action( 'bp_before_my_links_list_item' );
	printf( '<li class="%s">%s', $avmax_class, PHP_EOL );
	do_action( 'bp_before_my_links_list_item_content' );
}

function bp_link_list_item_close() {
	do_action( 'bp_after_my_links_list_item_content' );
	echo '</li>' . PHP_EOL;
	do_action( 'bp_after_my_links_list_item' );
}

function bp_link_list_item_left( $args = array() ) {

	extract( bp_link_list_parse_args( $args ) );

	switch ( (integer) $avatar_size ) {
		case 50:
			$thumb_html = ( $the_site ) ? bp_get_the_site_link_avatar_thumb() : bp_get_link_avatar_thumb();
			break;
		default:
			$thumb_html = ( $the_site ) ? bp_get_the_site_link_avatar() : bp_get_link_avatar();
	}

	printf('
		%1$s<div class="link-list-left">%2$s
			%5$s<a href="%3$s">%4$s</a>%6$s
		%7$s</div>%8$s',
		apply_filters( 'bp_before_my_links_list_item_left', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_left_content', '', $args ), // arg 2
		( $the_site ) ? bp_get_the_site_link_permalink() : bp_get_link_permalink(), // arg 3
		$thumb_html, // arg 4
		( $the_site ) ? bp_get_the_site_link_play_button() : bp_get_link_play_button(), // arg 5
		apply_filters( 'bp_after_my_links_list_item_avatar', '', $args ), // arg 6
		apply_filters( 'bp_after_my_links_list_item_left_content', '', $args ), // arg 7
		apply_filters( 'bp_after_my_links_list_item_left', '', $args ) // arg 8
	);
}

function bp_link_list_item_right( $args = array() ) {

	extract( bp_link_list_parse_args( $args ) );
	
	printf('
		%1$s<div class="link-list-right">%2$s
			%3$s%4$s
		%5$s</div>%6$s',
		apply_filters( 'bp_before_my_links_list_item_right', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_right_content', '', $args ), // arg 2
		bp_get_link_list_item_title( $args ), // arg 3
		bp_get_link_list_item_description( $args ), // arg 4
		apply_filters( 'bp_after_my_links_list_item_right_content', '', $args ), // arg 5
		apply_filters( 'bp_after_my_links_list_item_right', '', $args ) // arg 6
	);
}

function bp_link_list_item_footer( $args = array() ) {

	printf('
		%1$s<div class="link-list-footer">%2$s
			<div class="link-list-footer-left">
				%3$s
			</div>
			<div class="link-list-footer-right">
				%4$s
			</div>
		%5$s</div>%6$s',
		apply_filters( 'bp_before_my_links_list_item_footer', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_footer_content', '', $args ), // arg 2
		bp_get_link_list_item_vote_panel( $args ), // arg 3
		bp_get_link_list_item_xtrabar( $args ), // arg 4
		apply_filters( 'bp_after_my_links_list_item_footer_content', '', $args ), // arg 5
		apply_filters( 'bp_after_my_links_list_item_footer', '', $args ) // arg 6
	);
}

function bp_link_list_item_vote_panel( $args = array() ) {
	echo bp_get_link_list_item_vote_panel( $args );
}
function bp_get_link_list_item_vote_panel( $args = array() ) {

	extract( bp_link_list_parse_args( $args ) );

	if ( (integer) $avatar_size >= 0 ) {
		$vote_count_html =
			sprintf(
				'<div class="vote-count">
					<span id="vote-count-%1$d">%2$d</span> %3$s
				</div>',
				( $the_site ) ? bp_get_the_site_link_id() : bp_get_link_id(), // arg 1
				( $the_site ) ? bp_get_the_site_link_vote_count() : bp_get_link_vote_count(), // arg 2
				__('Votes', 'buddypress-links') // arg 3
			);
	} else {
		$vote_count_html = null;
	}

	return sprintf('
		%1$s<div class="link-vote-panel" id="link-vote-panel-%3$d">
			%2$s<div class="clickers">
				<a href="#vu" id="vote-up-%3$d" class="vote up"></a>
				<div id="vote-total-%3$d" class="vote-total">%4$+d</div>
				<a href="#vd" id="vote-down-%3$d"  class="vote down"></a>
			</div>
			%5$s
		%6$s</div>%7$s',
		apply_filters( 'bp_before_my_links_list_item_vote_panel', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_vote_panel_content', '', $args ), // arg 2
		( $the_site ) ? bp_get_the_site_link_id() : bp_get_link_id(), // arg 3
		( $the_site ) ? bp_get_the_site_link_vote_total() : bp_get_link_vote_total(), // arg 4
		$vote_count_html, // arg 5
		apply_filters( 'bp_after_my_links_list_item_vote_panel_content', '', $args ),	// arg 6
		apply_filters( 'bp_after_my_links_list_item_vote_panel', '', $args )	// arg 7
	);
}

function bp_get_link_list_item_title( $args = array() ) {

	extract( bp_link_list_parse_args( $args ) );

	return sprintf(
		'%1$s<h4>%2$s%3$s: <a href="%4$s" target="_blank">%5$s</a>%6$s</h4>%7$s',
		apply_filters( 'bp_before_my_links_list_item_title', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_title_content', '', $args ), // arg 2
		( $the_site ) ? bp_get_the_site_link_category_name() : bp_get_link_category_name(), // arg 3
		( $the_site ) ? bp_get_the_site_link_url() : bp_get_link_url(), // arg 4
		( $the_site ) ? bp_get_the_site_link_name() : bp_get_link_name(), // arg 5
		apply_filters( 'bp_after_my_links_list_item_title_content', '', $args ), // arg 6
		apply_filters( 'bp_after_my_links_list_item_title', '', $args ) // arg 7
	);
}

function bp_get_link_list_item_description( $args = array() ) {

	extract( bp_link_list_parse_args( $args ) );

	return sprintf('
		%1$s<p class="desc">%2$s
			<span class="domain">%3$s --</span>%4$s
			%5$s
		%6$s</p>%7$s',
		apply_filters( 'bp_before_my_links_list_item_description', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_description_content', '', $args ), // arg 2
		( $the_site ) ? bp_get_the_site_link_url_domain() : bp_get_link_url_domain(), // arg 3
		apply_filters( 'bp_after_my_links_list_item_description_domain', '', $args ), // arg 4
		( $the_site ) ? bp_get_the_site_link_description() : bp_get_link_description(), // arg 5
		apply_filters( 'bp_after_my_links_list_item_description_content', '', $args ), // arg 6
		apply_filters( 'bp_after_my_links_list_item_description', '', $args ) // arg 7
	);
}

// get_the_site_
function bp_get_link_list_item_xtrabar( $args = array() ) {

	extract( bp_link_list_parse_args( $args ) );
	
	// determine if wire is enabled
	if ( function_exists('bp_wire_install') ) {
		if ( $the_site ) {
			$wire_enabled = bp_the_site_link_is_wire_enabled();
		} else {
			$wire_enabled = bp_link_is_wire_enabled();
		}
	} else {
		// wire component not enabled
		$wire_enabled = false;
	}

	// build comment link
	if ( $wire_enabled ) {
		$wire_link = sprintf('
			%1$s<a href="%2$s" class="comments">%3$d&nbsp;%4$s</a>%5$s',
			apply_filters( 'bp_before_my_links_list_item_xtrabar_comments', '', $args ), // arg 1
			( $the_site ) ? bp_get_the_site_link_wire_permalink() : bp_get_link_wire_permalink(), // arg 2
			( $the_site ) ? bp_get_the_site_link_wire_count() : bp_get_link_wire_count(), // arg 3
			__( 'Comments', 'buddypress-links' ), // arg 4
			apply_filters( 'bp_after_my_links_list_item_xtrabar_comments', '', $args ) // arg 5
		);
	}

	return sprintf('
		%1$s<div class="xtrabar">%2$s
			%3$s<a href="%4$s" class="home">%5$s</a>%6$s%7$s
			%8$s<div class="owner">%9$s&nbsp;%10$s %11$s %12$s</div>%13$s
		%14$s</div>%15$s',
		apply_filters( 'bp_before_my_links_list_item_xtrabar_content', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_xtrabar', '', $args ), // arg 2
		apply_filters( 'bp_before_my_links_list_item_xtrabar_home', '', $args ), // arg 3
		( $the_site ) ? bp_get_the_site_link_permalink() : bp_get_link_permalink(), // arg 4
		__( 'Home', 'buddypress-links' ), // arg 5
		apply_filters( 'bp_after_my_links_list_item_xtrabar_home', '', $args ), // arg 6
		( $wire_enabled ) ? $wire_link : null, // arg 7
		apply_filters( 'bp_before_my_links_list_item_xtrabar_userlink', '', $args ), // arg 8
		( $the_site ) ? bp_get_the_site_link_user_avatar_mini() : bp_get_link_user_avatar_mini(), // arg 9
		( $the_site ) ? bp_get_the_site_link_userlink() : bp_get_link_userlink(), // arg 10
		__( 'created', 'buddypress-links' ), // arg 11
		( $the_site ) ? bp_get_the_site_link_time_elapsed_text() : bp_get_link_time_elapsed_text(), // arg 12
		apply_filters( 'bp_after_my_links_list_item_xtrabar_userlink', '', $args ), // arg 13
		apply_filters( 'bp_after_my_links_list_item_xtrabar', '', $args ), // arg 14
		apply_filters( 'bp_after_my_links_list_item_xtrabar_content', '', $args ) // arg 15
	);
}
?>