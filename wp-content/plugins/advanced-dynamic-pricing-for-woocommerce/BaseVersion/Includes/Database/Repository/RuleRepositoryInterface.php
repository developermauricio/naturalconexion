<?php

namespace ADP\BaseVersion\Includes\Database\Repository;

use ADP\BaseVersion\Includes\Database\Models\Rule;

interface RuleRepositoryInterface {
    /**
     * @param array $args ( array|string types, bool active_only, bool include_deleted, bool exclusive, int|array id )
     *
     * @return Rule[]
     */
    public function getRules($args = array()): array;

     /**
     * @param array $args
     *
     * @return int|null
     */
    public function getRulesCount($args = array());

    public function deleteAllRules();

    public function markRulesAsDeleted($type);

    public function markRuleAsDeleted($rule_id);

    /**
     * @param Rule $rule
     */
    public function storeRule($rule);

    public function changeRulePriority(int $ruleId, int $priority): int;

    public function markAsDisabledByPlugin($ruleId);

    public function deleteConditionsFromDbByTypes($types);

    public function isConditionTypeActive($types);

    public function disableRule($ruleId);

    public function enableRule($ruleId);
}
