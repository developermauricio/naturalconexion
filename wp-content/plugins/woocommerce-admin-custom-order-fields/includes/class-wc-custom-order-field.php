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
 * Custom Order Field class
 *
 * @since 1.0
 */
class WC_Custom_Order_Field {


	/** @var array the custom field raw data */
	private $data;

	/** @var string the custom field ID */
	private $id;

	/** @var mixed the custom field value */
	public $value;

	/** @var boolean have we run the field options filter already? */
	private $has_run_field_options_filter = false;


	/**
	 * Sets up the custom field.
	 *
	 * @since 1.0
	 *
	 * @param int $id the custom field ID
	 * @param array $data the custom field raw data
	 */
	public function __construct( $id, array $data ) {

		$this->id   = $id;
		$this->data = $data;
	}


	/**
	 * Magic method for getting custom field properties
	 *
	 * @since 1.0
	 * @param string $key the class member name
	 * @return mixed
	 */
	public function __get( $key ) {

		switch ( $key ) {

			case 'id':
				return $this->id;

			case 'label':
				/**
				 * Filter the custom field's label
				 *
				 * @since 1.5.0
				 * @param string $label The custom field's label
				 * @param WC_Custom_Order_Field $wc_custom_order_field Instance of this class
				 */
				return apply_filters( 'wc_admin_custom_order_fields_field_label', $this->data['label'], $this );

			case 'type':
				return $this->data['type'];

			case 'default':
				return isset( $this->data['default'] ) ? $this->data['default'] : null;

			case 'description':
				return isset( $this->data['description'] ) ? $this->data['description'] : null;

			case 'required':
				return $this->is_required();

			case 'visible':
				return $this->is_visible();

			case 'listable':
				return $this->is_listable();

			case 'sortable':
				return $this->is_sortable();

			case 'filterable':
				return $this->is_filterable();

			default:
				return null;
		}
	}


	/**
	 * Magic method for checking if custom field properties are set
	 *
	 * @since 1.0
	 * @param string $key the class member name
	 * @return bool
	 */
	public function __isset( $key ) {

		switch( $key ) {

			// field properties are always set
			case 'required':
			case 'visible':
			case 'listable':
			case 'sortable':
			case 'filterable':
				return true;

			case 'value':
				return isset( $this->value );

			default:
				return isset( $this->data[ $key ] );
		}
	}


	/**
	 * Get the field ID.
	 *
	 * @since 1.7.1
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}


	/**
	 * Get the field type.
	 *
	 * @since 1.7.1
	 * @return string
	 */
	public function get_type() {
		return $this->data['type'];
	}


	/**
	 * Gets order meta name for the field, which is the field ID prefixed with `_wc_acof_`
	 *
	 * @since 1.0
	 * @return string database option name for this field
	 */
	public function get_meta_key() {
		return '_wc_acof_' . $this->id;
	}


