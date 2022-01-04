<?php
/**
 * Admin View: Shipping table
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tbody>
	<tr valign="top">
		<td class="forminp">
			<table id="shipping_rules" class="shippingrows widefat" cellspacing="0">
				<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column">
							<label class="screen-reader-text" for="cb-select-all"><?php _e( 'Select all', 'wc-ss' ); ?></label>
							<input id="cb-select-all" type="checkbox">
						</td>
						<th class="shipping_class"><?php _e( 'Shipping class', 'wc-ss' ) ?></th>
						<th class="shipping_class"><?php _e( 'Condition', 'wc-ss' ) ?></th>
						<th class="shipping_class"><?php _e( 'Range [min] and [max]', 'wc-ss' ) ?><a class="tips" data-tip="<?php _e( "Enter here the range of values you want to use for the shipping rule. The comparison is made for values greater than [min] and less than or equal to [max].", 'wc-ss' ); ?>"> [?]</a></th>
						<th class="shipping_class"><?php _e( 'Cost', 'wc-ss' ) ?><a class="tips" data-tip="<?php _e( "You can add a percentage (without % symbol) or a fixed cost, excluding tax.", 'wc-ss' ); ?>"> [?]</a></th>
						<th class="shipping_class"><?php _e( 'Cost per additional unit' , 'wc-ss' ) ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="8">
							<a href="#" class="add rule button" id="<?php echo $this->id .'_add_button'; ?>"><?php _e( 'Add rule', 'wc-ss' ); ?></a>
							<a href="#" class="duplicate rule button" id="<?php echo $this->id .'_duplicate_button'; ?>"><?php _e( 'Duplicate rule', 'wc-ss' ); ?></a>
							<a href="#" class="delete rule button" id="<?php echo $this->id .'_delete_button'; ?>"><?php _e( 'Delete selected rule', 'wc-ss' ); ?></a>
						</th>
					</tr>
				</tfoot>
				<tbody class="price_table">
				<?php
				$j = -1;

				if( $this->table_rate ){ 
					foreach ( $this->table_rate as $key => $value ) {
						$j++;
				?>
							<tr class="flat_rate">
								<th class="check-column"><input type="checkbox" name="select" /></th>
								<td><select name="<?php echo esc_attr( $this->id .'[shipping_class]['. $j .']' ); ?>" class="shipping_class_list">
									<option value="no-class" <?php selected( $value[ 'shipping_class' ], 'no-class' ); ?>><?php _e( 'No Class', 'wc-ss' ); ?></option>
								<?php 
									if ( WC()->shipping->get_shipping_classes() ) {
									
										foreach ( WC()->shipping->get_shipping_classes() as $shipping_class ) {
											echo '<option value="' . esc_attr( $shipping_class->slug ) . '" '.selected( $value[ 'shipping_class' ], $shipping_class->slug , false).'>'.$shipping_class->name.'</option>';
										}
									} 
								?>
								</select></td>
								<td><select name="<?php echo esc_attr( $this->id .'[conditional]['. $j .']' ); ?>">
									<option value="1" <?php selected( $value[ 'conditional' ], 1 ); ?>><?php _e( 'Weight', 'wc-ss' ) ?></option>
									<option value="2" <?php selected( $value[ 'conditional' ], 2 ); ?>><?php _e( 'Price', 'wc-ss' ) ?></option>
									<option value="3" <?php selected( $value[ 'conditional' ], 3 ); ?>><?php _e( 'Item count', 'wc-ss' ) ?></option>
									<option value="4" <?php selected( $value[ 'conditional' ], 4 ); ?>><?php _e( 'Volume', 'wc-ss' ) ?></option>
								</select></td>
								<td>
									<div class="horizontal-inputs-wrap">
										<input type="text" name="<?php echo esc_attr( $this->id .'[range][min]['. $j .']' ); ?>" class="text wc_ss_input_decimal required" placeholder="<?php _e( "Min", "wc-ss" ); ?>" value="<?php echo esc_attr( $value[ 'range' ][ 'min' ] ); ?>">
										<input type="text" name="<?php echo esc_attr( $this->id .'[range][max]['. $j .']' ); ?>" class="text wc_ss_input_decimal required" placeholder="<?php _e( "Max", "wc-ss" ); ?>" value="<?php echo esc_attr( $value[ 'range' ][ 'max' ] ); ?>">
									</div>
								</td>
								<td><input type="text" class="wc_ss_input_decimal required" value="<?php echo esc_attr( $value[ 'cost' ] ); ?>" name="<?php echo esc_attr( $this->id .'[cost]['. $j .']' ); ?>" placeholder="0.00" size="6" /></td>
								<td><input type="text" class="wc_ss_input_decimal" value="<?php echo esc_attr( $value[ 'cost_per_additional_unit' ] ); ?>" name="<?php echo esc_attr( $this->id .'[cost_per_additional_unit]['. $j .']' ); ?>" placeholder="0.00" size="6"></td>
							</tr>
				<?php
					}					
				}
				?>
				</tbody>
			</table>
		</td>
	</tr>
</tbody>