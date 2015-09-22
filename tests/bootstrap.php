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
 * Include functions that are in trunk unit testing suite, but not yet in all versions of WordPress core.
 */
if ( ! function_exists( 'is_post_type_viewable' ) ) {
	/**
	 * Determines whether a post type is considered "viewable".
	 *
	 * For built-in post types such as posts and pages, the 'public' value will be evaluated.
	 * For all others, the 'publicly_queryable' value will be used.
	 *
	 * @since 4.4.0
	 *
	 * @param object $post_type_object Post type object.
	 * @return bool Whether the post type should be considered viewable.
	 */
	function is_post_type_viewable( $post_type_object ) {
		return $post_type_object->publicly_queryable || ( $post_type_object->_builtin && $post_type_object->public );
	}
}
