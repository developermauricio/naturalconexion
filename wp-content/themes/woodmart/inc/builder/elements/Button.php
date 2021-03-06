<?php if ( ! defined('WOODMART_THEME_DIR')) exit('No direct script access allowed');

/**
 * ------------------------------------------------------------------------------------------------
 *	WPBakery Button element
 * ------------------------------------------------------------------------------------------------
 */

if( ! class_exists( 'WOODMART_HB_Button' ) ) {
	class WOODMART_HB_Button extends WOODMART_HB_Element {

		public function __construct() {

			$this->args = array(
				'text' => esc_html__( 'Button with link', 'woodmart' ), 
				'icon' => WOODMART_ASSETS_IMAGES . '/header-builder/icons/hb-ico-button.svg',
			);

			$this->exclude_list = array(
				'wd_animation',
				'wd_animation_delay',
				'wd_animation_duration',
				'collapsible_content_divider',
				'wd_button_collapsible_content',
			);

			$this->vc_element = 'woodmart_button';
			parent::__construct();
			$this->template_name = 'button';
		}

		public function map() {}
	}
}
