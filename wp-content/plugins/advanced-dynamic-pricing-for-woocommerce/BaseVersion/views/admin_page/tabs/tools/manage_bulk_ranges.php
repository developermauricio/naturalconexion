<?php
/**
 * @var string $security
 * @var string $security_param
 */

?>

<div>
    <h3 class="tools-h3-title"><?php _e( 'Export rules with non-empty bulk ranges as CSV', 'advanced-dynamic-pricing-for-woocommerce' ); ?></h3>
    <button
        id="wdp-export-bulk-ranges"
        name="wdp-export-bulk-ranges"
        class="button button-primary wdp-export-bulk-ranges"
        type="submit"
    >
        <?php _e( 'Export into CSV', 'advanced-dynamic-pricing-for-woocommerce' ); ?>
    </button>
    <form method="post" enctype="multipart/form-data" class="wdp-import-tools-form">
        <input type="hidden" name="<?php echo $security_param; ?>" value="<?php echo $security; ?>"/>
        <h3 class="tools-h3-title"><?php _e( 'Re-import CSV to update ranges for EXISTING rules', 'advanced-dynamic-pricing-for-woocommerce' ); ?></h3>
        <input type="file" name="rules-to-import" id="rules-to-import" class="button"/>
        <button
            id="wdp-import-bulk-ranges"
            name="wdp-import-bulk-ranges"
            class="button button-primary"
            type="submit"
            style="min-height: 36px"
        >
            <?php _e( 'Import', 'advanced-dynamic-pricing-for-woocommerce' ); ?>
        </button>
    </form>
</div>
