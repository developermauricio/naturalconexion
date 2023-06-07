<?php

namespace ADP\BaseVersion\Includes\ProductExtensions;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

class ProductExtension
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \WC_Product
     */
    protected $product;

    /**
     * @param Context|\WC_Product $contextOrProduct
     * @param \WC_Product|null $deprecated
     */
    public function __construct($contextOrProduct, $deprecated = null)
    {
        $this->context = adp_context();
        $this->product = $contextOrProduct instanceof \WC_Product ? $contextOrProduct : $deprecated;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return float|null
     */
    public function getCustomPrice()
    {
        return isset($this->product->adpCustomInitialPrice) ? (float)$this->product->adpCustomInitialPrice : null;
    }

    public function setCustomPrice($price)
    {
        if ( $price === null ) {
            $this->product->adpCustomInitialPrice = null;
        } else {
            $this->product->adpCustomInitialPrice = (float)$price;
        }
    }
}
