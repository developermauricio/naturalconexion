<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\Interfaces;

defined('ABSPATH') or exit;

interface FeeCartAdj
{
    const FEE_VALUE_KEY = 'fee_value';
    const FEE_NAME_KEY = 'fee_name';
    const FEE_TAX_CLASS_KEY = 'fee_tax_class';

    /**
     * @param float $feeValue
     */
    public function setFeeValue($feeValue);

    /**
     * @param string $feeName
     */
    public function setFeeName($feeName);

    /**
     * @param string $taxClass
     */
    public function setFeeTaxClass($taxClass);

    /**
     * @return float
     */
    public function getFeeValue();

    /**
     * @return string
     */
    public function getFeeName();

    /**
     * @return string
     */
    public function getFeeTaxClass();

    public function translate();
}
