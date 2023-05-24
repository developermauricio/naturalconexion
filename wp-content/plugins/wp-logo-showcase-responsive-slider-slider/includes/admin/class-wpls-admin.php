<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package WP Logo Showcase Responsive Slider
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Wpls_Admin {

	function __construct() {

		// Admin init process
		add_action( 'admin_init', array($this, 'wpls_admin_init_process') );

		// Action to add admin menu
		add_action( 'admin_menu', array($this, 'wpls_register_menu') );

		// Action to add metabox
		add_action( 'add_meta_boxes', array($this, 'wpls_post_sett_metabox'), 10, 2 );

		// Action to save metabox value
		add_action( 'save_post_'.WPLS_POST_TYPE, array($this, 'wpls_save_meta_box_data') );
		
		// Action to add custom column to Logo listing
		add_filter('manage_'.WPLS_CAT_TYPE.'_custom_column', array($this, 'wplss_logoshowcase_cat_columns'), 10, 3);
		
		// Action to add custom column data to Logo listing
		add_filter('manage_edit-'.WPLS_CAT_TYPE.'_columns', array($this, 'wplss_logoshowcase_cat_manage_columns') ); 
	}

	/**
	 * Function to notification transient
	 * 
	 * @since 1.0.0
	 */
	function wpls_admin_init_process() {

		global $typenow;

		$current_page = isset( $_REQUEST['page'] ) ? wpls_clean( $_REQUEST['page'] ) : '';

		// If plugin notice is dismissed
		if( isset( $_GET['message'] ) && 'wpls-plugin-notice' == $_GET['message'] ) {
			set_transient( 'wpls_install_notice', true, 604800 );
		}

		// Redirect to external page for upgrade to menu
		if( $typenow == WPLS_POST_TYPE ) {

			if( $current_page == 'wpls-premium' ) {

				$tab_url		= add_query_arg( array( 'post_type' => WPLS_POST_TYPE, 'page' => 'wpls-solutions-features', 'tab' => 'wpls_basic_tabs' ), admin_url('edit.php') );

				wp_redirect( $tab_url );
				exit;
			}
		}

		// Redirect to external page for upgrade to menu
		// if( $typenow == WPLS_POST_TYPE ) {

		// 	if( $current_page == 'wpls-upgrade-pro' ) {

		// 		wp_redirect( WPLS_PLUGIN_LINK_UPGRADE );
		// 		exit;
		// 	}

		// 	if( $current_page == 'wpls-bundle-deal' ) {

		// 		wp_redirect( WPLS_PLUGIN_BUNDLE_LINK );
		// 		exit;
		// 	}
		// }
	}

	/**
	 * Function to add menu
	 * 
	 * @since 1.0.0
	 */
	function wpls_register_menu() {

		// How it Work Page
		add_submenu_page( 'edit.php?post_type='.WPLS_POST_TYPE, __( 'How it works, our plugins and offers', 'wp-logo-showcase-responsive-slider-slider' ), __( 'How It Works', 'wp-logo-showcase-responsive-slider-slider' ), 'manage_options', 'wpls-designs', array( $this, 'wpls_designs_page' ) );

		// Solutions & Features Page
		add_submenu_page( 'edit.php?post_type='.WPLS_POST_TYPE, __( 'Overview - Logo Showcase Responsive Slider', 'wp-logo-showcase-responsive-slider-slider' ), '<span style="color:#2ECC71">'. __( 'Overview', 'wp-logo-showcase-responsive-slider-slider' ).'</span>', 'manage_options', 'wpls-solutions-features', array( $this, 'wpls_solutions_features_page' ) );

		// Upgrade To PRO Page
		add_submenu_page( 'edit.php?post_type='.WPLS_POST_TYPE, __( 'Upgrade To PRO - Logo Showcase Responsive Slider', 'wp-logo-showcase-responsive-slider-slider' ), '<span style="color:#ff2700">'.__( 'Upgrade To PRO', 'wp-logo-showcase-responsive-slider-slider' ).'</span>', 'manage_options', 'wpls-premium', array( $this, 'wpls_premium_page' ) );
	}

	/**
	 * How It Work Page Html
	 * 
	 * @since 1.0
	 */
	function wpls_designs_page() {
		include_once( WPLS_DIR . '/includes/admin/settings/how-it-work.php' );
	}

	/**
	 * Solutions & Features Page Html
	 * 
	 * @since 2.0.11
	 */
	function wpls_solutions_features_page() {
		include_once( WPLS_DIR . '/includes/admin/settings/solution-features/solutions-features.php' );
	}

	/**
	 * Getting Started Page Html
	 * 
	 * @since 1.0.0
	 */
	function wpls_premium_page() {
		//include_once( WPLS_DIR . '/includes/admin/settings/premium.php' );
	}

	/**
	 * Post Settings Metabox
	 * 
	 * @since 2.5
	 */
	function wpls_post_sett_metabox( $post_type, $post ) {
		
		// Plugin Setting Metabox
		add_meta_box( 'wpls-post-metabox', __( 'WP Logo Showcase Responsive Slider - Settings', 'wp-logo-showcase-responsive-slider-slider' ), array( $this, 'wpls_post_sett_box_callback' ), WPLS_POST_TYPE, 'normal', 'high' );
		
		// Premium Setting Metabox
		add_meta_box( 'wpls-post-metabox-pro', __( 'More Premium - Settings', 'wp-logo-showcase-responsive-slider-slider' ), array( $this, 'wpls_post_sett_box_callback_pro' ), WPLS_POST_TYPE, 'normal', 'high' );
	}

	/**
	 * Function to handle 'Add Link URL' metabox HTML
	 * 
	 * @since 2.5
	 */
	function wpls_post_sett_box_callback( $post ) {
		include_once( WPLS_DIR .'/includes/admin/metabox/wpls-post-setting-metabox.php');		
	}
	
	/**
	 * Function to handle 'premium ' metabox HTML
	 * 
	 * @since 2.5
	 */
	function wpls_post_sett_box_callback_pro( $post ) {		
		include_once( WPLS_DIR .'/includes/admin/metabox/wpls-post-setting-metabox-pro.php');
	}

	/**
	 * Function to save metabox values
	 * 
	 * @since 2.5
	 */
	function wpls_save_meta_box_data( $post_id ) {

		global $post_type;

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )                	// Check Autosave
		|| ( ! isset( $_POST['post_ID'] ) || $post_id != $_POST['post_ID'] )  	// Check Revision
		|| ( $post_type !=  WPLS_POST_TYPE ) )              				// Check if current post type is supported.
		{
			return $post_id;
		}

		$prefix		= WPLS_META_PREFIX; // Taking metabox prefix
		$logo_link 	= isset( $_POST[$prefix.'logo_link'] ) ? wpls_clean_url( $_POST[$prefix.'logo_link'] ) : '';

		// Updating Post Meta
		update_post_meta( $post_id, 'wplss_slide_link', $logo_link );
	}

	/**
	 * Add custom column to Logo listing page
	 * 
	 * @since 1.0.0
	 */
	function wplss_logoshowcase_cat_columns($ouput, $column_name, $tax_id) {

		if( 'wpls_logo_shortcode' == $column_name ) {
			$ouput .= '[logoshowcase cat_id="' . esc_attr( $tax_id ). '"]';
		}

		return $ouput;
	}

	/**
	 * Add custom column data to Logo listing page
	 * 
	 * @since 1.0.0
	 */
	function wplss_logoshowcase_cat_manage_columns($columns) {

		$new_columns = array(
							'wpls_logo_shortcode' => esc_html__( 'Category Shortcode', 'wp-logo-showcase-responsive-slider-slider' )
						);

		$columns = wpls_logo_add_array( $columns, $new_columns, 2 );
		
		return $columns;
	}
}

$wpls_Admin = new Wpls_Admin();