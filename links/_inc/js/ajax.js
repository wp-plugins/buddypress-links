jQuery(document).ready( function() {

	jQuery("div#link-loop div#pag a").livequery('click',
		function() {
			jQuery('.ajax-loader').toggle();

			var lpage = jQuery(this).attr('href');
			lpage = lpage.split('=');

			jQuery.post( ajaxurl, {
				action: 'link_filter',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce_link_filter").val(),
				'lpage': lpage[1],
				'link-filter-box': jQuery("#link-filter-box").val()
			},
			function(response)
			{
				response = response.substr( 0, response.length - 1 );

				jQuery("div#link-loop").fadeOut(200,
					function() {
						jQuery('.ajax-loader').toggle();
						jQuery("div#link-loop").html(response);
						jQuery("div#link-loop").fadeIn(200);
					}
				);
			});

			return false;
		}
	);

	jQuery("input#link-filter-box").keyup(
		function(e) {
			if ( e.which == 13 ) {
				jQuery('.ajax-loader').toggle();

				jQuery.post( ajaxurl, {
					action: 'link_filter',
					'cookie': encodeURIComponent(document.cookie),
					'_wpnonce': jQuery("input#_wpnonce_link_filter").val(),
					'link-filter-box': jQuery("#link-filter-box").val()
				},
				function(response)
				{
					response = response.substr( 0, response.length - 1 );

					jQuery("div#link-loop").fadeOut(200,
						function() {
							jQuery('.ajax-loader').toggle();
							jQuery("div#link-loop").html(response);
							jQuery("div#link-loop").fadeIn(200);
						}
					);
				});

				return false;
			}
			
			return true;
		}
	);

	jQuery("div#links-directory-page ul#letter-list li a").livequery('click',
		function() {
			
			jQuery('.ajax-loader').toggle();

			jQuery("div#link-list-options a.selected").removeClass("selected");
			jQuery("#letter-list li a.selected").removeClass("selected");

			jQuery(this).addClass('selected');
			jQuery("input#links_search").val('');

			var letter = jQuery(this).attr('id')
			letter = letter.split('-');
			jQuery("input#letter").val(letter[1]);

			jQuery.post( ajaxurl, {
				action: 'directory_links',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce-link-filter").val(),
				'letter': letter[1],
				'category_id': jQuery("select#category_id").val(),
				'page': 1
			},
			function(response)
			{
				response = response.substr(0, response.length-1);
				jQuery("#link-dir-list").fadeOut(200,
					function() {
						jQuery('.ajax-loader').toggle();
						jQuery("#link-dir-list").html(response);
						jQuery("#link-dir-list").fadeIn(200);
					}
				);
			});

			return false;
		}
	);

	jQuery("form#links-directory-form select#category_id").livequery('change',
		function() {

			jQuery('.ajax-loader').toggle();
			jQuery("input#links_search").val('');
			document.cookie = 'bp_directory_links_category_id=' + jQuery(this).val();

			jQuery.post( ajaxurl, {
				action: 'directory_links',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce-link-filter").val(),
				'letter': jQuery("input#letter").val(),
				'category_id': jQuery(this).val(),
				'page': 1
			},
			function(response)
			{
				response = response.substr(0, response.length-1);
				jQuery("#link-dir-list").fadeOut(200,
					function() {
						jQuery('.ajax-loader').toggle();
						jQuery("#link-dir-list").html(response);
						jQuery("#link-dir-list").fadeIn(200);
					}
				);
			});

			return false;
		}
	);

	jQuery("form#search-links-form").submit( function() {
			jQuery('.ajax-loader').toggle();

			jQuery.post( ajaxurl, {
				action: 'directory_links',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce-link-filter").val(),
				's': jQuery("input#links_search").val(),
				'page': 1
			},
			function(response)
			{
				response = response.substr(0, response.length-1);
				jQuery("#link-dir-list").fadeOut(200,
					function() {
						jQuery('.ajax-loader').toggle();
						jQuery("#link-dir-list").html(response);
						jQuery("#link-dir-list").fadeIn(200);
					}
				);
			});

			return false;
		}
	);

	jQuery("div#link-dir-pag a").livequery('click',
		function() {
			jQuery('.ajax-loader').toggle();

			var page = jQuery(this).attr('href');
			page = page.split('lpage=');

			var letter = '';
			if ( jQuery("input#selected_letter").val() )
				letter = jQuery("input#selected_letter").val();

			var search_terms = '';
			if ( jQuery("input#search_terms").val() )
				search_terms = jQuery("input#search_terms").val();

			jQuery.post( ajaxurl, {
				action: 'directory_links',
				'cookie': encodeURIComponent(document.cookie),
				'lpage': page[1],
				'_wpnonce': jQuery("input#_wpnonce-link-filter").val(),

				'letter': letter,
				's': search_terms
			},
			function(response)
			{
				response = response.substr(0, response.length-1);
				jQuery("#link-dir-list").fadeOut(200,
					function() {
						jQuery('.ajax-loader').toggle();
						jQuery("#link-dir-list").html(response);
						jQuery("#link-dir-list").fadeIn(200);
					}
				);
			});

			return false;
		}
	);

	jQuery("form#link-search-form").submit(
		function() {
			return false;
		}
	);

	jQuery("div.link-vote-panel a.vote").livequery('click',
		function() {

			jQuery('.ajax-loader').toggle();

			var link = jQuery(this).attr('id')
			link = link.split('-');

			jQuery.post( ajaxurl, {
				action: 'link_vote',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce-link-vote").val(),
				'up_or_down': link[1],
				'link_id': link[2]
			},
			function(response)
			{
				var response_split = response.split('[[split]]');
				var err_num = response_split[0];

				jQuery("div#link-vote-panel-" + link[2]).fadeOut(200,
					function() {
						jQuery('#message').remove();

						if ( err_num <= -1 ) {
							jQuery('ul#link-list').before('<div id="message" class="error fade"><p>' + response_split[1] + '</p></div>')
						} else if ( err_num == 0 ) {
							jQuery('ul#link-list').before('<div id="message" class="updated"><p>' + response_split[1] + '</p></div>')
						} else {
							jQuery('ul#link-list').before('<div id="message" class="updated"><p>' + response_split[1] + '</p></div>')
							jQuery("div.link-vote-panel div#vote-total-" + link[2]).html(response_split[2]);
							jQuery("div.link-vote-panel span#vote-count-" + link[2]).html(response_split[3]);
						}

						jQuery("ul#link-list div#link-vote-panel-" + link[2]).fadeIn(200);
					}
				);

				jQuery('.ajax-loader').toggle();
			});

			return false;
		}
	);

});


