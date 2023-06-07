<?php

namespace ADP\BaseVersion\Includes\AdminExtensions\AdminPage\Tabs;

use ADP\BaseVersion\Includes\AdminExtensions\AdminPage\AdminPageFilterTitles;
use ADP\BaseVersion\Includes\AdminExtensions\Ajax;
use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Core\Rule\CartAdjustment\CartAdjustmentsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\ConditionsLoader;
use ADP\BaseVersion\Includes\Core\Rule\CartCondition\Interfaces\ProductAll;
use ADP\BaseVersion\Includes\Core\Rule\Limit\LimitsLoader;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\Factory;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage\AdminTabInterface;
use ADP\BaseVersion\Includes\Helpers\Helpers;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage\Paginator;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;

defined('ABSPATH') or exit;

class Rules implements AdminTabInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @var Context
     */
    protected $context;

    public function __construct($deprecated = null)
    {
        $this->context   = adp_context();
        $this->title     = self::getTitle();
        $this->paginator = new Paginator();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function handleSubmitAction()
    {
        if ( $bulkAction = $this->getBulkAction() ) {
            $ruleRepository = new RuleRepository();
            $rulesList = array_map('intval', is_array($_POST['rules']) ? $_POST['rules'] : []);

            if ( $bulkAction === "enable" ) {
                array_map( [$ruleRepository, 'enableRule'], $rulesList );
            } elseif ( $bulkAction === "disable" ) {
                array_map( [$ruleRepository, 'disableRule'], $rulesList );
            } elseif ( $bulkAction === "delete" ) {
                array_map( [$ruleRepository, 'markRuleAsDeleted'], $rulesList );
            }

            wp_redirect($_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    public static function getRelativeViewPath()
    {
        return 'admin_page/tabs/rules.php';
    }

    public static function getHeaderDisplayPriority()
    {
        return 10;
    }

    public static function getKey()
    {
        return 'rules';
    }

    public static function getTitle()
    {
        return __('Rules', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public function getViewVariables()
    {
        $context = $this->context;

        /**
         * @var ConditionsLoader $conditionsLoader
         * @var LimitsLoader $limitsLoader
         * @var CartAdjustmentsLoader $cartAdjLoader
         */
        $conditionsLoader = Factory::get("Core_Rule_CartCondition_ConditionsLoader");
        $limitsLoader     = Factory::get("Core_Rule_Limit_LimitsLoader");
        $cartAdjLoader    = Factory::get('Core_Rule_CartAdjustment_CartAdjustmentsLoader');

        // $conditions_templates = $condition_registry->get_templates_content();
        // $conditions_titles    = $condition_registry->get_titles();

        $conditions_templates = array();
        $conditions_titles    = array();
        foreach ($conditionsLoader->getAsList() as $group => $items) {
            foreach ($items as $item) {
                $key          = $item[$conditionsLoader::LIST_TYPE_KEY];
                $label        = $item[$conditionsLoader::LIST_LABEL_KEY];
                $templatePath = $item[$conditionsLoader::LIST_TEMPLATE_PATH_KEY];
                $taxonomy     = $item['taxonomy'] ?? null;

                //needed for ProductAll view
                if ($templatePath === ProductAll::TEMPLATE_PATH) {
                    /** @var ProductAll $condition */
                    $condition = $conditionsLoader->create($key);

                    $subConditionTemplatePaths = $condition->getSubConditionTemplatePaths();
                    foreach ($subConditionTemplatePaths as $subKey => $subPath) {
                        ob_start();
                        include $subPath;
                        if (isset($taxonomy)) {
                            $subKey = $subKey . '_' . $taxonomy->name;
                        }
                        $conditions_templates[$subKey] = ob_get_clean();
                    }
                }

                ob_start();
                include $templatePath;
                $conditions_templates[$key] = ob_get_clean();

                //it will be replaced by ProductAll conditions
                if (!in_array($item['parent_class'], array('AbstractConditionCartItems', 'CartItemsAmountAbstract', 'CartItemsWeightAbstract'))) {
                    $conditions_titles[$conditionsLoader->getGroupLabel($group)][$key] = $label;
                }
            }
        }

        $limits_templates = array();
        $limits_titles    = array();
        foreach ($limitsLoader->getAsList() as $group => $items) {
            foreach ($items as $item) {
                $key          = $item[$limitsLoader::LIST_TYPE_KEY];
                $label        = $item[$limitsLoader::LIST_LABEL_KEY];
                $templatePath = $item[$limitsLoader::LIST_TEMPLATE_PATH_KEY];

                ob_start();
                include $templatePath;
                $limits_templates[$key] = ob_get_clean();

                $limits_titles[$limitsLoader->getGroupLabel($group)][$key] = $label;
            }
        }

        $cart_templates = array();
        $cart_titles    = array();
        foreach ($cartAdjLoader->getAsList() as $group => $items) {
            foreach ($items as $item) {
                $key          = $item[$cartAdjLoader::LIST_TYPE_KEY];
                $label        = $item[$cartAdjLoader::LIST_LABEL_KEY];
                $templatePath = $item[$cartAdjLoader::LIST_TEMPLATE_PATH_KEY];

                ob_start();
                include $templatePath;
                $cart_templates[$key] = ob_get_clean();

                $cart_titles[$cartAdjLoader->getGroupLabel($group)][$key] = $label;
            }
        }

        $ruleRepository = new RuleRepository();
        $ruleArgs = $this->makeGetRulesArgs();
        unset($ruleArgs['q']); // ignore on purpose
        $rulesCount = $ruleRepository->getRulesCount($ruleArgs);

        $ruleArgs['active'] = true;
        $activeRulesCount = $ruleRepository->getRulesCount($ruleArgs);

        $ruleArgs['active'] = false;
        $inactiveRulesCount = $ruleRepository->getRulesCount($ruleArgs);

        $active = $this->getActive();

        $tabUrl = add_query_arg(array(
            'page' => AdminPage::SLUG,
            'tab'  => self::getKey(),
        ), admin_url('admin.php'));

        $options                  = $this->context->getSettings();
        $pagination               = $this->getPaginationHtml();
        $tab                      = self::getKey();
        $page                     = AdminPage::SLUG;
        $hide_inactive            = $this->getIsHideInactive();
        $ruleSearchQ              = $this->getSearchQueryIfExists();
        $disable_all_rules_coupon = $options->getOption('disable_all_rules_coupon_applied');

        return compact('conditions_templates', 'conditions_titles', 'limits_templates', 'limits_titles',
            'cart_templates', 'cart_titles', 'options', 'pagination', 'page', 'hide_inactive',
            'disable_all_rules_coupon', 'tab', 'ruleSearchQ', 'rulesCount', 'activeRulesCount', 'inactiveRulesCount', 'tabUrl','active');
    }

    /**
     * @return array<int,Rule>
     */
    public function getTabRules()
    {
        $ruleRepository = new RuleRepository();
        $rules = $ruleRepository->getRules($this->makeGetRulesArgs());
        $rules = array_map(function($rule) {
            return $rule->getData();
        }, $rules);
        return $rules;
    }

    protected function getPaginationHtml()
    {
        $rulesPerPage = $this->context->getOption('rules_per_page');

        $ruleRepository = new RuleRepository();
        $rulesCount = $ruleRepository->getRulesCount($this->makeGetRulesArgs());
        $totalPages = (int)ceil($rulesCount / $rulesPerPage);

        $this->paginator->setTotalItems($rulesCount);
        $this->paginator->setTotalPages($totalPages);

        return $this->paginator->makeHtml();
    }

    protected function getIsHideInactive()
    {
        return ! empty($_GET['hide_inactive']);
    }

    protected function getIsOnlyActive()
    {
        return isset($_REQUEST['active']) && $_REQUEST['active'] === '1';
    }

    protected function getIsOnlyInactive()
    {
        return isset($_REQUEST['active']) && $_REQUEST['active'] === '0';
    }

    protected function getActive()
    {
        return $_REQUEST['active'] ?? "all";
    }

    protected function getSearchQueryIfExists()
    {
        return isset($_GET['action']) && $_GET['action'] === 'search_rules' && isset($_GET['q']) ? $_GET['q'] : "";
    }

    protected function getBulkAction()
    {
        return ! empty($_POST['bulk_action']) ? $_POST['bulk_action'] : "";
    }

    protected function makeGetRulesArgs()
    {
        $args = array();

        if ( ! empty($_GET['product'])) {
            $args['product'] = (int)$_GET['product'];
            if ( ! empty($_GET['product_childs']) && is_array($_GET['product_childs'])) {
                $args['product_childs'] = array_map(function ($value) {
                    return (int)$value;
                }, $_GET['product_childs']);
            }
            if ( ! empty($_GET['product_categories']) && is_array($_GET['product_categories'])) {
                $args['product_categories'] = array_map(function ($value) {
                    return (int)$value;
                }, $_GET['product_categories']);
            }
            if ( ! empty($_GET['product_category_slug']) && is_array($_GET['product_category_slug'])) {
                $args['product_category_slug'] = array_map(function ($value) {
                    return sanitize_text_field((string)$value);
                }, $_GET['product_category_slug']);
            }
            if ( ! empty($_GET['product_attributes']) && is_array($_GET['product_attributes'])) {
                $args['product_attributes'] = array_map(function ($value) {
                    return (int)$value;
                }, $_GET['product_attributes']);
            }
            if ( ! empty($_GET['product_tags']) && is_array($_GET['product_tags'])) {
                $args['product_tags'] = array_map(function ($value) {
                    return (int)$value;
                }, $_GET['product_tags']);
            }
            if ( ! empty($_GET['product_sku'])) {
                $args['product_sku'] = sanitize_text_field((string)$_GET['product_sku']);
            }

            return $args;
        }

        if ( ! empty($_GET['rule_id'])) {
            $args = array('id' => (int)$_GET['rule_id']);

            return $args;
        }

        if ($this->getIsHideInactive()) {
            $args['active_only'] = true;
        }

        if ($this->getIsOnlyActive()) {
            $args['active'] = true;
        }

        if ($this->getIsOnlyInactive()) {
            $args['active'] = false;
        }

        $page = call_user_func(array($this->paginator, 'getPageNum'));
        if ($page < 1) {
            return array();
        }

        $rules_per_page = $this->context->getOption('rules_per_page');
        $args['limit']  = array(($page - 1) * $rules_per_page, $rules_per_page);

        $args['exclusive'] = 0;

        if ($this->getSearchQueryIfExists()) {
            $args['q'] = sanitize_text_field((string)$_GET['q']);
        }

        return $args;
    }

    public function enqueueScripts()
    {
        $baseVersionUrl = WC_ADP_PLUGIN_URL . "/BaseVersion/";

        wp_enqueue_script('wdp_settings-scripts', $baseVersionUrl . 'assets/js/rules.js', array(
            'jquery',
            'jquery-ui-sortable',
            'wdp_select2',
        ), WC_ADP_VERSION);

        $rules = $this->getTabRules();
        $paged = $this->paginator->getPageNum();

        $preloaded_lists = array(
            'currencies'           => Helpers::getCurrencies(),
            'payment_methods'      => Helpers::getPaymentMethods(),
            'shipping_methods'     => Helpers::getShippingMethods(),
            'all_shipping_methods' => Helpers::getAllShippingMethods(),
            'shipping_class'       => Helpers::getShippingClasses(),
            'shipping_zones'       => Helpers::getShippingZones(),
            'countries'            => Helpers::getCountries(),
            'states'               => Helpers::getStates(),
            'user_roles'           => Helpers::getUserRoles(),
            'languages'            => Helpers::getLanguages(),
            'user_capabilities'    => Helpers::getUserCapabilities(),
            'weekdays'             => Helpers::getWeekdays(),
        );

        foreach ($preloaded_lists as $list_key => &$list) {
            $list = apply_filters('wdp_preloaded_list_' . $list_key, $list);
        }

        $context = $this->context;
        /** @var AdminPageFilterTitles $adminFilterTitles */
        $adminFilterTitles = Factory::get("AdminExtensions_AdminPage_AdminPageFilterTitles", $context, new RuleRepository());

        $wdp_data = array(
            'page'               => self::getKey(),
            'rules'              => $rules,
            'titles'             => $adminFilterTitles->getTitles($rules),
            'links'              => $adminFilterTitles->getLinks($rules),
            'labels'             => array(
                'select2_no_results'       => _x('no results', 'select2 msg when results wasn\'t found',
                    'advanced-dynamic-pricing-for-woocommerce'),
                'select2_input_too_short'  => _x('Please enter %d or more characters',
                    'select2 msg when input is too short', 'advanced-dynamic-pricing-for-woocommerce'),
                'select2_input_too_long'   => _x('Please delete %d character', 'select2 msg when input is too long',
                    'advanced-dynamic-pricing-for-woocommerce'),
                'select2_error_loading'    => _x('The results could not be loaded',
                    'select2 msg when it get error while loading', 'advanced-dynamic-pricing-for-woocommerce'),
                'select2_loading_more'     => _x('Loading more results…', 'select2 msg when loading more',
                    'advanced-dynamic-pricing-for-woocommerce'),
                'select2_maximum_selected' => _x('You can only select %d item', 'select2 msg when max items selected',
                    'advanced-dynamic-pricing-for-woocommerce'),
                'select2_searching'        => _x('Searching…', 'select2 msg when searching',
                    'advanced-dynamic-pricing-for-woocommerce'),
                'confirm_remove_rule'      => __('Remove rule?', 'advanced-dynamic-pricing-for-woocommerce'),
                'currency_symbol'          => get_woocommerce_currency_symbol(),
                'fixed_discount'           => __('Fixed discount for item', 'advanced-dynamic-pricing-for-woocommerce'),
                'fixed_price'              => __('Fixed price for item', 'advanced-dynamic-pricing-for-woocommerce'),
                'fixed_discount_for_set'   => __('Fixed discount for set', 'advanced-dynamic-pricing-for-woocommerce'),
                'fixed_price_for_set'      => __('Fixed price for set', 'advanced-dynamic-pricing-for-woocommerce'),
                'are_you_sure_to_delete_selected_rules' => __(
                    "Are you sure to delete the selected rules?",
                    'advanced-dynamic-pricing-for-woocommerce'
                ),
            ),
            'lists'              => $preloaded_lists,
            'selected_rule'      => isset($_GET['rule_id']) ? (int)$_GET['rule_id'] : -1,
            'product'            => isset($_GET['product']) ? (int)$_GET['product'] : -1,
            'product_title'      => isset ($_GET['product']) ? CacheHelper::getWcProduct($_GET['product'])->get_title() : -1,
            'action_rules'       => isset($_GET['action_rules']) ? $_GET['action_rules'] : -1,
            'bulk_rule'          => self::getAllAvailableTypes(),
            'persistence_bulk_rule' => self::getAllAvailablePersistenceTypes(),
            'options'            => array(
                'close_on_select'        => defined("WC_ADP_PRO_VERSION_URL") ? false : true,
                'enable_product_exclude' => $context->getOption('allow_to_exclude_products'),
                'rules_per_page'         => $context->getOption('rules_per_page'),
            ),
            'paged'              => $paged,
            'security'           => wp_create_nonce(Ajax::SECURITY_ACTION),
            'security_query_arg' => Ajax::SECURITY_QUERY_ARG,
        );
        wp_localize_script('wdp_settings-scripts', 'wdp_data', $wdp_data);
    }

    public function registerAjax()
    {

    }

    protected static function getAllAvailableTypes()
    {
        return array(
            'bulk' => array(
                'all'                         => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::setDiscountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                        self::setPriceFixed(),
                    )),
                    'label' => __('Qty based on all matched products', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'total_qty_in_cart'           => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::setDiscountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                        self::setPriceFixed(),
                    )),
                    'label' => __('Qty based on all items in the cart', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'product_categories'          => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::setDiscountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                        self::setPriceFixed(),
                    )),
                    'label' => __('Qty based on product categories in all cart',
                        'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'product_selected_categories' => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::setDiscountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                        self::setPriceFixed(),
                    )),
                    'label' => __('Qty based on selected categories in all cart',
                        'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'selected_products'           => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::setDiscountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                        self::setPriceFixed(),
                    )),
                    'label' => __('Qty based on selected products in all cart',
                        'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'sets'                        => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::setDiscountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                        self::setPriceFixed(),
                    )),
                    'label' => __('Qty based on sets', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'product'                     => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on product', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'variation'                   => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on variation', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'cart_position'               => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on cart position', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'meta_data'                   => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on product meta data', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
            ),
            'tier' => array(
                'all'                         => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on all matched products', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'product_selected_categories' => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on selected categories in all cart',
                        'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'selected_products'           => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on selected products in all cart',
                        'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'sets'                        => array(
                    'items' => self::formatOutput(array(
                        self::setDiscountAmount(),
                        self::discountPercentage(),
                        self::setPriceFixed(),
                    )),
                    'label' => __('Qty based on sets', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'product'                     => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on product', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'variation'                   => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on variation', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
                'cart_position'               => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on cart position', 'advanced-dynamic-pricing-for-woocommerce'),
                ),
            ),
        );
    }

    protected static function getAllAvailablePersistenceTypes()
    {
        return array(
            'bulk' => array(
                'all'                         => array(
                    'items' => self::formatOutput(array(
                        self::discountAmount(),
                        self::discountPercentage(),
                        self::priceFixed(),
                    )),
                    'label' => __('Qty based on all matched products', 'advanced-dynamic-pricing-for-woocommerce'),
                ),

            ),
        );
    }


    private static function discountAmount()
    {
        return array(
            'key'   => 'discount__amount',
            'label' => __('Fixed discount for item', 'advanced-dynamic-pricing-for-woocommerce'),
        );
    }

    private static function setDiscountAmount()
    {
        return array(
            'key'   => 'set_discount__amount',
            'label' => __('Fixed discount for set', 'advanced-dynamic-pricing-for-woocommerce'),
        );
    }

    private static function discountPercentage()
    {
        return array(
            'key'   => 'discount__percentage',
            'label' => __('Percentage discount', 'advanced-dynamic-pricing-for-woocommerce'),
        );
    }

    private static function priceFixed()
    {
        return array(
            'key'   => 'price__fixed',
            'label' => __('Fixed price for item', 'advanced-dynamic-pricing-for-woocommerce'),
        );
    }

    private static function setPriceFixed()
    {
        return array(
            'key'   => 'set_price__fixed',
            'label' => __('Fixed price for set', 'advanced-dynamic-pricing-for-woocommerce'),
        );
    }

    private static function formatOutput($types)
    {
        return array_combine(array_column($types, 'key'), array_column($types, 'label'));
    }
}
