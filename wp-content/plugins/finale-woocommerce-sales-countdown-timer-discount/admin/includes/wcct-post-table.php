<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WCCT_Post_Table extends WP_List_Table {

	public $per_page = 20;
	public $data;
	public $meta_data;

	/**
	 * Constructor.
	 * @since  1.0.0
	 */
	public function __construct( $args = array() ) {
		global $status, $page;
		parent::__construct( array(
			'singular' => 'campaign', //singular name of the listed records
			'plural'   => 'campaigns', //plural name of the listed records
			'ajax'     => false,        //does this table support ajax?
		) );
		$status     = 'all';
		$page       = $this->get_pagenum();
		$this->data = array();
		// Make sure this file is loaded, so we have access to plugins_api(), etc.
		require_once( ABSPATH . '/wp-admin/includes/plugin-install.php' );
		parent::__construct( $args );
	}

	/**
	 * Text to display if no items are present.
	 * @since  1.0.0
	 * @return  void
	 */
	public function no_items() {
		echo wpautop( __( 'No Campaign Available', 'finale-woocommerce-sales-countdown-timer-discount' ) );
	}

	/**
	 * The content of each column.
	 *
	 * @param  array $item The current item in the list.
	 * @param  string $column_name The key of the current column.
	 *
	 * @since  1.0.0
	 * @return string              Output for the current column.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'check-column':
				return '&nbsp;';
			case 'status':
				return $item[ $column_name ];
				break;
		}
	}

	public function get_item_data( $item_id ) {
		global $wpdb;
		$data = array();

		if ( isset( $this->meta_data[ $item_id ] ) ) {
			$data = $this->meta_data[ $item_id ];
		} else {
			$this->meta_data[ $item_id ] = WCCT_Common::get_item_data( $item_id );
			$data                        = $this->meta_data[ $item_id ];
		}

		return $data;
	}

	public function column_campaign( $item ) {
		$output      = '';
		$data        = $this->get_item_data( (int) $item['id'] );
		$data_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		if ( isset( $data['campaign_fixed_recurring_start_date'] ) && $data['campaign_fixed_recurring_start_date'] != '' ) {
			$start_date    = $data['campaign_fixed_recurring_start_date'];
			$start_time    = $data['campaign_fixed_recurring_start_time'];
			$date1         = new Datetime( $start_date . ' ' . $start_time );
			$campaign_type = '';
			if ( $data['campaign_type'] == 'fixed_date' ) {
				$campaign_type = __( 'Fixed Date', 'finale-woocommerce-sales-countdown-timer-discount' );
			}
			$output = '';
			if ( ! empty( $campaign_type ) ) {
				$output .= '<strong>' . __( 'Type', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</strong>: ' . $campaign_type . '<br/>';
			}
			$output .= '<strong>' . __( 'Starts On', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</strong>: ' . $date1->format( $data_format ) . '<br/>';
			if ( $data['campaign_type'] == 'fixed_date' ) {
				$end_date = $data['campaign_fixed_end_date'];
				$end_time = $data['campaign_fixed_end_time'];
				$date2    = new Datetime( $end_date . ' ' . $end_time );
				$output   .= '<strong>' . __( 'Expires On', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</strong>: ' . $date2->format( $data_format );
			}
		}

		return wpautop( $output );
	}

	public function column_deals( $item ) {
		$data   = $this->get_item_data( (int) $item['id'] );
		$output = '';
		if ( isset( $data['deal_enable_price_discount'] ) && $data['deal_enable_price_discount'] == '1' ) {
			$deal_amount = (float) isset( $data['deal_amount'] ) ? $data['deal_amount'] : 0;

			switch ( $data['deal_type'] ) {
				case 'percentage':
					$deal_amount_text = "{$deal_amount}% on Regular Price";
					break;

				case 'percentage_sale':
					$deal_amount_text = "{$deal_amount}% on Sale Price";
					break;

				case 'fixed_sale':
					$currencySymbol   = get_woocommerce_currency_symbol();
					$deal_amount_text = "{$deal_amount}{$currencySymbol} on Regular Price";
					break;

				case 'fixed_price':
					$currencySymbol   = get_woocommerce_currency_symbol();
					$deal_amount_text = "{$deal_amount}{$currencySymbol} on Sale Price";
					break;
				case 'flat_sale':
					$currencySymbol   = get_woocommerce_currency_symbol();
					$deal_amount_text = "Flat {$deal_amount}{$currencySymbol} Price";
					break;
			}

			$output .= '<strong>' . __( 'Discount', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</strong>: ' . $deal_amount_text . '<br/>';
		}

		if ( isset( $data['deal_enable_goal'] ) && $data['deal_enable_goal'] == '1' ) {
			$deal_stock_text = '';
			if ( $data['deal_units'] == 'same' ) {
				$deal_stock_text = __( 'Product Stock', 'finale-woocommerce-sales-countdown-timer-discount' );
			} else {
				$deal_stock_text = __( 'Custom Stock', 'finale-woocommerce-sales-countdown-timer-discount' );
				if ( $data['deal_custom_units'] != '' ) {
					$goal_amt        = $data['deal_custom_units'];
					$deal_stock_text .= " ({$goal_amt})";
				}
			}
			$output .= '<strong>' . __( 'Inventory', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</strong>: ' . $deal_stock_text;
		}

		return wpautop( $output );
	}

	public function column_appearance( $item ) {
		$data = $this->get_item_data( (int) $item['id'] );

		$output = array();
		if ( $data['location_timer_show_single'] == '1' ) {
			$output[] = __( 'Countdown Timer', 'finale-woocommerce-sales-countdown-timer-discount' );
		}
		if ( $data['location_bar_show_single'] == '1' ) {
			$output[] = __( 'Counter Bar', 'finale-woocommerce-sales-countdown-timer-discount' );
		}

		return wpautop( implode( '<br/>', $output ) );
	}

	/**
	 * Content for the "product_name" column.
	 *
	 * @param  array $item The current item.
	 *
	 * @since  1.0.0
	 * @return string       The content of this column.
	 */
	public function column_status( $item ) {
		$output = WCCT_Common::wcct_set_campaign_status( $item['id'] );
		if ( $item['trigger_status'] == WCCT_SHORT_SLUG . 'disabled' ) {
			$output = __( 'Deactivated', 'finale-woocommerce-sales-countdown-timer-discount' );
		}

		return wpautop( $output );
	}

	public function column_priority( $item ) {
		$data = $this->get_item_data( (int) $item['id'] );
		if ( isset( $data['campaign_menu_order'] ) ) {
			return $data['campaign_menu_order'];
		} else {
			update_post_meta( (int) $item['id'], '_wcct_campaign_menu_order', 0 );

			return 0;
		}

		return;
	}

	public function column_name( $item ) {
		$edit_link     = WCCT_Common::get_edit_post_link( $item['id'] );
		$column_string = '<strong>';
		if ( $item['trigger_status'] == 'trash' ) {
			$column_string .= '' . _draft_or_post_title( $item['id'] ) . '' . _post_states( get_post( $item['id'] ) ) . ' (#' . $item['id'] . ')</strong>';
		} else {
			$column_string .= '<a href="' . $edit_link . '" class="row-title">' . _draft_or_post_title( $item['id'] ) . ' (#' . $item['id'] . ')</a>' . _post_states( get_post( $item['id'] ) ) . '</strong>';
		}
		$column_string .= '<div class=\'row-actions\'>';

		$count = count( $item['row_actions'] );
		foreach ( $item['row_actions'] as $k => $action ) {
			$column_string .= '<span class="' . $action['action'] . '"><a href="' . $action['link'] . '" ' . $action['attrs'] . '>' . $action['text'] . '</a>';
			if ( $k < $count - 1 ) {
				$column_string .= ' | ';
			}
			$column_string .= '</span>';
		}

		return wpautop( $column_string );
	}

	/**
	 * Retrieve an array of possible bulk actions.
	 * @since  1.0.0
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array();

		return $actions;
	}

	/**
	 * Prepare an array of items to be listed.
	 * @since  1.0.0
	 * @return array Prepared items.
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$total_items = $this->data['found_posts'];

		$this->set_pagination_args( array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $this->per_page, //WE have to determine how many items to show on a page
		) );

		unset( $this->data['found_posts'] );

		$this->items = $this->data;
	}

	/**
	 * Retrieve an array of columns for the list table.
	 * @since  1.0.0
	 * @return array Key => Value pairs.
	 */
	public function get_columns() {
		$columns = array(
			'check-column' => '&nbsp;',
			'name'         => __( 'Title', 'finale-woocommerce-sales-countdown-timer-discount' ),
			'campaign'     => __( 'Campaign', 'finale-woocommerce-sales-countdown-timer-discount' ),
			'deals'        => __( 'Deal', 'finale-woocommerce-sales-countdown-timer-discount' ),
			'appearance'   => __( 'Appearance', 'finale-woocommerce-sales-countdown-timer-discount' ),
			'status'       => __( 'Status', 'finale-woocommerce-sales-countdown-timer-discount' ),
			'priority'     => __( 'Priority', 'finale-woocommerce-sales-countdown-timer-discount' ),
		);

		return $columns;
	}

	/**
	 * Retrieve an array of sortable columns.
	 * @since  1.0.0
	 * @return array
	 */
	public function get_sortable_columns() {
		//        return array("Running","Finished","Schedule","Deactivated");
		return array(
			'running'     => array( 'Running', true ),
			'finished'    => array( 'Finished', true ),
			'schedule'    => array( 'Schedule', true ),
			'deactivated' => array( 'Deactivated', true ),
		);
	}

	public function get_table_classes() {
		$get_default_classes = parent::get_table_classes();
		array_push( $get_default_classes, 'wcct-instance-table' );

		return $get_default_classes;
	}

	public function single_row( $item ) {
		$tr_class = 'wcct_trigger_active';
		if ( $item['trigger_status'] == WCCT_SHORT_SLUG . 'disabled' ) {
			$tr_class = 'wcct_trigger_deactive';
		}
		echo '<tr class="' . $tr_class . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

}
