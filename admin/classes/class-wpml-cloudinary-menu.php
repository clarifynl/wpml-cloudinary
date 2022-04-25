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

		$template_model   = array(
			'spinner'     => ICL_PLUGIN_URL . '/res/img/ajax-loader.gif',
			'ajax_nonce'  => wp_create_nonce('wc_ajax_nonce'),
			'strings'     => array(
				'heading'     => __('Cloudinary Media Translation', 'wpml-cloudinary'),
				'warning'     => __('Please make backup of your database before using this.', 'wpml-cloudinary'),
				'finished'    => __("You're all done! From now on, all new cloudinary media that you insert will be synced between translations.", 'wpml-cloudinary')
			),
			'file_paths'  => array(
				'description' => __('Copies the attached file path from the original to attachments that are marked as translations.', 'wpml-cloudinary'),
				'button'      => __('Fix missing file paths on duplicated media', 'wpml-cloudinary'),
			),
			'cloudinary_meta' => array(
				'description' => __('Copies the cloudinary metadata from the original to attachments that are marked as translations.', 'wpml-cloudinary'),
				'button'      => __('Fix missing cloudinary metadata on duplicated media', 'wpml-cloudinary'),
			),
			'wordpress_meta' => array(
				'description' => __('Copies the wordpress metadata from the original to attachments that are marked as translations.', 'wpml-cloudinary'),
				'button'      => __('Fix incorrect wordpress metadata on duplicated media', 'wpml-cloudinary'),
			)
		);

		echo $template_service->show($template_model, 'wpml-cloudinary-menu.twig');
	}
}
