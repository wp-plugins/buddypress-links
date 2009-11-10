<?php
/**
 * Link Embedded Media Functions
 */

/*** General functions ********************************************************/

function bp_links_embed_trim_strip( $string ) {
	return  preg_replace( '/[\n\r]+/u', '', stripslashes( trim( $string ) ) );
}

function bp_links_embed_clean_whitespace( $string ) {
	return  preg_replace( '/\t+|\s{2,}/u', ' ', trim( $string ) );
}

function bp_links_embed_upload_from_url( $url ) {

	// build up our $files array.
	//its the same format as returned by wp_handle_upload()
	$files['file'] = null;
	$files['url'] = null;
	$files['type'] = null;
	$files['error'] = 'File upload failed!';

	// TODO handle a timeout error?
	// make sure we timeout before too long
	ini_set( 'default_socket_timeout', 10 );

	// get path from URL
	$url_parts = parse_url( $url );

	if ( $url_parts['path'] ) {

		// nice, have the path
		$url_path = $url_parts['path'];
		
		// get path info
		$path_parts = pathinfo( $url_parts['path'] );

		if ( $path_parts['basename'] && $path_parts['extension'] ) {
			$file_name = $path_parts['basename'];
			$file_extension = $path_parts['extension'];
		} else {
			// error
			return $files;
		}
	} else {
		// error
		return $files;
	}

	// grab remote file
	$remote_file = file_get_contents( $url );

	// make sure we got it
	if ( $remote_file ) {

		// get upload dir info
		add_filter( 'upload_dir', 'bp_links_avatar_upload_dir', 10, 0 );
		$upload_dir = wp_upload_dir();

		// make sure upload dir exists and is writable
		if ( !is_writable( $upload_dir['path'] ) ) {
			$files['error'] = 'Upload dir is not writable!';
			return $files;
		}

		// set local file path and url path
		$files['file'] = sprintf( '%s/remote_upload_%s.%s', $upload_dir['path'], md5( $file_name ), $file_extension );
		$files['url'] = sprintf( '%s/%s', $upload_dir['url'], $file_name );

		// try to write the file
		$bytes_written = file_put_contents( $files['file'], $remote_file );

		if ( $bytes_written ) {

			// get mime type
			$mimes = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif' => 'image/gif',
				'png' => 'image/png'
			);

			$wp_filetype = wp_check_filetype( $files['file'], $mimes );

			if ( $wp_filetype['type'] ) {
				$files['type'] = $wp_filetype['type'];
				$files['error'] = false;
			} else {
				$files['error'] = 'Only JPEG, GIF and PNG files are supported!';
			}
		}
	}

	return $files;
}

function bp_links_embed_download_avatar( $url ) {
	global $bp;

	require_once( ABSPATH . '/wp-admin/includes/image.php' );

	$bp->avatar_admin->original = bp_links_embed_upload_from_url( $url );

	// Move the file to the correct upload location.
	if ( !empty( $bp->avatar_admin->original['error'] ) ) {
		bp_core_add_message( sprintf( '%1$s %2$s', __( 'Upload Failed! Error was:', 'buddypress-links' ), $bp->avatar_admin->original['error'] ), 'error' );
		return false;
	}

	// Resize the image down to something manageable and then delete the original
	if ( getimagesize( $bp->avatar_admin->original['file'] ) > BP_AVATAR_ORIGINAL_MAX_WIDTH ) {
		$bp->avatar_admin->resized = wp_create_thumbnail( $bp->avatar_admin->original['file'], BP_AVATAR_ORIGINAL_MAX_WIDTH );
	}

	$bp->avatar_admin->image = new stdClass;

	// We only want to handle one image after resize.
	if ( empty( $bp->avatar_admin->resized ) )
		$bp->avatar_admin->image->dir = $bp->avatar_admin->original['file'];
	else {
		$bp->avatar_admin->image->dir = $bp->avatar_admin->resized;
		@unlink( $bp->avatar_admin->original['file'] );
	}

	/* Set the url value for the image */
	$bp->avatar_admin->image->url = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $bp->avatar_admin->image->dir );

	return true;
}

