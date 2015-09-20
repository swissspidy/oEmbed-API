<?php
/**
 * Contains the template for an oEmbed response.
 *
 * When a post is embedded, this file is used to
 * create the output.
 *
 * @package WP_oEmbed
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<title><?php wp_title( '-', true, 'right' ); ?></title>
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
			background-size: 20px;
			background-position: center;
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

		.wp-embed-comments a:hover .dashicons-admin-comments {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M5%202h9q.82%200%201.41.59T16%204v7q0%20.82-.59%201.41T14%2013h-2l-5%205v-5H5q-.82%200-1.41-.59T3%2011V4q0-.82.59-1.41T5%202z%27%20fill%3D%27%230073aa%27%2F%3E%3C%2Fsvg%3E");
		}

		.dashicons-share {
			background-image: url("data:image/svg+xml;charset=utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M14.5%2012q1.24%200%202.12.88T17.5%2015t-.88%202.12-2.12.88-2.12-.88T11.5%2015q0-.34.09-.69l-4.38-2.3Q6.32%2013%205%2013q-1.24%200-2.12-.88T2%2010t.88-2.12T5%207q1.3%200%202.21.99l4.38-2.3q-.09-.35-.09-.69%200-1.24.88-2.12T14.5%202t2.12.88T17.5%205t-.88%202.12T14.5%208q-1.3%200-2.21-.99l-4.38%202.3Q8%209.66%208%2010t-.09.69l4.38%202.3q.89-.99%202.21-.99z%27%20fill%3D%27%2382878c%27%2F%3E%3C%2Fsvg%3E");
		}

		.wp-embed-share-dialog-open:hover .dashicons-share {
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

		.wp-embed-social::after {
			content: "";
			display: table;
			clear: both;
		}

		.wp-embed-comments,
		.wp-embed-share {
			display: inline;
		}

		.wp-embed-social a:hover {
			text-decoration: none;
			color: #0073aa;
		}

		.wp-embed-comments a {
			line-height: 25px;
			display: inline-block;
		}

		.wp-embed-comments + .wp-embed-share {
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
			margin: -8px 0 0;
			padding: 0;
			background: transparent;
			border: none;
			cursor: pointer;
			outline: none;
		}

		.wp-embed-share-dialog-open .dashicons,
		.wp-embed-share-dialog-close .dashicons {
			padding: 4px;
			top: 8px;
		}

		.wp-embed-share-dialog-open:focus .dashicons,
		.wp-embed-share-dialog-close:focus .dashicons {
			-webkit-box-shadow: 0 0 0 1px #5b9dd9, 0 0 2px 1px rgba(30, 140, 190, .8);
			box-shadow: 0 0 0 1px #5b9dd9, 0 0 2px 1px rgba(30, 140, 190, .8);
			-webkit-border-radius: 100%;
			border-radius: 100%;
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

		html[dir="rtl"] .wp-embed-site-title {
			left: auto;
			right: 35px;
		}

		html[dir="rtl"] .wp-embed-social {
			float: left;
			text-align: left;
		}

		html[dir="rtl"] .wp-embed-meta {
			float: right;
		}

		html[dir="rtl"] .wp-embed-share {
			margin-left: 0;
			margin-right: 10px;
		}

		html[dir="rtl"] .wp-embed-share-dialog-close {
			right: auto;
			left: 20px;
		}
	</style>
	<script type="text/javascript">
		(function ( window, document ) {
			'use strict';

			var hash, secret, share_dialog, share_dialog_open, share_dialog_close, share_input, resize_limiter;

			window.onload = function () {
				share_dialog = document.querySelector( '.wp-embed-share-dialog' );
				share_dialog_open = document.querySelector( '.wp-embed-share-dialog-open' );
				share_dialog_close = document.querySelector( '.wp-embed-share-dialog-close' );
				share_input = document.querySelector( '.wp-embed-share-input' );

				if ( share_input ) {
					share_input.onclick = function ( e ) {
						e.target.select();
					};
				}

				function openSharingDialog() {
					share_dialog.className = share_dialog.className.replace( 'hidden', '' );
					share_input.select();
				}

				function closeSharingDialog() {
					share_dialog.className += ' hidden';
					document.querySelector( '.wp-embed-share-dialog-open' ).focus();
				}

				if ( share_dialog_open ) {
					share_dialog_open.onclick = function ( e ) {
						openSharingDialog();
						share_input.select();
						e.preventDefault();
					};
				}

				if ( share_dialog_close ) {
					share_dialog_close.onclick = function ( e ) {
						closeSharingDialog();
						e.preventDefault();
					};
				}

				document.onkeydown = function ( e ) {
					if ( e.keyCode === 27 && -1 === share_dialog.className.indexOf( 'hidden' ) ) {
						closeSharingDialog();
					}
				};

				if ( window.self === window.top ) {
					return;
				}

				hash = window.location.hash;
				secret = hash.replace( /.*secret=([\d\w]{10}).*/, '$1' );

				/**
				 * Send this document's height to the parent (embedding) site.
				 */
				window.parent.postMessage( {
					message: 'height',
					value: Math.ceil( document.body.getBoundingClientRect().height ),
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
					value: Math.ceil( document.body.getBoundingClientRect().height ),
					secret: secret
				}, '*' );
			};
		})( window, document );
	</script>
	<?php
	/**
	 * Print scripts or data in the head tag.
	 */
	do_action( 'oembed_head' );
	?>
