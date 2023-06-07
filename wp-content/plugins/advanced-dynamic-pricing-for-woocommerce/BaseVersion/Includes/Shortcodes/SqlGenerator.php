<?php

namespace ADP\BaseVersion\Includes\Shortcodes;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Rule\SingleItemRule;
use ADP\BaseVersion\Includes\Helpers\Helpers;

defined('ABSPATH') or exit;

class SqlGenerator
{
    /**
     * @var Context|null
     */
    protected $context;

    protected $appliedRules = array();

    /**
     * @var array
     */
    protected $join = array();

    /**
     * @var array
     */
    protected $where = array();

    /**
     * @var array
     */
    protected $excludeWhere = array();

    /**
     * @var array
     */
    protected $customTaxonomies = array();

    public function __construct()
    {
        $this->customTaxonomies = array_values(array_map(function ($tax) {
            return $tax->name;
        }, Helpers::getCustomProductTaxonomies()));

        $this->context = null;
    }

    /**
     * @param Context|SingleItemRule $contextOrRule
     * @param SingleItemRule|null $deprecated
     *
     * @return bool
     */
    public function applyRuleToQuery($contextOrRule, $deprecated = null)
    {
        $this->context = adp_context();
        $rule          = $contextOrRule instanceof SingleItemRule ? $contextOrRule : $deprecated;
        $filters       = $rule->getFilters();
        if ( ! $filters) {
            return false;
        }

        $filter = reset($filters);

        if ( ! $filter->isValid()) {
            return false;
        }

        $excludeIds = array();
        if ($this->context->getOption('allow_to_exclude_products') && $filter->getExcludeProductIds()) {
            $excludeIds = $filter->getExcludeProductIds();
        }

        $generated = $this->generateFilterSqlByType($filter->getType(), $filter->getValue(), $excludeIds);

        if ( ! empty($generated['where'])) {
            $this->where[] = $generated['where'];
        }

//        if ($this->context->getOption('allow_to_exclude_products') && $filter->getExcludeProductIds()) {
//            $ids                  = "( '" . implode("','",
//                    array_map('esc_sql', $filter->getExcludeProductIds())) . "' )";
//            $this->excludeWhere[] = "post.ID NOT IN {$ids} AND post.post_parent NOT IN {$ids}";
//        }

        $this->appliedRules[] = $rule;

        return true;
    }

    public function clear() {
        $this->appliedRules = array();
        $this->where = array();
    }

