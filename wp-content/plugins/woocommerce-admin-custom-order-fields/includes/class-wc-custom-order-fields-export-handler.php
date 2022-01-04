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
 * Custom Order Fields Export Handler class
 *
 * @since 1.2.0
 */
class WC_Admin_Custom_Order_Fields_Export_Handler {


	/**
	 * Setup class
	 *
	 * @since 1.2.0
	 */
	public function __construct() {

		// Customer / Order CSV Export column headers/data for CSV Export v4 and below
		if ( function_exists( 'wc_customer_order_csv_export' ) && version_compare( wc_customer_order_csv_export()->get_version(), '5.0.0', '<' ) ) {

			add_filter( 'wc_customer_order_csv_export_order_headers', [ $this, 'add_fields_to_csv_export_column_headers' ], 10, 2 );
			add_filter( 'wc_customer_order_csv_export_order_row',     [ $this, 'add_fields_to_csv_export_column_data' ], 10, 4 );

			// Customer / Order CSV Export custom format builder support, v4.0+
			add_filter( 'wc_customer_order_csv_export_format_column_data_options', [ $this, 'add_fields_to_export_orders_custom_mapping_options' ], 10, 2 );

		} else {

			add_filter( 'wc_customer_order_export_csv_order_headers', [ $this, 'add_fields_to_csv_export_column_headers' ], 10, 2 );
			add_filter( 'wc_customer_order_export_csv_order_row',     [ $this, 'add_fields_to_csv_export_column_data' ], 10, 4 );

			// Customer / Order CSV Export custom format builder support, v5.0+
			add_filter( 'wc_customer_order_export_csv_format_data_sources', [ $this, 'add_fields_to_export_orders_custom_mapping_options' ], 10, 2 );

			// Customer / Order XML Export admin custom fields
			add_filter( 'wc_customer_order_export_xml_order_data', [ $this, 'add_fields_to_xml_export_data' ], 10, 3 );

			// Customer / Order XML Export custom format builder support
			add_filter( 'wc_customer_order_export_xml_format_data_sources', [ $this, 'add_fields_to_export_orders_custom_mapping_options' ], 10, 2 );
		}

		if ( function_exists( 'wc_customer_order_xml_export_suite' ) ) {

			// Customer / Order XML Export admin custom fields
			add_filter( 'wc_customer_order_xml_export_suite_order_data', [ $this, 'add_fields_to_xml_export_data_legacy' ], 10, 2 );

			// Customer / Order XML Export custom format builder support
			add_filter( 'wc_customer_order_xml_export_suite_format_field_data_options', [ $this, 'add_fields_to_export_orders_custom_mapping_options' ], 10, 2 );
		}
	}


	/**
	 * Adds support for Customer/Order CSV Export by adding a column header for
	 * each registered admin order field
	 *
	 * @since 1.2.0
	 *
	 * @param array $headers existing array of header key/names for the CSV export
	 * @return array
	 */
	public function add_fields_to_csv_export_column_headers( $headers, $csv_generator ) {

		$export_format = version_compare( wc_customer_order_csv_export()->get_version(), '4.0.0', '<' ) ? $csv_generator->order_format : $csv_generator->export_format;

		// don't automatically add headers for custom formats
		if ( 'custom' === $export_format ) {
			return $headers;
		}

		foreach ( wc_admin_custom_order_fields()->get_order_fields() as $field_id => $field ) {
			$headers[ "admin_custom_order_field_{$field_id}" ] = 'admin_custom_order_field:' . str_replace( '-', '_', sanitize_title( $field->label ) ) . '_' . $field_id;
		}

		return $headers;
	}


	/**
	 * Adds support for Customer/Order CSV Export by adding data for admin order fields
	 *
	 * @since 1.2.0
	 *
	 * @param array $order_data generated order data matching the column keys in the header
	 * @param WC_Order $order order being exported
	 * @param \WC_Customer_Order_CSV_Export_Generator $csv_generator instance
	 * @return array
	*/
	public function add_fields_to_csv_export_column_data( $order_data, $order, $csv_generator ) {

		$field_data     = array();
		$new_order_data = array();

		foreach ( wc_admin_custom_order_fields()->get_order_fields( $order->get_id() ) as $field_id => $field ) {
			$field_data[ "admin_custom_order_field_{$field_id}" ] = $field->get_value_formatted();
		}

		// determine if the selected format is "one row per item"
		if ( version_compare( wc_customer_order_csv_export()->get_version(), '4.0.0', '<' ) ) {

			$one_row_per_item = ( 'default_one_row_per_item' === $csv_generator->order_format || 'legacy_one_row_per_item' === $csv_generator->order_format );

		// v4.0.0 - 4.0.2
		} elseif ( ! isset( $csv_generator->format_definition ) ) {

			// get the CSV Export format definition
			$format_definition = wc_customer_order_csv_export()->get_formats_instance()->get_format( $csv_generator->export_type, $csv_generator->export_format );

			$one_row_per_item = isset( $format_definition['row_type'] ) && 'item' === $format_definition['row_type'];

		// v4.0.3+
		} else {

			$one_row_per_item = 'item' === $csv_generator->format_definition['row_type'];
		}

		if ( $one_row_per_item ) {

			foreach ( $order_data as $data ) {
				$new_order_data[] = array_merge( $field_data, (array) $data );
			}

		} else {

			$new_order_data = array_merge( $field_data, $order_data );
		}

		return $new_order_data;
	}


