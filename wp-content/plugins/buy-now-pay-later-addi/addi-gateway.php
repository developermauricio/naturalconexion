<?php
/*
 * Plugin Name: Buy Now Pay Later - ADDI
 * Plugin URI: https://co.addi.com/
 * Description: Ofrece a tus clientes la posibilidad de comprar a cuotas lo que quieran, cuando quieran, pagando después con <strong>Addi</strong>. En minutos. SIN INTERESES. Sin complicaciones.
 * Author: Addi
 * Author URI: https://co.addi.com/
 * Version: 1.5.3
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * License: GPL v2 or later
 * Text Domain: buy-now-pay-later-addi
 * Domain Path: /languages
 */

ini_set("session.cookie_secure", 1);

/*
* file required to use some plugin hooks functions
*/
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );


/*
* registration hook when plugin is activated 
*/
add_action( 'activated_plugin', 'addi_plugin_activated');

/**
 * This function is being executed when activated_plugin is fired
 *  Validations:
 * - current user can activate the plugin
 * - PHP version is compatible
 * - Wordpress version is compatible
 * - Woocommerce class exists in wordpress installation
 * - table is being created
 */
function addi_plugin_activated() { 

    global $woocommerce;
    global $wp;
    global $wpdb;
    global $wp_version;

    $php_version = '7.0';
    $wordpress_version = '5.2';

    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    } 

    if ( version_compare( PHP_VERSION, $php_version, '<' ) ) {
        // deactivating plugin if PHP version is ot compatible
        deactivate_plugins( basename( __FILE__ ) );
        wp_die( __( 'This plugin can not be activated because it requires a PHP version greater than 5.3. Your PHP version can be updated by your hosting company.', 'woocommerce-addon-slug' ), 'Plugin dependency check', array( 'back_link' => true ) );
    }
    elseif ( version_compare( $wp_version, $wordpress_version, '<' ) ) {
        // deactivating plugin if wordpress version is ot compatible
        deactivate_plugins( basename( __FILE__ ) );
        wp_die( __( 'This plugin can not be activated because it requires a WordPress version greater than 3.8. Please go to Dashboard &#9656; Updates to get the latest version of WordPress .', 'woocommerce-addon-slug' ), 'Plugin dependency check', array( 'back_link' => true ) );
    }
    elseif( !class_exists( 'WooCommerce' ) ) {
        // deactivating plugin if woocommerce is not installed
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'Please install and Activate WooCommerce.', 'woocommerce-addon-slug' ), 'Plugin dependency check', array( 'back_link' => true ) );
    }
    else {

        $table_name = $wpdb->prefix . "wc_addi_gateway";
        $table_config_name = $wpdb->prefix . "wc_addi_config";
    
        //check if table already exists
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                order_id int(11) NOT NULL,
                order_status varchar(50) NOT NULL,
                date datetime NOT NULL
            ) $charset_collate;";
        
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        }

        //check if table already exists
        if($wpdb->get_var("SHOW TABLES LIKE '$table_config_name'") != $table_config_name) {

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_config_name (
                element varchar(50) NOT NULL,
                value varchar(50) NOT NULL
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        }
    }
}

/* Register activation hook. */
register_activation_hook( __FILE__, 'fx_addi_admin_notice_activation_hook' );

/**
 * Runs only when the plugin is activated.
 */
function fx_addi_admin_notice_activation_hook() {

    /* Create transient data */
    set_transient( 'fx_addi_admin_notice_transient', true, 5 );
}


/* Add admin notice */
add_action( 'admin_notices', 'fx_addi_admin_notice' );


/**
 * Admin Notice on Activation.
 * @since 0.1.0
 */
function fx_addi_admin_notice(){

    /* Check transient, if available display notice */
    if( get_transient( 'fx_addi_admin_notice_transient' ) ){
      if((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
        ?>
        <div class="updated notice is-dismissible">
            <p><strong>¡Pagamento parcelado com ADDI instalado corretamente!</strong>Agora você pode oferecer aos seus clientes o parcelamento na sua loja.</p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'fx_addi_admin_notice_transient' );
      }
      else{
        ?>
        <div class="updated notice is-dismissible">
            <p><strong>¡Pago a cuotas con ADDI instalado correctamente!</strong> Ya puedes ofrecer a tus clientes que paguen a cuotas en tu tienda.</p>
        </div>
        <div class="updated notice is-dismissible">
            <p><strong>Para garantizar que recibes correctamente la respuesta por parte de ADDI, te invitamos a verificar lo siguiente:</strong></p>
            <p>Dentro del archivo .htaccess del servidor, las siguientes reglas deben estar presentes</p>
            <p><b>CGIPassAuth On</b></p>
            <p><b>RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]</b></p>
            <p><b>SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0</b></p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'fx_addi_admin_notice_transient' );
      }
    }
}

add_action( 'woocommerce_before_add_to_cart_form', 'addi_before_add_to_cart_form' );

