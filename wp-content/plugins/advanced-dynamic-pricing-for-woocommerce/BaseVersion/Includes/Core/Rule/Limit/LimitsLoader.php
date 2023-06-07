<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Limit;

use ADP\BaseVersion\Includes\Core\Rule\Limit\Interfaces;
use ADP\Factory;
use Exception;

defined('ABSPATH') or exit;

class LimitsLoader
{
    const KEY = 'limits';

    const LIST_TYPE_KEY = 'type';
    const LIST_LABEL_KEY = 'label';
    const LIST_TEMPLATE_PATH_KEY = 'path';

    const GROUP_USAGE_RESTRICT = 'usage_restrictions';

    /**
     * @var array
     */
    protected $groups = array();

    /**
     * @var string[]
     */
    protected $items = array();

    public function __construct()
    {
        $this->initGroups();

        foreach (Factory::getClassNames('Core_Rule_Limit_Impl') as $className) {
            /**
             * @var $className RuleLimit
             */
            $this->items[$className::getType()] = $className;
        }
    }

    protected function initGroups()
    {
        $this->groups[self::GROUP_USAGE_RESTRICT] = __('Usage restrictions',
            'advanced-dynamic-pricing-for-woocommerce');
    }

    /**
     * @param $data
     *
     * @return RuleLimit
     * @throws Exception
     */
    public function build($data)
    {
        if (empty($data['type'])) {
            throw new Exception('Missing limit type');
        }

        $limit = $this->create($data['type']);

        if ($limit instanceof Interfaces\MaxUsageLimit) {
            $limit->setMaxUsage($data['options'][$limit::MAX_USAGE_KEY]);
        }

        if ($limit->isValid()) {
            return $limit;
        } else {
            throw new Exception('Wrong limit');
        }
    }

    /**
     * @param string $type
     *
     * @return RuleLimit
     * @throws Exception
     */
    public function create($type)
    {
        if (isset($this->items[$type])) {
            $className = $this->items[$type];

            return new $className();
        } else {
            throw new Exception('Wrong limit');
        }
    }

    public function getAsList()
    {
        $list = array();

        foreach ($this->items as $type => $className) {
            /**
             * @var $className RuleLimit
             */

            $list[$className::getGroup()][] = array(
                self::LIST_TYPE_KEY          => $className::getType(),
                self::LIST_LABEL_KEY         => $className::getLabel(),
                self::LIST_TEMPLATE_PATH_KEY => $className::getTemplatePath(),
            );
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param $key string
     *
     * @return string|null
     */
    public function getGroupLabel($key)
    {
        return isset($this->groups[$key]) ? $this->groups[$key] : null;
    }

    public function getItems()
    {
        return $this->items;
    }
}
