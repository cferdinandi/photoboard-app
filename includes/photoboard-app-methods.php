<?php

/**
 * plugins-in-training.php
 * These will be plugins down the road.
 */


	/**
	 * Get all post images.
	 * @param {String} $id ID of the current post
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
			foreach ($images as $image) {
				$exports .=
					'<div class="margin-bottom">' .
						'<div class="text-center">' .
							'<img class="img-photo" src="' . wp_get_attachment_image_src( $image->ID, 'large' )[0] . '">' .
						'</div>' .
						'<p class="text-muted clearfix">' .
							'<a class="btn float-right" href="' . wp_get_attachment_image_src( $image->ID, 'full' )[0] . '" download>' .
								'<svg class="icon">' .
									'<use xlink:href="#icon-download"></use>' .
								'</svg> ' .
								'Download' .
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
	 * @param {String} $id ID of the current post
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
			foreach ($videos as $video) {
				$exports .=
					'<div class="margin-bottom">' .
						'<div class="text-center">' .
							'<video controls preload="auto">'.
								'<source type="video/mp4" src="' . $video->guid . '">' .
								'<div class="flowplayer">' .
									'<source type="video/mp4" src="' . $video->guid . '">' .
								'</div>' .
								'<p><a target="_blank" href="' . $video->guid . '">Download the Video</a></p>' .
							'</video>' .
						'</div>' .
						'<p class="text-muted clearfix">' .
							'<a class="btn float-right" href="' . $video->guid . '" download>' .
								'<svg class="icon">' .
								    '<use xlink:href="#icon-download"></use>' .
								'</svg> ' .
								'Download' .
							'</a>' .
							$video->post_excerpt .
						'</p>' .
					'</div>';
			}
			return $exports;
		}

	}


	/**
	 * Automatically make the first post image the featured thumbnail
	 * @link http://stackoverflow.com/a/15605334
	 * @link https://wordpress.org/plugins/autoset-featured-image/
	 */
	function photoboard_auto_set_featured_thumbnail() {

		// Variables
		global $post;
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
				'posts_per_page' => -1,
			)
		);

		// Methods
		if ( $images ) {
			foreach ($images as $attachment_id => $attachment) {
				set_post_thumbnail($post->ID, $attachment_id);
			}
		} elseif ( $videos ) {
			$video_thumb_id = 92;
			set_post_thumbnail($post->ID, $video_thumb_id);
		} else {
			$story_thumb_id = 91;
			set_post_thumbnail($post->ID, $story_thumb_id);
		}

	}
	add_action('save_post', 'photoboard_auto_set_featured_thumbnail');
	add_action('draft_to_publish', 'photoboard_auto_set_featured_thumbnail');
	add_action('new_to_publish', 'photoboard_auto_set_featured_thumbnail');
	add_action('pending_to_publish', 'photoboard_auto_set_featured_thumbnail');
	add_action('future_to_publish', 'photoboard_auto_set_featured_thumbnail');




	/**
	 * Notify members of new post by email
	 */
	function photoboard_new_post_email() {

		// Variables
		global $post;
		$author = $post->post_author;
		$post_id = $post->ID;
		$users = get_users();
		// $user->user_email

		// Loop through each user
		foreach ($users as $user) {

			// User variables
			$user_id = $user->ID;
			$email = $user->user_email;
			$notifications = get_user_meta($user_id, 'photoboard_get_notifications', 'true');

			// Email variables
			$to = $email;
			$subject = 'New photos on Photoboard: ' . get_the_title( $post_id );
			$message =
				'Someone posted new photos or videos on Photoboard. Click here to view them: ' . get_permalink( $post_id) . "\r\n\r\n" .
				'To stop receiving these emails, visit ' . site_url() . '/notifications' . "\r\n";
			$headers = 'From: Photoboard <notifications@' . site_url() . '>' . "\r\n";

			// Don't send notification to post author
			if ( $user_id === $author ) return;

			// Send email
			if ( $notifications !== 'off' ) {
				wp_mail( $to, $subject, $message, $headers );
			}
		}

	}
	add_action('save_post', 'photoboard_auto_set_featured_thumbnail');
	add_action('draft_to_publish', 'photoboard_auto_set_featured_thumbnail');
	add_action('new_to_publish', 'photoboard_auto_set_featured_thumbnail');
	add_action('pending_to_publish', 'photoboard_auto_set_featured_thumbnail');
	add_action('future_to_publish', 'photoboard_auto_set_featured_thumbnail');




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
	 * @link http://davidwalsh.name/create-zip-php
	 * @param  array   $files       Files to compress
	 * @param  string  $destination Destination to save the file
	 * @param  boolean $overwrite   If true, overwrites existing file with same name
	 * @return string              	File name/destination
	 */
	function photoboard_create_zip($files = array(),$destination = '',$overwrite = false) {
		//if the zip file already exists and overwrite is false, return false
		if(file_exists($destination) && !$overwrite) { return false; }
		//vars
		$valid_files = array();
		//if files were passed in...
		if(is_array($files)) {
			//cycle through each file
			foreach($files as $file) {
				//make sure the file exists
				if(file_exists($file)) {
					$valid_files[] = $file;
				}
			}
		}
		//if we have good files...
		if(count($valid_files)) {
			//create the archive
			$zip = new ZipArchive();
			if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
				return false;
			}
			//add the files
			foreach($valid_files as $file) {
				$zip->addFile($file,$file);
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

			//close the zip -- done!
			$zip->close();

			//check to make sure the file exists
			return file_exists($destination);
		}
		else
		{
			return false;
		}
	}




	/**
	 * When a new post is created, generate a zip of all media
	 */
	function photoboard_create_zip_on_save() {

		// Variables
		global $post;
		$wp_upload_directory = wp_upload_dir();
		$files_to_zip = array();
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
				'post_parent'    => $id,
				'posts_per_page' => -1,
			)
		);

		// Push files to array
		if ($images) {
			foreach ($images as $image) {
				$files_to_zip[] = wp_get_attachment_image_src( $image->ID, 'full' )[0];
			}
		}

		if ($videos) {
			foreach ($videos as $video) {
				$files_to_zip[] = $video->guid;
			}
		}

		// Create zip
		photoboard_create_zip($files_to_zip, $wp_upload_directory[baseurl] . '/photoboard/' . $post->post_name . '.zip', true);

	}
	add_action('save_post', 'photoboard_create_zip_on_save');
	add_action('draft_to_publish', 'photoboard_create_zip_on_save');
	add_action('new_to_publish', 'photoboard_create_zip_on_save');
	add_action('pending_to_publish', 'photoboard_create_zip_on_save');
	add_action('future_to_publish', 'photoboard_create_zip_on_save');




	/**
	 * Get URL of post media zip file
	 */
	function photoboard_get_zip_file() {
		global $post;
		$wp_upload_directory = wp_upload_dir();
		return $wp_upload_directory[baseurl] . '/photoboard/' . $post->post_name . '.zip';
	}