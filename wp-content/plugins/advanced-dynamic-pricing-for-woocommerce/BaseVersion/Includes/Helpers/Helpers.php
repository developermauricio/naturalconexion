<?php

namespace ADP\BaseVersion\Includes\Helpers;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use WC_Product;
use WP_Term;

defined('ABSPATH') or exit;

add_action('woocommerce_subscriptions_deactivated', function () {
    $ruleRepository = new RuleRepository();
    $ruleRepository->deleteConditionsFromDbByTypes(array('subscription'));
});

class Helpers
{
    public static function getProductCustomFields($id)
    {
        global $wpdb;

        $wp_fields = $wpdb->get_results(
            $wpdb->prepare("SELECT DISTINCT CONCAT(fields.meta_key,'=',fields.meta_value) FROM {$wpdb->postmeta} AS fields
									JOIN {$wpdb->posts} AS products ON products.ID = fields.post_id
									WHERE products.post_type IN ('product','product_variation') AND products.ID = %d ORDER BY meta_key",
                $id), ARRAY_N);

        return array_map(function ($customField) {
            return array(
                'id'   => current($customField),
                'text' => current($customField),
            );
        }, $wp_fields);
    }

    public static function getProductAttributes($ids)
    {
        global $wc_product_attributes, $wpdb;

        if (empty($ids)) {
            return array();
        }

        $ids = implode(', ', $ids);

        $items = $wpdb->get_results("
			SELECT $wpdb->terms.term_id, $wpdb->terms.name, taxonomy
			FROM $wpdb->term_taxonomy INNER JOIN $wpdb->terms USING (term_id)
			WHERE $wpdb->terms.term_id in ($ids)
		");

        return array_values(array_filter(array_map(function ($term) use ($wc_product_attributes) {
            if ( ! isset($wc_product_attributes[$term->taxonomy])) {
                return false;
            }

            $attribute = $wc_product_attributes[$term->taxonomy]->attribute_label;

            return array(
                'id'   => (string)$term->term_id,
                'text' => $attribute . ': ' . $term->name,
            );
        }, $items)));
    }

    public static function getUsers($ids)
    {
        $users = get_users(array(
            'fields'  => array('ID', 'user_nicename'),
            'include' => $ids,
            'orderby' => 'user_nicename',
        ));

        return array_map(function ($user) {
            return array(
                'id'   => (string)$user->ID,
                'text' => $user->user_nicename,
            );
        }, $users);
    }

    public static function getUserRoles()
    {
        global $wp_roles;

        $all_roles = $wp_roles->roles;

        $result = array_map(function ($id, $role) {
            return array(
                'id'   => (string)$id,
                'text' => $role['name'],
            );
        }, array_keys($all_roles), $all_roles);

        // dummy role for non registered users
        $result[] = array(
            'id'   => 'wdp_guest',
            'text' => __('Guest', 'advanced-dynamic-pricing-for-woocommerce'),
        );

        return array_values($result);
    }

    public static function getUserCapabilities()
    {
        global $wp_roles;

        $all_roles = $wp_roles->roles;

        $capabilities = array();

        foreach ($all_roles as $role) {
            foreach ($role['capabilities'] as $capability => $value) {
                $capabilities[] = (string)$capability;
            }
        }

        $result = array_map(function ($capability) {
            return array(
                'id'   => $capability,
                'text' => $capability,
            );
        }, array_unique($capabilities));

        return array_values($result);
    }

    public static function getCountries()
    {
        $countries = WC()->countries->get_countries();

        $result = array_map(function ($id, $text) {
            return array(
                'id'   => $id,
                'text' => $text,
            );
        }, array_keys($countries), $countries);

        return array_values($result);
    }

    public static function getStates()
    {
        $shopCountryState = wc_format_country_state_string(get_option('woocommerce_default_country', ''));
        $shop_country     = isset($shopCountryState['country']) ? $shopCountryState['country'] : null;

        $countries      = WC()->countries->get_countries();
        $country_states = WC()->countries->get_states();

        $result = array();
        foreach ($country_states as $countryCode => $states) {
            foreach ($states as $id => $text) {
                $result[] = array(
                    'id'   => $countryCode . ':' . $id,
                    'text' => $text . (isset($countries[$countryCode]) && $shop_country !== $countryCode ? '(' . $countries[$countryCode] . ')' : ''),
                );
            }
        }

        return $result;
    }

    public static function getCurrencies()
    {
        $allCurrencies = get_woocommerce_currencies();

        $result = array();
        foreach ($allCurrencies as $code => $name) {

            $result[] = array(
                'id' => $code,
                'text' => $name
            );
        }

        return $result;
    }

    public static function getPaymentMethods()
    {
        $paymentGateways = WC()->payment_gateways->payment_gateways();

        $result = array();
        foreach ($paymentGateways as $paymentGateway) {
            if ( ! isset($paymentGateway->id)) {
                continue;
            }

            $result[] = array(
                'id'   => $paymentGateway->id,
                'text' => $paymentGateway->title ?? $paymentGateway->id,
            );
        }

        return $result;
    }

    public static function getAllShippingMethods()
    {
        $result = array(
            array(
                'id'   => "all",
                'text' => __('All', 'advanced-dynamic-pricing-for-woocommerce'),
            )
        );

        return array_merge($result, self::getShippingMethods());
    }

    public static function getShippingMethods()
    {
        $shippingMethods = array();
        foreach (WC()->shipping->get_shipping_methods() as $method) {
            $shippingMethods[$method->id] = __('[All zones]',
                    'advanced-dynamic-pricing-for-woocommerce') . " " . $method->method_title;
        }

        foreach (\WC_Shipping_Zones::get_zones() as $zone) {
            $methods = $zone['shipping_methods'];
            /** @var \WC_Shipping_Method $method */
            foreach ($methods as $method) {
                $shippingMethods[$method->get_rate_id()] = '[' . $zone['zone_name'] . '] ' . $method->get_title();
            }
        }

        $zone    = new \WC_Shipping_Zone(0);
        $methods = $zone->get_shipping_methods();
        /** @var \WC_Shipping_Method $method */
        foreach ($methods as $method) {
            $shippingMethods[$method->get_rate_id()] = __('[Rest of the World]',
                    'advanced-dynamic-pricing-for-woocommerce') . ' ' . $method->get_title();
        }

        $result = array();
        foreach ($shippingMethods as $k => $shippingMethod) {
            $result[] = array(
                'id'   => $k,
                'text' => $shippingMethod,
            );
        }

        return $result;
    }

    public static function getShippingZones()
    {
        $zones = \WC_Shipping_Zones::get_zones();

        $result = array();
        foreach ($zones as $z) {
            $result[] = array(
                'id'   => $z['id'],
                'text' => $z['zone_name'],
            );
        }

        $result[] = array(
            'id'   => 0,
            'text' => __('Locations not covered by your other zones', 'woocommerce'),
        );

        return $result;
    }

    public static function getShippingClasses()
    {
        $shippingClasses = WC()->shipping->get_shipping_classes();

        $result = array();
        foreach ($shippingClasses as $shippingClass) {
            $result[] = array(
                'id'   => $shippingClass->slug,
                'text' => $shippingClass->name,
            );
        }

        return $result;
    }

    public static function getWeekdays()
    {
        $result = array(
            __('Sunday', 'advanced-dynamic-pricing-for-woocommerce'),
            __('Monday', 'advanced-dynamic-pricing-for-woocommerce'),
            __('Tuesday', 'advanced-dynamic-pricing-for-woocommerce'),
            __('Wednesday', 'advanced-dynamic-pricing-for-woocommerce'),
            __('Thursday', 'advanced-dynamic-pricing-for-woocommerce'),
            __('Friday', 'advanced-dynamic-pricing-for-woocommerce'),
            __('Saturday', 'advanced-dynamic-pricing-for-woocommerce'),
        );
        array_walk($result, function (&$item, $key) {
            $item = array(
                'id'   => $key,
                'text' => $item,
            );
        });

        return $result;
    }

    public static function getLanguages()
    {
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        $translations = wp_get_available_translations();
        $result       = [];
        foreach ($translations as $iso => $lang) {
            $result[] = [
                'id'   => $iso,
                'text' => $lang['english_name'],
            ];
        }

        return $result;
    }

    public static function getProductTitle($id)
    {
        $product = wc_get_product($id);
        if (!($product instanceof WC_Product)) {
            return "";
        }

        if ($product instanceof \WC_Product_Variation) {
            $formattedVariationList = wc_get_formatted_variation(
                $product,
                true,
                true,
                true
            );

            return $product->get_name() . '<span class="description">' . $formattedVariationList . '</span>';
        } else {
            return $product->get_name();
        }
    }

    public static function getProductName($id)
    {
        $post = get_post($id);

        return $post !== null ? $post->post_name : "";
    }

    public static function getProductId($name)
    {
        if (is_int($name)) {
            if (CacheHelper::getWcProduct($name)) {
                return $name;
            }
        }


        /** @var WC_Product[] $posts */
        $posts = wc_get_products(array(
            'name' => $name,
            'type' => array_merge(array_keys(wc_get_product_types()), array('variation'))
        ));

        $post = reset($posts);

        if ($post instanceof WC_Product) {
            return $post->get_id();
        }

        return false;
    }

    public static function getProductLink($id)
    {
        return get_post_permalink($id);
    }

    public static function getCategoryTitle($id)
    {
        $term = get_term($id, 'product_cat');

        return ! empty($term) && ! is_wp_error($term) ? $term->name : "";
    }

    public static function getCategorySlugTitle($slug)
    {
        $term = get_term_by('slug', $slug, 'product_cat');

        return ! empty($term) && ! is_wp_error($term) ? $term->name : "";
    }

    public static function getCategorySlug($id)
    {
        $term = get_term($id, 'product_cat');

        return ! empty($term) && ! is_wp_error($term) ? $term->slug : $id;
    }

    public static function getCategoryId($name)
    {
        return is_numeric($name) ? $name : self::getTermId($name, 'product_cat');
    }

    public static function getCategoryLink($id)
    {
        return get_category_link($id);
    }

    public static function getCategorySlugLink($slug)
    {
        $link = get_term_link($slug, 'product_cat');

        return ! empty($link) && ! is_wp_error($link) ? $link : "";
    }

    public static function getTagTitle($id)
    {
        $term = get_term($id, 'product_tag');

        return ! empty($term) && ! is_wp_error($term) ? $term->name : "";
    }

    public static function getTagId($name)
    {
        return is_numeric($name) ? $name : self::getTermId($name, 'product_tag');
    }

    public static function getTagLink($id)
    {
        return get_tag_link($id);
    }

    public static function getAttributeTitle($id)
    {
        global $wc_product_attributes;

        $term = get_term($id, 'product_tag');

        if ($term and ! is_wp_error($term)) {
            $attribute = $wc_product_attributes[$term->taxonomy]->attribute_label;
            $ret       = $attribute . ': ' . $term->name;
        } else {
            $ret = $id;
        }

        return $ret;
    }

    public static function getAttributeId($name)
    {
        return is_numeric($name) ? $name : self::getTermId($name, 'product_tag');
    }

    public static function getAttributeLink($id)
    {
        return '';//TODO:??
    }

    public static function getTermId($name, $taxonomy)
    {
        $term = get_term_by('name', $name, $taxonomy);

        if ($term instanceof WP_Term) {
            return $term->term_id;
        }

        return 0;
    }

    public static function getTitleByType($id, $type)
    {
        if ('products' === $type) {
            $name = self::getProductTitle($id);
        } elseif ('product_categories' === $type) {
            $name = self::getCategoryTitle($id);
        } elseif ('product_category_slug' === $type) {
            $name = self::getCategorySlugTitle($id);
        } elseif ('product_tags' === $type) {
            $name = self::getTagTitle($id);
        } elseif ('product_attributes' === $type) {
            $name = self::getAttributeTitle($id);
        } elseif (in_array($type, array_keys(self::getCustomProductTaxonomies()))) {
            $name = self::getProductTaxonomyTermTitle($id, $type);
        } else {
            $name = $id;
        }

        return $name;
    }

    public static function getPermalinkByType($id, $type)
    {
        if ('products' === $type) {
            $link = self::getProductLink($id);
        } elseif ('product_categories' === $type) {
            $link = self::getCategoryLink($id);
        } elseif ('product_category_slug' === $type) {
            $link = self::getCategorySlugLink($id);
        } elseif ('product_tags' === $type) {
            $link = self::getTagLink($id);
        } elseif ('product_attributes' === $type) {
            $link = self::getAttributeLink($id);
        } elseif (in_array($type, array_keys(self::getCustomProductTaxonomies()))) {
            $link = self::getProductTaxonomyTermPermalink($id, $type);
        } else {
            $link = '';
        }

        return $link;
    }

    public static function getCustomProductTaxonomies($skipCache = false)
    {
        static $customTaxonomies = null;
        if ( ! $skipCache && $customTaxonomies !== null) {
            return $customTaxonomies;
        }

        $customTaxonomies = array_filter(get_taxonomies(array(
            'show_ui'      => true,
            'show_in_menu' => true,
            'object_type'  => array('product'),
        ), 'objects'), function ($tax) {
            $buildInTaxonomies = array('product_cat', 'product_tag');

            return ! in_array($tax->name, $buildInTaxonomies);
        });

        return $customTaxonomies;
    }

    public static function getProductTaxonomyTermTitle($termId, $taxonomyName)
    {
        $term = get_term($termId, $taxonomyName);

        return ! empty($term) && ! is_wp_error($term) ? $term->name : "";
    }

    public static function getProductTaxonomyTermPermalink($termId, $taxonomyName)
    {
        $termPermalink = get_term_link((int)$termId, $taxonomyName);

        return ! empty($termPermalink) && ! is_wp_error($termPermalink) ? $termPermalink : '';
    }
}
