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
	echo apply_filters( 'oembed_discovery_links', $output );
}

/**
 * Add JS to handle the messages from the embedded iframes.
 */
function wp_oembed_add_host_js() {
	wp_enqueue_script( 'wp-oembed', plugin_dir_url( dirname( __FILE__ ) ) . 'scripts/frontend.js', array(), '0.9.0-' . date( 'Ymd' ) );
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

	if ( get_option( 'permalink_structure' ) ) {
		$embed_url = trailingslashit( get_permalink( $post ) ) . user_trailingslashit( 'embed' );
	} else {
		$embed_url = add_query_arg( array( 'embed' => 'true' ), get_permalink( $post ) );
	}

	/**
	 * Filter the URL to embed a specific post.
	 *
	 * @param string  $embed_url The post embed URL.
	 * @param WP_Post $post      The corresponding post object.
	 */
	return esc_url_raw( apply_filters( 'post_embed_url', $embed_url, $post ) );
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
	if ( function_exists( 'rest_url' ) ) {
		$url = rest_url( 'wp/v2/oembed' );
	} else {
		$url = add_query_arg( array( 'oembed' => 'true' ), home_url( '/' ) );
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
	return apply_filters( 'oembed_endpoint_url', $url, $permalink, $format );
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

	// Get the post excerpt for the given post. This requires setting up the
	// global data.
	$previous_post = $GLOBALS['post'];
	$GLOBALS['post'] = $post;
	setup_postdata( $GLOBALS['post'] );

	$output = '<noscript>' . get_the_excerpt_embed() . "</noscript>\n";

	// Restore to the previous post.
	$GLOBALS['post'] = $previous_post;
	if ( $previous_post ) {
		setup_postdata( $GLOBALS['post'] );
	}

	$output .= "<script type='text/javascript'>\n";
	$output .= file_get_contents( dirname( dirname( __FILE__ ) ) . '/scripts/frontend.js' );
	$output .= "\n</script>";

	$output .= sprintf(
		'<iframe sandbox="allow-scripts" security="restricted" src="%1$s" width="%2$d" height="%3$d" title="%4$s" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" class="wp-embedded-content"></iframe>',
		esc_url( $embed_url ),
		absint( $width ),
		absint( $height ),
		esc_attr__( 'Embedded WordPress Post', 'oembed-api' )
	);

	/**
	 * Filters the oEmbed HTML output.
	 *
	 * @param string  $output The default HTML.
	 * @param WP_Post $post   Current post object.
	 * @param int     $width  Width of the response.
	 * @param int     $height Height of the response.
	 */
	return apply_filters( 'oembed_html', $output, $post, $width, $height );
}

/**
 * Get the oEmbed data for a given post.
 *
 * @param WP_Post|int $post  Optional. Post object or ID. Defaults to the current post.
 * @param int         $width The requested width.
 * @return array|false Response data on success, false if post doesn't exist.
 */
function get_oembed_response_data( $post = null, $width ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return false;
	}

	if ( 'publish' !== get_post_status( $post ) ) {
		return false;
	}

	/**
	 * Filter the allowed minimum width for the oEmbed response.
	 *
	 * @param int $width The minimum width. Defaults to 200.
	 */
	$minwidth = apply_filters( 'oembed_minwidth', 200 );

	/**
	 * Filter the allowed maximum width for the oEmbed response.
	 *
	 * @param int $width The maximum width. Defaults to 600.
	 */
	$maxwidth = apply_filters( 'oembed_maxwidth', 600 );

	if ( $width < $minwidth ) {
		$width = $minwidth;
	} else if ( $width > $maxwidth ) {
		$width = $maxwidth;
	}

	$height = ceil( $width / 16 * 9 );

	if ( 200 > $height ) {
		$height = 200;
	}

	$data = array(
		'version'       => '1.0',
		'provider_name' => get_bloginfo( 'name' ),
		'provider_url'  => get_home_url(),
		'author_name'   => get_bloginfo( 'name' ),
		'author_url'    => get_home_url(),
		'title'         => $post->post_title,
		'type'          => 'link',
	);

	/**
	 * Filter the oEmbed response data.
	 *
	 * @param array   $data   The response data.
	 * @param WP_Post $post   The post object.
	 * @param int     $width  The requested width.
	 * @param int     $height The calculated height.
	 */
	return apply_filters( 'oembed_response_data', $data, $post, $width, $height );
}

/**
 * Filters the oEmbed response data to add author information.
 *
 * @param array   $data The response data.
 * @param WP_Post $post The post object.
 * @return array The modified response data.
 */
function get_oembed_response_data_author( $data, $post ) {
	$author = get_userdata( $post->post_author );

	if ( $author ) {
		$data['author_name'] = $author->display_name;
		$data['author_url']  = get_author_posts_url( $author->ID );
	}

	return $data;
}

