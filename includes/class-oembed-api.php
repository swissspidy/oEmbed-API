<?php

/**
 * Class OEmbed_API
 */
class OEmbed_API {

	/**
	 * Server object
	 *
	 * @var WP_JSON_Server
	 */
	protected $server;

	/**
	 * Construct the API handler object.
	 *
	 * @param WP_JSON_Server $server
	 */
	public function __construct( WP_JSON_Server $server ) {

		$this->server = $server;

		add_filter( 'json_endpoints', array( $this, 'register_routes' ) );

	}

	/**
	 * Register the routes for the oEmbed API.
	 *
	 * @param array $routes WP-API routes.
	 *
	 * @return array Modified routes array.
	 */
	public function register_routes( $routes ) {

		$routes['/oembed'] = array(
			array(
				array( $this, 'get_oembed_response' ),
				WP_JSON_Server::READABLE,
			),
		);

		return $routes;

	}

	/**
	 * @param string $url      The url wanted to be embedded.
	 * @param string $callback Optional JSONP callback function name.
	 *
	 * @return WP_JSON_Response|WP_Error
	 */
	public function get_oembed_response( $url, $callback = '' ) {

		do_action( 'oembed_api_response_begin', $url, $callback );

		$id = url_to_postid( apply_filters( 'oembed_api_url', $url ) );

		if ( 0 === $id ) {
			return new WP_Error( 'json_oembed_invalid_url', __( 'Invalid URL.', 'oembed-api' ), array( 'status' => 404 ) );
		}

		/** @var array $post */
		$post = get_post( $id, ARRAY_A );

		// Link headers (see RFC 5988)

		$response = new WP_JSON_Response();
		$response->header( 'Last-Modified', mysql2date( 'D, d M Y H:i:s', $post['post_modified_gmt'] ) . 'GMT' );

		$post = $this->prepare_response( $post );

		$response->link_header( 'alternate', get_permalink( $id ), array( 'type' => 'text/html' ) );
		$response->set_data( $post );

		$callback = apply_filters( 'oembed_api_jsonp_callback', $callback, $url );

		// Enable JSONP
		if ( '' !== $callback ) {
			$_GET['_jsonp'] = $callback;
		}

		do_action( 'oembed_api_response_end', $url, $callback );

		return $response;

	}

	/**
	 * Prepare oEmbed response.
	 *
	 * @param array $post The unprepared post data
	 *
	 * @return array The prepared post data
	 */
	protected function prepare_response( $post ) {
		$post = get_post( $post['ID'] );
		setup_postdata( $post );

		$author = get_userdata( $post->post_author );

		$response = array(
			'version'       => '1.0',
			'provider_name' => get_bloginfo( 'name' ),
			'provider_url'  => get_home_url(),
			'author_name'   => $author->display_name,
			'author_url'    => get_author_posts_url( $author->ID, $author->nicename ),
			'title'         => $post->post_title,
			'type'          => 'rich',
			'html'          => apply_filters( 'the_content', $post->post_content ),
		);

		if ( has_post_thumbnail( $post->ID ) ) {
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );

			$response['thumbnail_url']    = $thumbnail[0];
			$response['thumbnail_width']  = $thumbnail[1];
			$response['thumbnail_height'] = $thumbnail[2];
		}

		if ( 'attachment' === $post->post_type && wp_attachment_is_image( $post->ID ) ) {
			$response['type'] = 'photo';
			unset( $response['html'] );

			$thumbnail = wp_get_attachment_image_src( $post->ID, 'full' );

			$response['thumbnail_url']    = $thumbnail[0];
			$response['thumbnail_width']  = $thumbnail[1];
			$response['thumbnail_height'] = $thumbnail[2];

		}

		return apply_filters( 'oembed_api_response', $response, $post );
	}

} 