function bp_links_embed_handle_upload( BP_Links_Link $link, $embed_code ) {

	// defaults
	$service = null;
	$data = null;
	$image_url = null;

	// picapp?
	if ( bp_links_embed_picapp_check( $embed_code ) ) {
		$service = BP_Links_Link::EMBED_SERVICE_PICAPP;
		$data = bp_links_embed_picapp_match( $embed_code );
		if ( $data ) {
			$image_url = bp_links_embed_picapp_img_url( $data );
		} else {
			bp_core_add_message( __( 'The code you entered is not valid PicApp embedding code.', 'buddypress-links' ), 'error' );
			return false;
		}
	}

	// fotoglif?
	if ( bp_links_embed_fotoglif_check( $embed_code ) ) {
		$service = BP_Links_Link::EMBED_SERVICE_FOTOGLIF;
		$data = bp_links_embed_fotoglif_match( $embed_code );
		if ( $data ) {
			$image_url = bp_links_embed_fotoglif_img_url( $data );
		} else {
			bp_core_add_message( __( 'The code you entered is not valid Fotoglif embedding code.', 'buddypress-links' ), 'error' );
			return false;
		}
	}

	// if no service, data and image_url by now then throw a generic error
	if ( !$service || !$data || !$image_url ) {
		bp_core_add_message( __( 'The embedding code you entered was not recognized.', 'buddypress-links' ), 'error' );
		return false;
	}

	// try to download the image for avatar creation
	if ( bp_links_embed_download_avatar( $image_url ) ) {

		$link->embed_service = $service;
		$link->embed_status = BP_Links_Link::EMBED_STATUS_PARTIAL;
		$link->embed_data = $data;

		if ( $link->save() ) {
			return true;
		} else {
			bp_core_add_message( __( 'A fatal error has occurred. Please try again in a moment.', 'buddypress-links' ), 'error' );
			return false;
		}

	} else {
		return false;
	}
}

function bp_links_embed_handle_crop( BP_Links_Link $link ) {
	if ( ( $link->embed_service ) && $link->embed_status == BP_Links_Link::EMBED_STATUS_PARTIAL ) {
		$link->embed_status = BP_Links_Link::EMBED_STATUS_ENABLED;
		return $link->save();
	}
}

/*** PicApp functions *********************************************************/

function bp_links_embed_picapp_check( $embed_code ) {
	return preg_match( '/picapp\.com/', $embed_code );
}

function bp_links_embed_picapp_match( $embed_code ) {

	$embed_code = bp_links_embed_trim_strip( $embed_code );
	$embed_code = bp_links_embed_clean_whitespace( $embed_code );
	
	$href_bits = bp_links_embed_picapp_match_href( $embed_code );
	$img_bits = bp_links_embed_picapp_match_img( $embed_code );

	if ( ( $href_bits ) && ( $img_bits ) && $href_bits['iid'] == $img_bits['imageId'] ) {
		return
			array(
				'href' => $href_bits,
				'img' => $img_bits
			);
	} else {
		// not valid PicApp embed code
		return false;
	}
}

function bp_links_embed_picapp_match_href( $string ) {
	if  ( preg_match( '/<a\shref="http:\/\/view\.picapp\.com\/default\.aspx\?(term=([^&]{1,50})&)?iid=(\d+)"[^>]*>/', $string, $matches ) ) {
		return
			array(
				'term' => $matches[2],
				'iid' => $matches[3]
			);
	} else {
		return false;
	}
}

function bp_links_embed_picapp_match_img( $string ) {

	$arr_to_return = array();

	// match img tag and find path
	if  ( preg_match( '/<img\ssrc="http:\/\/cdn\.picapp\.com([\/a-zA-z0-9]{1,50}\/[\w-]{1,100}\.jpg)\?[^"]+"[^>]+>/', $string, $matches ) ) {
		$img_tag = $matches[0];
		$arr_to_return['path'] = $matches[1];
	} else {
		return false;
	}

	// match adImageId
	if  ( preg_match( '/adImageId=(\d{1,50})/', $img_tag, $matches ) ) {
		$arr_to_return['adImageId'] = $matches[1];
	} else {
		return false;
	}

	// match adImageId
	if  ( preg_match( '/imageId=(\d{1,50})/', $img_tag, $matches ) ) {
		$arr_to_return['imageId'] = $matches[1];
	} else {
		return false;
	}

	// match width
	if  ( preg_match( '/width="(\d{1,4})"/', $img_tag, $matches ) ) {
		$arr_to_return['width'] = $matches[1];
	} else {
		return false;
	}

	// match height
	if  ( preg_match( '/height="(\d{1,4})"/', $img_tag, $matches ) ) {
		$arr_to_return['height'] = $matches[1];
	} else {
		return false;
	}

	// match alt text
	if  ( preg_match( '/alt="([^"]{1,100})"/', $img_tag, $matches ) ) {
		$arr_to_return['alt'] = $matches[1];
	} else {
		return false;
	}

	return $arr_to_return;
}


