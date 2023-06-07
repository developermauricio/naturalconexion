<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\RuleProcessor\Exceptions\RuleExecutionTimeout;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepositoryInterface;

defined('ABSPATH') or exit;

class RuleTimer
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var float
     */
    protected $execRuleStart;

    /**
     * @var float
     */
    protected $lastExecTime;

    /**
     * @param Context|Rule $contextOrRule
     * @param Rule|null $deprecated
     */
    public function __construct($contextOrRule, $deprecated = null)
    {
        $this->context        = adp_context();
        $this->rule           = $contextOrRule instanceof Rule ? $contextOrRule : $deprecated;
        $this->ruleRepository = new RuleRepository();
        $this->execRuleStart  = null;
        $this->lastExecTime   = null;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function withRuleRepository(RuleRepositoryInterface $repository)
    {
        $this->ruleRepository = $repository;
    }

    public function start()
    {
        $this->execRuleStart = microtime(true);
        $this->lastExecTime  = null;
    }

    /**
     * @return float
     */
    public function finish()
    {
        if ( ! isset($this->execRuleStart)) {
            return floatval(0);
        }

        $this->lastExecTime  = microtime(true) - $this->execRuleStart;
        $this->execRuleStart = null;

        return $this->lastExecTime;
    }

    /**
     * @throws RuleExecutionTimeout
     */
    public function checkExecutionTime()
    {
        $rule_max_exec_time = (float)$this->context->getOption('rule_max_exec_time');

        if (empty($rule_max_exec_time)) {
            return;
        }

        if ((microtime(true) - $this->execRuleStart) > $rule_max_exec_time) {
            throw new RuleExecutionTimeout();
        }
    }

    /**
     * @return float
     */
    public function getLastExecTime()
    {
        return $this->lastExecTime;
    }

    public function handleOutOfTime()
    {
        $this->context->adminNotice->addOutOfTimeNotice($this->rule->getId(), false);

        $this->ruleRepository->markAsDisabledByPlugin($this->rule->getId());
    }
}
