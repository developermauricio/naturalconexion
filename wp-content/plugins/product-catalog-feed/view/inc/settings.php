<?php
global $woocommerce_wpml,
       $woocommerce_wpwoof_common, /* get map fields */
       $wpwoot_catalog;  /* open graph plugin variable */

      /* Output ID field */
      $oFeedFBGooglePro->renderFields($all_fields['ID'], $meta_keys, $meta_keys_sort, $attributes, $wpwoof_values);

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
            if( isset($wpwoof_values['feed_variation_show_main']) && ! empty($wpwoof_values['feed_variation_show_main']) ) echo ' checked '; ?> />
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
</div><?php
////////////////////////////////////////////////////////////////// END FILTER BLOCk  //////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////// TITLE/DESCRIPTION Capital letter BLOCk  //////////////////////////////////////////////////////////////////////////////////////
$wpwoof_is_old = ( !empty( $wpwoof_values['field_mapping']['description']['value'] ) && is_string( $wpwoof_values['field_mapping']['description']['value'] ) && strpos($wpwoof_values['field_mapping']['description']['value'],'wpwoofdefa_')!==false );
?>
<hr class="wpwoof-break" />
<h4 class="wpwoofeed-section-heading">Product Descriptions Settings:</h4>
<h4><br/><br/>The plugin will fill descriptions in this order:</h4>
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

?>
<hr class="wpwoof-break" />
<h4 class="wpwoofeed-section-heading">Product Images Settings:</h4>
<h4><br><br>The plugin will fill images in this order:</h4>
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
<?php
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





