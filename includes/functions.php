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

	$data = array(
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
	);

	// Add post thumbnail to response if available.
	$thumbnail_id = false;

	if ( has_post_thumbnail( $post->ID ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post->ID );
	}

	if ( 'attachment' === get_post_type( $post ) && wp_attachment_is_image( $post->ID ) ) {
		$thumbnail_id = $post->ID;
	}

	if ( $thumbnail_id ) {
		list( $thumbnail_url, $thumbnail_width, $thumbnail_height ) = wp_get_attachment_image_src( $thumbnail_id, array( $width, 99999 ) );
		$data['thumbnail_url']    = $thumbnail_url;
		$data['thumbnail_width']  = $thumbnail_width;
		$data['thumbnail_height'] = $thumbnail_height;
	}

	/**
	 * Filters the oEmbed response data.
	 *
	 * @param array   $data The response data.
	 * @param WP_Post $post The post object.
	 */
	return apply_filters( 'oembed_response_data', $data, $post );
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
 * Register our TinyMCE plugin
 *
 * @param array $plugins List of current TinyMCE plugins.
 * @return array
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
		$data = $data [0];
	}

	/**
	 * Filter the XML response.
	 *
	 * @param string $result The built XML.
	 * @param array  $data   The original oEmbed response data.
	 */
	$result = apply_filters( 'oembed_xml_response', false, $data );

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
	if ( false !== get_query_var( 'embed', false ) && have_posts() && is_singular() ) {
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

	$trusted = false;

	foreach ( $wp_oembed->providers as $matchmask => $data ) {
		$regex = $data[1];

		// Turn the asterisk-type provider URLs into regex.
		if ( ! $regex ) {
			$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';
			$matchmask = preg_replace( '|^#http\\\://|', '#https?\://', $matchmask );
		}

		if ( preg_match( $matchmask, $url ) ) {
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

	if ( ! $trusted ) {
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

/**
 * Filter the string in the "more" link displayed after a trimmed excerpt.
 *
 * @SuppressWarnings(PHPMD.ElseExpression)
 *
 * @param string $more_string The string shown within the more link.
 * @return string The modified excerpt.
 */
function wp_oembed_excerpt_more( $more_string ) {
	global $post;

	if ( false === get_query_var( 'embed', false ) ) {
		return $more_string;
	}

	$text = wp_strip_all_tags( $post->post_content );

	/*
	 * translators: If your word count is based on single characters (e.g. East Asian characters),
	 * enter 'characters_excluding_spaces' or 'characters_including_spaces'. Otherwise, enter 'words'.
	 * Do not translate into your own language.
	 */
	if ( strpos( _x( 'words', 'Word count type. Do not translate!', 'oembed-api' ), 'characters' ) === 0 && preg_match( '/^utf\-?8$/i', get_option( 'blog_charset' ) ) ) {
		$text = trim( preg_replace( "/[\n\r\t ]+/", ' ', $text ), ' ' );
		preg_match_all( '/./u', $text, $words_array );
		$words_array = $words_array[0];
	} else {
		$words_array = preg_split( "/[\n\r\t ]+/", $text, -1, PREG_SPLIT_NO_EMPTY );
	}

	$more = sprintf(
		_n( '&hellip; (%d word)', '&hellip; (%d words)', count( $words_array ), 'oembed-api' ),
		count( $words_array )
	);

	// The `&lrm;` fixes bi-directional text display defect in RTL languages.
	$more = '<span class="wp-embed-more">' . $more . '&lrm;</span>';

	return ' ' . $more;
}

/**
 * Display the post excerpt for the embed template.
 */
function the_excerpt_embed() {
	$output = get_the_excerpt();
	/**
	 * Filter the post excerpt for the embed template.
	 *
	 * @param string $output The current post excerpt.
	 */
	echo apply_filters( 'the_excerpt_embed', $output );
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
