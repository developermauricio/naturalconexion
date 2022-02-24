<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 06-04-19
 * Time: 9:22 AM
 */

namespace WACVP\Inc\Reports;

use WACVP\Inc\Data;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Reports {

	protected static $instance = null;

	public $query;

	public $plugin_slug;
	public $start;
	public $end;
	protected $filename;

	private function __construct() {

		$this->query = Query_DB::get_instance();

		add_action( 'admin_menu', array( $this, 'admin_menu_page' ), 10 );
		add_action( 'wp_ajax_get_reports', array( $this, 'get_reports' ) );
		add_filter( 'set-screen-option', array( $this, 'abandoned_table_screen_options' ), 10, 3 );
		add_filter( 'woocommerce_get_geolocation', array( $this, 'clear_country_code_from_header' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'dashboard' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'localize_script' ) );
		add_action( 'wp_ajax_wacv_export_csv', [ $this, 'wacv_export_csv' ] );
		add_action( 'admin_init', array( $this, 'download_export_file' ) );

	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	public function abandoned_table_screen_options( $status, $option, $value ) {
		return $value;
	}

	public function admin_menu_page() {
		$abd_page = add_menu_page(
			esc_html__( 'Abandoned Cart', 'woo-abandoned-cart-recovery' ),
			esc_html__( 'Abandoned Cart', 'woo-abandoned-cart-recovery' ),
			apply_filters( 'wacv_change_role', 'manage_woocommerce' ),
			'wacv_sections',
			array( $this, 'abandoned_cart_table' ),
			'dashicons-cart',
			2
		);
		add_action( "load-$abd_page", array( $this, 'abd_screen_options' ) );

		add_submenu_page(
			'wacv_sections',
			__( 'Abandoned Carts', 'woo-abandoned-cart-recovery' ),
			__( 'Abandoned Carts', 'woo-abandoned-cart-recovery' ),
			apply_filters( 'wacv_change_role', 'manage_woocommerce' ),
			'wacv_sections',
			array( $this, 'abandoned_cart_table' )
		);

		$report_page = add_submenu_page(
			'wacv_sections',
			__( 'Reports', 'woo-abandoned-cart-recovery' ),
			__( 'Reports', 'woo-abandoned-cart-recovery' ),
			apply_filters( 'wacv_change_role', 'manage_woocommerce' ),
			'wacv_reports',
			array( $this, 'display_reports' )
		);
		add_action( "load-$report_page", array( $this, 'report_screen_options' ) );

		$customer_emails_page = add_submenu_page(
			'wacv_sections',
			__( "Emails", 'woo-abandoned-cart-recovery' ),
			__( "Emails", 'woo-abandoned-cart-recovery' ),
			apply_filters( 'wacv_change_role', 'manage_woocommerce' ),
			'wacv_customer_emails',
			array( $this, 'customer_emails_page' )
		);
		add_action( "load-$customer_emails_page", array( $this, 'customer_emails_screen_options' ) );


	}


	public function abandoned_cart_table() {
		Abandoned_Report_Table::get_instance()->abandoned_table();
	}

	public function abd_screen_options() {
		Abandoned_Report_Table::get_instance();
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Display', 'woo-abandoned-cart-recovery' ),
			'default' => 30,
			'option'  => 'wacv_acr_per_page'
		);
		add_screen_option( $option, $args );
	}

	public function display_reports() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wacv_reports' ) {
			if ( isset( $_GET['tab'] ) ) {
				$tab = sanitize_text_field( $_GET['tab'] );
				 $this->report_header( $tab );
				echo "<div class='wacv-abandoned-reports vlt-container'>";
				switch ( $tab ) {
					case 'cart_logs':
						$section = isset( $_GET['section'] ) && $_GET['section'] == 'product' ? true : false;
						?>
                        <div>
                            <ul class="subsubsub">
                                <li class="<?php echo ! $section ? 'wacv-active' : '' ?>">
                                    <a href="<?php echo admin_url( 'admin.php?page=wacv_reports&tab=cart_logs' ) ?>">
										<?php esc_html_e( 'Action logs', 'woo-abandoned-cart-recovery' ) ?>
                                    </a>
                                </li>
                                <li>|</li>
                                <li class="<?php echo $section ? 'wacv-active' : '' ?>">
                                    <a href="<?php echo admin_url( 'admin.php?page=wacv_reports&tab=cart_logs&section=product' ) ?>">
										<?php esc_html_e( 'Product', 'woo-abandoned-cart-recovery' ) ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
						<?php

						if ( $section ) {
							Cart_Logs_Report_Product::get_instance()->report_cart_logs();
						} else {
							Cart_Logs_Report_Table::get_instance()->report_cart_logs();
						}
						break;
					case 'product':
						Product_Report_Table::get_instance()->report_product();
						break;
					case 'coupon':
						Coupon_Used_Report_Table::get_instance()->report_coupon();
						break;
				}
				echo "</div>";
			} else {
				 $this->report_header( 'general' );
				$this->report_general();
			}
		}
	}

	public function report_header( $active ) {
		?>
        <h3><?php esc_html_e( 'Reports', 'woo-abandoned-cart-recovery' ) ?></h3>
        <div class="wacv-reports-control-bar vlt-tab-group">
            <a class="vlt-tab-item <?php echo $active == 'general' ? 'vlt-active' : '' ?>"
               href="<?php echo admin_url( 'admin.php?page=wacv_reports' ) ?>"><?php esc_html_e( 'General', 'woo-abandoned-cart-recovery' ) ?></a>
            <a class="vlt-tab-item <?php echo $active == 'cart_logs' ? 'vlt-active' : '' ?>"
               href="<?php echo admin_url( 'admin.php?page=wacv_reports&tab=cart_logs' ) ?>"><?php esc_html_e( 'Cart Logs', 'woo-abandoned-cart-recovery' ) ?></a>
            <a class="vlt-tab-item <?php echo $active == 'product' ? 'vlt-active' : '' ?>"
               href="<?php echo admin_url( 'admin.php?page=wacv_reports&tab=product' ) ?>"><?php esc_html_e( 'Product', 'woo-abandoned-cart-recovery' ) ?></a>
            <a class="vlt-tab-item <?php echo $active == 'coupon' ? 'vlt-active' : '' ?>"
               href="<?php echo admin_url( 'admin.php?page=wacv_reports&tab=coupon' ) ?>"><?php esc_html_e( 'Coupon', 'woo-abandoned-cart-recovery' ) ?></a>
        </div>
		<?php
	}

	public function report_general() {
		?>
        <div class="wacv-abandoned-reports vlt-container">
            <div class="wacv-select-time-range vlt-row">
				<?php
				$start  = $end = $selected = '';
				$button = 'button';
				include_once WACVP_INCLUDES . 'templates/html-date-picker.php';
				?>
            </div>
            <div class="wacv-general-reports-group vlt-row"></div>
            <div class="wacv-chart-container">
                <canvas id="myChart"></canvas>
            </div>
        </div>

		<?php
	}

	public function report_screen_options() {
		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( $_GET['tab'] );
			switch ( $tab ) {
				case 'cart_logs':
					if ( isset( $_GET['section'] ) && $_GET['section'] == 'product' ) {
						Cart_Logs_Report_Product::get_instance();
					} else {
						Cart_Logs_Report_Table::get_instance();
					}
					break;
				case 'product':
					Product_Report_Table::get_instance();
					break;
				case 'coupon':
					Coupon_Used_Report_Table::get_instance();
					break;
			}
			$option = 'per_page';
			$args   = array(
				'label'   => __( 'Display', 'woo-abandoned-cart-recovery' ),
				'default' => 30,
				'option'  => 'wacv_acr_per_page'
			);
			add_screen_option( $option, $args );
		}
	}

	public function get_reports() {
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'get_reports' ) {
			if ( wp_verify_nonce( $_POST['_ajax_nonce'], 'wacv_get_reports' ) ) {
				if ( isset( $_POST['data'] ) ) {
					$data = wc_clean( $_POST['data'] );

					if ( isset( $data['time_option'] ) ) {
						$end = current_time( 'timestamp' );
						switch ( $data['time_option'] ) {
							case 'today':
								$start = strtotime( 'midnight', $end );
								$end   = strtotime( 'tomorrow', $start ) - 1;
								$this->get_final_data( $start, $end, 'P1H', 'H' );
								break;
							case 'yesterday':
								$start = strtotime( 'midnight', $end - 86400 );
								$end   = strtotime( 'tomorrow', $start ) - 1;
								$this->get_final_data( $start, $end, 'P1H', 'H' );
								break;
							case '30days':
								$start = $end - 30 * 86400;
								$this->get_final_data( $start, $end, 'P1D', 'M-d' );
								break;
							case '90days':
								$start = $end - 90 * 86400;
								$this->get_final_data( $start, $end, 'P7D', 'W', 'Week' );
								break;
							case '365days':
								$start = $end - 365 * 86400;
								$this->get_final_data( $start, $end, 'P1M', "M 'y" );
								break;
						}
					}

					if ( isset( $data['from_date'] ) || isset( $data['to_date'] ) ) {
						if ( $data['to_date'] - $data['from_date'] < 86400 ) {
							$this->get_final_data( $data['from_date'], $data['to_date'], 'P1H', "H" );
						} else {
							$this->get_final_data( $data['from_date'], $data['to_date'], 'P1D', "M-d" );
						}
					}
				}
			}
		}
		wp_die();
	}

	public function get_final_data( $start, $end, $format_step, $format_view, $prefix = '' ) {
		$response = $chart_data = array();
		$total    = $tax = $order_total = $order_tax = 0;

		if ( $format_view == 'H' ) {
			$abd_data = $rcv_data = array_fill( 0, 24, 0 );
		} else {
			$abd_data = $rcv_data = $this->get_array_time_range( $start, $end, $format_step, $format_view, $prefix );
		}
		//Abandoned report

		$abd_results = $this->query->get_abd_cart_report( $start, $end );
		$prefix      = $prefix ? $prefix . ' ' : '';

		foreach ( $abd_results as $item ) {
			$cart_items = json_decode( $item->abandoned_cart_info )->cart;
			$hour       = date_i18n( 'H', $item->abandoned_cart_time );
			$day        = date_i18n( 'd', $item->abandoned_cart_time );
			$month      = date_i18n( 'M', $item->abandoned_cart_time );
			$week       = date_i18n( 'W', $item->abandoned_cart_time );
			$year       = date_i18n( 'y', $item->abandoned_cart_time );

			$time = '';

			switch ( $format_view ) {
				case 'H':
					$time = intval( $hour );
					break;
				case 'M-d':
					$time = $month . '-' . $day;
					break;
				case "M 'y":
					$time = $month . " '" . $year;
					break;
				case 'W':
					$time = $prefix . $week;
					break;
			}

			foreach ( $cart_items as $product ) {
				$total += ( $product->line_total );
				$tax   += ( $product->line_tax );
				if ( ! isset( $abd_data[ $time ] ) ) {
					$abd_data[ $time ] = 0;
				}
				$abd_data[ $time ] += $product->line_total + $product->line_tax;
			}
		}


		//Recovered report

		$recovered_results = $this->query->get_recovered_cart_report( $start, $end );

		foreach ( $recovered_results as $item ) {
			$hour  = date_i18n( 'H', $item->abandoned_cart_time );
			$day   = date_i18n( 'd', $item->abandoned_cart_time );
			$month = date_i18n( 'M', $item->abandoned_cart_time );
			$week  = date_i18n( 'W', $item->abandoned_cart_time );
			$year  = date_i18n( 'y', $item->abandoned_cart_time );

			$time = '';

			switch ( $format_view ) {
				case 'H':
					$time = intval( $hour );
					break;
				case 'M-d':
					$time = $month . '-' . $day;
					break;
				case "M 'y":
					$time = $month . " '" . $year;
					break;
				case 'W':
					$time = $prefix . $week;
					break;
			}

			$recovered_items = wc_get_order( $item->recovered_cart );
			$order_total     += $recovered_items->get_total();
			$order_tax       += $recovered_items->get_total_tax();
			if ( ! isset( $rcv_data[ $time ] ) ) {
				$rcv_data[ $time ] = 0;
			}
			$rcv_data[ $time ] += $recovered_items->get_total() + $recovered_items->get_total_tax();
		}

		//Response report

		$_rcv_data['label'] = array_keys( $rcv_data );
		$_rcv_data['value'] = array_values( $rcv_data );
		$_abd_data['label'] = array_keys( $abd_data );
		$_abd_data['value'] = array_values( $abd_data );

		$response['abd_count']      = count( $abd_results );
		$response['abd_total']      = wc_price( $total + $tax );
		$response['abd_tax']        = wc_price( $tax );
		$response['abd_chart_data'] = $_abd_data;

		$response['rcv_count']      = count( $recovered_results );
		$response['rcv_total']      = wc_price( $order_total + $order_tax );
		$response['rcv_tax']        = wc_price( $order_tax );
		$response['rcv_chart_data'] = $_rcv_data;

		$response['email_sent']    = $this->query->get_email_history_report( $start, $end, 'email' );
		$response['email_clicked'] = $this->query->get_email_history_report( $start, $end, 'email', 1 );

		$response['messenger_sent']    = $this->query->get_email_history_report( $start, $end, 'messenger' );
		$response['messenger_clicked'] = $this->query->get_email_history_report( $start, $end, 'messenger', 1 );

		return wp_send_json( $response );
	}

	public function get_array_time_range( $start, $end, $format_step, $format_view, $prefix = '' ) {
		$prefix = $prefix ? $prefix . ' ' : '';
		$range  = array();
		$period = new \DatePeriod(
			new \DateTime( date_i18n( 'Y-m-d', $start ) ),
			new \DateInterval( $format_step ),
			new \DateTime( date_i18n( 'Y-m-d', $end + 86400 ) )
		);

		foreach ( $period as $p ) {
			$range[ $prefix . $p->format( $format_view ) ] = 0;
		}

		return $range;
	}

	public function clear_country_code_from_header( $data ) {
		global $pagenow;
		if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'wacv_sections', 'wacv_report' ) ) ) {
			$data['country'] = '';
		}

		return $data;
	}

	public function customer_emails_page() {
		printf( "<h2>%s</h2>", esc_html__( "Emails", 'woo-abandoned-cart-recovery' ) );

		$customer_emails = Customer_Emails::get_instance();
		$customer_emails->prepare_items();
		$customer_emails->display();
	}

	public function customer_emails_screen_options() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Display', 'woo-abandoned-cart-recovery' ),
			'default' => 30,
			'option'  => 'wacv_customer_emails_per_page'
		);
		add_screen_option( $option, $args );
	}

	public function dashboard() {
		wp_add_dashboard_widget(
			'wacv_abandoned_dashboard', __( 'WooCommerce Abandoned Cart Recovery', 'woo-abandoned-cart-recovery' ),
			array( $this, 'widget' )
		);
	}

	public function widget() {
		?>
        <canvas id="canvas"></canvas>
		<?php
	}

	public function cart_time_filter( $el ) {
		return $el['recovered_cart_time'] >= $this->start && $el['recovered_cart_time'] < $this->end;
	}

	public function reminder_time_filter( $el ) {
		return $el['send_mail_time'] >= $this->start && $el['send_mail_time'] < $this->end;
	}

	public function localize_script() {
		if ( 'dashboard' == get_current_screen()->id ) {
			$query  = Query_DB::get_instance();
			$x_axis = $recovered_line = $reminder_line = [];

			$i = 6;

			$today = strtotime( 'tomorrow' ) - 1;
			$time  = $today - WEEK_IN_SECONDS;

			$args = [
				'fields'       => [ 'id', 'recovered_cart_time' ],
				'where_clause' => "recovered_cart_time > {$time} AND recovered_cart != 0"
			];

			$recovered = $query->get_abandoned_list( $args );

			$args2 = [
				'fields'       => [ 'id', 'send_mail_time', 'number_of_mailing', 'messenger_sent', 'sms_sent' ],
				'where_clause' => "send_mail_time > {$time} AND (number_of_mailing != 0 OR messenger_sent != 0 OR sms_sent != 0)"
			];

			$reminders = $query->get_abandoned_list( $args2 );

			for ( $i; $i >= 0; $i -- ) {
				$this->end    = $today - DAY_IN_SECONDS * $i;
				$this->start  = $this->end - DAY_IN_SECONDS + 1;
				$recover_arr  = array_filter( $recovered, [ $this, 'cart_time_filter' ] );
				$reminder_arr = array_filter( $reminders, [ $this, 'reminder_time_filter' ] );

				$reminder_count = 0;
				if ( ! empty( $reminder_arr ) && is_array( $reminder_arr ) ) {
					foreach ( $reminder_arr as $data ) {
						$reminder_count += $data['number_of_mailing'] + $data['messenger_sent'] + $data['sms_sent'];
					}
				}

				$x_axis[]         = date_i18n( "M'd", $today - DAY_IN_SECONDS * $i );
				$recovered_line[] = count( $recover_arr );
				$reminder_line[]  = $reminder_count;
			}

			wp_localize_script( WACVP_SLUG . 'dashboard', 'wacvParams',
				[ 'xAxis' => $x_axis, 'recoveredLine' => $recovered_line, 'reminderLine' => $reminder_line ]
			);
		}
	}


	public function get_file_path() {
		$upload_dir = wp_upload_dir();

		return trailingslashit( $upload_dir['basedir'] ) . $this->filename;
	}

	public function export_csv( $step, $from, $to ) {
		$file_path = $this->get_file_path();

		if ( $step == 1 ) {
			@unlink( $file_path );
		}

		if ( ! @file_exists( $file_path ) ) {
			$buffer  = fopen( 'php://output', 'w' );
			$headers = [ 'ID', 'Date', 'Customer', 'Email', 'Phone', 'Product ID', 'Product name', 'SKU', 'Price', 'Quantity', 'Subtotal' ];

			ob_start();
			fputcsv( $buffer, $headers, ",", '"', "\0" );
			$data = ob_get_clean();

			@file_put_contents( $file_path, $data );
			@chmod( $file_path, 0664 );
		}

		$file = @file_get_contents( $file_path );

		$ex_from = $from ? strtotime( $from ) : '';
		$ex_to   = $to ? strtotime( $to ) + DAY_IN_SECONDS - 1 : '';
		$list    = $this->query->get_data_for_export_csv( $step, $ex_from, $ex_to );

		$export_data = [];
		$date_format = wc_date_format();
		if ( ! empty( $list ) ) {
			foreach ( $list as $item ) {
				if ( $item['user_id'] >= 100000000 ) {
					$name[] = $item['billing_first_name'];
					$name[] = $item['billing_last_name'];
					$name   = implode( ' ', $name );
					$email  = $item['billing_email'];
					$phone  = $item['billing_phone'];
				} else {
					$user  = get_userdata( $item['user_id'] );
					$name  = $user->display_name;
					$email = $user->user_email;
					$phone = '';
				}

				if ( ! empty( $item['abandoned_cart_info'] ) ) {
					$cart = json_decode( $item['abandoned_cart_info'], true );
					$cart = $cart['cart'];
					if ( ! empty( $cart ) && is_array( $cart ) ) {
						foreach ( $cart as $_item ) {
							$pid           = ! empty( $_item['variation_id'] ) ? $_item['variation_id'] : $_item['product_id'];
							$product       = wc_get_product( $pid );
							$price         = $product->get_price();
							$export_data[] = [
								$item['id'],
								date_i18n( $date_format, $item['abandoned_cart_time'] ),
								$name,
								$email,
								$phone,
								$pid,
								$product->get_formatted_name(),
								$product->get_sku(),
								$price,
								$_item['quantity'],
								$price * $_item['quantity']
							];
						}
					}
				}
			}
		}

		if ( empty( $export_data ) ) {
			$query_args = array(
				'nonce'  => wp_create_nonce( 'product-csv' ),
				'action' => 'download_abandoned_cart_csv',
			);
			wp_send_json_success(
				array(
					'complete' => true,
					'url'      => add_query_arg( $query_args, admin_url( 'admin.php?page=wacv_sections' ) ),
				)
			);
		} else {
			$buffer = fopen( 'php://output', 'w' );
			ob_start();
			array_walk( $export_data, array( $this, 'export_row' ), $buffer );
			$file .= ob_get_clean();
			@file_put_contents( $file_path, $file );
			wp_send_json_success( [ 'complete' => false ] );
		}

	}

	protected function export_row( $row_data, $key, $buffer ) {
		fputcsv( $buffer, $row_data, ",", '"', "\0" );
	}

	public function wacv_export_csv() {
		if ( ! check_ajax_referer( 'wacv_ajax_nonce', 'nonce' ) ) {
			wp_send_json_error();
		}
		$from           = ! empty( $_POST['from'] ) ? sanitize_text_field( $_POST['from'] ) : '';
		$to             = ! empty( $_POST['to'] ) ? sanitize_text_field( $_POST['to'] ) : '';
		$step           = ! empty( $_POST['step'] ) ? sanitize_text_field( $_POST['step'] ) : 1;
		$this->filename = ! empty( $_POST['filename'] ) ? sanitize_text_field( $_POST['filename'] ) . '.csv' : '';
		$this->export_csv( $step, $from, $to );
	}


	public function download_export_file() {

		if ( isset( $_GET['action'] ) && $_GET['action'] === 'download_abandoned_cart_csv' ) {
			$this->filename = $_GET['filename'] ? sanitize_text_field( $_GET['filename'] ) . '.csv' : '';
			if ( $this->filename ) {
				$this->send_headers();
				if ( @file_exists( $this->get_file_path() ) ) {
					$file = @file_get_contents( $this->get_file_path() );
					echo wp_kses_post( $file );
					@unlink( $this->get_file_path() );
					die();
				}
			}
		}
	}

	public function send_headers() {
		if ( function_exists( 'gc_enable' ) ) {
			gc_enable();
		}
		if ( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 );
		}
		@ini_set( 'zlib.output_compression', 'Off' );
		@ini_set( 'output_buffering', 'Off' );
		@ini_set( 'output_handler', '' );
		ignore_user_abort( true );
		wc_set_time_limit( 0 );
		wc_nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $this->filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
	}
}
