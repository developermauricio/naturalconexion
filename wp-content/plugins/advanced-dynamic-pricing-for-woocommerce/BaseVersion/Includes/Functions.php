<?php

namespace ADP\BaseVersion\Includes;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\CartProcessor\CartBuilder;
use ADP\BaseVersion\Includes\Core\CartCalculator;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Database\RulesCollection;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedGroupedProduct;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedProductSimple;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedVariableProduct;
use ADP\BaseVersion\Includes\PriceDisplay\Processor;
use ADP\BaseVersion\Includes\WC\WcCartItemFacade;
use ADP\Factory;
use Exception;
use WC_Product;

defined('ABSPATH') or exit;

class Functions
{
    /**
     * @var self
     */
    protected static $instance = null;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Engine
     */
    protected $globalEngine;

    /**
     * @var Engine
     */
    protected $productProcessor;

    /**
     * @var CartBuilder
     */
    protected $cartBuilder;

    /**
     * @param Engine|null $engine
     */
    public function __construct($engine = null)
    {
        $this->context          = adp_context();
        $this->globalEngine     = $engine;
        $this->productProcessor = new Processor();
        $this->cartBuilder      = new CartBuilder();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
        $this->productProcessor->withContext($context);
        $this->cartBuilder->withContext($context);
    }

    /**
     * @param Engine|null $engine
     */
    public static function install($engine = null)
    {
        if (static::$instance === null) {
            static::$instance = new static($engine);
        }
    }