function bp_links_embed_picapp_href_url( $embed_data ) {
	return sprintf(
		'http://view.picapp.com/default.aspx?term=%s&amp;iid=%d',
		esc_url( $embed_data['href']['term'] ),
		$embed_data['href']['iid']
	);
}

function bp_links_embed_picapp_img_url( $embed_data ) {
	return sprintf(
		'http://cdn.picapp.com%s?adImageId=%d&amp;imageId=%d',
		esc_url( $embed_data['img']['path'] ),
		$embed_data['img']['adImageId'],
		$embed_data['img']['imageId']
	);
}


/*** Fotoglif functions *********************************************************/

function bp_links_embed_fotoglif_check( $embed_code ) {
	return preg_match( '/fotoglif\.com/', $embed_code );
}

function bp_links_embed_fotoglif_publisher_check( $embed_code ) {
	return preg_match( '/embed_login\.js/', $embed_code );
}

function bp_links_embed_fotoglif_iframe_check( $embed_code ) {
	return preg_match( '/iframe/i', $embed_code );
}

function bp_links_embed_fotoglif_match( $embed_code ) {

	$embed_code = bp_links_embed_trim_strip( $embed_code );
	$embed_code = bp_links_embed_clean_whitespace( $embed_code );

	if ( bp_links_embed_fotoglif_publisher_check( $embed_code ) ) {
		$style_bits = bp_links_embed_fotoglif_match_img( $embed_code );
	} else {
		$style_bits = bp_links_embed_fotoglif_match_div( $embed_code );
	}

	$script_bits = bp_links_embed_fotoglif_match_script( $embed_code );

	if ( ( $style_bits ) && ( $script_bits ) ) {

		// start building up embed data array
		$embed_data =
			array(
				'div' => $style_bits,
				'script' => $script_bits
			);

		// call fotoglif api to augment the data we have already
		return bp_links_embed_fotoglif_api_image_data( $embed_data );
		
	} else {
		// not valid Fotoglif embed code
		return false;
	}
}

function bp_links_embed_fotoglif_match_div( $string ) {

	$arr_to_return = array();

	// match img tag and find path
	if  ( preg_match( '/<div\sid="fotoglif_place_holder_\d+"([^>]+)>/', $string, $matches ) ) {
		$div_attributes = $matches[1];
	} else {
		return false;
	}

	// match width
	if  ( preg_match( '/[^-]width:\s*(\d{1,4})px/', $div_attributes, $matches ) ) {
		$arr_to_return['width'] = $matches[1];
	} else {
		return false;
	}

	// match height
	if  ( preg_match( '/[^-]height:\s*(\d{1,4})px/', $div_attributes, $matches ) ) {
		$arr_to_return['height'] = $matches[1];
	} else {
		return false;
	}

	return $arr_to_return;
}

function bp_links_embed_fotoglif_match_img( $string ) {

	$arr_to_return = array();

	// match img tag and find get src URL
	if  ( preg_match( '/<img([^>]+)>/', $string, $matches ) ) {
		$img_attributes = $matches[1];
	} else {
		return false;
	}

	// match width
	if  ( preg_match( '/width:\s*(\d+)px/', $img_attributes, $matches ) ) {
		$arr_to_return['width'] = $matches[1];
	} else {
		return false;
	}

	// no height given by this embed method
	$arr_to_return['height'] = null;

	// match image hash
	/*
	if  ( preg_match( '/src="[^"]+\/([A-Za-z0-9]{1,20})\.jpg"/', $img_attributes, $matches ) ) {
		$arr_to_return['image_hash'] = $matches[1];
	} else {
		return false;
	}
	*/

	return $arr_to_return;
}

