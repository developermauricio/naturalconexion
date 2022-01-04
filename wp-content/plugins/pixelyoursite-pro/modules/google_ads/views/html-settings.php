<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<h2 class="section-title">Google Ads Settings</h2>

<!-- General -->
<div class="card card-static">
	<div class="card-header">
		General
	</div>
	<div class="card-body">
        <div class="row mb-4">
            <div class="col">
                <?php Ads()->render_switcher_input( 'enabled' ); ?>
                <h4 class="switcher-label">Enable Google Ads</h4>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php Ads()->render_switcher_input( 'page_view_post_enabled' ); ?>
                <h4 class="switcher-label">Fire the page_view_event on posts</h4>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php Ads()->render_switcher_input( 'page_view_page_enabled' ); ?>
                <h4 class="switcher-label">Fire the page_view event on pages</h4>
            </div>
        </div>
        <div class="row">
            <div class="col mt-2">
            <label>Fire the page_view event on custom post type:</label>
            <input type="checkbox" class="custom-control-input" name="pys[google_ads][page_view_custom_post_enabled][-1]" value="0" checked />
            <div class="custom-controls-stacked ml-2">
                <?php
                $args = array(
                        'public' => true
                );
                $exclude = array("post","page");
                if(isWooCommerceActive()){
                    $exclude[] = "product";
                }
                if(isEddActive()){
                    $exclude[] = "download";
                }
                $post_types = get_post_types( $args, 'objects' );
                foreach ( $post_types as $type) {
                    if(in_array($type->name,$exclude)) continue;
                    ?><div class="form-inline"><?php
                    Ads()->render_switcher_input_array( 'page_view_custom_post_enabled', $type->name);
                    ?><h4 class="switcher-label"><?=$type->label?></h4></div><?php

                }
                ?>
            </div>
        </div>
        </div>
        <div class="row">
            <div class="col  form-inline">
                <label>google_business_vertical:</label>
                <?php Ads()->render_text_input( 'page_view_business_vertical','google_business_vertical' ); ?>
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