<?php
class Coordinadora_WP_Dashboard
{
    public static function ini()
    {
        register_activation_hook(__FILE__, __CLASS__ . '::enable');
        register_deactivation_hook(__FILE__, __CLASS__ . '::disable');
    }

    public static function enable()
    {
        self::request('Instalación');
    }

    public static function disable()
    {
        self::request('Desinstalación');
    }

    private static function request($event)
    {
        $url = "https://dashboard-shopify-woocommerce-backend-dot-cm-integraciones.uk.r.appspot.com/api/events";

        $shop_page_url = get_permalink(wc_get_page_id('shop'));
        $lastChar = $shop_page_url[strlen($shop_page_url) - 1];
        if ($lastChar === "/") {
            $shop_page_url = substr($shop_page_url, 0, -1);
        }

        $city = get_option('woocommerce_store_city');

        $data = array(
            'shopUrl' => $shop_page_url,
            'type' => $event,
            'source' => 'woocommerce',
        );

        $args = array(
            'body' => json_encode($data),
            'headers' => array(
                'content-type' => 'application/json',
            )
        );
        $response = wp_remote_post($url, $args);

        $logger = new WC_Logger();
        if (is_wp_error($response)) {
            $logger->add('coordinadora-dashboard', print_r($response, true));
            return;
        }

        $body = json_decode($response['body']);
        $logger->add('coordinadora-dashboard', print_r($body, true));
    }

    public static function notify($order_id)
    {
        $order = wc_get_order($order_id);

        $url = 'https://dashboard-pubsub-dot-cm-integraciones.uk.r.appspot.com/pubsub-publishers/woocommerce';
        $shop_page_url = get_permalink(wc_get_page_id('shop'));

        $created_at = $order->get_date_created()->setTimezone(new DateTimeZone('America/Bogota'))->format('Y-m-d H:i:sP');

        $args = array(
            'body' => json_encode(array(
                'shop' => $shop_page_url,
                'orderId' => $order_id,
                'createdAt' => $created_at,
                'total' => $order->get_total()
            )),
            'headers' => array(
                'content-type' => 'application/json',
            )
        );

        $response = wp_remote_post($url, $args);

        $logger = new WC_Logger();

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $error = print_r($error_message, true);
            $logger->add('coordinadora-dashboard', $error);
            return;
        } else {
            $logger->add('coordinadora-dashboard', 'notify enviada');
        }
    }
}
