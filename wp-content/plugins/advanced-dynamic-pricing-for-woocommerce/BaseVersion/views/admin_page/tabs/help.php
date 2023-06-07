<?php
defined('ABSPATH') or exit;

/**
 * @var array $tilesHelpInfo
 */
?>
<div id="wp-tab-help">
    <div class="wdp-help-list-container">
        <div class="wdp-row wdp-title-wrapper">
            <h3 class="wdp-column wdp-help-title"><?php _e('Help', 'advanced-dynamic-pricing-for-woocommerce'); ?></h3>
            <div class="wdp-column">
                <a href="<?php echo esc_url('https://docs.algolplus.com/pricing-order-docs/');?>" target="_blank"><?php _e('Documentation site', 'advanced-dynamic-pricing-for-woocommerce'); ?></a>
            </div>
            <div class="wdp-column">
                <a href="<?php echo esc_url('https://docs.algolplus.com/algol_pricing/faq-common/');?>" target="_blank"><?php _e('FAQ', 'advanced-dynamic-pricing-for-woocommerce'); ?></a>
            </div>
            <div class="wdp-column">
                <a href="<?php echo esc_url('https://docs.algolplus.com/support/');?>" target="_blank"><?php _e('Support', 'advanced-dynamic-pricing-for-woocommerce'); ?></a>
            </div>
        </div>
        <div class="wdp-tiles-grid">
            <?php foreach($tilesHelpInfo as $tile): ?>
                <div class="wdp-cell">
                    <a href="<?php echo $tile['link']; ?>" class="wdp-title-help-info" target="_blank">
                        <h2 class="wdp-tile-title"><?php echo $tile['title']; ?></h2>
                        <p><?php echo $tile['description'] ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
