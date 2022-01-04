<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

global $post;

?>

<?php if ( PixelYourSite\Facebook()->enabled() ) : ?>
    <?php $value = get_post_meta( $post->ID, '_pys_super_pack_remove_pixel', true ); ?>
    <p>
        <label>
            <input value="1" type="checkbox" name="pys_super_pack_remove_pixel" <?php checked( $value ); ?>>
            <strong>Remove Facebook pixel on this <?php echo get_post_type(); ?></strong>
        </label>
    </p>
<?php endif; ?>

<?php if ( PixelYourSite\GA()->enabled() ) : ?>
	<?php $value = get_post_meta( $post->ID, '_pys_super_pack_remove_ga_pixel', true ); ?>
	<p>
		<label>
			<input value="1" type="checkbox" name="pys_super_pack_remove_ga_pixel" <?php checked( $value ); ?>>
			<strong>Remove Google Analytics on this <?php echo get_post_type(); ?></strong>
		</label>
	</p>
<?php endif; ?>

<?php if ( PixelYourSite\Pinterest()->enabled() ) : ?>
	<?php $value = get_post_meta( $post->ID, '_pys_super_pack_remove_pinterest_pixel', true ); ?>
    <p>
        <label>
            <input value="1" type="checkbox" name="pys_super_pack_remove_pinterest_pixel" <?php checked( $value ); ?>>
            <strong>Remove Pinterest pixel on this <?php echo get_post_type(); ?></strong>
        </label>
    </p>
<?php endif; ?>