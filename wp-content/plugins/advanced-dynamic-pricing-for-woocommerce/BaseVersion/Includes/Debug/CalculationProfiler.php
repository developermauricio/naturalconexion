<?php

namespace ADP\BaseVersion\Includes\Debug;

use ADP\BaseVersion\Includes\Cache\CacheHelper;
use ADP\BaseVersion\Includes\CartProcessor\CartProcessor;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepository;
use ADP\BaseVersion\Includes\Database\Repository\PersistentRuleRepositoryInterface;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\BaseVersion\Includes\PriceDisplay\Processor;
use ADP\BaseVersion\Includes\PriceDisplay\WcProductProcessor\IWcProductProcessor;
use ADP\Factory;

defined('ABSPATH') or exit;

class CalculationProfiler
{
    const INITIAL_CART = 'initial_cart';
    const PROCESSED_CART = 'processed_cart';
    const PROCESSED_PRODUCTS = 'processed_products';
    const RULES_TIMING = 'rules_timing';
    const OPTIONS = 'options';
    const ACTIVE_HOOKS = 'active_hooks';

    /**
     * @var CartProcessor
     */
    protected $cartProcessor;

    /**
     * @var IWcProductProcessor
     */
    protected $productProcessor;

    /**
     * @var string
     */
    private $import_key;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ReportsStorage
     */
    protected $storage;

    /**
     * @var PersistentRuleRepositoryInterface
     */
    protected $persistentRuleRepository;

    /**
     * @param Context|CartProcessor $contextOrCartProcessor
     * @param CartProcessor|Processor $cartProcessorOrProductProcessor
     * @param Processor|null $deprecated
     */
    public function __construct($contextOrCartProcessor, $cartProcessorOrProductProcessor, $deprecated = null)
    {
        $this->context                  = adp_context();
        $this->cartProcessor            = $contextOrCartProcessor instanceof CartProcessor ? $contextOrCartProcessor : $cartProcessorOrProductProcessor;
        $this->productProcessor         = $cartProcessorOrProductProcessor instanceof IWcProductProcessor ? $cartProcessorOrProductProcessor : $deprecated;
        $this->persistentRuleRepository = new PersistentRuleRepository();

        // should wait, because impossible to create import key earlier
        add_action('wp_loaded', function () {
            $this->import_key = $this->createImportKey();
            $this->storage    = new ReportsStorage($this->import_key);
        }, 1);
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
        $this->cartProcessor->withContext($context);
        $this->productProcessor->withContext($context);
    }

    public function withPersistentRuleRepository(PersistentRuleRepositoryInterface $repository)
    {
        $this->persistentRuleRepository = $repository;
    }

    public function getImportKey()
    {
        return $this->import_key;
    }

    public function installActionCollectReport()
    {
        if ( $this->context->is($this->context::PROCESSING_UPDATE) ) {
            return;
        }
        add_action('shutdown', array($this, 'collectAndStoreReport'), PHP_INT_MAX); // do not use shutdown hook
    }

    public function collectAndStoreReport()
    {
        $context     = $this->context;
        $activeRules = array_merge(
            $this->persistentRuleRepository->getRulesFromWcCart($context, WC()->cart),
            CacheHelper::loadActiveRules($context)->getRules()
        );

        $activeRulesAsDict = array();
        foreach ($activeRules as $rule) {
            $activeRulesAsDict[$rule->getId()] = self::getRuleAsDict($rule, $context);
        }

        if ($context->is($context::AJAX)) {
            $prevProcessedProductsReport = $this->storage->getReport('processed_products');
            foreach ($prevProcessedProductsReport as $id => $report) {
                $this->productProcessor->calculateProduct($id);
            }
        }

        $reports = array(
            'processed_cart'     => (new Collectors\WcCart($this->cartProcessor))->collect(),
            'processed_products' => (new Collectors\Products($this->productProcessor))->collect(),
            'options'            => (new Collectors\Options($this->context))->collect(),
            'additions'          => (Factory::get("Debug_Collectors_PluginsAndThemes", $this->context))->collect(),
            'active_hooks'       => (new Collectors\ActiveHooks())->collect(),

            'rules' => $activeRulesAsDict,
        );

        foreach ($reports as $report_key => $report) {
            $this->storage->storeReport($report_key, $report);
        }
    }

    /**
     * @param Rule $rule
     * @param Context $context
     *
     * @return array
     */
    private static function getRuleAsDict($rule, $context)
    {
        $slug                  = 'wdp_settings';
        $tab                   = 'rules';
        $exporter              = Factory::get("ImportExport_Exporter");
        $data                  = $exporter->convertRule($rule);
        $data['id']            = $rule->getId();
        $data['edit_page_url'] = admin_url("admin.php?page={$slug}&tab={$tab}&rule_id={$rule->getId()}");

        return $data;
    }

    private function createImportKey()
    {
        if ( ! did_action('wp_loaded')) {
            _doing_it_wrong(__FUNCTION__,
                sprintf(__('%1$s should not be called before the %2$s action.', 'woocommerce'), 'create_import_key',
                    'wp_loaded'), WC_ADP_VERSION);

            return null;
        }

        global $wp;

        return substr(md5($wp->request . '|' . (string)get_current_user_id()), 0, 8);
    }
}
