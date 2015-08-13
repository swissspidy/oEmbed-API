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

/**
 * Class WP_oEmbed_TestCase.
 */
class WP_oEmbed_TestCase extends WP_UnitTestCase {
	/**
	 * Plugin instance
	 *
	 * @var WP_oEmbed_Plugin
	 */
	protected static $instance;

	/**
	 * Return the plugin instance.
	 *
	 * @return WP_oEmbed_Plugin
	 */
	function plugin() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WP_oEmbed_Plugin();
		}

		return self::$instance;
	}

	/**
	 * Set a POST variable.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 */
	function set_post( $key, $value ) {
		$_POST[ $key ] = $_REQUEST[ $key ] = addslashes( $value );
	}

	/**
	 * Unset a POST variable.
	 *
	 * @param string $key Key.
	 */
	function unset_post( $key ) {
		unset( $_POST[ $key ], $_REQUEST[ $key ] );
	}
}
