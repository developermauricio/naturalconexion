<?php

namespace ADP\BaseVersion\Includes\Database\Models;

use ADP\BaseVersion\Includes\Core\Rule\PackageRule;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Core\Rule\Structures\Filter;
use ADP\BaseVersion\Includes\Core\Rule\Structures\PackageItem;
use ADP\BaseVersion\Includes\Database\RuleStorage;
use ADP\BaseVersion\Includes\Enums\RuleTypeEnum;
use ADP\BaseVersion\Includes\Helpers\Helpers;
use ADP\BaseVersion\Includes\ImportExport\KeyKeeperDB;
use ADP\BaseVersion\Includes\SpecialStrategies\CompareStrategy;
use ADP\Factory;
use ADP\ProVersion\Includes\Database\Repository\CollectionRepository;

class Rule
{
    const TABLE_NAME = 'wdp_rules';

    /**
     * @var int
     */
    public $id;

    /**
     * @var bool
     */
    public $deleted;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var bool
     */
    public $exclusive;

    /**
     * @var string
     */
    public $rule_type;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $priority;

    /**
     * @var array
     */
    public $options;

    /**
     * @var array
     */
    public $additional;

    /**
     * @var array
     */
    public $advertising;

    /**
     * @var array
     */
    public $conditions;

    /**
     * @var array
     */
    public $filters;

    /**
     * @var array
     */
    public $limits;

    /**
     * @var array
     */
    public $productAdjustments;

    /**
     * @var array
     */
    public $sortableBlocksPriority;

    /**
     * @var array
     */
    public $bulkAdjustments;

    /**
     * @var array
     */
    public $roleDiscounts;

    /**
     * @var array
     */
    public $cartAdjustments;

    /**
     * @var array
     */
    public $getProducts;

    /**
     * @var array
     */
    public $autoAddProducts;

    /**
     * @var array
     */
    public $conditionMessage;

    /**
     * @var string
     */
    public $summary;

    public function __construct(
        $id,
        $deleted,
        $enabled,
        $exclusive,
        $rule_type,
        $title,
        $type,
        $priority,
        $options,
        $conditions,
        $filters,
        $limits,
        $cartAdjustments,
        $productAdjustments,
        $sortableBlocksPriority,
        $bulkAdjustments,
        $roleDiscounts,
        $getProducts,
        $autoAddProducts,
        $additional,
        $advertising,
        $conditionMessage
    ) {
        $this->id                     = intval($id);
        $this->deleted                = boolval($deleted);
        $this->enabled                = boolval($enabled);
        $this->exclusive              = boolval($exclusive);
        $this->rule_type              = strval($rule_type);
        $this->title                  = strval($title);
        $this->type                   = strval($type);
        $this->priority               = intval($priority);
        $this->options                = $options;
        $this->conditions             = $conditions;
        $this->filters                = $filters;
        $this->limits                 = $limits;
        $this->cartAdjustments        = $cartAdjustments;
        $this->productAdjustments     = $productAdjustments;
        $this->sortableBlocksPriority = $sortableBlocksPriority;
        $this->bulkAdjustments        = $bulkAdjustments;
        $this->roleDiscounts          = $roleDiscounts;
        $this->getProducts            = $getProducts;
        $this->autoAddProducts        = $autoAddProducts;
        $this->additional             = $additional;
        $this->advertising            = $advertising;
        $this->conditionMessage       = $conditionMessage;

        $this->summary                = "";
    }

    /**
     * @var array $data
     */
    public static function fromArray($data)
    {
        $rule = array(
            'id'                       => null,
            'deleted'                  => null,
            'enabled'                  => null,
            'exclusive'                => null,
            'rule_type'                => null,
            'title'                    => null,
            'type'                     => null,
            'priority'                 => null,
            'options'                  => null,
            'conditions'               => null,
            'filters'                  => null,
            'limits'                   => null,
            'cart_adjustments'         => null,
            'product_adjustments'      => null,
            'sortable_blocks_priority' => null,
            'bulk_adjustments'         => null,
            'role_discounts'           => null,
            'get_products'             => null,
            'auto_add_products'        => null,
            'additional'               => null,
            'advertising'              => null,
            'condition_message'        => null,
        );
        $rule = array_merge($rule, $data);

        $rule['advertising'] ? array_walk($rule['advertising'], function (&$value) {
            $value = stripslashes($value);
        }) : null;

        $rule['condition_message'] ? array_walk($rule['condition_message'], function (&$value) {
            $value = stripslashes_deep($value);
        }) : null;

        return new self(
            $rule['id'],
            $rule['deleted'],
            (new CompareStrategy())->isStringBool($rule['enabled']),
            (new CompareStrategy())->isStringBool($rule['exclusive']),
            $rule['rule_type'],
            $rule['title'],
            $rule['type'],
            $rule['priority'],
            $rule['options'],
            $rule['conditions'],
            $rule['filters'],
            $rule['limits'],
            $rule['cart_adjustments'],
            $rule['product_adjustments'],
            $rule['sortable_blocks_priority'],
            $rule['bulk_adjustments'],
            $rule['role_discounts'],
            $rule['get_products'],
            $rule['auto_add_products'],
            $rule['additional'],
            $rule['advertising'],
            $rule['condition_message']
        );
    }

