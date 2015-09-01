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
	 * Request arguments.
	 *
	 * @var array
	 */
	public $request;

	/**
	 * Simple constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'add_hooks' ) );
	}

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

			// Generate endpoint URLs
			add_filter( 'rest_oembed_endpoint_url', array( $this, 'endpoint_url' ), 10, 3 );
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

		$this->request = array(
			'url'      => $url,
			'format'   => $format,
			'maxwidth' => $maxwidth,
			'callback' => $callback,
		);

		echo $this->dispatch();
		exit;
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

	/**
	 * Replace the oEmbed endpoint URL.
	 *
	 * @param string $url       The URL to the oEmbed endpoint.
	 * @param string $permalink The permalink used for the `url` query arg.
	 * @param string $format    The requested response format.
	 *
	 * @return string
	 */
	public function endpoint_url( $url, $permalink, $format ) {
		$url = add_query_arg( array( 'oembed' => 'true' ), home_url( '/' ) );

		$url = add_query_arg( array(
			'url'    => $permalink,
			'format' => $format,
		), $url );

		return $url;
	}
}

new WP_Legacy_oEmbed_Controller();
