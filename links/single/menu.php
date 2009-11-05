<?php do_action( 'bp_before_link_menu_content' ) ?>
<?php do_action( 'bp_before_link_menu_avatar' ) ?>
<?php bp_link_avatar() ?>
<?php do_action( 'bp_after_link_menu_avatar' ) ?>
<?php do_action( 'bp_before_link_menu_buttons' ) ?>

<div class="button-block">
	<?php //bp_link_visit_button() ?>

	<?php do_action( 'bp_link_menu_buttons' ) ?>
</div>

<?php do_action( 'bp_after_link_menu_buttons' ) ?>
<?php do_action( 'bp_before_link_menu_owner' ) ?>

<div class="bp-widget">
	<h4><?php _e( 'Link Owner', 'buddypress-links' ) ?></h4>

	<?php bp_link_user_avatar_thumb() ?>
	<div class="link-owner-text"><?php bp_link_userlink() ?> <?php _e( 'created', 'buddypress-links' ); ?> <?php bp_link_time_elapsed_text() ?></div>
</div>

<div style="clear: both;"></div>

<?php do_action( 'bp_after_link_menu_owner' ) ?>
<?php do_action( 'bp_before_link_menu_voting' ) ?>

<div class="bp-widget">
	<h4><?php _e( 'Cast Your Vote', 'buddypress-links' ) ?></h4>
	<?php bp_link_list_item_vote_panel() ?>
	<?php bp_link_list_vote_form() ?>
</div>

<?php do_action( 'bp_after_link_menu_voting' ) ?>
<?php do_action( 'bp_after_link_menu_content' ) ?>