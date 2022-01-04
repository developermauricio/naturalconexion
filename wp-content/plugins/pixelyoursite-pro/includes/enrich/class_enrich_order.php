<?php
namespace PixelYourSite;

class EnrichOrder {
    private static $_instance;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function init() {
        //woo
        if(PYS()->getOption("woo_enabled_save_data_to_orders")) {

            add_action( 'woocommerce_checkout_update_order_meta',array($this,'woo_save_checkout_fields'),10, 2);
            add_action( 'woocommerce_analytics_update_order_stats',array($this,'woo_update_analytics'));

            if(PYS()->getOption("woo_add_enrich_to_order_details")) {
                add_action( 'add_meta_boxes', array($this,'woo_add_order_meta_boxes') );
            }

            if(PYS()->getOption("woo_add_enrich_to_admin_email")) {
                add_action( 'woocommerce_email_customer_details', array($this,'woo_add_enrich_to_admin_email'),80,4 );
            }

        }

        // edd
        if(PYS()->getOption("edd_enabled_save_data_to_orders")) {

            add_filter('edd_payment_meta', array($this, 'edd_save_checkout_fields'),10,2);

            if(PYS()->getOption("woo_add_enrich_to_order_details")) {
                add_action('edd_view_order_details_main_after', array($this, 'add_edd_order_details'));
            }
        }
    }

    function woo_update_analytics($orderId) {
        if(!metadata_exists( 'post', $orderId, 'pys_enrich_data_analytics' )) {
            $totals = getWooUserStat($orderId);
            if($totals['orders_count'] == 0) {
                $totals = array(
                    'orders_count' => 'Guest order',
                    'avg_order_value' => 'Guest order',
                    'ltv' => 'Guest order',
                );
            }
            update_post_meta($orderId,"pys_enrich_data_analytics",$totals);
        }

    }

    function woo_save_checkout_fields($order_id, $data) {
        $pysData = [];
        $pysData['pys_landing'] = isset($_REQUEST['pys_landing']) ? sanitize_text_field($_REQUEST['pys_landing']) : "";
        $pysData['pys_source'] = isset($_REQUEST['pys_source']) ? sanitize_text_field($_REQUEST['pys_source']) : "";
        $pysData['pys_utm'] = isset($_REQUEST['pys_utm']) ? sanitize_text_field($_REQUEST['pys_utm']) : "";
        $pysData['pys_browser_time'] = isset($_REQUEST['pys_browser_time']) ? sanitize_text_field($_REQUEST['pys_browser_time']) : "";

        update_post_meta($order_id,"pys_enrich_data",$pysData);
    }

    function woo_add_order_meta_boxes () {
        add_meta_box( 'pys_enrich_fields_woo', __('PixelYourSite Pro','pixelyoursite'),
            array($this,"woo_render_order_fields"), 'shop_order');
    }

    /**
     * @param \WC_Order$order
     * @param $sent_to_admin
     * @param $plain_text
     * @param $email
     */

    function woo_add_enrich_to_admin_email($order, $sent_to_admin) {
        if($sent_to_admin) {
            $orderId = $order->get_id();
            echo "<h2>". __('PixelYourSite Professional','pixelyoursite')."</h2>";
            include 'views/html-order-meta-box.php';
            echo "Your clients don't see this information, we only send it in the New Order email that you get. You can remove it by opening the PixelYourSite plugin's main page. Look for \"Add enriched order's data to WooCommerce's default \"New Order\" email.\" turn it OFF, and Save.</br>";
        }

    }

    function woo_render_order_fields() {
        global  $post;
        $orderId = $post->ID;
        include 'views/html-order-meta-box.php';
    }

    function edd_save_checkout_fields( $payment_meta ,$init_payment_data) {

        if ( 0 !== did_action('edd_pre_process_purchase') ) {
            $pysData = [];

            if(get_current_user_id()) {
                $totals = getEddCustomerTotals();
            } else {
                $totals = getEddCustomerTotalsByEmail($payment_meta['email']);
                if($totals['orders_count'] == 0) {
                    $totals = array(
                        'orders_count' => 'Guest order',
                        'avg_order_value' => 'Guest order',
                        'ltv' => 'Guest order',
                    );
                }
            }


            $pysData['pys_landing'] = isset($_POST['pys_landing']) ? sanitize_text_field($_POST['pys_landing']) : "";
            $pysData['pys_source'] = isset($_POST['pys_source']) ? sanitize_text_field($_POST['pys_source']) : "";
            $pysData['pys_utm'] = isset($_POST['pys_utm']) ? sanitize_text_field($_POST['pys_utm']) : "";
            $pysData['pys_browser_time'] = isset($_POST['pys_browser_time']) ? sanitize_text_field($_POST['pys_browser_time']) : "";

            $pysData = array_merge($pysData,$totals);
            $payment_meta['pys_enrich_data'] = $pysData;
        }
        return $payment_meta;
    }


    function add_edd_order_details($payment_id) {
        include 'views/html-edd-order-box.php';
    }
}

/**
 * @return EnrichOrder
 */
function EnrichOrder() {
    return EnrichOrder::instance();
}

EnrichOrder();

