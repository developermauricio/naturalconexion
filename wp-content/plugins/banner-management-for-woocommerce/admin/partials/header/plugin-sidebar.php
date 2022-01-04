<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$image_url = plugins_url( 'images/right_click.png', dirname( dirname( __FILE__ ) ) );
$tweet_url = 'https://twitter.com/share?text=' . urlencode( "I use Category Banner Management Plugin for #WooCommerce by @dotstore to help add the banner on checkout pages for #customerorders Enhance your customer experience of #productpages with banner images. Checkout this #plugin" ) . '&amp;hashtags=wordpress,woo &amp;url=https://wordpress.org/plugins/banner-management-for-woocommerce/';
$review_url = '';
$plugin_at = '';
$changelog_url = '';
$review_url = esc_url( 'https://wordpress.org/plugins/banner-management-for-woocommerce/#reviews' );
$plugin_at = 'WP.org';
$changelog_url = 'https://wordpress.org/plugins/banner-management-for-woocommerce/#developers';
?>
<div class="dotstore_plugin_sidebar">

    <div class="dotstore-important-link">
		<div class="image_box">
			<img src="<?php 
echo  esc_url( plugins_url( '/images/rate-us.png', dirname( dirname( __FILE__ ) ) ) ) ;
?>" alt="">
		</div>
		<div class="content_box">
			<h3><?php 
esc_html_e( 'Like This Plugin?', 'woo-banner-management' );
?></h3>
            <p class="star-container">
                <a href="<?php 
echo  esc_url( $review_url ) ;
?>" target="_blank">
                    <span class="dashicons dashicons-star-filled"></span>
                    <span class="dashicons dashicons-star-filled"></span>
                    <span class="dashicons dashicons-star-filled"></span>
                    <span class="dashicons dashicons-star-filled"></span>
                    <span class="dashicons dashicons-star-filled"></span>
                </a>
            </p>
			<p><?php 
esc_html_e( 'Your Review is very important to us as it helps us to grow more.', 'woo-banner-management' );
?></p>
			<a class="btn_style" href="<?php 
echo  esc_url( $review_url ) ;
?>" target="_blank"><?php 
esc_html_e( 'Review Us on ', 'woo-banner-management' );
?> <?php 
esc_html_e( $plugin_at, 'woo-banner-management' );
?></a>
            <h3><?php 
esc_html_e( 'Tweet about us!', 'woo-banner-management' );
?></h3>
            <a class="btn_style" href="<?php 
echo  esc_url( $tweet_url ) ;
?>" target="_blank"><i class="fa fa-twitter" aria-hidden="true"></i><?php 
esc_html_e( ' Tweet', 'woo-banner-management' );
?></a>
		</div>
    </div>

    <div class="dotstore-sidebar-section">
        <div class="dotstore-important-link-heading">
            <span class="dashicons dashicons-image-rotate-right"></span>
            <span class="heading-text"><?php 
esc_html_e( 'Free vs Pro Feature', 'woo-banner-management' );
?></span>
        </div>
        <div class="dotstore-important-link-content">
            <p><?php 
esc_html_e( 'Here’s an at a glance view of the main differences between Premium and free plugin features.', 'woo-banner-management' );
?></p>
            <a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/woocommerce-category-banner-management/#tab-free-vs-premium' ) ;
?>"><?php 
esc_html_e( 'Click here »', 'woo-banner-management' );
?></a>
        </div>
    </div>

    <div class="dotstore-sidebar-section">
        <div class="dotstore-important-link-heading">
            <span class="dashicons dashicons-star-filled"></span>
            <span class="heading-text"><?php 
esc_html_e( 'Suggest A Feature', 'woo-banner-management' );
?></span>
        </div>
        <div class="dotstore-important-link-content">
            <p><?php 
esc_html_e( 'Let us know how we can improve the plugin experience.', 'woo-banner-management' );
?></p>
            <p><?php 
esc_html_e( 'Do you have any feedback & feature requests?', 'woo-banner-management' );
?></p>
            <a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/suggest-a-feature' ) ;
