<?php
/**
 * Settings Page
 *
 * @package Wpos Analytic
 * @since 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<style type="text/css">
	.notice, .error, div.fs-notice.updated, div.fs-notice.success, div.fs-notice.promotion{display:none !important;}

	/* Some CSS  */
	/*.wpos-anylc-admin-wrap{text-align: center; padding:15px 0;}
	.wpos-anylc-line-style{font-style: italic; text-decoration: underline; color: #000; font-weight: 700; letter-spacing: 1px;}
	.wpos-anylc-email-line{ letter-spacing: 1px; position: relative; padding: 0.75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 0.25rem; color: #000; background-color: #F8F8F8; border-color: #F8F8F8;}
	.wpos-anylc-nl-wrap{ letter-spacing: 1px; position: relative; padding: 0.75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 0.25rem; color: #000; background-color: #F8F8F8; border-color: #F8F8F8;}
	.wpos-anylc-skip-btn{border-color:#404040 !important; color: #404040 !important;}
	.wpos-anylc-black-wrap{color: #000; font-weight: 700;}
	.wpos-anylc-list-wrap{margin-left: 10px;}
	.wpos-anylc-list-wrap li span{font-style: italic; color:  #000; font-weight: 700; letter-spacing: 1px;}
	.wpos-anylc-best-regard{ text-align: center; font-style: italic; color:#000;font-weight: 700; letter-spacing: 1px; margin-top: 25px;}
	.wpos-anylc-optin-action{display: flex;align-items: center; background-color: #F8F8F8;}
	.wpos-anylc-subs-btn{width: 50%;}
	.wpos-anylc-subs-btn .wpos-anylc-allow-btn{background: #FF0000; border-color:#FF0000;}
	.wpos-anylc-subs-btn .wpos-anylc-allow-btn:hover{background:#D22B2B; border-color:#D22B2B;}
	.wpos-anylc-btn-skip{width: 50%; display: flex; flex-direction: column;align-items: flex-start;}
	.wpos-anylc-btn-skip span{font-weight: 500;font-size:12px; font-style:italic; margin-right: 20px;}
	.wpos-anylc-btn-skip .wpos-anylc-skip-btn{font-size: 12px;}
	.wpos-anylc-terms{background-color:#F8F8F8;}*/
</style>

