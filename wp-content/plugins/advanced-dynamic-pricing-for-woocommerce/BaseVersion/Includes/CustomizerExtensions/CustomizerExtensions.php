<?php

namespace ADP\BaseVersion\Includes\CustomizerExtensions;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\CustomizerExtensions\AdvertisingThemeProperties;
use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties;
use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties;
use ADP\BaseVersion\Includes\CustomizerExtensions\ProductBulkTableThemeProperties\OptionsMenu as ProductOptionsMenuAlias;
use ADP\BaseVersion\Includes\CustomizerExtensions\CategoryBulkTableThemeProperties\OptionsMenu as CategoryOptionsMenuAlias;
use ADP\BaseVersion\Includes\CustomizerExtensions\ThemeProperties;
use ADP\BaseVersion\Includes\Database\Repository\ThemeModificationsRepository;
use WP_Customize_Manager;

defined('ABSPATH') or exit;

class CustomizerExtensions
{
    protected $options = array();

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ThemeModificationsRepository
     */
    protected $themeModificationsRepository;

    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
        $this->init();
        $this->themeModificationsRepository = new ThemeModificationsRepository();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    public function withThemeModificationsRepository(ThemeModificationsRepository $themeModificationsRepository)
    {
        $this->themeModificationsRepository = $themeModificationsRepository;
    }

    public function register()
    {
        add_action('customize_register', array($this, 'add_sections'));
        add_action('customize_controls_enqueue_scripts', array($this, 'customizerControlsScripts'), 999);
        add_action('customize_preview_init', array($this, 'customizePreviewInit'));

        // style customize
        add_action('wp_head', function () {
            $this->customizeCss();
        });
    }

    protected function init()
    {
        $this->options[ProductBulkTableThemeProperties::KEY] = array(
            'key'      => ProductBulkTableThemeProperties::SHORT_KEY,
            'title'    => __('Product bulk table (Pricing)', 'advanced-dynamic-pricing-for-woocommerce'),
            'priority' => 200,
            'options'  => $this->getProductTableOptions(ProductBulkTableThemeProperties::KEY),
        );

        $this->options[CategoryBulkTableThemeProperties::KEY] = array(
            'key'      => CategoryBulkTableThemeProperties::SHORT_KEY,
            'title'    => __('Category bulk table (Pricing)', 'advanced-dynamic-pricing-for-woocommerce'),
            'priority' => 200,
            'options'  => $this->getCategoryTableOptions(CategoryBulkTableThemeProperties::KEY),
        );

        $this->options[AdvertisingThemeProperties::KEY] = array(
            'key'      => AdvertisingThemeProperties::SHORT_KEY,
            'title'    => __('Discount message (Pricing)', 'advanced-dynamic-pricing-for-woocommerce'),
            'priority' => 200,
            'options'  => $this->getDiscountMessageOptions(AdvertisingThemeProperties::KEY),
        );
    }

    protected function initFontOptions($panelId, $section)
    {
        $mapSectionAndCssSelector = array(
            "$panelId-table_header"  => '.wdp_bulk_table_content .wdp_pricing_table_caption',
            "$panelId-table_columns" => '.wdp_bulk_table_content table thead td',
            "$panelId-table_body"    => '.wdp_bulk_table_content table tbody td',
            "$panelId-table_footer"  => '.wdp_bulk_table_content .wdp_pricing_table_footer',
        );

        if (empty($mapSectionAndCssSelector[$section])) {
            return false;
        }
        $selector = $mapSectionAndCssSelector[$section];

        $font_options = array(
            "$panelId-emphasis_bold"   => array(
                'label'             => __('Bold text', 'advanced-dynamic-pricing-for-woocommerce'),
                'default'           => false,
                'sanitize_callback' => 'wc_bool_to_string',
                'control_class'     => 'ADP\BaseVersion\Includes\CustomizerExtensions\Controls\FontEmphasisBold',
                'priority'          => 10,

                'apply_type'       => 'css',
                'selector'         => $selector,
                'css_option_name'  => 'font-weight',
                'css_option_value' => 'bold',
                'layout'           => 'any',
            ),
            "$panelId-emphasis_italic" => array(
                'label'             => __('Italic text', 'advanced-dynamic-pricing-for-woocommerce'),
                'default'           => false,
                'sanitize_callback' => 'wc_bool_to_string',
                'control_class'     => 'ADP\BaseVersion\Includes\CustomizerExtensions\Controls\FontEmphasisItalic',
                'priority'          => 20,

                'apply_type'       => 'css',
                'selector'         => $selector,
                'css_option_name'  => 'font-style',
                'css_option_value' => 'italic',
                'layout'           => 'any',
            ),
            "$panelId-text_align"      => array(
                'label'         => __('Text align', 'advanced-dynamic-pricing-for-woocommerce'),
                'default'       => '',
                'control_class' => 'ADP\BaseVersion\Includes\CustomizerExtensions\Controls\TextAlign',
                'priority'      => 30,

                'apply_type'      => 'css',
                'selector'        => $selector,
                'css_option_name' => 'text-align',
                'layout'          => 'any',
            ),
            "$panelId-text_color"      => array(
                'label'             => __('Text color', 'advanced-dynamic-pricing-for-woocommerce'),
                'default'           => '#6d6d6d',
                'sanitize_callback' => 'sanitize_hex_color',
                'control_class'     => '\WP_Customize_Color_Control',
                'priority'          => 10,

                'apply_type'      => 'css',
                'selector'        => $selector,
                'css_option_name' => 'color',
                'layout'          => 'any',
            ),
        );

        // bulk_table_header BOLD by default
        if ("$panelId-bulk_table_header" == $section) {
            $font_options["$panelId-emphasis_bold"]['default'] = true;
        }

        return $font_options;
    }

