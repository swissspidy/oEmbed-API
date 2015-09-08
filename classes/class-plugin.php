<?php
/**
 * Main plugin class.
 *
 * @package WP_oEmbed
 */

defined( 'WPINC' ) or die;

/**
 * Class WP_oEmbed_Plugin
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class WP_oEmbed_Plugin {
	/**
	 * Instance of our frontend class.
	 *
	 * @var WP_API_oEmbed_Frontend
	 */
	protected $frontendClass;

	/**
	 * Adds hooks.
	 */
	public function add_hooks() {
		// Load the plugin textdomain.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Whitelist this site as an oEmbed provider.
		add_action( 'init', array( $this, 'add_oembed_provider' ) );

		// Register scripts.
		add_action( 'init', array( $this, 'register_scripts' ) );

		// Configure the REST API route.
		add_action( 'rest_api_init', array( new WP_REST_oEmbed_Controller(), 'register_routes' ) );

		// Filter the REST API response to output XML if requested.
		add_filter( 'rest_pre_serve_request', array( $this, 'rest_pre_serve_request' ), 10, 4 );

		// Filter the oEmbed XML response to create an XML string.
		add_filter( 'rest_oembed_xml_response', array( $this, 'create_xml' ), 10, 2 );

		// Add a rewrite endpoint for the iframe.
		add_action( 'init', array( $this, 'add_rewrite_endpoint' ) );

		// Register our TinyMCE plugin.
		add_action( 'mce_external_plugins', array( $this, 'add_mce_plugin' ) );

		// Enqueue the resize script when the editor is also enqueued.
		add_action( 'wp_enqueue_editor', array( $this, 'load_mce_script' ) );

		// Setup our frontend facing component.
		$this->frontendClass = new WP_oEmbed_Frontend();

		add_action( 'wp_head', array( $this->frontendClass, 'add_oembed_discovery_links' ) );
		add_action( 'template_redirect', array( $this->frontendClass, 'template_redirect' ) );
		add_action( 'rest_oembed_output', array( $this->frontendClass, 'rest_oembed_output' ) );

		add_action( 'wp_head', array( $this->frontendClass, 'add_host_js' ) );

		add_filter( 'oembed_result', array( $this->frontendClass, 'filter_oembed_result' ), 10, 2 );

		add_filter( 'embed_oembed_discover', '__return_true' );
	}

	/**
	 * Load the plugin textdomain.
	 * @return bool
	 */
	public function load_textdomain() {
		return load_plugin_textdomain( 'oembed-api', false, basename( dirname( plugin_dir_path( __FILE__ ) ) ) . '/languages' );
	}

	/**
	 * Add our rewrite endpoint to permalinks and pages.
	 */
	public function add_rewrite_endpoint() {
		add_rewrite_endpoint( 'embed', EP_PERMALINK | EP_PAGES | EP_ATTACHMENT );
	}

	/**
	 * Add this site to the whitelist of oEmbed providers.
	 */
	public function add_oembed_provider() {
		wp_oembed_add_provider( home_url( '/*' ), get_oembed_endpoint_url() );
	}

	/**
	 * Register our scripts.
	 */
	public function register_scripts() {
		wp_register_script( 'autoembed', plugins_url( 'scripts/frontend.js', dirname( __FILE__ ) ) );
	}

	/**
	 * Register our TinyMCE plugin
	 *
	 * @param array $plugins List of current TinyMCE plugins.
	 *
	 * @return array
	 */
	public function add_mce_plugin( $plugins ) {
		$plugins['autoembed'] = plugins_url( 'tinymce/plugin.js', dirname( __FILE__ ) );

		return $plugins;
	}

	/**
	 * Load the resize script in the main window when TinyMCE is loaded, so that the
	 * embed popup can also resize the iframe correctly.
	 *
	 * @param array $opts TinyMCE options.
	 */
	public function load_mce_script( $opts ) {
		if ( $opts['tinymce'] ) {
			wp_enqueue_script( 'autoembed' );
		}
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

		if ( '/wp/v2/oembed' !== $request->get_route() || 'GET' !== $request->get_method() ) {
			return $served;
		}

		if ( ! isset( $params['format'] ) || 'xml' !== $params['format'] ) {
			return $served;
		}

		// Embed links inside the request.
		$data = $server->response_to_data( $result, false );

		if ( 404 === $result->get_status() ) {
			$data = $data [0];
		}

		/**
		 * Filter the XML response.
		 *
		 * @param string $result The built XML.
		 * @param array  $data   The original oEmbed response data.
		 */
		$result = apply_filters( 'rest_oembed_xml_response', false, $data );

		// Bail if there's no XML.
		if ( ! $result ) {
			status_header( 501 );
			die( 'Not implemented' );
		}

		if ( ! headers_sent() ) {
			$server->send_header( 'Content-Type', 'text/xml; charset=' . get_option( 'blog_charset' ) );
		}

		echo $result;

		return true;
	}

	/**
	 * Create an XML string from the oEmbed response data
	 *
	 * @param string|false $result The XML response string.
	 * @param array        $data   The original oEmbed response data.
	 *
	 * @return string|false
	 */
	public function create_xml( $result, $data ) {
		$oembed = new SimpleXMLElement( '<oembed></oembed>' );

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$element = $oembed->addChild( $key );

				foreach ( $value as $k => $v ) {
					$element->addChild( $k, $v );
				}

				continue;
			}

			$oembed->addChild( $key, $value );
		}

		$result = $oembed->asXML();

		return $result;
	}
}
