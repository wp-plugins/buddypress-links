<div class="item-list-tabs no-ajax" id="subnav">
	<ul>
		<?php bp_links_group_links_tabs() ?>
	</ul>
</div>

<?php do_action( 'bp_before_group_links_content' ) ?>

<?php
	do_action( 'template_notices' ); // (error/success feedback)
	do_action( 'bp_before_group_link_creation_content' );
	require_once BP_LINKS_THEME_DIR . '/single/forms/details.php';
	do_action( 'bp_after_group_link_creation_content' );
?>

<?php do_action( 'bp_after_group_links_content' ) ?>