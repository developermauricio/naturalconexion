<?php

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

class NTA_Whatsapp
{
    public function __construct()
    {
        $nta_whatsapp_shortcode = new NTA_Whatsapp_Shortcode();
        $nta_whatsapp_popup = new NTA_Whatsapp_Popup();
        $nta_whatsapp_setting = new NTA_Whatsapp_Setting();
        $nta_whatsapp_post_type = new NTA_Whatsapp_PostType();
        $nta_whatsapp_woocommerce = new NTA_Whatsapp_Woocommerce();
    }

    public function activation_hook()
    {

    }

    public function deactivation_hook()
    {

    }
}
