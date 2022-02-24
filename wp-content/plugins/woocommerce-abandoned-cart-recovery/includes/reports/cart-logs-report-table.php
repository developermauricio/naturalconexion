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

class Cart_Logs_Report_Table extends \WP_List_Table {

	protected static $instance = null;

	public $base_url;

	public $total_count;

	public $query;

	public $start;

	public $end;

	public function __construct() {
		//Set parent defaults
		parent::__construct( array(
			'singular' => 'cart_logs',     //singular name of the listed records
			'plural'   => 'cart_logs',    //plural name of the listed records
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
			<?php
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
			'email'  => __( 'Email', 'woo-abandoned-cart-recovery' ),
			'action' => __( 'Action', 'woo-abandoned-cart-recovery' ),
			'from'   => __( 'From', 'woo-abandoned-cart-recovery' ),
			''       => __( '', 'woo-abandoned-cart-recovery' ),
		);

		return $columns;
	}

	public function get_sortable_columns() {
		$sortable_columns = array(); //'name'=> array( 'name', true ),

		return $sortable_columns;
	}

	public function get_items() {

		$time        = Functions::get_time();
		$this->start = $time['start'];
		$this->end   = $time['end'];

		$export_data = $this->query->cart_log_record( $this->start, $this->end );

		return  $export_data ;
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

			switch ( $column_name ) {
				case 'email':
					if ( $item->user_login ) {
						$name = esc_html( $item->user_login );
					} elseif ( $item->billing_first_name || $item->billing_last_name ) {
						$name = esc_html( $item->billing_first_name ) . ' ' . esc_html( $item->billing_last_name );
					} else {
						$name = __( 'Guest', "woo-abandoned-cart-recovery" );
					}

					$out = "<div>$name</div>";

					$email = $item->user_email ? esc_html( $item->user_email ) : esc_html( $item->billing_email );
					$out   .= "<div>$email</div>";

					return $out;

				case 'action':
					$data_src = unserialize( $item->data );
					$date_fm  = get_option( 'date_format' );
					$time_fm  = get_option( 'time_format' );
					$out      = '';
					if ( ! empty( $data_src ) ) {
						foreach ( $data_src as $data ) {
							$product = wc_get_product( $data['product_id'] );
							if ( ! $product ) {
								continue;
							}
							$product_name = $product->get_name();
							$product_link = $product->get_permalink();
							$quantity     = $data['quantity'];
							$action       = $data['action'];
							$action_show  = $data['action'] == 'add_item' ? esc_html__( 'Added', "woo-abandoned-cart-recovery" ) : esc_html__( 'Removed', "woo-abandoned-cart-recovery" );
							$time         = date_i18n( $date_fm . ' ' . $time_fm, $data['time'] );
							$out          .= "<tr><td>$time</td><td class='$action'>$action_show</td><td><a href='$product_link' target='_blank'>{$product_name}</a> x {$quantity}</td></tr>";
						}
					}
					$out = "<table class='wacv-cart-log-rows'>$out</table>";

					return $out;

				case 'from':
					$code         = \WC_Geolocation::geolocate_ip( $item->ip )['country'] ?? '';
					$country_name = isset( WC()->countries->countries[ $code ] ) ? WC()->countries->countries[ $code ] : '';
					$country_flag = $code ? "<i class='" . strtolower( $code ) . " flag'></i>" : "<img class='wacv-country-flag' src='" . WACVP_IMAGES . '_unknown.png' . "'>";

					$from = "<div class='wacv-from'>
                            <div class='wacv-country-flag-group wacv-inline-block'>
                            {$country_flag}
                                <div class='wacv-from-detail'>
                                    <p>{$country_name}</p>
                                    <p>{$item->ip}</p>
                                </div>
                            </div>
                            <div class='wacv-user-agent wacv-inline-block'>
                                <i class='dashicons dashicons-info'></i>
                                <span class='wacv-user-agent-detail'>
                                    {$item->os_platform}
                                </span>    
                            </div>
                        </div>";

					return $from;

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


