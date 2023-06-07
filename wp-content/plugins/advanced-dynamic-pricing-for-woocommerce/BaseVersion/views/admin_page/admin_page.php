<?php

use ADP\BaseVersion\Includes\AdminExtensions\AdminPage;
use ADP\BaseVersion\Includes\AdminExtensions\AdminPage\AdminTabInterface;

defined('ABSPATH') or exit;

/**
 * @var $this AdminPage
 * @var $tabs AdminTabInterface[]
 * @var $current_tab AdminTabInterface
 */
?>

<div class="wrap woocommerce">

    <h2 class="wcp_tabs_container nav-tab-wrapper">
        <?php foreach ($tabs as $tab_key => $tab_handler): ?>
            <a class="nav-tab <?php echo($tab_key === $current_tab::getKey() ? 'nav-tab-active' : ''); ?>"
               href="admin.php?page=wdp_settings&tab=<?php echo $tab_key; ?>"><?php echo $tab_handler::getTitle(); ?></a>
        <?php endforeach; ?>
    </h2>

    <div class="wdp_settings ui-page-theme-a">
        <div class="wdp_settings_container">
            <?php
            $this->renderCurrentTab();
            ?>
        </div>
    </div>

</div>
