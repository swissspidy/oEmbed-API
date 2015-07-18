<?php
/**
 * User-facing components.
 *
 * @package WP_API_oEmbed
 */

defined( 'WPINC' ) or die;

/**
 * Class WP_API_oEmbed_Frontend
 */
class WP_API_oEmbed_Frontend {
	/**
	 * Output the embeddable HTML.
	 *
	 * @todo Is there a better / faster way?
	 */
	public function template_redirect() {
		global $wp_query, $post;

		if ( isset( $wp_query->query_vars['embed'] ) ) {
			/**
			 * This gets executed when someone embeds a post.
			 *
			 * @param WP_Post $post The current post object.
			 */
			do_action( 'rest_oembed_output', $post );
			exit;
		}
	}

	/**
	 * Add oEmbed discovery links in the website <head>.
	 */
	public function add_oembed_discovery_links() {
		if ( ! function_exists( 'get_rest_url' ) ) {
			return;
		}

		$output = '';

		if ( is_singular() ) {
			$output .= '<link rel="alternate" type="application/json+oembed" href="' . esc_url( rest_url( 'wp/v2/oembed?url=' . get_permalink() ) ) . '" />' . "\n";
		}

		$output = apply_filters( 'rest_oembed_discovery_links', $output );

		echo $output;
	}

	/**
	 * Output the HTML that gets embedded.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function rest_oembed_output( $post ) {
		if ( is_a( $post, 'WP_Post' ) ) {
			echo esc_html( $post->post_title );
		}
	}
}
