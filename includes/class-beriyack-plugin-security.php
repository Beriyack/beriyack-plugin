<?php
/**
 * The security and optimization functionality of the plugin.
 *
 * Implements various WordPress filters and actions to secure and speed up the website.
 *
 * @link       https://github.com/beriyack/beriyack-plugin
 * @since      1.0.0
 * @package    Beriyack_Plugin
 * @subpackage Beriyack_Plugin/includes
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Beriyack_Plugin_Security {

	/**
	 * Initialize the hooks.
	 */
	public function init_hooks() {
		$options = get_option( 'beriyack_plugin_options', array() );

		// 1. Revision limit
		add_filter( 'wp_revisions_to_keep', array( $this, 'limit_revisions_count' ), 10, 2 );

		// 2. Remove generator tag
		if ( isset( $options['sec_rm_generator'] ) && '1' === $options['sec_rm_generator'] ) {
			add_action( 'init', array( $this, 'remove_wp_generator' ) );
		}

		// 3. Hide login errors
		if ( isset( $options['sec_hide_login'] ) && '1' === $options['sec_hide_login'] ) {
			add_filter( 'login_errors', array( $this, 'hide_login_errors' ) );
		}

		// 4. Disable XML-RPC
		if ( isset( $options['sec_dis_xmlrpc'] ) && '1' === $options['sec_dis_xmlrpc'] ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'wp_headers', array( $this, 'remove_xmlrpc_pingback_header' ) );
		}

		// 5. Restrict REST API to logged in users
		if ( isset( $options['sec_dis_rest_api'] ) && '1' === $options['sec_dis_rest_api'] ) {
			add_filter( 'rest_authentication_errors', array( $this, 'restrict_rest_api_access' ) );
		}

		// 6. Remove version from styles and scripts
		if ( isset( $options['sec_rm_ver'] ) && '1' === $options['sec_rm_ver'] ) {
			add_filter( 'style_loader_src', array( $this, 'remove_asset_version' ), 9999 );
			add_filter( 'script_loader_src', array( $this, 'remove_asset_version' ), 9999 );
		}

		// 7. Disable emojis
		if ( isset( $options['sec_dis_emojis'] ) && '1' === $options['sec_dis_emojis'] ) {
			add_action( 'init', array( $this, 'disable_emojis' ) );
		}
	}

	/**
	 * Limit revisions count dynamically.
	 */
	public function limit_revisions_count( $num, $post ) {
		$options = get_option( 'beriyack_plugin_options', array() );
		$limit   = isset( $options['sec_revisions'] ) ? $options['sec_revisions'] : '5';

		if ( 'disabled' === $limit ) {
			return $num;
		}

		return intval( $limit );
	}

	/**
	 * Remove generator meta tags.
	 */
	public function remove_wp_generator() {
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
	}

	/**
	 * Hide login error details.
	 */
	public function hide_login_errors() {
		return __( 'Identifiants de connexion incorrects.', 'beriyack-plugin' );
	}

	/**
	 * Remove X-Pingback header.
	 */
	public function remove_xmlrpc_pingback_header( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	/**
	 * Restrict REST API to logged in users.
	 */
	public function restrict_rest_api_access( $errors ) {
		if ( is_wp_error( $errors ) ) {
			return $errors;
		}
		if ( is_user_logged_in() ) {
			return $errors;
		}
		return new WP_Error(
			'rest_forbidden',
			__( 'Accès à l\'API REST restreint aux utilisateurs authentifiés.', 'beriyack-plugin' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * Strip query strings (ver) from static scripts/styles.
	 */
	public function remove_asset_version( $src ) {
		if ( strpos( $src, 'ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;
	}

	/**
	 * Disable emojis in frontend and admin.
	 */
	public function disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
		add_filter( 'wp_resource_hints', array( $this, 'disable_emojis_dns_prefetch' ), 10, 2 );
	}

	/**
	 * Disable emojis in TinyMCE editor.
	 */
	public function disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		}
		return array();
	}

	/**
	 * Disable emoji DNS prefetch hints.
	 */
	public function disable_emojis_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
			$urls          = array_diff( $urls, array( $emoji_svg_url ) );
		}
		return $urls;
	}
}
