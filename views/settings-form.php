<?php
// settings form for single site / blog
?>

<div class="wrap">
	<h2><?php esc_html_e('SSL Insecure Content Fixer settings', 'ssl-insecure-content-fixer'); ?></h2>

	<form action="<?php echo esc_url(admin_url('options.php')); ?>" method="POST">
		<?php settings_fields(SSLFIX_PLUGIN_OPTIONS); ?>

		<table class="form-table">

			<?php require SSLFIX_PLUGIN_ROOT . 'views/settings-fields-common.php'; ?>

		</table>

		<?php submit_button(); ?>
	</form>
</div>
