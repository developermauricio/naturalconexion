<?php
defined('ABSPATH') or exit;

/**
 * @var $title string
 * @var $amount_saved float
 * @var $currency string
 */
?>
<tr>
    <td class="label"><?php echo $title ?>:</td>
    <td width="1%"></td>
    <td class="total">
        <?php echo wc_price($amount_saved, array('currency' => $currency)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </td>
</tr>
