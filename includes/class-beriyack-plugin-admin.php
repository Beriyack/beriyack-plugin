<?php
/**
 * The admin-facing functionality of the plugin.
 *
 * Defines the admin menu, registers settings, enqueues styles/scripts, and saves settings.
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
	 * Initialize hooks for the admin area.
	 */
	public function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
		add_action( 'wp_ajax_beriyack_save_settings', array( $this, 'ajax_save_settings' ) );
	}

	/**
	 * Set default options on plugin activation.
	 */
	public static function set_default_options() {
		if ( false === get_option( 'beriyack_plugin_options' ) ) {
			$defaults = array(
				'seo_enable'        => '1',
				'seo_fb_app_id'     => '',
				'seo_twitter_site'  => '',
				'seo_fallback_img'  => '',
				'sec_revisions'     => '5',
				'sec_rm_generator'  => '1',
				'sec_hide_login'    => '1',
				'sec_dis_xmlrpc'    => '1',
				'sec_dis_rest_api'  => '0',
				'sec_rm_ver'        => '0',
				'sec_dis_emojis'    => '1',
			);
			update_option( 'beriyack_plugin_options', $defaults );
		}
	}

	/**
	 * Register the admin menu and submenus.
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
	 * Enqueue admin stylesheet and javascript.
	 */
	public function enqueue_styles_and_scripts( $hook ) {
		// Only enqueue on our plugin pages
		$pages = array(
			'toplevel_page_beriyack-plugin',
			'beriyack-plugin_page_beriyack-plugin-seo',
			'beriyack-plugin_page_beriyack-plugin-security',
		);
		if ( ! in_array( $hook, $pages, true ) ) {
			return;
		}

		// Enqueue WordPress media libraries
		wp_enqueue_media();

		// Enqueue custom Google Fonts for premium design
		wp_enqueue_style( 'beriyack-google-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap', array(), null );

		// Enqueue admin styles
		wp_enqueue_style( 'beriyack-admin-style', BERIYACK_PLUGIN_URL . 'admin/css/admin-style.css', array(), BERIYACK_PLUGIN_VERSION );

		// Enqueue admin script
		wp_enqueue_script( 'beriyack-admin-script', BERIYACK_PLUGIN_URL . 'admin/js/admin-script.js', array( 'jquery' ), BERIYACK_PLUGIN_VERSION, true );

		// Localize AJAX parameters
		wp_localize_script( 'beriyack-admin-script', 'beriyack_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'beriyack_save_settings_nonce' ),
		) );
	}

	/**
	 * Render the admin settings page.
	 */
	public function display_settings_page() {
		// Detect tab from page slug or query parameter
		$tab = 'dashboard';
		if ( isset( $_GET['page'] ) ) {
			if ( $_GET['page'] === 'beriyack-plugin-seo' ) {
				$tab = 'seo';
			} elseif ( $_GET['page'] === 'beriyack-plugin-security' ) {
				$tab = 'security';
			}
		}
		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( $_GET['tab'] );
		}

		// Get current options
		$options = get_option( 'beriyack_plugin_options', array() );
		$defaults = array(
			'seo_enable'        => '1',
			'seo_fb_app_id'     => '',
			'seo_twitter_site'  => '',
			'seo_fallback_img'  => '',
			'sec_revisions'     => '5',
			'sec_rm_generator'  => '1',
			'sec_hide_login'    => '1',
			'sec_dis_xmlrpc'    => '1',
			'sec_dis_rest_api'  => '0',
			'sec_rm_ver'        => '0',
			'sec_dis_emojis'    => '1',
		);
		$options = wp_parse_args( $options, $defaults );

		// Include template
		include BERIYACK_PLUGIN_PATH . 'admin/partials/admin-display.php';
	}

	/**
	 * Handle settings saving via AJAX.
	 */
	public function ajax_save_settings() {
		// Verify Nonce and capability
		check_ajax_referer( 'beriyack_save_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Autorisation refusée.' ) );
		}

		// Retrieve POST parameters
		$form_data = array();
		if ( isset( $_POST['form_data'] ) ) {
			parse_str( $_POST['form_data'], $form_data );
		}

		// Read existing options to make sure we don't wipe out other properties if they exist
		$options = get_option( 'beriyack_plugin_options', array() );

		// Update fields securely
		$options['seo_enable']       = isset( $form_data['seo_enable'] ) ? '1' : '0';
		$options['seo_fb_app_id']    = isset( $form_data['seo_fb_app_id'] ) ? sanitize_text_field( $form_data['seo_fb_app_id'] ) : '';
		$options['seo_twitter_site'] = isset( $form_data['seo_twitter_site'] ) ? sanitize_text_field( $form_data['seo_twitter_site'] ) : '';
		$options['seo_fallback_img'] = isset( $form_data['seo_fallback_img'] ) ? esc_url_raw( $form_data['seo_fallback_img'] ) : '';

		$options['sec_revisions']    = isset( $form_data['sec_revisions'] ) ? sanitize_text_field( $form_data['sec_revisions'] ) : '5';
		$options['sec_rm_generator'] = isset( $form_data['sec_rm_generator'] ) ? '1' : '0';
		$options['sec_hide_login']   = isset( $form_data['sec_hide_login'] ) ? '1' : '0';
		$options['sec_dis_xmlrpc']   = isset( $form_data['sec_dis_xmlrpc'] ) ? '1' : '0';
		$options['sec_dis_rest_api'] = isset( $form_data['sec_dis_rest_api'] ) ? '1' : '0';
		$options['sec_rm_ver']       = isset( $form_data['sec_rm_ver'] ) ? '1' : '0';
		$options['sec_dis_emojis']   = isset( $form_data['sec_dis_emojis'] ) ? '1' : '0';

		// Save options
		update_option( 'beriyack_plugin_options', $options );

		wp_send_json_success( array( 'message' => 'Réglages enregistrés avec succès !' ) );
	}
}
