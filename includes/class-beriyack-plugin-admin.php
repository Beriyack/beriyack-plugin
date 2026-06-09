<?php
/**
 * Logique de l'administration du plugin.
 *
 * Enregistre les menus, charge les feuilles de style et scripts, gère la sauvegarde
 * des options et les requêtes AJAX (sauvegarde et prévisualisation d'article).
 *
 * @link       https://github.com/beriyack/beriyack-plugin
 * @since      1.0.0
 * @package    Beriyack_Plugin
 * @subpackage Beriyack_Plugin/includes
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Beriyack_Plugin_Admin {

	/**
	 * Initialise les hooks pour l'administration.
	 */
	public function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
		add_action( 'wp_ajax_beriyack_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_beriyack_get_post_preview', array( $this, 'ajax_get_post_preview' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( BERIYACK_PLUGIN_PATH . 'beriyack-plugin.php' ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Définit les options par défaut du plugin à l'activation.
	 */
	public static function set_default_options() {
		if ( false === get_option( 'beriyack_plugin_options' ) ) {
			$defaults = array(
				'seo_enable'              => '1',
				'seo_fb_app_id'           => '',
				'seo_twitter_site'        => '',
				'seo_fallback_img'        => '',
				'seo_add_sitemap_robots'  => '1',
				'seo_noindex_search_404'  => '1',
				'sec_revisions'           => '5',
				'sec_rm_generator'        => '1',
				'sec_hide_login'          => '1',
				'sec_dis_xmlrpc'          => '1',
				'sec_dis_rest_api'        => '0',
				'sec_rm_ver'              => '0',
				'sec_dis_emojis'          => '1',
			);
			update_option( 'beriyack_plugin_options', $defaults );
		}
	}

	/**
	 * Enregistre le menu principal et les sous-menus d'administration.
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			'Beriyack Plugin',
			'Beriyack Plugin',
			'manage_options',
			'beriyack-plugin',
			array( $this, 'display_settings_page' ),
			'dashicons-shield',
			80
		);

		add_submenu_page(
			'beriyack-plugin',
			'Tableau de bord',
			'Tableau de bord',
			'manage_options',
			'beriyack-plugin',
			array( $this, 'display_settings_page' )
		);

		add_submenu_page(
			'beriyack-plugin',
			'SEO Social',
			'SEO Social',
			'manage_options',
			'beriyack-plugin-seo',
			array( $this, 'display_settings_page' )
		);

		add_submenu_page(
			'beriyack-plugin',
			'Sécurité & Optimisation',
			'Sécurité & Optimisation',
			'manage_options',
			'beriyack-plugin-security',
			array( $this, 'display_settings_page' )
		);
	}

	/**
	 * Enfile (enqueue) les feuilles de style et fichiers javascript pour l'administration.
	 * Utilise des préfixes uniques pour éviter tout conflit avec "beriyack-theme".
	 */
	public function enqueue_styles_and_scripts( $hook ) {
		// Enfilement uniquement sur les pages du plugin
		$pages = array(
			'toplevel_page_beriyack-plugin',
			'beriyack-plugin_page_beriyack-plugin-seo',
			'beriyack-plugin_page_beriyack-plugin-security',
		);
		if ( ! in_array( $hook, $pages, true ) ) {
			return;
		}

		// Charge la bibliothèque de médias WordPress (Uploader natif)
		wp_enqueue_media();

		// Charge les polices Google Fonts
		wp_enqueue_style( 'beriyack-plugin-google-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap', array(), BERIYACK_PLUGIN_VERSION );

		// Charge le fichier CSS personnalisé
		wp_enqueue_style( 'beriyack-plugin-admin-style', BERIYACK_PLUGIN_URL . 'admin/css/admin-style.css', array(), BERIYACK_PLUGIN_VERSION );

		// Charge le fichier JS personnalisé
		wp_enqueue_script( 'beriyack-plugin-admin-script', BERIYACK_PLUGIN_URL . 'admin/js/admin-script.js', array( 'jquery' ), BERIYACK_PLUGIN_VERSION, true );

		// Transmet les paramètres d'accès AJAX de manière sécurisée
		wp_localize_script( 'beriyack-plugin-admin-script', 'beriyack_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'beriyack_save_settings_nonce' ),
		) );
	}

	/**
	 * Affiche le contenu HTML de la page des réglages.
	 */
	public function display_settings_page() {
		// Détection de l'onglet actif en fonction de la page courante ou d'un paramètre d'URL
		$tab = 'dashboard';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $_GET['page'] === 'beriyack-plugin-seo' ) {
				$tab = 'seo';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} elseif ( $_GET['page'] === 'beriyack-plugin-security' ) {
				$tab = 'security';
			}
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['tab'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		}

		// Récupère les options stockées
		$options = get_option( 'beriyack_plugin_options', array() );
		$defaults = array(
			'seo_enable'              => '1',
			'seo_fb_app_id'           => '',
			'seo_twitter_site'        => '',
			'seo_fallback_img'        => '',
			'seo_add_sitemap_robots'  => '1',
			'seo_noindex_search_404'  => '1',
			'sec_revisions'           => '5',
			'sec_rm_generator'        => '1',
			'sec_hide_login'          => '1',
			'sec_dis_xmlrpc'          => '1',
			'sec_dis_rest_api'        => '0',
			'sec_rm_ver'              => '0',
			'sec_dis_emojis'          => '1',
		);
		$options = wp_parse_args( $options, $defaults );

		// Requête pour récupérer les 10 derniers articles/pages publiés pour l'aperçu social
		$recent_posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => 10,
			'post_status'    => 'publish',
		) );

		// Charge le template HTML de l'administration
		include BERIYACK_PLUGIN_PATH . 'admin/partials/admin-display.php';
	}

	/**
	 * Gère la sauvegarde des réglages via AJAX.
	 */
	public function ajax_save_settings() {
		// Vérification du jeton de sécurité (Nonce) et des droits utilisateur
		check_ajax_referer( 'beriyack_save_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Autorisation refusée.' ) );
		}

		// Analyse les données envoyées par le formulaire
		$form_data = array();
		if ( isset( $_POST['form_data'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			parse_str( wp_unslash( $_POST['form_data'] ), $form_data );
		}

		// Lecture des options actuelles
		$options = get_option( 'beriyack_plugin_options', array() );

		// Nettoyage et mise à jour sécurisée des valeurs
		$options['seo_enable']             = isset( $form_data['seo_enable'] ) ? '1' : '0';
		$options['seo_fb_app_id']          = isset( $form_data['seo_fb_app_id'] ) ? sanitize_text_field( $form_data['seo_fb_app_id'] ) : '';
		$options['seo_twitter_site']       = isset( $form_data['seo_twitter_site'] ) ? sanitize_text_field( $form_data['seo_twitter_site'] ) : '';
		$options['seo_fallback_img']       = isset( $form_data['seo_fallback_img'] ) ? esc_url_raw( $form_data['seo_fallback_img'] ) : '';
		$options['seo_add_sitemap_robots'] = isset( $form_data['seo_add_sitemap_robots'] ) ? '1' : '0';
		$options['seo_noindex_search_404'] = isset( $form_data['seo_noindex_search_404'] ) ? '1' : '0';

		// Traitement du nombre de révisions (accepte les valeurs négatives, nulles et positives)
		$options['sec_revisions']          = isset( $form_data['sec_revisions'] ) ? sanitize_text_field( $form_data['sec_revisions'] ) : '5';

		$options['sec_rm_generator']       = isset( $form_data['sec_rm_generator'] ) ? '1' : '0';
		$options['sec_hide_login']         = isset( $form_data['sec_hide_login'] ) ? '1' : '0';
		$options['sec_dis_xmlrpc']         = isset( $form_data['sec_dis_xmlrpc'] ) ? '1' : '0';
		$options['sec_dis_rest_api']       = isset( $form_data['sec_dis_rest_api'] ) ? '1' : '0';
		$options['sec_rm_ver']             = isset( $form_data['sec_rm_ver'] ) ? '1' : '0';
		$options['sec_dis_emojis']         = isset( $form_data['sec_dis_emojis'] ) ? '1' : '0';

		// Sauvegarde de l'option globale
		update_option( 'beriyack_plugin_options', $options );

		wp_send_json_success( array( 'message' => 'Réglages enregistrés avec succès !' ) );
	}

	/**
	 * AJAX : Récupère les informations d'un article pour mettre à jour la maquette d'aperçu social.
	 */
	public function ajax_get_post_preview() {
		check_ajax_referer( 'beriyack_save_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Autorisation refusée.' ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => 'ID d\'article invalide.' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( array( 'message' => 'Article introuvable.' ) );
		}

		// Récupère le titre nettoyé
		$title = get_the_title( $post_id );

		// Récupère l'extrait ou génère une portion de contenu
		$excerpt = '';
		if ( ! empty( $post->post_excerpt ) ) {
			$excerpt = wp_strip_all_tags( $post->post_excerpt );
		} else {
			$content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
			$excerpt = wp_html_excerpt( $content, 150, '...' );
		}

		// Récupère l'image à la une
		$image = '';
		if ( has_post_thumbnail( $post_id ) ) {
			$image = get_the_post_thumbnail_url( $post_id, 'large' );
		}

		wp_send_json_success( array(
			'title'   => $title,
			'excerpt' => $excerpt,
			'image'   => $image,
		) );
	}

	/**
	 * Ajoute un lien rapide "Réglages" dans la liste des extensions WordPress.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=beriyack-plugin' ) . '">' . __( 'Réglages', 'beriyack-plugin' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
}
