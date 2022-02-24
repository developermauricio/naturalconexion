<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 23-03-19
 * Time: 2:08 PM
 */

namespace WACVP\Inc\Reports;

use WACVP\Inc\Functions;
use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Cart_Logs_Report_Product extends \WP_List_Table {

	protected static $instance = null;

//	public $per_page = 5;

	public $base_url;

	public $total_count;

	public $query;

	public $start;

	public $end;

	public function __construct() {
		parent::__construct( array(
			'singular' => 'cart_logs_product',     //singular name of the listed records
			'plural'   => 'cart_logs_product',    //plural name of the listed records
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


	public function report_cart_logs() {
		$this->prepare_items();
		?>
        <form method="get" class="wacv-filter-form">
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>"/>
            <input type="hidden" name="tab" value="<?php echo esc_attr($_REQUEST['tab']); ?>"/>
            <input type="hidden" name="section" value="<?php echo esc_attr($_REQUEST['section']); ?>"/>
			<?php
			$this->search_box( 'Search', 'cart_logs_product' );
			$this->display();
			?>
        </form>
		<?php
	}

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
//		$this->process_bulk_action();
		$data = $this->get_items();
//		check($data);die;

//		usort( $data, array( $this, 'usort_reorder' ) );

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
			'product'     => __( 'Product', 'woo-abandoned-cart-recovery' ),
			'add_item'    => __( 'Add item', 'woo-abandoned-cart-recovery' ),
			'remove_item' => __( 'Remove item', 'woo-abandoned-cart-recovery' ),
			'total'       => __( 'Total', 'woo-abandoned-cart-recovery' ),
		);

		return $columns;
	}

	public function get_sortable_columns() {

		$sortable_columns = array(//			'name'             => array( 'name', true ),
		);

		return $sortable_columns;
	}

	public function get_items() {
		$search = $get_data = $export_data = array();

		$time        = Functions::get_time();
		$this->start = $time['start'];
		$this->end   = $time['end'];

		if ( ! empty( $_GET['s'] ) ) {
			$keyword = sanitize_text_field( $_GET['s'] );
			$args    = array( 'post_type' => 'product', 'post_status' => 'publish', 's' => $keyword );
			$items   = new \WP_Query( $args );
			if ( $items->have_posts() ) {
				foreach ( $items->posts as $item ) {
					$search[] = $item->ID;
				}
			}
		}

		$get_data = $this->query->cart_log_record( $this->start, $this->end );

		if ( count( $get_data ) > 0 ) {
			foreach ( $get_data as $item ) {
				$list_action = unserialize( $item->data );
				if ( $list_action ) {
					foreach ( $list_action as $action ) {

						$id = $action['product_id'];

						if ( ! empty( $search ) && ! in_array( $id, $search ) ) {
							continue;
						}

						$export_data[ $id ]['product'] = wc_get_product( $id )->get_name();

						if ( ! isset( $export_data[ $id ]['add_item'] ) ) {
							$export_data[ $id ]['add_item'] = 0;
						}

						if ( ! isset( $export_data[ $id ]['remove_item'] ) ) {
							$export_data[ $id ]['remove_item'] = 0;
						}

						if ( $action['action'] == 'add_item' ) {
							$export_data[ $id ]['add_item'] += $action['quantity'];
						}

						if ( $action['action'] == 'remove_item' ) {
							$export_data[ $id ]['remove_item'] += $action['quantity'];
						}
					}
				}
			}
		}

		return ( $export_data );
	}

	public function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'name'; //If no sort, default to title
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'asc'; //If no order, default to asc

		if ( ! isset( $a[ $orderby ] ) ) {
			$a[ $orderby ] = 0;
		}
		if ( ! isset( $b[ $orderby ] ) ) {
			$b[ $orderby ] = 0;
		}

		if ( is_int( $a[ $orderby ] ) ) {
			$result = $a[ $orderby ] - $b[ $orderby ] >= 0 ? 1 : - 1; //Determine sort order
		} else {
			$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); //Determine sort order
		}

		return ( $order === 'asc' ) ? $result : - $result; //Send final sort direction to usort
	}

	public function column_default( $item, $column_name ) {
		if ( ! empty( $item ) ) {
//			check( $item );
			switch ( $column_name ) {
				case 'product':
					return $item['product'];
				case 'add_item':
					return $item['add_item'];
				case 'remove_item':
					return $item['remove_item'];
				case 'total':
					return $item['add_item'] - $item['remove_item'];
				default:
					return;
			}
		}
	}

	function extra_tablenav( $which ) {
		if ( $which == 'top' ) {
			$button   = 'submit';
			$selected = isset( $_GET['wacv_time_range'] ) ? sanitize_text_field( $_GET['wacv_time_range'] ) : get_option( 'wacv_time_range' );
			update_option( 'wacv_time_range', $selected );
			wc_get_template(
				'html-date-picker.php',
				array( 'start' => $this->start, 'end' => $this->end, 'button' => $button, 'selected' => $selected ),
				'', WACVP_TEMPLATES );
		}
	}
}