    protected function getProductTableOptions($panelId)
    {
        $type = 'product';

        $product_options = array(
            ProductOptionsMenuAlias::KEY                          => array(
                'title'    => __('Options', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 10,
                'options'  => array(
                    'table_layout'              => array(
                        'label'        => __('Product table layout', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'      => ProductOptionsMenuAlias::LAYOUT_VERBOSE,
                        'control_type' => 'select',
                        'choices'      => array(
                            ProductOptionsMenuAlias::LAYOUT_VERBOSE => __('Display ranges as rows',
                                'advanced-dynamic-pricing-for-woocommerce'),
                            ProductOptionsMenuAlias::LAYOUT_SIMPLE  => __('Display ranges as headers',
                                'advanced-dynamic-pricing-for-woocommerce'),
                        ),
                        'priority'     => 5,

                        'apply_type' => 'filter',
//						'hook'       => "wdp_{$type}_bulk_table_action",
                        'layout'     => 'any',
                    ),
                    'product_bulk_table_action' => array(
                        'label'        => __('Product Bulk Table position', 'advanced-dynamic-pricing-for-woocommerce'),
                        'description'  => __('You can use shortcode [adp_product_bulk_rules_table] in product template.',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'default'      => 'woocommerce_after_add_to_cart_form',
                        'control_type' => 'select',
                        'choices'      => apply_filters('wdp_product_bulk_table_places', array(
                            'woocommerce_before_single_product_summary' => __('Above product summary',
                                'advanced-dynamic-pricing-for-woocommerce'),
                            'woocommerce_after_single_product_summary'  => __('Below product summary',
                                'advanced-dynamic-pricing-for-woocommerce'),
                            'woocommerce_before_single_product'         => __('Above product',
                                'advanced-dynamic-pricing-for-woocommerce'),
                            'woocommerce_after_single_product'          => __('Below product',
                                'advanced-dynamic-pricing-for-woocommerce'),
                            'woocommerce_before_add_to_cart_form'       => __('Above add to cart',
                                'advanced-dynamic-pricing-for-woocommerce'),
                            'woocommerce_after_add_to_cart_form'        => __('Below add to cart',
                                'advanced-dynamic-pricing-for-woocommerce'),
                            'woocommerce_product_meta_start'            => __('Above product meta',
                                'advanced-dynamic-pricing-for-woocommerce'),
                            'woocommerce_product_meta_end'              => __('Below product meta',
                                'advanced-dynamic-pricing-for-woocommerce'),
                        )),
                        'priority'     => 10,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_action",
                        'layout'     => 'any',
                    ),
                    'show_discounted_price'     => array(
                        'label'             => __('Show discounted price', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => true,
                        'priority'          => 20,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                        'hook'       => "wdp_show_discounted_price_in_{$type}_bulk_table",
                        'layout'     => ProductOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'show_discount_column'      => array(
                        'label'             => __('Show fixed discount column',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => true,
                        'priority'          => 30,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                        'hook'       => "wdp_show_product_discount_in_{$type}_bulk_table",
                        'layout'     => ProductOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'show_footer'               => array(
                        'label'             => __('Show footer', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => true,
                        'priority'          => 40,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                        'hook'       => "wdp_show_footer_in_{$type}_bulk_table",
                        'layout'     => 'any',
                    ),
                ),

            ),
            ProductBulkTableThemeProperties\StyleHeaderMenu::KEY  => array(
                'title'    => __('Style header', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 20,
                'options'  => array(
                    'use_message_as_title' => array(
                        'label'             => __('Use bulk table message as table header',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => false,
                        'priority'          => 50,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                        'hook'       => "wdp_use_message_as_{$type}_bulk_table_header",
                        'layout'     => 'any',
                    ),
                    'bulk_title'           => array(
                        'label'    => __('Header bulk title', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Bulk deal', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 50,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_header_for_bulk_title",
                        'layout'     => 'any',
                    ),
                    'tier_title'           => array(
                        'label'    => __('Header tier title', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Tier deal', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 50,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_header_for_tier_title",
                        'layout'     => 'any',
                    ),
                ),
            ),
            ProductBulkTableThemeProperties\StyleColumnsMenu::KEY => array(
                'title'    => __('Style columns', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 30,
                'options'  => array(
                    'qty_column_title'                           => array(
                        'label'    => __('Quantity column title', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Quantity', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 50,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_qty_title",
                        'layout'     => ProductOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'discount_column_title'                      => array(
                        'label'    => __('Discount column title', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Discount', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 60,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_discount_price_title",
                        'layout'     => ProductOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'discount_column_title_for_rule_fixed_price' => array(
                        'label'    => __('Discount column title, for fixed price',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Discount', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 65,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_rule_fixed_price_title",
                        'layout'     => ProductOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'discount_column_title_for_fixed_price'      => array(
                        'label'    => __('Discounted price column title, for fixed price',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Fixed price', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 70,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_fixed_price_title",
                        'layout'     => ProductOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'discounted_price_title'                     => array(
                        'label'    => __('Discounted price column title', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Discounted price', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 80,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_discounted_price_title",
                        'layout'     => ProductOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'header_background_color'                    => array(
                        'label'             => __('Background color', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => '#efefef',
                        'sanitize_callback' => 'sanitize_hex_color',
                        'control_class'     => 'WP_Customize_Color_Control',
                        'priority'          => 90,

                        'apply_type'      => 'css',
                        'selector'        => '.wdp_bulk_table_content table thead td',
                        'css_option_name' => 'background-color',
                        'layout'          => 'any',
                    ),
                ),
            ),
            ProductBulkTableThemeProperties\StyleBodyMenu::KEY    => array(
                'title'    => __('Style body', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 40,
                'options'  => array(
                    'body_background_color' => array(
                        'label'             => __('Background color', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => '#ffffff',
                        'sanitize_callback' => 'sanitize_hex_color',
                        'control_class'     => 'WP_Customize_Color_Control',
                        'priority'          => 50,

                        'apply_type'      => 'css',
                        'selector'        => '.wdp_bulk_table_content table tbody td',
                        'css_option_name' => 'background-color',
                        'layout'          => 'any',
                    ),
                ),
            ),
            ProductBulkTableThemeProperties\StyleFooterMenu::KEY  => array(
                'title'    => __('Style footer', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 50,
                'options'  => array(),
            ),
        );


        foreach ($product_options as $section => &$section_data) {
            if ($font_options = $this->initFontOptions($panelId, $section)) {
                $section_data['options'] = array_merge($font_options, $section_data['options']);
            }
        }

        return $product_options;
    }

    protected function getCategoryTableOptions($panelId)
    {
        $type = 'category';

        $categoryOptions = array(
            CategoryBulkTableThemeProperties\OptionsMenu::KEY      => array(
                'title'    => __('Options', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 10,
                'options'  => array(
                    'table_layout'               => array(
                        'label'        => __('Category table layout', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'      => CategoryOptionsMenuAlias::LAYOUT_VERBOSE,
                        'control_type' => 'select',
                        'choices'      => array(
                            CategoryOptionsMenuAlias::LAYOUT_VERBOSE => __('Display ranges as rows',
                                'advanced-dynamic-pricing-for-woocommerce'),
                            CategoryOptionsMenuAlias::LAYOUT_SIMPLE  => __('Display ranges as headers',
                                'advanced-dynamic-pricing-for-woocommerce'),
                        ),
                        'priority'     => 5,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_action",
                        'layout'     => 'any',
                    ),
                    'category_bulk_table_action' => array(
                        'label'        => __('Category Bulk Table position',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'description'  => __('You can use shortcode [adp_product_bulk_rules_table] in product template.',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'default'      => 'woocommerce_before_shop_loop',
                        'control_type' => 'select',
                        'choices'      => apply_filters('wdp_category_bulk_table_places', array(
                            'woocommerce_before_shop_loop' => __('At top of the page',
                                'advanced-dynamic-pricing-for-woocommerce'),
                            'woocommerce_after_shop_loop'  => __('At bottom of the page',
                                'advanced-dynamic-pricing-for-woocommerce'),
                        )),
                        'priority'     => 10,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_action",
                        'layout'     => 'any',
                    ),

                    'show_discount_column' => array(
                        'label'             => __('Show fixed discount column',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => true,
                        'priority'          => 30,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                        'hook'       => "wdp_show_product_discount_in_{$type}_bulk_table",
                        'layout'     => CategoryOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'show_footer'          => array(
                        'label'             => __('Show footer', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => true,
                        'priority'          => 40,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                        'hook'       => "wdp_show_footer_in_{$type}_bulk_table",
                        'layout'     => 'any',
                    ),
                ),

            ),
            CategoryBulkTableThemeProperties\StyleHeaderMenu::KEY  => array(
                'title'    => __('Style header', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 20,
                'options'  => array(
                    'use_message_as_title' => array(
                        'label'             => __('Use bulk table message as table header',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => false,
                        'priority'          => 50,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                        'hook'       => "wdp_use_message_as_{$type}_bulk_table_header",
                        'layout'     => 'any',
                    ),
                    'bulk_title'           => array(
                        'label'    => __('Header bulk title', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Bulk deal', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 50,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_header_for_bulk_title",
                        'layout'     => 'any',
                    ),
                    'tier_title'           => array(
                        'label'    => __('Header tier title', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Tier deal', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 50,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_header_for_tier_title",
                        'layout'     => 'any',
                    ),
                ),
            ),
            CategoryBulkTableThemeProperties\StyleColumnsMenu::KEY => array(
                'title'    => __('Style columns', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 30,
                'options'  => array(
                    'qty_column_title'                           => array(
                        'label'    => __('Quantity column title', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Quantity', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 50,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_qty_title",
                        'layout'     => CategoryOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'discount_column_title'                      => array(
                        'label'    => __('Discount column title', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Discount', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 60,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_discount_price_title",
                        'layout'     => CategoryOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'discount_column_title_for_rule_fixed_price' => array(
                        'label'    => __('Discount column title, for fixed price',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Discount', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 65,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_rule_fixed_price_title",
                        'layout'     => CategoryOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'discount_column_title_for_fixed_price'      => array(
                        'label'    => __('Discounted price column title, for fixed price',
                            'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __('Fixed price', 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 70,

                        'apply_type' => 'filter',
                        'hook'       => "wdp_{$type}_bulk_table_fixed_price_title",
                        'layout'     => CategoryOptionsMenuAlias::LAYOUT_VERBOSE,
                    ),
                    'header_background_color'                    => array(
                        'label'             => __('Background color', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => '#efefef',
                        'sanitize_callback' => 'sanitize_hex_color',
                        'control_class'     => 'WP_Customize_Color_Control',
                        'priority'          => 90,

                        'apply_type'      => 'css',
                        'selector'        => '.wdp_bulk_table_content table thead td',
                        'css_option_name' => 'background-color',
                        'layout'          => 'any',
                    ),
                ),
            ),
            CategoryBulkTableThemeProperties\StyleBodyMenu::KEY    => array(
                'title'    => __('Style body', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 40,
                'options'  => array(
                    'body_background_color' => array(
                        'label'             => __('Background color', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => '#ffffff',
                        'sanitize_callback' => 'sanitize_hex_color',
                        'control_class'     => 'WP_Customize_Color_Control',
                        'priority'          => 50,

                        'apply_type'      => 'css',
                        'selector'        => '.wdp_bulk_table_content table tbody td',
                        'css_option_name' => 'background-color',
                        'layout'          => 'any',
                    ),
                ),
            ),
            CategoryBulkTableThemeProperties\StyleFooterMenu::KEY  => array(
                'title'    => __('Style footer', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 50,
                'options'  => array(),
            ),
        );

        foreach ($categoryOptions as $section => &$sectionData) {
            if ($fontOptions = $this->initFontOptions($panelId, $section)) {
                $sectionData['options'] = array_merge($fontOptions, $sectionData['options']);
            }
        }

        return $categoryOptions;
    }

    protected function getDiscountMessageOptions($panelId)
    {
        return array(
            AdvertisingThemeProperties\GlobalMenu::KEY   => array(
                'title'    => __('Global options', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 5,
                'options'  => array(
                    'amount_saved_label' => array(
                        'label'    => __('Amount saved label', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'  => __("Amount Saved", 'advanced-dynamic-pricing-for-woocommerce'),
                        'priority' => 5,

                        'apply_type' => 'filter',
                    ),
                )
            ),
            AdvertisingThemeProperties\CartMenu::KEY     => array(
                'title'    => __('Cart', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 10,
                'options'  => array(
                    'enable'   => array(
                        'label'             => __('Enable amount saved', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => false,
                        'priority'          => 5,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                    ),
                    'position' => array(
                        'label'        => __('Position of amount saved', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'      => "woocommerce_cart_totals_before_order_total",
                        'control_type' => 'select',
                        'choices'      => apply_filters("wdp_" . "cart" . "_discount_message_places",
                            array(
                                'woocommerce_cart_totals_before_order_total' => __('Before order total',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'woocommerce_cart_totals_after_order_total'  => __('After order total',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                            )),
                        'priority'     => 10,

                        'apply_type' => 'filter',
                    ),
                )
            ),
            AdvertisingThemeProperties\MiniCartMenu::KEY => array(
                'title'    => __('Mini Cart', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 15,
                'options'  => array(
                    'enable'   => array(
                        'label'             => __('Enable amount saved', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => false,
                        'priority'          => 5,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                    ),
                    'position' => array(
                        'label'        => __('Position of amount saved', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'      => "woocommerce_mini_cart_contents",
                        'control_type' => 'select',
                        'choices'      => apply_filters("wdp_" . "mini-cart" . "_discount_message_places",
                            array(
                                'woocommerce_before_mini_cart_contents' => __('Before mini cart contents',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'woocommerce_mini_cart_contents'        => __('After mini cart contents',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                            )),
                        'priority'     => 10,

                        'apply_type' => 'filter',
                    ),
                )
            ),
            AdvertisingThemeProperties\CheckoutMenu::KEY => array(
                'title'    => __('Checkout', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 20,
                'options'  => array(
                    'enable'   => array(
                        'label'             => __('Enable amount saved', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => false,
                        'priority'          => 5,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                    ),
                    'position' => array(
                        'label'        => __('Position of amount saved', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'      => "woocommerce_review_order_before_order_total",
                        'control_type' => 'select',
                        'choices'      => apply_filters("wdp_" . "checkout" . "_discount_message_places",
                            array(
                                'woocommerce_review_order_before_cart_contents' => __('Before cart contents',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'woocommerce_review_order_after_cart_contents'  => __('After cart contents',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'woocommerce_review_order_before_order_total'    => __('Before order total',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                                'woocommerce_review_order_after_order_total'    => __('After order total',
                                    'advanced-dynamic-pricing-for-woocommerce'),
                            )),
                        'priority'     => 10,

                        'apply_type' => 'filter',
                    ),
                )
            ),
            AdvertisingThemeProperties\EditOrderMenu::KEY => array(
                'title'    => __('Edit backend order', 'advanced-dynamic-pricing-for-woocommerce'),
                'priority' => 45,
                'options'  => array(
                    'enable'   => array(
                        'label'             => __('Enable amount saved', 'advanced-dynamic-pricing-for-woocommerce'),
                        'default'           => false,
                        'priority'          => 5,
                        'control_type'      => 'checkbox',
                        'sanitize_callback' => 'wc_string_to_bool',

                        'apply_type' => 'filter',
                    ),
                    'position' => array(
                        'label'        => '',
                        'default'      => "woocommerce_admin_order_totals_after_tax",
                        'control_type' => 'hidden',
                        'priority'     => 10,

                        'apply_type' => 'filter',
                    ),
                )
            ),
        );
    }

    public function customizeCss()
    {
        $css         = array();
        $attrOptions = $this->themeModificationsRepository->getModifications();
        $context     = $this->context;
        $important   = ! $context->is($context::CUSTOMIZER) ? '! important' : "";

        $isProduct      = $context->is($context::WC_PRODUCT_PAGE);
        $productLoop    = $context->is($context::PRODUCT_LOOP);
        $isCategoryPage = $context->is($context::WC_CATEGORY_PAGE);

        $panelId = $isProduct || $productLoop ? 'wdp_product_bulk_table' : ($isCategoryPage ? 'wdp_category_bulk_table' : false);
        if (empty($panelId) || empty($this->options[$panelId])) {
            return;
        }
        $panelData = $this->options[$panelId];

        if (empty($panelData['options']) && ! is_array($panelData['options'])) {
            return;
        }
        foreach ($panelData['options'] as $sectionId => $sectionSettings) {
            foreach ($sectionSettings['options'] as $optionId => $optionData) {
                if (empty($optionData['apply_type'])) {
                    continue;
                }
                if ('css' == $optionData['apply_type'] && $optionData['selector']) {
                    $default = $optionData['default'];
                    if ( ! isset($attrOptions[$panelId][$sectionId][$optionId])) {
                        $optionValue = $default;
                    } else {
                        $optionValue = $attrOptions[$panelId][$sectionId][$optionId];
                    }
                    if ( ! empty($optionData['css_option_value'])) {
                        if ($optionValue) {
                            $css[] = sprintf("%s { %s: %s ! important}", $optionData['selector'],
                                $optionData['css_option_name'], $optionData['css_option_value']);
                        }
                    } else {
                        if ($optionValue) {
                            $css[] = sprintf("%s { %s: %s %s}", $optionData['selector'],
                                $optionData['css_option_name'], $optionValue, $important);
                        }
                    }
                }
            }
        }
        ?>
        <style type="text/css">
            <?php echo join(' ', $css); ?>
        </style>
        <?php

    }

    /**
     * @return ThemeProperties|null
     */
    public function getThemeOptions()
    {
        if ( ! did_action('wp_loaded')) {
            _doing_it_wrong(__FUNCTION__,
                sprintf(__('%1$s should not be called before the %2$s action.', 'woocommerce'),
                    __NAMESPACE__ . '/Customizer::getThemeOptions', 'wp_loaded'), '2.2.2');

            return null;
        }

        $result      = array();
        $attrOptions = $this->themeModificationsRepository->getModifications();

        foreach ($this->options as $panelId => $panelData) {
            if (empty($panelData['options']) || empty($panelData['key'])) {
                continue;
            }

//            $key = $panelData['key'];
            $key = $panelId;

            $sectionOptions = array();
            foreach ($panelData['options'] as $sectionId => $sectionSettings) {
                if ( ! isset($sectionSettings['options'])) {
                    continue;
                }

//                $sectionKey = str_replace($panelId . '-', "", $sectionId);
                $sectionKey = $sectionId;

                $options = array();
                foreach ($sectionSettings['options'] as $optionId => $optionData) {
                    if (empty($optionData['apply_type'])) {
                        continue;
                    }

                    // font options
                    $optionKey = str_replace($panelId . '-', "", $optionId);

                    $default = $optionData['default'];
                    if ( ! isset($attrOptions[$panelId][$sectionId][$optionId])) {
                        $attrOption = $default;
                    } else {
                        $attrOption = $attrOptions[$panelId][$sectionId][$optionId];
                    }

                    /**
                     * Do not apply saved value which not in choices
                     * e.g. delete add_action
                     */
                    $choices = $optionData['choices'] ?? array();
                    if ($choices && empty($choices[$attrOption])) {
                        $attrOption = $default;
                    }

                    $options[$optionKey] = $attrOption;
                }

                $sectionOptions[$sectionKey] = $options;
            }

            $result[$key] = $sectionOptions;
        }

        return $this->convertToThemeProperties($result);
    }

    /**
     * @param array $props
     *
     * @return ThemeProperties
     */
    protected function convertToThemeProperties(array $props)
    {
        $themeProperties = new ThemeProperties();

        $obj                            = $themeProperties->productBulkTable->options;
        $data                           = $props[$themeProperties->productBulkTable::KEY][$obj::KEY];
        $obj->tableLayout               = $data['table_layout'];
        $obj->tablePositionAction       = $data['product_bulk_table_action'];
        $obj->isShowDiscountedPrice     = $data['show_discounted_price'];
        $obj->isShowFixedDiscountColumn = $data['show_discount_column'];
        $obj->isShowFooter              = $data['show_footer'];

        $obj                      = $themeProperties->productBulkTable->styleHeader;
        $data                     = $props[$themeProperties->productBulkTable::KEY][$obj::KEY];
        $obj->isBold              = $data['emphasis_bold'];
        $obj->isItalic            = $data['emphasis_italic'];
        $obj->textAlign           = $data['text_align'];
        $obj->textColor           = $data['text_color'];
        $obj->isUseMessageAsTitle = $data['use_message_as_title'];
        $obj->headerBulkTitle     = $data['bulk_title'];
        $obj->headerTierTitle     = $data['tier_title'];

        $obj                                              = $themeProperties->productBulkTable->styleColumns;
        $data                                             = $props[$themeProperties->productBulkTable::KEY][$obj::KEY];
        $obj->isBold                                      = $data['emphasis_bold'];
        $obj->isItalic                                    = $data['emphasis_italic'];
        $obj->textAlign                                   = $data['text_align'];
        $obj->textColor                                   = $data['text_color'];
        $obj->quantityColumnTitle                         = $data['qty_column_title'];
        $obj->discountColumnTitle                         = $data['discount_column_title'];
        $obj->discountColumnTitleForFixedPriceRule        = $data['discount_column_title_for_rule_fixed_price'];
        $obj->discountedPriceColumnTitleForFixedPriceRule = $data['discount_column_title_for_fixed_price'];
        $obj->discountedPriceColumnTitle                  = $data['discounted_price_title'];
        $obj->headerBackgroundColor                       = $data['header_background_color'];

        $obj                  = $themeProperties->productBulkTable->styleBody;
        $data                 = $props[$themeProperties->productBulkTable::KEY][$obj::KEY];
        $obj->isBold          = $data['emphasis_bold'];
        $obj->isItalic        = $data['emphasis_italic'];
        $obj->textAlign       = $data['text_align'];
        $obj->textColor       = $data['text_color'];
        $obj->backgroundColor = $data['body_background_color'];

        $obj            = $themeProperties->productBulkTable->styleFooter;
        $data           = $props[$themeProperties->productBulkTable::KEY][$obj::KEY];
        $obj->isBold    = $data['emphasis_bold'];
        $obj->isItalic  = $data['emphasis_italic'];
        $obj->textAlign = $data['text_align'];
        $obj->textColor = $data['text_color'];

        $obj                            = $themeProperties->categoryBulkTable->options;
        $data                           = $props[$themeProperties->categoryBulkTable::KEY][$obj::KEY];
        $obj->tableLayout               = $data['table_layout'];
        $obj->tablePositionAction       = $data['category_bulk_table_action'];
        $obj->isShowFixedDiscountColumn = $data['show_discount_column'];
        $obj->isShowFooter              = $data['show_footer'];

        $obj                      = $themeProperties->categoryBulkTable->styleHeader;
        $data                     = $props[$themeProperties->categoryBulkTable::KEY][$obj::KEY];
        $obj->isBold              = $data['emphasis_bold'];
        $obj->isItalic            = $data['emphasis_italic'];
        $obj->textAlign           = $data['text_align'];
        $obj->textColor           = $data['text_color'];
        $obj->isUseMessageAsTitle = $data['use_message_as_title'];
        $obj->headerBulkTitle     = $data['bulk_title'];
        $obj->headerTierTitle     = $data['tier_title'];

        $obj                                              = $themeProperties->categoryBulkTable->styleColumns;
        $data                                             = $props[$themeProperties->categoryBulkTable::KEY][$obj::KEY];
        $obj->isBold                                      = $data['emphasis_bold'];
        $obj->isItalic                                    = $data['emphasis_italic'];
        $obj->textAlign                                   = $data['text_align'];
        $obj->textColor                                   = $data['text_color'];
        $obj->quantityColumnTitle                         = $data['qty_column_title'];
        $obj->discountColumnTitle                         = $data['discount_column_title'];
        $obj->discountColumnTitleForFixedPriceRule        = $data['discount_column_title_for_rule_fixed_price'];
        $obj->discountedPriceColumnTitleForFixedPriceRule = $data['discount_column_title_for_fixed_price'];
        $obj->headerBackgroundColor                       = $data['header_background_color'];

        $obj                  = $themeProperties->categoryBulkTable->styleBody;
        $data                 = $props[$themeProperties->categoryBulkTable::KEY][$obj::KEY];
        $obj->isBold          = $data['emphasis_bold'];
        $obj->isItalic        = $data['emphasis_italic'];
        $obj->textAlign       = $data['text_align'];
        $obj->textColor       = $data['text_color'];
        $obj->backgroundColor = $data['body_background_color'];

        $obj            = $themeProperties->categoryBulkTable->styleFooter;
        $data           = $props[$themeProperties->categoryBulkTable::KEY][$obj::KEY];
        $obj->isBold    = $data['emphasis_bold'];
        $obj->isItalic  = $data['emphasis_italic'];
        $obj->textAlign = $data['text_align'];
        $obj->textColor = $data['text_color'];

        $obj                   = $themeProperties->advertisingThemeProperties->global;
        $data                  = $props[$themeProperties->advertisingThemeProperties::KEY][$obj::KEY];
        $obj->amountSavedLabel = $data['amount_saved_label'];

        $obj                            = $themeProperties->advertisingThemeProperties->cart;
        $data                           = $props[$themeProperties->advertisingThemeProperties::KEY][$obj::KEY];
        $obj->isEnableAmountSaved       = $data['enable'];
        $obj->positionAmountSavedAction = $data['position'];

        $obj                            = $themeProperties->advertisingThemeProperties->miniCart;
        $data                           = $props[$themeProperties->advertisingThemeProperties::KEY][$obj::KEY];
        $obj->isEnableAmountSaved       = $data['enable'];
        $obj->positionAmountSavedAction = $data['position'];

        $obj                            = $themeProperties->advertisingThemeProperties->checkout;
        $data                           = $props[$themeProperties->advertisingThemeProperties::KEY][$obj::KEY];
        $obj->isEnableAmountSaved       = $data['enable'];
        $obj->positionAmountSavedAction = $data['position'];

        $obj                            = $themeProperties->advertisingThemeProperties->editOrder;
        $data                           = $props[$themeProperties->advertisingThemeProperties::KEY][$obj::KEY];
        $obj->isEnableAmountSaved       = $data['enable'];
        $obj->positionAmountSavedAction = $data['position'];

        return $themeProperties;
    }

    /**
     * @param WP_Customize_Manager $wpCustomize Theme Customizer object.
     */
    public function add_sections(WP_Customize_Manager $wpCustomize)
    {
        foreach ($this->options as $panel_id => $panel_data) {
            $panel_title   = ! empty($panel_data['title']) ? $panel_data['title'] : null;
            $panel_options = ! empty($panel_data['options']) ? $panel_data['options'] : null;

            if ( ! $panel_title || ! $panel_options) {
                continue;
            }

            $wpCustomize->add_panel($panel_id, array(
                'title'    => $panel_title,
                'priority' => ! empty($panel_data['priority']) ? $panel_data['priority'] : 200,
            ));

            foreach ($panel_options as $section_id => $section_settings) {
                $this->add_section($wpCustomize, $section_id, $section_settings, $panel_id);
            }
        }

    }


    /**
     * @param WP_Customize_Manager $wp_customize Theme Customizer object.
     * @param string $section_id Parent menu id
     * @param array $sectionSettings (See above)
     * @param string $panelId
     */
    protected function add_section(
        WP_Customize_Manager $wp_customize,
        string $section_id,
        array $sectionSettings,
        string $panelId
    ) {
        if ( ! empty($sectionSettings['options'])) {
            $wp_customize->add_section($section_id, array(
                'title'    => $sectionSettings['title'],
                'priority' => $sectionSettings['priority'] ?? 20,
                'panel'    => $panelId,
            ));

            uasort($sectionSettings['options'], function ($item1, $item2) {
                if ($item1['priority'] == $item2['priority']) {
                    return 0;
                }

                return $item1['priority'] < $item2['priority'] ? -1 : 1;
            });

            foreach ($sectionSettings['options'] as $option_id => $data) {
                $setting = sprintf(
                    '%s[%s][%s][%s]',
                    $this->themeModificationsRepository::OPTION_NAME,
                    $panelId,
                    $section_id,
                    $option_id
                );
                $this->add_option($wp_customize, $setting, $section_id, $data);
            }
        }
    }

    /**
     * @param WP_Customize_Manager $wpCustomize Theme Customizer object.
     * @param string $setting Option id
     * @param string $sectionId Parent menu id
     * @param array $data Option data
     */
    protected function add_option(WP_Customize_Manager $wpCustomize, $setting, $sectionId, $data)
    {
        $priority    = ! empty($data['priority']) ? $data['priority'] : 20;
        $description = ! empty($data['description']) ? $data['description'] : "";

        $transport = 'refresh';
        if ($data['apply_type'] == 'css') {
            $transport = 'postMessage';
        }

        $wpCustomize->add_setting($setting, array(
            'default'    => $data['default'],
            'capability' => 'edit_theme_options',
            'transport'  => $transport,
            'priority'   => $priority,
        ));


        if ( ! empty($data['control_class']) && class_exists($data['control_class'])) {
            $class   = $data['control_class'];
            $control = new $class($wpCustomize, $setting, array(
                'label'       => $data['label'],
                'description' => $description,
                'section'     => $sectionId,
                'settings'    => $setting,
                'priority'    => $priority,
            ));
            $wpCustomize->add_control($control);
        } else {
            $wpCustomize->add_control($setting, array(
                'label'       => $data['label'],
                'description' => $description,
                'section'     => $sectionId,
                'settings'    => $setting,
                'type'        => $data['control_type'] ?? 'text',
                'choices'     => $data['choices'] ?? array(),
            ));
        }
    }

    public function customizerControlsScripts()
    {
        $baseVersionUrl = WC_ADP_PLUGIN_URL . "/BaseVersion/";
        wp_enqueue_style('wc-plc-customizer-control-css', $baseVersionUrl . 'assets/css/customize-controls.css',
            array(), WC_ADP_VERSION);
        wp_enqueue_script(
            'wc-plc-customizer-control-js',
            $baseVersionUrl . 'assets/js/customize-controls.js',
            ['jquery'],
            WC_ADP_VERSION
        );
    }

    public function customizePreviewInit()
    {
        $baseVersionUrl = WC_ADP_PLUGIN_URL . "/BaseVersion/";
        wp_enqueue_script('wc-plc-customizer-preview-js', $baseVersionUrl . 'assets/js/wdp-customize-preview.js',
            array(), WC_ADP_VERSION, true);

        $cssControls = array();
        foreach ($this->options as $panelId => $panelData) {
            if (empty($panelData['options'])) {
                continue;
            }

            foreach ($panelData['options'] as $sectionId => $sectionSettings) {
                if (isset($sectionSettings['options'])) {
                    foreach ($sectionSettings['options'] as $option_id => $optionData) {
                        if (empty($optionData['apply_type'])) {
                            continue;
                        }

                        if ('css' == $optionData['apply_type']) {
                            $control_id = sprintf(
                                '%s[%s][%s][%s]',
                                $this->themeModificationsRepository::OPTION_NAME,
                                $panelId,
                                $sectionId,
                                $option_id
                            );
                            $selector       = $optionData['selector'];
                            $cssOptionName  = $optionData['css_option_name'];
                            $cssOptionValue = $optionData['css_option_value'] ?? null;

                            $cssControls[$control_id] = array(
                                'selector'         => $selector,
                                'css_option_name'  => $cssOptionName,
                                'css_option_value' => $cssOptionValue,
                            );
                        }
                    }
                }
            }
        }

        $localize = array(
            'css_controls' => $cssControls,
        );
        wp_localize_script('wc-plc-customizer-preview-js', 'wdp_customize_preview', $localize);
    }
}
