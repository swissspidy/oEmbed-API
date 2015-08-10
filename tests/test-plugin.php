<?php
/**
 * Test our plugin.
 *
 * @package WP_API_oEmbed
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
	/*function test_add_oembed_provider() {
		$oembed = _wp_oembed_get_object();

		wp_oembed_remove_provider( home_url( '/*' ) );
		$this->assertArrayNotHasKey( home_url( '/*' ), $oembed->providers );

		$this->plugin()->add_oembed_provider();

		$this->assertArrayHasKey( home_url( '/*' ), $oembed->providers );
		$this->assertEquals( array( esc_url( rest_url( 'wp/v2/oembed' ) ), false ),
			$oembed->providers[ home_url( '/*' ) ]
		);
	}*/

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

		$expected = '<iframe sandbox="allow-scripts" security="restricted" src="' . esc_url( get_post_embed_url( $post_id ) ) . '" width="200" height="200" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>';

		$this->assertEquals( $expected, get_post_embed_html( $post_id, 200, 200 ) );
	}
}
