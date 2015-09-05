=== SSL Insecure Content Fixer ===
Contributors: webaware
Plugin Name: SSL Insecure Content Fixer
Plugin URI: https://ssl.webaware.net.au/
Author URI: http://webaware.com.au/
Donate link: http://shop.webaware.com.au/donations/?donation_for=SSL+Insecure+Content+Fixer
Tags: ssl, https, insecure content, partially encrypted, mixed content
Requires at least: 3.2.1
Tested up to: 4.3
Stable tag: 2.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Clean up WordPress website HTTPS insecure content

== Description ==

Clean up your WordPress website's HTTPS insecure content and mixed content warnings. Installing the SSL Insecure Content Fixer plugin will solve most insecure content warnings with little or no effort. The remainder can be diagnosed with a few simple tools.

When you install SSL Insecure Content Fixer, its default settings are activated and it will automatically perform some basic fixes on your website using the Simple fix level. You can select more comprehensive fix levels as needed by your website.

WordPress Multisite gets a network settings page. This can be used to set default settings for all sites within a network, so that network administrators only need to specify settings on sites that have requirements differing from the network defaults.

See the [SSL Insecure Content Fixer website](https://ssl.webaware.net.au/) for more details.

= Translations =

Many thanks to the generous efforts of our translators:

* Bulgarian (bg_BG) -- [Ivan Arnaudov](http://templateinspector.com/)

If you'd like to help out by translating this plugin, please [sign up for an account and dig in](https://translate.webaware.com.au/projects/ssl-insecure-content-fixer).

== Installation ==

1. Either install automatically through the WordPress admin, or download the .zip file, unzip to a folder, and upload the folder to your /wp-content/plugins/ directory. Read [Installing Plugins](https://codex.wordpress.org/Managing_Plugins#Installing_Plugins) in the WordPress Codex for details.
2. Activate the plugin through the 'Plugins' menu in WordPress.

If your browser still reports insecure/mixed content, have a read of the [Cleaning Up page](https://ssl.webaware.net.au/cleaning-up-content/). If that doesn't help, tell me the URL of the problem page in [the support forum](https://wordpress.org/support/plugin/ssl-insecure-content-fixer).

== Frequently Asked Questions ==

= How do I tell what is causing the insecure content / mixed content warnings? =

Look in your web browser's error console.

* Google Chrome has a [JavaScript Console](https://developers.google.com/chrome-developer-tools/docs/console) in its developer tools
* FireFox has the [Web Console](https://developer.mozilla.org/en-US/docs/Tools/Web_Console) or [Firebug](http://getfirebug.com/)
* Internet Explorer has the [F12 Tools Console](https://msdn.microsoft.com/library/bg182326%28v=vs.85%29)
* Safari has the [Error Console](https://developer.apple.com/library/safari/documentation/AppleApplications/Conceptual/Safari_Developer_Guide/Introduction/Introduction.html)

NB: after you open your browser's console, refresh your page so that it tries to load the insecure content again and logs warnings to the error console.

[Why No Padlock?](https://www.whynopadlock.com/) has a really good online test tool for diagnosing HTTPS problems.

= I get "insecure content" warnings from some of my content =

You are probably loading content (such as images) with a URL that starts with "http:". Take that bit away, but leave the slashes, e.g. `//www.example.com/image.png`; your browser will load the content, using HTTPS when your page uses it.

= My website is behind a load balancer or reverse proxy =

If your website is behind a load balancer or other reverse proxy, and WordPress doesn't know when HTTPS is being used, you will need to select the appropriate [HTTPS detection settings](https://ssl.webaware.net.au/https-detection/). See my blog post, [WordPress is_ssl() doesn’t work behind some load balancers](http://snippets.webaware.com.au/snippets/wordpress-is_ssl-doesnt-work-behind-some-load-balancers/), for some details.

= I get warnings about basic WordPress scripts like jquery.js =

You are probably behind a reverse proxy -- see the FAQ above about load balancers / reverse proxies, and run the SSL Tests from the WordPress admin Tools menu.

= I still get "insecure content" warnings on my secure page =

Post about it to [the support forum](https://wordpress.org/support/plugin/ssl-insecure-content-fixer), and be sure to include a link to the page. Posts without working links will probably be ignored.

= You listed my plugin, but I've fixed it =

Great! Tell me which plugin is yours and how to check for your new version, and I'll drop the "fix" from my next release.

== Contributions ==

* [Translate into your preferred language](https://translate.webaware.com.au/projects/ssl-insecure-content-fixer)
* [Fork me on GitHub](https://github.com/webaware/ssl-insecure-content-fixer)

== Upgrade Notice ==

= 2.1.2 =

* fixed: HTTPS detection for host 123-reg

== Changelog ==

The full changelog can be found [on GitHub](https://github.com/webaware/ssl-insecure-content-fixer/blob/master/changelog.md). Recent entries:

= 2.1.2, 2015-09-05 =

* fixed: HTTPS detection for host 123-reg

= 2.1.1, 2015-08-11 =

* fixed: HTTPS detection doesn't work unless SSL Tests page was just visited
* added: show update notice on plugin admin page

= 2.1.0, 2015-07-30 =

* security fix: restrict access to AJAX test script; don't disclose server environment with system information
* changed: always show server environment on test results
* added: Bulgarian translation (thanks, [Ivan Arnaudov](http://templateinspector.com/)!)
* added: .htaccess file for AJAX SSL Tests, fixes conflict with some security plugins

= 2.0.0, 2015-07-26 =

* changed: handle media loaded by calling `wp_get_attachment_image()`, `wp_get_attachment_image_src()`, etc. via AJAX
* changed: in multisite, test tools (and settings) are only available to super admins
* added: settings page for controlling behaviour
* added: Simple, Content, Widgets, Capture, and Off modes for fixes
* added: fix for [WooCommerce + Google Chrome HTTP_HTTPS bug](https://github.com/woothemes/woocommerce/issues/8479) (fixed in WooCommerce v2.3.13)
* added: load translation (if anyone fancies [supplying some](https://translate.webaware.com.au/projects/ssl-insecure-content-fixer)!)
