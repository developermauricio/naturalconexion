<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class WCCT_discount
 * @package Finale-Lite
 * @author XlPlugins
 */
class WCCT_discount {

	public static $_instance = null;
	public $excluded = array();
	public $is_wc_calculating = false;
	private $percentage = false;

	public function __construct() {

		global $woocommerce;

		if ( version_compare( $woocommerce->version, 3.0, '>=' ) ) {

			add_filter( 'woocommerce_product_get_price', array( $this, 'wcct_trigger_get_price' ), 999, 2 );
			add_filter( 'woocommerce_product_get_sale_price', array( $this, 'wcct_trigger_get_sale_price' ), 999, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'wcct_trigger_get_price' ), 999, 2 );
			add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'wcct_trigger_get_sale_price' ), 999, 2 );
			add_filter( 'woocommerce_product_get_date_on_sale_from', array( $this, 'wcct_date_on_sale_from' ), 999, 2 );
			add_filter( 'woocommerce_product_variation_get_date_on_sale_from', array( $this, 'wcct_date_on_sale_from' ), 999, 2 );
			add_filter( 'woocommerce_product_get_date_on_sale_to', array( $this, 'wcct_date_on_sale_to' ), 999, 2 );
			add_filter( 'woocommerce_product_variation_get_date_on_sale_to', array( $this, 'wcct_date_on_sale_to' ), 999, 2 );
		} else {
			add_filter( 'woocommerce_get_price', array( $this, 'wcct_trigger_get_price' ), 10, 2 );
			add_filter( 'woocommerce_get_sale_price', array( $this, 'wcct_trigger_get_sale_price' ), 999, 2 );
		}

		/**
		 * For variation products we need to handle case where we mark variable product as it is on sale
		 */
		add_filter( 'woocommerce_product_is_on_sale', array( $this, 'wcct_maybe_mark_product_having_sale' ), 999, 2 );

		/**
		 * modify price ranges for variable products
		 */
		add_filter( 'woocommerce_variation_prices', array( $this, 'wcct_change_price_ranges' ), 900, 3 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'maybe_flag_running_calculations' ) );
		add_action( 'woocommerce_after_calculate_totals', array( $this, 'maybe_unflag_running_calculations' ) );

		/**
		 * Need to modify the variation hash in order to let the woocommerce display the correct and modified variation
		 * Commented in 2.6.1 as causing transients creation every time on page load, so DB flood.
		 */
		add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'maybe_modify_variation_price_hash' ), 999, 3 );
	}

	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * @param $get_price
	 * @param $product_global
	 *
	 * @return bool|float|int|mixed|void
	 */
	public function wcct_trigger_get_price( $get_price, $product_global ) {
		if ( ! $product_global instanceof WC_Product ) {
			return $get_price;
		}

		$is_skip = apply_filters( 'wcct_skip_discounts', false, $get_price, $product_global );

		wcct_force_log( "Product id {$product_global->get_id()} : " . __FUNCTION__ . ' Before Price: ' . $get_price );

		if ( '' === $get_price ) {
			return $get_price;
		}

		if ( true === $is_skip ) {
			return $get_price;
		}

		if ( in_array( $product_global->get_type(), WCCT_Common::get_sale_compatible_league_product_types(), true ) ) {
			$get_price = $this->wcct_trigger_create_price( $get_price, $product_global );
		}
		wcct_force_log( "Product id {$product_global->get_id()} : " . __FUNCTION__ . ' After Price: ' . $get_price );

		return $get_price;
	}

	public function wcct_trigger_create_price( $sale_price, $product_global, $mode = 'basic', $regular_price = false, $parent_product = 0 ) {
		/**
		 * Here we are handling the case of all the hooks works while variable price range is getting generated
		 * We always pass $regular_price in that case
		 * so for the regular price we have, we do not need to generate any product variation object
		 * In that way we can easily by pass creating an object & the queries associated with that.
		 */

		if ( false === $regular_price ) {
			$type              = $product_global->get_type();
			$parent_id         = WCCT_Core()->public->wcct_get_product_parent_id( $product_global );
			$product_global_id = $product_global->get_id();
		} else {
			$type              = 'variation';
			$product_global_id = $product_global;
			$parent_id         = $parent_product;
		}

		if ( WCCT_Core()->public->wcct_restrict_for_booking_oth( $parent_id, $type ) ) {
			return $sale_price;
		}

		$temp_id = $parent_id;
		$data    = WCCT_Core()->public->get_single_campaign_pro_data( $temp_id );

		if ( empty( $data ) ) {
			wcct_force_log( ' terminating ' . __FUNCTION__ . ' For Product' . $temp_id );

			return $sale_price;
		}

		if ( ! isset( $data['deals'] ) || ! is_array( $data['deals'] ) ) {
			return $sale_price;
		}

		WCCT_Core()->public->wcct_get_product_obj( $temp_id );
		$deals          = $data['deals'];
		$deals_override = $deals;

		if ( false === $regular_price ) {
			do_action( 'wcct_before_get_regular_price', $product_global );

			$regular_price = $product_global->get_regular_price();

			do_action( 'wcct_after_get_regular_price', $product_global );
		}

		if ( empty( $regular_price ) ) {
			return $sale_price;
		}

		if ( ! is_array( $deals_override ) ) {
			return $sale_price;
		}

		if ( $deals_override['override'] == 1 ) {

			$check_sale = get_post_meta( $product_global_id, '_sale_price', true ); // we are fetching sale price from db using get_post_meta otherwise will stick in loop
			$check_sale = apply_filters( 'wcct_discount_check_sale_price', $check_sale, $product_global );

			if ( $check_sale >= '0' ) {
				$sale_price = (float) $sale_price;

				return wc_format_decimal( $sale_price, wc_get_price_decimals() );
			}
		}

		$deal        = $deals_override;
		$deal_amount = (int) isset( $deal['deal_amount'] ) ? $deal['deal_amount'] : 0;

		if ( $deal_amount >= 0 ) {
			switch ( $deal['type'] ) {
				case 'percentage':
					$deal_amount = apply_filters( "wcct_deal_amount_percentage_{$type}", $deal_amount, $product_global, $data );

					if ( 'sale' === $mode && $deal_amount == '0' ) {
						return '';
					}
					$set_sale_price = $regular_price - ( $regular_price * ( $deal_amount / 100 ) );
					if ( $set_sale_price >= 0 ) {
						$sale_price = $set_sale_price;
					} else {
						$sale_price = 0;
					}
					break;
				case 'percentage_sale':
					$deal_amount = apply_filters( "wcct_deal_amount_percentage_{$type}", $deal_amount, $product_global, $data );
					if ( 'sale' === $mode && $deal_amount == '0' ) {
						return '';
					}
					if ( empty( $sale_price ) ) {
						$sale_price = $regular_price;
					}
					$set_sale_price = $sale_price - ( $sale_price * ( $deal_amount / 100 ) );
					if ( $set_sale_price >= 0 ) {
						$sale_price = $set_sale_price;
					} else {
						$sale_price = 0;
					}
					break;
				case 'fixed_sale':
					$deal_amount = apply_filters( "wcct_deal_amount_fixed_amount_{$type}", $deal_amount, $product_global, $data );
					if ( 'sale' === $mode && $deal_amount == '0' ) {
						return '';
					}
					if ( empty( $sale_price ) ) {
						$sale_price = $regular_price;
					}

					if ( false !== $this->percentage ) {
						$deal_amount = ( $deal_amount + ( $deal_amount * ( $this->percentage / 100 ) ) );
					}

					$set_sale_price = $sale_price - $deal_amount;
					if ( $set_sale_price >= 0 ) {
						$sale_price = $set_sale_price;
					} else {
						$sale_price = 0;
					}
					break;
				case 'fixed_price':
					$deal_amount = apply_filters( "wcct_deal_amount_fixed_amount_{$type}", $deal_amount, $product_global, $data );
					if ( 'sale' === $mode && $deal_amount == '0' ) {
						return '';
					}

					if ( false !== $this->percentage ) {
						$deal_amount = ( $deal_amount + ( $deal_amount * ( $this->percentage / 100 ) ) );
					}

					$set_sale_price = $regular_price - $deal_amount;
					if ( $set_sale_price >= 0 ) {
						$sale_price = $set_sale_price;
					} else {
						$sale_price = 0;
					}
					break;
			}
		} else {
			return wc_format_decimal( $sale_price, wc_get_price_decimals() );
		}

		$sale_price = apply_filters( 'wcct_finale_discounted_price', $sale_price, $regular_price, $product_global_id );

		return wc_format_decimal( $sale_price, wc_get_price_decimals() );
	}

	public function wcct_trigger_get_sale_price( $sale_price, $product_global ) {
		if ( ! $product_global instanceof WC_Product ) {
			return $sale_price;
		}

		$is_skip = apply_filters( 'wcct_skip_discounts', false, $sale_price, $product_global );

		if ( true === $is_skip ) {
			return $sale_price;
		}
		wcct_force_log( "Product id {$product_global->get_id()} : " . __FUNCTION__ . ' Before Price: ' . $sale_price );

		if ( in_array( $product_global->get_type(), WCCT_Common::get_sale_compatible_league_product_types(), true ) ) {
			$sale_price = $this->wcct_trigger_create_price( $sale_price, $product_global, 'sale' );
		}

		wcct_force_log( "Product id {$product_global->get_id()} : " . __FUNCTION__ . ' After Price: ' . $sale_price );

		return $sale_price;
	}

	public function wcct_trigger_create_sale_variation( $sale_price, $variation, $product_global ) {
		return $this->wcct_trigger_create_price( $sale_price, $variation, 'sale' );
	}

	public function wcct_date_on_sale_from( $sale_from_date, $product_global ) {

		$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product_global );

		if ( WCCT_Core()->public->wcct_restrict_for_booking_oth( $parent_id ) ) {
			return $sale_from_date;
		}

		$data = WCCT_Core()->public->get_single_campaign_pro_data( $parent_id );

		if ( isset( $data['deals'] ) && is_array( $data['deals'] ) && count( $data['deals'] ) > 0 ) {
			$deals = $data['deals'];
			if ( isset( $deals['override'] ) && $deals['override'] == '1' && is_object( $sale_from_date ) ) {
				return $sale_from_date;
			}

			$sale_start_date = (int) $data['deals']['start_time'];
			$timezone        = new DateTimeZone( WCCT_Common::wc_timezone_string() );

			if ( $sale_from_date instanceof WC_DateTime ) {
				$sale_from_date->setTimezone( $timezone );
				$sale_from_date->setTimestamp( $sale_start_date );
			} else {
				$sale_from_date = new WC_DateTime();
				$sale_from_date->setTimezone( $timezone );
				$sale_from_date->setTimestamp( $sale_start_date );
			}
		}

		return $sale_from_date;
	}

	public function wcct_date_on_sale_to( $sale_from_to, $product_global ) {

		$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product_global );
		if ( WCCT_Core()->public->wcct_restrict_for_booking_oth( $parent_id ) ) {
			return $sale_from_to;
		}
		$data = WCCT_Core()->public->get_single_campaign_pro_data( $parent_id );

		if ( isset( $data['deals'] ) && is_array( $data['deals'] ) && count( $data['deals'] ) > 0 ) {
			$deals = $data['deals'];
			if ( isset( $deals['override'] ) && $deals['override'] == '1' && is_object( $sale_from_to ) ) {
				return $sale_from_to;
			}

			$sale_end_date = (int) $data['deals']['end_time'];
			$timezone      = new DateTimeZone( WCCT_Common::wc_timezone_string() );
			if ( $sale_from_to instanceof WC_DateTime ) {
				$sale_from_to->setTimezone( $timezone );
				$sale_from_to->setTimestamp( $sale_end_date );
			} else {
				$sale_from_to = new WC_DateTime();
				$sale_from_to->setTimezone( $timezone );
				$sale_from_to->setTimestamp( $sale_end_date );

			}
		}

		return $sale_from_to;
	}

	/**
	 * @hooked over `woocommerce_get_variation_prices_hash`
	 * Added current time as unique key so that the variation prices comes to display with the discounts added by finale but not by the object caching (by WordPress)
	 *
	 * @param array $price_hash
	 * @param WC_Product $product
	 * @param boolean $display
	 *
	 * @return array
	 */
	public function maybe_modify_variation_price_hash( $price_hash, $product, $display ) {

		if ( false === $display ) {
			return $price_hash;
		}

		if ( ! $product instanceof WC_Product ) {
			return $price_hash;
		}

		$campaign = WCCT_Core()->public->get_single_campaign_pro_data( $product->get_id() );
		if ( empty( $campaign ) || ! isset( $campaign['deals'] ) || empty( $campaign['deals'] ) ) {
			return $price_hash;
		}

		unset( $campaign['deals']['start_time'] );
		unset( $campaign['deals']['end_time'] );
		$hash = md5( maybe_serialize( $campaign['deals'] ) );

		if ( is_array( $price_hash ) ) {
			$price_hash[] = $hash;
		} elseif ( empty( $price_hash ) ) {
			$price_hash = array( $hash );
		} else {
			$price_hash = array( $price_hash, $hash );
		}

		return $price_hash;
	}

	public function wcct_change_price_ranges( $price_ranges, $product, $display ) {
		if ( ! $product instanceof WC_Product ) {
			return $price_ranges;
		}
		$prices         = array();
		$regular_prices = array();

		/**
		 * Using the product object to get the tax.
		 * If different variations will have different tax then it will display the incorrect product range
		 */
		if ( $product->is_taxable() && ! wc_prices_include_tax() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
			$check_tax = wc_get_price_including_tax( $product, array(
				'qty'   => 1,
				'price' => 10, // Passing price as 10 to get the tax
			) );

			$this->percentage = ( $check_tax - 10 ) * 10; // Now minus 10 to get the actual tax percentage
		}

		if ( is_array( $price_ranges ) && count( $price_ranges ) > 0 ) {

			foreach ( $price_ranges['regular_price'] as $key => $val ) {
				$regular_prices[ $key ] = $val;
			}

			$prices = $this->wcct_set_variation_price( $price_ranges['price'], 'basic', $regular_prices, $product->get_id() );
		}

		$this->percentage = false;

		asort( $prices );
		asort( $regular_prices );

		$price_ranges = array(
			'price'         => $prices,
			'regular_price' => $regular_prices,
			'sale_price'    => $prices,
		);

		return $price_ranges;
	}

	public function wcct_set_variation_price( $input, $type = 'basic', $regular_price = false, $parent_product = 0 ) {
		if ( is_array( $input ) ) {

			foreach ( $input as $k => $price ) {

				$is_skip = apply_filters( 'wcct_skip_discounts', false, $price, $k );

				if ( true === $is_skip ) {
					$input[ $k ] = $price;
					continue;
				}
				//formatting the prices as per WC is doing so that further comparison can take place between reg price and sale price to detect is on sale

				$input[ $k ] = $this->wcct_trigger_create_price( $price, $k, $type, ( false !== $regular_price ) ? $regular_price[ $k ] : false, $parent_product );
			}
		}

		return $input;
	}

	public function wcct_maybe_mark_product_having_sale( $bool, $product ) {
		if ( ! $product instanceof WC_Product ) {
			return $bool;
		}
		if ( in_array( $product->get_type(), WCCT_Common::get_variable_league_product_types(), true ) ) {

			$price_ranges   = $product->get_variation_prices();
			$prices         = array();
			$regular_prices = array();

			if ( is_array( $price_ranges ) && count( $price_ranges ) > 0 ) {

				foreach ( $price_ranges['regular_price'] as $key => $val ) {
					$regular_prices[ $key ] = $val;

				}

				$prices = $this->wcct_set_variation_price( $price_ranges['price'], 'basic', $regular_prices, $product->get_id() );
			}
			asort( $prices );
			asort( $regular_prices );

			$price_ranges = array(
				'price'         => $prices,
				'regular_price' => $regular_prices,
				'sale_price'    => $prices,
			);

			if ( is_array( $price_ranges['regular_price'] ) && ! empty( $price_ranges['regular_price'] ) ) {
				$bool = false;
				foreach ( $price_ranges['regular_price'] as $id => $price ) {
					if ( $price_ranges['sale_price'][ $id ] != $price && $price_ranges['sale_price'][ $id ] == $price_ranges['price'][ $id ] ) {
						$bool = true;
					}
				}
			}

			return $bool;
		} else {
			$price     = $product->get_price();
			$reg_price = $product->get_regular_price();

			if ( '' !== (string) $price && $reg_price > $price ) {
				$bool = true;
			}
		}

		return $bool;
	}

	public function maybe_flag_running_calculations() {

		$this->is_wc_calculating = true;
	}

	public function maybe_unflag_running_calculations() {
		$this->is_wc_calculating = false;
	}


}

if ( class_exists( 'WCCT_Core' ) ) {
	WCCT_Core::register( 'discount', 'WCCT_discount' );
}
