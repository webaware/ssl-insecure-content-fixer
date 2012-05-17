=== SSL Insecure Content Fixer ===
Contributors: webaware
Plugin Name: SSL Insecure Content Fixer
Plugin URI: http://snippets.webaware.com.au/wordpress-plugins/ssl-insecure-content-fixer/
Author URI: http://www.webaware.com.au/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FNFKTWZPRJDQE
Tags: ssl, https, insecure content, partially encrypted
Requires at least: 3.0.1
Tested up to: 3.3.2
Stable tag: 1.1.0

A very simple plugin that fixes some common problems with insecure content on pages using SSL.

== Description ==

It’s quite common to use WordPress as the host for an online shop, and that often means having an order page that needs to be encrypted via SSL. You don’t want your customers providing credit card details or other sensitive information over an unencrypted connection! But some WordPress plugins don’t take SSL into account, and merrily load scripts and stylesheets without encryption. This plugin attempts to fix this problem, where there are simple solutions. How it does this is described in [this blog post](http://snippets.webaware.com.au/snippets/cleaning-up-wordpress-plugin-script-and-stylesheet-loads-over-ssl/).

It is very lightweight, so it doesn't impact on performance, but that also means it doesn't catch everything. Some fixes need a bigger hammer, like the [WordPress HTTPS](http://wordpress.org/extend/plugins/wordpress-https/) plugin. If your problem is small, a small solution like this one might fit better.

**Current fixes:**

* scripts that are registered using wp_register_script or wp_enqueue_script
* stylesheets that are registered using wp_register_style or wp_enqueue_style
* the stylesheet loaded by the links-shortcode plugin
* the stylesheet loaded by the list-category-posts-with-pagination plugin
* the stylesheet loaded by the youtube-feeder plugin

I'll be adding other fixes as I become aware of other problems that can be easily fixed. The better solution is to get errant plugins fixed by their authors, but until they do, let me know about other problems and I'll attempt to add fixes for them to this plugin.

== Installation ==

1. Upload this plugin to your /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

If your browser still reports insecure content, tell me the URL of the problem page in [the forum](http://wordpress.org/tags/ssl-insecure-content-fixer?forum_id=10).

== Frequently Asked Questions ==

= I get "insecure content" warnings from some of my content =

You are probably loading content with a URL that starts with "http:". Take that bit away, but leave the slashes, e.g. `//www.example.com/`; your browser should load the content, but will use SSL when your page uses it.

= I still get "insecure content" warnings on my secure page =

Post about it to [the forum](http://wordpress.org/tags/ssl-insecure-content-fixer?forum_id=10), and be sure to include a link to the page. Posts without working links will be ignored.

= You listed my plugin, but I've fixed it =

Great! Tell me which plugin is yours, and how to check for your new version, and I'll drop the "fix" from my next release.

== Changelog ==

= 1.1.0 [2012-05-17] =
* added: handle youtube-fixer

= 1.0.0 [2012-04-19] =
* initial release
