<?php

namespace ADP\BaseVersion\Includes\Shortcodes;

use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\CustomizerExtensions\CustomizerExtensions;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\VolumePricingTable\RangeDiscountTable;
use ADP\Factory;

defined('ABSPATH') or exit;

class CategoryRangeDiscountTableShortcode
{
    const NAME = 'adp_category_bulk_rules_table';

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
        $rangeDiscountTable = Factory::get(
            "VolumePricingTable_RangeDiscountTable",
            $this->context,
            $this->customizer,
            new RuleRepository(),
            new PersistentRuleRepository()
        );

        return $rangeDiscountTable->getCategoryTableContent();
    }
}
