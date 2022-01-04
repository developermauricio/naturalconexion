<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function maybeMigrate() {
	
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}
	
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$pys_7_version = get_option( 'pys_core_version', false );
	$pys_6_version = get_option( 'pys_fb_pixel_version', false );
	$v5_free = get_option( 'pixel_your_site' );


    if ($pys_7_version && version_compare($pys_7_version, '7.6.0', '<')) {
        migrate_7_6_0_main_defaults();

        update_option( 'pys_core_version', PYS_VERSION );
        update_option( 'pys_updated_at', time() );
    } elseif ($pys_7_version && version_compare($pys_7_version, '7.2.0', '<')) {

        migrate_7_2_0_bing_defaults();

        update_option( 'pys_core_version', PYS_VERSION );
        update_option( 'pys_updated_at', time() );

    } elseif ($pys_7_version && version_compare($pys_7_version, '7.1.3', '<')) {

        migrate_v7_1_3_google_ads_conversion_labels();

        update_option( 'pys_core_version', PYS_VERSION );
        update_option( 'pys_updated_at', time() );

    } elseif ( ! $pys_7_version && $pys_6_version) {
		// migrate from PRO 6.x
		
		migrate_v6_options();
		migrate_v6_facebook_events();
		
		update_orders_meta();

		update_option( 'pys_core_version', PYS_VERSION );
		update_option( 'pys_updated_at', time() );
	
	} elseif ( ! $pys_7_version && is_array( $v5_free ) ) {
		// migrate from FREE 5.x
		
		migrate_v5_free_options();
		migrate_v5_free_events();
		
		update_orders_meta();
		
		update_option( 'pys_core_version', PYS_VERSION );
		update_option( 'pys_updated_at', time() );
		
	} elseif ( ! $pys_7_version ) {
		// first install
		
		update_orders_meta();

		update_option( 'pys_core_version', PYS_VERSION );
		update_option( 'pys_updated_at', time() );
		
	}

    if ($pys_7_version && version_compare($pys_7_version, '8.0.0', '<') ) {
        migrate_8_0_0();

        update_option( 'pys_core_version', PYS_VERSION );
        update_option( 'pys_updated_at', time() );
    }

    if ($pys_7_version && version_compare($pys_7_version, '8.3.1', '<') ) {
        migrate_8_3_1();

        update_option( 'pys_core_version', PYS_VERSION );
        update_option( 'pys_updated_at', time() );
    }
	
}

function migrate_8_3_1() {
    $globalOptions = array(
        "enable_page_title_param"          => !PYS()->getOption( 'enable_remove_page_title_param' ,false),
        'enable_content_name_param'        => !PYS()->getOption( 'enable_remove_content_name_param' ,false),
    );

    PYS()->updateOptions($globalOptions);
}

function migrate_8_0_0() {

    $globalOptions = array(
        "signal_click_enabled"          => isEventEnabled( 'click_event_enabled' ),
        "signal_watch_video_enabled"    => isEventEnabled( 'watchvideo_event_enabled' ),
        "signal_adsense_enabled"        => isEventEnabled( 'adsense_enabled' ),
        "signal_form_enabled"           => isEventEnabled( 'form_event_enabled' ),
        "signal_user_signup_enabled"    => isEventEnabled( 'complete_registration_event_enabled' ),
        "signal_download_enabled"       => isEventEnabled( 'download_event_enabled' ),
        "signal_comment_enabled"        => isEventEnabled( 'comment_event_enabled' )
    );

    PYS()->updateOptions($globalOptions);

    $gaOptions = array(
        'woo_view_item_list_enabled' => GA()->getOption('woo_view_category_enabled')
    );
    GA()->updateOptions($gaOptions);
}


function migrate_7_6_0_main_defaults() {
    $disabled = array();
    if(PYS()->getOption("woo_order_purchase_status_wc-pending") == "0") {
        $disabled[]="wc-pending";
    }
    if(PYS()->getOption("woo_order_purchase_status_wc-processing") == "0") {
        $disabled[]="wc-processing";
    }
    if(PYS()->getOption("woo_order_purchase_status_wc-on-hold") == "0") {
        $disabled[]="wc-on-hold";
    }
    if(PYS()->getOption("woo_order_purchase_status_wc-completed") == "0") {
        $disabled[]="wc-completed";
    }
    if(PYS()->getOption("woo_order_purchase_status_wc-refunded") == "0") {
        $disabled[]="wc-refunded";
    }
    if(PYS()->getOption("woo_order_purchase_status_wc-failed") == "0") {
        $disabled[]="wc-failed";
    }

    PYS()->updateOptions(array("woo_order_purchase_disabled_status" => $disabled));
}

function migrate_7_2_0_bing_defaults() {

    $bing_defaults = array(
        'gdpr_bing_prior_consent_enabled' => true,
        'gdpr_cookiebot_bing_consent_category' => 'marketing',
    );

    // update settings
    PYS()->updateOptions( $bing_defaults );
    PYS()->reloadOptions();

}

function migrate_v7_1_3_google_ads_conversion_labels() {

    $events = [
        'woo_purchase',
        'woo_initiate_checkout',
        'woo_add_to_cart',
        'woo_view_content',
        'woo_view_category',
        'edd_purchase',
        'edd_initiate_checkout',
        'edd_add_to_cart',
        'edd_view_content',
        'edd_view_category',
    ];

    foreach ($events as $event) {
        $label = Ads()->getOption("{$event}_conversion_label");
        $id = Ads()->getOption("{$event}_conversion_id");

        if (empty($label) || empty($id)) {
            continue;
        }

        $option_key = "{$event}_conversion_labels";
        $options = array(
            $option_key => array($id => $label)
        );
        Ads()->updateOptions($options);
    }

}

