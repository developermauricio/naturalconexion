<?php

namespace ADP\BaseVersion\Includes\AdminExtensions\AdminPage\Tabs;

use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Helpers\Helpers;
use ADP\BaseVersion\Includes\ImportExport\Importer;
use ADP\BaseVersion\Includes\ImportExport\Exporter;
use ADP\BaseVersion\Includes\ImportExport\ImporterCSV;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage\AdminTabInterface;
use ADP\BaseVersion\Includes\Database\Repository\RuleRepository;
use ADP\Factory;
use Exception;

defined('ABSPATH') or exit;

class Tools implements AdminTabInterface
{
    const IMPORT_TYPE_OPTIONS = 'options';
    const IMPORT_TYPE_RULES = 'rules';

    /**
     * @var string
     */
    protected $title;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var array
     */
    protected $groups;

    /**
     * @var array
     */
    protected $import_data_types;

    /**
     * @var string
     */
    protected $nonceParam;

    /**
     * @var string
     */
    protected $nonceName;

    public function __construct($deprecated = null)
    {
        $this->context = adp_context();
        $this->title   = self::getTitle();

        $this->import_data_types = array(
            self::IMPORT_TYPE_OPTIONS => __('Options', 'advanced-dynamic-pricing-for-woocommerce'),
            self::IMPORT_TYPE_RULES   => __('Rules', 'advanced-dynamic-pricing-for-woocommerce'),
        );

        add_action('wp_ajax_export-csv-bulk-ranges', array($this, "exportCSVBulkRangesAjaxCB"));
        add_action('wp_ajax_migrate-common-to-product-only', array($this, 'migrateCommonToProductOnly'));
        add_action('wp_ajax_migrate-product-only-to-common', array($this, 'migrateProductOnlyToCommon'));

        $this->nonceParam = 'wdp-request-tools-nonce';
        $this->nonceName = 'wdp-request-tools';
    }

    protected function checkNonceOrDie()
    {
        if (wp_verify_nonce($_REQUEST[$this->nonceParam] ?? null, $this->nonceName) === false) {
            wp_die(__('Invalid nonce specified', 'advanced-dynamic-pricing-for-woocommerce'),
                __('Error', 'advanced-dynamic-pricing-for-woocommerce'), ['response' => 403]);
        }
    }

    public function exportCSVBulkRangesAjaxCB()
    {
        $this->checkNonceOrDie();
        $els = self::prepareForExportRulesWithBulkRanges();
        header('Content-type: text/csv');
        header('Expires: 0');
        header('Content-Disposition: attachment; filename="advanced-dynamic-pricing-export.csv"');
        $fp = fopen('php://output', 'w');
        fputcsv($fp, array_keys($els[0]));
        foreach ($els as $el) {
            fputcsv($fp, (array)$el);
        }
        fclose($fp);
        die();
    }

    public function migrateCommonToProductOnly() {
        $this->checkNonceOrDie();

        $repo = new RuleRepository();
        wp_send_json_success("Done: " . $repo->migrateSuitableCommonRulesToPersistence() . " affected");
    }

