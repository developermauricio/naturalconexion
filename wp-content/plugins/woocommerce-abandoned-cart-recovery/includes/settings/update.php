<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 08-06-19
 * Time: 12:01 PM
 */

namespace WACVP\Inc\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Update extends Admin_Settings {

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
        <div id="" class="vi-ui bottom attached tab segment tab-admin" data-tab="update">
            <table class="wacv-table">
				<?php $this->text_option( 'update_key', __( "Auto update key", 'woo-abandoned-cart-recovery' ) ); ?>
                <tr>
                    <td></td>
                    <td>
                        <p class="description"><?php _e( 'Please fill your key what you get from <a target="_blank" href="https://villatheme.com/my-download">https://villatheme.com/my-download</a>. See <a target="_blank" href="https://villatheme.com/knowledge-base/how-to-use-auto-update-feature/">guide</a>.', 'woocommerce-lucky-wheel' ) ?>
                        </p>
                        <span class="vi-ui button green villatheme-get-key-button"
                              data-href="https://api.envato.com/authorization?response_type=code&client_id=villatheme-download-keys-6wzzaeue&redirect_uri=https://villatheme.com/update-key"
                              data-id="24089125"><?php echo esc_html__( 'Get Key', 'woo-abandoned-cart-recovery' ) ?></span>
                    </td>
                </tr>

            </table>
        </div>
		<?php
	}
}
