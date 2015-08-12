<?php
/**
 * Test frontend stuff.
 *
 * @package WP_API_oEmbed
 */

/**
 * Class WP_API_oEmbed_Test_Frontend.
 */
class WP_API_oEmbed_Test_Frontend extends WP_API_oEmbed_TestCase {
	/**
	 * API route class instance.
	 * @var WP_API_oEmbed_Frontend
	 */
	protected $class;

	/**
	 * Runs before each test.
	 */
	function setUp() {
		parent::setUp();

		$this->class = new WP_API_oEmbed_Frontend();
	}

	/**
	 * Runs after each test.
	 */
	function tearDown() {
		parent::tearDown();

		unset( $this->class );
	}

	/**
	 * Test output of add_oembed_discovery_links.
	 */
	function test_add_oembed_discovery_links_non_singular() {
		$this->assertEquals( '', $this->class->add_oembed_discovery_links() );
	}

	/**
	 * Test output of add_oembed_discovery_links.
	 */
	function test_add_oembed_discovery_links() {
		$post_id = $this->factory->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		ob_start();
		$this->class->add_oembed_discovery_links();
		$actual = ob_get_clean();

		$expected = '<link rel="alternate" type="application/json+oembed" href="' . esc_url( rest_url( 'wp/v2/oembed?url=' . get_permalink( $post_id ) ) ) . '" />' . "\n";
		$expected .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( rest_url( 'wp/v2/oembed?url=' . get_permalink() . '&format=xml' ) ) . '" />' . "\n";

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test filter_oembed_result_trusted method.
	 */
	function test_filter_oembed_result_trusted() {
		$html   = '<p></p><iframe onload="alert(1)"></iframe>';
		$actual = $this->class->filter_oembed_result( $html, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' );

		$this->assertEquals( $html, $actual );
	}

	/**
	 * Test filter_oembed_result_trusted method.
	 */
	function test_filter_oembed_result_untrusted() {
		$html   = '<p></p><iframe onload="alert(1)"></iframe>';
		$actual = $this->class->filter_oembed_result( $html, '' );

		$this->assertEquals( '<iframe sandbox="allow-scripts" security="restricted"></iframe>', $actual );
	}

	/**
	 * Test that only 1 iframe is allowed, nothing else.
	 */
	function test_filter_oembed_result_multiple_tags() {
		$html   = '<div><iframe></iframe><iframe></iframe><p></p></div>';
		$actual = $this->class->filter_oembed_result( $html, '' );

		$this->assertEquals( '<iframe sandbox="allow-scripts" security="restricted"></iframe>', $actual );
	}

	/**
	 * Test if the secret is appended to the URL.
	 */
	function test_filter_oembed_result_secret() {
		$html   = '<iframe src="https://wordpress.org"></iframe>';
		$actual = $this->class->filter_oembed_result( $html, '' );

		$matches = array();
		preg_match( '|src="https://wordpress.org#\?secret=([\w\d]+)" data-secret="([\w\d]+)"|', $actual, $matches );

		$this->assertTrue( isset( $matches[1] ) );
		$this->assertTrue( isset( $matches[2] ) );
		$this->assertEquals( $matches[1], $matches[2] );
	}
}