function addi_before_add_to_cart_form($argPosition){
    global $product;
    global $wpdb;

    if(!$argPosition) $argPosition = 'some value';

	// LOAD THE WC LOGGER
    $logger = wc_get_logger();
	$styles_to_json = "";
    $table_config_name = $wpdb->prefix . "wc_addi_config";
    $result = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","widget"));
    $position = "";

    $resultV = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","widget_position"));

    if(isset($resultV) && count($resultV) > 0) {

        foreach ($resultV as $itemV) {
            $newPosition = $itemV->value;
            $position = $newPosition;
        }

    }
    else {
        $wpdb->insert($table_config_name, array('element' => 'widget_position', 'value' => 'woocommerce_before_add_to_cart_form'));

        $position = "woocommerce_before_add_to_cart_form";
    }


    if($position === $argPosition) {
                            
        // verifying the integrity of the resulset, otherwise could throw an error.
        if(isset($result) && count($result) > 0) {

            try{
            //variables for widget css
            $widgetBorderColor_ = '';
            $widgetBorderRadius_ = '';
            $widgetFontColor_ = '';
            $widgetFontFamily_ = '';
            $widgetFontSize_ = '';
            $widgetBadgeBackgroundColor_ = '';
            $widgetInfoBackgroundColor_ = '';
            $widgetMargin_ = '';
            //varibles for modal css
            $modalBackgroundColor_ = '';
            $modalFontColor_ = '';
            $modalPriceColor_ = '';
            $modalBadgeBackgroundColor_ = '';
            $modalBadgeBorderRadius_ = '';
            $modalBadgeFontColor_ = '';
            $modalBadgeLogoStyle_ = '';
            $modalCardColor_ = '';
            $modalButtonBorderColor_ = '';
            $modalButtonBorderRadius_ = '';
            $modalButtonBackgroundColor_ = '';
            $modalButtonFontColor_ = '';
            
                foreach ($result as $item) {
                    $newValue = $item->value;

                    $getSalePriceFromPlugin = "";
                    $split = explode("|",$newValue);
                    $countEscape = 0;
                    $bol = $split[0];
                    $slug = $split[1];
                    $price = $product->get_price_html();
                    //print_r($price);
                    if(strpos(strip_tags( $price ), '-' ) == false &&
                    strpos(strip_tags( $price ), ':' ) == false) {
                        $escape = explode( " ", strip_tags( $price ));

                        foreach ($escape as &$value1) {
                            $countEscape+= 1;
                        }


                        if($countEscape > 1) {

                            foreach ($escape as $key => &$value_if) {

                                $matches = array();
                                $value_formatted = str_replace("&#36;", "$", $value_if);
                                $value_formatted = str_replace(".", "", $value_formatted);
                                preg_match_all("/([$][0-9]+)/", $value_formatted, $matches);

                                $first_match = $matches[0];
                                $match_in_array = null;

                                if(is_array($first_match)) {
                                    $match_in_array = $first_match[0];
                                }

                                //if ($key == 0) $price_regular = $matches[0];
                                //if ($key == 1) $price_sale = $matches[0];
                                if ($key == 0 ) $price_regular = $match_in_array !== '' ? $match_in_array : $matches[0];
                                if ($key == 1) $price_sale = $match_in_array !== '' ? $match_in_array : $matches[0];
                                
                            }

                            if(isset($price_sale) && $price_sale !== "") {

                                $price_sale = str_replace("$", "", $price_sale);
                                $getSalePriceFromPlugin = $price_sale;

                            }

                        }
                        else {

                            //print_r($escape);
                            foreach ($escape as &$value) {
                                $matches = array();
                                //$logger->info( '>>>> value normal :' . $value . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                $value_formatted = str_replace("&#36;", "$", $value);
                                $value_formatted = str_replace(".", "", $value_formatted);
                                //$logger->info( '>>>> value formatted :' . $value_formatted . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                preg_match_all("/([$][0-9]+)/", $value_formatted, $matches);
                                $prices_match = $matches[0];
                                //$logger->info( '>>>> match count :' . count($matches) . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                //$logger->info( '>>>> price match count :' . count($prices_match) . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                
                                // foreach ($matches as $item) {
                                //     $logger->info( '>>>> item position 0 :' . $item . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                // }
                                
                                if(isset($prices_match) && count($prices_match) > 1) {
                                    $price_regular = $prices_match[0];
                                    $price_sale = $prices_match[1];
                                    $price_sale = str_replace("$", "", $price_sale);
                                    $getSalePriceFromPlugin = $price_sale;
                                    //$logger->info( '>>>> price regular:' . $price_regular . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                    //$logger->info( '>>>> price sales:' . $price_sale . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                                    //$logger->info( '>>>> get Sale price from Plugin :' . $getSalePriceFromPlugin . ' ', array( 'source' => 'addi-widget-handler-log' ) );
                            }

                        }

                    }

                    }

                    $conf_result = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element like %s","conf_%"));

                    //print_r($conf_result);

                    if(isset($conf_result) && count($conf_result) > 0) {
                        // LOAD THE WC LOGGER
                        $logger = wc_get_logger();
                        try{

                            foreach ($conf_result as $item) {
                                $conf_type = $item->element;
                                $conf_value = $item->value;

                                switch ($conf_type) {
                                    case "conf_widgetBorderColor":
                                        $widgetBorderColor_ = $conf_value;
                                    break;
                                    case "conf_widgetBorderRadius":
                                        $widgetBorderRadius_ = $conf_value;
                                    break;
                                    case "conf_widgetFontColor":
                                        $widgetFontColor_ = $conf_value;
                                    break;
                                    case "conf_widgetFontFamily":
                                        $widgetFontFamily_ = $conf_value;
                                    break;
                                    case "conf_widgetFontSize":
                                        $widgetFontSize_ = $conf_value;
                                    break;
                                    case "conf_widgetBadgeBackgroundColor":
                                        $widgetBadgeBackgroundColor_ = $conf_value;
                                    break;
                                    case "conf_widgetInfoBackgroundColor":
                                        $widgetInfoBackgroundColor_ = $conf_value;
                                    break;
                                    case "conf_widgetMargin":
                                        $widgetMargin_ = $conf_value;
                                    break;
                                    case "conf_modalBackgroundColor":
                                        $modalBackgroundColor_ = $conf_value;
                                    break;
                                    case "conf_modalFontColor":
                                        $modalFontColor_ = $conf_value;
                                    break;
                                    case "conf_modalPriceColor":
                                        $modalPriceColor_ = $conf_value;
                                    break;
                                    case "conf_modalBadgeBackgroundColor":
                                        $modalBadgeBackgroundColor_ = $conf_value;
                                    break;
                                    case "conf_modalBadgeBorderRadius":
                                        $modalBadgeBorderRadius_ = $conf_value;
                                    break;
                                    case "conf_modalBadgeFontColor":
                                        $modalBadgeFontColor_ = $conf_value;
                                    break;
                                    case "conf_modalBadgeLogoStyle":
                                        $modalBadgeLogoStyle_ = $conf_value;
                                    break;
                                    case "conf_modalCardColor":
                                        $modalCardColor_ = $conf_value;
                                    break;
                                    case "conf_modalButtonBorderColor":
                                        $modalButtonBorderColor_ = $conf_value;
                                    break;
                                    case "conf_modalButtonBorderRadius":
                                        $modalButtonBorderRadius_ = $conf_value;
                                    break;
                                    case "conf_modalButtonBackgroundColor":
                                        $modalButtonBackgroundColor_ = $conf_value;
                                    break;
                                    case "conf_modalButtonFontColor":
                                        $modalButtonFontColor_ = $conf_value;
                                    break;
                                }
                            }
                        }
                        catch(Exception $e) {
                                $logger->info( 'Error getting data from database: ' . $e . ' ', array( 'source' => 'addi-error-handler-log' ) );
                        }
                    }

                    $styles_to_json = '{"widget": {"borderColor": "'. $widgetBorderColor_ .'","borderRadius": "'. $widgetBorderRadius_ .'","fontColor": "'. $widgetFontColor_ .'","fontFamily": "'. $widgetFontFamily_ .'","fontSize": "'. $widgetFontSize_ .'","badgeBackgroundColor": "'. $widgetBadgeBackgroundColor_ .'","infoBackgroundColor": "'. $widgetInfoBackgroundColor_ .'","margin": "'. $widgetMargin_ .'","whiteLogo":'. $modalBadgeLogoStyle_ .'},"modal":{"backgroundColor": "'. $modalBackgroundColor_ .'","fontColor": "'. $modalFontColor_ .'","fontFamily": "system-ui","priceColor": "'. $modalPriceColor_ .'","badgeBorderRadius": "'. $modalBadgeBorderRadius_ .'","badgeBackgroundColor": "'. $modalBadgeBackgroundColor_ .'","badgeFontColor":"'. $modalBadgeFontColor_ .'","cardColor": "'. $modalCardColor_ .'","buttonBorderColor": "'. $modalButtonBorderColor_ .'",	"buttonBorderRadius": "'. $modalButtonBorderRadius_ .'","buttonBackgroundColor": "'. $modalButtonBackgroundColor_ .'","buttonFontColor": "'. $modalButtonFontColor_ .'"}}';

                    //print_r($styles_to_json);

                    if($bol == 'yes') {
                        if((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
                            if( $product->is_on_sale() && $product->get_sale_price() !== "" ) {
                                //print_r('step 1');
                                echo "<addi-widget-br custom-widget-styles='". $styles_to_json ."' price='" . $product->get_sale_price() . "' ally-slug='" . $slug . "'></addi-widget-br>";
                            }
                            elseif($getSalePriceFromPlugin !== "") {
                                //print_r('step 2');
                                //print_r($getSalePriceFromPlugin);
                                echo "<addi-widget-br custom-widget-styles='". $styles_to_json ."' price='" . $getSalePriceFromPlugin . "' ally-slug='" . $slug . "'></addi-widget-br>";
                            }
                            else {
                            //print_r('step 3');
                            echo "<addi-widget-br custom-widget-styles='". $styles_to_json ."' price='" . $product->price . "' ally-slug='" . $slug . "'></addi-widget-br>";
                            }
                        }
                        else {
                            if( $product->is_on_sale() && $product->get_sale_price() !== ""  ) {
                            //print_r('step 1');
                            echo "<addi-widget custom-widget-styles='". $styles_to_json ."' price='" . $product->get_sale_price() . "' ally-slug='" . $slug . "'></addi-widget>";
                            }
                            elseif($getSalePriceFromPlugin !== "") {
                            //print_r('step 2');
                            //print_r($getSalePriceFromPlugin);
                            echo "<addi-widget custom-widget-styles='". $styles_to_json ."' price='" . $getSalePriceFromPlugin . "' ally-slug='" . $slug . "'></addi-widget>";
                            }
                            else {
                            //print_r('step 3');
                            echo "<addi-widget custom-widget-styles='". $styles_to_json ."' price='" . $product->price . "' ally-slug='" . $slug . "'></addi-widget>";
                            }
                        }

                    }

                    break;
                }
            }
            catch(Exception $e) {

            }
        }

    }
}

/**
* Registering compatible options for addi widget rendering
*/
$arg = "woocommerce_before_add_to_cart_form";
add_action('woocommerce_before_add_to_cart_form', function() { global $arg; addi_before_add_to_cart_form($arg); }, 10);
$arg2 = "woocommerce_before_single_product_summary";
add_action('woocommerce_before_single_product_summary', function() { global $arg2; addi_before_add_to_cart_form($arg2); }, 10);
$arg3 = "woocommerce_before_variations_form";
add_action('woocommerce_before_variations_form', function() { global $arg3; addi_before_add_to_cart_form($arg3); }, 10);
$arg4 = "woocommerce_before_single_variation";
add_action('woocommerce_before_single_variation', function() { global $arg4; addi_before_add_to_cart_form($arg4); }, 10);
$arg5 = "woocommerce_after_add_to_cart_button";
add_action('woocommerce_after_add_to_cart_button', function() { global $arg5; addi_before_add_to_cart_form($arg5); }, 10);
$arg6 = "woocommerce_after_variations_form";
add_action('woocommerce_after_variations_form', function() { global $arg6; addi_before_add_to_cart_form($arg6); }, 10);
$arg7 = "woocommerce_after_add_to_cart_form";
add_action('woocommerce_after_add_to_cart_form', function() { global $arg7; addi_before_add_to_cart_form($arg7); }, 10);
$arg8 = "woocommerce_product_meta_start";
add_action('woocommerce_product_meta_start', function() { global $arg8; addi_before_add_to_cart_form($arg8); }, 10);
$arg9 = "woocommerce_product_meta_end";
add_action('woocommerce_product_meta_end', function() { global $arg9; addi_before_add_to_cart_form($arg9); }, 10);
$arg10 = "woocommerce_share";
add_action('woocommerce_share', function() { global $arg10; addi_before_add_to_cart_form($arg10); }, 10);

/**
* Load custom CSS and JavaScript.
*/
add_action( 'wp_enqueue_scripts', 'addi_my_enqueue_scripts' );

function addi_my_enqueue_scripts() {
    // Enqueue my scripts.

    //Addi widget
    if((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
        $script_chunks = array(
                'product' => array('addi-widgets-container-br', 'addi-widget-br', 'addi-banner-br', 'addi-onboarding-br', 'addi-one-click-checkout-br'),
                'home' => array('addi-home-banner-br')
        );
    } else {
        $script_chunks = array(
            'product' => array('addi-widgets-container', 'addi-widget', 'addi-banner', 'addi-onboarding', 'addi-one-click-checkout'),
            'home' => array('addi-home-banner')
        );
    }
    $home_url = wp_make_link_relative(home_url()). '/';
    $country = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'br' : 'co';
    $is_product = json_encode(is_product());

    wp_register_script( 'widget-addi', 'https://s3.amazonaws.com/statics.addi.com/woocommerce/woocommerce-widget-wrapper.bundle.min.js', array(), null, true );
    wp_enqueue_script( 'widget-addi', 'https://s3.amazonaws.com/statics.addi.com/woocommerce/woocommerce-widget-wrapper.bundle.min.js', array(), null, true );

    wp_localize_script( 'widget-addi', 'addiParams', array ('country' => $country,
        'script_chunks' => $script_chunks,
        'home_url' => $home_url,
        'is_product' => $is_product));

    // Amplitude
    wp_enqueue_script( 'addi-amplitude', plugins_url( '/js/addi-amplitude.js' , __FILE__ ), array(), null, true );
    wp_enqueue_script( 'frontend-functions', plugins_url( '/js/frontend-functions.js' , __FILE__ ), array('addi-amplitude'), null, true );

    // Enqueue styles.
    wp_enqueue_style( 'widget-addi-style', plugins_url( '/css/style.css' , __FILE__ ) );
    // Add filters to catch and modify the styles and scripts as they're loaded.
    // Running this filter after everything to prevent conflicts with other plugins
    add_filter( 'script_loader_tag', __NAMESPACE__ . '\addi_my_add_attributes', 100, 2 );
}

/**
* Custom status styles in admin site
*/
add_action( 'admin_head', function () { ?>
	<style>
     mark.order-status.status-addi-approved {
        background: #c6e1c6 !important;
        color: #5b841b !important;
     }
	</style>
<?php } );

/**
* Add attributes based on defined script/style handles.
*/
function addi_my_add_attributes( $html, $handle ) : string {
	//echo "<h3> " . $handle . "</h3>";
    global $wpdb;
    $table_config_name = $wpdb->prefix . "wc_addi_config";

    switch( $handle ) {
        case 'widget-addi':
            #TODO: Update this function to remove the query
            $result = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","widget_home"));
            if(isset($result) && count($result) > 0) {
                foreach ($result as $item) {
                    $newValue = $item->value;
                    $split = explode("|",$newValue);
                    $enabled = $split[0];
                    $bannerType = $split[1];
                    $elementReference = $split[2];
                    $allySlug = $split[3];

                    if($enabled == 'yes') {

                       if(isset($elementReference) && $elementReference !== 'default') {
                        $html = str_replace( '></script>', ' data-banner-element-reference='.$elementReference.' data-ally-slug='. $allySlug.' data-banner-id='.$bannerType.' data-name=wooAddiHomeBanner data-show-banner=true></script>', $html );
                       }
                       else {
                        $html = str_replace( '></script>', ' data-banner-element-reference='.$elementReference.' data-ally-slug='.$allySlug.' data-name=wooAddiHomeBanner data-show-banner=true></script>', $html );
                       }

                    }
                    else {
                        $html = str_replace( '></script>', ' data-ally-slug='.$allySlug.' data-name=wooAddiHomeBanner></script>', $html );
                    }
                }
            }
            else {
                $resultW = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","widget"));

                // verifying the integrity of the resulset, otherwise could throw an error.
                if(isset($resultW) && count($resultW) > 0) {

                    foreach ($resultW as $item) {
                        $newValue = $item->value;
                        $split = explode("|",$newValue);
                        $bol = $split[0];
                        $slug = $split[1];

                        $html = str_replace( '></script>', ' data-ally-slug='.$slug.' data-name=wooAddiHomeBanner></script>', $html );
                    }

                }
            }
            //$html = str_replace( '></script>', ' data-banner-element-reference=#ms-masthead data-show-banner=true></script>', $html );
            break;
    }
    return $html;
}

add_action( 'admin_enqueue_scripts', 'addi_selectively_enqueue_admin_script' );

/**
 * Enqueue a script in the WordPress admin on edit.php.
 *
 * @param int $hook Hook suffix for the current admin page.
 */
function addi_selectively_enqueue_admin_script() {
    global $wp;
    $current_url = home_url($_SERVER['REQUEST_URI']);

    if (strpos($current_url, 'page=wc-settings&tab=checkout&section=addi' ) == false) {
        return;
    }

    // loading js
    wp_register_script( 'addi-js', plugins_url( '/js/functions.js' , __FILE__ ), array(), null, true );
    wp_enqueue_script( 'addi-js' );
    // Enqueue styles.
    wp_enqueue_style( 'addi-admin-style', plugins_url( '/css/admin-style.css' , __FILE__ ) );

}

add_action( 'init', 'addi_load_textdomain' );
/**
 * Load plugin textdomain.
 */
function addi_load_textdomain() {
    load_plugin_textdomain( 'buy-now-pay-later-addi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'verify_database' );
/**
 * Verify if table exists or not in database
 */
function verify_database() {
    global $wp;
    global $wpdb;

    if(!isset($_COOKIE['database_validation'])) {

        $table_config_name = $wpdb->prefix . "wc_addi_config";
        //check if table already exists
        if($wpdb->get_var("SHOW TABLES LIKE '$table_config_name'") != $table_config_name) {

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_config_name (
                element varchar(50) NOT NULL,
                value varchar(50) NOT NULL
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            $wpdb->insert($table_config_name, array('element' => 'widget', 'value' => 'no|'));

        }

        setcookie( "database_validation", true, strtotime( '+30 days' ) );
    }
}

add_filter( 'woocommerce_available_payment_gateways', 'woocommerce_available_payment_gateways' );
function woocommerce_available_payment_gateways( $available_gateways ) {
    if (! is_checkout() ) return $available_gateways;
    if (array_key_exists('addi',$available_gateways)) {
		 if((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
			 $available_gateways['addi']->order_button_text = __( 'Pagar com Addi', 'woocommerce' );
		 }else {
			 $available_gateways['addi']->order_button_text = __( 'Paga con Addi', 'woocommerce' );
		 }
    }
    return $available_gateways;
}

add_action( 'init', 'intercept_checkout_url' );
/**
 * Function to intercept checkout url with specific query param
 */
function intercept_checkout_url() {

    global $woocommerce;
    global $wp;
    global $wpdb;

	// LOAD THE WC LOGGER
    $logger = wc_get_logger();

    $table_name = $wpdb->prefix . "wc_addi_gateway";

	if( isset( $_GET['wc-order-id']) ) {

        $orderId = $_GET['wc-order-id'];

        // This id is registered on database, so it's needed to see its status.
        $result = $wpdb->get_results($wpdb->prepare("select * from {$table_name} where order_id = %d", $orderId));

        // verifying the integrity of the resulset, otherwise could throw an error.
        if(isset($result) && count($result) > 0) {
            try{

                foreach ($result as $item) {
                    $order_id = $item->order_id;
                    $order_status = $item->order_status;
                }
            }
            catch(Exception $e) {
                  $logger->info( 'Error getting data from database: ' . $e . ' ', array( 'source' => 'addi-error-handler-log' ) );
            }

            if(isset($order_id) && isset($order_status)) {
                if($order_status == 'APPROVED') {

                    $wpdb->delete( $table_name, array( 'order_id' => $orderId ) );

                    // get woocommerce order object
                    $order = wc_get_order( $order_id );

					$return_url = $order->get_checkout_order_received_url();
                    // Redirect to the thank you page
					echo "<script>window.location = '" .$return_url ."';</script>";
                }
                else {

					// get woocommerce order object
                    $order = wc_get_order( $orderId );

					// Get and Loop Over Order Items
					foreach ( $order->get_items() as $item_id => $item ) {

						//Get the WC_Product object
						$product = $item->get_product();
						$product_id = $product->get_id();
						WC()->cart->generate_cart_id( $product_id );
						WC()->cart->add_to_cart( $product_id);
					}

                }
            }
        }
	}
}

add_action( 'init', 'register_custom_statuses_as_order_status' );
function register_custom_statuses_as_order_status() {
    register_post_status( 'wc-addi-approved', array(
        'label'                     => get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Transação Aprovada' : 'Transacción Aprobada' ,//__('Transacción Aprobada','buy-now-pay-later-addi'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? _n_noop( 'Transação Aprovada <span class="count">(%s)</span>', 'Transação Aprovada <span class="count">(%s)</span>' ) : _n_noop( 'Transaccion Aprobada <span class="count">(%s)</span>', 'Transaccion Aprobada <span class="count">(%s)</span>' )
    ) );

    register_post_status( 'wc-addi-declined', array(
        'label'                     => get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Transação Não Aprovada': 'Transacción No Aprobada',//__('Transacción No Aprobada','buy-now-pay-later-addi'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? _n_noop( 'Transação Não Aprovada <span class="count">(%s)</span>', 'Transação Não Aprovada <span class="count">(%s)</span>' ) : _n_noop( 'Transaccion Aprobada <span class="count">(%s)</span>', 'Transaccion Aprobada <span class="count">(%s)</span>' )
    ) );
}

// Add to list of WC Order statuses
add_filter( 'wc_order_statuses', 'add_additional_custom_statuses_to_order_statuses' );
function add_additional_custom_statuses_to_order_statuses( $order_statuses ) {
    $new_order_statuses = array();
    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {
        $new_order_statuses[ $key ] = $status;
        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-addi-approved'] = get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Transação Aprovada' : 'Transacción Aprobada';//__('Transacción Aprobada','buy-now-pay-later-addi');
            $new_order_statuses['wc-addi-declined'] = get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Transação Não Aprovada' : 'Transacción No Aprobada';//__('Transacción No Aprobada','buy-now-pay-later-addi');
        }
    }
    return $new_order_statuses;
}

// Adding new custom status to admin order list bulk dropdown
add_filter( 'bulk_actions-edit-shop_order', 'custom_dropdown_bulk_actions_shop_order', 50, 1 );
function custom_dropdown_bulk_actions_shop_order( $actions ) {
    $new_actions = array();

    // add new order status before processing
    foreach ($actions as $key => $action) {
        if ('mark_processing' === $key)
		    $new_actions['mark_addi-approved'] = get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Alterar status para Transação Aprovada' : 'Cambiar estado a Transacción Aprobada';//__( 'Change status to Transacción Aprobada', 'woocommerce' );
            $new_actions['mark_addi-declined'] = get_locale() == 'pt_PT' || get_locale() == 'pt_BR' ? 'Alterar status para Transação Não Aprovada' : 'Cambiar estado a Transacción No Aprobada';//__( 'Change status to Transacción No Aprobada', 'woocommerce' );
        $new_actions[$key] = $action;
    }
    return $new_actions;
}

 /*
 * This filter hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'addi_add_gateway_class' );
function addi_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Addi_Gateway';
	return $gateways;
}

$brazilCheckoutFieldspluginPath = 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php';
$checkoutFieldEditorAndManagerForWoocommercePath = 'checkout-field-editor-and-manager-for-woocommerce/start.php';
$fieldEditorForWoocommercePluginPath = 'woo-checkout-field-editor-pro/checkout-form-designer.php';
$yithWoocommerceCheckoutManagerPath = 'yith-woocommerce-checkout-manager/init.php';


//check if id fiel exists in database
global $wpdb;
$id_field_exists = false;

$table_config_name = $wpdb->prefix . "wc_addi_config";
$result = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","field_id"));

if(isset($result) && count($result) > 0) {

   foreach ($result as $item) {
        $id_field = $item->value;
   }

   if(isset($id_field) && $id_field !== '') {
      $id_field_exists = true;
   }
    
}
else {
    $wpdb->insert($table_config_name, array('element' => 'field_id', 'value' => ''));
}


if(is_plugin_active( $fieldEditorForWoocommercePluginPath ) ||
               is_plugin_active( $checkoutFieldEditorAndManagerForWoocommercePath ) ||
               is_plugin_active( $brazilCheckoutFieldspluginPath ) ||
               is_plugin_active( $yithWoocommerceCheckoutManagerPath ) ||
               $id_field_exists == true) {
    /**
     * Denial do not work for this hook, so it is necessary this piece of code to avoid errors.
    */
}
else{
    /* Register activation hook. */
    add_filter('woocommerce_checkout_fields', 'addi_custom_woocommerce_billing_fields' );
    
}

/**
 * Insert new billing cedula field if it does not exists
 *
 * @param string $fields     array of objects containing checkout fields.
 *
 * @return array             new array of checkout fields.
 */
function addi_custom_woocommerce_billing_fields($fields)
{
    $newFields = $fields;

    $fieldFound = false;

    // new array based on billings array
    $newFields['billing'] = array();

    //loop in array to verify if billing_cedula field exists or not
    foreach($fields['billing'] as $key1 => $billing) {

        if ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {

            if ( !class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
                if(strpos($key1, 'billing_cpf' ) !== false || strpos($key1, 'billing_id' ) !== false) {

                    $fieldFound = true;
                    break;

                }
            }
            else{
                $fieldFound = true;
            }

        }
        else {

            if(strpos($key1, 'billing_cedula' ) !== false ||
               strpos($key1, 'billing_id' ) !== false ||
               strpos($key1, 'billing_nmero' ) !== false ||
               strpos($key1, 'billing_numero' ) !== false ) {

                $fieldFound = true;
                break;

            }

        }
    }

    // if the field does not exists, then insert it before billing_last_name field
    if(!$fieldFound ) {

        foreach($fields['billing'] as $key1 => $billing) {
            $newFields['billing'][$key1] = $billing;
            if( strpos($key1, 'billing_last_name' ) !== false ) {

                if ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
                    $newFields['billing']['billing_cpf'] = array(
                        'label' => __('CPF', 'buy-now-pay-later-addi'), // Add custom field label
                        'placeholder' => _x('', 'placeholder', 'buy-now-pay-later-addi'), // Add custom field placeholder
                        'required' => true, // if field is required or not
                        'clear' => false, // add clear or not
                        'type' => 'text', // add field type
                        'class' => array('form-row-wide')   // add class name
                    );
                }
                else {
                    $newFields['billing']['billing_id'] = array(
                        'label' => __('Cédula', 'buy-now-pay-later-addi'), // Add custom field label
                        'placeholder' => _x('', 'placeholder', 'buy-now-pay-later-addi'), // Add custom field placeholder
                        'required' => true, // if field is required or not
                        'clear' => false, // add clear or not
                        'type' => 'text', // add field type
                        'class' => array('form-row-wide')   // add class name
                    );
                }

            }
        };
    }

    return $newFields;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'addi_init_gateway_class' );

/**
 * Init plugin class
 */
function addi_init_gateway_class() {

    // verify if woocommerce was installed, this plugin can´t extends its components if there are not classes in wordpress installation.
    if( !class_exists( 'WooCommerce' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'Please install and Activate WooCommerce.', 'woocommerce-addon-slug' ), 'Plugin dependency check', array( 'back_link' => true ) );
    }

    if (!class_exists('WC_Payment_Gateway'))
        return; // if the WC payment gateway class is not available, do nothing

    //class extend from woocommerce class , with this addi will be a new payment gateway.
	class WC_Addi_Gateway extends WC_Payment_Gateway {
 		/**
 		 * Class constructor
 		 */
 		public function __construct() {

            global $woocommerce;
            global $post;
            global $wp;
            global $wpdb;

            $background_color = get_background_color();

            // Define plugin attributes.
            $this->id = 'addi';
            $this->icon = strpos($background_color, '000' ) !== false ? plugins_url( './assets/ADDI_logo_white.png', __FILE__ ) : plugins_url( './assets/ADDI_logo.png', __FILE__ );
            $this->has_fields = false;
            $this->method_title = _x( 'Addi', 'Addi', 'buy-now-pay-later-addi' );
            $this->method_description = __( 'Pago a cuotas - ADDI.', 'buy-now-pay-later-addi' );

            $this->supports = array(
                'products'
            );

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables.
            // Pre defined , this cannot be changed.
            $this->title = __( 'Paga a cuotas', 'buy-now-pay-later-addi' );
            $this->enabled = $this->get_option( 'enabled' );
            $this->widget_enabled = $this->get_option( 'widget_enabled' );
            $this->widget_slug  = $this->get_option( 'widget_slug' );

            $this->description  = $this->get_option( 'description' );
            $this->field_billing_first_name = $this->get_option( 'field_billing_first_name' );
            $this->field_billing_last_name = $this->get_option( 'field_billing_last_name' );
            $this->field_id = $this->get_option( 'field_id' );
            $this->field_billing_city = $this->get_option( 'field_billing_city' );
            $this->field_billing_email = $this->get_option( 'field_billing_email' );
            $this->field_billing_phone = $this->get_option( 'field_billing_phone' );

            $this->prod_client_id = $this->get_option( 'prod_client_id' );
            $this->prod_client_secret = $this->get_option( 'prod_client_secret' );
            $this->testmode = 'yes' === $this->get_option( 'testmode' );
            $this->custom_order_status = 'yes' === $this->get_option( 'custom_order_status' );
            $this->logs = 'yes' === $this->get_option( 'logs' );
            // Pre defined , this cannot be changed.
            $this->callback_user = 'AddiWooCommercePlugin2021';
            $this->callback_password = 'jDb!mW!ePWjt9z6';

            //Widget position
            $this->conf_widget_position = $this->get_option( 'conf_widget_position' );

			//Widget Css properties
			$this->widgetBorderColor = $this->get_option( 'widgetBorderColor' );
			$this->widgetBorderRadius = $this->get_option( 'widgetBorderRadius' );
			$this->widgetFontColor = $this->get_option( 'widgetFontColor' );
			$this->widgetFontFamily = $this->get_option( 'widgetFontFamily' );
			$this->widgetFontSize = $this->get_option( 'widgetFontSize' );
			$this->widgetBadgeBackgroundColor = $this->get_option( 'widgetBadgeBackgroundColor' );
			$this->widgetInfoBackgroundColor = $this->get_option( 'widgetInfoBackgroundColor' );
			$this->widgetMargin = $this->get_option( 'widgetMargin' );
			//Modal Css properties
			$this->modalBackgroundColor = $this->get_option( 'modalBackgroundColor' );
			$this->modalFontColor = $this->get_option( 'modalFontColor' );
			$this->modalPriceColor = $this->get_option( 'modalPriceColor' );
			$this->modalBadgeBackgroundColor = $this->get_option( 'modalBadgeBackgroundColor' );
			$this->modalBadgeBorderRadius = $this->get_option( 'modalBadgeBorderRadius' );
			$this->modalBadgeFontColor = $this->get_option( 'modalBadgeFontColor' );
            $this->modalBadgeLogoStyle = 'yes' === $this->get_option( 'modalBadgeLogoStyle' );
			$this->modalCardColor = $this->get_option( 'modalCardColor' );
			$this->modalButtonBorderColor = $this->get_option( 'modalButtonBorderColor' );
			$this->modalButtonBorderRadius = $this->get_option( 'modalButtonBorderRadius' );
			$this->modalButtonBackgroundColor = $this->get_option( 'modalButtonBackgroundColor' );
			$this->modalButtonFontColor = $this->get_option( 'modalButtonFontColor' );

            //Widget Home properties
            $this->field_widget_position = $this->get_option( 'field_widget_position' );
            $this->field_widget_type = $this->get_option( 'field_widget_type' );
            $this->element_reference = $this->get_option( 'element_reference' );
            $this->widget_home_enabled = $this->get_option( 'widget_home_enabled' );

            // action hook to update options to new payment gateway
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            // action hook to link css / javascripts files or related to it.
            add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

            // action hook to init callback_handler function with the class
            add_action('init', 'callback_handler');

            // action hook to register callack function to a woocommerce rest api
            add_action( 'woocommerce_api_wc_addi_gateway', array( $this, 'callback_handler', ) );

            add_action('update_option', function( $option_name, $old_value, $value ) {

                global $wpdb;

                $table_config_name = $wpdb->prefix . "wc_addi_config";

                $newValue = $this->get_option( 'widget_enabled' );
                $newSlug = $this->get_option( 'widget_slug' );
                $newWidgetPosition = $this->get_option( 'conf_widget_position' );

                //field id control
                $newfieldId = $this->get_option( 'field_id' );

                //custom order status
                $customOrderStatus = $this->get_option( 'custom_order_status' );

                //Widget Home
                $newFieldWidgetPosition = $this->get_option( 'field_widget_position' );
                $newFieldWidgetType = $this->get_option( 'field_widget_type' );
                $newElementReference = $this->get_option( 'element_reference' );
                $newWidgetHomeEnabled = $this->get_option( 'widget_home_enabled' );

                $modalBadgeLogoStyleValue_ = 'false';

                switch($newFieldWidgetPosition) {
                    case 'on_header':
                        $newElementReference = 'header';
                        break;
                    case 'on_footer':
                        $newElementReference = '#content';
                        break;
                }

				//variables for widget css
                $widgetBorderColor_ = $this->get_option( 'widgetBorderColor' );
                $widgetBorderRadius_ = $this->get_option( 'widgetBorderRadius' );
                $widgetFontColor_ = $this->get_option( 'widgetFontColor' );
                $widgetFontFamily_ = $this->get_option( 'widgetFontFamily' );
                $widgetFontSize_ = $this->get_option( 'widgetFontSize' );
                $widgetBadgeBackgroundColor_ = $this->get_option( 'widgetBadgeBackgroundColor' );
                $widgetInfoBackgroundColor_ = $this->get_option( 'widgetInfoBackgroundColor' );
                $widgetMargin_ = $this->get_option( 'widgetMargin' );
                //varibles for modal css
                $modalBackgroundColor_ = $this->get_option( 'modalBackgroundColor' );
                $modalFontColor_ = $this->get_option( 'modalFontColor' );
                $modalPriceColor_ = $this->get_option( 'modalPriceColor' );
                $modalBadgeBackgroundColor_ = $this->get_option( 'modalBadgeBackgroundColor' );
                $modalBadgeBorderRadius_ = $this->get_option( 'modalBadgeBorderRadius' );
                $modalBadgeFontColor_ = $this->get_option( 'modalBadgeFontColor' );
                $modalBadgeLogoStyle_ = 'yes' === $this->get_option( 'modalBadgeLogoStyle' );
                $modalCardColor_ = $this->get_option( 'modalCardColor' );
                $modalButtonBorderColor_ = $this->get_option( 'modalButtonBorderColor' );
                $modalButtonBorderRadius_ = $this->get_option( 'modalButtonBorderRadius' );
                $modalButtonBackgroundColor_ = $this->get_option( 'modalButtonBackgroundColor' );
                $modalButtonFontColor_ = $this->get_option( 'modalButtonFontColor' );

                $modalBadgeLogoStyleValue_ = ($modalBadgeLogoStyle_ == 1 || $modalBadgeLogoStyle_ == '1')  ?
                    'true' : 'false';

                if($option_name == 'woocommerce_addi_settings') {

                    $result = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","widget"));

                    if(isset($result) && count($result) > 0) {
                        $wpdb->update( $table_config_name, array( 'value' => $newValue . '|' . $newSlug), array( 'element' => 'widget' ));
                    }
                    else {
                        $wpdb->insert($table_config_name, array('element' => 'widget', 'value' => $newValue . '|' . $newSlug));
                    }

                      /* FIELD ID */
                        $resultFI = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","field_id"));

                        if(isset($resultFI) && count($resultFI) > 0) {
                            $wpdb->update( $table_config_name, array( 'value' => $newfieldId), array( 'element' => 'field_id' ));
                        }
                        else {
                            $wpdb->insert($table_config_name, array('element' => 'field_id', 'value' => $newfieldId));
                        }
                      /* FIELD ID */

                     /* WIDGET POSITION */
                     $resultV = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","widget_position"));

                     if(isset($resultV) && count($resultV) > 0) {
                         $wpdb->update( $table_config_name, array( 'value' => $newWidgetPosition), array( 'element' => 'widget_position' ));
                     }
                     else {
                         $wpdb->insert($table_config_name, array('element' => 'widget_position', 'value' => $newWidgetPosition));
                     }
                     /* WIDGET POSITION */

                    $conf_result = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element like %s","conf_%"));

                    if(isset($conf_result) && count($conf_result) > 0) {
                        //update statements for widget css
                        $wpdb->update( $table_config_name, array( 'value' => $widgetBorderColor_), array( 'element' => 'conf_widgetBorderColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $widgetBorderRadius_), array( 'element' => 'conf_widgetBorderRadius' ));
                        $wpdb->update( $table_config_name, array( 'value' => $widgetFontColor_), array( 'element' => 'conf_widgetFontColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $widgetFontFamily_), array( 'element' => 'conf_widgetFontFamily' ));
                        $wpdb->update( $table_config_name, array( 'value' => $widgetFontSize_), array( 'element' => 'conf_widgetFontSize' ));
                        $wpdb->update( $table_config_name, array( 'value' => $widgetBadgeBackgroundColor_), array( 'element' => 'conf_widgetBadgeBackgroundColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $widgetInfoBackgroundColor_), array( 'element' => 'conf_widgetInfoBackgroundColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $widgetMargin_), array( 'element' => 'conf_widgetMargin' ));
                        //update statements for modal css
                        $wpdb->update( $table_config_name, array( 'value' => $modalBackgroundColor_), array( 'element' => 'conf_modalBackgroundColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $modalFontColor_), array( 'element' => 'conf_modalFontColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $modalPriceColor_), array( 'element' => 'conf_modalPriceColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $modalBadgeBackgroundColor_), array( 'element' => 'conf_modalBadgeBackgroundColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $modalBadgeBorderRadius_), array( 'element' => 'conf_modalBadgeBorderRadius' ));
                        $wpdb->update( $table_config_name, array( 'value' => $modalBadgeFontColor_), array( 'element' => 'conf_modalBadgeFontColor' ));
                        if(count($conf_result) <= 19) {
                            $wpdb->insert($table_config_name, array('element' => 'conf_modalBadgeLogoStyle', 'value' => $modalBadgeLogoStyleValue_));
                        }
                        else {
                            $wpdb->update( $table_config_name, array( 'value' => $modalBadgeLogoStyleValue_), array( 'element' => 'conf_modalBadgeLogoStyle' ));
                        }

                        $wpdb->update( $table_config_name, array( 'value' => $modalCardColor_), array( 'element' => 'conf_modalCardColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $modalButtonBorderColor_), array( 'element' => 'conf_modalButtonBorderColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $modalButtonBorderRadius_), array( 'element' => 'conf_modalButtonBorderRadius' ));
                        $wpdb->update( $table_config_name, array( 'value' => $modalButtonBackgroundColor_), array( 'element' => 'conf_modalButtonBackgroundColor' ));
                        $wpdb->update( $table_config_name, array( 'value' => $modalButtonFontColor_), array( 'element' => 'conf_modalButtonFontColor' ));
                    }
                    else {
                        //insert statements for widget css
                        $wpdb->insert($table_config_name, array('element' => 'conf_widgetBorderColor', 'value' => $widgetBorderColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_widgetBorderRadius', 'value' => $widgetBorderRadius_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_widgetFontColor', 'value' => $widgetFontColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_widgetFontFamily', 'value' => $widgetFontFamily_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_widgetFontSize', 'value' => $widgetFontSize_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_widgetBadgeBackgroundColor', 'value' => $widgetBadgeBackgroundColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_widgetInfoBackgroundColor', 'value' => $widgetInfoBackgroundColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_widgetMargin', 'value' => $widgetMargin_));
                        //insert statements for modal css
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalBackgroundColor', 'value' => $modalBackgroundColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalFontColor', 'value' => $modalFontColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalPriceColor', 'value' => $modalPriceColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalBadgeBackgroundColor', 'value' => $modalBadgeBackgroundColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalBadgeBorderRadius', 'value' => $modalBadgeBorderRadius_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalBadgeFontColor', 'value' => $modalBadgeFontColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalBadgeLogoStyle', 'value' => $modalBadgeLogoStyle_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalCardColor', 'value' => $modalCardColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalButtonBorderColor', 'value' => $modalButtonBorderColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalButtonBorderRadius', 'value' => $modalButtonBorderRadius_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalButtonBackgroundColor', 'value' => $modalButtonBackgroundColor_));
                        $wpdb->insert($table_config_name, array('element' => 'conf_modalButtonFontColor', 'value' => $modalButtonFontColor_));
                    }

                    /** WIDGET HOME  **/

                    $resultH = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","widget_home"));

                    if(isset($resultH) && count($resultH) > 0) {
                        $wpdb->update( $table_config_name, array( 'value' => $newWidgetHomeEnabled . '|' . $newFieldWidgetType . '|' . $newElementReference . '|' . $newSlug), array( 'element' => 'widget_home' ));
                    }
                    else {
                        $wpdb->insert($table_config_name, array('element' => 'widget_home', 'value' => $newWidgetHomeEnabled . '|' . $newFieldWidgetType . '|' . $newElementReference . '|' . $newSlug));
                    }

                    /** WIDGET HOME  **/

                    /** CUSTOM ORDER STATUS  **/

                    $resultCOS = $wpdb->get_results($wpdb->prepare("select * from {$table_config_name} where element = %s","custom_order_status"));

                    if(isset($resultCOS) && count($resultCOS) > 0) {
                        $wpdb->update( $table_config_name, array( 'value' => $customOrderStatus), array( 'element' => 'custom_order_status' ));
                    }
                    else {
                        $wpdb->insert($table_config_name, array('element' => 'custom_order_status', 'value' => $customOrderStatus));
                    }

                    /** CUSTOM ORDER STATUS **/

                }

            }, 10, 3);

            /* Register action hook. */
            add_action('init', array( $this, 'pacca_start_session' ), 1);

			/*
            * Callback response managament
            * In this place of the code it is verifying the order id and status from callback response,
            * so then, an action is taken ( display notice  or redirect to order received page).
            */

            // LOAD THE WC LOGGER
            $logger = wc_get_logger();

            // verify if this request is coming from admin site or frontend site
            if ( (! is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )) ) {

                $order_id;
                $order_status;
                $woocommerce_order_id_query_param;
                $table_name = $wpdb->prefix . "wc_addi_gateway";

                $querys = $_SERVER['QUERY_STRING'];

                if(strpos($querys, 'wc-order-id' ) !== false) {
                    $woocommerce_order_id_query_param = $_GET["wc-order-id"];
					$_SESSION["order_id_query_param"] = $woocommerce_order_id_query_param;
                }

                try {

                    if(isset($woocommerce_order_id_query_param)) {

                            // This id is registered on database, so it's needed to see its status.
                            $result = $wpdb->get_results($wpdb->prepare("select * from {$table_name} where order_id = %d", $woocommerce_order_id_query_param));

                            // verifying the integrity of the resulset, otherwise could throw an error.
                            if(isset($result) && count($result) > 0) {
                                try{

                                    foreach ($result as $item) {
                                        $order_id = $item->order_id;
                                        $order_status = $item->order_status;
                                        $wpdb->delete( $table_name, array( 'order_id' => $item->order_id ) );
                                    }
                                }
                                catch(Exception $e) {
                                    if ($this->logs == 'yes') {
                                      $logger->info( 'Error getting data from database: ' . $e . ' ', array( 'source' => 'addi-error-handler-log' ) );
                                    }
                                }

                            }

                            // verifying assignment variables was ok , otherwise it will show a notification
                            if(isset($order_id) && isset($order_status)) {
                                if($order_status !== 'APPROVED') {

									add_filter( 'woocommerce_checkout_fields' , function ( $fields ) {
										global $woocommerce;
                                        global $wp;

										$order_id = $_SESSION["order_id_query_param"];

										$order = wc_get_order( $order_id );

                                        // Get the Order meta data in an unprotected array
                                        $order_data  = $order->get_data(); // The Order data

                                        if($this->custom_order_status == 'yes') {
                                          $order->update_status( 'addi-declined', '', true );
                                        }

										//loop in array to verify if billing fields are populated or not
                                        foreach($fields['billing'] as $key1 => $billing) {

                                            if(!isset($fields['billing'][$key]['default']) ) {
                                                switch ($key1) {
                                                    case "billing_first_name":
                                                        if(isset($order_data['billing']['first_name'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_first_name();
                                                        }
                                                      break;
                                                    case "billing_last_name":
                                                        if(isset($order_data['billing']['last_name'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_last_name();
                                                        }
                                                      break;
                                                    case "billing_company":
                                                        if(isset($order_data['billing']['company'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_company();
                                                        }
                                                      break;
                                                    case "billing_address_1":
                                                        if(isset($order_data['billing']['address_1'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_address_1();
                                                        }
                                                      break;
                                                    case "billing_address_2":
                                                        if(isset($order_data['billing']['address_2'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_address_2();
                                                        }
                                                      break;
                                                    case "billing_city":
                                                        if(isset($order_data['billing']['city'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_city();
                                                        }
                                                      break;
                                                    case "billing_state":
                                                        if(isset($order_data['billing']['state'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_state();
                                                        }
                                                      break;
                                                    case "billing_postcode":
                                                        if(isset($order_data['billing']['postcode'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_postcode();
                                                        }
                                                      break;
                                                    case "billing_country":
                                                        if(isset($order_data['billing']['country'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_country();
                                                        }
                                                      break;
                                                    case "billing_email":
                                                        if(isset($order_data['billing']['email'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_email();
                                                        }
                                                      break;
                                                    case "billing_phone":
                                                        if(isset($order_data['billing']['phone'])) {
                                                          $fields['billing'][$key1]['default'] = $order->get_billing_phone();
                                                        }
                                                      break;
                                                }
                                            }
                                        }

                                        //loop in array to verify if shipping fields are populated or not
                                        foreach($fields['shipping'] as $key1 => $billing) {

                                            if(!isset($fields['shipping'][$key]['default']) ) {
                                                switch ($key1) {
                                                    case "shipping_first_name":
                                                        if(isset($order_data['shipping']['first_name'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_first_name();
                                                        }
                                                      break;
                                                    case "shipping_last_name":
                                                        if(isset($order_data['shipping']['last_name'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_last_name();
                                                        }
                                                      break;
                                                    case "shipping_company":
                                                        if(isset($order_data['shipping']['company'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_company();
                                                        }
                                                      break;
                                                    case "shipping_address_1":
                                                        if(isset($order_data['shipping']['addres_1'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_address_1();
                                                        }
                                                      break;
                                                    case "shipping_address_2":
                                                        if(isset($order_data['shipping']['addres_2'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_address_2();
                                                        }
                                                      break;
                                                    case "shipping_city":
                                                        if(isset($order_data['shipping']['city'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_city();
                                                        }
                                                      break;
                                                    case "shipping_state":
                                                        if(isset($order_data['shipping']['state'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_state();
                                                        }
                                                      break;
                                                    case "shipping_postcode":
                                                        if(isset($order_data['shipping']['postcode'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_postcode();
                                                        }
                                                      break;
                                                    case "shipping_country":
                                                        if(isset($order_data['shipping']['country'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_country();
                                                        }
                                                      break;
                                                    case "shipping_email":
                                                        if(isset($order_data['shipping']['email'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_email();
                                                        }
                                                      break;
                                                    case "shipping_phone":
                                                        if(isset($order_data['shipping']['phone'])) {
                                                          $fields['shipping'][$key1]['default'] = $order->get_shipping_phone();
                                                        }
                                                      break;
                                                }
                                            }
                                        }

										$_SESSION["order_id_query_param"]  = null;
										return $fields;
									} );
                                    // display notification
                                    wc_add_notice( __('Tu pago no fue aprobado. Por favor, inténtalo de nuevo.', 'buy-now-pay-later-addi'), 'error' );
                                }
                            }
                    }
                }
                catch(Exception $e) {
                    if ($this->logs == 'yes') {
                      $logger->info( ' Fatal Error :   ' . $e . ' ', array( 'source' => 'addi-gateway-log' ) );
                    }
                }
            }
            /*
            * Callback response management
            * In this place of the code it is verifying the order id and status from callback response,
            * so then, an action is taken ( display notice  or redirect to order received page).
            * --- END OF CODE ----
            */

 		}

		/**
 		 * Plugin options
 		 */
 		public function init_form_fields(){

            global $woocommerce;

            $this->form_fields = array(
                'enabled' => array(
                    'title'       => __( 'Habilitar/Deshabilitar', 'buy-now-pay-later-addi' ),
                    'label'       => __('Habilitar Addi', 'buy-now-pay-later-addi' ),
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'widget_slug' => array(
                    'title'       => __('Ally slug en ADDI','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                ),
                'hr3' => array(
                    'type'  => 'hr',
                    'class' => 'hr-default',
                ),
                'addi_section_checkout_page' => array(
					'title'    => __('Checkout', 'buy-now-pay-later-addi' ),
                    'type'     => 'text',
                    'class'    => 'widget-section-header',
                ),
                'description'  => array(
                    'title'       => __( 'Descripción', 'buy-now-pay-later-addi' ),
                    'type'        => 'textarea',
                    'description' => __( 'Esta descricpión es visible en el checkout.', 'buy-now-pay-later-addi' ),
                    'default'     => __( '<b>Finaliza tu compra con ADDI</b></br><b>Es simple, rápido y seguro</b></br><b>1.</b> Sin tarjeta de crédito y en minutos.</br><b>2.</b> Proceso 100% online y sin papeleo.</br><b>3.</b> Solo necesitas tu cédula y WhatsApp para aplicar.', 'buy-now-pay-later-addi' ),
                    'desc_tip'    => true,
                ),
                'addi_sub_section_checkout_page' => array(
					'title'    => __('Información del checkout', 'buy-now-pay-later-addi' ),
                    'type'     => 'text',
                    'class'    => 'widget-section-header',
                ),
                'addi_sub_section_checkout_page' => array(
					'title'    => __('Información del checkout', 'buy-now-pay-later-addi' ),
                    'type'     => 'text',
                    'class'    => 'widget-section-header',
                ),
                'addi_description_checkout_page' => array(
					'title'    => __('Indícanos aquí el nombre con el que identificas cada uno de estos datos en tu checkout. Si no has configurado nada especial, déjalo en blanco.', 'buy-now-pay-later-addi' ),
                    'type'     => 'text',
                    'class'    => 'widget-description-header',
                ),
                'field_billing_first_name' => array(
                    'title'       => __('Campo Nombres','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'default'     => '',
                    'desc_tip'    => false,
                ),
                'field_billing_last_name' => array(
                    'title'       => __('Campo Apellidos','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'default'     => '',
                    'desc_tip'    => false,
                ),
                'field_id' => array(
                    'title'       => __('Campo Documento','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'default'     => '',
                    'desc_tip'    => false,
                ),
                'field_billing_address_1' => array(
                    'title'       => __('Campo Dirección','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'default'     => '',
                    'desc_tip'    => false,
                ),
                'field_billing_city' => array(
                    'title'       => __('Campo Ciudad','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'default'     => '',
                    'desc_tip'    => false,
                ),
                'field_billing_email' => array(
                    'title'       => __('Campo Email','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'default'     => '',
                    'desc_tip'    => false,
                ),
                'field_billing_phone' => array(
                    'title'       => __('Campo Teléfono','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'default'     => '',
                    'desc_tip'    => false,
                ),
                'prod_client_id' => array(
                    'title'       => 'Client ID',
                    'type'        => 'password',
                ),
                'prod_client_secret' => array(
                    'title'       => 'Client Secret',
                    'type'        => 'password'
                ),
                'testmode' => array(
                    'title'       => __('Ambiente pruebas', 'buy-now-pay-later-addi' ),
                    'label'       => __('Habilitar ambiente de pruebas', 'buy-now-pay-later-addi' ),
                    'type'        => 'checkbox',
                    'description' => __('Colocar este método de pago en ambiente de pruebas.', 'buy-now-pay-later-addi' ),
                    'default'     => 'no',
                    'desc_tip'    => true,
                ),
                'custom_order_status' => array(
                    'title'       => __( 'Estados personalizados de Addi', 'buy-now-pay-later-addi' ),
                    'label'       => __('Habilitar los estados personalizados de Addi en los pedidos', 'buy-now-pay-later-addi' ),
                    'type'        => 'checkbox',
                    'description' => __('Esta opción cambiará los estados de los pedidos de compra cuando sean con Addi a transacciones aprobadas o no aprobadas.', 'buy-now-pay-later-addi' ),
                    'default'     => 'no',
                    'desc_tip'    => true,
                ),
                'logs' => array(
                    'title'       => __('Logs', 'buy-now-pay-later-addi' ),
                    'label'       => __('Habilitar Logs', 'buy-now-pay-later-addi' ),
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no',
                ),
				'hr1' => array(
                    'type'  => 'hr',
                    'class' => 'hr-default',
                ),
                'addi_section_product_page' => array(
					'title'    => __('Página de producto', 'buy-now-pay-later-addi' ),
                    'type'     => 'text',
                    'class'    => 'widget-section-header',
                ),
                'widget_enabled' => array(
                    'title'       => __('Widget', 'buy-now-pay-later-addi' ),
                    'label'       => __('Habilitar widget', 'buy-now-pay-later-addi' ),
                    'type'        => 'checkbox',
                    'description' => __('Habilitar widget ADDI en la página de producto.','buy-now-pay-later-addi' ),
                    'default'     => 'no',
                ),
                'conf_widget_position' => array(
                    'title'       => __('Posición del widget','buy-now-pay-later-addi' ),
                    'type'        => 'select',
                    'default'     => 'woocommerce_before_add_to_cart_form',
                    'desc_tip'    => false,
                    'options' => array(
                        'woocommerce_before_single_product_summary' => __('Encima de título de producto','buy-now-pay-later-addi' ),
                        'woocommerce_before_add_to_cart_form' => __('Default','buy-now-pay-later-addi' ),
                        'woocommerce_before_variations_form' => __('Encima de formulario de variaciones de precio','buy-now-pay-later-addi' ),
                        'woocommerce_before_single_variation' => __('Encima de precio variación','buy-now-pay-later-addi' ),
                        'woocommerce_after_add_to_cart_button' => __('Debajo de botón añadir al carrito','buy-now-pay-later-addi' ),
                        'woocommerce_after_variations_form' => __('Debajo de formulario de variaciones de precio','buy-now-pay-later-addi' ),
                        'woocommerce_after_add_to_cart_form' => __('Debajo de formulario de agregar producto','buy-now-pay-later-addi' ),
                        'woocommerce_product_meta_start' => __('Encima de información extra','buy-now-pay-later-addi' ),
                        'woocommerce_product_meta_end' => __('Debajo de información extra','buy-now-pay-later-addi' ),
                        'woocommerce_share' => __('Encima de redes sociales','buy-now-pay-later-addi' ),
                   )
                ),
                'widget_section_widget_header' => array(
					'title'    => __('Configuración Estilos Widget', 'buy-now-pay-later-addi' ),
                    'type'     => 'text',
                    'class'    => 'widget-section-header',
                ),
				'widget_section_widget_header' => array(
					'title'    => __('Configuración Estilos Widget', 'buy-now-pay-later-addi' ),
                    'type'     => 'text',
                    'class'    => 'widget-section-header',
                ),
				'widgetBorderColor' => array(
					'title'       => __('Color del borde','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color (palabra o código HEX) para el borde del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                    'default'     => 'black',
                    'desc_tip'    => true,
                ),
				'widgetBorderRadius' => array(
					'title'       => __('Curvatura del borde','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el tamaño de la curvatura para el borde del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                    'default'     => '5px',
                    'desc_tip'    => true,
                ),
				'widgetFontColor' => array(
					'title'       => __('Color de fuente','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color (palabra o código HEX) para la fuente del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                    'default'     => 'black',
                    'desc_tip'    => true,
                ),
				'widgetFontFamily' => array(
					'title'       => __('Tipo de fuente','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el tipo de fuente que quieres usar para el widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                    'default'     => 'system-ui',
                    'desc_tip'    => true,
                ),
				'widgetFontSize' => array(
					'title'       => __('Tamaño de fuente','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el tamaño de fuente del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                    'default'     => '14px',
                    'desc_tip'    => true,
                ),
				'widgetBadgeBackgroundColor' => array(
					'title'       => __('Color de fondo ícono ADDI','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color del fondo del cuadro con el logo de ADDI para el widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                    'default'     => '#fff',
                    'desc_tip'    => true,
                ),
				'widgetInfoBackgroundColor' => array(
					'title'       => __('Color de fondo widget','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __(' Indica el color del fondo del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                    'default'     => 'transparent',
                    'desc_tip'    => true,
                ),
				'widgetMargin' => array(
					'title'       => __('Margen','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el tamaño de margen para el widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                    'default'     => '0',
                    'desc_tip'    => true,
                ),
                'modalBadgeLogoStyle' => array(
					'title'       => __('Logo ADDI en blanco','buy-now-pay-later-addi' ),
                    'label'       => __(' ','buy-now-pay-later-addi' ),
                    'type'        => 'checkbox',
                    'default'     => 'no',
                    'desc_tip'    => false,
                ),
				'widget_section_modal_header' => array(
					'title'    => __('Configuración Estilos Modal', 'buy-now-pay-later-addi' ),
                    'type'     => 'text',
                    'class'    => 'widget-section-header',
                ),
				'modalBackgroundColor' => array(
					'title'       => __('Color de fondo','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color (palabra o código HEX) del fondo para el modal con la información de ADDI.', 'buy-now-pay-later-addi' ),
                    'default'     => '#eee',
                    'desc_tip'    => true,
                ),
				'modalFontColor' => array(
					'title'       => __('Color de fuente','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color (palabra o código HEX) de la fuente para el modal con la información de ADDI.', 'buy-now-pay-later-addi' ),
                    'default'     => 'black',
                    'desc_tip'    => true,
                ),
				'modalPriceColor' => array(
					'title'       => __('Color precio','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color (palabra o código HEX) para el precio en el modal.', 'buy-now-pay-later-addi' ),
                    'default'     => '#3c65ec',
                    'desc_tip'    => true,
                ),
				'modalBadgeBackgroundColor' => array(
					'title'       => __('Color fondo banner','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color (palabra o código HEX) para el fondo del banner de tasa de interés.', 'buy-now-pay-later-addi' ),
                    'default'     => '#4cbd99',
                    'desc_tip'    => true,
                ),
				'modalBadgeBorderRadius' => array(
					'title'       => __('Curvatura del borde banner','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el tamaño de la curvatura para el borde del banner de tasa de interés.', 'buy-now-pay-later-addi' ),
                    'default'     => '5px',
                    'desc_tip'    => true,
                ),
				'modalBadgeFontColor' => array(
					'title'       => __('Color fuente banner','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color (palabra o código HEX) para la fuente del banner de tasa de interés.', 'buy-now-pay-later-addi' ),
                    'default'     => 'white',
                    'desc_tip'    => true,
                ),
				'modalCardColor' => array(
					'title'       => __('Color fondo modal','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color (palabra o código HEX) para el fondo del modal. ', 'buy-now-pay-later-addi' ),
                    'default'     => 'white',
                    'desc_tip'    => true,
                ),
				'modalButtonBorderColor' => array(
					'title'       => __('Color borde botón','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __(' Indica el color (palabra o código HEX) para el borde del botón del modal.', 'buy-now-pay-later-addi' ),
                    'default'     => '#4cbd99',
                    'desc_tip'    => true,
                ),
				'modalButtonBorderRadius' => array(
					'title'       => __('Curvatura del borde del botón','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el tamaño de la curvatura para el borde del widget de ADDI que aparece en la página de producto.', 'buy-now-pay-later-addi' ),
                    'default'     => '5px',
                    'desc_tip'    => true,
                ),
				'modalButtonBackgroundColor' => array(
					'title'       => __('Color de fondo botón','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color (palabra o código HEX) para el fondo del botón.', 'buy-now-pay-later-addi' ),
                    'default'     => 'transparent',
                    'desc_tip'    => true,
                ),
				'modalButtonFontColor' => array(
					'title'       => __('Color de fondo fuente botón','buy-now-pay-later-addi' ),
                    'type'        => 'text',
                    'description' => __('Indica el color (palabra o código HEX) para el fondo de la fuente del botón.', 'buy-now-pay-later-addi' ),
                    'default'     => '#4cbd99',
                    'desc_tip'    => true,
                ),
                'widget_section_widget_home' => array(
					'title'    => __('Home', 'buy-now-pay-later-addi' ),
                    'type'     => 'text',
                    'class'    => 'widget-section-header',
                ),
                'field_widget_position' => array(
                    'title'       => __('Posición del widget','buy-now-pay-later-addi' ),
                    'type'        => 'select',
                    'default'     => 'on_header',
                    'desc_tip'    => false,
                    'options' => array(
                        'on_header' => 'Debajo de header',
                        'on_footer' => 'Encima de footer',
                        'custom' => 'Personalizado',
                   ) // array of options for select/multiselects only
                ),
                'element_reference' => array(
                    'type'        => 'text',
                    'default'     => '',
                ),
                'field_widget_type' => array(
                    'title'       => __('Tipo de Widget','buy-now-pay-later-addi' ),
                    'type'        => 'select',
                    'default'     => 'default',
                    'desc_tip'    => false,
                    'options' => array(
                        'default' => 'default',
                        'banner_01' => 'banner_01',
                        'banner_02' => 'banner_02',
                        'banner_03' => 'banner_03',
                   ) // array of options for select/multiselects only
                ),
                'widget_home_enabled' => array(
                    'title'       => __('Widget en el Home', 'buy-now-pay-later-addi' ),
                    'label'       => __('Habilitar widget en el home', 'buy-now-pay-later-addi' ),
                    'type'        => 'checkbox',
                    'default'     => 'no',
                    'desc_tip'    => true,
                ),
            );

            $brazilCheckoutFieldspluginPath = 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php';
            $checkoutFieldEditorAndManagerForWoocommercePath = 'checkout-field-editor-and-manager-for-woocommerce/start.php';
            $fieldEditorForWoocommercePluginPath = 'woo-checkout-field-editor-pro/checkout-form-designer.php';
            $yithWoocommerceCheckoutManagerPath = 'yith-woocommerce-checkout-manager/init.php';

            if(is_plugin_active( $fieldEditorForWoocommercePluginPath ) ||
               is_plugin_active( $checkoutFieldEditorAndManagerForWoocommercePath ) ||
               is_plugin_active( $brazilCheckoutFieldspluginPath ) ||
               is_plugin_active( $yithWoocommerceCheckoutManagerPath )) {
                set_transient( "buy-now-pay-later-addi", "alive", 3 );
            }
            else {

                $wc_array;

                if(isset(WC()->checkout)) {
                    $wc_array = WC()->checkout->get_checkout_fields();
                }

                if ( isset( $wc_array ) ) {

                    foreach($wc_array['billing'] as $key1 => $billing) {

                        switch ($key1) {
                            case "billing_cedula":
                                setcookie("billingField", "billing_cedula", time()+120);
                                break;
                            case "billing_cpf":
                                setcookie("billingField", "billing_cpf", time()+120);
                                break;
                            case "billing_id":
                                setcookie("billingField", "billing_id", time()+120);
                        }

                        }

                }
            }

            if (!function_exists('fx_addi_brazilcheckouteditor_notice') && is_plugin_active( $brazilCheckoutFieldspluginPath )) {
                /* Add admin notice */
                add_action( 'admin_notices',function (){
                    if ( "alive" == get_transient( "buy-now-pay-later-addi" ) || !isset($this->field_id)) {
                        ?>
                        <div class="notice-warning notice is-dismissible">
                            <p>O plug-in <strong>Brazilian market for WooCommerce</strong> está instalado e modifica os campos de checkout. Certifique-se de indicar o identificador do campo do documento para garantir o funcionamento correto do <strong>ADDI</strong> plug-in.</p>
                        </div>
                        <?php
                        /* Delete transient, only display this notice once. */
                        delete_transient("buy-now-pay-later-addi");
                    }
                    else {
                        return;
                    }
                });
            }

            if (!function_exists('fx_addi_checkouteditor_notice') && is_plugin_active( $checkoutFieldEditorAndManagerForWoocommercePath )) {
                /* Add admin notice */
                add_action( 'admin_notices', function (){
                    if ( "alive" == get_transient( "buy-now-pay-later-addi" ) || !isset($this->field_id)) {
                        ?>
                        <div class="notice-warning notice is-dismissible">
                            <p>El plug-in <strong>Checkout Field Editor and Manager for Woocommerce</strong> está instalado y este modifica los campos del checkout. Por favor, asegúrate de configurar el nombre para el campo de documento y así asegurar el correcto funcionamiento de <strong>ADDI</strong>.</p>
                        </div>
                        <?php

                    }
                    else {
                        return;
                    }
                    /* Delete transient, only display this notice once. */
                    delete_transient("buy-now-pay-later-addi");
                });
            }

            if (!function_exists('fx_addi_checkouteditor_notice') && is_plugin_active( $fieldEditorForWoocommercePluginPath )) {
                /* Add admin notice */
                add_action( 'admin_notices', function (){
                    if ( "alive" == get_transient( "buy-now-pay-later-addi" ) || !isset($this->field_id)) {
                        ?>
                        <div class="notice-warning notice is-dismissible">
                            <p>El plug-in <strong>Checkout Field Editor for Woocommerce</strong> está instalado y este modifica los campos del checkout. Por favor, asegúrate de configurar el nombre para el campo de documento y así asegurar el correcto funcionamiento de <strong>ADDI</strong>.</p>
                        </div>
                        <?php

                    }
                    else {
                        return;
                    }
                    /* Delete transient, only display this notice once. */
                    delete_transient("buy-now-pay-later-addi");
                });
            }

            if (!function_exists('fx_addi_checkouteditor_notice') && is_plugin_active( $yithWoocommerceCheckoutManagerPath )) {
                /* Add admin notice */
                add_action( 'admin_notices', function (){
                    if ( "alive" == get_transient( "buy-now-pay-later-addi" ) || !isset($this->field_id)) {
                        ?>
                        <div class="notice-warning notice is-dismissible">
                            <p>El plug-in <strong>Yith Woocommerce Checkout Manager</strong> está instalado y este modifica los campos del checkout. Por favor, asegúrate de configurar el nombre para el campo de documento y así asegurar el correcto funcionamiento de <strong>ADDI</strong>.</p>
                        </div>
                        <?php

                    }
                    else {
                        return;
                    }
                    /* Delete transient, only display this notice once. */
                    delete_transient("buy-now-pay-later-addi");
                });
            }
        }

        /**
		 * You will need it if you want your custom credit card form, Step 4 is about it
		 */
		public function payment_fields() {
            global $woocommerce;
            global $wp;

			$totals = $woocommerce->cart->total;

			$options_api = [
                'headers'     => [
                    'Content-Type' => 'application/json',
                    'accept' => 'application/json',
					'WWW-Authenticate' => "Basic realm='" . gethostname() ."'",
                ],
                'timeout'     => 60,
                'data_format' => 'body',
            ];

            if((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
                $api_domain = $this->testmode ? 'https://channels-public-api.addi-staging.com.br/allies/' : 'https://channels-public-api.addi.com.br/allies/';
                $api_app_url = $api_domain . $this->widget_slug .'/config?requestedAmount=' . $totals;
            }
            else {
                $api_domain = $this->testmode ? 'https://channels-public-api.addi-staging.com/allies/' : 'https://channels-public-api.addi.com/allies/';
                $api_app_url = $api_domain . $this->widget_slug .'/config?requestedAmount=' . $totals;
            }

            // request
            $api_response = wp_remote_get($api_app_url, $options_api );

			if( !is_wp_error( $api_response ) ) {
			    // getting decoded body
                $body_api_response = json_decode( $api_response['body'], true );
                $country = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'br' : 'co';
                $installments  = number_format($totals/4, 2);
                // Including the required template
                $plugin_path = WP_PLUGIN_DIR.'/'.basename(__DIR__);

                // Getting the template version
				$widgetversion = isset($body_api_response['widgetConfig']['widgetVersion']) ? $body_api_response['widgetConfig']['widgetVersion'] : null;
                $template_version = 'bnpl';
                $discount = (isset($body_api_response['policy']['discount']) && $body_api_response['policy']['discount'] > 0) ? $body_api_response['policy']['discount'] : false;

                $min_amount = false;
                $max_amount = false;
                if(isset($body_api_response['minAmount']) && $body_api_response['minAmount'] !== ""
                    || $body_api_response['code'] == '007-015') {
                   $min_amount = number_format(intval($body_api_response['minAmount']),0,',','.');
                }

                if(isset($body_api_response['maxAmount']) && $body_api_response['maxAmount'] !== "" ) {
                    $max_amount = number_format(intval($body_api_response['maxAmount']),0,',','.');
                }

                if (isset($widgetversion) && ($widgetversion === '1.0.2' || $widgetversion === 'ADDI_TEMPLATE_02')) {
                    $template_version = 'bnpl_bnpn';
                }

                if($country == 'br') {
                    $template = $plugin_path . '/templates/' . $country . '/' . $template_version . '.php';
                    $min_amount_int = $body_api_response['minAmount'];
                    $max_amount_int = $body_api_response['maxAmount'];
                    echo  $this->render_template($template, ['installments' => $installments, 'total' => $totals,
                        'discount' => $discount, 'min_amount' => $min_amount, 'max_amount' => $max_amount, 'min_amount_int' => $min_amount_int, 'max_amount_int' => $max_amount_int]);
                    return;
                } else {
                    $description = "<div class='addi_description_container'>";

		            $description = $description . "<div style='height: 50px;border-bottom: 1px solid #C9CDD1;background: #091A42; border-radius: 8px 8px 0 0;color: white;font-size: 14px;line-height: 19.74px;padding: 7px 30px 0 18px;'><span>Después de hacer clic en &#8220;Paga con Addi&#8221;, será redirigido a Addi.</span></div><div class='container' style='padding: 20px;'><div><img style='margin-bottom: 11px;' src='" . plugins_url( '/assets/title.png' , __FILE__ ) ."'></div><div style='margin-bottom:10px;'><b style='color: #4D525C;font-size: 18px;'>Es simple, rápido y seguro</b></div><div class='mini-frame' style='border-radius: 8px;border: 1px solid #EBECED;padding: 8px;box-sizing: border-box;background-color: white;display: inline-flex;'><img style=' height: 28px;width: 28px;' src='" . plugins_url( '/assets/icons03.png' , __FILE__ ) ."'><span style='font-weight: 500;font-size: 14px;padding-left: 15px;'>Sin tarjeta de crédito y en minutos.</span></div><div class='mini-frame' style='margin-top: 10px;border-radius: 8px;border: 1px solid #EBECED;padding: 8px;box-sizing: border-box;background-color: white;display: inline-flex;'><img style=' height: 28px;width: 28px;' src='" . plugins_url( '/assets/icons02.png' , __FILE__ ) ."'><span style='font-weight: 500;font-size: 14px;padding-left: 15px;'>Solo necesitas tu cédula y WhatsApp para aplicar.</span></div><div class='mini-frame' style='margin-top: 10px;border-radius: 8px;border: 1px solid #EBECED;padding: 8px;box-sizing: border-box;background-color: white;display: inline-flex;'><img style=' height: 28px;width: 28px;' src='" . plugins_url( '/assets/icons01.png' , __FILE__ ) ."'><span style='font-weight: 500;font-size: 14px;padding-left: 15px;'>Proceso 100% online y sin papeleo.</span></div></div>";

                    if ($discount || $widgetversion == 'ADDI_TEMPLATE_NC_DISC') {
                        $discount_msg = "% de descuento";
                        // Check for template name here
                        if ($widgetversion == 'ADDI_TEMPLATE_NC_DISC') {
                            $discount_msg = "% descuento extra para nuevos usuarios de Addi";
                            $discount = 0.05;
                        }
                        $description = $description . "<div class='discount-container'><p class='discount-label'>" .
                            ($discount * 100) . $discount_msg . "</p></div>
                            <div class='discount-sub-container'><span class='discount-sub-label'>*Verás aplicado el descuento luego de hacer click en &#8220;Paga con Addi&#8221;</span></div>";
                    }

                    if ($min_amount && $totals < intval($body_api_response['minAmount'])) {
                        $description = $description . "<div class='constraint-container'>Solo disponible para compras mayores a $" .
                          $min_amount . "</div>";
                    }

                    if ($max_amount && $totals > intval($body_api_response['maxAmount'])) {
                        $description = $description . "<div class='constraint-container'>Solo disponible para compras menores a $".
                          $max_amount . "</div>";
                    }

                    $description = $description . "</div>";
                    echo wpautop( wptexturize( $description ) ); // @codingStandardsIgnoreLine.
                }
			}
        }

        private function render_template(/*$template, $variables*/) {
            ob_start();
            foreach ( func_get_args()[1] as $key => $value) {
                ${$key} = $value;
            }
            include func_get_args()[0];
            return ob_get_clean();
        }

        /*
		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		 */
	 	public function payment_scripts() {


        }

        /*
		 * We're processing the payments here
		 */
        public function process_payment( $order_id ) {

            global $woocommerce;
            global $wp;

            // LOAD THE WC LOGGER
            $logger = wc_get_logger();

            // we need it to get any order details
            $order = wc_get_order( $order_id );
            $_SESSION["order_id_process_payment"] = $order_id;

            try{
                if( !is_admin() ){
                    WC()->session->set( 'order_id_payment_session' , $order_id );
                }
            }
            catch(Exception $e) {
                if($this->logs == 'yes') {
                  $logger->info( '  ERROR saving variable session in Woocommerce :   ' . $e . ' ', array( 'source' => 'addi-error-handler-log' ) );
                }
            }

            //taking corresponding api and credentials
            $api_selected = $this->testmode ? ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://api.addi-staging-br.com' : 'https://api.staging.addi.com') : ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://api.addi.com.br' : 'https://api.addi.com');
            $client_id_selected = $this->prod_client_id;
            $client_secret_selected = $this->prod_client_secret;

            /*
              * Array with parameters for API interaction
             */

            $body_auth = [
                'audience'  => $api_selected,
                'grant_type' => 'client_credentials',
                'client_id' => $client_id_selected,
                'client_secret' => $client_secret_selected,
            ];

            $body_auth = wp_json_encode( $body_auth );

            $options_auth = [
                'body'        => $body_auth,
                'headers'     => [
                    'Content-Type' => 'application/json',
                    'accept' => 'application/json',
                ],
                'timeout'     => 60,
                'data_format' => 'body',
            ];

            // getting api url based on test mode checkbox
            $auth_app_url = $this->testmode ? ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://auth.addi-staging-br.com/oauth/token' : 'https://auth.addi-staging.com/oauth/token') : ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://auth.addi.com.br/oauth/token' : 'https://auth.addi.com/oauth/token');

            // request
            $auth_response = wp_remote_post($auth_app_url, $options_auth );

            // verify if body response is an error or contains data
            $body_auth_response = json_decode( $auth_response['body'], true );

            if(!is_array($body_auth_response)) {
                $body_auth_response = array();
            }

			$denied = in_array("access_denied", $body_auth_response) || in_array("Unauthorized", $body_auth_response);

            if( !is_wp_error( $auth_response ) && !$denied) {
                // getting decoded body
                $items = [];
                $client = new stdClass();
                $client_address = new stdClass();
                $allyUrlRedirection = new stdClass();

                // Get and Loop Over Order Items
                foreach ( $order->get_items() as $item_id => $item ) {

                    $object = new stdClass();
                    //Get the WC_Product object
                    $product = $item->get_product();
                    $object->sku = $product->get_sku();
                    $object->name = $item->get_name();
                    $object->quantity = $item->get_quantity();
                    $object->unitPrice = $product->get_regular_price();
                    $object->tax = $item->get_subtotal_tax();
                    $object->pictureUrl = wp_get_attachment_url( $product->get_image_id() );
                    $object->category = $product->get_type();
                    array_push($items, $object);
                }

                // //Get Address Client
                $client_address->lineOne = isset($this->field_billing_address_1) && ($this->field_billing_address_1 !== '') ?
                                           WC()->checkout->get_value('' . $this->field_billing_address_1 . '') :
                                           (($order->get_shipping_address_1() !== "" && $order->get_shipping_address_1() !== " ") ?
                                           $order->get_shipping_address_1() : $order->get_billing_address_1());

                $client_address->city =    isset($this->field_billing_city) && ($this->field_billing_city !== '') ?
                                           WC()->checkout->get_value('' . $this->field_billing_city . '') :
                                           (($order->get_shipping_city() !== "" && $order->get_shipping_city() !== " ") ?
                                           $order->get_shipping_city() : $order->get_billing_city());

                $client_address->country = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'BR' : 'CO';


                // // Get Order Client
                $id = '';
                if(isset($this->field_id) && ($this->field_id !== '')) {
				    $id = WC()->checkout->get_value('' . $this->field_id . '');

                    if ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
                        $id = str_replace( '.', '', $id );
                        $id = str_replace( '-', '', $id );
                    }
				}
				else{
                    if ((get_locale() == 'pt_PT' || get_locale() == 'pt_BR')) {
                        $id = WC()->checkout->get_value('billing_cpf') !== "" && WC()->checkout->get_value('billing_cpf') !== " " ? WC()->checkout->get_value('billing_cpf') : WC()->checkout->get_value('billing_id');

                        $id = str_replace( '.', '', $id );
                        $id = str_replace( '-', '', $id );

                    }
                    else {
                        $billing_cedula = WC()->checkout->get_value('billing_cedula');
                        $billing_id = WC()->checkout->get_value('billing_id');
                        $billing_nmero = WC()->checkout->get_value('billing_nmero');
                        $billing_numero = WC()->checkout->get_value('billing_numero');

                        if (isset($billing_id)) {
                            $id = $billing_id;
                        }
                        else if(isset($billing_cedula)) {
                            $id = $billing_cedula;
                        }
                        else if (isset($billing_nmero)) {
                            $id = $billing_nmero;
                        }
                        else if (isset($billing_numero)) {
                            $id = $billing_numero;
                        }
                    }
                }

                $client->idType = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'CPF' : 'CC';
                $client->idNumber =  $id;
                $client->firstName = isset($this->field_billing_first_name) && ($this->field_billing_first_name !== '') ?
                                     WC()->checkout->get_value('' . $this->field_billing_first_name . '') :
                                     (($order->get_shipping_first_name() !== "" && $order->get_shipping_first_name() !== " ")  ?
                                     $order->get_shipping_first_name() : $order->get_billing_first_name());

                $client->lastName = isset($this->field_billing_last_name) && ($this->field_billing_last_name !== '') ?
                                    WC()->checkout->get_value('' . $this->field_billing_last_name . '') :
                                    (($order->get_shipping_last_name() !== "" && $order->get_shipping_last_name() !== " ")  ?
                                    $order->get_shipping_last_name() : $order->get_billing_last_name());

                $client->email = isset($this->field_billing_email) && ($this->field_billing_email !== '') ?
                                 WC()->checkout->get_value('' . $this->field_billing_email . '') :
                                 $order->get_billing_email();

                $client->cellphone = isset($this->field_billing_phone) && ($this->field_billing_phone !== '') ?
                                 WC()->checkout->get_value('' . $this->field_billing_phone . '') :
                                 $order->get_billing_phone();

                $client->cellphoneCountryCode = (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? '+55' : '+57';
                $client->address = $client_address;

                // //Get URL redirection
                $site_url = get_site_url();
                // note: may this code will not be needed in the future
				// $url = str_replace( 'https://', 'http://', $site_url );
                $allyUrlRedirection->logoUrl = '';
                $allyUrlRedirection->callbackUrl = $site_url . '/?wc-api=wc_addi_gateway';
                $allyUrlRedirection->redirectionUrl = wc_get_checkout_url() . '?wc-order-id=' . $order_id;

                /*
                * Array with parameters for API interaction
                */
                $body_online_application = [
                    'orderId'  => $order->get_id(),
                    'totalAmount' => number_format($order->get_total(), 1, '.', ''),
                    'shippingAmount' => number_format($order->get_shipping_total(), 1, '.', ''),
                    'totalTaxesAmount' => number_format(round((($order->get_total()/1.19) * 1.19) - ($order->get_total()/1.19), 1), 1, '.', ''),
                    'currency' => (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'BRL' : 'COP',
                    'items' => $items,
                    'client' => $client,
                    'shippingAddress' => $client_address,
                    'allyUrlRedirection' => $allyUrlRedirection,
                ];

				// print_r(array_values($body_online_application));

                $body_online_application = wp_json_encode( $body_online_application );

                $options_online_application = [
                    'body'        => $body_online_application,
                    'headers'     => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer '. $body_auth_response['access_token'].'',
                    ],
                    'timeout'     => 100,
                    'data_format' => 'body',
                ];

                // getting api url based on test mode checkbox
                $online_app_url = $this->testmode ? ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://api.addi-staging-br.com/v1/online-applications' : 'https://api.addi-staging.com/v1/online-applications') : ( (get_locale() == 'pt_PT' || get_locale() == 'pt_BR') ? 'https://api.addi.com.br/v1/online-applications' : 'https://api.addi.com/v1/online-applications');

                // request
                $online_application_response = wp_remote_post( $online_app_url, $options_online_application );

				// verify if body response is an error or contains data
                $body_online_application_response = json_decode( $online_application_response['body'], true );

                if(!is_array($body_online_application_response)) {
                    $body_online_application_response = array();
                }

			    $invalid = in_array("000-009", $body_online_application_response) || in_array("El documento de identidad es inválido", $body_auth_response) || in_array("documento", $body_auth_response) || in_array("inválido", $body_auth_response);
                // verify if body response is an error or contains data
                if( !is_wp_error( $online_application_response) && !$invalid) {

                     try {
                         // getting decoded body
                         $http_response_history = $online_application_response['http_response']->get_response_object()->history;
                         $found = false;
                         $location_value;

                        //loop in response in order to look for a location header with a determined search parameter, it will
                        // contain Addi redirect url
                        foreach($http_response_history as $key => $value) {
                            $response_headers = $value->headers;
                            if(isset($response_headers)) {
                                $location_array = $response_headers->getValues('location');
                                if(isset($location_array)) {
                                    $location_string = $location_array[0];
                                    $search = 'token';
                                    $location_contain = preg_match("/{$search}/i",$location_string);
                                    if($location_contain) {
                                       $found = true;
                                       $location_value =  $location_array[0];
                                       break;
                                    }
                                }
                            }
                        }

                         //Redirect to addi checkout page
                         return array(
                             'result' => 'success',
                             'redirect' => $location_value
                         );
                     }
                     catch(Exception $e) {
                         // If something go wrong, show notification
                         wc_add_notice( __('Error procesando el pedido. Por favor, inténtalo de nuevo.', 'buy-now-pay-later-addi'), 'error' );
                         return;
                     }

                }
                else {
                    // If something go wrong, show notification
                    wc_add_notice( __('Error procesando el pedido. Documento de identidad inválido. Por favor, inténtalo de nuevo.', 'buy-now-pay-later-addi'), 'error' );
                    return;
                }

                return;
            }
            else {
                // If something go wrong, show notification
                wc_add_notice( __('Error procesando el pedido. Credenciales inválidas. Por favor, inténtalo de nuevo.', 'buy-now-pay-later-addi'), 'error' );
                return;
            }
        }

        /* Function to start sessions : sessions are necesarry to store order id and use it when third party page is redirecting to checkout page
        * Disclaimer: use of this function may conflict with server based cache services, we cannot support it’s use on servers. if this is the case,
        * please contact an administrator.
        */
        function pacca_start_session() {

            global $wp;
            global $wpdb;

            if(!session_id()) {
                session_start();
            }
        }


        /*
		 * Callback function to process Addi response from official website
		 */
		public function callback_handler() {

            global $woocommerce;
            global $wp;
            global $wpdb;

            // LOAD THE WC LOGGER
            $logger = wc_get_logger();

            // set init headers
            // content should be json
            // accept json only
            header('Content-type: application/json');
            header('Accept: application/json');

            if($this->logs == 'yes') {
                $logger->info( 'auth user ' . $_SERVER['PHP_AUTH_USER'] . '', array( 'source' => 'auth-log' ) );
                $logger->info( 'auth PW ' . $_SERVER['PHP_AUTH_PW'] . '', array( 'source' => 'auth-log' ) );
                $logger->info( 'remote User ' . $_SERVER['REMOTE_USER'] . '', array( 'source' => 'auth-log' ) );
                $logger->info( 'Server Auth ' . $_SERVER['HTTP_AUTHORIZATION'] . '', array( 'source' => 'auth-log' ) );
            }

            // verify if user/password are correct
            if ((base64_encode($_SERVER['PHP_AUTH_USER']) != base64_encode($this->callback_user)) ||
                (base64_encode($_SERVER['PHP_AUTH_PW']) != base64_encode($this->callback_password))) {
                // if not, will return a 401 Unauthorized error
                header('WWW-Authenticate: Basic realm="' . gethostname() .'"');
                header('HTTP/1.0 401 Unauthorized');
                return 'Bad request, try again.';
                exit;
            }
            else {
                // init headers to return a success response
                header("Authorization: Basic " . base64_encode("$this->callback_user':'$this->callback_password"));
                header( 'HTTP/1.1 200 OK' );
                // read parameter from body request / response
                $raw_post = file_get_contents( 'php://input' );
                $table_name = $wpdb->prefix . "wc_addi_gateway";

                if (!empty($raw_post))
                {
                    // handle post data
                    $callback_response = json_decode( $raw_post, true );

                    $callback_order_id = $callback_response['orderId'];
                    $callback_status = $callback_response['status'];
                    $callback_applicationId = $callback_response['applicationId'];

                    try{
                        if( !is_admin() ){
                            WC()->session->set( 'order_id_callback' , $callback_order_id );
                            WC()->session->set( 'order_status_callback' , $callback_status );
                        }
                    }
                    catch(Exception $e) {
                        //error logged in logger object
                        $logger->info( '  ERROR saving order id/ order status variable in callback method. Error details: ' . $e . ' ', array( 'source' => 'addi-gateway-log' ) );
                    }
                    // insert in table taking callback order id / callback status
                    $wpdb->insert($table_name, array('order_id' => $callback_order_id, 'order_status' => $callback_status, 'date' => date("Y-m-d h:i:s")) );

                    if($callback_status == 'APPROVED') {

                        try {

                            // get woocommerce order object
                            $order = wc_get_order( $callback_order_id );
                            // The text for the note
                            $note = __("ApplicationId : " . $callback_applicationId);
                            // Add the note
                            $order->add_order_note( $note );
                            // mark this order as completed
                            $order->payment_complete();

                            if($this->custom_order_status == 'yes') {
                               $order->update_status( 'addi-approved', '', true );
                            }

                            // Reduce stock of product in the store
                            $order->reduce_order_stock();
							$order->set_transaction_id($callback_applicationId);
                            $order->save();

                            // // Empty cart
                            if(isset($woocommerce) && isset($woocommerce->cart)){
                                $woocommerce->cart->empty_cart();
                            }

                            if($this->logs == 'yes') {
                                $logger->info( 'Order with ID = ' . $callback_order_id . '. not proccesed correctly. ', array( 'source' => 'auth-log' ) );
                            }
                        }
                        catch(Exception $e) {
                            if($this->logs == 'yes') {
                                $logger->info( 'Error processing order with ID =  ' . $callback_order_id . '. Details : ' . $e, array( 'source' => 'auth-log' ) );
                            }
                        }
                    }
                    else {
                        // get woocommerce order object
                        $order = wc_get_order( $callback_order_id );
                        // The text for the note
                        $note = __("ApplicationId : " . $callback_applicationId);
                        // Add the note
                        $order->add_order_note( $note );
						$order->set_transaction_id($callback_applicationId);
                        $order->save();
                    }

                    // returning same data post
                    echo $raw_post;
                    // exit
                    die();
                }
                else {
                    // returning same data post
                    echo $raw_post;
                    // exit
                    die();
                }
            }
        }

        private function logger_dna( $comment, $document = '', $url = '', $method = 'POST',
                                     $status_code = 200, $duration_ms = 0, $phone = '', $email = '') {
            $message_log = array(
                'id' => 'TEST_ID',
                'date' => date("Y-m-d h:i:s"),
                'document' => $document,
                'comment' => $comment,
                'phone' => $phone,
                'email' => $email
            );

            $body = array(
                'method' => $method,
                'url' => $url,
                'status' => $status_code,
                'body' => $message_log,
                'durationMs' => $duration_ms
            );

            $source = 'woocommerce_widget';
            $logger_api = 'https://logger-sandbox.addi.com';

            wp_remote_post( $logger_api . '/api/logger/' . $source ,  array(
                'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
                'body'        => json_encode($body),
                'method'      => 'POST',
                'data_format' => 'body',
            ));
        }

 	}
}
?>