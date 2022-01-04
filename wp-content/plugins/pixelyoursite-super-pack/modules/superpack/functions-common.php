<?php

namespace PixelYourSite\SuperPack;

use PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if WPML plugin installed and activated.
 *
 * @return bool
 */
function isWPMLActive() {

    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' );
}

function isPysProActive() {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	return is_plugin_active( 'pixelyoursite-pro/pixelyoursite-pro.php' );
	
}

function pysProVersionIsCompatible() {
 
	if ( ! function_exists( 'get_plugin_data' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	$data = get_plugin_data( WP_PLUGIN_DIR   . '/pixelyoursite-pro/pixelyoursite-pro.php', false, false );

	return version_compare( $data['Version'], PYS_SUPER_PACK_PRO_MIN_VERSION, '>=' );
	
}

function printLangList($activeLang,$languageCodes,$pixelSlag,$isTemplate = false) {

?>
<div>
    <div class="mb-2"><strong>WPML Detected.</strong> Fire this pixel for the following languages:</div>
    <?php

    if($activeLang != "empty") {
        $activeLangArray = explode("_",$activeLang);
    } else {
        $activeLangArray = array();
    }
    ?>
    <input class="pixel_lang" hidden name="pys[<?=$pixelSlag?>][pixel_lang][]"  <?=$isTemplate? 'data-value="'.$activeLang.'"' : 'value="'.$activeLang.'"' ?>/>
    <?php foreach ($languageCodes as $code) :?>
        <label class="custom-control custom-checkbox pixel_lang_check_box">
            <input type="checkbox" value="<?=$code?>" class="custom-control-input" <?=in_array($code,$activeLangArray) ? "checked":""?>>
            <span class="custom-control-indicator"></span>
            <span class="custom-control-description"><?=$code?></span>
        </label>
    <?php endforeach; ?>
</div>
<?php
}