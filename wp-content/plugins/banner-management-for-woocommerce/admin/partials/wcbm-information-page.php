<?php

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
$plugin_title = "Category Banner Management for Woocommerce";
$plugin_version_text = "Free Version";
?>
    <div class="wcbm-section-left">
         <div class="wcbm-main-table res-cl">
        <h2><?php 
esc_html_e( 'Quick info', 'woo-banner-management' );
?></h2>
        <table class="table-outer">
            <tbody>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Product Type', 'woo-banner-management' );
?></td>
                <td class="fr-2"><?php 
esc_html_e( 'WooCommerce Plugin', 'woo-banner-management' );
?></td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Product Name', 'woo-banner-management' );
?></td>
                <td class="fr-2"><?php 
esc_html_e( $plugin_title, 'woo-banner-management' );
?></td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Installed Version', 'woo-banner-management' );
?></td>
                <td class="fr-2"><?php 
esc_html_e( $plugin_version_text, 'woo-banner-management' );
?> <?php 
echo  esc_html( WCBM_PLUGIN_VERSION ) ;
?></td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'License & Terms of use', 'woo-banner-management' );
?></td>
                <td class="fr-2"><a target="_blank"  href="<?php 
echo  esc_url( 'https://www.thedotstore.com/terms-and-conditions/' ) ;
?>">
                        <?php 
esc_html_e( 'Click here', 'woo-banner-management' );
?></a>
                    <?php 
esc_html_e( 'to view license and terms of use.', 'woo-banner-management' );
?>
                </td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Help & Support', 'woo-banner-management' );
?></td>
                <td class="fr-2 wcbm-information">
                    <ul>
                        <li><a target="_blank" href="<?php 
echo  esc_url( site_url( 'wp-admin/admin.php?page=banner-setting&tab=wcbm-plugin-get-started' ) ) ;
?>"><?php 
esc_html_e( 'Quick Start', 'woo-banner-management' );
?></a></li>
                        <li><a target="_blank" href="<?php 
echo  esc_url( 'https://store.multidots.com/wp-content/uploads/2017/02/Banner-Management-for-WooCommerce-help-document-.pdf' ) ;
?>"><?php 
esc_html_e( 'Guide Documentation', 'woo-banner-management' );
?></a></li>
                        <li><a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/support/' ) ;
?>"><?php 
esc_html_e( 'Support Forum', 'woo-banner-management' );
?></a></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Localization', 'woo-banner-management' );
?></td>
                <td class="fr-2"><?php 
esc_html_e( 'English', 'woo-banner-management' );
?>, <?php 
esc_html_e( 'Spanish', 'woo-banner-management' );
?></td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Category page banner', 'woo-banner-management' );
?></td>
                <td class="fr-2"><?php 
esc_html_e( '[display_category_banner]', 'woo-banner-management' );
?></td>
            </tr>
            <tr>
                <td class="fr-1"><?php 
esc_html_e( 'Product page banner', 'woo-banner-management' );
?></td>
                <td class="fr-2"><?php 
esc_html_e( '[display_product_banner]', 'woo-banner-management' );
?></td>
            </tr>
            </tbody>
        </table>
    </div>
    </div>
<?php 
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-sidebar.php';