function migrate_v6_facebook_events() {
	global $post;
	
	$query = new \WP_Query( array(
		'post_type' => 'pys_fb_event',
		'posts_per_page' => -1
	) );
	
	/**
	 * Dynamic events on v6 can has various types of triggers per event. Script collects common event params and
	 * creates new v7 event for each trigger type from source event.
	 */
	$customEvents = array();
	
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			
			/**
			 * Collect common event params: custom event state, Facebook event type and params
			 */
			$v6_state = get_post_meta( $post->ID, '_state', true ); // active/paused
			$v6_fb_props = get_post_meta( $post->ID, '_facebook_event_properties', true );
			$v6_fb_event_type = get_post_meta( $post->ID, '_facebook_event_type', true );
			
			if ( $v6_fb_event_type == 'CustomEvent' ) {
				$fb_event_type = 'CustomEvent';
				$fb_custom_event_type = $v6_fb_props['_custom_event_name'];
				unset( $v6_fb_props['_custom_event_name'] );
			} else {
				$fb_event_type = $v6_fb_event_type;
				$fb_custom_event_type = null;
			}
			
			if ( ! empty( $v6_fb_props['currency'] ) && $v6_fb_props['currency'] == 'custom' ) {
				$v6_fb_props['custom_currency'] = $v6_fb_props['_custom_currency'];
			}
			
			$fb_custom_params = array();
			$v6_fb_custom_props = get_post_meta( $post->ID, '_facebook_event_custom_properties', true );
			
			if ( is_array( $v6_fb_custom_props ) ) {
				foreach ( $v6_fb_custom_props as $v6_fb_custom_prop_name => $v6_fb_custom_prop_value ) {
					$fb_custom_params[] = array(
						'name'  => $v6_fb_custom_prop_name,
						'value' => $v6_fb_custom_prop_value,
					);
				}
			}
			
			$customEventCommonParams = array(
				'title'                      => $post->post_title,
				'enabled'                    => $v6_state == 'active',
				'delay'                      => null,
				'triggers'                   => array(),
				'url_filters'                => array(),
				'facebook_enabled'           => true,
				'facebook_event_type'        => $fb_event_type,
				'facebook_custom_event_type' => $fb_custom_event_type,
				'facebook_params_enabled'    => empty( $v6_fb_props ) && empty( $fb_custom_params ) ? false : true,
				'facebook_params'            => $v6_fb_props,
				'facebook_custom_params'     => $fb_custom_params,
			);
			
			/**
			 * Collect custom event triggers
			 */
			$v6_type = get_post_meta( $post->ID, '_type', true );  // on_page/dynamic
			
			if ( $v6_type == 'on_page' ) {
				
				$page_visit_triggers = array();
				$v6_triggers = get_post_meta( $post->ID, '_on_page_triggers', true );

				foreach ( $v6_triggers as $v6_trigger ) {
					
					if ( ! empty( $v6_trigger ) ) {
						
						$page_visit_triggers[] = array(
							'rule'  => 'contains',
							'value' => $v6_trigger,
						);
						
					}
					
				}
				
				$customEvent = $customEventCommonParams;
				$customEvent['delay'] = (int) get_post_meta( $post->ID, '_delay', true );
				$customEvent['trigger_type'] = 'page_visit';
				$customEvent['page_visit_triggers'] = $page_visit_triggers;

				$customEvents[] = $customEvent;
				
			} else {
				
				$triggers = array();
				$v6_triggers = get_post_meta( $post->ID, '_dynamic_triggers', true );
				
				// collect and group triggers by type
				foreach ( $v6_triggers as $v6_trigger ) {
					
					if ( ! empty( $v6_trigger ) ) {
					
						if ( $v6_trigger['type'] == 'url_click' ) {
							
							if ( ! empty( $v6_trigger['value'] ) ) {
								
								$triggers['url_click'][] = array(
									'rule'  => 'contains',
									'value' => $v6_trigger['value'],
								);
	
							}
							
						} elseif ( $v6_trigger['type'] == 'css_click' ) {
							
							if ( ! empty( $v6_trigger['value'] ) ) {
								
								$triggers['css_click'][] = array(
									'rule'  => null,
									'value' => $v6_trigger['value'],
								);
	
							}
							
						} elseif ( $v6_trigger['type'] == 'css_mouseover' ) {
							
							if ( ! empty( $v6_trigger['value'] ) ) {
								
								$triggers['css_mouseover'][] = array(
									'rule'  => null,
									'value' => $v6_trigger['value'],
								);
	
							}
							
						} elseif ( $v6_trigger['type'] == 'scroll_pos' ) {
							
							if ( ! empty( $v6_trigger['value'] ) ) {
								
								$triggers['scroll_pos'][] = array(
									'rule'  => null,
									'value' => $v6_trigger['value'],
								);
	
							}
							
						}

					}
					
				}
				
				// sanitize url filters
				$url_filters    = array();
				$v6_url_filters = get_post_meta( $post->ID, '_dynamic_url_filters', true );
				
				if ( is_array( $v6_url_filters ) ) {
					foreach ( $v6_url_filters as $v6_url_filter ) {
						
						if ( ! empty( $v6_url_filter ) ) {
							$url_filters[] = $v6_url_filter;
						}
						
					}
				}
				
				// create new custom event for each trigger type
				foreach ( $triggers as $trigger_type => $triggers_values ) {
					
					$customEvent  = $customEventCommonParams;
					$customEvent['trigger_type'] = $trigger_type;
					$customEvent[ $trigger_type . '_triggers'] = $triggers_values;
					$customEvent['url_filters'] = $url_filters;
					
					$customEvents[] = $customEvent;
				
				}
				
			}
			
		}
		
	}
	
	wp_reset_postdata();
	
	foreach ( $customEvents as $eventParams ) {
		CustomEventFactory::create( $eventParams );
	}
	
}

