<?php
/**
 * Test our plugin.
 *
 * @package WP_oEmbed
 */

/**
 * Class WP_oEmbed_Test_Plugin.
 */
class WP_oEmbed_Test_Plugin extends WP_UnitTestCase {
	/**
	 * The plugin should be installed and activated.
	 */
	function test_plugin_activated() {
		$this->assertTrue( function_exists( 'wp_oembed_rewrite_endpoint' ) );
	}

	/**
	 * Test if the register_rest_route function exists.
	 */
	function test_rest_api_available() {
		$this->assertFalse( function_exists( 'register_rest_route' ) );
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

		wp_oembed_remove_provider( home_url( '/*' ) );
		$this->assertArrayNotHasKey( home_url( '/*' ), $oembed->providers );

		wp_oembed_add_site_as_provider();

		$this->assertArrayHasKey( home_url( '/*' ), $oembed->providers );
		$this->assertEquals( array( get_oembed_endpoint_url(), false ), $oembed->providers[ home_url( '/*' ) ] );
	}

	/**
	 * Test the get_post_embed_url function.
	 */
	function test_get_post_embed_url_non_existent_post() {
		$embed_url = get_post_embed_url( 0 );
		$this->assertFalse( $embed_url );
	}

	/**
	 * Test the get_post_embed_url function.
	 */
	function test_get_post_embed_url() {
		update_option( 'permalink_structure', '/%postname%' );

		$post_id   = $this->factory->post->create();
		$permalink = get_permalink( $post_id );
		$embed_url = get_post_embed_url( $post_id );

		$this->assertEquals( $permalink . '/embed', $embed_url );

		update_option( 'permalink_structure', '' );
	}

	/**
	 * Test the get_post_embed_url function.
	 */
	function test_get_post_embed_url_pretty_permalinks() {
		$post_id   = $this->factory->post->create();
		$permalink = get_permalink( $post_id );
		$embed_url = get_post_embed_url( $post_id );

		$this->assertEquals( $permalink . '&embed=true', $embed_url );
	}

	/**
	 * Test the get_post_embed_html function.
	 */
	function test_get_post_embed_html_non_existent_post() {
		$this->assertFalse( get_post_embed_html( 0, 200, 200 ) );
		$this->assertFalse( get_post_embed_html( null, 200, 200 ) );
	}

	/**
	 * Test the get_post_embed_html function.
	 */
	function test_get_post_embed_html() {
		$post_id = $this->factory->post->create();

		$expected = '<iframe sandbox="allow-scripts" security="restricted" src="' . esc_url( get_post_embed_url( $post_id ) ) . '" width="200" height="200" title="Embedded WordPress Post" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>';

		$this->assertEquals( $expected, get_post_embed_html( $post_id, 200, 200 ) );
	}

	/**
	 * Test get_oembed_response_data with a post that doesn't exist.
	 */
	function test_get_oembed_response_data_non_existent_post() {
		$this->assertFalse( get_oembed_response_data( 0, 100 ) );
	}

	/**
	 * Test get_oembed_response_data normally.
	 */
	function test_get_oembed_response_data() {
		$post = $this->factory->post->create_and_get();

		$data = get_oembed_response_data( $post, 400 );

		$this->assertEquals( 400, $data['width'] );
		$this->assertEquals( 225, $data['height'] );
	}

	/**
	 * Test get_oembed_response_data with a maxwidth that is too high.
	 */
	function test_get_oembed_response_data_maxwidth_too_high() {
		$post = $this->factory->post->create_and_get();

		$data = get_oembed_response_data( $post, 1000 );

		$this->assertEquals( 600, $data['width'] );
		$this->assertEquals( 338, $data['height'] );
	}

	/**
	 * Test get_oembed_response_data with a maxwidth that is too low.
	 */
	function test_get_oembed_response_data_maxwidth_too_low() {
		$post = $this->factory->post->create_and_get();

		$data = get_oembed_response_data( $post, 100 );

		$this->assertEquals( 200, $data['width'] );
		$this->assertEquals( 113, $data['height'] );
	}

	/**
	 * Test if our query vars have been successfully registered.
	 */
	function test_query_vars() {
		/* @var WP $wp */
		global $wp;

		foreach ( array( 'oembed', 'format', 'url', '_jsonp', 'maxwidth' ) as $query_var ) {
			$this->assertTrue( in_array( $query_var, $wp->public_query_vars ) );
		}
	}

	/**
	 * Test get_oembed_endpoint_url
	 */
	function test_get_oembed_endpoint_url() {
		$this->assertEquals( home_url() . '/?oembed=true', get_oembed_endpoint_url() );
		$this->assertEquals( home_url() . '/?oembed=true', get_oembed_endpoint_url( '', 'json' ) );
		$this->assertEquals( home_url() . '/?oembed=true', get_oembed_endpoint_url( '', 'xml' ) );

		$post_id = $this->factory->post->create();
		$url     = get_permalink( $post_id );

		$this->assertEquals( home_url() . '/?oembed=true&url=' . $url, get_oembed_endpoint_url( $url ) );
		$this->assertEquals( home_url() . '/?oembed=true&url=' . $url . '&format=xml', get_oembed_endpoint_url( $url, 'xml' ) );
	}
}
