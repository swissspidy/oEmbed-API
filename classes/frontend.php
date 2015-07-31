<?php
/**
 * User-facing components.
 *
 * @package WP_API_oEmbed
 */

defined( 'WPINC' ) or die;

/**
 * Class WP_API_oEmbed_Frontend
 */
class WP_API_oEmbed_Frontend {
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
	 * Add oEmbed discovery links in the website <head>.
	 */
	public function add_oembed_discovery_links() {
		$output = '';

		if ( is_singular() ) {
			$output .= '<link rel="alternate" type="application/json+oembed" href="' . esc_url( rest_url( 'wp/v2/oembed?url=' . get_permalink() ) ) . '" />' . "\n";
			$output .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( rest_url( 'wp/v2/oembed?url=' . get_permalink() . '&format=xml' ) ) . '" />' . "\n";
		}

		$output = apply_filters( 'rest_oembed_discovery_links', $output );

		echo $output;
	}

	/**
	 * Print the CSS used to style the embed output.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	protected function rest_oembed_output_css() {
		?>
		<style type="text/css">
			html, body {
				padding: 0;
				margin: 0;
			}

			body {
				font-family: sans-serif;
			}

			/* Text meant only for screen readers */
			.screen-reader-text {
				clip: rect(1px, 1px, 1px, 1px);
				height: 1px;
				overflow: hidden;
				position: absolute !important;
				width: 1px;
			}

			.wp-embed {
				width: 100%;
				padding: 25px 30px;
				font: 400 14px/1.5 'Open Sans', sans-serif;
				color: #82878c;
				background: white;
				border: 1px solid #e5e5e5;
				box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
				box-sizing: border-box;
				/* Clearfix */
				overflow: auto;
				zoom: 1;
			}

			.wp-embed a {
				color: #82878c;
				text-decoration: none;
			}

			.wp-embed a:hover {
				text-decoration: underline;
			}

			.wp-embed-heading {
				margin: 0 0 15px;
				font-weight: bold;
				font-size: 22px;
				line-height: 1.3;
				color: #32373c;
			}

			.wp-embed p {
				margin: 0;
			}

			.wp-embed-more {
				color: #b4b9be;
			}

			.wp-embed-meta,
			.wp-embed-social {
				margin-top: 30px;
				width: 50%;
			}

			.wp-embed-meta {
				float: left;
			}

			.wp-embed-site-icon {
				float: left;
				height: 25px;
				width: 25px;
			}

			.wp-embed-site-title {
				float: left;
				margin-top: 2px;
				margin-left: 10px;
				font-weight: bold;
			}

			.wp-embed-social {
				float: right;
				text-align: right;
			}

			.wp-embed-comments,
			.wp-embed-share {
				display: inline;
			}

			.wp-embed-share {
				margin-left: 10px;
			}

			.wp-embed-comments span:before,
			.wp-embed-share:before {
				display: inline-block;
				width: 20px;
				height: 20px;
				font-size: 20px;
				line-height: 1;
				font-family: dashicons;
				text-decoration: inherit;
				font-weight: 400;
				font-style: normal;
				vertical-align: top;
				text-align: center;
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
			}

			.wp-embed-comments span:before {
				margin-top: 2px;
				content: "\f101";
			}

			.wp-embed-share:before {
				margin-top: 1px;
				content: "\f237";
			}
		</style>
		<?php
	}

	/**
	 * Output the HTML that gets embedded
	 *
	 * @todo Use `.screen-reader-text` where needed.
	 * @todo Add hooks.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function rest_oembed_output( $post ) {
		$post_content = strip_tags( $post->post_content );
		$words        = str_word_count( $post_content );

		if ( count( $words ) > 35 ) {
			$more_words   = count( $words ) - 30;
			$post_content = implode( ' ', array_slice( $words, 0, 30 ) );
			$post_content .= sprintf( ' <span class="wp-embed-more>"(' . _n( '%d word', '%d words', $more_words, 'oembed-api' ) . ')</span>', $more_words );
		}
		?>
		<html>
		<head>
			<title><?php esc_html_e( $post->post_title, 'oembed-api' ); ?></title>
			<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>
			<link rel="stylesheet" href="https://s.w.org/wp-includes/css/dashicons.css"/>
			<?php $this->rest_oembed_output_css(); ?>
		</head>
		<body>
		<div class="wp-embed">
			<h1 class="wp-embed-heading"><?php echo esc_html( $post->post_title ); ?></h1>

			<p class="wp-embed-excerpt"><?php echo $post_content; ?></p>

			<div class="wp-embed-meta">
				<?php
				printf(
					'<img src="%s" width="32" height="32" alt="" class="wp-embed-site-icon"/>',
					esc_url( get_site_icon_url( null, 32, admin_url( 'images/w-logo-blue.png' ) ) )
				);
				?>
				<div class="wp-embed-site-title">
					<?php printf( '<a href="%s">%s</a>', esc_url( home_url() ), bloginfo( 'name' ) ); ?>
				</div>
			</div>
			<div class="wp-embed-social">
				<div class="wp-embed-comments">
					<span><?php echo esc_html( get_comments_number( $post->ID ) ); ?></span>
				</div>
				<div class="wp-embed-share">
				</div>
			</div>
		</div>
		</body>
		</html>
		<?php
	}


	/**
	 * If the $url isn't on the trusted providers list, we need to filter the HTML heavily for security.
	 *
	 * @param string $html The unfiltered oEmbed HTML.
	 * @param string $url  URL of the content to be embedded.
	 *
	 * @return string The filtered oEmbed HTML.
	 */
	public function filter_oembed_result( $html, $url ) {
		$wp_oembed = _wp_oembed_get_object();

		$trusted = false;

		foreach ( $wp_oembed->providers as $matchmask => $data ) {
			$regex = $data[1];

			// Turn the asterisk-type provider URLs into regex.
			if ( ! $regex ) {
				$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';
				$matchmask = preg_replace( '|^#http\\\://|', '#https?\://', $matchmask );
			}

			if ( preg_match( $matchmask, $url ) ) {
				$trusted = true;
				break;
			}
		}

		$allowed_html = array(
			'iframe' => array(
				'src'          => true,
				'width'        => true,
				'height'       => true,
				'frameborder'  => true,
				'marginwidth'  => true,
				'marginheight' => true,
				'scrolling'    => true,
			),
		);

		if ( ! $trusted ) {
			$html = wp_kses( $html, $allowed_html );
			$html = str_replace( '<iframe', '<iframe sandbox="" security="restricted"', $html );
		}

		return $html;
	}
}
