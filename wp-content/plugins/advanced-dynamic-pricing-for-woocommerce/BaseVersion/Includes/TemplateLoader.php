<?php

namespace ADP\BaseVersion\Includes;

defined('ABSPATH') or exit;

class TemplateLoader
{
    /**
     * @param string $templateName
     * @param array $args
     * @param string $templatePath
     *
     * @return false|string
     */
    public static function wdpGetTemplate($templateName, $args = array(), $templatePath = '')
    {
        if ( ! empty($args) && is_array($args)) {
            extract($args);
        }

        $fullTemplatePath = trailingslashit(WC_ADP_PLUGIN_TEMPLATES_PATH);

        if ($templatePath) {
            $fullTemplatePath .= trailingslashit($templatePath);
        }

        $fullExternalTemplatePath = locate_template(array(
            'advanced-dynamic-pricing-for-woocommerce/' . trailingslashit($templatePath) . $templateName,
            'advanced-dynamic-pricing-for-woocommerce/' . $templateName,
        ));

        if ($fullExternalTemplatePath) {
            $fullTemplatePath = $fullExternalTemplatePath;
        } else {
            $fullTemplatePath .= $templateName;
        }

        ob_start();
        include $fullTemplatePath;
        $templateContent = ob_get_clean();

        return $templateContent;
    }
}
