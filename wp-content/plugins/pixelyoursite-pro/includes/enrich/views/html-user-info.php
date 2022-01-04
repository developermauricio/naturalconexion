<?php
namespace PixelYourSite;

$pluginName = "";
if( isWooCommerceActive() && PYS()->getOption('woo_enabled_save_data_to_user')) {
    $pluginName = "WooCommerce:";
    $totals = getWooCustomerTotals($user->ID);
} elseif (isEddActive() && PYS()->getOption('edd_enabled_save_data_to_user')) {
    $pluginName = "Easy Digital Downloads:";
    $totals = getEddCustomerTotals($user->ID);
}
if($pluginName == "") {
    return;
}
?>
    <h3><?php _e('PixelYourSite Pro'); ?></h3>
    <table class="form-table">

        <tr >
            <th><?=$pluginName?></th>
            <td></td>
        </tr>
        <tr >
            <th>Number of orders:</th>
            <td><?=$totals['orders_count']?></td>
        </tr>
        <tr >
            <th>Lifetime value:</th>
            <td><?=$totals['ltv']?></td>
        </tr>
        <tr >
            <th>Average order value:</th>
            <td><?=$totals['avg_order_value']?></td>
        </tr>

    </table>
<?php
