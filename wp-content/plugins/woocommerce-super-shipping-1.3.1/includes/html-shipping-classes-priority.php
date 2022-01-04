<?php
/**
 * Admin View: Shipping costs table rates
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tbody>
	<tr>
		<td class="forminp">
			<table id="shipping_classes_priority"  class="shippingrows widefat" cellspacing="0">
				<thead>
					<tr>
						<th class="manage-column column-cb check-column"><input id="cb-select-all" type="checkbox"></th>
						<th class="shipping_class"><?php _e( 'Shipping class', 'wc-ss' ) ?></th>
						<th class="shipping_class"><?php _e( 'Priority', 'wc-ss' ) ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ( $this->shipping_classes_priority ) {
						foreach ( $this->shipping_classes_priority as $key => $shipping_priority ) { ?>
					<tr>
						<th class="check-column"><input type="checkbox" name="select" /></th>
						<td><select name="<?php echo esc_attr( $this->id .'_classes_priority['. $key .'][shipping_class]' ); ?>">
							<option value="no-class" <?php selected( $shipping_priority[ 'shipping_class' ], 'no-class' ); ?>><?php _e( 'No Class', 'wc-ss' ); ?></option>
						<?php 
							if ( WC()->shipping->get_shipping_classes() ) {
							
								foreach ( WC()->shipping->get_shipping_classes() as $shipping_class ) {
									echo '<option value="' . esc_attr( $shipping_class->slug ) . '" '.selected( $shipping_priority[ 'shipping_class' ], $shipping_class->slug , false).'>'.$shipping_class->name.'</option>';
								}
							} 
						?>
						</select></td>
						<td><input type="number" step="1" min="1" max="999" value="<?php echo esc_attr( $shipping_priority[ 'priority' ] ); ?>" name="<?php echo esc_attr( $this->id .'_classes_priority['. $key .'][priority]' ); ?>" placeholder="1" size="4" /></td>
					</tr>
					<?php 
						}
					} ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4">
							<a href="#" class="add button" onclick="add_new_shipping_class_priority_row(); return false;" id="<?php echo $this->id .'_classes_priority_add_button'; ?>"><?php _e( 'Add New Priority', 'wc-ss' ); ?></a>
							<a href="#" class="remove button" onclick="remove_shipping_class_priority_row(); return false;"><?php _e( 'Delete selected priorities', 'wc-ss' ); ?></a>
						</th>
					</tr>
				</tfoot>
			</table>
		</td>
	</tr>
</tbody>
			
<script type="text/javascript">
	
	//Adding price per weight row
	function add_new_shipping_class_priority_row() {
	
		var html_options = '';
		var html_row = '';
		var shipping_classes = '<?php echo json_encode( WC()->shipping->get_shipping_classes() ); ?>';
		json_shipping_classes = jQuery.parseJSON( shipping_classes );
		jQuery( json_shipping_classes ).each( function( key, value ){
			html_options += '<option value="' + value.slug + '" >'+ value.name +'</option>';
		});

		var size = jQuery('table#shipping_classes_priority tbody tr').size();

		if ( size == 0 ) {

			html_row = '<tr class="alternate">';
		} else {

			if ( jQuery('table#shipping_classes_priority tbody tr').last().hasClass( 'alternate' ) ) {
				
				html_row = '<tr>';
			} else {

				html_row = '<tr class="alternate">';
			}
		};

		html_row += '<th class="check-column"><input type="checkbox" name="select" /></th>\
					<td><select name="<?php echo $this->id; ?>_classes_priority['+ size +'][shipping_class]">\
						<option value="no-class"><?php _e( 'No Class', 'wc-ss' ); ?></option>' + html_options + '</select>\
					</td>\
					<td><input type="number" step="1" min="1" max="999" value="" name="<?php echo $this->id; ?>_classes_priority['+ size +'][priority]" placeholder="1" size="4" /></td>\
				</tr>';
	
		jQuery( html_row ).appendTo( 'table#shipping_classes_priority tbody' );
	
		return false;
	}
	
	// Remove price per weight row
	function remove_shipping_class_priority_row() {
		var size = jQuery('table#shipping_classes_priority tbody tr th.check-column input:checked').size();
		if( size > 0 ){
			var answer = confirm("<?php _e( 'Delete the selected shipping classes priority?', 'wc-ss' ); ?>")
			if (answer) {
				jQuery('table#shipping_classes_priority tbody tr th.check-column input:checked').each(function(i, el){
					jQuery(el).closest('tr').remove();
				});
			}
		}
		return false;
	}
</script>