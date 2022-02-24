<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 26-06-19
 * Time: 2:27 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="wacv-modal" class="wacv-modal-get-email template-1" tabindex="-1" style="display: none;">
    <div class="wacv-modal-wrapper">
        <div class="wacv-modal-content">
			<?php if ( ! $param['info_require'] ) : ?>
                <span class="wacv-close-popup">&times;</span>
			<?php endif; ?>
            <div class="wacv-container">
                <div class="wacv-get-email-title">
					<?php echo esc_html( stripslashes( $param['title_popup'] ) ) ?>
                </div>
                <div class="wacv-get-email-sub-title">
					<?php echo esc_html( stripslashes( $param['sub_title_popup'] ) ) ?>
                </div>

				<?php if ( $param['email_field'] ) { ?>
                    <div class="wacv-email-invalid-notice" style="color:red;">
						<?php echo esc_html( stripslashes( $param['invalid_email'] ) ) ?>
                    </div>
                    <div>
                        <input type="text" class="wacv-popup-input-email" placeholder="<?php esc_attr_e( 'Email', 'woo-abandoned-cart-recovery' ); ?>">
                    </div>
				<?php } ?>

				<?php if ( $param['phone_field'] ) { ?>
                    <div class="wacv-phone-number-invalid-notice" style="color:red;">
						<?php echo esc_html( stripslashes( $param['invalid_phone'] ) ) ?>
                    </div>
                    <div class="wacv-get-customer-phone-number">
						<?php
						$locate         = WC_Geolocation::geolocate_ip();
						$detect_country = $locate['country'] ?? '';
						?>
                        <select class="wacv-country-calling-code">
							<?php

							$codes = include WC()->plugin_path() . '/i18n/phone.php';
							ksort( $codes );
							foreach ( $codes as $country => $code ) {
								if ( ! $code ) {
									continue;
								}

								$selected = $detect_country == $country ? 'selected' : '';

								if ( is_array( $code ) ) {
									foreach ( $code as $c ) {
										$selected = $c == current( $code ) ? $selected : '';
										printf( '<option value="%s" %s>%s (%s)</option>', esc_attr( $c ), esc_attr( $selected ), esc_html( $country ), esc_html( $c ) );
									}
								} else {
									printf( '<option value="%s" %s>%s (%s)</option>', esc_attr( $code ), esc_attr( $selected ), esc_html( $country ), esc_html( $code ) );
								}
							}
							?>
                        </select>
                        <input type="text" class="wacv-popup-input-phone-number" placeholder="<?php esc_attr_e( 'Phone number', 'woo-abandoned-cart-recovery' ); ?>">
                    </div>
				<?php }

				if ( $param['app_id'] && $param['app_secret'] && $param['user_token'] && ( $param['single_page'] || $param['shop_page'] || $param['cart_page'] || $param['front_page'] ) ) {
					?>
                    <div>
                        <div class="fb-messenger-checkbox-container"></div>
                    </div>
				<?php }

				if ( $param['enable_gdpr'] && $param['gdpr_content'] ) {
					?>
                    <div class="wacv-gdpr-group">
                        <input type="checkbox" class="wacv-gdpr-checkbox">
                        <span class="wacv-gdpr-content">
                            <?php echo wp_kses_post( html_entity_decode( $param['gdpr_content'] ) ) ?>
                        </span>
                    </div>
				<?php } ?>

                <div class="wacv-get-email-btn-group">
                    <button type="button" class="wacv-get-email-btn wacv-add-to-cart-btn wacv-btn-first">
						<?php echo esc_html( stripslashes( $param['add_to_cart_btn'] ) ) ?>
                    </button>
                </div>
				<?php do_action( 'wacv_popup_footer' ); ?>
            </div>
        </div>
    </div>
</div>
