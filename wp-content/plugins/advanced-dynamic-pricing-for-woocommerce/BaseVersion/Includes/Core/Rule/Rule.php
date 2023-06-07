<?php

namespace ADP\BaseVersion\Includes\Core\Rule;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustment;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\RuleCondition;
use ADP\BaseVersion\Includes\Core\Rule\Limit\RuleLimit;
use ADP\BaseVersion\Includes\Core\Rule\Structures\AutoAdd;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Gift;
use ADP\BaseVersion\Includes\Core\RuleProcessor\RuleProcessor;

defined('ABSPATH') or exit;

interface Rule
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     */
    public function setTitle($title);

    /**
     * @param Context $context
     *
     * @return RuleProcessor
     */
    public function buildProcessor($context);

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @return array<int, RuleCondition>
     */
    public function getConditions();

    /**
     * @param RuleCondition $condition
     */
    public function addCondition($condition);

    /**
     * TODO remove after implement conditions groups
     *
     * @return string
     */
    public function getConditionsRelationship();

    /**
     * @return array<int, RuleLimit>
     */
    public function getLimits();

    /**
     * @param RuleLimit $limit
     */
    public function addLimit($limit);

    /**
     * @return array<int, CartAdjustment>
     */
    public function getCartAdjustments();

    /**
     * @param CartAdjustment $cartAdjustment
     */
    public function addCartAdjustment($cartAdjustment);

    /**
     * @return array<int,Gift>
     */
    public function getGifts();

    /**
     * @param array<int,Gift> $gifts
     */
    public function setGifts($gifts);

    /**
     * @return array<int,AutoAdd>
     */
    public function getAutoAdds();

    /**
     * @param array<int,AutoAdd> $autoAdds
     */
    public function setAutoAdds($autoAdds);

    /**
     * @param array<int, RuleCondition> $conditions
     */
    public function setConditions($conditions);

    /**
     * @param string $code
     */
    public function setActivationCouponCode($code);

    /**
     * @return string|null
     */
    public function getActivationCouponCode();

    /**
     * @return string
     */
    public function getHash();

    public function setEnabledTimer($enabled);

    /**
     * @param string $message
     */
    public function setTimerMessage($message);

    /**
     * @param string $message
     */
    public function setDiscountMessage($message);

    /**
     * @param string $message
     */
    public function setDiscountMessageCartItem($message);

    /**
     * @return bool
     */
    public function getEnabledTimer();

    /**
     * @return string
     */
    public function getTimerMessage();

    /**
     * @return string
     */
    public function getDiscountMessage();

    /**
     * @param string $message
     */
    public function setLongDiscountMessage($message);

    /**
     * @return string
     */
    public function getLongDiscountMessage();

    /**
     * @param string $badge
     */
    public function setSaleBadge($badge);

    /**
     * @return string
     */
    public function getSaleBadge();

    /**
     * @return \DateTime|null
     */
    public function getDateFrom();

    /**
     * @param \DateTime|null $dateFrom
     */
    public function setDateFrom(\DateTime $dateFrom);

    /**
     * @return \DateTime|null
     */
    public function getDateTo();

    /**
     * @param \DateTime|null $dateTo
     */
    public function setDateTo(\DateTime $dateTo);

    public function setBlocks($blocks);

    public function getBlocks();
}
