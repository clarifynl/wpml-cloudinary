<?php

class WPML_Cloudinary_Duplicate_Media {

	/**
	 * Holds the meta keys for Cloudinary sync meta to maintain consistency.
	 */
	const META_KEYS = array(
		'attached_file' => '_wp_attached_file',
		'public_id'     => '_public_id',
		'cloudinary'    => '_cloudinary_v2',
		'process_log'   => '_cloudinary_process_log',
		'cloudinary_v3' => '_cloudinary'
	);

	/**
	 * Get all attachment translations created by WPML
	 */
	public function get_attachment_duplicates($attachment_id) {
		$trid = apply_filters('wpml_element_trid', NULL, $attachment_id, 'post_attachment');
		if ($trid) {
			$duplications = apply_filters('wpml_get_element_translations', NULL, $trid, 'post_attachment');

			return $duplications;
		}

		return;
	}

	/*
	 * Update duplicated WPML attachment file when original get's updated by Cloudinary
	 */
	public function file_updated($file, $attachment_id) {
		$upload_file = $file;
		$uploads     = wp_get_upload_dir();

		// Get relative upload path from absolute file path
		if (0 === strpos($file, $uploads['basedir'])) {
			$upload_file = str_replace($uploads['basedir'], '', $file);
			$upload_file = ltrim($upload_file, '/');
		}

		$upload_file = apply_filters('_wp_relative_upload_path', $upload_file, $file);
		$duplicates  = $this->get_attachment_duplicates($attachment_id);

		// Get attachment duplicates
		if ($duplicates && $upload_file) {
			foreach ($duplicates as $duplicate) {
				if (!$duplicate->original) {
					$duplicate_id = (int) $duplicate->element_id;
					update_post_meta($duplicate_id, self::META_KEYS['attached_file'], $upload_file);
				}
			}
		}

		return $file;
	}

	/*
	 * Update duplicated WPML attachment cloudinary meta when original get's updated by Cloudinary
	 */
	public function meta_updated($attachment_id, $meta_key, $meta_value) {
		if ($meta_key === self::META_KEYS['cloudinary'] && $meta_value) {
			$duplicates = $this->get_attachment_duplicates($attachment_id);

			if ($duplicates) {
				foreach ($duplicates as $duplicate) {
					if (!$duplicate->original) {
						$duplicate_id = (int) $duplicate->element_id;
						update_post_meta($duplicate_id, self::META_KEYS['cloudinary'], $meta_value);
					}
				}
			}
		}

		return;
	}

