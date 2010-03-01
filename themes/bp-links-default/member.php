<div class="item-list-tabs no-ajax" id="subnav">
	<ul>
		<?php if ( bp_is_my_profile() ) : ?>
			<?php bp_get_options_nav() ?>
		<?php endif; ?>
		<?php bp_links_dtheme_link_order_options_list() ?>
		<?php bp_links_dtheme_link_category_filter_options_list() ?>
	</ul>
</div>

<?php do_action( 'bp_before_member_links_content' ) ?>

<div id="links-mylinks" class="links mylinks">
	<?php bp_links_locate_template( array( 'links-loop.php' ), true ) ?>
</div>

<?php do_action( 'bp_after_member_links_content' ) ?>
