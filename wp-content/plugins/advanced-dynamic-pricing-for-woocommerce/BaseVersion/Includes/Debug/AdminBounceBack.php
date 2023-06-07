<?php

namespace ADP\BaseVersion\Includes\Debug;

use WC_Session_Handler;

defined('ABSPATH') or exit;

class AdminBounceBack
{
    const REQUEST_KEY = 'wdp_bounce_back';
    const REQUEST_KEY_VALUE = '1';

    const LAST_IMPORT_KEY_SESSION_KEY = 'wdp_last_import_key';

    /**
     * @var CalculationProfiler
     */
    protected $profiler;

    public function __construct($profiler)
    {
        $this->profiler = $profiler;
    }

    public function catchBounceEvent()
    {
        if ( ! empty($_REQUEST[self::REQUEST_KEY])) {
            $this->actionBounceBack();
            $this->profiler->installActionCollectReport();
        }
    }

    /**
     * We wait until page fully loaded
     */
    protected function actionBounceBack()
    {
        if (did_action('wp_print_scripts')) {
            _doing_it_wrong(__FUNCTION__,
                sprintf(
                    __('%1$s should not be called earlier the %2$s action.', 'woocommerce'),
                    'action_bounce_back',
                    'wp_print_scripts'
                ),
                WC_ADP_VERSION
            );

            return null;
        }

        add_action("wp_print_scripts", function () {
            $referer = wp_get_referer();
            $referer = $referer ?: admin_url();
            WC()->session->set(self::LAST_IMPORT_KEY_SESSION_KEY, $this->profiler->getImportKey());

            ?>
            <meta http-equiv="refresh" content="0; url=<?php echo $referer; ?>">
            <?php
        });
    }

    public static function generateBounceBackUrl()
    {
        return add_query_arg(self::REQUEST_KEY, self::REQUEST_KEY_VALUE, get_permalink(wc_get_page_id('shop')));
    }

    public static function getBounceBackReportDownloadUrl()
    {
        $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
        $session       = new $session_class();
        /**
         * @var WC_Session_Handler $session
         */
        $session->init();

        if (isset($session->wdp_last_import_key)) {
            $importKey = $session->get(self::LAST_IMPORT_KEY_SESSION_KEY);
            $session->__unset(self::LAST_IMPORT_KEY_SESSION_KEY);
            $session->save_data();
        } else {
            $importKey = false;
        }

        $reporter = new ReporterAjax(null);

        return !$importKey ? "" : add_query_arg(array(
            'action' => 'download_report',
            ReporterAjax::IMPORT_KEY_REQUEST_KEY => $importKey,
            'reports' => 'all',
            $reporter->getNonceParam() => wp_create_nonce($reporter->getNonceName()),
        ), admin_url("admin-ajax.php"));
    }
}
