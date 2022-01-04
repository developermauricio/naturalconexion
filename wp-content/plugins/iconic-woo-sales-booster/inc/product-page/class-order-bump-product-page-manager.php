<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once 'abstracts/class-order-bump-product-page-manager-abstract.php';

/**
 * Iconic_WSB_Order_Bump_Product_Page_Manager.
 *
 * @class    Iconic_WSB_Order_Bump_Product_Page_Manager
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Order_Bump_Product_Page_Manager extends Iconic_WSB_Order_Bump_Product_Page_Manager_Abstract {
	/**
	 * Iconic_WSB_Order_Bump_Product_Page_Manager constructor.
	 */
	public function __construct() {
		parent::__construct(
			'_iconic_wsb_product_page_order_bump_ids',
			'iconic_wsb_product_page_order_bump_ids',
			__( 'Frequently Bought Together', 'iconic-wsb' ),
			__( 'Display these products on the single product page and easily add them to the cart with a single button.', 'iconic-wsb' )
		);

		$this->hooks();

		if ( ! is_admin() ) {
			add_action( 'init', array( $this, 'init_frontend' ) );
		}
	}

	/**
	 * Init frontend hooks
	 */
	public function init_frontend() {
		add_action( $this->get_product_page_order_bump_render_hook(), array( $this, 'frontend_product_page_order_bump' ) );
		add_filter( 'iconic_wsb_remove_products', array( $this, 'maybe_remove_products' ) );
	}

	/**
	 * Render bump products section on frontend
	 */
	public function frontend_product_page_order_bump() {
		global $iconic_wsb_class, $product;

		if ( $product instanceof WC_Product ) {
			$bump_ids = $this->get_bump_products_ids( $product->get_id() );

			if ( ! empty( $bump_ids ) ) {
				$bump_products = array_map( function ( $id ) {
					return wc_get_product( $id );
				}, $this->get_bump_products_ids( $product->get_id() ) );

				$bump_products = $this->remove_already_in_cart_products( $bump_products );

				$bump_products = array_filter( $bump_products, [ $this, 'isValidBump' ] );

				// No bumps? Do nothing.
				if ( empty( $bump_products ) ) {
					return;
				}

				// Add current product to start of array.
				$bump_products    = array_merge( array( wc_get_product( $product->get_id() ) ), $bump_products );
				$bump_ids         = array_merge( array( $product->get_id() ), $bump_ids );
				$price_html       = self::get_price_html_for_bumps( $bump_ids, $product->get_id() );
				$product_settings = $this->get_fields_data( $product->get_id() );
				$settings         = $this->get_settings();
				$set_unchecked    = empty( $product_settings['set_unchecked'] ) ? 'no' : $product_settings['set_unchecked'];
				$checked_products = self::get_checked_products( $product->get_id() );
				$price_html       = self::get_price_html_for_bumps( $checked_products, $product->get_id() );

				$product_settings['title'] = ( '' === trim( $product_settings['title'] ) ) ? $settings['order_bump_title'] : $product_settings['title'];

				$iconic_wsb_class->template->include_template(
					'frontend/order-bump/product/bump-products.php',
					array(
						'title'            => $product_settings['title'],
						'sales_pitch'      => $product_settings['sales_pitch'],
						'set_unchecked'    => $set_unchecked,
						'bump_products'    => $bump_products,
						'total_price'      => $price_html,
						'settings'         => $settings,
						'product'          => $product,
						'checked_products' => $checked_products,
						'discount_message' => $this->get_discount_message( $product->get_id() ),
					)
				);
			}
		}
	}

	/**
	 * Returns the product IDs which among the FBT products should be checked on page load.
	 *
	 * @param int $offer_product_id Offer Product ID.
	 *
	 * @return array $checked_products
	 */
	public static function get_checked_products( $offer_product_id ) {
		$checked_products = array();
		$product_settings = self::get_fields_data( $offer_product_id );
		$set_unchecked    = empty( $product_settings['set_unchecked'] ) ? 'no' : $product_settings['set_unchecked'];
		$fbt_products     = is_array( $product_settings['fbt_products'] ) ? $product_settings['fbt_products'] : array();

		return 'yes' === $set_unchecked ? array( $offer_product_id ) : array_merge( array( $offer_product_id ), $fbt_products );
	}

	/**
	 * If setting is true, remove products from
	 * bump when already in cart.
	 *
	 * @param bool $remove_products Remove product.
	 *
	 * @return bool
	 */
	public function maybe_remove_products( $remove_products ) {
		$settings = $this->get_settings();

		if ( ! $settings['hide_already_in_cart'] ) {
			return false;
		}

		return $remove_products;
	}

	/**
	 *  Register hooks
	 */
	private function hooks() {
		add_filter( 'wpsf_register_settings_iconic-wsb', array( $this, 'add_settings_section' ) );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_fbt_discount' ) );
		if ( is_admin() ) {
			add_action( 'iconic_wsb_product_data_panel', array( $this, 'add_discount_field' ), 10 );
		}
	}

	/**
	 * Returns all settings data related to FBT.
	 *
	 * @param int|bool $post_id
	 *
	 * @return array|bool
	 */
	public static function get_fields_data( $post_id = false ) {
		global $post;

		// If post_id is not defined, try to fetch it from global $post.
		if ( ! $post_id ) {
			if ( ! $post ) {
				return false;
			}

			$post_id = $post->ID;
		}

		$product_settings = array(
			'title'          => get_post_meta( $post_id, '_iconic_wsb_fbt_title', true ),
			'discount_type'  => get_post_meta( $post_id, '_iconic_wsb_fbt_discount_type', true ),
			'discount_value' => get_post_meta( $post_id, '_iconic_wsb_fbt_discount_value', true ),
			'fbt_products'   => get_post_meta( $post_id, '_iconic_wsb_product_page_order_bump_ids', true ),
			'sales_pitch'    => get_post_meta( $post_id, '_iconic_wsb_fbt_sales_pitch', true ),
			'set_unchecked'  => get_post_meta( $post_id, '_iconic_wsb_fbt_set_unchecked', true ),
		);

		if ( ! is_admin() ) {
			$settings                  = self::get_settings();
			$product_settings['title'] = empty( trim( $product_settings['title'] ) ) ? $settings['order_bump_title'] : $product_settings['title'];
		}

		return $product_settings;
	}

	/**
	 * Add service settings section
	 *
	 * @param array $settings
	 *
	 * @return mixed
	 */
	public function add_settings_section( $settings ) {
		$settings['sections']['product-page'] = array(
			'tab_id'              => 'order_bump',
			'section_id'          => 'product_page',
			'section_title'       => __( 'Frequently Bought Together', 'iconic-wsb' ),
			'section_description' => __( 'These are cross-sells which appear on the product page near the Add to Cart button.', 'iconic-wsb' ),
			'section_order'       => 0,
			'fields'              => array(
				array(
					'id'      => 'link_product_titles',
					'title'   => __( 'Link Product Titles?', 'iconic-wsb' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'id'      => 'show_product_thumbnail',
					'title'   => __( 'Show Product Thumbnail?', 'iconic-wsb' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'id'      => 'hide_already_in_cart',
					'title'   => __( 'Hide Product if Already in Cart?', 'iconic-wsb' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'id'      => 'use_ajax',
					'title'   => __( 'Enable AJAX for "Add Selected to Cart" button?', 'iconic-wsb' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'id'      => 'show_hidden_products',
					'title'   => __( 'Show hidden products?', 'iconic-wsb' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				array(
					'id'      => 'order_bump_position',
					'title'   => __( 'Position', 'iconic-wsb' ),
					'type'    => 'select',
					'default' => 'woocommerce_after_add_to_cart_button',
					'choices' => array(
						'woocommerce_after_add_to_cart_button'  => __( 'After Add to Cart Button', 'iconic-wsb' ),
						'woocommerce_before_add_to_cart_button' => __( 'Before Add to Cart Button', 'iconic-wsb' ),
					),
				),
				array(
					'id'      => 'order_bump_title',
					'title'   => __( 'Cross Sells Title', 'iconic-wsb' ),
					'desc'    => __( 'Leave blank to disable the title.', 'iconic-wsb' ),
					'type'    => 'text',
					'default' => __( 'Frequently Bought Together', 'iconic-wsb' ),
				),
			),
		);

		return $settings;
	}

	/**
	 * Add frequently bought together discount.
	 *
	 * @param WC_Cart $cart
	 */
	public function add_fbt_discount( $cart ) {
		$items             = $cart->get_cart();
		$offer_product_ids = array();

		// Find the offers applicable.
		foreach ( $items as $item ) {
			if ( isset( $item["iconic_wsb_fbt"] ) ) {
				$offer_product_ids[] = $item["iconic_wsb_fbt"];
			}
		}

		$offer_product_ids = array_unique( $offer_product_ids );
		foreach ( $offer_product_ids as $offer_product_id ) {
			self::apply_discount_for_offer( $offer_product_id, $cart );
		}
	}

	/**
	 * Apply discount for the given FBT offer ID.
	 *
	 * @param int     $offer_product_id
	 * @param WC_Cart $cart
	 */
	public static function apply_discount_for_offer( $offer_product_id, $cart ) {
		if ( empty( $offer_product_id ) ) {
			return;
		}

		$items            = $cart->get_cart();
		$cart_product_ids = array();

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $key => $item ) {
			if ( isset( $item['iconic_wsb_fbt'] ) && $item['iconic_wsb_fbt'] === $offer_product_id ) {
				$cart_product_ids[] = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
			}
		}

		$discount_applicable = self::validate_cart_discount( $offer_product_id, $cart_product_ids );

		if ( $discount_applicable ) {
			$fee_id       = '_iconic_wsb_fbt_discount';
			$current_fees = $cart->fees_api()->get_fees();
			$discount     = self::calculate_discount( $offer_product_id, $cart_product_ids, true );

			if ( empty( $discount ) ) {
				return;
			}

			$fee = array(
				'id'        => $fee_id,
				'name'      => __( 'Bought together discount', 'iconic-wsb' ),
				'amount'    => - floatval( apply_filters( 'iconic_wsb_fbt_bought_together_discount', $discount, $offer_product_id, $cart_product_ids ) ),
				'taxable'   => false,
				'tax_class' => '',
			);

			if ( ! isset( $current_fees[ $fee_id ] ) ) {
				$cart->fees_api()->add_fee( $fee );

				return;
			}

			$current_fees[ $fee_id ]->amount += - $discount;
			$cart->fees_api()->set_fees( $current_fees );
		}
	}

	/**
	 * Return hook which render bump section on frontend
	 *
	 * @return string
	 */
	public function get_product_page_order_bump_render_hook() {
		$settings = $this->get_settings();

		return apply_filters( 'iconic_wsb_product_page_order_bump_render_hook', $settings['order_bump_position'] );
	}

	/**
	 * Get service settings
	 *
	 * @return array
	 */
	public static function get_settings() {
		global $iconic_wsb_class;

		$prefix = 'order_bump_product_page_';

		$defaults = [
			'order_bump_position'    => 'woocommerce_after_add_to_cart_button',
			'link_product_titles'    => true,
			'show_product_thumbnail' => true,
			'hide_already_in_cart'   => true,
			'order_bump_title'       => __( 'Frequently Bought Together', 'iconic-wsb' ),
			'use_ajax'               => true,
			'show_hidden_products'   => false,
		];

		$settings = [];

		foreach ( $defaults as $key => $default ) {
			if ( isset( $iconic_wsb_class->settings ) && is_array( $iconic_wsb_class->settings ) && array_key_exists( $prefix . $key, $iconic_wsb_class->settings ) ) {
				$settings[ $key ] = $iconic_wsb_class->settings[ $prefix . $key ];
			} else {
				$settings[ $key ] = $default;
			}
		}

		return apply_filters( 'iconic_wsb_product_page_settings', $settings );
	}

	/**
	 * Get total price for array of products.
	 *
	 * @param array $bump_products
	 *
	 * @return string
	 */
	public function get_total_price( $bump_products ) {
		$total = 0;
		$range = false;

		foreach ( $bump_products as $bump_product ) {
			if ( self::has_price_range( $bump_product ) ) {
				$range = true;
			}

			$total += floatval( $bump_product->get_price( 'edit' ) );
		}

		$total = wc_price( $total );

		return $range ? sprintf( '%s: %s', __( 'From', 'iconic-wsb' ), $total ) : $total;
	}

	/**
	 * Does a product have a price range?
	 *
	 * @param array|WC_Product|WC_Product_Variable $products A single product, or array of products/product IDs.
	 *
	 * @return bool
	 */
	public static function has_price_range( $products ) {
		if ( ! is_array( $products ) ) {
			$products = array( $products );
		}

		$products = array_filter( $products );

		foreach ( $products as $product ) {
			if ( is_numeric( $product ) ) {
				$product = wc_get_product( $product );
			}

			if ( ! $product || ! $product->is_type( 'variable' ) ) {
				continue;
			}

			$prices = $product->get_variation_prices( true );

			if ( empty( $prices['price'] ) ) {
				continue;
			}

			$min_price = current( $prices['price'] );
			$max_price = end( $prices['price'] );

			return $min_price !== $max_price;
		}

		return false;
	}

	/**
	 * Get all variations and their attributes. This function is different
	 * from WC_Product_Variable::get_available_variations(), this function will
	 * create all the combinations from the attributes even if the variation attribute
	 * is set to "any"
	 *
	 * @param WC_Product_Variable $product
	 *
	 * @return void
	 */
	public static function get_variations( $product ) {
		if ( ! is_a( $product, "WC_Product_Variable" ) ) {
			return false;
		}

		$attributes = array_reverse( $product->get_variation_attributes() );

		//prepend "attribute_" to the attribute key because that's how find_matching_product_variation_id() expects it
		$attributes_modified = array();
		foreach ( $attributes as $key => $val ) {
			$attributes_modified[ "attribute_" . sanitize_title( $key ) ] = $val;
		}

		//get all possible combinations for attributes
		$cartesian        = wc_array_cartesian( $attributes_modified );
		$final_variations = array();

		foreach ( $cartesian as $attributes ) {
			$attributes = array_map( 'strval', $attributes );

			//find the variation for given attributes
			$variation_id = self::find_matching_product_variation_id( $product->get_ID(), $attributes );

			if ( $variation_id ) {
				$variation = wc_get_product( $variation_id );
				if ( is_object( $variation ) && $variation->is_purchasable() && $variation->is_in_stock() ) {
					$final_variations[] = array(
						"variation_id" => $variation->get_ID(),
						"attributes"   => $attributes,
					);
				}
			}
		}

		//replace slugs with the name
		foreach ( $final_variations as &$variaions ) {
			foreach ( $variaions["attributes"] as $taxonomy => &$slug ) {
				$taxonomy = substr( $taxonomy, 10 ); //remove "attribute_"
				if ( substr( $taxonomy, 0, 3 ) === "pa_" ) {
					$slug = self::get_term_name_from_slug( $slug, $taxonomy );
				}
			}
		}

		return $final_variations;
	}

	/**
	 * Find matching product variation
	 *
	 * @param $product_id
	 * @param $attributes
	 *
	 * @return int
	 */
	public static function find_matching_product_variation_id( $product_id, $attributes ) {
		return ( new \WC_Product_Data_Store_CPT() )->find_matching_product_variation(
			new \WC_Product( $product_id ),
			$attributes
		);
	}

	/**
	 * Return the placeholder for FBT Variable Product dropdown.
	 *
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	public static function get_variable_dropdown_placeholder( $product ) {
		if ( ! is_a( $product, 'WC_Product_Variable' ) ) {
			return false;
		}

		$attributes       = array_reverse( $product->get_variation_attributes() );
		$attribute_labels = array();

		foreach ( $attributes as $atribute_key => $atribute_value ) {
			if ( 'pa_' === substr( $atribute_key, 0, 3 ) ) {
				$taxonomy           = get_taxonomy( $atribute_key );
				$attribute_labels[] = $taxonomy->labels->singular_name;
			} else {
				$attribute_labels[] = $atribute_key;
			}
		}

		$attribute_labels = array_reverse( $attribute_labels );

		return implode( ' - ', $attribute_labels );
	}

	/**
	 * Get term's name from it's slug
	 *
	 * @param string $slug     Term slug.
	 * @param string $taxonomy Taxonomy key.
	 *
	 * @return string $term_name Term Name.
	 */
	public static function get_term_name_from_slug( $slug, $taxonomy ) {
		$term = get_term_by( 'slug', $slug, urldecode( $taxonomy ) );

		if ( is_object( $term ) ) {
			return $term->name;
		}
	}

	/**
	 * Returns the FBT discount rule for given offer product ID
	 *
	 * @param int $product_id
	 *
	 * @return arr discount rule
	 */
	public static function get_discount_rule( $product_id ) {
		$tax_rates = self::get_product_tax_rates( $product_id );
		$product   = wc_get_product( $product_id );

		$rule = array(
			'type'     => get_post_meta( $product_id, '_iconic_wsb_fbt_discount_type', true ),
			'discount' => get_post_meta( $product_id, '_iconic_wsb_fbt_discount_value', true ),
		);

		// If prices include tax, we need to get the discount excluding tax.
		if ( 'simple' === $rule['type'] && ( wc_prices_include_tax() || empty( $tax_rates ) ) ) {
			$base_tax_rates           = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
			$remove_taxes             = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $rule['discount'], $base_tax_rates, true ) : WC_Tax::calc_tax( $rule['discount'], $tax_rates, true );
			$rule['discount_exc_tax'] = $rule['discount'] - array_sum( $remove_taxes ); // Unrounded since we're dealing with tax inclusive prices. Matches logic in cart-totals class. @see adjust_non_base_location_price.
		}

		return apply_filters( 'iconic_wsb_fbt_discount_rule', $rule, $product_id );
	}

	/**
	 * Make sure that all the FBT products are present in the cart. For Variable products check if child/variation
	 * exists in cart.
	 *
	 * @param int $product_id
	 *
	 * @return array|false If valid then array of product ids which are present in the cart for the given FBT else
	 *                     false.
	 */
	public static function validate_cart_discount( $offer_product_id, $cart_product_ids ) {
		$fbt_meta_products_ids = get_post_meta( $offer_product_id, "_iconic_wsb_product_page_order_bump_ids", true );

		if ( empty( $fbt_meta_products_ids ) || ! is_array( $fbt_meta_products_ids ) ) {
			return false;
		}

		$fbt_meta_products_ids[] = $offer_product_id;

		if ( empty( $cart_product_ids ) ) {
			return false;
		}

		$parent_product_ids = array();
		$cart_product_ids   = array_filter( $cart_product_ids );

		foreach ( $cart_product_ids as $cart_product_id ) {
			$cart_product = wc_get_product( $cart_product_id );

			if ( ! $cart_product || ! $cart_product->is_in_stock() || ! $cart_product->is_purchasable() ) {
				continue;
			}

			// We consider the product if either the product ID matches, else the parent ID.
			if ( in_array( $cart_product_id, $fbt_meta_products_ids ) ) {
				$parent_product_ids[] = $cart_product_id;
			} else {
				if ( in_array( $cart_product->get_parent_id(), $fbt_meta_products_ids ) ) {
					$parent_product_ids[] = $cart_product->get_parent_id();
				}
			}
		}

		$missing_products = array_diff( $fbt_meta_products_ids, $parent_product_ids );

		// if no product is missing then discount is applicable
		return ! count( $missing_products );
	}

	/**
	 * Calculates FBT discount for the given $product_id.
	 *
	 * @param int $offer_product_id The ID of the product based on which the discount rull will be applied.
	 * @param arr $cart_product_ids The list of products ID to apply discount on
	 *
	 * @return int|float
	 */
	public static function calculate_discount( $offer_product_id, $cart_product_ids = false, $excluding_tax = false ) {
		$cart_product_ids = $cart_product_ids ? $cart_product_ids : get_post_meta( $offer_product_id, "_iconic_wsb_product_page_order_bump_ids", true );

		$rule = self::get_discount_rule( $offer_product_id );

		if ( $rule && is_array( $rule ) ) {
			$offer_product  = wc_get_product( $offer_product_id );
			$fbt_products[] = $offer_product_id;
			$discount_type  = $rule["type"];
			$discount       = isset( $rule["discount_exc_tax"] ) && ( 'excl' === get_option( 'woocommerce_tax_display_shop' ) || $excluding_tax ) ? $rule["discount_exc_tax"] : $rule["discount"];
			$total_price    = self::calculate_product_total( $cart_product_ids, $excluding_tax );

			if ( $offer_product && $discount_type && $discount ) {
				$discount_value = $discount_type == 'percentage' ? ( $total_price / 100 ) * $discount : $discount;

				return apply_filters( "iconic_wsb_fbt_calculate_discount", $discount_value, $offer_product_id, $cart_product_ids );
			}
		}

		return 0;
	}

	/**
	 * Calculate sum of the prices of given product IDs.
	 *
	 * @param array   $product_ids   Array of product IDs.
	 * @param boolean $excluding_tax To exclude tax or not to exclude.
	 *
	 * @return int|float
	 */
	public static function calculate_product_total( $product_ids, $excluding_tax = false ) {
		$total = 0;

		if ( empty( $product_ids ) ) {
			return;
		}

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product || ! $product->is_in_stock() || ! $product->is_purchasable() ) {
				continue;
			}

			$price = $excluding_tax ? wc_get_price_excluding_tax( $product ) : wc_get_price_to_display( $product );
			$price = apply_filters( "iconic_wsb_fbt_product_price", $price, $product, $excluding_tax );
			$total += $price;
		}

		return $total;
	}

	/**
	 * Returns the FBT discount message for the given Offer Product
	 *
	 * @param int $offer_product_id
	 *
	 * @return bool|string Discount message.
	 */
	function get_discount_message( $offer_product_id ) {
		$discount_rule = self::get_discount_rule( $offer_product_id );
		$tax_rates     = self::get_product_tax_rates( $offer_product_id );

		if ( empty( $discount_rule ) || empty( $discount_rule["discount"] ) ) {
			return apply_filters( "iconic_wsb_fbt_discount_mesage", false, $offer_product_id );
		}

		//show discount message only when no product is in cart
		$fbt_meta_products_ids = get_post_meta( $offer_product_id, "_iconic_wsb_product_page_order_bump_ids", true );

		if ( empty( $fbt_meta_products_ids ) ) {
			return apply_filters( "iconic_wsb_fbt_discount_mesage", false, $offer_product_id );
		}

		$not_in_bundle = false;

		foreach ( $fbt_meta_products_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			// If this product is not purchasable, or already in the cart.
			if ( ! $product->is_in_stock() || ! $product->is_purchasable() || ( apply_filters( 'iconic_wsb_remove_products', true ) && Iconic_WSB_Cart::get_cart_item_by_product_id( $product_id ) ) ) {
				$not_in_bundle = true;
				break;
			}
		}

		if ( $not_in_bundle ) {
			return apply_filters( 'iconic_wsb_fbt_discount_mesage', false, $offer_product_id );
		}

		/*
		Use tax excluded rate when:
		1. If prices are added including tax and 'Display prices in the shop' setting is set to 'Excluding tax'.
		2. OR $tax_rates are empty as we do not charge any tax from a customer whose state/country doesn't have a tax_rate set.
		*/
		$discount_amount = isset( $discount_rule['discount_exc_tax'] ) && ( 'excl' === get_option( 'woocommerce_tax_display_shop' ) || empty( $tax_rates ) ) ? $discount_rule['discount_exc_tax'] : $discount_rule['discount'];
		$discount        = 'percentage' === $discount_rule['type'] ? $discount_rule['discount'] . '%' : strip_tags( wc_price( $discount_amount ) );
		$message         = sprintf( __( 'Save %s when bought together', 'iconic-wsb' ), $discount );

		return apply_filters( 'iconic_wsb_fbt_discount_mesage', $message, $offer_product_id );
	}

	/**
	 * Returns all possible combination of attributes for those attributes which do not have any specific value.
	 *
	 * @param WC_Product_Variation $product
	 *
	 * @return array|void
	 */
	public static function get_variation_any_attributes( $product ) {
		if ( is_a( $product, "WC_Product_Variation" ) ) {
			$attributes = $product->get_variation_attributes();

			//get all the terms for all the attributes which have 'any'
			$blank_attributes = array();
			foreach ( $attributes as $attribute_key => $attribute_val ) {
				if ( ! $attribute_val ) {
					$blank_attributes[ $attribute_key ] = get_terms( array(
						'taxonomy' => substr( $attribute_key, 10 ),
						'fields'   => 'id=>name',
					) );
				}
			}

			$cartesian = wc_array_cartesian( $blank_attributes );

			return $cartesian;
		}
	}

	/**
	 * Completes the atribute data by appending $any_attributes attributes with
	 * specific attributes (the attributes with specific values) for the given
	 * variation $product. This data is later used as 4th param to add_to_cart().
	 *
	 *
	 * @param WC_Product_Variation $product
	 * @param arr                  $any_attributes
	 *
	 * @return array
	 */
	public static function get_variation_dropdown_option_attributes( $product, $any_attributes ) {
		if ( is_a( $product, "WC_Product_Variation" ) ) {
			$specific_attributes = array();
			$final_attributes    = array();
			$product_attributes  = $product->get_variation_attributes();
			if ( $product_attributes ) {
				foreach ( $product_attributes as $attribute_key => $attribute_value ) {
					if ( $attribute_value ) {
						$specific_attributes[ $attribute_key ] = $attribute_value;
					}
				}
				//
				$final_attributes = array_merge( $specific_attributes, $any_attributes );
			}

			return $final_attributes;
		}
	}

	/**
	 * Returns placeholder for the FBT variation product.
	 *
	 * @param WC_Product_Variation $product
	 *
	 * @return string
	 */
	public static function get_variation_dropdown_placeholder( $product ) {
		$attributes = $product->get_variation_attributes();
		$taxonomies = array();

		foreach ( $attributes as $attribute_key => $attribute_val ) {
			//only for those attributes which have 'any' value
			if ( ! $attribute_val ) {
				$attribute_key = substr( $attribute_key, 10 ); //remove "attribute_"
				$taxonomy      = get_taxonomy( $attribute_key );

				if ( $taxonomy ) {
					$taxonomies[] = $taxonomy->labels->singular_name;
				}
			}
		}

		$taxonomies = array_reverse( $taxonomies );

		return implode( ' - ', $taxonomies );
	}

	/**
	 * Get price for the given product
	 *
	 * @param object|int $product
	 *
	 * @return string
	 */
	public static function get_price_html( $product ) {
		$product         = is_object( $product ) ? $product : wc_get_product( $product );
		$has_price_range = self::has_price_range( $product );
		$prefix          = $has_price_range ? sprintf( '%s ', wc_get_price_html_from_text() ) : '';
		$price           = wc_get_price_to_display( $product );
		$price           = apply_filters( "iconic_wsb_fbt_product_price", $price, $product );

		return $prefix . wc_price( $price );
	}

	/**
	 * Get price html for a collection of product bumps.
	 *
	 * @param array $product_ids       Price to be calculated for these product IDs.
	 * @param int   $offer_product_id  Offer product ID.
	 *
	 * @return string
	 */
	public static function get_price_html_for_bumps( $product_ids, $offer_product_id ) {
		$total               = self::calculate_product_total( $product_ids );
		$has_price_range     = self::has_price_range( $product_ids );
		$prefix              = $has_price_range ? sprintf( '%s ', wc_get_price_html_from_text() ) : '';
		$discount_applicable = self::validate_cart_discount( $offer_product_id, $product_ids );

		if ( $discount_applicable ) {
			$tax_rates     = self::get_product_tax_rates( $offer_product_id );
			$exclude_tax   = empty( $tax_rates ) ? true : false;

			$discount   = self::calculate_discount( $offer_product_id, $product_ids, $exclude_tax );
			$price_html = $discount ? $prefix . wc_format_sale_price( $total, $total - $discount ) : $prefix . wc_price( $total );
		} else {
			$price_html = $prefix . wc_price( $total );
		}

		return $price_html;
	}

	/**
	 * Get tax rate for given product.
	 *
	 * @param int $product_id Product Id.
	 *
	 * @return array
	 */
	public static function get_product_tax_rates( $product_id ) {
		$cache_key = sprintf( 'iconic_wsb_product_tax_rates_%d', $product_id );
		$tax_rates = wp_cache_get( $cache_key );

		if ( $tax_rates ) {
			return $tax_rates;
		}

		$product   = wc_get_product( $product_id );
		$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
		wp_cache_set( $cache_key, $tax_rates );

		return $tax_rates;
	}
}
