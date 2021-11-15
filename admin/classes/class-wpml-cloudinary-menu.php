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
		global $sitepress, $wpdb;

		$wpml_wp_api     = $sitepress->get_wp_api();
		$wpml_media_path = $wpml_wp_api->constant( 'WPML_MEDIA_PATH' );

		$template_service_loader = new WPML_Twig_Template_Loader( array( $wpml_media_path . '/templates/menus/' ) );
		$pagination              = new WPML_Admin_Pagination();

		return new WPML_Media_Menus( $template_service_loader, $sitepress, $wpdb, $pagination );
	}
}