/**
 * Filters the oEmbed response data to return an iframe embed code.
 *
 * @param array   $data   The response data.
 * @param WP_Post $post   The post object.
 * @param int     $width  The requested width.
 * @param int     $height The calculated height.
 * @return array The modified response data.
 */
function get_oembed_response_data_rich( $data, $post, $width, $height ) {
	$data['width']  = absint( $width );
	$data['height'] = absint( $height );
	$data['type']   = 'rich';
	$data['html']   = get_post_embed_html( $post, $width, $height );

	// Add post thumbnail to response if available.
	$thumbnail_id = false;

	if ( has_post_thumbnail( $post->ID ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post->ID );
	}

	if ( 'attachment' === get_post_type( $post ) ) {
		if ( wp_attachment_is_image( $post ) ) {
			$thumbnail_id = $post->ID;
		} else if ( wp_attachment_is( 'video', $post ) ) {
			$thumbnail_id = get_post_thumbnail_id( $post );
			$data['type'] = 'video';
		}
	}

	if ( $thumbnail_id ) {
		list( $thumbnail_url, $thumbnail_width, $thumbnail_height ) = wp_get_attachment_image_src( $thumbnail_id, array( $width, 99999 ) );
		$data['thumbnail_url']    = $thumbnail_url;
		$data['thumbnail_width']  = $thumbnail_width;
		$data['thumbnail_height'] = $thumbnail_height;
	}

	return $data;
}

/**
 * Load the plugin textdomain.
 *
 * @codeCoverageIgnore
 */
function wp_oembed_load_textdomain() {
	load_plugin_textdomain( 'oembed-api', false, basename( dirname( plugin_dir_path( __FILE__ ) ) ) . '/languages' );
}

/**
 * Register our scripts.
 */
function wp_oembed_register_scripts() {
	wp_register_script( 'autoembed', plugins_url( 'scripts/frontend.js', dirname( __FILE__ ) ) );
}

/**
 * Register our TinyMCE plugin.
 *
 * @param array $plugins List of current TinyMCE plugins.
 * @return array The modified list of TinyMCE plugins.
 */
function wp_oembed_add_mce_plugin( $plugins ) {
	$plugins['autoembed'] = plugins_url( 'scripts/tinymce-plugin.js', dirname( __FILE__ ) );

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
 * Ensures that the specified format is either JSON or XML.
 *
 * Returns JSON if something else is provided.
 *
 * @param string $format The given oEmbed response format.
 * @return string The format, either XML or JSON.
 */
function wp_oembed_ensure_format( $format ) {
	if ( ! in_array( $format, array( 'json', 'xml' ) ) ) {
		return 'json';
	}

	return $format;
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
		$data = $data[0];
	}

	$result = _oembed_create_xml( $data );

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
 * Create an XML string from the oEmbed response data.
 *
 * @access private
 *
 * @param array            $data The original oEmbed response data.
 * @param SimpleXMLElement $node Optional. XML node to append the result to recursively.
 * @return string|false XML string on success, false on error.
 */
function _oembed_create_xml( $data, $node = null ) {
	if ( ! is_array( $data ) || empty( $data ) ) {
		return false;
	}

	if ( null === $node ) {
		$node = new SimpleXMLElement( '<oembed></oembed>' );
	}

	foreach ( $data as $key => $value ) {
		if ( is_numeric( $key ) ) {
			$key = 'oembed';
		}

		if ( is_array( $value ) ) {
			$item = $node->addChild( $key );
			_oembed_create_xml( $value, $item );
		} else {
			$node->addChild( $key, esc_html( $value ) );
		}
	}

	return $node->asXML();
}

/**
 * Add the query vars we need for the legacy controller.
 *
 * @param array $query_vars Registered query vars.
 * @return array The modified query vars array.
 */
function wp_oembed_add_query_vars( $query_vars ) {
	return array_merge( $query_vars, array( 'embed', 'oembed', 'format', 'url', '_jsonp', 'maxwidth' ) );
}

/**
 * Returns the path to our oEmbed template.
 *
 * @param string $template The path of the template to include.
 * @return string The filtered template.
 */
function wp_oembed_include_template( $template ) {
	if ( is_embed() ) {
		return dirname( plugin_dir_path( __FILE__ ) ) . '/includes/template.php';
	}

	return $template;
}

/**
 * Is the query for an embedded post?
 *
 * @return bool Whether we're in an embedded post or not.
 */
function is_embed() {
	return (bool) false !== get_query_var( 'embed', false ) && ( is_singular() || is_404() );
}

/**
 * Filters the returned oEmbed HTML.
 *
 * If the $url isn't on the trusted providers list,
 * we need to filter the HTML heavily for security.
 *
 * @param string $return The returned oEmbed HTML.
 * @param object $data   A data object result from an oEmbed provider.
 * @param string $url    The URL of the content to be embedded.
 * @return string The filtered and sanitized oEmbed result.
 */
function wp_filter_oembed_result( $return, $data, $url ) {
	if ( false === $return || ! in_array( $data->type, array( 'rich', 'video' ) ) ) {
		return $return;
	}

	require_once( ABSPATH . WPINC . '/class-oembed.php' );
	$wp_oembed = _wp_oembed_get_object();

	// Don't modify the HTML for trusted providers.
	if ( false !== $wp_oembed->get_provider( $url, array( 'discover' => false ) ) ) {
		return $return;
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
			'class'        => true,
		),
	);

	$html = wp_kses( $return, $allowed_html );
	preg_match( '|^.*(<iframe.*?></iframe>).*$|m', $html, $iframes );

	if ( empty( $iframes ) ) {
		return false;
	}

	$html = str_replace( '<iframe', '<iframe sandbox="allow-scripts" security="restricted"', $iframes[1] );

	preg_match( '/ src=[\'"]([^\'"]*)[\'"]/', $html, $results );

	if ( ! empty( $results ) ) {
		$secret = wp_generate_password( 10, false );

		$url = esc_url( "{$results[1]}#?secret=$secret" );

		$html = str_replace( $results[0], " src=\"$url\" data-secret=\"$secret\"", $html );
	}

	return $html;
}

