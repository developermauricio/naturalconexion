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
 * @copyright   Copyright (c) 2012-2021, SkyVerge, Inc. (info@skyverge.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_10_6 as Framework;

/**
 * WooCommerce Admin Custom Order Fields main class.
 *
 * @since 1.0
 */
class WC_Admin_Custom_Order_Fields extends Framework\SV_WC_Plugin {


	/** plugin version number */
	const VERSION = '1.15.1';

	/** @var WC_Admin_Custom_Order_Fields single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'admin_custom_order_fields';

	/** @var \WC_Admin_Custom_Order_Fields_Admin instance */
	protected $admin;

	/** @var \WC_Admin_Custom_Order_Fields_Export_Handler instance */
	protected $export_handler;


	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain' => 'woocommerce-admin-custom-order-fields',
			)
		);

		// display any publicly-visible custom order data in the frontend
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_order_details_after_order_table' ) );

		// display any publicly-visible custom order data in emails
		add_action( 'woocommerce_email_after_order_table', array( $this, 'add_order_details_after_order_table_emails' ), 20, 3 );

		// custom ajax handler for AJAX search
		add_action( 'wp_ajax_wc_admin_custom_order_fields_json_search_field', [ $this, 'add_json_search_field' ] );

		// save default field values when order is created
		add_action( 'wp_insert_post', array( $this, 'save_default_field_values' ), 10, 2 );
	}


	/**
	 * Initializes the plugin.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 */
	public function init_plugin() {

		// include required files
		$this->includes();

		$this->add_milestone_hooks();
	}


	/**
	 * Loads and initializes the plugin lifecycle handler.
	 *
	 * @since 1.11.0
	 */
	protected function init_lifecycle_handler() {

		require_once( $this->get_plugin_path() . '/includes/class-wc-custom-order-fields-lifecycle.php' );

		$this->lifecycle_handler = new SkyVerge\WooCommerce\Admin_Custom_Order_Fields\Lifecycle( $this );
	}


	/**
	 * Includes required files.
	 *
	 * @since 1.0
	 */
	private function includes() {

		require_once( $this->get_plugin_path() . '/includes/class-wc-custom-order-field.php' );

		$this->export_handler = $this->load_class( '/includes/class-wc-custom-order-fields-export-handler.php', 'WC_Admin_Custom_Order_Fields_Export_Handler' );

		if ( is_admin() && ! is_ajax() ) {
			$this->admin_includes();
		}
	}


	/**
	 * Includes required admin files.
	 *
	 * @since 1.0
	 */
	private function admin_includes() {

		// load order list table/edit order customizations
		$this->load_class( '/includes/admin/class-wc-admin-custom-order-fields-admin.php', 'WC_Admin_Custom_Order_Fields_Admin' );
	}


	/** Frontend methods ******************************************************/


	/**
	 * Displays any publicly viewable order fields on the frontend View Order page.
	 *
	 * @since 1.0
	 *
	 * @param \WC_Order $order the order object
	 */
	public function add_order_details_after_order_table( $order ) {

		$order_fields = $this->get_order_fields( $order->get_id(), true );

		if ( ! empty( $order_fields ) ) {

			// load the template
			wc_get_template(
				'order/custom-order-fields.php',
				[
					'order'        => $order,
					'order_fields' => $order_fields,
				],
				'',
				wc_admin_custom_order_fields()->get_plugin_path() . '/templates/'
			);
		}
	}


	/**
	 * Displays any publicly viewable order fields in order emails below the order table.
	 *
	 * @since 1.5.0
	 *
	 * @param \WC_Order $order the order object
	 * @param mixed $_ unused
	 * @param bool $plain_text
	 */
	public function add_order_details_after_order_table_emails( $order, $_, $plain_text ) {

		$order_fields = $this->get_order_fields( $order->get_id(), true );

		if ( ! empty( $order_fields ) ) {

			// load the template
			wc_get_template(
				$plain_text ? 'emails/plain/custom-order-fields.php' : 'emails/custom-order-fields.php',
				[
					'order'        => $order,
					'order_fields' => $order_fields,
				],
				'',
				wc_admin_custom_order_fields()->get_plugin_path() . '/templates/'
			);
		}
	}


	/** Admin methods ******************************************************/


	/**
	 * Adds milestone hooks.
	 *
	 * @since 1.11.0
	 */
	protected function add_milestone_hooks() {

		// first field(s) saved
		add_action( 'wc_admin_custom_order_fields_saved_fields', function( $posted_data, $fields ) {

			if ( ! empty( $fields ) ) {

				if ( count( $fields ) > 1 ) {
					$message = __( 'You created your first fields!', 'woocommerce-admin-custom-order-fields' );
				} else {
					$message = __( 'You created your first field!', 'woocommerce-admin-custom-order-fields' );
				}

				wc_admin_custom_order_fields()->get_lifecycle_handler()->trigger_milestone( 'saved-fields', lcfirst( $message ) );
			}

		}, 10, 2 );

		// first field(s) set to an order
		add_action( 'wc_admin_custom_order_fields_set_order_fields', function( $order_id, $fields ) {

			if ( count( $fields ) > 1 ) {
				$message = __( 'You have set custom fields to your first order!', 'woocommerce-admin-custom-order-fields' );
			} else {
				$message = __( 'You have set a custom field to your first order!', 'woocommerce-admin-custom-order-fields' );
			}

			wc_admin_custom_order_fields()->get_lifecycle_handler()->trigger_milestone( 'set-fields', lcfirst( $message ) );

		}, 10, 2 );
	}


	/**
	 * AJAX search handler for enhanced select fields.
	 *
	 * Searches for custom order admin fields and returns the results.
	 *
	 * @since 1.0
	 */
	public function add_json_search_field() {
		global $wpdb;

		check_ajax_referer( 'search-field', 'security' );

		// the search term
		$term = isset( $_GET['term'] ) ? urldecode( stripslashes( strip_tags( $_GET['term'] ) ) ) : '';

		// the field to search
		$field_name = isset( $_GET['request_data']['field_name'] ) ? urldecode( stripslashes( strip_tags( $_GET['request_data']['field_name'] ) ) ) : '';

		if ( empty( $term ) || empty( $field_name ) ) {
			die;
		}

		$found_values = [];
		$results      = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM " . $wpdb->postmeta . " WHERE meta_key = %s and meta_value LIKE %s", $field_name, '%' . $term . '%' ) );

		if ( $results ) {
			foreach ( $results as $result ) {
				$found_values[ $result->meta_value ] = stripslashes( $result->meta_value );
			}
		}

		echo json_encode( $found_values );
		exit;
	}


	/**
	 * Renders a notice for the user to read the docs before adding custom fields.
	 *
	 * @internal
	 *
	 * @since 1.1.4
	 */
	public function add_admin_notices() {

		// show any dependency notices
		parent::add_admin_notices();

		// add notice for selecting export format
		if ( $this->is_plugin_settings() ) {

			$this->get_admin_notice_handler()->add_admin_notice(
				/* translators: Placeholders: %1$s - opening <a> link tag, %2$s - closing </a> link tag */
				sprintf( __( 'Thanks for installing Admin Custom Order Fields! Before you get started, please %1$sread the documentation%2$s', 'woocommerce-admin-custom-order-fields' ),
					'<a href="' . $this->get_documentation_url() . '">',
					'</a>'
				),
				'read-the-docs',
				array(
					'always_show_on_settings' => false,
					'notice_class'            => 'updated',
				)
			);
		}
	}


	/**
	 * Saves the default field values when an order is created.
	 *
	 * @since 1.3.3
	 *
	 * @param int $post_id new order ID
	 * @param \WP_Post $post the post object
	 */
	public function save_default_field_values( $post_id, $post ) {

		if ( 'shop_order' === $post->post_type ) {

			$order_fields = $this->get_order_fields( $post_id );

			if ( ! empty( $order_fields ) ) {

				foreach ( $order_fields as $order_field ) {

					if ( $default = $order_field->get_default_value() ) {

						// force unique, because oddly this can be invoked when changing the status of an existing order
						add_post_meta( $post_id, $order_field->get_meta_key(), $default, true );
					}
				}

				/**
				 * Fires upon setting custom order fields to an order.
				 *
				 * @since 1.11.0
				 *
				 * @param int $post_id the order ID
				 * @param \WC_Custom_Order_Field[] $order_fields array of order fields
				 */
				do_action( 'wc_admin_custom_order_fields_set_order_fields', $post_id, $order_fields );
			}
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Returns the main Admin Custom Order Fields Instance.
	 *
	 * Ensures only one instance is/can be loaded.
	 * @see wc_admin_custom_order_fields()
	 *
	 * @since 1.3.0
	 *
	 * @return \WC_Admin_Custom_Order_Fields
	 */
	public static function instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Get the Admin instance
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Admin_Custom_Order_Fields_Admin
	 */
	public function get_admin_instance() {

		return $this->admin;
	}


	/**
	 * Returns the Export Handler instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Admin_Custom_Order_Fields_Export_Handler
	 */
	public function get_export_handler_instance() {

		return $this->export_handler;
	}


	/**
	 * Returns any configured order fields.
	 *
	 * @since 1.0
	 *
	 * @param int $order_id optional order identifier, if provided any set values are loaded
	 * @param bool $return_all optional: if all order fields should be returned or only those with set for the provided order
	 * @return \WC_Custom_Order_Field[]
	 */
	public function get_order_fields( $order_id = null, $return_all = true ) {

		$order_fields = array();

		// get the order object if we can
		$order = $order_id ? wc_get_order( $order_id ) : null;

		$custom_order_fields = get_option( 'wc_admin_custom_order_fields' );

		if ( ! is_array( $custom_order_fields ) ) {
			$custom_order_fields = array();
		}

		foreach ( $custom_order_fields as $field_id => $field ) {

			$order_field = new \WC_Custom_Order_Field( $field_id, $field );
			$has_value   = false;

			// if getting the fields for an order, does the order have a value set?
			if ( $order instanceof \WC_Order ) {

				$set_value = false;
				$value     = '';

				if ( metadata_exists( 'post', $order_id, $order_field->get_meta_key() ) ) {

					$set_value = true;
					$value     = $order->get_meta( $order_field->get_meta_key() );
				}

				if ( $set_value ) {

					$order_field->set_value( $value );
					$has_value = true;
				}
			}

			if ( $return_all || $has_value ) {
				$order_fields[ $field_id ] = $order_field;
			}
		}

		return $order_fields;
	}


	/**
	 * Returns the plugin name, localized.
	 *
	 * @since 1.1
	 *
	 * @return string the plugin name
	 */
	public function get_plugin_name() {

		return __( 'WooCommerce Admin Custom Order Fields', 'woocommerce-admin-custom-order-fields' );
	}


	/**
	 * Returns the full path and filename of the main plugin class.
	 *
	 * @since 1.1
	 *
	 * @return string
	 */
	protected function get_file() {

		return __FILE__;
	}


	/**
	 * Returns the URL to the settings page.
	 *
	 * @since 1.1
	 *
	 * @param string|null $_ unused
	 * @return string URL to the settings page
	 */
	public function get_settings_url( $_ = null ) {

		return admin_url( 'admin.php?page=wc_admin_custom_order_fields' );
	}


	/**
	 * Gets the plugin documentation url, used for the 'Docs' plugin action.
	 *
	 * @since 1.3.4
	 *
	 * @return string
	 */
	public function get_documentation_url() {

		return 'https://docs.woocommerce.com/document/woocommerce-admin-custom-order-fields/';
	}


	/**
	 * Gets the support URL, used for the 'Support' plugin action link.
	 *
	 * @since 1.3.4
	 *
	 * @return string
	 */
	public function get_support_url() {

		return 'https://woocommerce.com/my-account/marketplace-ticket-form/';
	}


	/**
	 * Gets the plugin sales page URL.
	 *
	 * @since 1.11.3
	 *
	 * @return string
	 */
	public function get_sales_page_url() {

		return 'https://woocommerce.com/products/admin-custom-order-fields/';
	}


	/**
	 * Returns true if on the gateway settings page.
	 *
	 * @since 1.1
	 *
	 * @return bool
	 */
	public function is_plugin_settings() {

		return isset( $_GET['page'] ) && 'wc_admin_custom_order_fields' === $_GET['page'];
	}


}


/**
 * Returns the One True Instance of Admin Custom Order Fields.
 *
 * @since 1.3.0
 *
 * @return \WC_Admin_Custom_Order_Fields
 */
function wc_admin_custom_order_fields() {

	return \WC_Admin_Custom_Order_Fields::instance();
}


// fire it up!
wc_admin_custom_order_fields();
