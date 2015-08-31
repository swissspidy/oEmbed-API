<?php
/**
 * Test our REST route.
 *
 * @package WP_oEmbed
 */

/**
 * Class WP_oEmbed_Test_Response.
 */
class WP_oEmbed_Test_Response extends WP_oEmbed_TestCase {
	/**
	 * Test a request with a wrong URL.
	 */
	function test_request_with_bad_url() {
		$response = new WP_oEmbed_Response( array(
			'url'      => '',
			'format'   => 'json',
		) );

		$this->assertEquals( 'Not Found', $response->dispatch() );
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

		$response = new WP_oEmbed_Response( array(
			'url'      => get_permalink( $post->ID ),
			'format'   => 'json',
			'maxwidth' => 600,
			'callback' => '',
		) );

		$data = json_decode( $response->dispatch(), true );

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

		$response = new WP_oEmbed_Response( array(
			'url'      => get_permalink( $post->ID ),
			'format'   => 'xml',
			'maxwidth' => 600,
			'callback' => '',
		) );

		$data = $response->dispatch();

		$xml = simplexml_load_string( $data );
		$this->assertInstanceOf( 'SimpleXMLElement', $xml );
	}
}
