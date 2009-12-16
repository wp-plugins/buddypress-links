<?php get_header() ?>

<?php //if ( bp_has_links() ) : while ( bp_links() ) : bp_the_link(); ?>

	<div class="content-header">
		<ul class="content-header-nav">
			<?php bp_link_admin_tabs(); ?>
		</ul>
	</div>

	<div id="content">

		<?php do_action( 'template_notices' ) // (error/success feedback) ?>

		<?php
			do_action( 'bp_before_link_admin_content' );

			switch ( bp_links_admin_current_action_variable() ) {
				case 'edit-details':
					require_once 'forms/details.php';
					break;
				case 'link-avatar':
					require_once 'forms/avatar.php';
					break;
				case 'delete-link':
					require_once 'forms/delete.php';
					break;
				default:
					die('Invalid admin action!');
			}

			do_action( 'bp_after_link_admin_content' );
		?>

	</div>

<?php //endwhile; endif; ?>

<?php get_footer() ?>