<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/public
 * @author     E-goi <egoi@egoi.com>
 */
class Smart_Marketing_Addon_Sms_Order_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function smsonw_enqueue_styles() {

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smart-marketing-addon-sms-order-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function smsonw_enqueue_scripts() {

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smart-marketing-addon-sms-order-public.js', array( 'jquery' ), $this->version, false );

        wp_enqueue_script('jquery');
        wp_localize_script( 'jquery', 'egoi_public_object', array(
                'ajax_url'       => admin_url( 'admin-ajax.php' ),
                'ajax_nonce'     => wp_create_nonce( 'egoi_public_object' ))
        );
	}

	/**
	 * Add field to order checkout form
	 *
	 * @param $checkout
	 */
	function smsonw_notification_checkout_field($checkout) {
		$recipients = json_decode(get_option('egoi_sms_order_recipients'), true);
		if (isset($recipients['notification_option']) && $recipients['notification_option']) {
            $checked = $checkout->get_value( 'egoi_notification_option' ) ? $checkout->get_value( 'egoi_notification_option' ) : 1;

            woocommerce_form_field('egoi_notification_option', array(
				'type'          => 'checkbox',
				'class'         => array('my-field-class form-row-wide'),
				'label'         => __('I want to be notified by SMS (Order Status)', 'smart-marketing-addon-sms-order'),
			), $checked);
		}
	}

	/**
	 * Save notification field from order checkout
	 *
	 * @param $order_id
	 */
	function smsonw_notification_checkout_field_update_order_meta($order_id) {
	    $option = filter_var($_POST['egoi_notification_option'], FILTER_SANITIZE_NUMBER_INT);
		if (isset($option) && filter_var($option, FILTER_VALIDATE_BOOLEAN)) {
			update_post_meta($order_id, 'egoi_notification_option', 1);
		} else {
			update_post_meta($order_id, 'egoi_notification_option', 0);
		}
	}


	/**
     FOLLOW PRICE
     */

	function smsonw_follow_price_add_button()
    {
        $follow_price = json_decode(get_option('egoi_sms_follow_price'), true);
        $button_text = ( isset($follow_price['follow_price_button_name']) && $follow_price['follow_price_button_name'] != '') ? $follow_price['follow_price_button_name'] : 'Follow price!';

        ?>
        <a class="button" id="triggerFollowPrice"><?php echo $button_text ?></a>
        <?php

        $this->printFollowPriceForm($follow_price);
    }


    private function processRequest($post){

	    if(isset($post['egoi_action']) &&  $post['egoi_action']=='saveFollowPrice') {
            //Validate ProductId
            if (!isset($post["productId"]) || $post["productId"] <= 0) {
                wp_send_json_error("Product not found!");
            }

            //Validate mphone
            if (!isset($post["mphone"]) || $post["mphone"] == '') {
                wp_send_json_error("Must enter your mobile phone");
            }

            //Validate prefix mphone
            if (!isset($post["prefixMphone"]) || $post["prefixMphone"] == '') {
                wp_send_json_error("Must enter your mobile phone country code");
            }

            $this->saveFollowPrice($post["productId"], $post["prefixMphone"]."-".$post["mphone"]);
            wp_send_json_success("Save with success!");
        }
	    return;
    }

