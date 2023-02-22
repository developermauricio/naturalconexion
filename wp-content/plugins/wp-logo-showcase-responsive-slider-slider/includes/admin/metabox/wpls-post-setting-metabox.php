<?php
/**
 * Function Custom meta box for slider link
 * 
 * @package WP Logo Showcase Responsive Slider
 * @since 1.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

$prefix		= WPLS_META_PREFIX; // Metabox prefix
$logo_link	= get_post_meta( $post->ID, 'wplss_slide_link', true );
?>

<table class="form-table wpls-metabox-table">
	<tbody>
		<tr>
			<th>
				<label for="wpls-logo-link"><?php esc_html_e( 'Logo Link', 'wp-logo-showcase-responsive-slider-slider' ); ?></label>
			</th>
			<td>
				<input type="url" value="<?php echo esc_url( $logo_link ); ?>" class="large-text wpls-logo-link" id="wpls-logo-link" name="<?php echo esc_attr( $prefix ); ?>logo_link" /><br/>
				<span class="description"><?php esc_html_e('Enter link URL for logo. i.e.', 'wp-logo-showcase-responsive-slider-slider'); ?> https://www.essentialplugin.com/</span>
			</td>
		</tr>

		<tr class="wpls-pro-feature">
			<th>
				<?php esc_html_e('Logo Description', 'wp-logo-showcase-responsive-slider-slider'); ?><span class="wpls-pro-tag"><?php esc_html_e('PRO','wp-logo-showcase-responsive-slider-slider');?></span>
			</th>
			<td>
				<textarea name="<?php echo esc_attr($prefix); ?>logo_desc" class="large-text" rows="4" disabled></textarea><br/>
				<span class="description"><?php esc_html_e('Enter logo description using default wordpress content editor.', 'wp-logo-showcase-responsive-slider-slider'); ?></span>
				<strong><?php echo sprintf( __( 'Utilize this <a href="%s" target="_blank">Premium Features</a> to get best of this plugin with  Annual or Lifetime bundle deal.', 'wp-logo-showcase-responsive-slider-slider'), WPLS_PLUGIN_LINK_UNLOCK); ?></strong>
			</td>
		</tr>

		<tr class="wpls-pro-feature">
			<th>
				<?php esc_html_e('Logo Image URL', 'wp-logo-showcase-responsive-slider-slider'); ?><span class="wpls-pro-tag"><?php esc_html_e('PRO','wp-logo-showcase-responsive-slider-slider');?></span>
			</th>
			<td>
				<input type="url" value="" class="large-text wpls-logo-url" id="wpls-logo-url" name="<?php echo esc_attr( $prefix ); ?>logo_url" disabled /><br/>
				<span class="description"><?php esc_html_e('Enter external URL of logo. If you don not want to use an image from your media gallery, you can set an URL for logo image here.', 'wp-logo-showcase-responsive-slider-slider'); ?></span>
				<strong><?php echo sprintf( __( 'Utilize this <a href="%s" target="_blank">Premium Features</a> to get best of this plugin with  Annual or Lifetime bundle deal.', 'wp-logo-showcase-responsive-slider-slider'), WPLS_PLUGIN_LINK_UNLOCK); ?></strong>
			</td>
		</tr>
	</tbody>
</table><!-- end .wpls-metabox-table -->