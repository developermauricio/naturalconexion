<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 20-06-19
 * Time: 9:47 AM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php wp_nonce_field( 'wacv-filter', '_wpnonce', false ); ?>
<div class="wacv-select-time-group">
    <div class="wacv-select-time-1">
        <select name="wacv_time_range" class="wacv-select-time-report">
			<?php
			$options = array(
				"today"     => esc_html__( 'Today', 'woo-abandoned-cart-recovery' ),
				"yesterday" => esc_html__( 'Yesterday', 'woo-abandoned-cart-recovery' ),
				"30days"    => esc_html__( '30 days', 'woo-abandoned-cart-recovery' ),
				"90days"    => esc_html__( '90 days', 'woo-abandoned-cart-recovery' ),
				"365days"   => esc_html__( '365 days', 'woo-abandoned-cart-recovery' ),
				"custom"    => esc_html__( 'Custom', 'woo-abandoned-cart-recovery' ),
			);
			foreach ( $options as $key => $value ) {
				$select = $selected == $key ? 'selected' : '';
				?>
                <option value="<?php echo esc_attr( $key ) ?>" <?php echo esc_attr( $select ) ?>><?php echo esc_html( $value ) ?></option>
				<?php
			}
			?>
        </select>
    </div>
    <div class="wacv-custom-time-range vi-ui segment">
        <div class="wacv-custom-time-range-flex-layer">
            <div class="vi-ui input small">
                <input type="date" class="wacv-date-from" name="wacv_start" value="<?php echo date_i18n( 'Y-m-d', intval( $start ) ) ?>">
            </div>
            <div class="vi-ui input small">
                <input type="date" class="wacv-date-to" name="wacv_end" value="<?php echo date_i18n( 'Y-m-d', intval( $end ) ) ?>">
            </div>
            <button type="<?php echo esc_attr( $button ) ?>" value="filter" name="action" class="wacv-view-reports vi-ui button primary tiny">
				<?php esc_html_e( 'View', 'woo-abandoned-cart-recovery' ) ?></button>
        </div>
    </div>
</div>
