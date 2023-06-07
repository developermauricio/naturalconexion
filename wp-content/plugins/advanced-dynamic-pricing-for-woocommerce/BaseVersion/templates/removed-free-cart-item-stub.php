<?php

defined('ABSPATH') or exit;

/**
 * @var string $hash
 * @var string $cartUrl
 * @var float $qty
 *
 */

?>
    <tr>
        <td colspan="1" class="adp-free-cart-item-removed-stub-plus" style="vertical-align: middle">+</td>
        <td colspan="<?php echo ( ! isset($options["dont_show_restore_link"]) || ! $options["dont_show_restore_link"]) ? "3" : "5"; ?>"
            class="adp-free-cart-item-removed-stub-text" style="vertical-align: middle">
            <?php
            echo sprintf(__('You have deleted %d free products from the cart.',
                'advanced-dynamic-pricing-for-woocommerce'), $qty);
            ?>
        </td>
        <?php
        if ( ! isset($options["dont_show_restore_link"]) || ! $options["dont_show_restore_link"]) {
            ?>
            <td colspan="2" class="adp-free-cart-item-removed-stub-url">
                <a href="<?php echo $cartUrl; ?>">
                    <?php _e("Restore", 'advanced-dynamic-pricing-for-woocommerce'); ?>
                </a>
            </td>
            <?php
        }
        ?>
    </tr>
<?php
