<?php
/**
 * Test our REST route.
 *
 * @package WP_oEmbed
 */

/**
 * Class WP_oEmbed_Test_Response.
 */
class WP_Legacy_oEmbed_Test_Controller extends WP_UnitTestCase {
	/**
	 * Test our default filters and hooks.
	 */
	public function test_filters() {
		global $wp_filter;

		$this->assertarrayHasKey( 'wp_oembed_add_query_vars', $wp_filter['query_vars'][10] );
	}
	/**
	 * Test a request with a wrong URL.
	 */
	function test_request_with_bad_url() {
		// WP_Query arguments.
		$request = array(
			'url'      => '',
			'format'   => 'json',
		);

		$legacy_controller = new WP_Legacy_oEmbed_Controller();

		$this->assertEquals( 'Not Found', $legacy_controller->dispatch( $request ) );
	}

	/**
	 * Test a request with invalid format.
	 */
	function test_request_invalid_format() {
		$post_id = $this->factory->post->create();

		// WP_Query arguments.
		$request = array(
			'url'	  => get_permalink( $post_id ),
			'format'   => 'random',
			'maxwidth' => 600,
			'callback' => '',
		);

		$legacy_controller = new WP_Legacy_oEmbed_Controller();

		$this->assertEquals( 'Invalid format', $legacy_controller->dispatch( $request ) );
	}

	/**
	 * Test request for a normal post.
	 */
	function test_request_json() {
		$user = $this->factory->user->create_and_get( array(
			'display_name' => 'John Doe',
		) );
		$post = $this->factory->post->create_and_get( array(
			'post_author' => $user->ID,
			'post_title'  => 'Hello World',
		) );

		// WP_Query arguments.
		$request = array(
			'url'	  => get_permalink( $post->ID ),
			'format'   => 'json',
			'maxwidth' => 600,
			'callback' => '',
			'oembed'   => true,
		);

		$legacy_controller = new WP_Legacy_oEmbed_Controller();

		$data = json_decode( $legacy_controller->dispatch( $request ), true );

		$this->assertTrue( is_array( $data ) );

		$this->assertArrayHasKey( 'version', $data );
		$this->assertArrayHasKey( 'provider_name', $data );
		$this->assertArrayHasKey( 'provider_url', $data );
		$this->assertArrayHasKey( 'author_name', $data );
		$this->assertArrayHasKey( 'author_url', $data );
		$this->assertArrayHasKey( 'title', $data );
		$this->assertArrayHasKey( 'type', $data );

		$this->assertEquals( '1.0', $data['version'] );
		$this->assertEquals( get_bloginfo( 'name' ), $data['provider_name'] );
		$this->assertEquals( get_home_url(), $data['provider_url'] );
		$this->assertEquals( $user->display_name, $data['author_name'] );
		$this->assertEquals( get_author_posts_url( $user->ID, $user->user_nicename ), $data['author_url'] );
		$this->assertEquals( $post->post_title, $data['title'] );
		$this->assertEquals( 'rich', $data['type'] );
	}

	/**
	 * Test request for a normal post.
	 */
	function test_request_json_not_implemented() {
		$post = $this->factory->post->create_and_get( array(
			'post_title'  => 'Hello World',
		) );

		$request = array(
			'url'      => get_permalink( $post->ID ),
			'format'   => 'json',
			'maxwidth' => 600,
			'callback' => '',
		);

		$legacy_controller = new WP_Legacy_oEmbed_Controller();

		add_filter( 'rest_oembed_json_response', '__return_false' );

		$this->assertEquals( 'Not implemented', $legacy_controller->dispatch( $request ) );
	}

	/**
	 * Test request for a normal post.
	 */
	function test_request_jsonp() {
		$user = $this->factory->user->create_and_get( array(
			'display_name' => 'John Doe',
		) );
		$post = $this->factory->post->create_and_get( array(
			'post_author' => $user->ID,
			'post_title'  => 'Hello World',
		) );

		$request = array(
			'url'	  => get_permalink( $post->ID ),
			'format'   => 'json',
			'maxwidth' => 600,
			'callback' => 'mycallback',
		);

		$legacy_controller = new WP_Legacy_oEmbed_Controller();

		$data = $legacy_controller->dispatch( $request );

		$this->assertEquals( 0, strpos( $data, '/**/mycallback(' ) );
	}

	/**
	 * Test JSONP request with an invalid callback.
	 */
	function test_request_jsonp_invalid_callback() {
		$user = $this->factory->user->create_and_get( array(
			'display_name' => 'John Doe',
		) );
		$post = $this->factory->post->create_and_get( array(
			'post_author' => $user->ID,
			'post_title'  => 'Hello World',
		) );

		$request = array(
			'url'	  => get_permalink( $post->ID ),
			'format'   => 'json',
			'maxwidth' => 600,
			'callback' => array( 'foo', 'bar' ),
		);

		$legacy_controller = new WP_Legacy_oEmbed_Controller();

		$data = $legacy_controller->dispatch( $request );

		$this->assertFalse( strpos( $data, '/**/mycallback(' ) );
	}

	/**
	 * Test request for a normal post.
	 */
	function test_request_xml() {
		$user = $this->factory->user->create_and_get( array(
			'display_name' => 'John Doe',
		) );
		$post = $this->factory->post->create_and_get( array(
			'post_author' => $user->ID,
			'post_title'  => 'Hello World',
		) );

		$request = array(
			'url'	  => get_permalink( $post->ID ),
			'format'   => 'xml',
			'maxwidth' => 600,
			'callback' => '',
		);

		$legacy_controller = new WP_Legacy_oEmbed_Controller();

		$data = $legacy_controller->dispatch( $request );

		$xml = simplexml_load_string( $data );
		$this->assertInstanceOf( 'SimpleXMLElement', $xml );
	}

	/**
	 * Test request for a normal post.
	 */
	function test_request_xml_not_implemented() {
		$post = $this->factory->post->create_and_get( array(
			'post_title'  => 'Hello World',
		) );

		$request = array(
			'url'	  => get_permalink( $post->ID ),
			'format'   => 'xml',
			'maxwidth' => 600,
			'callback' => '',
		);

		add_filter( 'rest_oembed_xml_response', '__return_false' );

		$legacy_controller = new WP_Legacy_oEmbed_Controller();

		$this->assertEquals( 'Not implemented',  $legacy_controller->dispatch( $request ) );
	}
}
