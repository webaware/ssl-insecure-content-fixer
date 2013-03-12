<?php
/*
Plugin Name: SSL Insecure Content Fixer
Plugin URI: http://snippets.webaware.com.au/wordpress-plugins/ssl-insecure-content-fixer/
Description: Fix some common problems with insecure content on pages using SSL
Version: 1.7.1
Author: WebAware
Author URI: http://www.webaware.com.au/
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
		add_filter('plugin_row_meta', array(__CLASS__, 'addPluginDetailsLinks'), 10, 2);
		add_action('admin_menu', array(__CLASS__, 'addAdminMenu'));

		if (is_ssl()) {
			add_action('wp_print_scripts', array(__CLASS__, 'scriptsFix'), 100);
			add_action('wp_print_styles', array(__CLASS__, 'stylesFix'), 100);

			// handle admin styles; must run before print_admin_styles() is called
			add_action('admin_print_styles', array(__CLASS__, 'stylesFix'), 19);

			// filter image links e.g. in calls to wp_get_attachment_image(), wp_get_attachment_image_src(), etc.
			add_filter('wp_get_attachment_url', array(__CLASS__, 'filterGetAttachUrl'), 100);

			// filter Image Widget image links
			add_filter('image_widget_image_url', array(__CLASS__, 'filterImageWidgetURL'));
		}
	}

	/**
	* action hook for adding plugin details links
	*/
	public static function addPluginDetailsLinks($links, $file) {
		if ($file == SSLFIX_PLUGIN_NAME) {
			$testURL = self::fixURL(plugins_url('is_ssl-test.php', __FILE__));
			$links[] = '<a href="' . $testURL . '" target="_blank">test is_ssl()</a>';
			$links[] = '<a href="http://wordpress.org/support/plugin/ssl-insecure-content-fixer">' . __('Get help') . '</a>';
			$links[] = '<a href="http://wordpress.org/extend/plugins/ssl-insecure-content-fixer/">' . __('Rating') . '</a>';
			$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=FNFKTWZPRJDQE">' . __('Donate') . '</a>';
		}

		return $links;
	}

	/**
	* action hook for building admin menu
	*/
	public function addAdminMenu() {
		// register the instructions page, only linked from plugin page
		global $_registered_pages;

		$hookname = get_plugin_page_hookname('is_ssl-test', '');
		if (!empty($hookname)) {
			add_action($hookname, array(__CLASS__, 'is_sslTest'));
			$_registered_pages[$hookname] = true;
		}
	}

	/**
	* check that SSL can be detected, try to diagnose why it can't
	*/
	public static function is_sslTest() {
		include SSLFIX_PLUGIN_ROOT . 'is_ssl-test.php';
	}

	/**
	* force plugins to load scripts with SSL if page is SSL
	*/
	public static function scriptsFix() {
		global $wp_scripts;

		// search the registered scripts for any that will load as insecure content
		foreach ((array) $wp_scripts->registered as $script) {
			// only fix if source URL starts with http://
			if (stripos(ltrim($script->src), 'http://') === 0)
				$script->src = self::fixURL($script->src);
		}
	}

	/**
	* force plugins to load styles with SSL if page is SSL
	*/
	public static function stylesFix() {
		global $wp_styles;

		// search the registered stylesheets for any that will load as insecure content
		foreach ((array) $wp_styles->registered as $style) {
			// only fix if source URL starts with http://
			if (stripos(ltrim($style->src), 'http://') === 0)
				$style->src = self::fixURL($style->src);
		}

		// force list-category-posts-with-pagination plugin to load its CSS with SSL (it doesn't use wp_enqueue_style)
		if (function_exists('admin_register_head') && is_dir(WP_PLUGIN_DIR . '/list-category-posts-with-pagination')) {
			remove_action('wp_head', 'admin_register_head');
			$url = plugins_url('pagination.css', 'list-category-posts-with-pagination/x');
			wp_enqueue_style('lcpwp', $url);
		}
	}

	/**
	* filter attachment links to load over SSL if page is SSL
	* @param string $url the URL to the attachment
	* @return string
	*/
	public static function filterGetAttachUrl($url) {
		// only fix if source URL starts with http://
		if (stripos($url, 'http://') === 0) {
			$url = self::fixURL($url);
		}

		return $url;
	}

	/**
	* filter Image Widget image links to load over SSL if page is SSL
	* @param string $imageurl the URL to the widget image
	* @return string
	*/
	public static function filterImageWidgetURL($imageurl) {
		// only fix if source URL starts with http://
		if (stripos(ltrim($imageurl), 'http://') === 0) {
			$imageurl = self::fixURL($imageurl);
		}

		return $imageurl;
	}

	/**
	* replace URL with one that uses SSL
	* @param string $url
	* @return string
	*/
	private static function fixURL($url) {
		return str_ireplace('http://', 'https://', $url);
	}

	/**
	* remove filters that are methods of an object of some class
	* @param string $filterName name of action or filter hook
	* @param string $className name of class for object method
	*/
	private static function removeObjectFilters($filterName, $className) {
		global $wp_filter;

		// must take a variable to iterate over array of filters,
		// else a subtle reference bug messes up the original array!
		$filters = $wp_filter[$filterName];

		foreach ($filters as $priority => $hooks) {
			foreach ($hooks as $idx => $filter) {
				// check for function being a method on a $className object
				if (is_array($filter['function']) && is_a($filter['function'][0], $className)) {
					remove_filter($filterName, $idx, $priority);
					break;
				}
			}
		}
	}
}

SSLInsecureContentFixer::run();
