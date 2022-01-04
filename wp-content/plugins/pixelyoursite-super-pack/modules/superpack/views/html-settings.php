<?php

namespace PixelYourSite;

use PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<h2 class="section-title">Super Pack Settings</h2>

<!-- General -->
<div class="card card-static">
    <div class="card-header">
        General
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <?php SuperPack()->render_switcher_input( 'enabled' ); ?>
                <h4 class="switcher-label">Enable Super Pack</h4>
            </div>
        </div>
    </div>
</div>

<!-- Additional Pixel IDs -->
<div class="card card-static">
    <div class="card-header">
        Additional Pixel IDs
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <p>Add additional Facebook, Google Analytics and Pinterest pixel IDs on the same site.</p>
				<?php SuperPack()->render_switcher_input( 'additional_ids_enabled' ); ?>
                <h4 class="switcher-label">Enable additional pixel IDs</h4>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Params -->
<div class="card card-static">
    <div class="card-header">
        Dynamic Parameters for Events
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <p>Use page title, post ID, category or tags as your dynamic events parameters.</p>
				<?php SuperPack()->render_switcher_input( 'dynamic_params_enabled' ); ?>
                <h4 class="switcher-label">Enable dynamic params</h4>
            </div>
        </div>
    </div>
</div>

<!-- Custom Thank You Page -->
<div class="card card-static">
    <div class="card-header">
        Custom Thank You Pages
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <p>Define custom thank you pages (general or for a particular product) and fire the
                    Facebook pixel on it.</p>
				<?php SuperPack()->render_switcher_input( 'custom_thank_you_page_enabled', true ); ?>
                <h4 class="switcher-label">Enable Custom Thank You Pages</h4>
            </div>
        </div>

        <div <?php renderCollapseTargetAttributes( 'custom_thank_you_page_enabled', SuperPack() ); ?>>
            <?php if ( isWooCommerceActive() ) : ?>
                <?php include 'html-ctp-woo.php'; ?>
            <?php endif; ?>
        
            <?php if ( isEddActive() ) : ?>
                <?php include 'html-ctp-edd.php'; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Remove Pixel -->
<div class="card card-static">
    <div class="card-header">
        Remove Pixel from Pages
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <p>Remove Facebook, Google Analytics or Pinterest pixels from a particular page or post.</p>
				<?php SuperPack()->render_switcher_input( 'remove_pixel_enabled' ); ?>
                <h4 class="switcher-label">Enable remove pixel from pages</h4>
            </div>
        </div>
    </div>
</div>

<!-- AMP -->
<div class="card card-static">
    <div class="card-header">
        AMP Support
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col">
                <p>Fire Facebook, Google Analytics or Pinterest pixels on AMP pages.</p>
				<?php SuperPack()->render_switcher_input( 'amp_enabled' ); ?>
                <h4 class="switcher-label">Enable AMP integration</h4>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <?php if ( SuperPack\isAMPactivated() ) : ?>
                    <div class="indicator">ON</div>
                <?php else : ?>
                    <div class="indicator indicator-off">OFF</div>
                <?php endif; ?>
                <h4 class="indicator-label">AMP by <a href="https://wordpress.org/plugins/amp/"
                    target="_blank">WordPress.com VIP, XWP, Google, and contributors</a></h4>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
	            <?php if ( SuperPack\isAMPforWPactivated() ) : ?>
                    <div class="indicator">ON</div>
	            <?php else : ?>
                    <div class="indicator indicator-off">OFF</div>
	            <?php endif; ?>
                <h4 class="indicator-label">Accelerated Mobile Pages by <a href="https://wordpress.org/plugins/accelerated-mobile-pages/"
                    target="_blank">Ahmed Kaludi, Mohammed Kaludi</a></h4>
            </div>
        </div>
    </div>
</div>

<hr>
<div class="row justify-content-center">
    <div class="col-4">
        <button class="btn btn-block btn-sm btn-save">Save Settings</button>
    </div>
</div>
