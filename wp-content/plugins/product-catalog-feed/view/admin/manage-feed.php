<?php
global $woocommerce_wpwoof_common;
include('feed-manage-list.php');
require_once dirname(__FILE__).'/../../inc/feedfbgooglepro.php';

$myListTable = new Wpwoof_Feed_Manage_list();
$myListTable->prepare_items();

?>
    <script>
        function storeWpWoofdata(){
            var data = jQuery('#iDwpwoofGLS').serialize()+"&action=set_wpwoof_global_data";
            jQuery.fn.saveWPWoofParam( data, function () {
                /*$('#idWpWoofGCats').html($('#feed_google_category').val());*/
            });
        }
        jQuery(function($){
            $('#IDextraGlobal input' ).change(storeWpWoofdata);
            $('#IDextraGlobal select').change(storeWpWoofdata);
        });
    </script>
<div class="wpwoof-content-top wpwoof-box headerManagePage">
    <div a>
        <a class="wpwoof-button wpwoof-button-orange1" id="idWpWoofAddNewFeed" href="#">Create New Feed</a>
    </div>
    <div b>
        <vr></vr>
    </div>
    <div c>
         <a a target="_blank" href="https://www.pixelyoursite.com/product-catalog-for-woocommerce-video-tutorials">VIDEO: Watch these short video tutorials for tips about the plugin.</a>
         <a a target="_blank" href="https://www.pixelyoursite.com/woocommerce-product-catalog-feed-help">Learn how to use the plugin</a>
         <a target="_blank" href="https://www.pixelyoursite.com/facebook-product-catalog-feed">Learn how to create a Facebook Product Catalog</a>
    </div>
