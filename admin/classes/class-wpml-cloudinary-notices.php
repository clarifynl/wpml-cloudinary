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
			<p><?php printf(
				__( 'WPML Cloudinary is enabled but not effective. It requires <a href="%s" target="_blank">WPML</a> in order to work.', 'wpml-cloudinary' ),
				'https://wpml.org/'
			); ?>
			</p>
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
			<p><?php printf(
				__( 'WPML Cloudinary is enabled but not effective. It requires <a href="%s" target="_blank">Cloudinary</a> in order to work.', 'wpml-cloudinary' ),
				'https://wordpress.org/plugins/cloudinary-image-management-and-manipulation-in-the-cloud-cdn/'
			); ?>
			</p>
		</div>
		<?php
	}

}
