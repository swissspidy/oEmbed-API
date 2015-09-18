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
	 * Test our template_include hook
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
	 * Test output of add_oembed_discovery_links.
	 */
	function test_add_oembed_discovery_links_non_singular() {
		ob_start();
		wp_oembed_add_discovery_links();
		$actual = ob_get_clean();
		$this->assertEquals( '', $actual );
	}

	/**
	 * Test output of add_oembed_discovery_links.
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
	 * Test filter_oembed_result_trusted method.
	 */
	function test_filter_oembed_result_trusted() {
		$html   = '<p></p><iframe onload="alert(1)"></iframe>';

		$actual = wp_filter_oembed_result( $html, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' );

		$this->assertEquals( $html, $actual );
	}

	/**
	 * Test filter_oembed_result_trusted method.
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
	 * Test filter_oembed_result_trusted method for current site.
	 */
	function test_filter_oembed_result_current_site() {
		$html   = '<p></p><iframe onload="alert(1)"></iframe>';
		$actual = wp_filter_oembed_result( $html, home_url( '/' ) );

		$this->assertEquals( '<iframe sandbox="allow-scripts" security="restricted"></iframe>', $actual );
	}

	/**
	 * Test filter_oembed_result_trusted method without iframe.
	 */
	function test_filter_oembed_result_no_iframe() {
		$html   = '<span>Hello</span><p>World</p>';
		$actual = wp_filter_oembed_result( $html, '' );

		$this->assertEquals( 'HelloWorld', $actual );

		$html   = '<div><p></p></div><script></script>';
		$actual = wp_filter_oembed_result( $html, '' );

		$this->assertEquals( '', $actual );
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
		$actual = ob_get_clean();

		$this->assertTrue( false !== strpos( $actual, '<script type="text/javascript">' ) );
	}

	/**
	 * Test rest_oembed_output method.
	 */
	function test_rest_oembed_output() {
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
	 * Test rest_oembed_output method.
	 */
	function test_rest_oembed_output_404() {
		global $wp_rewrite;

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$wp_rewrite->flush_rules();

		$this->go_to( home_url( '/non-existent-post/embed/' ) );
		$GLOBALS['wp_query']->query_vars['embed'] = true;

		$this->assertQueryTrue( 'is_404' );

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
	 * Test the wp_oembed_excerpt_more function.
	 */
	function test_wp_oembed_excerpt_more_no_embed() {
		$GLOBALS['wp_query'] = new WP_Query();

		$this->assertEquals( 'foo bar', wp_oembed_excerpt_more( 'foo bar' ) );
	}

	/**
	 * Test the wp_oembed_excerpt_more function.
	 */
	function test_wp_oembed_excerpt_more() {
		$GLOBALS['wp_query']                      = new WP_Query();
		$GLOBALS['wp_query']->query_vars['embed'] = true;

		$GLOBALS['post'] = $this->factory->post->create_and_get( array(
			'post_content' => 'Foo Bar',
		) );

		$actual = wp_oembed_excerpt_more( '' );

		$this->assertEquals( ' <span class="wp-embed-more">&hellip; (2 words)&lrm;</span>', $actual );
	}
}
