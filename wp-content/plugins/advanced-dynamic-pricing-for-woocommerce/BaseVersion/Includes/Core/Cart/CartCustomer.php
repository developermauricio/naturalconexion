<?php

namespace ADP\BaseVersion\Includes\Core\Cart;

use ADP\BaseVersion\Includes\Core\Cart\CartCustomer\AddedRecommendedAutoAddItems;
use ADP\BaseVersion\Includes\Core\Cart\CartCustomer\RemovedAutoAddItems;
use ADP\BaseVersion\Includes\Core\Cart\CartCustomer\RemovedFreeItems;
use ADP\BaseVersion\Includes\Core\Cart\CartCustomer\RemovedRecommendedPromotions;

defined('ABSPATH') or exit;

class CartCustomer
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var array
     */
    protected $billingAddress;

    /**
     * @var array
     */
    protected $shippingAddress;

    /**
     * @var string
     */
    protected $selectedPaymentMethod;

    /**
     * @var string[]
     */
    protected $selectedShippingMethods;

    /**
     * @var bool
     */
    protected $isVatExempt;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var RemovedFreeItems[]
     */
    protected $removedFreeItemsList;

    /**
     * @var RemovedAutoAddItems[]
     */
    protected $removedAutoAddItemsList;

    /**
     * @var AddedRecommendedAutoAddItems[]
     */
    protected $addedRecommendedAutoAddItemsList;

    /**
     * @var RemovedRecommendedPromotions[]
     */
    protected $removedRecommendedPromotions;

    /**
     * @var array
     */
    protected $meta;

    /**
     * @var CustomerTax|null
     */
    protected $customerTaxAdj;

    /**
     * @param int $id
     */
    public function __construct($id = null)
    {
        $this->id = null;
        $this->setId($id);

        $this->billingAddress          = array();
        $this->shippingAddress         = array();
        $this->selectedPaymentMethod   = null;
        $this->selectedShippingMethods = array();
        $this->isVatExempt             = false;
        $this->removedFreeItemsList    = array();
        $this->removedAutoAddItemsList = array();
        $this->meta                    = array();

        $this->customerTaxAdj = null;
    }

    /**
     * @param array $metaArray
     */
    public function setMetaData($metaArray)
    {
        if (is_array($metaArray)) {
            $this->meta = array_filter($metaArray, 'is_array');
        }
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param $key string
     *
     * @return mixed|null
     */
    public function getMetaValue($key)
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }

    /**
     * @param int|null $id
     */
    public function setId($id)
    {
        if (is_numeric($id) && intval($id) >= 0) {
            $this->id = intval($id);
        }
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    public function isGuest()
    {
        return $this->id === null || $this->id === 0;
    }

    /**
     * @param array $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = (array)$billingAddress;
    }

    /**
     * @return array
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param array $shippingAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = (array)$shippingAddress;
    }

    /**
     * @return array
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param array<int, string> $selectedShippingMethods
     */
    public function setSelectedShippingMethods($selectedShippingMethods)
    {
        $this->selectedShippingMethods = $selectedShippingMethods;
    }

    /**
     * @return array<int, string>
     */
    public function getSelectedShippingMethods()
    {
        return $this->selectedShippingMethods;
    }

    /**
     * @param string|null $selectedPaymentMethod
     */
    public function setSelectedPaymentMethod($selectedPaymentMethod)
    {
        $this->selectedPaymentMethod = $selectedPaymentMethod;
    }

    /**
     * @return string|null
     */
    public function getSelectedPaymentMethod()
    {
        return $this->selectedPaymentMethod;
    }

    /**
     * @param array<int, string> $roles
     */
    public function setRoles($roles)
    {
        $this->roles = (array)$roles;
    }

    /**
     * All non registered users have a dummy 'wdp_guest' role
     *
     * @return array<int, string>
     */
    public function getRoles()
    {
        return ! empty($this->roles) ? $this->roles : array('wdp_guest');
    }

    /**
     * @param bool $isVatExempt
     */
    public function setIsVatExempt($isVatExempt)
    {
        $this->isVatExempt = boolval($isVatExempt);
    }

    /**
     * @return bool
     */
    public function isVatExempt()
    {
        $taxExempt = $this->isVatExempt;
        if ( $customerTaxAdj = $this->getCustomerTaxAdj() ) {
            $taxExempt = ! $customerTaxAdj->isWithTax();
        }

        return $taxExempt;
    }

    public function setCustomerTaxAdj(CustomerTax $customerTaxAdj)
    {
        $this->customerTaxAdj = $customerTaxAdj;
    }

    /**
     * @return CustomerTax|null
     */
    public function getCustomerTaxAdj()
    {
        return $this->customerTaxAdj;
    }

    public function getShippingCountry()
    {
        return isset($this->shippingAddress['country']) ? $this->shippingAddress['country'] : "";
    }

    public function getShippingState()
    {
        return isset($this->shippingAddress['state']) ? $this->shippingAddress['state'] : "";
    }

    public function getShippingPostCode()
    {
        return isset($this->shippingAddress['postcode']) ? $this->shippingAddress['postcode'] : "";
    }

    public function getShippingCity()
    {
        return isset($this->shippingAddress['city']) ? $this->shippingAddress['city'] : "";
    }

    public function getBillingCountry()
    {
        return isset($this->billingAddress['country']) ? $this->billingAddress['country'] : "";
    }

    public function getBillingState()
    {
        return isset($this->billingAddress['state']) ? $this->billingAddress['state'] : "";
    }

    public function getBillingPostCode()
    {
        return isset($this->billingAddress['postcode']) ? $this->billingAddress['postcode'] : "";
    }

    public function getBillingCity()
    {
        return isset($this->billingAddress['city']) ? $this->billingAddress['city'] : "";
    }

    /**
     * @return array<int, RemovedFreeItems>
     */
    public function getRemovedFreeItemsList()
    {
        return $this->removedFreeItemsList;
    }

    /**
     * @param array<int, RemovedFreeItems> $removedFreeItemsList
     */
    public function setRemovedFreeItemsList($removedFreeItemsList)
    {
        if ( ! is_array($removedFreeItemsList)) {
            return;
        }

        $this->removedFreeItemsList = array();
        foreach ($removedFreeItemsList as $item) {
            if ($item instanceof RemovedFreeItems) {
                $this->removedFreeItemsList[] = $item;
            }
        }
    }

    /**
     * @param string $giftHash
     *
     * @return RemovedFreeItems
     */
    public function getRemovedFreeItems($giftHash)
    {
        $result = null;

        foreach ($this->removedFreeItemsList as $removedFreeItems) {
            if ($removedFreeItems->getGiftHash() === $giftHash) {
                $result = $removedFreeItems;
                break;
            }
        }

        if ($result === null) {
            $result                       = new RemovedFreeItems($giftHash);
            $this->removedFreeItemsList[] = $result;
        }

        return $result;
    }

    /**
     * @return array<int, RemovedAutoAddItems>
     */
    public function getRemovedAutoAddItemsList()
    {
        return $this->removedAutoAddItemsList;
    }

    /**
     * @param array<int, RemovedAutoAddItems> $removedAutoAddItemsList
     */
    public function setRemovedAutoAddItemsList($removedAutoAddItemsList)
    {
        if ( ! is_array($removedAutoAddItemsList)) {
            return;
        }

        $this->removedAutoAddItemsList = array();
        foreach ($removedAutoAddItemsList as $item) {
            if ($item instanceof RemovedAutoAddItems) {
                $this->removedAutoAddItemsList[] = $item;
            }
        }
    }

    /**
     * @param string $autoAddHash
     *
     * @return RemovedAutoAddItems
     */
    public function getRemovedAutoAddItems($autoAddHash)
    {
        $result = null;

        foreach ($this->removedAutoAddItemsList as $removedAutoAddItems) {
            if ($removedAutoAddItems->getAutoAddHash() === $autoAddHash) {
                $result = $removedAutoAddItems;
                break;
            }
        }

        if ($result === null) {
            $result                       = new RemovedAutoAddItems($autoAddHash);
            $this->removedAutoAddItemsList[] = $result;
        }

        return $result;
    }

    /**
     * @return array<int, AddedRecommendedAutoAddItems>
     */
    public function getAddedRecommendedAutoAddItemsList()
    {
        return $this->addedRecommendedAutoAddItemsList;
    }

    /**
     * @param array<int, AddedRecommendedAutoAddItems> $addedRecommendedAutoAddItemsList
     */
    public function setAddedRecommendedAutoAddItemsList($addedRecommendedAutoAddItemsList)
    {
        if (!is_array($addedRecommendedAutoAddItemsList)) {
            return;
        }

        $this->addedRecommendedAutoAddItemsList = array();
        foreach ($addedRecommendedAutoAddItemsList as $item) {
            if ($item instanceof AddedRecommendedAutoAddItems) {
                $this->addedRecommendedAutoAddItemsList[] = $item;
            }
        }
    }

    /**
     * @param string $autoAddHash
     *
     * @return AddedRecommendedAutoAddItems
     */
    public function getAddedRecommendedAutoAddItems($autoAddHash)
    {
        $result = null;

        foreach ($this->addedRecommendedAutoAddItemsList as $addedRecommendedAutoAddItems) {
            if ($addedRecommendedAutoAddItems->getAutoAddHash() === $autoAddHash) {
                $result = $addedRecommendedAutoAddItems;
                break;
            }
        }

        if ($result === null) {
            $result = new AddedRecommendedAutoAddItems($autoAddHash);
            $this->addedRecommendedAutoAddItemsList[] = $result;
        }

        return $result;
    }

    /**
     * @return array<int, RemovedRecommendedPromotions>
     */
    public function getRemovedRecommendedPromotionsList()
    {
        return $this->removedRecommendedPromotions;
    }

    /**
     * @param array<int, RemovedRecommendedPromotions> $removedRecommendedPromotions
     */
    public function setRemovedRecommendedPromotionsList($removedRecommendedPromotions)
    {
        if (!is_array($removedRecommendedPromotions)) {
            return;
        }

        $this->removedRecommendedPromotions = array();
        foreach ($removedRecommendedPromotions as $item) {
            if ($item instanceof RemovedRecommendedPromotions) {
                $this->removedRecommendedPromotions[] = $item;
            }
        }
    }

    /**
     * @param string $autoAddHash
     *
     * @return RemovedRecommendedPromotions
     */
    public function getRemovedRecommendedPromotionsItems($autoAddHash)
    {
        $result = null;

        foreach ($this->removedRecommendedPromotions as $removedRecommendedPromotion) {
            if ($removedRecommendedPromotion->getAutoAddHash() === $autoAddHash) {
                $result = $removedRecommendedPromotion;
                break;
            }
        }

        if ($result === null) {
            $result = new RemovedRecommendedPromotions($autoAddHash);
            $this->removedRecommendedPromotions[] = $result;
        }

        return $result;
    }
}