function bp_links_embed_fotoglif_match_script( $string ) {

	$arr_to_return = array();

	// match img tag and find path
	if  ( preg_match( '/<script\stype="[^"]+"\ssrc="http:\/\/www\.fotoglif\.com\/(embed\/)?embed(\.py|_login\.js)\?([^"]+)">\s*<\/script>/', $string, $matches ) ) {
		$query_string = $matches[3];
	} else {
		return false;
	}

	// match hash
	if  ( preg_match( '/hash=([a-z0-9]{1,20})/', $query_string, $matches ) ) {
		$arr_to_return['album_hash'] = $matches[1];
	} else {
		return false;
	}

	// match size
	if  ( preg_match( '/size=(small|medium|large)/', $query_string, $matches ) ) {
		$arr_to_return['size'] = $matches[1];
	} else {
		$arr_to_return['size'] = 'medium';
	}

	// match imageuid
	if  ( preg_match( '/imageuid=(\d{1,20})/', $query_string, $matches ) ) {
		$arr_to_return['imageuid'] = $matches[1];
	} else {
		return false;
	}

	// match layout
	if  ( preg_match( '/layout=([\w-])/', $query_string, $matches ) ) {
		$arr_to_return['layout'] = $matches[1];
	} else {
		$arr_to_return['layout'] = null;
	}

	// match jpgembed
	if  ( preg_match( '/jpgembed=(yes|no)/', $query_string, $matches ) ) {
		$arr_to_return['jpgembed'] = $matches[1];
	} else {
		return false;
	}

	// match pubID
	if  ( preg_match( '/pubID=([a-z0-9]{1,20})/', $query_string, $matches ) ) {
		$arr_to_return['pubID'] = $matches[1];
	} else {
		$arr_to_return['pubID'] = null;
	}

	// match pubid
	if  ( preg_match( '/pubid=([a-z0-9]{1,20})/', $query_string, $matches ) ) {
		$arr_to_return['pubid'] = $matches[1];
	} else {
		$arr_to_return['pubid'] = null;
	}

	return $arr_to_return;
}

function bp_links_embed_fotoglif_img_url( $embed_data, $size = 'large' ) {

	switch ( $size ) {
		case 'medium':
			$size_path = 'medium';
			break;
		default:
		case 'large':
			$size_path = 'large';
			break;
	}

	return sprintf( 'http://gallery.fotoglif.com/images/%s/%s.jpg', $size_path, $embed_data['api']['image_hash'] );
}

function bp_links_embed_fotoglif_script_url( $embed_data ) {
	return sprintf(
		'http://www.fotoglif.com/embed/embed.py?hash=%1$s&amp;size=%2$s&amp;imageuid=%3$d&amp;layout=%4$s&amp;jpgembed=%5$s&amp;pubID=&amp;pubid=%6$s',
		$embed_data['script']['album_hash'], // arg 1
		$embed_data['script']['size'], // arg 2
		$embed_data['script']['imageuid'], // arg 3
		$embed_data['script']['layout'], // arg 4
		$embed_data['script']['jpgembed'], // arg 5
		BP_LINKS_EMBED_FOTOGLIF_PUBID // arg 6
	);
}

