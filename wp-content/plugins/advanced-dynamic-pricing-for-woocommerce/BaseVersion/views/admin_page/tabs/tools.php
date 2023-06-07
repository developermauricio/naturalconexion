<?php
defined('ABSPATH') or exit;

/**
 * @var array $groups
 * @var array $sections
 * @var array $import_data_types
 * @var string $security
 * @var string $security_param
 * @var \ADP\BaseVersion\Includes\AdminExtensions\AdminPage\Tabs\Tools $tabHandler
 */
$items = array();
foreach ($groups as $group) {
    foreach ($group['items'] as $key => $item) {
        $items[$key] = $item;
    }
}

?>
<ul class="subsubsub">
    <?php
    $last_index = "migration_rules";
    foreach ($sections as $index => $section):
        if (empty($section['templates'])) {
            continue;
        }
        $section_title = $section['title'];
        ?>
        <li>
            <a class="section_choice"
               data-section="<?php echo $index; ?>" href="#section=<?php echo $index; ?>">
                <?php echo $section_title; ?>
            </a>
            <?php echo($last_index == $index ? '' : ' | '); ?>
        </li>
    <?php endforeach; ?>
</ul><br class="clear"/>
<div>
    <input type="hidden" name="tab" value="<?php echo $tabHandler::getKey(); ?>"/>
    <?php foreach ($sections as $index => $section):
        $class = array('section', $index . '-tools-section');
        $id = $index . '_section';
        $label = $section['title'];
        ?>
        <div class="section tools-section" id="<?php echo $id; ?>">
            <div class="section-tab-header" style="display: flex; align-items: center;">
                <h2><?php echo $label; ?></h2>
                <?php if ($label === "Manage bulk ranges"): ?>
                    <a href="https://docs.algolplus.com/algol_pricing/manage-bulk-ranges/" style="margin-left: 10px; padding-top: 3px;"
                       target="_blank"><?php _e('Read short guide', 'advanced-dynamic-pricing-for-woocommerce') ?></a>
                <?php endif; ?>
            </div>

            <table class="section-tools">
                <?php
                if (isset($section['templates']) && is_array($section['templates'])) {
                    foreach ($section['templates'] as $template) {
                        $tabHandler->renderToolsTemplate($template,
                            compact('groups', 'sections', 'import_data_types', 'security', 'security_param'));
                    }
                }
                ?>
            </table>
        </div>
    <?php endforeach; ?>
</div>
<script>
    var wdp_export_items = JSON.stringify(<?php echo json_encode($items) ?>);
</script>

<?php do_action('wdp_tools_options') ?>
