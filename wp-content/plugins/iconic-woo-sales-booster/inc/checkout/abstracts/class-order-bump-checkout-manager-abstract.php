<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Order_Bump_Checkout_Manager_Abstract.
 *
 * @class    Iconic_WSB_Order_Bump_Checkout_Manager_Abstract
 * @version  1.0.0
 * @category Abstract Class
 * @author   Iconic
 */
abstract class Iconic_WSB_Order_Bump_Checkout_Manager_Abstract {
	/**
	 * @var array
	 */
	protected $validation_errors = [];

	/**
	 * Singleton
	 *
	 * @return self
	 */
	final public static function get_instance() {
		static $instances = array();

		$calledClass = get_called_class();

		if ( ! isset( $instances [ $calledClass ] ) ) {
			$instances[ $calledClass ] = new $calledClass();
		}

		return $instances[ $calledClass ];
	}

	/**
	 * Iconic_WSB_Order_Bump_Checkout_Manager_Abstract constructor.
	 */
	protected function __construct() {
		$this->common_hooks();
	}

	/**
	 * Register common hooks
	 */
	protected function common_hooks() {
		add_action( 'init', array( $this, 'registerCPT' ) );
		add_action( 'edit_form_after_title', array( $this, 'render_bump_edit_section' ) );
		add_filter( 'post_row_actions', array( $this, 'remove_inline_actions' ), 10, 2 );
		add_filter( 'request', array( $this, 'order_by_priority' ) );
		add_filter( 'manage_' . $this->get_post_type() . '_posts_columns', array( $this, 'remove_date_column' ) );
		add_action( 'save_post_' . $this->get_post_type(), array( $this, 'save_bump' ), 1, 3 );
		add_filter( 'post_updated_messages', array( $this, 'change_bump_messages' ) );
		add_action( 'wp_ajax_iconic_wsb_handle_sorting_bump_checkout_product', array( $this, 'handle_sorting' ) );

		add_action( 'woocommerce_before_calculate_totals', array( $this, 'calculate_bump_price' ) );

		// Statistics columns
		add_filter( 'manage_' . $this->get_post_type() . '_posts_columns', array( $this, 'add_statistics_columns' ), 99, 1 );
		add_action( 'manage_' . $this->get_post_type() . '_posts_custom_column',
			array( $this, 'render_statistics_columns' ), 10, 2 );

		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'bump_purchased' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'check_for_removing' ) );
		add_filter( 'months_dropdown_results', array( $this, 'remove_date_filter' ), 1, 2 );

		add_action( 'woocommerce_thankyou', array( $this, 'remove_viewed_bumps' ) );
	}

	/**
	 * Remove all view bumps. Let increase statistics again
	 */
	public function remove_viewed_bumps() {
		WC()->session->set( 'iconic_wsb_viewed_bumps', [] );
	}

	/**
	 * Remove filter by date on bumps list page
	 *
	 * @param array  $dates
	 * @param string $post_type
	 *
	 * @return array
	 */
	public function remove_date_filter( $dates, $post_type ) {
		if ( $post_type === $this->get_post_type() ) {
			return [];
		}

		return $dates;
	}

	/**
	 * Remove bumps if they are not suitable
	 *
	 * @param WC_cart $cart
	 */
	public function check_for_removing( $cart ) {
		foreach ( $cart->cart_contents as $cart_item ) {
			if ( isset( $cart_item['bump_id'] ) ) {
				$bump = $this->get_order_bump( $cart_item['bump_id'] );

				if ( $bump && ! $bump->is_suitable( false ) ) {
					$product_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
					Iconic_WSB_Cart::remove_from_cart( $product_id );
					wc_add_notice( sprintf( __( '%s was removed from cart, because bump is not available in this case' ),
						$cart_item['data']->get_title() ) );
				}
			}
		}
	}

	/**
	 * Increase statistic purchasing
	 *
	 * @param WC_Order_Item $item
	 * @param string        $cart_item_key
	 */
	public function bump_purchased( $item, $cart_item_key ) {
		if ( isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
			$cart_item = WC()->cart->cart_contents[ $cart_item_key ];

			if ( isset( $cart_item['bump_id'] ) ) {
				$bump = $this->get_order_bump( $cart_item['bump_id'] );

				if ( $bump ) {
					$bump->increase_purchases_count();
				}
			}
		}
	}

	/**
	 * Increase viewing bump if user is seeing it in first time
	 *
	 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump
	 */
	public function view( $bump ) {
		$viewed_bumps = WC()->session->get( 'iconic_wsb_viewed_bumps', [] );

		if ( ! in_array( $bump->get_id(), $viewed_bumps ) ) {
			$bump->increase_impression_count();
			$viewed_bumps[] = $bump->get_id();

			WC()->session->set( 'iconic_wsb_viewed_bumps', $viewed_bumps );
		}
	}

	/**
	 * Add statistics column to bump admin table
	 *
	 * @param array $columns
	 *
	 * @return mixed
	 */
	public function add_statistics_columns( $columns ) {
		$columns['impression'] = __( 'Impressions', 'iconic-wsb' );
		$columns['purchases']  = __( 'Purchases', 'iconic-wsb' );
		$columns['conversion'] = __( 'Conversions', 'iconic-wsb' );
		$columns['draggable']  = '';

		return $columns;
	}

	/**
	 * @param string $column
	 * @param int    $post_id
	 */
	public function render_statistics_columns( $column, $post_id ) {
		$bump = $this->get_order_bump( $post_id );

		switch ( $column ) {
			case 'impression' :
				echo $bump->get_impression_count();
				break;
			case 'purchases' :
				echo $bump->get_purchases_count();
				break;
			case 'conversion' :
				$this->conversation_column( $bump );
				break;
			case 'draggable':
				echo '<span class="dashicons dashicons-menu iconic-wsb-sortable"></span>';
				break;
		}
	}

	/**
	 * Render conversation column
	 *
	 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump
	 */
	public function conversation_column( $bump ) {
		$rate = $bump->get_conversion_rate() * 100;

		if ( $rate > 30 ) {
			$class = 'conversation-mark--good';
		} elseif ( $rate > 10 || ( $rate === 0 && $bump->get_impression_count() === 0 ) ) {
			$class = 'conversation-mark--normal';
		} else {
			$class = 'conversation-mark--badly';
		}

		$conversation = number_format( $rate, 2, '.', '2' ) . '%';

		echo '<span class="conversation-mark ' . $class . '"><span>' . $conversation . '</span></span>';
	}

	/**
	 * @return Iconic_WSB_Order_Bump_Checkout_Abstract[]
	 */
	public function get_active_bumps() {
		$bumps = get_posts( array(
			'numberposts' => - 1,
			'post_type'   => $this->get_post_type(),
			'post_status' => 'publish',
			'orderby'     => 'meta_value',
			'order'       => 'ASC',
			'meta_key'    => '_priority',
		) );

		return array_map( function ( $post ) {
			return $this->get_order_bump( $post->ID );
		}, $bumps );
	}

	/**
	 * Return first suitable bump for user cart
	 *
	 * @return bool|Iconic_WSB_Order_Bump_Checkout_Abstract|mixed
	 */
	public function get_suitable_bump() {
		$checkout_bumps = $this->get_active_bumps();

		foreach ( $checkout_bumps as $checkout_bump ) {
			// Dont check the product in cart if same product is enabled as offer product.
			$check_for_cart = ! $checkout_bump->get_enable_bump_for_same_product();
			if ( $checkout_bump->is_suitable( $check_for_cart ) && $checkout_bump->is_valid()) {
				return $checkout_bump;
			}
		}

		return false;
	}

	/**
	 * @param WC_Cart $cart_object
	 */
	public function calculate_bump_price( $cart_object ) {
		foreach ( $cart_object->cart_contents as $key => $value ) {
			if ( isset( $value['bump_price'] ) ) {
				if ( $value['data'] instanceof WC_Product ) {
					$value['data']->set_price( $value['bump_price'] );
				}
			}
		}
	}

	/**
	 * Save data by calling setter with validation for requiring
	 *
	 * @param string   $field
	 * @param mixed    $value
	 * @param callable $method
	 * @param bool     $required
	 */
	protected function save_field( $field, $value, $method, $required = true ) {
		if ( empty( $value ) && $required ) {
			$this->validation_errors[] = sprintf( __( '%s is required', 'iconic-wsb' ), $field );

			return;
		}

		call_user_func( $method, $value );
	}

	/**
	 * Save priority when user change order for checkout order bump
	 */
	public function handle_sorting() {
		$output   = [];
		$replaces = [];

		parse_str( $_REQUEST['data'], $output );

		$post_type = $_REQUEST['post_type'];
		if ( $post_type !== $this->get_post_type() ) {
			return;
		}

		$before_posts = $_REQUEST['posts'];
		$after_posts  = $output['post'];

		foreach ( $after_posts as $key => $post_id ) {
			// Before post on this position
			$before_post_id = $before_posts[ array_search( $post_id, $after_posts ) ];
			// If post dont change position
			if ( $before_post_id == $post_id ) {
				continue;
			}

			$before_order_bump = $this->get_order_bump( $before_post_id );
			$order_bump        = $this->get_order_bump( $post_id );

			$replaces[] = [
				'order_bump' => $order_bump,
				'priority'   => $before_order_bump->get_priority(),
			];
		}

		foreach ( $replaces as $replace ) {
			if ( $replace['order_bump'] instanceof Iconic_WSB_Order_Bump_Checkout_Abstract ) {
				$replace['order_bump']->set_priority( $replace['priority'] );
			}
		}

		wp_send_json( [ 'posts' => $after_posts ] );
	}

	/**
	 * @param array                                   $data
	 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump
	 */
	protected function save_product_step( $data, $bump ) {
		$this->save_field( __( 'Display type', 'iconic-wsb' ), $data['iconic_wsb_display_type'],
			array( $bump, 'set_display_type' ) );
		$this->save_field( __( 'Apply when', 'iconic-wsb' ), $data['iconic_wsb_apply_when_specific'],
			array( $bump, 'set_apply_when_specific' ) );
		
		$enable_for_same_product = isset( $data['iconic_wsb_enable_bump_for_same_product'] ) ? true : false;
		$this->save_field(
			__( 'Show Order Bump even if the offer product is already in the cart.', 'iconic-wsb' ),
			$enable_for_same_product,
			array( $bump, 'set_enable_bump_for_same_product' ),
			false
		);

		if ( isset( $data['iconic_wsb_specific_product'] ) ) {
			$this->save_field( __( 'Specific products', 'iconic-wsb' ), $data['iconic_wsb_specific_product'],
				array( $bump, 'set_specific_products' ), false );
		}
	}

	/**
	 * @param array                                   $data
	 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump
	 */
	protected function save_offer_step( $data, $bump ) {
		if ( $data['iconic_wsb_discount_type'] === 'percentage' && $data['iconic_wsb_discount'] > 100 ) {
			$this->validation_errors[] = __( 'Discount cannot be more than 100%', 'iconic-wsb' );

			return;
		} elseif ( $data['iconic_wsb_discount_type'] === 'simple' ) {
			$product = wc_get_product( $data['iconic_wsb_product_offer'] );

			if ( $product && $product->get_price() < $data['iconic_wsb_discount'] ) {
				$this->validation_errors[] = __( 'Discount cannot be more than product price', 'iconic-wsb' );

				return;
			}
		}

		if ( $data['iconic_wsb_display_type'] === 'specific' && in_array( $data['iconic_wsb_product_offer'],
				$data['iconic_wsb_specific_product'] ) ) {
			$this->validation_errors[] = __( 'Offered product cannot be in list of products used for condition',
				'iconic-wsb' );

			return;
		}

		$this->save_field( __( 'Product offer', 'iconic-wsb' ), $data['iconic_wsb_product_offer'],
			array( $bump, 'set_product_offer' ) );
		$this->save_field( __( 'Discount', 'iconic-wsb' ), $data['iconic_wsb_discount'], array( $bump, 'set_discount' ),
			false );
		$this->save_field( __( 'Discount type', 'iconic-wsb' ), $data['iconic_wsb_discount_type'],
			array( $bump, 'set_discount_type' ) );
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
			$messages['post'][1] = __( 'Updated.', 'iconic-wsb' );
			$messages['post'][6] = __( 'Created.', 'iconic-wsb' );
		}

		return $messages;
	}

	/**
	 * Check saving
	 *
	 * @param WP_Post $post
	 *
	 * @return bool
	 */
	protected function is_user_save_post( $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		if ( ! empty( $_REQUEST['action'] ) && in_array( $_REQUEST['action'],
				array( 'delete', 'trash', 'untrash' ) ) ) {
			return false;
		}
		if ( $post->post_status == 'auto-draft' ) {
			return false;
		}

		return true;
	}

	/**
	 * @param int     $post_id
	 * @param WP_Post $post
	 * @param bool    $update
	 *
	 * @return bool
	 */
	public function save_bump( $post_id, $post, $update ) {
		if ( ! $this->is_user_save_post( $post ) ) {
			return false;
		}

		static $saved = false;

		$bump = $this->get_order_bump( $post_id );
		$data = $_REQUEST;

		if ( $bump && ! $saved ) {
			do_action( 'iconic-wsb-before-save-checkout-bump', $bump, $update );

			if ( ! $bump->get_priority() ) {
				$bump->generate_priority();
			}

			$this->save_product_step( $data, $bump );
			$this->save_offer_step( $data, $bump );
			$this->save_customization_step( $data, $bump );

			$saved = true;

			if ( ! empty( $this->validation_errors ) ) {
				$this->show_validation_errors();

				if ( ! $update ) {
					$bump->set_draft();
				}
			}

			do_action( 'iconic-wsb-after-save-checkout-bump', $bump, $update );
		}

		return true;
	}

	/**
	 * Render validation error after save bump
	 */
	public function show_validation_errors() {
		$validation_errors = apply_filters( 'iconic-wsb-checkout-order-bump-validation-errors',
			$this->validation_errors );

		foreach ( $validation_errors as $error ) {
			Iconic_WSB_Notifier::flash( $error, Iconic_WSB_Notifier::ERROR );
		}
	}

	/**
	 * @param array $defaults
	 *
	 * @return array
	 */
	public static function remove_date_column( $defaults ) {
		unset( $defaults['date'] );

		return $defaults;
	}

	/**
	 * Order bumps by priority meta key
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function order_by_priority( $vars ) {
		if ( isset( $vars['post_type'] ) && $vars['post_type'] === $this->get_post_type() && $vars['post_status'] !== 'draft' ) {
			$vars['orderby']  = [ '_priority' => 'ASC', 'title' => 'ASC' ];
			$vars['meta_key'] = apply_filters( 'iconic-wsb-checkout-order-bump-priority-key', '_priority' );
		}

		return $vars;
	}

	/**
	 * Remove quick view and frontend view inline actions
	 *
	 * @param array   $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function remove_inline_actions( $actions, $post ) {
		if ( $post->post_type === $this->get_post_type() ) {
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['view'] );
		}

		return $actions;
	}

	/**
	 * @param $post_id
	 *
	 * @return Iconic_WSB_Order_Bump_Checkout_Abstract
	 */
	abstract public function get_order_bump( $post_id );

	/**
	 * Return managed post type
	 *
	 * @return string
	 */
	abstract public function get_post_type();

	/**
	 *
	 * @param array                                   $data
	 * @param Iconic_WSB_Order_Bump_Checkout_Abstract $bump
	 *
	 * @return mixed
	 */
	abstract protected function save_customization_step( $data, $bump );

	/**
	 * Register CPT for bump
	 */
	abstract public function registerCPT();

	/**
	 * Render section create\edit bump
	 *
	 * @param WP_Post $post
	 */
	abstract public function render_bump_edit_section( $post );

	private function __clone() {
	}
}