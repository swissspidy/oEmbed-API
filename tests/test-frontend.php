<?php

class WP_API_oEmbed_Test_Frontend extends WP_API_oEmbed_TestCase {
	function test_rewrite_endpoint() {
		global $wp_rewrite;

		$this->assertEquals( 'embed', $wp_rewrite->endpoints[0][1] );
		$this->assertEquals( 'embed', $wp_rewrite->endpoints[0][2] );
	}

	function test_add_oembed_provider() {
		$oembed = _wp_oembed_get_object();
		$this->assertArrayHasKey( home_url( '/*' ), $oembed->providers );
		$this->assertEquals(
			array( esc_url( rest_url( 'wp/v2/oembed' ) ), false ),
			$oembed->providers[ home_url( '/*' ) ]
		);
	}
}
