<?php
/**
 * REST API endpoint controller.
 *
 * @package WP_API_oEmbed
 */

defined( 'WPINC' ) or die;

/**
 * Class WP_REST_oEmbed_Controller
 */
class WP_REST_oEmbed_Controller {
	/**
	 * Register the API routes.
	 */
	public function register_routes() {
		register_rest_route( 'wp/v2', '/oembed', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_item' ),
				'args'     => array(
					'url'      => array(
						'required'          => true,
						'sanitize_callback' => 'esc_url_raw',
					),
					'format'   => array(
						'default'           => 'json',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'maxwidth' => array(
						'default'           => apply_filters( 'rest_oembed_default_width', 600 ),
						'sanitize_callback' => 'absint',
					),
				),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Callback for our API endpoint.
	 *
	 * Returns the JSON object for the post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( WP_REST_Request $request ) {
		$post_id = url_to_postid( $request['url'] );

		/**
		 * Filter the determined post id.
		 *
		 * @param int    $post_id The post ID.
		 * @param string $url     The requestd URL.
		 */
		$post_id = apply_filters( 'rest_oembed_request_post_id', $post_id, $request['url'] );

		if ( 0 === $post_id ) {
			return new WP_Error( 'rest_oembed_invalid_url', __( 'Invalid URL.', 'oembed-api' ), array( 'status' => 404 ) );
		}

		// Todo: Perhaps just default to json if something invalid is provided.
		if ( ! in_array( $request['format'], array( 'json', 'xml' ) ) ) {
			return new WP_Error( 'rest_oembed_invalid_format', __( 'Invalid format.', 'oembed-api' ), array( 'status' => 501 ) );
		}

		return get_oembed_response_data( $post_id, $request['maxwidth'] );
	}

	/**
	 * Get the item's schema for display / public consumption purposes, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'oEmbed response',
			'type'       => 'object',
			'properties' => array(
				'type'          => array(
					'description' => 'The resource type. Either photo, video, link, or rich.',
					'type'        => 'string',
				),
				'version'       => array(
					'description' => 'The oEmbed version number. Always 1.0.',
					'type'        => 'number',
				),
				'width'         => array(
					'description' => 'Width of the embeddable post',
					'type'        => 'integer',
				),
				'height'        => array(
					'description' => 'Height of the embeddable post',
					'type'        => 'integer',
				),
				'title'         => array(
					'description' => 'The title for the object.',
					'type'        => 'string',
				),
				'url'           => array(
					'description' => 'URL to the object.',
					'type'        => 'string',
					'format'      => 'uri',
				),
				'html'           => array(
					'description' => 'HTML to embed the object.',
					'type'        => 'string',
				),
				'author_name'   => array(
					'description' => 'The name of the object\'s author.',
					'type'        => 'string',
				),
				'author_url'    => array(
					'description' => 'URL to the object\'s author.',
					'type'        => 'string',
					'format'      => 'uri',
				),
				'provider_name' => array(
					'description' => 'The name of the object\'s provider.',
					'type'        => 'string',
				),
				'provider_url'  => array(
					'description' => 'URL to the object\'s provider.',
					'type'        => 'string',
					'format'      => 'uri',
				),
			),
		);

		return $schema;
	}
}