	/**
	 * Adds support for Customer / Order XML Export by adding a dedicated <CustomFields> tag
	 *
	 * @internal
	 *
	 * @since 1.13.2
	 *
	 * @param array $order_data order data for the XML output
	 * @param \WC_Order $order order object
	 * @param \SkyVerge\WooCommerce\CSV_Export\XML_Export_Generator $generator export generator
	 * @return array - updated order data
	 */
	public function add_fields_to_xml_export_data( $order_data, $order, $generator = null ) {

		// only add custom order field data to custom formats if set in the format builder
		if ( $generator && 'custom' === $generator->export_format ) {

			// the data here can use a renamed version of the ACOF data, so we need to get format definition first to find out the new name
			$format_definition = $generator->format_definition ?: [];
			$custom_fields_key = isset( $format_definition['fields']['AdminCustomOrderFields'] ) ? $format_definition['fields']['AdminCustomOrderFields'] : null;

			if ( $custom_fields_key && isset( $order_data[ $custom_fields_key ] ) ) {
				$order_data[ $custom_fields_key ] = $this->get_fields_xml_required_format( $order );
			}

		} else {

			$order_data['CustomFields'] = $this->get_fields_xml_required_format( $order );
		}

		return $order_data;
	}


	/**
	 * Adds support for Customer / Order XML Export by adding a dedicated <CustomFields> tag.
	 *
	 * TODO: remove once we drop compatibility for XML Export {CW 2019-12-11}
	 *
	 * @since 1.7.0
	 *
	 * @param array $order_data order data for the XML output
	 * @param \WC_Order $order order object
	 * @return array - updated order data
	 */
	public function add_fields_to_xml_export_data_legacy( $order_data, $order ) {

		// only add custom order field data to custom formats if set in the format builder with v2.0+
		if ( 'custom' === get_option( 'wc_customer_order_xml_export_suite_orders_format', 'default' ) ) {

			// the data here can use a renamed version of the ACOF data, so we need to get format definition first to find out the new name
			$format_definition = wc_customer_order_xml_export_suite()->get_formats_instance()->get_format( 'orders', 'custom' );
			$custom_fields_key = isset( $format_definition['fields']['AdminCustomOrderFields'] ) ? $format_definition['fields']['AdminCustomOrderFields'] : null;

			if ( $custom_fields_key && isset( $order_data[ $custom_fields_key ] ) ) {
				$order_data[ $custom_fields_key ] = $this->get_fields_xml_required_format( $order );
			}

		} else {

			$order_data['CustomFields'] = $this->get_fields_xml_required_format( $order );
		}

		return $order_data;
	}


	/**
	 * Creates array of fields in format required for xml_to_array()
	 *
	 * Filter in method allows modification of individual fields array format
	 *
	 * @since 1.7.0
	 *
	 * @param \WC_Order $order order object
	 * @return array|null - fields in array format required by array_to_xml() or null if no fields
	 */
	protected function get_fields_xml_required_format( $order ) {

		$fields       = array();
		$order_fields = wc_admin_custom_order_fields()->get_order_fields( $order->get_id() );

		foreach( $order_fields as $id => $field ) {

			$field_data = array();

			$field_data['ID']    = $id;
			$field_data['Name']  = $field->label;
			$field_data['Value'] = $field->get_value_formatted();

			$fields['CustomField'][] = apply_filters( 'wc_admin_custom_order_fields_xml_field_data', $field_data, $order, $field );
		}

		return ! empty( $fields ) ? $fields : null;
	}


	/**
	 * Filters the custom format building options to allow adding Custom Order Fields.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param string[] $options the custom format building options
	 * @param string $export_type the export type, 'customers' or 'orders'
	 * @return string[] updated custom format options
	 */
    public function add_fields_to_export_orders_custom_mapping_options( $options, $export_type ) {

		if ( 'orders' === $export_type ) {

			$custom_fields = wc_admin_custom_order_fields()->get_order_fields();

			if ( ! empty( $custom_fields ) ) {

				$export_options      = current_filter();
				$custom_option_added = false;

				foreach ( $custom_fields as $field_id => $field ) {

					if ( 'wc_customer_order_csv_export_format_column_data_options' === $export_options || 'wc_customer_order_export_csv_format_data_sources' === $export_options ) {

						$options[] = "admin_custom_order_field_{$field_id}";

					} elseif ( ( 'wc_customer_order_xml_export_suite_format_field_data_options' === $export_options || 'wc_customer_order_export_xml_format_data_sources' === $export_options ) && ! $custom_option_added ) {

						$options[]           = 'AdminCustomOrderFields';
						$custom_option_added = true;
					}
				}
			}
		}

		return $options;
	}


}