function migrate_v6_options() {
	global $wp_roles;
	
	/**
	 * CORE
	 */
	
	$v6_fb = get_option( 'pys_fb_pixel_pro', array() );
	
	$v7_core = array(
		'license_key'     => isset( $v6_fb['license_key'] ) ? $v6_fb['license_key'] : null,
		'license_status'  => isset( $v6_fb['license_status'] ) ? $v6_fb['license_status'] : null,
		'license_expires' => isset( $v6_fb['license_expires'] ) ? $v6_fb['license_expires'] : null,
		
		'gdpr_facebook_prior_consent_enabled' => isset( $v6_fb['gdpr_enable_before_consent'] ) ? $v6_fb['gdpr_enable_before_consent'] : null,
		'gdpr_cookiebot_integration_enabled'  => isset( $v6_fb['gdpr_cookiebot_enabled'] ) ? $v6_fb['gdpr_cookiebot_enabled'] : null,
		'custom_events_enabled'          => isset( $v6_fb['events_enabled'] ) ? $v6_fb['events_enabled'] : null,
		'woo_enabled'                         => isset( $v6_fb['woo_enabled'] ) ? $v6_fb['woo_enabled'] : null,
		'woo_add_to_cart_on_button_click'     => isset( $v6_fb['woo_add_to_cart_btn_enabled'] ) ? $v6_fb['woo_add_to_cart_btn_enabled'] : null,
		'woo_add_to_cart_on_cart_page'        => isset( $v6_fb['woo_add_to_cart_page_enabled'] ) ? $v6_fb['woo_add_to_cart_page_enabled'] : null,
		'woo_add_to_cart_on_checkout_page'    => isset( $v6_fb['woo_add_to_cart_checkout_enabled'] ) ? $v6_fb['woo_add_to_cart_checkout_enabled'] : null,
		'woo_event_value'                     => isset( $v6_fb['woo_event_value'] ) ? $v6_fb['woo_event_value'] : null,
		'woo_tax_option'                      => isset( $v6_fb['woo_tax_option'] ) ? $v6_fb['woo_tax_option'] : null,
		'woo_shipping_option'                 => isset( $v6_fb['woo_shipping_option'] ) ? $v6_fb['woo_shipping_option'] : null,
		'woo_ltv_order_statuses'              => isset( $v6_fb['woo_lifetime_value_order_statuses'] ) ? $v6_fb['woo_lifetime_value_order_statuses'] : null,
		'woo_purchase_on_transaction'         => isset( $v6_fb['woo_purchase_on_transaction'] ) ? $v6_fb['woo_purchase_on_transaction'] : null,
		'woo_purchase_value_option'           => isset( $v6_fb['woo_purchase_value_option'] ) ? $v6_fb['woo_purchase_value_option'] : null,
		'woo_purchase_value_percent'          => isset( $v6_fb['woo_purchase_value_percent'] ) ? $v6_fb['woo_purchase_value_percent'] : null,
		'woo_purchase_value_global'           => isset( $v6_fb['woo_purchase_value_global'] ) ? $v6_fb['woo_purchase_value_global'] : null,
		'woo_initiate_checkout_value_enabled' => isset( $v6_fb['woo_initiate_checkout_value_enabled'] ) ? $v6_fb['woo_initiate_checkout_value_enabled'] : null,
		'woo_initiate_checkout_value_option'  => isset( $v6_fb['woo_initiate_checkout_value_option'] ) ? $v6_fb['woo_initiate_checkout_value_option'] : null,
		'woo_initiate_checkout_value_percent' => isset( $v6_fb['woo_initiate_checkout_value_percent'] ) ? $v6_fb['woo_initiate_checkout_value_percent'] : null,
		'woo_initiate_checkout_value_global'  => isset( $v6_fb['woo_initiate_checkout_value_global'] ) ? $v6_fb['woo_initiate_checkout_value_global'] : null,
		'woo_add_to_cart_value_enabled'       => isset( $v6_fb['woo_add_to_cart_value_enabled'] ) ? $v6_fb['woo_add_to_cart_value_enabled'] : null,
		'woo_add_to_cart_value_option'        => isset( $v6_fb['woo_add_to_cart_value_option'] ) ? $v6_fb['woo_add_to_cart_value_option'] : null,
		'woo_add_to_cart_value_percent'       => isset( $v6_fb['woo_add_to_cart_value_percent'] ) ? $v6_fb['woo_add_to_cart_value_percent'] : null,
		'woo_add_to_cart_value_global'        => isset( $v6_fb['woo_add_to_cart_value_global'] ) ? $v6_fb['woo_add_to_cart_value_global'] : null,
		'woo_view_content_value_enabled'      => isset( $v6_fb['woo_view_content_value_enabled'] ) ? $v6_fb['woo_view_content_value_enabled'] : null,
		'woo_view_content_delay'              => isset( $v6_fb['woo_view_content_delay'] ) ? $v6_fb['woo_view_content_delay'] : null,
		'woo_view_content_value_option'       => isset( $v6_fb['woo_view_content_value_option'] ) ? $v6_fb['woo_view_content_value_option'] : null,
		'woo_view_content_value_percent'      => isset( $v6_fb['woo_view_content_value_percent'] ) ? $v6_fb['woo_view_content_value_percent'] : null,
		'woo_view_content_value_global'       => isset( $v6_fb['woo_view_content_value_global'] ) ? $v6_fb['woo_view_content_value_global'] : null,
		'woo_affiliate_value_enabled'         => isset( $v6_fb['woo_affiliate_value_enabled'] ) ? $v6_fb['woo_affiliate_value_enabled'] : null,
		'woo_affiliate_value_option'          => isset( $v6_fb['woo_affiliate_value_option'] ) ? $v6_fb['woo_affiliate_value_option'] : null,
		'woo_affiliate_value_global'          => isset( $v6_fb['woo_affiliate_value_global'] ) ? $v6_fb['woo_affiliate_value_global'] : null,
		'woo_affiliate_event_type'            => isset( $v6_fb['woo_affiliate_event_type'] ) ? $v6_fb['woo_affiliate_event_type'] : null,
		'woo_affiliate_custom_event_type'     => isset( $v6_fb['woo_affiliate_custom_event_type'] ) ? $v6_fb['woo_affiliate_custom_event_type'] : null,
		'woo_paypal_value_enabled'            => isset( $v6_fb['woo_paypal_value_enabled'] ) ? $v6_fb['woo_paypal_value_enabled'] : null,
		'woo_paypal_value_option'             => isset( $v6_fb['woo_paypal_value_option'] ) ? $v6_fb['woo_paypal_value_option'] : null,
		'woo_paypal_value_global'             => isset( $v6_fb['woo_paypal_value_global'] ) ? $v6_fb['woo_paypal_value_global'] : null,
		'woo_paypal_event_type'               => isset( $v6_fb['woo_paypal_event_type'] ) ? $v6_fb['woo_paypal_event_type'] : null,
		'woo_paypal_custom_event_type'        => isset( $v6_fb['woo_paypal_custom_event_type'] ) ? $v6_fb['woo_paypal_custom_event_type'] : null,
		
		'edd_enabled'                         => isset( $v6_fb['edd_enabled'] ) ? $v6_fb['edd_enabled'] : null,
		'edd_add_to_cart_on_button_click'     => isset( $v6_fb['edd_add_to_cart_btn_enabled'] ) ? $v6_fb['edd_add_to_cart_btn_enabled'] : null,
		'edd_add_to_cart_on_checkout_page'    => isset( $v6_fb['edd_add_to_cart_checkout_enabled'] ) ? $v6_fb['edd_add_to_cart_checkout_enabled'] : null,
		'edd_event_value'                     => isset( $v6_fb['edd_event_value'] ) ? $v6_fb['edd_event_value'] : null,
		'edd_tax_option'                      => isset( $v6_fb['edd_tax_option'] ) ? $v6_fb['edd_tax_option'] : null,
		'edd_ltv_order_statuses'              => isset( $v6_fb['edd_lifetime_value_order_statuses'] ) ? $v6_fb['edd_lifetime_value_order_statuses'] : null,
		'edd_purchase_on_transaction'         => isset( $v6_fb['edd_purchase_on_transaction'] ) ? $v6_fb['edd_purchase_on_transaction'] : null,
		'edd_purchase_value_option'           => isset( $v6_fb['edd_purchase_value_option'] ) ? $v6_fb['edd_purchase_value_option'] : null,
		'edd_purchase_value_percent'          => isset( $v6_fb['edd_purchase_value_percent'] ) ? $v6_fb['edd_purchase_value_percent'] : null,
		'edd_purchase_value_global'           => isset( $v6_fb['edd_purchase_value_global'] ) ? $v6_fb['edd_purchase_value_global'] : null,
		'edd_initiate_checkout_value_enabled' => isset( $v6_fb['edd_initiate_checkout_value_enabled'] ) ? $v6_fb['edd_initiate_checkout_value_enabled'] : null,
		'edd_initiate_checkout_value_option'  => isset( $v6_fb['edd_initiate_checkout_value_option'] ) ? $v6_fb['edd_initiate_checkout_value_option'] : null,
		'edd_initiate_checkout_value_percent' => isset( $v6_fb['edd_initiate_checkout_value_percent'] ) ? $v6_fb['edd_initiate_checkout_value_percent'] : null,
		'edd_initiate_checkout_value_global'  => isset( $v6_fb['edd_initiate_checkout_value_global'] ) ? $v6_fb['edd_initiate_checkout_value_global'] : null,
		'edd_add_to_cart_value_enabled'       => isset( $v6_fb['edd_add_to_cart_value_enabled'] ) ? $v6_fb['edd_add_to_cart_value_enabled'] : null,
		'edd_add_to_cart_value_option'        => isset( $v6_fb['edd_add_to_cart_value_option'] ) ? $v6_fb['edd_add_to_cart_value_option'] : null,
		'edd_add_to_cart_value_percent'       => isset( $v6_fb['edd_add_to_cart_value_percent'] ) ? $v6_fb['edd_add_to_cart_value_percent'] : null,
		'edd_add_to_cart_value_global'        => isset( $v6_fb['edd_add_to_cart_value_global'] ) ? $v6_fb['edd_add_to_cart_value_global'] : null,
		'edd_view_content_delay'              => isset( $v6_fb['edd_view_content_delay'] ) ? $v6_fb['edd_view_content_delay'] : null,
		'edd_view_content_value_enabled'      => isset( $v6_fb['edd_view_content_value_enabled'] ) ? $v6_fb['edd_view_content_value_enabled'] : null,
		'edd_view_content_value_option'       => isset( $v6_fb['edd_view_content_value_option'] ) ? $v6_fb['edd_view_content_value_option'] : null,
		'edd_view_content_value_percent'      => isset( $v6_fb['edd_view_content_value_percent'] ) ? $v6_fb['edd_view_content_value_percent'] : null,
		'edd_view_content_value_global'       => isset( $v6_fb['edd_view_content_value_global'] ) ? $v6_fb['edd_view_content_value_global'] : null,
	);
	

	foreach ( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ) as $post_type ) {
		
		// skip product post type when WC is active
		if ( isWooCommerceActive() && $post_type->name == 'product' ) {
			continue;
		}
		
		// skip download post type when EDD is active
		if ( isEddActive() && $post_type->name == 'download' ) {
			continue;
		}
	}
	
	// 'do_not_track_user_roles'
	foreach ( $wp_roles->roles as $role => $options ) {
		
		if ( isset( $v6_fb[ 'disable_for_' . $role ] ) && $v6_fb[ 'disable_for_' . $role ] ) {
			$v7_core['do_not_track_user_roles'][] = $role;
		}

	}
	
	// cleanup
	foreach ( $v7_core as $key => $value ) {
		if ( $value === null ) {
			unset( $v7_core[ $key ] );
		}
	}
	
	// update settings
	PYS()->updateOptions( $v7_core );
	PYS()->reloadOptions();
	
	/**
	 * FACEBOOK
	 */
	
	$v7_fb = array(
		'pixel_id'                            => isset( $v6_fb['pixel_id'] ) ? array( $v6_fb['pixel_id'] ) : null,
		'advanced_matching_enabled'           => isset( $v6_fb['advance_matching_enabled'] ) ? $v6_fb['advance_matching_enabled'] : null,
		'adsense_enabled'                     => isset( $v6_fb['adsense_enabled'] ) ? $v6_fb['adsense_enabled'] : null,
		'click_event_enabled'                 => isset( $v6_fb['click_event_enabled'] ) ? $v6_fb['click_event_enabled'] : null,
		'watchvideo_event_enabled'            => isset( $v6_fb['youtube_enabled'] ) ? $v6_fb['youtube_enabled'] : null,
		'search_event_enabled'                => isset( $v6_fb['search_event_enabled'] ) ? $v6_fb['search_event_enabled'] : null,
		'woo_variable_as_simple'              => isset( $v6_fb['woo_product_data'] ) ? $v6_fb['woo_product_data'] == 'main' : null,
		'woo_content_id'                      => isset( $v6_fb['woo_content_id'] ) ? $v6_fb['woo_content_id'] : null,
		'woo_content_id_prefix'               => isset( $v6_fb['woo_content_id_prefix'] ) ? $v6_fb['woo_content_id_prefix'] : null,
		'woo_content_id_suffix'               => isset( $v6_fb['woo_content_id_suffix'] ) ? $v6_fb['woo_content_id_suffix'] : null,
		'woo_content_id_logic'                => isset( $v6_fb['woo_content_id_format'] ) ? $v6_fb['woo_content_id_format'] : null,
		'woo_purchase_enabled'                => isset( $v6_fb['woo_purchase_enabled'] ) ? $v6_fb['woo_purchase_enabled'] : null,
		'woo_initiate_checkout_enabled'       => isset( $v6_fb['woo_initiate_checkout_enabled'] ) ? $v6_fb['woo_initiate_checkout_enabled'] : null,
		'woo_view_content_enabled'            => isset( $v6_fb['woo_view_content_enabled'] ) ? $v6_fb['woo_view_content_enabled'] : null,
		'woo_view_category_enabled'           => isset( $v6_fb['woo_view_category_enabled'] ) ? $v6_fb['woo_view_category_enabled'] : null,
		'woo_affiliate_enabled'               => isset( $v6_fb['woo_affiliate_enabled'] ) ? $v6_fb['woo_affiliate_enabled'] : null,
		'woo_paypal_enabled'                  => isset( $v6_fb['woo_paypal_enabled'] ) ? $v6_fb['woo_paypal_enabled'] : null,
		'edd_content_id'                      => isset( $v6_fb['edd_content_id'] ) ? $v6_fb['edd_content_id'] : null,
		'edd_content_id_prefix'               => isset( $v6_fb['edd_content_id_prefix'] ) ? $v6_fb['edd_content_id_prefix'] : null,
		'edd_content_id_suffix'               => isset( $v6_fb['edd_content_id_suffix'] ) ? $v6_fb['edd_content_id_suffix'] : null,
		'edd_purchase_enabled'                => isset( $v6_fb['edd_purchase_enabled'] ) ? $v6_fb['edd_purchase_enabled'] : null,
		'edd_initiate_checkout_enabled'       => isset( $v6_fb['edd_initiate_checkout_enabled'] ) ? $v6_fb['edd_initiate_checkout_enabled'] : null,
		'edd_add_to_cart_enabled'             => isset( $v6_fb['edd_add_to_cart_enabled'] ) ? $v6_fb['edd_add_to_cart_enabled'] : null,
		'edd_view_content_enabled'            => isset( $v6_fb['edd_view_content_enabled'] ) ? $v6_fb['edd_view_content_enabled'] : null,
		'edd_view_category_enabled'           => isset( $v6_fb['edd_view_category_enabled'] ) ? $v6_fb['edd_view_category_enabled'] : null,
	);
	
	// cleanup
	foreach ( $v7_fb as $key => $value ) {
		if ( $value === null ) {
			unset( $v7_fb[ $key ] );
		}
	}
	
	// update settings
	Facebook()->updateOptions( $v7_fb );
	Facebook()->reloadOptions();
	
	/**
	 * HEAD and FOOTER
	 */
	
	$v6_hf = get_option( 'head_footer', array() );
	
	$v7_hf = array(
		'head_any'                          => isset( $v6_hf['head_any'] ) ? $v6_hf['head_any'] : null,
		'head_desktop'                      => isset( $v6_hf['head_desktop'] ) ? $v6_hf['head_desktop'] : null,
		'head_mobile'                       => isset( $v6_hf['head_mobile'] ) ? $v6_hf['head_mobile'] : null,
		'footer_any'                        => isset( $v6_hf['footer_any'] ) ? $v6_hf['footer_any'] : null,
		'footer_desktop'                    => isset( $v6_hf['footer_desktop'] ) ? $v6_hf['footer_desktop'] : null,
		'footer_mobile'                     => isset( $v6_hf['footer_mobile'] ) ? $v6_hf['footer_mobile'] : null,
		'woo_order_received_disable_global' => isset( $v6_hf['woo_order_received_disable_global'] ) ? $v6_hf['woo_order_received_disable_global'] : null,
		'woo_order_received_head_any'       => isset( $v6_hf['woo_order_received_head_any'] ) ? $v6_hf['woo_order_received_head_any'] : null,
		'woo_order_received_head_desktop'   => isset( $v6_hf['woo_order_received_head_desktop'] ) ? $v6_hf['woo_order_received_head_desktop'] : null,
		'woo_order_received_head_mobile'    => isset( $v6_hf['woo_order_received_head_mobile'] ) ? $v6_hf['woo_order_received_head_mobile'] : null,
		'woo_order_received_footer_any'     => isset( $v6_hf['woo_order_received_footer_any'] ) ? $v6_hf['woo_order_received_footer_any'] : null,
		'woo_order_received_footer_desktop' => isset( $v6_hf['woo_order_received_footer_desktop'] ) ? $v6_hf['woo_order_received_footer_desktop'] : null,
		'woo_order_received_footer_mobile'  => isset( $v6_hf['woo_order_received_footer_mobile'] ) ? $v6_hf['woo_order_received_footer_mobile'] : null
	);
	
	// cleanup
	foreach ( $v7_hf as $key => $value ) {
		if ( $value === null ) {
			unset( $v7_hf[ $key ] );
		}
	}
	
	// update settings
	HeadFooter()->updateOptions( $v7_hf );
	HeadFooter()->reloadOptions();
	
}

