<?php
/**
 * Sets up the template tags used by the plugin.
 *
 * @package WP_oEmbed
 */

/**
 * Add oEmbed discovery links in the website <head>.
 */
function wp_oembed_add_discovery_links() {
	$output = '';

	if ( is_singular() ) {
		$output .= '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_oembed_endpoint_url( get_permalink() ) ) . '" />' . "\n";
		$output .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( get_oembed_endpoint_url( get_permalink(), 'xml' ) ) . '" />' . "\n";
	}

	/**
	 * Filter the oEmbed discovery links.
	 *
	 * @param string $output HTML of the discovery links.
	 */
	echo apply_filters( 'rest_oembed_discovery_links', $output );
}

/**
 * Add JS to handle the messages from the embedded iframes.
 */
function wp_oembed_add_host_js() {
	?>
	<script type="text/javascript">
		<?php readfile( dirname( dirname( __FILE__ ) ) . '/scripts/frontend.js' ); ?>
	</script>
	<?php
}


/**
 * Get the URL to embed a specific post, for example in an iframe.
 *
 * @param int|WP_Post $post Optional. Post ID or object. Defaults to the current post.
 * @return string|false The post embed URL on success, false if the post doesn't exist.
 */
function get_post_embed_url( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}

	$embed_url = add_query_arg( array( 'embed' => 'true' ), get_permalink( $post ) );

	if ( get_option( 'permalink_structure' ) ) {
		$embed_url = trailingslashit( get_permalink( $post ) ) . user_trailingslashit( 'embed' );
	}

	return $embed_url;
}

/**
 * Get the oEmbed endpoint URL for a given permalink.
 *
 * Pass an empty string as the first argument
 * to get the endpoint base URL.
 *
 * @param string $permalink Optional. The permalink used for the `url` query arg.
 * @param string $format    Optional. The requested response format. Default is json.
 * @return string The oEmbed endpoint URL.
 */
function get_oembed_endpoint_url( $permalink = '', $format = 'json' ) {
	$url = add_query_arg( array( 'oembed' => 'true' ), home_url( '/' ) );

	if ( function_exists( 'rest_url' ) ) {
		$url = rest_url( 'wp/v2/oembed' );
	}

	if ( 'json' === $format ) {
		$format = false;
	}

	if ( '' !== $permalink ) {
		$url = add_query_arg( array(
			'url'    => $permalink,
			'format' => $format,
		), $url );
	}

	/**
	 * Filter the oEmbed endpoint URL.
	 *
	 * @param string $url       The URL to the oEmbed endpoint.
	 * @param string $permalink The permalink used for the `url` query arg.
	 * @param string $format    The requested response format.
	 */
	return apply_filters( 'rest_oembed_endpoint_url', $url, $permalink, $format );
}

/**
 * Get the embed code for a specific post.
 *
 * @param int|WP_Post $post   Optional. Post ID or object. Defaults to the current post.
 * @param int         $width  The width for the response.
 * @param int         $height The height for the response.
 * @return string|false Embed code on success, false if post doesn't exist.
 */
function get_post_embed_html( $post = null, $width, $height ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}

	$embed_url = get_post_embed_url( $post );

	$output = sprintf(
		'<iframe sandbox="allow-scripts" security="restricted" src="%1$s" width="%2$d" height="%3$d" title="%4$s" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>',
		esc_url( $embed_url ),
		$width,
		$height,
		__( 'Embedded WordPress Post', 'oembed-api' )
	);

	/**
	 * Filters the oEmbed HTML output.
	 *
	 * @param string  $output The default HTML.
	 * @param WP_Post $post   Current post object.
	 * @param int     $width  Width of the response.
	 * @param int     $height Height of the response.
	 */
	return apply_filters( 'rest_oembed_html', $output, $post, $width, $height );
}

/**
 * Get the oEmbed data for a given post.
 *
 * @param WP_Post|int $post  Optional. Post object or ID. Defaults to the current post.
 * @param int         $width The requested width.
 * @return array|false Response data on success, false if post doesn't exist.
 */