    public function getData()
    {
        $data = array(
            'id'                       => $this->id,
            'deleted'                  => $this->deleted,
            'enabled'                  => $this->enabled ? 'on' : 'off',
            'exclusive'                => $this->exclusive,
            'rule_type'                => $this->rule_type,
            'title'                    => $this->title,
            'type'                     => $this->type,
            'priority'                 => $this->priority,
            'options'                  => $this->options,
            'conditions'               => $this->conditions,
            'filters'                  => $this->filters,
            'limits'                   => $this->limits,
            'cart_adjustments'         => $this->cartAdjustments,
            'product_adjustments'      => $this->productAdjustments,
            'sortable_blocks_priority' => $this->sortableBlocksPriority,
            'bulk_adjustments'         => $this->bulkAdjustments,
            'role_discounts'           => $this->roleDiscounts,
            'get_products'             => $this->getProducts,
            'auto_add_products'        => $this->autoAddProducts,
            'additional'               => $this->additional,
            'advertising'              => $this->advertising,
            'condition_message'        => $this->conditionMessage,
        );

        return $data;
    }

    public function getDataForDB()
    {
        $data = array(
            'deleted'                  => isset($this->deleted) ? intval($this->deleted) : null,
            'enabled'                  => isset($this->enabled) ? intval($this->enabled) : null,
            'exclusive'                => isset($this->exclusive) ? intval($this->exclusive) : null,
            'rule_type'                => $this->rule_type,
            'title'                    => $this->title,
            'type'                     => $this->type,
            'priority'                 => $this->priority,
            'options'                  => is_array($this->options) ? serialize(self::sanitizeArrayTextFields($this->options)) : null,
            'conditions'               => is_array($this->conditions) ? serialize(self::sanitizeArrayTextFields($this->conditions)) : null,
            'filters'                  => is_array($this->filters) ? serialize(self::sanitizeArrayTextFields($this->filters)) : null,
            'limits'                   => is_array($this->limits) ? serialize(self::sanitizeArrayTextFields($this->limits)) : null,
            'cart_adjustments'         => is_array($this->cartAdjustments) ? serialize(self::sanitizeArrayTextFields($this->cartAdjustments)) : null,
            'product_adjustments'      => is_array($this->productAdjustments) ? serialize(self::sanitizeArrayTextFields($this->productAdjustments)) : null,
            'sortable_blocks_priority' => is_array($this->sortableBlocksPriority) ? serialize(self::sanitizeArrayTextFields($this->sortableBlocksPriority)) : null,
            'bulk_adjustments'         => is_array($this->bulkAdjustments) ? serialize(self::sanitizeArrayTextFields($this->bulkAdjustments)) : null,
            'role_discounts'           => is_array($this->roleDiscounts) ? serialize(self::sanitizeArrayTextFields($this->roleDiscounts)) : null,
            'get_products'             => is_array($this->getProducts) ? serialize(self::sanitizeArrayTextFields($this->getProducts)) : null,
            'auto_add_products'        => is_array($this->autoAddProducts) ? serialize(self::sanitizeArrayTextFields($this->autoAddProducts)) : null,
            'additional'               => is_array($this->additional) ? serialize($this->additional) : null,
            'advertising'              => is_array($this->advertising) ? serialize($this->advertising) : null,
            'condition_message'        => is_array($this->conditionMessage) ? serialize($this->conditionMessage) : null,
        );

        $data = array_filter($data, function ($value) {
            return isset($value);
        });

        $data['summary'] = $this->buildSummary();

        return $data;
    }

