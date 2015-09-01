<?php
/**
 * Plugin Name: oEmbed API
 * Plugin URI:  https://github.com/swissspidy/oEmbed-API
 * Description: Allow others to easily embed your blog posts on their sites using oEmbed.
 * Version:     0.4.0
 * Author:      Pascal Birchler
 * Author URI:  https://pascalbirchler.com
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
include( dirname( __FILE__ ) . '/classes/class-wp-legacy-oembed-controller.php' );
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

/**
 * Add our rewrite endpoint on plugin activation.
 */
function oembed_api_activate_plugin() {
	$oembed_api = new WP_oEmbed_Plugin();
	$oembed_api->add_rewrite_endpoint();
	flush_rewrite_rules( false );
}

/**
 * Flush rewrite rules on plugin deactivation.
 */
function oembed_api_deactivate_plugin() {
	flush_rewrite_rules( false );
}


register_activation_hook( __FILE__, 'oembed_api_activate_plugin' );
register_deactivation_hook( __FILE__, 'oembed_api_deactivate_plugin' );

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
 * @param string      $permalink The permalink used for the `url` query arg.
 * @param string|bool $format    The requested response format.
 *                               Provide false to get the default format.
 *
 * @return string
 */
function get_oembed_endpoint_url( $permalink = '', $format = false ) {
	$url = '';

	if ( function_exists( 'rest_url' ) ) {
		$url = rest_url( 'wp/v2/oembed' );
	}

	if ( '' === $permalink ) {
		return $url;
	}

	/** This filter is defined in classes/class-plugin.php */
	$default_format = apply_filters( 'rest_oembed_default_format', 'json' );

	if ( $format === $default_format ) {
		$format = false;
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

	return $url;
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

/**
 * Get the oEmbed data for a given post.
 *
 * @param WP_Post|int $post  Post object or ID.
 * @param int         $width The requested width.
 *
 * @return mixed|void
 */
function get_oembed_response_data( $post, $width ) {
	/**
	 * Current post object.
	 *
	 * @var WP_Post $post
	 */
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}

	/**
	 * User object for the post author.
	 *
	 * @var WP_User $author
	 */
	$author = get_userdata( $post->post_author );

	// If a post doesn't have an author, fall back to the site's name.
	$author_name = get_bloginfo( 'name' );
	$author_url  = get_home_url();

	if ( $author ) {
		$author_name = $author->display_name;
		$author_url  = get_author_posts_url( $author->ID, $author->user_nicename );
	}

	/**
	 * Filter the allowed minimum width for the oEmbed response.
	 *
	 * @param int $width The minimum width. Defaults to 200.
	 */
	$minwidth = apply_filters( 'rest_oembed_minwidth', 200 );

	/**
	 * Filter the allowed maximum width for the oEmbed response.
	 *
	 * @param int $width The maximum width. Defaults to 600.
	 */
	$maxwidth = apply_filters( 'rest_oembed_maxwidth', 600 );

	if ( $width < $minwidth ) {
		$width = $minwidth;
	} else if ( $width > $maxwidth ) {
		$width = $maxwidth;
	}

	$height = ceil( $width / 16 * 9 );

	/**
	 * Filters the oEmbed response data.
	 *
	 * @param array   $data The response data.
	 * @param WP_Post $post The post object.
	 */
	$data = apply_filters( 'rest_oembed_response_data', array(
		'version'       => '1.0',
		'provider_name' => get_bloginfo( 'name' ),
		'provider_url'  => get_home_url(),
		'author_name'   => $author_name,
		'author_url'    => $author_url,
		'title'         => $post->post_title,
		'type'          => 'rich',
		'width'         => $width,
		'height'        => $height,
		'html'          => get_post_embed_html( $post, $width, $height ),
	), $post );

	return $data;
}
