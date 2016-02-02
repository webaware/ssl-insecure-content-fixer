<?php

/**
* manage admin
*/
class SSLInsecureContentFixerAdmin {

	protected $has_settings_errors			= false;

	/**
	* hook into WordPress
	*/
	public function __construct() {
		add_action('admin_init', array($this, 'adminInit'));
		add_action('load-tools_page_ssl-insecure-content-fixer-tests', array($this, 'setNonceCookie'));
		add_action('load-settings_page_ssl-insecure-content-fixer', array($this, 'setNonceCookie'));
		add_action('admin_print_styles-settings_page_ssl-insecure-content-fixer', array($this, 'printStylesSettings'));
		add_action('admin_print_styles-tools_page_ssl-insecure-content-fixer-tests', array($this, 'printStylesTests'));
		add_action('admin_menu', array($this, 'adminMenu'));
		add_action('network_admin_menu', array($this, 'adminMenuNetwork'));
		add_filter('plugin_row_meta', array($this, 'pluginDetailsLinks'), 10, 2);
		add_action('plugin_action_links_' . SSLFIX_PLUGIN_NAME, array($this, 'pluginActionLinks'));
		add_action('wp_ajax_sslfix-test-https', array($this, 'ajaxTestHTTPS'));
	}

	/**
	* admin_init action
	*/
	public function adminInit() {
		add_settings_section(SSLFIX_PLUGIN_OPTIONS, false, false, SSLFIX_PLUGIN_OPTIONS);
		register_setting(SSLFIX_PLUGIN_OPTIONS, SSLFIX_PLUGIN_OPTIONS, array($this, 'settingsValidate'));

		// in_plugin_update_message isn't supported on multisite != blog-1, so just add another row
		if (current_user_can('update_plugins')) {
			add_action('after_plugin_row_' . SSLFIX_PLUGIN_NAME, array($this, 'upgradeMessage'), 20, 2);
		}
	}

	/**
	* load CSS for settings page
	*/
	public function printStylesSettings() {
		echo "<style>\n";
		readfile(SSLFIX_PLUGIN_ROOT . 'css/settings.css');
		echo "</style>\n";
	}

	/**
	* load CSS for tests page
	*/
	public function printStylesTests() {
		echo "<style>\n";
		readfile(SSLFIX_PLUGIN_ROOT . 'css/tests.css');
		echo "</style>\n";
	}

	/**
	* add plugin details links on plugins page
	*/
	public function pluginDetailsLinks($links, $file) {
		if ($file == SSLFIX_PLUGIN_NAME) {
			if (is_network_admin()) {
				if (current_user_can('manage_network_options')) {
					$url = network_admin_url('settings.php?page=ssl-insecure-content-fixer');
					$links[] = sprintf('<a href="%s">%s</a>', esc_url($url), _x('Settings', 'plugin details links', 'ssl-insecure-content-fixer'));
				}
			}
			elseif (current_user_can($this->getMenuCapability())) {
				$url = admin_url('tools.php?page=ssl-insecure-content-fixer-tests');
				$links[] = sprintf('<a href="%s">%s</a>', esc_url($url), _x('SSL Tests', 'menu link', 'ssl-insecure-content-fixer'));
			}

			$links[] = sprintf('<a href="https://ssl.webaware.net.au/" target="_blank">%s</a>', _x('Instructions', 'plugin details links', 'ssl-insecure-content-fixer'));
			$links[] = sprintf('<a href="https://wordpress.org/support/plugin/ssl-insecure-content-fixer" target="_blank">%s</a>', _x('Get help', 'plugin details links', 'ssl-insecure-content-fixer'));
			$links[] = sprintf('<a href="https://wordpress.org/plugins/ssl-insecure-content-fixer/" target="_blank">%s</a>', _x('Rating', 'plugin details links', 'ssl-insecure-content-fixer'));
			$links[] = sprintf('<a href="https://translate.wordpress.org/projects/wp-plugins/ssl-insecure-content-fixer" target="_blank">%s</a>', _x('Translate', 'plugin details links', 'ssl-insecure-content-fixer'));
			$links[] = sprintf('<a href="http://shop.webaware.com.au/donations/?donation_for=SSL+Insecure+Content+Fixer" target="_blank">%s</a>', _x('Donate', 'plugin details links', 'ssl-insecure-content-fixer'));
		}

		return $links;
	}

