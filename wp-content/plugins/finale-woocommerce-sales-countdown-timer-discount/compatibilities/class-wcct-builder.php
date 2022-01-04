<?php
defined( 'ABSPATH' ) || exit;

class WCCT_remove_builder {
	private static $ins = null;
	private $post_type = 'wcct_countdown';

	public function __construct() {

		add_action( 'admin_init', array( $this, 'actions' ), 3 );
		add_filter( 'fl_builder_post_types', array( $this, 'fl_builder_post_types' ) );
		add_filter( 'vc_check_post_type_validation', array( $this, 'vc_check_post_type_validation' ), 10, 2 );
		add_filter( 'et_builder_post_types', array( $this, 'divi_builder_post_types' ), 10, 1 );

	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function actions() {
		remove_post_type_support( $this->post_type, 'elementor' );
	}

	public function fl_builder_post_types( $post_types ) {
		$index = array_search( $this->post_type, $post_types );
		if ( $index ) {
			unset( $post_types[ $index ] );
		}

		return $post_types;
	}


	public function vc_check_post_type_validation( $roles, $type ) {
		if ( $this->post_type === $type ) {
			return false;
		}

		return $roles;
	}

	public function divi_builder_post_types( $post_types ) {

		$index = array_search( $this->post_type, $post_types );
		if ( $index ) {
			unset( $post_types[ $index ] );
		}

		return $post_types;
	}
}

WCCT_remove_builder::get_instance();
