
<?php if (is_multisite() && !is_network_admin()): ?>
<tr class="active update plugin-update-tr" id="<?php echo esc_attr($r->slug . '-update'); ?>" data-slug="<?php echo esc_attr($r->slug); ?>" data-plugin="<?php echo esc_attr($file); ?>">
	<th class="check-column">&nbsp;</th>
	<td colspan="<?php echo esc_attr($colspan - 1); ?>" class="plugin-update">
		<div class="update-message" style="margin: 0">
			<?php printf(esc_html__('There is a new version of %s available.', 'ssl-insecure-content-fixer'), esc_html($plugin_name)); ?>
		</div>
	</td>
</tr>
<?php endif; ?>

<tr class="active update" id="<?php echo esc_attr($r->slug . '-update-custom-msg'); ?>" data-slug="<?php echo esc_attr($r->slug); ?>" data-plugin="<?php echo esc_attr($file); ?>">
	<th class="check-column">&nbsp;</th>
	<td colspan="<?php echo esc_attr($colspan - 1); ?>" class="plugin-update">
		<div class="update-message">
			<strong><?php echo esc_html($r->upgrade_notice); ?></strong>
		</div>
	</td>
</tr>