/*** Create/Admin form functionality ***/
jQuery(document).ready( function() {

	// element shortcuts
	var e_loader = jQuery(".ajax-loader");
	var e_url = jQuery("input#link-url");
	var e_url_ro = jQuery("input#link-url-readonly");
	var e_fields = jQuery("div#link-name-desc-fields");
	var e_name = e_fields.children("input#link-name");
	var e_desc = e_fields.children("textarea#link-desc");
	var e_conf = jQuery("span#link-url-embed-confirm");
	var e_clear = jQuery("span#link-url-embed-clear");
	var e_clear_a = e_clear.children("a");
	var e_embed = jQuery("div#link-url-embed");
	var e_avopt_p = jQuery("div.link-avatar-option p");
	var e_avimg_def = e_avopt_p.children("img.avatar-default");
	var e_avimg_cur = e_avopt_p.children("img.avatar-current");

	// bind to URL clear link click event
	function bindClearUrlClick()
	{
		e_clear_a.click( function() {
			e_clear.fadeOut(500);
			e_embed.slideUp(500, function() {
				e_embed.html(''); e_name.val(''); e_desc.val(''); e_url.val('');
				e_avimg_cur.attr("src", e_avimg_def.attr("src"));
				e_avimg_cur.attr("alt", e_avimg_def.attr("alt"));
				e_avimg_cur.attr("width", e_avimg_def.width());
				e_avimg_cur.attr("height", e_avimg_def.height());
				e_url.removeAttr("readonly");
				e_url_ro.val(0);
				e_fields.slideDown(500);
			});
			return;
		});
	}
	
	// bind to edit check box click event
	function bindEditTextClick()
	{
		jQuery("input#link-url-embed-edit-text").click( function() {
			if ( jQuery(this).attr('checked') ) {
				e_fields.fadeIn(750);
			} else {
				e_fields.fadeOut(750, function() {
					e_name.val(e_name.data('default_value'));
					e_desc.val(e_desc.data('default_value'));
				});
			}
		});
	}

	// detect if url is embeddable
	function detectUrl()
	{
		var url = jQuery.trim( e_url.val() );
		var services = e_url.data("embed_regex");
		
		// only try to match if url has some meat AND has changed
		if ( url.length >= 15 && !e_url.attr("readonly") ) {
			// make sure embed content is blank
			e_embed.html(''); e_name.val(''); e_desc.val('');
			// loop through supported services and try to match url
			for ( var i in services ) {
				if ( url.match( services[i] ) ) {
					return true;
				}
			}
		}
		return false;
	}

	// need to bind these if embed panel is visible on page load
	if ( e_embed.is(':visible') ) {
		bindClearUrlClick();
		bindEditTextClick();
	}

	// try to locate an auto embed service for the URL entered
	e_url.livequery('blur', function()
	{
		e_loader.toggle();
		
		if ( detectUrl() ) {
			if ( confirm( e_conf.html() ) ) {
				e_url.attr("readonly", "readonly");
				e_url_ro.val(1);
				e_clear.fadeIn(500, bindClearUrlClick);
			} else {
				e_loader.toggle();
				return;
			}
		} else {
			e_loader.toggle();
			return;
		}

		jQuery.post( ajaxurl, {
			action: 'link_auto_embed_url',
			'cookie': encodeURIComponent(document.cookie),
			'_wpnonce': jQuery("input#_wpnonce-link-auto-embed").val(),
			'url': e_url.val()
		},
		function(response) {

			var response_split = response.split('[[split]]');
			var err_num = response_split[0];

			jQuery('#message').remove();

			if ( err_num < 1 ) {
				jQuery('form#link-details-form').before('<div id="message" class="error fade"><p>' + response_split[1] + '</p></div>')
				e_fields.fadeIn(750);
			} else {

				e_embed.html(response_split[1]);

				var e_embimg = jQuery("div#link-url-embed-content img");
				e_avimg_cur.attr("src", e_embimg.attr("src"));
				e_avimg_cur.attr("alt", e_embimg.attr("alt"));
				e_avimg_cur.removeAttr("width");
				e_avimg_cur.removeAttr("height");

				e_fields.fadeOut(750, function() {
					e_name.data('default_value', response_split[2]);
					e_desc.data('default_value', response_split[3]);
					e_name.val(response_split[2]);
					e_desc.val(response_split[3]);
					e_embed.slideDown(750, bindEditTextClick);
				});
			}
			e_loader.toggle();
		});
	});

	// toggle avatar options panel
	jQuery("form#link-details-form a#link-avatar-fields-toggle").click(
		function() {
			jQuery("div#link-avatar-fields").toggle(500, function(){
				var state = jQuery("input#link-avatar-fields-display");
				state.val( ( 1 == state.val() ) ? 0 : 1 );
			});
		}
	);

	// toggle advanced settings panel
	jQuery("form#link-details-form a#link-settings-fields-toggle").click(
		function() {
			jQuery("div#link-settings-fields").toggle(500, function(){
				var state = jQuery("input#link-settings-fields-display");
				state.val( ( 1 == state.val() ) ? 0 : 1 );
			});
		}
	);

	// disable right click for avatars that are based on embeded images to comply with their TOS
	jQuery("img.avatar-embed").bind("contextmenu",
		function(){
			return false;
		}
	);
	
});