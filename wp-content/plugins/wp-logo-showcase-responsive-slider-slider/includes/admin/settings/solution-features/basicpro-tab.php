<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package WP Logo Showcase Responsive Slider and Carousel
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>
<div id="wpls_basic_tabs" class="wpls-vtab-cnt wpls_basic_tabs wpls-clearfix">
	<h3 class="wpls-basic-heading">Compare <span class="wpls-blue">"Logo Showcase Responsive Slider"</span> Basic VS Pro</h3>
	<table class="wpos-plugin-pricing-table">
		<colgroup></colgroup>
		<colgroup></colgroup>
		<colgroup></colgroup>
		<thead>
			<tr>
				<th></th>
				<th>
					<h2><?php esc_html_e('Free', 'wp-logo-showcase-responsive-slider-slider'); ?></h2>
				</th>
				<th>
					<h2 class="wpos-epb" style="margin-bottom: 10px;"><?php esc_html_e('Premium', 'wp-logo-showcase-responsive-slider-slider'); ?></h2>
				</th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<th><?php esc_html_e('Designs', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Designs that make your website better', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td>1</td>
				<td>15+</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcodes', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Shortcode provide output to the front-end side', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td>2 (Slider, Center)</td>
				<td>6 (Grid,  Slider, Center, List, Ticker and Filter )</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcode Parameters', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Add extra power to the shortcode', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td>20</td>
				<td>30+</td>
			</tr>
			<tr>
				<th><?php esc_html_e('WP Templating Features', 'wp-logo-showcase-responsive-slider-slider'); ?><span class="subtext"><?php esc_html_e('You can modify plugin html/designs in your current theme.', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"> </i></td>
				<td><i class="dashicons dashicons-yes"> </i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Center Mode', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Display slider with center mode.', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Shortcode Generator', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Play with all shortcode parameters with preview panel. No documentation required!!', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Option Show/Hide Title', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Display logo title', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr> 
			<tr>
				<th><?php esc_html_e('Tooltip', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Enable tooltip on logo.', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Animation', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Enable animation effect on logo', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>   
			<tr>
				<th><?php esc_html_e('Animation Type', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Enable animation effect on logo', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td>0</td>
				<td>15</td>
			</tr>
			
			<tr>
				<th><?php esc_html_e('Drag & Drop Slide Order Change', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Arrange your desired slides with your desired order and display', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			
			<tr>
				<th><?php esc_html_e('Loop Control for slider', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Infinite scroll control', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Lazyload Support', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Add lazyload support for image.', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Gutenberg Block Supports', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Use this plugin with Gutenberg easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Elementor Page Builder Support', 'wp-logo-showcase-responsive-slider-slider'); ?> <em class="wpos-new-feature">New</em><span><?php esc_html_e('Use this plugin with Elementor easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Bevear Builder Support', 'wp-logo-showcase-responsive-slider-slider'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with Bevear Builder easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('SiteOrigin Page Builder Support', 'wp-logo-showcase-responsive-slider-slider'); ?> <em class="wpos-new-feature">New</em><span><?php esc_html_e('Use this plugin with SiteOrigin easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Divi Page Builder Native Support', 'wp-logo-showcase-responsive-slider-slider'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with Divi Builder easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
				<tr>
				<th><?php esc_html_e('Fusion Page Builder (Avada) native support', 'wp-logo-showcase-responsive-slider-slider'); ?> <em class="wpos-new-feature">New</em> <span><?php esc_html_e('Use this plugin with Fusion( Avada ) Builder easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Visual Composer Page Builder Supports', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Use this plugin with Visual Composer easily', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>       
			
			<tr>
				<th><?php esc_html_e('Display logos for Particular Categories', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Display only logos with particular category', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
					<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Exclude Logos', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Do not display the logos you want', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Exclude Some Categories', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Do not display the logos for particular categories', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-no-alt"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Logo Order / Order By Parameters', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Display logo according to date, title and etc', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Multiple Slider Parameters', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Slider parameters like autoplay, number of slide, sider dots and etc.', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Slider RTL Support', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Slider supports for RTL website', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><i class="dashicons dashicons-yes"></i></td>
				<td><i class="dashicons dashicons-yes"></i></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Automatic Update', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Get automatic  plugin updates', 'wp-logo-showcase-responsive-slider-slider'); ?></span></th>
				<td><?php esc_html_e('Lifetime', 'wp-logo-showcase-responsive-slider-slider'); ?></td>
				<td><?php esc_html_e('Lifetime', 'wp-logo-showcase-responsive-slider-slider'); ?></td>
			</tr> 
			<tr>
				<th><?php esc_html_e('Support', 'wp-logo-showcase-responsive-slider-slider'); ?><span><?php esc_html_e('Get support for plugin', 'wp-logo-showcase-responsive-slider-slider') ?></span></th>
				<td><?php esc_html_e('Limited', 'wp-logo-showcase-responsive-slider-slider') ?></td>
				<td><?php esc_html_e('1 Year', 'wp-logo-showcase-responsive-slider-slider') ?></td>
			</tr>
		</tbody>
	</table>
	<div class="wpls-deal-offer-wrap">
		<div class="wpls-deal-offer"> 
			<div class="wpls-inn-deal-offer">
				<h3 class="wpls-inn-deal-hedding"><span>Buy Logo Showcase Pro</span> today and unlock all the powerful features.</h3>
				<h4 class="wpls-inn-deal-sub-hedding"><span style="color:red;">Extra Bonus: </span>Users will get <span>15% off</span> on the regular price using this coupon code.</h4>
			</div>
			<div class="wpls-inn-deal-offer-btn">
				<div class="wpls-inn-deal-code"><span>EPS15</span></div>
				<a href="<?php echo WPLS_PLUGIN_BUNDLE_LINK; ?>"  target="_blank" class="wpls-sf-btn wpls-sf-btn-orange"><span class="dashicons dashicons-cart"></span> Get Essential Bundle Now</a>
			</div>
		</div>
	</div>
</div>