<?php

$payment      = new EDD_Payment( $payment_id );

$meta = $payment->get_meta();

?>
<style>
    table.pys_order_meta {
        width: 100%;text-align:left
    }
    table.pys_order_meta td.border span {
        border-top: 1px solid #f1f1f1;
        display: block;
    }
    table.pys_order_meta th,
    table.pys_order_meta td {
        padding:10px
    }
</style>
<div id="edd-payment-notes" class="postbox">
    <h3 class="hndle"><span>PixelYourSite Pro</span></h3>
    <div class="inside">
        <?php if(isset($meta['pys_enrich_data'])) :
            $data = $meta['pys_enrich_data'];
            ?>
            <table class="pys_order_meta">
                <tr >
                    <th>Landing Page:</th>
                    <td><a href="<?=$data['pys_landing']?>" target="_blank" ><?=$data['pys_landing']?></a></td>
                </tr>
                <tr>
                    <th>Traffic source:</th>
                    <td><?=$data['pys_source']?></td>
                </tr>
                <?php
                $utms = explode("|",$data['pys_utm']);
                foreach($utms as $utm) {
                    $item = explode(":",$utm);
                    $name = $item[0];
                    $value = $item[1] == "undefined" ? "No ".$name." detected for this order" : $item[1];
                    ?>
                    <tr>
                        <th><?=$name?>:</th>
                        <td><?=$value?></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td colspan="2" class="border"><span></span></td>
                </tr>
                <?php
                $userTime = explode("|",$data['pys_browser_time']);
                ?>
                <tr >
                    <th>Client's browser time</th>
                    <td></td>
                </tr>
                <tr >
                    <th>Hour:</th>
                    <td><?=$userTime[0]?></td>
                </tr>
                <tr >
                    <th>Day:</th>
                    <td><?=$userTime[1]?></td>
                </tr>
                <tr >
                    <th>Month:</th>
                    <td><?=$userTime[2]?></td>
                </tr>

                <tr>
                    <td colspan="2" class="border"<td><span></span></td>
                </tr>
                <?php

                ?>
                <tr >
                    <th>Number of orders:</th>
                    <td><?=$data['orders_count']?></td>
                </tr>
                <tr >
                    <th>Lifetime value:</th>
                    <td><?=$data['ltv']?></td>
                </tr>
                <tr >
                    <th>Average order value:</th>
                    <td><?=$data['avg_order_value']?></td>
                </tr>

            </table>

        <?php else: ?>
            <h2>No data</h2>
        <?php endif; ?>
    </div>
</div>
