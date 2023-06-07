<?php
defined('ABSPATH') or exit;

/**
 * @var array $tabs
 */

?>

<div id="wdp-report-window">
    <div id="wdp-report-control-bar">
        <div id="wdp-report-resizer"></div>

        <div id="wdp-report-main-tab-selector" class="tab-links-list">

            <div class="tab-link selected" data-tab-id="cart"><?php echo __('Cart',
                    'advanced-dynamic-pricing-for-woocommerce'); ?></div>
            <div class="tab-link" data-tab-id="products"><?php echo __('Products',
                    'advanced-dynamic-pricing-for-woocommerce'); ?></div>
            <div class="tab-link" data-tab-id="rules"><?php echo __('Rules',
                    'advanced-dynamic-pricing-for-woocommerce'); ?></div>
            <div class="tab-link" data-tab-id="reports"><?php echo __('Get system report',
                    'advanced-dynamic-pricing-for-woocommerce'); ?></div>

            <div id="wdp-report-resizer"></div>
        </div>

        <div id="progress_div" style="margin-right: 10px;">
            <img class="spinner_img" alt="spinner">
        </div>

        <div id="wdp-report-goto-debug-settings" class="tab-link">
            <?php
            echo __('Only admins see this panel. ', 'advanced-dynamic-pricing-for-woocommerce');
            echo sprintf(
                wp_kses(
                        '<a href="%s" target="_blank">' .__('How to hide it.', 'advanced-dynamic-pricing-for-woocommerce') .'</a>',
                    array(
                        'a' => array(
                            'href' => array(),
                            'target' => array(),
                        ),
                    )
                ),
                esc_url(get_admin_url() . 'admin.php?page=wdp_settings&tab=options#section=debug')
            );
            ?>
        </div>

        <div id="wdp-report-window-refresh">
            <button>
                <?php echo __('Refresh', 'advanced-dynamic-pricing-for-woocommerce'); ?>
            </button>
        </div>

        <div id="wdp-report-window-close">
            <span class="dashicons dashicons-no-alt"></span>
        </div>
    </div>


    <div id="wdp-report-tab-window"></div>

</div>
