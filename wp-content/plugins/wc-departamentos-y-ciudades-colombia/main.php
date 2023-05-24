<?php
/**
 * @package         QCode - Departamentos y Ciudades de Colombia para Woocommerce
 * @author          QCode
 * @version         1.0.6
 *
 * @wordpress-plugin
 * Plugin name:     QCode - Departamentos y Ciudades de Colombia para Woocommerce
 * Description:     Plugin para mostrar el campo departamento y ciudad como listas de selección. Compatible con el plugin de Coordinadora.
 * Author:          QCode
 * Version:         1.0.6
 * Author URI:      https://qcode.co/
 * WC tested up to: 6.1.0
 * WC requires at least: 5.6.0
 */

if (!defined('ABSPATH')) exit;

if (in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {

  /** Places Coordinadora */
  require_once( 'includes/DYCDCPWC_Woo_Places_Class.php' );
  $GLOBALS['wc_states_places'] = new DYCDCPWC_Woo_Places_Class(__FILE__);

  function DYCDCPWC_woo_default_address_fields( $fields ) {
    if ($fields['city']['priority'] < $fields['state']['priority']){
      $state_priority = $fields['state']['priority'];
      $fields['state']['priority'] = $fields['city']['priority'];
      $fields['city']['priority'] = $state_priority;
    }
    $fields['postcode']['required'] = false;
    return $fields;
  }
  add_filter( 'woocommerce_default_address_fields', 'DYCDCPWC_woo_default_address_fields' );


	add_action('woocommerce_checkout_update_order_meta', function( $order_id ) {
    $order = new WC_Order( $order_id );
    $destinationCity = $order->get_billing_city();
	  $destinationCityShipping= $order->get_shipping_city();
    $cityParsed = substr($destinationCity, 0, strlen($destinationCity) - 11);
    $cityParsedShipping = substr($destinationCityShipping, 0, strlen($destinationCityShipping) - 11);
    update_post_meta( $order_id, '_billing_city', $cityParsed );
	  update_post_meta( $order_id, '_shipping_city', $cityParsedShipping );
  } , 10, 2);

}
