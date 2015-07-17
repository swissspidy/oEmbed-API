<?php
/**
 * Plugin Name: oEmbed API
 * Plugin URI:  https://github.com/swissspidy/oEmbed-API
 * Description: An oEmbed provider for WordPress using the WP-API plugin.
 * Version:     0.1.0
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

include( dirname( __FILE__ ) . '/lib/requirements-check.php' );

$wp_api_oembed_requirements_check = new WP_API_oEmbed_Requirements_Check( array(
	'title' => 'oEmbed API',
	'php'   => '5.3',
	'wp'    => '4.0',
	'file'  => __FILE__,
));

if ( $wp_api_oembed_requirements_check->passes() ) {
	// Pull in the plugin classes and initialize.
	include( dirname( __FILE__ ) . '/lib/wp-stack-plugin.php' );
	include( dirname( __FILE__ ) . '/classes/plugin.php' );
	WP_API_oEmbed_Plugin::start( __FILE__ );

	register_activation_hook( __FILE__, array( WP_API_oEmbed_Plugin::get_instance(), 'activate_plugin' ) );
	register_deactivation_hook( __FILE__, array( WP_API_oEmbed_Plugin::get_instance(), 'deactivate_plugin' ) );
}

unset( $wp_api_oembed_requirements_check );
