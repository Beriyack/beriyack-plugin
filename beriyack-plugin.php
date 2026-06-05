<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/beriyack/beriyack-plugin
 * @since             1.0.0
 * @package           Beriyack_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Beriyack Plugin
 * Plugin URI:        https://github.com/beriyack/beriyack-plugin
 * Description:       Un plugin WordPress complet pour gérer le SEO social (balises Open Graph, Twitter Cards) et optimiser la sécurité et la vitesse du site (limitation des révisions, suppression des traces WordPress, protection de l'API REST).
 * Version:           1.0.0
 * Author:            Beriyack
 * Author URI:        https://beriyack.ch
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       beriyack-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'BERIYACK_PLUGIN_VERSION', '1.0.0' );
define( 'BERIYACK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BERIYACK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_beriyack_plugin() {
	require_once BERIYACK_PLUGIN_PATH . 'includes/class-beriyack-plugin-admin.php';
	// Set default options on activation if they don't exist
	Beriyack_Plugin_Admin::set_default_options();
}
register_activation_hook( __FILE__, 'activate_beriyack_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require BERIYACK_PLUGIN_PATH . 'includes/class-beriyack-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file will
 * register all of the hooks with WordPress.
 *
 * @since    1.0.0
 */
function run_beriyack_plugin() {
	$plugin = new Beriyack_Plugin();
	$plugin->run();
}
run_beriyack_plugin();
