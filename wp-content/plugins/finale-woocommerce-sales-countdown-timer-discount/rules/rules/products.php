<?php
defined( 'ABSPATH' ) || exit;


class WCCT_Rule_Product_Select extends WCCT_Rule_Base {

	protected static $parent_id = array();

	public function __construct() {
		parent::__construct( 'product_select' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Product_Select';
	}

	public function is_match( $rule_data, $product_id ) {
		$result = false;

		if ( $product_id && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {
			$prod_id = $product_id;

			/**
			 * Making rules compatible with the WPML
			 * Providing the user to enter products in the Base Language
			 * Trying & getting the base language translation post to validate the rule
			 *
			 */
			if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {

				if ( isset( self::$parent_id[ $prod_id ] ) ) {
					$get_parent_id = self::$parent_id[ $prod_id ];
				} else {
					$get_parent_id               = get_post_meta( $prod_id, '_icl_lang_duplicate_of', true );
					self::$parent_id[ $prod_id ] = $get_parent_id;
				}

				if ( $get_parent_id && '' !== $get_parent_id ) {
					$prod_id = $get_parent_id;
				} elseif ( version_compare( ICL_SITEPRESS_VERSION, '3.2' ) > 0 ) {
					$prod_id = apply_filters( 'wpml_object_id', $prod_id, 'product', false, ICL_LANGUAGE_CODE );
				} else {
					$prod_id = icl_object_id( $prod_id, 'product', false, ICL_LANGUAGE_CODE );
				}
			}


			/**
			 *  making rule compatible with polylang
			 *  providing the user to enter products in the base language
			 */
			if ( function_exists( 'pll_current_language' ) ) {
				$current_language = pll_current_language();
				$prod_id          = pll_get_post( $prod_id, $current_language );
			}

			$in     = in_array( $prod_id, $rule_data['condition'] );
			$result = 'in' === $rule_data['operator'] ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Product_Type extends WCCT_Rule_Base {

	protected static $product_type = array();

	public function __construct() {
		parent::__construct( 'product_type' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$terms = get_terms( 'product_type', array(
			'hide_empty' => false,
		) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $product_id ) {
		$result = false;

		if ( 0 === $product_id ) {
			return $this->return_is_match( $result, $rule_data );
		}

		if ( $product_id && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			if ( ! isset( self::$product_type[ $product_id ] ) ) {

				$product_terms = get_post_meta( $product_id, '_wcct_product_taxonomy_term_ids', true );

				if ( is_array( $product_terms ) && isset( $product_terms['type'] ) ) {
					self::$product_type[ $product_id ] = $product_terms['type'];
				} else {
					if ( ! is_array( $product_terms ) ) {
						$product_terms = array();
					}

					$product_terms['type'] = wp_get_post_terms( $product_id, 'product_type', array(
						'fields' => 'ids',
					) );

					update_post_meta( $product_id, '_wcct_product_taxonomy_term_ids', $product_terms );

					self::$product_type[ $product_id ] = $product_terms['type'];
				}
			}

			$product_types = self::$product_type[ $product_id ];
			$in            = count( array_intersect( $product_types, $rule_data['condition'] ) ) > 0;
			$result        = 'in' === $rule_data['operator'] ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Product_Category extends WCCT_Rule_Base {

	protected static $product_cat = array();

	public function __construct() {
		parent::__construct( 'product_category' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$terms = get_terms( 'product_cat', array(
			'hide_empty' => false,
		) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $product_id ) {
		$result = false;

		if ( $product_id && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			if ( ! isset( self::$product_cat[ $product_id ] ) ) {

				$product_terms = get_post_meta( $product_id, '_wcct_product_taxonomy_term_ids', true );

				if ( is_array( $product_terms ) && isset( $product_terms['category'] ) ) {
					self::$product_cat[ $product_id ] = $product_terms['category'];
				} else {
					if ( ! is_array( $product_terms ) ) {
						$product_terms = array();
					}

					$product_terms['category'] = wp_get_post_terms( $product_id, 'product_cat', array(
						'fields' => 'ids',
					) );

					update_post_meta( $product_id, '_wcct_product_taxonomy_term_ids', $product_terms );

					self::$product_cat[ $product_id ] = $product_terms['category'];
				}
			}

			$product_cat = self::$product_cat[ $product_id ];
			$in          = count( array_intersect( $product_cat, $rule_data['condition'] ) ) > 0;
			$result      = 'in' === $rule_data['operator'] ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Product_Attribute extends WCCT_Rule_Base {

	protected static $product_term = array();

	public function __construct() {
		parent::__construct( 'product_attribute' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'has', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'does not have', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result               = array();
		$attribute_taxonomies = WCCT_Compatibility::wc_get_attribute_taxonomies();

		if ( $attribute_taxonomies ) {

			foreach ( $attribute_taxonomies as $tax ) {
				$attribute_taxonomy_name = WCCT_Compatibility::wc_attribute_taxonomy_name( $tax->attribute_name );
				if ( taxonomy_exists( $attribute_taxonomy_name ) ) {
					$terms = get_terms( $attribute_taxonomy_name, 'orderby=name&hide_empty=0' );
					if ( $terms ) {
						foreach ( $terms as $term ) {
							$result[ $attribute_taxonomy_name . '|' . $term->term_id ] = $tax->attribute_name . ': ' . $term->name;
						}
					}
				}
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function sort_attribute_taxonomies( $taxa, $taxb ) {
		return strcmp( $taxa->attribute_name, $taxb->attribute_name );
	}

	public function is_match( $rule_data, $product_id ) {
		$result = false;

		if ( $product_id && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			$product_terms = get_post_meta( $product_id, '_wcct_product_taxonomy_term_ids', true );

			foreach ( $rule_data['condition'] as $condition ) {
				$term_data               = explode( '|', $condition );
				$attribute_taxonomy_name = $term_data[0];
				$term_id                 = $term_data[1];

				if ( ! isset( self::$product_term[ $product_id ] ) ) {

					if ( is_array( $product_terms ) && isset( $product_terms[ $attribute_taxonomy_name ] ) ) {
						self::$product_term[ $product_id ] = $product_terms[ $attribute_taxonomy_name ];
					} else {
						if ( ! is_array( $product_terms ) ) {
							$product_terms = array();
						}

						$product_terms[ $attribute_taxonomy_name ] = wp_get_post_terms( $product_id, $attribute_taxonomy_name, array(
							'fields' => 'ids',
						) );

						update_post_meta( $product_id, '_wcct_product_taxonomy_term_ids', $product_terms );

						self::$product_term[ $product_id ] = $product_terms[ $attribute_taxonomy_name ];
					}
				}

				$post_terms = self::$product_term[ $product_id ];
				$in         = in_array( $term_id, $post_terms );
				$result     = 'in' === $rule_data['operator'] ? $in : ! $in;
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Product_Price extends WCCT_Rule_Base {

	protected static $product = array();
	protected static $child = array();

	public function __construct() {
		parent::__construct( 'product_price' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'==' => __( 'is equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'!=' => __( 'is not equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'>'  => __( 'is greater than', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'<'  => __( 'is less than', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'>=' => __( 'is greater or equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'=<' => __( 'is less or equal to', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_condition_input_type() {
		return 'Text';
	}

	public function is_match( $rule_data, $product_id ) {

		global $woocommerce;
		$result = false;

		if ( isset( self::$product[ $product_id ] ) ) {
			$product = self::$product[ $product_id ];
		} else {
			$product = wc_get_product( $product_id );

			self::$product[ $product_id ] = $product;
		}

		if ( $product && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) && '' !== $rule_data['condition'] ) {
			$value = (float) $rule_data['condition'];

			if ( 'grouped' === $product->get_type() ) {
				foreach ( $product->get_children() as $child_id ) {

					if ( isset( self::$child[ $child_id ] ) ) {
						$child = self::$child[ $child_id ];
					} else {
						$child = wc_get_product( $child_id );

						self::$child[ $child_id ] = $child;
					}

					if ( '' !== $child->get_price() ) {
						if ( version_compare( $woocommerce->version, '3.0.0', '>=' ) ) {
							$child_prices[] = wc_get_price_excluding_tax( $child );

						} else {
							$child_prices[] = $child->get_price_excluding_tax();
						}
					}
				}

				if ( ! empty( $child_prices ) ) {
					$min = min( $child_prices );
					$max = max( $child_prices );
				} else {
					$min = '';
					$max = '';
				}

				switch ( $rule_data['operator'] ) {
					case '==':
						$result = ( $min <= $value && $value <= $max );
						break;
					case '!=':
						$result = ( ! ( $min <= $value && $value <= $max ) );
						break;
					case '>':
						//check if is range
						if ( ( $min <= $value && $value < $max ) ) {
							$result = true;
						} else {
							if ( $min > $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}
						break;
					case '<':
						//check if is range
						if ( ( $min < $value && $value <= $max ) ) {
							$result = true;
						} else {
							if ( $max < $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}
						break;
					case '>=':
						if ( ( $min <= $value && $value <= $max ) ) {
							$result = true;
						} else {
							if ( $min >= $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}
						break;
					case '=<':
						if ( ( $min <= $value && $value <= $max ) ) {
							$result = true;
						} else {
							if ( $max < $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}
						break;
					default:
						$result = false;
						break;
				}
			} elseif ( $product->get_type() === 'variable' ) {
				$prices = $product->get_variation_prices();
				$min    = (float) current( $prices['price'] );
				$max    = (float) end( $prices['price'] );
				switch ( $rule_data['operator'] ) {
					case '==':
						$result = ( $min <= $value && $value <= $max );
						break;
					case '!=':
						$result = ( ! ( $min <= $value && $value <= $max ) );
						break;
					case '>':
						//check if is range
						if ( ( $min <= $value && $value < $max ) ) {
							$result = true;
						} else {
							if ( $min > $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}
						break;
					case '<':
						//check if is range
						if ( ( $min < $value && $value <= $max ) ) {
							$result = true;
						} else {
							if ( $max < $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}
						break;
					case '>=':
						if ( ( $min <= $value && $value <= $max ) ) {
							$result = true;
						} else {
							if ( $min >= $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}
						break;
					case '=<':
						if ( ( $min <= $value && $value <= $max ) ) {
							$result = true;
						} else {
							if ( $max < $value ) {
								$result = true;
							} else {
								$result = false;
							}
						}
						break;
					default:
						$result = false;
						break;
				}
			} else {
				$price = (float) $product->get_price();
				switch ( $rule_data['operator'] ) {
					case '==':
						$result = $price == $value;
						break;
					case '!=':
						$result = $price != $value;
						break;
					case '>':
						$result = $price > $value;
						break;
					case '<':
						$result = $price < $value;
						break;
					case '=<':
						$result = $price <= $value;
						break;
					case '>=':
						$result = $price >= $value;
						break;
					default:
						$result = false;
						break;
				}
			}
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_Product_Tags extends WCCT_Rule_Base {

	protected static $product_term = array();

	public function __construct() {
		parent::__construct( 'product_tags' );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();

		$terms = get_terms( 'product_tag', array(
			'hide_empty' => false,
		) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $product_id ) {
		$result = false;

		if ( $product_id && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			if ( ! isset( self::$product_term[ $product_id ] ) ) {

				$product_terms = get_post_meta( $product_id, '_wcct_product_taxonomy_term_ids', true );

				if ( is_array( $product_terms ) && isset( $product_terms['tag'] ) && ! empty( $product_terms['tag'] ) ) {
					self::$product_term[ $product_id ] = $product_terms['tag'];
				} else {
					if ( ! is_array( $product_terms ) ) {
						$product_terms = array();
					}

					$product_terms['tag'] = wp_get_post_terms( $product_id, 'product_tag', array(
						'fields' => 'ids',
					) );

					update_post_meta( $product_id, '_wcct_product_taxonomy_term_ids', $product_terms );

					self::$product_term[ $product_id ] = $product_terms['tag'];
				}
			}

			$product_tag = self::$product_term[ $product_id ];
			$in          = count( array_intersect( $product_tag, $rule_data['condition'] ) ) > 0;
			$result      = 'in' === $rule_data['operator'] ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}

class WCCT_Rule_WCCT_Product_Tax extends WCCT_Rule_Base {
	public $tax_slug;
	protected static $product_type = array();

	public function __construct( $product_tax ) {
		$this->tax_slug = $product_tax;
		parent::__construct( $this->tax_slug );
	}

	public function get_possible_rule_operators() {
		$operators = array(
			'in'    => __( 'is', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
			'notin' => __( 'is not', 'finale-woocommerce-sales-countdown-timer-discount-plugin' ),
		);

		return $operators;
	}

	public function get_possible_rule_values() {
		$result = array();
		$terms  = get_terms( $this->tax_slug, array(
			'hide_empty' => false,
		) );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$result[ $term->term_id ] = $term->name;
			}
		}

		return $result;
	}

	public function get_condition_input_type() {
		return 'Chosen_Select';
	}

	public function is_match( $rule_data, $product_id ) {
		$result = false;

		if ( $product_id && isset( $rule_data['condition'] ) && isset( $rule_data['operator'] ) ) {

			if ( ! isset( self::$product_type[ $product_id ] ) ) {

				$product_terms = get_post_meta( $product_id, '_wcct_product_taxonomy_term_ids', true );

				if ( is_array( $product_terms ) && isset( $product_terms[ $this->tax_slug ] ) ) {
					self::$product_type[ $product_id ] = $product_terms[ $this->tax_slug ];
				} else {
					if ( ! is_array( $product_terms ) ) {
						$product_terms = array();
					}

					$product_terms[ $this->tax_slug ] = wp_get_post_terms( $product_id, $this->tax_slug, array(
						'fields' => 'ids',
					) );

					update_post_meta( $product_id, '_wcct_product_taxonomy_term_ids', $product_terms );

					self::$product_type[ $product_id ] = $product_terms[ $this->tax_slug ];
				}
			}

			$product_types = self::$product_type[ $product_id ];
			$in            = count( array_intersect( $product_types, $rule_data['condition'] ) ) > 0;
			$result        = 'in' === $rule_data['operator'] ? $in : ! $in;
		}

		return $this->return_is_match( $result, $rule_data );
	}

}
