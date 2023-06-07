<?php
/**
 * The library responsible for the UI of all ORION products.
 *
 * @link       https://orionorigin.com
 * @since      1.0.0
 * @author     ORION
 * @package    ORION
 */

 if ( ! class_exists( 'O_Utils' ) ) {
	/**
	 * O_Utils
	 */
	class O_Utils {

		/**
		 * Outputs the settings fields
		 *
		 * @param array $options Settings to output.
		 */
		public static function admin_fields( $options ) {
			global $o_row_templates;
			ob_start();
			foreach ( $options as $value ) {
				if ( ! isset( $value['type'] ) ) {
					continue;
				}
				if ( ! isset( $value['id'] ) ) {
					$value['id'] = '';
				}
				if ( ! isset( $value['name'] ) ) {
					$value['name'] = $value['id'];
				}
				if ( ! isset( $value['hierarchy'] ) ) {
					$value['hierarchy'] = array( $value['name'] );
				}
				if ( ! isset( $value['title'] ) ) {
					$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
				}
				if ( ! isset( $value['class'] ) ) {
					$value['class'] = '';
				}
				if ( ! isset( $value['row_class'] ) ) {
					$value['row_class'] = '';
				}
				if ( ! isset( $value['css'] ) ) {
					$value['css'] = '';
				}
				if ( ! isset( $value['row_css'] ) ) {
					$value['row_css'] = '';
				}
				if ( ! isset( $value['default'] ) ) {
					$value['default'] = '';
				}
				if ( ! isset( $value['desc'] ) ) {
					$value['desc'] = '';
				}
				if ( ! isset( $value['desc_tip'] ) ) {
					$value['desc_tip'] = false;
				}
				if ( ! isset( $value['ignore_desc_col'] ) ) {
					$value['ignore_desc_col'] = false;
				}
				if ( ! isset( $value['label_class'] ) ) {
					$value['label_class'] = '';
				}
				if ( ! isset( $value['placeholder'] ) ) {
					$value['placeholder'] = '';
				}
				$tip = '';
				if ( isset( $value['tip'] ) ) {
					$tip = "<span class='o-info' data-tooltip-title='" . $value['tip'] . "'></span>";
				}

				// Custom attribute handling.
				$custom_attributes = array();

				if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
					foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '=' . esc_attr( $attribute_value );
					}
				}

				// Attributes custom attribute handling.
				$options_custom_attributes = array();

				if ( ! empty( $value['options_custom_attributes'] ) && is_array( $value['options_custom_attributes'] ) ) {
					foreach ( $value['options_custom_attributes'] as $option_key => $option_attributes ) {
						foreach ( $option_attributes as $attribute => $attribute_value ) {
							$options_custom_attributes[ $option_key ][] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
						}
					}
				}

				$description = $value['desc'];

				if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
					$description = "<p style='margin-top:0'> $description </p>";
				} elseif ( $description ) {
					$description = "<span class='description'>$description</span>";
				}

				$post_id         = get_the_ID();
				$option_value    = '';
				$url_field_value = '';
				$raw_hierarchy   = self::explode_x( array( '[', ']' ), $value['name'] );
				$hierarchy       = array_filter( $raw_hierarchy );
				$section_types   = array( 'sectionbegin', 'sectionend' );
				$settings_table  = self::get_proper_value( $options[0], 'table', 'metas' );

				if ( ! in_array( $value['type'], $section_types, true ) & ! empty( $hierarchy ) ) {
					$root_key    = $hierarchy[0];
					$session_key = $root_key . "_$post_id";
					// We check if the meta is already stored in the session (db optimization) otherwise, we look for the original meta.
					$option_value = null;
					if ( isset( $_SESSION['o-data'] ) ) {
						$_SESSION['o-data'] = filter_input( INPUT_POST, 'o-data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
						$option_value       = self::get_proper_value( $_SESSION['o-data'], $session_key, false );
					}

					if ( ! $option_value ) {
						// Retrieve from the metas.
						if ( 'metas' === $settings_table ) {
							$option_value                       = get_post_meta( $post_id, $root_key, true );
							$_SESSION['o-data'][ $session_key ] = $option_value;
						} elseif ( 'options' === $settings_table ) { // Retrieve from the options.
							$option_value                       = get_option( $root_key );
							$_SESSION['o-data'][ $session_key ] = $option_value;
						} elseif ( 'custom' === $settings_table ) {
							$option_value                       = self::get_proper_value( $options[0], 'data', array() );
							$_SESSION['o-data'][ $session_key ] = $option_value;
						}
					}

					$session_key = $root_key . "_$post_id";
					$root_value  = self::get_proper_value( $_SESSION['o-data'], $session_key, false );
					if ( $root_key !== $value['name'] ) {
						$option_value = self::find_in_array_by_key( $root_value, $value['name'] );
					}
				}
				$col_class = self::get_proper_value( $value, 'col_class', '' );
				if ( ! $option_value && '0' !== $option_value ) {
					$option_value = $value['default'];
				}
				if ( ! in_array( $value['type'], $section_types, true ) && ! $value['ignore_desc_col'] ) {
					$descrip = $value['desc'];
					?>
				<tr style="<?php echo esc_attr( $value['row_css'] ); ?>" class="<?php echo esc_attr( $value['row_class'] ); ?>">
				<td class='label <?php echo esc_attr( $col_class ); ?>'>
					<?php
					echo wp_kses_post( $value['title'] . $tip );
					echo wp_kses_post( "<div class='o-desc'>" . $descrip . '</div>' );
					?>
				</td>
					<?php
				}

				if ( ! in_array( $value['type'], $section_types, true ) ) {
					if ( isset( $value['show_as_label'] ) ) {
						echo wp_kses_post( "<label class='" . $value['label_class'] . "'>" . $value['title'] . $tip );
					} else {
						echo wp_kses_post( "<td class='$col_class'>" );
					}
				}
				// Switch based on type.
				switch ( $value['type'] ) {
					case 'sectionbegin':
						// We start/reset the session.
						$_SESSION['o-data'] = array();
						?>
					<div class="o-wrap">
					<div id="<?php echo esc_attr( $value['id'] ); ?>" class="o-metabox-container">
					<div class='block-form'>
					<table class="wp-list-table widefat fixed pages o-root">
					<tbody>
						<?php
						break;
					case 'sectionend':
						?>
					</tbody>
					</table>
					</div>
					</div>
					</div>
						<?php
						break;
					// Standard text inputs and subtypes like 'number'.
					case 'text':
					case 'email':
					case 'number':
					case 'password':
						$type = $value['type'];
						?>
					<input
						name="<?php echo esc_attr( $value['name'] ); ?>"
						id="<?php echo esc_attr( $value['id'] ); ?>"
						type="<?php echo esc_attr( $type ); ?>"
						style="<?php echo esc_attr( $value['css'] ); ?>"
						value="<?php echo esc_attr( $option_value ); ?>"
						class="<?php echo esc_attr( $value['class'] ); ?>"
						placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
						<?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?> />

						<?php
						break;

					case 'color':
						$type            = 'text';
						$value['class'] .= 'o-color';
						?>
					<div class="o-color-container">
						<input name="<?php echo esc_attr( $value['name'] ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							type="<?php echo esc_attr( $type ); ?>"
							style="<?php echo esc_attr( $value['css'] ); ?>"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="<?php echo esc_attr( $value['class'] ); ?>"
							<?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?>
						/>
						<span class="o-color-btn"></span>
					</div>

						<?php
						break;

					case 'textarea':
						?>

					<textarea name="<?php echo esc_attr( $value['name'] ); ?>"
						id="<?php echo esc_attr( $value['id'] ); ?>"
						style="<?php echo esc_attr( $value['css'] ); ?>"
						class="<?php echo esc_attr( $value['class'] ); ?>"
						<?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?>>
						<?php echo esc_textarea( $option_value ); ?>
					</textarea>

						<?php
						break;

					case 'texteditor':
						wp_editor(
							$option_value,
							$value['id'],
							array(
								'wpautop'       => true,
								'media_buttons' => false,
								'textarea_name' => $value['name'],
								'textarea_rows' => 10,
							)
						);
						break;

					case 'select':
					case 'multiselect':
					case 'post-type':
						if ( 'post-type' === $value['type'] ) {
							// We make sure the limit is -1 if not set.
							$value['args']['posts_per_page'] = self::get_proper_value( $value['args'], 'posts_per_page', -1 );
							$posts                           = get_posts( $value['args'] );
							$posts_ids                       = self::get_proper_value( $value, 'first_value', array() );
							foreach ( $posts as $post ) {
								$posts_ids[ $post->ID ] = $post->post_title;
							}
							$value['options'] = $posts_ids;
						}

						$select_name     = esc_attr( $value['name'] );
						$multiselect_tag = '';
						if (
						'multiselect' === $value['type']
						||
						in_array( 'multiple="multiple"', $custom_attributes, true ) ) {
							$multiselect_tag = esc_attr( '[]' );
						}
						$select_id        = $value['id'];
						$select_css_style = $value['css'];
						$select_class     = $value['class'];
						$select_attr      = implode( ' ', $custom_attributes );

						$select_is_multiple = '';
						if ( 'multiselect' === $value['type'] ) {
							$select_is_multiple = 'multiple="multiple"';
						}
						?>
					<select name="<?php echo esc_attr( $select_name . $multiselect_tag ); ?>"
							id="<?php echo esc_attr( $select_id ); ?>"
							style="<?php echo esc_attr( $select_css_style ); ?>"
							class="<?php echo esc_attr( $select_class ); ?>"
						<?php
						echo esc_attr( $select_attr );
						echo ' ';
						echo esc_attr( $select_is_multiple );
						?>
					>
						<?php
						foreach ( $value['options'] as $key => $val ) {
							$option_custom_attributes = self::get_proper_value( $options_custom_attributes, $key, array() );
							?>
							<option
									value="<?php echo esc_attr( $key ); ?>"
								<?php echo esc_attr( implode( ' ', $option_custom_attributes ) ); ?> <?php
								if ( is_array( $option_value ) ) {
									// phpcs:disable
									selected( in_array( $key, $option_value, false ), true );
									// phpcs:enable
								} else {
									selected( $option_value, $key );
								}
								?>
							><?php echo esc_html( $val ); ?></option>
							<?php
						}
						?>
					</select>

						<?php
						break;
					case 'groupedselect':
						$select_name_array = 'multiselect' === $value['type'] ? esc_attr( '[]' ) : '';
						$select_multiple   = 'multiselect' === $value['type'] ? esc_attr( 'multiple="multiple"' ) : '';
						?>
					<select name="<?php echo esc_attr( $value['name'] ) . ' ' . esc_attr( $select_name_array ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>"
							style="<?php echo esc_attr( $value['css'] ); ?>"
							class="<?php echo esc_attr( $value['class'] ); ?>"
						<?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?>
						<?php echo esc_attr( $select_multiple ); ?>
					>
						<?php
						foreach ( $value['options'] as $group => $group_values ) {
							?>
							<optgroup label="<?php echo esc_attr( $group ); ?>">
								<?php
								foreach ( $group_values as $key => $val ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>"
										<?php
										if ( is_array( $option_value ) ) {
											selected( in_array( $key, $option_value, true ), true );
										} else {
											selected( $option_value, $key );
										}
										?>
									><?php echo esc_attr( $val ); ?></option>
									<?php
								}
								?>
							</optgroup>
							<?php
						}
						?>
					</select>
						<?php
						break;

					// Radio inputs.
					case 'radio':
						?>
					<fieldset>
						<ul>
							<?php
							foreach ( $value['options'] as $key => $val ) {
								?>
								<li>
									<label><input name="<?php echo esc_attr( $value['name'] ); ?>"
												value="<?php echo esc_attr( $key ); ?>" type="radio"
												style="<?php echo esc_attr( $value['css'] ); ?>"
												class="<?php echo esc_attr( $value['class'] ); ?>" <?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?> <?php checked( $key, $option_value ); ?> /> <?php echo esc_attr( $val ); ?>
									</label>
								</li>
								<?php
							}
							?>
						</ul>
					</fieldset>
						<?php
						break;

					case 'checkbox':
						$visbility_class = array();

						if ( ! isset( $value['hide_if_checked'] ) ) {
							$value['hide_if_checked'] = false;
						}
						if ( ! isset( $value['show_if_checked'] ) ) {
							$value['show_if_checked'] = false;
						}
						if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) {
							$visbility_class[] = 'hidden_option';
						}
						if ( 'option' === $value['hide_if_checked'] ) {
							$visbility_class[] = 'hide_options_if_checked';
						}
						if ( 'option' === $value['show_if_checked'] ) {
							$visbility_class[] = 'show_options_if_checked';
						}

						if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
							?>
						<fieldset>
							<?php
						} else {
							?>
						<fieldset class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
							<?php
						}

						if ( ! empty( $value['title'] ) ) {
							?>
						<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span>
						</legend>
							<?php
						}
						$cb_value = self::get_proper_value( $value, 'value', false );
						if ( ! $cb_value ) {
							$cb_value = self::get_proper_value( $value, 'default', 1 );
						}
						?>
						<label for="<?php echo esc_attr( $value['id'] ); ?>">
							<input name="<?php echo esc_attr( $value['name'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								value="<?php echo esc_attr( $cb_value ); ?>"
								<?php checked( $option_value, $cb_value ); ?>
								<?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?> />
							<?php echo wp_kses_post( $description ); ?>
						</label> <?php echo esc_attr( $tip ); ?>
						<?php
						if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) {
							?>
						</fieldset>

							<?php
						} else {
							?>
						</fieldset>
							<?php
						}
						break;

					case 'image':
						$set_btn_label    = self::get_proper_value( $value, 'set', 'Set image' );
						$remove_btn_label = self::get_proper_value( $value, 'remove', 'Remove image' );

						$img_src      = '';
						$root_img_src = self::get_proper_image_url( $option_value, false );
						if ( $root_img_src ) {
							$img_src = self::get_medias_root_url( "/$root_img_src" );
						}
						?>
						<div class="<?php echo esc_attr( $value['class'] ); ?>">
							<button class="button o-add-media"><?php echo esc_attr( $set_btn_label ); ?></button>
							<button class="button o-remove-media"><?php echo esc_attr( $remove_btn_label ); ?></button>
							<input type="hidden" name="<?php echo esc_attr( $value['name'] ); ?>"
								value="<?php echo esc_attr( $root_img_src ); ?>">
							<div class="media-preview">
								<?php
								if ( isset( $option_value ) ) {
									echo wp_kses_post( "<img src='$img_src'>" );
								}
								?>
							</div>
						</div>
						<?php
						break;
					case 'file':
						$set_btn_label    = self::get_proper_value( $value, 'set', 'Set file' );
						$remove_btn_label = self::get_proper_value( $value, 'remove', 'Remove file' );
						?>
						<div class="<?php echo esc_attr( $value['class'] ); ?>">
							<button class="button o-add-media"><?php echo esc_attr( $set_btn_label ); ?></button>
							<button class="button o-remove-media"><?php echo esc_attr( $remove_btn_label ); ?></button>
							<input type="hidden" name="<?php echo esc_attr( $value['name'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>">
							<div class="media-name">
								<?php
								if ( isset( $option_value ) ) {
									echo esc_attr( basename( $option_value ) );
								}
								?>
							</div>
						</div>
						<?php
						break;

					case 'date':
						$type            = 'date';
						$value['class'] .= 'o-date';
						?>
					<div class="o-date-container">
						<input name="<?php echo esc_attr( $value['name'] ); ?>"
							id="<?php echo esc_attr( $value['id'] ); ?>" type="<?php echo esc_attr( $type ); ?>"
							style="<?php echo esc_attr( $value['css'] ); ?>"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="<?php echo esc_attr( $value['class'] ); ?>" <?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?> />
					</div>

						<?php
						break;

					case 'repeatable-fields':
						if ( ! is_array( $option_value ) ) {
							$option_value = array();
						}
						$value['popup'] = self::get_proper_value( $value, 'popup', false );
						$lazy_mode      = self::get_proper_value( $value, 'lazyload', false );

						if ( $value['popup'] ) {
							add_thickbox();
							$modal_id            = uniqid( 'o-modal-' );
							$modal_trigger_class = '';
							// We don't need to do the lazy load for empty popups.
							if ( $lazy_mode && ! empty( $option_value ) ) {
								$modal_trigger_class = 'lazy-popup';
							}
							echo wp_kses_post( "<a class='o-modal-trigger button button-primary button-large $modal_trigger_class' data-toggle='o-modal' data-target='#$modal_id' data-modalid='$modal_id'>" . $value['popup_button'] . '</a>' );
							echo wp_kses_post(
								'<div class="omodal fade o-modal" id="' . $modal_id . '" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="omodal-dialog">
								<div class="omodal-content">
								<div class="omodal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h4 class="omodal-title" id="myModalLabel' . $modal_id . '">' . $value['popup_title'] . '</h4>
								</div>
								<div class="omodal-body">'
							);
							$value['class'] .= ' table-fixed-layout';
						}
						if ( $lazy_mode ) {
							echo wp_kses_post(
								"<img id='$modal_id-spinner' src='" . VPC_URL . "/admin/images/spinner.gif' style='display: none;'>"
							);
						}
						?>

					<table id="<?php echo esc_attr( $value['id'] ); ?>"
						class="<?php echo esc_attr( $value['class'] ); ?> widefat repeatable-fields-table">
						<thead>
						<tr>
							<?php
							foreach ( $value['fields'] as $field ) {
								$tip = '';
								if ( isset( $field['tip'] ) ) {
									$tip = "<span class='o-info' data-tooltip-title=\"" . $field['tip'] . '"></span>';
								}
								$col_class = self::get_proper_value( $field, 'col_class', '' );
								echo wp_kses_post( "<td class='$col_class'>" . $field['title'] . "$tip</td>" );
							}
							?>
							<td style="width: 20px;"></td>
						</tr>
						</thead>
						<tbody>
							<?php
							if ( $lazy_mode ) {
								$option_value = array();
							}
							self::get_repeatable_field_table_rows( $value, $option_value );
							$row_tpl                    = self::get_row_template( $value );
							$row_tpl                    = preg_replace( "/\r|\n/", '', $row_tpl );
							$row_tpl                    = preg_replace( '/\s+/', ' ', $row_tpl );
							$tpl_id                     = uniqid();
							$o_row_templates[ $tpl_id ] = $row_tpl;

							$add_label = self::get_proper_value( $value, 'add_btn_label', esc_html__( 'Add', 'cosh' ) );
							?>
						</tbody>
					</table>
					<a class="button mg-top add-rf-row"
					data-tpl="<?php echo esc_attr( $tpl_id ); ?>"><?php echo esc_attr( $add_label ); ?></a>
						<?php

						if ( $value['popup'] ) {
							echo wp_kses_post( '</div></div></div></div>' );
						}
						break;

					case 'groupedfields':
						?>

					<div class="o-wrap xl-gutter-8">
						<?php
						foreach ( $value['fields'] as $field ) {
							$field['show_as_label']   = true;
							$field['ignore_desc_col'] = true;
							$field['table']           = $settings_table;
							if ( ! isset( $field['label_class'] ) ) {
								$nb_cols              = count( $value['fields'] );
								$field['label_class'] = 'o-col xl-1-' . $nb_cols;
							}
							echo wp_kses_post( self::admin_fields( array( $field ) ) );
						}
						?>
					</div>

						<?php
						break;

					case 'custom':
						call_user_func( $value['callback'] );
						break;
					case 'button':
						?>
						<a id="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>"
						value="<?php echo esc_attr( $option_value ); ?>"
						class="<?php echo esc_attr( $value['class'] ); ?>" <?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?>><?php echo esc_attr( $value['title'] ); ?></a>
						<?php
						break;
					case 'google-font':
						self::get_google_fonts_selector( $option_value, esc_attr( $value['id'] ), esc_attr( $value['name'] ), esc_attr( $value['css'] ), esc_attr( $value['class'] ) );
						break;

					// Default: run an action.
					default:
						do_action( 'o_admin_field_' . $value['type'], $value );
						break;
				}
				if ( ! in_array( $value['type'], $section_types, true ) ) {
					if ( isset( $value['show_as_label'] ) ) {
						echo wp_kses_post( '</label>' );
					} else {
						echo wp_kses_post( '</td>' );
					}
				}
				if ( ! in_array( $value['type'], $section_types, true ) && ! $value['ignore_desc_col'] ) {
					?>
				</tr>
					<?php
				}
			}

			return ob_get_clean();
		}

		/**
		 * Get row template.
		 *
		 * @param mixed $value Value.
		 *
		 * @return string
		 */
		public static function get_row_template( $value ) {
			$row_class = self::get_proper_value( $value, 'row_class', '' );
			$row_tpl   = "<tr class='o-rf-row $row_class'>";
			// ID unique permettant d'identifier de façon unique tous les indexes de ce template et de la remplacer tous ensemble en cas de besoin.
			$index = uniqid();
			foreach ( $value['fields'] as $field ) {
				$field_tpl = $field;
				if ( 'groupedfields' === $field['type'] ) {
					foreach ( $field_tpl['fields'] as $i => $grouped_field ) {
						$field_tpl['fields'][ $i ]['name'] = $value['name'] . '[{' . $index . '}][' . $grouped_field['name'] . ']';
					}
				}
				$field_tpl['name']            = $value['name'] . '[{' . $index . '}][' . $field_tpl['name'] . ']';
				$field_tpl['ignore_desc_col'] = true;
				$row_tpl                     .= self::admin_fields( array( $field_tpl ) );
			}
			// We add the remove button to the template.
			$row_tpl .= '<td><a class="remove-rf-row"></a></td></tr>';

			return $row_tpl;
		}

		/**
		 * Get a value by key in an array if defined
		 *
		 * @param array  $values Array to search into.
		 * @param string $search_key Searched key.
		 * @param mixed  $default_value Value if the key does not exist in the array.
		 *
		 * @return mixed
		 */
		public static function get_proper_value( $values, $search_key, $default_value = '' ) {
			if ( isset( $values[ $search_key ] ) ) {
				$default_value = $values[ $search_key ];
			}

			return $default_value;
		}

		/**
		 * Explode a character.
		 *
		 * @param string $delimiters Delimiters.
		 * @param string $string String value.
		 *
		 * @return false|string[]
		 */
		public static function explode_x( $delimiters, $string ) {
			return explode( chr( 1 ), str_replace( $delimiters, chr( 1 ), $string ) );
		}

		/**
		 * Returns a media URL
		 *
		 * @param mixed $media_id Media ID.
		 *
		 * @return mixed
		 */
		public static function get_media_url( $media_id ) {
			$attachment = wp_get_attachment_image_src( $media_id, 'full' );

			return $attachment[0];
		}

		/**
		 * Find in array by key.
		 *
		 * @param mixed $root_value Value.
		 * @param mixed $key Key.
		 *
		 * @return false|mixed
		 */
		public static function find_in_array_by_key( $root_value, $key ) {
			$bracket_pos         = strpos( $key, '[' );
			$usable_value_index  = substr( $key, $bracket_pos );
			$usable_value_index2 = str_replace( '[', '', $usable_value_index );
			$temp_array          = explode( ']', $usable_value_index2, -1 );
			foreach ( $temp_array as $key => $value ) {
				if ( ! is_array( $root_value ) || ! isset( $root_value[ $value ] ) ) {
					return false;
				}
				$root_value = $root_value[ $value ];
			}
			return $root_value;
		}

		/**
		 * Get proper image url.
		 *
		 * @param mixed $suspected_link Suspected Link.
		 * @param mixed $with_root Has permissions.
		 *
		 * @return array|mixed|string|string[]|void
		 */
		public static function get_proper_image_url( $suspected_link, $with_root = true ) {
			if ( empty( $suspected_link ) ) {
				return $suspected_link;
			}
			$img_src = $suspected_link;
			if ( is_numeric( $suspected_link ) ) {
				$raw_img_src = wp_get_attachment_url( $suspected_link );
				$img_src     = str_replace( self::get_medias_root_url( '/' ), '', $raw_img_src );
			}
			$img_src = str_replace( self::get_medias_root_url( '/' ), '', $img_src );
			// Code for bad https handling.
			if ( strpos( self::get_medias_root_url( '/' ), 'https' ) === false ) {
				$https_home = str_replace( 'http', 'https', self::get_medias_root_url( '/' ) );
				$img_src    = str_replace( $https_home, '', $img_src );
			}

			if ( $with_root ) {
				$img_src = self::get_medias_root_url( "/$img_src" );
			}

			return $img_src;
		}

		/**
		 * Start with
		 *
		 * @param mixed $haystack Haystack.
		 * @param mixed $needle Needle.
		 *
		 * @return bool
		 */
		public static function starts_with( $haystack, $needle ) {
			// search backwards starting from haystack length characters from the end.
			return '' === $needle || strrpos( $haystack, $needle, - strlen( $haystack ) ) !== false;
		}


		/**
		 * Ends with
		 *
		 * @param mixed $haystack Haystack.
		 * @param mixed $needle Needle.
		 *
		 * @return bool
		 */
		public static function ends_with( $haystack, $needle ) {
			$temp = strlen( $haystack ) - strlen( $needle );
			// search forward starting from end minus needle length characters.
			return '' === $needle || ( $temp >= 0 && strpos( $haystack, $needle, $temp ) !== false );
		}

		/**
		 * Get Google fonts selector
		 *
		 * @param mixed $selected_font Font.
		 * @param mixed $id Id.
		 * @param mixed $name name.
		 * @param mixed $style Style.
		 * @param mixed $class class.
		 *
		 * @return mixed
		 */
		public static function get_google_fonts_selector( $selected_font = false, $id = '', $name = '', $style = '', $class = '' ) {
			$file_path       = plugin_dir_path( __FILE__ ) . '/assets/js/googlefont.json';
			$fonts_json_file = fopen( $file_path, 'r' );
			$font_content    = fread( $fonts_json_file, filesize( $file_path ) );
			fclose( $fonts_json_file );
			$decoded_fonts = json_decode( $font_content, true );
			?>
		<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"
				style="<?php echo esc_attr( $style ); ?>"
				class="o-google-font-selector <?php echo esc_attr( $class ); ?>">
			<?php
			echo wp_kses_post( '<option value="">Pick a font</option>' );
			foreach ( $decoded_fonts['items'] as $font ) {
				if (
					isset( $font['family'] )
					&&
					isset( $font['files'] )
					&&
					isset( $font['files']['regular'] ) ) {
					$selected    = '';
					$field_value = 'http://fonts.googleapis.com/css?family=' . rawurlencode( $font['family'] ) . '|' . $font['family'] . '|' . $font['category'];
					if ( $selected_font === $field_value ) {
						$selected = 'selected';
					}
					echo wp_kses_post( '<option value="' . $field_value . '" ' . $selected . '>' . $font['family'] . '</option> ' );
				}
			}
			?>
		</select>
			<?php
			return $decoded_fonts['items'];
		}

		/**
		 * Get media root url
		 *
		 * @param mixed $path Path.
		 *
		 * @return string|void
		 */
		public static function get_medias_root_url( $path = '/' ) {
			$upload_url_path = get_option( 'upload_url_path' );
			if ( $upload_url_path ) {
				return $upload_url_path . $path;
			} else {
				return site_url( $path );
			}
		}


		/**
		 * This registers google font.
		 *
		 * @param string $font_name Font Name.
		 * @param string $raw_url Font raw url.
		 *
		 * @return void
		 */
		public static function register_google_font( $font_name, $raw_url ) {
			$font_url = str_replace( 'http://', '//', $raw_url );
			if ( $font_url ) {
				$handler = sanitize_title( $font_name );
				wp_register_style( $handler, $font_url, array(), '__return_false', 'all' );
				wp_enqueue_style( $handler );
			}
		}

		/**
		 * This provides repeatable field table rows.
		 *
		 * @param mixed $repeatable_field_settings Field settings.
		 * @param mixed $repeatable_fields_data Field data.
		 *
		 * @return void
		 */
		public static function get_repeatable_field_table_rows( $repeatable_field_settings, $repeatable_fields_data ) {
			foreach ( $repeatable_fields_data as $i => $row ) {
				?>
			<tr class='<?php echo esc_attr( $repeatable_field_settings['row_class'] ); ?>'>
				<?php
				foreach ( $repeatable_field_settings['fields'] as $field ) {
					if ( isset( $row[ $field['name'] ] ) ) {
						$field_value = $row[ $field['name'] ];
					} else {
						$field_value = '';
					}
					$field['name'] = $repeatable_field_settings['name'] . "[$i][" . $field['name'] . ']';
					// If it's a grouped field.
					if ( 'groupedfields' === $field['type'] ) {
						foreach ( $field['fields'] as $grouped_field_index => $grouped_field_item ) {
							$field['fields'][ $grouped_field_index ]['name'] = $repeatable_field_settings['name'] . "[$i][" . $grouped_field_item['name'] . ']';
						}
					} elseif (
					'image' === $field['type']
					&&
					isset( $field['url_name'] )
					) {
						$field['url_name'] = $repeatable_field_settings['name'] . "[$i][" . $field['url_name'] . ']';
					}
					$field['default']         = $field_value;
					$field['ignore_desc_col'] = true;

					echo wp_kses( self::admin_fields( array( $field ) ), self::get_allowed_tags() );
				}
				?>
			<td>
				<a class="remove-rf-row"></a>
			</td>
				<?php
				echo wp_kses_post( '</tr>' );
			}
		}

		/**
		 * Function to load allowed html tags.
		 *
		 * @return array
		 */
		public static function get_allowed_tags() {
			$allowed_tags = wp_kses_allowed_html( 'post' );
			add_filter(
				'safe_style_css',
				function( $styles ) {
					$styles[] = 'display';
					return $styles;
				}
			);

			$allowed_tags['li'] = array(
				'id'             => array(),
				'name'           => array(),
				'class'          => array(),
				'value'          => array(),
				'style'          => array(),
				'data-ttf'       => array(),
				'data-fonturl'   => array(),
				'data-fontname'  => array(),
				'data-color'     => array(),
				'data-minwidth'  => array(),
				'data-minheight' => array(),
			);

			$allowed_tags['br'] = array();

			$allowed_tags['input'] = array(
				'type'           => array(),
				'id'             => array(),
				'name'           => array(),
				'style'          => array(),
				'class'          => array(),
				'value'          => array(),
				'min'            => array(),
				'max'            => array(),
				'row_class'      => array(),
				'selected'       => array(),
				'checked'        => array(),
				'readonly'       => array(),
				'placeholder'    => array(),
				'step'           => array(),
				'data-fonturl'   => array(),
				'data-fontname'  => array(),
				'data-minwidth'  => array(),
				'data-minheight' => array(),
				'autocomplete'   => array(),
				'autocorrect'    => array(),
				'autocapitalize' => array(),
				'spellcheck'     => array(),
				'pattern'        => array(),
				'required'       => array(),
			);
			$allowed_tags['form']  = array(
				'accept-charset' => array(),
				'id'             => array(),
				'name'           => array(),
				'style'          => array(),
				'class'          => array(),
				'value'          => array(),
				'action'         => array(),
				'autocomplete'   => array(),
				'row_class'      => array(),
				'novalidate'     => array(),
				'method'         => array(),
				'readonly'       => array(),
				'target'         => array(),
				'data-fonturl'   => array(),
				'data-fontname'  => array(),
				'data-minwidth'  => array(),
				'data-minheight' => array(),
				'autocorrect'    => array(),
				'autocapitalize' => array(),
				'hidden'         => array(),
			);

			$allowed_tags['div'] = array(
				'id'                   => array(),
				'name'                 => array(),
				'data-id'              => array(),
				'class'                => array(),
				'row_class'            => array(),
				'role'                 => array(),
				'aria-labelledby'      => array(),
				'aria-hidden'          => array(),
				'data-fonturl'         => array(),
				'data-minwidth'        => array(),
				'data-minheight'       => array(),
				'data-tooltip-content' => array(),
				'tabindex'             => array(),
				'style'                => array(),
				'data-tooltip-title'   => array(),
				'data-placement'       => array(),
				'media'                => array(),
			);
			$allowed_tags['i']   = array();

			$allowed_tags['button'] = array(
				'id'                => array(),
				'name'              => array(),
				'class'             => array(),
				'value'             => array(),
				'data-tpl'          => array(),
				'style'             => array(),
				'data-id'           => array(),
				'data-dismiss'      => array(),
				'aria-hidden'       => array(),
				'data-editor'       => array(),
				'type'              => array(),
				'data-wp-editor-id' => array(),
			);

			$allowed_tags['body'] = array(
				'id'                 => array(),
				'name'               => array(),
				'class'              => array(),
				'data-gr-c-s-loaded' => array(),
			);

			$allowed_tags['a']        = array(
				'id'               => array(),
				'name'             => array(),
				'class'            => array(),
				'data-tpl'         => array(),
				'href'             => array(),
				'data-toggle'      => array(),
				'data-target'      => array(),
				'data-modalid'     => array(),
				'target'           => array(),
				'data-group'       => array(),
				'data-slide-index' => array(),
				'download'         => array(),
				'style'            => array(),
			);
			$allowed_tags['select']   = array(
				'id'         => array(),
				'name'       => array(),
				'class'      => array(),
				'data-tpl'   => array(),
				'style'      => array(),
				'multiple'   => array(),
				'tabindex'   => array(),
				'data-rule'  => array(),
				'data-group' => array(),
			);
			$allowed_tags['optgroup'] = array(
				'id'       => array(),
				'name'     => array(),
				'class'    => array(),
				'data-tpl' => array(),
				'style'    => array(),
				'multiple' => array(),
				'tabindex' => array(),
				'label'    => array(),
			);
			$allowed_tags['option']   = array(
				'id'       => array(),
				'name'     => array(),
				'class'    => array(),
				'value'    => array(),
				'style'    => array(),
				'selected' => array(),
				'tabindex' => array(),
				'disabled' => array(),
			);

			$allowed_tags['span'] = array(
				'id'                 => array(),
				'name'               => array(),
				'class'              => array(),
				'value'              => array(),
				'style'              => array(),
				'data-tooltip-title' => array(),
				'data-placement'     => array(),
			);

			$allowed_tags['h1']     = array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			);
			$allowed_tags['iframe'] = array();
			$allowed_tags['h2']     = array(
				'id'    => array(),
				'class' => array(),
				'style' => array(),
			);
			$allowed_tags['h3']     = array(
				'style' => array(),
				'id'    => array(),
				'class' => array(),
			);

			$allowed_tags['link'] = array(
				'id'    => array(),
				'rel'   => array(),
				'media' => array(),
				'href'  => array(),
			);

			$allowed_tags['textarea'] = array(
				'autocomplete'   => array(),
				'autocorrect'    => array(),
				'autocapitalize' => array(),
				'spellcheck'     => array(),
				'class'          => array(),
				'rows'           => array(),
				'cols'           => array(),
				'name'           => array(),
				'id'             => array(),
				'style'          => true,
			);

			$allowed_tags['table'] = array(
				'border'      => array(),
				'cellpadding' => array(),
				'cellspacing' => array(),
				'class'       => array(),
				'style'       => array(),
			);

			$allowed_tags['tr'] = array(
				'align'   => array(),
				'class'   => array(),
				'style'   => array(),
				'data-id' => array(),
			);

			$allowed_tags['td'] = array(
				'colspan' => array(),
				'class'   => array(),
				'style'   => array(),
			);

			$allowed_tags['th'] = array(
				'colspan' => array(),
				'class'   => array(),
				'style'   => array(),
			);

			$allowed_tags['img'] = array(
				'src'    => array(),
				'alt'    => array(),
				'height' => array(),
				'width'  => array(),
				'style'  => array(),
				'class'  => array(),
			);

			return apply_filters( 'o_allowed_tags', $allowed_tags );
		}
	}

}
