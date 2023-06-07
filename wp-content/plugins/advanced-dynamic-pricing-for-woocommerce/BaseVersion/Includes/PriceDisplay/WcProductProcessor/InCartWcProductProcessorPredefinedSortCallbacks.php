<?php

namespace ADP\BaseVersion\Includes\PriceDisplay\WcProductProcessor;

use ADP\BaseVersion\Includes\Core\Cart\CartItem;
use ADP\BaseVersion\Includes\SpecialStrategies\CompareStrategy;

class InCartWcProductProcessorPredefinedSortCallbacks
{
    public static function cartItemsAsIs($cartItems)
    {
        return $cartItems;
    }

    public static function cartItemsInReverseOrder($cartItems)
    {
        return array_reverse($cartItems);
    }

    public static function sortCartItemsByPriceDesc($cartItems)
    {
        $compare = new CompareStrategy();

        usort($cartItems, function ($a, $b) use (&$compare) {
            /**
             * @var CartItem $a
             * @var CartItem $b
             */
            if ($compare->floatsAreEqual($a->getPrice(), $b->getPrice())) {
                return 0;
            }

            return $compare->floatLessAndEqual($a->getPrice(), $b->getPrice()) ? 1 : -1;
        });

        return $cartItems;
    }

    public static function sortCartItemsByPriceAsc($cartItems)
    {
        $compare = new CompareStrategy();

        usort($cartItems, function ($a, $b) use (&$compare) {
            /**
             * @var CartItem $a
             * @var CartItem $b
             */

            if ($compare->floatsAreEqual($a->getPrice(), $b->getPrice())) {
                return 0;
            }

            return $compare->floatLessAndEqual($a->getPrice(), $b->getPrice()) ? -1 : 1;
        });

        return $cartItems;
    }
}
