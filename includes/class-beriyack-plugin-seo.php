<?php
/**
 * The public-facing SEO functionality of the plugin.
 *
 * Injects Open Graph (Facebook) and Twitter Card tags in wp_head dynamically.
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
	 * Initialize the hooks for the public-facing SEO.
	 */
	public function init_hooks() {
		add_action( 'wp_head', array( $this, 'insert_social_tags' ), 5 );
	}

	/**
	 * Insert Open Graph and Twitter Card tags in the site head.
	 */
	public function insert_social_tags() {
		// Do not run on admin, feed, xmlrpc or login pages
		if ( is_admin() || is_feed() || defined( 'XMLRPC_REQUEST' ) || ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php' ) ) {
			return;
		}

		$options = get_option( 'beriyack_plugin_options', array() );
		$enabled = isset( $options['seo_enable'] ) ? $options['seo_enable'] : '1';

		if ( '1' !== $enabled ) {
			return;
		}

		// 1. Determine URL
		if ( is_singular() ) {
			$url = get_permalink();
		} else {
			global $wp;
			$url = home_url( add_query_arg( array(), $wp->request ) );
		}

		// 2. Determine Title
		if ( is_front_page() || is_home() ) {
			$title = get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' );
		} elseif ( is_singular() ) {
			$title = get_the_title();
		} else {
			$title = wp_get_document_title();
		}

		// 3. Determine Description
		$description = '';
		if ( is_singular() ) {
			$post = get_post();
			if ( $post && ! empty( $post->post_excerpt ) ) {
				$description = wp_strip_all_tags( $post->post_excerpt );
			} elseif ( $post && ! empty( $post->post_content ) ) {
				// Strip shortcodes and HTML, then grab an excerpt
				$content     = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
				$description = wp_html_excerpt( $content, 150, '...' );
			} else {
				$description = get_bloginfo( 'description' );
			}
		} else {
			$description = get_bloginfo( 'description' );
		}

		// 4. Determine Image
		$image = '';
		if ( is_singular() && has_post_thumbnail() ) {
			$image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
		}

		// Fallback to option if empty
		if ( empty( $image ) && ! empty( $options['seo_fallback_img'] ) ) {
			$image = $options['seo_fallback_img'];
		}

		// 5. Determine Type
		$type = is_singular( 'post' ) ? 'article' : 'website';

		// 6. Gather other metadata
		$site_name    = get_bloginfo( 'name' );
		$twitter_site = isset( $options['seo_twitter_site'] ) ? $options['seo_twitter_site'] : '';
		$fb_app_id    = isset( $options['seo_fb_app_id'] ) ? $options['seo_fb_app_id'] : '';

		// Clean values for tag output
		$title       = esc_attr( wp_strip_all_tags( $title ) );
		$description = esc_attr( wp_strip_all_tags( $description ) );
		$url         = esc_url( $url );
		$image       = esc_url( $image );
		$site_name   = esc_attr( $site_name );

		// Output Open Graph tags
		echo "\n<!-- Beriyack Plugin SEO Social Tags -->\n";
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

		// Output Twitter Card tags
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . $title . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . $description . '" />' . "\n";
		if ( ! empty( $image ) ) {
			echo '<meta name="twitter:image" content="' . $image . '" />' . "\n";
		}
		if ( ! empty( $twitter_site ) ) {
			// Prepend @ if it's missing
			$twitter_site_formatted = ( strpos( $twitter_site, '@' ) === 0 ) ? $twitter_site : '@' . $twitter_site;
			echo '<meta name="twitter:site" content="' . esc_attr( $twitter_site_formatted ) . '" />' . "\n";
			echo '<meta name="twitter:creator" content="' . esc_attr( $twitter_site_formatted ) . '" />' . "\n";
		}
		echo "<!-- / Beriyack Plugin SEO Social Tags -->\n\n";
	}
}
