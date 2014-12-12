<?php
/**
 * Plugin Name:       oEmbed API
 * Plugin URI:        https://github.com/swissspidy/oembed-api
 * Description:       Adds oEmbed support to WordPress using the WP-API
 * Version:           0.0.1
 * Author:            Pascal Birchler
 * Author URI:        http://pascalbirchler.com/
 * Text Domain:       oembed-api
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) or die();

/**
 * Register hooks that are fired when the plugin is activated or deactivated.
 */

/**
 * Plugin Activation
 */
function oembed_api_acivation() {
	if ( ! function_exists( 'get_json_url' ) ) {
		exit( __( 'The oEmbed API plugin requires WP-API to be installed!', 'oembed-api' ) );
	}
}

register_activation_hook( __FILE__, 'oembed_api_acivation' );

/**
 * Add our oEmbed API endpoint.
 *
 * @param WP_JSON_Server $server
 */
function oembed_api_init( WP_JSON_Server $server ) {
	include dirname( __FILE__ ) . '/includes/class-oembed-api.php';
	new OEmbed_API( $server );
}

add_action( 'wp_json_server_before_serve', 'oembed_api_init' );


/**
 * Add our own site as an oEmbed provider for demo purposes.
 */
function oembed_api_add_provider() {
	$format = apply_filters( 'oembed_api_url_format', get_home_url() . '/*' );
	wp_oembed_add_provider( $format, get_json_url( null, 'oembed' ) );
}

add_action( 'plugins_loaded', 'oembed_api_add_provider' );

/**
 * Add oEmbed discovery links to single talent & product pages
 */
function oembed_api_discovery_links() {
	if ( is_singular() ) {
		echo '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_json_url( null, 'oembed/?url=' . get_permalink() ) ) . '" />' . "\n";
	}
}

add_action( 'wp_head', 'oembed_api_discovery_links' );