function get_oembed_response_data( $post = null, $width ) {
	/**
	 * Current post object.
	 *
	 * @var WP_Post $post
	 */
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}

	/**
	 * User object for the post author.
	 *
	 * @var WP_User $author
	 */
	$author = get_userdata( $post->post_author );

	// If a post doesn't have an author, fall back to the site's name.
	$author_name = get_bloginfo( 'name' );
	$author_url  = get_home_url();

	if ( $author ) {
		$author_name = $author->display_name;
		$author_url  = get_author_posts_url( $author->ID, $author->user_nicename );
	}

	/**
	 * Filter the allowed minimum width for the oEmbed response.
	 *
	 * @param int $width The minimum width. Defaults to 200.
	 */
	$minwidth = apply_filters( 'rest_oembed_minwidth', 200 );

	/**
	 * Filter the allowed maximum width for the oEmbed response.
	 *
	 * @param int $width The maximum width. Defaults to 600.
	 */
	$maxwidth = apply_filters( 'rest_oembed_maxwidth', 600 );

	if ( $width < $minwidth ) {
		$width = $minwidth;
	} else if ( $width > $maxwidth ) {
		$width = $maxwidth;
	}

	$height = ceil( $width / 16 * 9 );

	/**
	 * Filters the oEmbed response data.
	 *
	 * @param array   $data The response data.
	 * @param WP_Post $post The post object.
	 */
	$data = apply_filters( 'rest_oembed_response_data', array(
		'version'       => '1.0',
		'provider_name' => get_bloginfo( 'name' ),
		'provider_url'  => get_home_url(),
		'author_name'   => $author_name,
		'author_url'    => $author_url,
		'title'         => $post->post_title,
		'type'          => 'rich',
		'width'         => $width,
		'height'        => $height,
		'html'          => get_post_embed_html( $post, $width, $height ),
	), $post );

	return $data;
}

/**
 * Load the plugin textdomain.
 */
function wp_oembed_load_textdomain() {
	load_plugin_textdomain( 'oembed-api', false, basename( dirname( plugin_dir_path( __FILE__ ) ) ) . '/languages' );
}

/**
 * Add this site to the whitelist of oEmbed providers.
 */
function wp_oembed_add_site_as_provider() {
	wp_oembed_add_provider( home_url( '/*' ), get_oembed_endpoint_url() );
}

/**
 * Register our scripts.
 */
function wp_oembed_register_scripts() {
	wp_register_script( 'autoembed', plugins_url( 'scripts/frontend.js', dirname( __FILE__ ) ) );
}

/**
 * Register our TinyMCE plugin
 *
 * @param array $plugins List of current TinyMCE plugins.
 * @return array
 */
function wp_oembed_add_mce_plugin( $plugins ) {
	$plugins['autoembed'] = plugins_url( 'tinymce/plugin.js', dirname( __FILE__ ) );

	return $plugins;
}

/**
 * Load the resize script in the main window when TinyMCE is loaded, so that the
 * embed popup can also resize the iframe correctly.
 *
 * @param array $opts TinyMCE options.
 */
function wp_oembed_load_mce_script( $opts ) {
	if ( array_key_exists( 'tinymce', $opts ) && $opts['tinymce'] ) {
		wp_enqueue_script( 'autoembed' );
	}
}

/**
 * Hooks into the REST API output to print XML instead of JSON.
 *
 * @access private
 *
 * @param bool                      $served  Whether the request has already been served.
 * @param WP_HTTP_ResponseInterface $result  Result to send to the client. Usually a WP_REST_Response.
 * @param WP_REST_Request           $request Request used to generate the response.
 * @param WP_REST_Server            $server  Server instance.
 * @return true
 */
