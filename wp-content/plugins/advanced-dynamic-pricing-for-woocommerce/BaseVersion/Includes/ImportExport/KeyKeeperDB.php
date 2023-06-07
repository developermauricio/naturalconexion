<?php

namespace ADP\BaseVersion\Includes\ImportExport;

defined('ABSPATH') or exit;

interface KeyKeeperDB
{
    const TITLE = 'title';
    const TYPE = 'type';
    const RULE_TYPE = 'rule_type';
    const EXCLUSIVE = 'exclusive';
    const PRIORITY = 'priority';
    const ENABLED = 'enabled';
    const OPTIONS = 'options';
    const ADDITIONAL = 'additional';
    const CONDITIONS = 'conditions';
    const FILTERS = 'filters';
    const LIMITS = 'limits';
    const PROD_ADJS = 'product_adjustments';
    const SORT_BLOCKS_PRIOR = 'sortable_blocks_priority';
    const BULK_ADJS = 'bulk_adjustments';
    const ROLE_DISCOUNTS = 'role_discounts';
    const CART_ADJS = 'cart_adjustments';
    const FREE_PRODUCTS = 'get_products';
    const AUTO_ADD_PRODUCTS = 'auto_add_products';
    const ADVERTISING = 'advertising';
    const CONDITION_MESSAGE = 'condition_message';
}