function bp_links_embed_fotoglif_api_image_data( $embed_data ) {

	// make sure we timeout before too long
	ini_set( 'default_socket_timeout', 10 );

	// build the URL we will be querying for image data
	$api_url = sprintf( 'http://api.fotoglif.com/image/get?image_uid=%s', $embed_data['script']['imageuid'] );

	// grab JSON data
	$json_data = trim( file_get_contents( $api_url ) );

	// make sure we got it
	if ( $json_data ) {
		// decode json data
		$json_data_decoded = json_decode( $json_data, true );
		// if decoding successful, append info to embed data
		if ( isset( $json_data_decoded['response'][0]['image_hash'] ) ) {
			$embed_data['api']['image_hash'] = $json_data_decoded['response'][0]['image_hash'];
			$embed_data['api']['height'] = $json_data_decoded['response'][0]['height'];
			$embed_data['api']['width'] = $json_data_decoded['response'][0]['width'];
			$embed_data['api']['album_uid'] = $json_data_decoded['response'][0]['album_uid'];
			// awesome, return it
			return $embed_data;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/************************************************
 * Links Embedded Media Template Helper Functions
 */

function bp_link_embed_is_enabled() {
	global $links_template;
	return ( BP_Links_Link::EMBED_STATUS_ENABLED == $links_template->link->embed_status );
}

function bp_link_embed_tags() {
	echo bp_get_link_embed_tags();
}
	function bp_get_link_embed_tags() {
		global $links_template;

		if ( true !== bp_link_embed_is_enabled() ) {
			return null;
		}

		switch ( $links_template->link->embed_service ) {
			case BP_Links_Link::EMBED_SERVICE_PICAPP:
				return bp_get_link_embed_picapp_tags( $links_template->link->embed_data );
			case BP_Links_Link::EMBED_SERVICE_FOTOGLIF:
				return bp_get_link_embed_fotoglif_tags( $links_template->link->embed_data );
			default:
				// status enabled but no matching service is unlikely
				return false;
		}
	}

/*** PicApp template tags ***/

function bp_link_embed_picapp_tags( $embed_data ) {
	echo bp_get_link_embed_picapp_tags( $embed_data );
}
	function bp_get_link_embed_picapp_tags( $embed_data ) {
		return
			bp_get_link_embed_picapp_href_tag( $embed_data, bp_get_link_embed_picapp_img_tag( $embed_data ) ) .
			bp_get_link_embed_picapp_script_tag();
	}

function bp_link_embed_picapp_href_tag( $embed_data, $content ) {
	echo bp_get_link_embed_picapp_href_tag( $embed_data, $content );
}
	function bp_get_link_embed_picapp_href_tag( $embed_data, $content ) {
		return sprintf(
			'<a href="%s" target="_blank">%s</a>',
			bp_links_embed_picapp_href_url( $embed_data ),
			$content
		);
	}

function bp_link_embed_picapp_img_tag( $embed_data ) {
	echo bp_get_link_embed_picapp_img_tag( $embed_data );
}
	function bp_get_link_embed_picapp_img_tag( $embed_data ) {
		return sprintf(
			'<img src="%s" width="%d" height="%d"  border="0" alt="%s"/>',
			bp_links_embed_picapp_img_url( $embed_data ),
			$embed_data['img']['width'],
			$embed_data['img']['height'],
			esc_attr( $embed_data['img']['alt'] )
		);
	}

function bp_link_embed_picapp_script_tag() {
	echo bp_get_link_embed_picapp_script_tag();
}
	function bp_get_link_embed_picapp_script_tag() {
		return '<script type="text/javascript" src="http://cdn.pis.picapp.com/IamProd/PicAppPIS/JavaScript/PisV4.js"></script>';
	}


/*** Fotoglif template tags ***/

function bp_link_embed_fotoglif_tags( $embed_data ) {
	echo bp_get_link_embed_fotoglif_tags( $embed_data );
}
	function bp_get_link_embed_fotoglif_tags( $embed_data ) {
		return
			bp_get_link_embed_fotoglif_div_tag( $embed_data ) .
			bp_get_link_embed_fotoglif_script_tag( $embed_data );
	}

function bp_link_embed_fotoglif_div_tag( $embed_data ) {
	echo bp_get_link_embed_fotoglif_div_tag( $embed_data );
}
	function bp_get_link_embed_fotoglif_div_tag( $embed_data ) {

		$width_style = ( empty( $embed_data['div']['width'] ) ) ? '' : sprintf( ' width: %1$dpx;', $embed_data['div']['width'] );
		$height_style = ( empty( $embed_data['div']['height'] ) ) ? '' : sprintf( ' height: %1$dpx;', $embed_data['div']['height'] );

		return sprintf(
			'<div id="fotoglif_place_holder_%1$d" style="border-style: double; border-width: 5px; border-color: #bbbbbb; background-color: rgb(122, 122, 122);"%2$s%3$s></div>',
			$embed_data['script']['imageuid'], // arg 1
			$width_style, // arg 2
			$height_style // arg 3
		);
	}

function bp_link_embed_fotoglif_script_tag( $embed_data ) {
	echo bp_get_link_embed_fotoglif_script_tag( $embed_data );
}
	function bp_get_link_embed_fotoglif_script_tag( $embed_data ) {
		return sprintf(
			'<script type="text/javascript" src="%s"></script>',
			bp_links_embed_fotoglif_script_url( $embed_data )
		);
	}
?>