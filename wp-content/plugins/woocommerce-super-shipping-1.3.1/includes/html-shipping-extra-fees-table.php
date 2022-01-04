<?php
/**
 * Admin View: Extra fees shipping table
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tbody>
	<tr>
		<td class="forminp">
			<table id="shipping_extra_fees"  class="shippingrows widefat" cellspacing="0">
				<thead>
					<tr>
						<th class="manage-column column-cb check-column"><input id="cb-select-all" type="checkbox"></th>
						<th><?php _e( 'Fee Label', 'wc-ss' ) ?></th>
						<th><?php _e( 'Fee', 'wc-ss' ) ?></th>
					</tr>
				</thead>
				<tbody>
					<?php 
					if ( $this->shipping_extra_fees ) {
						foreach ( $this->shipping_extra_fees as $key => $fee ) { ?>
					<tr>
						<th class="check-column"><input type="checkbox" name="select" /></th>
						<td><input type="text" size="20" value="<?php echo esc_attr( $fee[ 'label' ] ); ?>" name="<?php echo esc_attr( $this->id .'_extra_fees['. $key .'][label]' ); ?>" placeholder="<?php echo __( 'ie: My fee name', 'wc-ss' ); ?>"/></td>
						<td><input type="text" class="wc_ss_input_decimal" value="<?php echo esc_attr( $fee[ 'amount' ] ); ?>" name="<?php echo esc_attr( $this->id .'_extra_fees['. $key .'][amount]' ); ?>" placeholder="1.00" size="4" /></td>
					</tr>
					<?php
						} 
					} ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4">
							<a href="#" class="add button" onclick="add_new_shipping_extra_fee_row(); return false;" id="<?php echo $this->id .'_extra_fees'.'_add_button'; ?>"><?php _e( 'Add New Fee', 'wc-ss' ); ?></a>
							<a href="#" class="remove button" onclick="remove_shipping_extra_fee_row(); return false;"><?php _e( 'Delete selected fees', 'wc-ss' ); ?></a>
						</th>
					</tr>
				</tfoot>
			</table>
		</td>
	</tr>
</tbody>
			
<script type="text/javascript">
	
	//Adding price per weight row
	function add_new_shipping_extra_fee_row() {
	
		var html_row = '';
		var size = jQuery('table#shipping_extra_fees tbody tr').size();

		if ( size == 0 ) {

			html_row = '<tr class="alternate">';
		} else {

			if ( jQuery('table#shipping_extra_fees tbody tr').last().hasClass( 'alternate' ) ) {
				
				html_row = '<tr>';
			} else {

				html_row = '<tr class="alternate">';
			}
		};

		html_row += '<th class="check-column"><input type="checkbox" name="select" /></th>\
					<td><input type="text" size="20" value="" name="<?php echo $this->id .'_extra_fees' ?>['+ size +'][label]" placeholder="<?php echo __( 'ie: My fee name', 'wc-ss' ); ?>" /></td>\
					<td><input type="text" class="wc_ss_input_decimal" value="" name="<?php echo $this->id .'_extra_fees' ?>['+ size +'][amount]" placeholder="1" size="4" /></td>\
				</tr>';
	
		jQuery( html_row ).appendTo( 'table#shipping_extra_fees tbody' );
	
		return false;
	}
	
	// Remove price per weight row
	function remove_shipping_extra_fee_row() {
		var size = jQuery('table#shipping_extra_fees tbody tr th.check-column input:checked').size();
		if( size > 0 ){
			var answer = confirm("<?php _e( 'Delete the selected shipping classes priority?', 'wc-ss' ); ?>")
			if (answer) {
				jQuery('table#shipping_extra_fees tbody tr th.check-column input:checked').each(function(i, el){
					jQuery(el).closest('tr').remove();
				});
			}
		}
		return false;
	}
</script>