<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/beriyack/beriyack-plugin
 * @since      1.0.0
 * @package    Beriyack_Plugin
 * @subpackage Beriyack_Plugin/includes
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Beriyack_Plugin {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Beriyack_Plugin_Admin. Handles all administrative options and styles.
	 * - Beriyack_Plugin_SEO. Handles Open Graph and Twitter Card tags in frontend.
	 * - Beriyack_Plugin_Security. Handles security and optimizations filters.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once BERIYACK_PLUGIN_PATH . 'includes/class-beriyack-plugin-admin.php';
		require_once BERIYACK_PLUGIN_PATH . 'includes/class-beriyack-plugin-seo.php';
		require_once BERIYACK_PLUGIN_PATH . 'includes/class-beriyack-plugin-security.php';
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		// Instantiate administrative settings and hooks
		$plugin_admin = new Beriyack_Plugin_Admin();
		$plugin_admin->init_hooks();

		// Instantiate SEO public-facing hooks
		$plugin_seo = new Beriyack_Plugin_SEO();
		$plugin_seo->init_hooks();

		// Instantiate security and optimization hooks
		$plugin_security = new Beriyack_Plugin_Security();
		$plugin_security->init_hooks();
	}
}
