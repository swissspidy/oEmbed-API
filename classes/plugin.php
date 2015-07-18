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
	 * Instance of our frontend class.
	 *
	 * @var WP_API_oEmbed_Frontend
	 */
	protected $frontendClass;

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

		// Whitelist this site as an oEmbed provider.
		$this->hook( 'init', 'add_oembed_provider' );

		// Configure the REST API route.
		add_action( 'rest_api_init', array( new WP_API_oEmbed_Endppoint(), 'register_routes' ) );

		// Add a rewrite endpoint for the iframe.
		$this->hook( 'init', 'add_rewrite_endpoint' );

		// Setup our frontend facing component.
		$this->frontendClass = new WP_API_oEmbed_Frontend();

		add_action( 'wp_head', array( $this->frontendClass, 'add_oembed_discovery_links' ) );
		add_action( 'template_redirect', array( $this->frontendClass, 'template_redirect' ) );
		add_action( 'rest_oembed_output', array( $this->frontendClass, 'rest_oembed_output' ) );
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
	 * Add this site as an oEmbed provider for testing purposes.
	 */
	public function add_oembed_provider() {
		if ( ! function_exists( 'rest_url' ) ) {
			return;
		}

		wp_oembed_add_provider( home_url( '/*' ), esc_url( rest_url( 'wp/v2/oembed' ) ) );
	}
}
