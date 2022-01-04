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
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-admin-custom-order-fields/ for more information.
 *
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2021, SkyVerge, Inc. (info@skyverge.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Renders visible custom order fields on the View Order page.
 *
 * @type \WC_Order $order the order object.
 * @type \WC_Custom_Order_Field[] $order_fields Array of order fields.
 *
 * @version 1.15.0
 * @since 1.5.0
 */

ob_start();

foreach ( $order_fields as $order_field ) :

	if ( $order_field->is_visible() && ( $value = $order_field->get_value_formatted() ) ) :

		?>
		<tr>
			<th><?php echo wp_kses_post( $order_field->label ); ?></th>
			<td><?php echo 'textarea' === $order_field->type ? wpautop( wp_kses_post( $value ) ) : wp_kses_post( $value ); ?></td>
		</tr>
		<?php

	endif;

endforeach;

$output = ob_get_clean();

if ( ! empty( $output ) ) :

	?>
	<header>
		<h2><?php esc_html_e( 'Additional Order Details', 'woocommerce-admin-custom-order-fields' ); ?></h2>
	</header>
	<table class="shop_table order-custom-fields">
		<?php echo $output; ?>
	</table>
	<?php

endif;
