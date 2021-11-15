<?php

class Wpml_Cloudinary_Notices {

	/**
	 * Missing WPML notice
	 *
	 * @since    1.0.0
	 */
	public function missing_wpml_notice() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'WPML Cloudinary is enabled but not effective. It requires WPML in order to work.', 'wpml-cloudinary' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Missing Cloudinary notice
	 *
	 * @since    1.0.0
	 */
	public function missing_cldn_notice() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'WPML Cloudinary is enabled but not effective. It requires the Cloudinary plugin in order to work.', 'wpml-cloudinary' ); ?></p>
		</div>
		<?php
	}

}
