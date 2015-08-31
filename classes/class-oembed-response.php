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
 * Class WP_oEmbed_Response
 */
class WP_oEmbed_Response {
	/**
	 * Request arguments.
	 *
	 * @var array
	 */
	protected $request;

	/**
	 * Simple constructor.
	 *
	 * @param array $args Request arguments.
	 */
	public function __construct( $args ) {
		$this->request = $args;
	}

	/**
	 * Handle the whole request and print the response.
	 */
	public function dispatch() {
		if ( ! in_array( $this->request['format'], array( 'json', 'xml' ) ) ) {
			status_header( 501 );
			return 'Invalid format';
		}

		$post_id = url_to_postid( $this->request['url'] );

		/**
		 * Filter the determined post id.
		 *
		 * @param int    $post_id The post ID.
		 * @param string $url     The requestd URL.
		 */
		$post_id = apply_filters( 'rest_oembed_request_post_id', $post_id, $this->request['url'] );

		if ( 0 === $post_id ) {
			status_header( 404 );
			return 'Not Found';
		}

		$data = get_oembed_response_data( $post_id, $this->request['maxwidth'] );

		if ( 'json' === $this->request['format'] ) {
			return $this->json_response( $data, $this->request['callback'] );
		}

		return $this->xml_response( $data );
	}

	/**
	 * Print the JSON response.
	 *
	 * @param array       $data     The oEmbed response data.
	 * @param string|bool $callback JSONP callback.
	 *
	 * @return string The JSON response data.
	 */
	public function json_response( $data, $callback ) {
		if ( ! is_string( $this->request['callback'] ) || preg_match( '/[^\w\.]/', $this->request['callback'] ) ) {
			$this->request['callback'] = false;
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
			$content_type = $callback ? 'application/javascript' : 'application/json';
			header( 'Content-Type: ' . $content_type . '; charset=' . get_option( 'blog_charset' ) );
			header( 'X-Content-Type-Options: nosniff' );
		}

		if ( $this->request['callback'] ) {
			return '/**/' . $this->request['callback'] . '(' . $result . ')';
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
