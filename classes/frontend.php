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
		$words = str_word_count( $post_content );
		if ( count( $words ) > 35 ) {
			$more_words = count( $words ) - 30;
			$oembed_content = implode( ' ', array_slice( $words, 0, 30 ) );
			$oembed_content .= sprintf( ' <span class="oembed-words>"(' . _n( '%d word', '%d words', $more_words ) . ')</span>', $more_words );
		} else {
			$oembed_content = $post_content;
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
				height: 338px;
				width: 600px;
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
			<h1><?php esc_html_e( $post->post_title ); ?></h1>
		</div>
		<div class="oembed-content"><?php echo $oembed_content; ?></div>
		<div class="oembed-footer">
			<div class="oembed-footer-site">
				<img src="<?php echo esc_url( get_site_icon_url( null, 32, admin_url( 'images/w-logo-blue.png' ) ) ); ?>" />
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
}
