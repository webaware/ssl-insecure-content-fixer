<?php

/**
* class for managing the plugin
*/
class SSLInsecureContentFixer {

	public $options							= false;
	public $network_options					= false;

	/**
	* static method for getting the instance of this singleton object
	* @return self
	*/
	public static function getInstance() {
		static $instance = null;

		if (is_null($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	* hook into WordPress
	*/
	private function __construct() {
		$this->loadOptions();
		$this->proxyFix();

		add_action('init', array($this, 'init'));

		if ($this->options['fix_level'] !== 'off' && is_ssl()) {
			// filter script and stylesheet links
			add_filter('script_loader_src', 'ssl_insecure_content_fix_url');
			add_filter('style_loader_src', 'ssl_insecure_content_fix_url');

			// filter uploads dir so that plugins using it to determine upload URL also work
			add_filter('upload_dir', array(__CLASS__, 'uploadDir'));

			// filter image links on front end e.g. in calls to wp_get_attachment_image(), wp_get_attachment_image_src(), etc.
			if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
				add_filter('wp_get_attachment_url', 'ssl_insecure_content_fix_url', 100);
			}

			// filter plugin Image Widget old-style image links
			add_filter('image_widget_image_url', 'ssl_insecure_content_fix_url');

			// handle Content fix level
			if ($this->options['fix_level'] === 'content') {
				add_filter('the_content', array($this, 'fixContent'), 9999);		// also for fix_level 'widget'
				add_filter('widget_text', array($this, 'fixContent'), 9999);		// not for fix_level 'widget' (no need to duplicate effort)
			}

			// handle Widget fix level
			if ($this->options['fix_level'] === 'widgets') {
				add_filter('the_content', array($this, 'fixContent'), 9999);		// also for fix_level 'content'
				add_action('dynamic_sidebar_before', array($this, 'fixWidgetsStart'), 9999, 2);
				add_action('dynamic_sidebar_after', array($this, 'fixWidgetsEnd'), 9999, 2);
			}

			// handle Capture fix level
			if ($this->options['fix_level'] === 'capture') {
				add_action('init', array($this, 'fixCaptureStart'), 5);
			}

			// handle some specific plugins
			if (!empty($this->options['fix_specific'])) {
				add_action('wp_print_styles', array($this, 'fixSpecific'), 100);
			}
		}

		if (is_admin()) {
			require SSLFIX_PLUGIN_ROOT . 'includes/class.SSLInsecureContentFixerAdmin.php';
			new SSLInsecureContentFixerAdmin();
		}
	}

	/**
	* load options for plugin
	*/
	protected function loadOptions() {
		$defaults = array(
			'fix_level'		=>	'simple',
			'proxy_fix'		=>	'normal',
			'fix_specific'	=>	array(
									'woo_https' => 1
								),
		);

		if (is_multisite()) {
			$this->network_options = get_site_option(SSLFIX_PLUGIN_OPTIONS, $defaults);

			// use network-wide settings as default for individual sites
			$defaults = $this->network_options;
		}

		$this->options = get_option(SSLFIX_PLUGIN_OPTIONS, $defaults);
	}

	/**
	* check options for required proxy fix
	*/
	protected function proxyFix() {
		// failsafe: allow website owners to force the proxy fix off, in case of conflicts
		if (defined('SSLFIX_PLUGIN_NO_HTTPS_DETECT') && SSLFIX_PLUGIN_NO_HTTPS_DETECT) {
			return;
		}

		if (!empty($this->options['proxy_fix'])) {
			switch ($this->options['proxy_fix']) {

				case 'HTTP_X_FORWARDED_PROTO':
					if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
						$_SERVER['HTTPS'] = 'on';
					}
					break;

				case 'HTTP_X_FORWARDED_SSL':
					if (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && ($_SERVER['HTTP_X_FORWARDED_SSL'] === 'on' || $_SERVER['HTTP_X_FORWARDED_SSL'] === '1')) {
						$_SERVER['HTTPS'] = 'on';
					}
					break;

				case 'HTTP_CF_VISITOR':
					if (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false) {
						$_SERVER['HTTPS'] = 'on';
					}
					break;

				case 'detect_fail':
					// only force-enable https if site is set to run fully on https
					if (stripos(get_option('siteurl'), 'https://') === 0) {
						$_SERVER['HTTPS'] = 'on';

						// add JavaScript detection of page protocol, and pray!
						add_action('wp_print_scripts', array($this, 'scriptForceHTTPS'));
					}
					break;

			}
		}

		if (!empty($this->options['fix_specific']['woo_https'])) {
			// stop old WooCommerce versions from falsely detecting HTTPS from Google Chrome/Chromium
			// @link http://develop.woothemes.com/woocommerce/2015/07/woocommerce-2-3-13-security-and-maintenance-release/
			// @link https://github.com/woothemes/woocommerce/issues/8479
			// @link http://superuser.com/a/943989/473190
			unset($_SERVER['HTTP_HTTPS']);
		}
	}

	/**
	* load text translations
	*/
	public function init() {
		load_plugin_textdomain('ssl-insecure-content-fixer', false, basename(dirname(SSLFIX_PLUGIN_FILE)) . '/languages/');
	}

	/**
	* fix images, embeds, iframes in content
	* @param string $content
	* @return string
	*/
	public function fixContent($content) {
		static $searches = array(
			'#<(?:img|iframe) .*?src=[\'"]\Khttp://[^\'"]+#i',		// fix image and iframe elements
			'#<link .*?href=[\'"]\Khttp://[^\'"]+#i',				// fix link elements
			'#<script [^>]*?src=[\'"]\Khttp://[^\'"]+#i',			// fix script elements
			'#url\([\'"]?\Khttp://[^)]+#i',							// inline CSS e.g. background images
		);
		$content = preg_replace_callback($searches, array(__CLASS__, 'fixContent_src_callback'), $content);

		// fix object embeds
		static $embed_searches = array(
			'#<object .*?</object>#is',								// fix object elements, including contained embed elements
			'#<embed .*?(?:/>|</embed>)#is',						// fix embed elements, not contained in object elements
		);
		$content = preg_replace_callback($embed_searches, array(__CLASS__, 'fixContent_embed_callback'), $content);

		return $content;
	}

	/**
	* callback for fixContent() regex replace for URLs
	* @param array $matches
	* @return string
	*/
	public static function fixContent_src_callback($matches) {
		return 'https' . substr($matches[0], 4);
	}

	/**
	* callback for fixContent() regex replace for embeds
	* @param array $matches
	* @return string
	*/
	public static function fixContent_embed_callback($matches) {
		// match from start of http: URL until either end quotes or query parameter separator, thus allowing for URLs in parameters
		$content = preg_replace_callback('#http://[^\'"&\?]+#i', array(__CLASS__, 'fixContent_src_callback'), $matches[0]);

		return $content;
	}

	/**
	* start capturing widget zone content to be fixed
	* @param int|string $index
	* @param bool $has_widgets
	*/
	public function fixWidgetsStart($index, $has_widgets) {
		if ($has_widgets) {
			ob_start();
		}
	}

	/**
	* stop capturing widget zone content and fix it
	* @param int|string $index
	* @param bool $has_widgets
	*/
	public function fixWidgetsEnd($index, $has_widgets) {
		if ($has_widgets) {
			echo $this->fixContent(ob_get_clean());
		}
	}

	/**
	* start capturing page for Capture fix level
	*/
	public function fixCaptureStart() {
		ob_start(array($this, 'fixCaptureEnd'));
	}

	/**
	* stop capturing page and fix it
	* @param string $buffer
	* @return string
	*/
	public function fixCaptureEnd($buffer) {
		return $this->fixContent($buffer);
	}

	/**
	* force specific plugins to load assets with HTTPS
	*/
	public function fixSpecific() {
		if (!empty($this->options['fix_specific']['lcpwp'])) {
			// force list-category-posts-with-pagination plugin to load its CSS with HTTPS (it doesn't use wp_enqueue_style)
			if (function_exists('admin_register_head') && is_dir(WP_PLUGIN_DIR . '/list-category-posts-with-pagination')) {
				remove_action('wp_head', 'admin_register_head');
				$url = plugins_url('pagination.css', 'list-category-posts-with-pagination/x');
				wp_enqueue_style('lcpwp', $url);
			}
		}
	}

	/**
	* use JavaScript to force the browser back to HTTPS if the page is loaded via HTTP
	*/
	public function scriptForceHTTPS() {
		require SSLFIX_PLUGIN_ROOT . 'views/script-force-https.php';
	}

	/**
	* filter uploads dir so that plugins using it to determine upload URL also work
	* @param array $uploads
	* @return array
	*/
	public static function uploadDir($uploads) {
		$uploads['url']		= ssl_insecure_content_fix_url($uploads['url']);
		$uploads['baseurl']	= ssl_insecure_content_fix_url($uploads['baseurl']);

		return $uploads;
	}

}
