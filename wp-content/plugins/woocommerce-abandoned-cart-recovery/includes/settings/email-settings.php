<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 08-06-19
 * Time: 12:01 PM
 */

namespace WACVP\Inc\Settings;

use WACVP\Inc\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Settings extends Admin_Settings {

	protected static $instance = null;

	public function __construct() {
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function setting_page() {
		?>
        <div class="vi-ui bottom attached tab segment tab-admin" data-tab="email">

            <h4><?php esc_html_e( 'Email for Abandoned Cart', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->checkbox_option( 'send_email_to_member', __( "Send mail reminder to members", 'woo-abandoned-cart-recovery' ) );
				$this->checkbox_option( 'send_email_to_guest', __( "Send mail reminder to guest", 'woo-abandoned-cart-recovery' ) );
				$this->text_option( 'email_reply_address', __( "Reply Emails to", 'woo-abandoned-cart-recovery' ) );
				$this->send_email_rules_settings( 'email_rules' );
				$this->checkbox_option( 'email_to_admin_when_cart_recover', __( "Notification to Admin", 'woo-abandoned-cart-recovery' ), __( 'Send a notification email to admin whenever a cart is recovered', 'woo-abandoned-cart-recovery' ) );
				$this->checkbox_option( 'price_incl_tax', __( "Price include tax", 'woo-abandoned-cart-recovery' ), __( 'Display price include tax if tax is enabled & prices entered exclusive of tax', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>
            <hr>

            <h4><?php esc_html_e( 'Email for Abandoned Order', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$order_stt = array(
					'wc-failed'    => 'Failed',
					'wc-cancelled' => 'Cancelled',
					'wc-pending'   => 'Pending payment',
					'wc-on-hold'   => "On hold",
				);
				$this->checkbox_option( 'enable_reminder_order', __( "Enable", 'woo-abandoned-cart-recovery' ), __( '', 'woo-abandoned-cart-recovery' ) );
				$this->select_option( 'order_stt', $order_stt, __( "Order status", 'woo-abandoned-cart-recovery' ), '', '', true );
				$this->send_email_rules_settings( 'abd_orders' );
				?>
            </table>
            <hr>
            <table class="wacv-table">
				<?php
				$size_options = wp_get_additional_image_sizes();
				$r            = [ '' => esc_html__( 'Original' ) ];
				foreach ( $size_options as $key => $args ) {
					$height    = $args['height'] ? $args['height'] : 'auto';
					$width     = $args['width'] ? $args['width'] : 'auto';
					$r[ $key ] = "{$key} ({$width}x{$height})";
				}
				$this->select_option( 'image_size', $r, __( "Image size", 'woo-abandoned-cart-recovery' ), '', '' );
				?>
            </table>
            <p class="vi-ui yellow message"><?php esc_html_e( 'Note: Recover link will be not work with Admin role', 'woo-abandoned-cart-recovery' ); ?></p>
        </div>
		<?php
	}

	//Email Rules

	public function send_email_rules_settings( $slug ) {
		$data          = self::get_field( $slug );
		$list_template = Functions::get_email_template();
		?>
        <tr>
            <td class="col-1">
                <label><?php esc_html_e( 'Send mail rules', 'woo-abandoned-cart-recovery' ) ?></label>
            </td>

            <td class="col-2">
                <table class="vi-ui celled table wacv-email-rules-table">
                    <thead>
                    <tr>
                        <th><?php esc_html_e( 'Send after', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th><?php esc_html_e( 'Unit', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th><?php esc_html_e( 'Email template', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th><?php esc_html_e( 'Action', 'woo-abandoned-cart-recovery' ); ?></th>
                    </tr>
                    </thead>
                    <tbody class="wacv-<?php echo esc_attr($slug) ?>-row-target">
					<?php
					if ( isset( $data['time_to_send'] ) ) {
						$loop = count( $data['time_to_send'] );

						for ( $i = 0; $i < $loop; $i ++ ) { ?>
                            <tr class="wacv-<?php echo esc_attr($slug) ?>-row-target" data-index="<?php echo esc_attr($i) ?>">
                                <td class="vlt-padding-small cols-1">
                                    <input type="number" name="wacv_params[<?php echo esc_attr($slug) ?>][time_to_send][]"
                                           class="vlt-input vlt-border vlt-none-shadow vlt-round"
                                           value="<?php echo esc_attr($data['time_to_send'][ $i ]) ?>" min="1">
                                </td>
                                <td class="vlt-padding-small cols-2">
                                    <select name="wacv_params[<?php echo esc_attr($slug) ?>][unit][]"
                                            class="vlt-input vlt-border vlt-none-shadow vlt-round">
                                        <option value="minutes" <?php echo $data['unit'][ $i ] == 'minutes' ? 'selected' : ''; ?>><?php esc_html_e( 'minutes', 'woo-abandoned-cart-recovery' ); ?></option>
                                        <option value="hours" <?php echo $data['unit'][ $i ] == 'hours' ? 'selected' : ''; ?>><?php esc_html_e( 'hours', 'woo-abandoned-cart-recovery' ); ?></option>
                                    </select>
                                </td>
                                <td class="vlt-padding-small cols-3">
                                    <select name="wacv_params[<?php echo esc_attr($slug) ?>][template][]"
                                            class="wacv-select-email-template vlt-input vlt-border vlt-none-shadow vlt-round">
										<?php
										foreach ( $list_template as $template ) {
											$selected = '';
											if ( isset( $data['template'][ $i ] ) ) {
												$selected = $template['id'] == $data['template'][ $i ] ? 'selected' : '';
											}
											echo "<option value='" . $template['id'] . "' $selected>" . $template['value'] . "</option>";
										}
										?>
                                    </select>
                                </td>
                                <td align="center" class="vlt-padding-small cols-4">
                                    <button class="wacv-delete-<?php echo esc_attr($slug) ?> vi-ui small icon red button"
                                            type="button">
                                        <i class="trash icon"> </i>
                                    </button>
                                </td>
                            </tr>
						<?php }
					} ?>
                    </tbody>
                </table>
                <button type="button" class="wacv-add-<?php echo esc_attr($slug) ?> vi-ui small icon green button">
					<?php esc_html_e( 'Add rule', 'woo-abandoned-cart-recovery' ); ?>
                </button>
            </td>
            <td class="col-3"></td>
        </tr>
		<?php
	}
}
