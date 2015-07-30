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
class WP_API_oEmbed_Plugin {
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
	 * Protected constructor.
	 */
	protected function __construct() {
	}

	/**
	 * Returns the plugin's object instance.
	 *
	 * @return self The plugin object instance.
	 */
	public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new self();
		}

		return static::$instance;
	}

	/**
	 * Adds hooks.
	 */
	public function add_hooks() {
		// Load the plugin textdomain.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Whitelist this site as an oEmbed provider.
		add_action( 'init', array( $this, 'add_oembed_provider' ) );

		// Configure the REST API route.
		add_action( 'rest_api_init', array( new WP_API_oEmbed_Endppoint(), 'register_routes' ) );

		// Add a rewrite endpoint for the iframe.
		add_action( 'init', array( $this, 'add_rewrite_endpoint' ) );

		// Setup our frontend facing component.
		$this->frontendClass = new WP_API_oEmbed_Frontend();

		add_action( 'wp_head', array( $this->frontendClass, 'add_oembed_discovery_links' ) );
		add_action( 'template_redirect', array( $this->frontendClass, 'template_redirect' ) );
		add_action( 'rest_oembed_output', array( $this->frontendClass, 'rest_oembed_output' ) );

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
