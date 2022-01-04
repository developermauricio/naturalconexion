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
 * @copyright   Copyright (c) 2012-2020, SkyVerge, Inc. (info@skyverge.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginFramework\v5_10_6 as Framework;

/**
 * Checkbox input field HTML
 *
 * @type \WC_Custom_Order_Field $field The field object
 * @type string $id The input field ID, formatted & escaped
 * @type string $name The input field name, formatted & escaped
 *
 * @since 1.6.1
 * @version 1.11.0
 */

$options = $field->get_options();

if ( ! empty( $options ) ) :

	$option_count = 0;

	foreach ( $options as $option ) :

		?>
		<label for="<?php echo $id . '-' . $option_count; ?>">
			<input
				type="checkbox"
				name="<?php echo $name; ?>[]"
				id="<?php echo $id . '-' . $option_count; ?>"
				value="<?php echo esc_attr( $option['value'] ); ?>"
				<?php checked( $option['selected'], true ); ?>
			/>
			<?php echo esc_html( $option['label'] ); ?>
		</label>
		<?php

		$option_count++;

	endforeach;

endif;
