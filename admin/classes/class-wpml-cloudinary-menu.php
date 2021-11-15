<?php

class WPML_Cloudinary_Menu {

	/**
	 * Register WPML Cloudinary menu
	 */
	public function wpml_menu( $menu_id ) {
		if ( 'WPML' !== $menu_id ) {
			return;
		}

		$menu_label         = __( 'Cloudinary Media', 'wpml-cloudinary' );
		$menu               = array();
		$menu['order']      = 600;
		$menu['page_title'] = $menu_label;
		$menu['menu_title'] = $menu_label;
		$menu['capability'] = 'edit_others_posts';
		$menu['menu_slug']  = 'wpml-cloudinary';
		$menu['function']   = array( $this, 'menu_content' );

		do_action( 'wpml_admin_menu_register_item', $menu );
	}

	/**
	 * Render WPML Cloudinary menu
	 */
	public function menu_content() {
		$template_paths   = array( WPML_CLOUDINARY_PLUGIN_PATH . '/admin/templates' );
		$template_loader  = new WPML_Twig_Template_Loader( $template_paths );
		$template_service = $template_loader->get_template();

		$languages        = apply_filters('wpml_active_languages', NULL);
		$template_model   = array(
			'attachments'  => $this->get_duplicated_media(),
			'languages'    => $languages,
			'strings'      => array(
				'heading'           => __('Cloudinary Media Translation', 'wpml-cloudinary'),
				'description'       => __('If existing duplicated media is missing the cloudinary url as attached file path, press the <strong>sync</strong> button below', 'wpml-cloudinary'),
				'original_language' => __('Original Media Language', 'wpml-cloudinary')
			)
		);

		echo $template_service->show($template_model, 'wpml-cloudinary-menu.twig');
	}

	/**
	 * Get WPML batch translate limit
	 *
	private function get_batch_translate_limit( $active_languages ) {
		global $sitepress;

		$limit = $sitepress->get_wp_api()->constant('WPML_MEDIA_BATCH_LIMIT');
		$limit = !$limit ? floor(10 / max($active_languages - 1, 1)) : $limit;
		return max($limit, 1);
	}*/

	/**
	 * Update all duplicated attachements by WPML
	 *
	public function batch_update_duplicated_media() {
		global $wpdb;

		$wpml_media_att_dup = make(\WPML_Media_Attachments_Duplication::class);
		$active_languages   = count(apply_filters('wpml_active_languages', NULL));
		$limit              = $this->get_batch_translate_limit($active_languages);

		$sql = "
			SELECT element_id
			FROM {$wpdb->prefix}icl_translations
			WHERE element_type = 'post_attachment'
			AND source_language_code IS NULL
			LIMIT %d
		";

		$sql_prepared = $wpdb->prepare($sql, [$limit]);
		$attachments  = $wpdb->get_results($sql_prepared);
		$found        = $wpdb->get_var('SELECT FOUND_ROWS()');

		if ($attachments) {
			foreach ($attachments as $attachment) {
				syslog(LOG_DEBUG, '$attachment: '. json_encode($attachment));
			}
		}

		$response = [];
		$response['left'] = max($found - $limit, 0);
		if ($response['left']) {
			$response['message'] = sprintf(esc_html__('Updating duplicated media. %d left', 'sitepress'), $response['left']);
		} else {
			$response['message'] = sprintf(esc_html__('Updating duplicated media: done!', 'sitepress'), $response['left']);
		}

		echo wp_json_encode($response);
	}*/

	/**
	 * Get duplicated media
	 */
	public function get_duplicated_media() {
		return;
	}
}
