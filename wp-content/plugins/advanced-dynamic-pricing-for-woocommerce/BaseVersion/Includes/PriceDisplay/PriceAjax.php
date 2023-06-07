<?php

namespace ADP\BaseVersion\Includes\PriceDisplay;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Engine;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml\GroupedProductPriceHtml;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml\SimpleProductPriceHtml;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml\VariableProductPriceHtml;
use ADP\BaseVersion\Includes\PriceDisplay\ConcreteProductPriceHtml\VariationProductPriceHtml;
use ADP\BaseVersion\Includes\PriceDisplay\PriceFormatters\TotalProductPriceFormatter;
use ADP\BaseVersion\Includes\ProductExtensions\ProductExtension;
use ADP\BaseVersion\Includes\WC\PriceFunctions;

defined('ABSPATH') or exit;

class PriceAjax
{
    const ACTION_GET_SUBTOTAL_HTML = 'get_price_product_with_bulk_table';
    const ACTION_CALCULATE_SEVERAL_PRODUCTS = 'adp_calculate_several_products';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @var PriceFunctions
     */
    protected $priceFunctions;

    /**
     * @var string
     */
    protected $nonceParam;

    /**
     * @var string
     */
    protected $nonceName;

    /**
     * @param Context|Engine $contextOrEngine
     * @param Engine|null $deprecated
     */
    public function __construct($contextOrEngine, $deprecated = null)
    {
        $this->context        = adp_context();
        $this->engine         = $contextOrEngine instanceof Engine ? $contextOrEngine : $deprecated;
        $this->priceFunctions = new PriceFunctions();

        $this->nonceParam = 'wdp-request-price-ajax-nonce';
        $this->nonceName = 'wdp-request-price-ajax';
    }

    /**
     * @return string
     */
    public function getNonceParam(): string
    {
        return $this->nonceParam;
    }

    /**
     * @return string
     */
    public function getNonceName(): string
    {
        return $this->nonceName;
    }

