<?php
namespace HTML5Player\Model;

class GlobalChanges{
    protected static $_instance = null;

    public function __construct(){
        add_action( 'admin_head', [$this, 'h5vp_my_custom_script']);
        add_action('admin_menu', [$this, 'h5vp_add_custom_link_into_cpt_menu']);
        add_action( 'wp_dashboard_setup', [$this, 'h5vp_add_dashboard_widgets'] );
        
    }

    /**
     * Create instance
     */
    public static function instance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Add custom script to open 'PRO Version Demo' menu in a new tap
     */
    function h5vp_my_custom_script() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                $( "ul#adminmenu a[href$='https://links.bplugins.com/h5vp-menu']" ).attr( 'target', '_blank' );
            });
        </script>
        <?php
    }

    /**
     * add submenu -> PRO Version Demo
     */
    function h5vp_add_custom_link_into_cpt_menu() {
        global $submenu;
        $link = 'https://links.bplugins.com/h5vp-menu';
        $submenu['edit.php?post_type=videoplayer'][] = array( 'PRO Version Demo', 'manage_options', $link, 'meta'=>'target="_blank"' );
    }

    /**
     * Add a sectoin to the dashboard area
     */
    function h5vp_add_dashboard_widgets() {
        wp_add_dashboard_widget( 'h5vp_example_dashboard_widget', 'Support Html5 Video Player', [$this, 'h5vp_dashboard_widget_function'] );
    
        global $wp_meta_boxes;
        $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
        $example_widget_backup = array( 'h5vp_example_dashboard_widget' => $normal_dashboard['h5vp_example_dashboard_widget'] );
        unset( $normal_dashboard['h5vp_example_dashboard_widget'] );
       $sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );
        $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
   } 
   
   /**
    * Dashboard area content
    */
   function h5vp_dashboard_widget_function() {
       // Display whatever it is you want to show.
       echo '<p>It is hard to continue development and support for this plugin without contributions from users like you. If you enjoy using the plugin and find it useful, please consider support by  <b>DONATION</b> or <b>BUY THE PRO VERSION (No ads)</b> of the Plugin. Your support will help encourage and support the plugins continued development and better user support.</p>	
        <center>
        <a target="_blank" href="https://gum.co/wpdonate"><div><img width="200" src="'.H5VP_PLUGIN_DIR.'img/donation.png'.'" alt="Donate Now" /></div></a>
        </center><br />
        <script src="https://gumroad.com/js/gumroad-embed.js"></script>
        <div class="gumroad-product-embed" data-gumroad-product-id="mizkf" data-outbound-embed="true"><a href="https://gumroad.com/l/mizkf">Loading...</a></div>';
   }
}
GlobalChanges::instance();