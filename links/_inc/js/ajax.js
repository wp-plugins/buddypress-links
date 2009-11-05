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

			if ( !jQuery("input#selected_letter").val() )
				var letter = '';
			else
				var letter = jQuery("input#selected_letter").val();

			if ( !jQuery("input#search_terms").val() )
				var search_terms = '';
			else
				var search_terms = jQuery("input#search_terms").val();

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

	// disable right click for avatars that based on embeded images to comply with their TOS
	jQuery("img.avatar-embed").bind("contextmenu",
		function(){
			return false;
		}
	);
	
});