<?php
defined('ABSPATH') or exit;

$bounceBackDownloadReportUrl = \ADP\BaseVersion\Includes\Debug\AdminBounceBack::getBounceBackReportDownloadUrl();
$bounceBackReportUrl         = \ADP\BaseVersion\Includes\Debug\AdminBounceBack::generateBounceBackUrl();
?>

<div>
    <?php if ($bounceBackDownloadReportUrl): ?>
        <iframe src="<?php echo $bounceBackDownloadReportUrl; ?>" width=0 height=0 style='display:none'></iframe>
    <?php endif; ?>
    <div id="wdp_reporter_tab_reports_buttons_template" style="margin-top: 15px;">
        <a class="button" href="<?php echo $bounceBackReportUrl; ?>"
           id="export_all"><?php echo __('Get system report', 'advanced-dynamic-pricing-for-woocommerce'); ?></a>
    </div>
</div>
