<?php

namespace ADP\BaseVersion\Includes\AdminExtensions;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepositoryInterface;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepositoryInterface;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Helpers\Helpers;

defined('ABSPATH') or exit;

class WcProductPageExtensions
{
    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var Context
     */
    protected $context;

   /**
     * @var PersistentRuleRepositoryInterface
     */
    protected $persistentRuleRepository;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context                  = adp_context();
        $this->ruleRepository           = new RuleRepository();
        $this->persistentRuleRepository = new PersistentRuleRepository();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function withRuleRepository(RuleRepositoryInterface $repository)
    {
        $this->ruleRepository = $repository;
    }

    public function withPersistentRuleRepository(PersistentRuleRepositoryInterface $repository)
    {
        $this->persistentRuleRepository = $repository;
    }

    public function register()
    {
        add_action('woocommerce_product_write_panel_tabs', array($this, 'editRulesTab'));
        add_action('woocommerce_product_data_panels', array($this, 'editRulesPanel'));
        add_action('woocommerce_after_' . 'product' . '_object_save', array($this, 'dropCacheAfterProductSave'), 10, 2);
    }

    public function editRulesTab()
    {
        ?>
        <li class="edit_rules_tab">
            <a href="#edit_rules_data">
                <span>
                    <?php _e('Pricing Rules', 'advanced-dynamic-pricing-for-woocommerce'); ?>
                </span>
            </a>
        </li>
        <?php
    }

    public function editRulesPanel()
    {
        global $post, $thepostid, $product_object;

        /** Some boys like to purge global variables. We will not allow ourselves to be beaten. */
        if ($product_object instanceof \WC_Product) {
            $product = CacheHelper::getWcProduct($product_object);
        } elseif (is_numeric($thepostid)) {
            $product = CacheHelper::getWcProduct($thepostid);
        } elseif ($post instanceof \WP_Post) {
            $product = CacheHelper::getWcProduct($post);
        } else {
            $product = null;
        }

        if ( ! $product) {
            ?>
            <div id="edit_rules_data" class="panel woocommerce_options_panel">
                <h4><?php _e('Product wasn\'t returned', 'advanced-dynamic-pricing-for-woocommerce'); ?></h4>
            </div>
            <?php
            return;
        }


        $listRulesUrlArgs = array(
            'product'      => $product->get_id(),
            'action_rules' => 'list',
        );

        if ($product instanceof \WC_Product_Variable && ! empty($product->get_children())) {
            $listRulesUrlArgs['product_childs'] = $product->get_children();
        }

        if ( ! empty($product->get_sku())) {
            $listRulesUrlArgs['product_sku'] = $product->get_sku();
        }

        $categories = get_the_terms($product->get_id(), 'product_cat');
        if ($categories !== false && ! ($categories instanceof \WP_Error)) {
            if (($termIds = array_column($categories, 'term_id')) && ! empty($termIds)) {
                $listRulesUrlArgs['product_categories'] = $termIds;
            }
            if (($slugs = array_column($categories, 'slug')) && ! empty($slugs)) {
                $listRulesUrlArgs['product_category_slug'] = $slugs;
            }
        }

        if ( ! empty($product->get_attributes())) {
            $productAttributes   = $product->get_attributes();
            $productAttributeIds = array();
            foreach ($productAttributes as $attr) {
                $terms = get_the_terms($product->get_id(), $attr->get_name());
                if ($terms !== false && ! ($terms instanceof \WP_Error)) {
                    $productAttributeIds = array_merge($productAttributeIds,
                        Helpers::getProductAttributes(array_column($terms, 'term_id')));
                }
            }
            if ( ! empty($productAttributeIds)) {
                $listRulesUrlArgs['product_attributes'] = array_column($productAttributeIds, 'id');
            }
        }

        $tags = get_the_terms($product->get_id(), 'product_tag');
        if ($tags !== false && ! ($tags instanceof \WP_Error) && ! empty($tags)) {
            $listRulesUrlArgs["product_tags"] = array_column($tags, 'term_id');
        }

        $listRulesUrl = add_query_arg($listRulesUrlArgs, menu_page_url('wdp_settings', false));

        $addRulesUrl = add_query_arg(array(
            'product'      => $product->get_id(),
            'action_rules' => 'add',
        ), menu_page_url('wdp_settings', false));

        $rulesArgs = array('product' => $product->get_id(), 'active_only' => true);

        if (isset($listRulesUrlArgs['product_childs'])) {
            $rulesArgs['product_childs'] = $listRulesUrlArgs['product_childs'];
        }

        if (isset($listRulesUrlArgs['product_sku'])) {
            $rulesArgs['product_sku'] = $listRulesUrlArgs['product_sku'];
        }

        if (isset($listRulesUrlArgs['product_categories'])) {
            $rulesArgs['product_categories'] = $listRulesUrlArgs['product_categories'];
        }

        if (isset($listRulesUrlArgs['product_category_slug'])) {
            $rulesArgs['product_category_slug'] = $listRulesUrlArgs['product_category_slug'];
        }

        if (isset($listRulesUrlArgs['product_attributes'])) {
            $rulesArgs['product_attributes'] = $listRulesUrlArgs['product_attributes'];
        }

        if (isset($listRulesUrlArgs['product_tags'])) {
            $rulesArgs['product_tags'] = $listRulesUrlArgs['product_tags'];
        }

        $rules      = $this->ruleRepository->getRules($rulesArgs);
        $countRules = count($rules) != 0 ? count($rules) : '';
        ?>
        <div id="edit_rules_data" class="panel woocommerce_options_panel">
            <?php if (count($rules) != 0): ?>
                <button type="button" class="button" onclick="window.open('<?php echo $listRulesUrl ?>')"
                        style="margin: 5px;">
                    <?php printf(__('View %s rules for the product', 'advanced-dynamic-pricing-for-woocommerce'),
                        $countRules); ?></button>
            <?php endif; ?>
            <button type="button" class="button" onclick="window.open('<?php echo $addRulesUrl ?>')"
                    style="margin: 5px;">
                <?php _e('Add rule for the product', 'advanced-dynamic-pricing-for-woocommerce'); ?></button>
        </div>
        <?php
    }

    /**
     * @param \WC_Product $product
     * @param \WC_Product_Data_Store_CPT $dataStore
     */
    public function dropCacheAfterProductSave($product, $dataStore)
    {
        $this->persistentRuleRepository->recalculateCacheForProduct($this->context, $product);
    }
}
