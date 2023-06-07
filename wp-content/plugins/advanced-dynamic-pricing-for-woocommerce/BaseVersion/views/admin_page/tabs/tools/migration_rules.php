<?php
defined('ABSPATH') or exit;

?>

<div>
    <?php if (!adp_context()->getOption('support_persistence_rules')): ?>
        <h3 class="tools-h3-title notice-warn">
            <a href="<?php echo admin_url('admin.php?page=wdp_settings&tab=options#section=rules') ?>" target="_blank">
                <?php _e('You need to enable the "Support Product only rules" option', 'advanced-dynamic-pricing-for-woocommerce') ?>
            </a>
        </h3>
        <fieldset disabled>
    <?php endif; ?>
    <h3 class="tools-h3-title"><?php _e( 'Migrate "Common" rule type to "Product only" rule type', 'advanced-dynamic-pricing-for-woocommerce' ); ?></h3>
    <div class="migrate-rules-div">
        <button
            id="wdp-migrate-common-to-product-only"
            name="wdp-migrate-common-to-product-only"
            class="button button-primary wdp-migrate-common-to-product-only"
            type="submit"
        >
            <?php _e( 'Migrate', 'advanced-dynamic-pricing-for-woocommerce' ); ?>
        </button>
        <label class="migration-rules-affected"></label>
    </div>
    <h3 class="tools-h3-title"><?php _e( 'Migrate "Product only" rule type to "Common" rule type', 'advanced-dynamic-pricing-for-woocommerce' ); ?></h3>
    <div class="migrate-rules-div">
        <button
            id="wdp-migrate-product-only-to-common"
            name="wdp-migrate-product-only-to-common"
            class="button button-primary wdp-migrate-product-only-to-common"
            type="submit"
        >
            <?php _e( 'Migrate', 'advanced-dynamic-pricing-for-woocommerce' ); ?>
        </button>
        <label class="migration-rules-affected"></label>
    </div>
    <?php if (!adp_context()->getOption('support_persistence_rules')): ?>
        </fieldset>
    <?php endif; ?>
</div>
