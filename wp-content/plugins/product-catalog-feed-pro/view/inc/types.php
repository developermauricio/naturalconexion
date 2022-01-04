<table class="form-table wpwoof-addfeed-top">
    <tr class="addfeed-top-field">
        <th class="addfeed-top-label addfeed-bigger">Feed's Name:</th>
        <td class="addfeed-top-value">
            <input type="text" id="idFeedName" name="feed_name" value="<?php echo isset($wpwoof_values['feed_name']) ? $wpwoof_values['feed_name'] : ''; ?>" />
            <?php if( !empty($wpwoofeed_oldname) ) { ?>
                <input type="hidden" name="old_feed_name" value="<?php echo $wpwoofeed_oldname; ?>" style="display:none" />
            <?php } ?>
        </td>
    </tr>
    <tr class="addfeed-top-field">
        <th class="addfeed-top-label addfeed-bigger">Feed's Type:</th>
        <td class="addfeed-top-value">
            <select id="ID-feed_type" name="feed_type" onchange="jQuery.fn.toggleFeedField(this.value);">
                <option <?php if(isset($wpwoof_values['feed_type'])) { selected( "facebook", $wpwoof_values['feed_type'], true); } ?> value="facebook">Facebook Product Catalog</option>
                <option <?php if(isset($wpwoof_values['feed_type'])) { selected( "google", $wpwoof_values['feed_type'], true); } ?> value="google">Google Merchant</option>
                <option <?php if(isset($wpwoof_values['feed_type'])) { selected( "adsensecustom", $wpwoof_values['feed_type'], true); } ?> value="adsensecustom">Google Adwords Remarketing Custom</option>
                <option <?php if(isset($wpwoof_values['feed_type'])) { selected( "pinterest", $wpwoof_values['feed_type'], true); } ?> value="pinterest">Pinterest</option>
            </select>
        </td>
    </tr>
</table>