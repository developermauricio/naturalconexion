<?php
/**
 * Integration class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductBundles
 */

defined( 'YITH_WCPB' ) || exit;

/**
 * Compatibility Class
 *
 * @class   YITH_WCPB_Compatibility
 * @since   1.1.2
 */
class YITH_WCPB_Compatibility {

	/**
	 * Single instance of the class
	 *
	 * @var YITH_WCPB_Compatibility
	 */
	protected static $instance;


	/**
	 * WPML Integration instance.
	 *
	 * @var YITH_WCPB_Wpml_Compatibility_Premium|YITH_WCPB_Wpml_Compatibility
	 */
	public $wpml;

	/**
	 * Singleton implementation
	 *
	 * @return YITH_WCPB_Compatibility
	 */
	public static function get_instance() {
		/**
		 * The class.
		 *
		 * @var YITH_WCPB_Compatibility|YITH_WCPB_Compatibility_Premium $self
		 */
		$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

		return ! is_null( $self::$instance ) ? $self::$instance : $self::$instance = new $self();
	}

	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->load();
	}

	/**
	 * Set the plugins.
	 */
	protected function get_plugins() {
		return array(
			'wpml' => array(
				'always_enabled' => true,
			),
		);
	}

	/**
	 * Load Add-on classes
	 */
	protected function load() {
		foreach ( $this->get_plugins() as $slug => $plugin_info ) {
			$filename          = YITH_WCPB_INCLUDES_PATH . '/compatibility/class.yith-wcpb-' . $slug . '-compatibility.php';
			$premium_filename  = str_replace( '-compatibility.php', '-compatibility-premium.php', $filename );
			$premium_load      = file_exists( $premium_filename );
			$classname         = $this->get_class_name_from_slug( $slug );
			$premium_classname = $this->get_class_name_from_slug( $slug, $premium_load );
			$var               = str_replace( '-', '_', $slug );

			if ( ! isset( $plugin_info['always_enabled'] ) || ! $plugin_info['always_enabled'] ) {
				if ( ! static::has_plugin( $slug ) ) {
					continue;
				}
			}

			if ( file_exists( $filename ) && ! class_exists( $classname ) ) {
				require_once $filename;
			}

			if ( file_exists( $premium_filename ) && ! class_exists( $premium_classname ) ) {
				require_once $premium_filename;
			}

			if ( class_exists( $classname ) && method_exists( $classname, 'get_instance' ) ) {
				$this->$var = $classname::get_instance();
			}
		}
	}

	/**
	 * Get the class name from slug
	 *
	 * @param string $slug           The slug.
	 * @param bool   $premium_suffix Set true to append the premium suffix.
	 *
	 * @return string
	 */
	public function get_class_name_from_slug( $slug, $premium_suffix = false ) {
		$class_slug = str_replace( '-', ' ', $slug );
		$class_slug = ucwords( $class_slug );
		$class_slug = str_replace( ' ', '_', $class_slug );

		return 'YITH_WCPB_' . $class_slug . '_Compatibility' . ( $premium_suffix ? '_Premium' : '' );
	}

	/**
	 * Check if user has plugin.
	 *
	 * @param string $plugin_slug Plugin slug.
	 *
	 * @return bool
	 * @since   1.1.2
	 */
	public static function has_plugin( $plugin_slug ) {
		return false;
	}
}
