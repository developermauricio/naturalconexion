<?php
global $woocommerce_wpml,
       $woocommerce_wpwoof_common, /* get map fields */
       $wpwoot_catalog;  /* open graph plugin variable */
/*  DETECTING WMPL   */
if ( WoocommerceWpwoofCommon::isActivatedWMPL() ) {?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var feed_lg_elm = jQuery("select[name='feed_use_lang']");
            setLanguageToFeed(feed_lg_elm.val());
         });
        function setLanguageToFeed(lang){
            if(!lang) lang='all';
            jQuery("#lang_wpwoof_categories li.language_all").each(function(){
                var elm = jQuery(this);
                if(elm.hasClass('language_'+lang)) {
                    elm.show();
                } else {
                    elm.hide();
                }
            });
        }
    </script>
    <?php /* Language WMPL BLOCK */ ?>
    <hr class="wpwoof-break" />
    <h4 class="wpwoofeed-section-heading"><?php  echo __('WPML Detected', 'woocommerce_wpwoof'); ?></h4>
    <table class="form-table wpwoof-addfeed-top">
        <tr class="addfeed-top-field wpwoof-open-popup-wrap">
            <th class="addfeed-top-label"><?php  echo __('Select language:', 'woocommerce_wpwoof'); ?></th>
            <td class="addfeed-top-value">
                <select onchange="setLanguageToFeed(this.value)" name="feed_use_lang">
                    <?php
                    $sel = (!empty($wpwoof_values['feed_use_lang'])) ? $wpwoof_values['feed_use_lang'] : ICL_LANGUAGE_CODE;
                    /* ICL_LANGUAGE_CODE; */
                    ?>
                    <option value="all" <?php if($sel=='all') echo "selected='selected'" ?> ><?php  echo __('All Languages', 'woocommerce_wpwoof'); ?></option>
                    <?php
                    $aLanguages = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str');
                    foreach($aLanguages as $lang){
                        ?><option value="<?php echo $lang['language_code']; ?>" <?php if($sel==$lang['language_code']) echo "selected='selected'" ?>><?php echo (!empty($lang['translated_name']) ? $lang['translated_name'] : $lang['language_code']); ?></option><?php
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table><?php
}                               /* END Language WMPL BLOCK */
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                               /* Currency WMPL BLOCK */
?>
    
        <hr class="wpwoof-break" />
        <h4 class="wpwoofeed-section-heading"><?php  echo __('Multicurrency', 'woocommerce_wpwoof'); ?></h4>
        <p>The plugin works with the following multi-curency plugins:</p>
        <br>
        <h5 class="wpwoofeed-section-heading-plgn">WooCommerce Multilingual - <a target="_blank" href="https://wpml.org/documentation/related-projects/woocommerce-multilingual/">link</a></h5>
    <?php    if(WoocommerceWpwoofCommon::isActivatedWMPL() && isset($woocommerce_wpml) && is_object($woocommerce_wpml) && method_exists ( $woocommerce_wpml->multi_currency , 'get_currencies' ) ) { ?>
        <p>Active</p>
        <table class="form-table wpwoof-addfeed-top">
            <tr class="addfeed-top-field wpwoof-open-popup-wrap">
                <th class="addfeed-top-label"><?php  echo __('Select currency:', 'woocommerce_wpwoof'); ?></th>
                <td class="addfeed-top-value">
                    <select name="feed_use_currency">
                        <?php
                        $sel = (!empty($wpwoof_values['feed_use_currency'])) ? $wpwoof_values['feed_use_currency'] : false;

                        $aCurrencies = $woocommerce_wpml->multi_currency->get_currencies('include_default = true');
                        foreach ($aCurrencies as $currency => $cur_data) {
                            ?>
                            <option
                            value="<?php echo $currency; ?>" <?php
                            if ($sel == $currency ) { echo "selected='selected'"; }
                            ?>><?php echo $currency; ?></option><?php
                        }
                        ?>
                    </select>
                </td>
                <!-- <p class="description"><span></span><span>Select currency for feed.</span></p> -->
            </tr>
        </table><?php
    } else { ?>
        <p>Not active</p>
    <?php }

                         /* END Currency WMPL BLOCK */
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

     /* if DETECTING WMPL */
    ?>
        <hr class="wpwoof-break"/>
        <h5 class="wpwoofeed-section-heading-plgn">Multi Currency for WooCommerce - <a target="_blank" href="https://wordpress.org/plugins/woo-multi-currency/">link</a></h5>
                <?php
                    if( WoocommerceWpwoofCommon::isActivatedWMCL() ){ /* woocommerce-multi-currency */
                            $aWMLC =  WoocommerceWpwoofCommon::isActivatedWMCL('settings');
        ?>
        <p>Active</p>
        <table class="form-table wpwoof-addfeed-top">
        <tr class="addfeed-top-field wpwoof-open-popup-wrap">
            <th class="addfeed-top-label"><?php echo __('Select currency:', 'woocommerce_wpwoof'); ?></th>
            <td class="addfeed-top-value">
                <select name="feed_use_currency"><?php
                    $sel = (!empty($wpwoof_values['feed_use_currency'])) ? $wpwoof_values['feed_use_currency'] : false;
                    if(!$sel) $sel=$aWMLC['currency_default'];
                    $aCurrencies = $aWMLC['currency'];
                    foreach ($aCurrencies as $currency => $cur_data) {
                        ?><option
                        value="<?php echo $cur_data; ?>" <?php
                        if ($sel == $cur_data ) { echo "selected='selected'"; }
                        ?>><?php echo $cur_data; ?></option><?php
                    }
                    ?>
                </select>
            </td>
            <!-- <p class="description"><span></span><span>Select currency for feed.</span></p> -->
        </tr>
        </table><?php
} else {
    echo '<p>Not active</p>';
}
?>
        <hr class="wpwoof-break"/>
        <h5 class="wpwoofeed-section-heading-plgn">Currency Switcher for WooCommerce - <a target="_blank" href="https://wordpress.org/plugins/woo-multi-currency/">link</a></h5>
