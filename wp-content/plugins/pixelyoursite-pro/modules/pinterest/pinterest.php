<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Pinterest extends Settings implements Pixel {

	private static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;

	}

	public function __construct() {
        add_action( 'pys_admin_pixel_ids', array( $this, 'renderPixelIdField' ) );
	}
	
	public function enabled() {
		return false;
	}
	
	public function configured() {
		return false;
	}
	
	public function getPixelIDs() {
		return array();
	}
	
	public function getPixelOptions() {
	    return array();
    }
    
    public function getEventData( $eventType, $args = null ) {
	    return false;
    }
    public function addParamsToEvent(&$event) {
        return false;
    }
	public function outputNoScriptEvents() {}

	public function render_switcher_input( $key, $collapse = false, $disabled = false ) {

		$attr_name = "pys[pinterest][$key]";
		$attr_id = 'pys_pinterest_' . $key;

		?>

		<div class="custom-switch disabled">
			<input type="checkbox" name="<?php esc_attr_e( $attr_name ); ?>" value="1" disabled="disabled"
			       id="<?php esc_attr_e( $attr_id ); ?>" class="custom-switch-input">
			<label class="custom-switch-btn" for="<?php esc_attr_e( $attr_id ); ?>"></label>
		</div>

		<?php
	}

	public function renderCustomEventOptions( $event ) {}
 
	public function renderAddonNotice() {
	    echo '&nbsp;<a href="https://www.pixelyoursite.com/pinterest-tag" target="_blank" class="badge badge-pill badge-secondary">Requires paid add-on</a>';
    }

    public function renderPixelIdField() {
        ?>

        <div class="row align-items-center">
            <div class="col-2 py-2">
                <img class="tag-logo" src="<?php echo PYS_URL; ?>/dist/images/pinterest-square-small.png">
            </div>
            <div class="col-10">
                <h4 class="label">Add the Pinterest tag with our <a href="https://www.pixelyoursite.com/pinterest-tag?utm_source=pixelyoursite-pro-plugin&utm_medium=plugin&utm_campaign=pro-plugin-pinterest" target="_blank">Paid add-on.</a></h4>
            </div>
        </div>
        <hr>

        <?php
    }
}

/**
 * @return Pinterest
 */
function Pinterest() {
	return Pinterest::instance();
}

Pinterest();