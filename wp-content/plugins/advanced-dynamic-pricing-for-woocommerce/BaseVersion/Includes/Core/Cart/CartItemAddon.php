<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

use ADP\BaseVersion\Includes\Context\Currency;

defined('ABSPATH') or exit;

class CartItemAddon
{
    /**
     * @var string
     */
    public $key = "";

    /**
     * @var string
     */
    public $label = "";

    /**
     * @var mixed
     */
    public $value = null;

    /**
     * @var Currency
     */
    public $currency;

    /**
     * @var float
     */
    public $price = 0.0;

    public function __construct($key, $value, $price)
    {
        $this->key   = (string)$key;
        $this->value = $value;
        $this->price = (float)$price;

        $this->currency = adp_context()->currencyController->getDefaultCurrency();
        $this->label    = (string)$key;
    }
}