	/*
	 * Fix missing file paths on existing duplicated media
	 */
	public function fix_missing_file_paths() {
		global $wpdb;

		$limit = 10;
		$response = array();

		/*
		 * MYSQL query that gets:
		 * 1) All attachment translations with a source_language_code
		 * 2) Checks if the translation has a missing _wp_attached_file in it's postmeta
		 * 3) Checks if the original attachment exists in the translations
		 * 4) Checks if the original attachment does have a _wp_attached_file in it's postmeta
		 */
		$sql = "
			SELECT SQL_CALC_FOUND_ROWS t.element_id, t.trid, t.source_language_code
			FROM {$wpdb->prefix}icl_translations as t
			INNER JOIN $wpdb->postmeta pm
				ON t.element_id = pm.post_id
			WHERE t.element_type = 'post_attachment'
				AND t.source_language_code IS NOT null
				AND pm.meta_key = %s
				AND pm.meta_value = ''
				AND t.trid IN (
					SELECT trid
					FROM {$wpdb->prefix}icl_translations as o
					INNER JOIN $wpdb->postmeta pmo
						ON o.element_id = pmo.post_id
					WHERE o.element_type = 'post_attachment'
						AND o.source_language_code IS null
						AND pmo.meta_key = %s
						AND pmo.meta_value <> ''
				)
			LIMIT %d
		";

		$sql_prepared = $wpdb->prepare( $sql, array( self::META_KEYS['attached_file'], self::META_KEYS['attached_file'], $limit ) );
		$attachments  = $wpdb->get_results( $sql_prepared );
		$found        = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$translation     = (int) $attachment->element_id;
				$original_lang   = $attachment->source_language_code;
				$original        = (int) apply_filters('wpml_object_id', $translation, 'attachment', FALSE, $original_lang);
				$attached_file   = get_post_meta($original, self::META_KEYS['attached_file'], true);
				$cloudinary_meta = get_post_meta($original, self::META_KEYS['cloudinary'], true);

				// Copy attached file to translations
				if ($attached_file) {
					update_post_meta($translation, self::META_KEYS['attached_file'], $attached_file);
				}

				// Copy cloudinary meta data to translations
				if ($cloudinary_meta) {
					update_post_meta($translation, self::META_KEYS['cloudinary'], $cloudinary_meta);
				}
			}
		}

		$response['left'] = max( $found - $limit, 0 );
		if ( $response['left'] ) {
			$response['message'] = sprintf( esc_html__( 'Updating attached file on duplicated media. %d left', 'wpml-cloudinary' ), $response['left'] );
		} else {
			$response['message'] = sprintf( esc_html__( 'Updating attached file on duplicated media: done!', 'wpml-cloudinary' ), $response['left'] );
		}

		wp_send_json( $response );
	}

	/*
	 * Fix missing cloudinary meta on existing duplicated media
	 */
	public function fix_missing_cloudinary_meta() {
		global $wpdb;

		$limit = 10;
		$response = array();

		/*
		 * MYSQL query that gets:
		 * 1) All attachment translations with a source_language_code
		 * 2) Checks if the translation has a missing _cloudinary_v2 in it's postmeta
		 * 3) Checks if the original attachment exists in the translations
		 * 4) Checks if the original attachment does have a _cloudinary_v2 in it's postmeta
		 */
		$sql = "
			SELECT SQL_CALC_FOUND_ROWS t.element_id, t.trid, t.source_language_code
			FROM {$wpdb->prefix}icl_translations as t
			INNER JOIN $wpdb->postmeta pm
				ON t.element_id = pm.post_id
			WHERE t.element_type = 'post_attachment'
				AND t.source_language_code IS NOT null
				AND pm.meta_key = %s
				AND pm.meta_value = ''
				AND t.trid IN (
					SELECT trid
					FROM {$wpdb->prefix}icl_translations as o
					INNER JOIN $wpdb->postmeta pmo
						ON o.element_id = pmo.post_id
					WHERE o.element_type = 'post_attachment'
						AND o.source_language_code IS null
						AND pmo.meta_key = %s
						AND pmo.meta_value <> ''
				)
			LIMIT %d
		";

		$sql_prepared = $wpdb->prepare( $sql, array( self::META_KEYS['cloudinary'], self::META_KEYS['cloudinary'], $limit ) );
		$attachments  = $wpdb->get_results( $sql_prepared );
		$found        = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
		syslog(LOG_DEBUG, 'meta keys: ' . json_encode(self::META_KEYS) . ' found: ' . $found);

		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$translation     = (int) $attachment->element_id;
				$original_lang   = $attachment->source_language_code;
				$original        = (int) apply_filters('wpml_object_id', $translation, 'attachment', FALSE, $original_lang);
				$cloudinary_meta = get_post_meta($original, self::META_KEYS['cloudinary'], true);

				// Copy cloudinary meta data to translations
				if ($cloudinary_meta) {
					update_post_meta($translation, self::META_KEYS['cloudinary'], $cloudinary_meta);
				}
			}
		}

		$response['left'] = max( $found - $limit, 0 );
		if ( $response['left'] ) {
			$response['message'] = sprintf( esc_html__( 'Updating cloudinary meta on duplicated media. %d left', 'wpml-cloudinary' ), $response['left'] );
		} else {
			$response['message'] = sprintf( esc_html__( 'Updating cloudinary meta on duplicated media: done!', 'wpml-cloudinary' ), $response['left'] );
		}

		wp_send_json( $response );
	}
}
