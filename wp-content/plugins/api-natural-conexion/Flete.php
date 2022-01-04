<?php

class Flete
{

    private $rules;
    private $cities;
    private $citiesTxt;
    private $supper = null;
    private $zoneId = null;

    public function setSettings($cities, $rules)
    {
        $this->setRules($rules);
        $this->setCities($cities);
    }

    public function setCities($cities)
    {
        $this->cities = $cities;
        $this->getCite();
    }

    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    private function getCite()
    {
        $this->citiesTxt = '_';
        foreach ($this->cities as $cities) {
            $this->citiesTxt .= $cities . '_';
        }
        $this->citiesTxt = strtolower($this->citiesTxt);
    }

    public function findSuper(&$zone, $city, $state)
    {
        global $wpdb;

        $this->findByCity($wpdb, $city);
        $this->findByState($wpdb, $state);
        $this->findByCountry($wpdb);

        $zone = $this->zoneId;

        return $this->supper;
    }

    public function findByCountry($wpdb, $country = 'CO')
    {
        if ($this->zoneId) {
            return;
        }

        $zones = $wpdb->get_results($wpdb->prepare(
            "SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones 
            WHERE zone_id IN (
                SELECT zone_id 
                FROM {$wpdb->prefix}woocommerce_shipping_zone_methods 
                WHERE is_enabled = 1 AND zone_id in (
                    SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zone_locations 
                    WHERE location_type='country' 
                    AND location_code = %s
                )
            )
            ORDER BY zone_order DESC;",
            $country
        ));

        foreach ($zones as $zone) {
            $this->zoneId = $zone->zone_id;
            return;
        }
    }

    public function findByState($wpdb, $state)
    {
        if ($this->zoneId) {
            return;
        }

        $zones = $wpdb->get_results($wpdb->prepare(
            "SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones 
            WHERE zone_id IN (
                SELECT zone_id 
                FROM {$wpdb->prefix}woocommerce_shipping_zone_methods 
                WHERE is_enabled = 1 AND zone_id in ( 
                    SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zone_locations 
                    WHERE location_type='state' 
                    AND location_code LIKE %s 
                )
            )
            ORDER BY zone_order;",
            "%:{$state}"
        ));

        foreach ($zones as $zone) {
            $this->zoneId = $zone->zone_id;
            return;
        }
    }

    public function findByCity($wpdb, $city)
    {
        $postcode_locations = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->prefix}options WHERE option_name LIKE 'woocommerce_super_shipping_%_settings' AND option_value LIKE %s;",
            "%{$city}%"
        ));

        foreach ($postcode_locations as $location) {
            $instance_id = str_replace('woocommerce_super_shipping_', '', $location->option_name);
            $instance_id = str_replace('_settings', '', $instance_id);

            $exists =  $wpdb->get_row($wpdb->prepare("SELECT zone_id 
            FROM {$wpdb->prefix}woocommerce_shipping_zone_methods 
            WHERE is_enabled = 1 
            AND instance_id = %d 
            ORDER BY method_order ASC 
            LIMIT 1;", $instance_id));

            $this->supper = new WooCommerce_Super_Shipping($instance_id);

            if ($exists && $exists->zone_id) {
                $this->zoneId = $exists->zone_id;
            }
            return;
        }
    }

    public function calculate($total = 1, $city = '')
    {
        $total  = intval($total);
        $city   = '_' . strtolower($city) . '_';
        $cost_t = 0;
        if (strpos($this->citiesTxt, $city) !== false) {
            foreach ($this->rules as $rule) {
                $min  = intval($rule['range']['min']);
                $max  = intval($rule['range']['max']);
                $cost = intval($rule['cost']);

                if ($cost_t < $cost) {
                    $cost_t = $cost;
                }

                if ($min <= $total && $max >= $total) {
                    return $cost;
                }
            }
        } else {
            throw new Exception("La ciudad no está disponible es esta zona.", 1);
        }

        return $cost_t;
    }
}
