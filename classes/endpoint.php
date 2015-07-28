<?php
/**
 * REST API endpoint configuration.
 *
 * @package WP_API_oEmbed
 */

defined( 'WPINC' ) or die;

/**
 * Class WP_API_oEmbed_Endppoint
 */
class WP_API_oEmbed_Endppoint {
	/**
	 * Register the API routes.
	 */
	public function register_routes() {
		register_rest_route( 'wp/v2', '/oembed', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_oembed_response' ),
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

		add_filter( 'rest_pre_serve_request', array( $this, 'rest_pre_serve_request' ), 10, 4 );
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
	public function get_oembed_response( WP_REST_Request $request ) {
		$post_id = url_to_postid( $request['url'] );

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
			'html'          => $this->get_oembed_html( $post, $width, $height ),
		) );

		return $data;
	}

	/**
	 * Get the HTML output for the oEmbed response.
	 *
	 * @param WP_Post $post   The current post object.
	 * @param int     $width  The width for the response.
	 * @param int     $height The height for the response.
	 *
	 * @return string
	 */
	protected function get_oembed_html( $post, $width, $height ) {
		$embed_url = get_post_embed_url( $post );

		$output = sprintf(
			'<iframe sandbox="" security="restricted" src="%1$s" width="%2$d" height="%3$d" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>',
			esc_url( $embed_url ),
			$width,
			$height
		);

		/**
		 * Filters the oEmbed HTML output.
		 *
		 * @param string  $output The default HTML.
		 * @param WP_Post $post   Current post object.
		 * @param int     $width  Width of the response.
		 * @param int     $height Height of the response.
		 */
		$output = apply_filters( 'rest_oembed_html', $output, $post, $width, $height );

		return $output;
	}

	/**
	 * Hooks into the REST API output to print XML instead of JSON.
	 *
	 * @param bool                      $served  Whether the request has already been served.
	 * @param WP_HTTP_ResponseInterface $result  Result to send to the client. Usually a WP_REST_Response.
	 * @param WP_REST_Request           $request Request used to generate the response.
	 * @param WP_REST_Server            $server  Server instance.
	 *
	 * @return bool
	 */
	public function rest_pre_serve_request( $served, $result, $request, $server ) {
		$params = $request->get_params();

		if ( '/wp/v2/oembed' !== $request->get_route() || ! 'xml' === $params['format'] ) {
			return $served;
		}

		if ( 'HEAD' === $request->get_method() ) {
			return $served;
		}

		$server->send_header( 'Content-Type', 'text/xml; charset=' . get_option( 'blog_charset' ) );

		// Embed links inside the request.
		$result = $server->response_to_data( $result, false );

		$oembed = new SimpleXMLElement( '<oembed></oembed>' );
		foreach ( $result as $key => $value ) {
			if ( is_array( $value ) ) {
				$element = $oembed->addChild( $key );

				foreach ( $value as $k => $v ) {
					$element->addChild( $k, $v );
				}

				continue;
			}

			$oembed->addChild( $key, $value );
		}
		echo $oembed->asXML();

		return $served = true;
	}
}