    /**
     * @return string
     */
    protected function buildSummary() {
        /** @var RuleStorage $ruleStorage */
        $ruleStorage = Factory::get("Database_RuleStorage");

        if ($this->rule_type === RuleTypeEnum::PERSISTENT()->getValue()) {
            $rulesCol = $ruleStorage->buildPersistentRules(array($this));
        } else {
            $rulesCol = $ruleStorage->buildRules(array($this));
        }

        if (count($rulesCol->getRules()) === 0) {
            return "";
        }

        $rule = $rulesCol->getRules()[0];

        $pieces = [];
        if ($rule->getTitle()) {
            $pieces[] = $rule->getTitle();
        }

        if ($rule->getDiscountMessage()) {
            $pieces[] = $rule->getDiscountMessage();
        }

        if ($rule->getDiscountMessageCartItem()) {
            $pieces[] = $rule->getDiscountMessageCartItem();
        }

        if ($rule->getLongDiscountMessage()) {
            $pieces[] = $rule->getLongDiscountMessage();
        }

        if ($rule->getSaleBadge()) {
            $pieces[] = $rule->getSaleBadge();
        }

        if ($rule instanceof SingleItemRule) {
            $pieces = array_merge($pieces, $this->buildFiltersSummary($rule->getFilters()));

            if ($rule->getProductAdjustmentHandler()) {
                if ( $rule->getProductAdjustmentHandler()->getReplaceCartAdjustmentCode() ) {
                    $pieces[] = $rule->getProductAdjustmentHandler()->getReplaceCartAdjustmentCode();
                }
            }

            foreach ($rule->getRoleDiscounts() as $roleDiscount) {
                $pieces = array_merge($pieces, $roleDiscount->getRoles());
            }
        } elseif ($rule instanceof PackageRule) {
            foreach ($rule->getPackages() as $package) {
                $pieces = array_merge($pieces, $this->buildFiltersSummary($package->getFilters()));
            }

            if ($rule->getProductAdjustmentHandler()) {
                if ( $rule->getProductAdjustmentHandler()->getReplaceCartAdjustmentCode() ) {
                    $pieces[] = $rule->getProductAdjustmentHandler()->getReplaceCartAdjustmentCode();
                }
            }

            foreach ($rule->getRoleDiscounts() as $roleDiscount) {
                $pieces = array_merge($pieces, $roleDiscount->getRoles());
            }
        }

        if ($rule->getReplaceItemGiftsCode()) {
            $pieces[] = $rule->getReplaceItemGiftsCode();
        }

        return join("|", $pieces);
    }

    /**
     * @param array<int, Filter> $filters
     *
     * @return array
     */
    protected function buildFiltersSummary($filters)
    {
        if (empty($filters)) {
            return [];
        }

        $result = [];

        foreach ($filters as $filter) {
            if ( ! is_array($filter->getValue()) ) {
                continue;
            }

            if ($filter::TYPE_PRODUCT === $filter->getType()) {
                $result = array_map(
                    array("ADP\\BaseVersion\\Includes\\Helpers\\Helpers", "getProductTitle"),
                    $filter->getValue()
                );
            } elseif ($filter::TYPE_CATEGORY === $filter->getType()) {
                $result = array_map(
                    array("ADP\\BaseVersion\\Includes\\Helpers\\Helpers", "getCategoryTitle"),
                    $filter->getValue()
                );
            } elseif ($filter::TYPE_TAG === $filter->getType()) {
                $result = array_map(
                    array("ADP\\BaseVersion\\Includes\\Helpers\\Helpers", "getTagTitle"),
                    $filter->getValue()
                );
            } elseif ($filter::TYPE_ATTRIBUTE === $filter->getType()) {
                $result = array_map(
                    array("ADP\\BaseVersion\\Includes\\Helpers\\Helpers", "getAttributeTitle"),
                    $filter->getValue()
                );
            } elseif ($filter::TYPE_COLLECTIONS === $filter->getType()) {
                $collectionRepository = new CollectionRepository();
                foreach ($collectionRepository::getProductCollectionsByIds($filter->getValue()) as $collection ) {
                    $result[] = $collection->title;
                }
            }
        }

        return $result;
    }

    public static function createTable()
    {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charsetCollate = $wpdb->get_charset_collate();

        $tableName = $wpdb->prefix . self::TABLE_NAME;

        $sql = /** @lang MySQL */
            "CREATE TABLE {$tableName} (
            id INT NOT NULL AUTO_INCREMENT,
            deleted TINYINT(1) DEFAULT 0,
            enabled TINYINT(1) DEFAULT 1,
            exclusive TINYINT(1) DEFAULT 0,
            rule_type VARCHAR(50),
            type VARCHAR(50),
            title VARCHAR(255),
            priority INT,
            options TEXT,
            additional TEXT,
            advertising TEXT,
            conditions TEXT,
            filters TEXT,
            limits TEXT,
            product_adjustments TEXT,
            sortable_blocks_priority TEXT,
            bulk_adjustments TEXT,
            role_discounts TEXT,
            cart_adjustments TEXT,
            get_products TEXT,
            auto_add_products TEXT,
            condition_message TEXT,
            summary TEXT,
            PRIMARY KEY  (id),
            KEY deleted (deleted),
            KEY enabled (enabled)
        ) $charsetCollate;";
        dbDelta($sql);
    }

    public static function deleteTable()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . self::TABLE_NAME;
        $wpdb->query("DROP TABLE IF EXISTS $tableName");
    }

    public static function sanitizeArrayTextFields($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::sanitizeArrayTextFields($value);
            } else {
                $value = sanitize_text_field($value);
            }
        }

        return $array;
    }
}
