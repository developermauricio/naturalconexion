<?php

namespace ADP\BaseVersion\Includes;

use ADP\BaseVersion\Includes\AdminExtensions\AdminNotice;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage;
use ADP\BaseVersion\Includes\Compatibility\AeliaSwitcherCmp;
use ADP\BaseVersion\Includes\Compatibility\AlgWcCurrencySwitcherCmp;
use ADP\BaseVersion\Includes\Compatibility\PriceBasedOnCountryCmp;
use ADP\BaseVersion\Includes\Compatibility\VillaThemeMultiCurrencyCmp;
use ADP\BaseVersion\Includes\Compatibility\WoocsCmp;
use ADP\BaseVersion\Includes\Compatibility\YayCurrencyCmp;
use ADP\BaseVersion\Includes\Context\Currency;
use ADP\BaseVersion\Includes\Context\CurrencyController;
use ADP\BaseVersion\Includes\Context\Geolocation;
use ADP\BaseVersion\Includes\Context\Language;
use ADP\BaseVersion\Includes\Context\PriceSettings;
use ADP\BaseVersion\Includes\SpecialStrategies\GeoLocationStrategy;
use ADP\Factory;
use ADP\Settings\OptionsManager;
use Automattic\WooCommerce\Utilities\OrderUtil;
use Exception;
use WC_Tax;
use WP_Theme;
use WP_User;

defined('ABSPATH') or exit;

class Context
{
    const CUSTOMIZER = 'customizer';
    const ADMIN = 'admin';
    const AJAX = 'ajax';
    const REST_API = 'rest_api';
    const WP_CRON = 'wp_cron';
    const PHPUNIT = 'phpunit';
    const AJAX_REF_ADMIN = 'ajax_ref_admin';
    const PROCESSING_UPDATE = 'processing_upgrade';

    /**
     * Props which can be accessed anyway
     *
     * @var array<int,callable>
     */
    protected $firstBornPropsCallbacks = array();

    const PRODUCT_LOOP = 'product_loop';
    const SHOP_LOOP = 'shop_loop';
    const WC_PRODUCT_PAGE = 'wc_product_page';
    const WC_CATEGORY_PAGE = 'wc_category_page';
    const WC_CART_PAGE = 'wc_cart_page';
    const WC_CHECKOUT_PAGE = 'wc_checkout_page';
    const WC_SHOP_PAGE = 'wc_shop_page';

    const ADP_PLUGIN_PAGE = 'adp_admin_plugin_page';

    /**
     * Props which can be accessed only after parsing the main WordPress query, so
     * in __construct we should wait until it happens (if needed ofc)
     *
     * @var array<int,callable>
     */
    protected $queryPropsCallbacks = array();
    protected $adminQueryPropsCallbacks = array();

    const MODE_DEBUG = 'debug';
    const MODE_PRODUCTION = 'prod';

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var OptionsManager
     */
    protected $settings;

    /**
     * @var OptionsManager
     */
    protected $compatibilitySettings;

    /**
     * @var array
     */
    protected $props = array();

    /**
     * @var array
     */
    protected $changedProps = array();

    /**
     * @var WP_User
     */
    protected $currentUser;

    /**
     * @var bool
     */
    protected $userLoggedIn;

    protected $availableTaxClassSlugs = array();

    /**
     * @var bool
     */
    protected $wcLoaded;

    /**
     * @var PriceSettings
     */
    public $priceSettings;

    /**
     * @var CurrencyController
     */
    public $currencyController;

    /**
     * @var WP_Theme
     */
    public $currentTheme;

    /**
     * @var Geolocation
     */
    private $geoLocation;

    /**
     * @var AdminNotice
     */
    public $adminNotice;

    /**
     * @var Language
     */
    public $language;

