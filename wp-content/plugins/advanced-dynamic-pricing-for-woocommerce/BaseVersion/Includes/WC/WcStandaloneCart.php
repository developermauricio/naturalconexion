<?php

namespace ADP\BaseVersion\Includes\WC;

defined('ABSPATH') or exit;

class WcStandaloneCart extends \WC_Cart
{
    public function __construct()
    {
        $this->session  = new \WC_Cart_Session($this);
        $this->fees_api = new \WC_Cart_Fees($this);
    }

    public function get_customer()
    {
        $customer = parent::get_customer();

        return $customer ?? new \WC_Customer();
    }

    public function get_cart() {
        return array_filter( $this->get_cart_contents() );
    }

}
