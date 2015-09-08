<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

/**
 * Load the REST API and our own plugin.
 */
function _manually_load_oembed_api_plugin() {
	require dirname( __FILE__ ) . '/../wp-api-oembed.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_oembed_api_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
