<?php

class WPML_Cloudinary_Duplicate_Media {

	/*
	 * Fix missing file paths on duplicated media
	 */
	public function fix_missing_file_paths() {
		global $wpdb;

		$limit            = 10;
		$response         = array();
		$active_languages = count(apply_filters('wpml_active_languages', NULL));

		$sql = "
			SELECT SQL_CALC_FOUND_ROWS p1.ID, p1.post_parent
			FROM {$wpdb->prefix}icl_translations t
			INNER JOIN {$wpdb->posts} p1
				ON t.element_id = p1.ID
			LEFT JOIN {$wpdb->prefix}icl_translations tt
				ON t.trid = tt.trid
			WHERE t.element_type = 'post_attachment'
				AND t.source_language_code IS null
			GROUP BY p1.ID, p1.post_parent
			HAVING count(tt.language_code) < %d
			LIMIT %d
		";

		$sql_prepared = $wpdb->prepare( $sql, array( $active_languages, $limit ) );
		$attachments  = $wpdb->get_results( $sql_prepared );
		$found        = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				syslog(LOG_DEBUG, 'fix_missing_file_paths attachment: '. json_encode($attachment));
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

		if (0 === strpos($file, $uploads['basedir'])) {
			$upload_file = str_replace($uploads['basedir'], '', $file);
			$upload_file = ltrim($upload_file, '/');
		}

		$upload_file = apply_filters('_wp_relative_upload_path', $upload_file, $file);
		$duplicates  = $this->get_attachment_duplicates($attachment_id);

		// If attachment has duplicates
		if ($duplicates && $upload_file) {
			foreach ($duplicates as $duplicate) {
				if (!$duplicate->original) {
					$duplicate_id = (int) $duplicate->element_id;
					update_post_meta($duplicate_id, '_wp_attached_file', $upload_file);
				}
			}
		}

		return $file;
	}

}
