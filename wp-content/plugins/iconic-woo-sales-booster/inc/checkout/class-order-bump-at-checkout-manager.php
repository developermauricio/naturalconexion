<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once 'abstracts/class-order-bump-checkout-manager-abstract.php';

/**
 * Iconic_WSB_Order_Bump_At_Checkout_Manager.
 *
 * @class    Iconic_WSB_Order_Bump_At_Checkout_Manager
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Order_Bump_At_Checkout_Manager extends Iconic_WSB_Order_Bump_Checkout_Manager_Abstract {

	protected $cart_meta_key = "iconic_wsb_at_checkout";

	/**
	 * Run manager
	 */
	protected function __construct() {
		parent::__construct();

		// Clicks column
		add_action( 'manage_' . $this->get_post_type() . '_posts_custom_column',
			array( $this, 'render_clicks_columns' ), 10, 2 );

		if ( ! is_admin() ) {
			add_action( 'init', array( $this, 'init_frontend' ) );
		}
	}

	/**
	 * Add clicks  column to bump admin table
	 *
	 * @param array $columns
	 *
	 * @return mixed
	 */
	public function add_statistics_columns( $columns ) {
		$columns        = parent::add_statistics_columns( $columns );
		$result_columns = [];

		foreach ( $columns as $column => $name ) {
			$result_columns[ $column ] = $name;

			if ( $column === 'impression' ) {
				$result_columns['clicks'] = __( 'Clicks', 'iconic-wsb' );
			}
		}

		return $result_columns;
	}

	/**
	 * @param string $column
	 * @param int $post_id
	 */
	public function render_clicks_columns( $column, $post_id ) {
		if ( $column === 'clicks' ) {
			echo $this->get_order_bump( $post_id )->get_clicks_count();
		}
	}

	/**
	 * Init frontend hooks
	 */
	public function init_frontend() {
		// Render order bump HTML on Page Load.
		add_action( 'template_redirect', array( $this, 'render_checkout' ) );
		// Render order bump HTML on AJAX.
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'render_checkout' ), 99 );
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'handle_checkout_update' ) );
	}

	/**
	 * Render bump on checkout
	 */
	public function render_checkout() {
		$bump = $this->get_suitable_bump();

		if ( $bump ) {
			$render_hook = $bump->get_render_settings()['position'];
			$render_hook = apply_filters( 'iconic_wsb_order_bump_position', $render_hook, $bump );

			add_action( $render_hook, function () use ( $bump ) {
				global $iconic_wsb_class;

				$cart_item       = Iconic_WSB_Cart::get_cart_item( $this->cart_meta_key );
				$cart_item_id    = false;
				$product         = $bump->get_product_offer();

				if( $cart_item ) {
					$cart_item_id = ( isset( $cart_item["variation_id"]) && $cart_item["variation_id"] ) ? $cart_item["variation_id"] : $cart_item["product_id"];
					$variation_data  = Iconic_WSB_Cart::get_cart_item_variation_data( $this->cart_meta_key );
				} else {
					$variation_data = Iconic_WSB_Cart::remove_variation_key_prefix( $product->get_default_attributes() );
				}

				$cart_item_price = $bump->get_price_html( $cart_item_id );

				$iconic_wsb_class->template->include_template( 'frontend/order-bump/checkout/checkout-bump.php', array(
					'bump'                => $bump,
					'variation_data'      => $variation_data,
					'cart_item_id'        => $cart_item_id,
					'price'               => $cart_item_price,
				) );

				$this->view( $bump );
			} );
		}
	}

	/**
	 * Handle ajax when on checkout trigger update_checkout
	 *
	 * @param string $post_data
	 */
	public function handle_checkout_update( $post_data ) {
		$data = [];

		parse_str( $post_data, $data );

		if ( ! empty( $data['iconic-wsb-checkout-bump-action'] ) && ! empty( $data['iconic-wsb-bump-id'] ) ) {

			$bump = Iconic_WSB_Order_Bump_At_Checkout_Manager::get_order_bump( $data['iconic-wsb-bump-id'] );

			if ( $bump && $bump->is_suitable( false ) ) {

				$offer_product  = $bump->get_product_offer();
				$product_id     = ( isset( $data['iconic-wsb-checkout-variation-id'] ) && $data['iconic-wsb-checkout-variation-id'] ) ? $data['iconic-wsb-checkout-variation-id'] : $offer_product->get_id();
				$variation_data = null;
				
				if( isset( $data['iconic-wsb-checkout-variation-data'] ) && $data['iconic-wsb-checkout-variation-data'] ) {
					$variation_data = json_decode( $data['iconic-wsb-checkout-variation-data'], true );
				}
				
				if ( $offer_product ) {

					$action = $data['iconic-wsb-checkout-bump-action'];

					if ( $action == 'add' ) {
						try {
							Iconic_WSB_Cart::remove_previously_added_item( $this->cart_meta_key );
							Iconic_WSB_Cart::add_to_cart( $product_id, 1, array(
								'bump_price'           => $bump->get_discount_price( $product_id ),
								'bump_id'              => $bump->get_id(),
								"$this->cart_meta_key"  => 1, //so we know this product was added in cart by checkout bump
							), $variation_data );

							$bump->increase_click_count();
						} catch ( Exception $e ) {
							wc_get_logger()->add( 'iconic_wsb_errors', $e->getMessage() );
						}
					} elseif ( $action == 'remove' ) {
						Iconic_WSB_Cart::remove_previously_added_item( $this->cart_meta_key );
					}
				}
			}
		}
	}


	/**
	 * @param array $data
	 * @param Iconic_WSB_Order_Bump_At_Checkout $bump
	 */
	public function save_customization_step( $data, $bump ) {
		$this->save_field( __( 'Checkbox text', 'iconic-wsb' ), $data['iconic_wsb_checkbox_text'],
			array( $bump, 'set_checkbox_text' ) );
		$this->save_field( __( 'Bump description', 'iconic-wsb' ), $data['iconic_wsb_bump_description'],
			array( $bump, 'set_bump_description' ) );
		$this->save_field( __( 'Attachment', 'iconic-wsb' ), $data['iconic_wsb_image_attachment_id'],
			array( $bump, 'set_custom_image_id' ), false );
		$this->save_field( __( 'Render settings', 'iconic-wsb' ), $data['iconic_wsb_render_settings'],
			array( $bump, 'set_render_settings' ) );
	}

	/**
	 * Register checkout bump CTP
	 */
	public function registerCPT() {
		register_post_type( $this->get_post_type(), [
			'labels'             => [
				'name'               => __( 'Checkout Order Bumps', 'iconic-wsb' ),
				'singular_name'      => __( 'Checkout Order Bump', 'iconic-wsb' ),
				'add_new'            => __( 'Add New', 'iconic-wsb' ),
				'add_new_item'       => __( 'Add New Order Bump', 'iconic-wsb' ),
				'edit_item'          => __( 'Edit Order Bump', 'iconic-wsb' ),
				'new_item'           => __( 'New Order Bump', 'iconic-wsb' ),
				'view_item'          => __( 'View Order Bump', 'iconic-wsb' ),
				'search_items'       => __( 'Find Order Bump', 'iconic-wsb' ),
				'not_found'          => __( 'No order bumps were found.', 'iconic-wsb' ),
				'not_found_in_trash' => __( 'Not found in trash', 'iconic-wsb' ),
				'menu_name'          => __( 'Order Bumps', 'iconic-wsb' ),
			],
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => Iconic_WSB_Order_Bump::MENU_SLUG,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => [ 'title' ],
		] );
	}

	/**
	 * @param WP_Post $post
	 */
	public function render_bump_edit_section( $post ) {

		if ( $post->post_type === $this->get_post_type() ) {

			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_media();

			$bump = $this->get_order_bump( $post->ID );

			if ( $bump ) {
				global $iconic_wsb_class;

				$iconic_wsb_class->template->include_template( 'admin/order-bump/checkout/edit.php', array(
					'bump'  => $bump,
					'steps' => array(
						'products' => array(
							'title' => __( 'Product(s)', 'iconic-wsb' ),
							'template' => 'admin/order-bump/checkout/steps/products.php',
						),
						'offer' => array(
							'title' => __( 'Offer', 'iconic-wsb' ),
							'template' => 'admin/order-bump/checkout/steps/offer.php',
						),
						'customize' => array(
							'title' => __( 'Customize', 'iconic-wsb' ),
							'template' => 'admin/order-bump/checkout/at-checkout/steps/customization.php',
						),
					),
				) );
			}
		}
	}

	/**
	 * Return instance of checkout order bump
	 *
	 * @param int $id
	 *
	 * @return bool|Iconic_WSB_Order_Bump_At_Checkout
	 */
	public function get_order_bump( $id ) {
		try {
			require_once 'class-order-bump-at-checkout.php';

			$bump = new Iconic_WSB_Order_Bump_At_Checkout( $id );

		} catch ( Exception $e ) {
			return false;
		}

		return $bump;
	}

	/**
	 * Return managed post type
	 *
	 * @return string
	 */
	public function get_post_type() {
		return 'at_checkout_ob';
	}

	/**
	 * Change default updating messages
	 *
	 * @param array $messages
	 *
	 * @return mixed
	 */
	public function change_bump_messages( $messages ) {
		global $post;

		if ( $post && $post->post_type == $this->get_post_type() ) {
			$messages['post'][1] = __( 'Order Bump Updated.', 'iconic-wsb' );
			$messages['post'][6] = __( 'Order Bump Created.', 'iconic-wsb' );
		}

		return $messages;
	}
}