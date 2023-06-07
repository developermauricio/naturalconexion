<?php

namespace ADP\BaseVersion\Includes\PriceDisplay\PriceFormatters;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedProductSimple;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedVariableProduct;
use ADP\BaseVersion\Includes\WC\PriceFunctions;

defined('ABSPATH') or exit;

class DiscountRangeFormatter
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @var PriceFunctions
     */
    protected $priceFunctions;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context        = adp_context();
        $this->formatter      = new Formatter();
        $this->priceFunctions = new PriceFunctions();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function setFormatterTemplate()
    {
        $template = $this->getFormatterTemplate();
        $this->formatter->setTemplate(htmlspecialchars_decode($template));

        return true;
    }

    protected function getFormatterTemplate()
    {
        return _x(
            $this->context->getOption('replace_price_with_min_bulk_price_category_template'),
            "Replace price with lowest bulk price on the category page|Output template",
            "advanced-dynamic-pricing-for-woocommerce"
        );
    }

    /**
     * @param ProcessedProductSimple|ProcessedVariableProduct $processedProduct
     *
     * @return bool
     */
    public function isNeeded($processedProduct)
    {
        if ( ! $this->context->getOption("replace_price_with_min_bulk_price_category", false)) {
            return false;
        }

        if (apply_filters("adp_force_discount_range_formatter", false, $this->context, $processedProduct)) {
            return true;
        }

        $isHavingMinDiscountRangePrice = (
            ($processedProduct instanceof ProcessedProductSimple && $processedProduct->getMinDiscountRangePrice() !== null)
            || ($processedProduct instanceof ProcessedVariableProduct && $processedProduct->getLowestRangeDiscountPriceProduct() !== null)
        );

        return $this->context->isCatalog() && $isHavingMinDiscountRangePrice;
    }

    /**
     * @param ProcessedProductSimple|ProcessedVariableProduct $processedProduct
     *
     * @return string
     */
    public function getHtml($processedProduct)
    {
        $product = $processedProduct->getProduct();

        $minDiscountRangePriceForDisplay = null;
        $initialPriceForDisplay          = null;
        $minDiscountRangePrice          = null;
        $regularPrice          = null;
        $pos                   = null;
        if ($processedProduct instanceof ProcessedVariableProduct) {
            if ($discountRangeProcessed = $processedProduct->getLowestRangeDiscountPriceProduct()) {
                $minDiscountRangePrice = $discountRangeProcessed->getMinDiscountRangePrice();
                $minDiscountRangePriceForDisplay = $this->priceFunctions->getPriceToDisplay(
                    $product,
                    array(
                        'price' => $discountRangeProcessed->getMinDiscountRangePrice(),
                    )
                );
                $initialPriceForDisplay          = $this->priceFunctions->getPriceToDisplay(
                    $product,
                    array(
                        'price' => $discountRangeProcessed->getOriginalPrice(),
                    )
                );
                $pos                   = $discountRangeProcessed->getPos();
                $regularPrice          = $processedProduct->getLowestRangeDiscountPriceProduct()->getProduct()->get_regular_price();
            }
        } else {
            $minDiscountRangePrice = $processedProduct->getMinDiscountRangePrice();
            $minDiscountRangePriceForDisplay = $this->priceFunctions->getPriceToDisplay(
                $product,
                array(
                    'price' => $processedProduct->getMinDiscountRangePrice(),
                )
            );
            $initialPriceForDisplay          = $this->priceFunctions->getPriceToDisplay(
                $product,
                array(
                    'price' => $processedProduct->getOriginalPrice(),
                )
            );
            $pos                   = $processedProduct->getPos();
            $regularPrice          = $product->get_regular_price();
        }

        $replacements = array(
            'price'                 => ! is_null($minDiscountRangePriceForDisplay) ? $this->priceFunctions->format($minDiscountRangePriceForDisplay) : "",
            'price_suffix'          => ! is_null($minDiscountRangePrice) ? $product->get_price_suffix($minDiscountRangePrice): "",
            'price_striked'         => ! is_null($initialPriceForDisplay) ? '<del>' . $this->priceFunctions->format($initialPriceForDisplay) . '</del>' : "",
            'initial_price'         => ! is_null($initialPriceForDisplay) ? $this->priceFunctions->format($initialPriceForDisplay) : "",
            'Nth_item'              => $pos ? $this->addSuffixOf($pos) : "",
            'regular_price_striked' => '<del>' . $this->priceFunctions->format(
                    $this->priceFunctions->getPriceToDisplay($product, array("price" => $regularPrice))
                ) . '</del>',
        );

        $replacements = apply_filters(
            "adp_discount_range_formatter_replacements",
            $replacements,
            $processedProduct,
            $this
        );

        return $this->formatter->applyReplacements($replacements);
    }

    /**
     * Add ordinal indicator
     *
     * @param int $value
     *
     * @return string
     */
    public function addSuffixOf($value)
    {
        if ( ! is_numeric($value)) {
            return $value;
        }

        $value = (string)$value;

        $mod10  = $value % 10;
        $mod100 = $value % 100;

        if ($mod10 === 1 && $mod100 !== 11) {
            return $value . "st";
        }

        if ($mod10 === 2 && $mod100 !== 12) {
            return $value . "nd";
        }

        if ($mod10 === 3 && $mod100 !== 13) {
            return $value . "rd";
        }

        return $value . "th";
    }
}
