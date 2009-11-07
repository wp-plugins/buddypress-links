<?php get_header() ?>

<?php if ( bp_has_links() ) : while ( bp_links() ) : bp_the_link(); ?>

	<div class="content-header">
		<ul class="content-header-nav">
			<?php bp_link_admin_tabs(); ?>
		</ul>
	</div>

	<div id="content">

		<?php do_action( 'template_notices' ) // (error/success feedback) ?>

		<?php do_action( 'bp_before_link_admin_content' ) ?>

		<form action="<?php bp_link_admin_form_action() ?>" name="link-settings-form" id="link-settings-form" class="standard-form" method="post" enctype="multipart/form-data">

			<?php /* Edit Link Details */ ?>
			<?php if ( bp_is_link_admin_screen( 'edit-details' ) ) : ?>

				<h2><?php _e( 'Edit Details', 'buddypress-links' ); ?></h2>

				<?php do_action( 'bp_before_link_details_admin' ); ?>

				<label for="link-category">* <?php _e( 'Link Category', 'buddypress-links' ) ?></label>
				<?php bp_links_category_radio_options( bp_get_link_category_id(), 'link-category', '' ) ?>

				<label for="link-url">* <?php _e( 'Link URL', 'buddypress-links' ) ?></label>
				<input type="text" name="link-url" id="link-url" value="<?php bp_link_url() ?>" />

				<label for="link-name">* <?php _e( 'Link Name', 'buddypress-links' ) ?></label>
				<input type="text" name="link-name" id="link-name" value="<?php bp_link_name() ?>" />

				<label for="link-desc">* <?php _e( 'Link Description', 'buddypress-links' ) ?></label>
				<textarea name="link-desc" id="link-desc"><?php bp_link_description_editable() ?></textarea>

				<?php // TODO is this deprected? do_action( 'bp_links_custom_link_fields_editable' ) ?>

				<?php do_action( 'bp_after_link_details_admin' ); ?>

				<p>
					<input type="submit" value="<?php _e( 'Save Changes', 'buddypress-links' ) ?> &raquo;" id="save" name="save" />
					<?php _e( 'or', 'buddypress-links' ); ?> <input type="submit" value="<?php _e( 'Start Over', 'buddypress-links' ) ?>" id="reset" name="reset" />
				</p>
				<?php wp_nonce_field( 'bp_links_edit_link_details' ) ?>

			<?php endif; ?>

			<?php /* Manage Link Settings */ ?>
			<?php if ( bp_is_link_admin_screen( 'link-settings' ) ) : ?>

				<h2><?php _e( 'Link Settings', 'buddypress-links' ); ?></h2>

				<?php do_action( 'bp_before_link_settings_admin' ); ?>

				<?php if ( function_exists('bp_wire_install') ) : ?>

					<div class="checkbox">
						<label><input type="checkbox" name="link-show-wire" id="link-show-wire" value="1"<?php bp_link_show_wire_setting() ?>/> <?php _e( 'Enable comment wire', 'buddypress-links' ) ?></label>
					</div>

				<?php endif; ?>

				<h3><?php _e( 'Privacy Options', 'buddypress-links' ); ?></h3>

				<div class="radio">
					<label>
						<input type="radio" name="link-status" value="<?php echo BP_Links_Link::STATUS_PUBLIC ?>"<?php bp_link_show_status_setting( BP_Links_Link::STATUS_PUBLIC ) ?> />
						<strong><?php _e( 'This is a public link', 'buddypress-links' ) ?></strong>
						<ul>
							<li><?php _e( 'Any site member can see this link, and comment on the wire if enabled.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'This link will be listed in the links directory and in search results.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'Link content and activity will be visible to any site member.', 'buddypress-links' ) ?></li>
						</ul>
					</label>

					<label>
						<input type="radio" name="link-status" value="<?php echo BP_Links_Link::STATUS_FRIENDS ?>"<?php bp_link_show_status_setting( BP_Links_Link::STATUS_FRIENDS ) ?> />
						<strong><?php _e( 'This is a friends only link', 'buddypress-links' ) ?></strong>
						<ul>
							<li><?php _e( 'Only users who are in your friends list can see the link and comment on the wire if enabled.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'This link will NOT be listed in the links directory or in search results.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'Link content and activity will only be visible to your friends.', 'buddypress-links' ) ?></li>
						</ul>
					</label>

					<label>
						<input type="radio" name="link-status" value="<?php echo BP_Links_Link::STATUS_HIDDEN ?>"<?php bp_link_show_status_setting( BP_Links_Link::STATUS_HIDDEN ) ?> />
						<strong><?php _e( 'This is a hidden link', 'buddypress-links' ) ?></strong>
						<ul>
							<li><?php _e( 'Only you can see the link.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'This link will NOT be listed in the links directory or search results.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'Link content and activity will only be visible to you.', 'buddypress-links' ) ?></li>
						</ul>
					</label>
				</div>

				<?php do_action( 'bp_after_link_settings_admin' ); ?>

				<p><input type="submit" value="<?php _e( 'Save Changes', 'buddypress-links' ) ?> &raquo;" id="save" name="save" /></p>
				<?php wp_nonce_field( 'bp_links_edit_link_settings' ) ?>

			<?php endif; ?>

			<?php /* Link Avatar Settings */ ?>
			<?php if ( bp_is_link_admin_screen( 'link-avatar' ) ) : ?>

				<h2><?php _e( 'Link Avatar', 'buddypress-links' ); ?></h2>

				<div class="left-menu">
					<?php bp_link_avatar( 'type=full' ) ?>

					<?php if ( bp_get_link_has_avatar() ) : ?>
						<div class="generic-button" id="delete-link-avatar-button">
							<a class="edit" href="<?php bp_link_avatar_delete_link() ?>" title="<?php _e( 'Delete Avatar', 'buddypress-links' ) ?>"><?php _e( 'Delete Avatar', 'buddypress-links' ) ?></a>
						</div>
					<?php endif; ?>
				</div>

				<div class="main-column">

				<?php if ( 'upload-image' == bp_get_avatar_admin_step() ) : ?>

					<p><?php _e( 'Upload an image to use as an avatar for this link. The image will be shown on the main link page, and in search results.', 'buddypress-links' ) ?></p>

					<p>
						<input type="file" name="file" id="file" />
						<input type="submit" name="upload" id="upload" value="<?php _e( 'Upload Image', 'buddypress-links' ) ?>" />
						<input type="hidden" name="action" id="action" value="bp_avatar_upload" />
					</p>

					<p>
						<?php printf( '-- %1$s --', __( 'OR', 'buddypress-links' ) ) ?>
					</p>

					<div>
						<div>
							<?php printf( __( 'Paste Image Embed Code (%1$s and %2$s are supported)', 'buddypress-links' ), '<a href="http://www.picapp.com/" target="_blank">PicApp</a>', '<a href="http://www.fotoglif.com/" target="_blank">Fotoglif</a>' ) ?>
						</div>
						<textarea name="embed-html" id="embed-html" cols="50" rows="4"><?php echo ( isset( $_POST['embed-html'] ) ) ? esc_html( $_POST['embed-html'] ) : null; ?></textarea>
						<div>
							<input type="submit" name="embed-submit" id="embed-submit" value="<?php _e( 'Embed Image', 'buddypress-links' ) ?>" />
						</div>
					</div>

					<?php wp_nonce_field( 'bp_avatar_upload' ) ?>

				<?php endif; ?>

				<?php if ( 'crop-image' == bp_get_avatar_admin_step() ) : ?>

					<h3><?php _e( 'Crop Avatar', 'buddypress-links' ) ?></h3>

					<img src="<?php bp_avatar_to_crop() ?>" id="avatar-to-crop" class="avatar" alt="<?php _e( 'Avatar to crop', 'buddypress-links' ) ?>" />

					<div id="avatar-crop-pane">
						<img src="<?php bp_avatar_to_crop() ?>" id="avatar-crop-preview" class="avatar" alt="<?php _e( 'Avatar preview', 'buddypress-links' ) ?>" />
					</div>

					<input type="submit" name="avatar-crop-submit" id="avatar-crop-submit" value="<?php _e( 'Crop Image', 'buddypress-links' ) ?>" />

					<input type="hidden" name="image_src" id="image_src" value="<?php bp_avatar_to_crop_src() ?>" />
					<input type="hidden" id="x" name="x" />
					<input type="hidden" id="y" name="y" />
					<input type="hidden" id="w" name="w" />
					<input type="hidden" id="h" name="h" />

					<?php wp_nonce_field( 'bp_avatar_cropstore' ) ?>

				<?php endif; ?>

				</div>

			<?php endif; ?>

			<?php // TODO is this deprecated? do_action( 'bp_links_custom_edit_steps' ) // Allow plugins to add custom link edit screens ?>

			<?php /* Delete Link Option */ ?>
			<?php if ( bp_is_link_admin_screen( 'delete-link' ) ) : ?>

				<h2><?php _e( 'Delete Link', 'buddypress-links' ); ?></h2>

				<?php do_action( 'bp_before_link_delete_admin' ); ?>

				<div id="message" class="info">
					<p><?php _e( 'WARNING: Deleting this link will completely remove ALL content associated with it. There is no way back, please be careful with this option.', 'buddypress-links' ); ?></p>
				</div>

				<input type="checkbox" name="delete-link-understand" id="delete-link-understand" value="1" onclick="if(this.checked) { document.getElementById('delete-link-button').disabled = ''; } else { document.getElementById('delete-link-button').disabled = 'disabled'; }" /> <?php _e( 'I understand the consequences of deleting this link.', 'buddypress-links' ); ?>

				<?php do_action( 'bp_after_link_delete_admin' ); ?>

				<p><input type="submit" disabled="disabled" value="<?php _e( 'Delete Link', 'buddypress-links' ) ?> &raquo;" id="delete-link-button" name="delete-link-button" /></p>

				<input type="hidden" name="link-id" id="link-id" value="<?php bp_link_id() ?>" />

				<?php wp_nonce_field( 'bp_links_delete_link' ) ?>

			<?php endif; ?>

			<?php /* This is important, don't forget it */ ?>
			<input type="hidden" name="link-id" id="link-id" value="<?php bp_link_id() ?>" />

		</form>

		<?php do_action( 'bp_after_link_admin_content' ) ?>
	</div>

<?php endwhile; endif; ?>

<?php get_footer() ?>