    public function migrateProductOnlyToCommon() {
        $this->checkNonceOrDie();

        $repo = new RuleRepository();
        wp_send_json_success("Done: " . $repo->migrateSuitablePersistenceRulesToCommon() . " affected");
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    protected function prepareCSV($csvFilePath)
    {
        $newBulkRanges = array_map('str_getcsv', file($csvFilePath));
        unset($newBulkRanges[0]);
        $elements = array();
        foreach ($newBulkRanges as $newBulkRange) {
            $elements[$newBulkRange[0]]['ranges'][] = [
                'from' => $newBulkRange[2],
                'to' => $newBulkRange[3],
                'value' => $newBulkRange[4]
            ];
        }
        return $elements;
    }

    public function handleSubmitAction()
    {
        if (isset($_POST['wdp-import']) && ! empty($_POST['wdp-import-data']) && ! empty($_POST['wdp-import-type'])) {
            $this->checkNonceOrDie();

            $data = json_decode(
                str_replace('\\', '',
                    str_replace('\\"', '', wp_unslash($_POST['wdp-import-data']))
                ),
                true
            );
            $import_data_type = $_POST['wdp-import-type'];
            set_transient(
                'import-result',
                $this->actionGroups($data, $import_data_type) ? 'The operation completed successfully.' : 'The operation is failed.'
            );
            wp_redirect($_SERVER['HTTP_REFERER']);
            exit();
        } else if (isset($_POST['wdp-import-bulk-ranges'])) {
            $this->checkNonceOrDie();

            $csvFilePath = $_FILES['rules-to-import']['tmp_name'];
            $elements = $this->prepareCSV($csvFilePath);
            $this->actionReimportRulesWithBulkRanges($elements);
        } else if(isset($_POST['wdp-import-csv']) && !empty($_FILES['rules-to-import'])){
            $this->checkNonceOrDie();

            $data = ImporterCSV::prepareCSV($_FILES['rules-to-import']['tmp_name']);
            ImporterCSV::importRules($data, $_POST['wdp-import-data-rule-import']);
        }
    }

    public function getViewVariables()
    {
        $this->prepareExportGroups();
        $groups            = $this->groups;
        $import_data_types = $this->import_data_types;
        $sections          = $this->getSections();

        $security = wp_create_nonce($this->nonceName);
        $security_param = $this->nonceParam;

        return compact('groups', 'sections', 'import_data_types', 'security', 'security_param');
    }

    public static function getRelativeViewPath()
    {
        return 'admin_page/tabs/tools.php';
    }

    public static function getHeaderDisplayPriority()
    {
        return 40;
    }

    public static function getKey()
    {
        return 'tools';
    }

    public static function getTitle()
    {
        return __('Tools', 'advanced-dynamic-pricing-for-woocommerce');
    }

    public function enqueueScripts()
    {
//		$is_settings_page = isset( $_GET['page'] ) && $_GET['page'] == 'wdp_settings';
//		// Load backend assets conditionally
//		if ( ! $is_settings_page ) {
//			return;
//		}

        $baseVersionUrl = WC_ADP_PLUGIN_URL . "/BaseVersion/";
        wp_enqueue_script(
            'wdp-tools',
            $baseVersionUrl . 'assets/js/tools.js',
            ['jquery'],
            WC_ADP_VERSION,
            true
        );

        wp_localize_script('wdp-tools', 'wdpTools', [
            'security'       => wp_create_nonce($this->nonceName),
            'security_param' => $this->nonceParam,
        ]);

        if ($this->context->is($this->context::ADP_PLUGIN_PAGE)) {
            wp_dequeue_style('jquery-mobile-theme-styles');
        }
        wp_enqueue_style('wdp_options-styles', $baseVersionUrl . 'assets/css/tools.css', array(), WC_ADP_VERSION);
    }

    protected function actionGroups($data, $importDataType)
    {
        return $this->actionOptionsGroup($data, $importDataType) || $this->actionRulesGroup($data, $importDataType);
    }

    protected function actionOptionsGroup($data, $importDataType)
    {
        if ($importDataType !== self::IMPORT_TYPE_OPTIONS) {
            return false;
        }

        $settings = $this->context->getSettings();

        foreach (array_keys($settings->getOptions()) as $key) {
            $option = $settings->tryGetOption($key);

            if ($option) {
                if (isset($data[$key])) {
                    $option->set($data[$key]);
                }
            }
        }

        $settings->save();
        return true;
    }

    protected function prepareExportGroups()
    {
        $this->prepareOptionsGroup();
        $this->prepareExportGroup();
    }

    protected function prepareOptionsGroup()
    {
        $options = $this->context->getSettings()->getOptions();

        $options_group = array(
            'label' => __('Options', 'advanced-dynamic-pricing-for-woocommerce'),
            'data'  => $options,
        );

        $this->groups['options'] = array(
            'label' => __('Options', 'advanced-dynamic-pricing-for-woocommerce'),
            'items' => array('options' => $options_group),
        );
    }

    protected function actionRulesGroup($data, $importDataType)
    {
        if ($importDataType !== self::IMPORT_TYPE_RULES) {
            return false;
        }

        return count(Importer::importRules($data, $_POST['wdp-import-data-reset-rules'])) > 0;
    }

    protected static function prepareForExportRulesWithBulkRanges()
    {
        /** @var $exporter Exporter */
        $exporter = Factory::get("ImportExport_Exporter");
        /** @var $rules Rule[] */
        $rules = $exporter->exportRulesWithBulk();
        $els = array();
        foreach ($rules as $rule) {
            $ranges = $rule['bulk_adjustments']['ranges'];
            foreach ($ranges as $range) {
                $els[] = [
                    'Rule ID'    => $rule['id'],
                    'Rule Title' => $rule['title'],
                    'Range from'  => $range['from'],
                    'Range to'    => $range['to'],
                    'Discount value' => $range['value']
                ];
            }
        }
        return $els;
    }

    protected function prepareForBulkReimport($data): array
    {
        /** @var $exporter Exporter */
        $exporter = Factory::get("ImportExport_Exporter");
        /** @var $rules Rule[] */
        $rules = $exporter->exportRulesWithBulk();
        for ($i = 0; $i < count($rules); $i++) {
            if (isset($data[$rules[$i]['id']])) {
                $rules[$i]['bulk_adjustments']['ranges'] = $data[$rules[$i]['id']]['ranges'];
            }
        }
        return $rules;
    }

    protected function actionReimportRulesWithBulkRanges($data)
    {
        $items = $this->prepareForBulkReimport($data);
        Importer::UpdateRulesRanges($items);
    }

    protected function prepareExportGroup()
    {
        $exportItems = array();

        $exporter = Factory::get("ImportExport_Exporter");
        $rules    = $exporter->exportRules();

        foreach ($rules as &$rule) {
            unset($rule['id']);

            if ( ! empty($rule['filters'])) {
                foreach ($rule['filters'] as &$item) {
                    $item['value'] = isset($item['value']) ? $item['value'] : array();
                    $item['value'] = $this->convertElementsFromIdToName($item['value'], $item['type']);
                }
                unset($item);
            }

            if ( ! empty($rule['get_products']['value'])) {
                foreach ($rule['get_products']['value'] as &$item) {
                    $item['value'] = isset($item['value']) ? $item['value'] : array();
                    $item['value'] = $this->convertElementsFromIdToName($item['value'], $item['type']);
                }
                unset($item);
            }

            if ( ! empty($rule['auto_add_products']['value'])) {
                foreach ($rule['auto_add_products']['value'] as &$item) {
                    $item['value'] = $item['value'] ?? array();
                    $item['value'] = $this->convertElementsFromIdToName($item['value'], $item['type']);
                }
                unset($item);
            }

            if ( ! empty($rule['conditions'])) {
                foreach ($rule['conditions'] as &$item) {
                    foreach ($item['options'] as &$optionItem) {
                        if (is_array($optionItem)) {
                            $converted = null;
                            try {
                                $converted = $this->convertElementsFromIdToName($optionItem, $item['type']);
                            } catch (Exception $e) {

                            }

                            if ($converted) {
                                $optionItem = $converted;
                            }
                        }
                    }
                }
                unset($item);
            }
        }
        unset($rule);

        $exportItems['all'] = array(
            'label' => __('All', 'advanced-dynamic-pricing-for-woocommerce'),
            'data'  => $rules,
        );

        foreach ($rules as $rule) {
            $exportItems[] = array(
                'label' => "{$rule['title']}",
                'data'  => array($rule),
            );
        }

        $this->groups['rules'] = array(
            'label' => __('Rules', 'advanced-dynamic-pricing-for-woocommerce'),
            'items' => $exportItems
        );
    }

    /**
     * @param array $items or empty string
     * @param string $type
     *
     * @return array|string
     */
    protected function convertElementsFromIdToName($items, $type)
    {
        if (empty($items)) {
            return $items;
        }
        foreach ($items as &$value) {
            if ('products' === $type) {
                $value = Helpers::getProductName($value);
            } elseif ('product_categories' === $type) {
                $value = Helpers::getCategoryTitle($value);
            } elseif ('product_tags' === $type) {
                $value = Helpers::getTagTitle($value);
            } elseif ('product_attributes' === $type) {
                $value = Helpers::getAttributeTitle($value);
            }
        }

        return $items;
    }

    public function registerAjax()
    {

    }

    public function renderToolsTemplate($template, $data)
    {
        extract($data);
        include WC_ADP_PLUGIN_VIEWS_PATH . "admin_page/tabs/tools/{$template}.php";
    }

    protected function getSections()
    {
        return array(
            "system_report" => array(
                'title'     => __("System report", 'advanced-dynamic-pricing-for-woocommerce'),
                'templates' => array(
                    "system_report",
                ),
            ),
            'import_rule_csv' => array(
                'title'       => __('Import Rules (CSV)', 'advanced-dynamic-pricing-for-woocommerce'),
                'templates'   => array(
                    'import_rule_csv',
                ),
            ),
            "manage_bulk_ranges"        => array(
                'title'     => __("Manage bulk ranges", 'advanced-dynamic-pricing-for-woocommerce'),
                'templates' => array(
                    "manage_bulk_ranges",
                ),
            ),
            "export"        => array(
                'title'     => __("Export settings", 'advanced-dynamic-pricing-for-woocommerce'),
                'templates' => array(
                    "export",
                ),
            ),
            "import"        => array(
                'title'     => __("Import settings", 'advanced-dynamic-pricing-for-woocommerce'),
                'templates' => array(
                    "import",
                ),
            ),
            "migration_rules"        => array(
                'title'     => __("Convert rules", 'advanced-dynamic-pricing-for-woocommerce'),
                'templates' => array(
                    "migration_rules",
                ),
            ),
        );
    }
}