function _oembed_rest_pre_serve_request( $served, $result, $request, $server ) {
	$params = $request->get_params();

	if ( '/wp/v2/oembed' !== $request->get_route() || 'GET' !== $request->get_method() ) {
		return $served;
	}

	if ( ! isset( $params['format'] ) || 'xml' !== $params['format'] ) {
		return $served;
	}

	// Embed links inside the request.
	$data = $server->response_to_data( $result, false );

	if ( 404 === $result->get_status() ) {
		$data = $data [0];
	}

	/**
	 * Filter the XML response.
	 *
	 * @param string $result The built XML.
	 * @param array  $data   The original oEmbed response data.
	 */
	$result = apply_filters( 'rest_oembed_xml_response', false, $data );

	// Bail if there's no XML.
	if ( ! $result ) {
		status_header( 501 );
		die( 'Not implemented' );
	}

	if ( ! headers_sent() ) {
		$server->send_header( 'Content-Type', 'text/xml; charset=' . get_option( 'blog_charset' ) );
	}

	echo $result;

	return true;
}

/**
 * Create an XML string from the oEmbed response data
 *
 * @access private
 *
 * @param string|false $result The XML response string.
 * @param array        $data   The original oEmbed response data.
 * @return string|false XML string on success, false on error.
 */
function _oembed_create_xml( $result, $data ) {
	$oembed = new SimpleXMLElement( '<oembed></oembed>' );

	foreach ( $data as $key => $value ) {
		if ( is_array( $value ) ) {
			$element = $oembed->addChild( $key );

			foreach ( $value as $k => $v ) {
				$element->addChild( $k, $v );
			}

			continue;
		}

		$oembed->addChild( $key, $value );
	}

	$result = $oembed->asXML();

	return $result;
}

/**
 * Add the query vars we need for the legacy controller.
 *
 * @param array $query_vars Registered query vars.
 *
 * @return array
 */
function wp_oembed_add_query_vars( $query_vars ) {
	return array_merge( $query_vars, array( 'oembed', 'format', 'url', '_jsonp', 'maxwidth' ) );
}

/**
 * Returns the path to our oEmbed template.
 *
 * @param string $template The path of the template to include.
 * @return string The filtered template.
 */
function wp_oembed_include_template( $template ) {
	global $wp_query;

	if ( isset( $wp_query->query_vars['embed'] ) ) {
		return dirname( plugin_dir_path( __FILE__ ) ) . '/includes/template.php';
	}

	return $template;
}

/**
 * If the $url isn't on the trusted providers list, we need to filter the HTML heavily for security.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * @param string $html The unfiltered oEmbed HTML.
 * @param string $url  URL of the content to be embedded.
 *
 * @return string
 */
function wp_filter_oembed_result( $html, $url ) {
	require_once( ABSPATH . WPINC . '/class-oembed.php' );
	$wp_oembed = _wp_oembed_get_object();

	$trusted = $current_site = false;

	foreach ( $wp_oembed->providers as $matchmask => $data ) {
		$regex        = $data[1];
		$originalmask = $matchmask;

		// Turn the asterisk-type provider URLs into regex.
		if ( ! $regex ) {
			$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';
			$matchmask = preg_replace( '|^#http\\\://|', '#https?\://', $matchmask );
		}

		if ( preg_match( $matchmask, $url ) ) {
			if ( home_url( '/*' ) === $originalmask ) {
				$current_site = true;
			}

			$trusted = true;
			break;
		}
	}

	$allowed_html = array(
		'iframe' => array(
			'src'          => true,
			'width'        => true,
			'height'       => true,
			'frameborder'  => true,
			'marginwidth'  => true,
			'marginheight' => true,
			'scrolling'    => true,
			'title'        => true,
		),
	);

	if ( ! $trusted || $current_site ) {
		$html = wp_kses( $html, $allowed_html );
		$html = preg_replace( '|^(<iframe.*?></iframe>).*$|', '$1', $html );
		$html = str_replace( '<iframe', '<iframe sandbox="allow-scripts" security="restricted"', $html );

		preg_match( '/ src=[\'"]([^\'"]*)[\'"]/', $html, $results );

		if ( ! empty( $results ) ) {
			$secret = wp_generate_password( 10, false );

			$url = esc_url( "{$results[1]}#?secret=$secret" );

			$html = str_replace( $results[0], " src=\"$url\" data-secret=\"$secret\"", $html );
		}
	}

	if ( '' === $html ) {
		return false;
	}

	return $html;
}
