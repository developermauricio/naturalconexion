<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once 'abstracts/class-order-bump-checkout-manager-abstract.php';

/**
 * Iconic_WSB_Order_Bump_After_Checkout_Manager.
 *
 * @class    Iconic_WSB_Order_Bump_After_Checkout_Manager
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Order_Bump_After_Checkout_Manager extends Iconic_WSB_Order_Bump_Checkout_Manager_Abstract {

	private $cart_meta_key = "iconic_wsb_after_checkout";
	/**
	 * Run manager
	 */
	protected function __construct() {
		parent::__construct();

		if ( ! is_admin() ) {
			add_action( 'init', array( $this, 'init_frontend' ) );
		}
	}

	/**
	 * Init frontend hooks
	 */
	public function init_frontend() {
		add_action( 'woocommerce_review_order_before_submit', array( $this, 'render_checkout_fields' ) );
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'handle_checkout_update' ) );
	}

	/**
	 * Render bump on checkout
	 *
	 * @param Iconic_WSB_Order_Bump_After_Checkout $bump
	 */
	public function render_checkout_modal( $bump ) {
		add_action( 'woocommerce_after_checkout_form', function () use ( $bump ) {
			global $iconic_wsb_class;

			$product        = $bump->get_product_offer();
			$variation_data = ( $product->is_type( 'variable' ) || $product->is_type( 'variation' ) ) ? $product->get_variation_attributes() : array();
			$variation_data = Iconic_WSB_Cart::remove_variation_key_prefix( $variation_data );

			$iconic_wsb_class->template->include_template(
				'frontend/order-bump/checkout/checkout-bump-modal.php',
				array(
					'bump'           => $bump,
					'variation_data' => $variation_data,
					'product'        => $product,
				) 
			);

			$this->view( $bump );
		} );
	}

	/**
	 * Render hidden fields for bump at checkout.
	 * Used for adding to cart bump offer product
	 */
	public function render_checkout_fields() {
		$bump = $this->get_suitable_bump();
		if ( $bump ) {
			global $iconic_wsb_class;

			$iconic_wsb_class->template->include_template( 'frontend/order-bump/checkout/checkout-bump-fields.php',
				array(
					'bump' => $bump,
				) );

			$this->render_checkout_modal( $bump );
		}
	}

	/**
	 * Handle ajax when on checkout trigger update_checkout
	 *
	 * @param string $post_data Checkout post data.
	 */
	public function handle_checkout_update( $post_data ) {
		// If product is already in cart for 'after checkout' then don't add again.
		if ( Iconic_WSB_Cart::get_cart_item( $this->cart_meta_key ) ) {
			return;
		}

		$data = array();
		parse_str( $post_data, $data );
		if ( ! empty( $data['iconic-wsb-acb-action'] ) && ! empty( $data['iconic-wsb-acb-bump-id'] ) ) {
			$bump = self::get_order_bump( $data['iconic-wsb-acb-bump-id'] );

			if ( $bump && $bump->is_suitable( false ) ) {
				$offer_product  = $bump->get_product_offer();
				$variation_data = null;

				// If variation-id is present then add variation product in the cart.
				if ( isset( $data['iconic-wsb-acb-variation-id'] ) && $data['iconic-wsb-acb-variation-id'] ) {
					// For security, ensure that this product is child product of offer product.
					$variation = wc_get_product( $data['iconic-wsb-acb-variation-id'] );
					if ( $offer_product->get_id() == $variation->get_parent_id() ) {
						$offer_product = $variation;
					}
					$variation_data = json_decode( $data['iconic-wsb-acb-variation-data'], true );
				}

				if ( $offer_product ) {
					$action = $data['iconic-wsb-acb-action'];

					if ( 'add' === $action ) {
						try {
							Iconic_WSB_Cart::add_to_cart(
								$offer_product,
								1,
								array(
									'bump_price'           => $bump->get_discount_price( $offer_product->get_id() ),
									'bump_id'              => $bump->get_id(),
									"$this->cart_meta_key" => 1, // So we know this product was added in cart by us.
								),
								$variation_data
							);
						} catch ( Exception $e ) {
							wc_get_logger()->add( 'iconic_wsb_errors', $e->getMessage() );
						}
					}
				}
			}
		}
	}

	/**
	 * @param array                                $data
	 * @param Iconic_WSB_Order_Bump_After_Checkout $bump
	 */
	public function save_customization_step( $data, $bump ) {
		$this->save_field( __( 'Show Progress Bar', 'iconic-wsb' ), $data['iconic_wsb_show_progress_bar'],
			array( $bump, 'set_need_show_progress_bar' ) );
		$this->save_field( __( 'Bump Title', 'iconic-wsb' ), $data['iconic_wsb_bump_title'],
			array( $bump, 'set_bump_title' ) );
		$this->save_field( __( 'Bump Subtitle', 'iconic-wsb' ), $data['iconic_wsb_bump_subtitle'],
			array( $bump, 'set_bump_subtitle' ) );
		$this->save_field( __( 'Product Intro', 'iconic-wsb' ), $data['iconic_wsb_bump_product_intro'],
			array( $bump, 'set_product_intro' ) );
		$this->save_field( __( 'Product Benefits', 'iconic-wsb' ), $data['iconic_wsb_product_benefits'],
			array( $bump, 'set_product_benefits' ), false );
		$this->save_field( __( 'Button Text', 'iconic-wsb' ), $data['iconic_wsb_bump_button_text'],
			array( $bump, 'set_button_text' ) );
		$this->save_field( __( 'Skip Text', 'iconic-wsb' ), $data['iconic_wsb_bump_skip_text'],
			array( $bump, 'set_skip_text' ) );
	}

	/**
	 * Register checkout bump CTP
	 */
	public function registerCPT() {
		register_post_type( $this->get_post_type(), [
			'labels'             => [
				'name'               => __( 'After Checkout Cross-Sells', 'iconic-wsb' ),
				'singular_name'      => __( 'After Checkout Cross-Sell', 'iconic-wsb' ),
				'add_new'            => __( 'Add New', 'iconic-wsb' ),
				'add_new_item'       => __( 'Add New Cross-Sell', 'iconic-wsb' ),
				'edit_item'          => __( 'Edit Cross-Sell', 'iconic-wsb' ),
				'new_item'           => __( 'New Cross-Sell', 'iconic-wsb' ),
				'view_item'          => __( 'View Cross-Sell', 'iconic-wsb' ),
				'search_items'       => __( 'Find Cross-Sell', 'iconic-wsb' ),
				'not_found'          => __( 'No cross-sells were found.', 'iconic-wsb' ),
				'not_found_in_trash' => __( 'Not found in trash', 'iconic-wsb' ),
				'menu_name'          => __( 'After Checkout', 'iconic-wsb' ),
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
	 * Render bump edit/create section at admin side
	 *
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
						'products'  => array(
							'title'    => __( 'Product(s)', 'iconic-wsb' ),
							'template' => 'admin/order-bump/checkout/steps/products.php',
						),
						'offer'     => array(
							'title'    => __( 'Offer', 'iconic-wsb' ),
							'template' => 'admin/order-bump/checkout/steps/offer.php',
						),
						'customize' => array(
							'title'    => __( 'Customize', 'iconic-wsb' ),
							'template' => 'admin/order-bump/checkout/after-checkout/steps/customization.php',
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
	 * @return bool|Iconic_WSB_Order_Bump_After_Checkout
	 */
	public function get_order_bump( $id ) {
		try {
			require_once 'class-order-bump-after-checkout.php';

			$bump = new Iconic_WSB_Order_Bump_After_Checkout( $id );
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
		return 'after_checkout_ob';
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
			$messages['post'][1] = __( 'Cross-Sell Updated.', 'iconic-wsb' );
			$messages['post'][6] = __( 'Cross-Sell Created.', 'iconic-wsb' );
		}

		return $messages;
	}
}