?>"><?php 
esc_html_e( 'Submit Request »', 'woo-banner-management' );
?></a>
        </div>
    </div>

    <div class="dotstore-sidebar-section">
        <div class="dotstore-important-link-heading">
            <span class="dashicons dashicons-editor-kitchensink"></span>
            <span class="heading-text"><?php 
esc_html_e( 'Changelog', 'woo-banner-management' );
?></span>
        </div>
        <div class="dotstore-important-link-content">
            <p><?php 
esc_html_e( 'We improvise our products on a regular basis to deliver the best results to customer satisfaction.', 'woo-banner-management' );
?></p>
            <a target="_blank" href="<?php 
echo  esc_url( $changelog_url ) ;
?>"><?php 
esc_html_e( 'Visit Here »', 'woo-banner-management' );
?></a>
        </div>
    </div>

    <div class="dotstore-important-link dotstore-sidebar-section">
        <div class="dotstore-important-link-heading">
            <span class="dashicons dashicons-plugins-checked"></span>
            <span class="heading-text"><?php 
esc_html_e( 'Our Popular Plugins', 'woo-banner-management' );
?></span>
        </div>
        <div class="video-detail important-link">
            <ul>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugins_url( '/images/advance-flat-rate.png', dirname( dirname( __FILE__ ) ) ) ) ;
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/flat-rate-shipping-plugin-for-woocommerce/' ) ;
?> "><?php 
esc_html_e( 'Flat Rate Shipping Plugin for WC', 'woo-banner-management' );
?></a>
                </li> 
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugins_url( '/images/woo-conditional-product-fees-for-checkout.png', dirname( dirname( __FILE__ ) ) ) ) ;
?>">
                    <a  target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/product/woocommerce-extra-fees-plugin/' ) ;
?>"><?php 
esc_html_e( 'Extra Fees Plugin for WC', 'woo-banner-management' );
?></a>
                </li>
                <li>
                    <img class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugins_url( '/images/woo-advanced-product-size-chart.png', dirname( dirname( __FILE__ ) ) ) ) ;
?>">
                    <a  target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/woocommerce-advanced-product-size-charts/' ) ;
?>"><?php 
esc_html_e( 'Product Size Charts Plugin for WC', 'woo-banner-management' );
?></a>
                </li>
                <li>
                    <img  class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugins_url( '/images/woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers.png', dirname( dirname( __FILE__ ) ) ) ) ;
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/woocommerce-anti-fraud' ) ;
?>"><?php 
esc_html_e( 'Fraud Prevention Plugin for WC', 'woo-banner-management' );
?></a>
                </li>
                <li>
                    <img  class="sidebar_plugin_icone" src="<?php 
echo  esc_url( plugins_url( '/images/hide-shipping-method-for-woocommerce.png', dirname( dirname( __FILE__ ) ) ) ) ;
?>">
                    <a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/hide-shipping-method-for-woocommerce' ) ;
?>"><?php 
esc_html_e( 'Hide Shipping Method For WC', 'woo-banner-management' );
?></a>
                </li>
                </br>
            </ul>
        </div>
        <div class="view-button">
            <a class="button button-primary button-large" target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/plugins' ) ;
?>"><?php 
esc_html_e( 'VIEW ALL', 'woo-banner-management' );
?></a>
        </div>
    </div>

    <div class="dotstore-sidebar-section">
        <div class="dotstore-important-link-heading">
            <span class="dashicons dashicons-sos"></span>
            <span class="heading-text"><?php 
esc_html_e( 'Five Star Support', 'woo-banner-management' );
?></span>
        </div>
        <div class="dotstore-important-link-content">
            <p><?php 
esc_html_e( 'Got a question? Get in touch with theDotstore developers. We are happy to help! ', 'woo-banner-management' );
?></p>
            <a target="_blank" href="<?php 
echo  esc_url( 'https://www.thedotstore.com/support/' ) ;
?>"><?php 
esc_html_e( 'Submit a Ticket »', 'woo-banner-management' );
?></a>
        </div>
    </div>
</div>
