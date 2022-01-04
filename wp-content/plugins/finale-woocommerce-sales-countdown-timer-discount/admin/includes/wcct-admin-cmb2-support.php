<?php
defined( 'ABSPATH' ) || exit;

class WCCT_Admin_CMB2_Support {

	/**
	 * Callback function for groups
	 *
	 * @param $field_args CMB2 Field args
	 * @param $field
	 */
	public static function cmb2_wcct_before_call( $field_args, $field ) {
		$attributes = '';
		if ( ( $field_args['id'] == '_wcct_events' ) ) {
			$class_single = '';
			foreach ( $field_args['attributes'] as $attr => $val ) {
				if ( $attr == 'class' ) {
					$class_single .= ' ' . $val;
				}
				// if data attribute, use single quote wraps, else double
				$quotes     = false !== stripos( $attr, 'data-' ) ? "'" : '"';
				$attributes .= sprintf( ' %1$s=%3$s%2$s%3$s', $attr, $val, $quotes );
			}
			echo '<div class="wcct_custom_wrapper_group' . $class_single . '" ' . $attributes . '>';
		}
	}


	/**
	 * Output a message if the current page has the id of "2" (the about page)
	 *
	 * @param  object $field_args Current field args
	 * @param  object $field Current field object
	 */
	public static function cmb_after_row_cb( $field_args, $field ) {
		echo '</div></div>';
	}

	/**
	 * Output a message if the current page has the id of "2" (the about page)
	 *
	 * @param  object $field_args Current field args
	 * @param  object $field Current field object
	 */
	public static function cmb_before_row_cb( $field_args, $field ) {
		$default = array(
			'wcct_accordion_title'     => __( 'Untitled', 'finale-woocommerce-sales-countdown-timer-discount' ),
			'wcct_is_accordion_opened' => false,
		);

		$field_args = wp_parse_args( $field_args, $default );

		$is_active       = ( $field_args['wcct_is_accordion_opened'] ) ? 'active' : '';
		$is_display_none = ( ! $field_args['wcct_is_accordion_opened'] ) ? "style='display:none'" : '';
		echo '<div class="cmb2_wcct_wrapper_ac" data-slug="' . sanitize_title( $field_args['wcct_accordion_title'] ) . '" ><div class="cmb2_wcct_acc_head ' . $is_active . ' "><a href="javascript:void(0);">' . $field_args['wcct_accordion_title'] . '</a> <div class="toggleArrow"></div></div><div class="cmb2_wcct_wrapper_ac_data" ' . $is_display_none . '>';
	}

	/**
	 * Hooked over `xl_cmb2_add_conditional_script_page` so that we can load conditional logic scripts
	 *
	 * @param $options Pages
	 *
	 * @return mixed
	 */
	public static function wcct_push_support_form_cmb_conditionals( $pages ) {

		return $pages;
	}

	public static function row_classes_inline_desc( $field_args, $field ) {
		return array( 'wcct_field_inline_desc' );
	}

	public static function row_date_classes( $field_args, $field ) {
		return array( 'wcct_field_date_range' );
	}

	public static function render_trigger_nav() {
		$get_campaign_statuses = apply_filters( 'wcct_admin_trigger_nav', WCCT_Common::get_campaign_statuses() );
		$html                  = '<ul class="subsubsub subsubsub_wcct">';
		$html_inside           = array();
		$html_inside[]         = sprintf( '<li><a href="%s" class="%s">%s</a></li>', admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=all' ), self::active_class( 'all' ), __( 'All', 'finale-woocommerce-sales-countdown-timer-discount' ) );
		foreach ( $get_campaign_statuses as $status ) {
			$html_inside[] = sprintf( '<li><a href="%s" class="%s">%s</a></li>', admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=' . $status['slug'] ), self::active_class( $status['slug'] ), $status['name'] );
		}

		if ( count( $html_inside ) > 0 ) {
			$html .= implode( '', $html_inside );
		}
		$html .= '</ul>';

		echo $html;
	}

	public static function active_class( $trigger_slug ) {

		if ( self::get_current_trigger() == $trigger_slug ) {
			return 'current';
		}

		return '';
	}

	public static function get_current_trigger() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['section'] ) ) {
			return $_GET['section'];
		}

		return 'all';
	}

}
