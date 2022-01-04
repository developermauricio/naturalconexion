<?php
class Coordinadora_WC_Droop_Shipping
{
  private $droopApiKey;
  private $droopCustomLogoSrc;
  private $droopEnabled;


  public function __construct()
  {
    $shippingSettings = get_option('woocommerce_coordinadora_settings');
    if (isset($shippingSettings['droop_api_key']) &&  isset($shippingSettings['droop_custom_logo_url'])) {
      $this->droopApiKey = $shippingSettings['droop_api_key'];
      $this->droopCustomLogoSrc = $shippingSettings['droop_custom_logo_url'];
      $this->droopEnabled = isset($shippingSettings['droop_enabled']) ? $shippingSettings['droop_enabled'] : 'no';
    }
  }

  public function init()
  {
    add_action('wp_enqueue_scripts', array($this, 'add_coordinadora_wc_droop_js'));
    add_action('woocommerce_form_field_text', array($this, 'coordinadora_wc_add_droop_form_field'), 10, 4);
  }

  public function add_coordinadora_wc_droop_js()
  {
    wp_register_script(
      'droop',
      plugins_url('../assets/js/droop.js', __FILE__),
      array('jquery')
    );

    wp_enqueue_script('droop');

    wp_localize_script(
      'droop',
      'coordinadoraShippingSettings',
      array(
        'droopApiKey' => $this->droopApiKey,
        'droopEnabled' => $this->droopEnabled
      )
    );
  }

  public function coordinadora_wc_add_droop_form_field($field, $key, $args, $value)
  {
    if (is_checkout() && isset($this->droopEnabled) && $this->droopEnabled == "yes" && $this->droopApiKey && isset($this->droopCustomLogoSrc) && ($key == 'billing_address_1' || $key == 'shipping_address_1')) {
      $field .= '
          <p class="form-row">
            <label for="wc-droop-coordinadora">Selecciona un punto Droop Coordinadora para la entrega (opcional)</label>
            <puntos-drop id="wc-droop-coordinadora" entry-parameter="' . $this->droopApiKey . '" logo-comercio="' . $this->droopCustomLogoSrc . '"></puntos-drop>
          <p>';
    }
    return $field;
  }
}
