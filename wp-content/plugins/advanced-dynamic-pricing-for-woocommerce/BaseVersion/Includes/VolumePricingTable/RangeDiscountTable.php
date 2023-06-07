<?php

namespace ADP\BaseVersion\Includes\VolumePricingTable;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\CartProcessor\CartBuilder;
use ADP\BaseVersion\Includes\Compatibility\MixAndMatchCmp;
use ADP\BaseVersion\Includes\Compatibility\WpcBundleCmp;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\CartCalculator;
use ADP\BaseVersion\Includes\Core\Rule\PersistentRule;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Discount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Range;
use ADP\BaseVersion\Includes\Core\RuleProcessor\PersistentRuleProcessor;
use ADP\BaseVersion\Includes\Core\RuleProcessor\SingleItemRuleProcessor;
use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;
use ADP\BaseVersion\Includes\CustomizerExtensions\CustomizerExtensions;
use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepositoryInterface;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepositoryInterface;
use ADP\BaseVersion\Includes\Database\RulesCollection;
use ADP\BaseVersion\Includes\Database\RuleStorage;
use ADP\BaseVersion\Includes\Enums\RuleTypeEnum;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedGroupedProduct;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedProductSimple;
use ADP\BaseVersion\Includes\PriceDisplay\ProcessedVariableProduct;
use ADP\BaseVersion\Includes\PriceDisplay\Processor;
use ADP\BaseVersion\Includes\WC\PriceFunctions;
use ADP\Factory;
use Exception;
use WC_Product;

defined('ABSPATH') or exit;

