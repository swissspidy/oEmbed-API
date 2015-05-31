<?php
defined( 'WPINC' ) or die;

class WP_API_oEmbed_Plugin extends WP_Stack_Plugin2 {

	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * Plugin version.
	 */
	const VERSION = '0.1.0';

	/**
	 * Constructs the object, hooks in to `plugins_loaded`.
	 */
	protected function __construct() {
		$this->hook( 'plugins_loaded', 'add_hooks' );
	}

	/**
	 * Adds hooks.
	 */
	public function add_hooks() {
		$this->hook( 'init' );

		$this->hook( 'rest_api_init', 'register_routes' );
		$this->hook( 'wp_head', 'oembed_api_discovery_links' );
	}

	/**
	 * Initializes the plugin, registers textdomain, etc.
	 */
	public function init() {
		$this->load_textdomain( 'wp-api-oembed', '/languages' );
	}

	public function register_routes() {
		register_rest_route( 'oembed/v1', '/oembed', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_oembed_response' ),
		) );
	}

	public function oembed_api_discovery_links() {
		if ( ! function_exists( 'get_rest_url' ) ) {
			return;
		}

		$output = '';

		if ( is_singular() ) {
			$output .= '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_rest_url( null, 'oembed/v1/oembed/?url=' . get_permalink() ) ) . '" />' . "\n";
		}

		$output = apply_filters( 'oembed_api_discovery_links', $output );

		echo $output;
	}

	public function get_oembed_response( WP_REST_Request $data ) {
		$query_params = $data->get_query_params();

		if ( empty( $query_params['url'] ) ) {
			return new WP_Error( 'json_oembed_no_url', __( 'No URL provided.', 'oembed-api' ), array( 'status' => 404 ) );
		}

		$post_id = url_to_postid( $query_params['url'] );

		if ( 0 === $post_id ) {
			return new WP_Error( 'json_oembed_invalid_url', __( 'Invalid URL.', 'oembed-api' ), array( 'status' => 404 ) );
		}

		/** @var WP_Post $post */
		$post = get_post( $post_id );

		/** @var WP_User $author */
		$author = get_userdata( $post->post_author );

		$response_data = apply_filters( 'oembed_api_response_data', array(
			'version'       => '1.0',
			'provider_name' => get_bloginfo( 'name' ),
			'provider_url'  => get_home_url(),
			'author_name'   => $author->display_name,
			'author_url'    => get_author_posts_url( $author->ID, $author->nicename ),
			'title'         => $post->post_title,
			'type'          => 'rich',
			'html'          => apply_filters( 'the_content', $post->post_content ),
		) );

		if ( has_post_thumbnail( $post->ID ) ) {
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );

			$response_data['thumbnail_url']    = $thumbnail[0];
			$response_data['thumbnail_width']  = $thumbnail[1];
			$response_data['thumbnail_height'] = $thumbnail[2];
		}
		if ( 'attachment' === $post->post_type && wp_attachment_is_image( $post->ID ) ) {
			$response_data['type'] = 'photo';
			unset( $response_data['html'] );

			$thumbnail = wp_get_attachment_image_src( $post->ID, 'full' );

			$response_data['thumbnail_url']    = $thumbnail[0];
			$response_data['thumbnail_width']  = $thumbnail[1];
			$response_data['thumbnail_height'] = $thumbnail[2];
		}

		$response = new WP_REST_Response();
		$response->set_data( $response_data );

		return $response;
	}
}
