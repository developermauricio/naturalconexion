<?php

namespace ADP\BaseVersion\Includes\WC\DataStores;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ReflectionClass;
use WC_Data_Exception;
use WC_Object_Data_Store_Interface;
use WC_Product;
use WC_Product_Attribute;
use WC_Product_Variation;
use WC_Product_Variation_Data_Store_CPT;

defined('ABSPATH') or exit;

class ProductVariationDataStoreCpt extends WC_Product_Variation_Data_Store_CPT implements WC_Object_Data_Store_Interface
{
    /**
     * @var WC_Product|null
     */
    private $productParent = null;

    /**
     * Reads a product from the database and sets its data to the class.
     *
     * @param WC_Product_Variation $product Product object.
     *
     * @throws WC_Data_Exception If WC_Product::set_tax_status() is called with an invalid tax status (via read_product_data).
     */
    public function read(&$product)
    {
        if (is_null($this->productParent)) {
            return;
        }

        $product->set_defaults();

        if ( ! $product->get_id()) {
            return;
        }

        $productData = CacheHelper::getVariationProductData($product->get_id());

        if ( ! $productData || ! in_array($productData->post_type, array(
                'product',
                'product_variation'
            ), true)) {
            return;
        }

        $this->setProductProps($product, array(
            'name'              => $productData->post_title,
            'slug'              => $productData->post_name,
            'status'            => $productData->post_status,
            'menu_order'        => $productData->menu_order,
            'reviews_allowed'   => 'open' === $productData->comment_status,
            'parent_id'         => $productData->post_parent,
            'attribute_summary' => $productData->post_excerpt,
        ));

        $product->set_date_created(0 < $productData->post_date_gmt ? wc_string_to_timestamp($productData->post_date_gmt) : null);
        $product->set_date_modified(0 < $productData->post_modified_gmt ? wc_string_to_timestamp($productData->post_modified_gmt) : null);

        $this->read_product_data($product);
        $product->set_attributes($this->getProductVariationAttributes($product->get_id()));

        // Set object_read true once all data is read.
        $product->set_object_read(true);
    }

    private function getProductVariationAttributes($variationId)
    {
        $allMeta               = CacheHelper::getVariationProductMeta($variationId);
        $parentAttributes      = $this->productParent->get_attributes();
        $foundParentAttributes = array();
        $variationAttributes   = array();

        // Compare to parent variable product attributes and ensure they match.
        foreach ($parentAttributes as $attributeName => $attribute) {
            /**
             * @var $attribute WC_Product_Attribute
             */

            if ($attribute->get_variation()) {
                $attribute               = 'attribute_' . $attribute->get_name();
                $foundParentAttributes[] = $attribute;
                if ( ! array_key_exists($attribute, $variationAttributes)) {
                    $variationAttributes[$attribute] = ''; // Add it - 'any' will be asumed.
                }
            }
        }

        // Get the variation attributes from meta.
        foreach ($allMeta as $name => $value) {
            // Only look at valid attribute meta, and also compare variation level attributes and remove any which do not exist at parent level.
            if (0 !== strpos($name, 'attribute_') || ! in_array($name, $foundParentAttributes, true)) {
                unset($variationAttributes[$name]);
                continue;
            }

            $variationAttributes[$name] = $value;
        }

        return $variationAttributes;
    }

    private function setProductProps(&$product, $props)
    {
        $reflection = new ReflectionClass($product);
        $property   = $reflection->getProperty('data');
        $property->setAccessible(true);
        $data = $property->getValue($product);

        $property->setValue($product, array_merge($data, $props));
    }

    /**
     * @param $parent WC_Product
     */
    public function addParent($parent)
    {
        if ($parent instanceof WC_Product) {
            $this->productParent = $parent;
        }
    }

    /**
     * Read post data.
     *
     * @param WC_Product_Variation $product Product object.
     *
     * @throws WC_Data_Exception If WC_Product::set_tax_status() is called with an invalid tax status.
     */
    protected function read_product_data(&$product)
    {
        $productMeta = CacheHelper::getVariationProductMeta($product->get_id());

        $metaKeys = array(
            '_variation_description' => 'description',
            '_regular_price'         => 'regular_price',
            '_sale_price'            => 'sale_price',
            '_manage_stock'          => 'manage_stock',
            '_stock_status'          => 'stock_status',
            '_virtual'               => 'virtual',
            '_downloadable'          => 'downloadable',
            '_product_image_gallery' => 'gallery_image_ids',
            '_download_limit'        => 'download_limit',
            '_download_expiry'       => 'download_expiry',
            '_thumbnail_id'          => 'image_id',
            '_backorders'            => 'backorders',
            '_sku'                   => 'sku',
            '_stock'                 => 'stock_quantity',
            '_weight'                => 'weight',
            '_length'                => 'length',
            '_width'                 => 'width',
            '_height'                => 'height',
            '_tax_class'             => 'tax_class',
            '_tax_status'            => 'tax_status',
        );

        $props = array();

        foreach ($productMeta as $key => $value) {
            if (isset($metaKeys[$key])) {
                $props[$metaKeys[$key]] = $value;
            }
        }

        if ( ! isset($props['tax_class'])) {
            $props['tax_class'] = 'parent';
        }

        // must use set_date_props()
        if (isset($productMeta['_sale_price_dates_from'])) {
            $product->set_date_on_sale_from($productMeta['_sale_price_dates_from']);
        }
        if (isset($productMeta['_sale_price_dates_to'])) {
            $product->set_date_on_sale_to($productMeta['_sale_price_dates_to']);
        }

        $this->setProductProps($product, $props);

//		$product->set_shipping_class_id( current( $this->get_term_ids( $id, 'product_shipping_class' ) ) );

        if ($product->is_on_sale('edit')) {
            $product->set_price($product->get_sale_price('edit'));
        } else {
            $product->set_price($product->get_regular_price('edit'));
        }

        $parent_data = array(
            'title'              => $this->productParent->get_title(),
            'status'             => $this->productParent->get_status('nofilter'),
            'sku'                => $this->productParent->get_sku('nofilter'),
            'manage_stock'       => $this->productParent->managing_stock(),
            'backorders'         => $this->productParent->backorders_allowed(),
            'stock_quantity'     => $this->productParent->get_stock_quantity('nofilter'),
            'weight'             => $this->productParent->get_weight('nofilter'),
            'length'             => $this->productParent->get_length('nofilter'),
            'width'              => $this->productParent->get_width('nofilter'),
            'height'             => $this->productParent->get_height('nofilter'),
            'tax_class'          => $this->productParent->get_tax_class('nofilter'),
            'shipping_class_id'  => $this->productParent->get_shipping_class_id('nofilter'),
            'image_id'           => $this->productParent->get_image_id('nofilter'),
            'purchase_note'      => $this->productParent->get_purchase_note('nofilter'),
            'catalog_visibility' => $this->productParent->get_catalog_visibility('nofilter'),
        );
        // since WC 3.5.0
        if (method_exists($this->productParent, "get_low_stock_amount")) {
            $parent_data['low_stock_amount'] = $this->productParent->get_low_stock_amount('nofilter');
        }

        $product->set_parent_data($parent_data);

        // Pull data from the parent when there is no user-facing way to set props.
        $product->set_sold_individually($this->productParent->get_sold_individually('nofilter'));
        $product->set_tax_status($this->productParent->get_tax_status('nofilter'));
        $product->set_cross_sell_ids($this->productParent->get_cross_sell_ids('nofilter'));
    }
}
