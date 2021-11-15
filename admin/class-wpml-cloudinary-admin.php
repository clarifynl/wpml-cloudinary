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

	}

	/**
	 * Register the plugin requirements
	 *
	 * @since    1.0.0
	 */
	public function load_requirements() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			add_action( 'admin_notices', array( $this, 'missing_wpml_notice' ) );
		}

		if ( ! defined( 'CLDN_CORE') ) {
			add_action( 'admin_notices', array( $this, 'missing_cldn_notice' ) );
		}
	}

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