class RangeDiscountTable
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CustomizerExtensions
     */
    protected $customizer;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var PersistentRuleRepositoryInterface
     */
    protected $persistentRuleRepository;

    /**
     * @var FiltersFormatter
     */
    protected $filtersFormatter;

    /**
     * @var PriceFunctions
     */
    protected $priceFunctions;

    /**
     * @var ProductVolumePricingTableProperties
     */
    protected $productContextOptions;

    /**
     * @var CategoryBulkTableThemeProperties
     */
    protected $categoryContextOptions;

    /**
     * @param Context|CustomizerExtensions $contextOrCustomizer
     * @param null $deprecated
     */
    public function __construct($contextOrCustomizer, $deprecated = null)
    {
        $this->context                  = adp_context();
        $this->customizer               = $contextOrCustomizer instanceof CustomizerExtensions ? $contextOrCustomizer : $deprecated;
        $this->ruleRepository           = new RuleRepository();
        $this->persistentRuleRepository = new PersistentRuleRepository();
        $this->filtersFormatter         = Factory::get("VolumePricingTable_FiltersFormatter", $this->context);
        $this->priceFunctions           = new PriceFunctions();

        $this->productContextOptions  = new ProductBulkTableThemeProperties();
        $this->categoryContextOptions = new CategoryVolumePricingTableProperties();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function withRuleRepository(RuleRepositoryInterface $repository)
    {
        $this->ruleRepository = $repository;
    }

    public function withPersistentRuleRepository(PersistentRuleRepositoryInterface $repository)
    {
        $this->persistentRuleRepository = $repository;
    }

    /**
     * @param int|null $productId
     * @param array<string, string> $attributes
     *
     * @return string
     */
    public function getProductTableContent($productId = null, $attributes = array())
    {
        if ( ! $productId) {
            global $product;

            if ( ! isset($product)) {
                return "";
            }

            /**
             * @var $product WC_Product
             */
            $productId = $product->get_id();

            if ( ! $productId) {
                return "";
            }
        } else {
            $product = CacheHelper::getWcProduct($productId);
        }

        $wpCleverBundleCmp = new WpcBundleCmp();
        if ($wpCleverBundleCmp->isActive() && $wpCleverBundleCmp->isBundleProduct($product)) {
            return "";
        }

        $mixAndMatchCmp = new MixAndMatchCmp();
        if ($mixAndMatchCmp->isActive() && ($mixAndMatchCmp->isMixAndMatchProduct($product))) {
            return "";
        }

        if ($product instanceof \WC_Product_Variation && array_filter($attributes)) {
            $product->set_attributes(array_filter($attributes));
        }

        if ( $product instanceof \WC_Product_Variable ) {
            $availableVariationIds = $product->get_visible_children();

            if (count($availableVariationIds) === 0) {
                return '';
            }

            $availableProductsIDs = array_map(
                'intval',
                array_merge([$product->get_id()], $availableVariationIds)
            );
            $content              = '<span class="wdp_bulk_table_content" ';
            if ( $this->context->getOption( 'hide_parent_bulk_table' ) ) {
                $content .= 'style="display: none" ';
            }
            $content .= 'data-available-ids="' . json_encode( $availableProductsIDs ) . '">';

            try {
                $table = $this->getProductTable( $product );
                if ( $table ) {
                    $content .= $table->getHtml();
                }
            } catch ( Exception $exception ) {
            }

            $content .= '</span>';
        } else {
            $content = '<span class="wdp_bulk_table_content"> ';
            try {
                $table = $this->getProductTable( $product );
                if ( $table ) {
                    $content .= $table->getHtml();
                }
            } catch ( Exception $exception ) {
            }
            $content .= '</span>';
        }

        return $content;
    }

    /**
     * @param int|null $categoryID
     *
     * @return string
     */
    public function getCategoryTableContent($categoryID = null)
    {
        $content = "";

        try {
            $table = $this->getCategoryTable($categoryID);
            if ($table) {
                $content .= '<span class="wdp_bulk_table_content">';
                $content .= $table->getHtml();
                $content .= '</span>';
            }
        } catch (Exception $exception) {
            $content = "";
        }

        return $content;
    }

    /**
     * @param WC_Product $product
     *
     * @return SingleItemRule|null
     * @throws Exception
     */
    public function findRuleForProductTable($product)
    {
        if ( ! $product || ! ($product instanceof WC_Product)) {
            return null;
        }

        $context = $this->context;
        $cartBuilder = new CartBuilder($context);
        $cart        = $cartBuilder->create(WC()->customer, WC()->session);
        $cartBuilder->populateCart($cart, WC()->cart);

        $rule = $this->findProductOnlyRule($cart, $product);

        if (! $rule) {
            /** @var SingleItemRuleProcessor[] $ruleProcessors */
            $ruleProcessors = array();
            foreach (CacheHelper::loadActiveRules($context)->getRules() as $tmpRule) {
                // discount table only for 'SingleItem' rule
                if ($tmpRule instanceof SingleItemRule && $tmpRule->getProductRangeAdjustmentHandler()) {
                    $ruleProcessors[] = $tmpRule->buildProcessor($context);
                }
            }

            $matchedRuleProcessor = null;
            if ($product instanceof \WC_Product_Variable) {
                $availableProductsIDs = array_merge([$product->get_id()], $product->get_visible_children());

                foreach ($availableProductsIDs as $tmpProductId) {
                    $tmpProduct = CacheHelper::getWcProduct($tmpProductId);
                    if ( ! $tmpProduct) {
                        continue;
                    }

                    foreach ($ruleProcessors as $ruleProcessor) {
                        if ($ruleProcessor->isProductMatched($cart, $tmpProduct,
                            ! $context->getOption('discount_table_ignores_conditions'))) {
                            $matchedRuleProcessor = $ruleProcessor;
                            break;
                        }
                    }
                }
            } else {
                if ($tmpProduct = CacheHelper::getWcProduct($product->get_id())) {
                    $tmpProduct->set_props($product->get_changes());

                    foreach ($ruleProcessors as $ruleProcessor) {
                        if ($ruleProcessor->isProductMatched($cart, $tmpProduct,
                            ! $context->getOption('discount_table_ignores_conditions'))) {
                            $matchedRuleProcessor = $ruleProcessor;
                            break;
                        }
                    }
                }
            }

            if ( ! $matchedRuleProcessor) {
                return null;
            }

            $rule = clone $matchedRuleProcessor->getRule();
        }

        if ($context->getOption('discount_table_ignores_conditions')) {
            $rule->setConditions(array());
        }


        if ($this->context->isShowBulkTablePricesIncludingCoupons()) {
            //turn OFF checkbox "Don't modify product prices and show discount as coupon"
            //to correctly show prices in bulk table
            $rule->getProductRangeAdjustmentHandler()->setReplaceAsCartAdjustment(false);
        }

        return $rule;
    }

    /**
     * @param Cart $cart
     * @param WC_Product $product
     * @return Rule|null
     */
    protected function findProductOnlyRule($cart, $product)
    {
        $context = $this->context;
        $rule = null;

        if ($product instanceof \WC_Product_Variable && $this->context->isCheckParentsWhenFindingProductOnlyRule()) {
            $availableProductsIDs = array_merge([$product->get_id()], $product->get_visible_children());

            foreach ($availableProductsIDs as $tmpProductId) {
                $tmpProduct = CacheHelper::getWcProduct($tmpProductId);
                if (!$tmpProduct) {
                    continue;
                }

                $objects = $this->persistentRuleRepository->getCacheWithProduct($tmpProduct);

                foreach ($objects as $object) {
                    if ($object && $object->rule && $object->price !== null && $object->rule->getProductRangeAdjustmentHandler(
                        )) {
                        $matchedRuleProcessor = $object->rule->buildProcessor($context);
                        if ($matchedRuleProcessor->isRuleOptionalMatchedCart(
                            $cart,
                            true,
                            !$context->getOption('discount_table_ignores_conditions')
                        )
                        ) {
                            $rule = $object->rule;
                            break 2;
                        }
                    }
                }
            }
        } else {
            $objects = $this->persistentRuleRepository->getCacheWithProduct($product);

            foreach ($objects as $object) {
                if ($object && $object->rule && $object->price !== null && $object->rule->getProductRangeAdjustmentHandler(
                    )) {
                    $matchedRuleProcessor = $object->rule->buildProcessor($context);
                    if ($matchedRuleProcessor->isRuleOptionalMatchedCart(
                        $cart,
                        true,
                        !$context->getOption('discount_table_ignores_conditions')
                    )
                    ) {
                        $rule = $object->rule;
                        break;
                    }
                }
            }
        }

        return $rule;
    }

    /**
     * @param SingleItemRule $rule
     *
     * @return Processor|null
     */
    public function makePriceProcessor($rule)
    {
        $context     = $this->context;
        $cartBuilder = new CartBuilder($context);

        $bulk_table_calculation_mode = $context->getOption('bulk_table_calculation_mode');

        if ($bulk_table_calculation_mode === 'only_bulk_rule_table') {
            /** @var CartCalculator $calc */
            $calc           = Factory::get("Core_CartCalculator", new RulesCollection(array($rule)));
            $priceProcessor = new Processor($calc);
        } elseif ($bulk_table_calculation_mode === 'all') {
            if ( ! $context->getOption('discount_table_ignores_conditions')) {
                $priceProcessor = new Processor();
            } else {
                $newRules = array();
                foreach (CacheHelper::loadActiveRules()->getRules() as $loopRule) {
                    $newLoopRule = clone $loopRule;
                    $newLoopRule->setConditions(array());
                    $newRules[] = $newLoopRule;
                }

                /** @var CartCalculator $calc */
                $calc = Factory::get("Core_CartCalculator", new RulesCollection($newRules));

                $priceProcessor = new Processor($calc);
            }
        } else {
            return null;
        }

        $priceProcessor->withCart($cartBuilder->create(WC()->customer, WC()->session));

        return $priceProcessor;
    }

    /**
     * @return ProductVolumePricingTableProperties
     */
    protected function buildProductContextOptions()
    {
        $themeOptions = $this->customizer->getThemeOptions()->productBulkTable;

        $contextOptions                                       = new ProductVolumePricingTableProperties();
        $contextOptions->isUseMessageAsTitle                  = $themeOptions->styleHeader->isUseMessageAsTitle;
        $contextOptions->headerBulkTitle                      = $themeOptions->styleHeader->headerBulkTitle;
        $contextOptions->headerTierTitle                      = $themeOptions->styleHeader->headerTierTitle;
        $contextOptions->tableLayout                          = $themeOptions->options->tableLayout;
        $contextOptions->quantityColumnTitle                  = $themeOptions->styleColumns->quantityColumnTitle;
        $contextOptions->isShowFixedDiscountColumn            = $themeOptions->options->isShowFixedDiscountColumn;
        $contextOptions->discountColumnTitleForFixedPriceRule = $themeOptions->styleColumns->discountColumnTitleForFixedPriceRule;
        $contextOptions->discountedPriceColumnTitleForFixedPriceRule = $themeOptions->styleColumns->discountedPriceColumnTitleForFixedPriceRule;
        $contextOptions->discountColumnTitle                  = $themeOptions->styleColumns->discountColumnTitle;
        $contextOptions->isShowDiscountedPrice                = $themeOptions->options->isShowDiscountedPrice;
        $contextOptions->discountedPriceColumnTitle           = $themeOptions->styleColumns->discountedPriceColumnTitle;
        $contextOptions->isShowFooter                         = $themeOptions->options->isShowFooter;

        return $contextOptions;
    }

    /**
     * @param WC_Product $product
     *
     * @return Table|null
     * @throws Exception
     */
    public function getProductTable($product)
    {
        $contextOptions = $this->buildProductContextOptions();
        foreach (get_object_vars($this->productContextOptions) as $key => $value) {
            if ($value !== null) {
                $contextOptions->$key = $value;
            }
        }

        $context = $this->context;

        $rule = $this->findRuleForProductTable($product);
        if ( ! $rule) {
            return null;
        }

        $priceProcessor = $this->makePriceProcessor($rule);
        if ( ! $priceProcessor) {
            return null;
        }

        $table = new Table($context);

        $handler = $rule->getProductRangeAdjustmentHandler();
        if ( ! $handler) {
            return null;
        }

        /** HEADER */
        $headerTitle = '';
        if ($contextOptions->isUseMessageAsTitle) {
            $headerTitle = __(
                apply_filters('wdp_format_bulk_table_message', $handler->getPromotionalMessage()),
                'advanced-dynamic-pricing-for-woocommerce'
            );
        } elseif ($handler::TYPE_BULK === $handler->getType()) {
            $headerTitle = $contextOptions->headerBulkTitle;
        } elseif ($handler::TYPE_TIER === $handler->getType()) {
            $headerTitle = $contextOptions->headerTierTitle;
        }
        $table->setTableHeader($headerTitle);

        /** COLUMNS AND ROWS */
        $ranges = $rule->getProductRangeAdjustmentHandler()->getRanges();

        if ($contextOptions->tableLayout === $contextOptions::LAYOUT_SIMPLE) {
            $this->fillSimpleProductTable($table, $contextOptions, $rule, $priceProcessor, $product);
        } elseif ($contextOptions->tableLayout === $contextOptions::LAYOUT_VERBOSE) {
            /** COLUMNS */
            $columns = $this->createColumnsForProductVerboseTable($contextOptions, $rule);
            foreach ($columns as $key => $title) {
                $table->addColumn($key, $title);
            }

            /** ROWS */
            foreach ($ranges as $range) {
                $row = array();
                foreach (array_keys($columns) as $key) {
                    $value     = $this->calculateColumnValueForProductVerboseTable(
                        $key,
                        $range,
                        $priceProcessor,
                        $product
                    );
                    $row[$key] = ! is_null($value) ? $value : "-";
                }
                $table->addRow($row);
            }
        }

        /** FOOTER */
        $this->setUpFooter($table, $rule, $contextOptions);

        return apply_filters('adp_discount_product_table', $table, $contextOptions, $product, $rule, $priceProcessor);
    }

    /**
     * @param Table $table
     * @param ProductVolumePricingTableProperties $contextOptions
     * @param SingleItemRule $rule
     * @param Processor $priceProcessor
     * @param \WC_Product $product
     */
    protected function fillSimpleProductTable(
        $table,
        $contextOptions,
        $rule,
        $priceProcessor,
        $product
    ) {
        $ranges = $rule->getProductRangeAdjustmentHandler()->getRanges();

        /** COLUMNS */
        $columns = array();
        foreach ($ranges as $index => $range) {
            if ($range->getFrom() == $range->getTo()) {
                $value = $range->getFrom();
            } else {
                if (is_infinite($range->getTo())) {
                    $value = $range->getFrom() . ' +';
                } else {
                    $value = $range->getFrom() . ' - ' . $range->getTo();
                }
            }

            $table->addColumn($index, apply_filters('adp_simple_discount_product_table_column', $value, $range));
            $columns[] = $range;
        }

        /**ROWS */
        $row = array();
        foreach (array_keys($columns) as $index) {
            $range    = $ranges[$index];
            $discount = $range->getData();

            $processedProd = $priceProcessor->calculateProduct($product, $range->getFrom());

            $value = null;
            if ( ! is_null($processedProd)) {
                if ($processedProd instanceof ProcessedVariableProduct || $processedProd instanceof ProcessedGroupedProduct) {
                    $lowestPriceProduct  = $processedProd->getLowestPriceProduct();
                    $highestPriceProduct = $processedProd->getHighestPriceProduct();

                    $value = "-";

                    if ( ! is_null($lowestPriceProduct) && ! is_null($highestPriceProduct)) {
                        $lowestPriceToDisplay  = $this->priceFunctions->getProcProductPriceToDisplay(
                            $lowestPriceProduct,
                            $lowestPriceProduct->getPrice($range->getFrom())
                        );
                        $highestPriceToDisplay = $this->priceFunctions->getProcProductPriceToDisplay(
                            $highestPriceProduct,
                            $highestPriceProduct->getPrice($range->getFrom())
                        );

                        if (
                            $discount->getType() === $discount::TYPE_PERCENTAGE
                            && $contextOptions->isSimpleLayoutForcePercentage
                        ) {
                            $value = "{$discount->getValue()}%";
                        } elseif ($contextOptions->isShowDiscountedPrice && $lowestPriceToDisplay === $highestPriceToDisplay) {
                            $value = $this->priceFunctions->format($lowestPriceToDisplay);
                        } elseif ($this->context->isShowPriceRangeInBulkTableForVariableProducts()) {
                            $value = $this->priceFunctions->format($lowestPriceToDisplay) . "-" . $this->priceFunctions->format($highestPriceToDisplay);
                        } else {
                            $value = null;
                        }
                    }
                } elseif ($processedProd instanceof ProcessedProductSimple) {
                    if (
                        $discount->getType() === $discount::TYPE_PERCENTAGE
                        && $contextOptions->isSimpleLayoutForcePercentage
                    ) {
                        $value = "{$discount->getValue()}%";
                    } elseif ( $contextOptions->isShowDiscountedPrice ) {
                        $priceToDisplay = $this->priceFunctions->getProcProductPriceToDisplay(
                            $processedProd,
                            $processedProd->getPrice($range->getFrom())
                        );
                        $value          = $this->priceFunctions->format($priceToDisplay);
                    }
                }

                if (
                    $discount->getType() === $discount::TYPE_PERCENTAGE
                    && $contextOptions->isSimpleLayoutForcePercentage
                ) {
                    $value = apply_filters('adp_simple_discount_product_table_cell_percentage',
                        $value,
                        $processedProd,
                        $range,
                        $product,
                        $priceProcessor
                    );
                } else {
                    $value = apply_filters('adp_simple_discount_product_table_cell_discounted_price',
                        $value,
                        $processedProd,
                        $range,
                        $product,
                        $priceProcessor
                    );
                }
            }

            $row[$index] = $value;
        }

        $table->addRow($row);
    }

    /**
     * @param ProductVolumePricingTableProperties $contextOptions
     * @param SingleItemRule $rule
     *
     * @return array
     */
    protected function createColumnsForProductVerboseTable($contextOptions, $rule)
    {
        $ranges         = $rule->getProductRangeAdjustmentHandler()->getRanges();
        $columns        = array();

        $columns['qty'] = _x(
            $contextOptions->quantityColumnTitle,
            'product bulk table qty column title',
            'advanced-dynamic-pricing-for-woocommerce'
        );

        $isFixedDiscount = false;
        foreach ($ranges as $index => $range) {
            /** @var Discount $discount */
            $discount = $range->getData();
            if ($discount->getType() === $discount::TYPE_FIXED_VALUE) {
                $isFixedDiscount = true;
            }
        }

        if ( ! $isFixedDiscount && $contextOptions->isShowDiscountedPrice) {
            $columns['discount_value'] = _x(
                $contextOptions->discountColumnTitle,
                'product bulk table discount value column title',
                'advanced-dynamic-pricing-for-woocommerce'
            );
        }


        if ($contextOptions->isShowFixedDiscountColumn) {
            if ($isFixedDiscount) {
                $columns['discounted_price'] = _x(
                    $contextOptions->discountedPriceColumnTitleForFixedPriceRule,
                    'product bulk table discounted price column title for fixed discount',
                    'advanced-dynamic-pricing-for-woocommerce'
                );
            } else {
                $columns['discounted_price'] = _x(
                    $contextOptions->discountedPriceColumnTitle,
                    'product bulk table discounted price column title',
                    'advanced-dynamic-pricing-for-woocommerce'
                );
            }
        }

        return $columns;
    }

    /**
     * @param string $key
     * @param Range $range
     * @param Processor $priceProcessor
     * @param \WC_Product $product
     *
     * @return string|null
     */
    protected function calculateColumnValueForProductVerboseTable(
        $key,
        $range,
        $priceProcessor,
        $product
    ) {
        $value    = null;
        $discount = $range->getData();

        switch ($key) {
            case 'qty':
                if ($range->getFrom() == $range->getTo()) {
                    $value = $range->getFrom();
                } else {
                    if (is_infinite($range->getTo())) {
                        $value = $range->getFrom() . ' +';
                    } else {
                        $value = $range->getFrom() . ' - ' . $range->getTo();
                    }
                }

                $value = apply_filters('adp_verbose_discount_product_table_cell_qty', $value, $range, $product,
                    $priceProcessor);
                break;
            case 'discount_value':
                if ($discount->getValue()) {
                    if ($discount::TYPE_PERCENTAGE === $discount->getType()) {
                        $value = "{$discount->getValue()}%";
                    } else {
                        $value = $this->priceFunctions->format($discount->getValue());
                    }
                }

                $value = apply_filters('adp_verbose_discount_product_table_cell_discount_value', $value, $range,
                    $product, $priceProcessor);
                break;
            case 'discounted_price':
                $processedProd = $priceProcessor->calculateProduct($product, $range->getFrom());
                $price         = null;

                if ( ! is_null($processedProd)) {
                    if ($processedProd instanceof ProcessedVariableProduct || $processedProd instanceof ProcessedGroupedProduct) {
                        $lowestPriceProduct  = $processedProd->getLowestPriceProduct();
                        $highestPriceProduct = $processedProd->getHighestPriceProduct();

                        $value = "-";

                        if ( ! is_null($lowestPriceProduct) && ! is_null($highestPriceProduct)) {
                            if ($this->context->getOption("bulk_table_prices_tax") === 'incl') {
                                $lowestPriceToDisplay = $this->priceFunctions->getPriceIncludingTax(
                                    $lowestPriceProduct->getProduct(),
                                    array(
                                        'price' => $lowestPriceProduct->getPrice($range->getFrom()),
                                        'qty'   => 1,
                                    )
                                );

                                $highestPriceToDisplay = $this->priceFunctions->getPriceIncludingTax(
                                    $highestPriceProduct->getProduct(),
                                    array(
                                        'price' => $highestPriceProduct->getPrice($range->getFrom()),
                                        'qty'   => 1,
                                    )
                                );
                            } elseif ($this->context->getOption("bulk_table_prices_tax") === 'excl') {
                                $lowestPriceToDisplay = $this->priceFunctions->getPriceExcludingTax(
                                    $lowestPriceProduct->getProduct(),
                                    array(
                                        'price' => $lowestPriceProduct->getPrice($range->getFrom()),
                                        'qty'   => 1,
                                    )
                                );

                                $highestPriceToDisplay = $this->priceFunctions->getPriceExcludingTax(
                                    $highestPriceProduct->getProduct(),
                                    array(
                                        'price' => $highestPriceProduct->getPrice($range->getFrom()),
                                        'qty'   => 1,
                                    )
                                );
                            } else {
                                $lowestPriceToDisplay  = $this->priceFunctions->getProcProductPriceToDisplay(
                                    $lowestPriceProduct,
                                    $lowestPriceProduct->getPrice($range->getFrom())
                                );
                                $highestPriceToDisplay = $this->priceFunctions->getProcProductPriceToDisplay(
                                    $highestPriceProduct,
                                    $highestPriceProduct->getPrice($range->getFrom())
                                );
                            }

                            if ($lowestPriceToDisplay === $highestPriceToDisplay) {
                                $price = $lowestPriceToDisplay;
                                $value = $this->priceFunctions->format($lowestPriceToDisplay);
                            } elseif ($this->context->isShowPriceRangeInBulkTableForVariableProducts()) {
                                $value = $this->priceFunctions->format($lowestPriceToDisplay) . "-" . $this->priceFunctions->format($highestPriceToDisplay);
                            }
                        }
                    } elseif ($processedProd instanceof ProcessedProductSimple) {
                        if ($this->context->getOption("bulk_table_prices_tax") === 'incl') {
                            $priceToDisplay = $this->priceFunctions->getPriceIncludingTax(
                                $processedProd->getProduct(),
                                array(
                                    'price' => $processedProd->getPrice($range->getFrom()),
                                    'qty'   => 1,
                                )
                            );
                        } elseif ($this->context->getOption("bulk_table_prices_tax") === 'excl') {
                            $priceToDisplay = $this->priceFunctions->getPriceExcludingTax(
                                $processedProd->getProduct(),
                                array(
                                    'price' => $processedProd->getPrice($range->getFrom()),
                                    'qty'   => 1,
                                )
                            );
                        } else {
                            $priceToDisplay = $this->priceFunctions->getProcProductPriceToDisplay(
                                $processedProd,
                                $processedProd->getPrice($range->getFrom())
                            );
                        }

                        $price = $priceToDisplay;
                        $value = $this->priceFunctions->format($priceToDisplay);
                    }
                }

                $value = apply_filters(
                    'adp_verbose_discount_product_table_cell_discounted_price',
                    $value,
                    $price,
                    $range,
                    $product,
                    $priceProcessor
                );
                break;
        }

        return $value;
    }

    /**
     * @param int|null $termId
     *
     * @return SingleItemRule|null
     * @throws Exception
     */
    public function findRuleForCategoryTable($termId)
    {
        if ( ! $termId || ! is_int($termId)) {
            return null;
        }

        $context     = $this->context;
        $cartBuilder = new CartBuilder($context);
        $cart        = $cartBuilder->create(WC()->customer, WC()->session);
        $cartBuilder->populateCart($cart, WC()->cart);

        $matchedRuleProcessor = null;

        $rows = $this->ruleRepository->getRules(
            array(
                'active_only' => true,
                'rule_types' => array(RuleTypeEnum::PERSISTENT()->getValue()),
                'filter_types' => array('product_categories'),
            )
        );

        /** @var RuleStorage $storage */
        $storage         = Factory::get("Database_RuleStorage");
        $ruleCollection = $storage->buildPersistentRules($rows);

        /** @var PersistentRuleProcessor[] $ruleProcessors */
        $ruleProcessors = array();
        foreach ( $ruleCollection->getRules() as $rule ) {
            if ($rule instanceof PersistentRule && $rule->getProductRangeAdjustmentHandler()) { // discount table only for 'SingleItem' rule
                $ruleProcessors[] = $rule->buildProcessor($context);
            }
        }

        foreach ($ruleProcessors as $ruleProcessor) {
            if ($ruleProcessor->isCategoryMatched($cart, $termId,
                ! $context->getOption('discount_table_ignores_conditions'))) {
                $matchedRuleProcessor = $ruleProcessor;
                break;
            }
        }

        if ( ! $matchedRuleProcessor) {
            /** @var SingleItemRuleProcessor[] $ruleProcessors */
            $ruleProcessors = array();
            foreach (CacheHelper::loadActiveRules($context)->getRules() as $rule) {
                if ($rule instanceof SingleItemRule && $rule->getProductRangeAdjustmentHandler()) { // discount table only for 'SingleItem' rule
                    $ruleProcessors[] = $rule->buildProcessor($context);
                }
            }

            foreach ($ruleProcessors as $ruleProcessor) {
                if ($ruleProcessor->isCategoryMatched($cart, $termId,
                    ! $context->getOption('discount_table_ignores_conditions'))) {
                    $matchedRuleProcessor = $ruleProcessor;
                    break;
                }
            }

            if ( ! $matchedRuleProcessor) {
                return null;
            }
        }

        $rule = clone $matchedRuleProcessor->getRule();

        if ($context->getOption('discount_table_ignores_conditions')) {
            $rule->setConditions(array());
        }

        return $rule;
    }

    /**
     * @return CategoryVolumePricingTableProperties
     */
    protected function buildCategoryContextOptions()
    {
        $themeOptions = $this->customizer->getThemeOptions()->categoryBulkTable;

        $contextOptions                                       = new CategoryVolumePricingTableProperties();
        $contextOptions->isUseMessageAsTitle                  = $themeOptions->styleHeader->isUseMessageAsTitle;
        $contextOptions->headerBulkTitle                      = $themeOptions->styleHeader->headerBulkTitle;
        $contextOptions->headerTierTitle                      = $themeOptions->styleHeader->headerTierTitle;
        $contextOptions->tableLayout                          = $themeOptions->options->tableLayout;
        $contextOptions->quantityColumnTitle                  = $themeOptions->styleColumns->quantityColumnTitle;
        $contextOptions->isShowFixedDiscountColumn            = $themeOptions->options->isShowFixedDiscountColumn;
        $contextOptions->discountColumnTitleForFixedPriceRule = $themeOptions->styleColumns->discountColumnTitleForFixedPriceRule;
        $contextOptions->discountColumnTitle                  = $themeOptions->styleColumns->discountColumnTitle;

        return $contextOptions;
    }

    /**
     * @param int|null $termId
     *
     * @return Table|null
     * @throws Exception
     */
    public function getCategoryTable($termId = null)
    {
        if ( ! $termId) {
            if (is_tax()) {
                global $wp_query;
                if (isset($wp_query->queried_object->term_id)) {
                    $termId = $wp_query->queried_object->term_id;
                }
            }

            if ( ! $termId) {
                return null;
            }
        }

        $contextOptions = $this->buildCategoryContextOptions();
        foreach (get_object_vars($this->categoryContextOptions) as $key => $value) {
            if ($value !== null) {
                $contextOptions->$key = $value;
            }
        }

        $context      = $this->context;

        if ( ! ($rule = $this->findRuleForCategoryTable($termId))) {
            return null;
        }

        $table   = new Table($context);
        $handler = $rule->getProductRangeAdjustmentHandler();
        if ( ! $handler) {
            return null;
        }

        /** HEADER */
        $headerTitle = '';
        if ($contextOptions->isUseMessageAsTitle) {
            $headerTitle = __(
                apply_filters('wdp_format_bulk_table_message', $handler->getPromotionalMessage()),
                'advanced-dynamic-pricing-for-woocommerce'
            );
        } elseif ($handler::TYPE_BULK === $handler->getType()) {
            $headerTitle = $contextOptions->headerBulkTitle;
        } elseif ($handler::TYPE_TIER === $handler->getType()) {
            $headerTitle = $contextOptions->headerTierTitle;
        }
        $table->setTableHeader($headerTitle);

        /** COLUMNS AND ROWS */
        $ranges = $rule->getProductRangeAdjustmentHandler()->getRanges();
        if ($contextOptions->tableLayout === $contextOptions::LAYOUT_SIMPLE) {
            $this->fillSimpleCategoryTable($table, $contextOptions, $rule);
        } elseif ($contextOptions->tableLayout === $contextOptions::LAYOUT_VERBOSE) {
            /** COLUMNS */
            $columns = $this->createColumnsForCategoryVerboseTable($contextOptions, $rule);
            foreach ($columns as $key => $title) {
                $table->addColumn($key, $title);
            }

            /** ROWS */
            foreach ($ranges as $range) {
                $row = array();
                foreach (array_keys($columns) as $key) {
                    $value     = $this->calculateColumnValueForCategoryVerboseTable($key, $range);
                    $row[$key] = ! is_null($value) ? $value : "-";
                }

                $table->addRow($row);
            }
        }

        /** FOOTER */
        $this->setUpFooter($table, $rule, $contextOptions);


        return $table;
    }

    /**
    * @param Table $table
    * @param CategoryVolumePricingTableProperties $contextOptions
    * @param SingleItemRule $rule
    */
    protected function fillSimpleCategoryTable(
        $table,
        $contextOptions,
        $rule
    ) {
        $ranges = $rule->getProductRangeAdjustmentHandler()->getRanges();

        /** COLUMNS */
        $columns = array();
        foreach ($ranges as $index => $range) {
             if ($range->getFrom() == $range->getTo()) {
                $value = $range->getFrom();
            } else {
                if (is_infinite($range->getTo())) {
                    $value = $range->getFrom() . ' +';
                } else {
                    $value = $range->getFrom() . ' - ' . $range->getTo();
                }
            }

            $table->addColumn($index, apply_filters('adp_simple_discount_category_table_cell_qty', $value, $range));
            $columns[] = $range;
        }

        /**ROWS */
        $row = array();
        foreach (array_keys($columns) as $index) {
            $range    = $ranges[$index];
            $discount = $range->getData();

            $value = null;
            if ($discount->getValue()) {
                if ($discount::TYPE_PERCENTAGE === $discount->getType()) {
                    $value = "{$discount->getValue()}%";
                } else {
                    $value = $this->priceFunctions->format($discount->getValue());
                }
            }

            $value = apply_filters('adp_simple_discount_category_table_cell_discount_value', $value, $range);

            $row[$index] = $value;
        }

        $table->addRow($row);
    }

    /**
     * @param CategoryVolumePricingTableProperties $contextOptions
     * @param SingleItemRule $rule
     *
     * @return array
     */
    protected function createColumnsForCategoryVerboseTable($contextOptions, $rule)
    {
        $ranges         = $rule->getProductRangeAdjustmentHandler()->getRanges();
        $columns        = array();
        $columns['qty'] = $contextOptions->quantityColumnTitle;

        $isFixedDiscount = false;
        foreach ($ranges as $index => $range) {
            /** @var Discount $discount */
            $discount = $range->getData();
            if ($discount->getType() === $discount::TYPE_FIXED_VALUE) {
                $isFixedDiscount = true;
            }
        }

        if ($contextOptions->isShowFixedDiscountColumn) {
            if ($isFixedDiscount) {
                $columns['discount_value'] = _x(
                    $contextOptions->discountColumnTitleForFixedPriceRule,
                    'category bulk table discount value column title',
                    'advanced-dynamic-pricing-for-woocommerce'
                );
            } else {
                $columns['discount_value'] = _x(
                    $contextOptions->discountColumnTitle,
                    'category bulk table discount value column title',
                    'advanced-dynamic-pricing-for-woocommerce'
                );
            }
        }

        return $columns;
    }

    /**
     * @param string $key
     * @param Range $range
     *
     * @return string|null
     */
    protected function calculateColumnValueForCategoryVerboseTable($key, $range)
    {
        $discount = $range->getData();
        $value    = null;

        switch ($key) {
            case 'qty':
                if ($range->getFrom() == $range->getTo()) {
                    $value = $range->getFrom();
                } else {
                    if (is_infinite($range->getTo())) {
                        $value = $range->getFrom() . ' +';
                    } else {
                        $value = $range->getFrom() . ' - ' . $range->getTo();
                    }
                }

                $value = apply_filters('adp_verbose_discount_category_table_cell_qty', $value, $range);
                break;
            case 'discount_value':
                if ($discount->getValue()) {
                    if ($discount::TYPE_PERCENTAGE === $discount->getType()) {
                        $value = "{$discount->getValue()}%";
                    } else {
                        $value = $this->priceFunctions->format($discount->getValue());
                    }
                }

                $value = apply_filters('adp_verbose_discount_category_table_cell_discount_value', $value, $range);
                break;
        }

        return $value;
    }

    /**
     * @param ProductVolumePricingTableProperties $contextOptions
     */
    public function setProductContextOptions($contextOptions)
    {
        if ($contextOptions instanceof ProductVolumePricingTableProperties) {
            $this->productContextOptions = $contextOptions;
        }
    }

    /**
     * @param CategoryVolumePricingTableProperties $contextOptions
     */
    public function setCategoryContextOptions($contextOptions)
    {
        if ($contextOptions instanceof CategoryVolumePricingTableProperties) {
            $this->categoryContextOptions = $contextOptions;
        }
    }

    /**
     * @param Table $table
     * @param SingleItemRule $rule
     * @param ProductVolumePricingTableProperties|CategoryVolumePricingTableProperties $themeOptions
     */
    protected function setUpFooter($table, $rule, $themeOptions)
    {
        $isShowFooter = $themeOptions->isShowFooter;
        $footerText   = '';
        if ($isShowFooter) {
            if ($rule->getProductRangeAdjustmentHandler()->getPromotionalMessage()) {
                if ( ! $themeOptions->isUseMessageAsTitle) {
                    $footerText = "<p>" . _x($rule->getProductRangeAdjustmentHandler()->getPromotionalMessage(),
                            "Bulk table promotional message",
                            'advanced-dynamic-pricing-for-woocommerce') . "</p>";
                }
            } else {
                $footerText       = '';
                $humanizedFilters = $this->filtersFormatter->formatRule($rule);
                if ($humanizedFilters) {
                    $footerText = "<div>" . __('Bulk pricing will be applied to package:',
                            'advanced-dynamic-pricing-for-woocommerce') . "</div>";
                    $footerText .= "<ul>";
                    foreach ($humanizedFilters as $filterText) {
                        $footerText .= "<li>" . $filterText . "</li>";
                    }
                    $footerText .= "</ul>";
                }
            }
        }

        $table->setTableFooter($footerText);
    }
}
