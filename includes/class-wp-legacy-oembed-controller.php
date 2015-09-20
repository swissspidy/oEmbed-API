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

		$format = sanitize_text_field( get_query_var( 'format', 'json' ) );

		/**
		 * Filter the maxwidth oEmbed parameter.
		 *
		 * @param int $maxwidth Maximum allowed width. Defaults to 600.
		 *
		 * @return int
		 */
		$maxwidth = apply_filters( 'oembed_default_width', 600 );
		$maxwidth = get_query_var( 'maxwidth', $maxwidth );

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
		if ( ! in_array( $request['format'], array( 'json', 'xml' ) ) ) {
			status_header( 501 );
			return 'Invalid format';
		}

		$need_restore_current_blog = false;

		// We need to switch to blog in case of a sub directory Multisite config
		if ( is_multisite() && ! is_subdomain_install() ) {
			$network_url = trailingslashit( network_site_url() );
			$url_parts   = explode( '/', str_replace( $network_url, '', $request['url'] ) );
			$blog_slug   = reset( $url_parts );
			$blog_id     = get_id_from_blogname( $blog_slug );

			if ( ! empty( $blog_id ) && (int) $blog_id !== get_current_blog_id() ) {
				$need_restore_current_blog = true;
				switch_to_blog( $blog_id );
			}
		}

		$post_id = url_to_postid( $request['url'] );

		/**
		 * Filter the determined post id.
		 *
		 * @param int    $post_id The post ID.
		 * @param string $url     The requestd URL.
		 */
		$post_id = apply_filters( 'oembed_request_post_id', $post_id, $request['url'] );

		$data = get_oembed_response_data( $post_id, $request['maxwidth'] );

		// Restore current blog if needed
		if ( $need_restore_current_blog ) {
			restore_current_blog();
		}

		if ( false === $data ) {
			status_header( 404 );
			return 'Not Found';
		}

		if ( 'json' === $request['format'] ) {
			return $this->json_response( $data, $request );
		}

		return $this->xml_response( $data );
	}

	/**
	 * Print the JSON response.
	 *
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 *
	 * @param array $data     The oEmbed response data.
	 * @param array $request  The request arguments.
	 * @return string The JSON response data.
	 */
	public function json_response( $data, $request ) {
		if ( ! is_string( $request['callback'] ) || preg_match( '/[^\w\.]/', $request['callback'] ) ) {
			$request['callback'] = false;
		}

		if ( function_exists( 'wp_json_encode' ) ) {
			$result = wp_json_encode( $data );
		} else {
			$result = json_encode( $data );
		}

		/**
		 * Filter the JSON response.
		 *
		 * @param string $result The encoded JSON.
		 * @param array  $data   The original oEmbed response data.
		 */
		$result = apply_filters( 'oembed_json_response', $result, $data );

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
			return 'Not implemented';
		}

		if ( ! headers_sent() ) {
			header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ) );
		}

		return $result;
	}
}
