<?php

/**
 * helpers.php
 * Utility methods for plugins.
 */

	/**
	 * Form Fields
	 */

	// Label
	function photoboard_form_field_label( $id = '', $label = '' ) {
		$field =
			'<label for="' . $id . '">' .
				$label .
			'</label>';
		return $field;
	}

	// Checkbox
	function photoboard_form_field_checkbox( $id = '', $label = '', $value = '', $checked = '' ) {
		$field =
			'<label for="' . $id . '">' .
				'<input type="checkbox"
					id="' . $id . '"
					name="' . $id . '"
					value="' . $value . '" ' .
					$checked .
				'>' .
				$label .
			'</label>';
		return $field;
	}

	// Text Input
	function photoboard_form_field_text_input( $type = 'text', $id = '', $label = '', $value = '' ) {
		$field =
			'<input
				type="' . $type . '"
				id="' . $id . '"
				name="' . $id . '"
				value="' . $value . '" ' .
			'>';
		return $field;
	}

	// Text Area
	function photoboard_form_field_text_area( $id = '', $label = '', $value = '' ) {
		$field =
			'<textarea
				id="' . $id . '"
				name="' . $id . '" ' .
			'>' .
				$value .
			'</textarea>';
		return $field;
	}


	// Submit
	function photoboard_form_field_submit( $id = '', $class = '', $label = '', $action = '', $nonce_field = '' ) {
		$field =
			wp_nonce_field( $action, $nonce_field) .
			'<button type="submit" class="' . $class . '" id="' . $id . '" name="' . $id . '">' . $label . '</button>';
		return $field;
	}

	// Checkbox + Wrapper
	function photoboard_form_field_checkbox_plus( $id, $label, $value = '', $checked = '' ) {
		$field =
			'<div class="field-checkbox">' .
					photoboard_form_field_checkbox( $id, $label, $value, $checked ) .
			'</div>';
		return $field;
	}

	// Text Input + Label and Wrapper
	function photoboard_form_field_text_input_plus( $type, $id, $label, $value = '' ) {
		$field =
			'<div class="field-text-input">' .
				photoboard_form_field_label( $id, $label ) .
				photoboard_form_field_text_input( $type, $id, $label, $value ) .
			'</div>';
		return $field;
	}

	// Text Area + Label and Wrapper
	function photoboard_form_field_text_area_plus( $id, $label, $value = '' ) {
		$field =
			'<div class="field-textarea">' .
				photoboard_form_field_label( $id, $label ) .
				photoboard_form_field_text_area( $id, $label, $value ) .
			'</div>';
		return $field;
	}


	// Submit + Wrapper
	function photoboard_form_field_submit_plus( $id, $class, $label, $action, $nonce_field ) {
		$field =
			'<div class="field-submit">' .
				wp_nonce_field( $action, $nonce_field) .
				photoboard_form_field_submit( $id, $class, $label, $action, $nonce_field ) .
			'</div>';
		return $field;
	}





	/**
	 * URL Helpers
	 */

	// Get and sanitize the current URL
	function photoboard_get_url() {

		// Get URL
		$url  = @( $_SERVER['HTTPS'] != 'on' ) ? 'http://' . $_SERVER['SERVER_NAME'] :  'https://' . $_SERVER['SERVER_NAME'];
		$url .= ( $_SERVER['SERVER_PORT'] !== 80 ) ? ":" . $_SERVER['SERVER_PORT'] : '';
		$url .= $_SERVER['REQUEST_URI'];

		return $url;
	}


	// Get the site domain and remove the www.
	function photoboard_get_site_domain() {
		$sitename = strtolower( $_SERVER['SERVER_NAME'] );
		if ( substr( $sitename, 0, 4 ) == 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}
		return $sitename;
	}


	// Prepare URL for status string
	function photoboard_prepare_url( $url ) {

		// If URL has a '?', add an '&'.
		// Otherwise, add a '?'.
		$url_status = strpos($url, '?');
		if ( $url_status === false ) {
			$concate = '?';
		}
		else {
			$concate = '&';
		}

		return $url . $concate;
	}


	// Remove a $_GET variable from the URL
	function photoboard_clean_url( $variable, $url ) {
		$new_url = preg_replace('/(?:&|(\?))' . $variable . '=[^&]*(?(1)&|)?/i', '$1', $url);
		$last_char = substr( $new_url, -1 );
		if ( $last_char == '?' ) {
			$new_url = substr($new_url, 0, -1);
		}
		return $new_url;
	}





	/**
	 * String Helpers
	 */

	// Does string contain letter?
	function photoboard_has_letters( $string ) {
		return preg_match( '/[a-zA-Z]/', $string );
	}

	// Does string contain numbers?
	function photoboard_has_numbers( $string ) {
		return preg_match( '/\d/', $string );
	}

	// Does string contain special characters?
	function photoboard_has_special_chars( $string ) {
		return preg_match('/[^a-zA-Z\d]/', $string);
	}