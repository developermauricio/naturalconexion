<?php

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
?>
    <div class="wcbm-section-left">
        <div class="wcbm-main-table res-cl">
        <h2><?php 
esc_html_e( 'Thanks For Installing Category Banner Management for Woocommerce', 'woo-banner-management' );
?>
        </h2>
        <table class="table-outer">
            <tbody>
            <tr>
                <td class="fr-2">
                    <p class="block gettingstarted"><strong><?php 
esc_html_e( 'Getting Started', 'woo-banner-management' );
?> </strong></p>
                    <p class="block textgetting">
                        <?php 
esc_html_e( 'Category Banner Management for Woocommerce plugin that allows you to manage Woocommerce pages, products and category wise banners in your WooCommerce store.', 'woo-banner-management' );
?>
                    </p>
                    <p class="block textgetting">
                        <?php 
esc_html_e( 'For eg. Add banner on Shop, Cart, Checkout, Product, Category and Thankyou page.', 'woo-banner-management' );
?>
                    </p>
                    <span class="gettingstarted">
                        <?php 
?>
                            <p class="block textgetting">
                                    <strong><?php 
esc_html_e( 'Screenshot-1', 'woo-banner-management' );
?></strong>
                                </p>
                                <p class="block textgetting">
                                    <?php 
esc_html_e( 'Default woocommerce page specific banner settings.', 'woo-banner-management' );
?>
                                </p>
                                <img src="<?php 
echo  esc_url( plugins_url( '/images/Getting_Started_01.png', dirname( __FILE__ ) ) ) ;
?>">
                                <p class="block textgetting">
                                    <strong><?php 
esc_html_e( 'Screenshot-2', 'woo-banner-management' );
?></strong>
                                </p>
                                <p class="block textgetting">
                                    <?php 
esc_html_e( 'Set global banner for each product.', 'woo-banner-management' );
?>
                                </p>
                                <img src="<?php 
echo  esc_url( plugins_url( '/images/product_banner_global_setting_image.png', dirname( __FILE__ ) ) ) ;
?>">
                                <p class="block textgetting">
                                    <strong><?php 
esc_html_e( 'Screenshot-3', 'woo-banner-management' );
?></strong>
                                </p>
                                <p class="block textgetting">
                                    <?php 
esc_html_e( 'Set banner for each category.', 'woo-banner-management' );
?>
                                </p>
                                <img src="<?php 
echo  esc_url( plugins_url( '/images/category_banner_setting_image.png', dirname( __FILE__ ) ) ) ;
?>">
                                <p class="block textgetting">
                                    <strong><?php 
esc_html_e( 'Screenshot-4', 'woo-banner-management' );
?></strong>
                                </p>
                                <p class="block textgetting">
                                    <?php 
esc_html_e( 'Global settings for product banner.', 'woo-banner-management' );
?>
                                </p>
                                <img src="<?php 
echo  esc_url( plugins_url( '/images/Global_settings_for_product_banner.png', dirname( __FILE__ ) ) ) ;
?>">
                        <?php 
?>
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    </div>
<?php 
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-sidebar.php';