/**
 * Filter the string in the "more" link displayed after a trimmed excerpt.
 *
 * @param string $more_string The string shown within the more link.
 * @return string The modified excerpt.
 */
function wp_oembed_excerpt_more( $more_string ) {
	if ( ! is_embed() ) {
		return $more_string;
	}

	return sprintf(
		_x( '&hellip; %s', 'read more link', 'oembed-api' ),
		sprintf(
			'<a class="wp-embed-more" href="%s" target="_top">%s</a>',
			get_the_permalink(),
			__( 'Read more', 'oembed-api' )
		)
	);
}

/**
 * Retrieve the post excerpt for the embed template.
 *
 * @return string Post excerpt.
 */
function get_the_excerpt_embed() {
	$output = get_the_excerpt();
	/**
	 * Filter the post excerpt for the embed template.
	 *
	 * @param string $output The current post excerpt.
	 */
	return apply_filters( 'the_excerpt_embed', $output );
}

/**
 * Display the post excerpt for the embed template.
 */
function the_excerpt_embed() {
	echo get_the_excerpt_embed();
}

/**
 * Filters the post excerpt for the embed template.
 *
 * Shows players for video and audio attachments.
 *
 * @param string $content The current post excerpt.
 * @return string The modified post excerpt.
 */
function wp_oembed_excerpt_attachment( $content ) {
	if ( is_attachment() ) {
		return prepend_attachment( '' );
	}

	return $content;
}

/**
 * Custom old slug redirection function.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @codeCoverageIgnore
 */
function wp_oembed_old_slug_redirect() {
	global $wp_query, $wpdb;

	if ( false === get_query_var( 'embed', false ) ) {
		return;
	}

	if ( ! is_404() || false === get_query_var( 'name', false ) ) {
		return;
	}

	// Guess the current post_type based on the query vars.
	$post_type = 'post';

	if ( get_query_var( 'post_type' ) ) {
		$post_type = get_query_var( 'post_type' );
	} elseif ( get_query_var( 'pagename', false ) ) {
		$post_type = 'page';
	}

	if ( is_array( $post_type ) ) {
		if ( count( $post_type ) > 1 ) {
			return;
		}
		$post_type = reset( $post_type );
	}

	// Do not attempt redirect for hierarchical post types.
	if ( is_post_type_hierarchical( $post_type ) ) {
		return;
	}

	$query = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta, $wpdb->posts WHERE ID = post_id AND post_type = %s AND meta_key = '_wp_old_slug' AND meta_value = %s", $post_type, $wp_query->query_vars['name'] );

	if ( get_query_var( 'year', false ) ) {
		$query .= $wpdb->prepare( ' AND YEAR(post_date) = %d', $wp_query->query_vars['year'] );
	}
	if ( get_query_var( 'monthnum', false ) ) {
		$query .= $wpdb->prepare( ' AND MONTH(post_date) = %d', $wp_query->query_vars['monthnum'] );
	}
	if ( get_query_var( 'day', false ) ) {
		$query .= $wpdb->prepare( ' AND DAYOFMONTH(post_date) = %d', $wp_query->query_vars['day'] );
	}

	$post_id = (int) $wpdb->get_var( $query );

	if ( ! $post_id ) {
		return;
	}

	wp_redirect( get_post_embed_url( $post_id ), 301 );
	exit;
}

/**
 * Disable the admin bar in the embed template.
 */
function wp_oembed_disable_admin_bar() {
	if ( is_embed() ) {
		add_filter( 'show_admin_bar', '__return_false', 9999 );
	}
}
