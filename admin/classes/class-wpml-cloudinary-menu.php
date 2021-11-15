<?php

class WPML_Cloudinary_Menu {

	/**
	 * @param string $menu_id
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

	public function menu_content() {
		$template_paths   = array( WPML_CLOUDINARY_PLUGIN_PATH . '/admin/templates' );
		$template_loader  = new WPML_Twig_Template_Loader( $template_paths );
		$template_service = $template_loader->get_template();
		$template_model   = array(
			'attachments'  => array(),
			'strings'      => array(
				'heading'     => __('Cloudinary Media Translation', 'wpml-cloudinary'),
				'description' => __('If existing duplicated media is missing the cloudinary url as attached file path, press the <strong>sync</strong> button below', 'wpml-cloudinary')
			)
		);

		echo $template_service->show($template_model, 'wpml-cloudinary-menu.twig');
	}
}
