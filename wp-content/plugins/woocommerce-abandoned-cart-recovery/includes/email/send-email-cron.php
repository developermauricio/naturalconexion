<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 25-03-19
 * Time: 5:00 PM
 */

namespace WACVP\Inc\Email;

use WACVP\Inc\Aes_Ctr;
use WACVP\Inc\Data;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Send_Email_Cron {

	protected static $instance = null;

	public $query;

	public $data;
	public $wc_email;

	public $new_email_settings;

	public $old_email_settings;
	public $last_time = false;
	public $render_data = [];
	public $email_type = 'abandoned_cart';

	protected $characters_array;
	protected $country;
	protected $record_data;
	protected $cart_items;

	private function __construct() {
		$this->query = Query_DB::get_instance();
		add_action( 'wacv_cron_send_email_abd_cart', array( $this, 'send_reminder_mail' ) );
		add_action( 'wacv_cron_send_email_abd_order', array( $this, 'send_reminder_order' ) );

		add_action( 'wp_ajax_wacv_send_abd_order', array( $this, 'wacv_send_abd_order' ) );
		add_action( 'wp_ajax_send_email_abd_manual', array( $this, 'send_email_abd_manual' ) );
		add_filter( 'woocommerce_email_styles', array( $this, 'custom_css' ) );

		add_filter( 'viwec_render_content', array( $this, 'render_element_abandoned_cart' ), 10, 3 );
		add_filter( 'viwec_render_content', array( $this, 'render_element_recover_button' ), 10, 3 );
		add_filter( 'viwec_ids_to_suggest_products', array( $this, 'ids_to_suggest_products' ) );

		add_filter( 'viwec_register_replace_shortcode', array( $this, 'register_replace_shortcode' ) );

		add_action( 'admin_init', [ $this, 'debug' ] );
	}

	public function debug() {
		if ( isset( $_GET['debug'] ) && $_GET['debug'] === 'abandoned_cart' ) {
			$this->send_reminder_mail();
		}
		if ( isset( $_GET['debug'] ) && $_GET['debug'] === 'abandoned_order' ) {
			$this->send_reminder_order();
		}
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function send_reminder_mail() {

		if ( ! class_exists( 'WC_Email' ) ) {
			include_once WC_ABSPATH . 'includes/emails/class-wc-email.php';
		}

		$this->wc_email = new \WC_Email();

		$this->data = Data::get_params();

		if ( ! empty( $this->data['email_rules'] ) ) {
			$email_rules = $this->data['email_rules'];
			$count       = count( $email_rules['send_time'] );
			for ( $i = 0; $i < $count; $i ++ ) {
				$this->last_time = ( $count - 1 ) == $i ? true : false;
				$time_to_send    = current_time( 'timestamp' ) - intval( $email_rules['time_to_send'][ $i ] ) * Data::get_instance()->case_unit( $email_rules['unit'][ $i ] );

				$lists = $this->query->get_list_email_to_send( $time_to_send, $email_rules['send_time'][ $i ] );

				if ( is_array( $lists ) && count( $lists ) > 0 ) {
					if ( isset( $email_rules['template'][ $i ] ) ) {
						foreach ( $lists as $id => $item ) {
							$this->email_content( $item, $email_rules['template'][ $i ] );
						}
					}
				}
			}
		}
	}

	public function email_content( $item, $temp_id ) {

		$result = '';
		if ( ! empty( $item->user_id ) ) {

			$email            = $item->user_email;
			$customer_name    = $item->user_login;
			$customer_surname = '';
			$country          = $item->user_id < WACVP_GUEST_ID_LIMIT ? get_user_meta( $item->user_id, 'billing_country', true ) : '';

			if ( $item->user_type == 'guest' ) {
				$results_guest = $this->query->get_guest_info( $item->user_id );
				if ( ! empty( $results_guest ) ) {
					$email         = $results_guest->billing_email;
					$customer_name = ! empty( trim( $results_guest->billing_first_name ) ) ? trim( $results_guest->billing_first_name ) : '';

					if ( ! $customer_name ) {
						$length        = strpos( $email, '@' );
						$customer_name = substr( $email, 0, $length );
					}

					$customer_surname = ! empty( trim( $results_guest->billing_last_name ) ) ? trim( $results_guest->billing_last_name ) : '';
					$country          = ! empty( $results_guest->billing_country ) ? $results_guest->billing_country : '';
				}
			}

			if ( is_email( $email ) ) {
				$cart        = json_decode( $item->abandoned_cart_info );
				$cart_detail = $pd_arr = array();
				$cart_total  = 0;

				if ( $country && $this->data['price_incl_tax'] ) {
					$this->country = $country;
					add_filter( 'woocommerce_matched_rates', array( $this, 'add_tax_rate' ) );
				}

				$image_size = $this->data['image_size'];
				foreach ( $cart->cart as $cart_item_key => $cart_item ) {
					$pid     = $cart_item->variation_id ? $cart_item->variation_id : $cart_item->product_id;
					$product = wc_get_product( $pid );
					if ( $product ) {
						$pd_name     = $product->get_name();
						$image       = wp_get_attachment_image_url( $product->get_image_id(), $image_size );
						$image_url   = $image ? $image : wc_placeholder_img_src( $image_size );
						$description = explode( '.', $product->get_short_description() );
						$description = isset( $description[0] ) ? $description[0] : '';
						$qty         = $cart_item->quantity;

						$price  = $product->get_price( 'edit' );
						$amount = wc_get_price_to_display( $product, [ 'price' => $price, 'qty' => $qty ] );

						$cart_total += $amount;
						$pd_arr[]   = $cart_item->product_id;
						$pd_url     = $product->get_permalink();

						$cart_detail[] = array(
							'image'        => $image_url,
							'name'         => $pd_name,
							'desc'         => $description,
							'price'        => wc_price( $amount, [ 'currency' => get_option( 'woocommerce_currency' ) ] ),
							'url'          => $pd_url,
							'quantity'     => $qty,
							'product_id'   => $cart_item->product_id,
							'variation_id' => $cart_item->variation_id ?? '',
						);
					}
				}

				if ( ! empty( $cart_detail ) ) {
					$this->cart_items = $cart_detail;

					$temp_obj = get_post( $temp_id );

					if ( ! $temp_obj ) {
						return false;
					}

					$acr_id        = $item->id;
					$sent_email_id = uniqid() . $acr_id;

					$this->record_data = array(
						'acr_id'           => $acr_id,
						'cart_total'       => $cart_total,
						'temp_id'          => $temp_id,
						'product_arr'      => $pd_arr,
						'mail_count'       => $item->number_of_mailing,
						'email'            => $email,
						'customer_name'    => $customer_name,
						'customer_surname' => $customer_surname,
						'sent_email_id'    => $sent_email_id,
					);

					$coupon_code              = $coupon_expire = '';
					$coupon_default_params    = Data::get_instance()->get_coupon_params();
					$this->new_email_settings = get_post_meta( $temp_id, 'wacv_email_settings_new', true );
					$this->old_email_settings = get_post_meta( $temp_id, 'wacv_email_settings', true );
					$email_settings           = $this->new_email_settings ? $this->new_email_settings : $this->old_email_settings;
					$email_settings           = wp_parse_args( $email_settings, $coupon_default_params );

					if ( $email_settings['use_coupon'] ) {
						$coupon_code = $this->get_coupon_to_send( $email_settings );
					}

					if ( $coupon_code ) {
						$coupon = new \WC_Coupon( $coupon_code );
						if ( $coupon ) {
							if ( $coupon->get_date_expires() ) {
								$coupon_expire = $coupon->get_date_expires()->date_i18n( wc_date_format() . ' ' . wc_time_format() );
							}
						}
					}

					$this->record_data['coupon_code']        = $coupon_code;
					$this->record_data['coupon_expire']      = $coupon_expire;
					$this->record_data['link']               = $this->create_link( $coupon_code, $acr_id, $sent_email_id, $temp_id );
					$this->record_data['tracking_open_link'] = $this->create_tracking_open_link( $acr_id, $sent_email_id );
					$this->record_data['unsubscribe_link']   = $this->create_unsubscribe_link( $acr_id );

					$message = '';
					if ( $temp_obj->post_type === 'wacv_email_template' ) {
						$email_subject                = isset( $email_settings['subject'] ) ? $email_settings['subject'] : $this->data['email_subject'];
						$this->record_data['subject'] = $this->replace_shortcodes( $email_subject );

						$message = $this->create_email_content( $cart_detail, $temp_id );
					}

					if ( $temp_obj->post_type === 'viwec_template' && class_exists( 'VIWEC_Render_Email_Template' ) ) {
						$args  = [ 'template_id' => $temp_id ];
						$email = new \VIWEC_Render_Email_Template( $args );

						ob_start();
						$email->get_content();
						$message = ob_get_clean();
						$message = $this->replace_shortcodes( $message );

						$this->record_data['subject'] = $this->replace_shortcodes( $email->get_subject() );
					}

					if ( $message ) {
						$result = $this->send_mail( $message );
					}
				}
			}
		}

		return $result;
	}

	public function replace_shortcodes( $message ) {
		$data = $this->record_data;
		$arr  = array(
			'{wacv_coupon}'           => $data['coupon_code'] ?? '',
			'{wacv_coupon_expire}'    => $data['coupon_expire'] ?? '',
			'{wacv_checkout_btn}'     => $data['link'],
			'{site_title}'            => get_bloginfo(),
			'{customer_name}'         => $data['customer_name'],
			'{wacv_customer_name}'    => $data['customer_name'],
			'{customer_surname}'      => $data['customer_surname'],
			'{wacv_customer_surname}' => $data['customer_surname'],
			'{site_address}'          => WC()->countries->get_base_address(),
			'{store_address}'         => WC()->countries->get_base_address(),
			'{admin_email}'           => get_bloginfo( 'admin_email' ),
			'{site_url}'              => site_url(),
			'{home_url}'              => home_url(),
			'{shop_url}'              => get_permalink( wc_get_page_id( 'shop' ) ),
			'{wacv_coupon_start}'     => '',
			'{wacv_coupon_end}'       => '',
			'{unsubscribe_link}'      => $data['unsubscribe_link'],
			'{coupon_code}'           => $data['coupon_code'] ?? '',
			'{coupon_expire}'         => $data['coupon_expire'] ?? '',
		);

		return str_replace( array_keys( $arr ), array_values( $arr ), $message );
	}

	public function create_email_content( $cart_detail, $temp_id ) {
		$message      = '';
		$template_obj = get_post( $temp_id );

		if ( $template_obj ) {
			$template = wp_specialchars_decode( get_post( $temp_id )->post_content );
		} else {
			$temp_id = '';
			ob_start();
			wc_get_template( 'email-default.php', array(), '', WACVP_TEMPLATES );
			$template                     = ob_get_clean();
			$this->record_data['subject'] = $this->data['email_subject'];
		}

		if ( $template ) {
			$out     = '';
			$pattern = '/{wacv_cart_detail_start}([\s\S]+){wacv_cart_detail_end}/';

			if ( preg_match( ( $pattern ), $template, $match ) ) {

				foreach ( $cart_detail as $item ) {
					$item['price']    = __( 'Price:', 'woo-abandoned-cart-recovery' ) . ' ' . $item['price'];
					$item['url']      = "<a href='{$item['url']}' style='font-weight: inherit; color:inherit;'>{$item['name']}</a>";
					$item['quantity'] = __( 'Quantity:', 'woo-abandoned-cart-recovery' ) . ' ' . $item['quantity'];

					$search = array(
						'{wacv_image_product}',
						'{wacv_name_&_qty_product}',
						'{wacv_short_description}',
						'{product_amount}',
						'{product_name}',
						'{product_quantity}'
					);

					$out .= str_replace( $search, $item, $match[1] );
				}

				$template = str_replace( $match[0], $out, $template );
			}

			$message = $this->replace_shortcodes( $template );
			$message = $this->complete_message( $message );
		}

		return $message;
	}

	public function get_coupon_to_send( $email_settings ) {
		$coupon_code = '';

		if ( ! empty( $email_settings['use_coupon_generate'] ) ) {
			$coupon_code = $this->generate_coupon( $email_settings );
		} elseif ( ! empty( $email_settings['wc_coupon'] ) ) {
			$coupon_code = wc_get_coupon_code_by_id( $email_settings['wc_coupon'] );
		}

		return $coupon_code;
	}

	public function generate_coupon( $option ) {
		return wc_get_coupon_code_by_id( $this->create_coupon( $option ) );
	}

	public function create_coupon( $option ) {
		$code   = $this->create_code( $option );
		$coupon = new \WC_Coupon( $code );
		$desc   = isset( $option['gnr_coupon_desc'] ) ? $option['gnr_coupon_desc'] : '';
		$coupon->set_description( $desc );
		$coupon->set_discount_type( $option['gnr_coupon_type'] );
		$coupon->set_amount( $option['gnr_coupon_amount'] );
		if ( $option['gnr_coupon_date_expiry'] ) {
			$coupon->set_date_expires( $option['gnr_coupon_date_expiry'] * 24 * 60 * 60 + current_time( 'timestamp' ) );
		}
		$coupon->set_minimum_amount( $option['gnr_coupon_min_spend'] );
		$coupon->set_maximum_amount( $option['gnr_coupon_max_spend'] );
		$coupon->set_individual_use( $option['gnr_coupon_individual'] );
		$coupon->set_free_shipping( $option['gnr_coupon_free_shipping'] );
		$coupon->set_exclude_sale_items( $option['gnr_coupon_exclude_sale_items'] );
		$coupon->set_product_ids( $option['gnr_coupon_products'] );
		$coupon->set_excluded_product_ids( $option['gnr_coupon_exclude_products'] );
		$coupon->set_product_categories( $option['gnr_coupon_categories'] );
		$coupon->set_excluded_product_categories( $option['gnr_coupon_exclude_categories'] );
		$coupon->set_usage_limit( $option['gnr_coupon_limit_per_cp'] );
		$coupon->set_limit_usage_to_x_items( $option['gnr_coupon_limit_x_item'] );
		$coupon->set_usage_limit_per_user( $option['gnr_coupon_limit_user'] );

		return $coupon->save();
	}

	public function create_code( $option ) {
		$code = $option['gnr_coupon_prefix'];

		for ( $i = 0; $i < 8; $i ++ ) {
			$code .= $this->rand();
		}

		$args      = array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'title'          => $code
		);
		$the_query = new \WP_Query( $args );
		if ( $the_query->have_posts() ) {
			$code = $this->create_code( $option );
		}
		wp_reset_postdata();

		return $code;
	}

	protected function rand() {
		if ( $this->characters_array === null ) {
			$this->characters_array = array_merge( range( 0, 9 ), range( 'a', 'z' ) );
		}
		$rand = rand( 0, count( $this->characters_array ) - 1 );

		return $this->characters_array[ $rand ];
	}

	public function create_link( $coupon_code, $acr_id, $sent_email_id, $temp_id ) {
		$coupon      = $coupon_code ? '&' . $coupon_code : '';
		$template_id = $temp_id ? '&' . $temp_id : '&0';
		$pass        = get_option( 'wacv_private_key' );
		$url_encode  = Aes_Ctr::encrypt( $acr_id . '&' . $sent_email_id . $template_id . $coupon, $pass, 256 );

		return site_url( '?wacv_recover=cart_link&valid=' ) . $url_encode;
	}

	public function create_tracking_open_link( $acr_id, $sent_email_id ) {
		$pass       = get_option( 'wacv_private_key' );
		$url_encode = Aes_Ctr::encrypt( $acr_id . '&' . $sent_email_id, $pass, 256 );

		return "<img width='0' height='0' style='width:0; height:0;' src='" . site_url( '?wacv_open_email=' ) . $url_encode . "' >";
	}

	public function create_unsubscribe_link( $acr_id ) {
		$pass       = get_option( 'wacv_private_key' );
		$url_encode = Aes_Ctr::encrypt( $acr_id, $pass, 256 );

		return site_url( '?wacv_unsubscribe=' ) . $url_encode;
	}

	public function complete_message( $template ) {
		$mailer = WC()->mailer();

		if ( $this->new_email_settings && ! isset( $this->new_email_settings['woo_header'] ) ) {
			$message = $this->wc_email->style_inline( $this->wrap_message( $template ) );
		} elseif ( $this->new_email_settings && isset( $this->new_email_settings['woo_header'] ) ) {
			$heading     = ! empty( $this->new_email_settings['heading'] ) ? $this->new_email_settings['heading'] : '';
			$message     = $this->wc_email->style_inline( $mailer->wrap_message( $heading, $template ) );
			$padding     = array( 'padding: 12px;', 'padding: 48px 48px 32px' );
			$new_padding = array( 'padding:0;', 'padding:0' );
			$message     = str_replace( $padding, $new_padding, $message );
		} else {
			$heading = ! empty( $this->old_email_settings['heading'] ) ? $this->old_email_settings['heading'] : '';
			$message = $this->wc_email->style_inline( $mailer->wrap_message( $heading, $template ) );
		}

		return $message;
	}

	public function wrap_message( $message ) {
		ob_start();

		wc_get_template( 'email-header.php', '', '', WACVP_TEMPLATES );

		echo wptexturize( $message );

		wc_get_template( 'email-footer.php', '', '', WACVP_TEMPLATES );

		$message = ob_get_clean();

		return $message;
	}

	public function send_mail( $message ) {
		if ( ! $message ) {
			return false;
		}

		$acr_id = $this->record_data['acr_id'];
		$email  = $this->record_data['email'];

		$param = Data::get_params();

		$headers [] = "Content-Type: text/html";

		if ( $param['email_reply_address'] ) {
			$headers [] = "Reply-To: " . $param['email_reply_address'];
		}

		$message          .= $this->record_data['tracking_open_link'];
		$result_sent_mail = $this->wc_email->send( $email, $this->record_data['subject'], $message, $headers, '' );
		$complete         = $this->last_time ? '1' : null;

		$this->query->update_abd_cart_record(
			array(
				'send_mail_time'    => current_time( 'timestamp' ),
				'number_of_mailing' => $this->record_data['mail_count'] + 1,
				'email_complete'    => $complete
			),
			array( 'id' => $acr_id ) );

		$this->query->insert_email_history( 'email', $acr_id, $this->record_data['sent_email_id'], $this->record_data['temp_id'], $email, $this->record_data['coupon_code'] );

		return $result_sent_mail;
	}

	public function add_tax_rate( $rate ) {
		if ( ! wc_prices_include_tax() && $this->country ) {
			$rate = \WC_Tax::find_rates(
				array(
					'country'   => $this->country,
					'state'     => '',
					'postcode'  => '',
					'city'      => '',
					'tax_class' => '',
				)
			);
		}

		return $rate;
	}

	/******** Reminder for order ********/
	public function send_reminder_order() {
		$this->data = Data::get_params();

		if ( ! $this->data['enable_reminder_order'] ) {
			return;
		}

		if ( ! empty( $this->data['abd_orders'] ) ) {
			$email_rules = $this->data['abd_orders'];

			for ( $i = 0; $i < count( $email_rules['send_time'] ); $i ++ ) {

				$time_to_send = current_time( 'timestamp' ) - intval( $email_rules['time_to_send'][ $i ] ) * Data::get_instance()->case_unit( $email_rules['unit'][ $i ] );

				$args = array(
					'post_type'   => 'shop_order',
					'post_status' => $this->data['order_stt'],
					'date_query'  => array(
						array(
							'before' => array(
								'year'   => date_i18n( 'Y', $time_to_send ),
								'month'  => date_i18n( 'm', $time_to_send ),
								'day'    => date_i18n( 'd', $time_to_send ),
								'hour'   => date_i18n( 'H', $time_to_send ),
								'minute' => date_i18n( 'i', $time_to_send ),
								'second' => date_i18n( 's', $time_to_send ),
							),
							'column' => 'post_modified',
						),
					),
					'meta_query'  => array(
						array(
							'key'   => '_wacv_send_reminder_email',
							'value' => $email_rules['send_time'] [ $i ] - 1,
						),
						array(
							'key'   => '_wacv_reminder_unsubscribe',
							'value' => '',
						),
					)
				);

				$the_query = new \WP_Query( $args );

				if ( $the_query->have_posts() ) :
					foreach ( $the_query->get_posts() as $order ) {
						$this->create_email_reminder_order( $order->ID, $email_rules['template'] [ $i ], $email_rules['send_time'] [ $i ] );
					}
				endif;
			}
		}
	}

	public function create_email_reminder_order( $order_id, $template_id, $time ) {
		if ( ! class_exists( 'WC_Email' ) ) {
			include_once dirname( WC_PLUGIN_FILE ) . '/includes/emails/class-wc-email.php';
		}
		$this->wc_email = new \WC_Email();

		$this->email_type = 'abandoned_order';
		$send_mail        = $subject = '';
		$temp_obj         = get_post( $template_id );
		$order            = wc_get_order( $order_id );
		$email            = $order->get_billing_email();
		$currency         = $order->get_currency();

		if ( $temp_obj && is_email( $email ) ) {
			$this->new_email_settings = get_post_meta( $template_id, 'wacv_email_settings_new', true );
			$this->old_email_settings = get_post_meta( $template_id, 'wacv_email_settings', true );

			$sent_email_id      = '&' . uniqid() . $order_id;
			$pass               = get_option( 'wacv_private_key' );
			$recover_url_encode = Aes_Ctr::encrypt( $order_id . $sent_email_id, $pass, 256 );
			$unsub_url_encode   = Aes_Ctr::encrypt( $order_id, $pass, 256 );

			$this->record_data = array(
				'link'             => site_url( '?wacv_recover=order_link&valid=' ) . $recover_url_encode,
				'unsubscribe_link' => site_url( '?unsubscribe=' ) . $unsub_url_encode,
				'customer_name'    => $order->get_billing_first_name(),
				'customer_surname' => $order->get_billing_last_name(),
			);

			$order_detail = $order->get_items();
			if ( ! empty( $order_detail ) ) {
				$image_size = $this->data['image_size'];

				$cart_detail = [];
				foreach ( $order_detail as $item ) {
					$item    = $item->get_data();
					$pid     = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
					$product = wc_get_product( $pid );

					if ( ! $product ) {
						continue;
					}

					$image     = wp_get_attachment_image_url( $product->get_image_id(), $image_size );
					$image_url = $image ? $image : wc_placeholder_img_src( $image_size );


					$desc = explode( '.', $product->get_short_description() );
					$desc = isset( $desc[0] ) ? $desc[0] : '';

					$cart_detail[] = array(
						'image'        => $image_url,
						'name'         => $item['name'],
						'desc'         => $desc,
						'price'        => wc_price( $item['subtotal'], [ 'currency' => $currency ] ),
						'url'          => get_permalink( $pid ),
						'quantity'     => $item['quantity'],
						'product_id'   => $item['product_id'],
						'variation_id' => $item['variation_id'],
					);
				}
				$this->cart_items = $cart_detail;
			}

			$message = '';

			if ( $temp_obj->post_type === 'wacv_email_template' ) {
				$email_settings = $this->new_email_settings ? $this->new_email_settings : $this->old_email_settings;
				$subject        = isset( $email_settings['subject'] ) ? $email_settings['subject'] : '';
				$message        = $this->render_email_content_for_order_reminder( $temp_obj );
				$message        = $this->complete_message( $message );
			}

			if ( $temp_obj->post_type === 'viwec_template' ) {
				if ( class_exists( 'VIWEC_Render_Email_Template' ) ) {
					$args             = [ 'template_id' => $template_id ];
					$email_customizer = new \VIWEC_Render_Email_Template( $args );

					ob_start();
					$email_customizer->get_content();
					$message = ob_get_clean();

					$subject = $email_customizer->get_subject();
				}
			}

			if ( $message ) {
				$subject            = $this->replace_shortcodes( $subject );
				$tracking_open_link = $this->create_tracking_open_link( $order_id, $sent_email_id );
				$message            .= $tracking_open_link;
				$message            = $this->replace_shortcodes( $message );
				$send_mail          = $this->send_email_reminder_order( $email, $subject, $message );
				if ( $send_mail ) {
					update_post_meta( $order_id, '_wacv_send_reminder_email', $time );
					$this->query->insert_email_history( 'order', $order_id, $sent_email_id, $template_id );
				}
			}
		}

		return $send_mail;
	}

	public function render_email_content_for_order_reminder( $temp_obj ) {
		$template = wp_specialchars_decode( $temp_obj->post_content );

		$out = '';

		$pattern = '/{wacv_cart_detail_start}([\s\S]+){wacv_cart_detail_end}/';        //replace order items with shortcode

		if ( preg_match( $pattern, $template, $match ) ) {
			if ( ! empty( $this->cart_items ) ) {

				foreach ( $this->cart_items as $item ) {

					$item['name']     = $item['name'] . ' x ' . $item['quantity'];
					$item['url']      = "<a href='{$item['url']}' style='font-weight: inherit'>${item['name']}</a>";
					$item['price']    = __( 'Price:', 'woo-abandoned-cart-recovery' ) . $item['price'];
					$item['quantity'] = __( 'Quantity:', 'woo-abandoned-cart-recovery' ) . $item['quantity'];

					$search = array(
						'{wacv_image_product}',
						'{wacv_name_&_qty_product}',
						'{wacv_short_description}',
						'{product_amount}',
						'{product_name}',
						'{product_quantity}'
					);
					$out    .= str_replace( $search, $item, $match[1] );
				}
				$template = str_replace( $match[0], $out, $template );

			}
		}

		return $template;
	}

	public function send_email_reminder_order( $email, $subject, $message ) {

		$param = Data::get_params();

		$headers [] = "Content-Type: text/html";

		if ( $param['email_reply_address'] ) {
			$headers [] = "Reply-To: " . $param['email_reply_address'];
		}

		$mailer = new \WC_Email();
		$result = $mailer->send( $email, $subject, $message, $headers, '' );

		return $result;
	}

	public function wacv_send_abd_order() {
		if ( isset( $_POST['id'], $_POST['temp'] ) ) {
			$result    = true;
			$settings  = Data::get_params();
			$order_id  = sanitize_text_field( $_POST['id'] );
			$order     = wc_get_order( $order_id );
			$order_stt = $order->get_status();
			if ( in_array( 'wc-' . $order_stt, $settings['order_stt'] ) ) {
				$template = sanitize_text_field( $_POST['temp'] );
				$time     = get_post_meta( $order_id, '_wacv_send_reminder_email', true ) + 1;
				$result   = $this->create_email_reminder_order( $order_id, $template, $time );
			}
			wp_send_json( $result );
		}
		wp_die();
	}

	public function send_email_abd_manual() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wacv_ajax_nonce' ) ) {
			return;
		}

		$result = '';
		if ( isset( $_POST['id'], $_POST['temp'], $_POST['time'] ) ) {

			if ( ! class_exists( 'WC_Email' ) ) {
				include_once dirname( WC_PLUGIN_FILE ) . '/includes/emails/class-wc-email.php';
			}

			$this->data     = Data::get_params();
			$this->wc_email = new \WC_Email();
			$id             = sanitize_text_field( $_POST['id'] );
			$temp           = sanitize_text_field( $_POST['temp'] );
			$item           = $this->query->get_abd_cart_by_id( $id );
			$result         = $this->email_content( $item, $temp );
		}
		wp_send_json( $result );
		wp_die();
	}

	public function custom_css( $css ) {
		$custom_css = '';
		$custom_css .= 'p{margin:0 !important;line-height:1.8;}';
		$custom_css .= 'a{text-decoration: none; color: inherit !important; font-weight:inherit;} a:hover{color:#007CFF}';
		$custom_css .= ' .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td {line-height: 100%;} .ExternalClass {width: 100%;}';
		$css        = $css . $custom_css;

		return $css;
	}

	public function html_format_item( $props ) {
		ob_start();
		$row_style     = ! empty( $props['childStyle']['.viwec-item-row'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-item-row'] ) : '';
		$img_style     = ! empty( $props['childStyle']['.viwec-product-img'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-img'] ) : '';
		$pdetail_style = ! empty( $props['childStyle']['.viwec-product-detail'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-detail'] ) : '';
		$pname_style   = ! empty( $props['childStyle']['.viwec-product-name'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-name'] ) : '';
		$pqty_style    = ! empty( $props['childStyle']['.viwec-product-quantity'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-quantity'] ) : '';
		$pprice_style  = ! empty( $props['childStyle']['.viwec-product-price'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-price'] ) : '';
		$qty_text      = ! empty( $props['content']['quantity'] ) ? $props['content']['quantity'] : '';
		$price_text    = ! empty( $props['content']['price'] ) ? $props['content']['price'] : '';
		?>
        <table width='100%%' border='0' cellpadding='0' cellspacing='0' align='center'
               style=' border-collapse:separate;font-size: 0;'>
            <tr>
                <td valign='middle' style='<?php echo esc_attr( $row_style ) ?>'>
                    <!--[if mso | IE]>
                    <table width="100%%" role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style='<?php echo esc_attr($img_style) ?>'>
                    <![endif]-->
                    <div class='viwec-responsive' style='vertical-align:middle;display:inline-block;<?php echo esc_attr( $img_style ) ?>'>
                        <table align="left" width="100%%" border='0' cellpadding='0' cellspacing='0'>
                            <tr>
                                <td>
                                    <img width='100%%' src='%s' style='vertical-align: middle'>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!--[if mso | IE]></td>
                    <td style="vertical-align:top;">
                    <![endif]-->
                    <div class='viwec-responsive' style='vertical-align:middle;display:inline-block;line-height: 150%%;<?php echo esc_attr( $pdetail_style ) ?>'>
                        <table align="left" width="100%%" border='0' cellpadding='0' cellspacing='0'>
                            <tr>
                                <td class="viwec-mobile-hidden" style="padding: 0;width: 10px;"></td>
                                <td style="padding: 5px 0;" class="viwec-responsive-center">
                                    <p style="<?php echo esc_attr( $pname_style ) ?>">%s</p>
                                    <p style="<?php echo esc_attr( $pqty_style ) ?>"><span><?php echo esc_html( $qty_text ); ?></span>%s</p>
                                    <p style="<?php echo esc_attr( $pprice_style ) ?>"><span><?php echo esc_html( $price_text ); ?></span>%s</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!--[if mso | IE]></td></tr></table><![endif]-->
                </td>
            </tr>
        </table>
		<?php
		return ob_get_clean();
	}

	public function render_element_abandoned_cart( $type, $props, $render ) {
		if ( $type == 'html_abandoned_cart' ) {
			$item_distance = ! empty( $props['childStyle']['.viwec-product-distance'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-distance'] ) : '';
			$tmpl          = $this->html_format_item( $props );
			if ( $render->preview ) {
				echo sprintf( $tmpl, esc_url( WACVP_IMAGES . 'product.png' ), esc_html( 'Product name' ), esc_html( '2' ), wc_price( 20 ) );
				echo sprintf( "<div style='%s'></div>", esc_attr( $item_distance ) );
				echo sprintf( $tmpl, esc_url( WACVP_IMAGES . 'product.png' ), esc_html( 'Product name' ), esc_html( '2' ), wc_price( 20 ) );
			} else {
				if ( ! empty( $this->cart_items ) && is_array( $this->cart_items ) ) {
					$ids   = [];
					$count = count( $this->cart_items ) - 1;
					foreach ( $this->cart_items as $i => $item ) {
						$ids[] = $item['product_id'];
						echo sprintf( $tmpl, esc_url( $item['image'] ), esc_html( $item['name'] ), esc_html( $item['quantity'] ), $item['price'] );
						if ( $i != $count ) {
							echo sprintf( "<table width='100%%' border='0' cellpadding='0' cellspacing='0'><tr><td style='%s'></td></tr></table>", esc_attr( $item_distance ) );
						}
					}

					$this->record_data['ids_to_suggest_products'] = $ids;
				}
			}
		}
	}

	public function ids_to_suggest_products( $ids ) {
		return $this->record_data['ids_to_suggest_products'] ?? $ids;
	}

	public function render_element_recover_button( $type, $props, $render ) {

		if ( $type == 'html_recover_button' ) {

			$url = '';

			if ( ! $render->preview ) {
				$coupon_code = $render->render_data['coupon'] ?? '';

				if ( $this->email_type == 'abandoned_cart' && $coupon_code ) {
					$url = $this->create_link( $coupon_code, $this->record_data['acr_id'], $this->record_data['sent_email_id'], $this->record_data['temp_id'] );
				} else {
					$url = $this->record_data['link'];
				}
			}

			$text        = str_replace( [ '<p>', '</p>' ], [ '', '' ], $props['content']['text'] ?? '' );
			$align       = $props['style']['text-align'] ?? 'left';
			$style       = isset( $props['childStyle']['a'] ) ? viwec_parse_styles( $props['childStyle']['a'] ) : '';
			$text_color  = $props['style']['color'] ?? 'inherit';
			$font_weight = $props['style']['font-weight'] ?? 'normal';
			$width       = $props['childStyle']['a']['width'] ?? '';

			?>
            <table align='<?php echo esc_attr( $align ) ?>' width='<?php echo esc_attr( $width ) ?>' class='viwec-responsive' border='0' cellpadding='0' cellspacing='0'
                   role='presentation'
                   style='border-collapse:separate;width:<?php echo esc_attr( $width ) ?>;'>
                <tr>
                    <td class='viwec-mobile-button-padding' align='center' valign='middle' role='presentation' style='<?php echo esc_attr( $style ) ?>'>
                        <a href='<?php echo esc_url( $url ) ?>' target='_blank'
                           style='color:<?php echo esc_attr( $text_color ) ?> !important;font-weight: <?php echo esc_attr( $font_weight ) ?>;display:inline-block;
                                   text-decoration:none;text-transform:none;margin:0;text-align: center;max-width: 100%;'>
                            <span style='color:<?php echo esc_attr( $text_color ) ?>'><?php echo wp_kses_post( $text ) ?></span>
                        </a>
                    </td>
                </tr>
            </table>
			<?php
		}

	}

	public function register_replace_shortcode( $sc ) {
		$sc['abandonedd_cart'] = [
			'{wacv_unsubscribe_link}' => $this->record_data['unsubscribe_link'] ?? '',
			'{wacv_customer_name}'    => $this->record_data['customer_name'] ?? '',
			'{wacv_customer_surname}' => $this->record_data['customer_surname'] ?? '',
		];

		return $sc;
	}
}