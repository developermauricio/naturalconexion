<?php
defined('ABSPATH') or exit;

/**
 * @var string $header_html
 * @var array $table_header
 * @var array $rows
 * @var string $footer_html
 */


?>
<div class='clear'></div>

<div class="bulk_table">
    <div class="wdp_pricing_table_caption"><?php echo $header_html; ?></div>
    <table class="wdp_pricing_table">
        <thead>
        <tr>
            <?php foreach ($table_header as $label): ?>
                <td><?php echo $label ?></td>
            <?php endforeach; ?>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($row as $html): ?>
                    <td><?php echo $html ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <span class="wdp_pricing_table_footer"><?php echo $footer_html; ?></span>
</div>
