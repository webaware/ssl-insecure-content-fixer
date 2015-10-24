<?php
// settings form
?>

<div class="wrap">
	<h2><?php esc_html_e('SSL Insecure Content Fixer tests', 'ssl-insecure-content-fixer'); ?></h2>

	<p><?php esc_html_e('This page checks to see whether WordPress can detect HTTPS.', 'ssl-insecure-content-fixer'); ?></p>

	<div id="sslfix-loading">
		<p><?php esc_html_e('Running tests...', 'ssl-insecure-content-fixer'); ?>
		<img src="<?php echo esc_url(plugins_url('images/ajax-loader.gif', SSLFIX_PLUGIN_FILE)); ?>" />
		</p>
	</div>

	<h3 id="sslfix-test-result-head"><?php esc_html_e('Tests completed.', 'ssl-insecure-content-fixer'); ?><i id="sslfix-https-detection"></i></h3>

	<div class="sslfix-test-result" id="sslfix-normal">
		<p><?php printf(esc_html__('Your server can detect HTTPS normally. The recommended setting for HTTPS detection is %s.', 'ssl-insecure-content-fixer'), sprintf('<strong>%s</strong>', _x('standard WordPress function', 'proxy settings', 'ssl-insecure-content-fixer'))); ?></p>
	</div>

	<div class="sslfix-test-result" id="sslfix-HTTP_X_FORWARDED_PROTO">
		<p><?php printf(esc_html__('It looks like your server is behind a reverse proxy. The recommended setting for HTTPS detection is %s.', 'ssl-insecure-content-fixer'), '<strong>HTTP_X_FORWARDED_PROTO</strong>'); ?></p>
	</div>

	<div class="sslfix-test-result" id="sslfix-HTTP_X_FORWARDED_SSL">
		<p><?php printf(esc_html__('It looks like your server is behind a reverse proxy. The recommended setting for HTTPS detection is %s.', 'ssl-insecure-content-fixer'), '<strong>HTTP_X_FORWARDED_SSL</strong>'); ?></p>
	</div>

	<div class="sslfix-test-result" id="sslfix-HTTP_CF_VISITOR">
		<p><?php printf(esc_html__('It looks like your server uses CloudFlare Flexible SSL. The recommended setting for HTTPS detection is %s.', 'ssl-insecure-content-fixer'), '<strong>HTTP_CF_VISITOR</strong>'); ?></p>
	</div>

	<div class="sslfix-test-result" id="sslfix-detect_fail">
		<p><?php printf(esc_html__('Your server cannot detect HTTPS. The recommended setting for HTTPS detection is %s.', 'ssl-insecure-content-fixer'), sprintf('<strong>%s</strong>', _x('unable to detect HTTPS', 'proxy settings', 'ssl-insecure-content-fixer'))); ?></p>
		<p><?php printf(__('If you know of a way to detect HTTPS on your server, please <a href="%s" target="_blank">tell me about it</a>.', 'ssl-insecure-content-fixer'), 'http://shop.webaware.com.au/support/'); ?></p>
	</div>

	<div class="sslfix-test-result" id="sslfix-environment">
		<p><?php esc_html_e('Your server environment shows this:', 'ssl-insecure-content-fixer'); ?></p>
		<pre></pre>
	</div>

</div>
