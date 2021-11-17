<?php

use Cloudinary\Media;

class WPML_Cloudinary_Duplicate_Media {

	/**
	 * @var $cloudinary_media
	 */
	private $cloudinary_media = null;

	/**
	 * Get Cloudinary Plugin Media class
	 */
	private function get_cloudinary_media() {
		global $cloudinary_plugin;

		if (!$this->cloudinary_media && $cloudinary_plugin) {
			$this->cloudinary_media = new Media($cloudinary_plugin);
		}

		return $this->cloudinary_media;
	}

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
		$upload_file      = $file;
		$uploads          = wp_get_upload_dir();
		$cloudinary_media = $this->get_cloudinary_media();

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
					$is_cloudinary    = (bool) $cloudinary_media->is_cloudinary_url($upload_file);
					$cloudinary_meta  = get_post_meta($attachment_id, '_cloudinary_v2', true);
					$duplicate_id     = (int) $duplicate->element_id;
					update_post_meta($duplicate_id, '_wp_attached_file', $upload_file);

					// Copy _cloudinary_v2 meta when sync is finished
					syslog(LOG_DEBUG, 'file: ' . $upload_file . ' cloudinary url: ' . $is_cloudinary);
					if ($is_cloudinary) {
						update_post_meta($duplicate_id, '_cloudinary_v2', $cloudinary_meta);
					}
				}
			}
		}

		return $file;
	}

	/*
	 * Fix missing file paths on existing duplicated media
	 */
	public function fix_missing_file_paths() {
		global $wpdb;

		$limit = 10;
		$response = array();
		$cloudinary_media = $this->get_cloudinary_media();

		/*
		 * MYSQL query that gets:
		 * 1) All attachment translations with a language code
		 * 2) Checks if the translation has a missing _wp_attached_file in postmeta
		 * 3) Checks if the original attachment exists in the translations
		 * 4) Checks if the original attachment does have a _wp_attached_file in postmeta
		 */
		$sql = "
			SELECT SQL_CALC_FOUND_ROWS t.element_id, t.trid, t.source_language_code
			FROM {$wpdb->prefix}icl_translations as t
			INNER JOIN $wpdb->postmeta pm
				ON t.element_id = pm.post_id
			WHERE t.element_type = 'post_attachment'
				AND t.source_language_code IS NOT null
				AND pm.meta_key = '_wp_attached_file'
				AND pm.meta_value = ''
				AND t.trid IN (
					SELECT trid
					FROM {$wpdb->prefix}icl_translations as o
					INNER JOIN $wpdb->postmeta pmo
						ON o.element_id = pmo.post_id
					WHERE o.element_type = 'post_attachment'
						AND o.source_language_code IS null
						AND pmo.meta_key = '_wp_attached_file'
						AND pmo.meta_value <> ''
				)
			LIMIT %d
		";

		$sql_prepared = $wpdb->prepare( $sql, $limit );
		$attachments  = $wpdb->get_results( $sql_prepared );
		$found        = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$translation      = (int) $attachment->element_id;
				$original_lang    = $attachment->source_language_code;
				$original         = (int) apply_filters('wpml_object_id', $translation, 'attachment', FALSE, $original_lang);
				$attached_file    = get_post_meta($original, '_wp_attached_file', true);
				$is_cloudinary    = (bool) $cloudinary_media->is_cloudinary_url($attached_file);

				if ($attached_file && $is_cloudinary) {
					update_post_meta($translation, '_wp_attached_file', $attached_file);
				}
			}
		}

		$response['left'] = max( $found - $limit, 0 );
		if ( $response['left'] ) {
			$response['message'] = sprintf( esc_html__( 'Updating duplicated media. %d left', 'wpml-cloudinary' ), $response['left'] );
		} else {
			$response['message'] = sprintf( esc_html__( 'Updating duplicated media: done!', 'wpml-cloudinary' ), $response['left'] );
		}

		wp_send_json( $response );
	}
}
