<?php

namespace ADP\BaseVersion\Includes\SEO;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Engine;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedGroupedProduct;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedProductSimple;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedVariableProduct;
use ADP\BaseVersion\Includes\WC\PriceFunctions;

defined('ABSPATH') or exit;

class StructuredData
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Engine
     */
    protected $globalEngine;

    /**
     * @param Context|Engine $contextOrEngine
     * @param Engine|null    $deprecated
     */
    public function __construct($contextOrEngine, $deprecated = null)
    {
        $this->context      = adp_context();
        $this->globalEngine = $contextOrEngine instanceof Engine ? $contextOrEngine : $deprecated;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function install()
    {
        add_filter('woocommerce_structured_data_product_offer', array($this, 'structuredProductData'), 10, 2);
    }

    /**
     * @param array       $data
     * @param \WC_Product $product
     *
     * @return array
     */
    public function structuredProductData($data, $product)
    {
        if ( ! $this->globalEngine) {
            return $data;
        }

        if (is_object($product) && $product->get_price()) {
            $productProcessor = $this->globalEngine->getProductProcessor();
            $processedProduct = $productProcessor->calculateProduct($product, 1);

            if (is_null($processedProduct)) {
                return $data;
            }

            $decimals = $this->context->getPriceDecimals();

            if ($processedProduct instanceof ProcessedVariableProduct || $processedProduct instanceof ProcessedGroupedProduct) {
                if ($processedProduct->getLowestPrice() === $processedProduct->getHighestPrice()) {
                    unset($data['lowPrice']);
                    unset($data['highPrice']);
                    $data['@type'] = 'Offer';
                    $data['price'] = wc_format_decimal($processedProduct->getLowestPrice(), $decimals);
                    // Assume prices will be valid until the end of next year, unless on sale and there is an end date.
                    $data['priceValidUntil']    = gmdate('Y-12-31', time() + YEAR_IN_SECONDS);
                    $data['priceSpecification'] = [
                        'price'                 => wc_format_decimal($processedProduct->getLowestPrice(), $decimals),
                        'priceCurrency'         => $this->context->getCurrencyCode(),
                        'valueAddedTaxIncluded' => $this->context->getIsPricesIncludeTax() ? 'true' : 'false',
                    ];
                } else {
                    unset($data['price']);
                    unset($data['priceValidUntil']);
                    unset($data['priceSpecification']);
                    $data['@type'] = 'AggregateOffer';
                    $data['lowPrice']  = wc_format_decimal($processedProduct->getLowestPrice(), $decimals);
                    $data['highPrice'] = wc_format_decimal($processedProduct->getHighestPrice(), $decimals);
                    $data['offerCount'] = count( $product->get_children() );
                }
            } elseif ($processedProduct instanceof ProcessedProductSimple) {
                $data['price']              = wc_format_decimal($processedProduct->getPrice(), $decimals);
                $data['priceSpecification'] = [
                    'price'                 => wc_format_decimal($processedProduct->getPrice(), $decimals),
                    'priceCurrency'         => $this->context->getCurrencyCode(),
                    'valueAddedTaxIncluded' => $this->context->getIsPricesIncludeTax() ? 'true' : 'false',
                ];
            }

            $data['priceCurrency'] = $this->context->getCurrencyCode();
        }

        return $data;
    }
}
