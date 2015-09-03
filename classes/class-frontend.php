<?php
/**
 * User-facing components.
 *
 * @package WP_oEmbed
 */

defined( 'WPINC' ) or die;

/**
 * Class WP_oEmbed_Frontend
 */
class WP_oEmbed_Frontend {
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
			$output .= '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_oembed_endpoint_url( get_permalink() ) ) . '" />' . "\n";
			$output .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( get_oembed_endpoint_url( get_permalink(), 'xml' ) ) . '" />' . "\n";
		}

		$output = apply_filters( 'rest_oembed_discovery_links', $output );

		echo $output;
	}

	/**
	 * Add JS to handle the messages from the embedded iframes.
	 *
	 * @todo: Think of a better way to restrict the height.
	 */
	public function add_host_js() {
		?>
		<script type="text/javascript">
			<?php readfile( dirname( dirname( __FILE__ ) ) . '/scripts/frontend.js' ); ?>
		</script>
		<?php
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
				padding: 25px;
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

			.wp-embed-featured-image {
				margin-bottom: 20px;
			}

			.wp-embed-featured-image img {
				width: 100%;
				height: auto;
			}

			.wp-embed-heading {
				margin: 0 0 15px;
				font-weight: bold;
				font-size: 22px;
				line-height: 1.3;
			}

			.wp-embed-heading a {
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
				position: relative;
			}

			.wp-embed-site-icon {
				height: 25px;
				width: 25px;
			}

			.wp-embed-site-title {
				display: inline;
				margin-top: 2px;
				position: absolute;
				left: 35px;
				font-weight: bold;
			}

			.wp-embed-social {
				float: right;
				text-align: right;
			}

			.wp-embed-social .dashicons {
				-webkit-transition: color .1s ease-in;
				transition: color .1s ease-in;
			}

			.wp-embed-social a:hover {
				text-decoration: none;
				color: #0073aa;
			}

			.wp-embed-comments,
			.wp-embed-share {
				display: inline;
			}

			.wp-embed-share {
				margin-left: 10px;
			}

			.wp-embed-share-dialog {
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background-color: rgba(10, 10, 10, 0.8);
				text-align: center;
				color: #fff;
				opacity: 1;
				transition: opacity .25s ease-in-out;
				-moz-transition: opacity .25s ease-in-out;
				-webkit-transition: opacity .25s ease-in-out;
			}

			.wp-embed-share-dialog.hidden {
				opacity: 0;
				visibility: hidden;
			}

			.wp-embed-share-dialog-open,
			.wp-embed-share-dialog-close {
				background: transparent;
				border: none;
				cursor: pointer;
			}

			.wp-embed-share-dialog-open {
				color: #82878c;
			}

			.wp-embed-share-dialog-open:hover {
				color: #0073aa;
			}

			.wp-embed-share-dialog-open .dashicons {
				display: inline;
			}

			.wp-embed-share-dialog-close {
				position: absolute;
				top: 20px;
				right: 20px;
				color: #fff;
				font-size: 22px;
			}

			.wp-embed-share-dialog-close:hover {
				text-decoration: none;
			}

			.wp-embed-share-dialog-content {
				height: 100%;
				-webkit-transform-style: preserve-3d;
				-moz-transform-style: preserve-3d;
				transform-style: preserve-3d;
			}

			.wp-embed-share-dialog-text {
				position: relative;
				top: 50%;
				transform: translateY(-50%);
				padding: 0 20px;
			}

			.wp-embed-share-title {
				margin: 0 0 15px;
				font-weight: bold;
				font-size: 18px;
				line-height: 1.3;
			}

			.wp-embed-share-input {
				width: 100%;
				max-width: 600px;
				border: 0;
				height: 28px;
				padding: 0 5px;
			}
		</style>
		<?php
	}

	/**
	 * Print the JS used inside the iframe.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	protected function rest_oembed_output_js() {
		?>
		<script type="text/javascript">
			(function ( window, document ) {
				var hash, secret, share_dialog, embed, resize_limiter;

				window.onload = function () {
					share_dialog = document.getElementsByClassName('wp-embed-share-dialog')[0];

					// Select content when clicking on the input field.
					document.getElementsByClassName('wp-embed-share-input')[0].onclick = function () {
						this.select();
					};

					// Open the share dialog.
					document.getElementsByClassName('wp-embed-share-dialog-open')[0].onclick = function (e) {
						share_dialog.className = share_dialog.className.replace('hidden', '');
						e.preventDefault();
					};

					// Close the share dialog.
					document.getElementsByClassName('wp-embed-share-dialog-close')[0].onclick = function (e) {
						share_dialog.className += ' hidden';
						e.preventDefault();
					};

					if ( window.self === window.top ) {
						return;
					}

					hash = window.location.hash;
					secret = hash.replace( /.*secret=([\d\w]{10}).*/, '$1' );

					embed = document.getElementsByClassName( 'wp-embed' )[0];

					// Send this document's height to the parent (embedding) site.
					window.parent.postMessage( { 'message': 'height', 'value': embed.clientHeight + 2, 'secret': secret }, '*' );

					// Detect clicks to external (_top) links.
					var links = document.getElementsByTagName( 'a' );
					for ( var i = 0; i < links.length; i++ ) {
						if ( '_top' === links[ i ].getAttribute( 'target' ) ) {
							links[ i ].onclick = function ( e ) {
								if ( e.target.hasAttribute( 'href' ) ) {
									var href = e.target.getAttribute( 'href' );
								} else {
									var href = e.target.parentElement.getAttribute( 'href' );
								}

								// Send link target to the parent (embedding) site.
								window.parent.postMessage( {
									'message': 'link',
									'value': href,
									'secret': secret
								}, '*' );
								e.preventDefault();
							}
						}
					}
				};

				window.onresize = function () {
					if ( window.self === window.top ) {
						return;
					}

					// We need to limit how often we send the message, otherwise we're just wasting CPU.
					if ( resize_limiter ) {
						return;
					}
					resize_limiter = true;
					// Call onresize immediately, in case the resize finished before we got the final size.
					setTimeout( function () {
						resize_limiter = false;
						window.onresize();
					}, 50 );
					// Send this document's height to the parent (embedding) site.
					window.parent.postMessage( {
						'message': 'height',
						'value': embed.clientHeight + 2,
						'secret': secret
					}, '*' );
				};
			})( window, document );
		</script>
		<?php
	}

	/**
	 * Output the HTML that gets embedded
	 *
	 * @todo Use `.screen-reader-text` where needed.
	 * @todo Add hooks.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function rest_oembed_output( $post ) {
		$post_content = $post->post_content;

		$num_words = apply_filters( 'rest_oembed_output_excerpt_length', 35, $post );

		$total_words = preg_split( "/[\n\r\t ]+/", $post_content, - 1, PREG_SPLIT_NO_EMPTY );

		/*
		 * translators: If your word count is based on single characters (e.g. East Asian characters),
		 * enter 'characters_excluding_spaces' or 'characters_including_spaces'. Otherwise, enter 'words'.
		 * Do not translate into your own language.
		 */
		if ( strpos( _x( 'words', 'Word count type. Do not translate!', 'oembed-api' ), 'characters' ) === 0 && preg_match( '/^utf\-?8$/i', get_option( 'blog_charset' ) ) ) {
			$text = trim( preg_replace( "/[\n\r\t ]+/", ' ', $post_content ), ' ' );
			preg_match_all( '/./u', $text, $total_words );
		}

		$more = sprintf(
			_n( '&hellip; (%d word)', '&hellip; (%d words)', count( $total_words ), 'oembed-api' ),
			count( $total_words )
		);

		$post_content = wp_trim_words( $post_content, $num_words, ' <span class="wp-embed-more">' . $more . '</span>' );
		?>
		<html>
		<head>
			<title><?php esc_html_e( $post->post_title, 'oembed-api' ); ?></title>
			<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>
			<link rel="stylesheet" href="https://s.w.org/wp-includes/css/dashicons.css"/>
			<?php $this->rest_oembed_output_css(); ?>
			<?php $this->rest_oembed_output_js(); ?>
		</head>
		<body>
		<div class="wp-embed">
			<?php if ( has_post_thumbnail() ) : ?>
			<div class="wp-embed-featured-image">
				<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" target="_top">
					<?php the_post_thumbnail( array( 600, 340 ) ); ?>
				</a>
			</div>
			<?php endif; ?>

			<h1 class="wp-embed-heading">
				<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" target="_top">
					<?php echo esc_html( $post->post_title ); ?>
				</a>
			</h1>

			<p class="wp-embed-excerpt"><?php echo $post_content; ?></p>

			<div class="wp-embed-meta">
				<?php
				printf(
					'<img src="%s" width="32" height="32" alt="" class="wp-embed-site-icon"/>',
					esc_url( get_site_icon_url( 32, admin_url( 'images/w-logo-blue.png' ) ) )
				);
				?>
				<div class="wp-embed-site-title">
					<?php printf( '<a href="%s" target="_top">%s</a>', esc_url( home_url() ), get_bloginfo( 'name' ) ); ?>
				</div>
			</div>
			<div class="wp-embed-social">
				<div class="wp-embed-comments">
					<a href="<?php echo esc_url( get_comments_link( $post->ID ) ); ?>" target="_top">
						<span class="dashicons dashicons-admin-comments"></span>
						<?php
						printf(
							_n(
								'%s <span class="screen-reader-text">Comment</span>',
								'%s <span class="screen-reader-text">Comments</span>',
								get_comments_number( $post->ID ),
								'oembed-api'
							),
							get_comments_number( $post->ID )
						);
						?>
					</a>
				</div>
				<div class="wp-embed-share">
					<button type="button" class="wp-embed-share-dialog-open" aria-label="<?php _e( 'Open sharing dialog', 'oembed-api' ); ?>">
						<span class="dashicons dashicons-share"></span>
					</button>
				</div>
			</div>
			<div class="wp-embed-share-dialog hidden">
				<div class="wp-embed-share-dialog-content">
					<button type="button" class="wp-embed-share-dialog-close" aria-label="<?php _e( 'Close sharing dialog', 'oembed-api' ); ?>">
						<span class="dashicons dashicons-no"></span>
					</button>

					<div class="wp-embed-share-dialog-text">
						<h2 class="wp-embed-share-title">
							<?php _e( 'Copy and paste this URL into your site to embed:', 'oembed-api' ); ?>
						</h2>
						<?php
						printf(
							'<input type="text" value="%s" class="wp-embed-share-input" />',
							esc_url( get_permalink( $post->ID ) )
						);
						?>
					</div>
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
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 *
	 * @param string $html The unfiltered oEmbed HTML.
	 * @param string $url  URL of the content to be embedded.
	 *
	 * @return string The filtered oEmbed HTML.
	 */
	public function filter_oembed_result( $html, $url ) {
		require_once( ABSPATH . WPINC . '/class-oembed.php' );
		$wp_oembed = _wp_oembed_get_object();

		$trusted = $current_site = false;

		foreach ( $wp_oembed->providers as $matchmask => $data ) {
			$regex = $data[1];
			$originalmask = $matchmask;

			// Turn the asterisk-type provider URLs into regex.
			if ( ! $regex ) {
				$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';
				$matchmask = preg_replace( '|^#http\\\://|', '#https?\://', $matchmask );
			}

			if ( preg_match( $matchmask, $url ) ) {
				if ( home_url( '/*' ) === $originalmask ) {
					$current_site = true;
				}

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
				'title'        => true,
			),
		);

		if ( ! $trusted || $current_site ) {
			$html = wp_kses( $html, $allowed_html );
			$html = preg_replace( '|^(<iframe.*?></iframe>).*$|', '$1', $html );
			$html = str_replace( '<iframe', '<iframe sandbox="allow-scripts" security="restricted"', $html );

			preg_match( '/ src=[\'"]([^\'"]*)[\'"]/', $html, $results );

			if ( ! empty( $results ) ) {
				$secret = wp_generate_password( 10, false );

				$url = esc_url( "{$results[1]}#?secret=$secret" );

				$html = str_replace( $results[0], " src=\"$url\" data-secret=\"$secret\"", $html );
			}
		}

		if ( '' === $html ) {
			return false;
		}

		return $html;
	}
}