function migrate_v5_free_options() {
	
	$v5_free = get_option( 'pixel_your_site' );
	
	$v7_core = array(
		'gdpr_facebook_prior_consent_enabled' => isset( $v5_free['gdpr']['enable_before_consent'] ) ? $v5_free['gdpr']['enable_before_consent'] : null,
		'gdpr_cookiebot_integration_enabled'  => isset( $v5_free['gdpr']['cookiebot_enabled'] ) ? $v5_free['gdpr']['cookiebot_enabled'] : null,
		'custom_events_enabled'          => isset( $v5_free['std']['enabled'] ) ? $v5_free['std']['enabled'] : null,
		'woo_enabled'                         => isset( $v5_free['woo']['enabled'] ) ? $v5_free['woo']['enabled'] : null,
		'woo_add_to_cart_on_button_click'     => isset( $v5_free['woo']['on_add_to_cart_btn'] ) ? $v5_free['woo']['on_add_to_cart_btn'] : null,
		'woo_add_to_cart_on_cart_page'        => isset( $v5_free['woo']['on_add_to_cart_page'] ) ? $v5_free['woo']['on_add_to_cart_page'] : null,
		'woo_add_to_cart_on_checkout_page'    => isset( $v5_free['woo']['on_add_to_cart_checkout'] ) ? $v5_free['woo']['on_add_to_cart_checkout'] : null,
		'woo_shipping_option'                 => isset( $v5_free['woo']['purchase_transport'] ) ? $v5_free['woo']['purchase_transport'] : null,
		'woo_purchase_value_option'           => isset( $v5_free['woo']['purchase_value_option'] ) ? $v5_free['woo']['purchase_value_option'] : null,
		'woo_purchase_value_global'           => isset( $v5_free['woo']['purchase_global_value'] ) ? $v5_free['woo']['purchase_global_value'] : null,
		'woo_initiate_checkout_value_enabled' => isset( $v5_free['woo']['enable_checkout_value'] ) ? $v5_free['woo']['enable_checkout_value'] : null,
		'woo_initiate_checkout_value_option'  => isset( $v5_free['woo']['checkout_value_option'] ) ? $v5_free['woo']['checkout_value_option'] : null,
		'woo_initiate_checkout_value_global'  => isset( $v5_free['woo']['checkout_global_value'] ) ? $v5_free['woo']['checkout_global_value'] : null,
		'woo_add_to_cart_value_enabled'       => isset( $v5_free['woo']['enable_add_to_cart_value'] ) ? $v5_free['woo']['enable_add_to_cart_value'] : null,
		'woo_add_to_cart_value_option'        => isset( $v5_free['woo']['add_to_cart_value_option'] ) ? $v5_free['woo']['add_to_cart_value_option'] : null,
		'woo_add_to_cart_value_global'        => isset( $v5_free['woo']['add_to_cart_global_value'] ) ? $v5_free['woo']['add_to_cart_global_value'] : null,
		'woo_view_content_value_enabled'      => isset( $v5_free['woo']['enable_view_content_value'] ) ? $v5_free['woo']['enable_view_content_value'] : null,
		'woo_view_content_value_option'       => isset( $v5_free['woo']['view_content_value_option'] ) ? $v5_free['woo']['view_content_value_option'] : null,
		'woo_view_content_value_global'       => isset( $v5_free['woo']['view_content_global_value'] ) ? $v5_free['woo']['view_content_global_value'] : null,

		'edd_enabled'                         => isset( $v5_free['edd']['enabled'] ) ? $v5_free['edd']['enabled'] : null,
		'edd_add_to_cart_on_button_click'     => isset( $v5_free['edd']['on_add_to_cart_btn'] ) ? $v5_free['edd']['on_add_to_cart_btn'] : null,
		'edd_add_to_cart_on_checkout_page'    => isset( $v5_free['edd']['on_add_to_cart_checkout'] ) ? $v5_free['edd']['on_add_to_cart_checkout'] : null,
		'edd_purchase_value_option'           => 'global',
		'edd_purchase_value_global'           => isset( $v5_free['edd']['purchase_global_value'] ) ? $v5_free['edd']['purchase_global_value'] : null,
		'edd_initiate_checkout_value_enabled' => isset( $v5_free['edd']['enable_checkout_value'] ) ? $v5_free['edd']['enable_checkout_value'] : null,
		'edd_initiate_checkout_value_option'  => 'global',
		'edd_initiate_checkout_value_global'  => isset( $v5_free['edd']['checkout_global_value'] ) ? $v5_free['edd']['checkout_global_value'] : null,
		'edd_add_to_cart_value_enabled'       => isset( $v5_free['edd']['enable_add_to_cart_value'] ) ? $v5_free['edd']['enable_add_to_cart_value'] : null,
		'edd_add_to_cart_value_option'        => 'global',
		'edd_add_to_cart_value_global'        => isset( $v5_free['edd']['add_to_cart_global_value'] ) ? $v5_free['edd']['add_to_cart_global_value'] : null,
		'edd_view_content_value_enabled'      => isset( $v5_free['edd']['enable_view_content_value'] ) ? $v5_free['edd']['enable_view_content_value'] : null,
		'edd_view_content_value_option'       => 'global',
		'edd_view_content_value_global'       => isset( $v5_free['edd']['view_content_global_value'] ) ? $v5_free['edd']['view_content_global_value'] : null,
        
        'gdpr_ajax_enabled' => isset( $v5_free['gdpr']['gdpr_ajax_enabled'] ) ? $v5_free['gdpr']['gdpr_ajax_enabled']
            : null,
	);
    
    global $wp_roles;
    
    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new \WP_Roles();
    }
    
    // 'do_not_track_user_roles'
    foreach ( $wp_roles->roles as $role => $options ) {
        if ( isset( $v5_free['general'][ 'disable_for_' . $role ] ) && $v5_free['general'][ 'disable_for_' . $role ] ) {
            $v7_core['do_not_track_user_roles'][] = $role;
        }
    }
    
	// update settings
	PYS()->updateOptions( $v7_core );
	PYS()->reloadOptions();
	
	if ( isset( $v5_free['woo']['content_id'] ) ) {
		$woo_content_id = $v5_free['woo']['content_id'] == 'id' ? 'product_id' : 'product_sku';
	} else {
		$woo_content_id = null;
	}
	
	if ( isset( $v5_free['woo']['on_add_to_cart_btn'] ) && $v5_free['woo']['on_add_to_cart_btn'] ) {
		$woo_add_to_cart_enabled = true;
	} elseif ( isset( $v5_free['woo']['on_add_to_cart_page'] ) && $v5_free['woo']['on_add_to_cart_page'] ) {
		$woo_add_to_cart_enabled = true;
	} elseif ( isset( $v5_free['woo']['on_add_to_cart_checkout'] ) && $v5_free['woo']['on_add_to_cart_checkout'] ) {
		$woo_add_to_cart_enabled = true;
	} else {
		$woo_add_to_cart_enabled = false;
	}
	
	if ( isset( $v5_free['edd']['content_id'] ) ) {
		$edd_content_id = $v5_free['edd']['content_id'] == 'id' ? 'download_id' : 'download_sku';
	} else {
		$edd_content_id = null;
	}
	
	if ( isset( $v5_free['edd']['on_add_to_cart_btn'] ) && $v5_free['edd']['on_add_to_cart_btn'] ) {
		$edd_add_to_cart_enabled = true;
	} elseif ( isset( $v5_free['edd']['on_add_to_cart_checkout'] ) && $v5_free['edd']['on_add_to_cart_checkout'] ) {
		$edd_add_to_cart_enabled = true;
	} else {
		$edd_add_to_cart_enabled = false;
	}
	
	$v7_fb = array(
		'enabled'                       => isset( $v5_free['general']['enabled'] ) ? $v5_free['general']['enabled'] : null,
		'pixel_id'                      => isset( $v5_free['general']['pixel_id'] ) ? array( $v5_free['general']['pixel_id'] ) : null,
		'search_event_enabled'          => isset( $v5_free['general']['search_event_enabled'] ) ? $v5_free['general']['search_event_enabled'] : null,

		'woo_variable_as_simple'        => isset( $v5_free['woo']['variation_id'] ) && $v5_free['woo']['variation_id'] == 'main',
		'woo_content_id'                => $woo_content_id,
		'woo_purchase_enabled'          => isset( $v5_free['woo']['on_thank_you_page'] ) ? $v5_free['woo']['on_thank_you_page'] : null,
		'woo_initiate_checkout_enabled' => isset( $v5_free['woo']['on_checkout_page'] ) ? $v5_free['woo']['on_checkout_page'] : null,
		'woo_add_to_cart_enabled'       => $woo_add_to_cart_enabled,
		'woo_view_content_enabled'      => isset( $v5_free['woo']['on_view_content'] ) ? $v5_free['woo']['on_view_content'] : null,
		'woo_view_category_enabled'     => isset( $v5_free['woo']['on_view_category'] ) ? $v5_free['woo']['on_view_category'] : null,

		'edd_content_id'                => $edd_content_id,
		'edd_purchase_enabled'          => isset( $v5_free['edd']['on_success_page'] ) ? $v5_free['edd']['on_success_page'] : null,
		'edd_initiate_checkout_enabled' => isset( $v5_free['edd']['on_checkout_page'] ) ? $v5_free['edd']['on_checkout_page'] : null,
		'edd_add_to_cart_enabled'       => $edd_add_to_cart_enabled,
		'edd_view_content_enabled'      => isset( $v5_free['edd']['on_view_content'] ) ? $v5_free['edd']['on_view_content'] : null,
		'edd_view_category_enabled'     => isset( $v5_free['edd']['on_view_category'] ) ? $v5_free['edd']['on_view_category'] : null,
	);
	
	// update settings
	Facebook()->updateOptions( $v7_fb );
	Facebook()->reloadOptions();
	
}