    /**
     * @return bool
     */
    protected function isGlobalEngineExisting()
    {
        return isset($this->globalEngine);
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * @return array<int,float>
     */
    public function getGiftedCartProducts()
    {
        $products = array();

        foreach (WC()->cart->cart_contents as $ket => $wcCartItem) {
            $facade = new WcCartItemFacade($this->context, $wcCartItem, $ket);

            if ($facade->isFreeItem()) {
                $prodId = $facade->getVariationId() ? $facade->getVariationId() : $facade->getProductId();
                $qty    = $facade->getQty();

                if ( ! isset($products[$prodId])) {
                    $products[$prodId] = floatval(0);
                }

                $products[$prodId] += $qty;
            }
        }

        return $products;
    }

    /**
     * @param int|WC_Product $theProd
     * @param float $qty
     * @param bool $useEmptyCart
     *
     * @return array<int,Rule>
     */
    public function getActiveRulesForProduct($theProd, $qty = 1.0, $useEmptyCart = false)
    {
        if ($useEmptyCart || ! $this->isGlobalEngineExisting()) {
            $productProcessor = $this->productProcessor;
            $cart             = $this->cartBuilder->create(WC()->customer, WC()->session);
            $productProcessor->withCart($cart);
        } else {
            $productProcessor = $this->globalEngine->getProductProcessor();
        }

        if (is_numeric($theProd)) {
            $product = CacheHelper::getWcProduct($theProd);
        } elseif ($theProd instanceof WC_Product) {
            $product = clone $theProd;
        } else {
            return array();
        }

        $processedProduct = $productProcessor->calculateProduct($product, $qty);

        if (is_null($processedProduct)) {
            return array();
        }

        if ($processedProduct instanceof ProcessedVariableProduct || $processedProduct instanceof ProcessedGroupedProduct) {
            return array();
        }

        $rules = array();

        /** @var ProcessedProductSimple $processedProduct */
        foreach ($processedProduct->getHistory() as $ruleId => $amounts) {
            $rules[] = $ruleId;
        }

        return CacheHelper::loadRules($rules, $this->context);
    }

    /**
     *
     * @param array $listOfProducts
     * array[]['product_id']
     * array[]['qty']
     * array[]['cart_item_data'] Optional
     * @param boolean $plain Type of returning array. With False returns grouped by rules
     *
     * @return array
     * @throws Exception
     *
     */
    public function getDiscountedProductsForCart($listOfProducts, $plain = false)
    {
        if ( ! did_action('wp_loaded')) {
            _doing_it_wrong(__FUNCTION__,
                sprintf(__('%1$s should not be called before the %2$s action.', 'woocommerce'),
                    'getDiscountedProductsForCart', 'wp_loaded'), WC_ADP_VERSION);

            return array();
        }
        $result = array();
        $cart   = $this->cartBuilder->create(WC()->customer, WC()->session);

        foreach ($listOfProducts as $data) {
            if ( ! isset($data['product_id'], $data['qty'])) {
                continue;
            }

            $prodId       = intval($data['product_id']);
            $qty          = floatval($data['qty']);
            $cartItemData = array();
            if (isset($data['cart_item_data']) && is_array($data['cart_item_data'])) {
                $cartItemData = $data['cart_item_data'];
            }

            if ( ! $product = CacheHelper::getWcProduct($prodId)) {
                continue;
            }

            $cartItem = WcCartItemFacade::createFromProduct($this->context, $product, $cartItemData);
            $cartItem->setQty($qty);
            $cart->addToCart($cartItem->createItem());
        }

        $activeRuleCollection = CacheHelper::loadActiveRules($this->context);

        foreach ($activeRuleCollection->getRules() as $rule) {
            if ( ! ($rule instanceof SingleItemRule)) {
                continue;
            }

            /** @var SingleItemRule $rule */
            try {
                $ruleProc = $rule->buildProcessor($this->context);
            } catch (Exception $e) {
                continue;
            }

            if ( ! $rule->getConditions() || ! $ruleProc->isRuleMatchedCart($cart)) {
                continue;
            }

            $filters = $rule->getFilters();

            if ( ! $filters) {
                continue;
            }

            $listOfProductIds = array();
            foreach ($filters as $filter) {
                if ($filter->getType() === $filter::TYPE_PRODUCT && $filter->getMethod() === $filter::METHOD_IN_LIST) {
                    $listOfProductIds = array_merge($listOfProductIds, $filter->getValue());
                } else {
                    $listOfProductIds = null;
                    break;
                }
            }

            if ( ! $listOfProductIds) {
                continue;
            }

            $items = array();
            foreach ($listOfProductIds as $index => $prodId) {
                if ($product = CacheHelper::getWcProduct($prodId)) {
                    $cartItem = WcCartItemFacade::createFromProduct($this->context, $product);
                    $item     = $cartItem->createItem();
                    $item->addAttr($item::ATTR_TEMP);
                    $items[] = $item;
                    $cart->addToCart($item);
                }
            }

            if ( ! $items) {
                continue;
            }

            if ($plain) {
                /** @var CartCalculator $calc $calc */
                /** @see CartCalculator::make() */
                $calc = Factory::callStaticMethod("Core_CartCalculator", 'make');
                $calc->processCart($cart);
            } else {
                $ruleCollection = new RulesCollection(array($rule));
                $calc           = Factory::get("Core_CartCalculator", $ruleCollection);
                $calc->processCart($cart);
            }

            $ruleResult = array();
            foreach ($cart->getItems() as $item) {
                if ( ! $item->hasAttr($item::ATTR_TEMP)) {
                    continue;
                }

                $ruleResult[$item->getWcItem()->getProduct()->get_id()] = array(
                    'original_price'   => $item->getOriginalPrice(),
                    'discounted_price' => $item->getPrice(),
                );
            }

            if ( ! $ruleResult) {
                continue;
            }

            if ($plain) {
                if ( ! $result) {
                    $result = $ruleResult;
                } else {
                    foreach ($result as &$resultItem) {
                        foreach ($ruleResult as $k => $ruleItem) {
                            if ($ruleItem['product_id'] == $resultItem['product_id']) {
                                if ($resultItem['discounted_price'] > $ruleItem['discounted_price']) {
                                    $resultItem['discounted_price'] = $ruleItem['discounted_price'];
                                }
                                if ($resultItem['original_price'] < $ruleItem['original_price']) {
                                    $resultItem['original_price'] = $ruleItem['original_price'];
                                }
                                unset($ruleResult[$k]);
                                $ruleResult = array_values($ruleResult);
                                break;
                            }
                        }
                    }

                    $result = array_merge($result, $ruleResult);
                }
            } else {
                $result[] = $ruleResult;
            }

        }


        return $result;
    }

    /**
     * @param int|WC_product $theProd
     * @param float $qty
     * @param bool $useEmptyCart
     *
     * @return float|array|null
     * float for simple product
     * array is (min, max) range for variable
     * null if product is incorrect
     */
    public function getDiscountedProductPrice($theProd, $qty, $useEmptyCart = true)
    {
        if ($useEmptyCart || ! $this->isGlobalEngineExisting()) {
            $productProcessor = $this->productProcessor;
            $cart             = $this->cartBuilder->create(WC()->customer, WC()->session);
            $productProcessor->withCart($cart);
        } else {
            $productProcessor = $this->globalEngine->getProductProcessor();
        }

        if (is_numeric($theProd)) {
            $product = CacheHelper::getWcProduct($theProd);
        } elseif ($theProd instanceof WC_Product) {
            $product = clone $theProd;
        } else {
            return null;
        }

        $processedProduct = $productProcessor->calculateProduct($product, $qty);

        if (is_null($processedProduct)) {
            return array();
        }

        if ($processedProduct instanceof ProcessedVariableProduct) {
            return array($processedProduct->getLowestPrice(), $processedProduct->getHighestPrice());
        } elseif ($processedProduct instanceof ProcessedGroupedProduct) {
            return array($processedProduct->getLowestPrice(), $processedProduct->getHighestPrice());
        } elseif ($processedProduct instanceof ProcessedProductSimple) {
            return $processedProduct->getPrice();
        } else {
            return null;
        }
    }

    public function processCartManually()
    {
        if ($this->isGlobalEngineExisting()) {
            $this->globalEngine->process(false);
        }
    }

    /**
     * @param int|WC_product $theProd
     * @param float $qty
     * @param bool $useEmptyCart
     *
     * @return ProcessedProductSimple|ProcessedVariableProduct|ProcessedGroupedProduct|null
     */
    public function calculateProduct($theProd, $qty, $useEmptyCart = true)
    {
        if ($useEmptyCart || ! $this->isGlobalEngineExisting()) {
            $productProcessor = $this->productProcessor;
            $cart             = $this->cartBuilder->create(WC()->customer, WC()->session);
            $productProcessor->withCart($cart);
        } else {
            $productProcessor = $this->globalEngine->getProductProcessor();
        }

        if (is_numeric($theProd)) {
            $product = CacheHelper::getWcProduct($theProd);
        } elseif ($theProd instanceof WC_Product) {
            $product = clone $theProd;
        } else {
            return null;
        }

        return $productProcessor->calculateProduct($product, $qty);
    }

    /**
     * @param  SingleItemRule $rule
     * @param bool $useEmptyCart
     *
     * @return boolean
     */
    public function isRuleMatchedCart($rule, $useEmptyCart = true) {
        $cart   = $this->cartBuilder->create(WC()->customer, WC()->session);
        /** @var SingleItemRule $rule */
        try {
            $ruleProc = $rule->buildProcessor($this->context);
        } catch (Exception $e) {
            return false;
        }
        return $ruleProc->isRuleMatchedCart($cart);
    }

    public function recalculatePersistenceCache() {
        $ruleRepository           = new RuleRepository();
        $persistentRuleRepository = new PersistentRuleRepository();

        $rules = $ruleRepository->getRules( [ 'rule_types'      => [ 'persistent' ],
                                              'active_only'     => true,
                                              'include_deleted' => false,
        ] );

        $persistentRuleRepository->truncate();
        foreach ( $rules as $rule ) {
            $persistentRuleRepository->addRule( $persistentRuleRepository->getAddRuleData( $rule->id, $this->context ),
                $rule->id );
        }
    }

}
