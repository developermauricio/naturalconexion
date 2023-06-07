<?php

namespace ADP\BaseVersion\Includes\Core\Rule;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\RuleProcessor\PersistentRuleProcessor;
use Exception;

defined('ABSPATH') or exit;

class PersistentRule extends SingleItemRule implements Rule
{
    /**
     * @param Context $context
     *
     * @return PersistentRuleProcessor
     * @throws Exception
     */
    public function buildProcessor($context)
    {
        return new PersistentRuleProcessor($context, $this);
    }
}
