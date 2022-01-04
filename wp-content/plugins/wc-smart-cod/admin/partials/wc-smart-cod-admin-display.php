<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.linkedin.com/in/stratos-vetsos-08262473/
 * @since      1.0.0
 *
 * @package    Wc_Smart_Cod
 * @subpackage Wc_Smart_Cod/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wsc-columns">
	<div class="wsc-content">
		<div class="wc-smart-cod-info">
			<h4><?php echo WC_Smart_Cod::$plugin_friendly_name; ?></h4>
			<p>Version: <strong><?php echo WC_Smart_Cod::$version; ?></strong></p>
		</div>
		<table class="form-table">
			<?php echo $template_data['settings_html']; ?>
		</table>
	</div>
	<div class="wsc-sidebar">
		<div class="card">
			<h2>WooCommerce Smart COD PRO</h2>
			<p><?php echo $template_data['promo_texts']['sidebar']; ?></p>
			<p><strong><?php echo $template_data['coupon']; ?></strong></p>
			<ul>
				<?php foreach( $template_data['promo_texts']['features'] as $feature ) : ?>
					<li><?php echo $feature; ?></li>
				<?php endforeach; ?>
			</ul>
			<p>and many more</p>
				<a class="button button-primary" target="_blank" rel="noopener" href="<?php echo esc_url( $template_data['pro_url'] ); ?>?utm_source=plugin&utm_medium=settings">
					Learn more                        
				</a>
		</div>
	</div>
</div>