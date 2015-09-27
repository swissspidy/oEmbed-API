<?php
/**
 * Test frontend stuff.
 *
 * @package WP_oEmbed
 */

/**
 * Class WP_oEmbed_Test_Frontend.
 */
class WP_oEmbed_Test_Frontend extends WP_UnitTestCase {
	/**
	 * Test template inclusion.
	 */
	function test_template_include() {
		$this->assertEquals( '', wp_oembed_include_template( '' ) );

		$post_id = $this->factory->post->create();
		$this->go_to( get_permalink( $post_id ) );
		$GLOBALS['wp_query']->query_vars['embed'] = true;

		$this->assertQueryTrue( 'is_single', 'is_singular' );
		$this->assertEquals( dirname( plugin_dir_path( __FILE__ ) ) . '/includes/template.php', wp_oembed_include_template( '' ) );
	}

	/**
	 * Test discovery links.
	 */
	function test_add_oembed_discovery_links_non_singular() {
		ob_start();
		wp_oembed_add_discovery_links();
		$actual = ob_get_clean();
		$this->assertSame( '', $actual );
	}

	/**
	 * Test discovery links on a single post.
	 */
	function test_add_oembed_discovery_links() {
		$post_id = $this->factory->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		ob_start();
		wp_oembed_add_discovery_links();
		$actual = ob_get_clean();

		$expected = '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_oembed_endpoint_url( get_permalink() ) ) . '" />' . "\n";
		$expected .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( get_oembed_endpoint_url( get_permalink(), 'xml' ) ) . '" />' . "\n";

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test trusted HTML.
	 */
	function test_filter_oembed_result_trusted() {
		$html   = '<p></p><iframe onload="alert(1)"></iframe>';

		$actual = wp_filter_oembed_result( $html, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' );

		$this->assertEquals( $html, $actual );
	}

	/**
	 * Test untrusted HTML.
	 */
	function test_filter_oembed_result_untrusted() {
		$html   = '<p></p><iframe onload="alert(1)" src="http://example.com/sample-page/"></iframe>';
		$actual = wp_filter_oembed_result( $html, 'http://example.com/sample-page/' );

		$matches = array();
		preg_match( '|src=".*#\?secret=([\w\d]+)" data-secret="([\w\d]+)"|', $actual, $matches );

		$this->assertTrue( isset( $matches[1] ) );
		$this->assertTrue( isset( $matches[2] ) );
		$this->assertEquals( $matches[1], $matches[2] );
	}

	/**
	 * Test that only 1 iframe is allowed, nothing else.
	 */
	function test_filter_oembed_result_multiple_tags() {
		$html   = '<div><iframe></iframe><iframe></iframe><p></p></div>';
		$actual = wp_filter_oembed_result( $html, '' );

		$this->assertEquals( '<iframe sandbox="allow-scripts" security="restricted"></iframe>', $actual );
	}

	/**
	 * Test untrusted HTML without iframe.
	 */
	function test_filter_oembed_result_no_iframe() {
		$html   = '<span>Hello</span><p>World</p>';
		$actual = wp_filter_oembed_result( $html, '' );

		$this->assertFalse( $actual );

		$html   = '<div><p></p></div><script></script>';
		$actual = wp_filter_oembed_result( $html, '' );

		$this->assertFalse( $actual );
	}

	/**
	 * Test if the secret is appended to the URL.
	 */
	function test_filter_oembed_result_secret() {
		$html   = '<iframe src="https://wordpress.org"></iframe>';
		$actual = wp_filter_oembed_result( $html, '' );

		$matches = array();
		preg_match( '|src="https://wordpress.org#\?secret=([\w\d]+)" data-secret="([\w\d]+)"|', $actual, $matches );

		$this->assertTrue( isset( $matches[1] ) );
		$this->assertTrue( isset( $matches[2] ) );
		$this->assertEquals( $matches[1], $matches[2] );
	}

	/**
	 * Test add_host_js method.
	 */
	function test_add_host_js() {
		ob_start();
		wp_oembed_add_host_js();
		ob_end_clean();

		$this->assertTrue( wp_script_is( 'wp-oembed' ) );
	}

	/**
	 * Test default oEmbed output for a post.
	 */
	function test_oembed_output() {
		$user = $this->factory->user->create_and_get( array(
			'display_name' => 'John Doe',
		) );

		$post_id = $this->factory->post->create( array(
			'post_author'  => $user->ID,
			'post_title'   => 'Hello World',
			'post_content' => 'Foo Bar',
			'post_excerpt' => 'Bar Baz',
		) );
		$this->go_to( get_permalink( $post_id ) );
		$GLOBALS['wp_query']->query_vars['embed'] = true;

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		ob_start();
		include( dirname( plugin_dir_path( __FILE__ ) ) . '/includes/template.php' );
		$actual = ob_get_clean();

		$doc = new DOMDocument();
		$this->assertTrue( $doc->loadHTML( $actual ) );
		$this->assertTrue( false === strpos( $actual, 'Page not found' ) );
		$this->assertTrue( false !== strpos( $actual, 'Hello World' ) );
	}

	/**
	 * Test oEmbed output for a post with a thumbnail.
	 */
	function test_oembed_output_thumbnail() {
		$post_id       = $this->factory->post->create( array(
			'post_title'   => 'Hello World',
			'post_content' => 'Foo Bar',
			'post_excerpt' => 'Bar Baz',
		) );
		$file          = DIR_TESTDATA . '/images/canola.jpg';
		$attachment_id = $this->factory->attachment->create_object( $file, $post_id, array(
			'post_mime_type' => 'image/jpeg',
		) );
		set_post_thumbnail( $post_id, $attachment_id );

		$this->go_to( get_permalink( $post_id ) );
		$GLOBALS['wp_query']->query_vars['embed'] = true;

		$this->assertQueryTrue( 'is_single', 'is_singular' );

		ob_start();
		include( dirname( plugin_dir_path( __FILE__ ) ) . '/includes/template.php' );
		$actual = ob_get_clean();

		$doc = new DOMDocument();
		$this->assertTrue( $doc->loadHTML( $actual ) );
		$this->assertFalse( strpos( $actual, 'Page not found' ) );
		$this->assertTrue( false !== strpos( $actual, 'Hello World' ) );
		$this->assertTrue( false !== strpos( $actual, 'canola.jpg' ) );
	}

	/**
	 * Test oEmbed output for a non-existent post.
	 */
	function test_oembed_output_404() {
		global $wp_rewrite;

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$wp_rewrite->flush_rules();

		$this->go_to( home_url( '/non-existent-post/embed/' ) );
		$GLOBALS['wp_query']->query_vars['embed'] = true;

		$this->assertQueryTrue( 'is_404' );

		$this->assertEquals( dirname( plugin_dir_path( __FILE__ ) ) . '/includes/template.php', wp_oembed_include_template( '' ) );

		ob_start();
		include( dirname( plugin_dir_path( __FILE__ ) ) . '/includes/template.php' );
		$actual = ob_get_clean();

		$doc = new DOMDocument();
		$this->assertTrue( $doc->loadHTML( $actual ) );
		$this->assertTrue( false !== strpos( $actual, 'Page not found' ) );

		$wp_rewrite->set_permalink_structure( '' );
		$wp_rewrite->init();
	}

	/**
	 * Test oEmbed output for an attachment.
	 */
	function test_oembed_output_attachment() {
		$post          = $this->factory->post->create_and_get();
		$file          = DIR_TESTDATA . '/images/canola.jpg';
		$attachment_id = $this->factory->attachment->create_object( $file, $post->ID, array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => 'Hello World',
			'post_content'   => 'Foo Bar',
			'post_excerpt'   => 'Bar Baz',
		) );

		$this->go_to( get_attachment_link( $attachment_id ) );
		$GLOBALS['wp_query']->query_vars['embed'] = true;

		$this->assertQueryTrue( 'is_single', 'is_singular', 'is_attachment' );

		ob_start();
		include( dirname( plugin_dir_path( __FILE__ ) ) . '/includes/template.php' );
		$actual = ob_get_clean();

		$doc = new DOMDocument();
		$this->assertTrue( $doc->loadHTML( $actual ) );
		$this->assertFalse( strpos( $actual, 'Page not found' ) );
		$this->assertTrue( false !== strpos( $actual, 'Hello World' ) );
		$this->assertTrue( false !== strpos( $actual, 'canola.jpg' ) );
	}

	/**
	 * Test if registering our script works.
	 */
	function test_register_scripts() {
		wp_scripts()->remove( 'autoembed' );
		$this->assertFalse( wp_script_is( 'autoembed', 'registered' ) );

		wp_oembed_register_scripts();
		$this->assertTrue( wp_script_is( 'autoembed', 'registered' ) );
	}

	/**
	 * Test adding the TinyMCE plugin.
	 */
	function test_add_mce_plugin() {
		$actual = wp_oembed_add_mce_plugin( array() );

		$this->assertEquals( array( 'autoembed' => plugins_url( 'scripts/tinymce-plugin.js', dirname( __FILE__ ) ) ), $actual );
	}

	/**
	 * Test loading our TinyMCE script.
	 */
	function test_load_mce_script() {
		wp_oembed_load_mce_script( array() );
		$this->assertFalse( wp_script_is( 'autoembed' ) );

		wp_oembed_load_mce_script( array( 'tinymce' => true ) );
		$this->assertTrue( wp_script_is( 'autoembed' ) );
	}

	/**
	 * Test the excerpt.
	 */
	function test_wp_oembed_excerpt_more_no_embed() {
		$GLOBALS['wp_query'] = new WP_Query();

		$this->assertEquals( 'foo bar', wp_oembed_excerpt_more( 'foo bar' ) );
	}

	/**
	 * Test excerpt function.
	 */
	function test_wp_oembed_excerpt_more() {
		$GLOBALS['wp_query'] = new WP_Query();
		$GLOBALS['wp_query']->query_vars['embed'] = true;

		$GLOBALS['post'] = $this->factory->post->create_and_get( array(
			'post_content' => 'Foo Bar',
		) );

		$actual = wp_oembed_excerpt_more( '' );

		$expected = sprintf(
			'&hellip; <a class="wp-embed-more" href="%s" target="_top">Read more</a>',
			get_the_permalink()
		);

		$this->assertEquals( $expected, $actual );
	}
}
