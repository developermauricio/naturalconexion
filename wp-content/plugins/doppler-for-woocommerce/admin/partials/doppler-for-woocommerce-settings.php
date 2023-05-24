<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.fromdoppler.com/
 * @since      1.0.0
 *
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php

 if ( ! current_user_can( 'manage_options' ) ) {
 return;
 }

 if( isset($_GET['tab']) ) {
    $active_tab = $_GET['tab'];
 }else{
    $active_tab = 'lists';
 } 

 ?>

<div class="wrap dplr_settings">

    <a href="<?php _e('https://www.fromdoppler.com/en/?utm_source=landing&utm_medium=integracion&utm_campaign=wordpress', 'doppler-form')?>" target="_blank" class="dplr-logo-header"><img src="<?php echo DOPPLER_FOR_WOOCOMMERCE_URL?>admin/img/logo-doppler.svg" alt="Doppler logo"/></a>

    <h2 class="main-title"><?php _e('Doppler for WooCommerce', 'doppler-for-woocommerce')?> <?php echo $this->get_version()?></h2> 

    <h2 class="nav-tab-wrapper">
        <a href="?page=doppler_woocommerce_menu&tab=lists" class="nav-tab <?php echo $active_tab == 'lists' ? 'nav-tab-active' : ''; ?>"><?php _e('Lists to synchronize', 'doppler-for-woocommerce')?></a>
        <a href="?page=doppler_woocommerce_menu&tab=fields" class="nav-tab <?php echo $active_tab == 'fields' ? 'nav-tab-active' : ''; ?>"><?php _e('Fields Mapping', 'doppler-for-woocommerce')?></a>
    </h2>

    <h1 class="screen-reader-text"></h1>

    <?php

    switch($active_tab){

        case 'fields':
            if( isset($_POST['dplrwoo_mapping']) && is_array($_POST['dplrwoo_mapping']) && current_user_can('manage_options') && check_admin_referer('map-fields') ){
                update_option( 'dplrwoo_mapping', $this->sanitize_text_array($_POST['dplrwoo_mapping']) );
                $this->set_success_message(__('Fields mapped succesfully', 'doppler-for-woocommerce'));
                $this->reset_buyers_and_contacts_last_synch();
            }
            $wc_fields = $this->get_checkout_fields();
            $fields_resource = $this->doppler_service->getResource('fields');
            $dplr_fields = $fields_resource->getAllFields();
            $dplr_fields = isset($dplr_fields->items) ? $dplr_fields->items : [];
            $maps = get_option('dplrwoo_mapping');
            require_once('mapping.php');
        break;

        default:
            if( isset($_POST['dplr_subscribers_list']) && $this->validate_subscribers_list($_POST['dplr_subscribers_list']) && current_user_can('manage_options') && check_admin_referer('map-lists') ){

                $subscribers_lists = $this->sanitize_subscribers_list($_POST['dplr_subscribers_list']);

                update_option( 'dplr_subscribers_list', $subscribers_lists);
                $this->set_success_message(__('Subscribers lists saved succesfully', 'doppler-for-woocommerce'));
                
                $this->reset_buyers_and_contacts_last_synch();
            } else {
                $subscribers_lists = get_option('dplr_subscribers_list');
            }
            
            $lists = $this->get_alpha_lists();            
            
            //Check if saved buyers & contact Lists still exists, unset them if not.
            $has_to_update = false;

            if(!empty($subscribers_lists['buyers']) && !$this->list_exists($subscribers_lists['buyers'], $lists)){
                $has_to_update = true;
                $subscribers_lists['buyers'] = '0';
            }
        
            if(!empty($subscribers_lists['contacts']) && !$this->list_exists($subscribers_lists['contacts'], $lists)){
                $subscribers_lists['contacts'] = '0';
                $has_to_update = true;
            }
                
            if($has_to_update) update_option('dplr_subscribers_list', $subscribers_lists);
            
            require_once('lists.php');
        
        break;
    
    }
    ?>
    
</div>