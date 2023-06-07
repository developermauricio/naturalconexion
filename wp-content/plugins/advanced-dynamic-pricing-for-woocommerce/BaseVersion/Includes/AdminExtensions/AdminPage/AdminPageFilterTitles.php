<?php

namespace ADP\BaseVersion\Includes\AdminExtensions\AdminPage;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl\ProductAttributesAll;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl\ProductCategoriesAll;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl\ProductCategorySlugAll;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl\ProductCustomFieldsAll;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl\ProductsAll;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl\ProductSKUAll;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Impl\ProductTagsAll;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepositoryInterface;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ListComparisonCondition;
use ADP\BaseVersion\Includes\Helpers\Helpers;

defined('ABSPATH') or exit;

class AdminPageFilterTitles
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context        = adp_context();
        $this->ruleRepository = new RuleRepository();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function withRuleRepository(RuleRepositoryInterface $repository)
    {
        $this->ruleRepository = $repository;
    }

    public function getTitles($rules)
    {
        return $this->getFilterTitles($this->getIdsForFilterTitles($rules));
    }

    protected function getFiltersByType($rules)
    {
        $filtersByType = array(
            'products'              => array(),
            'giftable_products'     => array(),
            'giftable_categories'   => array(),
            'auto_add_products'     => array(),
            'product_tags'          => array(),
            'product_categories'    => array(),
            'product_category_slug' => array(),
            'product_attributes'    => array(),
            'product_sku'           => array(),
            'product_sellers'       => array(),
            'product_custom_fields' => array(),
            'usermeta'              => array(),
            'users_list'            => array(),
            'coupons'               => array(),
            'subscriptions'         => array(),
            'rules_list'            => array(),
        );
        foreach (array_keys(Helpers::getCustomProductTaxonomies()) as $taxName) {
            $filtersByType[$taxName] = array();
        }

        return $filtersByType;
    }

    public function getIdsForFilterTitles($rules)
    {
        // make array of filters split by type
        $filtersByType = apply_filters('wdp_ids_for_filter_titles', $this->getFiltersByType($rules), $rules);

        $conditionsQtyCustomTaxonomyTypes    = array();
        $conditionsAmountCustomTaxonomyTypes = array();
        foreach (array_keys(Helpers::getCustomProductTaxonomies()) as $taxName) {
            $conditionsCustomTaxonomyTypes["custom_taxonomy_all_$taxName"]           = $taxName;
        }

        foreach ($rules as $rule) {
            foreach ($rule['filters'] as $filter) {
                if ( ! empty($filter['value'])) {
                    $type  = $filter['type'];
                    $value = $filter['value'];

                    if (isset($filtersByType[$type])) {
                        $filtersByType[$type] = array_merge($filtersByType[$type], (array)$value);
                    }
                }

                if (isset($filter['product_exclude']['values'])) {
                    foreach ($filter['product_exclude']['values'] as $productId) {
                        $filtersByType['products'][] = $productId;
                    }
                }
            }

            if (isset($rule['get_products']['value'])) {
                foreach ($rule['get_products']['value'] as $filter) {
                    if ( ! isset($filter['value'])) {
                        continue;
                    }
                    $giftMode = isset($filter['gift_mode']) ? $filter['gift_mode'] : "giftable_products";

                    $type = "giftable_products";
                    if ($giftMode === "allow_to_choose_from_product_cat") {
                        $type = "giftable_categories";
                    }

                    $value = $filter['value'];

                    $filtersByType[$type] = array_merge($filtersByType[$type], (array)$value);
                }
            }

            if (isset($rule['auto_add_products']['value'])) {
                foreach ($rule['auto_add_products']['value'] as $filter) {
                    if ( ! isset($filter['value'])) {
                        continue;
                    }
                    $value = $filter['value'];

                    $filtersByType['auto_add_products'] = array_merge($filtersByType['auto_add_products'], (array)$value);
                }
            }

            if (isset($rule['bulk_adjustments']['selected_categories'])) {
                $filtersByType['product_categories'] = array_merge($filtersByType['product_categories'],
                    (array)$rule['bulk_adjustments']['selected_categories']);
            }

            if (isset($rule['bulk_adjustments']['selected_products'])) {
                $filtersByType['products'] = array_merge($filtersByType['products'],
                    (array)$rule['bulk_adjustments']['selected_products']);
            }

            if (isset($rule['conditions'])) {
                foreach ($rule['conditions'] as $condition) {
                    if ($condition['type'] === 'specific' && isset($condition['options'][2])) {
                        $value                       = $condition['options'][2];
                        $filtersByType['users_list'] = array_merge($filtersByType['users_list'], (array)$value);
                    } elseif ($condition['type'] === ProductAttributesAll::getType() && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                               = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['product_attributes'] = array_merge($filtersByType['product_attributes'],
                            (array)$value);
                    } elseif ($condition['type'] === ProductCustomFieldsAll::getType() && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                                  = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['product_custom_fields'] = array_merge($filtersByType['product_custom_fields'],
                            (array)$value);
                    } elseif ($condition['type'] === ProductSKUAll::getType() && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                                  = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['product_sku'] = array_merge($filtersByType['product_sku'],
                            (array)$value);
                    } elseif ($condition['type'] === 'usermeta' && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                     = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['usermeta'] = array_merge($filtersByType['usermeta'],
                            (array)$value);
                    } elseif ($condition['type'] === ProductCategoriesAll::getType() && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                               = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['product_categories'] = array_merge($filtersByType['product_categories'],
                            (array)$value);
                    } elseif ($condition['type'] === ProductCategorySlugAll::getType() && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                                  = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['product_category_slug'] = array_merge($filtersByType['product_category_slug'],
                            (array)$value);
                    } elseif ($condition['type'] === ProductTagsAll::getType() && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                         = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['product_tags'] = array_merge($filtersByType['product_tags'],
                            (array)$value);
                    } elseif ($condition['type'] === ProductsAll::getType() && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                     = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['products'] = array_merge($filtersByType['products'], (array)$value);
                    } elseif ($condition['type'] === 'cart_was_rule_applied' && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                       = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['rules_list'] = array_merge($filtersByType['rules_list'], (array)$value);
                    } elseif ($condition['type'] === 'cart_coupons' && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                    = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['coupons'] = array_merge($filtersByType['coupons'], (array)$value);
                    } elseif ($condition['type'] === 'subscriptions' && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $value                          = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType['subscriptions'] = array_merge($filtersByType['subscriptions'],
                            (array)$value);
                    } elseif (isset($conditionsCustomTaxonomyTypes[$condition['type']]) && isset($condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY])) {
                        $taxName                 = $conditionsCustomTaxonomyTypes[$condition['type']];
                        $value                   = $condition['options'][ListComparisonCondition::COMPARISON_LIST_KEY];
                        $filtersByType[$taxName] = array_merge($filtersByType[$taxName], (array)$value);
                    }
                }

            }

            if (isset($rule['cart_adjustments'])) {
                foreach ($rule['cart_adjustments'] as $cartAdjustment) {
                    if ($cartAdjustment['type'] === 'discount__apply_wc_coupons' && isset($cartAdjustment['options'])) {
                        $value = $cartAdjustment['options'];
                        $filtersByType['coupons'] = array_merge($filtersByType['coupons'], (array)$value);
                    } elseif ($cartAdjustment['type'] === 'discount__disable_wc_coupons' && isset($cartAdjustment['options'])) {
                        $value = $cartAdjustment['options'];
                        $filtersByType['coupons'] = array_merge($filtersByType['coupons'], (array)$value);
                    }
                }
            }

        }

        return $filtersByType;
    }


    /**
     * Retrieve from getIdsForFilterTitles() function filters
     * all products, tags, categories, attributes and return titles
     *
     * @param array $filtersByType
     *
     * @return array
     */
    public function getFilterTitles($filtersByType)
    {
        $result = array();

        // type 'products'
        $result['products'] = array();
        foreach ($filtersByType['products'] as $id) {
            $result['products'][$id] = '#' . $id . ' ' . Helpers::getProductTitle($id);
        }

        if (isset($_GET['product'])) {
            $id                      = $_GET['product'];
            $result['products'][$id] = '#' . $id . ' ' . Helpers::getProductTitle($id);
        }

        $result['rules_list'] = array();
        if (is_array($filtersByType['rules_list']) && ! empty($filtersByType['rules_list'])) {
            $rulesList = $this->ruleRepository->getRules($filtersByType['rules_list']);
            foreach ($rulesList as $rule) {
                $result['rules_list'][$rule->id] = $rule->title;
            }
        }

        // type 'giftable_products'
        $result['giftable_products'] = array();
        foreach ($filtersByType['giftable_products'] as $id) {
            $result['giftable_products'][$id] = '#' . $id . ' ' . Helpers::getProductTitle($id);
        }

        $result['giftable_categories'] = array();
        foreach ($filtersByType['giftable_categories'] as $id) {
            $result['giftable_categories'][$id] = Helpers::getCategoryTitle($id);
        }

        $result['auto_add_products'] = array();
        foreach ($filtersByType['auto_add_products'] as $id) {
            $result['auto_add_products'][$id] = '#' . $id . ' ' . Helpers::getProductTitle($id);
        }

        $result['product_sku'] = array();
        foreach ($filtersByType['product_sku'] as $sku) {
            $result['product_sku'][$sku] = 'SKU: ' . $sku;
        }

        $result['product_sellers'] = array();
        foreach ($filtersByType['product_sellers'] as $id) {
            $users                          = Helpers::getUsers(array($id));
            $result['product_sellers'][$id] = $users[0]['text'];
        }

        // type 'product_tags'
        $result['product_tags'] = array();
        foreach ($filtersByType['product_tags'] as $id) {
            $result['product_tags'][$id] = Helpers::getTagTitle($id);
        }

        // type 'product_categories'
        $result['product_categories'] = array();
        foreach ($filtersByType['product_categories'] as $id) {
            $result['product_categories'][$id] = Helpers::getCategoryTitle($id);
        }

        // type 'product_category_slug'
        $result['product_category_slug'] = array();
        foreach ($filtersByType['product_category_slug'] as $slug) {
            $result['product_category_slug'][$slug] = __('Slug',
                    'advanced-dynamic-pricing-for-woocommerce') . ': ' . $slug;
        }

        // product_taxonomies
        foreach (Helpers::getCustomProductTaxonomies() as $tax) {
            $result[$tax->name] = array();
            foreach ($filtersByType[$tax->name] as $id) {
                $result[$tax->name][$id] = Helpers::getProductTaxonomyTermTitle($id, $tax->name);
            }
        }

        // type 'product_attributes'
        $attributes                   = Helpers::getProductAttributes(array_unique($filtersByType['product_attributes']));
        $result['product_attributes'] = array();
        foreach ($attributes as $attribute) {
            $result['product_attributes'][$attribute['id']] = $attribute['text'];
        }

        // type 'product_custom_fields'
        $customfields                    = array_unique($filtersByType['product_custom_fields']); // use as is!
        $result['product_custom_fields'] = array();
        foreach ($customfields as $customfield) {
            $result['product_custom_fields'][$customfield] = $customfield;
        }

        // type 'users_list'
        $attributes           = Helpers::getUsers($filtersByType['users_list']);
        $result['users_list'] = array();
        foreach ($attributes as $attribute) {
            $result['users_list'][$attribute['id']] = $attribute['text'];
        }

        // type 'cart_coupons'
        $result['coupons'] = array();
        foreach (array_unique($filtersByType['coupons']) as $code) {
            $result['coupons'][$code] = $code;
        }

        // type 'subscriptions'
        $result['subscriptions'] = array();
        foreach ($filtersByType['subscriptions'] as $id) {
            $result['subscriptions'][$id] = '#' . $id . ' ' . Helpers::getProductTitle($id);
        }

        $result['usermeta'] = array();
        foreach ($filtersByType['usermeta'] as $usermeta) {
            $result['usermeta'][$usermeta] = $usermeta;
        }

        return apply_filters('wdp_filter_titles', $result, $filtersByType);
    }

    public function getLinks($rules)
    {
        return [];
    }
}
