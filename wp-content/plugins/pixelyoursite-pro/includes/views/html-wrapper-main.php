<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/** @var PYS $this */

include "html-popovers.php";

?>

<div class="wrap" id="pys">
    <h1><?php _e( 'PixelYourSite Pro', 'pys' ); ?></h1>
    <div class="container">
        <form method="post" enctype="multipart/form-data">
         
	        <?php wp_nonce_field( 'pys_save_settings' ); ?>
            
            <div class="row mb-3">
                <div class="col">
                    <nav class="nav nav-tabs">
    
                        <?php foreach ( getAdminPrimaryNavTabs() as $tab_key => $tab_data ) : ?>
    
                            <?php
    
                            $classes = array(
                                'nav-item',
                                'nav-link',
                            );
    
                            if ( $tab_key == getCurrentAdminTab() ) {
                                $classes[] = 'active';
                            }
    
                            $classes = implode( ' ', $classes );

                            if(isset($tab_data['class']) ) {
                                $classes .= ' '.$tab_data['class'];
                            }
    
                            ?>
    
                            <a class="<?php esc_attr_e( $classes ); ?>"
                               href="<?php echo esc_url( $tab_data['url'] ); ?>">
                                <?php esc_html_e( $tab_data['name'] ); ?>
                            </a>
    
                        <?php endforeach; ?>
    
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-9">

                    <?php

                    switch ( getCurrentAdminTab() ) {
                        case 'general':
	                        include "html-main-general.php";
                            break;

                        case 'events':
                            if ( getCurrentAdminAction() == 'edit' ) {
	                            include "html-main-events-edit.php";
                            } else {
	                            include "html-main-events.php";
                            }
	                        break;

	                    case 'woo':
		                    include "html-main-woo.php";
		                    break;

	                    case 'edd':
		                    include "html-main-edd.php";
		                    break;
                        case 'wcf':
                            include "html-main-wcf.php";
                            break;

                        case 'head_footer':
                            /** @noinspection PhpIncludeInspection */
                            include PYS_PATH . '/modules/head_footer/views/html-admin-page.php';
                            break;

                        case 'facebook_settings':
	                        /** @noinspection PhpIncludeInspection */
	                        include PYS_PATH . '/modules/facebook/views/html-settings.php';
                            break;

	                    case 'ga_settings':
		                    /** @noinspection PhpIncludeInspection */
		                    include PYS_PATH . '/modules/google_analytics/views/html-settings.php';
		                    break;
		                    
                        case 'google_ads_settings':
                            /** @noinspection PhpIncludeInspection */
                            include PYS_PATH . '/modules/google_ads/views/html-settings.php';
                            break;
                        case 'tiktok_settings':
                            /** @noinspection PhpIncludeInspection */
                            include PYS_PATH . '/modules/tiktok/views/html-settings.php';
                            break;
                        case 'gdpr':
	                        include "html-gdpr.php";
                            break;
                            
                        case 'reset_settings':
	                        include "html-reset.php";
	                        break;
                        case 'logs':
                            include "html-logs.php";
                            break;

                        default:
                            do_action( 'pys_admin_' . getCurrentAdminTab() );
                    }

                    ?>
                    
                </div>
                <div class="col-3">
                    <nav class="nav nav-pills flex-column mb-3">
    
                        <?php foreach ( getAdminSecondaryNavTabs() as $tab_key => $tab_data ) : ?>
    
                            <?php
    
                            $classes = array(
                                'nav-item',
                                'nav-link',
                            );
    
                            if ( $tab_key == getCurrentAdminTab() ) {
                                $classes[] = 'active';
                            }
    
                            $classes = implode( ' ', $classes );
    
                            ?>
    
                            <a class="<?php esc_attr_e( $classes ); ?>"
                               href="<?php echo esc_url( $tab_data['url'] ); ?>">
                                <?php esc_html_e( $tab_data['name'] ); ?>
                            </a>
    
                        <?php endforeach; ?>

                        <a class="nav-item nav-link" href="https://www.pixelyoursite.com/documentation"
                           target="_blank" style="font-weight: bold;">HELP</a>
                        <a href="<?php echo esc_url( buildAdminUrl( 'pixelyoursite', 'logs' ) ); ?>"
                           class="nav-item nav-link">Logs</a>
                    </nav>
                    
                    <?php if ( 'woo' == getCurrentAdminTab() ) : ?>
                        <div class="card card-static border-primary mb-5">
                            <div class="card-body">
                                <h4 class="card-title">Custom Audience File Export</h4>
                                <p class="card-text">Export a customer file with lifetime value. Use it to create a
                                    Custom
                                    Audience and a Value-Based Lookalike Audience. More details
                                    <a href="https://www.pixelyoursite.com/value-based-facebook-lookalike-audiences"
                                       target="_blank">here</a>.</p>
                                <button type="submit" name="pys[export_custom_audiences]" value="woo"
                                        class="btn btn-sm btn-block btn-primary">Export clients LTV file
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
	
	                <?php if ( 'edd' == getCurrentAdminTab() ) : ?>
                        <div class="card card-static border-primary mb-5">
                            <div class="card-body">
                                <h4 class="card-title">Custom Audience File Export</h4>
                                <p class="card-text">Export a customer file with lifetime value. Use it to create a
                                    Custom
                                    Audience and a Value-Based Lookalike Audience. More details
                                    <a href="https://www.pixelyoursite.com/value-based-facebook-lookalike-audiences"
                                       target="_blank">here</a>.</p>
                                <button type="submit" name="pys[export_custom_audiences]" value="edd"
                                        class="btn btn-sm btn-block btn-primary">Export clients LTV file
                                </button>
                            </div>
                        </div>
	                <?php endif; ?>
                    
                    <?php if ( ! isProductCatalogFeedProActive() ) : ?>
                        <div class="card card-static border-primary">
                            <div class="card-body">
                                <h4 class="card-title">WooCommerce Product Catalog Feeds</h4>
                                <p class="card-text">Generate auto-updating WooCommerce XML feeds for Facebook Product
                                    Catalog, Google Merchant, and Google Ads (custom type).</p>
                                <a href="https://www.pixelyoursite.com/product-catalog-facebook" target="_blank"
                                   class="btn btn-sm btn-block btn-primary">Click for details</a>
                            </div>
                        </div>
                    <?php endif; ?>
	
	                <?php if ( ! isEddProductsFeedProActive() ) : ?>
                        <div class="card card-static border-primary">
                            <div class="card-body">
                                <h4 class="card-title">Easy Digital Downloads Product Catalog Feeds</h4>
                                <p class="card-text">Generate auto-updating EDD XML feeds for Facebook Product Catalog.</p>
                                <a href="https://www.pixelyoursite.com/easy-digital-downloads-product-catalog"
                                   target="_blank" class="btn btn-sm btn-block btn-primary">Click for details</a>
                            </div>
                        </div>
	                <?php endif; ?>
	
	                <?php if ( ! isSuperPackActive() ) : ?>
                        <div class="card card-static border-primary">
                            <div class="card-body">
                                <h4 class="card-title">PixelYourSite Super Pack</h4>
                                <p class="card-text">Improve your tracking with these 5 add-ons.</p>
                                <a href="https://www.pixelyoursite.com/super-pack"
                                   target="_blank" class="btn btn-sm btn-block btn-primary">Click for details</a>
                            </div>
                        </div>
	                <?php endif; ?>
	
	                <?php if ( ! isBoostActive() ) : ?>
                        <div class="card card-static border-primary">
                            <div class="card-body">
                                <h4 class="card-title">Boost your conversion with social proof</h4>
                                <p class="card-text">Automatically capture and show recent visitors’ activity as nice,
                                    auto-vanishing pop-ups.</p>
                                <a href="https://www.boostplugin.com/"
                                   target="_blank" class="btn btn-sm btn-block btn-primary">Click for details</a>
                            </div>
                        </div>
	                <?php endif; ?>
                    
                    <?php if ( ! isSmartOpenGraphActive() ) : ?>
                        <div class="card card-static border-primary">
                            <div class="card-body">
                                <h4 class="card-title">Smart OpenGraph</h4>
                                <?php if ( isWooCommerceActive() ) : ?>
                                    <p class="card-text">Automatically add your WooCommerce products to a Facebook
                                        Product Catalog when someone visits them.</p>
                                <?php else : ?>
                                    <p class="card-text">Improve the way your content is shared on Facebook and other
                                        social
                                        networks with the right OpenGraph tags.</p>
                                <?php endif; ?>
                                <a href="https://www.pixelyoursite.com/opengraph-plugin" target="_blank"
                                   class="btn btn-sm btn-block btn-primary">Click for details</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( getCurrentAdminTab() !== 'reset_settings' ) : ?>
                        <a href="<?php echo esc_url( buildAdminUrl( 'pixelyoursite', 'reset_settings' ) ); ?>"
                           class="btn btn-sm btn-block btn-light mt-5">Reset all settings to defaults</a>
                    <?php endif; ?>
                    
                    <!-- @todo: +7.1.0+ add export settings button and feature -->
                    
                </div>
            </div>
        </form>
    </div>
</div>
