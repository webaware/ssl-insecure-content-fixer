<?php
/*
Plugin Name: SSL Insecure Content Fixer
Plugin URI: http://snippets.webaware.com.au/wordpress-plugins/ssl-insecure-content-fixer/
Description: A very simple plugin that fixes some common problems with insecure content on pages using SSL.
Version: 1.0.0
Author: WebAware
Author URI: http://www.webaware.com.au/
*/

class SSLInsecureContentFixer {
	/**
	* force plugins to load scripts with SSL if page is SSL
	*/
	public static function scriptsFix() {
		global $wp_scripts;

		if (is_ssl()) {
			if (!is_admin()) {
				// search the registered scripts for any that will load as insecure content
				foreach ((array) $wp_scripts->registered as $script) {
					// only fix if source URL starts with http://
					if (stripos($script->src, 'http://') !== FALSE)
						$script->src = str_replace('http://', 'https://', $script->src);
				}
			}
		}
	}

	/**
	* force plugins to load styles with SSL if page is SSL
	*/
	public static function stylesFix() {
		global $wp_styles;

		if (is_ssl()) {
			if (!is_admin()) {
				// search the registered stylesheets for any that will load as insecure content
				foreach ((array) $wp_styles->registered as $style) {
					// only fix if source URL starts with http://
					if (stripos($style->src, 'http://') !== FALSE)
						$style->src = str_replace('http://', 'https://', $style->src);
				}

				// force links-shortcode plugin to load its CSS with SSL (it doesn't use wp_enqueue_style)
				if (function_exists('linkssc_css') && is_dir(WP_PLUGIN_DIR . '/links-shortcode')) {
					remove_action('wp_head', 'linkssc_css');
					$url = plugins_url('links-shortcode.css', 'links-shortcode/links-shortcode.php');
					wp_enqueue_style('links-shortcode', $url);
				}

				// force list-category-posts-with-pagination plugin to load its CSS with SSL (it doesn't use wp_enqueue_style)
				if (function_exists('admin_register_head') && is_dir(WP_PLUGIN_DIR . '/list-category-posts-with-pagination')) {
					remove_action('wp_head', 'admin_register_head');
					$url = plugins_url('pagination.css', 'list-category-posts-with-pagination/list-category-posts-with-pagination.php');
					wp_enqueue_style('lcpwp', $url);
				}
			}
		}
	}
}

add_action('wp_print_scripts', array('SSLInsecureContentFixer', 'scriptsFix'), 100);
add_action('wp_print_styles', array('SSLInsecureContentFixer', 'stylesFix'), 100);
