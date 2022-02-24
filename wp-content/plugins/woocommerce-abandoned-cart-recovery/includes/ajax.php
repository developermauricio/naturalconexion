<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 09-04-19
 * Time: 2:00 PM
 */

namespace WACVP\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {

	protected static $instance = null;

	/**
	 * Setup instance attributes
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		add_action( 'wp_ajax_wacv_search', array( $this, 'wacv_search' ) );
		add_action( 'wp_ajax_wacv_get_email_history', array( $this, 'wacv_get_email_history' ) );
		add_action( 'wp_ajax_wacv_get_abd_cart_detail', array( $this, 'wacv_get_abd_cart_detail' ) );
		add_action( 'wp_ajax_wacv_remove_record', array( $this, 'wacv_remove_record' ) );

		//Export customer emails
//		add_action( 'wp_ajax_wacv_export_customer_emails', array( $this, 'wacv_export_customer_emails' ) );

		add_action( 'admin_init', array( $this, 'export_customer_emails' ) );

//		Update database
		add_action( 'admin_init', array( $this, 'update_database' ) );
	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function wacv_search() {

		if ( isset( $_GET['action'] ) && $_GET['action'] == 'wacv_search' ) {
			if ( isset( $_GET['param'] ) ) {
				$keyword = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );
				$result  = array();

				switch ( sanitize_text_field( $_GET['param'] ) ) {
					case 'user':
						$args = array( 'orderby' => 'nicenamne', 'order' => 'DESC', 's' => $keyword );

						$users = get_users( $args );
						foreach ( $users as $user ) {
							$result[] = array( 'id' => $user->ID, 'text' => $user->user_nicename );
						}
						break;
					case 'coupon':
						$args  = array( 'post_type' => 'shop_coupon', 'post_status' => 'publish', 's' => $keyword );
						$items = new \WP_Query( $args );
						if ( $items->have_posts() ) {
							foreach ( $items->posts as $item ) {
								$result[] = array( 'id' => $item->ID, 'text' => $item->post_title );
							}
						}
						break;
					case 'product':
						$args  = array( 'post_type' => 'product', 'post_status' => 'publish', 's' => $keyword );
						$items = new \WP_Query( $args );
						if ( $items->have_posts() ) {
							foreach ( $items->posts as $item ) {
								$result[] = array( 'id' => $item->ID, 'text' => $item->post_title );
							}
						}
						break;
				}
				wp_send_json( $result );
			}
		}
		wp_die();
	}

	public function wacv_get_email_history() {
		if ( isset( $_POST['id'] ) ) {
			$id       = sanitize_text_field( $_POST['id'] );
			$query    = Query_DB::get_instance();
			$results  = $query->get_email_history( $id );
			$date_fm  = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			$response = array();

			foreach ( $results as $result ) {
				$response[] = array(
					'type'      => $result->type,
					'sent_time' => $result->sent_time > 0 ? date_i18n( $date_fm, $result->sent_time ) : '',
					'opened'    => $result->opened > 0 ? date_i18n( $date_fm, $result->opened ) : '',
					'clicked'   => $result->clicked > 0 ? date_i18n( $date_fm, $result->clicked ) : '',
				);
			}
			wp_send_json( $response );
		}
		wp_die();
	}

	public function wacv_get_abd_cart_detail() {
		if ( isset( $_POST['id'] ) ) {
			$id     = sanitize_text_field( $_POST['id'] );
			$query  = Query_DB::get_instance();
			$result = $query->get_abd_cart_detail( $id );
			if ( $result ) {
				$cart     = json_decode( $result->abandoned_cart_info );
				$response = array();
				foreach ( $cart->cart as $item ) {
					$pid = $item->variation_id ? $item->variation_id : $item->product_id;
					$pd  = wc_get_product( $pid );
					if ( $pd ) {
						$p_name     = $pd->get_name();
						$line_total = $item->line_total + $item->line_tax;

						$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $pid ), 'thumbnail' );
						$image_url = $image_url ? $image_url : wp_get_attachment_image_src( get_post_thumbnail_id( $item->product_id ), 'thumbnail' );
						$image_url = $image_url ? $image_url : array( wc_placeholder_img_src( 'thumbnail' ) );

						$response[] = array(
							'name'     => $p_name,
							'amount'   => wc_price( $line_total ),
							'quantity' => $item->quantity,
							'img'      => $image_url[0]
						);
					} else {
						$response[] = array(
							'name'     => __( 'This product is not exist', 'woo-abandoned-cart-recovery' ),
							'amount'   => '',
							'quantity' => '',
							'img'      => ''
						);
					}
				}
				wp_send_json( $response );
			}
		}
		wp_die();
	}

	public function update_database() {
		$db_ver         = 1;
		$current_db_ver = get_option( 'wacv_db_ver' );
		if ( version_compare( $db_ver, $current_db_ver, '>' ) ) {
			$r[] = $this->modify_column( 'wacv_guest_info_record', 'ip', 'change ip input_date int(11) null' );
			$r[] = $this->drop_column( 'wacv_guest_info_record', 'os' );
			$r[] = $this->drop_column( 'wacv_guest_info_record', 'browser' );
			if ( ! in_array( 'fail', $r ) ) {
				update_option( 'wacv_db_ver', $db_ver );
			}
		}

		$db_ver       = 2;
		$check_update = get_option( 'wacv_update_db_ver' . $db_ver );
		if ( ! $check_update ) {
			$r[] = $this->modify_column( 'wacv_guest_info_record', 'shipping_charges', 'change shipping_charges status varchar(12) null' );
			if ( ! in_array( 'fail', $r ) ) {
				update_option( 'wacv_update_db_ver' . $db_ver, $db_ver );
			}
		}

		$this->update_column( 'wacv_abandoned_cart_record', 'email_complete', 'number_of_mailing', "enum('0','1')" );
		$this->update_column( 'wacv_abandoned_cart_record', 'sms_complete', 'sms_sent', "enum('0','1')" );
		$this->update_column( 'wacv_abandoned_cart_record', 'messenger_complete', 'messenger_sent', "enum('0','1')" );
	}

	public function update_column( $table, $col, $after, $format ) {
		global $wpdb;
		$update = 3;
		if ( ! get_option( 'wacv_update_db_' . $update . $col ) ) {
			$dbname = DB_NAME;
			$sql    = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA ='{$dbname}' AND TABLE_NAME = '{$wpdb->prefix}{$table}' AND COLUMN_NAME = '{$col}'";

			$check_exist = $wpdb->query( $sql );

			if ( ! $check_exist ) {
				$sql_add_col = " ALTER TABLE {$wpdb->prefix}{$table} ADD $col {$format}  AFTER {$after}";
				$result      = $wpdb->query( $sql_add_col );
				if ( $result ) {
					update_option( 'wacv_update_db_' . $update . $col, 1 );
				}
			} else {
				update_option( 'wacv_update_db_' . $update . $col, 1 );
			}
		}
	}

	public function modify_column( $table, $col, $query ) {
		global $wpdb;
		$dbname      = DB_NAME;
		$sql         = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA ='{$dbname}' AND TABLE_NAME = '{$wpdb->prefix}{$table}' AND COLUMN_NAME = '{$col}'";
		$check_exist = $wpdb->query( $sql );
		if ( $check_exist ) {
			try {
				$mod_sql = "ALTER TABLE {$wpdb->prefix}{$table} {$query}";
				$r       = $wpdb->query( $mod_sql );

				return $r ? $r : 'fail';
			} catch ( \Exception $e ) {
			}
		}

		return '';
	}

	public function drop_column( $table, $col ) {
		global $wpdb;
		$dbname      = DB_NAME;
		$sql         = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA ='{$dbname}' AND TABLE_NAME = '{$wpdb->prefix}{$table}' AND COLUMN_NAME = '{$col}'";
		$check_exist = $wpdb->query( $sql );
		if ( $check_exist ) {
			try {
				$mod_sql = "ALTER TABLE {$wpdb->prefix}{$table} drop column {$col}";
				$r       = $wpdb->query( $mod_sql );

				return $r ? $r : 'fail';

			} catch ( \Exception $e ) {
			}
		}

		return '';
	}

	public function wacv_remove_record() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( esc_html__( 'You have no permission to do this action', 'woo-abandoned-cart-recovery' ) );
		}

		$id = ! empty( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';

		if ( ! $id ) {
			wp_send_json_error( esc_html__( 'No id to remove record', 'woo-abandoned-cart-recovery' ) );
		}

		$query  = Query_DB::get_instance();
		$result = $query->remove_abd_record( $id, true );
		if ( $result ) {
			wp_send_json_success( $result );
		}

		wp_die();
	}

	public function wacv_export_customer_emails() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wacv_export_emails' ) ) {
			return;
		}

		$type = ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'partial';

		$fields = [];
		switch ( $_POST['field'] ) {
			case 'both':
				$fields = [ 'billing_email', 'billing_phone' ];
				break;
			case 'email':
				$fields = [ 'billing_email' ];
				break;
			case 'phone':
				$fields = [ 'billing_phone' ];
				break;
		}

		if ( $type == 'partial' ) {
			$last_id = get_option( 'wacv_last_export_customer_emails' );
			$last_id = $last_id ? $last_id : WACVP_GUEST_ID_LIMIT;
			$query   = Query_DB::get_instance();
			$args    = [
				'fields'       => $fields,
				'where_clause' => "id > {$last_id}",
				'limit'        => 100,
			];
			$query->get_customer_emails( $args );

		}

		wp_die();
	}

	public function export_customer_emails() {

		if ( ! empty( $_POST['wacv_export_customer_emails'] ) && ! empty( $_POST['wacv_map_field'] ) && isset( $_POST['_wacv_nonce'] ) && wp_verify_nonce( $_POST['_wacv_nonce'], 'wacv_export_customer_email' ) ) {


			$query = Query_DB::get_instance();

			$from = ! empty( $_POST['wacv_from'] ) ? sanitize_text_field( $_POST['wacv_from'] ) : '';
			$to   = ! empty( $_POST['wacv_to'] ) ? sanitize_text_field( $_POST['wacv_to'] ) : '';

			$where_clause = [];
			$filename     = 'wacv_emails_';

			if ( ! empty( $_GET['tab'] ) && $_GET['tab'] == 'unsubscribe' ) {
				$where_clause[] = "status='unsubscribe'";
				$filename       .= 'unsubscribe_';
			}

			switch ( true ) {
				case $from && ! $to:
					$filename       .= "from_{$from}";
					$from           = strtotime( $from );
					$where_clause[] = "input_date > {$from}";
					break;
				case ! $from && $to:
					$filename       .= "to_{$to}";
					$to             = strtotime( $to ) + 86399;
					$where_clause[] = "input_date < {$to}";
					break;
				case $from && $to:
					$filename       .= $from == $to ? $from : "from_{$from}_to_{$to}";
					$from           = strtotime( $from );
					$to             = strtotime( $to ) + 86399;
					$where_clause[] = "(input_date > {$from} AND input_date < {$to})";
					break;
				default :
					$filename .= time();
					break;
			}

			$header_row = [ 'ID', 'First name', 'Last name' ];
			$fields     = [ 'id', 'billing_first_name', 'billing_last_name' ];

			switch ( $_POST['wacv_map_field'] ) {
				case 'email':
					$fields[]       = 'billing_email';
					$header_row[]   = 'Email';
					$where_clause[] = "billing_email != ''";
					break;
				case 'phone':
					$fields[]       = 'billing_phone';
					$header_row[]   = 'Phone';
					$where_clause[] = "billing_phone != ''";
					break;
				case 'both':
				default:
					$fields[]       = 'billing_email';
					$fields[]       = 'billing_phone';
					$header_row[]   = 'Email';
					$header_row[]   = 'Phone';
					$where_clause[] = "(billing_email != '' OR billing_phone != '')";
					break;
			}

			$args = [ 'fields' => $fields, 'order' => 'ASC' ];

			if ( ! empty( $where_clause ) ) {
				$args['where_clause'] = implode( ' AND ', $where_clause );
			}

			$data_rows = $query->get_customer_emails( $args );

			if ( empty( $data_rows ) ) {
				$_POST['wacv_export_email_empty'] = 'empty';

				return;
			}

			$filename .= '.csv';

			$fh = @fopen( 'php://output', 'w' );
			fprintf( $fh, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Description: File Transfer' );
			header( 'Content-type: text/csv' );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			header( 'Expires: 0' );
			header( 'Pragma: public' );
			fputcsv( $fh, $header_row );
			foreach ( $data_rows as $data_row ) {
				fputcsv( $fh, $data_row );
			}
			$csvFile = stream_get_contents( $fh );
			fclose( $fh );
			die;
		}
	}
}