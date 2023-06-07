<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rightpress-condition.class.php';

/**
 * Condition Group: Cart Items
 *
 * @class RightPress_Condition_Cart_Items
 * @package RightPress
 * @author RightPress
 */
abstract class RightPress_Condition_Cart_Items extends RightPress_Condition
{

    protected $group_key        = 'cart_items';
    protected $group_position   = 220;
    protected $is_cart          = true;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        parent::__construct();

        $this->hook_group();
    }

    /**
     * Get group label
     *
     * @access public
     * @return string
     */
    public function get_group_label()
    {

        return esc_html__('Cart - Items', 'rightpress');
    }





}
