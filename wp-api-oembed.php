<?php
/**
 * Plugin Name: oEmbed API
 * Plugin URI:  https://github.com/swissspidy/oEmbed-API
 * Description: An oEmbed provider for WordPress using the WP-API plugin.
 * Version:     0.2.0
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

// Check if the REST API is available first.
if ( ! version_compare( get_option( 'rest_api_plugin_version' ), '2.0-beta3', '>=' ) ) {
	add_action( 'admin_notices', 'oembed_api_requirements_notice' );
	unset( $_GET['activate'] );

	return;
}

/**
 * Print the missing REST API notice and disable the plugin.
 */
function oembed_api_requirements_notice() {
	deactivate_plugins( plugin_basename( __FILE__ ) );
	?>
	<div class="error">
		<p><?php printf( 'The oEmbed API plugin requires version 2.0 of the REST API to be installed. Please update or install the REST API plugin.' ); ?></p>
	</div>
	<?php
}

// Pull in the plugin classes and initialize.
include( dirname( __FILE__ ) . '/lib/wp-stack-plugin.php' );
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
