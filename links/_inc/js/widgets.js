jQuery(document).ready( function() {
	jQuery("div#link-list-options a").livequery('click',
		function() {

			jQuery('#ajax-loader-links').toggle();

			jQuery("div#link-list-options a").removeClass("selected");
			jQuery(this).addClass('selected');

			jQuery.post( ajaxurl, {
				action: 'widget_links_list',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce-links").val(),
				'max_links': jQuery("input#links_widget_max").val(),
				'avatar_type': jQuery("input#links_avatar_type").val(),
				'filter': jQuery(this).attr('id')
			},
			function(response)
			{	
				jQuery('#ajax-loader-links').toggle();
				links_widget_response(response);
			});
		
			return false;
		}
	);
});

function links_widget_response(response) {
	response = response.substr(0, response.length-1);
	response = response.split('[[SPLIT]]');

	if ( response[0] != "-1" ) {
		jQuery("ul#link-list").fadeOut(200,
			function() {
				jQuery("ul#link-list").html(response[1]);
				jQuery("ul#link-list").fadeIn(200);
			}
		);

	} else {					
		jQuery("ul#link-list").fadeOut(200,
			function() {
				var message = '<p>' + response[1] + '</p>';
				jQuery("ul#link-list").html(message);
				jQuery("ul#link-list").fadeIn(200);
			}
		);
	}
}