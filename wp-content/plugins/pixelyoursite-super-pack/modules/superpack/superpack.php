<?php

namespace PixelYourSite;



if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SuperPack extends Settings implements Plugin {

	private static $_instance;
	
	private $configured;
	
	private $meta_box_screens = array();
	
	private $core_compatible;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();

            /** @noinspection PhpIncludeInspection */
            require_once PYS_SUPER_PACK_PATH . '/modules/superpack/functions-dynamic-params.php';
            /** @noinspection PhpIncludeInspection */
            require_once PYS_SUPER_PACK_PATH . '/modules/superpack/functions-cpt.php';
            /** @noinspection PhpIncludeInspection */
            require_once PYS_SUPER_PACK_PATH . '/modules/superpack/functions-remove-pixel.php';
            /** @noinspection PhpIncludeInspection */
            require_once PYS_SUPER_PACK_PATH . '/modules/superpack/functions-amp.php';
		}

		return self::$_instance;

	}

	public function __construct() {
	 
	    // cache status
	    $this->core_compatible = SuperPack\pysProVersionIsCompatible();
	    
		parent::__construct( 'superpack' );
		
		$this->locateOptions(
			PYS_SUPER_PACK_PATH . '/modules/superpack/options_fields.json',
			PYS_SUPER_PACK_PATH . '/modules/superpack/options_defaults.json'
		);
		
		// migrate after event post type registered
		add_action( 'pys_register_pixels', 'PixelYourSite\SuperPack\maybeMigrate' );
		
		add_action( 'pys_register_plugins', function( $core ) {
			/** @var PYS $core */
			$core->registerPlugin( $this );
		} );
        
		/** @noinspection PhpIncludeInspection */
		require_once PYS_SUPER_PACK_PATH . '/modules/superpack/functions-additional-ids.php';
        
        if ( ! $this->core_compatible ) {
            return;
        }
        

		
		if ( $this->getOption( 'enabled' ) ) {
            add_action( 'admin_init', array( $this, 'addMetaForCustomPost' ) );
		}
        
        add_filter( 'pys_admin_secondary_nav_tabs', 'PixelYourSite\SuperPack\adminSecondaryNavTabs' );
        add_action( 'pys_admin_superpack_settings', 'PixelYourSite\SuperPack\renderSettingsPage' );

        if(SuperPack\isWPMLActive()) {
            add_filter("pys_facebook_ids",array($this,"filter_facebook_ids"));
            add_filter("pys_google_ads_ids",array($this,"filter_google_ads_ids"));
            add_filter("pys_ga_ids",array($this,"filter_ga_ids"));
        }
	}


	public function addMetaForCustomPost() {
        $this->meta_box_screens = apply_filters( 'pys_superpack_meta_box_screens', array() );
        $this->meta_box_screens = array_unique( $this->meta_box_screens );
        if ( ! empty( $this->meta_box_screens ) ) {

            add_action( 'add_meta_boxes', array( $this, 'addSingularMetaBox' ) );
            foreach ( $this->meta_box_screens as $screen ) {
                add_action( 'save_post_' . $screen, array( $this, 'saveSingularMetaBox' ), 10, 1 );
            }
        }
    }
    
    /**
     * Returns cached core compatibility status.
     *
     * @return bool
     */
	public function getCoreCompatible() {
	    return $this->core_compatible;
    }

    /**
     * @since 2.0.5
     *
     * @return bool
     */
    public function enabled() {
        return $this->getOption( 'enabled' );
    }
	
	public function configured() {
		
		if ( $this->configured === null ) {
			$license_status = $this->getOption( 'license_status' );
			$this->configured = $this->getOption( 'enabled' ) && ! empty( $license_status );
		}
		
		return $this->configured;
		
	}

	public function adminUpdateLicense() {

		if ( ! PYS()->adminSecurityCheck() ) {
			return;
		}

		updateLicense( $this );

	}
	
	public function adminRenderPluginOptions() {
	    // for backward compatibility with PRO < 7.0.6
    }
    
    public function updatePlugin() {
        // for backward compatibility with PRO < 7.0.6
    }

	public function getPluginName() {
		return 'PixelYourSite Super Pack';
	}

	public function getPluginFile() {
		return PYS_SUPER_PACK_PLUGIN_FILE;
	}

	public function getPluginVersion() {
		return PYS_SUPER_PACK_VERSION;
	}
	
	public function addSingularMetaBox() {
		add_meta_box( 'pys', 'PixelYourSite PRO', array( $this, 'renderSingularMetaBox' ), $this->meta_box_screens, 'side' );
	}
	
	public function renderSingularMetaBox() {
		global $post;
		
		wp_nonce_field( 'pys_save_meta_box', '_pys_nonce' );
		
		do_action( 'pys_superpack_meta_box' );
		do_action( 'pys_superpack_meta_box_' . $post->post_type );
		
	}
	
	public function saveSingularMetaBox( $post_id ) {
		global $post;

		if ( ! isset( $_POST['_pys_nonce'] ) || ! wp_verify_nonce( $_POST['_pys_nonce'], 'pys_save_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( 'page' == get_post_type() && ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		do_action( 'pys_superpack_meta_box_save', $post_id, $_POST );
		do_action( 'pys_superpack_meta_box_save_' . $post->post_type, $post_id, $_POST );

	}

    /** Filter pixel id depend on selected lang in WPML plugin
     *
     * @param $pixel
     * @param $ids
     * @return array
     */

	function filterIds($pixel,$ids) {
        if(count($ids)  == 0 || is_admin()) {
            return $ids;
        }



        $filtered = array();
        $langSettings = (array)$pixel->getOption("pixel_lang");

        if(!$langSettings || count($langSettings) == 0 || $langSettings[0] == "") return  $ids;

        $current_lang_code = apply_filters( 'wpml_current_language', NULL );


        for ($i = 0;$i<count($ids);$i++) {
            if(count($langSettings) > $i) {
                if($langSettings[$i] != "empty") { // not add if lang settings is empty
                    $activeLangArray = explode("_",$langSettings[$i]);

                    if(in_array($current_lang_code,$activeLangArray)) {
                        $filtered[] = $ids[$i]; // add if current lang fits settings
                    } else {
                       // error_log("lang not in array");
                    }
                } else {
                    //error_log("lang is empty");
                }
            } else {
                $filtered[] = $ids[$i]; // add to filtered if dont have settings
            }
        }
        return $filtered;
    }

	function filter_facebook_ids($ids) {
        return $this->filterIds(Facebook(),$ids);
    }

    function filter_google_ads_ids($ids) {
        return $this->filterIds(Ads(),$ids);
    }

    function filter_ga_ids($ids) {
        return $this->filterIds(GA(),$ids);
    }

}

/**
 * @return SuperPack
 */
function SuperPack() {
	return SuperPack::instance();
}

SuperPack();