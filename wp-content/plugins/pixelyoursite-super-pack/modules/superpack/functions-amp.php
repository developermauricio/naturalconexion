<?php

namespace PixelYourSite\SuperPack;

use PixelYourSite;
use URL;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * @link https://www.ampproject.org/docs/reference/components/amp-analytics
 * @link https://www.ampproject.org/docs/analytics/analytics-vendors
 * @link https://github.com/ampproject/amphtml/blob/master/extensions/amp-analytics/0.1/vendors.js
 */

/**
 * Check if AMP by Automattic plugin activated.
 *
 * @link https://wordpress.org/plugins/amp/
 *
 * @return bool
 */
function isAMPactivated() {
	
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	
	return is_plugin_active( 'amp/amp.php' );
}

/**
 * Check if AMP for WP plugin activated.
 *
 * @link https://wordpress.org/plugins/accelerated-mobile-pages/
 *
 * @return bool
 */
function isAMPforWPactivated() {
	
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	
	return is_plugin_active( 'accelerated-mobile-pages/accelerated-moblie-pages.php' );
	
}

if ( PixelYourSite\SuperPack()->configured() && PixelYourSite\SuperPack()->getOption( 'amp_enabled' ) ) {
	
	if ( isAMPactivated() || isAMPforWPactivated() ) {
		add_filter( 'pys_page_url_triggers', 'PixelYourSite\SuperPack\modifyURLtriggers' );
		add_action( 'amp_post_template_footer', 'PixelYourSite\SuperPack\outputEvents' );
	}
	
	if ( isAMPactivated() ) {
		add_filter( 'amp_post_template_data', 'PixelYourSite\SuperPack\ampforwpRegisterAnalyticsComponent', 9 );
	}
	
	if ( isAMPforWPactivated() ) {
		add_filter( 'amp_post_template_data', 'PixelYourSite\SuperPack\ampforwpRegisterAnalyticsComponent', 21 );
	}
	
}

/**
 * Adds '/amp' suffix to "strict" rules
 *
 * @return array
 */
function modifyURLtriggers( $triggers ) {
	
	$un = new URL\Normalizer();
	
	foreach ( $triggers as $key => $trigger ) {
		
		if ( $trigger['rule'] != 'match' ) {
			continue;
		}
		
		$un->setUrl( $trigger['value'] );
		$url  = $un->normalize();
		$components = parse_url( $url );
		
		$amp_url = '';
		
		if ( isset( $components['host'] ) ) {
			$amp_url = $components['host'];
		}
		
		if ( isset( $components['scheme'] ) ) {
			$amp_url = $components['scheme'] . '://' . $amp_url;
		}
		
		if ( isset( $components['path'] ) ) {
			$amp_url .= $components['path'];
		}
		
		$amp_url = untrailingslashit( $amp_url ) . '/amp/';
		
		if ( isset( $components['query'] ) ) {
			$amp_url .= '?' . $components['query'];
		}
		
		$triggers[ $key ]['value'] = $amp_url;
		
	}
	
	return $triggers;
	
}

function ampforwpRegisterAnalyticsComponent( $data ) {
	
	if ( PixelYourSite\GA()->configured() ) {
		if ( empty( $data['amp_component_scripts']['amp-analytics'] ) ) {
			$data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
		}
	}
	
	return $data;

}