    public function getJoin()
    {
        return $this->join;
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function getExcludeWhere()
    {
        return $this->excludeWhere;
    }

    protected function generateFilterSqlByType($type, $value, $excludeIds = array())
    {
        if (in_array($type, $this->customTaxonomies)) {
            return $this->genSqlCustomTaxonomy($type, $value, $excludeIds);
        }

        $method_name = "genSql" . ucfirst($type);

        return method_exists($this, $method_name) ? call_user_func(array($this, $method_name), $value,
            $excludeIds) : false;
    }

    protected function genSqlProducts($productIds, $excludeIds = array())
    {
        $where = array();

        $ids_sql_in = "( '" . implode("','", array_map('esc_sql', $productIds)) . "' )";

        $where[] = "post.ID IN {$ids_sql_in} OR post.post_parent IN {$ids_sql_in}";

        if (count($excludeIds) > 0) {
            $ids                  = "( '" . implode("','", array_map('esc_sql', $excludeIds)) . "' )";
            $this->excludeWhere[] = "post.ID NOT IN {$ids} AND post.post_parent NOT IN {$ids}";
        }

        return array(
            'where' => implode(" ", $where),
        );
    }

    protected function addJoin($sqlJoin)
    {
        $hash = md5($sqlJoin);
        if ( ! isset($this->join[$hash])) {
            $this->join[$hash] = $sqlJoin;
        }
    }

    protected function genSqlProduct_sku($skus, $excludeIds = array())
    {
        global $wpdb;

        $skus_sql_in = "( '" . implode("','", array_map('esc_sql', $skus)) . "' )";

        $this->addJoin("LEFT JOIN {$wpdb->postmeta} as postmeta_1 ON post.ID = postmeta_1.post_id");

        $where   = array();
        $where[] = "postmeta_1.meta_key = '_sku'";
        $where[] = "postmeta_1.meta_value IN {$skus_sql_in}";

        if (count($excludeIds) > 0) {
            $ids                  = "( '" . implode("','", array_map('esc_sql', $excludeIds)) . "' )";
            $this->excludeWhere[] = "post.ID NOT IN {$ids} AND post.post_parent NOT IN {$ids}";
        }

        return array(
            'where' => "(" . implode(" AND ", $where) . ")",
        );
    }

    protected function genSqlProduct_sellers($sellers, $excludeIds = array())
    {
        global $wpdb;

        $sellers_sql_in = "( '" . implode("','", array_map('esc_sql', $sellers)) . "' )";

        $where   = array();
        $where[] = "post.post_author IN {$sellers_sql_in}";

        if (count($excludeIds) > 0) {
            $ids                  = "( '" . implode("','", array_map('esc_sql', $excludeIds)) . "' )";
            $this->excludeWhere[] = "post.ID NOT IN {$ids} AND post.post_parent NOT IN {$ids}";
        }

        return array(
            'where' => "(" . implode(" AND ", $where) . ")",
        );
    }

    protected function genSqlProduct_tags($tags, $excludeIds = array())
    {
        return $this->genSqlByTermIds('product_tag', $tags, $excludeIds);
    }

    protected function genSqlProduct_categories($categories, $excludeIds = array())
    {
        return $this->genSqlByTermIds('product_cat', $categories, $excludeIds);
    }

    protected function genSqlProduct_category_slug($categorySlugs, $excludeIds = array())
    {
        global $wpdb;
        $where = array();

        $category_slugs_sql_in = "( '" . implode("','", array_map('esc_sql', $categorySlugs)) . "' )";

        $this->addJoin("LEFT JOIN {$wpdb->term_relationships} as term_rel_1 ON post.ID = term_rel_1.object_id");
        $this->addJoin("LEFT JOIN {$wpdb->term_taxonomy} as term_tax_1 ON term_rel_1.term_taxonomy_id = term_tax_1.term_taxonomy_id");
        $this->addJoin("LEFT JOIN {$wpdb->terms} as term_1 ON term_tax_1.term_id = term_1.term_id");

        $where[] = "term_1.slug IN {$category_slugs_sql_in}";

        if (count($excludeIds) > 0) {
            $ids                  = "( '" . implode("','", array_map('esc_sql', $excludeIds)) . "' )";
            $this->excludeWhere[] = "post.ID NOT IN {$ids} AND post.post_parent NOT IN {$ids}";
        }

        return array(
            'where' => implode(" ", $where),
        );
    }

    protected function genSqlProduct_custom_fields($values, $excludeIds = array())
    {
        global $wpdb;
        $where = array();

        $custom_fields = array();
        foreach ($values as $value) {
            $value = explode("=", $value);
            if (count($value) !== 2) {
                continue;
            }
            $custom_fields[] = array(
                'key'   => $value[0],
                'value' => $value[1],
            );
        }

        $this->addJoin("LEFT JOIN {$wpdb->postmeta} as postmeta_1 ON post.ID = postmeta_1.post_id");

        $tmp_where = [];
        foreach ($custom_fields as $custom_field) {
            $tmp_where[] = "postmeta_1.meta_key='{$custom_field['key']}' AND postmeta_1.meta_value='{$custom_field['value']}'";
        }

        $where[] = "( " . implode(" OR ", $tmp_where) . " )";

        if (count($excludeIds) > 0) {
            $ids                  = "( '" . implode("','", array_map('esc_sql', $excludeIds)) . "' )";
            $this->excludeWhere[] = "post.ID NOT IN {$ids} AND post.post_parent NOT IN {$ids}";
        }


        return array(
            'where' => implode(" ", $where),
        );
    }

    protected function genSqlProduct_attributes($attributes, $excludeIds = array())
    {
        return $this->genSqlByTermIds('product_attr', $attributes, $excludeIds);
    }

    protected function genSqlCustomTaxonomy($taxName, $values, $excludeIds = array())
    {
        return $this->genSqlByTermIds($taxName, $values, $excludeIds);
    }

    protected function genSqlByTermIds($taxName, $termIds, $excludeIds = array())
    {
        $term_ids_sql_in = "( '" . implode("','", array_map('esc_sql', $termIds)) . "' )";

        global $wpdb;
        $where = array();

        $relationshipTableName = "term_rel_$taxName";
        $taxTableName          = "term_tax_$taxName";

        $this->addJoin( "LEFT JOIN {$wpdb->term_relationships} as {$relationshipTableName} ON post.ID = {$relationshipTableName}.object_id" );
        $this->addJoin( "LEFT JOIN {$wpdb->term_taxonomy} as {$taxTableName} ON {$relationshipTableName}.term_taxonomy_id = {$taxTableName}.term_taxonomy_id" );

        $where[] = "{$taxTableName}.term_id IN {$term_ids_sql_in}";

        if (count($excludeIds) > 0) {
            $ids                  = "( '" . implode("','", array_map('esc_sql', $excludeIds)) . "' )";
            $this->excludeWhere[] = "post.ID NOT IN {$ids} AND post.post_parent NOT IN {$ids}";
        }

        return array(
            'where' => implode(" ", $where),
        );
    }

    protected function genSqlProduct_custom_attributes($values, $excludeIds = array())
    {
        global $wpdb;
        $where = array();

        $this->addJoin("LEFT JOIN {$wpdb->postmeta} as postmeta_1 ON post.ID = postmeta_1.post_id");


        $tmp_where = [];
        foreach ($values as $value) {
            $tmp_where[] = "meta_key LIKE 'adp_custom_product_attribute_%' AND meta_value LIKE '%$value%'";
        }

        $where[] = "( " . implode(" OR ", $tmp_where) . " )";

        if (count($excludeIds) > 0) {
            $ids                  = "( '" . implode("','", array_map('esc_sql', $excludeIds)) . "' )";
            $this->excludeWhere[] = "post.ID NOT IN {$ids} AND post.post_parent NOT IN {$ids}";
        }

        return array(
            'where' => implode(" ", $where),
        );
    }

    protected function genSqlAny()
    {
        return array(
            'where' => array(),
        );
    }

    public function isEmpty()
    {
        return count($this->appliedRules) === 0;
    }
}