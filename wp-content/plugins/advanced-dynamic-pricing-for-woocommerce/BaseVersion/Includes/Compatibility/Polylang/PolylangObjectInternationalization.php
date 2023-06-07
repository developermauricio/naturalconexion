<?php

namespace ADP\BaseVersion\Includes\Compatibility\Polylang;

use ADP\BaseVersion\Includes\Core\Rule\Internationalization\IObjectInternationalization;

class PolylangObjectInternationalization implements IObjectInternationalization
{
    /**
     * @var string
     */
    private $languageSlug;

    public function __construct($languageSlug)
    {
        $this->languageSlug = $languageSlug;
    }

    public function translateProductId(int $productId): int
    {
        if ( $translValue = PLL()->model->post->get_translation( $productId, $this->languageSlug ) ) {
            $productId = (int)$translValue;
        }

        return $productId;
    }

    public function translateCategoryId(int $categoryId): int
    {
        if ( $translValue = PLL()->model->term->get_translation( $categoryId, $this->languageSlug ) ) {
            $categoryId = (int)$translValue;
        }

        return $categoryId;
    }

    public function translateCategorySlug(string $categorySlug): string
    {
        $term = get_term_by('slug', $categorySlug, 'product_cat');
        if ($term && !is_wp_error($term)) {
            if ( $translTermId = $this->translateCategoryId($term->term_id) ) {
                $translTerm = get_term( $translTermId, 'product_cat' );

                if ($translTerm && !is_wp_error($translTerm)) {
                    $categorySlug = $translTerm->slug;
                }
            }
        }

        return $categorySlug;
    }

    public function translateAttributeId(int $attributeId): int
    {
        if ( $translValue = PLL()->model->term->get_translation( $attributeId, $this->languageSlug ) ) {
            $attributeId = (int)$translValue;
        }

        return $attributeId;
    }

    public function translateTagId(int $tagId): int
    {
        if ( $translValue = PLL()->model->term->get_translation( $tagId, $this->languageSlug ) ) {
            $tagId = (int)$translValue;
        }

        return $tagId;
    }

    public function translateCustomTaxonomyTermId(int $termID, string $taxonomy): int
    {
        if ( $translValue = PLL()->model->term->get_translation( $termID, $this->languageSlug ) ) {
            $termID = (int)$translValue;
        }

        return $termID;
    }
}
