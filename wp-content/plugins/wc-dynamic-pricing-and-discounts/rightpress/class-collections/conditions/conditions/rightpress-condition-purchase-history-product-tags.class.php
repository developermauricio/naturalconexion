<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
require_once 'rightpress-condition-purchase-history.class.php';

/**
 * Condition: Purchase History - Product Tags
 *
 * @class RightPress_Condition_Purchase_History_Product_Tags
 * @package RightPress
 * @author RightPress
 */
abstract class RightPress_Condition_Purchase_History_Product_Tags extends RightPress_Condition_Purchase_History
{

    protected $key          = 'product_tags';
    protected $method       = 'list_advanced';
    protected $fields       = array(
        'before'    => array('timeframe_span'),
        'after'     => array('product_tags'),
    );
    protected $main_field   = 'product_tags';
    protected $position     = 50;

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

        return esc_html__('Purchased - Tags', 'rightpress');
    }

    /**
     * Get value by order
     *
     * @access protected
     * @param int $order_id
     * @return array
     */
    protected function get_purchase_history_value_by_order($order_id)
    {

        return RightPress_Help::get_wc_order_product_tag_ids($order_id);
    }





}
