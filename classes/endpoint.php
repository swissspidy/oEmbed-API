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
					'default'           => absint( apply_filters( 'rest_oembed_default_width', 600 ) ),
					'sanitize_callback' => 'absint',
				),
			),
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

		/**
		 * Current post object.
		 *
		 * @var WP_Post $post
		 */
		$post = get_post( $post_id );

		/**
		 * User object for the post author.
		 *
		 * @var WP_User $author
		 */
		$author = get_userdata( $post->post_author );

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

		$width = $request['maxwidth'];

		if ( $width < $minwidth ) {
			$width = $minwidth;
		} else if ( $width > $maxwidth ) {
			$width = $maxwidth;
		}

		// Todo: this shouldn't be hardcoded.
		$height = ceil( $width / 16 * 9 );

		/**
		 * Filters the oEmbed response data.
		 *
		 * @param array $data The response data.
		 */
		$data = apply_filters( 'rest_oembed_response_data', array(
			'version'       => '1.0',
			'provider_name' => get_bloginfo( 'name' ),
			'provider_url'  => get_home_url(),
			'author_name'   => $author->display_name,
			'author_url'    => get_author_posts_url( $author->ID, $author->user_nicename ),
			'title'         => $post->post_title,
			'type'          => 'rich',
			'width'         => $width,
			'height'        => $height,
			'html'          => get_post_embed_html( $post, $width, $height ),
		) );

		return $data;
	}
}
