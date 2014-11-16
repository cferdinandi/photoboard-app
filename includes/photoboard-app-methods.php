<?php

	/**
	 * Check if user can view content
	 */
	function photoboard_can_user_view( $user_id, $media_id ) {

		// Variables
		$user_id = ( empty($user_id) ? get_current_user_id() : $user_id );
		$groups = get_terms( 'user-group' );
		$visibility = get_post_meta($media_id, 'photoboard_img_visibility', true);

		// If user is in group without access, return false
		foreach ($groups as $group) {
			$is_in_group = is_object_in_term( $user_id, 'user-group', $group->slug );
			$group_has_access = $visibility[$group->slug];
			if ( $is_in_group && $group_has_access !== 'no' ) {
				return true;
			}
		}

		return false;

	}


	/**
	 * Get all post images.
	 * @param number $id ID of the current post
	 */
	function photoboard_get_post_imgs($id) {

		// Get all images
		$images = get_children(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_parent'    => $id,
				'posts_per_page' => -1,
			)
		);

		// Generate markup
		if ($images) {

			// Variables
			$user_id = get_current_user_id();
			$exports = '';

			foreach ($images as $image) {

				// Variables
				$track_event = 'onClick="_gaq.push([\'_trackEvent\', \'Images\', \'Download\', \'' . get_the_title($image->ID) . '\']);"';
				$img_large = wp_get_attachment_image_src( $image->ID, 'large' );
				$img_full = wp_get_attachment_image_src( $image->ID, 'full' );
				$user_can_view = photoboard_can_user_view( $user_id, $image->ID );

				// If user doesn't have access, skip to the next photo
				if ( !$user_can_view && !current_user_can( 'edit_files' ) ) continue;

				// Markup
				$exports .=
					'<div class="margin-bottom img-photo">' .
						'<div class="text-center margin-bottom-small">' .
							'<img src="' . $img_large[0] . '">' .
						'</div>' .
						'<p class="text-muted clearfix">' .
							'<a class="btn float-right" ' . $track_event . ' href="' . $img_full[0] . '" download>' .
								'<svg class="icon">' .
									'<use xlink:href="#icon-download"></use>' .
								'</svg> ' .
								'<span class="supporting-text">Download</span>' .
							'</a>' .
							$image->post_excerpt .
						'</p>' .
					'</div>';
			}
			return $exports;
		}

	}




	/**
	 * Get all post images.
	 * @param number $id ID of the current post
	 */
	function photoboard_get_post_vids($id) {

		// Get all videos
		$videos = get_children(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'video',
				'post_parent'    => $id,
				'posts_per_page' => -1,
			)
		);

		// Generate markup
		if ($videos) {

			// Variables
			$user_id = get_current_user_id();
			$exports = '';

			foreach ($videos as $video) {

				// Variables
				$track_event = 'onClick="_gaq.push([\'_trackEvent\', \'Videos\', \'Download\', \'' . get_the_title($video->ID) . '\']);"';
				$user_can_view = photoboard_can_user_view( $user_id, $video->ID );

				// If user doesn't have access, skip to the next video
				if ( !$user_can_view && !current_user_can( 'edit_files' ) ) continue;

				$exports .=
					'<div class="margin-bottom img-photo">' .
						'<div class="text-center">' .
							'<video clsas="margin-bottom-small" controls preload="auto">'.
								'<source type="video/mp4" src="' . $video->guid . '">' .
								'<p>Your browser will not play this video. <a' . $track_event . ' href="' . $video->guid . '" download>Download to Watch</a></p>' .
							'</video>' .
						'</div>' .
						'<p class="text-muted clearfix">' .
							'<a class="btn float-right" ' . $track_event . ' href="' . $video->guid . '" download>' .
								'<svg class="icon">' .
									'<use xlink:href="#icon-download"></use>' .
								'</svg> ' .
								'<span class="supporting-text">Download</span>' .
							'</a>' .
							$video->post_excerpt .
						'</p>' .
					'</div>';
			}
			return $exports;
		}

	}




	/**
	 * Get the thumbnail image for the album
	 */
	//
	function photoboard_get_album_thumbnail( $post_id ) {
		$format = get_post_meta( $post_id, 'photoboard_post_format', true );
		if ( $format === 'photos' ) {
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, 'thumbnail', 'class=img-photo' );
			} else {
				$images = get_children(
					array(
						'post_type'      => 'attachment',
						'post_mime_type' => 'image',
						'post_parent'    => $post_id,
						'posts_per_page' => 1,
					)
				);
				foreach ($images as $img) {
					$img_thumb = wp_get_attachment_image_src( $img->ID, 'thumbnail' );
				}
				echo '<img class="img-photo" src="' . $img_thumb[0] . '">' ;
			}
		} else if ( $format === 'videos' ) {
			?>
				<img height="300" width="300" class="img-photo" src="<?php echo get_template_directory_uri(); ?>/dist/img/play.jpg">
			<?php
		} else {
			?>
				<img height="300" width="300" class="img-photo" src="<?php echo get_template_directory_uri(); ?>/dist/img/blog.jpg">
			<?php
		}
	}




	/**
	 * Set post type on save
	 * @param array $post The post being updated
	 */
	function photoboard_set_post_type_on_save( $post ) {

		// If post is not published or is an autosave, bail
		if ( get_post_type( $post->ID ) !== 'post' || get_post_status( $post->ID ) !== 'publish' || wp_is_post_autosave( $post->ID ) ) return;

		// Variables
		$post_id = $post->ID;
		$images = get_children(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_parent'    => $post->ID,
				'posts_per_page' => 1,
			)
		);
		$videos = get_children(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'video',
				'post_parent'    => $post->ID,
				'posts_per_page' => 1,
			)
		);

		// Methods
		if ( $images ) {
			update_post_meta( $post->ID, 'photoboard_post_format', 'photos' );
		} elseif ( $videos ) {
			update_post_meta( $post->ID, 'photoboard_post_format', 'videos' );
		} else {
			update_post_meta( $post->ID, 'photoboard_post_format', 'article' );
		}

	}
	add_action('save_post', 'photoboard_set_post_type_on_save');
	add_action('draft_to_publish', 'photoboard_set_post_type_on_save');
	add_action('new_to_publish', 'photoboard_set_post_type_on_save');
	add_action('pending_to_publish', 'photoboard_set_post_type_on_save');
	add_action('future_to_publish', 'photoboard_set_post_type_on_save');




	/**
	 * Notify members of new post by email
	 * @param array $post The post being updated
	 */
	function photoboard_new_post_email( $post ) {

		// If post is not published, bail
		if ( get_post_type( $post->ID ) !== 'post' || get_post_status( $post->ID ) !== 'publish' ) return;

		// Variables
		$author = intval($post->post_author);
		$post_id = $post->ID;
		$users = get_users();
		$headers = Array();

		// Loop through each user
		foreach ($users as $user) {

			// User variables
			$user_id = intval($user->ID);
			$email = $user->user_email;
			$notifications = get_user_meta($user_id, 'photoboard_get_notifications', 'true');

			// Don't send notification to post author
			if ( $user_id === $author || $notifications === 'off' ) continue;

			// Add user to email list
			$headers[] = 'Bcc: ' . $email;

		}

		// Email variables
		$to = 'Our Family Photoboard <notifications@' . photoboard_get_site_domain() . '>';
		$subject = 'New photos on Photoboard: ' . get_the_title( $post_id );
		$message =
			'Someone posted new photos or videos on Photoboard. Click here to view them: ' . get_permalink( $post_id) . "\r\n\r\n" .
			'To stop receiving these emails, visit ' . site_url() . '/notifications' . "\r\n";
		$headers[] = 'From: Our Family Photoboard <notifications@' . photoboard_get_site_domain() . '>';

		// Send email
		wp_mail( $to, $subject, $message, $headers );

	}
	// add_action('save_post', 'photoboard_new_post_email');
	add_action('draft_to_publish', 'photoboard_new_post_email');
	add_action('new_to_publish', 'photoboard_new_post_email');
	add_action('pending_to_publish', 'photoboard_new_post_email');
	add_action('future_to_publish', 'photoboard_new_post_email');




	/**
	 * Create form for users to update notification preferences
	 */
	function photoboard_set_notifications_form() {

		if ( is_user_logged_in() ) {

			// Variables
			global $current_user;
			$user_id = $current_user->ID;
			$notifications = get_user_meta($user_id, 'photoboard_get_notifications', 'true');
			$checked = ( $notifications !== 'off' ? 'checked' : '');

			// Alert
			$wp_session = WP_Session::get_instance();
			$alert = stripslashes( $wp_session['photoboard_alert_notifications'] );
			unset( $wp_session['photoboard_alert_notifications'] );

			$form =
				$alert .
				'<form class="form-photoboard" id="photoboard-form-set-notifications" name="photoboard-form-set-notifications" action="" method="post">' .
					photoboard_form_field_checkbox_plus( 'photoboard-get-notifications', 'Receive email notifications when new photos or videos are posted.', $value = '', $checked ) .
					photoboard_form_field_submit_plus( 'photoboard-set-notifications-submit', 'btn', 'Update Notifications', 'photoboard-set-notifications-process-nonce', 'photoboard-set-notifications-process' ) .
				'</form>';

		} else {
			$form = '<p>' . __( 'You must be logged in to update a profile.', 'photoboard' ) . '</p>';
		}

		return $form;

	}
	add_shortcode( 'photoboard_notifications_form', 'photoboard_set_notifications_form' );




	/**
	 * Process user notification preference updates
	 */
	function photoboard_process_set_notifications_form() {
		if ( isset( $_POST['photoboard-set-notifications-process'] ) ) {
			if ( wp_verify_nonce( $_POST['photoboard-set-notifications-process'], 'photoboard-set-notifications-process-nonce' ) ) {

				// User variables
				global $current_user;
				$user_id = $current_user->ID;
				$referer = esc_url_raw( photoboard_get_url() );

				// Fields
				$field_notifications = $_POST['photoboard-get-notifications'];

				// Alert Messages
				$wp_session = WP_Session::get_instance();
				$alert_success = '<div class="alert alert-success">Your notification settings have been updated.</div>';
				$alert_failure = '<div class="alert alert-danger">Sorry, but something went wrong. Please try again.</div>';

				// Update settings
				if ( isset($field_notifications) ) {
					update_user_meta( $user_id, 'photoboard_get_notifications', 'on' );
				} else {
					update_user_meta( $user_id, 'photoboard_get_notifications', 'off' );
				}

				// Reload page
				$wp_session['photoboard_alert_notifications'] = $alert_success;
				wp_safe_redirect( $referer, 302 );
				exit;

			} else {
				die( 'Security check' );
			}
		}
	}
	add_action('init', 'photoboard_process_set_notifications_form');




	/**
	 * Create a zip file of all photos
	 * @param  array   $files       Files to compress
	 * @param  string  $destination Destination to save the file
	 * @param  boolean $overwrite   If true, overwrites existing file with same name
	 * @return string              	File name/destination
	 * @link http://davidwalsh.name/create-zip-php
	 */
	function photoboard_create_zip($files = array(), $destination = '', $overwrite = false) {

		// If the zip file already exists and overwrite is false, return false
		if ( file_exists( $destination ) && !$overwrite ) return false;

		// Variables
		$valid_files = array();

		// If files were passed in, cylcle through each file
		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				if ( file_exists( $file ) ) {
					$valid_files[] = $file;
				}
			}
		}

		//if we have good files, create the archive
		if ( count( $valid_files ) ) {

			$zip = new ZipArchive();

			// If non-overwriteable and file already exists, stop
			if ( $zip->open( $destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE ) !== true ) {
				return false;
			}

			// Add the files to the zip
			foreach ( $valid_files as $file ) {
				$zip->addFile( $file, basename( $file ) );
			}

			// Set filename to variable
			$export = $zip->filename;

			// Close the zip -- done!
			$zip->close();

			// Return the filename
			return $export;

		} else {
			return false;
		}
	}




	/**
	 * Add file to Media library
	 * @param string $filename Path to file
	 * @param number $parent_post_id ID of the post to attach file to
	 */
	function photoboard_add_file_to_media_library( $filename, $parent_post_id = 0 ) {

		// Includes
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Variables
		$filetype = wp_check_filetype( basename( $filename ), null ); // Get MIME type
		$wp_upload_dir = wp_upload_dir();
		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		// Insert the attachment and generate metadata
		$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}




	/**
	 * When a new post is created, generate a zip of all media
	 * @param number $post_id ID of the current post
	 */
	function photoboard_create_zip_on_save( $post ) {

		// If post is not published, bail
		if ( get_post_type( $post->ID ) !== 'post' || get_post_status( $post->ID ) !== 'publish' ) return;

		// Variables
		$post_id = $post->ID;
		$upload_dir = wp_upload_dir();
		$files_to_zip = array();
		$images = get_children(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_parent'    => $post->ID,
				'posts_per_page' => -1,
			)
		);
		$videos = get_children(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'video',
				'post_parent'    => $post->ID,
				'posts_per_page' => -1,
			)
		);

		// Push files to array
		if ($images) {
			foreach ($images as $image) {
				/*$img_src = wp_get_attachment_image_src( $image->ID, 'full' );
				$files_to_zip[] = $img_src[0];*/
				$files_to_zip[] = get_attached_file( $image->ID );
			}
		}

		if ($videos) {
			foreach ($videos as $video) {
				// $files_to_zip[] = $video->guid;
				$files_to_zip[] = get_attached_file( $video->ID );
			}
		}

		// Create zip, add to media library, and set post meta data
		$new_zip = photoboard_create_zip($files_to_zip, $upload_dir['path'] . '/' . $post->post_name . '.zip', true);
		$media = photoboard_add_file_to_media_library( $new_zip, $post->ID );
		update_post_meta( $post->ID, 'photoboard_download_zip', $media);

	}
	// add_action('save_post', 'photoboard_create_zip_on_save');
	add_action('draft_to_publish', 'photoboard_create_zip_on_save');
	add_action('new_to_publish', 'photoboard_create_zip_on_save');
	add_action('pending_to_publish', 'photoboard_create_zip_on_save');
	add_action('future_to_publish', 'photoboard_create_zip_on_save');




	/**
	 * Add new file to zip when added to post
	 */
	function photoboard_add_new_zip_file_on_update( $id ) {

		// Variables
		$file = get_attached_file( $id );
		$parent = wp_get_post_parent_id( $id );
		$zip_url = get_attached_file( get_post_meta( $parent, 'photoboard_download_zip', true ) );

		// If post is not published, bail
		if ( get_post_type( $parent ) !== 'post' || get_post_status( $parent ) !== 'publish' || get_post_mime_type( $id ) === 'application/zip' || !$zip_url ) return;

		// Add to zip
		$zip = new ZipArchive;
		if ($zip->open($zip_url) === TRUE) {
			$zip->addFile( $file, basename( $file ) );
			$zip->close();
		}

	}
	add_action('add_attachment', 'photoboard_add_new_zip_file_on_update');




	/**
	 * Delete file from zip when removed from post
	 */
	function photoboard_remove_zip_file_on_delete( $id ) {

		// Variables
		$file = get_attached_file( $id );
		$parent = wp_get_post_parent_id( $id );
		$zip_url = get_attached_file( get_post_meta( $parent, 'photoboard_download_zip', true ) );

		// If post is not published, bail
		if ( get_post_type( $parent ) !== 'post' || get_post_status( $parent ) !== 'publish' || get_post_mime_type( $id ) === 'application/zip' || !$zip_url ) return;

		// Remove from zip
		$zip = new ZipArchive;
		if ($zip->open($zip_url) === TRUE) {
			$zip->deleteName( basename( $file ) );
			$zip->close();
		}

	}
	add_action('delete_attachment', 'photoboard_remove_zip_file_on_delete');




	/**
	 * Add checkboxes to assign photo visibility by group
	 * @param array $form_fields Form fields settings
	 * @param object $post The attachment
	 * @return object The update form fields
	 */
	function photoboard_add_photo_visibility_checkbox($form_fields, $post) {

		// Get groups and visibility settings
		$img_visibility = get_post_meta($post->ID, 'photoboard_img_visibility', true);
		$groups = get_terms( 'user-group' );
		$html = '';

		// Create a checkbox for each group
		foreach ($groups as $group) {
			$group_id = $group->slug;
			$group_name = $group->name;
			$checked = ( $img_visibility[$group_id] === 'no' ? '' : 'checked' );
			$html .=
				'<label class="setting">' .
					'<input type="checkbox" name="attachments[' . $post->ID . '][' . $group_id . ']" value="1" style="float: left;" ' . $checked . '>' .
					'<span class="name">' . $group_name . '</span>' .
				'</label>';
		}

		$form_fields['visibility'] = array(
			'label' => __('Visibility', 'photoboard'),
			'input' => 'html',
			'html'  => $html,
			// 'helps' => __('Control photo visibility', 'photoboard'),
		);

		return $form_fields;

	}
	add_filter('attachment_fields_to_edit', 'photoboard_add_photo_visibility_checkbox', null, 2);




	/**
	 * Save photo visibility by group
	 * @param object $post The attachment
	 * @param object $attachment The attachment ID
	 * @return object The post
	 */
	function photoboard_save_photo_visibility($post, $attachment) {

		// Get groups and visibility settings
		$img_visibility = Array();
		$groups = get_terms( 'user-group' );

		// For each checkbox, set visibility
		foreach ($groups as $group) {
			$group_id = $group->slug;
			$group_name = $group->name;
			if ( isset( $attachment[$group_id] ) ) {
				$img_visibility[$group_id] = 'yes';
			} else {
				$img_visibility[$group_id] = 'no';
			}
		}

		// Save changes to database
		update_post_meta($post['ID'], 'photoboard_img_visibility', $img_visibility);
		return $post;

	}
	add_filter('attachment_fields_to_save', 'photoboard_save_photo_visibility', null , 2);