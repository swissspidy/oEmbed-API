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
		if ( ! function_exists( 'get_rest_url' ) ) {
			return;
		}

		$output = '';

		if ( is_singular() ) {
			$output .= '<link rel="alternate" type="application/json+oembed" href="' . esc_url( rest_url( 'wp/v2/oembed?url=' . get_permalink() ) ) . '" />' . "\n";
		}

		$output = apply_filters( 'rest_oembed_discovery_links', $output );

		echo $output;
	}

	/**
	 * Output the HTML that gets embedded.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function rest_oembed_output( $post ) {
		$post_content = strip_tags( $post->post_content );
		$words        = str_word_count( $post_content );
		$oembed_content = $post_content;

		if ( count( $words ) > 35 ) {
			$more_words     = count( $words ) - 30;
			$oembed_content = implode( ' ', array_slice( $words, 0, 30 ) );
			$oembed_content .= sprintf( ' <span class="oembed-words>"(' . _n( '%d word', '%d words', $more_words ) . ')</span>', $more_words );
		}
		?>
		<html>
		<head>
			<title><?php esc_html_e( $post->post_title ); ?></title>
			<style type="text/css">
				html, body {
					padding: 0;
					margin: 0;
				}

				body {
					border: 1px solid #aaa;
					padding: 20px;
					font-family: sans-serif;
				}

				.oembed-header h1 {
					font-size: 1.5em;
				}

				.oembed-content {
					font-size: 0.9em;
					color: #aaa;
				}

				.oembed-content .oembed-words {
					color: #ccc;
				}

				.oembed-footer-site {
					float: left;
					color: #777;
				}

				.oembed-footer-site img {
					height: 32px;
					width: 32px;
				}

				.oembed-footer-comments {
					float: right;
				}

				.oembed-footer-comments .comments::after {
					content: '';
					display: inline-block;
					background: url('<?php echo admin_url( 'images/comment-grey-bubble.png' ); ?>') 3px 5px no-repeat;
					height: 16px;
					width: 16px;
				}
			</style>
		</head>
		<body>
		<div class="oembed-header">
			<h1><?php echo esc_html( $post->post_title ); ?></h1>
		</div>
		<div class="oembed-content"><?php echo $oembed_content; ?></div>
		<div class="oembed-footer">
			<div class="oembed-footer-site">
				<img
					src="<?php echo esc_url( get_site_icon_url( null, 32, admin_url( 'images/w-logo-blue.png' ) ) ); ?>"/>
				<?php bloginfo( 'name' ); ?>
			</div>
			<div class="oembed-footer-comments">
				<span class="comments"><?php echo get_comments_number( $post->ID ); ?></span>
				<span class="sharing">LOL</span>
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

			// Turn the asterisk-type provider URLs into regex
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
