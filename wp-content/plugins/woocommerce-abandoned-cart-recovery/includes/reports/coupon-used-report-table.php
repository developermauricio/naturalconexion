<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 23-03-19
 * Time: 2:08 PM
 */

namespace WACVP\Inc\Reports;

use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Coupon_Used_Report_Table extends \WP_List_Table {

	protected static $instance = null;

	public $per_page = 30;

	public $base_url;

	public $total_count;

	public $query;


	public function __construct() {
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'abandoned',     //singular name of the listed records
			'plural'   => 'abandoneds',    //plural name of the listed records
			'ajax'     => true        //does this table support ajax?
		) );
		$this->query = Query_DB::get_instance();
	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * It will add the bulk action function and other variable needed for the class.
	 * @since 2.5.2
	 * @see WP_List_Table::__construct()
	 */

	public function report_coupon() {
		$this->prepare_items();
		$this->display();
	}

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
//		$this->process_bulk_action();
		$data = $this->get_items();
//		check($data);die;

		usort( $data, array( $this, 'usort_reorder' ) );

		$current_page = $this->get_pagenum();


		$total_items = count( $data );

		$data = array_slice( $data, ( ( $current_page - 1 ) * $this->per_page ), $this->per_page );

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			//WE have to calculate the total number of items
			'per_page'    => $this->per_page,
			//WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $this->per_page )
			//WE have to calculate the total number of pages
		) );
	}

	public function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'id'; //If no sort, default to title
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'asc'; //If no order, default to asc
		$result  = strcmp( $a[ $orderby ], $b[ $orderby ] ); //Determine sort order

		return ( $order === 'asc' ) ? $result : - $result; //Send final sort direction to usort
	}

	public function get_items() {
		$export_data = array();

		$rcv_results = $this->query->get_number_of_rcv_product();

		if ( count( $rcv_results ) > 0 ) {
			foreach ( $rcv_results as $result ) {
				$order = wc_get_order( $result->recovered_cart );
				if ( $order ) {
					$coupons = $order->get_coupon_codes();

					foreach ( $coupons as $coupon ) {
						$coupon_id  = wc_get_coupon_id_by_code( $coupon );
						$coupon_obj = ( new \WC_Coupon( $coupon_id ) );

						if ( ! isset( $export_data[ $coupon_id ]['number_of_used'] ) ) {
							$export_data[ $coupon_id ]['number_of_used'] = 0;
						}

						$export_data[ $coupon_id ]['id']             = $coupon_id;
						$export_data[ $coupon_id ]['name']           = $coupon;
						$export_data[ $coupon_id ]['type']           = $coupon_obj->get_discount_type();
						$export_data[ $coupon_id ]['description']    = $coupon_obj->get_description();
						$export_data[ $coupon_id ]['amount']         = $coupon_obj->get_amount();
						$export_data[ $coupon_id ]['number_of_used'] += 1;
					}
				}
			}
		}

		return ( $export_data );
	}

	public function column_default( $item, $column_name ) {
		if ( ! empty( $item ) ) {
			switch ( $column_name ) {
				case 'id':
					return $item['id'];
				case 'name':
					return $item['name'];
				case 'amount':
					return $item['amount'];
				case 'type':
					return wc_get_coupon_type( $item['type'] );
				case 'number_of_used':
					return $item['number_of_used'];

				default:
					return;
			}
		}
	}

	public function get_columns() {
		$columns = array(
			'id'             => __( 'Id', 'woo-abandoned-cart-recovery' ),
			'name'           => __( 'Coupon Code', 'woo-abandoned-cart-recovery' ),
			'number_of_used' => __( 'Number of Used', 'woo-abandoned-cart-recovery' ),
			'amount'         => __( 'Amount', 'woo-abandoned-cart-recovery' ),
			'type'           => __( 'Type', 'woo-abandoned-cart-recovery' ),
			'description'    => __( 'Description', 'woo-abandoned-cart-recovery' ),
		);

		return $columns;
	}

	public function get_sortable_columns() {

		$sortable_columns = array(
			'id'             => array( 'id', true ),       //true means it's already sorted
			'number_of_used' => array( 'number_of_used', true ),
		);

		return $sortable_columns;
	}

}


