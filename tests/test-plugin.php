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

		unset( $wp_rewrite->endpoints );
		wp_oembed_rewrite_endpoint();

		$this->assertEquals( EP_PERMALINK | EP_PAGES | EP_ATTACHMENT, $wp_rewrite->endpoints[0][0] );
		$this->assertEquals( 'embed', $wp_rewrite->endpoints[0][1] );
		$this->assertEquals( 'embed', $wp_rewrite->endpoints[0][2] );
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

		$expected = '<iframe sandbox="allow-scripts" security="restricted" src="' . esc_url( get_post_embed_url( $post_id ) ) . '" width="200" height="200" title="Embedded WordPress Post" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" class="wp-embedded-content"></iframe>';

		$this->assertStringEndsWith( $expected, get_post_embed_html( $post_id, 200, 200 ) );
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

	function test_get_oembed_response_data_thumbnail() {
		$post          = $this->factory->post->create_and_get();
		$file          = DIR_TESTDATA . '/images/canola.jpg';
		$attachment_id = $this->factory->attachment->create_object( $file, $post->ID, array(
			'post_mime_type' => 'image/jpeg',
		) );
		set_post_thumbnail( $post, $attachment_id );

		$data = get_oembed_response_data( $post, 400 );

		$this->assertArrayHasKey( 'thumbnail_url', $data );
		$this->assertArrayHasKey( 'thumbnail_width', $data );
		$this->assertArrayHasKey( 'thumbnail_height', $data );
		$this->assertTrue( 400 >= $data['thumbnail_width'] );
	}

	/**
	 * Test oEmbed response data with attachments
	 */
	function test_get_oembed_response_data_attachment() {
		$parent = $this->factory->post->create();
		$file   = DIR_TESTDATA . '/images/canola.jpg';
		$post   = $this->factory->attachment->create_object( $file, $parent, array(
			'post_mime_type' => 'image/jpeg',
		) );

		$data = get_oembed_response_data( $post, 400 );

		$this->assertArrayHasKey( 'thumbnail_url', $data );
		$this->assertArrayHasKey( 'thumbnail_width', $data );
		$this->assertArrayHasKey( 'thumbnail_height', $data );
		$this->assertTrue( 400 >= $data['thumbnail_width'] );
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

	/**
	 * Test our default filters and hooks.
	 */
	function test_filters() {
		global $wp_filter;

		// Init.
		$this->assertarrayHasKey( 'wp_oembed_load_textdomain', $wp_filter['init'][10] );
		$this->assertarrayHasKey( 'wp_oembed_register_scripts', $wp_filter['init'][10] );
		$this->assertarrayHasKey( 'wp_oembed_rewrite_endpoint', $wp_filter['init'][10] );

		// Template.
		$this->assertarrayHasKey( 'wp_oembed_add_discovery_links', $wp_filter['wp_head'][10] );
		$this->assertarrayHasKey( 'wp_oembed_add_host_js', $wp_filter['wp_head'][10] );
		$this->assertarrayHasKey( 'wp_oembed_include_template', $wp_filter['template_include'][10] );
		$this->assertarrayHasKey( 'wp_filter_oembed_result', $wp_filter['oembed_result'][10] );
		$this->assertarrayHasKey( '__return_true', $wp_filter['embed_oembed_discover'][10] );

		// TinyMCE.
		$this->assertarrayHasKey( 'wp_oembed_add_mce_plugin', $wp_filter['mce_external_plugins'][10] );
		$this->assertarrayHasKey( 'wp_oembed_load_mce_script', $wp_filter['wp_enqueue_editor'][10] );
	}

	/**
	 * Test the wp_oembed_ensure_format function.
	 */
	function test_wp_oembed_ensure_format() {
		$this->assertEquals( 'json', wp_oembed_ensure_format( 'json' ) );
		$this->assertEquals( 'xml', wp_oembed_ensure_format( 'xml' ) );
		$this->assertEquals( 'json', wp_oembed_ensure_format( 123 ) );
		$this->assertEquals( 'json', wp_oembed_ensure_format( 'random' ) );
		$this->assertEquals( 'json', wp_oembed_ensure_format( array() ) );
	}

	/**
	 * Test the _oembed_create_xml function.
	 */
	function test_oembed_create_xml() {
		$actual = _oembed_create_xml( array(
			'foo'  => 'bar',
			'bar'  => 'baz',
			'ping' => 'pong',
		) );

		$expected = '<oembed><foo>bar</foo><bar>baz</bar><ping>pong</ping></oembed>';

		$this->assertStringEndsWith( $expected, trim( $actual ) );

		$actual = _oembed_create_xml( array(
			'foo' => array(
				'bar' => 'baz',
			),
			'ping' => 'pong',
		) );

		$expected = '<oembed><foo><bar>baz</bar></foo><ping>pong</ping></oembed>';

		$this->assertStringEndsWith( $expected, trim( $actual ) );

		$actual = _oembed_create_xml( array(
			'foo' => array(
				'bar' => array(
					'ping' => 'pong',
				),
			),
			'hello' => 'world',
		) );

		$expected = '<oembed><foo><bar><ping>pong</ping></bar></foo><hello>world</hello></oembed>';

		$this->assertStringEndsWith( $expected, trim( $actual ) );

		$actual = _oembed_create_xml( array(
			array(
				'foo' => array(
					'bar',
				),
			),
			'helloworld',
		) );

		$expected = '<oembed><oembed><foo><oembed>bar</oembed></foo></oembed><oembed>helloworld</oembed></oembed>';

		$this->assertStringEndsWith( $expected, trim( $actual ) );
	}
}