</head>
<body>
<div class="wp-embed">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
			// Add post thumbnail to response if available.
			$thumbnail_id = false;

			if ( has_post_thumbnail() ) {
				$thumbnail_id = get_post_thumbnail_id();
			}

			if ( 'attachment' === get_post_type() && wp_attachment_is_image() ) {
				$thumbnail_id = get_the_ID();
			}

			if ( $thumbnail_id ) :
				/**
				 * Filters the oEmbed thumbnail image size.
				 *
				 * @param string|array $image_size   Thumbnail size to use in the embed.
				 * @param int          $thumbnail_id The current thumbnail ID.
				 */
				$image_size = apply_filters( 'oembed_image_size', array( 600, 340 ), $thumbnail_id );
				?>
				<div class="wp-embed-featured-image">
					<a href="<?php the_permalink(); ?>" target="_top">
						<?php echo wp_get_attachment_image( $thumbnail_id, $image_size ); ?>
					</a>
				</div>
			<?php endif; ?>

			<p class="wp-embed-heading">
				<a href="<?php the_permalink(); ?>" target="_top">
					<?php the_title(); ?>
				</a>
			</p>

			<div class="wp-embed-excerpt"><?php the_excerpt_embed(); ?></div>

			<div class="wp-embed-meta">
				<?php
				$site_icon_url = admin_url( 'images/w-logo-blue.png' );

				if ( function_exists( 'get_site_icon_url' ) ) {
					$site_icon_url = get_site_icon_url( 32, $site_icon_url );
				}

				/**
				 * Filters the site icon URL for use in the oEmbed template.
				 *
				 * @param string $site_icon_url The site icon URL.
				 */
				$site_icon_url = apply_filters( 'oembed_site_icon_url', $site_icon_url );

				printf(
					'<a href="%s" target="_top"><img src="%s" width="32" height="32" alt="" class="wp-embed-site-icon"/></a>',
					esc_url( home_url() ), esc_url( $site_icon_url )
				);
				?>
				<div class="wp-embed-site-title">
					<?php printf( '<a href="%s" target="_top">%s</a>', esc_url( home_url() ), get_bloginfo( 'name' ) ); ?>
				</div>
			</div>
			<div class="wp-embed-social">
				<?php if ( get_comments_number() || comments_open() ) : ?>
					<div class="wp-embed-comments">
						<a href="<?php comments_link(); ?>" target="_top">
							<span class="dashicons dashicons-admin-comments"></span>
							<?php
							printf(
								_n(
									'%s <span class="screen-reader-text">Comment</span>',
									'%s <span class="screen-reader-text">Comments</span>',
									get_comments_number(),
									'oembed-api'
								),
								get_comments_number()
							);
							?>
						</a>
					</div>
				<?php endif; ?>
				<div class="wp-embed-share">
					<button type="button" class="wp-embed-share-dialog-open"
					        aria-label="<?php _e( 'Open sharing dialog', 'oembed-api' ); ?>">
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
						<input type="text" value="<?php the_permalink(); ?>" class="wp-embed-share-input"/>
					</div>

					<button type="button" class="wp-embed-share-dialog-close"
					        aria-label="<?php _e( 'Close sharing dialog', 'oembed-api' ); ?>">
						<span class="dashicons dashicons-no"></span>
					</button>
				</div>
			</div>
			<?php
		endwhile;
	else :
		?>
		<p class="wp-embed-heading"><?php _e( 'Page not found', 'oembed-api' ); ?></p>

		<div class="wp-embed-excerpt">
			<p><?php _e( 'Error 404! The requested content was not found.', 'oembed-api' ) ?></p>
		</div>

		<div class="wp-embed-meta">
			<?php
			$site_icon_url = admin_url( 'images/w-logo-blue.png' );

			if ( function_exists( 'get_site_icon_url' ) ) {
				$site_icon_url = get_site_icon_url( 32, $site_icon_url );
			}

			/**
			 * Filters the site icon URL for use in the oEmbed template.
			 *
			 * @param string $site_icon_url The site icon URL.
			 */
			$site_icon_url = apply_filters( 'oembed_site_icon_url', $site_icon_url );

			printf(
				'<img src="%s" width="32" height="32" alt="" class="wp-embed-site-icon"/>',
				esc_url( $site_icon_url )
			);
			?>
			<div class="wp-embed-site-title">
				<?php printf( '<a href="%s" target="_top">%s</a>', esc_url( home_url() ), get_bloginfo( 'name' ) ); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
<?php
/**
 * Print scripts or data before the closing body tag.
 */
do_action( 'oembed_footer' );
?>
</body>
</html>
