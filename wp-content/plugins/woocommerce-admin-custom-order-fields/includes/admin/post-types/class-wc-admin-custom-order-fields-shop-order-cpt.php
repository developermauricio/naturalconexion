<?php
/**
 * WooCommerce Admin Custom Order Fields
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Admin Custom Order Fields to newer
 * versions in the future. If you wish to customize WooCommerce Admin Custom Order Fields for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-admin-custom-order-fields/ for more information.
 *
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2020, SkyVerge, Inc. (info@skyverge.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_10_6 as Framework;

/**
 * Order CPT class
 *
 * Handles modifications to the shop order CPT on both View Orders list table and Edit Order screen
 *
 * @since 1.0
 */
class WC_Admin_Custom_Order_Fields_Shop_Order_CPT {


	/**
	 * Adds actions/filters for View Orders/Edit Order screens.
	 *
	 * @since 1.0
 	 */
	public function __construct() {

		// add custom order field meta box to edit order screen
		add_action( 'admin_init', array( $this, 'load_meta_box' ) );

		// add listable custom order field column titles to the orders list table
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'render_column_titles' ), 15 );

		// add listable custom order field column content to the orders list table
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_column_content' ), 5 );

		// add sortable custom order fields
		add_filter( 'manage_edit-shop_order_sortable_columns', array( $this, 'add_sortable_columns' ) );

		// process sorting
		add_filter( 'request', array( $this, 'add_orderby' ), 20 );

		// make custom fields filterable
		add_filter( 'request', array( $this, 'add_filterable_field' ) );

		// handle filtering
		add_action( 'restrict_manage_posts', array( $this, 'restrict_orders' ), 15 );

		// make custom fields searchable
		add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'add_search_fields' ) );
	}


	/**
	 * Load the meta-box class
	 *
	 * @since 1.0
	 */
	public function load_meta_box() {

		require( 'meta-boxes/class-wc-admin-custom-order-fields-meta-box.php' );

		$this->meta_box = new \WC_Admin_Custom_Order_Fields_Meta_Box();
	}


	/** Listable Columns ******************************************************/


	/**
	 * Add any listable columns
	 *
	 * @since 1.0
	 * @param array $columns associative array of column id to display name
	 * @return array of column id to display name
	 */
	public function render_column_titles( $columns ) {

		// get all columns up to and excluding the 'order_actions' column
		$new_columns = array();

		foreach ( $columns as $name => $value ) {

			if ( $name === 'order_actions' ) {
				prev( $columns );
				break;
			}

			$new_columns[ $name ] = $value;
		}

		// inject our columns
		foreach ( wc_admin_custom_order_fields()->get_order_fields() as $order_field ) {

			if ( $order_field->is_listable() ) {
				$new_columns[ $order_field->get_meta_key() ] = $order_field->label;
			}
		}

		// add the 'order_actions' column, and any others
		foreach ( $columns as $name => $value ) {
			$new_columns[ $name ] = $value;
		}

		return $new_columns;
	}


	/**
	 * Display the values for the listable columns
	 *
	 * @since 1.0
	 * @param string $column the column name
	 */
	public function render_column_content( $column ) {
		global $post;

		foreach ( wc_admin_custom_order_fields()->get_order_fields( $post->ID ) as $order_field ) {

			if ( $column === $order_field->get_meta_key() ) {

				echo $order_field->get_value_formatted();

				break;
			}

		}
	}


	/** Sortable Columns ******************************************************/


	/**
	 * Make order columns sortable
	 *
	 * @since 1.0
	 * @param array $columns associative array of column name to id
	 * @return array of column name to id
	 */
	public function add_sortable_columns( $columns ) {

		foreach ( wc_admin_custom_order_fields()->get_order_fields() as $order_field ) {

			if ( $order_field->is_sortable() ) {
				$columns[ $order_field->get_meta_key() ] = $order_field->get_meta_key();
			}
		}

		return $columns;
	}


	/**
	 * Adds any sortable custom order fields
	 *
	 * @since 1.0
	 * @param array $vars query variables
	 * @return array query variables
	 */
	public function add_orderby( $vars ) {
		global $typenow;

		if ( $typenow !== 'shop_order' ) {
			return $vars;
		}

		// Sorting
		if ( isset( $vars['orderby'] ) ) {

			// is the user sorting by one of our custom sortable fields?
			foreach ( wc_admin_custom_order_fields()->get_order_fields() as $order_field ) {

				if ( $order_field->is_sortable() && $vars['orderby'] === $order_field->get_meta_key() ) {

					// sorting over one of our custom fields
					$vars = array_merge( $vars, array(
						'meta_key' => $order_field->get_meta_key(),
						'orderby'  => $order_field->is_numeric() ? 'meta_value_num' : 'meta_value'
					) );

					return $vars;
				}
			}
		}

		return $vars;
	}


	/** Filterable Columns ******************************************************/


	/**
	 * Renders dropdowns for any filterable custom order fields.
	 *
	 * @internal
	 *
	 * @since 1.0
	 */
	public function restrict_orders() {
		global $typenow;

		if ( 'shop_order' !== $typenow ) :
			return;
		endif;

		foreach ( wc_admin_custom_order_fields()->get_order_fields() as $order_field ) :

			if ( $order_field->is_filterable() ) :

				if ( $order_field->type === 'date' ) :

					// filterable date field: provide a monthly dropdown
					$this->render_months_dropdown( $order_field->label, $order_field->get_meta_key() );

				elseif ( $order_field->has_options() ) :

					// filterable multi item field (select, multiselect, radio, checkbox), provide a dropdown ?>
					<select name="<?php echo esc_attr( $order_field->get_meta_key() ); ?>"
						id="<?php echo esc_attr( $order_field->get_meta_key() ); ?>"
						class="wc-enhanced-select"
						data-placeholder="<?php
							/* translators: Placeholders: %s - field label */
							printf( __( 'Show all %s', 'wc_admin_custom_order_fields' ), $order_field->label ); ?>"
						data-allow_clear="true"
						style="min-width:200px;">
							<option value=""></option>
							<?php foreach ( $order_field->get_options() as $option ) : ?>
								<?php if ( '' === $option['value'] && '' === $option['label'] ) { continue; } ?>
								<?php echo '<option value="' . $option['value'] . '" ' . ( isset( $_GET[ $order_field->get_meta_key() ] ) ? selected( $option['value'], $_GET[ $order_field->get_meta_key() ] ) : '' ) . '>' . __( $option['label'], 'wc_admin_custom_order_fields' ) . '</option>'; ?>
							<?php endforeach; ?>
					</select>
					<?php

				elseif ( $order_field->type === 'text' ) :

					$requested_value = Framework\SV_WC_Helper::get_requested_value( $order_field->get_meta_key() );

					if ( ! empty( $requested_value ) ) {
						$requested_value = stripslashes( stripslashes( $requested_value ) );
					}

					?>
					<select
						class="sv-wc-enhanced-search"
						name="<?php echo esc_attr( $order_field->get_meta_key() ); ?>"
						style="min-width:200px;"
						data-selected="<?php echo esc_attr( $requested_value ); ?>"
						data-placeholder="<?php echo esc_attr( sprintf(
							/* translators: Placeholder: %s - field label */
							__( 'Show all %s', 'wc_admin_custom_order_fields' ),
							$order_field->label
						) ); ?>"
						data-allow_clear="true"
						data-action="wc_admin_custom_order_fields_json_search_field"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'search-field' ) ); ?>"
						data-request_data="<?php echo esc_attr( json_encode( [
							'field_name' => $order_field->get_meta_key(),
							'default'    => __( 'Show all ', 'wc_admin_custom_order_fields' ) . $order_field->label
						] ) ); ?>">
						<?php if ( ! empty( $requested_value ) ) : ?>
							<option value="<?php echo esc_attr( wp_slash( $requested_value ) ); ?>" selected="selected"><?php echo esc_html( $requested_value ); ?></option>
						<?php endif; ?>
					</select>
					<?php

					Framework\SV_WC_Helper::render_select2_ajax();

				endif;

			endif;

		endforeach;
	}


	/**
	 * Render a date dropdown containing dates from the $field_name field,
	 * organized by month
	 *
	 * @since 1.0
	 * @param string $display_name the field name to display
	 * @param string $field_name the internal field name
	 */
	private function render_months_dropdown( $display_name, $field_name ) {
		global $wpdb, $wp_locale;

		$months = $wpdb->get_results( $wpdb->prepare( "
		SELECT YEAR( FROM_UNIXTIME( meta_value ) ) as year, MONTH( FROM_UNIXTIME( meta_value ) ) as month, CAST( meta_value AS UNSIGNED ) AS meta_value_num
		FROM " . $wpdb->postmeta . "
		WHERE meta_value <> '' AND meta_key = %s
		GROUP BY year, month
		ORDER BY meta_value_num DESC", $field_name ) );

		$month_count = count( $months );

		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
			return;
		}

		$m = isset( $_GET[ $field_name ] ) ? (int) $_GET[ $field_name ] : 0;

		?>
		<select
			id="<?php echo esc_attr( $field_name ); ?>"
			name="<?php echo esc_attr( $field_name ); ?>"
			class="wc-enhanced-select"
			data-placeholder="<?php
				/* translators: Placeholders: %s - field display name */
				printf( __( 'Show all %s', 'wc_admin_custom_order_fields' ), $display_name ); ?>"
			data-allow_clear="true"
			style="min-width:200px;">
			<option value=""></option>
			<?php foreach ( $months as $arc_row ) : ?>

				<?php

				if ( 0 == $arc_row->year ) {
					continue;
				}

				$month = zeroise( $arc_row->month, 2 );
				$year = $arc_row->year;

				printf( '<option %1$s value="%2$s">%3$s</option>',
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					/* translators: Placeholders: %1$s: month name, %2$d: 4-digit year */
					sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
				);

				?>

			<?php endforeach; ?>
		</select>
		<?php
	}


	/**
	 * Filters the orders by any filterable custom order fields.
	 *
	 * @internal
	 *
	 * @since 1.0
	 *
	 * @param array $vars query variables
	 * @return array query variables
	 */
	public function add_filterable_field( $vars ) {
		global $typenow;

		if ( 'shop_order' !== $typenow ) {
			return $vars;
		}

		$meta_queries = [ 'relation' => 'AND' ];

		foreach ( wc_admin_custom_order_fields()->get_order_fields() as $order_field ) {

			// if the field is filterable and selected by the user
			if ( $order_field->is_filterable() && isset( $_GET[ $order_field->get_meta_key() ] ) && $_GET[ $order_field->get_meta_key() ] ) {

				if ( $order_field->type === 'date' ) {

					// Note from Justin in the past:
					// A note on filtering by date:  I store date as a timestamp rather than
					//  a YYYY-MM date string, which is not ideal, but necessary to allow for
					//  proper sorting over the meta value.  The consequence of this is that
					//  we have to do the monthly filtering also based on the timestamp (unless
					//  I want to go deeper and dynamically modify the query with the 'posts_where'
					//  filter) which can yield incorrect results in certain circumstances.  For
					//  instance, if the server timezone is UTC and the database is EST, dates
					//  on the edge between months could end up being filtered and displayed
					//  within the 'wrong' month.  Not sure there's much I can do about that
					$date = $_GET[ $order_field->get_meta_key() ];

					// from the start to the end of the month
					$from_date = substr( $date, 0, 4 ) . '-' . substr( $date, 4, 2 ) . '-01';
					$to_date   = substr( $date, 0, 4 ) . '-' . substr( $date, 4, 2 ) . '-' . date( 't', strtotime( $from_date ) );

					$meta_queries[] = [
						'key'     => $order_field->get_meta_key(),
						'value'   => array( strtotime( $from_date ), strtotime( $to_date ) ),
						'type'    => 'NUMERIC',
						'compare' => 'BETWEEN'
					];

				} elseif ( $order_field->type === 'multiselect' || $order_field->type === 'checkbox' ) {

					// multi-value field types, the values are stored as a serialized array, so do a LIKE comparison.  not ideal, but the best we can do
					$meta_queries[] = [
						'key'     => $order_field->get_meta_key(),
						'value'   => $_GET[ $order_field->get_meta_key() ],
						'type'    => 'CHAR',
						'compare' => 'LIKE'
					];

				} else {

					if ( 'text' === $order_field->type ) {
						$value = stripslashes( $_GET[ $order_field->get_meta_key() ] );
					} else {
						$value = $_GET[ $order_field->get_meta_key() ];
					}

					// simple comparison operator
					$meta_queries[] = [
						'key'     => $order_field->get_meta_key(),
						'value'   => $value,
						'type'    => $order_field->is_numeric() ? 'NUMERIC' : 'CHAR',
						'compare' => '='
					];
				}
			}
		}

		// update the query vars with our meta filter queries, if needed
		if ( count( $meta_queries ) > 1 ) {

			$vars = array_merge(
				$vars,
				[ 'meta_query' => $meta_queries ]
			);
		}

		return $vars;
	}


	/** Searchable ******************************************************/


	/**
	 * Add our custom order fields to the set of search fields so that
	 * the admin search functionality is maintained
	 *
	 * @since 1.0
	 * @param array $search_fields array of post meta fields to search by
	 * @return array of post meta fields to search by
	 */
	public function add_search_fields( $search_fields ) {

		foreach ( wc_admin_custom_order_fields()->get_order_fields() as $order_field ) {

			if ( 'date' === $order_field->type  ) {
				array_push( $search_fields, $order_field->get_meta_key() . '_formatted' );
			} else {
				array_push( $search_fields, $order_field->get_meta_key() );
			}
		}

		return $search_fields;
	}


}
