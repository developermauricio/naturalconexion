<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Internationalization;

use ADP\BaseVersion\Includes\Core\Rule\Structures\Filter;

defined('ABSPATH') or exit;

class FilterTranslator
{
    public function translateByType($type, $value, IObjectInternationalization $oi)
    {
        $returnAsArray = is_array($value);
        $values = is_array($value) ? $value : array($value);

        switch ($type) {
            case Filter::TYPE_PRODUCT:
                $values = array_map([$oi, 'translateProductId'], $values);
                break;
            case Filter::TYPE_CATEGORY:
                $values = array_map([$oi, 'translateCategoryId'], $values);
                break;
            case Filter::TYPE_ATTRIBUTE:
                $values = array_map([$oi, 'translateAttributeId'], $values);
                break;
            case Filter::TYPE_TAG:
                $values = array_map([$oi, 'translateTagId'], $values);
                break;
            case Filter::TYPE_CATEGORY_SLUG:
                $values = array_map([$oi, 'translateCategorySlug'], $values);
                break;
            case Filter::TYPE_SKU:
            case 'product_custom_fields':
            case Filter::TYPE_COLLECTIONS:
            case Filter::TYPE_ANY:
            case Filter::TYPE_SELLERS:
            case 'product_custom_attributes':
                break;
            default:
                $values = array_map(function ($value) use ($type, $oi) {
                    return $oi->translateCustomTaxonomyTermId($value, $type);
                }, $values);
                break;
        }

        return $returnAsArray ? $values : reset($values);
    }
}
