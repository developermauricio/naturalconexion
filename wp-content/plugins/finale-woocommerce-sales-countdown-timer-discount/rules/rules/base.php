<?php
defined( 'ABSPATH' ) || exit;

/**
 * Base class for a WCCT rule.
 */
class WCCT_Rule_Base {

	public function __construct( $name ) {

	}

	/**
	 * Get's the list of possible values for the rule.
	 *
	 * Override to return the correct list of possible values for your rule object.
	 * @return array
	 */
	public function get_possible_rule_values() {
		return array();
	}

	/**
	 * Gets the list of possible rule operators available for this rule object.
	 *
	 * Override to return your own list of operators.
	 *
	 * @return array
	 */
	public function get_possible_rule_operators() {
		return array(
			'==' => __( 'is equal to', 'finale-woocommerce-sales-countdown-timer-discount' ),
			'!=' => __( 'is not equal to', 'finale-woocommerce-sales-countdown-timer-discount' ),
		);
	}

	/*
	 * Gets the input object type slug for this rule object.
	 *
	 * @return string
	 */

	public function get_condition_input_type() {
		return 'Select';
	}

	/**
	 * Checks if the conditions defined for this rule object have been met.
	 *
	 * @return boolean
	 */
	public function is_match( $rule_data, $product_id ) {
		return false;
	}

	/**
	 * Helper function to wrap the return value from is_match and apply filters or other modifications in sub classes.
	 *
	 * @param boolean $result The result that should be returned.
	 * @param array $rule_data The array config object for the current rule.
	 *
	 * @return boolean
	 */
	public function return_is_match( $result, $rule_data ) {
		return apply_filters( 'woocommerce_wcct_rule_is_match', $result, $rule_data );
	}

}
