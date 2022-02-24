<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 08-06-19
 * Time: 2:35 PM
 */

namespace WACVP\Inc\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SMS_Settings extends Admin_Settings {

	protected static $instance = null;

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function setting_page() {
		$provider = self::$data['sms_provider'];
		?>
        <div class="vi-ui bottom attached tab segment tab-admin" data-tab="sms">
            <h4><?php esc_html_e( 'SMS account config', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php $this->select_option( 'sms_provider',
					$option = array(
						'twilio' => 'Twilio',
						'nexmo'  => 'Nexmo',
						'plivo'  => 'Plivo'
					),
					__( 'SMS provider', 'woo-abandoned-cart-recovery' ) ) ?>
            </table>
            <div class="wacv-providers">
                <div class="wacv-twilio-config" <?php $this->show( $provider, 'twilio' ) ?>>
                    <table class="wacv-table">
						<?php
						$this->text_option( 'sms_app_id', __( 'App ID', 'woo-abandoned-cart-recovery' ) );
						$this->text_option( 'sms_app_secret', __( 'App token', 'woo-abandoned-cart-recovery' ) );
						$this->text_option( 'from_phone', __( 'From number', 'woo-abandoned-cart-recovery' ), 'E.g: +1234567890' );
						?>
                    </table>
                </div>
                <div class="wacv-nexmo-config" <?php $this->show( $provider, 'nexmo' ) ?>>
                    <table class="wacv-table">
						<?php
						$this->text_option( 'sms_app_id_nexmo', __( 'App ID', 'woo-abandoned-cart-recovery' ) );
						$this->text_option( 'sms_app_secret_nexmo', __( 'App token', 'woo-abandoned-cart-recovery' ) );
						$this->text_option( 'from_phone_nexmo', __( 'From number', 'woo-abandoned-cart-recovery' ) );
						?>
                    </table>
                </div>
                <div class="wacv-plivo-config" <?php $this->show( $provider, 'plivo' ) ?>>
                    <table class="wacv-table">
						<?php
						$this->text_option( 'sms_app_id_plivo', __( 'Auth ID', 'woo-abandoned-cart-recovery' ) );
						$this->text_option( 'sms_app_secret_plivo', __( 'Auth secret', 'woo-abandoned-cart-recovery' ) );
						//						$this->text_option( 'from_phone_plivo', __( 'From number', 'woo-abandoned-cart-recovery' ) );
						$this->text_option( 'powerpack_uuid', __( 'Powerpack UUID', 'woo-abandoned-cart-recovery' ) );
						?>
                    </table>
                </div>
            </div>
            <h4><?php esc_html_e( 'Bitly config', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->checkbox_option( 'shortlink_enable', __( 'Use shortlink', 'woo-abandoned-cart-recovery' ) );
				$this->text_option( 'shortlink_access_token', __( 'Access token', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>

            <h4><?php esc_html_e( 'Send test SMS', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
                <tr>
                    <td class="col-1" valign="top">
						<?php esc_html_e( 'To number', 'woo-abandoned-cart-recovery' ); ?>
                    </td>
                    <td class="col-2">
                        <input type="text" class="wacv-to-phone-number" placeholder="E.g: +1234567890">
                    </td>
                </tr>
                <tr>
                    <td class="col-1" valign="top"></td>
                    <td class="col-2">
                        <button type="button" class="wacv-send-test-sms vi-ui small icon green button">
							<?php esc_html_e( 'Send', 'woo-abandoned-cart-recovery' ); ?>
                        </button>
                        <span class="wacv-send-test-sms-notice"></span>
                    </td>
                    <td class="col-3"></td>
                </tr>
                <tr>
                    <td class="col-1" valign="top">
                    </td>
                    <td class="col-2">
                        <div class="vi-ui styled accordion wacv-accor">
                            <div class="title">
                                <i class="dropdown icon"></i>
								<?php esc_html_e( 'Config guide', 'woo-abandoned-cart-recovery' ) ?>
                            </div>
                            <div class="content">
                                <ul>
                                    <li><?php _e( '1. Create an account at ', 'woo-abandoned-cart-recovery' ) ?></li>
                                    <li>
                                        <a href="https://www.twilio.com" target="_blank" rel="nofollow">https://www.twilio.com,</a>
                                        <a href="https://www.nexmo.com" target="_blank" rel="nofollow">https://www.nexmo.com</a>
										<?php _e( 'or', 'woo-abandoned-cart-recovery' ) ?>
                                        <a href="https://www.plivo.com" target="_blank" rel="nofollow">https://www.plivo.com</a>
                                    </li>
                                    <li><?php _e( '2. Login your SMS account ', 'woo-abandoned-cart-recovery' ) ?></li>
                                    <li><?php _e( '3. Copy app id, app token & phone number to input fields above. ', 'woo-abandoned-cart-recovery' ) ?></li>
                                    <li><?php _e( '4. Create an bitly\'s account at ', 'woo-abandoned-cart-recovery' ) ?>
                                        <a href="https://bitly.com/" target="_blank"
                                           rel="nofollow">https://bitly.com/</a>
                                    </li>
                                    <li><?php _e( '5. Login bitly\'s account ', 'woo-abandoned-cart-recovery' ) ?></li>
                                    <li><?php _e( '6. Go to Settings > Edit profile > Generic Access Token, enter your password & click \'Generate Token\' ', 'woo-abandoned-cart-recovery' ) ?></li>
                                    <li><?php _e( '7. Copy your access token to access token field & save settings', 'woo-abandoned-cart-recovery' ) ?></li>
                                    <li>
                                        <iframe width="560" height="315"
                                                src="https://www.youtube.com/embed/d2dAjh3BTC8?feature=oembed"
                                                frameborder="0" allow="autoplay; encrypted-media"
                                                allowfullscreen></iframe>
                                    </li>
                                    <li>
                                        <iframe width="560" height="315"
                                                src="https://www.youtube.com/embed/hkSSTrJJCwA?feature=oembed"
                                                frameborder="0" allow="autoplay; encrypted-media"
                                                allowfullscreen></iframe>
                                    </li>
                                    <li>
                                        <iframe width="560" height="315"
                                                src="https://www.youtube.com/embed/r9ubdfQvd90?feature=oembed"
                                                frameborder="0" allow="autoplay; encrypted-media"
                                                allowfullscreen></iframe>
                                    </li>
                                    <li><a href="http://docs.villatheme.com/?item=woocommerce-abandoned-cart-recovery"
                                           target="_blank" rel="nofollow">
											<?php esc_html_e( 'See detail', 'woo-abandoned-cart-recovery' ); ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <hr>

            <h4><?php esc_html_e( 'SMS for Abandoned Cart', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->checkbox_option( 'sms_abd_cart_enable', __( 'Enable', 'woo-abandoned-cart-recovery' ) );
				$this->send_message_rules_settings( 'sms_abd_cart' );
				?>
                <tr>
                    <td></td>
                    <td><?php esc_html_e( 'Note: SMS character limit is 160 with GSM-7 encoding & 63 with other encoding (Ex:', 'woo-abandoned-cart-recovery' );
						echo ' 👋)'; ?></td>
                </tr>
            </table>
            <hr>

            <h4><?php esc_html_e( 'SMS for Abandoned Order', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->checkbox_option( 'sms_abd_order_enable', __( 'Enable', 'woo-abandoned-cart-recovery' ) );
				$order_stt = array(
					'wc-failed'    => 'Failed',
					'wc-cancelled' => 'Cancelled',
					'wc-pending'   => 'Pending payment',
					'wc-on-hold'   => "On hold",
				);
				$this->select_option( 'sms_order_stt', $order_stt, __( "Order status", 'woo-abandoned-cart-recovery' ), '', '', true );
				$this->send_message_rules_settings( 'sms_abd_order' );
				?>
            </table>
        </div>
		<?php
	}

	public function show( $provider, $current ) {
		if ( $provider == $current ) {
			echo "style='display:block;'";
		} else {
			echo "style='display:none;'";
		}
	}
}
