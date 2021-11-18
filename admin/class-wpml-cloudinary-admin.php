<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    wpml_cloudinary
 * @subpackage wpml_cloudinary/admin
 * @author     Your Name <email@example.com>
 */
class WPML_Cloudinary_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $wpml_cloudinary    The ID of this plugin.
	 */
	private $wpml_cloudinary;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $wpml_cloudinary       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $wpml_cloudinary, $version ) {

		$this->wpml_cloudinary = $wpml_cloudinary;
		$this->version = $version;

		require_once plugin_dir_path( __FILE__ ) . 'classes/class-wpml-cloudinary-duplicate-media.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-wpml-cloudinary-menu.php';
		require_once plugin_dir_path( __FILE__ ) . 'classes/class-wpml-cloudinary-notices.php';
	}

	/**
	 * Register the plugin requirements
	 *
	 * @since    1.0.0
	 */
	public function load_requirements() {
		$notices = new WPML_Cloudinary_Notices();

		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			add_action( 'admin_notices', array( $notices, 'missing_wpml_notice' ) );
		}

		if ( ! defined( 'CLDN_CORE') ) {
			add_action( 'admin_notices', array( $notices, 'missing_cldn_notice' ) );
		}
	}

	/**
	 * When WPML is loaded
	 *
	 * @since    1.0.0
	 */
	public function wpml_loaded() {
		$menu = new WPML_Cloudinary_Menu();

		if ( $this->is_admin_or_xmlrpc() && ! $this->is_uploading_plugin_or_theme() ) {
			add_action( 'wpml_admin_menu_configure', array( $menu, 'wpml_menu' ) );
		}
	}

	public function is_admin_or_xmlrpc() {
		$is_admin  = is_admin();
		$is_xmlrpc = ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST );

		return $is_admin || $is_xmlrpc;
	}

	public function is_uploading_plugin_or_theme() {
		global $action;

		return ( isset( $action ) && ( $action == 'upload-plugin' || $action == 'upload-theme' ) );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->wpml_cloudinary, plugin_dir_url( __FILE__ ) . 'js/wpml-cloudinary-admin.js', array( 'jquery' ), $this->version, false );
	}

	/*
	 * Update duplicated WPML attachment file when original is updated by Cloudinary
	 */
	public function updated_attached_file($file, $attachment_id) {
		$duplicate_media = new WPML_Cloudinary_Duplicate_Media();

		return $duplicate_media->file_updated($file, $attachment_id);
	}

	/*
	 * Update duplicated WPML attachment meta when original is updated by Cloudinary
	 */
	public function updated_attachment_meta($meta_id, $object_id, $meta_key, $_meta_value) {
		$duplicate_media   = new WPML_Cloudinary_Duplicate_Media();
		$is_duplicate_post = apply_filters('wpml_master_post_from_duplicate', $object_id);
		syslog(LOG_DEBUG, 'object_id: ' . $object_id . ' is_duplicate_post: ' . $is_duplicate_post);

		if (get_post_type($object_id) === 'attachment' && !$is_duplicate_post) {
			return $duplicate_media->meta_updated($object_id, $meta_key, $_meta_value);
		}

		return;
	}

	/**
	 * Handle the AJAX post request for fixing the missing file paths
	 *
	 * @since    1.0.0
	 */
	public function fix_missing_file_paths() {
		$duplicate_media = new WPML_Cloudinary_Duplicate_Media();

		return $duplicate_media->fix_missing_file_paths();
	}
}
