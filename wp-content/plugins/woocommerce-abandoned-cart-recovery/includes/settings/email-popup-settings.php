<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 13-06-19
 * Time: 9:55 AM
 */

namespace WACVP\Inc\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Popup_Settings extends Admin_Settings {

	protected static $instance = null;

	public function __construct() {

	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function setting_page() {
		$redirect_option   = array(
			'no_redirect'      => __( 'No redirect', "woo-abandoned-cart-recovery" ),
			'to_cart_page'     => __( 'Cart page', "woo-abandoned-cart-recovery" ),
			'to_checkout_page' => __( 'Checkout page', "woo-abandoned-cart-recovery" ),
		);
		$unsub_type_option = array(
			'cart'  => __( 'Current abandoned cart', "woo-abandoned-cart-recovery" ),
			'email' => __( "Customer's email", "woo-abandoned-cart-recovery" ),
		);
		?>
        <div class="vi-ui bottom attached tab segment tab-admin" data-tab="popup">
            <h4><?php esc_html_e( 'Get email popup', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php $this->checkbox_option( 'front_page', __( "Appear on", 'woo-abandoned-cart-recovery' ), __( 'Choose which pages where you want the request email pop-up appear', 'woo-abandoned-cart-recovery' ), __( 'Home page', 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->checkbox_option( 'single_page', '', __( '', 'woo-abandoned-cart-recovery' ), __( 'Single product pages', 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->checkbox_option( 'shop_page', '', __( '', 'woo-abandoned-cart-recovery' ), __( 'Shop page', 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->checkbox_option( 'cart_page', '', __( '', 'woo-abandoned-cart-recovery' ), __( 'Cart page', 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->checkbox_option( 'category_page', '', __( '', 'woo-abandoned-cart-recovery' ), __( 'Category page', 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->text_option( 'popup_page_id', '', __( 'page_id, e.g: 123,456', 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->checkbox_option( 'email_field', __( "Fields display", 'woo-abandoned-cart-recovery' ), '', __( 'Email', 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->checkbox_option( 'phone_field', '', '', __( 'Phone number (Only appear in template 1 popup)', 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->checkbox_option( 'enable_gdpr', '', '', __( 'GDPR', 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->editor_option( 'gdpr_content', esc_html__( 'GDPR content' ) ); ?>
				<?php $this->checkbox_option( 'info_require', __( "Information required", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->number_option( 'dismiss_delay', __( "Dismiss time", 'woo-abandoned-cart-recovery' ), __( 'Set a time for the get email pop-up to reappear', 'woo-abandoned-cart-recovery' ), 'minutes' ); ?>
				<?php $this->select_option( 'redirect_after_atc', $redirect_option, __( "Redirect after Add to cart", 'woo-abandoned-cart-recovery' ), __( '', 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->template_popup( 'template_popup', __( "Template", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->text_option( 'title_popup', __( "Title", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->text_option( 'sub_title_popup', __( "Sub title", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->text_option( 'add_to_cart_btn', __( "Add to cart", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->text_option( 'invalid_email', __( "Invalid email notice", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->text_option( 'invalid_phone', __( "Invalid phone number notice", 'woo-abandoned-cart-recovery' ) ); ?>
            </table>
            <hr>
            <h4><?php esc_html_e( 'Unsubscribe popup', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php $this->text_option( 'unsub_title', __( "Title", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->text_option( 'unsub_content', __( "Content", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->text_option( 'unsub_button', __( "Button label", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->text_option( 'unsub_redirect', __( "Redirect URL", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->select_option( 'unsub_type', $unsub_type_option, __( "Unsubscribe type", 'woo-abandoned-cart-recovery' ),
					__( "The abandoned cart emails will be not sent to the Customer temporarily about current abandoned cart with current abandoned cart type or all the time with customer email type", 'woo-abandoned-cart-recovery' ) ); ?>
            </table>
            <hr>
            <h4><?php esc_html_e( 'Design', 'woo-abandoned-cart-recovery' ) ?></h4>
            <table class="wacv-table">
				<?php $this->color_field( 'popup_bg_color', __( "Background color", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->color_field( 'popup_title_color', __( "Title color", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->color_field( 'popup_sub_title_color', __( "Sub title color", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->color_field( 'popup_btn_color', __( "Button color", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->color_field( 'popup_btn_bg_color', __( "Button background color", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->color_field( 'popup_input_border_color', __( "Input border color", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->color_field( 'popup_input_text_color', __( "Input text color", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->color_field( 'popup_input_bg_color', __( "Input background color", 'woo-abandoned-cart-recovery' ) ); ?>
				<?php $this->color_field( 'popup_notice_color', __( "Notice color", 'woo-abandoned-cart-recovery' ) ); ?>
            </table>
        </div>
		<?php
	}


}
