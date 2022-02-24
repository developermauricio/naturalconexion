<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 11-07-19
 * Time: 4:12 PM
 */

namespace WACVP\Inc\Execute;


use WACVP\Inc\Data;
use WACVP\Inc\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Abandoned_Order_Reminder {

	protected static $instance = null;


	private function __construct() {
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_columns' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'column_callback_order' ), 10, 2 );
		add_action( 'admin_head', array( $this, 'enqueue_scripts' ) );
		add_action( 'manage_posts_extra_tablenav', array( $this, 'send_abandoned_order_manual' ) );
		add_action( 'add_meta_boxes_shop_order', array( $this, 'add_meta_boxes' ) );
//		add_action( 'woocommerce_new_order', array( $this, 'add_meta_data' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'add_meta_data' ), 10, 2 );
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function add_columns( $columns ) {
		$columns['email_sent'] = __( 'Abandoned reminder sent', 'woo-abandoned-cart-recovery' );

		return $columns;
	}

	public function enqueue_scripts() {
		if ( get_current_screen()->id == 'edit-shop_order' || 'shop_order' ) {
			?>
            <style>
                /* Safari */
                @-webkit-keyframes spin {
                    0% {
                        -webkit-transform: rotate(0deg);
                    }
                    100% {
                        -webkit-transform: rotate(360deg);
                    }
                }

                @keyframes spin {
                    0% {
                        transform: rotate(0deg);
                    }
                    100% {
                        transform: rotate(360deg);
                    }
                }

                .loader {
                    border: 3px solid #cfcfcf;
                    border-radius: 50%;
                    border-top: 3px solid #1679ef;
                    width: 16px;
                    height: 16px;
                    -webkit-animation: spin 1s linear infinite; /* Safari */
                    animation: spin 1s linear infinite;
                }

                .wacv-send-mail-progress progress {
                    width: 100%;
                    height: 5px;
                    display: none;
                }

                .wacv-loader {
                    margin-left: 5px;
                }

                .wacv-loader p {
                    margin: 0;
                    line-height: 2;
                }

                .wacv-loader p.wacv-success {
                    color: green;
                }

                .wacv-loader p.wacv-fail {
                    color: red;
                }
            </style>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {

                    $('.wp-list-table').before("<div class='wacv-send-mail-progress'><progress value='0' max='100'></progress></div>");

                    $('.wacv-send-abandoned-order-manual').on('click', function () {
                        let checkboxes = $('.wp-list-table input[type="checkbox"]:checked');
                        let idArr = checkboxes.map(filterID);
                        sendAbdOrderEmail(0, idArr);
                    });

                    function filterID() {
                        let id = parseInt($(this).val());
                        if (!isNaN(id)) {
                            return id;
                        }
                    }

                    function sendAbdOrderEmail(index, idArr) {
                        let temp = $('.wacv-email-template').val();
                        let progressBar = $('.wacv-send-mail-progress progress');
                        $.ajax({
                            url: '<?php echo admin_url( 'admin-ajax.php' )?>',
                            type: 'post',
                            data: {action: 'wacv_send_abd_order', id: idArr[index], temp: temp},
                            beforeSend: function () {
                                progressBar.show(200);
                            },
                            success: function (res) {
                                // console.log(res);
                                if (res && idArr[index + 1]) {
                                    sendAbdOrderEmail(index + 1, idArr);
                                }
                                progressBar.val((index + 1 / idArr.length) * 100);
                                if (index + 1 === idArr.length) {
                                    setTimeout(function () {
                                        progressBar.hide(500);
                                    }, 2000);
                                }
                            },
                            error: function (res) {
                                console.log(res);
                            },
                            complete: function () {
                            }
                        });
                    }


                    $('.wacv-send-single-abd-order-email-manual').on('click', function () {
                        let id = $(this).attr('data-id'), temp = $('.wacv-email-template').val();
                        $.ajax({
                            url: '<?php echo admin_url( 'admin-ajax.php' )?>',
                            type: 'post',
                            data: {action: 'wacv_send_abd_order', id: id, temp: temp},
                            beforeSend: function () {
                                $('.wacv-loader').addClass('loader');
                            },
                            success: function (res) {
                                if (res) {
                                    $('.wacv-loader').append('<p class="wacv-success">Successful.</p>');
                                } else {
                                    $('.wacv-loader').append('<p class="wacv-fail">Unsuccessful.</p>');
                                }

                                setTimeout(function () {
                                    $('.wacv-loader').text('');
                                }, 2000);
                            },
                            error: function (res) {
                                console.log(res);
                            },
                            complete: function () {
                                $('.wacv-loader').removeClass('loader');
                            }
                        });
                    });
                });

            </script>
			<?php
		}
	}

	public function column_callback_order( $col_id, $order_id ) {
		if ( $col_id == 'email_sent' ) {
			$order      = wc_get_order( $order_id );
			$order_stt  = $order->get_status();
			$accept_stt = Data::get_param( 'order_stt' );
			if ( is_array( $accept_stt ) && in_array( 'wc-' . $order_stt, (array) $accept_stt ) ) {
				$email      = $order->get_billing_email();
				$email_sent = get_post_meta( $order_id, '_wacv_send_reminder_email', true );
				if ( $email_sent != '' && $email ) {
					echo "<span class='wacv-email-sent-{$order_id}' data-time='{$email_sent}'>" . esc_html( $email_sent ) . "<i class='dashicons dashicons-email-alt' style='margin-top: 3px'></i></span>";
				}
				$sms_sent = get_post_meta( $order_id, '_wacv_send_reminder_sms', true );
				$phone    = $order->get_billing_phone();
				if ( $sms_sent != '' && $phone ) {
					echo "<span class='wacv-sms-sent-{$order_id}' data-time='{$sms_sent}' style='margin-left: 10px'>" . esc_html( $sms_sent ) . "<i class='dashicons dashicons-testimonial' style='margin-top: 3px'></i></span>";
				}
			}
		}
	}

	public function send_abandoned_order_manual( $which ) {
		if ( get_current_screen()->post_type != 'shop_order' ) {
			return;
		}
		if ( $which == 'top' ) {
			$templates = Functions::get_email_template();
			?>
            <div style="float: left">
                <select class='wacv-email-template'>
					<?php
					if ( ! empty( $templates ) ) {
						foreach ( $templates as $template ) {
							printf( "<option value='%s'>%s</option>", esc_attr( $template['id'] ), esc_html( $template['value'] ) );
						}
					}
					?>
                </select>
                <input type="button" class="button wacv-send-abandoned-order-manual" name="send_abandoned_order"
                       value="<?php esc_html_e( 'Send abandoned order', 'woo-abandoned-cart-recovery' ) ?>">
				<?php ?>
            </div>
			<?php
		}
	}

	public function add_meta_boxes( $post ) {
		if ( in_array( $post->post_status, (array) Data::get_params()['order_stt'] ) ) {
			add_meta_box(
				'wacv-send-abd-order-mail',
				__( 'Send abandoned order email', 'woo-abandoned-cart-recovery' ),
				array( $this, 'send_abd_order_mail_meta_box' ),
				'shop_order',
				'side',
				'high'
			);
		}
	}

	public function send_abd_order_mail_meta_box( $post ) {
		$templates = Functions::get_email_template();
		?>
        <div style="display: flex">
            <select class='wacv-email-template' style="width: 90%;">
				<?php
				if ( ! empty( $templates ) ) {
					foreach ( $templates as $template ) {
						printf( "<option value='%s'>%s</option>", esc_attr( $template['id'] ), esc_html( $template['value'] ) );
					}
				}
				?>
            </select>

            <button type="button" class="button wacv-send-single-abd-order-email-manual"
                    data-id="<?php esc_attr_e( $post->ID ) ?>">
				<?php esc_html_e( 'Send', 'woo-abandoned-cart-recovery' ) ?>
            </button>
            <div class="wacv-loader"></div>
        </div>
		<?php
	}

	public function add_meta_data( $order_id, $data ) {
//	    die('die');
		update_post_meta( $order_id, '_wacv_send_reminder_email', 0 );
		update_post_meta( $order_id, '_wacv_send_reminder_sms', 0 );
		update_post_meta( $order_id, '_wacv_check_phone_number', '' );
		update_post_meta( $order_id, '_wacv_reminder_unsubscribe', '' );
	}
}
