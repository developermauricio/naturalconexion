<?php
defined('ABSPATH') or exit;

?>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Show first X results in autocomplete',
            'advanced-dynamic-pricing-for-woocommerce') ?></th>
    <td class="forminp forminp-checkbox">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Show first X results in autocomplete',
                        'advanced-dynamic-pricing-for-woocommerce') ?></span></legend>
            <label for="limit_results_in_autocomplete">
                <input value="<?php echo $options['limit_results_in_autocomplete'] ?>"
                       name="limit_results_in_autocomplete" id="limit_results_in_autocomplete" placeholder="25"
                       type="number" min="1">
            </label>
        </fieldset>
    </td>
</tr>
