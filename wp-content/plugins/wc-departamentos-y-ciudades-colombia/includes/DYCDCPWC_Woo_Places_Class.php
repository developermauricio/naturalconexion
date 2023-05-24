<?php

  class DYCDCPWC_Woo_Places_Class {

    private static $places;
    public static $file;
    public static $plugin_path;
    public static $plugin_url;

    public function __construct( $file ) {
      self::$file = $file;
      add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    public function init() {
      $this->init_states();
      $this->init_places();
    }

    public function init_states() {
      add_filter( 'woocommerce_states', array( $this, 'wc_states' ),  );
    }

    /**
    * Implement WC States
    * @param mixed $states
    * @return mixed
    */
    public function wc_states( $states ) {
      $allowed = self::get_store_allowed_countries();
      if (!empty($allowed)) {
        foreach ($allowed as $code => $country) {
          if (file_exists(self::get_plugin_path() . '/assets/places/' . $code . '-states.php') ) {
            include(self::get_plugin_path() . '/assets/places/' . $code . '-states.php');
          }
        }
      }
      return $states;
    }

    public function init_places() {
      add_filter( 'woocommerce_billing_fields', array( $this, 'wc_billing_fields' ), 10, 2 );
      add_filter( 'woocommerce_shipping_fields', array( $this, 'wc_shipping_fields') , 10, 2 );
      add_filter( 'woocommerce_form_field_city', array( $this, 'wc_form_field_city' ), 10, 4 );
      add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
    }

    public function wc_billing_fields( $fields, $country ) {
      $fields['billing_city']['type'] = 'city';
      return $fields;
    }

    public function wc_shipping_fields( $fields, $country ) {
      $fields['shipping_city']['type'] = 'city';
      return $fields;
    }

    public function wc_form_field_city( $field, $key, $args, $value ) {
      if ( ( !empty( $args['clear'] ) ) ) {
        $after = '<div class="clear"></div>';
      } else {
        $after = '';
      }
      if ( $args['required'] ) {
        $args['class'][] = 'validate-required';
        $requried = '<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>>';
      } else {
        $requried = '';
      }
      $custom_attributes = array();
      if ( !empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
        foreach ($args['custom_attributes'] as $attribute => $attribute_value) {
          $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
        }
      }
      if ( !empty( $args['validate'] ) ) {
        foreach ($args['validate'] as $validate) {
          $args['class'][] = 'validate-' . $validate;
        }
      }
      $field  = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" data-priority="'.esc_attr__($args['priority']).'"  id="' . esc_attr( $args['id'] ) . '_field">';
      if ( $args['label'] ) {
        $field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) .'">' . $args['label'] . '</label>';
      }
      $country_key = $key == 'billing_city' ? 'billing_country' : 'shipping_country';
      $current_cc  = WC()->checkout->get_value( $country_key );
      $state_key = $key == 'billing_city' ? 'billing_state' : 'shipping_state';
      $current_sc  = WC()->checkout->get_value( $state_key );
      $places = self::get_places( $current_cc );
      if ( is_array( $places ) ) {
        $field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="city_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '">';
        $field .= '<option value="">'. __( 'Select an option&hellip;', 'woocommerce' ) .'</option>';
        if ( $current_sc ) {
          $dropdown_places = $places[ $current_sc ];
        } else if ( is_array($places) &&  isset($places[0])) {
          // $dropdown_places = array_reduce( $places, 'array_merge', array() );
          sort( $dropdown_places );
        } else {
          $dropdown_places = $places;
        }
        if ( is_array( $dropdown_places ) ) {
          foreach ( $dropdown_places as $places_key => $city_name ) {
            if(!is_array($city_name)) {
              $field .= '<option value="' . esc_attr( $places_key ) . '" ' . selected( $value, $places_key, false ) . '>' . $city_name .'</option>';
            }
          }
        }
        $field .= '</select>';
      } else {
        $field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
      }
      if ( $args['description'] ) {
        $field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
      }
      $field .= '</p>' . $after;
      return $field;
    }

    public function get_places( $p_code = null ) {
      if ( empty( self::$places ) ) {
        self::load_country_places();
      }

      if ( !is_null( $p_code ) ) {
        return isset( self::$places[ $p_code ] ) ? self::$places[ $p_code ] : false;
      } else {
        return self::$places;
      }

    }

    public function load_country_places() {
      global $places;
      $allowed = self::get_store_allowed_countries();
      if ( $allowed ) {
        foreach ($allowed as $code => $country) {
          if ( !isset( $places[$code] ) && file_exists( self::get_plugin_path() . '/assets/places/' . $code . '-cities.php' ) ) {
            include( self::get_plugin_path() . '/assets/places/' . $code . '-cities.php' );
          }
        }
      }
      self::$places = $places;
    }

    public function load_scripts() {
      if ( is_cart() || is_checkout() || is_wc_endpoint_url( 'edit-address' ) ) {
        $city_select_path = self::get_plugin_url() . 'assets/js/place-select.js';
        wp_enqueue_script( 'wc-city-select', $city_select_path, array( 'jquery', 'woocommerce' ), null, true );
        $places = json_encode( self::get_places() );
        wp_localize_script( 'wc-city-select', 'wc_city_select_params', array(
          'cities' => $places,
          'i18n_select_city_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' )
        ) );
      }
    }

    private function get_plugin_path() {
      if ( isset( self::$plugin_path ) ) {
        return self::$plugin_path;
      }
      $path = self::$plugin_path = plugin_dir_path( self::$file );
      return untrailingslashit( $path );
    }

    public function get_plugin_url() {
      if ( isset( self::$plugin_url ) ) {
        return self::$plugin_url;
      }
      return self::$plugin_url = plugin_dir_url( self::$file );
    }

    private function get_store_allowed_countries() {
      return array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );
    }

  }
