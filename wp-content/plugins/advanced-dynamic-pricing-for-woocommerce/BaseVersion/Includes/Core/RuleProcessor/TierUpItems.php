<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Cart\Cart;
use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule;
use ADP\BaseVersion\Includes\Core\Rule\PackageRule\PackageRangeAdjustments;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule\ProductsRangeAdjustments;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Discount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\RangeDiscount;
use ADP\BaseVersion\Includes\Core\Rule\Structures\SetDiscount;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Structures\CartSet;
use ADP\Factory;

defined('ABSPATH') or exit;

class TierUpItems
{
    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var ProductsRangeAdjustments|PackageRangeAdjustments
     */
    protected $handler;

    /**
     * @var Cart
     */
    protected $cart;

    const MARK_CALCULATED = 'tier_calculated';

    /**
     * @param SingleItemRule|PackageRule $rule
     * @param Cart $cart
     */
    public function __construct($rule, $cart)
    {
        $this->rule    = $rule;
        $this->cart    = $cart;
        $this->handler = $rule->getProductRangeAdjustmentHandler();
    }

    /**
     * @param array<int,CartItem> $items
     *
     * @return array<int,CartItem>
     */
    public function executeItems($items)
    {
        foreach ($this->handler->getRanges() as $range) {
            $items = $this->processRange($items, $range);
        }

        foreach ($items as $index => $item) {
            if ( ! $item->hasMark(self::MARK_CALCULATED)) {
                unset($items[$index]);
                array_splice($items, 0, 0, array($item));
            }
        }
        $items = array_values($items);

        foreach ($items as $item) {
            $item->removeMark(self::MARK_CALCULATED);
        }

        return $items;
    }

    /**
     * @param array<int,CartItem> $items
     * @param float $customQty
     *
     * @return array<int,CartItem>
     */
    public function executeItemsWithCustomQty($items, $customQty)
    {
        if ($customQty === floatval(0)) {
            return $items;
        }

        foreach ($this->handler->getRanges() as $range) {
            if ( ! is_null($customQty) && $range->isIn($customQty)) {
                $range = new RangeDiscount($range->getFrom(), $customQty, $range->getData());
                $items = $this->processRange($items, $range);
                break;
            }

            $items = $this->processRange($items, $range);
        }

        foreach ($items as $item) {
            $item->removeMark(self::MARK_CALCULATED);
        }

        return $items;
    }

    /**
     * @param array<int,CartSet> $items
     *
     * @return array<int,CartSet>
     */
    public function executeSets($items)
    {
        foreach ($this->handler->getRanges() as $range) {
            $items = $this->processRange($items, $range);
        }

        foreach ($items as $item) {
            $item->removeMark(self::MARK_CALCULATED);
        }

        return $items;
    }

    /**
     * @param array<int,CartItem>|array<int,CartSet> $elements
     * @param RangeDiscount $range
     *
     * @return array<int,CartItem>|array<int,CartSet>
     */
    protected function processRange($elements, $range)
    {
        $processedQty          = 1;
        $newElements           = array();
        $indexOfItemsToProcess = array();
        foreach ($elements as $element) {
            if ($element->hasMark(self::MARK_CALCULATED)) {
                $newElements[] = $element;
                $processedQty  += $element->getQty();
                continue;
            }

            if ($range->isLess($processedQty)) {
                if ($range->isIn($processedQty + $element->getQty())) {
                    $requireQty = $processedQty + $element->getQty() - $range->getFrom();

                    if ($requireQty > 0) {
                        $newItem = clone $element;
                        $newItem->setQty($requireQty);
                        $newElements[]           = $newItem;
                        $indexOfItemsToProcess[] = count($newElements) - 1;
                        $processedQty            += $requireQty;
                    }

                    if (($element->getQty() - $requireQty) > 0) {
                        $newItem = clone $element;
                        $newItem->setQty($element->getQty() - $requireQty);
                        $newElements[] = $newItem;
                        $processedQty  += $element->getQty() - $requireQty;
                    }
                } elseif ($range->isGreater($processedQty + $element->getQty())) {
                    $requireQty = $range->getQtyInc();

                    if ($requireQty > 0) {
                        $newItem = clone $element;
                        $newItem->setQty($requireQty);
                        $newElements[]           = $newItem;
                        $indexOfItemsToProcess[] = count($newElements) - 1;
                        $processedQty            += $requireQty;
                    }

                    if (($element->getQty() - $requireQty) > 0) {
                        $newItem = clone $element;
                        $newItem->setQty($element->getQty() - $requireQty);
                        $newElements[] = $newItem;
                        $processedQty  += $element->getQty() - $requireQty;
                    }

                } else {
                    $newElements[] = $element;
                    $processedQty  += $element->getQty();
                }
            } elseif ($range->isIn($processedQty)) {
                $requireQty = $range->getTo() + 1 - $processedQty;
                $requireQty = $requireQty < $element->getQty() ? $requireQty : $element->getQty();

                if ($requireQty > 0) {
                    $newItem = clone $element;
                    $newItem->setQty($requireQty);
                    $newElements[]           = $newItem;
                    $indexOfItemsToProcess[] = count($newElements) - 1;
                    $processedQty            += $requireQty;
                }

                if (($element->getQty() - $requireQty) > 0) {
                    $newItem = clone $element;
                    $newItem->setQty($element->getQty() - $requireQty);
                    $newElements[] = $newItem;
                    $processedQty  += $element->getQty() - $requireQty;
                }

            } elseif ($range->isGreater($processedQty)) {
                $newElements[] = $element;
                $processedQty  += $element->getQty();
            }
        }

        $discount        = $range->getData();
        /** @var PriceCalculator $priceCalculator */
        $priceCalculator = Factory::get("Core_RuleProcessor_PriceCalculator", $this->rule, $discount);
        foreach ($indexOfItemsToProcess as $index) {
            $elementToProcess = $newElements[$index];

            if ($elementToProcess instanceof CartSet) {
                if ($discount instanceof SetDiscount) {
                    $priceCalculator->calculatePriceForSet($elementToProcess, $this->cart, $this->handler);
                } elseif ($discount instanceof Discount) {
                    foreach ($elementToProcess->getItems() as $element) {
                        $priceCalculator->applyItemDiscount($element, $this->cart, $this->handler);
                    }
                }
                $elementToProcess->addMark(self::MARK_CALCULATED);
            } elseif ($elementToProcess instanceof CartItem) {
                $priceCalculator->applyItemDiscount($elementToProcess, $this->cart, $this->handler);
                $elementToProcess->addMark(self::MARK_CALCULATED);
            }

        }

        return $newElements;
    }
}
