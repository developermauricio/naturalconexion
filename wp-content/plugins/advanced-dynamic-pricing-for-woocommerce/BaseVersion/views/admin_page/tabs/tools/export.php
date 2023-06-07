<?php
defined('ABSPATH') or exit;

/**
 * @var $groups
 */
$items = array();
foreach ($groups as $group) {
    foreach ($group['items'] as $key => $item) {
        $items[$key] = $item;
    }
}

?>

<div>
    <div>
        <p>
            <label for="wdp-export-select">
                <?php _e('Copy these settings and use it to migrate plugin to another WordPress install.',
                    'advanced-dynamic-pricing-for-woocommerce') ?>
            </label>
            <select id="wdp-export-select">
                <?php foreach ($groups as $group_key => $group): ?>
                    <optgroup label="<?php echo $group['label']; ?>">
                        <?php foreach ($group['items'] as $key => $item): ?>
                            <option
                                value="<?php echo $key ?>" <?php selected($group_key === 'rules' and $key === 'all') ?> ><?php echo $item['label'] ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <textarea id="wdp-export-data" name="wdp-export-data" class="large-text" rows="15"></textarea>
        </p>
    </div>
</div>
