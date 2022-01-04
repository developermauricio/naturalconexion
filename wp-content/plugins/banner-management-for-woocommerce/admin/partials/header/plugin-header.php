<?php

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
global  $wcbm_fs ;
$plugin_version = WCBM_PLUGIN_VERSION;
$version_label = 'Free Version';
$plugin_name = 'Category Banner Management for Woocommerce';
?>
<div id="dotsstoremain">
    <div class="all-pad">
        <header class="dots-header">
            <div class="dots-plugin-details">
                <div class="dots-header-left">
                    <div class="dots-logo-main">
                        <div class="logo-image">
                            <img  src="<?php 
echo  esc_url( plugins_url( 'images/wcbm-logo.png', dirname( dirname( __FILE__ ) ) ) ) ;
?>">
                        </div>
                        <div class="plugin-version">
                            <span><?php 
esc_html_e( $version_label, 'woo-banner-management' );
?> <?php 
echo  esc_html( '2.1.3' ) ;
?></span>
                        </div>
                    </div>
                    <div class="plugin-name">
                        <div class="title"><?php 
esc_html_e( $plugin_name, 'woo-banner-management' );
?></div>
                        <div class="desc"><?php 
esc_html_e( 'Allows you to manage Woocommerce pages, product pages and category pages based banner settings in your WooCommerce store.', 'woo-banner-management' );
?></div>
                    </div>
                </div>
                <div class="dots-header-right">
                    <div class="button-group">
                        <div class="button-dots">
                            <span class="support_dotstore_image">
                                <a target="_blank" href="<?php 
echo  esc_url( 'http://www.thedotstore.com/support/' ) ;
?>">
                                    <span class="dashicons dashicons-sos"></span>
                                    <strong><?php 
esc_html_e( 'Quick Support', 'woo-banner-management' );
?></strong>
                                </a>
                            </span>
                        </div>
                        <div class="button-dots">
                            <span class="support_dotstore_image">
                                <a target="_blank" href="<?php 
echo  esc_url( 'https://docs.thedotstore.com/collection/252-woocommerce-category-banner-management' ) ;
?>">
                                    <span class="dashicons dashicons-media-text"></span>
                                    <strong><?php 
esc_html_e( 'Documentation', 'woo-banner-management' );
?></strong>
                                </a>
                            </span>
                        </div>

                        <?php 
?>
                            <div class="button-dots">
                                <span class="support_dotstore_image">
                                    <a target="_blank" href="<?php 
echo  esc_url( $wcbm_fs->get_upgrade_url() ) ;
?>">
                                        <span class="dashicons dashicons-upload"></span>
                                        <strong><?php 
esc_html_e( 'Upgrade To Pro', 'woo-banner-management' );
?></strong>
                                    </a>
                                </span>
                            </div>
                        <?php 
?>
                    </div>
                </div>
            </div>
            <?php 
$wcbm_setting = '';
$active_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
$active_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
$wcbm_getting_started = ( !empty($active_tab) && 'wcbm-plugin-get-started' === $active_tab ? 'active' : '' );
$wcbm_information = ( !empty($active_tab) && 'wcbm-plugin-information' === $active_tab ? 'active' : '' );
if ( empty($active_tab) && 'banner-setting' === $active_page ) {
    $wcbm_setting = 'active';
}

if ( !empty($active_tab) && 'wcbm-plugin-get-started' === $active_tab || !empty($active_tab) && 'wcbm-plugin-information' === $active_tab ) {
    $fee_about = 'active';
} else {
    $fee_about = '';
}

?>
            <div class="dots-menu-main">
                <nav>
                    <ul>
                        <li>
                            
                            <a class="dotstore_plugin <?php 
echo  esc_attr( $wcbm_setting ) ;
?>" href="<?php 
echo  esc_url( admin_url( '/admin.php?page=banner-setting' ) ) ;
?>"><?php 
esc_html_e( 'Banner Settings', 'woo-banner-management' );
?></a>
                        
                        </li>
                        <li>
                            <a class="dotstore_plugin <?php 
echo  esc_attr( $fee_about ) ;
?>" href="<?php 
echo  esc_url( admin_url( '/admin.php?page=banner-setting&tab=wcbm-plugin-get-started' ) ) ;
?>"><?php 
esc_html_e( 'About Plugin', 'woo-banner-management' );
?></a>
                            <ul class="sub-menu">
                                <li><a class="dotstore_plugin <?php 
echo  esc_attr( $wcbm_getting_started ) ;
?>" href="<?php 
echo  esc_url( admin_url( '/admin.php?page=banner-setting&tab=wcbm-plugin-get-started' ) ) ;
?>"><?php 
esc_html_e( 'Getting Started', 'woo-banner-management' );
?></a></li>
                                <li><a class="dotstore_plugin <?php 
echo  esc_attr( $wcbm_information ) ;
?>" href="<?php 
echo  esc_url( admin_url( '/admin.php?page=banner-setting&tab=wcbm-plugin-information' ) ) ;
?>"><?php 
esc_html_e( 'Quick info', 'woo-banner-management' );
?></a></li>
                            </ul>
                        </li>
                        <li>
                            <a class="dotstore_plugin"><?php 
esc_html_e( 'Dotstore', 'woo-banner-management' );
?></a>
                            <ul class="sub-menu">
                                <li><a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/woocommerce-plugins/' ) ;
?>"><?php 
esc_html_e( 'WooCommerce Plugins', 'woo-banner-management' );
?></a></li>
                                <li><a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/wordpress-plugins/' ) ;
?>"><?php 
esc_html_e( 'Wordpress Plugins', 'woo-banner-management' );
?></a></li><br>
                                <li><a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/support/' ) ;
?>"><?php 
esc_html_e( 'Contact Support', 'woo-banner-management' );
?></a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>


