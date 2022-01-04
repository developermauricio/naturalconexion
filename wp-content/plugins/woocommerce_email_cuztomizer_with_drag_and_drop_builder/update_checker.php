<?php
/**
 * Created by PhpStorm.
 * User: cartrabbit
 * Date: 28/02/19
 * Time: 2:58 PM
 */

if(!class_exists('WooEmailBuilderUpdateChecker')){
    class WooEmailBuilderUpdateChecker{

        private static $_instance = null;
        private static $_remote_license_url = 'https://app.flycart.org/';
        /**
         * Get the single instance
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Init the update checker
         * */
        public static function init(){
            self::addHooks();
            $update_checker = self::instance();
            $update_checker->runUpdater();
        }

        /**
         * Add hooks
         * */
        public static function addHooks(){
            add_filter('puc_request_info_result-woo-email-customizer-page-builder', 'WooEmailBuilderUpdateChecker::loadEmailCustomizerDescription', 10, 2);
            add_filter('in_plugin_update_message-woocommerce_email_cuztomizer_with_drag_and_drop_builder/woo-email-customizer-page-builder.php', 'WooEmailBuilderUpdateChecker::message_on_plugin_page_when_licence_is_expired', 10, 2);
        }

        /**
         * Message on plugin page when licence is expired
         * */
        public static function message_on_plugin_page_when_licence_is_expired($plugin_data, $response){
            if(empty($response->package)){
                echo "<br>";
                echo self::get_message_on_licence_expired();
            }
        }

        /**
         * get message on licence expired
         * */
        public static function get_message_on_licence_expired(){
            $msg = '';
            $licenceKey = self::getLicenceKey();
            if($licenceKey == ''){
                $upgrade_url = '<a href="'.self::get_settings_page_url().'">'.esc_html__('enter licence key', 'woo-email-customizer-page-builder').'</a>';
                $msg .= sprintf(esc_html__('Please %s to receive automatic updates or you can manually update the plugin by downloading it.', 'woo-email-customizer-page-builder'), $upgrade_url);
            } else {
                $upgrade_url = '<a target="_blank" href="'.self::get_product_url().'">'.esc_html__('renew your support licence', 'woo-email-customizer-page-builder').'</a>';
                $msg .= sprintf(esc_html__('Please %s to receive automatic updates or you can manually update the plugin by downloading it.', 'woo-email-customizer-page-builder'), $upgrade_url);
            }

            return $msg;
        }

        /**
         * Get product URL
         * */
        public static function get_product_url(){
            return 'https://codecanyon.net/item/woocommerce-email-customizer-with-drag-and-drop-email-builder/19849378';
        }

        /**
         * Load description
         * */
        public static function loadEmailCustomizerDescription($pluginInfo, $result){
            if(isset($pluginInfo->sections)){
                $section = $pluginInfo->sections;
                if(empty($section['description'])){
                    $section['description'] = self::plugin_description_content();
                    $pluginInfo->sections = $section;
                }
            } else {
                $pluginInfo->sections = array('description' => self::plugin_description_content());
            }

            return $pluginInfo;
        }

        /**
         * Validate licence key
         * */
        public static function validateLicenceKey($licence_key){
            $validate_url = self::getValidateURL($licence_key);
            $result = wp_remote_get( $validate_url , array());
            //Try to parse the json response
            $status = static::validateApiResponse($result);
            $metadata = null;

            if ( !is_wp_error($status) ){
                $json = json_decode( $result['body'] );
                $current_status = 'invalid';
                if ( is_object($json) && isset($json->license_check) && isset($json->license_check)) {
                    update_option('woo_email_customizer_page_builder_updated_time', time());
                    if($json->license_check && $json->support_check){
                        $current_status = 'active';
                    } elseif ($json->license_check){
                        $current_status = 'expired';
                    }
                }
                update_option('woo_email_customizer_page_builder_verified_key', $current_status);
                return $current_status;
            }

            return false;
        }

        /**
         * Check if $result is a successful update API response.
         *
         * @param array|WP_Error $result
         * @return true|WP_Error
         */
        protected static function validateApiResponse($result) {
            if ( is_wp_error($result) ) { /** @var WP_Error $result */
                return new WP_Error($result->get_error_code(), 'WP HTTP Error: ' . $result->get_error_message());
            }

            if ( !isset($result['response']['code']) ) {
                return new WP_Error(
                    'puc_no_response_code',
                    'wp_remote_get() returned an unexpected result.'
                );
            }

            if ( $result['response']['code'] !== 200 ) {
                return new WP_Error(
                    'puc_unexpected_response_code',
                    'HTTP response code is ' . $result['response']['code'] . ' (expected: 200)'
                );
            }

            if ( empty($result['body']) ) {
                return new WP_Error('puc_empty_response', 'The metadata file appears to be empty.');
            }

            return true;
        }

        /**
         * To run the updater
         * */
        protected function runUpdater(){
            $update_url = $this->getUpdateURL();
            if($update_url != ''){
                $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
                    $update_url,
                    plugin_dir_path( __FILE__ ).'woo-email-customizer-page-builder.php',
                    'woo-email-customizer-page-builder'
                );
            }
        }

        /**
         * To get update URL
         *
         * @return string
         * */
        protected function getUpdateURL(){
            $update_url = self::$_remote_license_url.'update?channel=envato&name=WooCommerce Email Customizer with Drag and Drop Email Builder&slug=woo-email-customizer-page-builder&license_key=';
            $licenceKey = self::getLicenceKey();
            $update_url .= $licenceKey;
            if($licenceKey == ''){
                add_action( 'admin_notices', array($this, 'errorNoticeInAdminPagesToEnterLicenceKey'));
                //return '';
            }
            return $update_url;
        }

        /**
         * To get validate URL
         *
         * @return string
         * */
        protected static function getValidateURL($licence_key){
            $update_url = self::$_remote_license_url.'verify?channel=envato&name=WooCommerce Email Customizer with Drag and Drop Email Builder&slug=woo-email-customizer-page-builder&license_key='.$licence_key;
            return $update_url;
        }

        /**
         * To display error message in admin page while there is no licence key
         * */
        public function errorNoticeInAdminPagesToEnterLicenceKey(){
            $notice_on_for_user = get_user_meta( get_current_user_id(), 'dismissed_woo_email_customizer_admin_installed_notice', true );
            if(!$notice_on_for_user){
                $htmlPrefix = '<div class="updated woocommerce-message"><p>';
                $htmlSuffix = '</p></div>';
                $msg = "<strong>";
                $msg .= __("WooCommerce Email Customizer with Drag and Drop Email Builder installed", 'woo-email-customizer-page-builder');
                $msg .= "</strong>";
                $msg .= __(" - Make sure to activate your copy of the plugin to receive updates, support and security fixes!", 'woo-email-customizer-page-builder');
                $msg .= '<p>';
                $msg .= '<a href="'.self::get_settings_page_url().'" class="button-primary">';
                $msg .= __('Settings', 'woo-email-customizer-page-builder');
                $msg .= '</a></p>';
                $msg .= '<a href="'.esc_url( wp_nonce_url( add_query_arg( 'wemc-hide-notice', 'installed' ), 'woo_email_customizer_hide_notices_nonce', '_wemc_notice_nonce' ) ).'" class="wemc-notice-a notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice.', 'woo-email-customizer-page-builder').'</span></a>';
                echo $htmlPrefix.$msg.$htmlSuffix;
            }
        }

        /**
         * Get licence key
         * */
        protected static function getLicenceKey(){
            return WooEmailCustomizerCommon::getEmailCustomizerSettings('licence_key', '');
        }

        /**
         * Get licence key
         * */
        protected static function get_settings_page_url(){
            return admin_url("admin.php?page=woo_email_customizer_page_builder&settings=default");
        }

        public static function plugin_description_content()
        {
            //puc_request_info_result_woo-email-customizer-page-builder
            $content = '<div class="js-item-description item-description has-toggle">
                  <div class="user-html"><p><strong>Revolutionary WooCommerce Email Customizer with Drag and Drop builder. Customize everything in the WooCommerce Email Order notification emails with a drag and drop editor.</strong></p>
                    <p>
                          <a href="https://docs.flycart.org/woocommerce-email-customizer-with-drag-and-drop-email-builder" rel="nofollow">
                            <img alt="WooCommerce Email Customizer with Drag and Drop Email Builder - 1" src="https://demo.flycart.org/codecanyan/documentation.png">
                          </a>
                          <a href="https://www.flycart.org/support" rel="nofollow">
                            <img alt="WooCommerce Email Customizer with Drag and Drop Email Builder - 2" src="https://demo.flycart.org/codecanyan/support.png">
                          </a>
                          <a href="https://codecanyon.net/user/flycart/follow">
                            <img alt="WooCommerce Email Customizer with Drag and Drop Email Builder - 3" src="https://demo.flycart.org/codecanyan/follow-us.png">
                          </a>
                    
                        </p>
                    
                    <p><img alt="Email Customizer for WooCommerce" width="100%" title="Email Customizer for WooCommerce" src="https://demo.flycart.org/codecanyan/woocommerce_3.0_blue.png"> </p>
                    <p><strong>Email Customizer now supports WooCommerce Subscription emails</strong></p>
                    <p>
                    Add a logo, header, footer, body text, custom paragraph texts, social icons, images and more with a simple, intuitive drag and drop interface. Create professional, beautiful transactional emails for your WooCommerce Online store
                    </p>
                    <p><strong>Say goodbye to template based email plugins where you can only edit header and footer. Say hello to the email builder which helps you create beautiful, elegant transactional emails and impress your customers</strong>
                    </p><h3 id="item-description__watch-quick-video-demo-drag-and-drop-email-editor">Watch quick video demo: Drag and drop email editor</h3>
                    <p>IMPORTANT NOTE: The email templates are for all the orders and customers. While designing the email, the editor will ask you to select an order to load SAMPLE data. It is just for sample so that you will get a realtime preview of the email design.  So please do not misunderstand </p>
                    <p>
                    <img alt="WooCommerce Email Customizer" width="100%" src="https://camo.envatousercontent.com/268282d7592fcbb8c48ba879dd81ab1e9f8b16ad/687474703a2f2f64656d6f2e666c79636172742e6f72672f636f646563616e79616e2f776f6f5f656d61696c5f6275696c6465722e676966">
                    
                    </p>
                    
                    <h3 id="item-description__what-is-the-difference-between-other-email-customizers">What is the difference between other email customizers</h3>
                    <p>
                    Well, most of the email customizer plugins allow you to just edit the header, footer and add a logo. Some will just have pre-defined templates.
                    </p>
                    <p>
                    But our plugin allows you to build and customize your email with an easy-to-use email builder. it is like a page builder where you drag and drop elements like text block, paragraph blocks, image blocks and edit them. 
                    </p>
                    <h3 id="item-description__repeat_purchases">Support for Next Purchase Coupons – Drive Repeat purchases and make more dollars per customer </h3>
                    <p>
                    The plugin is more than an email customizer. Send a <strong>single-use, unique coupon code for the next purchase. </strong>Include the Next Order coupon code within the order notification emails itself. Customer can use this coupon code for his next purchase.</p>
                    <p>Just drag and drop the new coupon box and get customers to purchase repeatedly. The feature is powered by our product <a href="https://www.retainful.com?utm_campaign=codecanyon&amp;utm_source=wecddeb&amp;utm_medium=web" rel="nofollow"> Retainful</a> and it’s FREE.
                    </p>
                    <h3 id="item-description__translations">Translations </h3>
                    <p>You can create an email template per language. Example: One email template for English, another in Spanish, yet another in French. 
                    So if a customer visits your site in French language and makes an order, the French email template will be used to send an email.
                    </p><p>
                    </p><p>
                    The items list will be in the customer’s language because we use the default WooCommerce item’s list with the hooks. It is compatible with WPML. So the items list and other WooCommerce supplied texts would be in the customer’s chosen language.
                    </p>
                    <h3 id="item-description__why-you-should-customize-woocommerce-emails">Why you should customize WooCommerce emails ?</h3>
                    <p>A good, professional email adds a lot more value to your online business. It tells you how professional you are, how committed you are. Besides, it is a good business practice to send elegant emails. You don’t want your business to get a bad impression by sending crappy looking emails. </p>
                    <p><strong>Keep customers coming back for more by sending well-designed, professional transactional emails</strong></p>
                    <p>
                    That is why we introduced a powerful drag and drop email builder to customize your woocommerce transactional emails. 
                     Just drag and drop text, heading, images, paragraphs, content blocks, buttons and more. And use the short codes
                      to include dynamic content like order details, customer information, billing address, shipping address and more.   
                    </p>
                    <p>
                      <strong><a href="http://demo.flycart.org/email-customizer/" rel="nofollow">Checkout the live demo at http://demo.flycart.org/email-customizer/</a> </strong>
                    <br>
                     Username: demo  <br>
                     Password: Demo@123 <br>
                    
                    </p>
                    <p>NOTE: The email builder uses latest Vue.js library for advanced realtime building. Try building and customising your emails with modern browsers like Chrome, Firefox  <img alt="\\" data-src="/images/smileys/happy.png">)” title=” :)” /&gt; </p>
                    
                    <h3 id="item-description__what-the-customers-say-about-it">What the customers say about it </h3>
                    
                    <p><img alt="Reviews for Email Customizer for WooCommerce" title="Reviews for Email Customizer for WooCommerce" data-src="https://camo.envatousercontent.com/4e2925edf1ae030f6a1ec228c59cec9ed90ebdc8/687474703a2f2f64656d6f2e666c79636172742e6f72672f636f646563616e79616e2f776f6f5f656d61696c5f637573746f6d697a65725f726576696577732d6d696e2e706e67"> </p>
                    
                    <p>
                      <strong><a href="mailto:support@flycart.org">Got a pre-sales question? Ask us</a></strong>
                    </p>
                    
                    <p>
                    <img alt="WooCommerce Email Customizer with Drag and Drop Email Builder" width="100%" title="WooCommerce Email Customizer with Drag and Drop Email Builder" data-src="https://camo.envatousercontent.com/33b24ce8a70de60c1d008a5da80afb118d86fcc9/687474703a2f2f64656d6f2e666c79636172742e6f72672f636f646563616e79616e2f666561747572655f696d6167655f656d61696c5f637573746f6d697a65722e706e67">
                    </p>
                    <h3 id="item-description__key-features">Key features</h3>
                    <ul>
                    <li>
                      Powerful drag and drop email builder built with Vue.js
                    </li>  
                    <li>
                      15 + elements including text, images, button, paragraphs, social icons and more
                    </li>
                    <li>Customize everything in your WooCommerce order email notifications</li>
                    <li>Include the dynamic data like order summary, customer information, products in the order using short codes</li>
                    <li>Short codes will be dynamically replaced with the respective WooCommerce data before the email is sent to the customer</li>
                    <li>Add your logo, images and a header, a footer, custom body and more</li>
                    <li>Display the order details, customer information, address and more.</li>
                    <li>Live-preview of your changes. Use an order to see how your emails would look like</li>
                    <li>Send a test email to validate your design</li>
                    <li>
                      Customize all WooCommerce Transactional Emails.
                    </li>
                    <li>
                     Send <strong>Next Order coupon code to customers within the transaction emails.
                    </strong>
                    </li>
                    <li>Support for <strong>WooCommerce Custom Order Status manager</strong>
                    </li>
                    <li>
                    </li>
                    <li>Support for <strong>WooCommerce Checkout Field Editor (official plugin)</strong>
                    </li>
                    <li>
                    </li>
                    <li>WordPress Multi-site compatible</li>
                    </ul>
                    </div>
                 </div>';

            return $content;
        }
    }

    WooEmailBuilderUpdateChecker::init();
}
