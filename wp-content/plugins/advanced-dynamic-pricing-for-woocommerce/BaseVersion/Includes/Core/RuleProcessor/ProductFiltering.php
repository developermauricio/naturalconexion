<?php

namespace ADP\BaseVersion\Includes\Core\RuleProcessor;

use ADP\BaseVersion\Includes\Helpers\Helpers;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Cache\CacheHelper;
use WC_Meta_Data;
use WC_Product;
use WC_Product_Attribute;

defined('ABSPATH') or exit;

class ProductFiltering
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array<int,WC_Product>
     */
    protected $cachedParents = array();

    /**
     * @var Context
     */
    protected $context;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param string $operationType
     * @param mixed $operationValues
     * @param string|null $operationMethod
     */
    public function prepare($operationType, $operationValues, $operationMethod)
    {
        $this->type = $operationType;

        if (is_array($operationValues)) {
            $this->values = $operationValues;
        } else {
            $this->value = $operationValues;
        }

        $this->method = ! empty($operationMethod) ? $operationMethod : 'in_list';
    }

    public function setType($operationType)
    {
        $this->type = $operationType;
    }

    public function isType($type)
    {
        return $type === $this->type;
    }

    public function setOperationValues($operationValues)
    {
        $this->values = $operationValues;
    }

    public function setMethod($operation_method)
    {
        $this->method = $operation_method;
    }

    /**
     * @param WC_Product $product
     *
     * @return false|WC_Product|null
     */
    protected function getMainProduct($product)
    {
        if ( ! $product->get_parent_id()) {
            return $product;
        }

        $parent = CacheHelper::getWcProduct($product->get_parent_id());

        return $parent ? $parent : $product;
    }

    /**
     * @param WC_Product $product
     * @param array $cartItem
     *
     * @return bool
     */
    public function checkProductSuitability($product, $cartItem = array())
    {
        if ($this->type === 'any' && $this->method === 'in_list') {
            return true;
        }

        if ($this->method === 'any') {
            return true;
        }

        if ( ! ((isset($this->values) && count($this->values)) || isset($this->value))) {
            return false;
        }

        $func = array($this, "compareProductWith" . ucfirst($this->type));

        if (is_callable($func)) {
            return call_user_func($func, $product, $cartItem);
        } elseif (in_array($this->type, array_keys(Helpers::getCustomProductTaxonomies()))) {
            return $this->compareProductWithCustom_taxonomy($product, $cartItem);
        }

        return false;
    }

    protected function compareProductWithProducts($product, $cartItem)
    {
        $result         = false;
        $product_parent = $this->getMainProduct($product);

        if ('in_list' === $this->method) {
            $result = (in_array($product->get_id(), $this->values) or in_array($product_parent->get_id(),
                    $this->values));
        } elseif ('not_in_list' === $this->method) {
            $result = ! (in_array($product->get_id(), $this->values) or in_array($product_parent->get_id(),
                    $this->values));
        } elseif ('any' === $this->method) {
            $result = true;
        }

        return $result;
    }

    protected function compareProductWithProduct_categories($product, $cartItem)
    {
        $product    = $this->getMainProduct($product);
        $categories = $product->get_category_ids();

        $values = array();
        foreach ($this->values as $value) {
            $values[] = $value;
            $child    = get_term_children($value, 'product_cat');

            if ($child && ! is_wp_error($child)) {
                $values = array_merge($values, $child);
            }
        }

        $is_product_in_category = count(array_intersect($categories, $values)) > 0;

        if ('in_list' === $this->method) {
            return $is_product_in_category;
        } elseif ('not_in_list' === $this->method) {
            return ! $is_product_in_category;
        }

        return false;
    }

    protected function compareProductWithProduct_category_slug($product, $cartItem)
    {
        $product        = $this->getMainProduct($product);
        $category_slugs = array_map(function ($category_id) {
            $term = get_term($category_id, 'product_cat');

            return $term ? $term->slug : '';
        }, $product->get_category_ids());

        $is_product_in_category = count(array_intersect($category_slugs, $this->values)) > 0;

        if ('in_list' === $this->method) {
            return $is_product_in_category;
        } elseif ('not_in_list' === $this->method) {
            return ! $is_product_in_category;
        }

        return false;
    }

    protected function compareProductWithProduct_tags($product, $cartItem)
    {
        $product = $this->getMainProduct($product);
        $tag_ids = $product->get_tag_ids();

        $is_product_has_tag = count(array_intersect($tag_ids, $this->values)) > 0;

        if ('in_list' === $this->method) {
            return $is_product_has_tag;
        } elseif ('not_in_list' === $this->method) {
            return ! $is_product_has_tag;
        }

        return false;
    }

    /**
     * @param WC_Product $product
     * @param array $cartItem
     *
     * @return bool
     */
    protected function compareProductWithProduct_attributes($product, $cartItem)
    {
//		$product = $this->get_cached_wc_product( $product ); // use variation attributes?
        $attrs = $product->get_attributes();

        $calculatedTermObj = array();
        $termAttrIds       = array(
            'empty' => array(),
        );

        $attrIds    = array();
        $attrCustom = array();

        if ($product->is_type('variation')) {
            if (count(array_filter($attrs)) < count($attrs)) {
                if (isset($cartItem['variation'])) {
                    $attrs = array();
                    foreach ($cartItem['variation'] as $attributeName => $value) {
                        $attrs[str_replace('attribute_', '', $attributeName)] = $value;
                    }
                }
            }

            $productVariable = $this->getMainProduct($product);
            $attrsVariable   = $productVariable->get_attributes();

            foreach ($attrsVariable as $attributeName => $productAttr) {
                /**
                 * @var WC_Product_Attribute $productAttr
                 */
                if ( ! $productAttr->get_variation()) {
                    $attrs[$productAttr->get_name()] = "";
                }
            }

            foreach ($attrs as $attributeName => $value) {
                $initAttributeName = $attributeName;
                $attributeName     = $this->attributeTaxonomySlug($attributeName);
                if ($value) {
                    $term_obj = get_term_by('slug', $value, $initAttributeName);
                    if ( ! is_wp_error($term_obj) && $term_obj && $term_obj->name) {
                        $attrIds[$attributeName] = (array)($term_obj->term_id);
                    } else {
                        $attrCustom[$attributeName] = (array)($value);
                    }
                } else {
                    // replace undefined variation attribute by the list of all option of this attribute
                    if (isset($attrsVariable[$attributeName])) {
                        $attributeObject = $attrsVariable[$attributeName];
                    } elseif (isset($attrsVariable['pa_' . $attributeName])) {
                        $attributeObject = $attrsVariable['pa_' . $attributeName];
                    } else {
                        continue;
                    }

                    /** @var WC_Product_Attribute $attributeObject */
                    if ($attributeObject->is_taxonomy()) {
                        $attrIds[$attributeName] = (array)($attributeObject->get_options());
                        foreach ($attributeObject->get_terms() as $term) {
                            /**
                             * @var \WP_Term $term
                             */
                            $attrCustom[$attributeName][] = $term->name;
                        }
                    } else {
                        if (strtolower($attributeName) == strtolower($attributeObject->get_name())) {
                            $attrCustom[$attributeName] = $attributeObject->get_options();
                        }
                    }
                }
            }
        } else {
            foreach ($attrs as $attr) {
                /** @var WC_Product_Attribute $attr */
                if ($attr->is_taxonomy()) {
                    $attrIds[strtolower($attr->get_name())] = (array)($attr->get_options());
                } else {
                    if (strtolower($attr->get_name()) == strtolower($attr->get_name())) {
                        $attrCustom[strtolower($attr->get_name())] = $attr->get_options();
                    }
                }
            }
        }

        $operationValuesTax         = array();
        $operationValuesCustomAttrs = array();
        foreach ($this->values as $attrId) {
            $term_obj = false;

            foreach ($termAttrIds as $hash => $tmpAttrIds) {
                if (in_array($attrId, $tmpAttrIds)) {
                    $term_obj = isset($calculatedTermObj[$hash]) ? $calculatedTermObj[$hash] : false;
                    break;
                }
            }

            if (empty($term_obj)) {
                $term_obj = get_term($attrId);
                if ( ! $term_obj) {
                    $termAttrIds['empty'][] = $attrId;
                    continue;
                }

                if (is_wp_error($term_obj)) {
                    continue;
                }

                $hash                     = md5(json_encode($term_obj));
                $calculatedTermObj[$hash] = $term_obj;
                if ( ! isset($termAttrIds[$hash])) {
                    $termAttrIds[$hash] = array();
                }
                $termAttrIds[$hash][] = $attrId;
            }

            $attributeName = $this->attributeTaxonomySlug($term_obj->taxonomy);
            if ( ! isset($operationValuesTax[$attributeName])) {
                $operationValuesTax[$attributeName] = array();
            }
            $operationValuesTax[$attributeName][]         = $attrId;
            $operationValuesCustomAttrs[$attributeName][] = $term_obj->name;
        }

        $isProductHasAttrsId = true;
        foreach ($operationValuesTax as $attributeName => $tmpAttrIds) {
            if (
                (
                    ! isset($attrIds[$attributeName])
                    || ! count(array_intersect($tmpAttrIds, $attrIds[$attributeName]))
                )
                && (
                    ! isset($attrIds[wc_attribute_taxonomy_name($attributeName)])
                    || ! count(array_intersect($tmpAttrIds, $attrIds[wc_attribute_taxonomy_name($attributeName)]))
                )
            ) {
                $isProductHasAttrsId = false;
                break;
            }
        }

        $isProductHasAttrsCustom = true;
        foreach ($operationValuesCustomAttrs as $attributeName => $tmp_attr_names) {
            if (
                ! isset($attrCustom[$attributeName])
                || ! count(array_intersect($tmp_attr_names, $attrCustom[$attributeName]))
            ) {
                $isProductHasAttrsCustom = false;
                break;
            }
        }

        if ('in_list' === $this->method) {
            return $isProductHasAttrsId || $isProductHasAttrsCustom;
        } elseif ('not_in_list' === $this->method) {
            return ! ($isProductHasAttrsId || $isProductHasAttrsCustom);
        }

        return false;
    }

    /**
     * @param WC_Product $product
     * @param array $cartItem
     *
     * @return bool
     */
    protected function compareProductWithProduct_custom_attributes($product, $cartItem)
    {

        $attrs = $product->get_attributes();

        $attrsCustom = array();

        if ($product->is_type('variation')) {
            $productVariable = $this->getMainProduct($product);
            $attrsVariable   = $productVariable->get_attributes();

            foreach ($attrsVariable as $attributeName => $productAttr) {
                /**
                 * @var WC_Product_Attribute $productAttr
                 */
                if ( ! $productAttr->get_variation()) {
                    if ($productAttr->is_taxonomy()) {
                        $attrs[$productAttr->get_name()] = array_map(function ($termId) use ($productAttr) {
                            return get_term($termId, $productAttr->get_taxonomy())->slug;
                        }, $productAttr->get_options());
                    } else {
                        $attrs[$productAttr->get_name()] = $productAttr->get_options();
                    }
                }
            }

            foreach ($attrs as $attributeName => $value) {
                $initAttributeName = $attributeName;
                $attributeName     = $this->attributeTaxonomySlug($attributeName);
                if ($value) {
                    $value = is_array($value) ? $value : [$value];
                    if ( ! isset($attrsCustom[$attributeName])) {
                        $attrsCustom[$attributeName] = [];
                    }
                    foreach ($value as $slug) {
                        $termObj = get_term_by('slug', $slug, $initAttributeName);
                        if (is_wp_error($termObj) || ! $termObj) {
                            $attrsCustom[$attributeName][] = $slug;
                        }
                    }
                } else {
                    // replace undefined variation attribute by the list of all option of this attribute
                    if (isset($attrsVariable[$attributeName])) {
                        $attributeObject = $attrsVariable[$attributeName];
                    } elseif (isset($attrsVariable['pa_' . $attributeName])) {
                        $attributeObject = $attrsVariable['pa_' . $attributeName];
                    } else {
                        continue;
                    }

                    /** @var WC_Product_Attribute $attributeObject */
                    if ( ! $attributeObject->is_taxonomy()) {
                        if (strtolower($attributeName) == strtolower($attributeObject->get_name())) {
                            $attrsCustom[$attributeName] = $attributeObject->get_options();
                        }
                    }
                }
            }
        } else {
            foreach ($attrs as $attr) {
                /** @var WC_Product_Attribute $attr */
                if ( ! $attr->is_taxonomy()) {
                    $attrsCustom[strtolower($attr->get_name())] = $attr->get_options();
                }
            }
        }


        $attrsCustom = array_map(function ($options) {
            return array_map("strtolower", $options);
        }, $attrsCustom);

        $inList = false;
        foreach ($this->values as $customAttr) {
            $pieces        = explode(":", $customAttr);
            $attributeName = strtolower(trim($pieces[0]));
            $option        = strtolower(trim($pieces[1]));

            if (isset($attrsCustom[$attributeName])) {
                if (in_array($option, $attrsCustom[$attributeName], true)) {
                    $inList = true;
                }
            }
        }

        if ('in_list' === $this->method) {
            return $inList;
        } elseif ('not_in_list' === $this->method) {
            return ! $inList;
        }

        return false;
    }

    private function attributeTaxonomySlug($attributeName)
    {
        $attributeName  = wc_sanitize_taxonomy_name($attributeName);
        $attribute_slug = 0 === strpos($attributeName, 'pa_') ? substr($attributeName, 3) : $attributeName;

        return $attribute_slug;
    }

    /**
     * @param \WC_Product $product
     * @param array $cartItem
     *
     * @return bool
     */
    protected function compareProductWithProduct_sku($product, $cartItem)
    {
        $result      = false;
        $productSkus = array($product->get_sku());

        if ($product->get_parent_id() && ($parent = CacheHelper::getWcProduct($product->get_parent_id()))) {
            $productSkus[] = $parent->get_sku();
        }

        if ('in_list' === $this->method) {
            $result = (count(array_intersect($productSkus, $this->values)) > 0);
        } elseif ('not_in_list' === $this->method) {
            $result = count(array_intersect($productSkus, $this->values)) === 0;
        } elseif ('any' === $this->method) {
            $result = true;
        }

        return $result;
    }

    protected function compareProductWithProductSellers($product, $cartItem)
    {
        $result = false;

        $product_post = get_post($product->get_id());
        $postAuthor   = $product_post->post_author;

        if ('in_list' === $this->method) {
            $result = (in_array($postAuthor, $this->values));
        } elseif ('not_in_list' === $this->method) {
            $result = ! (in_array($postAuthor, $this->values));
        }

        return $result;
    }

    protected function compareProductWithProduct_custom_fields($product, $cartItem)
    {
        $parentProduct             = $this->getMainProduct($product);
        $checkChildrenCustomFields = apply_filters(
            'wdp_compare_product_with_product_custom_fields_check_children',
            false
        );
        $meta                      = array();

        if ($checkChildrenCustomFields) {
            $meta = $this->getProductMeta($product);
        }

        $meta               = array_merge_recursive($this->getProductMeta($parentProduct), $meta);
        $customFields       = $this->prepareMeta($meta);
        $values             = is_array($this->values) ? $this->values : array();
        $isProductHasFields = count(array_intersect($customFields, $values)) > 0;

        if ( ! $isProductHasFields) {
            $meta = array();

            if ($checkChildrenCustomFields) {
                $meta = $this->getProductPostMeta($product);
            }

            $meta               = array_merge_recursive($this->getProductPostMeta($parentProduct), $meta);
            $customFields       = $this->prepareMeta($meta);
            $isProductHasFields = count(array_intersect($customFields, $values)) > 0;
        }

        if ('in_list' === $this->method) {
            return $isProductHasFields;
        } elseif ('not_in_list' === $this->method) {
            return ! $isProductHasFields;
        }

        return false;
    }

    protected function compareProductWithCustom_taxonomy($product, $cartItem)
    {
        $product  = $this->getMainProduct($product);
        $taxonomy = $this->type;

        $termIds          = wp_get_post_terms($product->get_id(), $taxonomy, array("fields" => "ids"));
        $isProductHasTerm = count(array_intersect($termIds, $this->values)) > 0;

        if ('in_list' === $this->method) {
            return $isProductHasTerm;
        } elseif ('not_in_list' === $this->method) {
            return ! $isProductHasTerm;
        }

        return false;
    }

    protected function compareProductWithProduct_shipping_class($product, $cartItem)
    {
        $shippingClass = $product->get_shipping_class();

        $hasProductShippingClass = in_array($shippingClass, $this->values);

        if ('in_list' === $this->method) {
            return $hasProductShippingClass;
        } elseif ('not_in_list' === $this->method) {
            return ! $hasProductShippingClass;
        }

        return false;
    }

    /**
     * @param WC_Product $product
     *
     * @return array
     */
    private function getProductMeta($product)
    {
        $meta = array();

        foreach ($product->get_meta_data() as $metaDatum) {
            /**
             * @var WC_Meta_Data $metaDatum
             */
            $data = $metaDatum->get_data();

            if ( ! isset($meta[$data['key']])) {
                $meta[$data['key']] = array();
            }
            $meta[$data['key']][] = $data['value'];
        }

        return $meta;
    }

    /**
     * @param WC_Product $product
     *
     * @return array
     */
    private function getProductPostMeta($product)
    {
        if ( ! ($postMeta = get_post_meta($product->get_id(), ""))) {
            return array();
        };
        $meta = array();

        foreach ($postMeta as $key => $value) {
            $meta[$key] = $value;
        }

        return $meta;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    private function prepareMeta($meta)
    {
        $customFields = array();
        foreach ($meta as $key => $values) {
            foreach ($values as $value) {
                if ( ! is_array($value) && ! is_object($value)) {
                    $customFields[] = "$key=$value";
                }
            }
        }

        return $customFields;
    }

}
