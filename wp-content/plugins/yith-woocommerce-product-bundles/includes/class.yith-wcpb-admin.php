<?php
/**
 * Admin class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles
 */

defined( 'YITH_WCPB' ) || exit;

if ( ! class_exists( 'YITH_WCPB_Admin' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 */
	class YITH_WCPB_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCPB_Admin
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public $version = YITH_WCPB_VERSION;

		/**
		 * Panel Object
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $panel;

		/**
		 * Premium version landing link
		 *
		 * @var string
		 */
		protected $premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-product-bundles';

		/**
		 * The panel page
		 *
		 * @var string
		 */
		protected $panel_page = 'yith_wcpb_panel';

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCPB_Admin|YITH_WCPB_Admin_Premium
		 */
		public static function get_instance() {
			/**
			 * The class.
			 *
			 * @var YITH_WCPB_Admin|YITH_WCPB_Admin_Premium $self
			 */
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

			return ! is_null( $self::$instance ) ? $self::$instance : $self::$instance = new $self();
		}

		/**
		 * Constructor
		 */
		protected function __construct() {

			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCPB_DIR . '/' . basename( YITH_WCPB_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 3 );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 20 );

			add_filter( 'woocommerce_product_data_tabs', array( $this, 'woocommerce_product_data_tabs' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'woocommerce_product_data_panels' ) );

			add_action( 'wp_ajax_yith_wcpb_select_product_box', array( $this, 'select_product_box' ) );
			add_action( 'wp_ajax_yith_wcpb_select_product_box_filtered', array( $this, 'select_product_box_filtered' ) );
			add_action( 'wp_ajax_yith_wcpb_add_product_in_bundle', array( $this, 'add_product_in_bundle' ) );

			add_action( 'woocommerce_admin_process_product_object', array( $this, 'woocommerce_process_product_meta' ) );

			add_action( 'yith_wcpb_admin_bundled_item_options', array( $this, 'print_bundled_item_options' ), 10, 2 );

			add_filter( 'woocommerce_admin_html_order_item_class', array( $this, 'woocommerce_admin_html_order_item_class' ), 10, 2 );
			add_filter( 'woocommerce_admin_order_item_class', array( $this, 'woocommerce_admin_html_order_item_class' ), 10, 2 );
			add_filter( 'woocommerce_admin_order_item_count', array( $this, 'woocommerce_admin_order_item_count' ), 10, 2 );
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'woocommerce_hidden_order_itemmeta' ) );
			add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'woocommerce_order_item_display_meta_key' ) );

			add_action( 'wp_ajax_woocommerce_add_order_item', array( $this, 'prevent_adding_bundle_products_in_orders' ), 5 );

			add_action( 'yith_wcpb_premium_tab', array( $this, 'show_premium_tab' ) );
			add_action( 'yith_wcpb_how_to_tab', array( $this, 'show_how_to_tab' ) );
		}

		/**
		 * Hide bundled_by meta in admin order
		 *
		 * @param array $hidden Hidden columns.
		 *
		 * @return array
		 */
		public function woocommerce_hidden_order_itemmeta( $hidden ) {
			return array_merge( $hidden, array( '_bundled_by', '_cartstamp' ) );
		}

		/**
		 * Filter display meta key
		 *
		 * @param string $display_key The key to be shown.
		 *
		 * @return string
		 */
		public function woocommerce_order_item_display_meta_key( $display_key ) {
			if ( '_yith_wcpb_title' === $display_key ) {
				$display_key = __( 'Custom name', 'yith-woocommerce-product-bundles' );
			}

			return $display_key;
		}

		/**
		 * Add CSS class in admin order bundled items
		 *
		 * @param string $class The class.
		 * @param array  $item  The item.
		 *
		 * @return string
		 */
		public function woocommerce_admin_html_order_item_class( $class, $item ) {
			if ( isset( $item['bundled_by'] ) ) {
				return $class . ' yith-wcpb-admin-bundled-item';
			}

			return $class;
		}

		/**
		 * Filter item count in admin orders page
		 *
		 * @param int      $count The count.
		 * @param WC_Order $order The order.
		 *
		 * @return int|string
		 */
		public function woocommerce_admin_order_item_count( $count, $order ) {
			$counter = 0;
			foreach ( $order->get_items() as $item ) {
				if ( isset( $item['bundled_by'] ) ) {
					$counter += $item['qty'];
				}
			}
			if ( $counter > 0 ) {
				$non_bundled_count = $count - $counter;

				// translators: 1: number of items; 2: number of elements included in the bundle.
				return sprintf( _n( '%1$s item [%2$s bundled elements]', '%1$s items [%2$s bundled elements]', $non_bundled_count, 'yith-woocommerce-product-bundles' ), $non_bundled_count, $counter );
			}

			return $count;
		}

		/**
		 * Add Product Bundle type in product type selector [in product wc-metabox]
		 *
		 * @param array $types Product types.
		 *
		 * @return array
		 * @deprecated 1.4.11 | use YITH_WCPB:product_type_selector instead.
		 */
		public function product_type_selector( $types ) {
			_deprecated_function( 'YITH_WCPB_Admin::product_type_selector', '1.4.11', 'YITH_WCPB:product_type_selector' );

			return yith_wcpb()->product_type_selector( $types );
		}

		/**
		 * Print bundled item options.
		 *
		 * @param YITH_WC_Bundled_Item $bundled_item The item.
		 * @param int                  $metabox_id   The metabox ID.
		 *
		 * @since 1.4.0
		 */
		public function print_bundled_item_options( $bundled_item, $metabox_id ) {
			yith_wcpb_get_view( '/admin/bundled-item-options.php', compact( 'bundled_item', 'metabox_id' ) );
		}

		/**
		 * Render Select Product Box.
		 */
		public function select_product_box() {
			yith_wcpb_get_view( '/admin/select-product-box.php' );
			die();
		}

		/**
		 * Render Select Product Box Filtered.
		 */
		public function select_product_box_filtered() {
			yith_wcpb_get_view( '/admin/select-product-box-products.php' );
			die();
		}

		/**
		 * Ajax Called in bundle_options_metabox.js
		 * return the empty form for the item
		 */
		public function add_product_in_bundle() {
			$response = array();
			if ( check_ajax_referer( 'yith-wcpb-add-product-to-bundle', 'security', false ) && isset( $_POST['id'], $_POST['bundle_id'], $_POST['product_id'] ) ) {
				$metabox_id = intval( $_POST['id'] );
				$bundle_id  = absint( $_POST['bundle_id'] );
				$product_id = absint( $_POST['product_id'] );
				$product    = wc_get_product( $product_id );

				if ( $product instanceof WC_Product && ! $product->is_type( 'simple' ) ) {
					$response['error'] = esc_html__( 'You can add only simple products with the FREE version of YITH WooCommerce Product Bundles', 'yith-woocommerce-product-bundles' );
				} else {
					$bundle_product = wc_get_product( $bundle_id );
					$bundled_item   = new YITH_WC_Bundled_Item( $bundle_product, $metabox_id, compact( 'product_id' ) );
					ob_start();
					yith_wcpb_get_view( '/admin/bundled-item.php', compact( 'metabox_id', 'bundled_item' ) );
					$response['html'] = ob_get_clean();
				}
			} else {
				$response['error'] = esc_html__( 'Something went wrong. Try again!', 'yith-woocommerce-product-bundles' );
			}
			wp_send_json( $response );
		}

		/**
		 * Add Bundle Options Tab [in product wc-metabox].
		 *
		 * @param array $product_tabs Product tabs.
		 *
		 * @return array
		 */
		public function woocommerce_product_data_tabs( $product_tabs ) {
			$product_tabs['yith_bundle_options'] = array(
				'label'  => __( 'Bundle Options', 'yith-woocommerce-product-bundles' ),
				'target' => 'yith_bundle_product_data',
				'class'  => array( 'show_if_yith_bundle' ),
			);

			$product_tabs['inventory']['class'] = array_merge( $product_tabs['inventory']['class'], array( 'show_if_yith_bundle' ) );

			return $product_tabs;
		}

		/**
		 * Add panel for Bundle Options Tab [in product wc-metabox]
		 */
		public function woocommerce_product_data_panels() {
			/**
			 * The product.
			 *
			 * @var WC_Product $product_object
			 */
			global $product_object;

			$bundle_product = $product_object->is_type( 'yith_bundle' ) ? $product_object : false;

			yith_wcpb_get_view( '/admin/bundle-options-tab.php', compact( 'bundle_product', 'product_object' ) );
		}

		/**
		 * Set the product meta before saving the product
		 *
		 * @param WC_Product_YITH_Bundle $product The product.
		 */
		public function woocommerce_process_product_meta( $product ) {
			if ( $product->is_type( 'yith_bundle' ) ) {

				// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$bundle_data         = ! empty( $_POST['_yith_wcpb_bundle_data'] ) && is_array( $_POST['_yith_wcpb_bundle_data'] ) ? wp_unslash( $_POST['_yith_wcpb_bundle_data'] ) : array();
				$indexed_bundle_data = array();
				if ( $bundle_data ) {
					$loop = 1;
					foreach ( $bundle_data as $single_bundle_data ) {

						foreach ( $single_bundle_data as $key => $value ) {
							if ( in_array( $key, array( 'bp_title', 'bp_description' ), true ) ) {
								$single_bundle_data[ $key ] = wp_kses_post( $single_bundle_data[ $key ] );
							} else {
								$single_bundle_data[ $key ] = wc_clean( $single_bundle_data[ $key ] );
							}
						}

						$indexed_bundle_data[ $loop ] = $single_bundle_data;
						$loop ++;
					}
				}

				$product->update_meta_data( '_yith_wcpb_bundle_data', $indexed_bundle_data );
				$product->update_meta_data( '_yith_bundle_product_version', yith_wcpb()->get_bundle_product_version() );
			}
		}

		/**
		 * Action Links
		 * add the action links to plugin admin page
		 *
		 * @param array $links The links.
		 *
		 * @return   array
		 * @use      plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			return yith_add_action_links( $links, $this->panel_page, defined( 'YITH_WCPB_PREMIUM' ), YITH_WCPB_SLUG );
		}

		/**
		 * Adds row meta.
		 *
		 * @param array    $row_meta_args Row meta arguments.
		 * @param string[] $plugin_meta   An array of the plugin's metadata,
		 *                                including the version, author,
		 *                                author URI, and plugin URI.
		 * @param string   $plugin_file   Path to the plugin file relative to the plugins directory.
		 *
		 * @return array
		 */
		public function plugin_row_meta( $row_meta_args, $plugin_meta, $plugin_file ) {
			$init = defined( 'YITH_WCPB_FREE_INIT' ) ? YITH_WCPB_FREE_INIT : YITH_WCPB_INIT;

			if ( $init === $plugin_file ) {
				$row_meta_args['slug']       = YITH_WCPB_SLUG;
				$row_meta_args['is_premium'] = defined( 'YITH_WCPB_PREMIUM' );
			}

			return $row_meta_args;
		}

		/**
		 * Retrieve Panel tabs.
		 *
		 * @return array
		 * @since 1.8.0
		 */
		protected function get_panel_tabs() {
			$tabs = array(
				'how-to'  => __( 'How to', 'yith-woocommerce-product-bundles' ),
				'premium' => __( 'Premium Version', 'yith-woocommerce-product-bundles' ),
			);

			return apply_filters( 'yith_wcpb_settings_admin_tabs', $tabs );
		}

		/**
		 * Retrieve Panel arguments.
		 *
		 * @return array
		 * @since 1.8.0
		 */
		protected function get_panel_args() {
			return array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'class'            => yith_set_wrapper_class(),
				'page_title'       => 'YITH WooCommerce Product Bundles',
				'menu_title'       => 'Product Bundles',
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->panel_page,
				'admin-tabs'       => $this->get_panel_tabs(),
				'options-path'     => YITH_WCPB_DIR . '/plugin-options',
				'plugin_slug'      => YITH_WCPB_SLUG,
				'is_free'          => true,
			);
		}

		/**
		 * Add a panel under YITH Plugins tab
		 */
		public function register_panel() {

			if ( ! empty( $this->panel ) ) {
				return;
			}

			$args = $this->get_panel_args();

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Don't allow adding bundle to orders through "Add products" box in orders.
		 *
		 * @since 1.2.21
		 */
		public function prevent_adding_bundle_products_in_orders() {
			if ( isset( $_POST['data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$items_to_add = array_filter( wp_unslash( (array) $_POST['data'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$bundle_titles = array();
				foreach ( $items_to_add as $item ) {
					if ( ! isset( $item['id'], $item['qty'] ) || empty( $item['id'] ) ) {
						continue;
					}
					$product_id = absint( $item['id'] );
					$product    = wc_get_product( $product_id );
					if ( $product && $product->is_type( 'yith_bundle' ) ) {
						$bundle_titles[] = $product->get_formatted_name();
					}
				}

				if ( $bundle_titles ) {
					// translators: %s is a comma-separated list of bundle products.
					wp_send_json_error( array( 'error' => sprintf( __( 'You are trying to add the following Bundle products to the order: %s. You cannot add Bundle products to orders through this box since this type of products needs to follow the normal WooCommerce "Add-to-cart > Cart > Checkout > Order" process.', 'yith-woocommerce-product-bundles' ), implode( ', ', $bundle_titles ) ) ) );
				}
			}
		}

		/**
		 * Enqueue scripts
		 */
		public function admin_enqueue_scripts() {
			$screen     = get_current_screen();
			$metabox_js = defined( 'YITH_WCPB_PREMIUM' ) ? 'bundle_options_metabox_premium.js' : 'bundle_options_metabox.js';

			wp_enqueue_style( 'yith-wcpb-admin-styles', YITH_WCPB_ASSETS_URL . '/css/admin.css', array(), YITH_WCPB_VERSION );
			wp_register_style( 'yith-wcpb-popup', YITH_WCPB_ASSETS_URL . '/css/yith-wcpb-popup.css', array(), YITH_WCPB_VERSION );

			wp_register_script( 'yith-wcpb-popup', yit_load_js_file( YITH_WCPB_ASSETS_URL . '/js/yith-wcpb-popup.js' ), array( 'jquery' ), YITH_WCPB_VERSION, true );
			wp_register_script( 'yith_wcpb_bundle_options_metabox', yit_load_js_file( YITH_WCPB_ASSETS_URL . '/js/' . $metabox_js ), array( 'jquery', 'jquery-ui-sortable', 'yith-wcpb-popup' ), YITH_WCPB_VERSION, true );

			if ( 'product' === $screen->id ) {
				wp_enqueue_style( 'yith-wcpb-popup' );
				wp_enqueue_style( 'yith-plugin-fw-fields' );

				wp_enqueue_script( 'yith-plugin-fw-fields' );
				wp_enqueue_script( 'yith_wcpb_bundle_options_metabox' );

				// TODO: to remove. It was kept for backward compatibility.
				wp_localize_script(
					'yith_wcpb_bundle_options_metabox',
					'ajax_object',
					array(
						'free_not_simple'     => __( 'You can add only simple products with the FREE version of YITH WooCommerce Product Bundles', 'yith-woocommerce-product-bundles' ),
						'yith_bundle_product' => __( 'You cannot add a bundle product', 'yith-woocommerce-product-bundles' ),
						'minimum_characters'  => apply_filters( 'yith_wcpb_minimum_characters_ajax_search', 3 ),
					)
				);

				wp_localize_script(
					'yith_wcpb_bundle_options_metabox',
					'yith_bundle_opts',
					array(
						'i18n'               => array(
							// translators: %s is the number of items added.
							'addedLabelSingular' => esc_html( sprintf( _n( '%s item added', '%s items added', 1, 'yith-woocommerce-product-bundles' ), 1 ) ),
							// translators: %s is the number of items added.
							'addedLabelPlural'   => esc_html( _n( '%s item added', '%s items added', 2, 'yith-woocommerce-product-bundles' ) ),
						),
						'minimum_characters' => apply_filters( 'yith_wcpb_minimum_characters_ajax_search', 3 ),
						'nonces'             => array(
							'addProductToBundle' => wp_create_nonce( 'yith-wcpb-add-product-to-bundle' ),
						),
					)
				);
			}
		}

		/**
		 * Show premium landing tab
		 */
		public function show_premium_tab() {
			$landing = YITH_WCPB_TEMPLATE_PATH . '/premium.php';
			file_exists( $landing ) && require $landing;
		}

		/**
		 * Show premium landing tab
		 */
		public function show_how_to_tab() {
			$landing = YITH_WCPB_TEMPLATE_PATH . '/how-to.php';
			file_exists( $landing ) && require $landing;
		}

		/**
		 * Get the premium landing uri
		 *
		 * @return  string
		 */
		public function get_premium_landing_uri() {
			return apply_filters( 'yith_plugin_fw_premium_landing_uri', $this->premium_landing, YITH_WCPB_SLUG );
		}
	}
}

/**
 * Unique access to instance of YITH_WCPB_Admin class
 *
 * @return YITH_WCPB_Admin|YITH_WCPB_Admin_Premium
 */
function yith_wcpb_admin() {
	return YITH_WCPB_Admin::get_instance();
}
