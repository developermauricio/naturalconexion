<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Compatibility\PhoneOrdersCmp;
use Exception;
use WC_Product;
use WC_Product_Variable;

defined('ABSPATH') or exit;

class FreeCartItem
{
    /**
     * @var WC_Product
     */
    protected $product;

    /**
     * @var float
     */
    protected $initialPrice;

    /**
     * @var float
     */
    protected $initialTax;

    /**
     * @var float
     */
    public $qty;

    /**
     * @var float
     */
    protected $qtyAlreadyInWcCart;

    /**
     * @var bool
     */
    protected $replaceWithCoupon;

    /**
     * @var string
     */
    protected $replaceCouponCode;

    /**
     * @var int
     */
    protected $ruleId;

    /**
     * @var array
     */
    public $originalWcCartItem = array();

    /**
     * @var int
     */
    protected $pos;

    /**
     * @var array
     */
    protected $attributes;
    const ATTR_TEMP = 'temporary';

    /**
     * @var string
     */
    protected $associatedGiftHash;

    /**
     * @var array
     */
    protected $variation;

    /**
     * @var array
     */
    protected $cartItemData;

    /**
     * @var WC_Product_Variable|null
     */
    protected $parentProduct;

    /**
     * @var bool
     */
    protected $selected;

    /**
     * @param WC_Product $product
     * @param float $qty
     * @param int $ruleId
     * @param string $associatedGiftHash
     *
     * @throws Exception
     */
    public function __construct($product, $qty, $ruleId, $associatedGiftHash)
    {
        if ( ! ($product instanceof WC_Product)) {
            throw new Exception(sprintf("Unsupported class of the product: %s", gettype($product)));
        }

        $this->product            = $product;
        $this->qty                = floatval($qty);
        $this->ruleId             = $ruleId;
        $this->associatedGiftHash = $associatedGiftHash;
        $this->qtyAlreadyInWcCart = 0;
        $this->replaceWithCoupon  = false;
        $this->replaceCouponCode  = '';

        $this->initialPrice = floatval($product->get_price(''));
        $this->initialTax   = floatval(0);

        if ($product->get_parent_id()) {
            $this->parentProduct = CacheHelper::getWcProduct($product->get_parent_id());
        }

        if ($product instanceof \WC_Product_Variation) {
            $this->setVariation($product->get_variation_attributes());
        } else {
            $this->variation = array();
        }

        $this->cartItemData = array();
        $this->attributes   = array();

        $this->selected = false;
    }

    public function setReplaceWithCoupon($replace)
    {
        $this->replaceWithCoupon = boolval($replace);
    }

    public function isReplaceWithCoupon()
    {
        return $this->replaceWithCoupon;
    }

    public function setQtyAlreadyInWcCart($qty)
    {
        $this->qtyAlreadyInWcCart = $qty;
    }

    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * @return bool
     */
    public function getQtyAlreadyInWcCart()
    {
        return $this->qtyAlreadyInWcCart;
    }

    /**
     * @return WC_Product
     */
    public function getProduct(): WC_Product
    {
        return $this->product;
    }

    /**
     * @param float $initialPrice
     * @param float $initialTax
     */
    public function installInitialPrices($initialPrice, $initialTax)
    {
        $this->initialPrice = floatval($initialPrice);
        $this->initialTax   = floatval($initialTax);
    }

    public function getInitialPrice()
    {
        return $this->initialPrice;
    }

    public function getInitialTax()
    {
        return $this->initialTax;
    }

    public function hash()
    {
        $cartItemData = $this->cartItemData;
        unset($cartItemData[PhoneOrdersCmp::CART_ITEM_SKIP_KEY]);

        $data = array(
            $this->product->get_id(),
            $this->product->get_parent_id(),
            $this->ruleId,
//            $this->initialPrice,
            $this->replaceWithCoupon,
            $this->replaceCouponCode,
            $this->associatedGiftHash,
            $this->variation,
            $cartItemData,
            $this->selected,
            $this->attributes,
        );

        return md5(json_encode($data));
    }

    /**
     * @param int $pos
     */
    public function setPos($pos)
    {
        $this->pos = $pos;
    }

    /**
     * @return int
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * @return float
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param float $qty
     */
    public function setQty($qty)
    {
        if (is_numeric($qty)) {
            $this->qty = floatval($qty);
        }
    }

    /**
     * @return string
     */
    public function getReplaceCouponCode()
    {
        return $this->replaceCouponCode;
    }

    /**
     * @param string $replaceCouponCode
     */
    public function setReplaceCouponCode($replaceCouponCode)
    {
        $this->replaceCouponCode = $replaceCouponCode;
    }

    /**
     * @return string
     */
    public function getAssociatedGiftHash()
    {
        return $this->associatedGiftHash;
    }

    /**
     * @param array<int, string> $variation
     */
    public function setVariation($variation)
    {
        if ( ! is_array($variation)) {
            return;
        }

        if ( ! ($this->product instanceof \WC_Product_Variation) || ! ($this->parentProduct instanceof WC_Product_Variable)) {
            return;
        }
        $parentAttributes = $this->parentProduct->get_variation_attributes();

        foreach ($parentAttributes as $attributeName => $values) {
            $attributeName = 'attribute_' . sanitize_title($attributeName);
            if (empty($variation[$attributeName])) {
                $variation[$attributeName] = reset($values);
            }
        }

        $this->variation = $variation;
    }

    /**
     * @return array<int, string>
     */
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * @param array $cartItemData
     */
    public function setCartItemData($cartItemData)
    {
        $this->cartItemData = $cartItemData;
    }

    /**
     * @return array
     */
    public function getCartItemData()
    {
        return $this->cartItemData;
    }

    /**
     * @param bool $selected
     */
    public function setSelected($selected)
    {
        $this->selected = boolval($selected);
    }

    /**
     * @return bool
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * @param string $attribute
     *
     * @return bool
     */
    public function hasAttr($attribute)
    {
        return in_array($attribute, $this->attributes);
    }

    public function addAttr(...$attributes)
    {
        $allowedAttrs = array(
            self::ATTR_TEMP,
        );

        foreach ($attributes as $attribute) {
            if (in_array($attribute, $allowedAttrs)) {
                $this->attributes[] = $attribute;
            }
        }
    }

    public function removeAttr(...$attributes)
    {
        foreach ($attributes as $attr) {
            $pos = array_search($attr, $this->attributes);

            if ($pos !== false) {
                unset($this->attributes[$pos]);
            }
        }

        $this->attributes = array_values($this->attributes);
    }

    public function getAttrs()
    {
        return $this->attributes;
    }
}
