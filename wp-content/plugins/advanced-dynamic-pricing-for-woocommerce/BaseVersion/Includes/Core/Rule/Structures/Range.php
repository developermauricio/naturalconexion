<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Structures;

defined('ABSPATH') or exit;

class Range
{
    /**
     * @var float
     */
    protected $from;

    /**
     * @var float
     */
    protected $to;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param string|float $from
     * @param string|float $to
     * @param mixed $rangeData
     */
    public function __construct($from, $to, $rangeData)
    {
        $this->from = is_numeric($from) && $from >= 0 ? (float)$from : 0.0;
        $this->to   = is_numeric($to) ? (float)$to : INF;

        $this->data = $rangeData;
    }

    public function isValid()
    {
        return $this->lteEnd($this->from);
    }

    /**
     * Less than finish value of the interval
     *
     * @param int|float $value
     *
     * @return bool
     */
    private function ltEnd($value)
    {
        return $value < $this->to;
    }

    /**
     * Less than or equal finish value of the interval
     *
     * @param int|float $value
     *
     * @return bool
     */
    private function lteEnd($value)
    {
        return $value <= $this->to;
    }

    /**
     * Equal finish value of the interval
     *
     * @param int|float $value
     *
     * @return bool
     */
    private function isEqualEnd($value)
    {
        return $value === $this->to;
    }

    /**
     * Greater than finish value of the interval
     *
     * @param int|float $value
     *
     * @return bool
     */
    private function gtEnd($value)
    {
        return $this->to < $value;
    }

    /**
     * Greater than or equal finish value of the interval
     *
     * @param int|float $value
     *
     * @return bool
     */
    private function gteEnd($value)
    {
        return $this->to <= $value;
    }

    /**
     * Less than start value of the interval
     *
     * @param int|float $value
     *
     * @return bool
     */
    private function ltStart($value)
    {
        return $value < $this->from;
    }

    /**
     * Less than or equal start value of the interval
     *
     * @param int|float $value
     *
     * @return bool
     */
    private function lteStart($value)
    {
        return $value <= $this->from;
    }

    /**
     * Equal start value of the interval
     *
     * @param int|float $value
     *
     * @return bool
     */
    private function isEqualStart($value)
    {
        return $value === $this->from;
    }

    /**
     * Greater than start value of the interval
     *
     * @param int|float $value
     *
     * @return bool
     */
    private function gtStart($value)
    {
        return $this->from < $value;
    }

    /**
     * Greater than or equal start value of the interval
     *
     * @param int|float $value
     *
     * @return bool
     */
    private function gteStart($value)
    {
        return $this->from <= $value;
    }

    /**
     * Is value in interval?
     *
     * @param float $value
     *
     * @return bool
     */
    public function isIn($value)
    {
        return $this->from <= $value && $this->lteEnd($value);
    }

    /**
     * Is value greater than finish value of the interval?
     *
     * @param float $value
     *
     * @return bool
     */
    public function isGreater($value)
    {
        return $this->gtEnd($value);
    }

    /**
     * Is value greater than finish value of the interval inclusively?
     *
     * @param float $value
     *
     * @return bool
     */
    public function isGreaterInc($value)
    {
        return $this->gteEnd($value);
    }

    /**
     * Is value less than start value of the interval?
     *
     * @param float $value
     *
     * @return bool
     */
    public function isLess($value)
    {
        return $this->ltStart($value);
    }

    /**
     * Is value less than start value of the interval inclusively?
     *
     * @param float $value
     *
     * @return bool
     */
    public function isLessInc($value)
    {
        return $this->lteStart($value);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getQty()
    {
        return $this->to - $this->from;
    }

    public function getQtyInc()
    {
        return (float)($this->to - $this->from + 1);
    }
}
