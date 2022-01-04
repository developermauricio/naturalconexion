<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

function renderFacebookPixelIDs() {
	
	if ( PixelYourSite\SuperPack()->getOption( 'enabled' ) && PixelYourSite\SuperPack()->getOption( 'additional_ids_enabled' ) ) {
	    if ( PixelYourSite\SuperPack()->getCoreCompatible() ) {
            /** @noinspection PhpIncludeInspection */
            include PYS_SUPER_PACK_PATH . '/modules/superpack/views/html-facebook-ids.php';
        }
	}
	
}

function renderGoogleAnalyticsPixelIDs() {
	
	if ( PixelYourSite\SuperPack()->getOption( 'enabled' ) && PixelYourSite\SuperPack()->getOption( 'additional_ids_enabled' ) ) {
        if ( PixelYourSite\SuperPack()->getCoreCompatible() ) {
            /** @noinspection PhpIncludeInspection */
            include PYS_SUPER_PACK_PATH . '/modules/superpack/views/html-ga-ids.php';
        }
	}
	
}

function renderGoogleAdsIDs() {
	
	if ( PixelYourSite\SuperPack()->getOption( 'enabled' ) && PixelYourSite\SuperPack()->getOption( 'additional_ids_enabled' ) ) {
        if ( PixelYourSite\SuperPack()->getCoreCompatible() ) {
            /** @noinspection PhpIncludeInspection */
            include PYS_SUPER_PACK_PATH . '/modules/superpack/views/html-google-ads-ids.php';
        }
	}
	
}