	/**
	* add our admin menu items
	*/
	public function adminMenu() {
		$capability = $this->getMenuCapability();

		$label = _x('SSL Insecure Content', 'menu link', 'ssl-insecure-content-fixer');
		add_options_page($label, $label, $capability, 'ssl-insecure-content-fixer', array($this, 'settingsPage'));

		if (!is_network_admin()) {
			$label = _x('SSL Tests', 'menu link', 'ssl-insecure-content-fixer');
			add_management_page($label, $label, $capability, 'ssl-insecure-content-fixer-tests', array($this, 'testPage'));
		}
	}

	/**
	* add multisite network admin menu items
	*/
	public function adminMenuNetwork() {
		$label = _x('SSL Insecure Content', 'menu link', 'ssl-insecure-content-fixer');
		add_submenu_page('settings.php', $label, $label, 'manage_network_options', 'ssl-insecure-content-fixer', array($this, 'settingsPage'));
	}

	/**
	* add plugin action links
	*/
	public function pluginActionLinks($links) {
		if (current_user_can('manage_options')) {
			// add settings link
			$url = admin_url('options-general.php?page=ssl-insecure-content-fixer');
			$settings_link = sprintf('<a href="%s">%s</a>', esc_url($url), _x('Settings', 'plugin details links', 'ssl-insecure-content-fixer'));
			array_unshift($links, $settings_link);
		}

		return $links;
	}

	/**
	* show upgrade messages on Plugins admin page
	* @param string $file
	* @param object $current_meta
	*/
	public function upgradeMessage($file, $plugin_data) {
		$current = get_site_transient('update_plugins');

		if (isset($current->response[$file])) {
			$r = $current->response[$file];

			if (!empty($r->upgrade_notice)) {
				$wp_list_table = _get_list_table('WP_Plugins_List_Table');
				$colspan = $wp_list_table->get_column_count();
				$plugin_name = wp_kses($plugin_data['Name'], 'strip');

				require SSLFIX_PLUGIN_ROOT . 'views/admin-upgrade-message.php';
			}
		}
	}

	/**
	* settings admin
	*/
	public function settingsPage() {
		if (is_network_admin()) {
			// multisite network settings
			$options = SSLInsecureContentFixer::getInstance()->network_options;

			if (!empty($_POST[SSLFIX_PLUGIN_OPTIONS])) {
				check_admin_referer('settings', 'sslfix_nonce');

				$options = wp_unslash($_POST[SSLFIX_PLUGIN_OPTIONS]);
				$options = $this->settingsValidate($options);

				if (!$this->has_settings_errors) {
					update_site_option(SSLFIX_PLUGIN_OPTIONS, $options);
					add_settings_error(SSLFIX_PLUGIN_OPTIONS, 'sslfix-network-updated', __('Multisite network settings updated.', 'ssl-insecure-content-fixer'), 'updated');
				}
			}

			require SSLFIX_PLUGIN_ROOT . 'views/settings-form-network.php';
		}
		else {
			// individual site settings
			$options = SSLInsecureContentFixer::getInstance()->options;
			require SSLFIX_PLUGIN_ROOT . 'views/settings-form.php';
		}

		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$ver = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? time() : SSLFIX_PLUGIN_VERSION;

		$ajax_url = $this->getNoWpAJAX();

		wp_enqueue_script('sslfix-admin-settings', plugins_url("js/admin-settings$min.js", SSLFIX_PLUGIN_FILE), array('jquery'), $ver, true);
		wp_localize_script('sslfix-admin-settings', 'sslfix', array(
			'ajax_url_wp'	=> ssl_insecure_content_fix_url(admin_url('admin-ajax.php')),
			'ajax_url_ssl'	=> ssl_insecure_content_fix_url($ajax_url),
			'msg'			=> array(
									'recommended'		=> _x('* detected as recommended setting', 'proxy settings', 'ssl-insecure-content-fixer'),
								),
		));
	}

