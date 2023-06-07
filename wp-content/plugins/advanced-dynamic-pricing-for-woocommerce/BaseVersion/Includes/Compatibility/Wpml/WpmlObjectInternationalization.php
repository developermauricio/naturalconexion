<?php

namespace ADP\BaseVersion\Includes\Compatibility\Wpml;

use ADP\BaseVersion\Includes\Core\Rule\Internationalization\IObjectInternationalization;

class WpmlObjectInternationalization implements IObjectInternationalization
{
    /**
     * @var string
     */
    private $languageCode;

    public function __construct($languageCode)
    {
        $this->languageCode = $languageCode;
    }

    public function translateProductId(int $productId): int
    {
        if ($translValue = apply_filters('translate_object_id', $productId, 'post', false, $this->languageCode)) {
            $productId = (int)$translValue;
        }

        return $productId;
    }

    public function translateCategoryId(int $categoryId): int
    {
        if ($translValue = apply_filters('translate_object_id', $categoryId, 'product_cat', false,
            $this->languageCode)) {
            $categoryId = (int)$translValue;
        }

        return $categoryId;
    }

    public function translateCategorySlug(string $categorySlug): string
    {
        $term = get_term_by('slug', $categorySlug, 'product_cat');
        if ($term && !is_wp_error($term)) {
            $categorySlug = $term->slug;
        }

        return $categorySlug;
    }

    public function translateAttributeId(int $attributeId): int
    {
        $term = get_term($attributeId);
        if ($term && !is_wp_error($term)) {
            $attributeId = $term->term_id;
        }

        return $attributeId;
    }

    public function translateTagId(int $tagId): int
    {
        $translValue = apply_filters('translate_object_id', $tagId, 'product_tag', false, $this->languageCode);
        if ($translValue) {
            $tagId = (int)$translValue;
        }

        return $tagId;
    }

    public function translateCustomTaxonomyTermId(int $termID, string $taxonomy): int
    {
        $translValue = apply_filters('translate_object_id', $termID, $taxonomy, false, $this->languageCode);
        if ($translValue) {
            $termID = (int)$translValue;
        }

        return $termID;
    }
}
