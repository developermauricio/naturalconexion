<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Includes
 */
class WooEmailCustomizerCommon
{
    /**
     * Get additional css
     * */
    public static function getAdditionalCSS(){
        $additionalStyles = '';
        $css = self::getCSSFromSettings();
        global $wpdb;
        $mailTemplates = $wpdb->get_col( "SELECT post_content FROM $wpdb->posts WHERE post_type = 'woo_mb_template'" );
        foreach ($mailTemplates as $mailTemplate){
            if($mailTemplate != '' && trim($mailTemplate) != ''){
                $templateContent = json_decode($mailTemplate);
                if(isset($templateContent->additionalstyles) && $templateContent->additionalstyles != ''){
                    $additionalStyles .= $templateContent->additionalstyles;
                }
            }
        }
        return $additionalStyles.$css;
    }

    /**
     * Get css from settings
     * */
    public static function getCSSFromSettings(){
        $woo_mb_settings = get_option('woo_mb_settings', '');
        if ($woo_mb_settings != ''){
            $woo_mb_settings = json_decode($woo_mb_settings);
        }
        $maxWidth = isset($woo_mb_settings->container_width)? $woo_mb_settings->container_width: '';
        if($maxWidth == ''){
            $maxWidth = 640;
        }
        $order_item_table_border_color = isset($woo_mb_settings->order_item_table_border_color)? $woo_mb_settings->order_item_table_border_color: '#dddddd';
        $custom_css = isset($woo_mb_settings->custom_css)? $woo_mb_settings->custom_css: '';
        if($custom_css != ''){
            $custom_css = strip_tags($custom_css);
            $custom_css = str_replace('\n', '', $custom_css);
        }
        $product_image_height = isset($woo_mb_settings->product_image_height)? $woo_mb_settings->product_image_height: 32;
        $product_image_width = isset($woo_mb_settings->product_image_width)? $woo_mb_settings->product_image_width: 32;
        $css = "table.email_builder_table_items{border-collapse: collapse !important;width: 100%; border: 1px solid ".$order_item_table_border_color." !important;}";
        $css .= "table.email_builder_table_items tbody tr,
                table.email_builder_table_items tbody tr td,
                table.email_builder_table_items thead tr,
                table.email_builder_table_items thead tr th,
                table.email_builder_table_items thead tr td,
                table.email_builder_table_items tfoot tr,
                table.email_builder_table_items tfoot tr th,
                table.email_builder_table_items tfoot tr td
                    {border: 1px solid ".$order_item_table_border_color." !important;}";
        $css .= ".builder .email-container{max-width: ".$maxWidth."px}";
        $css .= "table.em-main { width: 100% !important; max-width: ".$maxWidth."px}";
        $css .= "table.em-image-caption-column { width: 50% !important; max-width: ".$maxWidth."px}";

        $css .= "@media only screen and (max-width: 640px) {";
        $css .= "table.em-image-caption-column { width: 100% !important; max-width: ".$maxWidth."px}}";
        $css .= "tr.order_item td img{
                    height: ".$product_image_height."px !important;
                    width: ".$product_image_width."px !important;
                 }";
        return $css.$custom_css;
    }

    /**
     * get custom fields of flexible checkout fields
     * */
    public static function getCustomFieldsOfFlexibleCheckoutFields(){
        global $flexible_checkout_fields;
        $fields = array();
        if(self::hasFlexibleCheckoutFieldsPlugin()){
            $fields = array();
            if(method_exists($flexible_checkout_fields, 'get_settings')){
                $field_settings = $flexible_checkout_fields->get_settings();
                $custom_fields['billing'] = (isset($field_settings['billing']))? $field_settings['billing']: array();
                $custom_fields['shipping'] = (isset($field_settings['shipping']))? $field_settings['shipping']: array();
                $custom_fields['order'] = (isset($field_settings['order']))? $field_settings['order']: array();
                foreach ($custom_fields as $custom_field){
                    if(!empty($custom_field))
                        foreach ($custom_field as $field_data){
                            if(isset($field_data['custom_field']) && $field_data['custom_field'] == 1){
                                if(isset($field_data['name']) && $field_data['name']){
                                    $fields['_'.$field_data['name']] = $field_data['label'];
                                }
                            }
                        }
                }
            }
        }

        return $fields;
    }

    /**
     * Check flexible checkout fields plugin loaded/function exists
     * */
    public static function hasFlexibleCheckoutFieldsPlugin(){
        if(function_exists('wpdesk_get_order_meta')) return true;
        return false;
    }

    /**
     * get Email customizer settings enable/disable template option
     * */
    public static function getEmailCustomizerSettingsTemplateOptions(){
        $woo_mb_settings_lang = get_option('woo_mb_settings_lang', '');
        if ($woo_mb_settings_lang != '') $woo_mb_settings_lang = json_decode($woo_mb_settings_lang);

        return $woo_mb_settings_lang;
    }

    /**
     * get Email customizer settings enable/disable template option
     * */
    public static function getEmailCustomizerSettings($key, $default = ''){
        $woo_mb_settings = get_option('woo_mb_settings', '');
        if ($woo_mb_settings != '') $woo_mb_settings = json_decode($woo_mb_settings, true);
        if(isset($woo_mb_settings[$key])){
            return $woo_mb_settings[$key];
        }
        return $default;
    }

    /**
     * get Email customizer settings enable/disable template option
     * */
    public static function getEmailCustomizerSettingsForAPI(){
        $woo_mb_settings_analytics_rabbit = get_option('woo_mb_settings_analytics_rabbit', '');
        if ($woo_mb_settings_analytics_rabbit != '') $woo_mb_settings_analytics_rabbit = json_decode($woo_mb_settings_analytics_rabbit);

        return $woo_mb_settings_analytics_rabbit;
    }

    /**
     * get Email customizer settings enable/disable template option
     * */
    public static function runMigrationScripts(){
        if(function_exists('WOOMBPB_RemoveEmailTemplateMigrationForLanguageFix')){
            //check migration for language fix in WordPress 5.1
            WOOMBPB_RemoveEmailTemplateMigrationForLanguageFix();
        }
    }

    /**
     * Import email templates
     * */
    public static function importEmailTemplates(){
        $result = false;
        if(isset($_POST['import']) && sanitize_text_field($_POST['import']) == 1){
            $message = '';
            if(!empty($_FILES["import_file"])){
                if(!empty($_FILES["import_file"]['type'])){
                    if($_FILES["import_file"]['type'] == 'application/json'){
                        if(!empty($_FILES["import_file"]["tmp_name"])){
                            $file = $_FILES["import_file"]["tmp_name"];
                            $json = file_get_contents($file);
                            $data = json_decode($json, true);
                            if(!empty($data)){
                                $result = self::importJSONContentToDB($data);
                                if($result){
                                    $message = esc_html__('Template(s) imported successfully', 'woo-email-customizer-page-builder');
                                }
                            } else {
                                $message = esc_html__('No data available to import', 'woo-email-customizer-page-builder');
                            }
                        } else {
                            $message = esc_html__('File not found', 'woo-email-customizer-page-builder');
                        }
                    } else {
                        $message = esc_html__('Support only json file format', 'woo-email-customizer-page-builder');
                    }
                } else {
                    $message = esc_html__('File not selected for import', 'woo-email-customizer-page-builder');
                }
            } else {
                $message = esc_html__('File not selected for import', 'woo-email-customizer-page-builder');
            }
            if($result){
                $class = 'updated notice notice-success emc-import-export-message';
            } else {
                $class = 'error notice emc-import-export-message';
            }
            if(!empty($message)){
                echo '<div class="'.$class.'">';
                echo '<p>';
                echo $message;
                echo '</p>';
                echo '</div>';
            }
        }
    }

    /**
     * Export email templates
     * */
    public static function exportEmailTemplates(){
        $isAdmin = is_admin();
        if($isAdmin){
            $filename = "email_templates_".time().".json";
            $array_data = self::getAllEmailTemplatesForExport();
            header( 'Content-Type: application/json' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '";' );

            // clean output buffer
            ob_end_clean();

            $handle = fopen( 'php://output', 'w' );

            fwrite($handle, json_encode($array_data));

            fclose( $handle );

            // flush buffer
            ob_flush();
        }
        exit();
    }

    /**
     * Get template for export
     * */
    protected static function getAllEmailTemplatesForExport(){
        $data = array();
        global $wpdb;
        $posts = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE `post_type` = 'woo_mb_template'" );
        if(!empty($posts)){
            $accepted_meta_fields[] = 'additionalstyles';
            $accepted_meta_fields[] = 'elements';
            $accepted_meta_fields[] = 'emailSettings';
            $accepted_meta_fields[] = 'html';
            $accepted_meta_fields[] = 'styles';
            $accepted_meta_fields[] = 'wec_language';
            $fields_to_export_default = self::getFieldsToExport();
            $i = 0;
            if(is_array($posts) && count($posts)){
                foreach ($posts as $post){
                    if(!empty($post->ID)){
                        $data[$i] = $fields_to_export_default;
                        $data[$i]['post_title'] = $post->post_title;
                        $data[$i]['post_name'] = $post->post_name;
                        $data[$i]['post_status'] = $post->post_status;
                        $post_content = json_decode($post->post_content, true);
                        if(isset($post_content['elements'])){
                            array_walk_recursive($post_content['elements'], function(&$item, $key) {
                                $item = addslashes($item);
                            });
                        }
                        if(isset($post_content['html']))
                            $post_content['html'] = addslashes($post_content['html']);
                        $data[$i]['post_content'] = json_encode($post_content);
                        $data[$i]['post_type'] = $post->post_type;
                        $post_id = $post->ID;
                        $post_metas = get_post_meta($post_id);
                        if(!empty($post_metas)){
                            foreach ($post_metas as $meta_key => $meta){
                                if(in_array($meta_key, $accepted_meta_fields)){
                                    if(isset($meta[0])){
                                        if(in_array($meta_key, array('emailSettings', 'elements'))){
                                            $json_data = json_decode($meta[0]);
                                            if(empty($json_data)){
                                                $get_in_array = get_post_meta($post_id, $meta_key, true);
                                                $json_data = json_encode($get_in_array);
                                            } else {
                                                $json_data = $meta[0];
                                            }
                                            $data[$i][$meta_key] = addslashes($json_data);
                                        } else {
                                            $data[$i][$meta_key] = $meta[0];
                                        }
                                    }
                                }
                            }
                        }
                        $i++;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get the field names to be exported
     * */
    protected static function getFieldsToExport(){
        $fields_to_export = array();
        $fields_to_export['post_title'] = '';
        $fields_to_export['post_name'] = '';
        $fields_to_export['wec_language'] = '';
        $fields_to_export['post_status'] = '';
        $fields_to_export['post_content'] = '';
        $fields_to_export['post_type'] = '';
        //Meta fields
        $fields_to_export['additionalstyles'] = '';
        $fields_to_export['elements'] = '';
        $fields_to_export['emailSettings'] = '';
        $fields_to_export['html'] = '';
        $fields_to_export['styles'] = '';

        return $fields_to_export;
    }

    /**
     * Import JSON Content to DB
     * */
    protected static function importJSONContentToDB($data)
    {
        if (!empty($data) && is_array($data) && count($data)) {
            $added_template = false;
            foreach ($data as $template) {
                if (isset($template['post_title']) && isset($template['wec_language']) && !empty($template['post_title']) && !empty($template['wec_language'])) {
                    $newPost['post_type'] = 'woo_mb_template';
                    $newPost['post_title'] = $template['post_title'];
                    $language = $newPost['post_name'] = $template['wec_language'];
                    $woo_mb_base = new WC_Email_Base();
                    $postid = $woo_mb_base->getEmailTemplateFromPost(sanitize_text_field($template['post_title']), $language);
                    $newPost['post_content'] = $template['post_content'];
                    $newPost['post_status'] = 'active';
                    if ($postid) {
                        $newPost['ID'] = $postid;
                        unset($newPost['post_name']);
                        $updated = wp_update_post($newPost);
                    } else {
                        $postid = wp_insert_post($newPost);
                    }

                    $metaData['additionalstyles'] = $template['additionalstyles'];
                    $metaData['elements'] = $template['elements'];
                    $metaData['emailSettings'] = $template['emailSettings'];
                    $metaData['html'] = $template['html'];
                    $metaData['styles'] = $template['styles'];
                    $metaData['wec_language'] = $language;
                    if ($postid) {
                        foreach ($metaData as $index => $value) {
                            if (get_post_meta($postid, $index)) {
                                update_post_meta($postid, $index, $value);
                            } else {
                                add_post_meta($postid, $index, $value, true);
                            }
                        }
                        $added_template = true;
                    }
                }
            }

            return $added_template;
        }

        return false;
    }

    /**
     * To load additional short codes
     * */
    public static function getAdditionalShortCodes(){
        $additional_shortcode = array();
        $additional_shortcode = apply_filters('woo_email_drag_and_drop_builder_load_additional_shortcode', $additional_shortcode);
        return $additional_shortcode;
    }

    /**
     * To load additional short code value
     * */
    public static function getAdditionalShortCodeValues($order, $sending_email){
        $additional_shortcode = array();
        $additional_shortcode = apply_filters('woo_email_drag_and_drop_builder_load_additional_shortcode_data', $additional_shortcode, $order, $sending_email);
        return $additional_shortcode;
    }

    /**
     * Get additional order meta keys
     *
     * @param object $order
     * @return array
     * */
    public static function getAdditionalOrderMetaKeys($order){
        $order_id = $order->get_id();
        $additional_order_meta_keys = array();
        if(!empty($order_id)){
            $excluded_fields = array('woo_discount_log');
            $order_meta_keys = get_post_custom_keys($order_id);
            $only_displaying_meta = apply_filters('woo_email_drag_and_drop_builder_load_hidden_meta_fields', false, $order);
            if(!empty($order_meta_keys)){
                foreach ($order_meta_keys as $order_meta_key){
                    if(!(substr($order_meta_key, 0, 1) === "_") || $only_displaying_meta){
                        if(!in_array($order_meta_key, $excluded_fields)){
                            $additional_order_meta_keys[] = $order_meta_key;
                        }
                    }
                }
            }
        }

        return $additional_order_meta_keys;
    }

    /**
     * To display settings link in plugin page
     *
     * @param array $links
     * @return array
     * */
    public static function addActionLinksInPluginPage($links){
        $mewlinks = array(
            '<a href="' . admin_url("admin.php?page=woo_email_customizer_page_builder&settings=default"). '">'.esc_html__('Settings', 'woo-email-customizer-page-builder').'</a>',
        );
        return array_merge( $links, $mewlinks );
    }
}