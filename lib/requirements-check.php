<?php

/**
 * Simple requirements checking class.
 */
class WP_API_oEmbed_Requirements_Check {
	private $title = '';
	private $php = '5.2.4';
	private $wp = '3.8';
	private $rest_api = '2.0';
	private $file;

	/**
	 * Constructor.
	 *
	 * @param array $args An array of arguments to overwrite the default requirements.
	 */
	public function __construct( $args ) {
		foreach ( array( 'title', 'php', 'wp', 'rest-api', 'file' ) as $setting ) {
			if ( isset( $args[ $setting ] ) ) {
				$this->$setting = $args[ $setting ];
			}
		}
	}

	/**
	 * @return bool True if the install passes the requirements, false otherwise.
	 */
	public function passes() {
		$passes = $this->php_passes() && $this->wp_passes() && $this->rest_api_passes();
		if ( ! $passes ) {
			add_action( 'admin_notices', array( $this, 'deactivate' ) );
		}

		return $passes;
	}

	/**
	 * Deactivate the plugin again.
	 */
	public function deactivate() {
		if ( isset( $this->file ) ) {
			deactivate_plugins( plugin_basename( $this->file ) );
		}
	}

	/**
	 * @return bool True if the PHP version is high enough, false otherwise.
	 */
	private function php_passes() {
		if ( $this->__php_at_least( $this->php ) ) {
			return true;
		} else {
			add_action( 'admin_notices', array( $this, 'php_version_notice' ) );

			return false;
		}
	}

	/**
	 * Compare the current PHP version with the minimum required version.
	 */
	private static function __php_at_least( $min_version ) {
		return version_compare( phpversion(), $min_version, '>=' );
	}

	/**
	 * Show the PHP version notice.
	 */
	public function php_version_notice() {
		?>
		<div class="error">
			<p><?php printf( 'The &#8220;%s&#8221; plugin cannot run on PHP versions older than %s. Please contact your host and ask them to upgrade.', esc_html( $this->title ), $this->php ); ?></p>
		</div>
		<?php
	}

	/**
	 * @return bool True if the WordPress version is high enough, false otherwise.
	 */
	private function wp_passes() {
		if ( $this->__wp_at_least( $this->wp ) ) {
			return true;
		} else {
			add_action( 'admin_notices', array( $this, 'wp_version_notice' ) );

			return false;
		}
	}

	/**
	 * Compare the current WordPress version with the minimum required version.
	 */
	private static function __wp_at_least( $min_version ) {
		return version_compare( get_bloginfo( 'version' ), $min_version, '>=' );
	}

	/**
	 * SHow the WordPress version notice.
	 */
	public function wp_version_notice() {
		?>
		<div class="error">
			<p><?php printf( 'The &#8220;%s&#8221; plugin cannot run on WordPress versions older than %s. Please update WordPress.', esc_html( $this->title ), $this->wp ); ?></p>
		</div>
		<?php
	}

	/**
	 * @return bool True if the REST API version is high enough, false otherwise.
	 */
	private function rest_api_passes() {
		if ( $this->__rest_api_at_least( $this->rest_api ) ) {
			return true;
		} else {
			add_action( 'admin_notices', array( $this, 'rest_api_version_notice' ) );

			return false;
		}
	}

	/**
	 * Compare the current version of the REST API with the minimum required version.
	 */
	private static function __rest_api_at_least( $min_version ) {
		if ( ! defined( 'REST_API_VERSION' ) ) {
			return false;
		}

		return version_compare( REST_API_VERSION, $min_version, '>=' );
	}

	/**
	 * Show the REST API version notice.
	 */
	public function rest_api_version_notice() {
		?>
		<div class="error">
			<p><?php printf( 'The &#8220;%s&#8221; plugin requires version %s or higher of the REST API to be installed. Please update or install the REST API plugin.', esc_html( $this->title ), $this->rest_api ); ?></p>
		</div>
		<?php
	}
}
