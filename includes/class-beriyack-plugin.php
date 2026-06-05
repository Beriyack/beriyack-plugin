<?php
/**
 * Fichier définissant la classe d'orchestration principale du plugin.
 *
 * Charge les dépendances et lance l'enregistrement des hooks d'administration
 * et de la partie publique du site.
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
	 * Définit les fonctionnalités principales du plugin.
	 *
	 * Initialise le plugin, charge les dépendances et prépare les hooks
	 * pour l'administration et le frontend.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
	}

	/**
	 * Charge les dépendances requises pour le plugin.
	 *
	 * Inclut les fichiers suivants :
	 * - Beriyack_Plugin_Admin : Gestion de l'admin (styles, JS, AJAX, réglages).
	 * - Beriyack_Plugin_SEO : Gestion des balises Open Graph et Twitter Cards en frontend.
	 * - Beriyack_Plugin_Security : Gestion des filtres de sécurité et de vitesse.
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
	 * Exécute le plugin en attachant tous les hooks à WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		// Instancie les fonctionnalités et hooks de l'administration
		$plugin_admin = new Beriyack_Plugin_Admin();
		$plugin_admin->init_hooks();

		// Instancie les fonctionnalités et hooks du SEO public
		$plugin_seo = new Beriyack_Plugin_SEO();
		$plugin_seo->init_hooks();

		// Instancie les fonctionnalités et hooks de sécurité et d'optimisation
		$plugin_security = new Beriyack_Plugin_Security();
		$plugin_security->init_hooks();
	}
}
