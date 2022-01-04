<?php

namespace PixelYourSite;

use PixelYourSite\GA\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<h2 class="section-title">Google Analytics Settings</h2>

<!-- General -->
<div class="card card-static">
	<div class="card-header">
		General
	</div>
	<div class="card-body">
        <div class="row mb-3">
            <div class="col">
                <?php GA()->render_switcher_input( 'enabled' ); ?>
                <h4 class="switcher-label">Enable Google Analytics</h4>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php GA()->render_switcher_input( 'enhance_link_attribution' ); ?>
                <h4 class="switcher-label">Enable Enhance Link Attribution</h4>
            </div>
        </div>


            <div class="row">
                <div class="col">
                    <?php GA()->render_switcher_input( 'disable_advertising_features' ); ?>
                    <h4 class="switcher-label">Disable all advertising features</h4>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <?php GA()->render_switcher_input( 'disable_advertising_personalization' ); ?>
                    <h4 class="switcher-label">Disable advertising personalization</h4>
                </div>
            </div>



        <div class="row">
            <div class="col">
				<?php GA()->render_switcher_input( 'anonimize_ip' ); ?>
                <h4 class="switcher-label">Anonimize IP</h4>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php GA()->render_switcher_input( 'track_user_id' ); ?>
                <h4 class="switcher-label">Track User-ID for logged-in users</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-11 col-offset-left">
                <div class="indicator">ON</div>
                <h4 class="indicator-label">Tracking Custom Dimensions</h4>
            </div>
            <div class="col-1">
		        <?php renderExternalHelpIcon( 'https://www.pixelyoursite.com/documentation/google-analytics-custom-dimensions' ); ?>
            </div>
        </div>
	</div>
</div>

<!-- Google Optimize -->
<div class="card card-static">
    <div class="card-header">
        Google Optimize
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col">
                <?php GA()->render_switcher_input( 'optimize_enabled' ); ?>
                <h4 class="switcher-label">Enable Google Optimize</h4>
            </div>
        </div>
        <div class="row mb-5">
            <div class="col">
                <?php GA()->render_text_input( 'optimize_id', 'Enter Optimize ID' ); ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <?php GA()->render_switcher_input( 'enable_anti_flicker' ); ?>
                <h4 class="switcher-label">Enable the anti-flicker snippet</h4>
            </div>
        </div>
        <div class="row">
            <div class="col form-inline">
                <label for="pys_ga_anti_flicker_timeout">Timeout:</label>
                <?php GA()->render_number_input( 'anti_flicker_timeout' ); ?>
                <label for="pys_ga_anti_flicker_timeout">milliseconds</label>
            </div>
        </div>
    </div>
</div>

<!-- Cross-Domain Tracking -->
<!-- @link: https://developers.google.com/analytics/devguides/collection/gtagjs/cross-domain -->
<div class="card card-static">
    <div class="card-header">
        Cross-Domain Tracking
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-11">
                <?php GA()->render_switcher_input( 'cross_domain_enabled' ); ?>
                <h4 class="switcher-label">Enable Cross-Domain Tracking</h4>
            </div>
            <div class="col-1">
                <?php renderPopoverButton( 'ga_cross_domain_tracking' ); ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col col-offset-left">
                <?php GA()->render_switcher_input( 'cross_domain_accept_incoming' ); ?>
                <h4 class="switcher-label">Accept incoming</h4>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-5 col-offset-left">
                <?php Helpers\renderCrossDomainDomain( 0 ); ?>
            </div>
        </div>

        <?php foreach ( GA()->getOption('cross_domain_domains') as $index => $domain ) : ?>

            <?php

            if ( $index === 0 ) {
                continue; // skip default ID
            }

            ?>

            <div class="row mt-3">
                <div class="col-5 col-offset-left">
                    <?php Helpers\renderCrossDomainDomain( $index ); ?>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-sm remove-row">
                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

        <?php endforeach; ?>

        <div class="row mt-3" id="pys_ga_cross_domain_domain" style="display: none;">
            <div class="col-5 col-offset-left">
                <input type="text" name="" id="" value="" placeholder="Enter domain" class="form-control">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-sm remove-row">
                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-5 col-offset-left">
                <button class="btn btn-sm btn-block btn-primary" type="button"
                        id="pys_ga_add_cross_domain_domain">
                    Add Extra Domain
                </button>
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

<script type="application/javascript">
    jQuery(document).ready(function ($) {

        $('#pys_ga_add_cross_domain_domain').click(function (e) {

            e.preventDefault();

            var $row = $('#pys_ga_cross_domain_domain').clone()
                .insertBefore('#pys_ga_cross_domain_domain')
                .attr('id', '')
                .css('display', 'flex');

            $('input[type="text"]', $row)
                .attr('name', 'pys[ga][cross_domain_domains][]');

        });

        $(document).on('click', '.remove-row', function () {
            $(this).closest('.row').remove();
        });

    });
</script>
