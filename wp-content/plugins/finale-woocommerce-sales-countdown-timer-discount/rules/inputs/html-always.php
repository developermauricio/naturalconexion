<?php
defined( 'ABSPATH' ) || exit;

class WCCT_Input_Html_Always {
	public function __construct() {
		// vars
		$this->type = 'Html_Always';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		_e( 'Campaign will always display for all visitors on your site.', 'finale-woocommerce-sales-countdown-timer-discount' );
	}

}
