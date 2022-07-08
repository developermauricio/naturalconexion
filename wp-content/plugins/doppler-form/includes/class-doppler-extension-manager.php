<?php

class Doppler_Extension_Manager {

    public $extensions = array( 
        'doppler-for-woocommerce' => array( 'class_name' => 'Doppler_For_Woocommerce',
											'dependency' => 'WooCommerce',
                                            'zip_file'   => 'https://downloads.wordpress.org/plugin/doppler-for-woocommerce.zip',
                                            'settings'   => 'doppler_woocommerce_menu' ),
        'doppler-for-learnpress'  => array( 'class_name' => 'Doppler_For_Learnpress',
											'dependency' => 'LearnPress',
                                            'zip_file'   => 'https://downloads.wordpress.org/plugin/doppler-for-learnpress.zip',
                                            'settings'   => 'doppler_learnpress_menu' )
	);

    /**
     * Check if an extension is active.
     */
    public function is_active( $extension_slug ) {
        if( !array_key_exists($extension_slug, $this->extensions) || !class_exists($this->extensions[$extension_slug]['class_name']) ){
            return false;
        }else if( class_exists($this->extensions[$extension_slug]['class_name']) ){
            return true;
        }
        return false;
    }
	
	/**
     * Check if an extension is active.
     */
    public function has_dependency( $extension_slug ) {
        if( !array_key_exists($extension_slug, $this->extensions) || !class_exists($this->extensions[$extension_slug]['dependency']) ){
            return false;
        }else if( class_exists($this->extensions[$extension_slug]['dependency']) ){
            return true;
        }
        return false;
    }

    /**
     * Install extensions.
     */
    public function install_extension() {  
        if(empty($_POST['extensionName'])) return false;
        
        if(!$this->is_plugin_installed($_POST['extensionName'])){
            include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            wp_cache_flush();
            $upgrader = new Plugin_Upgrader();
            $buffer = $upgrader->install( $this->extensions[$_POST['extensionName']]['zip_file'], array( 'clear_destination' => true ) );
        }
        
        if(!$this->is_active($_POST['extensionName'])){
            $extension_path = DOPPLER_PLUGINS_PATH .$_POST['extensionName'].'\\'.$_POST['extensionName'].'.php';
            if(activate_plugin($extension_path) == NULL){
                echo '1';
            }
        }
        exit();
    }

    /**
     * Check if an extension is installed.
     */
    private function is_plugin_installed( $slug ) {
        if ( ! function_exists( 'get_plugins' ) ) {
          require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $extension = $slug.'/'.$slug.'.php';
        $all_plugins = get_plugins();
                
        if ( !empty( $all_plugins[$extension] ) ) {
          return true;
        } else {
          return false;
        }
    }
}