=== SSL Insecure Content Fixer ===
Contributors: webaware
Plugin Name: SSL Insecure Content Fixer
Plugin URI: http://snippets.webaware.com.au/wordpress-plugins/ssl-insecure-content-fixer/
Author URI: http://www.webaware.com.au/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FNFKTWZPRJDQE
Tags: ssl, https, insecure content, partially encrypted, mixed content
Requires at least: 3.2.1
Tested up to: 3.9
Stable tag: 1.8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Fix some common problems with insecure content on pages using SSL

== Description ==

Fix some common problems with insecure content on pages using SSL. Mostly, the fixes are CSS and JavaScript links that don't use SSL. The plugin originally used the method described in [this blog post](http://snippets.webaware.com.au/snippets/cleaning-up-wordpress-plugin-script-and-stylesheet-loads-over-ssl/), but now uses WordPress filters where possible.

It is very lightweight, so it doesn't impact on performance, but that also means it doesn't catch everything. Some fixes need a bigger hammer, like the [WordPress HTTPS](http://wordpress.org/plugins/wordpress-https/) plugin. If your problem is small, a small solution like this one might fit better.

**Current fixes:**

* scripts that are registered using `wp_register_script()` or `wp_enqueue_script()`
* stylesheets that are registered using `wp_register_style()` or `wp_enqueue_style()`
* images and other media loaded by calling `wp_get_attachment_image()`, `wp_get_attachment_image_src()`, etc.
* data returned from `wp_upload_dir()` (e.g. for some CAPTCHA images)
* the stylesheet loaded by the [list-category-posts-with-pagination](http://wordpress.org/plugins/list-category-posts-with-pagination) plugin
* images loaded by the [image-widget](http://wordpress.org/plugins/image-widget/) plugin

I'll be adding other fixes as I find simple solutions for them. The better solution is to get errant plugins fixed by their authors, but until they do, let me know about them and I'll attempt to add fixes.

== Installation ==

1. Either install automatically through the WordPress admin, or download the .zip file, unzip to a folder, and upload the folder to your /wp-content/plugins/ directory. Read [Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins) in the WordPress Codex for details.
2. Activate the plugin through the 'Plugins' menu in WordPress.

If your browser still reports insecure/mixed content, have a read of the [FAQ](http://wordpress.org/plugins/ssl-insecure-content-fixer/faq/). If that doesn't help, tell me the URL of the problem page in [the support forum](http://wordpress.org/support/plugin/ssl-insecure-content-fixer).

== Frequently Asked Questions ==

= How do I tell what is causing the insecure content / mixed content warnings? =

Look in your web browser's error console.

* Google Chrome has a [JavaScript Console](https://developers.google.com/chrome-developer-tools/docs/console) in its developer tools
* FireFox has the [Web Console](https://developer.mozilla.org/en-US/docs/Tools/Web_Console) or [Firebug](http://getfirebug.com/)
* Internet Explorer has the [F12 Tools Console](http://msdn.microsoft.com/en-us/library/ie/gg589500%28v=vs.85%29.aspx)
* Safari has the [Error Console](https://developer.apple.com/library/safari/documentation/AppleApplications/Conceptual/Safari_Developer_Guide/Introduction/Introduction.html)

NB: after you open your browser's console, refresh your page so that it tries to load the insecure content again and logs warnings to the error console.

= I get "insecure content" warnings from some of my content =

You are probably loading content (such as images) with a URL that starts with "http:". Take that bit away, but leave the slashes, e.g. `//www.example.com/image.png`; your browser will load the content, using SSL when your page uses it.

= My website is behind a load balancer or reverse proxy =

If your website is behind a load balancer or other reverse proxy, and WordPress doesn't know when SSL is being used, this plugin won't help. See my blog post, [WordPress is_ssl() doesn’t work behind some load balancers](http://snippets.webaware.com.au/snippets/wordpress-is_ssl-doesnt-work-behind-some-load-balancers/), for some details. This plugin has a link to test whether is_ssl() is working, which you can find in the Tools menu in your WordPress admin. You might be able to fix it by adding this to your wp-config.php file:

`// Amazon AWS Elastic Load Balancer, CloudFlare, and some others
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    $_SERVER['HTTPS']='on';`

For websites hosted by Network Solutions, try downloading [this gist](https://gist.github.com/webaware/4688802) and saving it into your plugins folder, then enable the plugin "Force SSL URL Scheme".

= I get warnings about basic WordPress scripts like jquery.js =

You are probably behind a reverse proxy -- see the FAQ above about load balancers / reverse proxies, and run the is_ssl() test from the WordPress admin Tools menu.

= I still get "insecure content" warnings on my secure page =

Post about it to [the support forum](http://wordpress.org/support/plugin/ssl-insecure-content-fixer), and be sure to include a link to the page. Posts without working links will be ignored.

= You listed my plugin, but I've fixed it =

Great! Tell me which plugin is yours and how to check for your new version, and I'll drop the "fix" from my next release.

== Changelog ==

= 1.8.0 [2014-02-02] =
* changed: use script/style source filters instead of iterating over script/style dependency objects
* changed: only handle links for `wp_get_attachment_image()`, `wp_get_attachment_image_src()`, etc. on front end (i.e. not in admin)
* changed: refactor for code simplification
* added: fix data returned from `wp_upload_dir()` (fixes Contact Form 7 CAPTCHA images)
* added: Tools menu link to `is_ssl()` test

= 1.7.1 [2013-03-13] =
* fixed: is_ssl() test checks to ensure test page was actually loaded via SSL

= 1.7.0 [2013-03-13] =
* added: simple test to see whether [is_ssl()](http://codex.wordpress.org/Function_Reference/is_ssl) is working, and try to diagnose when it isn't

= 1.6.0 [2013-01-05] =
* added: handle images and other media loaded by calling `wp_get_attachment_image()`, `wp_get_attachment_image_src()`, etc.

= 1.5.0 [2012-11-09] =
* added: handle properly enqueued admin stylesheets for admin over SSL

= 1.4.1 [2012-09-21] =
* fixed: handle uppercase links properly (i.e. HTTP://)

= 1.4.0 [2012-09-13] =
* added: fix for images loaded by [image-widget](http://wordpress.org/plugins/image-widget/)

= 1.3.0 [2012-07-22] =
* removed: fix for links-shortcode (fixed in v1.3)

= 1.2.0 [2012-07-21] =
* removed: fix for youtube-feeder (fixed in v2.0.0); NB: v2.0.0 of that plugin still loads Youtube videos over http, so you will still get insecure content errors on pages with embedded videos until plugin author applies a fix.

= 1.1.0 [2012-05-17] =
* added: fix for youtube-feeder stylesheet

= 1.0.0 [2012-04-19] =
* initial release
