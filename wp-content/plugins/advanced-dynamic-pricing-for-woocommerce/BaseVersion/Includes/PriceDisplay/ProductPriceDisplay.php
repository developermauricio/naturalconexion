<?php

namespace ADP\BaseVersion\Includes\PriceDisplay;

use ADP\BaseVersion\Includes\Compatibility\SomewhereWarmBundlesCmp;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml\GroupedProductPriceHtml;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml\SimpleProductPriceHtml;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml\VariableProductPriceHtml;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml\VariationProductPriceHtml;
use ADP\Factory;

defined('ABSPATH') or exit;

class ProductPriceDisplay
{
    /**
     * @param Context $context
     * @param ProcessedProductSimple|ProcessedVariableProduct $processedProduct
     *
     * @return ConcreteProductPriceHtml|null
     */
    public static function create($context, $processedProduct)
    {
        $product = $processedProduct->getProduct();
        if (
            $product instanceof \WC_Product_Simple
            || $product instanceof \WC_Product_External
        ) {
            if ($product instanceof \WC_Product_Variation) {
                /** @var VariationProductPriceHtml $concretePriceHtml */
                $concretePriceHtml = Factory::get(
                    "PriceDisplay_ConcreteProductPriceHtml_VariationProductPriceHtml",
                    $context,
                    $processedProduct
                );
            } else {
                /** @var SimpleProductPriceHtml $concretePriceHtml */
                $concretePriceHtml = Factory::get("PriceDisplay_ConcreteProductPriceHtml_SimpleProductPriceHtml",
                    $context,
                    $processedProduct
                );
            }
        } elseif ($product instanceof \WC_Product_Variable) {
            /** @var VariableProductPriceHtml $concretePriceHtml */
            $concretePriceHtml = Factory::get("PriceDisplay_ConcreteProductPriceHtml_VariableProductPriceHtml",
                $context,
                $processedProduct
            );
        } elseif ($product instanceof \WC_Product_Grouped) {
            /** @var GroupedProductPriceHtml $concretePriceHtml */
            $concretePriceHtml = Factory::get("PriceDisplay_ConcreteProductPriceHtml_GroupedProductPriceHtml",
                $context,
                $processedProduct
            );
        } elseif ($product instanceof \WC_Product) {
            /** @var SimpleProductPriceHtml $concretePriceHtml */
            $concretePriceHtml = Factory::get("PriceDisplay_ConcreteProductPriceHtml_SimpleProductPriceHtml",
                $context,
                $processedProduct
            );
        } else {
            $concretePriceHtml = null;
        }

        return $concretePriceHtml;
    }
}
