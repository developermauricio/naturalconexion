<?php
/**
 * WooCommerce Admin Custom Order Fields
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Admin Custom Order Fields to newer
 * versions in the future. If you wish to customize WooCommerce Admin Custom Order Fields for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-admin-custom-order-fields/ for more information.
 *
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2020, SkyVerge, Inc. (info@skyverge.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_10_6 as Framework;

/**
 * View for a new field row
 *
 * @type int $index index row counter
 * @type int $field_id the field id
 * @type \WC_Custom_Order_Field $field the field
 * @type array $field_types associative array of field types
 * @type array $field_attributes associative array of field attributes
 *
 * @since 1.0
 * @version 1.11.0
 */
?>
<tr class="wc-custom-order-field">

	<td class="check-column">
		<input type="checkbox" />
		<input type="hidden"
		       name="wc-custom-order-field-id[<?php echo $index; ?>]"
		       value="<?php echo esc_attr( $field_id ); ?>" />
	</td>

	<td class="wc-custom-order-field-label">
		<input type="text"
		       name="wc-custom-order-field-label[<?php echo $index; ?>]"
		       value="<?php echo esc_attr( isset( $field->label ) ? $field->label : null ); ?>"
		       class="js-wc-custom-order-field-label" />
		<span class="wc-custom-order-field-id"><?php echo $field_id ? 'ID: ' .  $field_id : ''; ?></span>
	</td>

	<td class="wc-custom-order-field-type">
		<select name="wc-custom-order-field-type[<?php echo $index; ?>]"
		        class="js-wc-custom-order-field-type wc-enhanced-select"
		        style="width: 100px;">
			<?php foreach ( $field_types as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $field->type ) ? $field->type : null, $value );?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</td>

	<td class="wc-custom-order-field-description">
		<input type="text"
		       name="wc-custom-order-field-description[<?php echo $index; ?>]"
		       value="<?php echo esc_attr( isset( $field->description ) ? $field->description : null ); ?>"
		       class="js-wc-custom-order-field-description" />
	</td>

	<td class="wc-custom-order-field-default-values">
		<input type="text"
		       name="wc-custom-order-field-default-values[<?php echo $index; ?>]"
		       value="<?php echo esc_attr( isset( $field->default ) ? $field->default : null ); ?>"
		       class="js-wc-custom-order-field-default-values placeholder"
		       placeholder="<?php esc_attr_e( 'Pipe (|) separates options', 'woocommerce-admin-custom-order-fields' ); ?>" />
		<?php echo wc_help_tip( __( 'Use Pipe (|) to separate options and surround options with double stars (**) to set as a default.', 'woocommerce-admin-custom-order-fields' ) ); ?>
	</td>

	<td class="wc-custom-order-field-attributes">
		<select name="wc-custom-order-field-attributes[<?php echo $index; ?>][]"
		        class="js-wc-custom-order-field-attributes wc-enhanced-select"
		        multiple="multiple"
		        style="width: 250px;">
			<?php foreach ( $field_attributes as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $field->$value ) ? $field->$value : null );?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</td>

	<td class="js-wc-custom-order-field-draggable">
		<img src="<?php echo wc_admin_custom_order_fields()->get_plugin_url() ?>/assets/images/draggable-handle.png" />
	</td>

</tr>