    protected function checkNonceOrDie()
    {
        if (wp_verify_nonce($_REQUEST[$this->nonceParam] ?? null, $this->nonceName) === false) {
            wp_die(__('Invalid nonce specified', 'advanced-dynamic-pricing-for-woocommerce'),
                __('Error', 'advanced-dynamic-pricing-for-woocommerce'), ['response' => 403]);
        }
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function register()
    {
        add_action("wp_ajax_nopriv_" . self::ACTION_GET_SUBTOTAL_HTML, array($this, "ajaxCalculatePrice"));
        add_action("wp_ajax_" . self::ACTION_GET_SUBTOTAL_HTML, array($this, "ajaxCalculatePrice"));

        add_action("wp_ajax_nopriv_" . self::ACTION_CALCULATE_SEVERAL_PRODUCTS,
            array($this, "ajaxCalculateSeveralProducts"));
        add_action("wp_ajax_" . self::ACTION_CALCULATE_SEVERAL_PRODUCTS, array($this, "ajaxCalculateSeveralProducts"));
    }

    public function ajaxCalculatePrice()
    {
        $this->checkNonceOrDie();
        $prodId     = ! empty($_REQUEST['product_id']) ? intval($_REQUEST['product_id']) : false;
        $qty        = ! empty($_REQUEST['qty']) ? floatval($_REQUEST['qty']) : false;
        $attributes = ! empty($_REQUEST['attributes']) ? (array)$_REQUEST['attributes'] : array();

        $pageData  = ! empty($_REQUEST['page_data']) ? (array)$_REQUEST['page_data'] : array();
        $isProduct = isset($pageData['is_product']) ? wc_string_to_bool($pageData['is_product']) : null;

        $customPrice = null;
        if ( ! empty($_REQUEST['custom_price'])) {
            $customPrice = $this->parseCustomPrice($_REQUEST['custom_price']);
        }

        if ( ! $prodId || ! $qty) {
            wp_send_json_error();
        }

        $context = $this->context;

        $context->setProps(array(
            $context::ADMIN           => false,
            $context::AJAX            => false,
            $context::WC_PRODUCT_PAGE => $isProduct,
        ));

        $result = $this->calculatePrice($prodId, $qty, $attributes, $customPrice);

        if ($result === null) {
            wp_send_json_error();
        } else {
            wp_send_json_success($result);
        }
    }

    public function ajaxCalculateSeveralProducts()
    {
        $this->checkNonceOrDie();
        $list = ! empty($_REQUEST['products_list']) ? $_REQUEST['products_list'] : array();

        if ( ! is_array($list) || count($list) === 0) {
            wp_send_json_success(array());
        }

        $readyList = array();
        foreach ($list as $item) {
            $productId   = isset($item['product_id']) ? intval($item['product_id']) : 0;
            $qty         = isset($item['qty']) ? floatval($item['qty']) : floatval(0);
            $customPrice = isset($item['custom_price']) ? $this->parseCustomPrice($item['custom_price']) : null;
            $attributes  = isset($item['attributes']) ? $item['attributes'] : array();

            if ($productId === 0 || $qty === floatval(0)) {
                continue;
            }

            $readyList[] = array(
                'product_id'   => $productId,
                'qty'          => $qty,
                'custom_price' => $customPrice,
                'attributes'   => $attributes,
            );
        }

        if (count($readyList) === 0) {
            wp_send_json_success(array());
        }

        $pageData  = ! empty($_REQUEST['page_data']) ? (array)$_REQUEST['page_data'] : array();
        $isProduct = isset($pageData['is_product']) ? wc_string_to_bool($pageData['is_product']) : null;

        $context = $this->context;
        $context->setProps(array(
            $context::ADMIN           => false,
            $context::AJAX            => false,
            $context::WC_PRODUCT_PAGE => $isProduct,
        ));

        $result = array();
        foreach ($readyList as $item) {
            $result[$item['product_id']] = $this->calculatePrice($item['product_id'], $item['qty'],
                $item['attributes'], $item['custom_price']);
        }

        wp_send_json_success($result);
    }

    /**
     * @param string $customPrice
     *
     * @return float|null
     */
    protected function parseCustomPrice($customPrice)
    {
        $result = null;

        if (preg_match('/\d+\\' . wc_get_price_decimal_separator() . '\d+/', $customPrice, $matches) !== false) {
            $result = floatval(reset($matches));
        }

        return $result;
    }

    /**
     * @param int $productId
     * @param float $qty
     * @param array<string, string> $attributes
     * @param float|null $customPrice
     *
     * @return array|null
     */
    protected function calculatePrice($productId, $qty, $attributes = array(), $customPrice = null)
    {
        $product = CacheHelper::getWcProduct($productId);
        if ($customPrice !== null) {
            $productExt = new ProductExtension($product);
            $productExt->setCustomPrice($customPrice);
        }

        if ($product instanceof \WC_Product_Variation && array_filter($attributes)) {
            $product->set_attributes(array_filter($attributes));
        }

        $processedProduct = $this->engine->getProductProcessor()->calculateProduct($product, $qty);

        if (is_null($processedProduct)) {
            return null;
        }

        $priceDisplay  = $this->engine->getPriceDisplay();
        $strikethrough = $priceDisplay::priceHtmlIsAllowToStrikethroughPrice($this->context);

        $totalProductPriceFormatter = new TotalProductPriceFormatter($this->context);
        /** @var ConcreteProductPriceHtml $prodPriceDisplay */
        $prodPriceDisplay = ProductPriceDisplay::create($this->context, $processedProduct);
        if ( ! $prodPriceDisplay) {
            return null;
        }
        $prodPriceDisplay->withStriked($strikethrough);

        if ($prodPriceDisplay instanceof SimpleProductPriceHtml || $prodPriceDisplay instanceof VariationProductPriceHtml) {
            if ( ! $priceDisplay->priceHtmlIsModifyNeeded()) {
                return array(
                    'price_html'          => $prodPriceDisplay->getPriceHtml(),
                    'subtotal_html'       => $prodPriceDisplay->getFormattedSubtotalHtml($qty),
                    'total_price_html'    => $totalProductPriceFormatter->getHtmlNotIsModifyNeeded($product, $qty),
                    'original_price'      => $this->priceFunctions->getPriceToDisplay($product),
                    'discounted_price'    => $this->priceFunctions->getPriceToDisplay($product),
                    'original_subtotal'   => $this->priceFunctions->getPriceToDisplay($product, array('qty' => $qty)),
                    'discounted_subtotal' => $this->priceFunctions->getPriceToDisplay($product, array('qty' => $qty)),
                );
            } elseif ( ! $processedProduct->areRulesAppliedAtAll()) {
                return array(
                    'price_html'          => $prodPriceDisplay->getFormattedPriceHtml($prodPriceDisplay->getPriceHtml()),
                    'subtotal_html'       => $prodPriceDisplay->getFormattedSubtotalHtml($qty),
                    'total_price_html'    => $totalProductPriceFormatter->getHtmlAreRulesNotApplied($product, $qty),
                    'original_price'      => $prodPriceDisplay->getOriginalPrice(),
                    'discounted_price'    => $prodPriceDisplay->getDiscountedPrice(),
                    'original_subtotal'   => $prodPriceDisplay->getOriginalSubtotal($qty),
                    'discounted_subtotal' => $prodPriceDisplay->getDiscountedSubtotal($qty),
                );
            } else {
                return array(
                    'price_html'          => $prodPriceDisplay->getFormattedPriceHtml($prodPriceDisplay->getPriceHtml()),
                    'subtotal_html'       => $prodPriceDisplay->getFormattedSubtotalHtml($qty),
                    'total_price_html'    => $totalProductPriceFormatter->getHtmlProcessedProductSimple($processedProduct),
                    'original_price'      => $prodPriceDisplay->getOriginalPrice(),
                    'discounted_price'    => $prodPriceDisplay->getDiscountedPrice(),
                    'original_subtotal'   => $prodPriceDisplay->getOriginalSubtotal($qty),
                    'discounted_subtotal' => $prodPriceDisplay->getDiscountedSubtotal($qty),
                );
            }
        } elseif ($prodPriceDisplay instanceof VariableProductPriceHtml) {
            if ( ! $priceDisplay->priceHtmlIsModifyNeeded()) {
                return array(
                    'price_html'       => $prodPriceDisplay->getPriceHtml(),
                    'subtotal_html'    => $prodPriceDisplay->getFormattedSubtotalHtml($qty),
                    'total_price_html' => "",

                    'lowest_original_price'    => $prodPriceDisplay->getLowestOriginalPrice(),
                    'highest_original_price'   => $prodPriceDisplay->getHighestOriginalPrice(),
                    'lowest_discounted_price'  => $prodPriceDisplay->getLowestDiscountedPrice(),
                    'highest_discounted_price' => $prodPriceDisplay->getHighestDiscountedPrice(),

                    'lowest_original_subtotal'    => $prodPriceDisplay->getLowestOriginalSubtotal($qty),
                    'highest_original_subtotal'   => $prodPriceDisplay->getHighestOriginalSubtotal($qty),
                    'lowest_discounted_subtotal'  => $prodPriceDisplay->getLowestDiscountedSubtotal($qty),
                    'highest_discounted_subtotal' => $prodPriceDisplay->getHighestDiscountedSubtotal($qty),
                );
            } elseif ( ! $processedProduct->areRulesApplied()) {
                return array(
                    'price_html'          => $prodPriceDisplay->getFormattedPriceHtml($prodPriceDisplay->getPriceHtml()),
                    'subtotal_html'       => $prodPriceDisplay->getFormattedSubtotalHtml($qty),
                    'total_price_html'    => $totalProductPriceFormatter->getHtmlAreRulesNotApplied($product, $qty),

                    'lowest_original_price'    => $prodPriceDisplay->getLowestOriginalPrice(),
                    'highest_original_price'   => $prodPriceDisplay->getHighestOriginalPrice(),
                    'lowest_discounted_price'  => $prodPriceDisplay->getLowestDiscountedPrice(),
                    'highest_discounted_price' => $prodPriceDisplay->getHighestDiscountedPrice(),

                    'lowest_original_subtotal'    => $prodPriceDisplay->getLowestOriginalSubtotal($qty),
                    'highest_original_subtotal'   => $prodPriceDisplay->getHighestOriginalSubtotal($qty),
                    'lowest_discounted_subtotal'  => $prodPriceDisplay->getLowestDiscountedSubtotal($qty),
                    'highest_discounted_subtotal' => $prodPriceDisplay->getHighestDiscountedSubtotal($qty),
                );
            } else {
                return array(
                    'price_html'       => $prodPriceDisplay->getFormattedPriceHtml($prodPriceDisplay->getPriceHtml()),
                    'subtotal_html'    => $prodPriceDisplay->getFormattedSubtotalHtml($qty),
                    'total_price_html' => "",

                    'lowest_original_price'    => $prodPriceDisplay->getLowestOriginalPrice(),
                    'highest_original_price'   => $prodPriceDisplay->getHighestOriginalPrice(),
                    'lowest_discounted_price'  => $prodPriceDisplay->getLowestDiscountedPrice(),
                    'highest_discounted_price' => $prodPriceDisplay->getHighestDiscountedPrice(),

                    'lowest_original_subtotal'    => $prodPriceDisplay->getLowestOriginalSubtotal($qty),
                    'highest_original_subtotal'   => $prodPriceDisplay->getHighestOriginalSubtotal($qty),
                    'lowest_discounted_subtotal'  => $prodPriceDisplay->getLowestDiscountedSubtotal($qty),
                    'highest_discounted_subtotal' => $prodPriceDisplay->getHighestDiscountedSubtotal($qty),
                );
            }
        } elseif ($prodPriceDisplay instanceof GroupedProductPriceHtml) {
            if ( ! $priceDisplay->priceHtmlIsModifyNeeded()) {
                return array(
                    'price_html'       => $prodPriceDisplay->getPriceHtml(),
                    'subtotal_html'    => $prodPriceDisplay->getFormattedSubtotalHtml($qty),
                    'total_price_html' => "",

                    'lowest_original_price'    => "",
                    'highest_original_price'   => "",
                    'lowest_discounted_price'  => $prodPriceDisplay->getLowestDiscountedPrice(),
                    'highest_discounted_price' => $prodPriceDisplay->getHighestDiscountedPrice(),

                    'lowest_original_subtotal'    => "",
                    'highest_original_subtotal'   => "",
                    'lowest_discounted_subtotal'  => $prodPriceDisplay->getLowestDiscountedSubtotal($qty),
                    'highest_discounted_subtotal' => $prodPriceDisplay->getHighestDiscountedSubtotal($qty),
                );
            } elseif ( ! $processedProduct->areRulesApplied()) {
                return array(
                    'price_html'          => $prodPriceDisplay->getFormattedPriceHtml($prodPriceDisplay->getPriceHtml()),
                    'subtotal_html'       => $prodPriceDisplay->getFormattedSubtotalHtml($qty),
                    'total_price_html'    => $totalProductPriceFormatter->getHtmlAreRulesNotApplied($product, $qty),

                    'lowest_original_price'    => "",
                    'highest_original_price'   => "",
                    'lowest_discounted_price'  => $prodPriceDisplay->getLowestDiscountedPrice(),
                    'highest_discounted_price' => $prodPriceDisplay->getHighestDiscountedPrice(),

                    'lowest_original_subtotal'    => "",
                    'highest_original_subtotal'   => "",
                    'lowest_discounted_subtotal'  => $prodPriceDisplay->getLowestDiscountedSubtotal($qty),
                    'highest_discounted_subtotal' => $prodPriceDisplay->getHighestDiscountedSubtotal($qty),
                );
            } else {
                return array(
                    'price_html'       => $prodPriceDisplay->getFormattedPriceHtml($prodPriceDisplay->getPriceHtml()),
                    'subtotal_html'    => $prodPriceDisplay->getFormattedSubtotalHtml($qty),
                    'total_price_html' => "",

                    'lowest_original_price'    => "",
                    'highest_original_price'   => "",
                    'lowest_discounted_price'  => $prodPriceDisplay->getLowestDiscountedPrice(),
                    'highest_discounted_price' => $prodPriceDisplay->getHighestDiscountedPrice(),

                    'lowest_original_subtotal'    => "",
                    'highest_original_subtotal'   => "",
                    'lowest_discounted_subtotal'  => $prodPriceDisplay->getLowestDiscountedSubtotal($qty),
                    'highest_discounted_subtotal' => $prodPriceDisplay->getHighestDiscountedSubtotal($qty),
                );
            }
        }

        return null;
    }
}
