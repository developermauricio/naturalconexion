<?php

class QC_WC_CM_REST_Orders_Controller extends WP_REST_Controller
{

    public function __construct()
    {
        $this->template_url = apply_filters(
            'woocommerce_template_url',
            'woocommerce/'
        );
        $this->base = 'home';
        add_action('woocommerce_loaded', array($this, 'register_hooks'));

        $this->register_routes();
    }
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'wc/v3';

    /**
     * Register the routes for coupons.
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/ordersByTrackingNumbers/',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'wc_orders_by_tracking_numbers'),
                'permission_callback' => function () {
                    return current_user_can('read_private_posts');
                }
            )
        );
    }

    function wc_orders_by_tracking_numbers($request)
    {
        $trackingNumbers = $request["trackingNumbers"];

        $args = array(
            'post_type' => 'shop_order',
            'post_status' => array('any'),
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_coordinadora_tracking_number',
                    'value' => $trackingNumbers,
                    'compare' => 'IN',
                ),
            ),
            'fields' => 'ids'
        );

        $resp = new WP_Query($args);

        if (!isset($resp->posts)) {
            return [];
        }

        $orderIds = $resp->posts;

        $orders = [];
        foreach ($orderIds as $orderId) {
            $o =  wc_get_order($orderId);
            $orders[] = $o->get_data();
        }
        return new WP_REST_Response($orders, 200);
    }
}
