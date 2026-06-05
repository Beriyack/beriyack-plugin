<?php
/**
 * Gestion du SEO social en frontend (Facebook et Twitter)
 *
 * Injecte dynamiquement les balises Open Graph (Facebook) et Twitter Card dans le <head> public.
 *
 * @link       https://github.com/beriyack/beriyack-plugin
 * @since      1.0.0
 * @package    Beriyack_Plugin
 * @subpackage Beriyack_Plugin/includes
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Beriyack_Plugin_SEO {

	/**
	 * Initialise les hooks pour la partie publique (SEO).
	 */
	public function init_hooks() {
		add_action( 'wp_head', array( $this, 'insert_social_tags' ), 5 );
	}

	/**
	 * Injecte les balises Open Graph et Twitter Card dans la balise head du site.
	 */
	public function insert_social_tags() {
		// Ne pas exécuter dans l'admin, les flux RSS, les requêtes XML-RPC ou l'écran de connexion
		if ( is_admin() || is_feed() || defined( 'XMLRPC_REQUEST' ) || ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php' ) ) {
			return;
		}

		$options = get_option( 'beriyack_plugin_options', array() );
		$enabled = isset( $options['seo_enable'] ) ? $options['seo_enable'] : '1';

		if ( '1' !== $enabled ) {
			return;
		}

		// 1. Détermination de l'URL courante
		if ( is_singular() ) {
			$url = get_permalink();
		} else {
			global $wp;
			$url = home_url( add_query_arg( array(), $wp->request ) );
		}

		// 2. Détermination du Titre
		if ( is_front_page() || is_home() ) {
			$title = get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' );
		} elseif ( is_singular() ) {
			$title = get_the_title();
		} else {
			$title = wp_get_document_title();
		}

		// 3. Détermination de la Description
		$description = '';
		if ( is_singular() ) {
			$post = get_post();
			if ( $post && ! empty( $post->post_excerpt ) ) {
				$description = wp_strip_all_tags( $post->post_excerpt );
			} elseif ( $post && ! empty( $post->post_content ) ) {
				// Supprime les codes courts (shortcodes) et le HTML, puis tronque à 150 caractères
				$content     = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
				$description = wp_html_excerpt( $content, 150, '...' );
			} else {
				$description = get_bloginfo( 'description' );
			}
		} else {
			$description = get_bloginfo( 'description' );
		}

		// 4. Détermination de l'Image sociale
		$image = '';
		if ( is_singular() && has_post_thumbnail() ) {
			$image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		}

		// Si aucune image à la une, on utilise l'image par défaut (fallback) du plugin
		if ( empty( $image ) && ! empty( $options['seo_fallback_img'] ) ) {
			$image = $options['seo_fallback_img'];
		}

		// 5. Détermination du type (article pour un contenu individuel, site web pour le reste)
		$type = is_singular( 'post' ) ? 'article' : 'website';

		// 6. Récupération des autres réglages
		$site_name    = get_bloginfo( 'name' );
		$twitter_site = isset( $options['seo_twitter_site'] ) ? $options['seo_twitter_site'] : '';
		$fb_app_id    = isset( $options['seo_fb_app_id'] ) ? $options['seo_fb_app_id'] : '';

		// Sécurisation des valeurs pour l'affichage HTML
		$title       = esc_attr( wp_strip_all_tags( $title ) );
		$description = esc_attr( wp_strip_all_tags( $description ) );
		$url         = esc_url( $url );
		$image       = esc_url( $image );
		$site_name   = esc_attr( $site_name );

		// Rendu des balises Open Graph (Facebook, LinkedIn, etc.)
		echo "\n<!-- Réglages de partage social par Beriyack Plugin -->\n";
		echo '<meta property="og:site_name" content="' . $site_name . '" />' . "\n";
		echo '<meta property="og:type" content="' . $type . '" />' . "\n";
		echo '<meta property="og:title" content="' . $title . '" />' . "\n";
		echo '<meta property="og:description" content="' . $description . '" />' . "\n";
		echo '<meta property="og:url" content="' . $url . '" />' . "\n";
		if ( ! empty( $image ) ) {
			echo '<meta property="og:image" content="' . $image . '" />' . "\n";
		}
		if ( ! empty( $fb_app_id ) ) {
			echo '<meta property="fb:app_id" content="' . esc_attr( $fb_app_id ) . '" />' . "\n";
		}

		// Rendu des balises Twitter Card
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . $title . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . $description . '" />' . "\n";
		if ( ! empty( $image ) ) {
			echo '<meta name="twitter:image" content="' . $image . '" />' . "\n";
		}
		if ( ! empty( $twitter_site ) ) {
			// On s'assure de précéder le compte Twitter d'un @ s'il manque
			$twitter_site_formatted = ( strpos( $twitter_site, '@' ) === 0 ) ? $twitter_site : '@' . $twitter_site;
			echo '<meta name="twitter:site" content="' . esc_attr( $twitter_site_formatted ) . '" />' . "\n";
			echo '<meta name="twitter:creator" content="' . esc_attr( $twitter_site_formatted ) . '" />' . "\n";
		}
		echo "<!-- / Réglages de partage social par Beriyack Plugin -->\n\n";
	}
}
