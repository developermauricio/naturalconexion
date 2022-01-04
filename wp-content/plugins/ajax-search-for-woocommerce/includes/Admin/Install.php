<?php

namespace DgoraWcas\Admin;

use  DgoraWcas\Engines\TNTSearchMySQL\Indexer\Builder ;
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class Install
{
    /**
     * Call installation callback
     *
     * @return void
     */
    public static function maybeInstall()
    {
        if ( !(defined( 'DOING_AJAX' ) && DOING_AJAX) ) {
            add_action( 'admin_init', array( __CLASS__, 'checkVersion' ), 5 );
        }
    }
    
    /**
     * Install process
     *
     * @return void
     */
    public static function install()
    {
        if ( !defined( 'DGWT_WCAS_INSTALLING' ) ) {
            define( 'DGWT_WCAS_INSTALLING', true );
        }
        self::saveActivationDate();
        self::createOptions();
        self::maybeUpgradeOptions();
        // Update plugin version
        update_option( 'dgwt_wcas_version', DGWT_WCAS_VERSION );
    }
    
    /**
     * Save default options
     *
     * @return void
     */
    private static function createOptions()
    {
        global  $dgwtWcasSettings ;
        $sections = DGWT_WCAS()->settings->settingsFields();
        $settings = array();
        if ( is_array( $sections ) && !empty($sections) ) {
            foreach ( $sections as $options ) {
                if ( is_array( $options ) && !empty($options) ) {
                    foreach ( $options as $option ) {
                        if ( isset( $option['name'] ) && !isset( $dgwtWcasSettings[$option['name']] ) ) {
                            $settings[$option['name']] = ( isset( $option['default'] ) ? $option['default'] : '' );
                        }
                    }
                }
            }
        }
        $updateOptions = array_merge( $settings, $dgwtWcasSettings );
        update_option( DGWT_WCAS_SETTINGS_KEY, $updateOptions );
    }
    
    private static function maybeUpgradeOptions()
    {
        $settingsVersion = (int) get_option( 'dgwt_wcas_settings_version', 0 );
        
        if ( $settingsVersion < 1 ) {
            if ( (int) get_option( 'dgwt_wcas_settings_version_pro', 0 ) === 0 ) {
                self::upgradeOptionsTo1();
            }
            update_option( 'dgwt_wcas_settings_version', 1 );
            DGWT_WCAS()->settings->clearCache();
        }
    
    }
    
    private static function upgradeOptionsTo1()
    {
        $settings = get_option( DGWT_WCAS_SETTINGS_KEY );
        if ( empty($settings) ) {
            return;
        }
        // Product categories
        
        if ( isset( $settings['show_matching_categories'] ) ) {
            $settings['show_product_tax_product_cat'] = $settings['show_matching_categories'];
            unset( $settings['show_matching_categories'] );
        }
        
        
        if ( isset( $settings['show_categories_images'] ) ) {
            $settings['show_product_tax_product_cat_images'] = $settings['show_categories_images'];
            unset( $settings['show_categories_images'] );
        }
        
        
        if ( isset( $settings['search_in_product_categories'] ) ) {
            $settings['search_in_product_tax_product_cat'] = $settings['search_in_product_categories'];
            unset( $settings['search_in_product_categories'] );
        }
        
        // Product tags
        
        if ( isset( $settings['show_matching_tags'] ) ) {
            $settings['show_product_tax_product_tag'] = $settings['show_matching_tags'];
            unset( $settings['show_matching_tags'] );
        }
        
        
        if ( isset( $settings['search_in_product_tags'] ) ) {
            $settings['search_in_product_tax_product_tag'] = $settings['search_in_product_tags'];
            unset( $settings['search_in_product_tags'] );
        }
        
        // Product brands
        
        if ( DGWT_WCAS()->brands->hasBrands() ) {
            
            if ( isset( $settings['show_matching_brands'] ) ) {
                $settings['show_product_tax_' . DGWT_WCAS()->brands->getBrandTaxonomy()] = $settings['show_matching_brands'];
                unset( $settings['show_matching_brands'] );
            }
            
            
            if ( isset( $settings['search_in_brands'] ) ) {
                $settings['search_in_product_tax_' . DGWT_WCAS()->brands->getBrandTaxonomy()] = $settings['search_in_brands'];
                unset( $settings['search_in_brands'] );
            }
            
            
            if ( isset( $settings['show_brands_images'] ) ) {
                $settings['show_product_tax_' . DGWT_WCAS()->brands->getBrandTaxonomy() . '_images'] = $settings['show_brands_images'];
                unset( $settings['show_brands_images'] );
            }
        
        }
        
        update_option( DGWT_WCAS_SETTINGS_KEY, $settings );
    }
    
    /**
     * Save activation timestamp
     * Used to display notice, asking for a feedback
     *
     * @return void
     */
    private static function saveActivationDate()
    {
        $date = get_option( 'dgwt_wcas_activation_date' );
        if ( empty($date) ) {
            update_option( 'dgwt_wcas_activation_date', time() );
        }
    }
    
    /**
     * Check if SQL server support JSON data type
     */
    private static function checkIfDbSupportJson()
    {
        global  $wpdb ;
        $suppress_errors = $wpdb->suppress_errors;
        $wpdb->suppress_errors();
        $result = $wpdb->get_var( "SELECT JSON_CONTAINS('[1,2,3]', '2')" );
        $wpdb->suppress_errors( $suppress_errors );
        update_option( 'dgwt_wcas_db_json_support', ( $result === '1' && empty($wpdb->last_error) ? 'yes' : 'no' ) );
    }
    
    /**
     * Compare plugin version and install if a new version is available
     *
     * @return void
     */
    public static function checkVersion()
    {
        if ( !defined( 'IFRAME_REQUEST' ) ) {
            if ( !dgoraAsfwFs()->is_premium() && get_option( 'dgwt_wcas_version' ) != DGWT_WCAS_VERSION ) {
                self::install();
            }
        }
    }

}