<?php

/**
 * @package Coordinadora
 * @version 1.1.14
 */
/*
Plugin Name: Coordinadora
Plugin URI: https://www.coordinadora.com/portafolio-de-servicios/
Description: Plugin Oficial para la integración con Coordinadora.
Author: Coordinadora
Version: 1.1.14
Author URI: http://www.coordinadora.com

WC requires at least: 6.0
WC tested up to: 7.1.0
*/

// Exit if accessed directly
if (!defined('ABSPATH')) exit;
/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  register_activation_hook(__FILE__, 'enable');
  register_deactivation_hook(__FILE__, 'disable');

  require_once(__DIR__ . '/includes/Coordinadora_WP_Menu.php');
  require_once(__DIR__ . '/includes/Coordinadora_WC_Order_Custom_Fields.php');
  require_once(__DIR__ . '/includes/controllers/CM_Controller.php');
  require_once(__DIR__ . '/includes/Coordinadora_WC_Droop_Shipping.php');
  require_once(__DIR__ . '/includes/Coordinadora_WP_Dashboard.php');

  Coordinadora_WP_Menu::init();
  Coordinadora_WC_Order_Custom_Fields::init();

  $controllers = new CM_Controller(); // extends wc/v3 api rest
  $controllers->init();

  $droopClass = new Coordinadora_WC_Droop_Shipping();
  $droopClass->init();

  function coordinadora_wc_shipping_method()
  {
    if (!class_exists('WC_Coordinadora_Shipping_Method')) {
      require_once(__DIR__ . '/includes/Coordinadora_WC_Shipping_Method.php');
    }
    if (!class_exists('Coordinadora_WC_Shipping_Metho_Same_Day_Delivery')) {
      require_once(__DIR__ . '/includes/Coordinadora_WC_Shipping_Metho_Same_Day_Delivery.php');
    }
  }

  add_action('woocommerce_shipping_init', 'coordinadora_wc_shipping_method');

  function coordinadora_wc_add_shipping_method($methods)
  {
    $methods['coordinadora'] = 'WC_Coordinadora_Shipping_Method';
    $methods['Coordinadora_WC_Shipping_Metho_Same_Day_Delivery'] = 'Coordinadora_WC_Shipping_Metho_Same_Day_Delivery';
    return $methods;
  }

  add_action('woocommerce_shipping_methods', 'coordinadora_wc_add_shipping_method');

  function enable()
  {
    Coordinadora_WP_Dashboard::enable();
  }

  function disable()
  {
    Coordinadora_WP_Dashboard::disable();
  }

  function plugin_action_links($links)
  {
    $plugin_links = array();
    $plugin_links[] = '<a href="' . admin_url('admin.php?page=wc-settings&tab=shipping&section=coordinadora') . '">' . 'Ajustes' . '</a>';
    return array_merge($plugin_links, $links);
  }

  add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'plugin_action_links');

  add_action('woocommerce_order_status_processing', 'Coordinadora_WP_Dashboard::notify', 10, 3);
} else {
  echo '<h1>WooCommerce debe estar activado.</h1>';
}
