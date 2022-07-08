<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_REST_Doppler_Controller {
	/**
	 * You can extend this class with
	 * WP_REST_Controller / WC_REST_Controller / WC_REST_Products_V2_Controller / WC_REST_CRUD_Controller etc.
	 * Found in packages/woocommerce-rest-api/src/Controllers/
	 */
	protected $namespace = 'wc/v3';

	protected $rest_base = 'abandoned';

	public function get_abandoned_carts( $data ) {
		return array( 'abandoned' => 'Data' );
	}

	public function register_routes() {
		die('registering rest route');
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'get_abandoned_carts' ),
			)
		);
	}
}