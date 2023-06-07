<?php

namespace ADP\BaseVersion\Includes\Context;

use ADP\BaseVersion\Includes\Context;
use ReflectionClass;
use ReflectionException;
use WC_Product;

defined('ABSPATH') or exit;

class CurrencyController
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Currency
     */
    protected $defaultCurrency;

    /**
     * @var Currency
     */
    protected $currentCurrency;

    /**
     * @var float
     */
    protected $rate;

    /**
     * @var string[]
     */
    protected $currencySymbols = array();

    /**
     * @var callable|null
     */
    protected $customProductSalePriceCallback = null;

    /**
     * @var callable|null
     */
    protected $customProductRegularPriceCallback = null;

    /**
     * @var callable|null
     */
    protected $customProductPriceCallback = null;

    /**
     * @param Context $context
     * @param Currency $defaultCurrency
     */
    public function __construct($context, $defaultCurrency)
    {
        $this->context         = $context;
        $this->defaultCurrency = $defaultCurrency;
        $this->currentCurrency = $defaultCurrency;
        $this->rate            = $this->defaultCurrency->getRate();
        $this->currencySymbols = self::getDefaultCurrencySymbols();
    }

    /**
     * @param Currency $currency
     */
    public function setCurrentCurrency($currency)
    {
        if ($currency instanceof Currency) {
            $this->currentCurrency = $currency;
            $this->rate            = floatval($this->currentCurrency->getRate() / $this->defaultCurrency->getRate());
        }
    }

    /**
     * do not use 'strong' comparison
     * @see https://www.php.net/manual/en/language.oop5.object-comparison.php
     * Compare only attributes!
     *
     * @return bool
     */
    public function isCurrencyChanged()
    {
        return $this->defaultCurrency != $this->currentCurrency;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @return Currency
     */
    public function getDefaultCurrency()
    {
        return $this->defaultCurrency;
    }

    /**
     * @return Currency
     */
    public function getCurrentCurrency()
    {
        return $this->currentCurrency;
    }

    /**
     * @param WC_Product $product
     * @param string $prop
     *
     * @return mixed|null
     */
    protected function getProductProtectedProp($product, $prop)
    {
        try {
            $reflection = new ReflectionClass($product);
            $property   = $reflection->getProperty('data');
            $property->setAccessible(true);
        } catch (ReflectionException $e) {
            $property = null;
        }

        return isset($property->getValue($product)[$prop]) ? $property->getValue($product)[$prop] : null;
    }

    /**
     * @param WC_Product $product
     *
     * @return float
     */
    public function getDefaultCurrencyProductPrice($product)
    {
        $price = floatval($this->getProductProtectedProp($product, 'price'));

        if ($this->customProductPriceCallback) {
            return call_user_func($this->customProductPriceCallback, $price, $product, $this->defaultCurrency);
        }

        return $price;
    }

    /**
     * @param WC_Product $product
     *
     * @return float|null
     */
    public function getDefaultCurrencyProductSalePrice($product)
    {
        $salePrice = $this->getProductProtectedProp($product, 'sale_price');

        if ($this->customProductSalePriceCallback) {
            return call_user_func($this->customProductSalePriceCallback, $salePrice, $product, $this->defaultCurrency);
        }

        return $salePrice !== "" ? floatval($salePrice) : null;
    }

    /**
     * @param WC_Product $product
     *
     * @return float
     */
    public function getDefaultCurrencyProductRegularPrice($product)
    {
        $regularPrice = floatval($this->getProductProtectedProp($product, 'regular_price'));

        if ($this->customProductRegularPriceCallback) {
            return call_user_func($this->customProductRegularPriceCallback, $regularPrice, $product,
                $this->defaultCurrency);
        }

        return $regularPrice;
    }

    /**
     * @param WC_Product $product
     *
     * @return float
     */
    public function getCurrentCurrencyProductPrice($product)
    {
        $price = $this->getDefaultCurrencyProductPrice($product) * $this->rate;

        if ($this->customProductPriceCallback) {
            return call_user_func($this->customProductPriceCallback, $price, $product, $this->currentCurrency);
        }

        return $price;
    }

    /**
     * @param WC_Product $product
     * @param float $price
     *
     * @return float
     */
    public function getCurrentCurrencyProductPriceWithCustomPrice($product, $price)
    {
        $price = $price * $this->rate;

        if ($this->customProductPriceCallback) {
            return call_user_func($this->customProductPriceCallback, $price, $product, $this->currentCurrency);
        }

        return $price;
    }

    /**
     * @param WC_Product $product
     *
     * @return float|null
     */
    public function getCurrentCurrencyProductSalePrice($product)
    {
        $salePrice = $this->getDefaultCurrencyProductSalePrice($product);

        if ($this->customProductSalePriceCallback) {
            return call_user_func($this->customProductSalePriceCallback, $salePrice, $product, $this->currentCurrency);
        }

        return $salePrice !== "" && $salePrice !== null ? $salePrice * $this->rate : null;
    }

    /**
     * @param WC_Product $product
     *
     * @return float
     */
    public function getCurrentCurrencyProductRegularPrice($product)
    {
        $regularPrice = $this->getDefaultCurrencyProductRegularPrice($product) * $this->rate;

        if ($this->customProductRegularPriceCallback) {
            return call_user_func($this->customProductRegularPriceCallback, $regularPrice, $product,
                $this->currentCurrency);
        }

        return $regularPrice;
    }

    /**
     * @param array<int,string> $currencySymbols
     */
    public function setCurrencySymbols($currencySymbols)
    {
        if ( ! empty($currencySymbols) && is_array($currencySymbols)) {
            $this->currencySymbols = $currencySymbols;
        }
    }

    /**
     * @return array<int,string>
     */
    public function getCurrencySymbols()
    {
        return $this->currencySymbols;
    }

    /**
     * @param callable $callback
     */
    public function setCustomProductSalePriceCallback($callback)
    {
        if (is_callable($callback)) {
            $this->customProductSalePriceCallback = $callback;
        }
    }

    /**
     * @param callable $callback
     */
    public function setCustomProductRegularPriceCallback($callback)
    {
        if (is_callable($callback)) {
            $this->customProductRegularPriceCallback = $callback;
        }
    }

    /**
     * @param callable|null $callback
     */
    public function setCustomProductPriceCallback($callback)
    {
        if (is_callable($callback)) {
            $this->customProductPriceCallback = $callback;
        }
    }

    /**
     * @return array<int,string>
     */
    public static function getDefaultCurrencySymbols()
    {
        return array(
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => 'Afl.',
            'AZN' => 'AZN',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BYN' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'DKK',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '&euro;',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GBP' => '&pound;',
            'GEL' => '&#x20be;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x639;.&#x62f;',
            'IRR' => '&#xfdfc;',
            'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
            'ISK' => 'kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x441;&#x43e;&#x43c;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => 'KZT',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x644;.&#x62f;',
            'MAD' => '&#x62f;.&#x645;.',
            'MDL' => 'MDL',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRU' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => 'N&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#x434;&#x438;&#x43d;.',
            'RUB' => '&#8381;',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STN' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'L',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UAH' => '&#8372;',
            'UGX' => 'UGX',
            'USD' => '&#36;',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VES' => 'Bs.S',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'CFA',
            'XCD' => '&#36;',
            'XOF' => 'CFA',
            'XPF' => 'Fr',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK',
        );
    }

}
