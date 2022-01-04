<div class="wpwoof-addfeed-button <?php echo ($WpWoofTopSave);  ?>" ><!--  wpwoof-addfeed-button-top -->
    <div class="wpwoof-addfeed-button-inner">
        <p class="wpwoof-action-buttons">
            <input  <?php if( !isset($_REQUEST['edit']) || empty($_REQUEST['edit']) ) echo 'style="width:60%; display: inline-block;" '; ?> type="button"  name="wpwoof-addfeed-submit" class="CLSwpwoofSubmit wpwoof-button wpwoof-button-orange1" value="<?php echo $wpwoof_add_button; ?>" />
            <?php  if(1==1 || isset($_REQUEST['edit']) && !empty($_REQUEST['edit']) ) { ?>
                <a href="<?php menu_page_url('wpwoof-settings'); ?>" class="wpwoof-button" style="width: 20%;">Back</a>
            <?php } ?>
        </p>
    </div>
</div>