    /**
     * @param OptionsManager|null $settings
     *
     * @throws Exception
     */
    public function __construct($settings = null)
    {
        if (isset($settings) && $settings instanceof OptionsManager) {
            $this->settings = $settings;
        } else {
            $optionsManager = Factory::callStaticMethod("Settings_OptionsInstaller", "install");
            /** @var $optionsManager OptionsManager */
            $this->settings = $optionsManager;
        }

        $this->compatibilitySettings = Factory::callStaticMethod("Settings_CompatibilityOptionsInstaller", "install");

        $this->firstBornPropsCallbacks = array(
            self::ADMIN             => 'is_admin',
            self::CUSTOMIZER        => 'is_customize_preview',
            self::AJAX              => 'wp_doing_ajax',
            self::REST_API          => array($this, 'isRequestToRestApi'),
            self::WP_CRON           => 'wp_doing_cron',
            self::PHPUNIT           => array($this, 'isDoingPhpUnit'),
            self::AJAX_REF_ADMIN    => array($this, 'isDoingAjaxRefAdmin'),
            self::PROCESSING_UPDATE => array($this, 'isProcessingUpdatePlugin'),
        );

        $this->queryPropsCallbacks = array(
            self::PRODUCT_LOOP     => array($this, 'isWoocommerceProductLoop'),
            self::SHOP_LOOP        => array($this, 'isWoocommerceShopLoop'),
            self::WC_PRODUCT_PAGE  => 'is_product',
            self::WC_CATEGORY_PAGE => 'is_product_category',
            self::WC_CART_PAGE     => 'is_cart',
            self::WC_CHECKOUT_PAGE => 'is_checkout',
            self::WC_SHOP_PAGE     => 'is_shop',
        );

        $this->adminQueryPropsCallbacks = array(
            self::ADP_PLUGIN_PAGE => array($this, 'isAdpAdminPage'),
        );

        foreach ($this->firstBornPropsCallbacks as $prop => $callback) {
            $this->props[$prop] = $callback();
        }

        if (did_action('wp')) {
            $this->fetchQueryProps();
        } else {
            add_action('wp', array($this, 'fetchQueryProps'), 10, 0);
        }

        if (did_action('admin_init')) {
            $this->fetchAdminQueryProps();
        } else {
            add_action('admin_init', array($this, 'fetchAdminQueryProps'), 10, 0);
        }

        $this->userLoggedIn           = is_user_logged_in();
        $this->currentUser            = wp_get_current_user();
        $this->availableTaxClassSlugs = array('standard');
        $this->wcLoaded               = class_exists("WooCommerce");

        if ($this->wcLoaded && class_exists("WC_Tax")) {
            $this->availableTaxClassSlugs = array_merge($this->availableTaxClassSlugs, WC_Tax::get_tax_class_slugs());
        }

        $this->setUpPricesSettings();

        /** --- Currency ---  */
        $currencyCode = get_option('woocommerce_currency');
        $symbols      = CurrencyController::getDefaultCurrencySymbols();

        $symbol                   = isset($symbols[$currencyCode]) ? $symbols[$currencyCode] : '';
        $this->currencyController = new CurrencyController($this, new Currency($currencyCode, $symbol, 1));
        $this->currencyController->setCurrencySymbols($symbols);

        $woocsCmp = new WoocsCmp();
        if ($woocsCmp->isActive()) {
            $woocsCmp->modifyContext($this);
            $woocsCmp->prepareHooks();
        }

        $villaCmp = new VillaThemeMultiCurrencyCmp();
        if ($villaCmp->isActive()) {
            $villaCmp->modifyContext($this);
            $villaCmp->prepareHooks();
        }

        $aeliaCmp = new AeliaSwitcherCmp();
        if ($aeliaCmp->isActive()) {
            $aeliaCmp->modifyContext($this);
            $aeliaCmp->prepareHooks();
        }

        $algCmp = new AlgWcCurrencySwitcherCmp();
        if ($algCmp->isActive()) {
            $algCmp->modifyContext($this);
        }

        $yayCmp = new YayCurrencyCmp();
        if ($yayCmp->isActive()) {
            $yayCmp->modifyContext($this);
            $yayCmp->prepareHooks();
        }

        $priceBasedOnCountryCmp = new PriceBasedOnCountryCmp();
        if ($priceBasedOnCountryCmp->isActive()) {
            $priceBasedOnCountryCmp->modifyContext($this);
        }
        /** --- End Currency ---  */

        $this->currentTheme = $this->loadCurrentTheme();

        $this->geoLocation = null;

        $this->adminNotice = new AdminNotice($this);

        $this->language = Language::buildAsDefault();
    }

    public function fetchQueryProps()
    {
        foreach ($this->queryPropsCallbacks as $prop => $callback) {
            $this->props[$prop] = $callback();
        }
    }

    public function fetchAdminQueryProps()
    {
        foreach ($this->adminQueryPropsCallbacks as $prop => $callback) {
            $this->props[$prop] = $callback();
        }
    }

    protected static function isWoocommerceProductLoop()
    {
        global $wp_query;

        return ($wp_query->current_post + 1 < $wp_query->post_count) || 'products' !== woocommerce_get_loop_display_mode();
    }

    protected static function isWoocommerceShopLoop()
    {
        return ! empty($GLOBALS['woocommerce_loop']['name']);
    }

    protected static function isAdpAdminPage()
    {
        global $plugin_page;

        return $plugin_page === AdminPage::SLUG;
    }

