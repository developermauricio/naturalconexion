<?php
final class H5AP_Elementor_Widget_Extension {

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '2.3.7';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Elementor_Test_Extension The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Elementor_Test_Extension An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {

		//Register Frontend Script
		add_action( "elementor/frontend/after_register_scripts", [ $this, 'frontend_assets_scripts' ] );

		// Add Plugin actions
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );

		add_action( 'elementor/controls/controls_registered', [ $this, 'init_controls' ] );

	}

	/**
	 * Init Controls
	 *
	 * Include control files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_controls() {

		// Include Widget files
		require_once( __DIR__ . '/inc/elementor-custom-control/b-select-file.php' );

		// Register controls
		\Elementor\Plugin::$instance->controls_manager->register_control( 'b-select-file', new \BPlugins_B_Select_File() );
	}


	/**
	 * Frontend script
	 */
	public function frontend_assets_scripts(){
		wp_register_script( 'bplugins-plyrio', H5VP_PLUGIN_DIR. 'js/plyr.js' , array('jquery'), H5VP_VER, false );
		wp_register_script( 'h5vp-public', H5VP_PLUGIN_DIR. 'dist/public.js' , array('jquery', 'bplugins-plyrio'), time(), true );
		
		wp_register_style( 'bplugins-plyrio', H5VP_PLUGIN_DIR . 'css/player-style.css', array(), H5VP_VER, 'all' );
		wp_register_style( 'h5vp-public', H5VP_PLUGIN_DIR. 'dist/public.css' , array('bplugins-plyrio'), H5VP_VER );

		wp_localize_script( 'h5vp-public', 'ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php'),
		));
		
	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_widgets() {
		// Include Widget files
		require_once( __DIR__ . '/inc/Elementor/VideoPlayer.php' );

		// Register widget
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \VideoPlayer() );
	}
}

H5AP_Elementor_Widget_Extension::instance();