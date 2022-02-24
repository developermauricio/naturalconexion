<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 28-03-19
 * Time: 5:02 PM
 */

namespace WACVP\Inc\Email;

use WACVP\Inc\Query_DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Templates {

	protected static $instance = null;

	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'register_custom_post_type' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			add_action( 'save_post_wacv_email_template', array( $this, 'save_post' ) );
			add_filter( 'manage_wacv_email_template_posts_columns', array( $this, 'add_columns' ) );
			add_action( 'manage_wacv_email_template_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
			add_action( 'wp_ajax_send_test_email', array( $this, 'send_test_email' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu_page' ), 20 );

			add_filter( 'post_row_actions', array( $this, 'duplicate_email_template_row_action' ), 10, 2 );
			add_action( 'admin_action_duplicate_email', array( $this, 'duplicate_email_template' ) );

			add_filter( 'viwec_register_email_type', array( $this, 'register_email_type' ) );
			add_filter( 'viwec_sample_subjects', array( $this, 'register_email_sample_subject' ) );
			add_filter( 'viwec_sample_templates', array( $this, 'register_email_sample_template' ) );
			add_filter( 'viwec_live_edit_shortcodes', array( $this, 'register_render_preview_shortcode' ) );
			add_filter( 'viwec_register_preview_shortcode', array( $this, 'register_render_preview_shortcode' ) );

			add_action( 'admin_notices', array( $this, 'notice_get_email_customizer' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'notice_style' ), 999 );
			add_action( 'admin_init', array( $this, 'dismiss_notice' ) );
		}
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	public function register_email_type( $emails ) {
		$emails['abandoned_cart'] = [
			'name'            => __( 'WC Abandoned Cart', 'woo-abandoned-cart-recovery' ),
			'hide_rules'      => [
				'country',
				'category',
				'min_order',
				'max_order'
			],
			'accept_elements' => [
				'html/abandoned_cart',
				'html/recover_button',
				'html/coupon',
			]
		];

		return $emails;
	}

	public function register_email_sample_subject( $subjects ) {

		$subjects['abandoned_cart'] = 'Hi {wacv_customer_name}!! You left something in your cart';

		return $subjects;
	}

	public function register_email_sample_template( $samples ) {
		if ( ! defined( 'VIWEC_IMAGES' ) ) {
			define( 'VIWEC_IMAGES', WACVP_IMAGES );
		}

		$samples['abandoned_cart'] = [
			'basic' => [
				'name' => esc_html__( 'Basic', 'woo-abandoned-cart-recovery' ),
				'data' => '{"style_container":{"background-color":"transparent","background-image":"none"},"rows":{"0":{"props":{"style_outer":{"padding":"15px 35px","background-image":"none","background-color":"#162447","border-color":"transparent","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid2cols","dataCols":"2"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"width":"265px","line-height":"30px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p><span style=\"font-size: 20px; color: #ffffff;\">YOUR LOGO</span></p>"},"attrs":{},"childStyle":{}}}},"1":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"width":"265px","line-height":"30px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: right;\"><span style=\"color: #ffffff;\">Help Center</span></p>"},"attrs":{},"childStyle":{}}}}}},"1":{"props":{"style_outer":{"padding":"45px 35px","background-image":"none","background-color":"#f9f9f9","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/image","style":{"width":"530px","text-align":"center","padding":"0px","background-image":"none","background-color":"transparent"},"content":{},"attrs":{"src":"' . VIWEC_IMAGES . 'icon-cart-dark-blue-200x200-1.png"},"childStyle":{"img":{"width":"100px"}}},"1":{"type":"html/text","style":{"width":"530px","line-height":"48px","background-image":"none","background-color":"transparent","padding":"20px 0px 0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"font-size: 24px; color: #444444;\">Hi {customer_name}, You have a cart not checkout.</span></p>"},"attrs":{},"childStyle":{}}}}}},"2":{"props":{"style_outer":{"padding":"25px 35px 0px","background-image":"none","background-color":"#ffffff","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/abandoned_cart","style":{"width":"530px"},"content":{"quantity":"Quantity:","price":"Price:"},"attrs":{},"childStyle":{".viwec-item-row":{"background-color":"transparent","border-width":"0px","border-color":"#808080"},".viwec-product-img":{"width":"150px"},".viwec-product-distance":{"padding":"10px 0px 0px"},".viwec-product-name":{"font-size":"15px","font-weight":"400","color":"#444444","line-height":"22px"},".viwec-product-quantity":{"font-size":"15px","font-weight":"400","color":"#444444","line-height":"22px"},".viwec-product-price":{"font-size":"15px","font-weight":"400","color":"#444444","line-height":"22px"}}},"1":{"type":"html/spacer","style":{"width":"530px"},"content":{},"attrs":{},"childStyle":{".viwec-spacer":{"padding":"25px 0px 0px"}}},"2":{"type":"html/recover_button","style":{"width":"530px","font-size":"18px","font-weight":"400","color":"#ffffff","line-height":"22px","text-align":"center","padding":"0px"},"content":{"text":"Checkout Now"},"attrs":{},"childStyle":{"a":{"border-width":"0px","border-radius":"0px","border-color":"#ffffff","background-color":"#e43f5a","width":"158px","padding":"10px 20px"}}}}}}},"3":{"props":{"style_outer":{"padding":"0px 35px 25px","background-image":"none","background-color":"#ffffff","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/divider","style":{"width":"530px","padding":"25px 0px","background-image":"none","background-color":"transparent"},"content":{},"attrs":{},"childStyle":{"hr":{"border-top-color":"#f9f9f9","border-width":"10px 0px 0px"}}},"1":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px 0px 25px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p><span style=\"font-size: 20px;\">May Be You Like</span></p>"},"attrs":{},"childStyle":{}},"2":{"type":"html/suggest_product","style":{"width":"530px","padding":"0px","background-image":"none","background-color":"transparent"},"content":{},"attrs":{"data-product_type":"related","data-max_row":"1","data-column":"4","character-limit":"31"},"childStyle":{".viwec-suggest-product":{"width":"530px"},".viwec-product-name":{"font-size":"15px","font-weight":"400","color":"#444444","line-height":"21px"},".viwec-product-price":{"font-size":"15px","font-weight":"400","color":"#444444","line-height":"21px"},".viwec-product-distance":{"padding":"0px 0px 0px 10px"},".viwec-product-h-distance":{}}}}}}},"4":{"props":{"style_outer":{"padding":"25px 35px","background-image":"none","background-color":"#162447","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 20px;\">Get in Touch</span></p>"},"attrs":{},"childStyle":{}},"1":{"type":"html/social","style":{"width":"530px","text-align":"center","padding":"20px 0px 0px","background-image":"none","background-color":"transparent"},"content":{},"attrs":{"facebook":"' . VIWEC_IMAGES . 'fb-blue-white.png","facebook_url":"#","twitter":"' . VIWEC_IMAGES . 'twi-cyan-white.png","twitter_url":"#","instagram":"' . VIWEC_IMAGES . 'ins-white-color.png","instagram_url":"#","direction":""},"childStyle":{}},"2":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","background-color":"transparent","padding":"20px 0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 12px;\">This email was sent by : <span style=\"color: #ffffff;\"><a style=\"color: #ffffff;\" href=\"{admin_email}\">{admin_email}</a></span></span></p>\n<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 12px;\">For any questions please send an email to <span style=\"color: #ffffff;\"><a style=\"color: #ffffff;\" href=\"{admin_email}\">{admin_email}</a></span></span></p>\n<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 12px;\"><span style=\"color: #ffffff;\"><span style=\"color: #f5f5f5;\">Don\'t want to receive this email. Please click&nbsp;</span><a style=\"color: #ffffff;\" href=\"{wacv_unsubscribe_link}\">Unsubscribe</a></span></span></p>"},"attrs":{},"childStyle":{}},"3":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5;\"><span style=\"color: #f5f5f5;\"><span style=\"font-size: 12px;\"><a style=\"color: #f5f5f5;\" href=\"#\">Privacy Policy</a>&nbsp; |&nbsp; <a style=\"color: #f5f5f5;\" href=\"#\">Help Center</a></span></span></span></p>"},"attrs":{},"childStyle":{}}}}}}}}'
			]
		];

		return $samples;
	}

	public function admin_menu_page() {
		add_submenu_page(
			'wacv_sections',
			__( 'Email Templates', 'woo-abandoned-cart-recovery' ),
			__( 'Email Templates', 'woo-abandoned-cart-recovery' ),
			apply_filters( 'wacv_change_role', 'manage_woocommerce' ),
			'edit.php?post_type=wacv_email_template'
		);
	}

	public function register_custom_post_type() {
		$labels = array(
			'name'               => _x( 'Email Templates', 'Post Type General Name', 'woo-abandoned-cart-recovery' ),
			'singular_name'      => _x( 'Email Templates', 'Post Type Singular Name', 'woo-abandoned-cart-recovery' ),
			'menu_name'          => __( 'Email Templates', 'woo-abandoned-cart-recovery' ),
			'parent_item_colon'  => __( 'Parent Email', 'woo-abandoned-cart-recovery' ),
			'all_items'          => __( 'All Emails', 'woo-abandoned-cart-recovery' ),
			'view_item'          => __( 'View Template', 'woo-abandoned-cart-recovery' ),
			'add_new_item'       => __( 'Add New Email Template', 'woo-abandoned-cart-recovery' ),
			'add_new'            => __( 'Add New', 'woo-abandoned-cart-recovery' ),
			'edit_item'          => __( 'Edit Email Templates', 'woo-abandoned-cart-recovery' ),
			'update_item'        => __( 'Update Email Templates', 'woo-abandoned-cart-recovery' ),
			'search_items'       => __( 'Search Email Templates', 'woo-abandoned-cart-recovery' ),
			'not_found'          => __( 'Not Found', 'woo-abandoned-cart-recovery' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'woo-abandoned-cart-recovery' ),
		);

		$email_temp_args = array(
			'label'               => __( 'Email Templates', 'woo-abandoned-cart-recovery' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=wacv_email_template',
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => false,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'query_var'           => true,
			'capabilities'        => apply_filters( 'wacv_capabilities_email_template', array() ),
			/*'create_posts' => 'do_not_allow', //Disable add new */
			'menu_position'       => null,
			'map_meta_cap'        => true,
		);

		// Registering your Custom Post Type
		register_post_type( 'wacv_email_template', $email_temp_args );
	}

	public function add_meta_boxes() {

		add_meta_box(
			'email_settings',
			__( 'Email settings', 'woo-abandoned-cart-recovery' ),
			array( $this, 'email_settings' ),
			'wacv_email_template',
			'side'
		);
		add_meta_box(
			'preview_box',
			__( 'Email template builder', 'woo-abandoned-cart-recovery' ),
			array( $this, 'preview' ),
			'wacv_email_template'
		);
		add_meta_box(
			'coupon_setting',
			__( 'Coupon settings', 'woo-abandoned-cart-recovery' ),
			array( $this, 'coupon_setting_box' ),
			'wacv_email_template'
		);

		remove_meta_box( 'eg-meta-box', 'wacv_email_template', 'normal' );
	}

	public function email_settings( $post ) {
		$meta_value  = ( get_post_meta( $post->ID, 'wacv_email_settings_new', true ) );
		$subject     = isset( $meta_value['subject'] ) ? $meta_value['subject'] : __( 'Hey !! You left something in your cart', 'woo-abandoned-cart-recovery' );
		$heading     = isset( $meta_value['heading'] ) ? $meta_value['heading'] : __( 'Hey !! You left something in your cart', 'woo-abandoned-cart-recovery' );
		$woo_header  = isset( $meta_value['woo_header'] ) ? $meta_value['woo_header'] : '';
		$heading_stt = $woo_header ? 'block' : 'none';

		if ( class_exists( 'WooCommerce_Email_Template_Customizer' ) || class_exists( 'Woo_Email_Template_Customizer' ) ) {
			if ( class_exists( 'VIWEC_Render_Email_Template' ) ) {
				$use_viwec_url = admin_url( 'post-new.php?post_type=viwec_template&sample=abandoned_cart&style=basic' );
				$target        = '';
			}
		} else {
			$use_viwec_url = 'https://1.envato.market/BZZv1';
			$target        = '_blank';
		}

		?>
        <div class="wacv-padding">
            <a class="button wacv-viwec-suggest" href="<?php echo esc_url( $use_viwec_url ) ?>" target="<?php echo esc_attr( $target ) ?>">
				<?php esc_html_e( 'Use WooCommerce Email Template Customizer', 'woo-abandoned-cart-recovery' ); ?>
            </a>
        </div>
        <div class="wacv-email-subject wacv-padding">
            <p class="wacv-label"><?php esc_html_e( 'Subject', 'woo-abandoned-cart-recovery' ) ?></p>
            <input class="wacv-subject" type="text" name="email_settings[subject]" required
                   value="<?php esc_html_e( $subject ) ?>">
        </div>
        <p class="wacv-label"><?php esc_html_e( "Use WC's header & footer default", 'woo-abandoned-cart-recovery' ) ?></p>
        <div class="vi-ui toggle checkbox">
            <input type="checkbox" class="wacv-use-woo-header" name="email_settings[woo_header]"
                   value="1" <?php checked( $woo_header, 1 ) ?>>
            <label></label>
        </div>
        <div class="wacv-email-heading wacv-padding" style="display: <?php echo esc_attr( $heading_stt ) ?>">
            <p class="wacv-label"><?php esc_html_e( 'Heading', 'woo-abandoned-cart-recovery' ) ?></p>
            <input class="wacv-heading" type="text" name="email_settings[heading]" required
                   value="<?php esc_html_e( $heading ) ?>">
        </div>
        <div class="wacv-send-test-email wacv-padding">
            <p class="wacv-label"><?php esc_html_e( 'Test email', 'woo-abandoned-cart-recovery' ) ?></p>
            <input type="text" class="wacv-admin-email-test" value="<?php echo get_bloginfo( 'admin_email' ) ?>">
            <br>
            <div class="wacv-send-mail-action">
                <button type="button" class="wacv-send-test-email-btn button button-primary button-large">
					<?php esc_html_e( 'Send', 'woo-abandoned-cart-recovery' ) ?>
                </button>
                <span class="wacv-spinner spinner"></span>
                <span class="wacv-result-send-test-email"></span>
            </div>
            <div class="clear"></div>
        </div>
        <hr>
        <div id="wacv-control-panel">
            <!--            <table class="wacv-control-table">-->
            <!--                <tbody>-->
            <!---->
            <!--                </tbody>-->
            <!--            </table>-->
        </div>
		<?php
	}

	public function preview( $post ) {
		?>
        <table class="wacv-email-builder-area">
            <tr>
                <td>
                    <div class="wacv-elements">
                        <div class="wacv-text-drag element"><i class="dashicons dashicons-editor-textcolor"></i> Text
                        </div>
                        <div class="wacv-image-drag element"><i class="dashicons dashicons-format-image"></i> Image
                        </div>
                        <div class="wacv-button-drag element"><i class="dashicons dashicons-video-alt3"></i> Button
                        </div>
                        <div class="wacv-cart-drag element"><i class="dashicons dashicons-cart"></i> Cart</div>
                        <div class="wacv-divider-drag element"><i class="dashicons dashicons-minus"></i> Divider</div>
                    </div>
                    <div class="wacv-template-sample">
                        <select class="wacv-change-template">
                            <option value=""><?php esc_html_e( 'Template', 'woo-abandoned-cart-recovery' ) ?></option>
                            <option value="temp-1"><?php esc_html_e( 'Template 1', 'woo-abandoned-cart-recovery' ) ?></option>
                        </select>
                    </div>
                </td>
                <td>
                    <div id="wacv-preview">
                        <div class="wacv-email-content">
							<?php echo get_post_meta( $post->ID, 'wacv_email_html_edit', true ) ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="wacv-html-data-edit">
            <textarea style="display: none; width: 100%" class="" name="email_edit"> </textarea>
        </div>
        <div class="wacv-html-data-save">
            <textarea style="display: none;width: 100%" class="wacv-email-content-html" name="content"> </textarea>
        </div>
        <div class="clear"></div>
		<?php
	}

	public function coupon_setting_box( $post ) {
		$meta_value = ( get_post_meta( $post->ID, 'wacv_email_settings_new', true ) );
		$stt1       = isset( $meta_value['use_coupon_generate'] ) ? 'none' : '';
		$stt2       = ! isset( $meta_value['use_coupon_generate'] ) ? 'none' : '';
		?>
        <div>
            <table width="100%">
				<?php
				$this->checkbox_field( 'use_coupon', $meta_value, __( 'Use coupon', 'woo-abandoned-cart-recovery' ), __( "Note: This coupon won't be applied for order reminder email", 'woo-abandoned-cart-recovery' ) );
				//			$this->number_field( 'use_coupon_with_times', $meta_value, __( 'Send coupon with times', 'woo-abandoned-cart-recovery' ) );
				$this->checkbox_field( 'use_coupon_generate', $meta_value, __( 'Generate coupon', 'woo-abandoned-cart-recovery' ) );
				?>
            </table>

            <div class='wacv-select-wc-coupon' style="display: <?php echo esc_attr( $stt1 ) ?>">
                <table width="100%">
					<?php $this->select_coupon_field( 'wc_coupon', $meta_value, __( 'Select coupon', 'woo-abandoned-cart-recovery' ) ); ?>
                </table>
            </div>
            <div class='wacv-generate-coupon' style="display: <?php echo esc_attr( $stt2 ) ?>">
                <table width="100%">
					<?php
					$this->select_field( 'gnr_coupon_type', $meta_value, wc_get_coupon_types(), __( 'Discount type', 'woo-abandoned-cart-recovery' ) );
					$this->number_field( 'gnr_coupon_amount', $meta_value, __( 'Coupon amount', 'woo-abandoned-cart-recovery' ) );
					$this->number_field( 'gnr_coupon_date_expiry', $meta_value, __( 'Coupon expiry after x days', 'woo-abandoned-cart-recovery' ) );
					$this->number_field( 'gnr_coupon_min_spend', $meta_value, __( 'Minimum spend', 'woo-abandoned-cart-recovery' ) );
					$this->number_field( 'gnr_coupon_max_spend', $meta_value, __( 'Maximum spend', 'woo-abandoned-cart-recovery' ) );
					$this->checkbox_field( 'gnr_coupon_free_shipping', $meta_value, __( "Allow free shipping", 'woo-abandoned-cart-recovery' ) );
					$this->checkbox_field( 'gnr_coupon_individual', $meta_value, __( "Individual use only", 'woo-abandoned-cart-recovery' ) );
					$this->checkbox_field( 'gnr_coupon_exclude_sale_items', $meta_value, __( "Exclude sale items", 'woo-abandoned-cart-recovery' ) );
					$this->multi_select2_field( 'gnr_coupon_products', $this->get_products_for_select_field( $meta_value, 'gnr_coupon_products' ), __( 'Products', 'woo-abandoned-cart-recovery' ) );
					$this->multi_select2_field( 'gnr_coupon_exclude_products', $this->get_products_for_select_field( $meta_value, 'gnr_coupon_exclude_products' ), __( 'Exclude products', 'woo-abandoned-cart-recovery' ) );
					$this->multi_select2_field( 'gnr_coupon_categories', $meta_value, __( 'Product categories', 'woo-abandoned-cart-recovery' ), $this->get_categories() );
					$this->multi_select2_field( 'gnr_coupon_exclude_categories', $meta_value, __( 'Exclude categories', 'woo-abandoned-cart-recovery' ), $this->get_categories() );
					$this->number_field( 'gnr_coupon_limit_per_cp', $meta_value, __( 'Usage limit per coupon', 'woo-abandoned-cart-recovery' ) );
					$this->number_field( 'gnr_coupon_limit_x_item', $meta_value, __( 'Limit usage to X items', 'woo-abandoned-cart-recovery' ) );
					$this->number_field( 'gnr_coupon_limit_user', $meta_value, __( 'Usage limit per user', 'woo-abandoned-cart-recovery' ) );
					?>
                </table>
            </div>
        </div>
		<?php
	}

	public function checkbox_field( $field_name, $meta_value, $label = '', $explain = '', $col = 6 ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <tr>
            <td class="wacv-label">
                <label class=""><?php esc_html_e( $label ) ?></label>
            </td>
            <td class="vlt-twothird vlt-row">
                <div class="vlt-col s<?php echo esc_attr( $col ) ?>  vi-ui toggle checkbox">
                    <input type="checkbox" <?php echo isset( $meta_value[ $field_name ] ) && $meta_value[ $field_name ] == 1 ? 'checked' : '' ?>
                           name="email_settings[<?php echo esc_attr( $field_name ) ?>]"
                           value="1"
                           class="<?php echo esc_attr( $class ) ?> vlt-input vlt-none-shadow">
                    <label></label>
                    <span class="wacv-explain"><?php echo esc_html( $explain ) ?></span>
                </div>
            </td>
        </tr>
		<?php
	}

	public function select_coupon_field( $field_name, $meta_value, $label = '' ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <tr>
            <td class="wacv-label ">
                <label><?php esc_html_e( $label ) ?></label>
            </td>
            <td class="vlt-twothird vlt-row">
                <div class="vlt-col s6 <?php echo esc_attr( $class ) . '-outer' ?>">
                    <select class="<?php echo esc_attr( $class ) ?> vlt-select vlt-none-shadow vlt-round "
                            name="<?php echo "email_settings[$field_name]" ?>">
						<?php
						if ( isset( $meta_value[ $field_name ] ) ) {
							$cp_code = wc_get_coupon_code_by_id( $meta_value[ $field_name ] );
							echo "<option value=" . $meta_value[ $field_name ] . ">$cp_code</option>";
						}
						?>
                    </select>
                </div>
            </td>
        </tr>
		<?php
	}

	public function select_field( $field_name, $meta_value, $options, $label = '' ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <tr>
            <td class="wacv-label ">
                <label><?php esc_html_e( $label ) ?></label>
            </td>
            <td class="vlt-twothird vlt-row">
                <div class="vlt-col s6 <?php echo esc_attr( $class ) . '-outer' ?>">
                    <select class="<?php echo esc_attr( $class ) ?> vlt-select  vlt-none-shadow vlt-round"
                            name="<?php echo "email_settings[$field_name]" ?>">
						<?php
						if ( is_array( $options ) ) {
							foreach ( $options as $value => $view ) {
								$selected = isset( $meta_value[ $field_name ] ) && $value == $meta_value[ $field_name ] ? 'selected' : '';
								echo "<option value='" . $value . "' $selected>$view</option>";
							}
						}
						?>
                    </select>
                </div>
            </td>
        </tr>
		<?php
	}

	public function number_field( $field_name, $meta_value, $label = '', $placeholder = '', $col = 6 ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <tr>
            <td class="wacv-label">
                <label class=""><?php esc_html_e( $label ) ?></label>
            </td>
            <td class="vlt-twothird vlt-row">
                <div class="vlt-col s<?php echo esc_attr( $col ) ?>">
                    <input type="number" placeholder="<?php echo esc_attr( $placeholder ) ?>"
                           name="email_settings[<?php echo esc_attr( $field_name ) ?>]"
                           value="<?php echo isset( $meta_value[ $field_name ] ) ? $meta_value[ $field_name ] : '' ?>"
                           class="<?php echo esc_attr( $class ) ?> vlt-input vlt-none-shadow">
                </div>
            </td>
        </tr>
		<?php
	}

	public function multi_select2_field( $field_name, $meta_value, $label = '', $options = array() ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <tr>
            <td class="wacv-label">
                <label><?php esc_html_e( $label ) ?></label>
            </td>
            <td class="vlt-twothird vlt-row">
                <div class="vlt-col s6 <?php echo esc_attr( $class ) . '-outer' ?>">
                    <select multiple="multiple"
                            class="<?php echo esc_attr( $class ) ?> vlt-select  vlt-none-shadow vlt-round "
                            name="<?php echo "email_settings[$field_name][]" ?>">
						<?php

						if ( count( $options ) > 0 ) {
							foreach ( $options as $value => $view ) {
								$selected = isset( $meta_value[ $field_name ] ) && in_array( $value, $meta_value[ $field_name ] ) ? 'selected' : '';
								echo "<option value='" . $value . "' $selected>$view</option>";
							}
						} else {
							foreach ( $meta_value as $value => $view ) {
								echo "<option value='" . $value . "' selected>$view</option>";
							}
						}
						?>
                    </select>
                </div>
            </td>
        </tr>
		<?php
	}

	public function get_products_for_select_field( $list_id, $field_name ) {
		$options = array();

		if ( isset( $list_id[ $field_name ] ) && is_array( $list_id[ $field_name ] ) && count( $list_id[ $field_name ] ) > 0 ) {
			$products = wc_get_products( array( 'include' => $list_id[ $field_name ] ) );
			foreach ( $products as $product ) {
				$options[ $product->get_id() ] = $product->get_name();
			}
		}

		return $options;
	}

	public function get_categories() {
		$option = array();
		$args   = array(
			'taxonomy'   => "product_cat",
			'hide_empty' => 0,
			'orderby'    => 'name',
		);

		$categories = get_terms( $args );
		if ( count( $categories ) > 0 ) {
			foreach ( $categories as $category ) {
				$option[ $category->term_id ] = $category->name;
			}
		}

		return $option;
	}

	public function text_field( $field_name, $meta_value, $label = '', $placeholder = '', $col = 6 ) {
		$class = 'wacv-' . str_replace( '_', '-', $field_name );
		?>
        <div>
            <div class="wacv-label">
                <label class=""><?php esc_html_e( $label ) ?></label>
            </div>
            <div class="vlt-twothird vlt-row">
                <div class="vlt-col s<?php echo esc_attr( $col ) ?>">
                    <input type="text" placeholder="<?php echo esc_attr( $placeholder ) ?>"
                           name="email_settings[<?php echo esc_attr( $field_name ) ?>]"
                           value="<?php echo isset( $meta_value[ $field_name ] ) ? $meta_value[ $field_name ] : '' ?>"
                           class="<?php echo esc_attr( $class ) ?> vlt-input vlt-none-shadow">
                </div>
            </div>
        </div>
		<?php
	}

	public function save_post() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( isset( $_POST['ID'] ) && isset( $_POST['email_edit'] ) ) {
			update_post_meta( $_POST['ID'], 'wacv_email_html_edit', trim( sanitize_post( $_POST['email_edit'] ) ) );
		}
		if ( isset( $_POST['ID'] ) && isset( $_POST['email_settings'] ) ) {
			update_post_meta( $_POST['ID'], 'wacv_email_settings_new', wc_clean( $_POST['email_settings'] ) );
		}
		if ( isset( $_POST['ID'] ) && isset( $_POST['wacv_background_color'] ) ) {
			update_post_meta( $_POST['ID'], 'wacv_background_color', sanitize_text_field( $_POST['wacv_background_color'] ) );
		}
	}

	public function add_columns( $cols ) {
		unset( $cols['date'] );
		$cols['used'] = __( 'Recovered', 'woo-abandoned-cart-recovery' );
		$cols['date'] = __( 'Date', 'woo-abandoned-cart-recovery' );

		return $cols;
	}

	public function column_content( $col_id, $id ) {

		switch ( $col_id ) {
			case 'used':
				$template_sent = Query_DB::get_instance()->count_template( $id );
				$used          = get_post_meta( $id, 'wacv_template_used', true );
				if ( $template_sent ) {
					echo( round( ( intval( $used ) / intval( $template_sent ) ) * 100, 1 ) . '%' );
				}
				break;
		}
	}

	public function send_test_email() {
		$result = false;
		if ( isset( $_POST['email'] ) && is_email( $_POST['email'] ) && isset( $_POST['security'] ) && wp_verify_nonce( $_POST['security'], 'wacv_send_test_mail' ) ) {
			$subject    = ! empty( $_POST['subject'] ) ? sanitize_text_field( $_POST['subject'] ) : 'Send test email';
			$heading    = ! empty( $_POST['heading'] ) ? sanitize_text_field( $_POST['heading'] ) : 'Abandoned Cart Remind';
			$woo_header = ! empty( $_POST['woo_header'] ) ? sanitize_text_field( $_POST['woo_header'] ) : '';
			$coupon     = ! empty( $_POST['coupon'] ) ? sanitize_text_field( $_POST['coupon'] ) : 'j67s4hs8';
			$to         = sanitize_email( $_POST['email'] );
			$mailer     = WC()->mailer();
			$email      = new \WC_Email();
			$message    = trim( stripslashes( sanitize_post( $_POST['content'] ) ) );
			$search     = array(
				'{coupon_code}',
				'{wacv_checkout_btn}',
				'{site_title}',
				'{customer_name}',
				'{site_address}',
				'{admin_email}',
				'{site_url}',
				'{home_url}',
				'{shop_url}',
				'{wacv_coupon_start}',
				'{wacv_coupon_end}',
				'{wacv_cart_detail_start}',
				'{wacv_cart_detail_end}',
				'{wacv_image_product}',
				'{wacv_name_&_qty_product}',
				'{product_name}',
				'{product_quantity}',
				'{product_amount}',
				'{wacv_short_description}',
				'{unsubscribe_link}',
			);
			$replace    = array(
				$coupon,
				wc_get_checkout_url(),
				get_bloginfo(),
				'John Doe',
				WC()->countries->get_base_address(),
				get_bloginfo( 'admin_email' ),
				site_url(),
				home_url(),
				get_permalink( wc_get_page_id( 'shop' ) ),
				'',
				'',
				'',
				'',
				WACVP_IMAGES . 'product.png',
				'Product name x 2',
				'Product name',
				__( 'Quantity:', 'woo-abandoned-cart-recovery' ) . ' 2',
				__( 'Price:', 'woo-abandoned-cart-recovery' ) . ' $20',
				'This is the best product',
				site_url(),
			);

			$message = str_replace( $search, $replace, $message );
			$subject = str_replace( $search, $replace, $subject );
			$headers = "Content-Type: text/html";


			if ( $woo_header ) {
				$message = $email->style_inline( $mailer->wrap_message( $heading, $message ) );

				$padding     = array( 'style="padding: 12px;', 'padding: 48px 48px 32px' );
				$new_padding = array( 'style="padding:0', 'padding:0' );
				$message     = str_replace( $padding, $new_padding, $message );
			} else {
				$message = $email->style_inline( $this->wrap_message( $message ) );
			}
			$sent_mail = $mailer->send( $to, $subject, $message, $headers, '' );

			if ( $sent_mail ) {
				$result = true;
			}
		}

		wp_send_json( $result );
		wp_die();
	}

	public function wrap_message( $message ) {
		// Buffer.
		ob_start();

		wc_get_template( 'email-header.php', '', '', WACVP_TEMPLATES );

		echo wptexturize( $message ); // WPCS: XSS ok.

		wc_get_template( 'email-footer.php', '', '', WACVP_TEMPLATES );

		$message = ob_get_clean();

		return $message;
	}

	public function duplicate_email_template_row_action( $action, $post ) {
		if ( $post->post_type == 'wacv_email_template' && current_user_can( 'edit_posts' ) ) {
			$href   = wp_nonce_url( admin_url( "admin.php?action=duplicate_email&post={$post->ID}" ), 'duplicate_email' );
			$action = array( 'duplicate' => "<a href='$href'>" . __( 'Duplicate', 'woo-abandoned-cart-recovery' ) . "</a>" ) + $action;
			unset( $action['view'] );
			unset( $action['inline hide-if-no-js'] );
		}

		return $action;
	}

	public function duplicate_email_template() {
		if ( ! ( current_user_can( 'manage_woocommerce' ) && isset( $_GET['post'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'duplicate_email' ) ) ) {
			return;
		}

		$post_id           = sanitize_text_field( $_GET['post'] );
		$post              = get_post( $post_id );
		$args              = array(
			'post_author'  => $post->post_author,
			'post_content' => $post->post_content,
			'post_title'   => $post->post_title . ' (Duplicate)',
			'post_type'    => $post->post_type,
			'post_status'  => $post->post_status,
		);
		$post_html_edit    = get_post_meta( $post_id, 'wacv_email_html_edit', true );
		$post_settings     = get_post_meta( $post_id, 'wacv_email_settings', true );
		$post_settings_new = get_post_meta( $post_id, 'wacv_email_settings_new', true );
		$post_html_edit    = str_replace( "\\", "\\\\", $post_html_edit );

		$p_id = wp_insert_post( $args );
		update_post_meta( $p_id, 'wacv_email_html_edit', $post_html_edit );
		update_post_meta( $p_id, 'wacv_email_settings', $post_settings );
		update_post_meta( $p_id, 'wacv_email_settings_new', $post_settings_new );

		wp_safe_redirect( admin_url( "post.php?post={$p_id}&action=edit" ) );
		exit();
	}

	public function register_render_preview_shortcode( $sc ) {
		$sc['abandoned_cart'] = array(
			'{wacv_customer_name}'    => 'John Doe',
			'{wacv_unsubscribe_link}' => home_url( '?wacv_unsubscribe' ),
		);

		return $sc;
	}

	public function notice_get_email_customizer() {
		if ( in_array( get_current_screen()->id, WACV_Data()->plugin_pages() ) ) {

			if ( class_exists( 'WooCommerce_Email_Template_Customizer' ) || class_exists( 'Woo_Email_Template_Customizer' ) ) {
				return;
			}

			if ( get_option( 'wacv_dismiss_notice' ) ) {
				return;
			}

			$dismiss_url = add_query_arg( [ 'wacv_dismiss_notice' => true ] );
			$notice      = "<div class='notice notice-info is-dismissible' ><p><span>%1s</span> <a href='%2s'>%3s</a> <span>%4s</span> <a href='%5s'>%6s</a></p><a href='%7s' class='notice-dismiss'></a></div>";
			$free_ver    = 'https://wordpress.org/plugins/email-template-customizer-for-woo/';
			$pro_ver     = '';
			echo sprintf( $notice,
				__( 'To make your email template more beautifully, you can get WooCommerce Email Template Customizer', 'woo-abandoned-cart-recovery' ),
				esc_url( $free_ver ),
				__( 'Free version', 'woo-abandoned-cart-recovery' ),
				__( 'or', 'woo-abandoned-cart-recovery' ),
				esc_url( $pro_ver ),
				__( 'Premium version', 'woo-abandoned-cart-recovery' ),
				esc_url( $dismiss_url )
			);
		}
	}

	public function notice_style() {
		if ( in_array( get_current_screen()->id, WACV_Data()->plugin_pages() ) ) {
			$css = 'a.notice-dismiss{z-index:999; text-decoration:none;}';
			wp_add_inline_style( 'villatheme-support', $css );
		}
	}

	public function dismiss_notice() {
		if ( isset( $_GET['wacv_dismiss_notice'] ) && $_GET['wacv_dismiss_notice'] ) {
			update_option( 'wacv_dismiss_notice', true );
		}
	}

}

