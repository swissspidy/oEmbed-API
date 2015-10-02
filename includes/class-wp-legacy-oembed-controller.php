<?php
/**
 * Default oEmbed API endpoint configuration.
 *
 * Used when the REST API isn't available.
 *
 * @package WP_oEmbed_Plugin
 */

/**
 * Class WP_Legacy_oEmbed_Controller
 */
class WP_Legacy_oEmbed_Controller {
	/**
	 * Hook into the query parsing to detect oEmbed requests.
	 *
	 * If an oEmbed request is made, trigger the output.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param WP_Query $wp_query The WP_Query instance (passed by reference).
	 */
	public function parse_query( $wp_query ) {
		// Check for required params.
		if ( false === $wp_query->get( 'oembed', false ) ) {
			return;
		}

		if ( false === $wp_query->get( 'url', false ) ) {
			status_header( 400 );
			echo 'URL parameter missing';
			exit;
		}

		/**
		 * Check for the allowed query vars and set defaults.
		 *
		 * @see WP_REST_oEmbed_Controller::register_routes()
		 */

		$url = esc_url_raw( get_query_var( 'url' ) );

		$format = wp_oembed_ensure_format( get_query_var( 'format' ) );

		/**
		 * Filter the maxwidth oEmbed parameter.
		 *
		 * @param int $maxwidth Maximum allowed width. Defaults to 600.
		 */
		$maxwidth = apply_filters( 'oembed_default_width', 600 );
		$maxwidth = absint( get_query_var( 'maxwidth', $maxwidth ) );

		$callback = get_query_var( '_jsonp', false );

		$request = array(
			'url'      => $url,
			'format'   => $format,
			'maxwidth' => $maxwidth,
			'callback' => $callback,
		);

		echo $this->dispatch( $request );
		exit;
	}

	/**
	 * Handle the whole request and print the response.
	 *
	 * @param array $request The request arguments.
	 * @return string The oEmbed API response.
	 */
	public function dispatch( $request ) {
		$post_id = url_to_postid( $request['url'] );

		/**
		 * Filter the determined post id.
		 *
		 * @param int    $post_id The post ID.
		 * @param string $url     The requestd URL.
		 */
		$post_id = apply_filters( 'oembed_request_post_id', $post_id, $request['url'] );

		$data = get_oembed_response_data( $post_id, $request['maxwidth'] );

		if ( false === $data ) {
			status_header( 404 );
			return __( 'Invalid URL.', 'oembed-api' );
		}

		if ( 'json' === $request['format'] ) {
			return $this->json_response( $data, $request );
		}

		return $this->xml_response( $data );
	}

	/**
	 * Print the JSON response.
	 *
	 * @param array $data     The oEmbed response data.
	 * @param array $request  The request arguments.
	 * @return string The JSON response data.
	 */
	public function json_response( $data, $request ) {
		if ( ! is_string( $request['callback'] ) || preg_match( '/[^\w\.]/', $request['callback'] ) ) {
			$request['callback'] = false;
		}

		$result = wp_json_encode( $data );

		// Bail if the result couldn't be JSON encoded.
		if ( ! $result ) {
			status_header( 501 );
			return 'Not implemented';
		}

		if ( ! headers_sent() ) {
			$content_type = $request['callback'] ? 'application/javascript' : 'application/json';
			header( 'Content-Type: ' . $content_type . '; charset=' . get_option( 'blog_charset' ) );
			header( 'X-Content-Type-Options: nosniff' );
		}

		if ( $request['callback'] ) {
			return '/**/' . $request['callback'] . '(' . $result . ')';
		}

		return $result;
	}

	/**
	 * Print the XML response.
	 *
	 * @param array $data The oEmbed response data.
	 * @return string The XML response data.
	 */
	public function xml_response( $data ) {
		$result = _oembed_create_xml( $data );

		// Bail if there's no XML.
		if ( ! is_string( $result ) ) {
			status_header( 501 );
			return 'Not implemented';
		}

		if ( ! headers_sent() ) {
			header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ) );
		}

		return $result;
	}
}
