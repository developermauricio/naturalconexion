<?php

namespace ADP\BaseVersion\Includes\SpecialStrategies;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

class GeoLocationStrategy
{
    /**
     * API endpoints for geolocating an IP address
     *
     * @var array
     */
    private static $geoipApis = array(
        'ipinfo.io'  => 'https://ipinfo.io/%s/json',
        'ip-api.com' => 'http://ip-api.com/json/%s',
    );

    /**
     * Geolocate an IP address.
     *
     * @param string $ipAddress IP Address.
     *
     * @return array
     */
    public static function geoLocateIp($ipAddress = '')
    {
        $geolocation = apply_filters('wdp_geolocate_ip', false, $ipAddress);

        if (false !== $geolocation
            && isset($geolocation["country"]) && ! empty($geolocation["country"])
            && isset($geolocation["state"]) && ! empty($geolocation["state"])) {
            return array(
                'country'  => $geolocation["country"],
                'state'    => $geolocation["state"],
                'city'     => '',
                'postcode' => '',
            );
        }

        if (empty($ipAddress)) {
            $ipAddress = \WC_Geolocation::get_external_ip_address();
        }

        return self::geoLocateViaApi($ipAddress);
    }


    /**
     * Use APIs to Geolocate the user.
     *
     * Geolocation APIs can be added through the use of the woocommerce_geolocation_geoip_apis filter.
     * Provide a name=>value pair for service-slug=>endpoint.
     *
     * If APIs are defined, one will be chosen at random to fulfil the request. After completing, the result
     * will be cached in a transient.
     *
     * @param string $ipAddress IP address.
     *
     * @return array
     */
    private static function geoLocateViaApi($ipAddress)
    {
        $geolocation = get_transient('wdp_geoip_' . $ipAddress);
        if (false === $geolocation) {
            $geolocation   = array();
            $geoIpServices = apply_filters('woocommerce_geolocation_geoip_apis', self::$geoipApis);

            if (empty($geoIpServices)) {
                return array();
            }

            $geoIpServicesKeys = array_keys($geoIpServices);

            shuffle($geoIpServicesKeys);

            foreach ($geoIpServicesKeys as $serviceName) {
                $serviceEndpoint = $geoIpServices[$serviceName];
                $response        = wp_safe_remote_get(sprintf($serviceEndpoint, $ipAddress), array('timeout' => 2));

                if ( ! is_wp_error($response) && $response['body']) {
                    switch ($serviceName) {
                        case 'ip-api.com':
                        case 'ipinfo.io':
                            $data = json_decode($response['body']);
                            break;
                        default:
                            $geolocation = apply_filters(
                                'woocommerce_geolocation_geoip_response_' . $serviceName,
                                '',
                                $response['body']
                            );
                            break;
                    }
                    $states      = include WC()->plugin_path() . '/i18n/states.php';
                    $countryCode = $data->countryCode ?? '';
                    $city        = $data->city ?? '';
                    $postcode    = $data->zip ?? '';

                    if ($countryCode) {
                        break;
                    }
                }
            }
            $geolocation['country'] = sanitize_text_field(strtoupper($countryCode));
            $geolocation['city']    = sanitize_text_field($city);
            if ( ! empty($states) && isset($states[$countryCode])) {
                $country_cities = array_flip($states[$countryCode]);
                $state          = $country_cities[$geolocation['city']] ?? "";
            }
            $geolocation['state']    = ( ! empty($state)) ? $state : "";
            $geolocation['postcode'] = sanitize_text_field($postcode);
            set_transient('wdp_geoip_' . $ipAddress, $geolocation, WEEK_IN_SECONDS);
        }

        return $geolocation;
    }
}