	/**
	 * Sets the value for this field
	 *
	 * @since 1.0
	 * @param mixed $value the value
	 */
	public function set_value( $value ) {

		$this->value = $value;

		// for multi items, remove any default selections, and select the actual values
		if ( $this->has_options() ) {

			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}

			// make sure the options have been populated
			$this->get_options();

			foreach ( $this->data['options'] as $key => $option ) {

				if ( in_array( $option['value'], $value, true ) ) {
					$this->data['options'][ $key ]['selected'] = true;
				} else {
					$this->data['options'][ $key ]['selected'] = false;
				}
			}
		}
	}


	/**
	 * Gets the field value, using the default if any and no value has been set
	 *
	 * @since 1.0
	 * @return mixed the field value
	 */
	public function get_value() {

		$value = $this->value;

		if ( ! isset( $this->value ) && $this->default ) {

			if ( 'date' === $this->type && 'now' === $this->default ) {
				$value = time();
			} else {
				$value = $this->default;
			}
		}

		return $value;
	}


	/**
	 * Gets the custom field value, formatted based on field type.
	 *
	 * This will not be the default if no value has been set yet.
	 *
	 * @since 1.0
	 *
	 * @return string formatted value
	 */
	public function get_value_formatted() {

		$value = $this->value;

		// note we use value directly to avoid returning a default that would be displayed to a user
		switch ( $this->type ) {

			case 'date':
				$value_formatted = $value ? date_i18n( wc_date_format(), $value ) : '';
			break;

			case 'text':
			case 'textarea':
				$value_formatted = stripslashes( $value );
			break;

			case 'select':
			case 'multiselect':
			case 'checkbox':
			case 'radio':

				$options = $this->get_options();

				$value = array();

				foreach ( $options as $option ) {

					if ( $option['selected'] ) {

						$value[] = $option['label'];
					}
				}

				$value_formatted = implode( ', ', $value );

			break;

			default:
				$value_formatted = $value;
			break;
		}

		/**
		 * Filter the custom field's formatted value
		 *
		 * @since 1.5.0
		 * @param string $label The custom field's formatted value
		 * @param WC_Custom_Order_Field $wc_custom_order_field Instance of this class
		 */
		return apply_filters( 'wc_admin_custom_order_fields_field_value_formatted', $value_formatted, $this );
	}


	/**
	 * Returns true if this is a multi-item field
	 * (i.e. select, multiselect, radio, checkbox)
	 *
	 * @since 1.0
	 * @return bool true if this is a multi-item field, false otherwise
	 */
	public function has_options() {
		return in_array( $this->type, array( 'select', 'multiselect', 'radio', 'checkbox' ), true );
	}


	/**
	 * Get the options for the select, multiselect, radio and checkbox types.
	 * If no value has been set, items are marked as selected according to any
	 * configured defaults.
	 *
	 * @since 1.0
	 * @return null|array of arrays containing 'default', 'selected', 'label', 'value' keys
	 */
	public function get_options() {

		if ( ! $this->has_options() ) {
			return null;
		}

		// configured options
		$options = isset( $this->data['options'] ) && $this->data['options'] ? $this->data['options'] : array();

		// allow other plugins to hook in and supply their own options, but only run this filter once to avoid duplicate intensive operations
		if ( ! $this->has_run_field_options_filter ) {
			$this->data['options'] = $options = apply_filters( 'wc_admin_custom_order_field_options', $options, $this );
			$this->has_run_field_options_filter = true;
		}

		// set default values if no value provided
		if ( ! isset( $this->value ) ) {

			foreach ( $options as $key => $option ) {

				if ( $option['default'] ) {
					$options[ $key ]['selected'] = true;
				} else {
					$options[ $key ]['selected'] = false;
				}
			}
		}

		// add an empty option for non-required select/multiselect
		if ( ( 'select' === $this->type || 'multiselect' === $this->type ) && ! $this->is_required() ) {
			array_unshift( $options, array( 'default' => false, 'label' => '', 'value' => '', 'selected' => false ) );
		}

		return $options;
	}


	/**
	 * Gets the default option from an option list (if available).
	 *
	 * @since 1.15.0
	 *
	 * @return int|string|null the default option value, or null if no default available
	 */
	public function get_default_option() {

		foreach ( (array) $this->get_options() as $option ) {

			if ( isset( $option['default'] ) && $option['default'] ) {

				return $option['value'];
			}
		}

		return null;
	}


	/**
	 * Gets the default options from an option list (if available).
	 *
	 * @since 1.15.0
	 *
	 * @return array the default option value
	 */
	public function get_default_options() {

		// filters the mapped default options
		return array_filter( array_map(
			static function( $option ) {

				return isset( $option['default'] ) && $option['default'] ? $option['value'] : null;
			},
			(array) $this->get_options()
		) );
	}


	/**
	 * Gets the default field value.
	 *
	 * @since 1.15.0
	 *
	 * @return int|string|array|null the default field value, null if not available
	 */
	public function get_default_value() {

		if ( 'now' === $this->default && 'date' === $this->get_type() ) {

			return time();

		} elseif ( in_array( $this->get_type(), [ 'radio', 'select' ] ) ) {

			return $this->get_default_option();

		} elseif ( in_array( $this->get_type(), [ 'checkbox', 'multiselect' ] ) ) {

			return $this->get_default_options();
		}

		return $this->default;
	}


	/**
	 * Returns true if this is a required field, false otherwise
	 *
	 * @since 1.0
	 * @return bool true if this field is required, false otherwise
	 */
	public function is_required() {

		return isset( $this->data['required'] ) && $this->data['required'];
	}


	/**
	 * Returns true if this field is visible to the customer (in order emails/my account > order views), false otherwise
	 *
	 * @since 1.0
	 * @return bool true if this field is required, false otherwise
	 */
	public function is_visible() {

		return isset( $this->data['visible'] ) && $this->data['visible'];
	}


	/**
	 * Returns true if this custom field should be displayed in the Order admin
	 * list
	 *
	 * @since 1.0
	 * @return bool true if the field should be displayed in the orders list
	 */
	public function is_listable() {

		return isset( $this->data['listable'] ) && $this->data['listable'];
	}


	/**
	 * Returns true if this listable custom field is also sortable
	 *
	 * @since 1.0
	 * @return bool true if the field should be sortable in the orders list
	 */
	public function is_sortable() {

		return $this->is_listable() && isset( $this->data['sortable'] ) && $this->data['sortable'];
	}


	/**
	 * Returns true if this listable custom field is also filterable in the
	 * Orders admin
	 *
	 * @since 1.0
	 * @return bool true if the field is both listable and filterable
	 */
	public function is_filterable() {

		return $this->is_listable() && isset( $this->data['filterable'] ) && $this->data['filterable'];
	}


	/**
	 * Returns true if the custom field is numeric
	 *
	 * @return bool true if the the field is numeric, false otherwise
	 */
	public function is_numeric() {

		return 'date' === $this->type || ( isset( $this->data['is_numeric'] ) && $this->data['is_numeric'] );
	}


}