	/**
	* validate settings on save
	* @param array $input
	* @return array
	*/
	public function settingsValidate($input) {
		$output = array();

		$output['fix_level']		= empty($input['fix_level']) ? '' : $input['fix_level'];
		$output['proxy_fix']		= empty($input['proxy_fix']) ? '' : $input['proxy_fix'];

		if (!in_array($output['fix_level'], array('off', 'simple', 'content', 'widgets', 'capture'))) {
			add_settings_error(SSLFIX_PLUGIN_OPTIONS, 'sslfix-fix_level', _x('Fix level is invalid', 'settings error', 'ssl-insecure-content-fixer'));
			$this->has_settings_errors = true;
		}

		if (!in_array($output['proxy_fix'], array('normal', 'HTTP_X_FORWARDED_PROTO', 'HTTP_X_FORWARDED_SSL', 'HTTP_CF_VISITOR', 'detect_fail'))) {
			add_settings_error(SSLFIX_PLUGIN_OPTIONS, 'sslfix-proxy_fix', _x('HTTPS detection setting is invalid', 'settings error', 'ssl-insecure-content-fixer'));
			$this->has_settings_errors = true;
		}

		if (isset($input['fix_specific']) && is_array($input['fix_specific'])) {
			$output['fix_specific'] = array_map('intval', array_filter($input['fix_specific']));
		}
		else {
			$output['fix_specific'] = array();
		}

		return $output;
	}

	/**
	* set a cookie functioning like a nonce for the non-WP AJAX script
	*/
	public function setNonceCookie() {
		require SSLFIX_PLUGIN_ROOT . 'includes/nonces.php';

		$cookie_name  = ssl_insecure_content_fix_nonce_name(SSLFIX_PLUGIN_ROOT);
		$cookie_value = ssl_insecure_content_fix_nonce_value();

		setcookie($cookie_name, $cookie_value, time() + 30, '/');
	}

	/**
	* show SSL tests page
	*/
	public function testPage() {
		require SSLFIX_PLUGIN_ROOT . 'views/ssl-tests.php';

		$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$ver = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? time() : SSLFIX_PLUGIN_VERSION;

		$ajax_url = $this->getNoWpAJAX();

		wp_enqueue_script('sslfix-admin-settings', plugins_url("js/admin-tests$min.js", SSLFIX_PLUGIN_FILE), array('jquery'), $ver, true);
		wp_localize_script('sslfix-admin-settings', 'sslfix', array(
			'ajax_url_wp'	=> ssl_insecure_content_fix_url(admin_url('admin-ajax.php')),
			'ajax_url_ssl'	=> ssl_insecure_content_fix_url($ajax_url),
		));
	}

	/**
	* get path to non-WP AJAX script
	* @return string
	*/
	protected function getNoWpAJAX() {
		return plugins_url('nowp/ajax.php', SSLFIX_PLUGIN_FILE);
	}

	/**
	* get capability required for menu access
	* @return string
	*/
	protected function getMenuCapability() {
		return $this->isNetworkActivated() ? 'manage_network_options' : 'manage_options';
	}

	/**
	* test whether this plugin is network activated
	* @return bool
	*/
	protected function isNetworkActivated() {
		static $is_network_activated = null;

		if (is_null($is_network_activated)) {
			if (is_multisite()) {
				if (!function_exists('is_plugin_active_for_network')) {
					require_once ABSPATH . '/wp-admin/includes/plugin.php';
				}

				$is_network_activated = is_plugin_active_for_network(SSLFIX_PLUGIN_NAME);
			}
			else {
				$is_network_activated = false;
			}
		}

		return $is_network_activated;
	}

	/**
	* AJAX handler for testing HTTPS detection within WordPress
	*/
	public function ajaxTestHTTPS() {
		$response = array('https' => (is_ssl() ? 'yes' : 'no'));
		wp_send_json($response);
	}

}
