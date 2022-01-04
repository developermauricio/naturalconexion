<?php

/**
 * Método de envío para Coordinadora entrega el mismo día
 */
class Coordinadora_WC_Shipping_Metho_Same_Day_Delivery extends WC_Shipping_Method
{
  /**
   * @access public
   * @return void
   */
  public function __construct()
  {
    $this->id                 = 'coordinadora_same_day_delivery';
    $this->title              = __('Coordinadora (Entrega mismo día)');
    $this->method_title       = __('Coordinadora (Entrega mismo día)');
    $this->method_description = __('Método de envío Coordinadora.');
    $this->enabled            = $this->get_option('enabled');

    $shippingSettings = get_option('woocommerce_coordinadora_settings');
    $this->api_key            = isset($shippingSettings['api_key'])  && $shippingSettings['api_key'] ? $shippingSettings['api_key'] : "";

    $this->init();
  }

  /**
   * Init your settings
   *
   * @access public
   * @return void
   */
  function init()
  {
    // Load the settings API
    $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
    $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

    // Save settings in admin if you have any defined
    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
  }

  function init_form_fields()
  {
    $this->form_fields = array(
      'enabled' => array(
        'title'       => __('Activo', 'coordinadora'),
        'type'        => 'checkbox',
        'description' => __('Activa este método de envío.', 'coordinadora'),
        'default'     => 'no',
        'desc_tip' => true
      ),
    );
  }

  public function is_available($package)
  {
    return $this->enabled === 'yes' && !empty($this->api_key);
  }

  /**
   * calculate_shipping function.
   *
   * @access public
   * @param mixed $package
   * @return void
   */
  public function calculate_shipping($package = array())
  {
    $url_carrier = 'https://wc-backend-dot-cm-integraciones.uk.r.appspot.com/api/coordinadoraWs/calculateSameDayShipping';

    $cm_shipping = new Coordinadora_WC_Shipping();
    try {
      $rate = $cm_shipping->calculate_shipping($this->title, $package, $url_carrier, $this->api_key);

      if ($rate !== false) {
        $this->add_rate($rate);
      }
    } catch (Exception $exception) {
      $cm_shipping->addLog($exception->getMessage());
    }
  }
}
