<?php

namespace ADP\BaseVersion\Includes\Core\Rule;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\RuleProcessor\NoItemRuleProcessor;
use Exception;

defined('ABSPATH') or exit;

class NoItemRule extends BaseRule implements Rule
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Context $context
     *
     * @return NoItemRuleProcessor
     * @throws Exception
     */
    public function buildProcessor($context)
    {
        return new NoItemRuleProcessor($context, $this);
    }
}
