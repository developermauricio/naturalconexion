<?php

namespace ADP\BaseVersion\Includes\Core\Rule\Internationalization;

interface IObjectInternationalization
{
    public function translateProductId(int $productId): int;

    public function translateCategoryId(int $categoryId): int;

    public function translateCategorySlug(string $categorySlug): string;

    public function translateAttributeId(int $attributeId): int;

    public function translateTagId(int $tagId): int;

    public function translateCustomTaxonomyTermId(int $termID, string $taxonomy): int;
}
