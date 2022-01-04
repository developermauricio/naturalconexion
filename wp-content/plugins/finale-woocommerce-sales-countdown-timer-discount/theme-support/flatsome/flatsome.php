<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	ob_start();
	?>
    <style>
        .woocommerce-messages .woocommerce-info {
            margin-left: auto;
            margin-right: auto;
            color: inherit
        }

        .woocommerce-messages .woocommerce-info a.button.wc-forward {
            float: left
        }
    </style>
	<?php

	echo ob_get_clean();
}, 45 );
