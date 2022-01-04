<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="row mt-3">
    <div class="col">
        <h4>Easy Digital Downloads</h4>
        <p>You can set up a global Easy Digital Downloads Thank You Page here. If you need to,
	        you can also define Custom Thank You Pages for each product (edit the product and you will find this
	        option in the right side menu).</p>
    </div>
</div>
<div class="row">
    <div class="col col-offset-left">
		<?php SuperPack()->render_switcher_input( 'edd_custom_thank_you_page_global_enabled', true ); ?>
        <h4 class="switcher-label">Enable Easy Digital Downloads Global Thank You Page</h4>
    </div>
</div>
<div class="row">
    <div class="col col-offset-left">
        <div <?php renderCollapseTargetAttributes( 'edd_custom_thank_you_page_global_enabled', SuperPack() ); ?>>
            <div class="my-3">
                <label>Global Custom Page URL:</label>
				<?php SuperPack()->render_text_input( 'edd_custom_thank_you_page_global_url', 'Enter URL' ); ?>
            </div>
            <div>
                <label>Order Details:</label>
                <div class="custom-controls-stacked">
					<?php SuperPack()->render_radio_input( 'edd_custom_thank_you_page_global_cart', 'hidden', 'Hidden' ); ?>
					<?php SuperPack()->render_radio_input( 'edd_custom_thank_you_page_global_cart', 'after', 'After page content' ); ?>
					<?php SuperPack()->render_radio_input( 'edd_custom_thank_you_page_global_cart', 'before', 'Before page content' ); ?>
                </div>
            </div>
        </div>
    </div>
</div>