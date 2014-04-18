<?php
/*
Plugin Name: SSL Insecure Content Fixer
Plugin URI: http://snippets.webaware.com.au/wordpress-plugins/ssl-insecure-content-fixer/
Description: Fix some common problems with insecure content on pages using SSL
Version: 1.8.0
Author: WebAware
Author URI: http://www.webaware.com.au/
*/

/*
copyright (c) 2012-2014 WebAware Pty Ltd (email : rmckay@webaware.com.au)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if (!defined('SSLFIX_PLUGIN_ROOT')) {
	define('SSLFIX_PLUGIN_ROOT', dirname(__FILE__) . '/');
	define('SSLFIX_PLUGIN_NAME', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
}

class SSLInsecureContentFixer {

	/**
	* hook WordPress to handle script and style fixes
	*/
	public static function run() {
		add_filter('plugin_row_meta', array(__CLASS__, 'pluginDetailsLinks'), 10, 2);
		add_action('admin_menu', array(__CLASS__, 'adminMenu'));

		if (is_ssl()) {
			// filter script and stylesheet links
			add_filter('script_loader_src', array(__CLASS__, 'fixURL'));
			add_filter('style_loader_src', array(__CLASS__, 'fixURL'));

			// filter uploads dir so that plugins using it to determine upload URL also work
			add_filter('upload_dir', array(__CLASS__, 'uploadDir'));

			// filter image links on front end e.g. in calls to wp_get_attachment_image(), wp_get_attachment_image_src(), etc.
			if (!is_admin()) {
				add_filter('wp_get_attachment_url', array(__CLASS__, 'fixURL'), 100);
			}

			// filter plugin Image Widget old-style image links
			add_filter('image_widget_image_url', array(__CLASS__, 'fixURL'));

			// handle some specific plugins
			add_action('wp_print_styles', array(__CLASS__, 'stylesFix'), 100);
		}
	}

	/**
	* add plugin details links on plugins page
	*/
	public static function pluginDetailsLinks($links, $file) {
		if ($file == SSLFIX_PLUGIN_NAME) {
			$testURL = self::fixURL(plugins_url('is_ssl-test.php', __FILE__));
			$links[] = sprintf('<a href="%s" target="_blank">%s</a>', $testURL, __('Test is_ssl()', 'ssl-insecure-content-fixer'));
			$links[] = '<a href="http://wordpress.org/support/plugin/ssl-insecure-content-fixer">' . __('Get help', 'ssl-insecure-content-fixer') . '</a>';
			$links[] = '<a href="http://wordpress.org/plugins/ssl-insecure-content-fixer/">' . __('Rating', 'ssl-insecure-content-fixer') . '</a>';
			$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=FNFKTWZPRJDQE">' . __('Donate', 'ssl-insecure-content-fixer') . '</a>';
		}

		return $links;
	}

	/**
	* add our admin menu items
	*/
	public static function adminMenu() {
		// add external link to Tools area
		global $submenu;
		if (current_user_can('manage_options')) {
			$testURL = self::fixURL(plugins_url('is_ssl-test.php', __FILE__));
			$submenu['tools.php'][] = array(
				__('Test is_ssl()', 'ssl-insecure-content-fixer'),		// label
				'manage_options',										// permissions
				$testURL,												// URL
			);
		}
	}

	/**
	* force specific plugins to load styles with SSL
	*/
	public static function stylesFix() {
		// force list-category-posts-with-pagination plugin to load its CSS with SSL (it doesn't use wp_enqueue_style)
		if (function_exists('admin_register_head') && is_dir(WP_PLUGIN_DIR . '/list-category-posts-with-pagination')) {
			remove_action('wp_head', 'admin_register_head');
			$url = plugins_url('pagination.css', 'list-category-posts-with-pagination/x');
			wp_enqueue_style('lcpwp', $url);
		}
	}

	/**
	* replace http: URL with https: URL
	* @param string $url
	* @return string
	*/
	public static function fixURL($url) {
		// only fix if source URL starts with http://
		if (stripos($url, 'http://') === 0) {
			$url = 'https' . substr($url, 4);
		}

		return $url;
	}

	/**
	* filter uploads dir so that plugins using it to determine upload URL also work
	* @param array $uploads
	* @return array
	*/
	public static function uploadDir($uploads) {
		$uploads['url'] = self::fixURL($uploads['url']);
		$uploads['baseurl'] = self::fixURL($uploads['baseurl']);

		return $uploads;
	}

}

SSLInsecureContentFixer::run();
