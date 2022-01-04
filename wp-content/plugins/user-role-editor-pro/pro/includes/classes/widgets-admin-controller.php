<?php

/*
 * User Role Editor WordPress plugin
 * Class URE_Widgets_Admin_Controller - data update/load for Widgets Admin Access add-on
 * Author: Vladimir Garagulya
 * Author email: support@role-editor.com
 * Author URI: https://www.role-editor.com
 * License: GPL v2+ 
 */

class URE_Widgets_Admin_Controller {

    const ACCESS_DATA_KEY = 'ure_widgets_access_data';
    private $lib = null;
    
    
    public function __construct() {
        
        $this->lib = URE_Lib_Pro::get_instance();
        
        add_action('ure_process_user_request', array($this, 'update_data'));
        
    }
    // end of __construct()
    
        
    /**
     * Load widgets access data for role
     * @param string $role_id
     * @return array
     */
    public static function load_data_for_role( $role_id, $access_data=null ) {
    
        if ( $access_data==null ) {
            $access_data = get_option(self::ACCESS_DATA_KEY);
        }
        if ( is_array( $access_data ) && array_key_exists( $role_id, $access_data ) ) {
            $result = $access_data[$role_id];
            // update data structure to make it compatible with version 4.35+
            if ( !isset( $result['widgets'] ) ) {
                $result = array(
                    'access_model'=>1,
                    'widgets' => $result,
                    'sidebars' => array()
                    );
            }
            // update data structure to make it compatible with version 4.56+
            if ( !isset( $result['access_model'] ) ) {
                $result = array(
                    'access_model'=>1,
                    'widgets' => $result['widgets'],
                    'sidebars' => $result['sidebars'] 
                    );
            }
        } else {
            $result = array(
                'access_model'=>1,
                'widgets' => array(),
                'sidebars' => array()
                );
        }
        
        return $result;
    }
    // end of load_data_for_role()
    
    
    private function _load_data_for_user( WP_User $user ) {
                
        if ( $user->ID>0 ) {
            $user_data = get_user_meta( $user->ID, self::ACCESS_DATA_KEY, true );
        } else {
            $user_data = array();
        }
        // update data structure to make it compatible with version 4.56+
        if ( !is_array( $user_data ) ) {
            $user_data = self::load_data_for_role( 'does-not-exist', array() );
        } else {
            if ( !isset( $user_data['access_model'] ) ) {
                $user_data['access_model'] = 1;
            }
            if ( !isset( $user_data['widgets'] ) ) {                
                $user_data['widgets'] = array();
            }                
            if ( !isset( $user_data['sidebars'] ) ) {
                $user_data['sidebars'] = array();
            }                
        }
        
        return $user_data;
    }
    // end of _load_data_for_user()
    
    
    /*
     * Returns the list of blocked widgets and sidebars for the case when 
     * access_model=2 "Block Not Selected"
     */
    private function invert_selection( $data ) {

        if ( $data['access_model']==1 ) {
            // Block Selected found - no need to change
            return $data;
        }
        
        $blocked = array(
            'access_model'=>1, 
            'widgets'=>array(),
            'sidebars'=>array()
            );
        
        $all_widgets = $this->get_all_widgets();
        foreach( $all_widgets as $widget_class=>$widget ) {
            if ( !in_array( $widget_class, $data['widgets'] ) ) {
                $blocked['widgets'][] = $widget_class;
            }
        }
        
        $all_sidebars = $this->get_all_sidebars();
        foreach( $all_sidebars as $sidebar_id=>$sidebar ) {
            if ( !in_array($sidebar_id, $data['sidebars'] ) ) {
                $blocked['sidebars'][] = $sidebar_id;
            }
        }
        
        return $blocked;        
    }
    // end of invert_selection()
    

    /*
     * Return the list of widgests and sidebars blocked for the user $user
     */
    public function load_data_for_user( WP_User $user ) {
    
        $user_data = self::_load_data_for_user( $user );        
        $roles_data = get_option( self::ACCESS_DATA_KEY );
        $access_model = null;
        foreach ( $user->roles as $role_id ) {
            $role_data = self::load_data_for_role( $role_id, $roles_data );
            if ( empty( $access_model ) ) {
                $access_model = $role_data['access_model'];
                $user_data['access_model'] = $role_data['access_model'];
            }
            if ( $access_model!=$role_data['access_model'] ) { 
                continue;   // skip other access model restrictions
            }            
            $user_data = array_merge_recursive( $user_data, $role_data );            
            $user_data['access_model'] = $access_model;
        }                        
        $user_data['widgets'] = array_unique( $user_data['widgets'] );
        $user_data['sidebars'] = array_unique( $user_data['sidebars'] );
        
        $user_data = apply_filters( 'ure_widgets_edit_access_user', $user_data, $user );
        
        if ( $user_data['access_model']==2 ) {  // Block not selected
            // invert selection to return the list of blocked items
            $blocked = $this->invert_selection( $user_data );
        } else {    // Block selected
            $blocked = $user_data;
        }
        
        return $blocked;
    }
    // end of load_data_for_user()

    
    private function get_access_model_from_post() {
        
        $access_model = filter_input( INPUT_POST, 'ure_widgets_admin_access_model', FILTER_VALIDATE_INT );
        if ( $access_model!=1 && $access_model!=2 ) {
            $access_model = 1;
        }
        
        return $access_model;
    }
    
