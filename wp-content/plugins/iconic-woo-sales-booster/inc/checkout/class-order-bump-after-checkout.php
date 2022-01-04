<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once 'abstracts/class-order-bump-checkout-abstract.php';

/**
 * Iconic_WSB_Order_Bump_After_Checkout.
 *
 * @class    Iconic_WSB_Order_Bump_After_Checkout
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Order_Bump_After_Checkout extends Iconic_WSB_Order_Bump_Checkout_Abstract {
	/**
	 * Iconic_WSB_Order_Bump_After_Checkout constructor.
	 *
	 * @param int $bump_id
	 *
	 * @throws Exception
	 */
	public function __construct( $bump_id ) {
		parent::__construct( $bump_id, Iconic_WSB_Order_Bump_After_Checkout_Manager::get_instance()->get_post_type() );
	}

	/**
	 * @param bool $default
	 *
	 * @return bool
	 */
	public function need_show_progress_bar( $default = false ) {
		return $this->get_meta( 'show_progress_bar', $default ) === 'yes';
	}

	/**
	 * @param bool $show_progress_bar
	 *
	 * @return bool|int
	 */
	public function set_need_show_progress_bar( $show_progress_bar ) {
		if ( is_string( $show_progress_bar ) && in_array( $show_progress_bar, [ 'yes', 'no' ] ) ) {
			return $this->update_meta( 'show_progress_bar', $show_progress_bar );
		}

		if ( $show_progress_bar ) {
			return $this->update_meta( 'show_progress_bar', 'yes' );
		}

		return $this->update_meta( 'show_progress_bar', 'no' );
	}

	/**
	 *
	 *
	 * @inheritDoc
	 */
	public function is_suitable( $check_for_cart = true ) {
		if ( WC()->cart->is_empty() ) {
			return false;
		}
		if ( ( $check_for_cart && $this->is_in_cart() ) ) {
			return false;
		}

		if ( ! $this->is_valid() ) {
			return false;
		}

		$display_for = $this->get_display_type();

		if ( $display_for === 'specific' ) {
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
	 * @param bool $default
	 *
	 * @return string
	 */
	public function get_bump_title( $default = false ) {
		return $this->get_meta( 'bump_title', $default );
	}

	/**
	 * @param bool $bump_title
	 *
	 * @return string
	 */
	public function set_bump_title( $bump_title ) {
		return $this->update_meta( 'bump_title', $bump_title );
	}

	/**
	 * @param string $bump_subtitle
	 *
	 * @return string
	 */
	public function set_bump_subtitle( $bump_subtitle ) {
		return $this->update_meta( 'bump_subtitle', $bump_subtitle );
	}

	/**
	 * @param bool $default
	 *
	 * @return string
	 */
	public function get_bump_subtitle( $default = false ) {
		return $this->get_meta( 'bump_subtitle', $default );
	}

	/**
	 * @param string $product_intro
	 *
	 * @return string
	 */
	public function set_product_intro( $product_intro ) {
		return $this->update_meta( 'product_intro', $product_intro );
	}

	/**
	 * @param bool $default
	 *
	 * @return string
	 */
	public function get_product_intro( $default = false ) {
		return $this->get_meta( 'product_intro', $default );
	}

	/**
	 * @param array $product_benefits
	 *
	 * @return string
	 */
	public function set_product_benefits( $product_benefits ) {
		if ( ! array( $product_benefits ) ) {
			return false;
		}

		$product_benefits = array_filter( $product_benefits );

		return $this->update_meta( 'product_benefits', $product_benefits );
	}

	/**
	 * @param array $default
	 *
	 * @return array
	 */
	public function get_product_benefits( $default = [] ) {
		return $this->get_meta( 'product_benefits', $default );
	}

	/**
	 * @param string $button_text
	 *
	 * @return string
	 */
	public function set_button_text( $button_text ) {
		return $this->update_meta( 'button_text', $button_text );
	}

	/**
	 * @param bool $default
	 *
	 * @return string
	 */
	public function get_button_text( $default = false ) {
		return $this->get_meta( 'button_text', $default );
	}

	/**
	 * @param string $skip_text
	 *
	 * @return string
	 */
	public function set_skip_text( $skip_text ) {
		return $this->update_meta( 'skip_text', $skip_text );
	}

	/**
	 * @param bool $default
	 *
	 * @return string
	 */
	public function get_skip_text( $default = false ) {
		return $this->get_meta( 'skip_text', $default );
	}

	/**
	 * @param string $open_animation
	 *
	 * @return string
	 */
	public function set_open_animation( $open_animation ) {
		return $this->update_meta( 'open_animation', $open_animation );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function get_open_animation( $default = 'lightSpeedIn' ) {
		return $this->get_meta( 'open_animation', $default );
	}

	/**
	 * @param string $close_animation
	 *
	 * @return string
	 */
	public function set_close_animation( $close_animation ) {
		return $this->update_meta( 'close_animation', $close_animation );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function get_close_animation( $default = 'bounceOutDown' ) {
		return $this->get_meta( 'close_animation', $default );
	}
}