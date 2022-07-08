<?php
if ( ! current_user_can( 'manage_options' ) ) {
    return;
}
?>

<div class="dplr-tab-content">

    <?php $this->display_success_message() ?>
    <?php $this->display_error_message() ?>

    <p class="size-medium">
        <?php _e('Obtain the Tracking Code from the Doppler App and paste it here in order to monitor the actions that the visitors perform in it. Any doubts? Press <a href="https://help.fromdoppler.com/en/create-onsite-tracking-automation" class="green-link">HELP</a>','doppler-for-woocommerce') ?>
    </p>

    <form id="dplrwoo-form-hub" action="" method="post" class="w-100 mw-7">

        <?php wp_nonce_field( 'use-hub' );?>
        <p>
            <textarea name="dplr_hub_script" class="w-100" rows="3" placeholder="<?php _e('Paste tracking code here.','doppler-for-woocommerce')?>"><?php echo stripslashes(html_entity_decode($dplr_hub_script)) ?></textarea>
        </p>
        <button id="dplrwoo-hub-btn" class="dp-button button-medium primary-green">
            <?php _e('Save', 'doppler-for-woocommerce') ?>
        </button>

    </form>

</div>