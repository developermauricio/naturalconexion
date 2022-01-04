<?php

include "rapid-addon.php";

final class import_add_on {

    protected static $instance;
    protected $add_on;

    static public function get_instance() {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct() {
        global $woocommerce_wpwoof_common;
        // Define the add-on
        $this->add_on = new RapidAddon(WPWOOF_SL_ITEM_NAME . ' Add-On', 'wpwoof_allimport_add_on');

        // Add UI elements to the import template
        foreach (wpwoof_product_catalog::$field_names as $key => $value) {
            if (!isset($value['toImport']))
                continue;
            switch ($value['toImport']) {
                case 'text':
                    $this->add_on->add_field($key, $value['title'], 'text');
                    break;
                case 'textarea':
                    $this->add_on->add_field($key, $value['title'], 'textarea');
                    break;
                case 'radio':
                    $this->add_on->add_field($key, $value['title'], 'radio', $value['options']);
                    break;
                case 'trigger':
                    $this->add_on->add_field($key, $value['title'], 'radio', array(0=>'off',1=>'on'));
                    break;
                default:
                    break;
            }
        }

        foreach ($woocommerce_wpwoof_common->product_fields as $key => $value) {
            if (!isset($value['toImport']))
                continue;
            switch ($value['toImport']) {
                case 'text':
                    $this->add_on->add_field($key, $value['header'], 'text');
                    break;
                case 'textarea':
                    $this->add_on->add_field($key, $value['header'], 'text');
                    break;
                case 'radio':
//                    if (($keyA = array_search('', $value['custom'])) !== false) { //remove empty value
//                        unset($value['custom'][$keyA]);
//                    }
                    $this->add_on->add_field($key, $value['header'], 'radio', $value['custom']);
                    break;
                default:
                    break;
            }
        }
        $this->add_on->set_import_function([$this, 'import']);
        add_action('admin_init', [$this, 'admin_init']);
    }

    public function admin_init() {
        $this->add_on->run(
                array(
                    "post_types" => array("product"),
                )
        );
    }

    // Check if the user has allowed these fields to be updated, and then import data to them
    public function import($post_id, $data, $import_options) {
        global $woocommerce_wpwoof_common;

        foreach (wpwoof_product_catalog::$field_names as $key => $value) {
            if (!isset($value['toImport']))
                continue;
            if ($this->add_on->can_update_meta($key, $import_options)) {
                update_post_meta($post_id, $key, $data[$key]);
            }
        }
        $extraCustomFields = array();
        foreach ($woocommerce_wpwoof_common->product_fields as $key => $value) {
            if (!isset($value['toImport']))
                continue;
            $extraCustomFields[$key] = array('value' => $data[$key]);
        }
        if ($this->add_on->can_update_meta($key, $import_options) && count($extraCustomFields)) {
            update_post_meta($post_id, 'wpwoofgoogle', $extraCustomFields);
        }
    }

}

import_add_on::get_instance();
