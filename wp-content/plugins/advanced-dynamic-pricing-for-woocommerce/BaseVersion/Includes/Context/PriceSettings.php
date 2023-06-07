<?php

namespace ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

class PriceSettings
{
    /**
     * @var bool
     */
    protected $taxEnabled = false;

    /**
     * @var bool
     */
    protected $includeTax = false;

    /**
     * @var int
     */
    protected $decimals = 2;

    /**
     * @var string
     */
    protected $decimalSeparator = '.';

    /**
     * @var string
     */
    protected $thousandSeparator = '';

    /**
     * @var string
     */
    protected $priceFormat = self::FORMAT_LEFT;
    const FORMAT_LEFT = '%1$s%2$s';
    const FORMAT_RIGHT = '%2$s%1$s';
    const FORMAT_LEFT_SPACE = '%1$s&nbsp;%2$s';
    const FORMAT_RIGHT_SPACE = '%2$s&nbsp;%1$s';

    public function __construct()
    {

    }

    /**
     * @param bool $taxEnabled
     *
     * @return self
     */
    public function setTaxEnabled($taxEnabled)
    {
        $this->taxEnabled = boolval($taxEnabled);

        return $this;
    }

    /**
     * @return bool
     */
    public function isTaxEnabled()
    {
        return $this->taxEnabled;
    }

    /**
     * @param bool $includeTax
     *
     * @return self
     */
    public function setIncludeTax($includeTax)
    {
        $this->includeTax = boolval($includeTax);

        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludeTax()
    {
        return $this->taxEnabled && $this->includeTax;
    }

    /**
     * @param int $decimals
     *
     * @return self
     */
    public function setDecimals($decimals)
    {
        $this->decimals = intval($decimals);

        return $this;
    }

    /**
     * @return int
     */
    public function getDecimals()
    {
        return $this->decimals;
    }

    /**
     * @param string $decimalSeparator
     */
    public function setDecimalSeparator($decimalSeparator)
    {
        if ( ! empty($decimalSeparator) && is_string($decimalSeparator)) {
            $this->decimalSeparator = $decimalSeparator;
        }
    }

    /**
     * @return string
     */
    public function getDecimalSeparator()
    {
        return $this->decimalSeparator;
    }

    /**
     * @param string $thousandSeparator
     */
    public function setThousandSeparator($thousandSeparator)
    {
        if (is_string($thousandSeparator)) {
            $this->thousandSeparator = $thousandSeparator;
        }
    }

    /**
     * @return string
     */
    public function getThousandSeparator()
    {
        return $this->thousandSeparator;
    }

    /**
     * @param string $priceFormat
     */
    public function setPriceFormat($priceFormat)
    {
        if ($priceFormat) {
            $this->priceFormat = $priceFormat;
        }
    }

    /**
     * @return string
     */
    public function getPriceFormat()
    {
        return $this->priceFormat;
    }
}
