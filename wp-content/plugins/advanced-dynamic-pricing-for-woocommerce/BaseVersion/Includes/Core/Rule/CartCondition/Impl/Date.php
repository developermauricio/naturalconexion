<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl;

use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\DateTimeComparisonCondition;

defined('ABSPATH') or exit;

class Date extends AbstractCondition implements DateTimeComparisonCondition
{
    const FROM = 'from';
    const TO = 'to';
    const SPECIFIC_DATE = 'specific_date';

    const AVAILABLE_COMP_METHODS = array(
        self::FROM,
        self::TO,
        self::SPECIFIC_DATE,
    );

    /**
     * @var string
     */
    protected $comparisonDate;

    /**
     * @var string
     */
    protected $comparisonMethod;

    public function check($cart)
    {
        // it is not actually UTC.The time has already shifted by WP. UTC is for convenience.
        $date = (new \DateTime("now", new \DateTimeZone("UTC")))->setTimestamp($cart->getContext()->time());
        $date->setTime(0, 0, 0);

        $comparisonDate = \DateTime::createFromFormat(
            "Y-m-d",
            $this->comparisonDate,
            new \DateTimeZone("UTC")
        );
        $comparisonDate->setTime(0, 0, 0);
        $comparisonMethod = $this->comparisonMethod;

        return $this->compareTimeUnixFormat($date->getTimestamp(), $comparisonDate->getTimestamp(), $comparisonMethod);
    }

    public static function getType()
    {
        return 'date';
    }

    public static function getLabel()
    {
        return __('Date', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public static function getTemplatePath()
    {
        return WC_ADP_PLUGIN_VIEWS_PATH . 'conditions/datetime/date.php';
    }

    public static function getGroup()
    {
        return ConditionsLoader::GROUP_DATE_TIME;
    }

    public function setComparisonDateTime($comparisonDatetime)
    {
        gettype($comparisonDatetime) === 'string' ? $this->comparisonDate = $comparisonDatetime : $this->comparisonDate = null;
    }

    public function setDateTimeComparisonMethod($comparisonMethod)
    {
        in_array(
            $comparisonMethod,
            self::AVAILABLE_COMP_METHODS
        ) ? $this->comparisonMethod = $comparisonMethod : $this->comparisonMethod = null;
    }

    public function getComparisonDateTime()
    {
        return $this->comparisonDate;
    }

    public function getDateTimeComparisonMethod()
    {
        return $this->comparisonMethod;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ! is_null($this->comparisonMethod) and ! is_null($this->comparisonDate);
    }
}
