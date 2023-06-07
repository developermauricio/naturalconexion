<?php
defined('ABSPATH') or exit;

/**
 * @var $import_data_types
 * @var string $security
 * @var string $security_param
 */

?>

<form method="post" class="wdp-import-tools-form">
    <input type="hidden" name="<?php echo $security_param; ?>" value="<?php echo $security; ?>"/>
    <div>
        <div>
            <?php
                $importResultMsg = get_transient('import-result');
                if ($importResultMsg !== false) {
                    $msgClass = strpos($importResultMsg, 'success') !== false ? 'import-notice notice-ok' : 'import-notice notice-fail';
                    echo "<p class='$msgClass'>" . $importResultMsg . '</p>';
                    delete_transient('import-result');
                }
            ?>
            <label for="wdp-import-data">
                <?php _e('Paste text into this field to import settings into the current WordPress install.',
                    'advanced-dynamic-pricing-for-woocommerce') ?>
            </label>
            <select id="wdp-import-select" name="wdp-import-type">
                <?php foreach ($import_data_types as $type => $label): ?>
                    <option value="<?php echo $type ?>"
                        <?php if ($type == 'rules') {
                            echo ' selected';
                        } ?>><?php echo $label ?></option>
                <?php endforeach; ?>
            </select>
            <div>
                <textarea id="wdp-import-data" name="wdp-import-data" class="large-text" rows="15"></textarea>
            </div>
            <div class="wdp-import-type-options-rules wdp-import-type-options">
                <input type="hidden" name="wdp-import-data-reset-rules" value="0">
                <input type="checkbox" id="wdp-import-data-reset-rules" name="wdp-import-data-reset-rules" value="1">
                <label for="wdp-import-data-reset-rules">
                    <?php _e('Clear all rules before import', 'advanced-dynamic-pricing-for-woocommerce') ?>
                </label>
            </div>
            <?php do_action('wdp_import_tools_options') ?>
        </div>
    </div>
    <p>
        <button type="submit" id="wdp-import" name="wdp-import" class="button button-primary">
            <?php _e('Import', 'advanced-dynamic-pricing-for-woocommerce') ?></button>
    </p>
</form>
