<?php
/**
 * Main plugin class.
 *
 * @package WP_API_oEmbed
 */

defined( 'WPINC' ) or die;

/**
 * Class WP_API_oEmbed_Plugin
 */
class WP_API_oEmbed_Plugin extends WP_Stack_Plugin2 {
	/**
	 * Instance of this class.
	 *
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

		$this->hook( 'init', 'add_oembed_provider' );
		$this->hook( 'rest_api_init', 'register_routes' );

		$this->hook( 'init', 'add_rewrite_endpoint' );
		$this->hook( 'wp_head', 'add_oembed_discovery_links' );
		$this->hook( 'template_redirect' );
		$this->hook( 'rest_oembed_output' );
	}

	/**
	 * Initializes the plugin, registers textdomain, etc.
	 */
	public function init() {
		$this->load_textdomain( 'oembed-api', '/languages' );
	}

	/**
	 * Add our rewrite endpoint to permalinks.
	 */
	public function add_rewrite_endpoint() {
		add_rewrite_endpoint( 'embed', EP_PERMALINK );
	}

	/**
	 * Add our rewrite endpoint on plugin activation.
	 */
	public function activate_plugin() {
		$this->add_rewrite_endpoint();
		flush_rewrite_rules();
	}

	/**
	 * Flush rewrite rules on plugin deactivation.
	 */
	public function deactivate_plugin() {
		flush_rewrite_rules();
	}

	/**
	 * Output the embeddable HTML.
	 *
	 * @todo Is there a better / faster way?
	 */
	public function template_redirect() {
		global $wp_query, $post;

		if ( isset( $wp_query->query_vars['embed'] ) ) {
			/**
			 * This gets executed when someone embeds a post.
			 *
			 * @param WP_Post $post The current post object.
			 */
			do_action( 'rest_oembed_output', $post );
			exit;
		}
	}

	/**
	 * Add this site as an oEmbed provider for testing purposes.
	 */
	public function add_oembed_provider() {
		if ( ! function_exists( 'rest_url' ) ) {
			return;
		}

		wp_oembed_add_provider( home_url( '/*' ), esc_url( rest_url( 'wp/v2/oembed' ) ) );
	}

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
	}

	/**
	 * Add oEmbed discovery links in the website <head>.
	 */
	public function add_oembed_discovery_links() {
		if ( ! function_exists( 'get_rest_url' ) ) {
			return;
		}

		$output = '';

		if ( is_singular() ) {
			$output .= '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_rest_url( null, 'oembed/v1/oembed/?url=' . get_permalink() ) ) . '" />' . "\n";
		}

		$output = apply_filters( 'rest_oembed_discovery_links', $output );

		echo $output;
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

		if ( 'json' !== $request['format'] ) {
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
		$embed_url = add_query_arg( array( 'embed' => true ), get_permalink( $post ) );

		if ( get_option( 'permalink_structure' ) ) {
			$embed_url = trailingslashit( get_permalink( $post ) ) . user_trailingslashit( 'embed' );
		}

		$output = sprintf(
			'<iframe src="%1$s" width="%2$d" height="%3$d" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>',
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
	 * Output the HTML that gets embedded.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function rest_oembed_output( $post ) {
		if ( is_a( $post, 'WP_Post' ) ) {
			echo esc_html( $post->post_title );
		}
	}
}
