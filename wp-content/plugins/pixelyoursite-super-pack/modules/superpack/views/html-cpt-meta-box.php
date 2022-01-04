<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$enabled   = get_post_meta( $post->ID, '_pys_super_pack_cpt_enabled', true );
$page_url  = get_post_meta( $post->ID, '_pys_super_pack_cpt_url', true );
$show_cart = get_post_meta( $post->ID, '_pys_super_pack_cpt_cart', true );
$condition = get_post_meta( $post->ID, '_pys_super_pack_cpt_condition', true );

if ( ! $show_cart ) {
	$show_cart = 'hidden';
}

if ( ! $condition ) {
	$condition = 'only';
}

?>

<div>

    <p>
        <label>
            <input value="1" type="checkbox" name="pys_super_pack_cpt_enabled" <?php checked( $enabled ); ?>>
            <strong>Enable Custom Thank You Page</strong>
        </label>
    </p>

    <p class="super-pack-cpt" style="display: none;">
        <label for="pys_super_pack_cpt_url"><strong>Custom Thank You Page URL</strong></label>
        <input value="<?php esc_attr_e( $page_url ); ?>" type="url" name="pys_super_pack_cpt_url"
               id="pys_super_pack_cpt_url"
               placeholder="Enter URL" style="width: 100%;">
    </p>

    <p class="super-pack-cpt" style="display: none;">
        <strong>Use Custom Thank You Page</strong>
        <br>

        <input type="radio" value="only" <?php checked( $condition, 'only' ); ?> name="pys_super_pack_cpt_condition"
               id="pys_super_pack_cpt_condition_only">
        <label for="pys_super_pack_cpt_condition_only">When <strong>only</strong> this product is in the cart</label>
        <br>

        <input type="radio" value="in_cart" <?php checked( $condition, 'in_cart' ); ?>
               name="pys_super_pack_cpt_condition"
               id="pys_super_pack_cpt_condition_in_cart">
        <label for="pys_super_pack_cpt_condition_in_cart">When this product is in the cart</label>
    </p>

    <p class="super-pack-cpt" style="display: none;">
        <strong>Custom Thank You Page Order Details</strong>
        <br>

        <input type="radio" value="hidden" <?php checked( $show_cart, 'hidden' ); ?> name="pys_super_pack_cpt_cart"
               id="pys_super_pack_cpt_cart_hidden">
        <label for="pys_super_pack_cpt_cart_hidden">Hidden</label>
        <br>

        <input type="radio" value="after" <?php checked( $show_cart, 'after' ); ?> name="pys_super_pack_cpt_cart"
               id="pys_super_pack_cpt_cart_after">
        <label for="pys_super_pack_cpt_cart_after">After page content</label>
        <br>

        <input type="radio" value="before" <?php checked( $show_cart, 'before' ); ?> name="pys_super_pack_cpt_cart"
               id="pys_super_pack_cpt_cart_before">
        <label for="pys_super_pack_cpt_cart_before">Before page content</label>
    </p>

</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {

        toggle_ctp_controls();

        $('input[name="pys_super_pack_cpt_enabled"]').change(function () {
            toggle_ctp_controls();
        });

        function toggle_ctp_controls() {

            var enabled = $('input[name="pys_super_pack_cpt_enabled"]:checked').val();

            if (enabled) {
                $('.super-pack-cpt').show('fast');
            } else {
                $('.super-pack-cpt').hide('fast');
            }

        }

    });
</script>