    protected static function isProcessingUpdatePlugin()
    {
        return wp_doing_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] === 'update-plugin';
    }

    protected static function isRequestToRestApi()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $rest_prefix = trailingslashit(rest_get_url_prefix());
        $request_uri = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']));
        $wordpress   = (false !== strpos($request_uri, $rest_prefix));

        return $wordpress;
    }

    protected static function isDoingPhpUnit()
    {
        return defined("PHPUNIT_COMPOSER_INSTALL");
    }

    /**
     * @return bool
     */
    protected static function isDoingAjaxRefAdmin()
    {
        if ( ! isset($_SERVER["HTTP_REFERER"])) {
            return false;
        }

        $referer = parse_url($_SERVER["HTTP_REFERER"]);
        $admin   = parse_url(admin_url("admin.php"));

        return isset($referer['path'], $admin['path']) && ($referer['path'] === $admin['path']);
    }

    /**
     * @param $newProps array
     *
     * @return self
     */
    public function setProps($newProps)
    {
        foreach ($newProps as $key => $value) {
            $this->changedProps[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption($key, $default = false)
    {
        return $this->settings->getOption($key);
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getCompatibilityOption($key, $default = false)
    {
        return $this->compatibilitySettings->getOption($key);
    }

    public function getSettings(): OptionsManager
    {
        return $this->settings;
    }

    /**
     * @return OptionsManager|mixed
     */
    public function getCompatibilitySettings()
    {
        return $this->compatibilitySettings;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getProp($key, $default = false)
    {
        $value = $default;

        if (isset($this->props[$key])) {
            $value = $this->props[$key];
        }

        if (isset($this->changedProps[$key])) {
            $value = $this->changedProps[$key];
        }

        return $value;
    }

    public function is($prop)
    {
        return $this->getProp($prop, null);
    }

    public function isCatalog()
    {
        return ! $this->getProp(self::WC_PRODUCT_PAGE) || $this->getProp(self::SHOP_LOOP);
    }

    public function isProductPage()
    {
        return $this->getProp(self::WC_PRODUCT_PAGE);
    }

    public function isPluginAdminPage()
    {
        return $this->getProp(self::ADMIN) && isset($_GET['page']) && $_GET['page'] === AdminPage::SLUG;
    }

    public function isWoocommerceCouponsEnabled()
    {
        return wc_coupons_enabled();
    }

    /**
     * @return WP_User
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * @param WP_User $user
     */
    public function setCurrentUser($user)
    {
        if ($user instanceof WP_User) {
            $this->currentUser = $user;
        }
    }

    public function getIsTaxEnabled()
    {
        return wc_tax_enabled();
    }

    public function getIsPricesIncludeTax()
    {
        return wc_prices_include_tax();
    }

    public function getTaxDisplayShopMode()
    {
        return get_option('woocommerce_tax_display_shop');
    }

    public function getTaxDisplayCartMode()
    {
        return get_option('woocommerce_tax_display_cart');
    }

    public function getPriceDecimals()
    {
        return $this->priceSettings->getDecimals();
    }

    public function getCurrencyCode()
    {
        return $this->currencyController->getCurrentCurrency()->getCode();
    }

    /**
     * @return array<int,string>
     */
    public function getAvailableTaxClassSlugs()
    {
        return $this->availableTaxClassSlugs;
    }

    public function setMode($mode)
    {
        if (self::MODE_PRODUCTION === $mode || self::MODE_DEBUG === $mode) {
            $this->mode = $mode;
        }
    }

    /**
     * @param string $mode
     *
     * @return bool
     */
    public function isMode($mode)
    {
        return $this->mode === $mode;
    }

    /**
     * @return bool
     */
    public function isProductionMode()
    {
        return $this->mode === self::MODE_PRODUCTION;
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->mode === self::MODE_DEBUG;
    }

    /**
     * TODO implement
     *
     * @param Exception $exception
     */
    public function handleError($exception)
    {
        return;
    }

    /**
     * @return bool
     */
    public function isUserLoggedIn()
    {
        return $this->userLoggedIn;
    }

    public function isUsingGlobalPriceSettings()
    {
        return apply_filters('adp_feature_flag_use_global_price_settings', true);
    }

    public function isAllowExactApplicationOfReplacementCoupon()
    {
        return apply_filters('adp_feature_flag_allow_exact_application_of_replacement_coupon', true);
    }

    public function isHideRulesWithoutDiscountInOrderEditPage()
    {
        return apply_filters('adp_feature_flag_hide_rules_without_discount_in_order_edit_page', false);
    }

    public function isUnpackVariationsWithAnyAttributes()
    {
        return apply_filters('adp_unpack_variations_with_any_attributes', false);
    }

    public function isReplaceProductVariationDataStore()
    {
        return apply_filters('adp_replace_variation_data_store', true);
    }

    public function isExclusivityRequiresHistoryChange()
    {
        $default = ! $this->getOption("exclusivity_locks_all_matched_products", false);

        return apply_filters('adp_exclusivity_requires_history_change', $default);
    }

    public function isShowBulkTablePricesIncludingCoupons()
    {
        return apply_filters('adp_show_bulk_table_prices_including_coupons', false);
    }

    public function isToCompensateTrdPartAdjustmentForFixedPrice()
    {
        return apply_filters('adp_is_to_compensate_trd_party_adj_for_fixed_price', true);
    }

    public function isCheckParentsWhenFindingProductOnlyRule()
    {
        return apply_filters('adp_is_check_parents_when_finding_product_only_rule', true);
    }

    public function isRegisterUpdateCheckoutScript()
    {
        return apply_filters('adp_is_register_update_checkout_script', true);
    }

    public function isRuleSuppressed()
    {
        return apply_filters('adp_rules_suppression', false);
    }

    public function isUseMergedCoupons()
    {
        return apply_filters('adp_use_merged_coupons', true);
    }

    public function isUseSelectedShippingMethodsEverywhere()
    {
        return apply_filters('adp_use_shipping_methods_everywhere', false);
    }

    public function isUseSelectedPaymentMethodEverywhere()
    {
        return apply_filters('adp_use_payment_method_everywhere', false);
    }

    public function isTranslateRules()
    {
        return apply_filters('adp_translate_rules', true);
    }

    public function isShowPriceRangeInBulkTableForVariableProducts()
    {
        return apply_filters('adp_show_price_range_in_bulk_table_for_variable_products', false);
    }

    protected function setUpPricesSettings()
    {
        $settings = new PriceSettings();

        if ($this->isUsingGlobalPriceSettings() && $this->wcLoaded) {
            $settings->setTaxEnabled(wc_tax_enabled());
            $settings->setIncludeTax(wc_prices_include_tax());
            $settings->setDecimals(wc_get_price_decimals());
            $settings->setDecimalSeparator(wc_get_price_decimal_separator());
            $settings->setThousandSeparator(wc_get_price_thousand_separator());
            $settings->setPriceFormat(get_woocommerce_price_format());
        } else {
            $settings->setTaxEnabled(get_option('woocommerce_calc_taxes') === 'yes');
            $settings->setIncludeTax(get_option('woocommerce_prices_include_tax') === 'yes');
            $settings->setDecimals(get_option('woocommerce_price_num_decimals', 2));
            $settings->setDecimalSeparator(stripslashes(get_option('woocommerce_price_decimal_sep')));
            $settings->setThousandSeparator(stripslashes(get_option('woocommerce_price_thousand_sep')));

            switch (get_option('woocommerce_currency_pos')) {
                case 'left':
                    $priceFormat = $settings::FORMAT_LEFT;
                    break;
                case 'right':
                    $priceFormat = $settings::FORMAT_RIGHT;
                    break;
                case 'left_space':
                    $priceFormat = $settings::FORMAT_LEFT_SPACE;
                    break;
                case 'right_space':
                    $priceFormat = $settings::FORMAT_RIGHT_SPACE;
                    break;
                default:
                    $priceFormat = null;
                    break;
            }

            $settings->setPriceFormat($priceFormat);
        }

        if ( ! $this->getOption('is_calculate_based_on_wc_precision')) {
            $settings->setDecimals($settings->getDecimals() + 2);
        }

        $this->priceSettings = $settings;
    }

    /**
     * @return WP_Theme
     */
    protected function loadCurrentTheme()
    {
        return wp_get_theme();
    }

    /**
     * @return WP_Theme
     */
    public function getCurrentTheme()
    {
        return $this->currentTheme;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return Geolocation
     */
    public function getGeoLocation(): Geolocation
    {
        if ($this->geoLocation !== null) {
            return $this->geoLocation;
        }

        $location = GeoLocationStrategy::geoLocateIp();
        $this->geoLocation = new Geolocation(
            $location['country'] ?? "",
            $location['state'] ?? "",
            $location['city'] ?? "",
            $location['postcode'] ?? ""
        );

        return $this->geoLocation;
    }

    /**
     * @return bool
     */
    public function isHPOSEnabled() {
        $isHPOSEnabled = false;
        if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil')) {
            $isHPOSEnabled = OrderUtil::custom_orders_table_usage_is_enabled();
        }
        return $isHPOSEnabled;
    }
}
