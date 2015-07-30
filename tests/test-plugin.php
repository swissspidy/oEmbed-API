<?php
/**
 * Test our plugin.
 */

/**
 * Class WP_API_oEmbed_Test_Plugin.
 */
class WP_API_oEmbed_Test_Plugin extends WP_API_oEmbed_TestCase {
	/**
	 * The plugin should be installed and activated.
	 */
	function test_plugin_activated() {
		$this->assertTrue( class_exists( 'WP_API_oEmbed_Plugin' ) );
	}

	/**
	 * Test if the register_rest_route function exists.
	 */
	function test_rest_api_available() {
		$this->assertTrue( function_exists( 'register_rest_route' ) );
	}

	/**
	 * Test the WordPress, PHP, and REST API requirements.
	 */
	function test_minimum_requirements() {
		$requirements_check = new WP_API_oEmbed_Requirements_Check( array(
			'title'    => 'oEmbed API',
			'php'      => '5.2',
			'wp'       => '4.3-beta3',
			'rest_api' => '2.0-beta3',
			'file'     => __FILE__,
		) );

		$this->assertTrue( $requirements_check->passes() );
	}

	/**
	 * Ensure our rewrite endpoint is there.
	 */
	function test_rewrite_endpoint() {
		global $wp_rewrite;

		$this->assertEquals( 'embed', $wp_rewrite->endpoints[0][1] );
		$this->assertEquals( 'embed', $wp_rewrite->endpoints[0][2] );
	}

	/**
	 * Test if the site was added as an oEmbed provider.
	 */
	function test_add_oembed_provider() {
		$oembed = _wp_oembed_get_object();
		$this->assertArrayHasKey( home_url( '/*' ), $oembed->providers );
		$this->assertEquals( array( esc_url( rest_url( 'wp/v2/oembed' ) ), false ),
			$oembed->providers[ home_url( '/*' ) ]
		);
	}
}
