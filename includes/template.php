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
setup_postdata( $post );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<title><?php esc_html_e( $post->post_title, 'oembed-api' ); ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>
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

		/* Dashicons */
		.dashicons {
			display: inline-block;
			width: 20px;
			height: 20px;
			background-color: transparent;
			background-repeat: no-repeat;
			background-size: cover;
			-webkit-transition: background .1s ease-in;
			transition: background .1s ease-in;
			position: relative;
			top: 5px;
		}

		.dashicons-no {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M15.55%2013.7l-2.19%202.06-3.42-3.65-3.64%203.43-2.06-2.18%203.64-3.43-3.42-3.64%202.18-2.06%203.43%203.64%203.64-3.42%202.05%202.18-3.64%203.43z%27%20fill%3D%27%23fff%27%2F%3E%3C%2Fsvg%3E");
		}

		.dashicons-admin-comments {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M5%202h9q.82%200%201.41.59T16%204v7q0%20.82-.59%201.41T14%2013h-2l-5%205v-5H5q-.82%200-1.41-.59T3%2011V4q0-.82.59-1.41T5%202z%27%20fill%3D%27%2382878c%27%2F%3E%3C%2Fsvg%3E");
		}

		.dashicons-admin-comments:hover {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M5%202h9q.82%200%201.41.59T16%204v7q0%20.82-.59%201.41T14%2013h-2l-5%205v-5H5q-.82%200-1.41-.59T3%2011V4q0-.82.59-1.41T5%202z%27%20fill%3D%27%230073aa%27%2F%3E%3C%2Fsvg%3E");
		}

		.dashicons-share {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M14.5%2012q1.24%200%202.12.88T17.5%2015t-.88%202.12-2.12.88-2.12-.88T11.5%2015q0-.34.09-.69l-4.38-2.3Q6.32%2013%205%2013q-1.24%200-2.12-.88T2%2010t.88-2.12T5%207q1.3%200%202.21.99l4.38-2.3q-.09-.35-.09-.69%200-1.24.88-2.12T14.5%202t2.12.88T17.5%205t-.88%202.12T14.5%208q-1.3%200-2.21-.99l-4.38%202.3Q8%209.66%208%2010t-.09.69l4.38%202.3q.89-.99%202.21-.99z%27%20fill%3D%27%2382878c%27%2F%3E%3C%2Fsvg%3E");
		}

		.dashicons-share:hover {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M14.5%2012q1.24%200%202.12.88T17.5%2015t-.88%202.12-2.12.88-2.12-.88T11.5%2015q0-.34.09-.69l-4.38-2.3Q6.32%2013%205%2013q-1.24%200-2.12-.88T2%2010t.88-2.12T5%207q1.3%200%202.21.99l4.38-2.3q-.09-.35-.09-.69%200-1.24.88-2.12T14.5%202t2.12.88T17.5%205t-.88%202.12T14.5%208q-1.3%200-2.21-.99l-4.38%202.3Q8%209.66%208%2010t-.09.69l4.38%202.3q.89-.99%202.21-.99z%27%20fill%3D%27%230073aa%27%2F%3E%3C%2Fsvg%3E");
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

		.wp-embed-comments,
		.wp-embed-share {
			float: right;
			position: relative;
		}

		.wp-embed-social a:hover {
			text-decoration: none;
		}

		.wp-embed-comments {
			left: -30px;
		}

		.wp-embed-comments a {
			line-height: 25px;
		}

		.wp-embed-comments + .wp-embed-share {
			right: -30px;
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

		.wp-embed-share-dialog-close {
			position: absolute;
			top: 20px;
			right: 20px;
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

			var hash, secret, share_dialog, share_input, embed, resize_limiter;

			window.onload = function () {
				share_dialog = document.querySelector( '.wp-embed-share-dialog' );
				share_input = document.querySelector( '.wp-embed-share-input' );

				share_input.onclick = function ( e ) {
					e.target.select();
				};

				document.querySelector( '.wp-embed-share-dialog-open' ).onclick = function ( e ) {
					share_dialog.className = share_dialog.className.replace( 'hidden', '' );
					share_input.select();
					e.preventDefault();
				};

				document.querySelector( '.wp-embed-share-dialog-close' ).onclick = function ( e ) {
					share_dialog.className += ' hidden';
					document.querySelector( '.wp-embed-share-dialog-open' ).focus();
					e.preventDefault();
				};

				if ( window.self === window.top ) {
					return;
				}

				hash = window.location.hash;
				secret = hash.replace( /.*secret=([\d\w]{10}).*/, '$1' );

				embed = document.querySelector( '.wp-embed' );

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

	<div class="wp-embed-excerpt"><?php the_excerpt(); ?></div>

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
		<?php if ( get_comments_number( $post->ID ) || comments_open( $post->ID ) ) : ?>
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
		<?php endif; ?>
		<div class="wp-embed-share">
			<button type="button" class="wp-embed-share-dialog-open" aria-label="<?php _e( 'Open sharing dialog', 'oembed-api' ); ?>">
				<span class="dashicons dashicons-share"></span>
			</button>
		</div>
	</div>
	<div class="wp-embed-share-dialog hidden">
		<div class="wp-embed-share-dialog-content">
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

			<button type="button" class="wp-embed-share-dialog-close" aria-label="<?php _e( 'Close sharing dialog', 'oembed-api' ); ?>">
				<span class="dashicons dashicons-no"></span>
			</button>
		</div>
	</div>
</div>
</body>
</html>
