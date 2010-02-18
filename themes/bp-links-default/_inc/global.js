// AJAX Functions

jQuery(document).ready( function() {
	var j = jQuery;

	/**** Page Load Actions **********************/

	/* Link filter and scope set. */
	bp_init_objects( [ 'links' ] );

	/* Clear cookies on logout */
	j('a.logout').click( function() {
		j.cookie('bp-links-scope', null );
		j.cookie('bp-links-filter', null );
		j.cookie('bp-links-extras', null );
	});

	/**** Directory ******************************/

	/* When the category filter select box is changed, re-query */
	j('select#links-category-filter').change( function() {
		var extras;
		var el_cat = j('select#links-category-filter');
		if ( el_cat.val().length ) {
			extras = 'category-' + el_cat.val();
		}

		bp_filter_request( 'links', j.cookie('bp-links-filter'), j.cookie('bp-links-scope'), 'div.links', j('#links_search').val(), 1, extras );

		return false;
	});

	/**** Lightbox ****************************/

	j("a.link-play").live('click',
		function() {

			var link = j(this).attr('id')
			link = link.split('-');

			j.post( ajaxurl, {
				action: 'link_lightbox',
				'cookie': encodeURIComponent(document.cookie),
				'link_id': link[2]
			},
			function(response)
			{
				var response_split = response.split('[[split]]');
				var err_num = response_split[0];

				if ( err_num >= 1 ) {
					j.fn.colorbox({
						html: response_split[1],
						maxWidth: '90%',
						maxHeight: '90%'
					});
				}
			});

			return false;
		}
	);

	/**** Voting ******************************/

	jQuery("div.link-vote-panel a.vote").live('click',
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

						jQuery("div#link-vote-panel-" + link[2]).fadeIn(200);
					}
				);

				jQuery('.ajax-loader').toggle();
			});

			return false;
		}
	);
	
});