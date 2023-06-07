<?php

namespace ADP\BaseVersion\Includes\Shortcodes;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\CustomizerExtensions\CustomizerExtensions;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\VolumePricingTable\ProductVolumePricingTableProperties;
use ADP\BaseVersion\Includes\VolumePricingTable\RangeDiscountTable;
use ADP\BaseVersion\Includes\VolumePricingTable\RangeDiscountTableDisplay;
use ADP\Factory;

defined('ABSPATH') or exit;

class ProductRangeDiscountTableShortcode
{
    const NAME = 'adp_product_bulk_rules_table';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CustomizerExtensions
     */
    protected $customizer;

    /**
     * @param Context|CustomizerExtensions $contextOrCustomizer
     * @param null $deprecated
     */
    public function __construct($contextOrCustomizer, $deprecated = null)
    {
        $this->context    = adp_context();
        $this->customizer = $contextOrCustomizer instanceof CustomizerExtensions ? $contextOrCustomizer : $deprecated;
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param CustomizerExtensions $customizer
     */
    public static function register($customizer)
    {
        $shortcode = new self($customizer);
        add_shortcode(self::NAME, array($shortcode, 'getContent'));
    }

    public function getContent($args)
    {
        /** @var RangeDiscountTable $table */
        /** @var RangeDiscountTableDisplay $tableDisplay */
        $table = Factory::get("VolumePricingTable_RangeDiscountTable", $this->customizer);
        $tableDisplay = Factory::get("VolumePricingTable_RangeDiscountTableDisplay", $this->customizer);

        $productTableOptions = new ProductVolumePricingTableProperties();

        if ( ! empty($args['layout'])
             && in_array(
                 $args['layout'],
                 array($productTableOptions::LAYOUT_VERBOSE, $productTableOptions::LAYOUT_SIMPLE)
             )
        ) {
            $productTableOptions->tableLayout = $args['layout'];
        }

        $productTableOptions->isSimpleLayoutForcePercentage = isset($args['force_percentage'])
                                                              && wc_string_to_bool($args['force_percentage']);

        $table->setProductContextOptions($productTableOptions);

        $productId = ! empty($args['id']) ? intval($args['id']) : null;

        $tableDisplay->hookLoadAssets();

        return $table->getProductTableContent($productId);
    }
}
