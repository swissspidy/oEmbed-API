# oEmbed API #
Contributors:      swissspidy, pento, netweb  
Donate link:         
Tags:              oembed, api  
Requires at least: 4.3  
Tested up to:      4.3  
Stable tag:        0.5.0  
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

[![Build Status](https://travis-ci.org/swissspidy/oEmbed-API.svg?branch=master)](https://travis-ci.org/swissspidy/oEmbed-API)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/swissspidy/oEmbed-API/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/swissspidy/oEmbed-API/?branch=master)
[![Codecov Code Coverage](https://codecov.io/github/swissspidy/oEmbed-API/coverage.svg?branch=master)](https://codecov.io/github/swissspidy/oEmbed-API/?branch=master)

## Installation ##

### Manual Installation ###

1. 2. Upload the entire `/oembed-api` directory to the `/wp-content/plugins/` directory.
3. Activate oEmbed API through the 'Plugins' menu in WordPress.
4. Copy the permalink of one of your blog posts and paste it into a new post. It should automatically be embedded.

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

## Screenshots ##

1. Example of how embedding a WordPress post looks like.

## Contribute ##

Here is how you can contribute:

Join the discussion on [Trac](https://core.trac.wordpress.org/ticket/32522) and submit pull requests on [GitHub](https://github.com/swissspidy/oEmbed-API).

## Changelog ##

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

### 0.5.0 ###
This update includes dozens of bug fixes and even some accessibility improvements. Now displays featured images, too!

### 0.4.0 ###
You can now use this plugin without the REST API too!

### 0.0.1 ###
First release
