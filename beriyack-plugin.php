<?php
/**
 * Fichier principal d'initialisation du plugin
 *
 * Ce fichier est lu par WordPress pour générer les métadonnées de l'extension
 * dans l'administration de WordPress. Il charge également toutes les dépendances,
 * enregistre les fonctions d'activation/désactivation et démarre le plugin.
 *
 * @link              https://github.com/beriyack/beriyack-plugin
 * @since             1.0.0
 * @package           Beriyack_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Beriyack Plugin
 * Plugin URI:        https://github.com/beriyack/beriyack-plugin
 * Description:       Un plugin WordPress complet pour gérer le SEO social (balises Open Graph, Twitter Cards) et optimiser la sécurité et la vitesse du site (limitation des révisions, suppression des traces WordPress, protection de l'API REST).
 * Version:           1.2.0
 * Author:            Beriyack
 * Author URI:        https://beriyack.ch
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       beriyack-plugin
 * Domain Path:       /languages
 */

// Si ce fichier est appelé directement, on avorte.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'BERIYACK_PLUGIN_VERSION', '1.2.0' );
define( 'BERIYACK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BERIYACK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Code exécuté lors de l'activation du plugin.
 */
function activate_beriyack_plugin() {
	require_once BERIYACK_PLUGIN_PATH . 'includes/class-beriyack-plugin-admin.php';
	// Définit les options par défaut lors de l'activation si elles n'existent pas
	Beriyack_Plugin_Admin::set_default_options();
}
register_activation_hook( __FILE__, 'activate_beriyack_plugin' );

/**
 * La classe principale du plugin utilisée pour définir l'internationalisation,
 * les hooks d'administration et les hooks publics.
 */
require BERIYACK_PLUGIN_PATH . 'includes/class-beriyack-plugin.php';

/**
 * Démarre l'exécution du plugin.
 *
 * Comme tout le plugin est enregistré via des hooks WordPress, le lancement
 * de la classe principale va enregistrer tous les hooks nécessaires.
 *
 * @since    1.0.0
 */
function run_beriyack_plugin() {
	$plugin = new Beriyack_Plugin();
	$plugin->run();
}
run_beriyack_plugin();
