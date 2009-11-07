<?php get_header() ?>

	<div class="content-header">
		<ul class="content-header-nav">
			<?php bp_link_creation_tabs(); ?>
		</ul>
	</div>

	<div id="content">	
		<h2><?php _e( 'Create a Link', 'buddypress-links' ) ?> <?php bp_link_creation_stage_title() ?></h2>
		<?php do_action( 'template_notices' ) // (error/success feedback) ?>

		<?php do_action( 'bp_before_link_creation_content' ) ?>

		<form action="<?php bp_link_creation_form_action() ?>" method="post" id="create-link-form" class="standard-form" enctype="multipart/form-data">
	
			<!-- Link creation step 1: Basic link details -->
			<?php if ( bp_is_link_creation_step( 'link-details' ) ) : ?>

				<?php do_action( 'bp_before_link_details_creation_step' ); ?>

				<label for="link-category"><?php _e('Link Category', 'buddypress-links') ?> <? _e( '(required)', 'buddypress-links' )?></label>
				<?php bp_links_category_radio_options( bp_get_new_link_category_id(), 'link-category', '' ) ?>

				<label for="link-url"><?php _e('Link URL', 'buddypress-links') ?> <? _e( '(required)', 'buddypress-links' )?></label>
				<input type="text" name="link-url" id="link-url" value="<?php bp_new_link_url() ?>" />

				<label for="link-name"><?php _e('Link Name', 'buddypress-links') ?> <? _e( '(required)', 'buddypress-links' )?></label>
				<input type="text" name="link-name" id="link-name" value="<?php bp_new_link_name() ?>" />
	
				<label for="link-desc"><?php _e('Link Description', 'buddypress-links') ?> <? _e( '(required)', 'buddypress-links' )?></label>
				<textarea name="link-desc" id="link-desc" cols="60" rows="6"><?php bp_new_link_description() ?></textarea>
	
				<?php do_action( 'bp_after_link_details_creation_step' ); ?>

				<?php wp_nonce_field( 'bp_links_create_save_link-details' ) ?>

			<?php endif; ?>

			<!-- Link creation step 2: Link settings -->
			<?php if ( bp_is_link_creation_step( 'link-settings' ) ) : ?>

				<?php do_action( 'bp_before_link_settings_creation_step' ); ?>

				<?php if ( function_exists('bp_wire_install') ) : ?>
				<div class="checkbox">
					<label><input type="checkbox" name="link-show-wire" id="link-show-wire" value="1"<?php if ( bp_get_new_link_enable_wire() ) { ?> checked="checked"<?php } ?> /> <?php _e('Enable comment wire', 'buddypress-links') ?></label>
				</div>
				<?php endif; ?>

				<h3><?php _e( 'Privacy Options', 'buddypress-links' ); ?></h3>

				<div class="radio">
					<label><input type="radio" name="link-status" value="<?php echo BP_Links_Link::STATUS_PUBLIC ?>"<?php if ( BP_Links_Link::STATUS_PUBLIC == bp_get_new_link_status() || !bp_get_new_link_status() ) { ?> checked="checked"<?php } ?> />
						<strong><?php _e( 'This is a public link', 'buddypress-links' ) ?></strong>
						<ul>
							<li><?php _e( 'Any site member can see this link, and comment on the wire if enabled.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'This link will be listed in the links directory and in search results.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'Link content and activity will be visible to any site member.', 'buddypress-links' ) ?></li>
						</ul>
					</label>
		
					<label><input type="radio" name="link-status" value="<?php echo BP_Links_Link::STATUS_FRIENDS ?>"<?php if ( BP_Links_Link::STATUS_FRIENDS == bp_get_new_link_status() ) { ?> checked="checked"<?php } ?> />
						<strong><?php _e( 'This is a friends-only link', 'buddypress-links' ) ?></strong>
						<ul>
							<li><?php _e( 'Only users who are in your friends list can see the link and comment on the wire if enabled.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'This link will NOT be listed in the links directory or in search results.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'Link content and activity will only be visible to your friends.', 'buddypress-links' ) ?></li>
						</ul>
					</label>
		
					<label><input type="radio" name="link-status" value="<?php echo BP_Links_Link::STATUS_HIDDEN ?>"<?php if ( BP_Links_Link::STATUS_HIDDEN == bp_get_new_link_status() ) { ?> checked="checked"<?php } ?> />
						<strong><?php _e('This is a hidden link', 'buddypress-links') ?></strong>
						<ul>
							<li><?php _e( 'Only you can see the link.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'This link will NOT be listed in the links directory or search results.', 'buddypress-links' ) ?></li>
							<li><?php _e( 'Link content and activity will only be visible to you.', 'buddypress-links' ) ?></li>
						</ul>
					</label>
				</div>

				<?php do_action( 'bp_after_link_settings_creation_step' ); ?>

				<?php wp_nonce_field( 'bp_links_create_save_link-settings' ) ?>
	
			<?php endif; ?>
		
			<!-- Link creation step 3: Avatar Uploads -->
			<?php if ( bp_is_link_creation_step( 'link-avatar' ) ) : ?>

				<?php do_action( 'bp_before_link_avatar_creation_step' ); ?>

				<div class="left-menu">
					<?php bp_new_link_avatar() ?>
				</div>
		
				<div class="main-column">
					<p><?php _e("Upload an image to use as an avatar for this link. The image will be shown on the main link page, and in search results.", 'buddypress-links') ?></p>
			
				<?php if ( !bp_get_avatar_admin_step() ) : ?>
			
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

				<?php endif; ?>
			
				<?php if ( 'crop-image' == bp_get_avatar_admin_step() ) : ?>
			
					<h3><?php _e( 'Crop Link Avatar', 'buddypress-links' ) ?></h3>
				
					<img src="<?php bp_avatar_to_crop() ?>" id="avatar-to-crop" class="avatar" alt="<?php _e( 'Avatar to crop', 'buddypress-links' ) ?>" />
				
					<div id="avatar-crop-pane" style="width:100px;height:100px;overflow:hidden;">
						<img src="<?php bp_avatar_to_crop() ?>" id="avatar-crop-preview" class="avatar" alt="<?php _e( 'Avatar preview', 'buddypress-links' ) ?>" />
					</div>

					<input type="submit" name="avatar-crop-submit" id="avatar-crop-submit" value="<?php _e( 'Crop Image', 'buddypress-links' ) ?>" />
				
					<input type="hidden" name="image_src" id="image_src" value="<?php bp_avatar_to_crop_src() ?>" />
					<input type="hidden" name="upload" id="upload" />
					<input type="hidden" id="x" name="x" />
					<input type="hidden" id="y" name="y" />
					<input type="hidden" id="w" name="w" />
					<input type="hidden" id="h" name="h" />

				<?php endif; ?>
				
					<p><?php _e( 'To skip the avatar upload process, hit the "Finish" button.', 'buddypress-links' ) ?></p>
				</div>

				<?php do_action( 'bp_after_link_avatar_creation_step' ); ?>

				<?php wp_nonce_field( 'bp_links_create_save_link-avatar' ) ?>
		
			<?php endif; ?>
		
			<?php do_action( 'bp_links_custom_create_steps' ) // Allow plugins to add custom link creation steps ?>
		
			<?php do_action( 'bp_before_link_creation_step_buttons' ); ?>

			<div class="submit" id="previous-next">
				<!-- Previous Button -->
				<?php if ( !bp_is_first_link_creation_step() ) : ?>
					<input type="button" value="&larr; <?php _e('Previous Step', 'buddypress-links') ?>" id="link-creation-previous" name="previous" onclick="location.href='<?php bp_link_creation_previous_link() ?>'" />
				<?php endif; ?>

				<!-- Next Button -->
				<?php if ( !bp_is_last_link_creation_step() && !bp_is_first_link_creation_step() ) : ?>
					<input type="submit" value="<?php _e('Next Step', 'buddypress-links') ?> &rarr;" id="link-creation-next" name="save" />
				<?php endif;?>
		
				<!-- Create Button -->
				<?php if ( bp_is_first_link_creation_step() ) : ?>
					<input type="submit" value="<?php _e('Create Link and Continue', 'buddypress-links') ?> &rarr;" id="link-creation-create" name="save" />
				<?php endif; ?>
		
				<!-- Finish Button -->
				<?php if ( bp_is_last_link_creation_step() ) : ?>
					<input type="submit" value="<?php _e('Finish', 'buddypress-links') ?> &rarr;" id="link-creation-finish" name="save" />
				<?php endif; ?>
			</div>
			
			<?php do_action( 'bp_after_link_creation_step_buttons' ); ?>

			<!-- Don't leave out this hidden field -->
			<input type="hidden" name="link_id" id="link_id" value="<?php bp_new_link_id() ?>" />
		</form>

		<?php do_action( 'bp_after_link_creation_content' ) ?>
	
	</div>

<?php get_footer() ?>