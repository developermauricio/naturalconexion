<?php
/*
Plugin Name: API_NATURAL_CONEXION
Plugin URI: /
Description: Api
Version: 0.1
Author: NATURAL_CONEXION
Author URI: /
Text Domain: NATURAL_CONEXION
License: GPLv2
*/

use \Automattic\WooCommerce\Admin\API\Reports\Products\DataStore as ProductsDataStore;

include("Flete.php");

/**
 * Grab latest post title by an author!
 *
 * @param array $data Options for the function.
 * @return string|null Post title for the latest, * or null if none.
 */
function my_awesome_func($data)
{
    $zones = WC_Shipping_Zones::get_zones();
    return wp_send_json($zones);
}

function zones($data)
{
    $data_store = WC_Data_Store::load('shipping-zone');
    $raw_zones  = $data_store->get_zones();
    $zones      = array();

    $name = $data->get_param('name');
    $id   = $data->get_param('id');

    $response = [];

    foreach ($raw_zones as $raw_zone) {
        $zone                                = new WC_Shipping_Zone($raw_zone);
        $zones[$zone->get_id()]            = $zone->get_data();
        $zones[$zone->get_id()]['zone_id'] = $zone->get_id();

        if ($id && $zone->get_id() == $id) {
            return wp_send_json($zones[$zone->get_id()]);
        }
        if ($name && strpos($zones[$zone->get_id()]['zone_name'], $name)) {
            $response[] = $zones[$zone->get_id()];
        } else if (!$name) {
            $response[] = $zones[$zone->get_id()];
        }
    }

    return $response;
}

function zones_raw($data)
{
    $data_store = WC_Data_Store::load('shipping-zone');
    $raw_zones  = $data_store->get_zones();

    $name = $data->get_param('name');
    $id   = $data->get_param('id');

    $response = [];
    if ($id || $name) {
        foreach ($raw_zones as $zone) {
            if ($id && $zone->zone_id == $id) {
                return wp_send_json($zone);
            }
            if ($name && strpos($zone->zone_name, $name)) {
                $response[] = $zone;
            }
        }
    } else {
        $response = $raw_zones;
    }

    return wp_send_json($response);
}

function shipping_methods($data)
{
    $id   = $data->get_param('zone');
    if (!$id) {
        return wp_send_json(["msg" => "Falta enviar el id de la zona"], 401);
    }
    $exists = false;
    if ($id) {
        $data_store = WC_Data_Store::load('shipping-zone');
        $raw_zones  = $data_store->get_zones();
        foreach ($raw_zones as $zone) {
            if ($zone->zone_id == $id) {
                $exists = true;
            }
        }
    }
    if (!$exists) {
        return wp_send_json(["msg" => "Zona no valida"], 401);
    }
    $zone                                = new WC_Shipping_Zone($id);
    $response = [];
    foreach ($zone->get_shipping_methods(false, 'json') as $method) {
        $method = json_decode(json_encode($method));
        if ($method){
            unset($method->settings_html);
            unset($method->has_settings);
        }
        $response[] = $method;
    }
    return wp_send_json($response);
}

function total_flete($data)
{
    global $wpdb;

    $total  = $data->get_param('cantidad');
    $state   = $data->get_param('state');
    $city   = $data->get_param('city');
    $zoneId = null;


    if (!$city) {
        return wp_send_json(["msg" => "Falta el nombre de la ciudad"], 401);
    }

    if (!$total || !is_numeric($total)) {
        return wp_send_json(["msg" => "Falta ingresar el total de producto"], 401);
    }

    $flete = new Flete();
    $supper = $flete->findSuper($zoneId, $city, $state);

    $zone = new WC_Shipping_Zone($zoneId);
    $methods = $zone->get_shipping_methods(true, 'json');

    $free = new stdClass();
    $response = new stdClass();
    $response->free = null;

    try {
        if ($supper) {
            $config = $supper->instance_settings;
            $flete->setSettings(
                $config['colombian_cities'],
                $config['shipping_rules']
            );
        }

        foreach ($methods as $method) {
            if (!$supper && $method->id === 'super_shipping') {
                $config = $method->instance_settings;
                $flete->setSettings(
                    $config['colombian_cities'],
                    $config['shipping_rules']
                );
            } else if ($method->id ===  'free_shipping') {
                if ($method->requires !== '') {
                    $free->min_amount = $method->min_amount;
                    $free->requires = $method->requires;
                    $response->free = $free;
                }
            }
        }

        $response->total = $flete->calculate($total, $city);
    } catch (\Throwable $th) {
        return wp_send_json(['msg' => $th->getMessage()], 401);
    }
    return wp_send_json($response, 200);
}

function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *");
}

add_action('init','add_cors_http_header');

add_action('rest_api_init', function () {
    register_rest_route('api_natural/v1', '/zones', array(
        'methods' => 'GET',
        'callback' => 'my_awesome_func',
    ));
    register_rest_route('api_natural/v1', '/zones_locations', array(
        'methods' => 'GET',
        'callback' => 'zones',
    ));
    register_rest_route('api_natural/v1', '/only_zones', array(
        'methods' => 'GET',
        'callback' => 'zones_raw',
    ));
    register_rest_route('api_natural/v1', '/shipping_methods', array(
        'methods' => 'GET',
        'callback' => 'shipping_methods',
    ));
    register_rest_route('api_natural/v1', '/total_flete', array(
        'methods' => 'GET',
        'callback' => 'total_flete',
    ));
});
