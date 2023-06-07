<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

defined('ABSPATH') or exit;

interface Listener
{
    public function calcProcessStarted();

    /**
     * @param bool $result
     */
    public function processResult($result);

    /**
     * @param RuleProcessor $proc
     */
    public function ruleCalculated($proc);
}
