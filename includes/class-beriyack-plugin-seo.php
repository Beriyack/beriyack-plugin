<?php
/**
 * Gestion du SEO social en frontend (Facebook et Twitter).
 *
 * Injecte les balises Open Graph et Twitter Cards dynamiquement selon le type de page,
 * et gère les optimisations de robots.txt et d'indexation (noindex).
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
	 * Initialise les hooks publics du SEO.
	 */
	public function init_hooks() {
		add_action( 'wp_head', array( $this, 'insert_social_tags' ), 5 );
		add_filter( 'robots_txt', array( $this, 'robots_txt_tweaks' ) );
		add_filter( 'wp_robots', array( $this, 'robots_tag_tweaks' ) );
	}

	/**
	 * Injecte le Sitemap natif dans le fichier robots.txt.
	 */
	public function robots_txt_tweaks( $output ) {
		$options = get_option( 'beriyack_plugin_options', array() );
		$enabled = isset( $options['seo_add_sitemap_robots'] ) ? $options['seo_add_sitemap_robots'] : '1';

		if ( '1' === $enabled && function_exists( 'get_sitemap_url' ) ) {
			$output .= 'Sitemap: ' . esc_url( get_sitemap_url( 'index' ) ) . "\n";
		}

		return $output;
	}

	/**
	 * Désindexe (noindex, nofollow) les pages de recherche et d'erreur 404.
	 */
	public function robots_tag_tweaks( $robots ) {
		$options = get_option( 'beriyack_plugin_options', array() );
		$enabled = isset( $options['seo_noindex_search_404'] ) ? $options['seo_noindex_search_404'] : '1';

		if ( '1' === $enabled ) {
			if ( is_search() || is_404() ) {
				$robots['noindex']  = true;
				$robots['nofollow'] = true;
			}
		}

		return $robots;
	}

	/**
	 * Injecte les balises Open Graph et Twitter Card dans l'en-tête HTML.
	 */
	public function insert_social_tags() {
		// Ne pas exécuter dans l'administration, les flux RSS ou l'écran de connexion
		if ( is_admin() || is_feed() || ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php' ) ) {
			return;
		}

		$options = get_option( 'beriyack_plugin_options', array() );
		$enabled = isset( $options['seo_enable'] ) ? $options['seo_enable'] : '1';

		if ( '1' !== $enabled ) {
			return;
		}

		// Initialisation des variables par défaut
		$title            = wp_get_document_title();
		$site_name        = get_bloginfo( 'name' );
		$site_description = get_bloginfo( 'description', 'display' );
		$description      = $site_description;
		$url              = home_url( '/' );
		$image            = '';
		$image_alt        = '';
		$author_name      = $site_name;
		$type             = 'website';

		// 1. Détermination contextuelle des métadonnées (Titre, URL, Description, Image, Auteur)
		if ( is_front_page() ) {
			$title = get_bloginfo( 'name' );
			if ( ! empty( $site_description ) ) {
				$title .= ' - ' . $site_description;
			}
			if ( empty( $description ) ) {
				$description = sprintf( __( 'Bienvenue sur %s. Découvrez nos derniers contenus et actualités.', 'beriyack-plugin' ), $site_name );
			}
		} elseif ( is_home() ) {
			$title       = wp_get_document_title();
			$description = sprintf( __( 'Retrouvez les derniers articles et actualités de %s.', 'beriyack-plugin' ), $site_name );
			$url         = get_post_type_archive_link( 'post' );
		} elseif ( is_singular() ) {
			$post        = get_queried_object();
			$type        = 'article';
			$url         = get_permalink( $post );
			$author_name = get_the_author_meta( 'display_name', $post->post_author );

			// Récupère l'extrait ou génère une description à partir du contenu
			if ( ! empty( $post->post_excerpt ) ) {
				$description = wp_strip_all_tags( $post->post_excerpt );
			} elseif ( ! empty( $post->post_content ) ) {
				$content     = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
				$description = wp_html_excerpt( $content, 150, '...' );
			}

			// Récupère l'image à la une et son texte alternatif (alt)
			if ( has_post_thumbnail( $post->ID ) ) {
				$image     = get_the_post_thumbnail_url( $post->ID, 'large' );
				$thumbnail_id = get_post_thumbnail_id( $post->ID );
				$image_alt = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term        = get_queried_object();
			$url         = get_term_link( $term );
			$description = wp_strip_all_tags( term_description( $term ) );
			if ( empty( $description ) ) {
				$description = sprintf( __( 'Retrouvez tous les contenus liés à %s.', 'beriyack-plugin' ), $term->name );
			}
			// Ajoute un lien vers le sitemap des taxonomies pour un meilleur SEO.
			if ( function_exists( 'wp_get_sitemap_providers' ) && function_exists( 'get_sitemap_url' ) ) {
				$sitemap_url = get_sitemap_url( $term->taxonomy );
				if ( $sitemap_url ) {
					echo '<link rel="sitemap" type="application/xml" title="' . esc_attr( sprintf( __( 'Sitemap %s', 'beriyack-plugin' ), $term->taxonomy ) ) . '" href="' . esc_url( $sitemap_url ) . '" />' . "\n";
				}
			}
		} elseif ( is_author() ) {
			$author      = get_queried_object();
			$description = get_the_author_meta( 'description', $author->ID );
			$url         = get_author_posts_url( $author->ID );
		} elseif ( is_search() ) {
			$search_query = get_search_query();
			$description  = sprintf( __( 'Résultats de la recherche pour "%1$s" sur le blog de %2$s.', 'beriyack-plugin' ), $search_query, $site_name );
			$url          = get_search_link( $search_query );
		} elseif ( is_404() ) {
			$description = __( 'La page que vous recherchez semble introuvable.', 'beriyack-plugin' );
			global $wp;
			$url         = home_url( add_query_arg( array(), $wp->request ) );
		}

		// Utilise l'image par défaut (fallback) si aucune image spécifique n'a été trouvée
		if ( empty( $image ) && ! empty( $options['seo_fallback_img'] ) ) {
			$image = $options['seo_fallback_img'];
		}

		// Nettoyage et sécurisation des en-têtes de métadonnées
		$title       = esc_attr( wp_strip_all_tags( $title ) );
		$description = trim( preg_replace( '/\s+/', ' ', $description ) );
		$description = esc_attr( wp_strip_all_tags( $description ) );
		$url         = esc_url( $url );
		$image       = esc_url( $image );
		$site_name   = esc_attr( $site_name );
		$author_name = esc_attr( $author_name );

		$twitter_site = isset( $options['seo_twitter_site'] ) ? $options['seo_twitter_site'] : '';
		$fb_app_id    = isset( $options['seo_fb_app_id'] ) ? $options['seo_fb_app_id'] : '';

		// Rendu HTML
		echo "\n<!-- Balises SEO Réseaux Sociaux - Beriyack Plugin -->\n";
		echo '<meta name="author" content="' . $author_name . '" />' . "\n";
		echo '<meta property="og:site_name" content="' . $site_name . '" />' . "\n";
		echo '<meta property="og:type" content="' . $type . '" />' . "\n";
		echo '<meta property="og:title" content="' . $title . '" />' . "\n";
		echo '<meta property="og:url" content="' . $url . '" />' . "\n";
		echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '" />' . "\n";

		if ( ! empty( $description ) ) {
			echo '<meta name="description" content="' . $description . '" />' . "\n";
			echo '<meta property="og:description" content="' . $description . '" />' . "\n";
		}
		if ( ! empty( $image ) ) {
			echo '<meta property="og:image" content="' . $image . '" />' . "\n";
		}
		if ( ! empty( $fb_app_id ) ) {
			echo '<meta property="fb:app_id" content="' . esc_attr( $fb_app_id ) . '" />' . "\n";
		}

		// Rendu des balises Twitter Card
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . $title . '" />' . "\n";
		
		if ( ! empty( $description ) ) {
			echo '<meta name="twitter:description" content="' . $description . '" />' . "\n";
		}
		if ( ! empty( $image ) ) {
			echo '<meta name="twitter:image" content="' . $image . '" />' . "\n";
		}
		if ( ! empty( $image_alt ) ) {
			echo '<meta name="twitter:image:alt" content="' . esc_attr( $image_alt ) . '" />' . "\n";
		}
		if ( ! empty( $twitter_site ) ) {
			$twitter_site_formatted = ( strpos( $twitter_site, '@' ) === 0 ) ? $twitter_site : '@' . $twitter_site;
			echo '<meta name="twitter:site" content="' . esc_attr( $twitter_site_formatted ) . '" />' . "\n";
			echo '<meta name="twitter:creator" content="' . esc_attr( $twitter_site_formatted ) . '" />' . "\n";
		}
		echo "<!-- / Balises SEO Réseaux Sociaux - Beriyack Plugin -->\n\n";
	}
}
