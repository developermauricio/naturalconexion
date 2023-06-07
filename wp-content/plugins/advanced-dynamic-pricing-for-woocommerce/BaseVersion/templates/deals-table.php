<?php
defined('ABSPATH') or exit;

/** @var array $data */
?>
<div class="wdp_deals_table_caption"><?php _e('Active deals', 'advanced-dynamic-pricing-for-woocommerce') ?></div>
<table class="wdp_deals_table">
    <tbody>
    <tr>
        <td><?php echo $data['title'] ?></td>
    </tr>
    <?php foreach ($data['adjustments'] as $adjustment): ?>
        <tr>
            <td><?php echo esc_html($adjustment) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