<?php if(WoocommerceWpwoofCommon::isActivatedWCS()){ /* currency-switcher-woocommerce */

        $function_currencies = alg_get_enabled_currencies();
        $currencies          = get_woocommerce_currencies();

        $selected_currency   = (!empty($wpwoof_values['feed_use_currency'])) ? $wpwoof_values['feed_use_currency'] : false;
        if(! $selected_currency)  $selected_currency=alg_get_current_currency_code();
        ?>
        <p>Active</p>
        <table class="form-table wpwoof-addfeed-top">
        <tr class="addfeed-top-field wpwoof-open-popup-wrap">
            <th class="addfeed-top-label"><?php echo __('Select currency:', 'woocommerce_wpwoof'); ?></th>
            <td class="addfeed-top-value">
                <select name="feed_use_currency"><?php
                    foreach ( $function_currencies as $currency_code ) {
                        if ( isset( $currencies[ $currency_code ] ) ) {
                            if ( '' == $selected_currency ) {
                                $selected_currency = $currency_code;
                            }
                            ?><option value="<?php echo $currency_code; ?>" <?php echo selected( $currency_code, $selected_currency, false ); ?>><?php
                                    echo $currency_code; ?></option><?php
                            }
                        }
                    ?>
                </select>
            </td>
        </tr>
        </table><?php
} else {
    echo '<p>Not active</p>';
} ?>
        <hr class="wpwoof-break"/>
        <h5 class="wpwoofeed-section-heading-plgn">Price Based on Country for WooCommerce - <a target="_blank" href="https://wordpress.org/plugins/woocommerce-product-price-based-on-countries/">link</a></h5>
        <?php if( WoocommerceWpwoofCommon::isActivatedWCPBC() ){  ?>
        <p>Active</p>
        <table class="form-table wpwoof-addfeed-top">
        <tr class="addfeed-top-field wpwoof-open-popup-wrap">
            <th class="addfeed-top-label"><?php echo __('Select currency:', 'woocommerce_wpwoof'); ?></th>
            <td class="addfeed-top-value">
                 <select name="feed_use_currency"><?php
                     $sel = (!empty($wpwoof_values['feed_use_currency'])) ? $wpwoof_values['feed_use_currency'] : false;
                     foreach ( WoocommerceWpwoofCommon::isActivatedWCPBC('settings') as $name => $currency_code ) {
                            ?><option value="<?php echo $name; ?>" <?php echo selected( $name, $sel ) ?>><?php
                            echo $currency_code['currency']." ( ".$name." )"; ?></option><?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        </table><?php
} else {
    echo '<p>Not active</p>';
} ?>
        <hr class="wpwoof-break"/>
        <h5 class="wpwoofeed-section-heading-plgn">WOOCS – Currency Switcher for WooCommerce - <a target="_blank" href="https://wordpress.org/plugins/woocommerce-currency-switcher/">link</a></h5>
        <?php if( WoocommerceWpwoofCommon::isActivatedWOOCS() ){
            global $WOOCS;?>
        <p>Active</p>
        <table class="form-table wpwoof-addfeed-top">
        <tr class="addfeed-top-field wpwoof-open-popup-wrap">
            <th class="addfeed-top-label"><?php echo __('Select currency:', 'woocommerce_wpwoof'); ?></th>
            <td class="addfeed-top-value">
                 <select name="feed_use_currency"><?php
                    $sel = (!empty($wpwoof_values['feed_use_currency'])) ? $wpwoof_values['feed_use_currency'] : false;
                    if(!$sel) $sel=$WOOCS->default_currency;
                     foreach ( $WOOCS->get_currencies() as $currency_code => $currencyArr ) {
                            ?><option value="<?php echo $currency_code; ?>" <?php echo selected( $currency_code, $sel ); ?>><?php
                            echo $currencyArr['name']; ?></option><?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        </table><?php
} else {
    echo '<p>Not active</p>';
}
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      /* Output ID field */
      $oFeedFBGooglePro->renderFields($all_fields['ID'], $meta_keys, $meta_keys_sort, $attributes, $wpwoof_values);

    ////////////////////////////////////////////////////////////////// TAX BLOCk  //////////////////////////////////////////////////////////////////////////////////////
?>
<hr class="wpwoof-break" />
<h4 class="wpwoofeed-section-heading">Prices & Tax:</h4>
<table class="form-table wpwoof-addfeed-top">
<tr class="addfeed-top-field">
    <th class="addfeed-top-label">Variable products price:</th>
    <td class="addfeed-top-value">
        <select name="feed_variable_price">
            <option <?php if(isset($wpwoof_values['feed_variable_price'])) { selected( "small", $wpwoof_values['feed_variable_price'], true); } ?> value="small">Smaller Price</option>
            <option <?php if(isset($wpwoof_values['feed_variable_price'])) { selected( "big",   $wpwoof_values['feed_variable_price'], true); } ?> value="big"  >Bigger Price</option>
            <option <?php if(isset($wpwoof_values['feed_variable_price'])) { selected( "first", $wpwoof_values['feed_variable_price'], true); } ?> value="first">First Variation Price</option>
        </select>
    </td>
</tr>
</table>
<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/* Output TAX fields */
$oFeedFBGooglePro->renderFields($all_fields['TAX'], $meta_keys, $meta_keys_sort, $attributes, $wpwoof_values);
?>
<label class="stl-facebook stl-all stl-google stl-adsensecustom" style="display: inline;">
    <input type="checkbox" class="wpwoof-mapping" value="1" name="replace_price_sp" <?php checked( isset($wpwoof_values['replace_price_sp'])||!empty($wpwoof_values['replace_price_sp']) ); ?>> Replace the price with the sale price when possible
</label>
<?php
$at = wc_get_product_types();
if( isset( $at["subscription"] ) ) {
?>
<h4><br><br>We noticed that you use WooCommerce Subscriptions, please configure the pricing logic.</h4>

    <table class="form-table wpwoof-addfeed-top">
        <tr class="addfeed-top-field">
            <th class="addfeed-top-label">When there is a fee:</th>
            <td class="addfeed-top-value">
                <select name="feed_subscriptions[fee]">
                    <option <?php
                        if( !isset( $wpwoof_values['feed_subscriptions']['fee'] ) ||  $wpwoof_values['feed_subscriptions']['fee'] == "feeplusprice" ) {
                            ?> selected <?php
                        } ?> value="feeplusprice">Use Fee + Subscription Price</option>
                    <option <?php
                        if( isset( $wpwoof_values['feed_subscriptions']['fee'] ) ) {
                            selected( "price",   $wpwoof_values['feed_subscriptions']['fee'], true);
                        } ?> value="price"  >Use just the Subscription Price</option>
                    <option <?php
                        if( isset( $wpwoof_values['feed_subscriptions']['fee'] ) ) {
                            selected( "fee", $wpwoof_values['feed_subscriptions']['fee'], true);
                        } ?> value="fee">Use just the Fee value</option>
                </select>
            </td>
        </tr>
    </table>

    <table class="form-table wpwoof-addfeed-top">
        <tr class="addfeed-top-field">
            <th class="addfeed-top-label">When free trial exists:</th>
            <td class="addfeed-top-value">
                <select name="feed_subscriptions[trial]">
                    <option <?php
                    if( !isset( $wpwoof_values['feed_subscriptions']['trial'] ) ||  $wpwoof_values['feed_subscriptions']['trial'] == "fee" ) {
                        ?> selected <?php
                    } ?> value="fee">Use the Fee value</option>
                    <option <?php
                    if( isset( $wpwoof_values['feed_subscriptions']['trial'] ) ) {
                        selected( "price",   $wpwoof_values['feed_subscriptions']['trial'], true);
                    } ?> value="price"  >Use the Subscription Price</option>
                    <option <?php
                    if( isset( $wpwoof_values['feed_subscriptions']['trial'] ) ) {
                        selected( "feeplusprice", $wpwoof_values['feed_subscriptions']['trial'], true);
                    } ?> value="feeplusprice"> Use the Fee + Subscription price</option>
                    <option <?php
                    if( isset( $wpwoof_values['feed_subscriptions']['trial'] ) ) {
                        selected( "zerro", $wpwoof_values['feed_subscriptions']['trial'], true);
                    } ?> value="zerro"> Always show a "0" price</option>
                </select>
            </td>
        </tr>
    </table>
    <p  style="display: block;">The same logic will apply to the "sale price".</p>
<?php } ?>
<script type="text/javascript">
    function showHideRedBox(){
        if(jQuery('#IDtax_countries').length>0){
           if( jQuery('#IDtax_countries').val()==""){
               jQuery('#IDtax_countriesdiv').addClass('redbox');
           }else{
               jQuery('#IDtax_countriesdiv').removeClass('redbox');
           }
        }
    }
    function showHideCountries(value){
        if(value=='false') {
            jQuery('.CSS_tax_countries').hide();
        } else  {
            jQuery('.CSS_tax_countries').show();
            showHideRedBox();

        }
    }
    jQuery(document).ready(function($) {
        if ($('#ID_tax_field').length>0) {
            showHideCountries($('#ID_tax_field').val());
        }
        $(":input").inputmask();
    });
</script>
<?php
////////////////////////////////////////////////////////////////// END TAX BLOCk  //////////////////////////////////////////////////////////////////////////////////////

?>
 <hr class="wpwoof-break stl-facebook" />
    <h4 class="wpwoofeed-section-heading stl-facebook">Inventory:</h4><p class="stl-facebook" ></p>
    <div class="input-number-with-p-inside stl-facebook">
            <input type="hidden" value="0"  name="field_mapping[inventory][value]">
            <input type="checkbox" class="ios-switch" value="1" id="inventory" name="field_mapping[inventory][value]"<?php
            if( !isset($wpwoof_values['field_mapping']['inventory']['value']) || ! empty($wpwoof_values['field_mapping']['inventory']['value']) ) echo ' checked '; if (!isset($wpwoof_values['field_mapping']['inventory']['value'])) echo 'data-new="1"'; ?> />
            <label class="addfeed-top-label" for="inventory">Add the "inventory" field to your feed</label>
  </div>
  <div class="input-number-with-p-inside stl-facebook" style="display: block;">
     <p>If WooCommerce stock management is disabled and the product is in stock, use this value:</p>
     <input type="number" name="field_mapping[inventory][default]" value="<?php  echo !isset($wpwoof_values['field_mapping']['inventory']['default']) ? 5 : (int)$wpwoof_values['field_mapping']['inventory']['default']; ?>">
  </div>

<?php
////////////////////////////////////////////////////////////////// FILTER BLOCk  //////////////////////////////////////////////////////////////////////////////////////
?>
<hr class="wpwoof-break" />
<h4 class="wpwoofeed-section-heading">Filters:</h4>
<div class="filter_flex">
    <div class="wpwoof-addfeed-top">
        <div class="filter_flex_section">
            <input type="hidden" name="feed_remove_variations" value="0">
            <input type="checkbox" class="ios-switch" value="1" id="feed_remove_variations" name="feed_remove_variations"<?php
            if( ! empty($wpwoof_values['feed_remove_variations']) ) echo ' checked '; ?> />
            <label class="addfeed-top-label" for="feed_remove_variations">Exclude variations for variable products</label>
        </div>
        <div class="filter_flex_section">
            <input type="hidden" value="0"  name="feed_variation_show_main">
            <input type="checkbox" class="ios-switch" value="1" id="feed_variation_show_main" name="feed_variation_show_main"<?php
            if( !isset($wpwoof_values['feed_variation_show_main']) || ! empty($wpwoof_values['feed_variation_show_main']) ) echo ' checked '; ?> />
            <label class="addfeed-top-label" for="feed_variation_show_main">Show main variable product item</label>
        </div>
        <div class="filter_flex_section">
            <input type="hidden" value="0"  name="feed_group_show_main">
            <input type="checkbox" class="ios-switch" value="1" id="feed_group_show_main" name="feed_group_show_main"<?php
            if( !isset($wpwoof_values['feed_group_show_main']) || ! empty($wpwoof_values['feed_group_show_main']) ) echo ' checked '; ?> />
            <label class="addfeed-top-label" for="feed_group_show_main">Show main grouped product item</label>
        </div>
        <div class="filter_flex_section">
            <input type="hidden" value="0"  name="feed_bundle_show_main">
            <input type="checkbox" class="ios-switch" value="1" id="feed_bundle_show_main" name="feed_bundle_show_main"<?php
            if( !isset($wpwoof_values['feed_bundle_show_main']) || ! empty($wpwoof_values['feed_bundle_show_main']) ) echo ' checked '; ?> />
            <label class="addfeed-top-label" for="feed_bundle_show_main">Show main bundle product item</label>
        </div>
        <div>
            Price bigger: 
            <input id="feed_filter_price_bigger" inputmode="decimal" name="feed_filter_price_bigger"  data-inputmask="'alias': 'numeric', 'digits': 2, 'digitsOptional': true,  'placeholder': '0'" inputmode="numeric" style="text-align: right;" size="6" value="<?php if( isset($wpwoof_values['feed_filter_price_bigger'])) echo $wpwoof_values['feed_filter_price_bigger']; ?>"> 
            smaller: 
            <input id="feed_filter_price_smaller" inputmode="decimal" name="feed_filter_price_smaller" data-inputmask="'alias': 'numeric', 'digits': 2, 'digitsOptional': true,  'placeholder': '0'" inputmode="numeric" style="text-align: right;" size="6" value="<?php if( isset($wpwoof_values['feed_filter_price_smaller'])) echo $wpwoof_values['feed_filter_price_smaller']; ?>"> 
        </div>
    </div>
    <div class="wpwoof-addfeed-top">
        <div class="wpwoof-open-popup-wrap">
            <a href="#chose_categories" class="wpwoof-button wpwoof-button-blue wpwoof-open-popup" id="wpwoof-select-categories">Select Product Categories</a>
            <div class="wpwoof-popup-wrap" style="display: none;">
                <div class="wpwoof-popup-bg"></div>
                <div class="wpwoof-popup">
                    <div class="wpwoof-popup-close" tabindex="0" title="Close"></div>
                    <div class="wpwoof-popup-form" >
                        <div id="wpwoof-popup-categories" class="wpwoof-popup-body">
                            <?php wpwoofcategories( $wpwoof_values ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="wpwoof-open-popup-wrap">
            <a href="#chose_product_type" class="wpwoof-button wpwoof-button-blue wpwoof-open-popup" id="wpwoof-select-product_type">Select Product Types</a>
            <div class="wpwoof-popup-wrap" style="display: none;">
                <div class="wpwoof-popup-bg"></div>
                <div class="wpwoof-popup">
                    <div class="wpwoof-popup-close" tabindex="0" title="Close"></div>
                    <div class="wpwoof-popup-form" >
                        <div id="wpwoof-popup-type" class="wpwoof-popup-body">
                            <p><b>Please select product types</b></p>
                            <ul>
                                <?php
                                $is_empty_product_type = true;
                                if( ! empty($wpwoof_values['feed_filter_product_type']) &&
                                    is_array($wpwoof_values['feed_filter_product_type']) &&
                                    count($wpwoof_values['feed_filter_product_type']) > 0 )
                                {
                                    $is_empty_product_type = false;
                                }
                                foreach ( wc_get_product_types() as $value => $label ) {
                                    $selected = true;
                                    if( ! $is_empty_product_type ) {
                                        $selected = in_array($value, $wpwoof_values['feed_filter_product_type']);
                                    }
                                    echo '<li><label class="wpwoof_checkboxes_top"><input type="checkbox" name="feed_filter_product_type[]" value="' . esc_attr( $value ) . '" ' .
                                        ( $selected ? 'checked' : '' ) .'>' . esc_html( $label ) . '</label></li>';
                                }
                                ?>
                            </ul>
                            <div id="wpwoof-popup-bottom"><a href="javascript:void(0);" class="button button-secondary wpwoof-popup-done">Done</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-label">
            <p>Stock:</p>
            <select name="feed_filter_stock">
                <option <?php if(isset($wpwoof_values['feed_filter_stock'])) { selected( "all", $wpwoof_values['feed_filter_stock'], true); } ?> value="all">All Products</option>
                <option <?php if(isset($wpwoof_values['feed_filter_stock'])) { selected( "instock", $wpwoof_values['feed_filter_stock'], true); } ?> value="instock">Only in stock</option>
                <option <?php if(isset($wpwoof_values['feed_filter_stock'])) { selected( "outofstock", $wpwoof_values['feed_filter_stock'], true); } ?> value="outofstock">Only out of stock</option>
            </select>
        </div>
        <div class="flex-label">
            <p>Sale:</p>
            <select name="feed_filter_sale">
                <option <?php if(isset($wpwoof_values['feed_filter_sale'])) { selected( "all", $wpwoof_values['feed_filter_sale'], true); } ?> value="all">All Products</option>
                <option <?php if(isset($wpwoof_values['feed_filter_sale'])) { selected( "sale", $wpwoof_values['feed_filter_sale'], true); } ?> value="sale">Only products on sale</option>
                <option <?php if(isset($wpwoof_values['feed_filter_sale'])) { selected( "notsale", $wpwoof_values['feed_filter_sale'], true); } ?> value="notsale">Only products not on sale</option>
            </select>
        </div>
    </div>
</div><?php
////////////////////////////////////////////////////////////////// END FILTER BLOCk  //////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////// Smart Settings BLOCk  //////////////////////////////////////////////////////////////////////////////////////
$wpwoofLastLabelValue = 12;
?>
<hr class="wpwoof-break stl-facebook stl-google" />
<h4 class="wpwoofeed-section-heading stl-facebook stl-google">Smart Tags:</h4>
<div class="input-number-with-p-inside stl-facebook stl-google">
    <p>Add the "recent-product" tag for the lastest</p>
    <input type="number" name="feed_filter_recent-product"  value="<?php
      echo (isset($wpwoof_values['feed_filter_recent-product'])  && (int)$wpwoof_values['feed_filter_recent-product']>0  ) ? (int)$wpwoof_values['feed_filter_recent-product'] : $wpwoofLastLabelValue;
      ?>" />
    <p>products</p>
</div>
<?php /*div class="input-number-with-p-inside stl-facebook stl-google">
    <p>Add the "top-7-days" tag to the</p>
    <input type="number" name="feed_filter_top-7-days" value="<?php
    echo (isset($wpwoof_values['feed_filter_top-7-days'])  && (int)$wpwoof_values['feed_filter_top-7-days']>0  ) ? (int)$wpwoof_values['feed_filter_top-7-days'] : $wpwoofLastLabelValue;
    ?>" />
    <p>products in the last 7 days</p>
</div */ ?>
<div class="input-number-with-p-inside stl-facebook stl-google">
    <p>Add the "top-30-days" tag to the</p>
    <input type="number" name="feed_filter_top-30-days" value="<?php
    echo (isset($wpwoof_values['feed_filter_top-30-days'])  && (int)$wpwoof_values['feed_filter_top-30-days']>0  ) ? (int)$wpwoof_values['feed_filter_top-30-days'] : $wpwoofLastLabelValue;
    ?>" />
    <p>products in the last 30 days</p>
</div>
<p class="stl-facebook stl-google">These tags are added under the custom_label_0. Use them to create Product Sets.</p><?php
//////////////////////////////////////////////////////////////////END LIMIT AND LABEL BLOCk  //////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////// TITLE/DESCRIPTION Capital letter BLOCk  //////////////////////////////////////////////////////////////////////////////////////
$wpwoof_is_old = ( !empty( $wpwoof_values['field_mapping']['description']['value'] ) && is_string( $wpwoof_values['field_mapping']['description']['value'] ) && strpos($wpwoof_values['field_mapping']['description']['value'],'wpwoofdefa_')!==false );
?>
<hr class="wpwoof-break" />
<h4 class="wpwoofeed-section-heading">Product Titles Settings:</h4>
    <br><br>
    <label>
        <input name="custom-title" type="checkbox" value="1" <?php if( !empty($wpwoof_values['custom-title']) ) echo ' checked'; ?> />
        Use custom titles
    </label>
    <br><br>
    <label>
        <input name="add-variation-title" type="checkbox" value="1" <?php if( !empty($wpwoof_values['add-variation-title']) ) echo ' checked'; ?> />
        Add variation title in the product name
    </label>
    <br><br>
    <label>
        <input name="title-uc_every_first" type="checkbox" value="1" <?php if( !empty($wpwoof_values['title-uc_every_first']) || !empty($wpwoof_values['title']['uc_every_first'])) echo ' checked'; ?> />
        Remove capital letters from product titles
    </label>
<hr class="wpwoof-break" />
<h4 class="wpwoofeed-section-heading">Product Descriptions Settings:</h4>
<h4><br/><br/>The plugin will fill descriptions in this order:</h4>
    <label>
        <input name="custom-description" type="checkbox" value="1" <?php if( !empty($wpwoof_values['custom-description']) ) echo ' checked'; ?> />
        Use custom description
    </label>
    <br><br>
    <label>
        <input name="field_mapping[description][0]" type="hidden" value="0">
        <input name="field_mapping[description][0]" type="checkbox"  value="description_short"    <?php
        if( !empty($wpwoof_values['field_mapping']['description'][0]) || ( !isset($wpwoof_values['field_mapping']['description'][0]) && !$wpwoof_is_old )
            || ( $wpwoof_is_old &&  $wpwoof_values['field_mapping']['description']['value']=='wpwoofdefa_description_short')
        ) echo ' checked';
        ?>/>
        Short description
    </label>
    <br><br>
    <label>
        <input name="field_mapping[description][1]" type="hidden" value="0">
        <input name="field_mapping[description][1]" type="checkbox" value="description"    <?php
        if( !empty($wpwoof_values['field_mapping']['description'][1])
            || ( !isset($wpwoof_values['field_mapping']['description'][1]) && !$wpwoof_is_old)
            || ( $wpwoof_is_old && $wpwoof_values['field_mapping']['description']['value']=='wpwoofdefa_description')
        ) echo ' checked';
        ?> />
        Description
    </label>
    <br><br>
    <label>
        <input name="field_mapping[description][2]" type="hidden" value="0">
        <input name="field_mapping[description][2]" type="checkbox" value="title"    <?php
        if( !empty($wpwoof_values['field_mapping']['description'][2])
            || ( !isset($wpwoof_values['field_mapping']['description'][2])   && !$wpwoof_is_old )
            || ( $wpwoof_is_old &&  $wpwoof_values['field_mapping']['description']['value']=='wpwoofdefa_title')
        ) echo ' checked';
        ?> />
        Product Title
    </label>
    <div class="stl-facebook" <?=(!isset($wpwoof_values['feed_type']) || $wpwoof_values['feed_type']!='facebook')? 'style="display: none;"':''?>>
        <hr class="wpwoof-break" />
        <label>
            <input name="field_mapping[add_short_description]" type="hidden" value="0">
            <input name="field_mapping[add_short_description]" type="checkbox" value="add_short_description"    <?php
            checked( !isset($wpwoof_values['field_mapping']['add_short_description'])
                ||  !empty($wpwoof_values['field_mapping']['add_short_description']));
            ?> />
            Add short description
        </label>
    </div>
            <?php
////////////////////////////////////////////////////////////////// TITLE/DESCRIPTION Capital letter BLOCk  //////////////////////////////////////////////////////////////////////////////////////

$fall_sel = $woocommerce_wpwoof_common->getPicturesFields();
?>
<hr class="wpwoof-break" />
<h4 class="wpwoofeed-section-heading">Product Images Settings:</h4>
<h4><br><br>The plugin will fill images in this order:</h4>
<label>
    <input name="wpwoofeed_images[0]" type="hidden" value="0">
    <input name="wpwoofeed_images[0]" value="custom" type="checkbox"  <?php
    if( !empty($wpwoof_values['wpwoofeed_images'][0]) || (!isset($wpwoof_values['wpwoofeed_images'][0]) && !$wpwoof_is_old ) ) echo ' checked';
    $oldSel = "";
    if( !empty($wpwoof_values["field_mapping"]["image_link"]["fallback image_link"]) &&
        ( $wpwoof_values["field_mapping"]["image_link"]["fallback image_link"] == 'wpfoof-carusel-box-media-name' //wpfoof-carusel-box-media-name
            ||
            $wpwoof_values["field_mapping"]["image_link"]["fallback image_link"] == 'wpfoof-box-media-name'
        )  ) {
            echo ' checked'; $oldSel = $wpwoof_values["field_mapping"]["image_link"]["fallback image_link"];
            $wpwoof_values['wpwoofeed_images']= array('custom' => "1");
         }



    ?> />  Custom images. When you edit your products you can add custom images.
</label>

<select name="wpwoofeed_images[custom]"   class="wpwoof_mapping wpwoof_mapping_option"><?php
    foreach ($fall_sel as $el=>$nm){ ?>
        <option value="<?php echo $el ?>" <?php
            if (!empty($wpwoof_values['wpwoofeed_images']['custom']) && ( !$wpwoof_is_old && $wpwoof_values['wpwoofeed_images']['custom']==$el || $wpwoof_is_old && $oldSel==$el ) ) {
                  ?>selected<?php
            } ?>><?php
        echo /*$oldSel."==".$el."|".*/ $nm;
        ?></option><?php
    }
?></select>

<?php  if ( is_plugin_active( WPWOOF_YSEO ) ){ ?>
    <br><br>
    <label>
        <input name="wpwoofeed_images[yoast_seo_product_image]" type="hidden" value="0">
        <input name="wpwoofeed_images[yoast_seo_product_image]" value="yoast_seo_product_image" type="checkbox" <?php
        if( !empty($wpwoof_values['wpwoofeed_images']['yoast_seo_product_image'])
            || ( $wpwoof_is_old && $wpwoof_values['field_mapping']['image_link']['value']=="wpwoofdefa_yoast_seo_product_image" )
        ) echo ' checked';
        ?> />
        YOAST SEO product image
    </label>
<?php } ?>
<br><br>
<label>
    <input name="wpwoofeed_images[product_image]" type="hidden" value="0">
    <input name="wpwoofeed_images[product_image]" value="product_image" type="checkbox" <?php
    if( !empty($wpwoof_values['wpwoofeed_images']['product_image'])
        || ( !isset($wpwoof_values['wpwoofeed_images']['product_image']) && !$wpwoof_is_old )
        || ( $wpwoof_is_old && $wpwoof_values['field_mapping']['image_link']['value']=="wpwoofdefa_image_link" )

    ) echo ' checked';
    ?> />
    Your product feature image.
    <?php
    $sel = (!empty($wpwoof_values['field_mapping']['image-size'])) ? $wpwoof_values['field_mapping']['image-size'] : "full";
    ?>
    <!-- p class="p_inline_block " style="display: inline-block;">Image size: </p -->
    <select name="field_mapping[image-size]" class="wpwoof_mapping wpwoof_mapping_option">
        <option value="full">Full</option>
        <?php
        global  $_wp_additional_image_sizes;
        foreach ( get_intermediate_image_sizes() as $_size ) {
            if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
                ?><option <?php echo ($sel==$_size) ? " selected " : "" ?> value="<?php echo $_size; ?>"><?php echo ucwords($_size); ?> <?php echo get_option( "{$_size}_size_w" )."X".get_option( "{$_size}_size_h"); ?></option><?php
                $sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
            } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
                ?><option <?php echo ($sel==$_size) ? " selected " : "" ?> value="<?php echo $_size; ?>"><?php echo ucwords($_size); ?> <?php echo  $_wp_additional_image_sizes[ $_size ]['width']."X".$_wp_additional_image_sizes[ $_size ]['height']; ?></option><?php

            }
        }?>
    </select>
</label>
    <br><br>
    <label>
        <input name="wpwoofeed_images[category]" type="hidden" value="0">
        <input name="wpwoofeed_images[category]" value="category" type="checkbox" <?php
        if( !empty($wpwoof_values['wpwoofeed_images']['category']) || ( !isset($wpwoof_values['wpwoofeed_images']['category']) && !$wpwoof_is_old ) ) echo ' checked';
        ?> />
        The category image
    </label>
<br><br>
<label>
    <input name="wpwoofeed_images[global]" type="hidden" value="0">
    <input name="wpwoofeed_images[global]" value="global" type="checkbox" <?php
    if( !empty($wpwoof_values['wpwoofeed_images']['global']) || (!isset($wpwoof_values['wpwoofeed_images']['global']) && !$wpwoof_is_old ) ) echo ' checked';
    ?> />
    The global image
</label>
<br><br>
<label class="stl-facebook stl-google">
    <input type="hidden" name="field_mapping[expand_more_images]" value="0">
    <input name="field_mapping[expand_more_images]"  class="ios-switch" type="checkbox"  value="1" <?php
    if( !empty($wpwoof_values['field_mapping']['expand_more_images']) || ( !isset($wpwoof_values['field_mapping']['expand_more_images']) && !$wpwoof_is_old ) ) echo ' checked';
    ?> />
    Include additional_images_link
</label>
<br><br>
<label>
    <input type="hidden" name="field_mapping[variation_parent_image]" value="0">
    <input name="field_mapping[variation_parent_image]"  class="ios-switch" type="checkbox"  value="1" <?php
    if( !empty($wpwoof_values['field_mapping']['variation_parent_image'])) echo ' checked';
    ?> />
    Use the parent image for variations
</label><?php
////////////////////////////////////////////////////////////////// END Product Images Settings BLOCk  //////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////// Product Condition BLOCk  //////////////////////////////////////////////////////////////////////////////////////
?>
<hr class="wpwoof-break stl-facebook stl-google" />
<h4 class="wpwoofeed-section-heading stl-facebook stl-google">Product Condition:</h4>
<h4 class="stl-facebook stl-google"><br><br>The plugin will fill condition in this order:</h4>
<p class="stl-facebook stl-google">The plugin's custom condition. When you edit your product you can select its condition.</p>
<?php
if ( is_plugin_active( WPWOOF_SMART_OGR ) ){
 ?><label class="stl-facebook stl-google">
    <input name="field_mapping[condition][opengraph]"  type="hidden"    value="0" >
    <input name="field_mapping[condition][opengraph]"  type="checkbox"  value="1" <?php
    if( !empty($wpwoof_values['field_mapping']['condition']['opengraph']) || !isset($wpwoof_values['field_mapping']['condition']['opengraph']) ) echo ' checked';
    ?> /> We've detected the Smart OpenGraph plugin. If custom condition is defined, it will be used.
    <br><br></label>
<?php }

$val = !empty($wpwoof_values['field_mapping']['condition']['define'] ) ? $wpwoof_values['field_mapping']['condition']['define'] : '';
?>
<p class="p_inline_block stl-facebook stl-google">This will be used if no condition is found: </p>
    <select class="stl-facebook stl-google" name="field_mapping[condition][define]">
        <option <?php if( $val=='new' ) {         ?>selected="selected" <?php } ?> value="new">new</option>
        <option <?php if( $val=='refurbished' ) { ?>selected="selected" <?php } ?> value="refurbished">refurbished</option>
        <option <?php if( $val=='used' ) {        ?>selected="selected" <?php } ?> value="used">used</option>
    </select><?php
////////////////////////////////////////////////////////////////// END Product Condition BLOCk  //////////////////////////////////////////////////////////////////////////////////////





