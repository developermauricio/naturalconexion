<?php

namespace ADP\BaseVersion\Includes\WC;

defined('ABSPATH') or exit;

class WcProductCustomAttributesCache
{
    public function installHooks()
    {
        add_action('woocommerce_after_product_object_save', function ($product, $dataStore) {
            /** @var \WC_Product $product */
            if ( ! $product->is_type("variation")) {
                $this->updateProductAttributes($product->get_id());
            }
        }, 10, 2);

        add_action("adp_force_custom_product_attributes_update", function () {
            $this->updateAllProductsCustomAttributes();
        });
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function findCustomAttributes($query) {
        global $wpdb;

        $atLeastOneExists = $wpdb->get_var("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE 'adp_custom_product_attribute_%'");
        if ( $atLeastOneExists === null ) {
            $this->updateAllProductsCustomAttributes();
        }

        return $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE 'adp_custom_product_attribute_%' AND meta_value LIKE '%$query%'");
    }

    /**
     * @param int $productId
     */
    protected function updateProductAttributes($productId)
    {
        $attributes = $this->calculateSingleProductCustomAttributes($productId);
        $metaKeyPrefix = "adp_custom_product_attribute";
        $metaKeyIndex = 0;

        while (metadata_exists('post', $productId, $metaKeyPrefix . "_" . $metaKeyIndex)) {
            delete_metadata('post', $productId, $metaKeyPrefix . "_" . $metaKeyIndex);
            $metaKeyIndex++;
        }

        $metaKeyIndex = 0;
        foreach ($attributes as $name => $options) {
            foreach ($options as $option) {
                update_post_meta($productId, $metaKeyPrefix . "_" . $metaKeyIndex, $name . ":" . $option);
                $metaKeyIndex++;
            }
        }
    }

    protected function updateAllProductsCustomAttributes()
    {
        $products = wc_get_products(array(
            'return' => 'ids',
            'limit'  => -1,
        ));

        foreach ($products as $productID) {
            $this->updateProductAttributes($productID);
        }
    }

    /**
     * @param int $productId
     *
     * @return array
     */
    protected function calculateSingleProductCustomAttributes($productId)
    {
        $metaAttributes = get_post_meta($productId, "_product_attributes", true);

        if (empty($metaAttributes) || ! is_array($metaAttributes)) {
            return array();
        }

        $attributes = array();

        foreach ($metaAttributes as $metaAttributeKey => $metaAttributeValue) {
            $metaValue = array_merge(
                array(
                    'name'         => '',
                    'value'        => '',
                    'position'     => 0,
                    'is_visible'   => 0,
                    'is_variation' => 0,
                    'is_taxonomy'  => 0,
                ),
                (array)$metaAttributeValue
            );

            if ( ! empty($metaValue['is_taxonomy'])) {
                continue;
            }

            $name    = $metaValue['name'];
            $options = wc_get_text_attributes($metaValue['value']);

            $attributes[$name] = $options;
        }

        return $attributes;
    }
}
