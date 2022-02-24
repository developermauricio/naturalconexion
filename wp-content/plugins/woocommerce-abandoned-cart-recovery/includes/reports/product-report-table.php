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

class Product_Report_Table extends \WP_List_Table {

	protected static $instance = null;

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


	public function report_product() {
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

		$per_page = $this->get_items_per_page( 'wacv_acr_per_page', 30 );

		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			//WE have to calculate the total number of items
			'per_page'    => $per_page,
			//WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page )
			//WE have to calculate the total number of pages
		) );
	}

	public function get_columns() {
		$columns = array(
//			'id'         => __( 'Product Id', 'woo-abandoned-cart-recovery' ),
			'name'       => __( 'Product Name', 'woo-abandoned-cart-recovery' ),
			'abd_qty'    => __( 'Current Abandoned', 'woo-abandoned-cart-recovery' ),
			'abd_amount' => __( 'Amount of Abandoned', 'woo-abandoned-cart-recovery' ),
			'rcv_qty'    => __( 'Recovered', 'woo-abandoned-cart-recovery' ),
			'rcv_amount' => __( 'Amount of Recovered', 'woo-abandoned-cart-recovery' ),
		);

		return $columns;
	}

	public function get_sortable_columns() {

		$sortable_columns = array(
//			'id'         => array( 'id', true ),       //true means it's already sorted
			'name'       => array( 'name', true ),
			'abd_qty'    => array( 'abd_qty', true ),
			'abd_amount' => array( 'abd_amount', true ),
			'rcv_qty'    => array( 'rcv_qty', true ),
			'rcv_amount' => array( 'rcv_amount', true ),
		);

		return $sortable_columns;
	}

	public function get_items() {
		$search = $abd_results = $export_data = array();
		if ( ! empty( $_POST['s'] ) ) {
			$keyword = sanitize_text_field( $_POST['s'] );
			$args    = array( 'post_type' => 'product', 'post_status' => 'publish', 's' => $keyword );
			$items   = new \WP_Query( $args );
			if ( $items->have_posts() ) {
				foreach ( $items->posts as $item ) {
					$search[] = $item->ID;
				}
			}
		}

		$abd_results = $this->query->get_number_of_abd_product();

		if ( count( $abd_results ) > 0 ) {

			foreach ( $abd_results as $result ) {
				$cart = json_decode( $result->abandoned_cart_info )->cart;

				foreach ( $cart as $item ) {
					if ( ! empty( $search ) && ! in_array( $item->product_id, $search ) ) { //search item
						continue;
					}
					$pid     = $item->product_id;
					$product = wc_get_product( $pid );
					if ( ! $product ) {
						continue;
					}
					$name     = $product->get_name();
					$link     = $product->get_permalink();
					$quantity = $item->quantity;
					$amount   = $item->line_total + $item->line_tax;

					if ( ! isset( $export_data[ $pid ]['abd_amount'] ) ) {
						$export_data[ $pid ]['abd_amount'] = 0;
					}
					if ( ! isset( $export_data[ $pid ]['abd_qty'] ) ) {
						$export_data[ $pid ]['abd_qty'] = 0;
					}

					$export_data[ $pid ]['id']         = $pid;
					$export_data[ $pid ]['name']       = "<a href='{$link}' target='_blank'>{$name}</a>";
					$export_data[ $pid ]['abd_qty']    += $quantity;
					$export_data[ $pid ]['abd_amount'] += $amount;
				}
			}
		}

		$rcv_results = $this->query->get_number_of_rcv_product();
//
		if ( count( $rcv_results ) > 0 ) {
			foreach ( $rcv_results as $result ) {
				$order = wc_get_order( $result->recovered_cart );
				if ( is_a( $order, 'WC_Order' ) ) {
					$items = $order->get_items();
					foreach ( $items as $item ) {

						if ( ! isset( $export_data[ $item->get_product_id() ]['rcv_amount'] ) ) {
							$export_data[ $item->get_product_id() ]['rcv_amount'] = 0;
						}
						if ( ! isset( $export_data[ $item->get_product_id() ]['rcv_qty'] ) ) {
							$export_data[ $item->get_product_id() ]['rcv_qty'] = 0;
						}

						$export_data[ $item->get_product_id() ]['rcv_qty']    += $item->get_quantity();
						$export_data[ $item->get_product_id() ]['rcv_amount'] += $item->get_total() + $item->get_total_tax();
					}
				}
			}
		}

		foreach ( $export_data as $key => $data ) {
			if ( ! isset( $data['id'] ) ) {
				unset( $export_data[ $key ] );
			}
			if ( ! isset( $data['rcv_qty'] ) ) {
				$export_data[ $key ]['rcv_qty'] = 0;
			}
			if ( ! isset( $data['rcv_amount'] ) ) {
				$export_data[ $key ]['rcv_amount'] = 0;
			}
		}

		return ( $export_data );
	}

	public function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'id'; //If no sort, default to title
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'asc'; //If no order, default to asc
		$result  = strcmp( $a[ $orderby ], $b[ $orderby ] ); //Determine sort order

		return ( $order === 'asc' ) ? $result : - $result; //Send final sort direction to usort
	}

	public function column_default( $item, $column_name ) {
		if ( ! empty( $item ) ) {
			switch ( $column_name ) {
				case 'name':
					return $item['name'];
				case 'abd_qty':
					return $item['abd_qty'];
				case 'abd_amount':
					return wc_price( $item['abd_amount'] );
				case 'rcv_qty':
					return $item['rcv_qty'];
				case 'rcv_amount':
					return wc_price( $item['rcv_amount'] );

				default:
					return;
			}
		}
	}

	function extra_tablenav( $which ) {
		if ( $which == 'top' ) {

			?>
            <form method="post" class="wacv-filter-form">
                <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>"/>
				<?php
				$this->search_box( 'Search', 'cart_logs_product' );
				?>
            </form>
			<?php
		}
	}
}


