<?php
/**
 * Contains the template for an oEmbed response.
 *
 * When a post is embedded, this file is used to
 * create the output.
 *
 * @package WP_oEmbed
 */

if ( ! headers_sent() ) {
	header( 'X-WP-oembed: true' );
}

wp_enqueue_style( 'open-sans' );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<title><?php wp_title( '-', true, 'right' ); ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
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
			-webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
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
			border: none;
		}

		.wp-embed-featured-image.square {
			float: left;
			max-width: 160px;
			margin-right: 20px;
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

		.wp-embed .wp-embed-more {
			color: #b4b9be;
		}

		.wp-embed-meta {
			display: table;
			width: 100%;
			margin-top: 30px;
		}

		.wp-embed-site-icon {
			position: absolute;
			top: 50%;
			left: 0;
			-webkit-transform: translateY(-50%);
			transform: translateY(-50%);
			height: 25px;
			width: 25px;
			border: 0;
		}

		.wp-embed-site-title {
			font-weight: bold;
			line-height: 25px;
		}

		.wp-embed-site-title a {
			position: relative;
			display: inline-block;
			padding-left: 35px;
		}

		.wp-embed-site-title,
		.wp-embed-social {
			display: table-cell;
		}

		.wp-embed-social {
			text-align: right;
			white-space: nowrap;
			vertical-align: middle;
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
			background-color: rgba(10, 10, 10, 0.9);
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
		}

		.wp-embed-share-dialog-open .dashicons {
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

		.wp-embed-share-dialog-close .dashicons {
			height: 24px;
			width: 24px;
			background-size: 24px;
		}

		.wp-embed-share-dialog-content {
			height: 100%;
			-webkit-transform-style: preserve-3d;
			-moz-transform-style: preserve-3d;
			transform-style: preserve-3d;
			overflow: hidden;
		}

		.wp-embed-share-dialog-text {
			margin-top: 25px;
			padding: 20px;
		}

		.wp-embed-share-tabs {
			margin: 0 0 20px;
			padding: 0;
			list-style: none;
		}

		.wp-embed-share-tab-button {
			display: inline;
		}

		.wp-embed-share-tab-button button {
			margin: 0;
			padding: 0;
			border: none;
			background: transparent;
			font-size: 16px;
			line-height: 1.3;
			color: #aaa;
			cursor: pointer;
			-webkit-transition: color .1s ease-in;
			transition: color .1s ease-in;
		}

		.wp-embed-share-tab-button [aria-selected="true"] {
			color: #fff;
		}

		.wp-embed-share-tab-button button:hover {
			color: #fff;
		}

		.wp-embed-share-tab-button + .wp-embed-share-tab-button {
			margin: 0 0 0 10px;
			padding: 0 0 0 11px;
			border-left: 1px solid #aaa;
		}

		.wp-embed-share-tab[aria-hidden="true"] {
			display: none;
		}

		p.wp-embed-share-description {
			margin: 0;
			font-size: 14px;
			line-height: 1;
			font-style: italic;
			color: #aaa;
		}

		.wp-embed-share-input {
			box-sizing: border-box;
			width: 100%;
			border: none;
			height: 28px;
			margin: 0 0 10px 0;
			padding: 0 5px;
			font: 400 14px/1.5 'Open Sans', sans-serif;
			resize: none;
			cursor: text;
		}

		textarea.wp-embed-share-input {
			height: 72px;
		}

		html[dir="rtl"] .wp-embed-featured-image.square {
			float: right;
			margin-right: 0;

			margin-left: 20px;
		}

		html[dir="rtl"] .wp-embed-site-title a {
			padding-left: 0;
			padding-right: 35px;
		}

		html[dir="rtl"] .wp-embed-site-icon {
			margin-right: 0;
			margin-left: 10px;
			left: auto;
			right: 0;
		}

		html[dir="rtl"] .wp-embed-social {
			text-align: left;
		}

		html[dir="rtl"] .wp-embed-meta {
		}

		html[dir="rtl"] .wp-embed-share {
			margin-left: 0;
			margin-right: 10px;
		}

		html[dir="rtl"] .wp-embed-share-dialog-close {
			right: auto;
			left: 20px;
		}

		html[dir="rtl"] .wp-embed-share-tab-button + .wp-embed-share-tab-button {
			margin: 0 10px 0 0;
			padding: 0 11px 0 0;
			border-left: none;
			border-right: 1px solid #aaa;
		}
	</style>
	<script type="text/javascript">
		(function ( window, document ) {
			'use strict';

			var secret = window.location.hash.replace( /.*secret=([\d\w]{10}).*/, '$1' ),
				share_dialog, share_dialog_open, share_dialog_close, share_dialog_tabs, share_input, resizing;

			function sendEmbedMessage( message, value ) {
				window.parent.postMessage( {
					message: message,
					value: value,
					secret: secret
				}, '*' );
			}

			function onLoad() {
				share_dialog = document.querySelector( '.wp-embed-share-dialog' );
				share_dialog_open = document.querySelector( '.wp-embed-share-dialog-open' );
				share_dialog_close = document.querySelector( '.wp-embed-share-dialog-close' );
				share_input = document.querySelectorAll( '.wp-embed-share-input' );
				share_dialog_tabs = document.querySelectorAll( '.wp-embed-share-tab-button button' );

				if ( share_input ) {
					for ( var i = 0; i < share_input.length; i++ ) {
						share_input[ i ].addEventListener( 'click', function ( e ) {
							e.target.select();
						} );
					}
				}

				function openSharingDialog() {
					share_dialog.className = share_dialog.className.replace( 'hidden', '' );
					share_input[ 0 ].select();
				}

				function closeSharingDialog() {
					share_dialog.className += ' hidden';
					document.querySelector( '.wp-embed-share-dialog-open' ).focus();
				}

				if ( share_dialog_open ) {
					share_dialog_open.addEventListener( 'click', function ( e ) {
						openSharingDialog();
						e.preventDefault();
					} );
				}

				if ( share_dialog_close ) {
					share_dialog_close.addEventListener( 'click', function ( e ) {
						closeSharingDialog();
						e.preventDefault();
					} );
				}

				if ( share_dialog_tabs ) {
					for ( var i = 0; i < share_dialog_tabs.length; i++ ) {
						share_dialog_tabs[ i ].addEventListener( 'click', function ( e ) {
							var currentTab = document.querySelector( '.wp-embed-share-tab-button [aria-selected="true"]' );
							currentTab.setAttribute( 'aria-selected', 'false' );
							document.querySelector( '#' + currentTab.getAttribute( 'aria-controls' ) ).setAttribute( 'aria-hidden', 'true' );

							e.target.setAttribute( 'aria-selected', 'true' );
							document.querySelector( '#' + e.target.getAttribute( 'aria-controls' ) ).setAttribute( 'aria-hidden', 'false' );
						} );

						share_dialog_tabs[ i ].addEventListener( 'keydown', function ( e ) {
							var previousSibling = e.target.parentElement.previousElementSibling,
								nextSibling = e.target.parentElement.nextElementSibling,
								newTab;

							if ( 37 === e.keyCode ) {
								newTab = previousSibling;
							} else if ( 39 === e.keyCode ) {
								newTab = nextSibling;
							} else {
								return false;
							}

							if ( 'rtl' === document.documentElement.getAttribute( 'dir' ) ) {
								newTab = ( newTab === previousSibling ) ? nextSibling : previousSibling;
							}

							if ( newTab ) {
								e.target.setAttribute( 'tabindex', '-1' );
								e.target.setAttribute( 'aria-selected', false );
								document.querySelector( '#' + e.target.getAttribute( 'aria-controls' ) ).setAttribute( 'aria-hidden', 'true' );

								newTab.firstElementChild.setAttribute( 'tabindex', '0' );
								newTab.firstElementChild.setAttribute( 'aria-selected', 'true' );
								newTab.firstElementChild.focus();
								document.querySelector( '#' + newTab.firstElementChild.getAttribute( 'aria-controls' ) ).setAttribute( 'aria-hidden', 'false' );
							}
						} );
					}
				}

				document.addEventListener( 'keydown', function ( e ) {
					if ( e.keyCode === 27 && -1 === share_dialog.className.indexOf( 'hidden' ) ) {
						closeSharingDialog();
					}
				}, false );

				if ( window.self === window.top ) {
					return;
				}

				/**
				 * Send this document's height to the parent (embedding) site.
				 */
				sendEmbedMessage( 'height', Math.ceil( document.body.getBoundingClientRect().height ) );

				/**
				 * Detect clicks to external (_top) links.
				 */
				var links = document.getElementsByTagName( 'a' ), href;
				for ( var i = 0; i < links.length; i++ ) {
					links[ i ].addEventListener( 'click', function ( e ) {
						if ( e.target.hasAttribute( 'href' ) ) {
							href = e.target.getAttribute( 'href' );
						} else {
							href = e.target.parentElement.getAttribute( 'href' );
						}

						/**
						 * Send link target to the parent (embedding) site.
						 */
						sendEmbedMessage( 'link', href );
						e.preventDefault();
					} );
				}
			}

			document.addEventListener( 'DOMContentLoaded', onLoad, false );

			/**
			 * Iframe resize handler.
			 */
			function onResize() {
				if ( window.self === window.top ) {
					return;
				}

				clearTimeout( resizing );

				resizing = setTimeout( function () {
					sendEmbedMessage( 'height', Math.ceil( document.body.getBoundingClientRect().height ) );
				}, 100 );
			}

			window.addEventListener( 'resize', onResize, false );
		})( window, document );
	</script>
	<?php
	/**
	 * Print scripts or data in the head tag.
	 */
	do_action( 'oembed_head' );
	?>
</head>
<body <?php body_class(); ?>>
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

		if ( $thumbnail_id ) {
			$aspect_ratio = 1;
			$measurements = array( 1, 1 );
			$image_size   = 'full'; // Fallback.

			$meta = wp_get_attachment_metadata( $thumbnail_id );
			if ( is_array( $meta ) ) {
				foreach ( $meta['sizes'] as $size => $data ) {
					if ( $data['width'] / $data['height'] > $aspect_ratio ) {
						$aspect_ratio = $data['width'] / $data['height'];
						$measurements = array( $data['width'], $data['height'] );
						$image_size   = $size;
					}
				}
			}

			/**
			 * Filter the thumbnail image size for use in the embed template.
			 *
			 * @param string $image_size Thumbnail image size.
			 */
			$image_size = apply_filters( 'oembed_thumbnail_image_size', $image_size );

			$shape = $measurements[0] / $measurements[1] >= 1.75 ? 'rectangular' : 'square';

			/**
			 * Filter the thumbnail shape for use in the embed template.
			 *
			 * Rectangular images are shown above the title
			 * while square images are shown next to the content.
			 *
			 * @param string $shape Thumbnail image shape. Either 'rectangular' or 'square'.
			 */
			$shape = apply_filters( 'oembed_thumbnail_image_shape', $shape );
		}
		?>
		<div <?php post_class( 'wp-embed' ); ?>>
			<?php if ( $thumbnail_id && 'rectangular' === $shape ) : ?>
				<div class="wp-embed-featured-image rectangular">
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

			<?php if ( $thumbnail_id && 'square' === $shape ) : ?>
				<div class="wp-embed-featured-image square">
					<a href="<?php the_permalink(); ?>" target="_top">
						<?php echo wp_get_attachment_image( $thumbnail_id, $image_size ); ?>
					</a>
				</div>
			<?php endif; ?>

			<div class="wp-embed-excerpt"><?php the_excerpt_embed(); ?></div>

			<?php
			/**
			 * Print additional content after the embed excerpt.
			 */
			do_action( 'oembed_content' );
			?>

			<div class="wp-embed-meta">
				<div class="wp-embed-site-title">
					<?php
					$site_icon_url = admin_url( 'images/w-logo-blue.png' );

					if ( function_exists( 'get_site_icon_url' ) ) {
						$site_icon_url = get_site_icon_url( 32, $site_icon_url );
					}

					/**
					 * Filters the site icon URL for use in the embed template.
					 *
					 * @param string $site_icon_url The site icon URL.
					 */
					$site_icon_url = apply_filters( 'oembed_site_icon_url', $site_icon_url );

					printf(
						'<a href="%s" target="_top"><img src="%s" width="32" height="32" alt="" class="wp-embed-site-icon"/><span>%s</span></a>',
						esc_url( home_url() ),
						esc_url( $site_icon_url ),
						esc_attr( get_bloginfo( 'name' ) )
					);
					?>
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
									absint( get_comments_number() )
								);
								?>
							</a>
						</div>
					<?php endif; ?>
					<div class="wp-embed-share">
						<button type="button" class="wp-embed-share-dialog-open"
						        aria-label="<?php esc_attr_e( 'Open sharing dialog', 'oembed-api' ); ?>">
							<span class="dashicons dashicons-share"></span>
						</button>
					</div>
				</div>
			</div>
			<div class="wp-embed-share-dialog hidden">
				<div class="wp-embed-share-dialog-content">
					<div class="wp-embed-share-dialog-text">
						<ul class="wp-embed-share-tabs" role="tablist">
							<li id="wp-embed-share-tab-button-wordpress" class="wp-embed-share-tab-button" role="presentation">
								<button role="tab" aria-controls="wp-embed-share-tab-wordpress" aria-selected="true" tabindex="0"><?php esc_html_e( 'WordPress Embed', 'oembed-api' ); ?></button>
							</li>
							<li id="wp-embed-share-tab-button-embed" class="wp-embed-share-tab-button" role="presentation">
								<button role="tab" aria-controls="wp-embed-share-tab-html" aria-selected="false" tabindex="-1"><?php esc_html_e( 'HTML Embed', 'oembed-api' ); ?></button>
							</li>
						</ul>
						<div id="wp-embed-share-tab-wordpress" class="wp-embed-share-tab" role="tabpanel" aria-labelledby="wp-embed-share-tab-button-wordpress" aria-hidden="false">
							<input type="text" value="<?php the_permalink(); ?>" class="wp-embed-share-input" tabindex="0" readonly/>

							<p class="wp-embed-share-description">
								<?php _e( 'Copy and paste this URL into your WordPress site to embed', 'oembed-api' ); ?>
							</p>
						</div>
						<div id="wp-embed-share-tab-html" class="wp-embed-share-tab" role="tabpanel" aria-labelledby="wp-embed-share-tab-button-html" aria-hidden="true">
							<textarea class="wp-embed-share-input" tabindex="0" readonly><?php echo esc_attr( get_post_embed_html( null, 600, 400 ) ); ?></textarea>

							<p class="wp-embed-share-description">
								<?php _e( 'Copy and paste this code into your site to embed', 'oembed-api' ); ?>
							</p>
						</div>
					</div>

					<button type="button" class="wp-embed-share-dialog-close" aria-label="<?php esc_attr_e( 'Close sharing dialog', 'oembed-api' ); ?>">
						<span class="dashicons dashicons-no"></span>
					</button>
				</div>
			</div>
		</div>
		<?php
	endwhile;
else :
	?>
	<div class="wp-embed">
		<p class="wp-embed-heading"><?php _e( 'Page not found', 'oembed-api' ); ?></p>

		<div class="wp-embed-excerpt">
			<p><?php _e( 'Error 404! The requested content was not found.', 'oembed-api' ) ?></p>
		</div>

		<div class="wp-embed-meta">
			<div class="wp-embed-site-title">
				<?php
				$site_icon_url = admin_url( 'images/w-logo-blue.png' );

				if ( function_exists( 'get_site_icon_url' ) ) {
					$site_icon_url = get_site_icon_url( 32, $site_icon_url );
				}

				/**
				 * Filters the site icon URL for use in the embed template.
				 *
				 * @param string $site_icon_url The site icon URL.
				 */
				$site_icon_url = apply_filters( 'oembed_site_icon_url', $site_icon_url );

				printf(
					'<a href="%s" target="_top"><img src="%s" width="32" height="32" alt="" class="wp-embed-site-icon"/><span>%s</span></a>',
					esc_url( home_url() ),
					esc_url( $site_icon_url ),
					esc_attr( get_bloginfo( 'name' ) )
				);
				?>
			</div>
		</div>
	</div>
	<?php
endif;

/**
 * Print scripts or data before the closing body tag.
 */
do_action( 'oembed_footer' );
?>
</body>
</html>
