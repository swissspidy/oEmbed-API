<?php
/**
 * Test our REST route.
 *
 * @package WP_oEmbed
 */

/**
 * Class WP_REST_oEmbed_Test_Endpoint.
 */
class WP_REST_oEmbed_Test_Controller extends WP_UnitTestCase {
	/**
	 * Load the REST API.
	 */
	public static function setUpBeforeClass() {
		require( dirname( __FILE__ ) . '/../vendor/json-rest-api/plugin.php' );
	}

	/**
	 * REST Server instance.
	 *
	 * @var WP_REST_Server
	 */
	protected $server;

	/**
	 * Runs before each test.
	 */
	public function setUp() {
		parent::setUp();

		/* @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );
	}

	/**
	 * Runs after each test.
	 */
	public function tearDown() {
		parent::tearDown();

		/* @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$wp_rest_server = null;
	}

	/**
	 * Test if our route has been registerd correctly.
	 *
	 * Borrowed from the REST API.
	 */
	public function test_route_availability() {
		// Check the route was registered correctly.
		$filtered_routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/wp/v2/oembed', $filtered_routes );
		$route = $filtered_routes['/wp/v2/oembed'];
		$this->assertCount( 1, $route );
		$this->assertArrayHasKey( 'callback', $route[0] );
		$this->assertArrayHasKey( 'methods', $route[0] );
		$this->assertArrayHasKey( 'args', $route[0] );
	}

	/**
	 * Test a POST request.
	 */
	function test_request_with_wrong_method() {
		$request = new WP_REST_Request( 'POST', '/wp/v2/oembed' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 'rest_no_route', $data[0]['code'] );
	}

	/**
	 * Test a request with a missing URL param.
	 */
	function test_request_without_url_param() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 'rest_missing_callback_param', $data[0]['code'] );
		$this->assertEquals( 'url', $data[0]['data']['params'][0] );
	}

	/**
	 * Test a request with a wrong URL.
	 */
	function test_request_with_bad_url() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );
		$request->set_param( 'url', 'http://google.com/' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 'rest_oembed_invalid_url', $data[0]['code'] );
	}

	/**
	 * Test a request with invalid format.
	 */
	function test_request_invalid_format() {
		$post_id = $this->factory->post->create();

		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post_id ) );
		$request->set_param( 'format', 'random' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 'rest_oembed_invalid_format', $data[0]['code'] );
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

		$request = new WP_REST_Request( 'GET', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );

		$response = $this->server->dispatch( $request );
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

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertTrue( is_array( $data ) );
	}

	/**
	 * Test XML output by the rest_pre_serve_request method.
	 */
	function test_rest_pre_serve_request() {
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

		$response = $this->server->dispatch( $request );

		ob_start();
		_oembed_rest_pre_serve_request( true, $response, $request, $this->server );
		$output = ob_get_clean();

		$xml = simplexml_load_string( $output );
		$this->assertInstanceOf( 'SimpleXMLElement', $xml );
	}

	/**
	 * Test the rest_pre_serve_request method.
	 */
	function test_rest_pre_serve_request_wrong_format() {
		$post = $this->factory->post->create_and_get();

		$request = new WP_REST_Request( 'HEAD', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );
		$request->set_param( 'format', 'json' );

		$response = $this->server->dispatch( $request );

		$this->assertTrue( _oembed_rest_pre_serve_request( true, $response, $request, $this->server ) );
	}

	/**
	 * Test the rest_pre_serve_request method.
	 */
	function test_rest_pre_serve_request_wrong_method() {
		$post = $this->factory->post->create_and_get();

		$request = new WP_REST_Request( 'HEAD', '/wp/v2/oembed' );
		$request->set_param( 'url', get_permalink( $post->ID ) );
		$request->set_param( 'format', 'xml' );

		$response = $this->server->dispatch( $request );

		$this->assertTrue( _oembed_rest_pre_serve_request( true, $response, $request, $this->server ) );
	}

	/**
	 * Test get_oembed_endpoint_url
	 */
	function test_get_oembed_endpoint_url() {
		$this->assertEquals( home_url() . '/?rest_route=/wp/v2/oembed', get_oembed_endpoint_url() );
		$this->assertEquals( home_url() . '/?rest_route=/wp/v2/oembed', get_oembed_endpoint_url( '', 'xml' ) );

		$post_id = $this->factory->post->create();
		$url     = get_permalink( $post_id );

		$this->assertEquals( home_url() . '/?rest_route=%2Fwp%2Fv2%2Foembed&url=' . $url, get_oembed_endpoint_url( $url ) );
		$this->assertEquals( home_url() . '/?rest_route=%2Fwp%2Fv2%2Foembed&url=' . $url . '&format=xml', get_oembed_endpoint_url( $url, 'xml' ) );
	}

	/**
	 * Test get_oembed_endpoint_url
	 */
	function test_get_oembed_endpoint_url_pretty_permalinks() {
		update_option( 'permalink_structure', '/%postname%' );

		$this->assertEquals( home_url() . '/wp-json/wp/v2/oembed', get_oembed_endpoint_url() );
		$this->assertEquals( home_url() . '/wp-json/wp/v2/oembed', get_oembed_endpoint_url( '', 'xml' ) );

		$post_id = $this->factory->post->create();
		$url     = get_permalink( $post_id );

		$this->assertEquals( home_url() . '/wp-json/wp/v2/oembed?url=' . $url, get_oembed_endpoint_url( $url ) );
		$this->assertEquals( home_url() . '/wp-json/wp/v2/oembed?url=' . $url . '&format=xml', get_oembed_endpoint_url( $url, 'xml' ) );

		update_option( 'permalink_structure', '' );
	}

	/**
	 * Test the availability of the item's schema for display / public consumption purposes
	 */
	public function test_get_item_schema() {
		$request    = new WP_REST_Request( 'OPTIONS', '/wp/v2/oembed' );
		$response = rest_handle_options_request( null, $this->server, $request );
		$data       = $response->get_data();
		$properties = $data['schema']['properties'];
		$this->assertEquals( 11, count( $properties ) );
		$this->assertArrayHasKey( 'type', $properties );
		$this->assertArrayHasKey( 'version', $properties );
		$this->assertArrayHasKey( 'width', $properties );
		$this->assertArrayHasKey( 'height', $properties );
		$this->assertArrayHasKey( 'title', $properties );
		$this->assertArrayHasKey( 'url', $properties );
		$this->assertArrayHasKey( 'html', $properties );
		$this->assertArrayHasKey( 'author_name', $properties );
		$this->assertArrayHasKey( 'author_url', $properties );
		$this->assertArrayHasKey( 'provider_name', $properties );
		$this->assertArrayHasKey( 'provider_url', $properties );
	}
}
