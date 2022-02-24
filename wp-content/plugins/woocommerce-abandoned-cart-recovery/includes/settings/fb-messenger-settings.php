<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 08-06-19
 * Time: 2:35 PM
 */

namespace WACVP\Inc\Settings;

use WACVP\Inc\Data;
use WACVP\Inc\Facebook\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FB_Messenger_Settings extends Admin_Settings {

	protected static $instance = null;

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'fb_token_expire_notice' ) );
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function fb_token_expire_notice() {
		$data = Data::get_params();
		if ( $data['app_id'] && $data['app_secret'] ) {
			$check = get_transient( 'wacv_fb_user_token_live' );
			if ( ! $check ) {
				$mess = esc_html__( 'Your facebook user token is expired, please re-connect to continue using', 'woo-abandoned-cart-recovery' );
				echo "<div class='notice notice-warning is-dismissible'><p>{$mess}</p><button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
			}
		}
	}

	public function setting_page() {
		$data           = Data::get_params();
		$link_login     = $check = '';
		$show_field     = false;
		$link_call_back = add_query_arg( array( 'page' => 'wacv_settings' ), admin_url( 'admin.php' ) );
		$user_token     = $data['user_token'];
		if ( $data['app_id'] && $data['app_secret'] ) {
			$fb_api     = Api::get_instance();
			$check      = $user_token ? $fb_api->check_token_live( $user_token ) : false;
			$link_login = $fb_api->get_link_login(
				$link_call_back,
				array(
					'email',
					'public_profile',
					'pages_messaging',
					'pages_show_list',
					'pages_manage_metadata',
					//'pages_read_engagement',
				) );

			if ( isset( $_GET['code'] ) ) {
				$token      = $fb_api->get_Token( $link_call_back );
				$user_token = $fb_api->extoken( $token );

				if ( $user_token == "error" ) {
					$link_call_back = $link_call_back . "&wacv_error=error";
					wp_safe_redirect( $link_call_back );
				} else {
					$new_data = ( wp_parse_args( array( 'user_token' => $user_token ), $data ) );
					update_option( 'wacv_params', $new_data );
					set_transient( 'wacv_fb_user_token_live', true, 90 * DAY_IN_SECONDS );
					wp_safe_redirect( $link_call_back );
				}
				exit();
			}
		}
		do_action( 'wacv_before_fb_settings' );
		?>
        <div class="vi-ui bottom attached tab segment tab-admin" data-tab="facebook">
            <h4><?php esc_html_e( 'Connect', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
                <input type="hidden" name="wacv_params[user_token]" value="<?php echo esc_attr( $user_token ) ?>">
				<?php
				$this->text_option( 'app_id', __( 'App ID', 'woo-abandoned-cart-recovery' ) );
				$this->text_option( 'app_secret', __( 'App secret', 'woo-abandoned-cart-recovery' ) );
				$this->text_option_read_only( admin_url( 'admin.php?page=wacv_settings' ), __( 'Valid OAuth redirected URls:', 'woo-abandoned-cart-recovery' ) );
				$this->text_option_read_only( admin_url( 'admin-ajax.php?action=wacv_fb_message' ), __( 'Callback Webhooks URL', 'woo-abandoned-cart-recovery' ) );
				$this->text_option_read_only( $data['app_verify_token'], __( 'Verify Token', 'woo-abandoned-cart-recovery' ), 'app_verify_token', 'wacv-change-token', 'sync alternate', __( 'Change webhooks verify token.', "woo-abandoned-cart-recovery" ) );
				?>
            </table>
			<?php
			if ( ! $check && $link_login ) {
				?>
                <table class='wacv-table'>
                    <tr>
                        <td class="col-1"></td>
                        <td class="col-2">
                            <a class="vi-ui primary button wacv-btn" href="<?php echo esc_url( $link_login ); ?>">
								<?php esc_html_e( 'Login Facebook', 'woo-abandoned-cart-recovery' ) ?>
                            </a>
                        </td>
                        <td class="col-3"></td>
                    </tr>
                </table>
				<?php
			} else {
				if ( $user_token && $data['app_id'] && $data['app_secret'] ) {
					$list_page   = $fb_api->Get_List_Page( $user_token );
					$page_option = array();
					if ( isset( $list_page['accounts'] ) ) {
						$show_field = true;
						?>
                        <table class='wacv-table'>
                            <tr>
                                <td class="col-1"></td>
                                <td class="col-2">
                                    <button type="button" class="wacv-log-out-fb wacv-btn vi-ui small icon red button">
										<?php esc_html_e( 'Log out Facebook', 'woo-abandoned-cart-recovery' ); ?>
                                    </button>
                                </td>
                                <td class="col-3"></td>
                            </tr>
                        </table>
                        <hr>

                        <h4><?php esc_html_e( 'Config', 'woo-abandoned-cart-recovery' ) ?></h4>
						<?php
						foreach ( $list_page['accounts'] as $page ) {
							$page_option[ $page['id'] ] = $page['name'];
						}
						$list_opt = count( $page_option ) > 0 ? $page_option : array( __( 'You haven\'t had any page. Create page before complete settings', 'woo-abandoned-cart-recovery' ) );
						?>
                        <table class='wacv-table'>
							<?php $this->select_option( 'page_id', $list_opt, __( 'Active page', 'woo-abandoned-cart-recovery' ) ); ?>
                        </table>

						<?php
					} else {
						if ( $link_login ) {
							?>
                            <table class='wacv-table'>
                                <tr>
                                    <td class="col-1"></td>
                                    <td class="col-2">
                                        <a class="vi-ui primary button wacv-btn" href="<?php echo esc_url($link_login); ?>">
											<?php esc_html_e( 'Reconnect Facebook', 'woo-abandoned-cart-recovery' ) ?>
                                        </a>
                                        <span style="color: red"><?php esc_html_e( 'No page was selected when connect Facebook', 'woo-abandoned-cart-recovery' ) ?></span>
                                    </td>
                                    <td class="col-3"></td>
                                </tr>
                            </table>
							<?php
						}
					}
				}
			}
			?>

            <table class='wacv-table' style="<?php echo $show_field ? '' : 'display:none;' ?>">
				<?php
				$this->select_option( 'app_lang', Data::get_instance()->list_language(), __( 'Language', 'woo-abandoned-cart-recovery' ) );
				$this->select_option( 'app_skin', [
					'light' => __( 'Light', 'woo-abandoned-cart-recovery' ),
					'dark'  => __( 'Dark', 'woo-abandoned-cart-recovery' )
				], __( 'Checkbox skin', 'woo-abandoned-cart-recovery' ) );
				$this->checkbox_option( 'checkbox_require', __( "Send to messenger require", 'woo-abandoned-cart-recovery' ), __( '', 'woo-abandoned-cart-recovery' ) );
				$this->send_message_rules_settings( 'messenger_rules' );
				?>
            </table>

            <table class='wacv-table'>
				<?php
				$this->checkbox_option( 'fb_test_mode', __( "Test mode", 'woo-abandoned-cart-recovery' ), __( 'If enable, a sample message will send to customer immediately', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>

			<?php $this->connect_guide(); ?>

        </div>
		<?php
	}

	public function guide_row( $text, $image = '', $desc = '' ) {
		?>
        <div class="title">
            <i class="dropdown icon"></i>
            <div>
				<?php echo wp_kses_post( $text ) ?>
            </div>
        </div>
        <div class="content">
			<?php
			if ( $desc ) {
				echo wp_kses_post( $desc );
			}
			if ( $image ) {
				printf( '<div class="wacv-image-div"><div class="vi-ui segment"><img class="" src="%s"></div></div>', esc_attr( WACVP_IMAGES . $image ) );
			}
			?>
        </div>
		<?php
	}

	public function text_area_row( $text, $row = 2 ) {
		return sprintf(
			'<div class="wacv-description-text"><textarea rows="%d" readonly>%s</textarea><span class="wacv-textarea-copy vi-ui button tiny">%s</span></div>',
			esc_attr( $row ), esc_html( $text ), esc_html__( 'Copy', 'woo-abandoned-cart-recovery' )
		);
	}

	public function connect_guide() {
		?>
        <div class="wacv-guide-wrapper">
            <div class="vi-ui styled accordion">

                <div class="title">
                    <i class="dropdown icon"></i>
					<?php esc_html_e( 'Facebook API guide', 'woo-abandoned-cart-recovery' ); ?>
                </div>
                <div class="content">

                    <h4><?php esc_html_e( 'Connect', 'woo-abandoned-cart-recovery' ); ?></h4>
                    <div class="vi-ui accordion wacv-simple">
						<?php
						$text = sprintf( '%s <a href="%s" target="_blank">%s</a>. %s',
							esc_html__( '1 - Create your facebook app at', 'woo-abandoned-cart-recovery' ),
							esc_url( 'https://developers.facebook.com/' ),
							esc_html__( 'here', 'woo-abandoned-cart-recovery' ),
							esc_html__( "Select 'Manage Business Integrations' type.", 'woo-abandoned-cart-recovery' )
						);
						$this->guide_row( $text, 'create-app.jpg' );
						$this->guide_row( esc_html__( '2 - At FB settings page > Basic, copy App ID, App secret to your plugin settings page.', 'woo-abandoned-cart-recovery' ), 'copy-app-id.jpg' );
						$this->guide_row( esc_html__( '3 - Fill all your information.', 'woo-abandoned-cart-recovery' ), 'full-settings.jpg' );
						$this->guide_row( esc_html__( '4 - Add products: Facebook Login with Web platform, Webhooks and Messenger.', 'woo-abandoned-cart-recovery' ), 'add-products.jpg' );
						$this->guide_row( esc_html__( '5 - Facebook login > Settings: Copy the Valid OAuth Redirect URIs from the plugin settings and paste it to the settings of the Facebook login.', 'woo-abandoned-cart-recovery' ), 'oauth-redirect.jpg' );
						$this->guide_row( esc_html__( '6 - Webhooks: Copy the Callback URL and Verify Token from the plugin settings and paste it to  the settings of the Webhooks.', 'woo-abandoned-cart-recovery' ), 'add-webhook.jpg' );
						$this->guide_row( esc_html__( '7 - Save this setting page', 'woo-abandoned-cart-recovery' ) );
						$this->guide_row( esc_html__( '8 - Click \'Login to Facebook\' button > Select page you want to connect > Accept permissions > Done', 'woo-abandoned-cart-recovery' ) );
						$this->guide_row( esc_html__( '9 - After connect to Facebook, return your Facebook App > Messenger > Settings. Edit Page Subscriptions, add subscription fields:', 'woo-abandoned-cart-recovery' ) . '<br><b>messages, messaging_postbacks, messaging_optins</b>', 'subscribe-webhook.jpg' );
						$this->guide_row( esc_html__( '9 - To publish checkbox plugin on frontend, you have to submit for reviewing your Facebook App and get the approval from the Facebook Team. Please follow these below steps:', 'woo-abandoned-cart-recovery' ) );
						?>
                    </div>

                    <br>

                    <h4><?php esc_html_e( 'App Review', 'woo-abandoned-cart-recovery' ); ?></h4>
                    <div class="vi-ui accordion wacv-simple">
						<?php
						$desc = sprintf( '<a class="wacv-download-file" href="https://drive.google.com/file/d/1S8NDwG5aid7g9Tc278nCvSd7IS7vjDws/view?usp=sharing" download="download">%s</a>',
							esc_html__( 'Download', 'woo-abandoned-cart-recovery' ) );
						$this->guide_row( esc_html__( '1 - Download the attachment file', 'woo-abandoned-cart-recovery' ), '', $desc );

						$this->guide_row( esc_html__( '2 - Check again to ensured that all your necessary information is filled in the Facebook App Dashboard/Settings/Basic.', 'woo-abandoned-cart-recovery' ), 'full-settings.jpg' );
						$this->guide_row( esc_html__( '3 - Go to App Review > Permission and Feature. Add permission:', 'woo-abandoned-cart-recovery' )
						                  . ' <b> pages_show_list, pages_manage_metadata, pages_messaging, Business Asset User Profile Access</b>.', 'add-permissions.jpg' );
						$this->guide_row( esc_html__( '4 - Go to Request page > Edit request.', 'woo-abandoned-cart-recovery' ), 'edit-request.jpg' );

						$text = 'Login credentials:&#13;&#10;&#13;&#10;';
						$text .= 'Login URL: {your_login_url}&#13;&#10;';
						$text .= 'Username: {replace with your_username}&#13;&#10;';
						$text .= 'Password: {replace with your_password}&#13;&#10;&#13;&#10;';
						$text .= 'To see how permissions are used in my app:&#13;&#10;';
						$text .= '1. Navigate to {your_login_url}&#13;&#10;';
						$text .= '2. Login in using the credentials provided&#13;&#10;';
						$text .= "3. Once you’ve accessed the website, click the Abandoned Cart> Settings button in the left nav.&#13;&#10;";
						$text .= "4. Copy App ID, App secret from Fb app to my plugin. Click  'Save' button.&#13;&#10;";
						$text .= '5. Copy Valid OAuth redirected URls, Callback Webhooks URL, Verify Token from my plugin to Fb App.&#13;&#10;';
						$text .= "6. Click 'Login Facebook' button.";
						//						$text .= "7. Go to any products pages on shop, check on checkbox send to messenger box.";
						//						$text .= "8. Chat with the bot on messenger box by typing: hi, hello, information, info.";
						$text = $this->text_area_row( $text, 13 );
						$this->guide_row( esc_html__( '5 - Add App Verification Details', 'woo-abandoned-cart-recovery' ), 'app-verification.jpg', $text );

						$text = $this->text_area_row( 'The page_show_list is required for my plugin to get the page list when connecting to the Facebook App' );
						$this->guide_row( esc_html__( '6 -  Add page_show_list detail and upload the video which you have just downloaded in the step 1.', 'woo-abandoned-cart-recovery' ), 'pages-show-list.jpg', $text );

						$text = $this->text_area_row( 'pages_manage_metadata is required to my plugin subscribe pages to webhooks for messenger bot' );
						$this->guide_row( esc_html__( '7 - Add pages_manage_metadata detail & select uploaded video', 'woo-abandoned-cart-recovery' ), 'pages-manage-metadata.jpg', $text );

						$text1 = 'pages_messaging is required to make Page Messenger bot  where bot will answer to clients that who send message to page\'s inbox for client support or other significant FAQ data and so on.';
						$text2 = '1.  Navigate to {replace with your admin url} &#13;&#10;';
						$text2 .= '2.  Login in using the credentials provided:&#13;&#10;';
						$text2 .= 'Login URL: {your_login_url}&#13;&#10;Username: {replace with your_username}&#13;&#10;Password: {replace with your_password}&#13;&#10;';
						$text2 .= '3.  Once you’ve accessed the website, click the Abandoned Cart> Settings button in the left nav.&#13;&#10;';
						$text2 .= '4. Go to any products pages on shop, check on checkbox send to messenger box.&#13;&#10;';
						$text2 .= '5. Chat with the bot on messenger box by typing: hi, hello, information, info.';
						$text  = $this->text_area_row( $text1 ) . $this->text_area_row( $text2, 7 );
						$this->guide_row( esc_html__( '8 - Add pages_messaging detail & select uploaded video', 'woo-abandoned-cart-recovery' ), 'pages-messaging.jpg', $text );

						$text = $this->text_area_row( 'Requesting Business Asset User Profile Access helps the app to use the name of the customers when they chat with the Chatbot of my App' );
						$this->guide_row( esc_html__( '9 - Add Business Asset User Profile Access detail & select uploaded video', 'woo-abandoned-cart-recovery' ), 'user-profile-access.jpg', $text );

						$this->guide_row( esc_html__( "10 - Click 'Submit for Review' button.", 'woo-abandoned-cart-recovery' ) );
						?>
                    </div>

                    <br>
                    <h4><?php esc_html_e( 'Video guide', 'woo-abandoned-cart-recovery' ); ?></h4>
                    <br>
                    <div>
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/opWQa7tAsjg?feature=oembed" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>

                </div>
            </div>
        </div>
		<?php
	}

	//Messenger rule

	public function send_messenger_rules_settings() {
		$data = self::get_field( 'messenger_rules' );
//		check( Data::get_instance()->params );
		?>
        <tr class="vlt-row vlt-margin-top">

            <td class="vlt-third col-1">
                <label><?php esc_html_e( 'Send to messenger rules', 'woo-abandoned-cart-recovery' ) ?></label>
            </td>

            <td class="vlt-twothird col-2">
                <table class="wacv-messenger-rules-table vi-ui celled table">
                    <thead>
                    <tr>
                        <th class="cols-1"><?php esc_html_e( 'Send after', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th class="cols-2"><?php esc_html_e( 'Unit', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th class="cols-3"><?php esc_html_e( 'Message', 'woo-abandoned-cart-recovery' ); ?></th>
                        <th class="cols-4"><?php esc_html_e( 'Action', 'woo-abandoned-cart-recovery' ); ?></th>
                    </tr>
                    </thead>
                    <tbody class="wacv-table-row-target-mess">
					<?php
					if ( isset( $data['time_to_send'] ) ) {
						$loop = count( $data['time_to_send'] );

						for ( $i = 0; $i < $loop; $i ++ ) { ?>
                            <tr class="wacv-table-row-target-mess" data-index="<?php echo esc_attr($i) ?>">
                                <td class="vlt-padding-small wacv-messenger-time">
                                    <input type="number" name="wacv_params[messenger_rules][time_to_send][]"
                                           class="vlt-input vlt-border vlt-none-shadow vlt-round"
                                           value="<?php echo esc_attr($data['time_to_send'][ $i ]) ?>" min="1">
                                </td>
                                <td class="vlt-padding-small wacv-messenger-unit">
                                    <select name="wacv_params[messenger_rules][unit][]"
                                            class="vlt-input vlt-border vlt-none-shadow vlt-round">
                                        <option value="minutes" <?php echo $data['unit'][ $i ] == 'minutes' ? 'selected' : ''; ?>><?php esc_html_e( 'minutes', 'woo-abandoned-cart-recovery' ); ?></option>
                                        <option value="hours" <?php echo $data['unit'][ $i ] == 'hours' ? 'selected' : ''; ?>><?php esc_html_e( 'hours', 'woo-abandoned-cart-recovery' ); ?></option>
                                    </select>
                                </td>
                                <td class="vlt-padding-small">
                                    <input type="text" value="<?php echo esc_attr($data['message'][ $i ]) ?>"
                                           name="wacv_params[messenger_rules][message][]"
                                           class="vlt-input vlt-border vlt-none-shadow vlt-round">
                                </td>
                                <td align="center" class="vlt-padding-small">
                                    <button class="wacv-delete-messenger-rule vi-ui small icon red button"
                                            type="button">
                                        <i class="trash icon"> </i>
                                    </button>
                                </td>
                            </tr>
						<?php }
					} ?>
                    </tbody>
                </table>
                <button type="button" class="wacv-add-messenger-rule vi-ui small icon green button">
					<?php esc_html_e( 'Add rule', 'woo-abandoned-cart-recovery' ); ?>
                </button>

            </td>
            <td class="col-3"></td>
        </tr>

		<?php
	}
}
