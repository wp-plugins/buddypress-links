<div class="item-list-tabs no-ajax" id="subnav">
	<ul>
		<?php if ( bp_is_my_profile() ) : ?>
			<?php bp_get_options_nav() ?>
		<?php endif; ?>

		<?php if ( 'invites' != bp_current_action() ) : ?>
		<li id="members-order-select" class="last filter">

			<?php _e( 'Order By:', 'buddypress' ) ?>
			<select id="links-all">
				<option value="active"><?php _e( 'Last Active', 'buddypress' ) ?></option>
				<option value="popular"><?php _e( 'Most Popular', 'buddypress-links' ) ?></option>
				<option value="newest"><?php _e( 'Newly Created', 'buddypress' ) ?></option>
				<option value="most-votes"><?php _e( 'Most Votes', 'buddypress-links' ) ?></option>
				<option value="high-votes"><?php _e( 'Highest Rated', 'buddypress-links' ) ?></option>
				<?php do_action( 'bp_member_link_order_options' ) ?>
			</select>
		</li>
		<?php endif; ?>
	</ul>
</div>

<?php do_action( 'bp_before_member_links_content' ) ?>

<div id="links-mylinks" class="links mylinks do-filter-request">
	<?php bp_links_locate_template( array( 'links-loop.php' ), true ) ?>
</div>

<?php do_action( 'bp_after_member_links_content' ) ?>
