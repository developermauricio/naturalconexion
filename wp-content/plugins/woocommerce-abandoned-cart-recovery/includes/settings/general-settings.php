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

class General_Settings extends Admin_Settings {

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
        <div class="vi-ui bottom attached active tab segment tab-admin" data-tab="general">
            <h4><?php esc_html_e( 'Cart', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php
				$this->checkbox_option( 'tracking_member', __( "Tracking Member's Cart", 'woo-abandoned-cart-recovery' ), __( 'If enable, the plugin will track abandoned cart of logged users', 'woo-abandoned-cart-recovery' ) );
				$this->number_option( 'member_cut_off_time', __( 'Abandoned Cart time for Members', 'woo-abandoned-cart-recovery' ), __( 'Select the time to mark a cart as abandoned with logged in users', 'woo-abandoned-cart-recovery' ), 'minutes' );
				$this->checkbox_option( 'tracking_guest', __( "Tracking Guest's Cart", 'woo-abandoned-cart-recovery' ), __( 'If enable, the plugin will track abandoned cart of guests', 'woo-abandoned-cart-recovery' ) );
				$this->number_option( 'guest_cut_off_time', __( 'Abandoned Cart time for Guest', 'woo-abandoned-cart-recovery' ), __( 'Select the time to mark a cart as abandoned with guests', 'woo-abandoned-cart-recovery' ), 'minutes' );
				$this->number_option( 'delete_record_time', __( 'Delete abandoned records', 'woo-abandoned-cart-recovery' ), __( 'Abandoned cart records will be automatically deleted after this time', 'woo-abandoned-cart-recovery' ), 'days' );

				$tracking_user_exclude_opts = array();
				if ( is_array( self::get_field( 'tracking_user_exclude' ) ) && count( self::get_field( 'tracking_user_exclude' ) ) > 0 ) {
					foreach ( get_users( array( 'include' => self::get_field( 'tracking_user_exclude' ) ) ) as $user ) {
						$tracking_user_exclude_opts[ $user->ID ] = $user->user_nicename;
					}
				}
				$this->select_option( 'tracking_user_exclude', $tracking_user_exclude_opts, __( "Exclude people", 'woo-abandoned-cart-recovery' ), '', '', true );

				$this->select_option(
					'direct_recover_link',
					[ __( 'Cart page', 'woo-abandoned-cart-recovery' ), __( 'Checkout page', 'woo-abandoned-cart-recovery' ) ],
					__( "Recover link to", 'woo-abandoned-cart-recovery' ) );

				$this->checkbox_option( 'enable_cart_log', __( "Cart logs", 'woo-abandoned-cart-recovery' ), __( 'If enable, add to cart or remove cart action will be record.', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>
            <hr>

            <table class="wacv-table">
                <tr class="vlt-row vlt-margin-top">
                    <td class="vlt-third vlt-margin-bottom-8 col-1">
                        <label style="font-weight: bold" class=""><?php esc_html_e( 'Cron job' ) ?></label>
                    </td>
                    <td class="vlt-twothird vlt-row col-2">
				        <?php
				        $cron_command = site_url() . '/wp-cron.php';
				        if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
					        ?>
                            <p>
						        <?php esc_html_e( "Your WP Cron is disabled. If you want to use WP Cron, open file wp-config.php and delete row define( \"DISABLE_WP_CRON\", true );.", 'woo-abandoned-cart-recovery' ); ?>
                            </p>
                            <p>
						        <?php esc_html_e( " If you want to use CronJob server, access your server and config with command:", 'woo-abandoned-cart-recovery' ); ?>
                            </p>
                            <p>
                                <input type="text" value="<?php echo( " * * * * * curl $cron_command" ) ?>" readonly>
                            </p>
							<?php
						} else {
							?>
                            <p>
								<?php esc_html_e( "Your WP Cron is enabled. If you want to use Cronjob server, open file wp-config.php and add row define( \"DISABLE_WP_CRON\", true );. Access your server and config with command:", 'woo-abandoned-cart-recovery' ); ?>
                            </p>
                            <div class="wacv-input-readonly-block">
                                <input type="text" class="wacv-readonly"
                                       value="<?php echo( " * * * * * curl $cron_command" ) ?>" readonly>
                                <span class="wacv-copy-icon">
                                  <i class="copy outline icon"></i>
                                </span>
                            </div>
				        <?php }
				        ?>
                    </td>
                    <td class="col-3"></td>
                </tr>
                <!--				--><?php //$this->checkbox_option( 'enable_cron_server', __( "Enable cron server", 'woo-abandoned-cart-recovery' ), __( 'If enable, command from Cron server will be accepted', 'woo-abandoned-cart-recovery' ) ); ?>
            </table>
        </div>
		<?php
	}

	//Email Rules

}
