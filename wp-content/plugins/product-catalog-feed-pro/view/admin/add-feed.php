<?php
require_once dirname(__FILE__).'/../../inc/common.php';
require_once dirname(__FILE__).'/../../inc/countries.php';
require_once dirname(__FILE__).'/../../inc/feedfbgooglepro.php';



?>
<div class="wpwoof-box">
    <?php
    //trace($wpwoof_values);
    /* output store/back buttons */

    $WpWoofTopSave = "wpwoof-addfeed-button-top";
    include dirname(__FILE__).'/../inc/store_action.php';

    $WpWoofTopSave = "";



    $all_fields = wpwoof_get_all_fields();

    $meta_keys        = wpwoof_get_product_fields();
    $meta_keys_sort   = wpwoof_get_product_fields_sort();
    $attributes       = wpwoof_get_all_attributes();

    $oFeedFBGooglePro = new FeedFBGooglePro($meta_keys, $meta_keys_sort, $attributes);


    /* Output setting fields */
    //$oFeedFBGooglePro->renderFields($all_fields['setting'], $meta_keys, $meta_keys_sort, $attributes, $wpwoof_values);
    /* output settings part */
    include dirname(__FILE__).'/../inc/types.php';
    include dirname(__FILE__).'/../inc/settings.php';

    /* Output require fields */
    $oFeedFBGooglePro->renderFields($all_fields['required'], $meta_keys, $meta_keys_sort, $attributes, $wpwoof_values);

    /* Output optinal fields */
    $oFeedFBGooglePro->renderFields($all_fields['extra'], $meta_keys, $meta_keys_sort, $attributes, $wpwoof_values);

    ?>
    <script>jQuery("select[name*='custom_label_'], select[name='field_mapping[gtin][value]'], select[name='field_mapping[mpn][value]']").fastselect();</script>
    <br/><br/><hr class="wpwoof-break" /><br/><br/>
    <?php include dirname(__FILE__).'/../inc/store_action.php'; /* output store/back buttons */ ?>

    <?php if( isset($_REQUEST['edit']) && !empty($_REQUEST['edit']) ) { ?>
            <input type="hidden" name="edit_feed" value="<?php echo $_REQUEST['edit']; ?>">
        <?php } ?>
    </div>