    private function get_access_data_from_post() {
        
        $access_model = $this->get_access_model_from_post();
        $access_data = array(
            'access_model' => $access_model,
            'widgets' => array(),
            'sidebars' => array()
            );
        $keys_to_skip = array('action', 'ure_nonce', '_wp_http_referer', 'ure_object_type', 'ure_object_name', 'user_role');        
        foreach ($_POST as $key=>$value) {
            if (in_array($key, $keys_to_skip)) {
                continue;
            }
            if (substr($key, 0, 7)==='ure_sb-') {    // Sidebar
                $access_data['sidebars'][] = substr($key, 7);
            } else {
                $access_data['widgets'][] = $key;
            }
            
        }
        
        return $access_data;
    }
    // end of get_access_data_from_post()
        
    
    private function save_access_data_for_role($role_id) {
        
        $access_for_role = $this->get_access_data_from_post();
        $access_data = get_option(self::ACCESS_DATA_KEY);        
        if (!is_array($access_data)) {
            $access_data = array();
        }
        if (count($access_for_role)>0) {
            $access_data[$role_id] = $access_for_role;
        } else {
            unset($access_data[$role_id]);
        }
        update_option(self::ACCESS_DATA_KEY, $access_data);
        
    }
    // end of save_access_data_for_role()
    
    
    private function save_access_data_for_user($user_login) {
        
//$access_for_user = $this->get_access_data_from_post();
        // TODO ...
        
    }
    // end of save_menu_access_data_for_user()   
                    
    
    public function get_allowed_roles($user) {
        $allowed_roles = array();
        if ( empty( $user ) ) {   // request for Role Editor - work with currently selected role
            $current_role = filter_input(INPUT_POST, 'current_role', FILTER_SANITIZE_STRING);
            $allowed_roles[] = $current_role;
        } else {    // request from user capabilities editor - work with that user roles
            $allowed_roles = $user->roles;
        }
        
        return $allowed_roles;
    }
    // end of get_allowed_roles()
                    
    
    public function get_all_widgets() {
        global $wp_widget_factory;
	
        if (is_object($wp_widget_factory)) {
            return $wp_widget_factory->widgets;
        } else {
            return array();
        }
    }
    // end of get_all_widgets()
    

    /**
     * Returns the list of globally registered sidebars
     * @global array $wp_registered_sidebars
     * @return array
     */
    private function get_registered_sidebars() {
        global $wp_registered_sidebars;
        
        $rsb = array();
        if (is_array($wp_registered_sidebars)) {
            foreach($wp_registered_sidebars as $id=>$sidebar) {
                $rsb[$id] = array(
                    'id'=>$id,
                    'name'=>$sidebar['name'],
                    'description'=>$sidebar['description']
                    );
            }            
        }
        
        return $rsb;
    }
    // end of get_registered_sidebars()


    private function get_divi_sidebars() {
        $dsb = array();
        // Add widgets area (sidebars) created by users via Divi theme interface (not loaded globally)
        // code was written according to Divi/includes/builder/functions.php, et_builder_widgets_init()
        $et_pb_widgets = get_theme_mod( 'et_pb_widgets' );
        if (!empty($et_pb_widgets['areas'])) {
            foreach($et_pb_widgets['areas'] as $id => $name) {
                $dsb[$id] = array(
                    'id'=>$id,
                    'name'=>$name,
                    'description'=>''
                    );
            }
        }
        
        return $dsb;
    }
    
    
    public function get_all_sidebars() {
        
        $rsb = $this->get_registered_sidebars();        
        $dsb = $this->get_divi_sidebars();
        
        $all_sb = array_merge($rsb, $dsb);
        
        return $all_sb;
    }
    // end of get_all_sidebars()
    
    
    public function update_data() {
    
        if (!isset($_POST['action']) || $_POST['action']!=='ure_update_widgets_access') {
            return;
        }
        
        $editor = URE_Editor::get_instance();
        if (!current_user_can('ure_widgets_access')) {
            $editor->set_notification( esc_html__('URE: you do not have enough permissions to access this module.', 'user-role-editor') );
            return;
        }
        
        $ure_object_type = filter_input(INPUT_POST, 'ure_object_type', FILTER_SANITIZE_STRING);
        if ($ure_object_type!=='role' && $ure_object_type!=='user') {
            $editor->set_notification( esc_html__('URE: widgets access: Wrong object type. Data was not updated.', 'user-role-editor') );
            return;
        }
        $ure_object_name = filter_input(INPUT_POST, 'ure_object_name', FILTER_SANITIZE_STRING);
        if (empty($ure_object_name)) {
            $editor->set_notification( esc_html__('URE: widgets access: Empty object name. Data was not updated', 'user-role-editor') );
            return;
        }
                        
        if ($ure_object_type=='role') {
            $this->save_access_data_for_role($ure_object_name);
        } else {
            $this->save_access_data_for_user($ure_object_name);
        }
        
        $editor->set_notification( esc_html__('Widgets access: data was updated successfully', 'user-role-editor') );
    }
    // end of update_data()

        
}
// end of URE_Widgets_Admin_Controller class
