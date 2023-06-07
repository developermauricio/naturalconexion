<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rightpress-condition-shipping.class.php';

/**
 * Condition: Shipping - State
 *
 * @class RightPress_Condition_Shipping_State
 * @package RightPress
 * @author RightPress
 */
abstract class RightPress_Condition_Shipping_State extends RightPress_Condition_Shipping
{

    protected $key      = 'state';
    protected $method   = 'list';
    protected $fields   = array(
        'after' => array('states'),
    );
    protected $position = 20;

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

        return esc_html__('Shipping state', 'rightpress');
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

        // Get country from package
        if ($shipping_package !== null && is_array($shipping_package) && !empty($shipping_package['destination']['country'])) {
            $country = $shipping_package['destination']['country'];
        }
        // Get country from customer session
        else {
            $country = $customer->get_shipping_country();
        }

        // Get state from package
        if ($shipping_package !== null && is_array($shipping_package) && !empty($shipping_package['destination']['state'])) {
            $state = $shipping_package['destination']['state'];
        }
        // Get state from customer session
        else {
            $state = $customer->get_shipping_state();
        }

        // Combine country and state
        if ($country && $state) {
            return $country . '_' . $state;
        }

        return null;
    }





}
