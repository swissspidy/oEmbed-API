<?php
/**
 * Test our REST route.
 *
 * @package WP_API_oEmbed
 */

/**
 * Class WP_REST_oEmbed_Test_Endpoint.
 */
class WP_REST_oEmbed_Test_Endpoint extends WP_API_oEmbed_TestCase {
	/**
	 * API route class instance.
	 * @var WP_REST_oEmbed_Endpoint
	 */
	protected $class;

	/**
	 * Runs before each test.
	 */
	function setUp() {
		parent::setUp();

		$this->class = new WP_REST_oEmbed_Endpoint();

		$GLOBALS['wp_rest_server'] = new WP_REST_Server();
	}

	/**
	 * Runs after each test.
	 */
	function tearDown() {
		parent::tearDown();

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

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 'rest_missing_callback_param', $data[0]['code'] );
		$this->assertEquals( 'url', $data[0]['data']['params'][0] );
	}

	/**
	 * Test a request with a wrong URL.
	 */
	function test_request_with_bad_url() {
		$this->class->register_routes();
		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );
		$request->set_param( 'url', 'http://google.com/' );

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 'rest_oembed_invalid_url', $data[0]['code'] );
	}

	/**
	 * Test a request with invalid format.
	 */
	function test_request_invalid_format() {
		$this->class->register_routes();

		$post_id = $this->factory->post->create();

		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post_id ) );
		$request->set_param( 'format', 'random' );

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 'rest_oembed_invalid_format', $data[0]['code'] );
	}

	/**
	 * Test request for a normal post.
	 */
	function test_request_json() {
		$this->class->register_routes();

		$user = $this->factory->user->create_and_get( array(
			'display_name' => 'John Doe',
		) );
		$post = $this->factory->post->create_and_get( array(
			'post_author' => $user->ID,
			'post_title'  => 'Hello World',
		) );

		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

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
	function test_request_xml() {
		$this->class->register_routes();

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

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

		$this->assertTrue( is_array( $data ) );
	}

	/**
	 * Test XML output by the rest_pre_serve_request method.
	 */
	function test_rest_pre_serve_request() {
		$this->class->register_routes();

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

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );

		ob_start();
		$this->plugin()->rest_pre_serve_request( true, $response, $request, $GLOBALS['wp_rest_server'] );
		$output = ob_get_clean();

		$xml = simplexml_load_string( $output );
		$this->assertInstanceOf( 'SimpleXMLElement', $xml );
	}

	/**
	 * Test the rest_pre_serve_request method.
	 */
	function test_rest_pre_serve_request_wrong_format() {
		$this->class->register_routes();

		$post = $this->factory->post->create_and_get();

		$request = new WP_REST_Request( 'HEAD', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );
		$request->set_param( 'format', 'json' );

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );

		$this->assertTrue( $this->plugin()->rest_pre_serve_request( true, $response, $request, $GLOBALS['wp_rest_server'] ) );
	}

	/**
	 * Test the rest_pre_serve_request method.
	 */
	function test_rest_pre_serve_request_wrong_method() {
		$this->class->register_routes();

		$post = $this->factory->post->create_and_get();

		$request = new WP_REST_Request( 'HEAD', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );
		$request->set_param( 'format', 'xml' );

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );

		$this->assertTrue( $this->plugin()->rest_pre_serve_request( true, $response, $request, $GLOBALS['wp_rest_server'] ) );
	}

	/**
	 * Test request with maxwidth param.
	 */
	function test_request_maxwidth() {
		$this->class->register_routes();

		$user = $this->factory->user->create_and_get();
		$post = $this->factory->post->create_and_get( array(
			'post_author' => $user->ID,
		) );

		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );
		$request->set_param( 'maxwidth', 400 );

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $data['width'] );
		$this->assertEquals( 225, $data['height'] );
	}

	/**
	 * Test request with maxwidth param that is too high.
	 */
	function test_request_maxwidth_too_high() {
		$this->class->register_routes();

		$user = $this->factory->user->create_and_get();
		$post = $this->factory->post->create_and_get( array(
			'post_author' => $user->ID,
		) );

		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );
		$request->set_param( 'maxwidth', 1000 );

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 600, $data['width'] );
		$this->assertEquals( 338, $data['height'] );
	}

	/**
	 * Test request with maxwidth param that is too low.
	 */
	function test_request_maxwidth_too_low() {
		$this->class->register_routes();

		$user = $this->factory->user->create_and_get();
		$post = $this->factory->post->create_and_get( array(
			'post_author' => $user->ID,
		) );

		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );
		$request->set_param( 'maxwidth', - 10 );

		$response = $GLOBALS['wp_rest_server']->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $data['width'] );
		$this->assertEquals( 113, $data['height'] );
	}
}
