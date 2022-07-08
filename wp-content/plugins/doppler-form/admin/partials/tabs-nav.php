<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<h2 class="nav-tab-wrapper">
    <a href="?page=doppler_forms_main&tab=forms" class="nav-tab <?php echo $active_tab == 'forms' ? 'nav-tab-active' : ''; ?>"><?php _e('Forms', 'doppler-form')?></a>
    <a href="?page=doppler_forms_main&tab=lists" class="nav-tab <?php echo $active_tab == 'lists' ? 'nav-tab-active' : ''; ?>"><?php _e('Lists Managment', 'doppler-form')?></a>
    <a href="?page=doppler_forms_main&tab=data-hub" class="nav-tab <?php echo $active_tab == 'data-hub' ? 'nav-tab-active' : ''; ?>"><?php _e('On-Site Tracking', 'doppler-form')?></a>
</h2>