# oEmbed API #
Contributors:      swissspidy, pento  
Donate link:         
Tags:              oembed, api  
Requires at least: 4.0  
Tested up to:      4.4-trunk  
Stable tag:        trunk  
License:           GPLv2 or later  
License URI:       http://www.gnu.org/licenses/gpl-2.0.html  

Allow others to easily embed your blog posts on their sites using oEmbed.

## Description ##

It’s pretty easy to embed YouTube videos or tweets in WordPress. Just paste the URL to the content on a new line and you automagically get a preview of the embed.

We wondered why this doesn’t also work with WordPress blogs and created this little plugin here.

This plugin makes WordPress an oEmbed provider, which means you can easily embed your blog posts somewhere else. Even on your own website!

The beautiful embeds contain a short summary of the post and (if available) also its featured image.

Everything is easily extensible. You can completely change the look and feel of the embedded content.

**Note:** We are working on bringing this functionality to WordPress core at one point. See WordPress Core ticket [\#32522](https://core.trac.wordpress.org/ticket/32522) for more details.

**Get Involved**

We hold weekly chats about this plugin in the \#feature-oembed WordPress Slack channel. Read the [announcement post](http://make.wordpress.org/core/2015/07/17/oembed-feature-plugin/) for more information.

You can also join the discussion on [Trac](https://core.trac.wordpress.org/ticket/32522) and submit pull requests on [GitHub](https://github.com/swissspidy/oEmbed-API).

[![Build Status](https://travis-ci.org/swissspidy/oEmbed-API.svg?branch=master)](https://travis-ci.org/swissspidy/oEmbed-API)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/swissspidy/oEmbed-API/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/swissspidy/oEmbed-API/?branch=master)
[![Code Climate](https://codeclimate.com/github/swissspidy/oEmbed-API/badges/gpa.svg)](https://codeclimate.com/github/swissspidy/oEmbed-API)
[![Codecov Code Coverage](https://codecov.io/github/swissspidy/oEmbed-API/coverage.svg?branch=master)](https://codecov.io/github/swissspidy/oEmbed-API/?branch=master)

## Installation ##

### Manual Installation ###

1. Upload the entire `/oembed-api` directory to the `/wp-content/plugins/` directory.
2. Activate oEmbed API through the 'Plugins' menu in WordPress.
3. Copy the permalink of one of your blog posts and paste it into a new post. It should automatically be embedded.

## Frequently Asked Questions ##

### Is the REST API required for this plugin to work? ###

While we support the REST API, it is not required for this plugin to run. The only thing you need is an up-to-date version of WordPress.

### How does it work again? ###

We leverage a technique called oEmbed. Thanks to oEmbed, you can embed content from the web by only knowing its URL. While WordPress has supported this feature since version 2.9, it actually isn’t an oEmbed provider itself. That’s what we’re changing.

This plugin is fully compliant with the JSON specification at [oEmbed.com](http://oembed.com).

### Is it really that easy? ###

Yes.

### How can I replace the blue WordPress logo in my embeds? ###

The WordPress logo is displayed when there’s no site icon available. Site icons represent your site in browser tabs, bookmark menus, and on the home screen of mobile devices. Add your unique site icon in the Customizer and it will be used in the embed too.

### Why can’t I embed site XY? ###

The oEmbed API plugin needs to run on both sites so the embedded site serves the right HTML and that your site knows how to embed it.

### Why is my embedded site not showing? ###

When you get an empty preview, it’s possible that the embedded site has the `X-Frame-Options` header set. This prevents loading the site in an iframe for security reasons.

### How can I disable the oEmbed API? ###

See the 'Other Notes' section for that.

## Screenshots ##

1. Example of how embedding a WordPress post looks like.
2. Example of a post with a featured image
3. You can easily copy the sharing URL for any post.

## Developer Reference ##

There are some handy functions developer can use with this plugin.

For example, `get_post_embed_url` returns the URL to a post’s embed template used for the iframe, while `get_post_embed_html` returns the `<iframe>` tag to do this.

To complement these two functions, `get_oembed_endpoint_url` returns the URL to the oEmbed API endpoint itself.

**Disabling the feature:**

Disabling the rewrite endpoint:

`remove_action( 'init', 'wp_oembed_rewrite_endpoint' );`

Disabling the legacy controller:

<pre><code>
function my_remove_oembed_query_vars( $query_vars ) {
	unset $query_vars['oembed'];
	return $query_vars;
}
add_filter( 'query_vars', 'my_remove_oembed_query_vars' );
</code></pre>

Disabling the REST API route:

<pre><code>
function my_disable_oembed_route( $endpoints ) {
	unset $endpoints['/wp/v2/oembed'];
	return $endpoints;
}
add_action( 'rest_endpoints', 'my_disable_oembed_route' );
</code></pre>

Disabling auto discovery of other sites:

`add_filter( 'embed_oembed_discover', '__return_false' );`

Disabling output of the discovery links on your site:

`remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );`

## Changelog ##

### 0.9.0 ###
* New: Added new `is_embed` conditional tag.
* New: Support for embedding video and audio attachments.
* Enhancement: More classes to better target the embedded content via CSS.
* Enhancement: Major JavaScript improvements leading to better performance.
* Enhancement: More robust XML generation.
* Enhancement: Show read more link instead of word count in the excerpt.
* Fixed: Correct sanitization of the `maxwidth` param in the legacy controller.
* Fixed: Better sanitization of the resulting HTML.

### 0.8.0 ###

* New: Added translations for Arabic, Arabic (Morocco), Spanish, French, Italian, Finnish, Hebrew
* Enhancement: Majorly improved embed template
* Enhancement: Added a 404 page for non-existent posts
* Enhancement: Improved API controllers
* Enhancement: Better multisite support
* Fixed: Redirect to the right URL when post slugs change
* Fixed: Now with proper emoji support
* Fixed: Hitting esc now closes the sharing dialog

### 0.7.0 ###
* New: Code Climate configuration
* New: Added translations for Danish (Denmark), German (Germany), German (Switzerland), Japanese, Greek, and English (Australia).
* Enhancement: Full Right-to-Left language support.
* Enhancement: Various performance improvements.
* Fixed: Previously the word count was not always correct.
* Fixed: Dashicons are now correctly shown in Internet Explorer.
* Fixed: Increased the iframe height limit from 600 to 1000px.
* Fixed: Removed the `rest_` prefix from filter names

### 0.6.0 ###
* Further accessibility improvements.
* Better embeds for attachments.
* `thumbnail_url`, `thumbnail_width` and `thumbnail_height` are now sent in the oEmbed endpoint response if available.
* Many bug fixes thanks to improved test coverage.

### 0.5.0 ###
* Accessibility improvements.
* Various JavaScript bug fixes, mainly related to click handling.
* Now displays the featured image if available.
* Bug fix related to word count
* CSS improvements

### 0.4.0 ###
* Now also works without the REST API enabled.
* Lots of bug fixes and refactoring.

### 0.3.0 ###
* Added a bunch of polish.

### 0.2.0 ###
* Okay, now it's actually usable.

### 0.1.0 ###
* First release

## Upgrade Notice ##

### 0.8.0 ###
This version comes with plenty of new translations, better multisite support and and a majorly improved embed template with design and accessibility improvements.

### 0.7.0 ###
This version includes many new translations, full RTL language support and also has better Internet Explorer compatibility.

### 0.6.0 ###
Major refactoring under the hood, improved accessibility and much more.

### 0.5.0 ###
This update includes dozens of bug fixes and even some accessibility improvements. Now displays featured images, too!

### 0.4.0 ###
You can now use this plugin without the REST API too!

### 0.0.1 ###
First release
