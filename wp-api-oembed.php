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
 * @package WP_API_oEmbed
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
include( dirname( __FILE__ ) . '/classes/endpoint.php' );
include( dirname( __FILE__ ) . '/classes/frontend.php' );
include( dirname( __FILE__ ) . '/classes/plugin.php' );

/**
 * Init our plugin.
 */
function oembed_api_init() {
	$oembed_api = WP_API_oEmbed_Plugin::get_instance();
	$oembed_api->add_hooks();
}

add_action( 'plugins_loaded', 'oembed_api_init' );

register_activation_hook( __FILE__, array( WP_API_oEmbed_Plugin::get_instance(), 'activate_plugin' ) );
register_deactivation_hook( __FILE__, array( WP_API_oEmbed_Plugin::get_instance(), 'deactivate_plugin' ) );

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