</div>
    <div class="wpwoof-content-top wpwoof-box headerManagePage">
        <div a>
            <a class="wpwoof-button wpwoof-button-orange1 wpwoof-button-upgrade2pro" target="_blank" href="https://www.pixelyoursite.com/plugins/product-catalog-feed-for-woocommerce?utm_source=feed-free-plugin&utm_medium=plugin&utm_campaign=upgrade">Upgrade</a>
        </div>
        <div b>
            <vr></vr>
        </div>
        <div>
        <h3>Upgrade and you can:</h3>
            <ul>
                <li>Add additional fields to your fields and map them to existing product data (product attributes, custom fields).</li>
                <li>Add extra fields to your products. Any data that you want to use for your feeds is supported.</li>
                <li>Add custom titles, custom descriptions, and custom landing URLs for your feeds.</li>
                <li>Exclude products or categories from feeds.</li>
                <li>Use powerful filters when you create feeds (category, product type, stock, sale status.</li>
                <li>Filter products by price.</li>
                <li>Use custom labels to send extra data with your feeds.</li>
                <li>Use our smart tags: recent products, on-sale products, top sales in the last 30 days. Create Product Sets with them.</li>
            </ul>
        </div>
    </div>

<form id="contact-filter" method="post">
	<!-- For plugins, we also need to ensure that the form posts back to our current page -->
	<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
	<?php //$myListTable->search_box('search', 'search_id'); ?>
	<!-- Now we can render the completed list table -->
	<?php $myListTable->display() ?>
</form>
<div class="wpwoof-box">
    <form method="post" action="#" id="iDwpwoofGLS">
        <h3>Global Settings:</h3>
        <table class="form-table manage_global_settings_block">
            <tr>
                <th>Regenerate active feeds:</th>
                <td>
                    <?php $current_interval = $woocommerce_wpwoof_common->getInterval();  ?>
                    <select name="wpwoof_schedule" id="wpwoof_schedule" onchange="jQuery.fn.saveWPWoofParam({'action':'set_wpwoof_shedule','wpwoof_schedule':this.value});">
                        <?php 

                        $intervals = array(
                            /*
                            '604800'    => '1 Week',
                            '86400'     => '24 Hours',
                            '43200'     => '12 Hours',
                            '21600'     => '6 Hours',
                            '3600'      => '1 Hour',
                            '900'       => '15 Minutes',
                            '300'       => '5 Minutes',
                            */
                            '0'         => 'Never',
                            '3600'      => 'Hourly',
                            '86400'     => 'Daily',
                            '43200'     => 'Twice daily',
                            '604800'    => 'Weekly'
                        );
                        foreach($intervals as $interval => $interval_name) {
                            ?><option <?php
                                if($interval==$current_interval OR !$current_interval AND !$interval ) {
                                    echo " selected ";
                                } ?> value="<?php
                                                echo $interval;
                                            ?>"><?php echo $interval_name;
                            ?></option><?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Global Google Taxonomy:</th>
                <td>
                        <?php
                        $data = $woocommerce_wpwoof_common->getGlobalGoogleCategory();
                        ?>
                        <input class="wpwoof_google_category_g_name" type="hidden" name="feed_google_category" value="<?php echo $data['name']; ?>" />
                        <input type="text"   name="wpwoof_google_category" onchange="storeTaxonomyParams(this);" class="wpwoof_google_category_g" value="" style='display:none;' />
                        <?php
                        $taxSrc = admin_url('admin-ajax.php');
                        $taxSrc = add_query_arg( array( 'action'=>'wpwoofgtaxonmy'), $taxSrc);
                        ?>
                        <script>
                            var WPWOOFpreselect =  '<?php echo $data['name'] ?>';
                            jQuery(function($) {
                                loadTaxomomy(".wpwoof_google_category_g", function(){
                                    var sNames = jQuery('.wpwoof_google_category_g_name').val();
                                    if( WPWOOFpreselect != sNames ) {
                                        jQuery.fn.saveWPWoofParam({
                                            'action': 'set_wpwoof_category',
                                            'wpwoof_feed_google_category': sNames,
                                        }, function () {
                                            WPWOOFpreselect = sNames;
                                        });
                                    }
                                });
                            });
                        </script>
                    </td>
            </tr>
            <tr>
                <td colspan="3"><hr class="wpwoof-break" /></td>
            </tr>
            <tr>
                <th>Brand:</th><td><b>The plugin will fill brand in this order:</b></td>
            </tr>

            <tr><td></td>
                <td>
					<p>Custom Brand. The plugin adds a dedicated Brand field to every product.</p><br>
<?php                  ////////////////////////////////////////////////////////////////// Brand BLOCk  //////////////////////////////////////////////////////////////////////////////////////
     $wpwoof_values    = $woocommerce_wpwoof_common->getGlobalData();
     $attributes       = wpwoof_get_all_attributes();
    $all_fields = wpwoof_get_all_fields();
    $meta_keys = wpwoof_get_product_fields();
    $meta_keys_sort = wpwoof_get_product_fields_sort();
    $oFeed = new FeedFBGooglePro($meta_keys, $meta_keys_sort, $attributes);
//     trace($wpwoof_values,1);
?>

    <p class="p_inline_block stl-facebook stl-google" style="display: inline-block;">This value: </p>
    <select  onchange="storeWpWoofdata();" name="brand[value]"
             class="stl-facebook stl-google wpwoof_mapping wpwoof_mapping_option"  style="display: inline-block;" ><?php
        $html = '';
        $html .= '<optgroup label="">';
        $html .= '<option value="">select</option>';
        $html .= '</optgroup>';


        $html .= '<optgroup label="Global Product Attributes">';
        foreach ($attributes['global'] as $key => $value) {
            if ($key=='product_visibility') continue; 
            $html .= '<option value="wpwoofattr_' . $key . '" ' . (isset($wpwoof_values['brand']['value']) ? selected('wpwoofattr_' . $key, $wpwoof_values['brand']['value'], false) : '') . ' >' . $value . '</option>';
        }
        $html .= '</optgroup>';
        if(isset($attributes['pa']) and count($attributes['pa'])) {
            $html .= '<optgroup label="Product Attributes">';
            foreach ($attributes['pa'] as $key => $value) {
                $html .= '<option value="wpwoofattr_' . $key . '" ' . (isset($wpwoof_values['brand']['value']) ? selected('wpwoofattr_' . $key, $wpwoof_values['brand']['value'], false) : '') . ' >' . $value . '</option>';
            }
            $html .= '</optgroup>';
        }
        if(isset($attributes['meta']) and count($attributes['meta'])) {
            $html .= '<optgroup label="Custom Fields">';
            foreach ($attributes['meta'] as $key => $value) {
                $html .= '<option value="wpwoofattr_' . $value . '" ' . (isset($wpwoof_values['brand']['value']) ? selected('wpwoofattr_' . $value, $wpwoof_values['brand']['value'], false) : '') . ' >' . $value . '</option>';
            }
            $html .= '</optgroup>';
        }

        echo $html;
        ?></select><br><br>
        <script>jQuery("select[name='brand[value]']").fastselect();</script>

                <?php
                if ( is_plugin_active( WPWOOF_BRAND_YWBA ) ){
                ?>  <label class="stl-facebook stl-google" >
                            <input onchange="storeWpWoofdata();" name="brand[WPWOOF_BRAND_YWBA]" value="1" type="checkbox" <?php
                            if( !empty($wpwoof_values['brand']['WPWOOF_BRAND_YWBA']) || !isset($wpwoof_values['brand']['WPWOOF_BRAND_YWBA']) ) echo ' checked';
                            ?> />  YITH WooCommerce Brands Add-on plugin detected, use it when possible
                        <br><br></label>
                    <?php
                }
                if ( is_plugin_active( WPWOOF_BRAND_PEWB ) ) {
                    ?> <label class="stl-facebook stl-google" >
                        <input onchange="storeWpWoofdata();" type="checkbox" name="brand[WPWOOF_BRAND_PEWB]" value="1" <?php
                        if( !empty($wpwoof_values['brand']['WPWOOF_BRAND_PEWB']) || !isset($wpwoof_values['brand']['WPWOOF_BRAND_PEWB']) ) echo ' checked';
                        ?> /> Perfect WooCommerce Brands. Use it when possible
                        <br><br></label>
                    <?php
                }
                if ( is_plugin_active( WPWOOF_BRAND_PRWB ) ) {
                    ?> <label class="stl-facebook stl-google">
                        <input onchange="storeWpWoofdata();" type="checkbox" name="brand[WPWOOF_BRAND_PRWB]" value="1" <?php
                        if( !empty($wpwoof_values['brand']['WPWOOF_BRAND_PRWB']) || !isset($wpwoof_values['brand']['WPWOOF_BRAND_PRWB']) ) echo ' checked';
                        ?> /> Premmerce WooCommerce Brands. Use it when possible
                        <br><br></label>
                    <?php
                }
                if ( is_plugin_active( WPWOOF_BRAND_PBFW ) ) {
                    ?> <label class="stl-facebook stl-google" >
                        <input onchange="storeWpWoofdata();" type="checkbox" name="brand[WPWOOF_BRAND_PBFW]" value="1" <?php
                        if( !empty($wpwoof_values['brand']['WPWOOF_BRAND_PBFW']) || !isset($wpwoof_values['brand']['WPWOOF_BRAND_PBFW']) ) echo ' checked';
                        ?> /> Product Brands For WooCommerce. Use it when possible
                        <br><br></label>
                    <?php
                }
                ?>
                 <label class="stl-facebook stl-google" >
                        <input name="brand[autodetect]" type="hidden" value="0" />
                        <input onclick="storeWpWoofdata();" name="brand[autodetect]" type="checkbox" value="1" <?php
                        if( !empty($wpwoof_values['brand']['autodetect']) || !isset($wpwoof_values['brand']['autodetect']) ) echo ' checked';
                        ?>/> Possible "brand" field autodetected. Use it when possible
                     <br><br></label>

                    <p class="p_inline_block stl-facebook stl-google">Use this when brand is missing: </p>
                    <input onchange="storeWpWoofdata();" class="stl-facebook stl-google" name="brand[define]" type="text" value="<?php
                       echo !empty($wpwoof_values['brand']['define']) ? $wpwoof_values['brand']['define'] : get_bloginfo( 'name' ) ;  ?>"/>
<?php   ////////////////////////////////////////////////////////////////// END Brand BLOCk  ////////////////////////////////////////////////////////////////////////////////////// ?>
                </td>
                <td style="padding-bottom: 100px;"><?= isset($all_fields['notoutput']['brand'])?$oFeed->getHelpLinks($all_fields['notoutput']['brand']):''?></td>
            </tr>
            <tr>
                <td colspan="3"><hr class="wpwoof-break" /></td>
            </tr>
            <tr>
                <th>GTIN:</th>
				<td><b>The plugin will fill GTIN in this order:</b></td>
            </tr>
            <tr>
                <th></th>
				<td>
					<p>Custom GTIN. The plugin adds a dedicated GTIN field.</p><br>
					<p style="display: inline-block">This value:</p>
                <?php $value = isset($wpwoof_values['extra']['gtin']['value'])?$wpwoof_values['extra']['gtin']:array('value'=>'','custom_value'=>'');
                echo $oFeed->renderExtraFieldsForMapping('gtin',$value );
                ?>
                    <input type="text" name="extra[gtin][custom_value]" placeholder="Custom value" value="<?=$value['custom_value']?>" class="catalog_pro_dashboard_input" style="display: <?=$value['value']=='custom_value' ?'inline' : "none"?>;">
                </td>
					<td style="vertical-align: bottom;padding-bottom: 23px;width: 100px;"><?= isset($all_fields['dashboardRequired']['gtin'])?$oFeed->getHelpLinks($all_fields['dashboardRequired']['gtin']):''?></td>
            </tr>
            <tr>
                <td colspan="3"><hr class="wpwoof-break" /></td>
            </tr>
            <tr>
                <th>MPN:</th>
				<td>
					<b>The plugin will fill MPN in this order:</b>
                </td>
            </tr>
            <tr>
                <td></td>
				<td>
					<p>Custom MPN. The plugin adds a dedicated MPN field.</p><br>
					<p style="display: inline-block">This value:</p>
                    <?php $value = isset($wpwoof_values['extra']['mpn']['value'])?$wpwoof_values['extra']['mpn']:array('value'=>'','custom_value'=>'');
                    echo $oFeed->renderExtraFieldsForMapping('mpn',$value);?>
                    <input type="text" name="extra[mpn][custom_value]" placeholder="Custom value" value="<?=$value['custom_value']?>" class="catalog_pro_dashboard_input" style="display: <?=$value['value']=='custom_value' ?'inline' : "none"?>;">
                </td>
                <td style="vertical-align: bottom;padding-bottom: 23px;"><?= isset($all_fields['dashboardRequired']['mpn'])?$oFeed->getHelpLinks($all_fields['dashboardRequired']['mpn']):''?></td>
            </tr>
            <tr>
                <td colspan="3"><hr class="wpwoof-break" /></td>
            </tr>
            <tr>
                <?php $val = isset($wpwoof_values['extra']['identifier_exists']['custom_value'])? $wpwoof_values['extra']['identifier_exists']['custom_value']:''; ?>
                <th>Identifier exists:</th><td><p style="display: inline-block">This value:</p>
                <select name="extra[identifier_exists][custom_value]" class="wpwoof_mapping wpwoof_mapping_option">
                    <option value="true">select</option>
                    <option <?php selected($val,'yes') ?> value="yes"> Yes</option>
                    <option <?php selected($val,'no') ?> value="no">No</option>
                </select>
                    <input type="hidden" name="extra[identifier_exists][value]" value="custom_value" >
                </td>
                <td><?= isset($all_fields['dashboardRequired']['identifier_exists'])?$oFeed->getHelpLinks($all_fields['dashboardRequired']['identifier_exists']):''?></td>
            </tr>
        </table>
    </form>
    
</div>

<?php include('info-settings.php');