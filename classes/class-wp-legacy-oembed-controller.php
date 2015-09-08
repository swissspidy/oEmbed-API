<?php
/**
 * Default oEmbed API endpoint configuration.
 *
 * Used when the REST API isn't available.
 *
 * @package WP_oEmbed_Plugin
 */

defined( 'WPINC' ) or die;

/**
 * Class WP_Legacy_oEmbed_Controller
 */
class WP_Legacy_oEmbed_Controller {
	/**
	 * Adds hooks.
	 */
	public function add_hooks() {
		// Load fallback if REST API isn't available.
		if ( ! defined( 'REST_API_VERSION' ) || ! version_compare( REST_API_VERSION, '2.0-beta3', '>=' ) ) {
			// Add needed query vars.
			add_action( 'query_vars', array( $this, 'add_query_vars' ) );

			// Hook into parse_query.
			add_action( 'parse_query', array( $this, 'parse_query' ) );
		}
	}

	/**
	 * Add the query vars we need.
	 *
	 * @param array $query_vars Registered query vars.
	 *
	 * @return array
	 */
	public function add_query_vars( $query_vars ) {
		return array_merge( $query_vars, array( 'oembed', 'format', 'url', '_jsonp', 'maxwidth' ) );
	}

	/**
	 * Hook into the query parsing to detect oEmbed requests.
	 *
	 * If an oEmbed request is made, trigger the output.
	 *
	 * @param WP_Query $wp_query The WP_Query instance (passed by reference).
	 */
	public function parse_query( $wp_query ) {
		// Check for required params.
		if ( ! array_key_exists( 'oembed', $wp_query->query_vars ) ||
			 ! array_key_exists( 'url', $wp_query->query_vars )
		) {
			return;
		}

		/**
		 * Check for the allowed query vars and set defaults.
		 *
		 * @see WP_REST_oEmbed_Controller::register_routes()
		 */

		$url = esc_url_raw( $wp_query->query_vars['url'] );

		/**
		 * Filter the default oEmbed response format.
		 *
		 * @param string $format oEmbed response format. Defaults to json.
		 *
		 * @return string
		 */
		$format = apply_filters( 'rest_oembed_default_format', 'json' );

		if ( isset( $wp_query->query_vars['format'] ) ) {
			$format = sanitize_text_field( $wp_query->query_vars['format'] );
		}

		/**
		 * Filter the maxwidth oEmbed parameter.
		 *
		 * @param int $maxwidth Maximum allowed width. Defaults to 600.
		 *
		 * @return int
		 */
		$maxwidth = apply_filters( 'rest_oembed_default_width', 600 );
		if ( isset( $wp_query->query_vars['maxwidth'] ) ) {
			$maxwidth = absint( $wp_query->query_vars['maxwidth'] );
		}

		$callback = isset( $wp_query->query_vars['_jsonp'] ) ? $wp_query->query_vars['_jsonp'] : false;

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
	 */
	public function dispatch( $request ) {
		if ( ! in_array( $request['format'], array( 'json', 'xml' ) ) ) {
			status_header( 501 );
			return 'Invalid format';
		}

		$post_id = url_to_postid( $request['url'] );

		/**
		 * Filter the determined post id.
		 *
		 * @param int    $post_id The post ID.
		 * @param string $url     The requestd URL.
		 */
		$post_id = apply_filters( 'rest_oembed_request_post_id', $post_id, $request['url'] );

		if ( 0 === $post_id ) {
			status_header( 404 );
			return 'Not Found';
		}

		$data = get_oembed_response_data( $post_id, $request['maxwidth'] );

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
	 *
	 * @return string The JSON response data.
	 */
	public function json_response( $data, $request ) {
		if ( ! is_string( $request['callback'] ) || preg_match( '/[^\w\.]/', $request['callback'] ) ) {
			$request['callback'] = false;
		}

		$result = wp_json_encode( $data );

		/**
		 * Filter the JSON response.
		 *
		 * @param string $result The encoded JSON.
		 * @param array  $data   The original oEmbed response data.
		 *
		 * @return string
		 */
		$result = apply_filters( 'rest_oembed_json_response', $result, $data );

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
	 *
	 * @return string The XML response data.
	 */
	public function xml_response( $data ) {
		/**
		 * Filter the XML response.
		 *
		 * @param string $result The built XML.
		 * @param array  $data   The original oEmbed response data.
		 *
		 * @return string
		 */
		$result = apply_filters( 'rest_oembed_xml_response', false, $data );

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