function migrate_v5_free_events() {
	
	$v5_free_events = get_option( 'pixel_your_site_std_events' );
	
	if ( ! is_array( $v5_free_events ) ) {
		return;
	}
	
	foreach ( $v5_free_events as $v5_free_event ) {
		
		if ( empty( $v5_free_event['pageurl'] ) ) {
			continue;
		}
		
		if ( $v5_free_event['eventtype'] == 'CustomCode' ) {
			continue;
		}
		
		$std_events = array(
			'ViewContent',
			'Search',
			'AddToCart',
			'AddToWishlist',
			'InitiateCheckout',
			'AddPaymentInfo',
			'Purchase',
			'Lead',
			'CompleteRegistration',
		);
		
		if ( ! in_array( $v5_free_event['eventtype'], $std_events ) ) {
			$fb_event_type        = 'CustomEvent';
			$fb_custom_event_type = $v5_free_event['custom_name'];
		} else {
			$fb_event_type        = $v5_free_event['eventtype'];
			$fb_custom_event_type = null;
		}
		
		$fb_params = array(
			'value'            => $v5_free_event['value'],
			//			'currency'         => $currency,
			'content_name'     => $v5_free_event['content_name'],
			'content_ids'      => $v5_free_event['content_ids'],
			'content_type'     => $v5_free_event['content_type'],
			'content_category' => $v5_free_event['content_category'],
			'num_items'        => $v5_free_event['num_items'],
			'order_id'         => $v5_free_event['order_id'],
			'search_string'    => $v5_free_event['search_string'],
			'status'           => $v5_free_event['status'],
		);
		
		if ( $v5_free_event['custom_currency'] == true ) {
			$fb_params['currency'] = 'custom';
			$fb_params['custom_currency'] = $v5_free_event['currency'];
		} elseif ( isset( $v5_free_event['currency'] ) ) {
			$fb_params['currency'] = $v5_free_event['currency'];
			$fb_params['custom_currency'] = null;
		}

		$fb_custom_params = array();
		
		foreach ( $v5_free_event as $param => $value ) {
			
			// skip standard params
			if ( array_key_exists( $param, $fb_params ) ) {
				continue;
			}
			
			// skip system params
			if ( in_array( $param, array( 'pageurl', 'eventtype', 'custom_currency', 'code', 'custom_name' ) ) ) {
				continue;
			}
			
			$fb_custom_params[] = array(
				'name' => $param,
				'value' => $value
			);
			
		}
		
		$customEvent = array(
			'title'                      => 'Untitled',
			'enabled'                    => true,
			'delay'                      => null,
			'trigger_type'               => 'page_visit',
			'triggers'                   => array(),
			'url_filters'                => array(),
			'page_visit_triggers'        => array(
				array(
					'rule'  => 'contains',
					'value' => $v5_free_event['pageurl'],
				),
			),
			'facebook_enabled'           => true,
			'facebook_event_type'        => $fb_event_type,
			'facebook_custom_event_type' => $fb_custom_event_type,
			'facebook_params_enabled'    => empty( $fb_params ) && empty( $fb_custom_params ) ? false : true,
			'facebook_params'            => $fb_params,
			'facebook_custom_params'     => $fb_custom_params,
		);
		
		CustomEventFactory::create( $customEvent );

	}
	
}

function update_orders_meta() {
	global $wpdb;
	
	//@todo: select orders from last update only
	
	$query = "
		INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value )
		SELECT ID as post_id, '_pys_purchase_event_fired' as meta_key, 1 as meta_value
		FROM $wpdb->posts
		WHERE post_type IN ( 'shop_order', 'edd_payment' )
	";
	
	$wpdb->get_results( $query );
	
}