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

class Recovered_Report_Table extends \WP_List_Table {

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
	public function recovered_table() {
		$class_all_customer = $class_member = $class_guest = '';
		$section            = isset( $_GET['recovered'] ) ? sanitize_text_field( $_GET['recovered'] ) : '';

		switch ( $section ) {
			case 'all_customer':
			case '':
				$class_all_customer = 'current';
				break;
			case 'member':
				$class_member = 'current';
				break;
			case 'guest':
				$class_guest = 'current';
				break;
		}

		?>
        <h3><?php esc_html_e( 'Recovered Carts', 'woo-abandoned-cart-recovery' ) ?></h3>
		<?php do_action( 'wacv_notices' ) ?>
        <div class="wacv-abanoned-cart-page">
            <ul class="subsubsub">
                <li><a href="<?php echo admin_url( 'admin.php?page=wacv_recovered&recovered=all_customer' ) ?>"
                       class="<?php echo esc_html( $class_all_customer ) ?>"><?php esc_html_e( 'All', 'woo-abandoned-cart-recovery' ) ?></a>
                    |
                </li>
                <li><a href="<?php echo admin_url( 'admin.php?page=wacv_recovered&recovered=member' ) ?>"
                       class="<?php echo esc_html( $class_member ) ?>"><?php esc_html_e( 'Member', 'woo-abandoned-cart-recovery' ) ?></a>
                    |
                </li>
                <li><a href="<?php echo admin_url( 'admin.php?page=wacv_recovered&recovered=guest' ) ?>"
                       class="<?php echo esc_html( $class_guest ) ?>"><?php esc_html_e( 'Guest', 'woo-abandoned-cart-recovery' ) ?></a>
                </li>
            </ul>
			<?php
			$this->show_record();
			?>
        </div>
		<?php
	}

	public function show_record() {
		$this->prepare_items();
		$this->display();
	}

	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'id'           => __( 'ID', 'woo-abandoned-cart-recovery' ),
			'customer'     => __( 'Customer', 'woo-abandoned-cart-recovery' ),
			'email'        => __( 'Email Address', 'woo-abandoned-cart-recovery' ),
			'order_detail' => __( 'Recovered Cart Detail', 'woo-abandoned-cart-recovery' ),
			'coupon'       => __( 'Coupon used', 'woo-abandoned-cart-recovery' ),
			'date'         => __( 'Recovered Date', 'woo-abandoned-cart-recovery' ),
			'more_info'    => __( 'More Info', 'woo-abandoned-cart-recovery' )
		);

		return apply_filters( 'wcal_abandoned_orders_columns', $columns );
	}

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
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
		$result  = strcmp( $a->$orderby, $b->$orderby ); //Determine sort order

		return ( $order === 'asc' ) ? $result : - $result; //Send final sort direction to usort
	}

	public function get_items() {
		$section     = isset( $_GET['recovered'] ) ? sanitize_text_field( $_GET['recovered'] ) : 'all_customer';
		$export_data = $results = array();

		switch ( $section ) {
			case 'all_customer':
				$results = $this->query->get_recover_list( 'all_customer' );
				break;
			case 'member':
				$results = $this->query->get_recover_list( 'member' );
				break;
			case 'guest':
				$results = $this->query->get_recover_list( 'guest' );
				break;
		}

		$i = 0;
		foreach ( $results as $result ) {

			$user_email = $result->user_email;
			$name       = $result->user_login;

			if ( $result->user_type == 'guest' ) {
				$results_guest = $this->query->get_guest_info( $result->user_id );
				if ( ! empty( $results_guest ) ) {
					$user_email = $results_guest->billing_email;
					$name       = $results_guest->billing_first_name . ' ' . $results_guest->billing_last_name;
					$name       = ! empty( trim( $name ) ) ? $name : __( 'Guest', 'woo-abandoned-cart-recovery' );
				} else {
					$user_email = '';
					$name       = __( 'Visitor', 'woo-abandoned-cart-recovery' );
				}
			}

			$order_id = $result->recovered_cart;
			$order    = wc_get_order( $order_id );
			$coupon   = implode( ',', $order->get_used_coupons() );

			$item_total = ( $order->get_order_item_totals() );

			$line_total = $item_total['order_total']['value'];
			$line_tax   = $item_total['tax']['value'];

			$date_format = date_i18n( get_option( 'date_format' ), $result->recovered_cart_time );
			$time_format = date_i18n( get_option( 'time_format' ), $result->recovered_cart_time );

			$export_data[ $i ] = new \stdClass();

			$export_data[ $i ]->id       = $result->id;
			$export_data[ $i ]->customer = $name;
			$export_data[ $i ]->email    = $user_email;
			$export_data[ $i ]->total    = $line_total;
			$export_data[ $i ]->tax      = $line_tax;
			$export_data[ $i ]->coupon   = $coupon;
			$export_data[ $i ]->date     = $date_format . ' ' . $time_format;
			$i ++;
		}

		return ( $export_data );
	}

	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'id':
				return $item->id;
			case 'customer':
				return $item->customer;
			case 'email':
				return $item->email;
			case 'coupon':
				return $item->coupon;
			case 'order_detail':
				$out = "<table class='wacv-recovered-detail-tb'><tr><td>" . __( 'Total: ', 'woo-abandoned-cart-recovery' ) . "</td><td>" . $item->total . "</td></tr>";
				$out .= "<tr><td>" . __( 'Tax: ', 'woo-abandoned-cart-recovery' ) . "</td><td>" . $item->tax . "</td></tr>";
				$out .= "</table>";

				return $out;
			case 'date':
				return $item->date;
			default:
				return;
		}

	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'wacv_abandoned_id',
			$item->id  //$abd_id
		);
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'           => array( 'id', true ),       //true means it's already sorted
			'customer'     => array( 'customer', true ),
			'order_detail' => array( 'total', true ),
			'date'         => array( 'date', true ),
		);

		return $sortable_columns;
	}

	public function process_bulk_action() {
//	    check($this->current_action());die;
		if ( 'delete' == $this->current_action() ) {
//			check( $_GET );
			wp_die( 'Items deleted (or they would be if we had items to delete)!' );
		}
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete'
		);

		return $actions;
	}

}


