<?php
/**
 * Admin View: Product Settings
 */

if ( ! defined( 'YITH_WFBT' ) ) {
	exit;
} // Exit if accessed directly

?>

<div id="yith_wfbt_data_option" class="panel woocommerce_options_panel">

	<?php foreach ( $options as $group => $fields ): ?>
		<div class="options_group <?php echo ! empty( $fields['class'] ) ? esc_attr( $fields['class'] ) : ''; ?>">
			<?php foreach ( $fields as $field_key => $field ):

				do_action( 'yith_wfbt_product_panel_before_field_' . $field_key, $field );

				if ( ! is_array( $field ) ) {
					continue;
				}
				// build data if any
				$data = '';
				if ( isset( $field['data'] ) ) {
					foreach ( $field['data'] as $key => $value ) {
						$data .= ' data-' . $key . '="' . $value . '"';
					}
				}
				// build attr if any
				$attr = '';
				if ( isset( $field['attr'] ) ) {
					foreach ( $field['attr'] as $key => $value ) {
						$attr .= ' ' . $key . '="' . $value . '"';
					}
				}

				$desc  = ! empty( $field['desc'] ) ? esc_attr( $field['desc'] ) : '';
				$class = ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : '';

				$value = $metas[ $field_key ];
				if ( $class == 'wc_input_price' ) {
					$value = wc_format_localized_price( $metas[ $field_key ] );
				}
				?>
				<p class="form-field" <?php echo $data; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>>
					<label
						for="<?php echo esc_attr( $field['name'] ) ?>"><?php echo esc_html( $field['label'] ); ?></label>
					<?php
					switch ( $field['type'] ) :

						case 'variation_select': ?>
							<select id="<?php echo esc_attr( $field['name'] ) ?>"
								name="<?php echo esc_attr( $field['name'] ) ?>" <?php echo $attr ?>>
								<?php
								$variations = YITH_WFBT_Admin()->get_variations( $product_id );
								foreach ( $variations as $variation ) :
									// store var id
									$to_exclude[] = $variation['id'];
									?>
									<option value="<?php echo esc_attr( $variation['id'] ); ?>" <?php selected( $variation['id'], $value ) ?>><?php echo esc_html( $variation['name'] ); ?></option>
								<?php
								endforeach;
								?>
							</select>
							<?php break;

						case 'product_select':

							$product_ids = array_filter( array_map( 'absint', (array) $value ) );
							$json_ids = array();

							foreach ( $product_ids as $product_id ) {
								$product = wc_get_product( $product_id );
								if ( is_object( $product ) ) {
									$json_ids[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
								}
							}

							yit_add_select2_fields( array(
								'class'             => 'wc-product-search',
								'style'             => 'width: 50%;',
								'id'                => 'yith_wfbt_ids',
								'name'              => 'yith_wfbt_ids',
								'data-placeholder'  => __( 'Search for a product&hellip;', 'yith-woocommerce-frequently-bought-together' ),
								'data-multiple'     => true,
								'data-action'       => 'yith_ajax_search_product',
								'data-selected'     => $json_ids,
								'value'             => implode( ',', array_keys( $json_ids ) ),
								'custom-attributes' => array(
									'data-exclude' => implode( ',', $to_exclude ),
								),
							) );
							break;

						case 'select': ?>
							<select id="<?php echo esc_attr( $field['name'] ) ?>"
								name="<?php echo esc_attr( $field['name'] ) ?>" <?php echo $attr ?>>
								<?php foreach ( $field['options'] as $option_key => $option_name ) : ?>
									<option
										value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key, $value ); ?>><?php echo esc_html( $option_name ); ?></option>
								<?php endforeach; ?>
							</select>
							<?php break;

						case 'radio': ?>
							<?php foreach ( $field['options'] as $option_key => $option_name ) : ?>
								<label for="<?php echo esc_attr( $field['name'] . '-' . $option_key ) ?>" class="radio-label">
									<input type="radio"
										id="<?php echo esc_attr( $field['name'] . '-' . $option_key ) ?>"
										name="<?php echo esc_attr( $field['name'] ) ?>"
										value="<?php echo esc_attr( $option_key ); ?>" <?php checked( $option_key, $value ); ?>>
									<span><?php echo esc_html( $option_name ); ?></span></label><br>
							<?php endforeach;
							break;

						case 'textarea' : ?>
							<textarea id="<?php echo esc_attr( $field['name'] ) ?>"
								name="<?php echo esc_attr( $field['name'] ) ?>" <?php echo $attr ?>><?php echo esc_html( $value ); ?></textarea>
							<?php break;

						case 'checkbox': // this is a workaround to use plugin fw onoff keeping the WooCommerce html.?>
							<span class="yith-plugin-ui">
								<span class="yith-plugin-fw-onoff-container">
    								<input type="checkbox" id="<?php echo esc_attr( $field['name'] ) ?>"
											name="<?php echo esc_attr( $field['name'] ) ?>"
											value="yes" <?php checked( 'yes', $value ) ?> <?php echo $attr ?> class="on_off">
    								<span class="yith-plugin-fw-onoff"
											data-text-on="<?php echo esc_attr_x( 'YES', 'YES/NO button: use MAX 3 characters!', 'yith-plugin-fw' ); ?>"
											data-text-off="<?php echo esc_attr_x( 'NO', 'YES/NO button: use MAX 3 characters!', 'yith-plugin-fw' ); ?>"></span>
    							</span>
							</span>
							<?php break;

						default: ?>
							<input type="<?php echo esc_attr( $field['type'] ) ?>" class="<?php echo esc_attr( $class ); ?>"
								id="<?php echo esc_attr( $field['name'] ) ?>"
								name="<?php echo esc_attr( $field['name'] ) ?>"
								value="<?php echo esc_attr( $value ) ?>" <?php echo $attr ?>/>
							<?php break;
					endswitch; ?>

					<?php if( ! empty( $desc ) && 'checkbox' !== $field['type'] ) : ?>
						<span class="desc"><?php echo esc_html( $desc ); ?></span>
					<?php endif; ?>
				</p>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
</div>