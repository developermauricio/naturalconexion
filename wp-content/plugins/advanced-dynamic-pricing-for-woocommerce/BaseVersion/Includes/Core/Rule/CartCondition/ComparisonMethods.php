<?php

namespace ADP\BaseVersion\Includes\Core\Rule\CartCondition;

defined('ABSPATH') or exit;

interface ComparisonMethods
{
    const LT = '<';
    const LTE = '<=';
    const MTE = '>=';
    const MT = '>';
    const EQ = '=';
    const NEQ = '!=';
    const IN_LIST = 'in_list';
    const NOT_IN_LIST = 'not_in_list';
    const AT_LEAST_ONE_ANY = 'at_least_one_any';
    const AT_LEAST_ONE = 'at_least_one';
    const ALL = 'all';
    const ONLY = 'only';
    const NONE = 'none';
    const NONE_AT_ALL = 'none_at_all';
    const IN_RANGE = 'in_range';
    const NOT_IN_RANGE = 'not_in_range';
    const LATER = 'later';
    const EARLIER = 'earlier';
    const FROM = 'from';
    const TO = 'to';
    const SPECIFIC_DATE = 'specific_date';
    const CONTAINS = 'contains';
    const NOT_CONTAINS = 'not_contains';
}
