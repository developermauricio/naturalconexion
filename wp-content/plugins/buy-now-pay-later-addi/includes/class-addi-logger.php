<?php
class AddiLogger {

    public static $events = array (
        'SELECTED_PAYMENT_METHOD' => array('NAME' => 'selectedPaymentMethod', 'DESCRIPTION' => '01: On select payment method ADDI'),
        'GET_ALLY_CONFIG' => array('NAME' => 'getAllyConfig', 'DESCRIPTION' => '02: On get ally config ADDI'),
        'DISPLAY_PAYMENT_METHOD' => array('NAME' => 'displayPaymentMethod', 'DESCRIPTION' => '03: On Display payment method ADDI'),
        'CLICK_END_PURCHASE' => array('NAME' => 'clickEndPurchase', 'DESCRIPTION' => '04: On click end purchase ADDI'),
        'GET_ORDER_FORM' => array('NAME' => 'getOrderForm', 'DESCRIPTION' => '01: On select payment method ADDI'),
    );

    public static $errors  = array (
        'GET_ALLY_CONFIG_ERROR' => array('NAME' => 'getAllyConfig', 'DESCRIPTION' => '02: On get ally config ADDI'),
    );

    public static function logger_dna( $event, $error = false, $url = '', $method = 'POST', $status_code = 200, $duration_ms = 0, $phone = '', $email = '') {
        if ($error) {
            $comment =  self::$errors[$event]['NAME'];
            $document = self::$errors[$event]['DESCRIPTION'] . ' ' .$error;
        } else {
            $comment =  self::$events[$event]['NAME'];
            $document = self::$events[$event]['DESCRIPTION'];
        }

        $message_log = array(
            'id' => 'TEST_ID',
            'date' => date("Y-m-d h:i:s"),
            'document' => $document,
            'comment' => $comment,
            'phone' => $phone,
            'email' => $email
        );

        $body = array(
            'method' => $method,
            'url' => $url,
            'status' => $status_code,
            'body' => $message_log,
            'durationMs' => $duration_ms
        );

        $source = 'woocommerce_widget';
        $logger_api = 'https://logger-sandbox.addi.com';

        $result = wp_remote_post( $logger_api . '/api/logger/' . $source ,  array(
            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'        => json_encode($body),
            'method'      => 'POST',
            'data_format' => 'body',
        ));

        return $result;
    }
}