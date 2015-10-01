<?php
/**
 * Sets up the default filters and actions for most
 * of the plugin hooks.
 *
 * If you need to remove a default hook, this file will
 * give you the priority for which to use to remove the
 * hook.
 *
 * @package WP_oEmbed
 */

// Load the plugin textdomain.
add_action( 'init', 'wp_oembed_load_textdomain' );

// Register scripts.
add_action( 'init', 'wp_oembed_register_scripts' );

// Disable the admin bar in embeds.
add_action( 'parse_query', 'wp_oembed_disable_admin_bar' );

// Load fallback if REST API isn't available.
if ( ! defined( 'REST_API_VERSION' ) || ! version_compare( REST_API_VERSION, '2.0-beta3', '>=' ) ) {
	// Pull in the required class.
	require_once( dirname( dirname( __FILE__ ) ) . '/includes/class-wp-legacy-oembed-controller.php' );

	// Add needed query vars.
	add_filter( 'query_vars', 'wp_oembed_add_query_vars' );

	// Hook into parse_query.
	add_action( 'parse_query', array( new WP_Legacy_oEmbed_Controller(), 'parse_query' ) );
} else {
	// Pull in the required class.
	require_once( dirname( dirname( __FILE__ ) ) . '/includes/class-wp-rest-oembed-controller.php' );

	// Configure the REST API route.
	add_action( 'rest_api_init', array( new WP_REST_oEmbed_Controller(), 'register_routes' ) );

	// Filter the REST API response to output XML if requested.
	add_filter( 'rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4 );
}

// Filter the oEmbed XML response to create an XML string.
add_filter( 'oembed_xml_response', '_oembed_create_xml' );

// Add a rewrite endpoint for the iframe.
add_action( 'init', 'wp_oembed_rewrite_endpoint' );

// Register our TinyMCE plugin.
add_action( 'mce_external_plugins', 'wp_oembed_add_mce_plugin' );

// Enqueue the resize script when the editor is also enqueued.
add_action( 'wp_enqueue_editor', 'wp_oembed_load_mce_script' );

add_filter( 'template_include', 'wp_oembed_include_template' );
add_filter( 'template_redirect', 'wp_oembed_old_slug_redirect', 1 );

add_action( 'wp_head', 'wp_oembed_add_discovery_links' );
add_action( 'wp_head', 'wp_oembed_add_host_js' );

add_action( 'oembed_head', 'print_emoji_detection_script' );
add_action( 'oembed_head', 'print_emoji_styles' );
add_action( 'oembed_head', 'wp_print_head_scripts', 20 );
add_action( 'oembed_head', 'wp_print_styles', 20 );
add_action( 'oembed_head', 'wp_no_robots' );
add_action( 'oembed_head', 'rel_canonical' );
add_action( 'oembed_head', 'locale_stylesheet' );

add_action( 'oembed_footer', 'wp_print_footer_scripts', 20 );

add_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10, 3 );

add_filter( 'embed_oembed_discover', '__return_true' );

add_filter( 'excerpt_more', 'wp_oembed_excerpt_more', 20 );

add_filter( 'the_excerpt_embed', 'wptexturize' );
add_filter( 'the_excerpt_embed', 'convert_chars' );
add_filter( 'the_excerpt_embed', 'wpautop' );
add_filter( 'the_excerpt_embed', 'shortcode_unautop' );
add_filter( 'the_excerpt_embed', 'wp_oembed_excerpt_attachment' );

add_filter( 'oembed_response_data', 'get_oembed_response_data_author', 10, 2, 4 );
add_filter( 'oembed_response_data', 'get_oembed_response_data_rich', 10, 4 );
