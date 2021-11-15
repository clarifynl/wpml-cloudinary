<?php

class Wpml_Cloudinary_Attached_File {
	/**
	 * Get all attachment translations created by WPML
	 */
	public function get_duplicated_attachments($attachment_id) {
		$trid = apply_filters('wpml_element_trid', NULL, $attachment_id, 'post_attachment');
		if ($trid) {
			$duplications = apply_filters('wpml_get_element_translations', NULL, $trid, 'post_attachment');

			return $duplications;
		}

		return;
	}

	/*
	 * Update duplicated WPML attachment file when original is updated by Cloudinary
	 */
	public function file_updated($file, $attachment_id) {
		/*
		 * _wp_relative_upload_path function is marked private.
		 * This means it is not intended for use by plugin or theme developers, only in other core functions. It is listed here for completeness.
		 */
		$upload_file = _wp_relative_upload_path($file);
		$duplicates  = $this->get_duplicated_attachments($attachment_id);

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
