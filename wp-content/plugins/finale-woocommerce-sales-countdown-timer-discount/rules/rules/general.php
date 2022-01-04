<?php
defined( 'ABSPATH' ) || exit;

class WCCT_Rule_General_Always extends WCCT_Rule_Base {

	public function __construct() {
		parent::__construct( 'general_always' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_Always';
	}

	public function is_match( $rule_data, $product_id ) {
		return true;
	}

}




