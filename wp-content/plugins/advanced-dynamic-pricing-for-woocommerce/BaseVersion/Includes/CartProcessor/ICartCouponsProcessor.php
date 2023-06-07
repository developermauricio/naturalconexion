<?php

namespace ADP\BaseVersion\Includes\CartProcessor;

use ADP\BaseVersion\Includes\Core\Cart\Cart;

interface ICartCouponsProcessor
{
    public function init();

    public function installActions();

    public function sanitize(\WC_Cart $wcCart);

    public function applyCouponsToWcCart(Cart $cart, \WC_Cart $wcCart);

    public function updateTotals(\WC_Cart $wcCart);
}
