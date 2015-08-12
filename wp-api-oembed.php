<?php
/**
 * Plugin Name: oEmbed API
 * Plugin URI:  https://github.com/swissspidy/oEmbed-API
 * Description: An oEmbed provider for WordPress using the WP-API plugin.
 * Version:     0.3.0
 * Author:      Pascal Birchler
 * Author URI:  https://spinpress.com
 * License:     GPLv2+
 * Text Domain: oembed-api
 * Domain Path: /languages
 *
 * @package WP_oEmbed
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'WPINC' ) or die;

// Pull in the plugin classes and initialize.
include( dirname( __FILE__ ) . '/classes/class-wp-rest-oembed-controller.php' );
include( dirname( __FILE__ ) . '/classes/class-oembed-endpoint.php' );
include( dirname( __FILE__ ) . '/classes/class-frontend.php' );
include( dirname( __FILE__ ) . '/classes/class-plugin.php' );

/**
 * Init our plugin.
 */
function oembed_api_init() {
	$oembed_api = new WP_oEmbed_Plugin();
	$oembed_api->add_hooks();
}

add_action( 'plugins_loaded', 'oembed_api_init' );

register_activation_hook( __FILE__, array( 'WP_oEmbed_Plugin', 'activate_plugin' ) );
register_deactivation_hook( __FILE__, array( 'WP_oEmbed_Plugin', 'deactivate_plugin' ) );

/**
 * Get the URL to embed a specific post, for example in an iframe.
 *
 * @param int|WP_Post $post Post ID or object. Defaults to the current post.
 *
 * @return bool|string URL on success, false otherwise.
 */
function get_post_embed_url( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}

	$embed_url = add_query_arg( array( 'embed' => 'true' ), get_permalink( $post ) );

	if ( get_option( 'permalink_structure' ) ) {
		$embed_url = trailingslashit( get_permalink( $post ) ) . user_trailingslashit( 'embed' );
	}

	return $embed_url;
}

/**
 * Get the oEmbed endpoint URL for a given permalink.
 *
 * Pass an empty string as the first argument
 * to get the endpoint base URL.
 *
 * @param string $permalink The permalink used for the `url` query arg.
 * @param string $format    The requested response format.
 *
 * @return string
 */
function get_oembed_endpoint_url( $permalink = '', $format = 'json' ) {
	$url = add_query_arg( array( 'oembed' => 'true' ), home_url() );

	if ( function_exists( 'rest_url' ) ) {
		$url = rest_url( 'wp/v2/oembed' );
	}

	if ( '' === $permalink ) {
		return esc_url( $url );
	}

	$url = add_query_arg( array(
		'url'    => $permalink,
		'format' => $format,
	), $url );

	/**
	 * Filter the oEmbed endpoint URL.
	 *
	 * @param string $url       The URL to the oEmbed endpoint.
	 * @param string $permalink The permalink used for the `url` query arg.
	 * @param string $format    The requested response format.
	 *
	 * @return string
	 */
	$url = apply_filters( 'rest_oembed_endpoint_url', $url, $permalink, $format );

	return esc_url( $url );
}

/**
 * Get the embed code fpr a specific post.
 *
 * @param int|WP_Post $post   Post ID or object. Defaults to the current post.
 * @param int         $width  The width for the response.
 * @param int         $height The height for the response.
 *
 * @return bool|string Embed code on success, false otherwise.
 */
function get_post_embed_html( $post = null, $width, $height ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}

	$embed_url = get_post_embed_url( $post );

	$output = sprintf(
		'<iframe sandbox="allow-scripts" security="restricted" src="%1$s" width="%2$d" height="%3$d" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>',
		esc_url( $embed_url ),
		$width,
		$height
	);

	/**
	 * Filters the oEmbed HTML output.
	 *
	 * @param string  $output The default HTML.
	 * @param WP_Post $post   Current post object.
	 * @param int     $width  Width of the response.
	 * @param int     $height Height of the response.
	 */
	$output = apply_filters( 'rest_oembed_html', $output, $post, $width, $height );

	return $output;
}
