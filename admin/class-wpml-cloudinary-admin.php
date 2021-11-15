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
class Wpml_Cloudinary_Admin {

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

		require_once plugin_dir_path( __FILE__ ) . 'admin/includes/class-wpml-cloudinary-attached-file.php';
		require_once plugin_dir_path( __FILE__ ) . 'admin/includes/class-wpml-cloudinary-notices.php';
	}

	/**
	 * Register the plugin requirements
	 *
	 * @since    1.0.0
	 */
	public function load_requirements() {
		$notices = new Wpml_Cloudinary_Notices();

		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			add_action( 'admin_notices', array( $notices, 'missing_wpml_notice' ) );
		}

		if ( ! defined( 'CLDN_CORE') ) {
			add_action( 'admin_notices', array( $notices, 'missing_cldn_notice' ) );
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in wpml_cloudinary_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The wpml_cloudinary_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->wpml_cloudinary, plugin_dir_url( __FILE__ ) . 'css/wpml-cloudinary-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in wpml_cloudinary_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The wpml_cloudinary_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->wpml_cloudinary, plugin_dir_url( __FILE__ ) . 'js/wpml-cloudinary-admin.js', array( 'jquery' ), $this->version, false );

	}

	/*
	 * Update duplicated WPML attachment file when original is updated by Cloudinary
	 */
	public function updated_attached_file($file, $attachment_id) {
		$attached_file = new Wpml_Cloudinary_Attached_File();

		return $attached_file->file_updated($file, $attachment_id);
	}
}
