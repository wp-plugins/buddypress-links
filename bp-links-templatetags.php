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
	echo sprintf( '<input type="radio" name="%s" value=""%s%s />%s ', $element_name, $class_string, $selected, __('All') );

	do_action( 'bp_after_links_category_radio_options_with_all' );

	bp_links_category_radio_options();
}

function bp_is_link_admin_screen( $slug ) {
	global $bp;
	
	if ( $bp->current_component != BP_LINKS_SLUG || 'admin' != $bp->current_action )
		return false;
	
	if ( $bp->action_variables[0] == $slug )
		return true;
	
	return false;
}

function bp_get_link_has_avatar() {
	global $bp;

	if ( !empty( $_FILES ) || !bp_links_fetch_avatar( array( 'item_id' => $bp->links->current_link->id, 'object' => 'link', 'no_grav' => true ) ) )
		return false;

	return true;
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

		if ( $bp->current_component == $bp->links->slug && 'edit-details' == $bp->action_variables[0] ) {
			if ( isset( $_COOKIE['bp_edit_link_category_id'] ) && $_COOKIE['bp_edit_link_category_id'] != $link->category_id ) {
				$category_id = $_COOKIE['bp_edit_link_category_id'];
			}
		}

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

		if ( $bp->current_component == $bp->links->slug && 'edit-details' == $bp->action_variables[0] ) {
			if ( isset( $_COOKIE['bp_edit_link_url'] ) && $_COOKIE['bp_edit_link_url'] != $link->url ) {
				$url = $_COOKIE['bp_edit_link_url'];
			}
		}

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

		if ( $bp->current_component == $bp->links->slug && 'edit-details' == $bp->action_variables[0] ) {
			if ( isset( $_COOKIE['bp_edit_link_name'] ) && $_COOKIE['bp_edit_link_name'] != $link->name ) {
				$name = $_COOKIE['bp_edit_link_name'];
			}
		}

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

function bp_link_avatar( $args = '' ) {
	echo bp_get_link_avatar( $args );
}
	function bp_get_link_avatar( $args = '' ) {
		global $bp, $links_template;

		$defaults = array(
			'type' => 'full',
			'width' => false,
			'height' => false,
			'class' => 'avatar',
			'id' => false,
			'alt' => __( 'Link avatar', 'buddypress-links' )
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( $links_template->link->embed_status == BP_Links_Link::EMBED_STATUS_ENABLED ) {
			// when embed service is picapp, reduce avatar to 140x140
			if ( 'full' == $type && $links_template->link->embed_service == BP_Links_Link::EMBED_SERVICE_PICAPP ) {
				$width = 140;
				$height = 140;
				$class = 'avatar avatar-embed avatar-embed-picapp';
			} else {
				$class = 'avatar avatar-embed';
			}
		}

		// Fetch the avatar from the folder
		$avatar = bp_links_fetch_avatar( array( 'item_id' => $links_template->link->id, 'object' => 'link', 'type' => $type, 'avatar_dir' => 'link-avatars', 'alt' => $alt, 'css_id' => $id, 'class' => $class, 'width' => $width, 'height' => $height ) );

		return apply_filters( 'bp_get_link_avatar', $avatar );
	}

function bp_link_avatar_thumb() {
	echo bp_get_link_avatar_thumb();
}
	function bp_get_link_avatar_thumb( $link = false ) {
		return bp_get_link_avatar( 'type=thumb' );
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

function bp_link_description_editable() {
	echo bp_get_link_description_editable();
}
	function bp_get_link_description_editable( $link = false ) {
		global $bp, $links_template;

		if ( !$link )
			$link =& $links_template->link;

		$description = $link->description;
		
		if ( $bp->current_component == $bp->links->slug && 'edit-details' == $bp->action_variables[0] ) {
			if ( isset( $_COOKIE['bp_edit_link_description'] ) && $_COOKIE['bp_edit_link_description'] != $link->description ) {
				$description = $_COOKIE['bp_edit_link_description'];
			}
		}

		return apply_filters( 'bp_get_link_description_editable', $description );
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
			$ret_text = 'day';
		} elseif( $time_elapsed > 3600 ) {
			// at least one hour old
			$ret_number = floor( $time_elapsed / 60 / 60 );
			$ret_text = 'hour';
		} elseif( $time_elapsed > 60 ) {
			// at least one minute hold
			$ret_number = floor( $time_elapsed / 60 );
			$ret_text = 'minute';
		} else {
			// only seconds old
			$ret_number = $time_elapsed;
			$ret_text = 'second';
		}

		if ( $ret_number > 1 ) {
			$ret_text .= 's';
		}

		return apply_filters( 'bp_get_link_time_elapsed_text', sprintf( '%d %s %s', $ret_number, __( $ret_text ), 'ago' ) );
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

function bp_link_show_wire_setting( $link = false ) {
	global $links_template;

	if ( !$link )
		$link =& $links_template->link;

	if ( $link->enable_wire )
		echo ' checked="checked"';
}

function bp_link_is_wire_enabled( $link = false ) {
	global $links_template;

	if ( $link )
		return (bool) $link->enable_wire;
	else
		return (bool) $links_template->link->enable_wire;
}

function bp_link_show_status_setting( $setting, $link = false ) {
	global $links_template;

	if ( !$link )
		$link =& $links_template->link;

	if ( $setting == $link->status )
		echo ' checked="checked"';
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
	<li<?php if ( 'link-settings' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->links->slug ?>/<?php echo $link->slug ?>/admin/link-settings"><?php _e( 'Link Settings', 'buddypress-links' ) ?></a></li>
	<li<?php if ( 'link-avatar' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->links->slug ?>/<?php echo $link->slug ?>/admin/link-avatar"><?php _e( 'Link Avatar', 'buddypress-links' ) ?></a></li>

	<?php do_action( 'bp_links_admin_tabs', $current_tab, $link->slug ) ?>
	
	<li<?php if ( 'delete-link' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->root_domain . '/' . $bp->links->slug ?>/<?php echo $link->slug ?>/admin/delete-link"><?php _e( 'Delete Link', 'buddypress-links' ) ?></a></li>
<?php
}

function bp_link_admin_form_action( $page = false ) {
	echo bp_get_link_admin_form_action( $page );
}
	function bp_get_link_admin_form_action( $page = false, $link = false ) {
		global $bp, $links_template;

		if ( !$link )
			$link =& $links_template->link;

		if ( !$page )
			$page = $bp->action_variables[0];

		return apply_filters( 'bp_link_admin_form_action', bp_get_link_permalink( $link ) . '/admin/' . $page );
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

function bp_link_creation_tabs() {
	global $bp;
	
	if ( !is_array( $bp->links->link_creation_steps ) )
		return false;
	
	if ( !$bp->links->current_create_step )
		$bp->links->current_create_step = array_shift( array_keys( $bp->links->link_creation_steps ) );

	$counter = 1;
	foreach ( $bp->links->link_creation_steps as $slug => $step ) {
		$is_enabled = bp_are_previous_link_creation_steps_complete( $slug ); ?>
		
		<li<?php if ( $bp->links->current_create_step == $slug ) : ?> class="current"<?php endif; ?>><?php if ( $is_enabled ) : ?><a href="<?php echo $bp->loggedin_user->domain . $bp->links->slug ?>/create/step/<?php echo $slug ?>"><?php endif; ?><?php echo $counter ?>. <?php echo $step['name'] ?><?php if ( $is_enabled ) : ?></a><?php endif; ?></li><?php
		$counter++;
	}
	
	unset( $is_enabled );
	
	do_action( 'bp_links_creation_tabs' );
}

function bp_link_creation_stage_title() {
	global $bp;
	
	echo apply_filters( 'bp_link_creation_stage_title', '<span>&mdash; ' . $bp->links->link_creation_steps[$bp->links->current_create_step]['name'] . '</span>' );
}

function bp_link_creation_form_action() {
	echo bp_get_link_creation_form_action();
}
	function bp_get_link_creation_form_action() {
		global $bp;
		
		if ( empty( $bp->action_variables[1] ) )
			$bp->action_variables[1] = array_shift( array_keys( $bp->links->link_creation_steps ) );
		
		return apply_filters( 'bp_get_link_creation_form_action', $bp->loggedin_user->domain . $bp->links->slug . '/create/step/' . $bp->action_variables[1] );
	}

function bp_is_link_creation_step( $step_slug ) {
	global $bp;

	// Make sure we are in the links component
	if ( $bp->current_component != BP_LINKS_SLUG || 'create' != $bp->current_action )
		return false;
	
	// If this the first step, we can just accept and return true
	if ( !$bp->action_variables[1] && array_shift( array_keys( $bp->links->link_creation_steps ) ) == $step_slug )
		return true;
	
	// Before allowing a user to see a link creation step we must make sure previous steps are completed
	if ( !bp_is_first_link_creation_step() ) {
		if ( !bp_are_previous_link_creation_steps_complete( $step_slug ) )
			return false;
	}

	// Check the current step against the step parameter
	if ( $bp->action_variables[1] == $step_slug )
		return true;
	
	return false;
}

function bp_is_link_creation_step_complete( $step_slugs ) {
	global $bp;
	
	if ( !$bp->links->completed_create_steps )
		return false;

	if ( is_array( $step_slugs ) ) {
		$found = true;
		
		foreach ( $step_slugs as $step_slug ) {
			if ( !in_array( $step_slug, $bp->links->completed_create_steps ) )
				$found = false;
		}
		
		return $found;
	} else {
		return in_array( $step_slugs, $bp->links->completed_create_steps );
	}

	return true;
}


function bp_are_previous_link_creation_steps_complete( $step_slug ) {
	global $bp;
	
	// If this is the first link creation step, return true
	if ( array_shift( array_keys( $bp->links->link_creation_steps ) ) == $step_slug )
		return true;
	
	reset( $bp->links->link_creation_steps );
	unset( $previous_steps );
		
	// Get previous steps
	foreach ( $bp->links->link_creation_steps as $slug => $name ) {
		if ( $slug == $step_slug )
			break;
	
		$previous_steps[] = $slug;
	}
	
	return bp_is_link_creation_step_complete( $previous_steps );
}

function bp_new_link_id() {
	echo bp_get_new_link_id();
}
	function bp_get_new_link_id() {
		global $bp;
		return apply_filters( 'bp_get_new_link_id', $bp->links->new_link_id );
	}

function bp_new_link_category_id() {
	echo bp_get_new_link_category_id();
}
	function bp_get_new_link_category_id() {
		global $bp;

		$category_id = $bp->links->current_link->category_id;

		if ( empty( $category_id ) && isset($_COOKIE['bp_new_link_category_id']) )
			$category_id = $_COOKIE['bp_new_link_category_id'];

		return apply_filters( 'bp_get_new_link_category_id', $category_id );
	}

function bp_new_link_url() {
	echo bp_get_new_link_url();
}
	function bp_get_new_link_url() {
		global $bp;
		
		$link_url = $bp->links->current_link->url;

		if ( empty( $link_url ) && isset($_COOKIE['bp_new_link_url']) )
			$link_url = $_COOKIE['bp_new_link_url'];

		return apply_filters( 'bp_get_new_link_url', $link_url );
	}

function bp_new_link_name() {
	echo bp_get_new_link_name();
}
	function bp_get_new_link_name() {
		global $bp;

		$link_name = $bp->links->current_link->name;

		if ( empty( $link_name ) && isset($_COOKIE['bp_new_link_name']) )
			$link_name = $_COOKIE['bp_new_link_name'];

		return apply_filters( 'bp_get_new_link_name', $link_name );
	}

function bp_new_link_description() {
	echo bp_get_new_link_description();
}
	function bp_get_new_link_description() {
		global $bp;

		$link_description = $bp->links->current_link->description;

		if ( empty( $link_description ) && isset($_COOKIE['bp_new_link_description']) )
			$link_description = $_COOKIE['bp_new_link_description'];

		return apply_filters( 'bp_get_new_link_description', $link_description );
	}

function bp_new_link_enable_wire() {
	echo bp_get_new_link_enable_wire();
}
	function bp_get_new_link_enable_wire() {
		global $bp;
		return (int) apply_filters( 'bp_get_new_link_enable_wire', $bp->links->current_link->enable_wire );
	}

function bp_new_link_status() {
	echo bp_get_new_link_status();
}
	function bp_get_new_link_status() {
		global $bp;
		return apply_filters( 'bp_get_new_link_status', $bp->links->current_link->status );
	}

function bp_new_link_avatar( $args = '' ) {
	echo bp_get_new_link_avatar( $args );
}
	function bp_get_new_link_avatar( $args = '' ) {
		global $bp;
			
		$defaults = array(
			'type' => 'full',
			'width' => false,
			'height' => false,
			'class' => 'avatar',
			'id' => 'avatar-crop-preview',
			'alt' => __( 'Link avatar', 'buddypress-links' )
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
	
		return apply_filters( 'bp_get_new_link_avatar', bp_links_fetch_avatar( array( 'item_id' => $bp->links->current_link->id, 'object' => 'link', 'type' => $type, 'avatar_dir' => 'link-avatars', 'alt' => $alt, 'width' => $width, 'height' => $height, 'class' => $class ) ) );
	}

function bp_link_creation_previous_link() {
	echo bp_get_link_creation_previous_link();
}
	function bp_get_link_creation_previous_link() {
		global $bp;
		
		foreach ( $bp->links->link_creation_steps as $slug => $name ) {
			if ( $slug == $bp->action_variables[1] )
				break;
	
			$previous_steps[] = $slug;
		}

		return apply_filters( 'bp_get_link_creation_previous_link', $bp->loggedin_user->domain . $bp->links->slug . '/create/step/' . array_pop( $previous_steps ) );
	}

function bp_is_last_link_creation_step() {
	global $bp;
	
	$last_step = array_pop( array_keys( $bp->links->link_creation_steps ) );

	if ( $last_step == $bp->links->current_create_step )
		return true;
	
	return false;
}

function bp_is_first_link_creation_step() {
	global $bp;
	
	$first_step = array_shift( array_keys( $bp->links->link_creation_steps ) );

	if ( $first_step == $bp->links->current_create_step )
		return true;
	
	return false;
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

		// defaults
		$width = false;
		$height = false;
		$class = 'avatar';

		if ( $site_links_template->link->embed_status == BP_Links_Link::EMBED_STATUS_ENABLED ) {
			// when embed service is picapp, reduce avatar to 140x140
			if ( $site_links_template->link->embed_service == BP_Links_Link::EMBED_SERVICE_PICAPP ) {
				$width = 140;
				$height = 140;
				$class = 'avatar avatar-embed avatar-embed-picapp';
			} else {
				$class = 'avatar avatar-embed';
			}
		}

		return apply_filters( 'bp_the_site_link_avatar', bp_links_fetch_avatar( array( 'item_id' => $site_links_template->link->id, 'object' => 'link', 'type' => 'full', 'avatar_dir' => 'link-avatars', 'height' => $height, 'width' => $width, 'class' => $class, 'alt' => __( 'Link Avatar', 'buddypress-links' ) ) ) );
	}

function bp_the_site_link_avatar_thumb() {
	echo bp_get_the_site_link_avatar_thumb();
}
	function bp_get_the_site_link_avatar_thumb() {
		global $site_links_template;
		
		return apply_filters( 'bp_get_the_site_link_avatar_thumb', bp_links_fetch_avatar( array( 'item_id' => $site_links_template->link->id, 'object' => 'link', 'type' => 'thumb', 'avatar_dir' => 'link-avatars', 'alt' => __( 'Link Avatar', 'buddypress-links' ) ) ) );
	}

function bp_the_site_link_avatar_mini() {
	echo bp_get_the_site_link_avatar_mini();
}
	function bp_get_the_site_link_avatar_mini() {
		global $site_links_template;

		return apply_filters( 'bp_get_the_site_link_avatar_mini', bp_links_fetch_avatar( array( 'item_id' => $site_links_template->link->id, 'object' => 'link', 'type' => 'thumb', 'width' => 30, 'height' => 30, 'avatar_dir' => 'link-avatars', 'alt' => __( 'Link Avatar', 'buddypress-links' ) ) ) );
	}

function bp_the_site_link_user_avatar() {
	echo bp_get_the_site_link_user_avatar();
}
	function bp_get_the_site_link_user_avatar() {
		global $site_links_template;

		return apply_filters( 'bp_the_site_link_user_avatar', bp_link_user_avatar( null, $site_links_template->link ) );
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

	echo sprintf( '<select id="%s"%s>', $element_id, $class_string );
	echo '<option value="">All</option>';

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
		'avatar_type' => 'thumb'
	);

	return wp_parse_args( $args, $defaults );
}

function bp_link_list( $args = array() ) {

	bp_link_list_open();

	while ( bp_links() ) {
		bp_the_link();

		// set item_id
		$args['item_id'] = bp_get_link_id();

		bp_link_list_item_open();
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

		bp_link_list_item_open();
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

function bp_link_list_item_open() {
	do_action( 'bp_before_my_links_list_item' );
	echo '<li>' . PHP_EOL;
	do_action( 'bp_before_my_links_list_item_content' );
}

function bp_link_list_item_close() {
	do_action( 'bp_after_my_links_list_item_content' );
	echo '</li>' . PHP_EOL;
	do_action( 'bp_after_my_links_list_item' );
}

function bp_link_list_item_left( $args = array() ) {

	extract( bp_link_list_parse_args( $args ) );

	switch ( $avatar_type ) {
		case 'full':
			$div_class = 'link-list-left-wide';
			$thumb_html = ( $the_site ) ? bp_get_the_site_link_avatar() : bp_get_link_avatar();
			break;
		case 'thumb':
		default:
			$div_class = 'link-list-left';
			$thumb_html = ( $the_site ) ? bp_get_the_site_link_avatar_thumb() : bp_get_link_avatar_thumb();
	}

	printf('
		%1$s<div class="%2$s">%3$s
			<a href="%4$s">%5$s</a>%6$s
		%7$s</div>%8$s',
		apply_filters( 'bp_before_my_links_list_item_left', '', $args ), // arg 1
		$div_class, // arg 2
		apply_filters( 'bp_before_my_links_list_item_left_content', '', $args ), // arg 3
		( $the_site ) ? bp_get_the_site_link_permalink() : bp_get_link_permalink(), // arg 4
		$thumb_html, // arg 5
		apply_filters( 'bp_after_my_links_list_item_avatar', '', $args ), // arg 6
		apply_filters( 'bp_after_my_links_list_item_left_content', '', $args ), // arg 7
		apply_filters( 'bp_after_my_links_list_item_left', '', $args ) // arg 8
	);
}

function bp_link_list_item_right( $args = array() ) {

	extract( bp_link_list_parse_args( $args ) );

	switch ( $avatar_type ) {
		case 'full':
//			$vote_panel = bp_get_link_list_item_vote_panel( $args );
			$vote_panel = null;
			break;
		case 'thumb':
		default:
			$vote_panel = null;
			
	}
	
	printf('
		%1$s<div class="link-list-right">%2$s
			%3$s%4$s%5$s
		%6$s</div>%7$s',
		apply_filters( 'bp_before_my_links_list_item_right', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_right_content', '', $args ), // arg 2
		bp_get_link_list_item_title( $args ), // arg 3
		bp_get_link_list_item_description( $args ), // arg 4
		$vote_panel, // arg 5
		apply_filters( 'bp_after_my_links_list_item_right_content', '', $args ), // arg 6
		apply_filters( 'bp_after_my_links_list_item_right', '', $args ) // arg 7
	);
}

function bp_link_list_item_footer( $args = array() ) {

	extract( bp_link_list_parse_args( $args ) );

	switch ( $avatar_type ) {
		case 'full':
			$left_div_class = 'link-list-footer-left-wide';
			$right_div_class = 'link-list-footer-right-wide';
			break;
		case 'thumb':
		default:
			$left_div_class = 'link-list-footer-left';
			$right_div_class = 'link-list-footer-right';
	}

	printf('
		%1$s<div class="link-list-footer">%2$s
			<div class="%3$s">
				%4$s
			</div>
			<div class="%5$s">
				%6$s
			</div>
		%7$s</div>%8$s',
		apply_filters( 'bp_before_my_links_list_item_footer', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_footer_content', '', $args ), // arg 2
		$left_div_class, // arg 3
		bp_get_link_list_item_vote_panel( $args ), // arg 4
		$right_div_class, // arg 5
		bp_get_link_list_item_xtrabar( $args ), // arg 6
		apply_filters( 'bp_after_my_links_list_item_footer_content', '', $args ), // arg 7
		apply_filters( 'bp_after_my_links_list_item_footer', '', $args ) // arg 8
	);
}

function bp_link_list_item_vote_panel( $args = array() ) {
	echo bp_get_link_list_item_vote_panel( $args );
}
function bp_get_link_list_item_vote_panel( $args = array() ) {

	extract( bp_link_list_parse_args( $args ) );

	return sprintf('
		%1$s<div class="link-vote-panel" id="link-vote-panel-%3$d">
			%2$s<div class="clickers">
				<a href="#vu" id="vote-up-%3$d" class="vote up"></a>
				<div id="vote-total-%3$d" class="vote-total">%4$+d</div>
				<a href="#vd" id="vote-down-%3$d"  class="vote down"></a>
			</div>
			<div class="vote-count">
				<span id="vote-count-%3$d">%5$d</span> %6$s
			</div>
		%7$s</div>%8$s',
		apply_filters( 'bp_before_my_links_list_item_vote_panel', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_vote_panel_content', '', $args ), // arg 2
		( $the_site ) ? bp_get_the_site_link_id() : bp_get_link_id(), // arg 3
		( $the_site ) ? bp_get_the_site_link_vote_total() : bp_get_link_vote_total(), // arg 4
		( $the_site ) ? bp_get_the_site_link_vote_count() : bp_get_link_vote_count(), // arg 5
		__('Votes', 'buddypress-links'), // arg 6
		apply_filters( 'bp_after_my_links_list_item_vote_panel_content', '', $args ),	// arg 7
		apply_filters( 'bp_after_my_links_list_item_vote_panel', '', $args )	// arg 8
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
			%1$s<a href="%2$s" class="comments">%3$d&nbsp;Comments</a>%4$s',
			apply_filters( 'bp_before_my_links_list_item_xtrabar_comments', '', $args ), // arg 1
			( $the_site ) ? bp_get_the_site_link_wire_permalink() : bp_get_link_wire_permalink(), // arg 2
			( $the_site ) ? bp_get_the_site_link_wire_count() : bp_get_link_wire_count(), // arg 3
			apply_filters( 'bp_after_my_links_list_item_xtrabar_comments', '', $args ) // arg 4
		);
	}

	return sprintf('
		%1$s<div class="xtrabar">%2$s
			%3$s<a href="%4$s" class="home">Home</a>%5$s%6$s
			%7$s<span class="owner">%8$s&nbsp;%9$s %10$s %11$s</span>%12$s
		%13$s</div>%14$s',
		apply_filters( 'bp_before_my_links_list_item_xtrabar_content', '', $args ), // arg 1
		apply_filters( 'bp_before_my_links_list_item_xtrabar', '', $args ), // arg 2
		apply_filters( 'bp_before_my_links_list_item_xtrabar_home', '', $args ), // arg 3
		( $the_site ) ? bp_get_the_site_link_permalink() : bp_get_link_permalink(), // arg 4
		apply_filters( 'bp_after_my_links_list_item_xtrabar_home', '', $args ), // arg 5
		( $wire_enabled ) ? $wire_link : null, // arg 6
		apply_filters( 'bp_before_my_links_list_item_xtrabar_userlink', '', $args ), // arg 7
		( $the_site ) ? bp_get_the_site_link_user_avatar_mini() : bp_get_link_user_avatar_mini(), // arg 8
		( $the_site ) ? bp_get_the_site_link_userlink() : bp_get_link_userlink(), // arg 9
		__( 'created', 'buddypress-links' ), // arg 10
		( $the_site ) ? bp_get_the_site_link_time_elapsed_text() : bp_get_link_time_elapsed_text(), // arg 11
		apply_filters( 'bp_after_my_links_list_item_xtrabar_userlink', '', $args ), // arg 12
		apply_filters( 'bp_after_my_links_list_item_xtrabar', '', $args ), // arg 13
		apply_filters( 'bp_after_my_links_list_item_xtrabar_content', '', $args ) // arg 14
	);
}
?>