<?php
/**
 * Sets up the default filters and actions for most
 * of the plugin hooks.
 *
 * If you need to remove a default hook, this file will
 * give you the priority for which to use to remove the
 * hook.
 *
 * @codeCoverageIgnore
 *
 * @package WP_oEmbed
 */

// Load the plugin textdomain.
add_action( 'init', 'wp_oembed_load_textdomain' );

// Whitelist this site as an oEmbed provider.
add_action( 'init', 'wp_oembed_add_site_as_provider' );

// Register scripts.
add_action( 'init', 'wp_oembed_register_scripts' );

// Load fallback if REST API isn't available.
if ( ! defined( 'REST_API_VERSION' ) || ! version_compare( REST_API_VERSION, '2.0-beta3', '>=' ) ) {
	// Add needed query vars.
	add_action( 'query_vars', 'wp_oembed_add_query_vars' );

	// Hook into parse_query.
	add_action( 'parse_query', array( new WP_Legacy_oEmbed_Controller(), 'parse_query' ) );
}

// Configure the REST API route.
add_action( 'rest_api_init', array( new WP_REST_oEmbed_Controller(), 'register_routes' ) );

// Filter the REST API response to output XML if requested.
add_filter( 'rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4 );

// Filter the oEmbed XML response to create an XML string.
add_filter( 'rest_oembed_xml_response', '_oembed_create_xml', 10, 2 );

// Add a rewrite endpoint for the iframe.
add_action( 'init', 'wp_oembed_rewrite_endpoint' );

// Register our TinyMCE plugin.
add_action( 'mce_external_plugins', 'wp_oembed_add_mce_plugin' );

// Enqueue the resize script when the editor is also enqueued.
add_action( 'wp_enqueue_editor', 'wp_oembed_load_mce_script' );

add_filter( 'template_include', 'wp_oembed_include_template' );

add_action( 'wp_head', 'wp_oembed_add_discovery_links' );

add_action( 'wp_head', 'wp_oembed_add_host_js' );

add_filter( 'wp_oembed_result', 'wp_filter_oembed_result', 10, 2 );

add_filter( 'embed_oembed_discover', '__return_true' );
