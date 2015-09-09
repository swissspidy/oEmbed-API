<?php
/**
 * Contains the template for an oEmbed response.
 *
 * When a post is embedded, this file is used to
 * create the output.
 *
 * @package WP_oEmbed
 */

/* @var WP_Post $post */
global $post;

$post_content = $post->post_content;

/**
 * Filter the post excerpt lenght in the oEmbed output.
 *
 * @param int     $num_words Number of words. Defaults to 35.
 * @param WP_Post $post      The current post object.
 */
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
<!DOCTYPE html>
<html>
<head>
	<title><?php esc_html_e( $post->post_title, 'oembed-api' ); ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>
	<link rel="stylesheet" href="https://s.w.org/wp-includes/css/dashicons.css"/>
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

		.wp-embed p {
			margin: 0;
		}

		p.wp-embed-heading {
			margin: 0 0 15px;
			font-weight: bold;
			font-size: 22px;
			line-height: 1.3;
		}

		.wp-embed-heading a {
			color: #32373c;
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
			line-height: 25px;
			-webkit-transition: color .1s ease-in;
			transition: color .1s ease-in;
		}

		.wp-embed-social a:hover {
			text-decoration: none;
			color: #0073aa;
		}

		.wp-embed-comments,
		.wp-embed-share {
			float: right;
		}

		.wp-embed-comments a {
			line-height: 25px;
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
			margin: 0;
			padding: 0;
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

		p.wp-embed-share-title {
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
			text-align: center;
			font: 400 14px/1.5 'Open Sans', sans-serif;
		}
	</style>
	<script type="text/javascript">
		(function ( window, document ) {
			'use strict';

			var hash, secret, share_dialog, embed, resize_limiter;

			window.onload = function () {
				share_dialog = document.getElementsByClassName( 'wp-embed-share-dialog' )[ 0 ];

				document.getElementsByClassName( 'wp-embed-share-input' )[ 0 ].onclick = function ( e ) {
					e.target.select();
				};

				document.getElementsByClassName( 'wp-embed-share-dialog-open' )[ 0 ].onclick = function ( e ) {
					share_dialog.className = share_dialog.className.replace( 'hidden', '' );
					e.preventDefault();
				};

				document.getElementsByClassName( 'wp-embed-share-dialog-close' )[ 0 ].onclick = function ( e ) {
					share_dialog.className += ' hidden';
					e.preventDefault();
				};

				if ( window.self === window.top ) {
					return;
				}

				hash = window.location.hash;
				secret = hash.replace( /.*secret=([\d\w]{10}).*/, '$1' );

				embed = document.getElementsByClassName( 'wp-embed' )[ 0 ];

				/**
				 * Send this document's height to the parent (embedding) site.
				 */
				window.parent.postMessage( {
					message: 'height',
					value: embed.clientHeight + 2,
					secret: secret
				}, '*' );

				/**
				 * Detect clicks to external (_top) links.
				 */
				var links = document.getElementsByTagName( 'a' );
				for ( var i = 0; i < links.length; i++ ) {
					if ( '_top' === links[ i ].getAttribute( 'target' ) ) {
						links[ i ].onclick = function ( e ) {
							if ( e.target.hasAttribute( 'href' ) ) {
								var href = e.target.getAttribute( 'href' );
							} else {
								var href = e.target.parentElement.getAttribute( 'href' );
							}

							/**
							 * Send link target to the parent (embedding) site.
							 */
							window.parent.postMessage( {
								message: 'link',
								value: href,
								secret: secret
							}, '*' );
							e.preventDefault();
						}
					}
				}
			};

			/**
			 * Iframe resize handler.
			 */
			window.onresize = function () {
				if ( window.self === window.top ) {
					return;
				}

				/**
				 * We need to limit how often we send the message,
				 * otherwise we're just wasting CPU.
				 * */
				if ( resize_limiter ) {
					return;
				}
				resize_limiter = true;

				/**
				 * Call onresize immediately, in case the resize finished before we got the final size.
				 */
				setTimeout( function () {
					resize_limiter = false;
					window.onresize();
				}, 50 );

				window.parent.postMessage( {
					message: 'height',
					value: embed.clientHeight + 2,
					secret: secret
				}, '*' );
			};
		})( window, document );
	</script>
</head>
<body>
<div class="wp-embed">
	<?php
	// Add post thumbnail to response if available.
	$thumbnail_id = false;

	if ( has_post_thumbnail( $post->ID ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post->ID );
	}

	if ( 'attachment' === get_post_type( $post ) && wp_attachment_is_image( $post->ID ) ) {
		$thumbnail_id = $post->ID;
	}

	if ( $thumbnail_id ) :
	?>
		<div class="wp-embed-featured-image">
			<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" target="_top">
				<?php echo wp_get_attachment_image( $thumbnail_id, array( 600, 340 ) ); ?>
			</a>
		</div>
	<?php endif; ?>

	<p class="wp-embed-heading">
		<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" target="_top">
			<?php echo esc_html( $post->post_title ); ?>
		</a>
	</p>

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
		<div class="wp-embed-share">
			<button type="button" class="wp-embed-share-dialog-open" aria-label="<?php _e( 'Open sharing dialog', 'oembed-api' ); ?>">
				<span class="dashicons dashicons-share"></span>
			</button>
		</div>
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
	</div>
	<div class="wp-embed-share-dialog hidden">
		<div class="wp-embed-share-dialog-content">
			<button type="button" class="wp-embed-share-dialog-close" aria-label="<?php _e( 'Close sharing dialog', 'oembed-api' ); ?>">
				<span class="dashicons dashicons-no"></span>
			</button>

			<div class="wp-embed-share-dialog-text">
				<p class="wp-embed-share-title">
					<?php _e( 'Copy and paste this URL into your site to embed:', 'oembed-api' ); ?>
				</p>
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