<div class="wrap wpos-anylc-optin">

	<?php if( isset($_GET['error']) && $_GET['error'] == 'wpos_anylc_error' ) { ?>
	<div class="error">
		<p><strong>Sorry, Something happened wrong. Please contact us on <a href="mailto:support@wponlinesupport.com">support@wponlinesupport.com</a></strong></p>
	</div>
	<?php } ?>

	<form method="POST" action="https://analytics.wponlinesupport.com">
		<div class="wpos-anylc-optin-wrap" style="width: 650px; margin: 0 auto; margin-top: 70px;">

			<div>
				<div style="height:50px; text-align: center; background-color: rgba(180,185,190, 0.2);">
					<img style="position: relative; top:-40px; border-radius:50%;" src="<?php echo esc_url( $analy_product['icon'] ); ?>" alt="Icon" />
				</div>
				<div style="padding: 10px;">
					<div style="margin-top:50px; margin-bottom: 30px; text-align: center; font-weight: 700; font-size: 24px;">Never miss an important update</div>

					<div style="font-size: 20px; font-weight: 500; line-height:25px; margin: 10px 12px; color:#646970;">Opt-in to get email notifications for security & feature updates, and to share some basic WordPress environment info. This will help us make the plugin more compatible with your site and better at doing what you need it to.</div>
				</div>
			</div>			

			<!-- <div class="wpos-anylc-optin-icon-wrap" style="text-align: center; background-color: rgba(180,185,190, 0.3);">
				<div class="wpos-anylc-optin-icon wpos-anylc-wp-badge"><i class="dashicons dashicons-wordpress"></i></div>
				<div class="wpos-anylc-optin-plus"><i class="dashicons dashicons-plus"></i></div>
				<div class="wpos-anylc-optin-icon"><img src="<?php //echo esc_url( $analy_product['icon'] ); ?>" alt="Icon" /></div>
				<div class="wpos-anylc-optin-plus"><i class="dashicons dashicons-plus"></i></div>
				<div class="wpos-anylc-optin-icon"><img src="<?php //echo esc_url( $analy_product['brand_icon'] ); ?>" alt="Icon" /></div>
			</div> -->
			<!-- <div class="wpos-anylc-optin-cnt"> -->
				<!-- <p>Hey <?php //echo ucfirst( $user_name ); ?>,</p>
				<p>Don't ever miss an opportunity to <b>opt in</b> for Email Notifications / Announcements about exciting New Features and Update Releases.</p>
				<p>Contribute in helping us making <b><?php //echo esc_html( $product_name ); ?></b> compatible with most themes and plugins by allowing to share non-sensitive data to <a target="_blank" href="https://www.essentialplugin.com/">essentialplugin.com</a> about your website.</p>
				<p>If you skip this, that's okay! <b><?php //echo esc_html( $product_name ); ?></b> will still work just fine.</p> -->

				<!-- <div class="wpos-anylc-admin-wrap"><span class="wpos-anylc-black-wrap">Hey! &#128100; <?php //echo ucfirst( $user_name ); ?>,</span> Opt in Bonuses, Information & &#127873; <span class="wpos-anylc-line-style">Premium Gifts</span> for YOU</div>

				<div class="wpos-anylc-email-line">Opt- in subscribers will have - &#128233; Email notifications, security features, update releases for <span class="wpos-anylc-line-style"><?php //echo esc_html( $product_name ); ?></span></div>
				<div>
					<p>Globally, <span class="wpos-anylc-black-wrap">49%</span> of users want to receive <span class="wpos-anylc-line-style">Promotional Emails</span> about the brands through emails.</p>
				</div>

				<div class="wpos-anylc-nl-wrap">
				<p>Our newsletter also contains topics about:</p>
				<ul class="wpos-anylc-list-wrap">
					<li>1. <span>WordPress (WP)</span> &#8702; From Matt Mullenweg to Smallest of WP Version Upgrades</li>
					<li>2. <span>Digital Marketing</span> &#8702; From Traffic to Conversions</li>
					<li>3. <span>General Tech</span> &#8702; From WEB to chatGPT to EV </li>
				</ul>
				</div>
				<div>
					<p><span class="wpos-anylc-black-wrap">42%</span> of our Email users are benefited with our shared useful knowledge, content & first email will come with &#127873; <span class="wpos-anylc-line-style">Premium Gifts.</span></p>
				</div>
				<div class="wpos-anylc-best-regard">With Best Regards, &#128591; Namaste! & Universal Blessings</div> -->

				<?php if( ! empty( $analy_product['promotion'] ) ) { ?>
				<div class="wpos-anylc-promotion-wrap">
					<?php foreach( $analy_product['promotion'] as $promotion_key => $promotion_data ) { ?>
					<div><label><input type="checkbox" value="<?php echo esc_attr( $promotion_key ); ?>" name="promotion[]" checked="checked" /> <?php echo esc_html( $promotion_data['name'] ); ?></label></div>
					<?php } ?>
				</div>
				<?php } ?>
			<!-- </div> -->
			<div class="wpos-anylc-optin-action wpos-anylc-clearfix" style="background-color: rgba(180,185,190, 0.3);">

				<button type="submit" name="wpos_anylc_optin" class="button button-primary button-large wpos-anylc-allow-btn" value="wpos_anylc_optin">Allow and Continue</button>

				<?php if( is_null( $opt_in ) ) { ?>
				<button type="submit" name="wpos_anylc_action" class="button button-secondary button-large right wpos-anylc-skip-btn" value="skip" style="padding: 0 !important;background: transparent;border: none;">Skip</button>
				<?php }

				if( ! empty( $optin_form_data ) ) {
				 	foreach ($optin_form_data as $data_key => $data_value) {
				 		echo '<input type="hidden" name="'.esc_attr( $data_key ).'" value="'.esc_attr( $data_value ).'" />';
				 	}
				} ?>

				<!-- <div class="wpos-anylc-btn-skip">
					<?php //if( is_null( $opt_in ) ) { ?>
					<button type="submit" name="wpos_anylc_action" class="button button-secondary button-large wpos-anylc-skip-btn" value="skip">I'm fine without subscription!</button>
					<?php //}

					//if( ! empty( $optin_form_data ) ) {
					//	foreach ($optin_form_data as $data_key => $data_value) {
					//		echo '<input type="hidden" name="'.esc_attr( $data_key ).'" value="'.esc_attr( $data_value ).'" />';
					//	}
				//	} ?>
				</div> -->
				<!-- <div class="wpos-anylc-subs-btn">
					<button type="submit" name="wpos_anylc_optin" class="button button-primary right button-large wpos-anylc-allow-btn" value="wpos_anylc_optin">OK LETS START SUBSCRIBE</button>
				</div> -->
			</div>
			<div class="wpos-anylc-optin-permission">
				<a class="wpos-anylc-permission-toggle" href="javascript:void(0);">What permissions are being granted?</a>

				<div class="wpos-anylc-permission-wrap wpos-anylc-hide">
					<div class="wpos-anylc-permission">
						<i class="dashicons dashicons-admin-users"></i>
						<div>
							<span class="wpos-anylc-permission-name">Your Profile Overview</span>
							<span class="wpos-anylc-permission-info">Name and email address</span>
						</div>
					</div>
					<div class="wpos-anylc-permission">
						<i class="dashicons dashicons-admin-settings"></i>
						<div>
							<span class="wpos-anylc-permission-name">Your Site Overview</span>
							<span class="wpos-anylc-permission-info">Site URL, WP version, PHP info & Theme</span>
						</div>
					</div>
					<div class="wpos-anylc-permission">
						<i class="dashicons dashicons-admin-plugins"></i>
						<div>
							<span class="wpos-anylc-permission-name">Current Plugin Events</span>
							<span class="wpos-anylc-permission-info">Activation, Deactivation and Uninstall</span>
						</div>
					</div>
				</div>
			</div>
			<div class="wpos-anylc-terms">
				<a href="https://www.essentialplugin.com/privacy-policy/#free-pluign-info" target="_blank">Privacy Policy</a> - <a href="https://www.essentialplugin.com/term-and-condition/" target="_blank">Terms of Service</a>
			</div>
		</div>
	</form>
</div><!-- end .wrap -->