<?php
defined('ABSPATH') or exit;

/**
 * @var $title string
 * @var $amount_saved float
 */
?>
<li class="woocommerce-mini-cart__total total adp-discount" style="text-align: center">
    <strong><?php echo $title; ?>:</strong>
    <?php echo wc_price($amount_saved); ?>
</li>
