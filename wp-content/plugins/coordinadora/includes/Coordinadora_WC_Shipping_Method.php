<?php
require_once(__DIR__ . '/Coordinadora_WC_Shipping.php');
/**
 * Método de envío para Coordinadora
 */
class WC_Coordinadora_Shipping_Method extends WC_Shipping_Method
{
  /**
   * @access public
   * @return void
   */
  public function __construct()
  {
    $this->id                 = 'coordinadora';
    $this->title              = __('Coordinadora');
    $this->method_title       = __('Coordinadora');
    $this->method_description = __('Método de envío Coordinadora.');
    $this->enabled            = $this->get_option('enabled');
    $this->api_key            = $this->get_option('api_key');
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
      'api_key' => array(
        'title'       => __('API Key', 'coordinadora'),
        'type'        => 'text',
        'description' => __('API Key provisto por Coordinadora', 'coordinadora'),
        'default'     => '',
        'custom_attributes' => array(
          'autocomplete' => 'off',
          'required'      => 'true'
        ),
        'desc_tip' => true
      ),
      'droop_enabled' => array(
        'title'       => __('Puntos Droop activo', 'coordinadora'),
        'type'        => 'checkbox',
        'description' => __('Activa puntos droop en el checkout.', 'coordinadora'),
        'default'     => 'no',
        'desc_tip' => true
      ),
      'droop_api_key' => array(
        'title'       => __('Droop API Key', 'coordinadora'),
        'type'        => 'text',
        'description' => __('Droop API Key provisto por Coordinadora', 'coordinadora'),
        'default'     => '',
        'desc_tip'    => true,
        'custom_attributes' => array(
          'autocomplete' => 'off',
        )
      ),
      'droop_custom_logo_url' => array(
        'title'       => __('URL del logo del Droop', 'coordinadora'),
        'type'        => 'text',
        'description' => __('URL del logo para mostrar en la ventana del Droop de Coordinadora.', 'coordinadora'),
        'default'     => '',
        'desc_tip'    => true,
        'custom_attributes' => array(
          'autocomplete' => 'off',
        )
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
    $url_carrier = 'https://wc-backend-dot-cm-integraciones.uk.r.appspot.com/api/coordinadoraWs/CalculateShipping';

    $cm_shipping = new Coordinadora_WC_Shipping();
    try {
      $rate = $cm_shipping->calculate_shipping($this->title, $package, $url_carrier, $this->settings['api_key']);

      if ($rate !== false) {
        $this->add_rate($rate);
      }
    } catch (Exception $exception) {
      $cm_shipping->addLog($exception->getMessage());
    }
  }
}
