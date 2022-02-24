<?php

namespace WACVP\Inc\Reports;

use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Customer_Emails extends \WP_List_Table {
	protected static $instance = null;
	public $query;

	public function __construct() {
		parent::__construct( array(
			'singular' => 'customer_email',     //singular name of the listed records
			'plural'   => 'customer_emails',    //plural name of the listed records
			'ajax'     => true        //does this table support ajax?
		) );
		$this->query = Query_DB::get_instance();
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	function get_columns() {
		$columns = array(
			'id'            => 'ID',
			'customer_name' => 'Name',
			'billing_email' => 'Email',
			'billing_phone' => 'Phone',
		);

		return $columns;
	}

	public function prepare_items() {
		$current_tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'all';

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page     = $this->get_items_per_page( 'wacv_customer_emails_per_page', 30 );
		$current_page = $this->get_pagenum();

		$where_clause[] = "(billing_email !='' OR billing_phone !='')";
		$where_clause[] = $current_tab == 'unsubscribe' ? "AND status = 'unsubscribe'" : '';
		$where_clause   = implode( ' ', $where_clause );

		$args = [
			'fields'       => [ 'id', 'billing_email', 'billing_phone', 'billing_first_name', 'billing_last_name' ],
			'where_clause' => $where_clause,
			'limit'        => $per_page,
			'offset'       => ( $current_page - 1 ) * $per_page
		];

		$this->items = $this->query->get_customer_emails( $args );

		if ( $this->items ) {
			$total_items = $this->query->count_records( [ 'table' => 'wacv_guest_info_record', 'where_clause' => $where_clause ] );

			$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page                     //WE have to determine how many items to show on a page
			) );
		}

	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'billing_email':
			case 'billing_phone':
				return $item[ $column_name ];
			case 'customer_name':
				return $item['billing_first_name'] . ' ' . $item['billing_last_name'];
		}
	}

	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {

			$tabs = [
				'all'         => esc_html__( 'All', 'woo-abandoned-cart-recovery' ),
				'unsubscribe' => esc_html__( 'Unsubscribe', 'woo-abandoned-cart-recovery' )
			];

			$current_tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'all';
			?>
            <div>
                <ul class="subsubsub">
					<?php
					foreach ( $tabs as $key => $name ) {
						$current = $current_tab == $key ? 'current' : '';
						$href    = add_query_arg( [ 'tab' => $key ] );
						printf( "<li><a href='%s' class='%s'>%s</a></li>", esc_url( $href ), esc_attr( $current ), esc_html( $name ) );
						echo esc_html( $key === 'all' ? '|' : '' );
					}
					?>
                </ul>
            </div>

            <div class="clear"></div>

			<?php

			if ( ! empty( $_POST['wacv_export_email_empty'] ) && $_POST['wacv_export_email_empty'] == 'empty' ) {
				printf( "<div id='wacv-message' class='notice notice-error'><p>%s</p></div>", esc_html__( 'No record was found.' ) );
			}
			?>

            <div class="wacv-control-buttons">
                <form method="post" id="wacv-export-emails">
					<?php wp_nonce_field( 'wacv_export_customer_email', '_wacv_nonce' ) ?>
                    <div>
						<?php esc_html_e( 'From', 'woo-abandoned-cart-recovery' ); ?>
                        <input type="date" name="wacv_from">
						<?php esc_html_e( 'To', 'woo-abandoned-cart-recovery' ); ?>
                        <input type="date" name="wacv_to">
                        <select name="wacv_map_field" class="wacv-select-export-field">
                            <option value="both"><?php esc_html_e( 'Both email & phone', 'woo-abandoned-cart-recovery' ); ?></option>
                            <option value="email"><?php esc_html_e( 'Only email', 'woo-abandoned-cart-recovery' ); ?></option>
                            <option value="phone"><?php esc_html_e( 'Only phone', 'woo-abandoned-cart-recovery' ); ?></option>
                        </select>
                        <button type="submit" name="wacv_export_customer_emails" value="export" class="button wacv-submit-btn button-primary">
							<?php esc_html_e( 'Export', 'woo-abandoned-cart-recovery' ); ?>
                        </button>
                    </div>
                </form>
            </div>
			<?php
		}
	}
}

