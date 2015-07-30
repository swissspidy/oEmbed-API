<?php
/**
 * Test our REST route.
 *
 * @package WP_API_oEmbed
 */

/**
 * Class WP_API_oEmbed_Test_Endpoint.
 */
class WP_API_oEmbed_Test_Endpoint extends WP_API_oEmbed_TestCase {
	/**
	 * API route class instance.
	 * @var WP_API_oEmbed_Endppoint
	 */
	protected $class;

	/**
	 * Runs before each test.
	 */
	function setUp() {
		$this->class = new WP_API_oEmbed_Endppoint();

		$GLOBALS['wp_rest_server'] = new WP_REST_Server();
	}

	/**
	 * Runs after each test.
	 */
	function tearDown() {
		unset( $this->class );
	}

	/**
	 * Test if our route has been registerd correctly.
	 *
	 * Borrowed from the REST API.
	 */
	public function test_register_route() {
		$this->class->register_routes();

		// Check the route was registered correctly.
		$filtered_routes = $GLOBALS['wp_rest_server']->get_routes();
		$this->assertArrayHasKey( '/wp/v2/oembed', $filtered_routes );
		$route = $filtered_routes['/wp/v2/oembed'];
		$this->assertCount( 1, $route );
		$this->assertArrayHasKey( 'callback', $route[0] );
		$this->assertArrayHasKey( 'methods', $route[0] );
		$this->assertArrayHasKey( 'args', $route[0] );
	}

	/**
	 * Test the route before we have registered it.
	 */
	function test_non_existing_route() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );

		/* @var WP_REST_Response $response */
		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 'rest_no_route', $data[0]['code'] );
	}

	/**
	 * Test a POST request.
	 */
	function test_request_with_wrong_method() {
		$this->class->register_routes();

		$request = new WP_REST_Request( 'POST', '/wp/v2/oembed' );

		/* @var WP_REST_Response $response */
		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 'rest_no_route', $data[0]['code'] );
	}

	/**
	 * Test a request with a missing URL param.
	 */
	function test_request_without_url_param() {
		$this->class->register_routes();
		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );

		/* @var WP_REST_Response $response */
		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 'rest_missing_callback_param', $data[0]['code'] );
		$this->assertEquals( 'url', $data[0]['data']['params'][0] );
	}

	/**
	 * Test a request with a wrong URL.
	 */
	function test_request_with_bad_url() {
		$request = new WP_REST_Request( 'POST', '/wp/v2/oembed' );
		$request->set_param( 'url', 'http://google.com/' );

		$response = $this->class->get_oembed_response( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'rest_oembed_invalid_url', $response->get_error_code() );
	}
}