	/**
	 * @param array $follow_price
	 */
    private function printFollowPriceForm($follow_price=array())
    {

        $this->getCustomerMobilePhone();
        if (!is_product()) { return; }
        ?>
        <style>
            #printFollowPriceForm {
                position: absolute;
                margin: 0 auto;
                right: 0px;
                z-index: 999999;
                background: <?=$follow_price['follow_background_color']; ?>;
                padding: 2em;
                border: 2px solid <?=$follow_price['follow_background_color']; ?>;
                border-radius: 5px;
            }
            #printFollowPriceForm input[type=text] {
                width: 190px;
                height: 30px;
                border: none;
                background-color: #fff;
                -moz-border-radius: 4px;
                border-radius: 4px;
                padding-left: 10px;
                padding-right: 10px;
                border: 1px solid #ccc;
            }

            #printFollowPriceForm input[type=submit],
            #printFollowPriceForm input[type=button]{
                font-size: 100%;
                margin: 0;
                line-height: 1;
                cursor: pointer;
                position: relative;
                text-decoration: none;
                overflow: visible;
                padding: .618em 1em;
                font-weight: 700;
                border-radius: 3px;
                left: auto;
                color: <?=$follow_price['follow_button_text_color']; ?>;
                background-color: <?=$follow_price['follow_button_color']; ?>;
                border: 0;
                display: inline-block;
                background-image: none;
                box-shadow: none;
                text-shadow: none;
            }
        </style>

        <div id="printFollowPriceForm" style="display: none;">
            <form method="POST" action="#" id="saveFollowPriceEgoi" >
                <input type="hidden" name="egoi_action" value="saveFollowPrice" />
                <input type="hidden" name="action" value="egoi_cellphone_actions" />
                <input type="hidden" name="productId" value="<?php echo wc_get_product()->get_id(); ?>" />
                <p style="color: <?=$follow_price['follow_text_color']; ?>;"><?=$follow_price['follow_title_pop']; ?> </p>
                <p>  + <input name="prefixMphone" placeholder="351" style="width: 35px;" value="" /> <input name="mphone" placeholder="917789988" value="<?php echo $this->getCustomerMobilePhone()[1] ?>" /> </p>
                <p> <input type="submit" value="OK" /> </p>
            </form>
            <div id="followPriceMessage" style="display: none; color: <?=$follow_price['follow_text_color']; ?>;">
                <span><?php _e('An error has occurred! Please try later.', 'smart-marketing-addon-sms-order');?></span>
            </div>
        </div>
        <script>
            (function( $ ) {

                $( document ).ready(function() {

                    const anim = 200;
                    var messageRef = $('#followPriceMessage');

                    const tooglePopFollowPrice = () => {
                        if( jQuery('#printFollowPriceForm').css('display') == 'none' ) { jQuery('#printFollowPriceForm').show(anim); } else {jQuery('#printFollowPriceForm').hide(anim);} return true;
                    }

                    $('#triggerFollowPrice').on('click', () => {
                        tooglePopFollowPrice();
                    });

                    $('#saveFollowPriceEgoi').submit(function(e){
                        e.preventDefault();
                        var data = {
                            security: egoi_public_object.ajax_nonce
                        };

                        $(this).serializeArray().forEach((obj) => {
                            data[obj.name] = obj.value
                        })

                        $.ajax({
                            url: egoi_public_object.ajax_url,
                            type: "POST",
                            data:data,
                            success: function(data){
                                tooglePopFollowPrice();
                            },
                            error: function() {
                                var messageHolder = $(messageRef.find("span")[0]);
                                messageHolder.text(response.data)
                                messageRef.show(anim)
                                return;
                            }
                        });
                    });
                });

            })( jQuery );
        </script>
        <?php
    }

    function getCustomerMobilePhone(){
        $customer_id = get_current_user_id();
        $phone = get_user_meta( $customer_id, 'billing_phone', true );
        //TODO:set default if not found
        return explode("-", $phone);
    }


    function saveFollowPrice($product_id, $mobile) {
	    if( $this->getFollowPrice($product_id, $mobile) > 0 ){
	        return true;
        }

        global $wpdb;
        $wpdb->insert("{$wpdb->prefix}egoi_sms_follow_price", array(
            'product_id' => $product_id,
            'mobile' => $mobile,
            'time' => current_time('mysql')
        ));
        return true;
    }

    function getFollowPrice($product_id, $mobile) {
        global $wpdb;
        $result = $wpdb->get_results("SELECT count(1) as exist From {$wpdb->prefix}egoi_sms_follow_price where mobile = '".$mobile."' and product_id = '".$product_id."'", ARRAY_A);
        return $result[0]["exist"];
    }

	function smsonw_notification_abandoned_cart_trigger() {
	    require_once plugin_dir_path( dirname( __FILE__ ) ).'includes/class-smart-marketing-addon-sms-order-abandonned-cart.php';
	    $abandonedService = new Smart_Marketing_Addon_Sms_Order_Abandoned_Cart();
        $abandonedService->start();
    }

    function smsonw_notification_abandoned_cart_clear($order_id){
        require_once plugin_dir_path( dirname( __FILE__ ) ).'includes/class-smart-marketing-addon-sms-order-abandonned-cart.php';
        $abandonedService = new Smart_Marketing_Addon_Sms_Order_Abandoned_Cart();
        $abandonedService->convertCart($order_id);
    }

    function egoi_cellphone_actions(){
        check_ajax_referer( 'egoi_public_object', 'security' );
        $result = $this->processRequest($_POST);
    }

}
