<?php
/**
 * Test frontend stuff.
 *
 * @package WP_API_oEmbed
 */

/**
 * Class WP_API_oEmbed_Test_Frontend.
 */
class WP_API_oEmbed_Test_Frontend extends WP_API_oEmbed_TestCase {
	/**
	 * Check if our rewrite endpoint exists.
	 */
	function test_rewrite_endpoint() {
		global $wp_rewrite;

		$this->assertEquals( 'embed', $wp_rewrite->endpoints[0][1] );
		$this->assertEquals( 'embed', $wp_rewrite->endpoints[0][2] );
	}

	/**
	 * Check if our own site is on the oEmbed provider whitelist.
	 */
	function test_add_oembed_provider() {
		$oembed = _wp_oembed_get_object();
		$this->assertArrayHasKey( home_url( '/*' ), $oembed->providers );
		$this->assertEquals(
			array( esc_url( rest_url( 'wp/v2/oembed' ) ), false ),
			$oembed->providers[ home_url( '/*' ) ]
		);
	}
}
