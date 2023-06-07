<?php
defined('ABSPATH') or exit;

/**
 * @var boolean $hide_inactive
 * @var string $pagination Pagination HTML
 * @var string $tab current tab key
 * @var string $page current page slug
 * @var string $tabHandler current tab handler
 * @var \ADP\Settings\OptionsManager $options
 * @var string $ruleSearchQ rule search query
 * @var integer $rulesCount
 * @var integer $activeRulesCount
 * @var integer $inactiveRulesCount
 * @var string $tabUrl
 * @var string $active
 */
?>

<div id="poststuff">

    <div class="wdp-list-container" id="rules-action-controls">
        <div class="wdp-row" style="margin: 0;">
        <div class="wdp-column wdp-column-max-content wdp-row" style="flex-direction: column">
                <ul class="subsubsub" style="margin-top: auto;">
                    <li>
                        <a class="<?php echo $active === "all" ? "current" : ""; ?>" href="<?php echo add_query_arg("active", "all", $tabUrl);?>">
                            <?php _e('All', 'advanced-dynamic-pricing-for-woocommerce'); ?>
                            <span class="count"><?php echo "($rulesCount)"; ?></span>
                        </a> |
                    </li>
                    <li>
                        <a class="<?php echo $active === "1" ? "current" : ""; ?>" href="<?php echo add_query_arg("active", "1", $tabUrl);?>">
                            <?php _e('Active', 'advanced-dynamic-pricing-for-woocommerce'); ?>
                            <span class="count"><?php echo "($activeRulesCount)"; ?></span>
                        </a> |
                    </li>
                    <li>
                        <a class="<?php echo $active === "0" ? "current" : ""; ?>" href="<?php echo add_query_arg("active", "0", $tabUrl);?>">
                            <?php _e('Inactive', 'advanced-dynamic-pricing-for-woocommerce'); ?>
                            <span class="count"><?php echo "($inactiveRulesCount)"; ?></span>
                        </a>
                    </li>
                </ul>
                <button class="button add-rule wdp-addlist-item loading">
                    <?php _e('Add rule', 'advanced-dynamic-pricing-for-woocommerce'); ?>
                </button>
            </div>

            <div class="wdp-wrapper wdp-column wdp-column-max-content" style="margin-left: auto;">
                <div style="display: inline-block; width: 100%;">
                    <div id="progressBarBlock" style="padding: 0; width: 100%;">
                        <div id="progressBar"></div>
                    </div>
                </div>
                <?php if($options->getOption('support_shortcode_products_on_sale') || $options->getOption('support_shortcode_products_bogo') || $options->getOption('support_persistence_rules')): ?>
                <div class="wdp-row" style="margin: 5px 0;">
                    <div class="wdp-column">
                        <select name="recalculace_selector">
                            <?php if($options->getOption('support_persistence_rules')): ?>
                                <option value="recalculate_persistence_cache"><?php _e('Recalculate Product only rules cache', 'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                            <?php endif;
                            if($options->getOption('support_shortcode_products_on_sale')): ?>
                                <option value="rebuild_onsale_list"><?php _e('Update Onsale List', 'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                            <?php endif;
                            if($options->getOption('support_shortcode_products_bogo')): ?>
                                <option value="rebuild_bogo_list"><?php _e('Update Bogo List', 'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="wdp-column">
                        <button type="button" class="button wdp-rebuild-run"><?php _e('Run', 'advanced-dynamic-pricing-for-woocommerce') ?></button>
                    </div>
                </div>
                <?php endif; ?>
                <form id="search-rules" method="get">
                    <input type="hidden" name="page" value="<?php echo $page; ?>">
                    <input type="hidden" name="tab" value="<?php echo $tab; ?>">
                    <input type="hidden" name="action" value="search_rules">
                    <input type="search" name="q" value="<?php echo $ruleSearchQ; ?>">
                    <button type="submit" class="button wdp-btn-rule-action-controls"><?php _e('Search rules', 'advanced-dynamic-pricing-for-woocommerce') ?></button>
                </form>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['product']) && isset($_GET['action_rules'])): ?>
        <div>
            <span class="tag-show-rules-for-product"><?php printf(__('Only rules for product "%s" are shown',
                    'advanced-dynamic-pricing-for-woocommerce'),
                    \ADP\BaseVersion\Includes\Helpers\Helpers::getProductTitle($_GET['product'])); ?></span>
        </div>
    <?php endif; ?>

    <div style="clear: both;">
        <div style="float: left; margin: 5px 0;">
            <input type="checkbox" id="bulk-action-select-all">
        </div>

        <form id="bulk-action" method="post" style="display: inline-block; float: left; margin-right: 10px; ">
            <input type="hidden" name="page" value="<?php echo $page; ?>"/>
            <input type="hidden" name="tab" value="<?php echo $tab; ?>"/>
            <select id="bulk-action-selector" name="bulk_action" style="width: 131px;">
                <option value=""><?php _e('Bulk actions', 'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                <option value="enable"><?php _e('Activate', 'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                <option value="disable"><?php _e('Deactivate', 'advanced-dynamic-pricing-for-woocommerce'); ?></option>
                <option value="delete"><?php _e('Delete', 'advanced-dynamic-pricing-for-woocommerce'); ?></option>
            </select>
            <button type="submit" class="button"><?php _e('Apply', 'advanced-dynamic-pricing-for-woocommerce') ?></button>
        </form>

        <form id="rules-filter" method="get" style="float: right; margin: 5px">
            <input type="hidden" name="page" value="<?php echo $page; ?>"/>
            <input type="hidden" name="tab" value="<?php echo $tab; ?>"/>
            <?php echo $pagination; ?>
        </form>
    </div>

    <div id="post-body" class="metabox-holder">
        <div id="postbox-container-2" class="postbox-container">
            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                <div id="rules-container"
                     class="sortables-container group-container loading wdp-list-container"></div>
                <p id="no-rules"
                   class="wdp-no-list-items loading"><?php _e('No rules defined',
                        'advanced-dynamic-pricing-for-woocommerce') ?></p>
                <p>
                    <button class="button add-rule wdp-add-list-item loading">
                        <?php _e('Add rule', 'advanced-dynamic-pricing-for-woocommerce') ?></button>
                </p>
                <div style="float: right; margin: 5px">
                    <?php echo $pagination; ?>
                </div>
                <div id="progress_div" style="">
                    <div id="container"><span class="spinner is-active" style="float:none;"></span></div>
                </div>

            </div>
        </div>

        <div style="clear: both;"></div>
    </div>
</div>

<?php include 'rules/templates.php'; ?>
