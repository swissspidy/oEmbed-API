<?php
/**
 * Test HTTP headers in separate processes.
 *
 * @package WP_oEmbed
 */

/**
 * Class WP_oEmbed_Test_HTTP_Headers.
 *
 * @runTestsInSeparateProcesses
 */
class WP_oEmbed_Test_HTTP_Headers extends WP_UnitTestCase {
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

		add_filter( 'oembed_json_response', '__return_false' );

		$this->assertEquals( 'Not implemented', $legacy_controller->dispatch( $request ) );
	}

	/**
	 * Test the HTTP headers set by the json_response method.
	 */
	function test_request_json_response_headers() {
		if ( ! function_exists( 'xdebug_get_headers' ) ) {
			$this->markTestSkipped( 'xdebug is required for this test' );
		}

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
		$legacy_controller->dispatch( $request );

		$headers = xdebug_get_headers();

		$this->assertTrue( in_array( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), $headers ) );
		$this->assertTrue( in_array( 'X-Content-Type-Options: nosniff', $headers ) );

		$request['callback'] = 'foobar';

		$legacy_controller->dispatch( $request );

		$headers = xdebug_get_headers();

		$this->assertTrue( in_array( 'Content-Type: application/javascript; charset=' . get_option( 'blog_charset' ), $headers ) );
		$this->assertTrue( in_array( 'X-Content-Type-Options: nosniff', $headers ) );
	}

	/**
	 * Test the HTTP headers set by the xml_response method.
	 */
	function test_request_xml_response_headers() {
		if ( ! function_exists( 'xdebug_get_headers' ) ) {
			$this->markTestSkipped( 'xdebug is required for this test' );
		}

		$post = $this->factory->post->create_and_get( array(
			'post_title'  => 'Hello World',
		) );

		$request = array(
			'url'      => get_permalink( $post->ID ),
			'format'   => 'xml',
			'maxwidth' => 600,
		);

		$legacy_controller = new WP_Legacy_oEmbed_Controller();
		$legacy_controller->dispatch( $request );

		$headers = xdebug_get_headers();

		$this->assertTrue( in_array( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), $headers ) );
	}

	/**
	 * Test HTTP headers set by the rest_pre_serve_request method.
	 */
	function test_rest_pre_serve_request_headers() {
		if ( ! function_exists( 'xdebug_get_headers' ) ) {
			$this->markTestSkipped( 'xdebug is required for this test' );
		}

		require_once( dirname( __FILE__ ) . '/../vendor/json-rest-api/plugin.php' );
		require_once( dirname( __FILE__ ) . '/../includes/class-wp-rest-oembed-controller.php' );

		$user = $this->factory->user->create_and_get( array(
			'display_name' => 'John Doe',
		) );
		$post = $this->factory->post->create_and_get( array(
			'post_author' => $user->ID,
			'post_title'  => 'Hello World',
		) );

		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );
		$request->set_param( 'format', 'xml' );

		$server = new WP_REST_Server;

		$response = $server->dispatch( $request );

		ob_start();
		_oembed_rest_pre_serve_request( true, $response, $request, $server );
		$output = ob_get_clean();

		$this->assertNotEmpty( $output );

		$headers = xdebug_get_headers();

		$this->assertTrue( in_array( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), $headers ) );
	}
}