function outputEvents() {
	
	if ( PixelYourSite\isDisabledForCurrentRole() ) {
		return;
	}
	
	$eventsManager = PixelYourSite\PYS()->getEventsManager();
	$eventsManager->doingAMP = true;
	$eventsManager->setupEventsParams();
	
	if ( PixelYourSite\Facebook()->configured() && ! apply_filters( 'pys_pixel_disabled', false, 'facebook' ) ) {
		
		foreach ( $eventsManager->getStaticEvents( 'facebook' ) as $eventName => $events ) {
			foreach ( $events as $event ) {
				foreach ( PixelYourSite\Facebook()->getPixelIDs() as $pixelID ) {
					renderFacebookTag( $pixelID, $eventName, $event['params'] );
				}
			}
		}
		
	}
	
	if ( PixelYourSite\GA()->configured() && ! apply_filters( 'pys_pixel_disabled', false, 'ga' ) ) {
		
		foreach ( $eventsManager->getStaticEvents( 'ga' ) as $eventName => $events ) {
			foreach ( $events as $event ) {
				foreach ( PixelYourSite\GA()->getPixelIDs() as $pixelID ) {
					renderGoogleAnalyticsTag( $pixelID, $eventName, $event['params'] );
				}
			}
		}
		
	}
	
	if ( PixelYourSite\Pinterest()->configured() && ! apply_filters( 'pys_pixel_disabled', false, 'pinterest' ) ) {
		
		foreach ( $eventsManager->getStaticEvents( 'pinterest' ) as $eventName => $events ) {
			foreach ( $events as $event ) {
				foreach ( PixelYourSite\Pinterest()->getPixelIDs() as $pixelID ) {
					renderPinterestTag( $pixelID, $eventName, $event['params'] );
				}
			}
		}
		
	}
	
}

function renderPinterestTag( $pixelID, $eventName, $params ) {
	
	$args = array(
		'tid'      => $pixelID,
		'event'    => urlencode( $eventName ),
		'noscript' => 1,
	);
	
	foreach ( $params as $param => $value ) {
		@$args[ 'ed[' . $param . ']' ] = urlencode( $value );
	}
	
	printf( '<amp-pixel src="%s"></amp-pixel>', add_query_arg( $args, 'https://ct.pinterest.com/v3' ) );
	
}

/**
 * @link https://www.ampproject.org/docs/reference/components/amp-analytics
 * @link https://www.ampproject.org/docs/analytics/deep_dive_analytics
 * @link https://github.com/ampproject/amphtml/blob/b09564bc6092e9ec6ae636582f921774f426b09f/examples/analytics-vendors.amp.html#L420
 */
function renderFacebookTag( $pixelID, $eventName, $params ) {

	$args = array(
		'id'       => $pixelID,
		'ev'       => urlencode( $eventName ),
		'noscript' => 1,
		'rand'     => 'RANDOM',
		'mobile'   => 'amp',
	);
	
	foreach ( $params as $param => $value ) {
		@$args[ 'cd[' . $param . ']' ] = urlencode( $value );
	}
	
	printf( '<amp-pixel src="%s"></amp-pixel>', add_query_arg( $args, 'https://www.facebook.com/tr' ) );
	
}

/**
 * @link https://www.ampproject.org/docs/reference/components/amp-analytics
 * @link https://www.ampproject.org/docs/analytics/deep_dive_analytics
 * @link https://github.com/ampproject/amphtml/blob/b09564bc6092e9ec6ae636582f921774f426b09f/examples/analytics-vendors.amp.html#L478
 */
function renderGoogleAnalyticsTag( $pixelID, $eventName, $params ) {
	
	$trigger = 'track' . $eventName;
	$request = $eventName == 'PageView' ? 'pageview' : 'event';
	
	$vars = array(
		'eventCategory' => isset( $params['event_category'] ) ? $params['event_category'] : '',
		'eventAction'   => $eventName,
		'eventLabel'    => isset( $params['event_label'] ) ? $params['event_label'] : '',
		'eventValue'    => isset( $params['value'] ) ? $params['value'] : '',
	);
	
	$args = array(
		'vars'     => array(
			'account'     => $pixelID,
			'anonymizeIP' => PixelYourSite\GA()->getOption( 'anonimize_ip' ),
		),
		'triggers' => array(
			$trigger => array(
				'on'      => 'visible',
				'request' => $request,
				'vars'    => $vars,
			),
		),
	);
	
	printf( '<amp-analytics type="googleanalytics"><script type="application/json">%s</script></amp-analytics>',
		json_encode( $args ) );
	
}