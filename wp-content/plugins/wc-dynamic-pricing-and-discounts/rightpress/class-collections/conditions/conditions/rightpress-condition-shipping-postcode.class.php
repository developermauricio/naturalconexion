<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rightpress-condition-shipping.class.php';

/**
 * Condition: Shipping - Postcode
 *
 * @class RightPress_Condition_Shipping_Postcode
 * @package RightPress
 * @author RightPress
 */
abstract class RightPress_Condition_Shipping_Postcode extends RightPress_Condition_Shipping
{

    protected $key      = 'postcode';
    protected $method   = 'postcode';
    protected $fields   = array(
        'after' => array('postcode'),
    );
    protected $position = 30;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        parent::__construct();

        $this->hook();
    }

    /**
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {

        return esc_html__('Shipping postcode', 'rightpress');
    }

    /**
     * Get shipping value
     *
     * @access public
     * @param object $customer
     * @param array $shipping_package
     * @return mixed
     */
    public function get_shipping_value($customer, $shipping_package = null)
    {

        // Get postcode from package
        if ($shipping_package !== null && is_array($shipping_package) && !empty($shipping_package['destination']['postcode'])) {
            $postcode = $shipping_package['destination']['postcode'];
        }
        // Get postcode from customer session
        else {
            $postcode = $customer->get_shipping_postcode();
        }

        return $postcode;
    }





}
