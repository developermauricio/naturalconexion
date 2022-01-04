<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once 'abstracts/class-order-bump-checkout-abstract.php';

/**
 * Iconic_WSB_Order_Bump_Checkout.
 *
 * @class    Iconic_WSB_Order_Bump_Checkout
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Order_Bump_At_Checkout extends Iconic_WSB_Order_Bump_Checkout_Abstract {
	/**
	 * Iconic_WSB_Order_Bump_Checkout constructor.
	 *
	 * @param int $bump_id
	 *
	 * @throws Exception
	 */
	public function __construct( $bump_id ) {
		parent::__construct( $bump_id, Iconic_WSB_Order_Bump_At_Checkout_Manager::get_instance()->get_post_type() );
	}

	/**
	 * Get bump image
	 *
	 * @param array $size
	 *
	 * @return array|bool|false|string
	 */
	public function get_offer_image_src( $size = [ 100, 100 ] ) {
		$image = false;

		if ( $this->get_custom_image_id( 0 ) > 0 ) {
			$image = wp_get_attachment_image_url( $this->get_custom_image_id(), $size );
		}

		if ( ! $image ) {
			$offer_product = wc_get_product( $this->get_product_offer() );

			if ( $offer_product ) {
				$image = wp_get_attachment_image_url( $offer_product->get_image_id(), $size );
			}
		}

		return $image ? $image : wc_placeholder_img_src( $size );
	}

	/**
	 * @param $default
	 *
	 * @return int
	 */
	public function get_custom_image_id( $default = false ) {
		return $this->get_meta( 'custom_image_id', $default );
	}

	/**
	 * @param int $attachment_id
	 *
	 * @return bool|int
	 */
	public function set_custom_image_id( $attachment_id ) {
		return $this->update_meta( 'custom_image_id', intval( $attachment_id ) );
	}

	/**
	 * @param $default
	 *
	 * @return string
	 */
	public function get_checkbox_text( $default = false ) {
		return $this->get_meta( 'checkbox_text', $default );
	}

	/**
	 * @param string $checkbox_text
	 *
	 * @return bool|int
	 */
	public function set_checkbox_text( $checkbox_text ) {
		return $this->update_meta( 'checkbox_text', $checkbox_text );
	}

	/**
	 * @param $default
	 *
	 * @return string
	 */
	public function get_bump_description( $default = false ) {
		return $this->get_meta( 'bump_description', $default );
	}

	/**
	 * @param string $bump_description
	 *
	 * @return bool|int
	 */
	public function set_bump_description( $bump_description ) {
		return $this->update_meta( 'bump_description', $bump_description );
	}

	/**
	 * @inheritDoc
	 */
	public function is_suitable( $check_for_cart = true ) {
		if ( WC()->cart->is_empty() ) {
			return false;
		}

		if ( $check_for_cart && $this->is_in_cart() && ! self::in_cart_as_bump() ) {
			return false;
		}

		if ( ! $this->is_valid() ) {
			return false;
		}

		$display_for = $this->get_display_type();

		if ( 'specific' === $display_for ) {
			$needle_products = array_map( 'intval', $this->get_specific_products() );
			$condition       = $this->get_apply_when_specific();

			if ( 'all' === $condition ) {
				foreach ( $needle_products as $needle_product ) {
					if ( ! Iconic_WSB_Cart::is_product_in_cart( $needle_product ) ) {
						return false;
					}
				}

				return true;
			} else if ( 'any' === $condition ) {
				foreach ( $needle_products as $needle_product ) {
					if ( Iconic_WSB_Cart::is_product_in_cart( $needle_product ) ) {
						return true;
					}
				}

				return false;
			}
		}

		return true;
	}

	/**
	 * @param $default
	 *
	 * @return array
	 */
	public function get_render_settings( $default = false ) {
		$default = $default ? $default : array(
			'highlight_color' => '#333333',
			'border_color'    => '#E2E2E2',
			'border_style'    => 'solid',
			'show_image'      => 'yes',
			'show_shadow'     => 'yes',
			'show_price'      => 'yes',
			'position'        => 'woocommerce_review_order_before_submit',
		);

		return $this->get_meta( 'render_settings', $default );
	}

	/**
	 * @param array $render_settings
	 *
	 * @return bool|int
	 */
	public function set_render_settings( $render_settings ) {
		$defaults = array(
			'highlight_color' => '#333333',
			'border_color'    => '#E2E2E2',
			'border_style'    => 'solid',
			'show_image'      => 'yes',
			'show_shadow'     => 'yes',
			'show_price'      => 'yes',
			'position'        => 'woocommerce_review_order_before_submit',
		);

		$render_settings = wp_parse_args( $render_settings, $defaults );

		return $this->update_meta( 'render_settings', $render_settings );
	}

	/**
	 * Get count of click on order bump
	 *
	 * @param int $default
	 *
	 * @return int
	 */
	public function get_clicks_count( $default = 0 ) {
		return (int) $this->get_meta( 'clicks_count', $default );
	}

	/**
	 * Increase clicks count on $on value
	 *
	 * @param int $on
	 *
	 * @return bool|int
	 */
	public function increase_click_count( $on = 1 ) {
		return $this->set_clicks_count( $this->get_clicks_count() + $on );
	}

	/**
	 * Set clicks count
	 *
	 * @param int $count
	 *
	 * @return bool|int
	 */
	public function set_clicks_count( $count ) {
		return $this->update_meta( 'clicks_count', $count );
	}
}