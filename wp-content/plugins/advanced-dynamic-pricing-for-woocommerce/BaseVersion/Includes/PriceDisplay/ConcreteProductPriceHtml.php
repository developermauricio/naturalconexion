<?php

namespace ADP\BaseVersion\Includes\PriceDisplay;

defined('ABSPATH') or exit;

interface ConcreteProductPriceHtml
{
    /**
     * @param bool $striked
     *
     * @return self
     */
    public function withStriked($striked);

    /**
     * @param string $priceHtml
     *
     * @return string
     */
    public function getFormattedPriceHtml($priceHtml);


    /**
     * @param string $priceHtml
     *
     * @return string
     */
    public function getPriceHtmlWithoutFormatting($priceHtml);

    /**
     * @param float $qty
     *
     * @return string
     */
    public function getFormattedSubtotalHtml($qty);

    /**
     * @param float $qty
     *
     * @return string
     */
    public function getFormattedSubtotalHtmlWithoutPriceSuffix($qty);

    /**
     * @return string
     */
    public function getPriceHtml();
}
