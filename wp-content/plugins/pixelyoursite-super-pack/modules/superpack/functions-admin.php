<?php

namespace PixelYourSite\SuperPack;

use PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function adminSecondaryNavTabs( $tabs ) {
	
	$tabs['superpack_settings'] = array(
		'url'  => PixelYourSite\buildAdminUrl( 'pixelyoursite', 'superpack_settings' ),
		'name' => 'Super Pack Settings',
	);
	
	return $tabs;

}

function renderSettingsPage() {

	/** @noinspection PhpIncludeInspection */
    include PYS_SUPER_PACK_PATH . '/modules/superpack/views/html-settings.php';

}

function adminNoticePysProNotActive() {
	
	if ( current_user_can( 'manage_options' ) ) : ?>
	
        <div class="notice notice-error">
            <p>PixelYourSite Super Pack Add-on needs PixelYourSite PRO in order to work. Activate it now.</p>
        </div>
	
	<?php endif;
	
}

function adminNoticePysProOutdated() {
	
	if ( current_user_can( 'manage_options' ) ) : ?>
	
        <div class="notice notice-error">
            <p>PixelYourSite Super Pack Add-on requires PixelYourSite PRO version <?php echo
                PYS_SUPER_PACK_PRO_MIN_VERSION; ?> or newer. Please, update to latest version.</p>
        </div>
	
	<?php endif;
	
}