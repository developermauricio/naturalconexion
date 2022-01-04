<?php 

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( !class_exists( 'Addify_Registration_Fields_Addon_Front' ) ) {

	class Addify_Registration_Fields_Addon_Front extends Addify_Registration_Fields_Addon {

		public function __construct() {

			add_action( 'wp_loaded', array( $this, 'afreg_front_scripts' ) );
			add_action( 'woocommerce_register_form', array($this, 'afreg_extra_fields_show' ) );
			add_action( 'woocommerce_after_checkout_registration_form', array($this, 'afreg_extra_fields_show' ));
			add_action( 'woocommerce_register_post', array($this, 'afreg_default_fields_validate'), 10, 3 );
			add_action( 'woocommerce_register_post', array($this, 'afreg_validate_extra_register_fields'), 10, 3 );

			
			add_action( 'user_register', array( $this, 'afreg_save_extra_fields' ) );
			add_action( 'woocommerce_edit_account_form', array($this, 'afreg_update_extra_fields_my_account' ));
			add_action( 'woocommerce_save_account_details_errors', array($this, 'afreg_validate_update_role_my_account'), 10, 1 );
			add_action( 'woocommerce_save_account_details', array($this, 'afreg_save_update_role_my_account'), 12, 1 );
			

			//For WordPress
			add_filter('register_form', array($this, 'afreg_extra_fields_show_wordpress'));
			add_filter( 'registration_errors', array($this, 'afreg_validate_extra_register_fields_wordpress'), 10, 3 );

			//Manual Approve Users
			add_action('woocommerce_registration_redirect', array($this, 'afreg_user_autologout'), 2);
			add_action('woocommerce_before_customer_login_form', array($this, 'afreg_registration_message'), 2);
			add_filter('wp_authenticate_user', array($this, 'afreg_auth_login'));

			add_filter( 'woocommerce_form_field_multiselect', array($this, 'afreg_custom_multiselect_handler'), 10, 4 );

			//Default Fields
			add_action( 'woocommerce_register_form_start', array($this, 'afreg_default_fields' ));

			add_filter( 'woocommerce_email_order_meta_fields', array($this, 'afreg_email_order_meta_fields'), 10, 3 );

			
			

		}

		


		public function afreg_email_order_meta_fields( $fields, $sent_to_admin, $order ) {

			$user = wp_get_current_user();

			$afreg_args = array( 
				'posts_per_page' => -1,
				'post_type' => 'afreg_fields',
				'post_status' => 'publish',
				'orderby' => 'menu_order',
				'suppress_filters' => false,
				'order' => 'ASC'
			);
			

			$afreg_extra_fields = get_posts($afreg_args);

			foreach ($afreg_extra_fields as $afreg_field) {

				$afreg_field_type          = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
				$afreg_field_order_details = get_post_meta( intval($afreg_field->ID), 'afreg_field_order_details', true );
				$afregcheck                = get_user_meta( $user->ID, 'afreg_additional_' . intval($afreg_field->ID), true );

				if (!empty($afregcheck) && 'on' == $afreg_field_order_details) {

					$value = get_user_meta( $user->ID, 'afreg_additional_' . intval($afreg_field->ID), true );
					
					if ( 'fileupload' == $afreg_field_type) {



						$upload_url = wp_upload_dir();

						$current_file = '';

						$curr_image_new_folder = $upload_url['basedir'] . '/addify_registration_uploads/' . $value;
	
						$curr_image = esc_url(AFREG_URL . 'uploaded_files/' . $value);

						if (file_exists($curr_image_new_folder)) {

							$current_file = esc_url($upload_url['baseurl'] . '/addify_registration_uploads/' . $value);

						} elseif (file_exists($curr_image)) {

							$current_file = esc_url(AFREG_URL . 'uploaded_files/' . $value);

						}

						$value = '<a href="' . esc_url($current_file ) . '">' . esc_html__('Click here to view', 'addify_reg') . '</a>';
						

						$fields[$afreg_field->post_title] = array(
							'label' => esc_html__($afreg_field->post_title . ': ', 'addify_reg'),
							'value' => $value,
						);

					} elseif ( in_array( $afreg_field_type , array( 'multiselect' , 'multi_checkbox' , 'select', 'radio') ) ) {

						$val_array            = explode(', ' , $value );
						 $afreg_field_options = unserialize(get_post_meta(  intval($afreg_field->ID) , 'afreg_field_option', true )); 
						 $value               = '';
						foreach ( $val_array as $option_val ) {
							foreach ($afreg_field_options as $afreg_field_option ) { 
								if ( esc_attr( $option_val ) == $afreg_field_option['field_value'] ) {
									$value .=  $afreg_field_option['field_text'] . ', ';
								}
							}
						}

						$fields[$afreg_field->post_title] = array(
							'label' => esc_html__($afreg_field->post_title . ' ', 'addify_reg'),
							'value' => rtrim($value, ', '),
						);

					} else {

						$fields[$afreg_field->post_title] = array(
							'label' => esc_html__($afreg_field->post_title . ' ', 'addify_reg'),
							'value' => $value,
						);
					}

				}
			}
				
			return $fields;
		}

		public function afreg_before_checkout_create_order( $order, $data) {


				$user = wp_get_current_user();

				$afreg_args = array( 
					'posts_per_page' => -1,
					'post_type' => 'afreg_fields',
					'post_status' => 'publish',
					'orderby' => 'menu_order',
					'suppress_filters' => false,
					'order' => 'ASC'
				);
				

				$afreg_extra_fields = get_posts($afreg_args);

				foreach ($afreg_extra_fields as $afreg_field) {

					$afreg_field_type = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
					$afregcheck       = get_user_meta( $user->ID, 'afreg_additional_' . intval($afreg_field->ID), true );

					if (!empty($afregcheck)) {

						$value = get_user_meta( $user->ID, 'afreg_additional_' . intval($afreg_field->ID), true );
						$order->update_meta_data( 'afreg_additional_' . intval($afreg_field->ID), $value );
						

					}
				}


		}

		

		public function afreg_front_scripts() {

			wp_enqueue_style( 'afreg-front-css', plugins_url( '/css/afreg_front.css', __FILE__ ), false, '1.0' );
			wp_enqueue_style( 'color-spectrum-css', plugins_url( '/css/afreg_color_spectrum.css', __FILE__ ), false, '1.0' );
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'afreg-front-js', plugins_url( '/js/afreg_front.js', __FILE__ ), false, '1.0' );
			wp_enqueue_script( 'color-spectrum-js', plugins_url( '/js/afreg_color_spectrum.js', __FILE__ ), false, '1.0' );

			if (!empty(get_option('afreg_site_key')) && !empty(get_option('afreg_secret_key'))) {
				wp_enqueue_script( 'Google reCaptcha JS', '//www.google.com/recaptcha/api.js', false, '1.0' );
			}
			
		}

		public function afreg_extra_fields_show() { ?>

			<div class="afreg_extra_fields">
				<h3><?php echo esc_html__(get_option('afreg_additional_fields_section_title'), 'addify_reg'); ?></h3>

				<?php

				$user = wp_get_current_user();

				wp_nonce_field( 'afreg_nonce_action', 'afreg_nonce_field' );

				if ( isset( $_POST['register']) && '' != $_POST['register']) {

					if (!empty($_REQUEST['afreg_nonce_field'])) {

						$retrieved_nonce = sanitize_text_field($_REQUEST['afreg_nonce_field']);
					} else {
							$retrieved_nonce = 0;
					}

					if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

						echo '';
					}
				}


				if ( !empty( get_option('afreg_enable_user_role')) && 'yes' == get_option('afreg_enable_user_role')) {

					if ( !empty(get_option('afreg_user_role_field_text'))) {

						$role_field_label = get_option('afreg_user_role_field_text');
					} else {

						$role_field_label = 'Select User Role';
					}

					//When error values should stay
					if (!empty($_POST['afreg_select_user_role'])) {
						$vall =  sanitize_text_field( $_POST['afreg_select_user_role']);
					} else {
						$vall = '';
					}

					?>
				<p class="form-row form-row-wide">
					<label for="afreg_user_role"><?php echo esc_html__($role_field_label, 'addify_reg'); ?><span class="required">*</span></label>
					<select class="input-select" name="afreg_select_user_role" id="afreg_select_user_role">
						<option value=""><?php echo esc_html__('---Select---', 'addify_reg'); ?></option>
						<?php
						$user_roles = get_option('afreg_user_roles');
						global $wp_rolesss;
						if ( !isset( $wp_rolesss ) ) {
							$wp_rolesss = new WP_Roles();
						}

						if ( !empty( $user_roles)) {
							foreach ( $user_roles as $key => $value) {
								?>
						<option value="<?php echo esc_attr($value); ?>" <?php echo selected($value, $vall); ?>>
								
								<?php echo esc_attr(translate_user_role( $wp_rolesss->roles[$value]['name'], 'default' )); ?>
						</option>
						<?php } } ?>
					</select>
				</p>
				<?php } ?>

				<?php

					
					$afreg_args = array( 
						'posts_per_page' => -1,
						'post_type' => 'afreg_fields',
						'post_status' => 'publish',
						'orderby' => 'menu_order',
						'suppress_filters' => false,
						'order' => 'ASC'
					);

					$afreg_extra_fields = get_posts($afreg_args);
					if (!empty($afreg_extra_fields)) {

						foreach ($afreg_extra_fields as $afreg_field) {

							//When error values should stay
							if (!empty($_POST['afreg_additional_' . intval($afreg_field->ID)])) {
								$vall =  sanitize_text_field( $_POST['afreg_additional_' . intval($afreg_field->ID)]);
							} else {
								$vall = '';
							}

							if (!empty($_POST['afreg_additional_' . intval($afreg_field->ID)])) {
								$vall_checkbox =  sanitize_meta('', $_POST['afreg_additional_' . intval($afreg_field->ID)], '');
							} else {
								$vall_checkbox = array();
							}


							$afreg_field_type                      = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
							$afreg_field_options                   = unserialize(get_post_meta( intval($afreg_field->ID), 'afreg_field_option', true )); 
							$afreg_field_required                  = get_post_meta( intval($afreg_field->ID), 'afreg_field_required', true );
							$afreg_field_width                     = get_post_meta( intval($afreg_field->ID), 'afreg_field_width', true );
							$afreg_field_show_in_registration_form = get_post_meta( $afreg_field->ID, 'afreg_field_show_in_registration_form', true );

							if ( !empty( get_post_meta( intval($afreg_field->ID), 'afreg_field_placeholder', true ))) {
								$afreg_field_placeholder = get_post_meta( intval($afreg_field->ID), 'afreg_field_placeholder', true );
							} else {
								$afreg_field_placeholder = '';
							}
								
							$afreg_field_description       = get_post_meta( intval($afreg_field->ID), 'afreg_field_description', true );
							$afreg_field_css               = get_post_meta( intval($afreg_field->ID), 'afreg_field_css', true );
							$afreg_field_heading_type      = get_post_meta( intval($afreg_field->ID), 'afreg_field_heading_type', true );
							$afreg_field_description_field = get_post_meta( intval($afreg_field->ID), 'afreg_field_description_field', true );

							if (!empty($afreg_field_width) && 'full' == $afreg_field_width) {

								$afreg_main_class = 'form-row-wide newr';

							} elseif (!empty($afreg_field_width) && 'half' == $afreg_field_width) {

								$afreg_main_class = 'half_width newr';
							}

							if (!empty(get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true ))) {

								$afreg_is_dependable = get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true );
							} else {
								$afreg_is_dependable = 'off';
							}

							$afreg_field_user_roles = get_post_meta( $afreg_field->ID, 'afreg_field_user_roles', true );
							$field_roles            = unserialize($afreg_field_user_roles);


							if ('heading' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { 

								if (!empty($afreg_field_css)) {
									$css_class = $afreg_field_css;
								} else {
									$css_class = '';	
								}


								?>

								<<?php echo esc_attr($afreg_field_heading_type); ?> class="<?php echo esc_attr($css_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<?php echo wp_kses_post($afreg_field->post_title); ?>
								</<?php echo esc_attr($afreg_field_heading_type); ?>>

								<?php 
							} elseif ('description' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { 

								if (!empty($afreg_field_css)) {
									$css_class = $afreg_field_css;
								} else {
									$css_class = '';	
								}


								?>

								<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
									<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																			<?php 
																			if (!empty($afreg_field->post_title)) {
																				echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																			?>
																			</label>
									<span class="<?php echo esc_attr($css_class); ?>">
									<?php echo wp_kses_post($afreg_field_description_field); ?>
									</span>

								</p>

								<?php 
							} elseif ( 'text' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { 
									
								?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																			<?php 
																			if (!empty($afreg_field->post_title)) {
																				echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																			?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
											
										</span></label>
										<input type="text" class="input-text 
										<?php 
										if (!empty($afreg_field_css)) {
											echo esc_attr($afreg_field_css);} 
										?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($vall); ?>" placeholder="<?php echo esc_html__($afreg_field_placeholder, 'addify_reg'); ?>" />
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'textarea' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										<textarea class="input-text 
										<?php 
										if (!empty($afreg_field_css)) {
											echo esc_attr($afreg_field_css);} 
										?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>"><?php echo esc_attr($vall); ?></textarea>
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'email' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										<input type="email" class="input-text 
										<?php 
										if (!empty($afreg_field_css)) {
											echo esc_attr($afreg_field_css);} 
										?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($vall); ?>" placeholder="<?php echo esc_html__($afreg_field_placeholder, 'addify_reg'); ?>" />
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'select' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										<select class="input-select 
										<?php 
										if (!empty($afreg_field_css)) {
											echo esc_attr($afreg_field_css);} 
										?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
											<?php foreach ($afreg_field_options as $afreg_field_option) { ?>
												<option value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" <?php echo selected($afreg_field_option['field_value'], $vall); ?>>
													<?php 
													if (!empty($afreg_field_option['field_text'])) {
														echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg');} 
													?>
												</option>
											<?php } ?>
										</select>
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'multiselect' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										<select class="input-select <?php echo esc_attr($afreg_field_css); ?>" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>[]" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" multiple>
											<?php 
											foreach ($afreg_field_options as $afreg_field_option) {

												//For Multiselect
												if (is_array($afreg_field_option['field_value']) && in_array( esc_attr($afreg_field_option['field_value']), $_POST['afreg_additional_' . intval($afreg_field->ID)])) {
													$vall_se = 'selected';
												} else {
													$vall_se = '';
												}

												?>

												
												<option value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" <?php echo esc_attr( $vall_se ); ?>>
													<?php echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg'); ?>
												</option>
											<?php } ?>
										</select>
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'multi_checkbox' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?> 

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										


										<?php foreach ($afreg_field_options as $afreg_field_option) { ?>
											<input type="checkbox" class="input-checkbox 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
											" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>[]" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" 
																				<?php 
																				if ( in_array($afreg_field_option['field_value'], $vall_checkbox)) {
																					echo 'checked'; } 
																				?>
											 />
											<span class="afreg_radios">
											<?php 
											if (!empty($afreg_field_option['field_text'])) {
												echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg');} 
											?>
											</span>
										<?php } ?>

										
										<?php if (!empty($afreg_field_description)) { ?>
											<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'checkbox' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?> 

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										


										
											<input <?php echo checked('yes', esc_attr($vall)); ?> type="checkbox" class="input-checkbox 
									<?php 
									if (!empty($afreg_field_css)) {
										echo esc_attr($afreg_field_css);} 
									?>
									" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="yes" />

										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'radio' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required ) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										
										<?php foreach ($afreg_field_options as $afreg_field_option) { ?>
											<input type="radio" class="input-radio 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
											" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" <?php echo checked($afreg_field_option['field_value'], $vall); ?> />
											<span class="afreg_radio">
											<?php 
											if (!empty($afreg_field_option['field_text'])) {
												echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg');} 
											?>
											</span>
										<?php } ?>
										
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message_radio"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ('number' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										<input type="number" class="input-text 
										<?php 
										if (!empty($afreg_field_css)) {
											echo esc_attr($afreg_field_css);} 
										?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($vall); ?>" placeholder="<?php echo esc_html__($afreg_field_placeholder, 'addify_reg'); ?>" />
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'password' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										<input type="password" class="input-text 
										<?php 
										if (!empty($afreg_field_css)) {
											echo esc_attr($afreg_field_css);} 
										?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($vall); ?>" placeholder="<?php echo esc_html__($afreg_field_placeholder, 'addify_reg'); ?>" />
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'fileupload' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form && !is_checkout() ) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										<input type="file" class="input-text 
										<?php 
										if (!empty($afreg_field_css)) {
											echo esc_attr($afreg_field_css);} 
										?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($vall); ?>" placeholder="<?php echo esc_html__($afreg_field_placeholder, 'addify_reg'); ?>" />
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ('color' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										<input type="color" class="input-text color_sepctrum 
										<?php 
										if (!empty($afreg_field_css)) {
											echo esc_attr($afreg_field_css);} 
										?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($vall); ?>" placeholder="<?php echo esc_html__($afreg_field_placeholder, 'addify_reg'); ?>" />
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message_radio"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'datepicker' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										<input type="date" class="input-text  
										<?php 
										if (!empty($afreg_field_css)) {
											echo esc_attr($afreg_field_css);} 
										?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($vall); ?>" placeholder="<?php echo esc_html__($afreg_field_placeholder, 'addify_reg'); ?>" />
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message_radio"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'timepicker' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
											*
											<?php
										} 
										?>
										</span></label>
										<input type="time" class="input-text  
										<?php 
										if (!empty($afreg_field_css)) {
											echo esc_attr($afreg_field_css);} 
										?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($vall); ?>" placeholder="<?php echo esc_html__($afreg_field_placeholder, 'addify_reg'); ?>" />
										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message_radio"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } elseif ( 'googlecaptcha' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">*</span></label>
										
										<div class="g-recaptcha" data-sitekey="<?php echo esc_attr(get_option('afreg_site_key')); ?>"></div>

										<?php if (!empty($afreg_field_description)) { ?>
											<span class="afreg_field_message_radio"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

								<?php } ?>

								<!-- Dependable -->
								<?php if ('on' == $afreg_is_dependable && !empty($field_roles)) { ?>

									<style>
										#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?> { display: none; }
									</style>

								<?php } ?>

								<script>
									
									jQuery(document).on('change', '#afreg_select_user_role', function() {

										var val = this.value;
										var field_roles = new Array();
										var is_dependable = '<?php echo esc_attr($afreg_is_dependable); ?>';
											
											<?php if ( !empty($field_roles)) { ?>
												<?php foreach ($field_roles as $key => $value) { ?>

													field_roles.push('<?php echo esc_attr($value); ?>');

												<?php } ?>

												var match_val = field_roles.includes(val);

												if (match_val == true && is_dependable == 'on') {


													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												} else if (match_val == false && is_dependable == 'on') {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').hide();
												} else {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												}

											<?php } ?>


									});
									jQuery(document).on('ready' , function() {

										var val = jQuery('#afreg_select_user_role').val();
										var field_roles = new Array();
										var is_dependable = '<?php echo esc_attr($afreg_is_dependable); ?>';
											
											<?php if ( !empty($field_roles)) { ?>
												<?php foreach ($field_roles as $key => $value) { ?>

													field_roles.push('<?php echo esc_attr($value); ?>');

												<?php } ?>

												var match_val = field_roles.includes(val);

												if (match_val == true && is_dependable == 'on') {


													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												} else if (match_val == false && is_dependable == 'on') {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').hide();
												} else {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												}

											<?php } ?>


									});

								</script>

								<?php 
						

						}
						
					}


					?>
			</div>

			<?php 
		}

		public function afreg_validate_extra_register_fields( $username, $email, $validation_errors ) {

			$afreg_args = array( 
				'posts_per_page' => -1,
				'post_type' => 'afreg_fields',
				'post_status' => 'publish',
				'orderby' => 'menu_order',
				'suppress_filters' => false,
				'order' => 'ASC'
			);

			if (isset($_POST['register']) || is_checkout() ) {

				if ( isset( $_POST['afreg_select_user_role'] ) && empty( $_POST['afreg_select_user_role'] ) ) {

					if ( !empty(get_option('afreg_user_role_field_text'))) {

						$role_field_label = get_option('afreg_user_role_field_text');
					} else {

						$role_field_label = 'Select User Role';
					}

					$validation_errors->add( 'afreg_select_user_role_error', esc_html__( $role_field_label . ' is required!', 'addify_b2b' ) );
				}

				$afreg_extra_fields = get_posts($afreg_args);
				if (!empty($afreg_extra_fields)) {

					if (!empty($_REQUEST['afreg_nonce_field'])) {

						$retrieved_nonce = sanitize_text_field($_REQUEST['afreg_nonce_field']);
					} else {
						$retrieved_nonce = 0;
					}

					if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

						echo '';
					}

					foreach ($afreg_extra_fields as $afreg_field) {

						$afreg_field_required  = get_post_meta( intval($afreg_field->ID), 'afreg_field_required', true );
						$afreg_field_type      = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
						$afreg_field_file_type = get_post_meta( intval($afreg_field->ID), 'afreg_field_file_type', true );
						$afreg_field_file_size = get_post_meta( intval($afreg_field->ID), 'afreg_field_file_size', true );

						$afreg_field_show_in_registration_form = get_post_meta( $afreg_field->ID, 'afreg_field_show_in_registration_form', true );

						if (!empty(get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true ))) {

							$afreg_is_dependable = get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true );
						} else {
							$afreg_is_dependable = 'off';
						}

						$afreg_field_user_roles = get_post_meta( $afreg_field->ID, 'afreg_field_user_roles', true );
						$field_roles            = unserialize($afreg_field_user_roles);



						if ('on' == $afreg_is_dependable && !empty($field_roles)) {


							if ( !empty( get_option('afreg_enable_user_role')) && 'yes' == get_option('afreg_enable_user_role')) {
								

								if ( isset( $_POST['afreg_select_user_role'] ) && !empty( $_POST['afreg_select_user_role'] ) ) {

									if ( in_array($_POST['afreg_select_user_role'], $field_roles)) {

										

										if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required && 'off' != $afreg_field_show_in_registration_form ) ) {

											/* translators: %s: field label */
											$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__('%s is required!', 'addify_b2b' ) , $afreg_field->post_title ) );
										}
										

										if ('email' == $afreg_field_type) {

											if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required && 'off' != $afreg_field_show_in_registration_form ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_EMAIL) ) {

												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__(  '%s is not a valid email address!', 'addify_b2b' ) , $afreg_field->post_title ) ); 
											}

										}

										if ( 'multiselect' == $afreg_field_type) {
									
											if (empty($_POST['afreg_additional_' . intval($afreg_field->ID)]) && 'on' == $afreg_field_required && 'off' != $afreg_field_show_in_registration_form) {
												
												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__('%s is required!', 'addify_b2b' ) , $afreg_field->post_title ) );
											
											}
										}

										if ('number' == $afreg_field_type) {

											if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required && 'off' != $afreg_field_show_in_registration_form ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_INT) ) {

												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error',
												esc_html( $afreg_field->post_title ) . esc_html__( ' is not a valid number!', 'addify_b2b' ) );
											}

										}

										if ('multi_checkbox' == $afreg_field_type || 'checkbox' == $afreg_field_type || 'radio' == $afreg_field_type) { 

											if ( !isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required && 'off' != $afreg_field_show_in_registration_form ) ) {

												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__('%s is required!', 'addify_b2b' ) , $afreg_field->post_title ) );
											}

										}


										if ( 'googlecaptcha' == $afreg_field_type) { 
									
											if (isset($_POST['g-recaptcha-response']) && '' != $_POST['g-recaptcha-response']) {
												$ccheck = $this->captcha_check(sanitize_text_field($_POST['g-recaptcha-response']));
												if ('' == $ccheck) {
													/* translators: %s: field label */
													$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( 'Invalid reCaptcha!', 'addify_b2b' ) );
												}
											} else {
												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__('%s is required!', 'addify_b2b' ) , $afreg_field->post_title ) );
											}
										}

										if ( 'fileupload' == $afreg_field_type) {

											if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required && 'off' != $afreg_field_show_in_registration_form) {

												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__('%s is required!', 'addify_b2b' ) , $afreg_field->post_title ) );

											}

											if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && !empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required && 'off' != $afreg_field_show_in_registration_form) {

												$afreg_allowed_types =  explode(',', $afreg_field_file_type);
												$afreg_filename      = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']);
												$afreg_ext           = pathinfo($afreg_filename, PATHINFO_EXTENSION);

												if (!in_array($afreg_ext, $afreg_allowed_types) ) {

													/* translators: %s: field label */
													$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html( $afreg_field->post_title ) . esc_html__( ': File type is not allowed!', 'addify_b2b' ) );
												}

												if ( isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size'])) {

													$afreg_filesize = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size']);
												} else {
													$afreg_filesize = 0;
												}
											
												$afreg_allowed_size = $afreg_field_file_size * 1000000; // convert from MB to Bytes

												if ($afreg_filesize > $afreg_allowed_size) {

													/* translators: %s: field label */
													$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html( $afreg_field->post_title ) . esc_html__( ': File size is too big!', 'addify_b2b' ) );

												}
											}
										}




									}
								}
							}



						} else {



							if ( !empty( get_option('afreg_enable_user_role')) && 'yes' == get_option('afreg_enable_user_role')) {
								if ( isset( $_POST['afreg_select_user_role'] ) && empty( $_POST['afreg_select_user_role'] ) ) {

									if ( !empty(get_option('afreg_user_role_field_text'))) {

										$role_field_label = get_option('afreg_user_role_field_text');
									} else {

										$role_field_label = 'Select User Role';
									}

									/* translators: %s: field label */
									$validation_errors->add( 'afreg_select_user_role_error', esc_html( $role_field_label ) . esc_html__(  ' is required!', 'addify_b2b' ) );
								}
							}


							if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && 'off' != $afreg_field_show_in_registration_form ) {

								/* translators: %s: field label */
								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__('%s is required!', 'addify_b2b' ) , $afreg_field->post_title ) );
							}

							if ('email' == $afreg_field_type) {

								if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && 'off' != $afreg_field_show_in_registration_form && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_EMAIL) ) {

									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html( $afreg_field->post_title ) . esc_html__( ' is not a valid email address!', 'addify_b2b' ) );
								}

							}

							if ( 'multiselect' == $afreg_field_type) {
						
								if (empty($_POST['afreg_additional_' . intval($afreg_field->ID)]) && 'on' == $afreg_field_required && 'off' != $afreg_field_show_in_registration_form) {
									
									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__('%s is required!', 'addify_b2b' ) , $afreg_field->post_title ) );
								
								}
							}

							if ('number' == $afreg_field_type) {

								if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && 'off' != $afreg_field_show_in_registration_form && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_INT) ) {

									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html( $afreg_field->post_title ) . esc_html__( ' is not a valid number!', 'addify_b2b' ) );
								}

							}

							if ('multi_checkbox' == $afreg_field_type || 'checkbox' == $afreg_field_type || 'radio' == $afreg_field_type) { 

								if ( !isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && 'off' != $afreg_field_show_in_registration_form ) {

									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__('%s is required!', 'addify_b2b' ) , $afreg_field->post_title ) );
								}

							}


							if ( 'googlecaptcha' == $afreg_field_type) { 
						
								if (isset($_POST['g-recaptcha-response']) && '' != $_POST['g-recaptcha-response']) {
									$ccheck = $this->captcha_check(sanitize_text_field($_POST['g-recaptcha-response']));
									if ('' == $ccheck) {
										/* translators: %s: field label */
										$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( 'Invalid reCaptcha!', 'addify_b2b' ) );
									}
								} else {
									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__('%s is required!', 'addify_b2b' ) , $afreg_field->post_title ) );
								}
							}

							if ( 'fileupload' == $afreg_field_type) {

								if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required && 'off' != $afreg_field_show_in_registration_form) {

									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', sprintf( esc_html__('%s is required!', 'addify_b2b' ) , $afreg_field->post_title ) );

								}

								if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && !empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required && 'off' != $afreg_field_show_in_registration_form) {

									$afreg_allowed_types =  explode(',', $afreg_field_file_type);
									$afreg_filename      = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']);
									$afreg_ext           = pathinfo($afreg_filename, PATHINFO_EXTENSION);

									if (!in_array($afreg_ext, $afreg_allowed_types) ) {

										/* translators: %s: field label */
										$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html( $afreg_field->post_title ) . esc_html__( ': File type is not allowed!', 'addify_b2b' ) );
									}

									if ( isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size'])) {

										$afreg_filesize = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size']);
									} else {
										$afreg_filesize = 0;
									}
								
									$afreg_allowed_size = $afreg_field_file_size * 1000000; // convert from MB to Bytes

									if ($afreg_filesize > $afreg_allowed_size) {

										/* translators: %s: field label */
										$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html( $afreg_field->post_title ) . esc_html__( ': File size is too big!', 'addify_b2b' ) );

									}
								}
							}


						}




						
					}
				}
			}

			return $validation_errors;
		}

		public function afreg_validate_extra_register_fields_wordpress( $validation_errors, $username, $email ) {

			$afreg_args = array( 
				'posts_per_page' => -1,
				'post_type' => 'afreg_fields',
				'post_status' => 'publish',
				'orderby' => 'menu_order',
				'suppress_filters' => false,
				'order' => 'ASC'
			);

			if ( true ) {

				if ( isset( $_POST['afreg_select_user_role'] ) && empty( $_POST['afreg_select_user_role'] ) ) {

					if ( !empty(get_option('afreg_user_role_field_text'))) {

						$role_field_label = get_option('afreg_user_role_field_text');
					} else {

						$role_field_label = 'Select User Role';
					}

					$validation_errors->add( 'afreg_select_user_role_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html(__( $role_field_label , 'addify_reg') ) . esc_html__( ' is required.', 'addify_b2b' ) );
				}

				$afreg_extra_fields = get_posts($afreg_args);
				if (!empty($afreg_extra_fields)) {

					if (!empty($_REQUEST['afreg_nonce_field'])) {

						$retrieved_nonce = sanitize_text_field($_REQUEST['afreg_nonce_field']);
					} else {
						$retrieved_nonce = 0;
					}

					if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

						echo '';
					}

					foreach ($afreg_extra_fields as $afreg_field) {

						$afreg_field_required  = get_post_meta( intval($afreg_field->ID), 'afreg_field_required', true );
						$afreg_field_type      = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
						$afreg_field_file_type = get_post_meta( intval($afreg_field->ID), 'afreg_field_file_type', true );
						$afreg_field_file_size = get_post_meta( intval($afreg_field->ID), 'afreg_field_file_size', true );

						if (!empty(get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true ))) {

							$afreg_is_dependable = get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true );
						} else {
							$afreg_is_dependable = 'off';
						}

						$afreg_field_user_roles = get_post_meta( $afreg_field->ID, 'afreg_field_user_roles', true );
						$field_roles            = unserialize($afreg_field_user_roles);



						if ('on' == $afreg_is_dependable && !empty($field_roles)) {


							if ( !empty( get_option('afreg_enable_user_role')) && 'yes' == get_option('afreg_enable_user_role')) {


								if ( isset( $_POST['afreg_select_user_role'] ) && !empty( $_POST['afreg_select_user_role'] ) ) {

									if ( in_array($_POST['afreg_select_user_role'], $field_roles)) {

										if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

											/* translators: %s: field label */
											$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__('%s is required.', 'addify_b2b' ) , $afreg_field->post_title ) );
										}

										if ('email' == $afreg_field_type) {

											if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_EMAIL) ) {

												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__(  '%s is not a valid email address!', 'addify_b2b' ) , $afreg_field->post_title ) ); 
											}

										}

										if ( 'multiselect' == $afreg_field_type) {
									
											if (empty($_POST['afreg_additional_' . intval($afreg_field->ID)]) && 'on' == $afreg_field_required) {
												
												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__('%s is required.', 'addify_b2b' ) , $afreg_field->post_title ) );
											
											}
										}

										if ('number' == $afreg_field_type) {

											if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_INT) ) {

												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' .
												esc_html( $afreg_field->post_title ) . esc_html__( ' is not a valid number!', 'addify_b2b' ) );
											}

										}

										if ('multi_checkbox' == $afreg_field_type || 'checkbox' == $afreg_field_type || 'radio' == $afreg_field_type) { 

											if ( !isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__('%s is required.', 'addify_b2b' ) , $afreg_field->post_title ) );
											}

										}


										if ( 'googlecaptcha' == $afreg_field_type) { 
									
											if (isset($_POST['g-recaptcha-response']) && '' != $_POST['g-recaptcha-response']) {
												$ccheck = $this->captcha_check(sanitize_text_field($_POST['g-recaptcha-response']));
												if ('' == $ccheck) {
													/* translators: %s: field label */
													$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html__( 'Invalid reCaptcha!', 'addify_b2b' ) );
												}
											} else {
												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__('%s is required.', 'addify_b2b' ) , $afreg_field->post_title ) );
											}
										}

										if ( 'fileupload' == $afreg_field_type) {

											if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required) {

												/* translators: %s: field label */
												$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__('%s is required.', 'addify_b2b' ) , $afreg_field->post_title ) );

											}

											if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && !empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required) {

												$afreg_allowed_types =  explode(',', $afreg_field_file_type);
												$afreg_filename      = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']);
												$afreg_ext           = pathinfo($afreg_filename, PATHINFO_EXTENSION);

												if (!in_array($afreg_ext, $afreg_allowed_types) ) {

													/* translators: %s: field label */
													$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( $afreg_field->post_title ) . esc_html__( ': File type is not allowed!', 'addify_b2b' ) );
												}

												if ( isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size'])) {

													$afreg_filesize = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size']);
												} else {
													$afreg_filesize = 0;
												}
												
												/* translators: %s: field label */
												$afreg_allowed_size = $afreg_field_file_size * 1000000; // convert from MB to Bytes

												if ($afreg_filesize > $afreg_allowed_size) {

													$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( $afreg_field->post_title ) . esc_html__( ': File size is too big!', 'addify_b2b' ) );

												}
											}
										}




									}
								}
							}



						} else {

							if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

								/* translators: %s: field label */
								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__('%s is required.', 'addify_b2b' ) , $afreg_field->post_title ) );
							}

							if ('email' == $afreg_field_type) {

								if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_EMAIL) ) {

									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( $afreg_field->post_title ) . esc_html__( ' is not a valid email address!', 'addify_b2b' ) );
								}

							}

							if ( 'multiselect' == $afreg_field_type) {
						
								if (empty($_POST['afreg_additional_' . intval($afreg_field->ID)]) && 'on' == $afreg_field_required) {
									
									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__('%s is required.', 'addify_b2b' ) , $afreg_field->post_title ) );
								
								}
							}

							if ('number' == $afreg_field_type) {

								if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_INT) ) {

									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( $afreg_field->post_title ) . esc_html__( ' is not a valid number!', 'addify_b2b' ) );
								}

							}

							if ('multi_checkbox' == $afreg_field_type || 'checkbox' == $afreg_field_type || 'radio' == $afreg_field_type) { 

								if ( !isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__('%s is required.', 'addify_b2b' ) , $afreg_field->post_title ) );
								}

							}


							if ( 'googlecaptcha' == $afreg_field_type) { 
						
								if (isset($_POST['g-recaptcha-response']) && '' != $_POST['g-recaptcha-response']) {
									$ccheck = $this->captcha_check(sanitize_text_field($_POST['g-recaptcha-response']));
									if ('' == $ccheck) {
										/* translators: %s: field label */
										$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html__( 'Invalid reCaptcha!', 'addify_b2b' ) );
									}
								} else {
									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__('%s is required.', 'addify_b2b' ) , $afreg_field->post_title ) );
								}
							}

							if ( 'fileupload' == $afreg_field_type) {

								if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required) {

									/* translators: %s: field label */
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . sprintf( esc_html__('%s is required.', 'addify_b2b' ) , $afreg_field->post_title ) );

								}

								if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && !empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required) {

									$afreg_allowed_types =  explode(',', $afreg_field_file_type);
									$afreg_filename      = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']);
									$afreg_ext           = pathinfo($afreg_filename, PATHINFO_EXTENSION);

									if (!in_array($afreg_ext, $afreg_allowed_types) ) {

										/* translators: %s: field label */
										$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( $afreg_field->post_title ) . esc_html__( ': File type is not allowed!', 'addify_b2b' ) );
									}

									if ( isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size'])) {

										$afreg_filesize = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size']);
									} else {
										$afreg_filesize = 0;
									}
								
									$afreg_allowed_size = $afreg_field_file_size * 1000000; // convert from MB to Bytes

									if ($afreg_filesize > $afreg_allowed_size) {

										/* translators: %s: field label */
										$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( $afreg_field->post_title ) . esc_html__( ': File size is too big!', 'addify_b2b' ) );

									}
								}
							}


						}




						
					}
				}
			}

			return $validation_errors;
		}


		public function afreg_save_extra_fields( $customer_id ) {
			$user = new WP_User($customer_id);
			if ( !isset( $_POST['afreg_select_user_role'] ) || empty( $_POST['afreg_select_user_role'] ) ) {
				$user->set_role( get_option('default_role') );
			}


			if (isset( $_POST['first_name'])) {

				if (!empty($_REQUEST['afreg_nonce_field'])) {

					$retrieved_nonce = sanitize_text_field($_REQUEST['afreg_nonce_field']);
				} else {
					$retrieved_nonce = 0;
				}

				if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

					echo '';
				}
			}

			//Manual Approve User

			if (isset ( $_POST['afreg_select_user_role']) && '' != $_POST['afreg_select_user_role']) {
				$default_role = sanitize_text_field($_POST['afreg_select_user_role']);
			} else {

				$default_role = get_option('default_role');
			}

			if (!empty( get_option('afreg_exclude_user_roles_approve_new_user'))) {
				$manual_user_roles = get_option('afreg_exclude_user_roles_approve_new_user');	
			} else {
				$manual_user_roles = array();
			}


			if ( !empty( get_option('afreg_enable_approve_user')) && 'yes' == get_option('afreg_enable_approve_user')) {
				


				if (! in_array($default_role, $manual_user_roles) ) {
					if (is_checkout() && 'yes' == get_option('afreg_enable_approve_user_checkout') ) {
						update_user_meta($customer_id, 'afreg_new_user_status', 'pending');
					} elseif (! is_checkout() ) {
						update_user_meta($customer_id, 'afreg_new_user_status', 'pending');
					} elseif (is_account_page() && is_wc_endpoint_url( 'edit-account' )) {

						update_user_meta($customer_id, 'afreg_new_user_status', 'approved');

					} else {
						update_user_meta($customer_id, 'afreg_new_user_status', 'approved');
					}
				}
			}

			//Default Fields

			$def_fiels_email_fields = '';
			//First Name
			if ( isset( $_POST['first_name'] ) && '' != $_POST['first_name'] ) {
				update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
				update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['first_name'] ) );

				$checkfield = $this->getFieldBySlug('first_name');

				if (!empty($checkfield)) {

					$title = $checkfield[0]->post_title;
				} else {
					$title = 'First Name';
				}
				
				$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_reg') . '</b>' . sanitize_text_field($_POST['first_name']) . '</p>';
				
			}

			  //Last Name
			if ( isset( $_POST['last_name'] ) && '' != $_POST['last_name'] ) {
				update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
				update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['last_name'] ) );

				$checkfield = $this->getFieldBySlug('last_name');

				if (!empty($checkfield)) {

					$title = $checkfield[0]->post_title;
				} else {
					$title = 'Last Name';
				}
				
				$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_reg') . '</b>' . sanitize_text_field($_POST['last_name']) . '</p>';

			}

			  //Company
			if ( isset( $_POST['billing_company'] ) ) {
				update_user_meta( $customer_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );

				$checkfield = $this->getFieldBySlug('billing_company');

				if (!empty($checkfield)) {

					$title = $checkfield[0]->post_title;
				} else {
					$title = 'Company';
				}
				
				$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_reg') . '</b>' . sanitize_text_field($_POST['billing_company']) . '</p>';

			}

			  //country
			if ( isset( $_POST['billing_country'] ) ) {
				update_user_meta( $customer_id, 'billing_country', sanitize_text_field( $_POST['billing_country'] ) );


				$checkfield = $this->getFieldBySlug('billing_country');

				if (!empty($checkfield)) {

					$title = $checkfield[0]->post_title;
				} else {
					$title = 'Country';
				}
				
				$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_reg') . '</b>' . sanitize_text_field($_POST['billing_country']) . '</p>';

			}


			  //address 1
			if ( isset( $_POST['billing_address_1'] ) ) {
				update_user_meta( $customer_id, 'billing_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );

				$checkfield = $this->getFieldBySlug('billing_address_1');

				if (!empty($checkfield)) {

					$title = $checkfield[0]->post_title;
				} else {
					$title = 'Address 1';
				}
				
				$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_reg') . '</b>' . sanitize_text_field($_POST['billing_address_1']) . '</p>';

			}

			  //address 2
			if ( isset( $_POST['billing_address_2'] ) ) {
				update_user_meta( $customer_id, 'billing_address_2', sanitize_text_field( $_POST['billing_address_2'] ) );

				$checkfield = $this->getFieldBySlug('billing_address_2');

				if (!empty($checkfield)) {

					$title = $checkfield[0]->post_title;
				} else {
					$title = 'Address 2';
				}
				
				$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_reg') . '</b>' . sanitize_text_field($_POST['billing_address_2']) . '</p>';

			}

			  //city
			if ( isset( $_POST['billing_city'] ) ) {
				update_user_meta( $customer_id, 'billing_city', sanitize_text_field( $_POST['billing_city'] ) );

				$checkfield = $this->getFieldBySlug('billing_city');

				if (!empty($checkfield)) {

					$title = $checkfield[0]->post_title;
				} else {
					$title = 'City';
				}
				
				$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_reg') . '</b>' . sanitize_text_field($_POST['billing_city']) . '</p>';

			}

			  //state
			if ( isset( $_POST['billing_state'] ) ) {
				update_user_meta( $customer_id, 'billing_state', sanitize_text_field( $_POST['billing_state'] ) );

				$checkfield = $this->getFieldBySlug('billing_state');

				if (!empty($checkfield)) {

					$title = $checkfield[0]->post_title;
				} else {
					$title = 'State';
				}
				
				$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_reg') . '</b>' . sanitize_text_field($_POST['billing_state']) . '</p>';

			}

			  //postcode
			if ( isset( $_POST['billing_postcode'] ) ) {
				update_user_meta( $customer_id, 'billing_postcode', sanitize_text_field( $_POST['billing_postcode'] ) );

				$checkfield = $this->getFieldBySlug('billing_postcode');

				if (!empty($checkfield)) {

					$title = $checkfield[0]->post_title;
				} else {
					$title = 'Post Code';
				}
				
				$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_reg') . '</b>' . sanitize_text_field($_POST['billing_postcode']) . '</p>';


			}

			  //phone
			if ( isset( $_POST['billing_phone'] ) ) {
				update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );

				$checkfield = $this->getFieldBySlug('billing_phone');

				if (!empty($checkfield)) {

					$title = $checkfield[0]->post_title;
				} else {
					$title = 'Phone';
				}
				
				$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_reg') . '</b>' . sanitize_text_field($_POST['billing_phone']) . '</p>';

			}



			$afreg_args = array( 
				'posts_per_page' => -1,
				'post_type' => 'afreg_fields',
				'post_status' => 'publish',
				'orderby' => 'menu_order',
				'suppress_filters' => false,
				'order' => 'ASC'
			);
			

				$afreg_extra_fields = get_posts($afreg_args);
			
			if (!empty($_POST['afreg_select_user_role'])) {
					

				//User Role

				if ( !empty( get_option('afreg_enable_user_role')) && 'yes' == get_option('afreg_enable_user_role')) {
					$user_roles = get_option('afreg_user_roles');
					$user       = new WP_User($customer_id);

					if (!empty( $user_roles)) {

						if (!empty($_POST['afreg_select_user_role']) && in_array($_POST['afreg_select_user_role'], $user_roles)) {

							$user->set_role(sanitize_text_field($_POST['afreg_select_user_role']));
						} else {
							$user->set_role( get_option('default_role') );
						}
					}
				}
			}

			if (!empty($afreg_extra_fields)) {

				foreach ($afreg_extra_fields as $afreg_field) {

					if (!empty(get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true ))) {

						$afreg_is_dependable = get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true );
					} else {
						$afreg_is_dependable = 'off';
					}

					$afreg_field_user_roles = get_post_meta( $afreg_field->ID, 'afreg_field_user_roles', true );
					$field_roles            = unserialize($afreg_field_user_roles);

					$afreg_field_type = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );

					if ('on' == $afreg_is_dependable && !empty($field_roles)) {

						if ( isset( $_POST['afreg_select_user_role'] ) && in_array($_POST['afreg_select_user_role'], $field_roles)) {

							if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) || isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)] ) ) {

								if ( 'fileupload' == $afreg_field_type) {

									$upload_url = wp_upload_dir();

									if ( isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && '' != $_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) { 

										$file        = time() . sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']);
										$target_path = $upload_url['basedir'] . '/addify_registration_uploads/';
										$target_path = $target_path . $file;
										if ( isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)]['tmp_name'])) {

											$temp = move_uploaded_file(sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['tmp_name']), $target_path);
										} else {

											$temp = '';
										}
										
										update_user_meta($customer_id, 'afreg_additional_' . intval($afreg_field->ID), $file);

									}

								} elseif ( 'multiselect' == $afreg_field_type) { 
									$prefix   = '';
									$multival = '';
									foreach (sanitize_meta('', $_POST['afreg_additional_' . intval($afreg_field->ID)], '') as $value) {
										$multival .= $prefix . $value;
										$prefix    = ', ';
									}
									update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), sanitize_text_field($multival) );

								} elseif ( 'multi_checkbox' == $afreg_field_type) { 
									$prefix   = '';
									$multival = '';
									foreach (sanitize_meta('', $_POST['afreg_additional_' . intval($afreg_field->ID)], '') as $value) {
										$multival .= $prefix . $value;
										$prefix    = ', ';
									}
									update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), sanitize_text_field($multival) );

								} else {

									update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), sanitize_text_field($_POST['afreg_additional_' . intval($afreg_field->ID)]));
								}

							}
						}

					} else {

						if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) || isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)] ) ) {

							if ( 'fileupload' == $afreg_field_type) {

								$upload_url = wp_upload_dir();

								if ( isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && '' != $_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) { 

									$file        = time() . sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']);
									$target_path = $upload_url['basedir'] . '/addify_registration_uploads/';
									$target_path = $target_path . $file;
									if ( isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)]['tmp_name'])) {

										$temp = move_uploaded_file(sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['tmp_name']), $target_path);
									} else {

										$temp = '';
									}
									
									update_user_meta($customer_id, 'afreg_additional_' . intval($afreg_field->ID), $file);

								}

							} elseif ( 'multiselect' == $afreg_field_type) { 
								$prefix   = '';
								$multival = '';
								foreach (sanitize_meta('', $_POST['afreg_additional_' . intval($afreg_field->ID)], '') as $value) {
									$multival .= $prefix . $value;
									$prefix    = ', ';
								}
								update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), sanitize_text_field($multival) );

							} elseif ( 'multi_checkbox' == $afreg_field_type) { 
								$prefix   = '';
								$multival = '';
								foreach (sanitize_meta('', $_POST['afreg_additional_' . intval($afreg_field->ID)], '') as $value) {
									$multival .= $prefix . $value;
									$prefix    = ', ';
								}
								update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), sanitize_text_field($multival) );

							} else {

								update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), sanitize_text_field($_POST['afreg_additional_' . intval($afreg_field->ID)]));
							}

						}

					}
				}

			}

			//Emails

			wc()->mailer();

			//Email to admin
			do_action( 'afreg_new_user_email_notification_admin', $customer_id, $def_fiels_email_fields);

			if ( ( !empty( get_option('afreg_enable_approve_user')) && 'yes' == get_option('afreg_enable_approve_user') ) && !in_array($default_role, $manual_user_roles)) {

				//Pending user email to user
				do_action( 'afreg_pending_user_email_notification_user', $customer_id, $def_fiels_email_fields);


			} else {

				//Welcome Email to user
				do_action( 'afreg_new_user_email_notification_user', $customer_id, $def_fiels_email_fields);
			}

			
			
			

		}

		

		public function afreg_update_extra_fields_my_account() {

			$user  = wp_get_current_user();
			$roles = ( array ) $user->roles;
			wp_nonce_field( 'afreg_nonce_action', 'afreg_nonce_field' );
			?>
			<div class="afreg_extra_fields">
				<h3><?php echo esc_html__(get_option('afreg_additional_fields_section_title'), 'addify_reg'); ?></h3>
				<fieldset>

				<!-- User Role -->

				<?php 

				if ( !empty( get_option('afreg_enable_user_role')) && 'yes' == get_option('afreg_enable_user_role')) {

					if ( !empty(get_option('afreg_user_role_field_text'))) {

						$role_field_label = get_option('afreg_user_role_field_text');
					} else {

						$role_field_label = 'Select User Role';
					}


					if (!empty(get_option('afreg_allow_update_myaccount')) && 'yes' == get_option('afreg_allow_update_myaccount')) { 
						?>

						<p class="form-row form-row-wide">
							<label for="afreg_user_role"><?php echo esc_html__($role_field_label, 'addify_reg'); ?><span class="required">*</span></label>
							<select class="input-select" name="afreg_select_user_role" id="afreg_select_user_role">
								<option value=""><?php echo esc_html__('---Select---', 'addify_reg'); ?></option>
								<?php
								$user_roles = get_option('afreg_user_roles');
								global $wp_rolesss;
								if ( !isset( $wp_rolesss ) ) {
									$wp_rolesss = new WP_Roles();
								}

								if ( !empty( $user_roles)) {
									foreach ( $user_roles as $key => $value) {
										?>
								<option value="<?php echo esc_attr($value); ?>" <?php echo selected($value, $roles[0]); ?>>
										
										<?php echo esc_attr(translate_user_role( $wp_rolesss->roles[$value]['name'], 'default' )); ?>
								</option>
								<?php } } ?>
							</select>
						</p>


					<?php } else { ?>


					<p class="form-row form-row-wide">
						<label for="afreg_user_role"><?php echo esc_html__($role_field_label, 'addify_reg'); ?></label>
						<input type="text" id="afreg_select_user_role" value="<?php echo esc_attr($roles[0]); ?>">
						<b><?php echo esc_attr( ucfirst($roles[0] ) ); ?></b>
					</p>

				<?php } } ?>

				


				<?php
				$afreg_args = array( 
					'posts_per_page' => -1,
					'post_type' => 'afreg_fields',
					'post_status' => 'publish',
					'orderby' => 'menu_order',
					'order' => 'ASC',
					'suppress_filters' => false,
				);

				$afreg_extra_fields = get_posts($afreg_args);

				
				if (!empty($afreg_extra_fields)) {

					foreach ($afreg_extra_fields as $afreg_field) {

						$afreg_field_type     = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
						$afreg_field_options  = unserialize(get_post_meta( intval($afreg_field->ID), 'afreg_field_option', true )); 
						$afreg_field_required = get_post_meta( intval($afreg_field->ID), 'afreg_field_required', true );
						$afreg_field_width    = get_post_meta( intval($afreg_field->ID), 'afreg_field_width', true );

						$afreg_field_show_in_my_account = get_post_meta( $afreg_field->ID, 'afreg_field_show_in_my_account', true );

						if ( !empty( get_post_meta( intval($afreg_field->ID), 'afreg_field_placeholder', true ))) {
							$afreg_field_placeholder = get_post_meta( intval($afreg_field->ID), 'afreg_field_placeholder', true );
						} else {
							$afreg_field_placeholder = '';
						}
						$afreg_field_description = get_post_meta( intval($afreg_field->ID), 'afreg_field_description', true );
						$afreg_field_css         = get_post_meta( intval($afreg_field->ID), 'afreg_field_css', true );
						$afreg_field_read_only   = get_post_meta( $afreg_field->ID, 'afreg_field_read_only', true );

						if (!empty($afreg_field_width) && 'full' == $afreg_field_width) {

							$afreg_main_class = 'form-row-wide';

						} elseif (!empty($afreg_field_width) && 'half' == $afreg_field_width) {

							$afreg_main_class = 'half_width';
						}

						$value = get_user_meta( intval($user->ID), 'afreg_additional_' . intval($afreg_field->ID), true );


						if (!empty(get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true ))) {

							$afreg_is_dependable = get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true );
						} else {
							$afreg_is_dependable = 'off';
						}

						$afreg_field_user_roles   = get_post_meta( $afreg_field->ID, 'afreg_field_user_roles', true );
						$field_roles              = unserialize($afreg_field_user_roles);
						$afreg_field_heading_type = get_post_meta( intval($afreg_field->ID), 'afreg_field_heading_type', true );

						$afreg_field_description_field = get_post_meta( intval($afreg_field->ID), 'afreg_field_description_field', true );



						

							

						if ('heading' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { 

							if (!empty($afreg_field_css)) {
								$css_class = $afreg_field_css;
							} else {
								$css_class = '';	
							}


							?>

								<<?php echo esc_attr($afreg_field_heading_type); ?> class="<?php echo esc_attr($css_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
									<?php echo wp_kses_post($afreg_field->post_title); ?>
								</<?php echo esc_attr($afreg_field_heading_type); ?>>

									<?php 
						} elseif ('description' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { 

							if (!empty($afreg_field_css)) {
								$css_class = $afreg_field_css;
							} else {
								$css_class = '';	
							}


							?>

								<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
									<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																	<?php 
																	if (!empty($afreg_field->post_title)) {
																		echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																	?>
																			</label>
									<span class="<?php echo esc_attr($css_class); ?>">
										<?php echo wp_kses_post($afreg_field_description_field); ?>
									</span>

								</p>

									<?php 
						} elseif ('text' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { 
							?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
										<span class="required">
											<?php 
											if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
												?>
												*
												<?php
											} 
											?>
											</span></label>

											<?php 
											if ( 'on' == $afreg_field_read_only) { 
												echo esc_attr($value);
											} else { 
												?>

										<input type="text" class="input-text 
												<?php 
												if (!empty($afreg_field_css)) {
													echo esc_attr($afreg_field_css);} 
												?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr( $afreg_field_placeholder ); ?>" />
												<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

									<?php } elseif ( 'textarea' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>
										<?php 
										if ( 'on' == $afreg_field_read_only) { 
											echo esc_attr($value);
										} else { 
											?>

										<textarea class="input-text 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>"><?php echo esc_attr($value); ?></textarea>
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

								<?php } elseif ( 'email' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ( 'on' == $afreg_field_read_only ) { 
											echo esc_attr($value);
										} else { 
											?>

										<input type="text" class="input-text 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr( $afreg_field_placeholder ); ?>" />
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

								<?php } elseif ( 'select' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ( 'on' == $afreg_field_read_only) { 
											foreach ($afreg_field_options as $afreg_field_option) {
												if ( esc_attr($value) == esc_attr($afreg_field_option['field_value']) ) {
													echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg');
												}
												
											}
										} else { 
											?>

										<select class="input-select 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
											<?php foreach ($afreg_field_options as $afreg_field_option) { ?>
												<option value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" 
																		  <?php 
																			
																				echo selected(esc_attr($value), esc_attr($afreg_field_option['field_value']));
																			
																			?>
												>
													<?php 
													if (!empty($afreg_field_option['field_text'])) {
														echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg');} 
													?>
												</option>
											<?php } ?>
										</select>
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

								<?php } elseif ( 'multiselect' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ( 'on' == $afreg_field_read_only) {
											$val_array            = explode(', ' , $value );
											 $afreg_field_options = unserialize(get_post_meta(  intval($afreg_field->ID) , 'afreg_field_option', true )); 
											 $value               = array();
											foreach ( $val_array as $option_val ) {
												foreach ($afreg_field_options as $afreg_field_option ) { 
													if ( $afreg_field_option['field_value'] == $option_val ) {
														$value[] =  $afreg_field_option['field_text'] ;
													}
												}
											}
											echo esc_attr( implode(', ' , $value ) );
										} else { 
											?>

										<select class="input-select 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>[]" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" multiple>
											<?php 
											foreach ($afreg_field_options as $afreg_field_option) {

												$db_values = explode(', ', $value);

												if (!empty($db_values)) {
													?>
													<option value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" 
																			  <?php 
																				if (in_array(esc_attr($afreg_field_option['field_value']), $db_values)) {
																					echo 'selected';} 
																				?>
													>
														<?php echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg'); ?>
												<?php } else { ?>
												<option value="<?php echo esc_attr($afreg_field_option['field_value']); ?>">
													<?php echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg'); ?>
												</option>
											<?php } } ?>
										</select>
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

									<?php 
								} elseif ('multi_checkbox' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) {
									
									?>
									 

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ('on' == $afreg_field_read_only) { 
											$val_array            = explode(', ' , $value );
											 $afreg_field_options = unserialize(get_post_meta(  intval($afreg_field->ID) , 'afreg_field_option', true )); 
											 $value               = array();
											foreach ( $val_array as $option_val ) {
												foreach ($afreg_field_options as $afreg_field_option ) { 
													if ( $afreg_field_option['field_value'] == $option_val ) {
														$value[] =  $afreg_field_option['field_text'] ;
													}
												}
											}
											echo esc_attr( implode(', ' , $value ) );
										} else { 
											?>


											<?php 
											foreach ($afreg_field_options as $afreg_field_option) {

												$db_values = explode(', ', $value);
												?>
												<input type="checkbox" class="input-checkbox 
													<?php 
													if (!empty($afreg_field_css)) {
														echo esc_attr($afreg_field_css);} 
													?>
													" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>[]" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" 
														<?php 
															
														if (in_array(esc_attr($afreg_field_option['field_value']), $db_values)) {
															echo 'checked';
														}
															
														
														?>
														 />
													<span class="afreg_checkbox">
														<?php 
														if (!empty($afreg_field_option['field_text'])) {
															echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg');} 
														?>
													</span>
												<?php } ?>
										
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

									<?php 
								} elseif ('checkbox' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) {
									
									?>
									 

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ('on' == $afreg_field_read_only) { 
											echo esc_attr($value);
										} else { 
											?>


											<input <?php echo checked('yes', esc_attr($value)); ?> type="checkbox" class="input-checkbox 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
									" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="yes" />
											
										
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

								<?php } elseif ( 'radio' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ( 'on' == $afreg_field_read_only) { 
											foreach ($afreg_field_options as $afreg_field_option) {
												if ( esc_attr($value) == esc_attr($afreg_field_option['field_value']) ) {
													echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg') ;
												}
												
											}
										} else { 
											?>
										
											<?php foreach ($afreg_field_options as $afreg_field_option) { ?>
											<input type="radio" class="input-radio 
												<?php 
												if (!empty($afreg_field_css)) {
													echo esc_attr($afreg_field_css);} 
												?>
											" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" 
												<?php 
												
													echo checked(esc_attr($value), esc_attr($afreg_field_option['field_value']));
												
												?>
											  />
											  <span class="afreg_radio">
												<?php 
												if (!empty($afreg_field_option['field_text'])) {
													echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg');} 
												?>
											</span>

											
												<?php
											} 
											?>
										
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message_radio"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

								<?php } elseif ( 'number' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required ) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ( 'on' == $afreg_field_read_only) { 
											echo esc_attr($value);
										} else { 
											?>

										<input type="number" class="input-text 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr( $afreg_field_placeholder); ?>" />
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

								<?php } elseif ( 'password' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ('on' == $afreg_field_read_only ) { 
											echo esc_attr($value);
										} else { 
											?>

										<input type="password" class="input-text 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr( $afreg_field_placeholder); ?>" />
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

								<?php } elseif ( 'fileupload' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="<?php echo esc_attr($afreg_field->field_name); ?>"><?php echo esc_html__('Current', 'addify_reg'); ?> <?php 
										if (!empty($afreg_field->post_title)) {
											echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
										?>
										</label>

										<?php 

										$upload_url = wp_upload_dir();

										$current_file = '';

										$curr_image_new_folder = $upload_url['basedir'] . '/addify_registration_uploads/' . $value;
						
										$curr_image = esc_url(AFREG_URL . 'uploaded_files/' . $value);

										if (file_exists($curr_image_new_folder)) {

											$current_file = esc_url($upload_url['baseurl'] . '/addify_registration_uploads/' . $value);

										} elseif (file_exists($curr_image)) {

											$current_file = esc_url(AFREG_URL . 'uploaded_files/' . $value);

										}

										if (!empty($value)) {
											$ext = pathinfo($current_file, PATHINFO_EXTENSION);
											if ( 'jpg' == $ext || 'JPG' == $ext || 'jpeg' == $ext || 'JPEG' == $ext || 'png' == $ext || 'PNG' == $ext || 'gif' == $ext || 'GIF' == $ext || 'bmp' == $ext || 'BMP' == $ext) { 
												?>
												<a href="<?php echo esc_url($current_file); ?>" target="_blank">
												<img src="<?php echo esc_url($current_file); ?>" width="150" height="150" />
											
											</a>
											<?php } else { ?>

												<a href="<?php echo esc_url($current_file); ?>" target="_blank">
													<img src="<?php echo esc_url(AFREG_URL); ?>images/file_icon.png" width="150" height="150" title="Click to View" />
												</a>
											
										<?php } } ?>

										<?php 
										if ('on' == $afreg_field_read_only) { 
											echo '';
										} else { 
											?>

										<input type="hidden"  value="<?php echo esc_attr($value); ?>" id="curr_afreg_additional_<?php echo intval($afreg_field->ID); ?>" name="curr_afreg_additional_<?php echo intval($afreg_field->ID); ?>">

										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
											<?php 
											if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
												?>
												*
												<?php
											} 
											?>
											</span></label>
										<input type="file" class="input-text 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="" placeholder="<?php echo esc_attr( $afreg_field_placeholder); ?>" />
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

								<?php } elseif ( 'color' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>
									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ( 'on' == $afreg_field_read_only) { 
											echo esc_attr($value);
										} else { 
											?>

										<input type="color" class="input-text color_sepctrumm 
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr( $afreg_field_placeholder); ?>" />
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message_radio"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>


										<script>
								
										jQuery(".color_sepctrumm").spectrum({
											color: "<?php echo esc_attr($value); ?>",
											preferredFormat: "hex",
										});

										</script>
									</p>

								<?php } elseif ( 'datepicker' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ( 'on' == $afreg_field_read_only) { 
											echo esc_attr($value);
										} else { 
											?>

										<input type="date" class="input-text  
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr( $afreg_field_placeholder); ?>" />
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message_radio"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

								<?php } elseif ( 'timepicker' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">
										<?php 
										if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
											?>
												*
												<?php
										} 
										?>
											</span></label>

										<?php 
										if ( 'on' == $afreg_field_read_only ) { 
											echo esc_attr($value);
										} else { 
											?>

										<input type="time" class="input-text  
											<?php 
											if (!empty($afreg_field_css)) {
												echo esc_attr($afreg_field_css);} 
											?>
										" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr( $afreg_field_placeholder); ?>" />
											<?php if (!empty($afreg_field_description)) { ?>
												<br>
											<span class="afreg_field_message_radio"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } } ?>
									</p>

								<?php } elseif ( 'googlecaptcha' == $afreg_field_type && 'off' != $afreg_field_show_in_my_account && !is_checkout() ) { ?>

									<p class="form-row <?php echo esc_attr($afreg_main_class); ?>">
										<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																				<?php 
																				if (!empty($afreg_field->post_title)) {
																					echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																				?>
										<span class="required">*</span></label>
										
										<div class="g-recaptcha" data-sitekey="<?php echo esc_attr(get_option('afreg_site_key')); ?>"></div>

										<?php if (!empty($afreg_field_description)) { ?>
											<br>
											<span class="afreg_field_message_radio"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
										<?php } ?>
									</p>

									<?php 
								}


							


						
								?>

						<!-- Dependable -->
								<?php if ('on' == $afreg_is_dependable && !empty($field_roles)) { ?>

									<style>
										#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?> { display: none; }
									</style>

								<?php } ?>

								<script>
									
									jQuery(document).on('change', '#afreg_select_user_role', function() {

										var val = this.value;
										var field_roles = new Array();
										var is_dependable = '<?php echo esc_attr($afreg_is_dependable); ?>';
											
											<?php if ( !empty($field_roles)) { ?>
												<?php foreach ($field_roles as $key => $value) { ?>

													field_roles.push('<?php echo esc_attr($value); ?>');

												<?php } ?>

												var match_val = field_roles.includes(val);

												if (match_val == true && is_dependable == 'on') {


													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												} else if (match_val == false && is_dependable == 'on') {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').hide();
												} else {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												}

											<?php } ?>


									});
									jQuery(document).on('ready' , function() {

										var val = jQuery('#afreg_select_user_role').val();
										var field_roles = new Array();
										var is_dependable = '<?php echo esc_attr($afreg_is_dependable); ?>';
											
											<?php if ( !empty($field_roles)) { ?>
												<?php foreach ($field_roles as $key => $value) { ?>

													field_roles.push('<?php echo esc_attr($value); ?>');

												<?php } ?>

												var match_val = field_roles.includes(val);

												if (match_val == true && is_dependable == 'on') {


													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												} else if (match_val == false && is_dependable == 'on') {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').hide();
												} else {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												}

											<?php } ?>


									});

								</script>
								<?php 

						
					}
				}


				?>
			</fieldset>
			</div>
			
			<?php 
		}

		public function afreg_validate_update_role_my_account( $validation_errors) {

			$afreg_allowed_tags = array(
			'strong' => array(),
			);


			if ( isset( $_POST['afreg_select_user_role'] ) && empty( $_POST['afreg_select_user_role'] ) ) {

				if ( !empty(get_option('afreg_user_role_field_text'))) {

					$role_field_label = get_option('afreg_user_role_field_text');
				} else {

					$role_field_label = 'Select User Role';
				}

				$validation_errors->add( 'afreg_select_user_role_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html(__( $role_field_label , 'addify_reg') ) . esc_html__( ' is required.', 'addify_b2b' ) );
			}

			$afreg_args = array( 
				'posts_per_page' => -1,
				'post_type' => 'afreg_fields',
				'post_status' => 'publish',
				'orderby' => 'menu_order',
				'suppress_filters' => false,
				'order' => 'ASC'
			);

			$afreg_extra_fields = get_posts($afreg_args);
			if (!empty($afreg_extra_fields)) {

				if (!empty($_REQUEST['afreg_nonce_field'])) {

					$retrieved_nonce = sanitize_text_field($_REQUEST['afreg_nonce_field']);
				} else {
						$retrieved_nonce = 0;
				}

				if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

					echo '';
				}

				$user  = wp_get_current_user();
				$roles = ( array ) $user->roles;

				foreach ($afreg_extra_fields as $afreg_field) {

					$afreg_field_required  = get_post_meta( intval($afreg_field->ID), 'afreg_field_required', true );
					$afreg_field_type      = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
					$afreg_field_file_type = get_post_meta( intval($afreg_field->ID), 'afreg_field_file_type', true );
					$afreg_field_file_size = get_post_meta( intval($afreg_field->ID), 'afreg_field_file_size', true );
					$afreg_field_read_only = get_post_meta( $afreg_field->ID, 'afreg_field_read_only', true );



					if (!empty(get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true ))) {

						$afreg_is_dependable = get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true );
					} else {
						$afreg_is_dependable = 'off';
					}

					$afreg_field_user_roles = get_post_meta( $afreg_field->ID, 'afreg_field_user_roles', true );
					$field_roles            = unserialize($afreg_field_user_roles);

					if ('on' == $afreg_is_dependable && !empty($field_roles)) {

						if ( !empty($_POST['afreg_select_user_role']) && in_array($_POST['afreg_select_user_role'], $field_roles)) {

							if ( 'fileupload' != $afreg_field_type) {
								if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
								}
							}

							if ( 'email' == $afreg_field_type) {

								if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_EMAIL) ) {

									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is not a valid email address!', 'addify_reg' ) );
								}

								if ( empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
								}

							}

							if ( 'multiselect' == $afreg_field_type) {
							
								if (empty($_POST['afreg_additional_' . intval($afreg_field->ID)]) && 'on' == $afreg_field_required) {
									
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
									
								}
							}

							if ( 'number' == $afreg_field_type) {

								if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_INT) ) {

									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is not a valid number!', 'addify_reg' ) );
								}

								if ( empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
								}

							}

							if ( 'multi_checkbox' == $afreg_field_type || 'checkbox' == $afreg_field_type || 'radio' == $afreg_field_type) { 

								if ( !isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
								}

							}

							if ( 'googlecaptcha' == $afreg_field_type) { 
							
								if (isset($_POST['g-recaptcha-response']) && '' != $_POST['g-recaptcha-response']) {
									$ccheck = $this->captcha_check(sanitize_text_field($_POST['g-recaptcha-response']));
									if ( 'error' == $ccheck) {
										$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( 'Invalid reCaptcha!', 'addify_reg' ) );
									}
								} else {
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
								}
							}

							if ( 'fileupload' == $afreg_field_type) {


								if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && !empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required) {

									$afreg_allowed_types =  explode(',', $afreg_field_file_type);
									$afreg_filename      = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']);
									$afreg_ext           = pathinfo($afreg_filename, PATHINFO_EXTENSION);

									if (!in_array($afreg_ext, $afreg_allowed_types) ) {

										$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ': File type is not allowed!', 'addify_reg' ) );
									}

									if ( isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)]['size'])) {
										$afreg_filesize = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size']);	
									} else {
										$afreg_filesize = '';
									}
									
									$afreg_allowed_size = $afreg_field_file_size * 1000000; // convert from MB to Bytes

									if ($afreg_filesize > $afreg_allowed_size) {

										$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ': File size is too big!', 'addify_reg' ) );

									}
								}
							}
						}

					} else {

						if ( 'fileupload' != $afreg_field_type) {
							if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
							}
						}

						if ( 'email' == $afreg_field_type) {

							if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_EMAIL) ) {

								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is not a valid email address!', 'addify_reg' ) );
							}

							if ( empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
							}

						}

						if ( 'multiselect' == $afreg_field_type) {
						
							if (empty($_POST['afreg_additional_' . intval($afreg_field->ID)]) && 'on' == $afreg_field_required) {
								
								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
								
							}
						}

						if ( 'number' == $afreg_field_type) {

							if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_INT) ) {

								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is not a valid number!', 'addify_reg' ) );
							}

							if ( empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
							}

						}

						if ( ( 'multi_checkbox' == $afreg_field_type || 'checkbox' == $afreg_field_type || 'radio' == $afreg_field_type ) && 'on' != $afreg_field_read_only ) { 

							if ( !isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
							}

						}

						if ( 'googlecaptcha' == $afreg_field_type) { 
						
							if (isset($_POST['g-recaptcha-response']) && '' != $_POST['g-recaptcha-response']) {
								$ccheck = $this->captcha_check(sanitize_text_field($_POST['g-recaptcha-response']));
								if ( 'error' == $ccheck) {
									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( 'Invalid reCaptcha!', 'addify_reg' ) );
								}
							} else {
								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
							}
						}

						if ( 'fileupload' == $afreg_field_type) {


							if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && !empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required) {

								$afreg_allowed_types =  explode(',', $afreg_field_file_type);
								$afreg_filename      = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']);
								$afreg_ext           = pathinfo($afreg_filename, PATHINFO_EXTENSION);

								if (!in_array($afreg_ext, $afreg_allowed_types) ) {

									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ': File type is not allowed!', 'addify_reg' ) );
								}

								if ( isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)]['size'])) {
									$afreg_filesize = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size']);	
								} else {
									$afreg_filesize = '';
								}
								
								$afreg_allowed_size = $afreg_field_file_size * 1000000; // convert from MB to Bytes

								if ($afreg_filesize > $afreg_allowed_size) {

									$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ': File size is too big!', 'addify_reg' ) );

								}
							}
						}

					}
				}
			}

		}

		public function afreg_get_user_role( $user_id) {
			global $wp_roles;

			$roles = array();
			$user  = new WP_User( $user_id );
			if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
				foreach ( $user->roles as $role ) {
					$roles[] .= translate_user_role( $role );
				}
			}
			return implode(', ', $roles);
		} 

		public function afreg_save_update_role_my_account( $customer_id) {

			

			if (!empty($_REQUEST['afreg_nonce_field'])) {

				$retrieved_nonce = sanitize_text_field($_REQUEST['afreg_nonce_field']);
			} else {
					$retrieved_nonce = 0;
			}

			if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

				echo '';
			}

			$new_user_role = '';

			if (!empty($_POST['afreg_select_user_role'])) {

				$new_user_role = sanitize_text_field($_POST['afreg_select_user_role']);
					

				//User Role

				if ( !empty( get_option('afreg_enable_user_role')) && 'yes' == get_option('afreg_enable_user_role')) {
					$user_roles = get_option('afreg_user_roles');
					$user       = new WP_User($customer_id);

					if (!empty( $user_roles)) {

						if (!empty($_POST['afreg_select_user_role']) && in_array($_POST['afreg_select_user_role'], $user_roles)) {

							$user->set_role($new_user_role);
						} else {
							$user->set_role( get_option('default_role') );
						}
					}
				}
			} else {

				$new_user_role = $this->afreg_get_user_role($customer_id);

			}

			$afreg_args = array( 
				'posts_per_page' => -1,
				'post_type' => 'afreg_fields',
				'post_status' => 'publish',
				'orderby' => 'menu_order',
				'suppress_filters' => false,
				'order' => 'ASC'
			);

			$afreg_extra_fields = get_posts($afreg_args);

			if (!empty($afreg_extra_fields)) {

				

				foreach ($afreg_extra_fields as $afreg_field) {

					$afreg_field_type = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );

					$afreg_field_read_only = get_post_meta( $afreg_field->ID, 'afreg_field_read_only', true );

					if ('on' != $afreg_field_read_only) {


						if ('checkbox' == $afreg_field_type) {

							if (!isset($_POST['afreg_additional_' . intval($afreg_field->ID)])) {

								update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), 'no');


							} else {

								update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), sanitize_text_field($_POST['afreg_additional_' . intval($afreg_field->ID)]));
							}


						}

						if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) || isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)] ) ) {

							if ( 'fileupload' == $afreg_field_type) {

								$upload_url = wp_upload_dir();

								if ( isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && '' != $_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) { 

									if ( isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)]['name'])) {
										$file = time() . sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']);
									} else {
										$file = '';
									}
									
									$target_path = $upload_url['basedir'] . '/addify_registration_uploads/';
									$target_path = $target_path . $file;
									if ( isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)]['tmp_name'])) {
										$temp = move_uploaded_file(sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['tmp_name']), $target_path);
									} else {
										$temp = '';
									}
									
									update_user_meta($customer_id, 'afreg_additional_' . intval($afreg_field->ID), $file);

								}

							} elseif ( 'multiselect' == $afreg_field_type) { 
								$prefix   = '';
								$multival = '';
								foreach (sanitize_meta('', $_POST['afreg_additional_' . intval($afreg_field->ID)], '') as $value) {
									$multival .= $prefix . $value;
									$prefix    = ', ';
								}
								update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), sanitize_text_field($multival) );

							} elseif ( 'multi_checkbox' == $afreg_field_type) { 
								$prefix   = '';
								$multival = '';
								foreach (sanitize_meta('', $_POST['afreg_additional_' . intval($afreg_field->ID)], '') as $value) {
									$multival .= $prefix . $value;
									$prefix    = ', ';
								}
								update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), sanitize_text_field($multival) );

							} else {

								update_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), sanitize_text_field($_POST['afreg_additional_' . intval($afreg_field->ID)]));
							}

						} 
					}
				}

			}


			//Emails

			wc()->mailer();

			//update user email to admin
			do_action( 'afreg_update_user_email_notification_admin', $customer_id, $new_user_role);

		}

		public function afreg_extra_fields_show_wordpress() {
			wp_nonce_field( 'afreg_nonce_action', 'afreg_nonce_field' );
			?>
			<div class="wordpress_additional">
				<h3><?php echo esc_html__(get_option('afreg_additional_fields_section_title'), 'addify_reg'); ?></h3>
				<?php

				if ( !empty( get_option('afreg_enable_user_role')) && 'yes' == get_option('afreg_enable_user_role')) {

					if ( !empty(get_option('afreg_user_role_field_text'))) {

						$role_field_label = get_option('afreg_user_role_field_text');
					} else {

						$role_field_label = 'Select User Role';
					}

					//When error values should stay
					if (!empty($_POST['afreg_select_user_role'])) {

						if (!empty($_REQUEST['afreg_nonce_field'])) {

							$retrieved_nonce = sanitize_text_field($_REQUEST['afreg_nonce_field']);
						} else {
								$retrieved_nonce = 0;
						}

						if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

							echo '';
						}

						$vall =  sanitize_text_field( $_POST['afreg_select_user_role']);
					} else {
						$vall = '';
					}

					?>
				<p class="form-row-wordpress">
					<label for="afreg_user_role"><?php echo esc_html__($role_field_label, 'addify_reg'); ?><span class="required">*</span></label>
					<select class="input input-select" name="afreg_select_user_role" id="afreg_select_user_role">
						<option value=""><?php echo esc_html__('---Select---', 'addify_reg'); ?></option>
						<?php
						$user_roles = get_option('afreg_user_roles');
						global $wp_rolesss;
						if ( !isset( $wp_rolesss ) ) {
							$wp_rolesss = new WP_Roles();
						}

						if ( !empty( $user_roles)) {
							foreach ( $user_roles as $key => $value) {
								?>
						<option value="<?php echo esc_attr($value); ?>" <?php echo selected($value, $vall); ?>>
								<?php echo esc_attr($wp_rolesss->roles[$value]['name']); ?>
						</option>
						<?php } } ?>
					</select>
				</p>
					<?php 
				}

				$afreg_args         = array( 
					'posts_per_page' => -1,
					'post_type' => 'afreg_fields',
					'post_status' => 'publish',
					'orderby' => 'menu_order',
					'suppress_filters' => false,
					'order' => 'ASC'
				);
				$afreg_extra_fields = get_posts($afreg_args);
				if (!empty($afreg_extra_fields)) {

					foreach ($afreg_extra_fields as $afreg_field) {

						$afreg_field_type        = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
						$afreg_field_options     = unserialize(get_post_meta( intval($afreg_field->ID), 'afreg_field_option', true )); 
						$afreg_field_required    = get_post_meta( intval($afreg_field->ID), 'afreg_field_required', true );
						$afreg_field_description = get_post_meta( intval($afreg_field->ID), 'afreg_field_description', true );
						$afreg_field_width       = get_post_meta( intval($afreg_field->ID), 'afreg_field_width', true );

						if (!empty($afreg_field_width) && 'full' == $afreg_field_width) {

							$afreg_main_class = 'form-row-wide';

						} elseif (!empty($afreg_field_width) && 'half' == $afreg_field_width) {

							$afreg_main_class = 'form-row-wide';
						}

						if (!empty(get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true ))) {

							$afreg_is_dependable = get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true );
						} else {
							$afreg_is_dependable = 'off';
						}

						$afreg_field_user_roles   = get_post_meta( $afreg_field->ID, 'afreg_field_user_roles', true );
						$field_roles              = unserialize($afreg_field_user_roles);
						$afreg_field_heading_type = get_post_meta( intval($afreg_field->ID), 'afreg_field_heading_type', true );

						$afreg_field_description_field = get_post_meta( intval($afreg_field->ID), 'afreg_field_description_field', true );

						if ('heading' == $afreg_field_type) { 

							if (!empty($afreg_field_css)) {
								$css_class = $afreg_field_css;
							} else {
								$css_class = '';	
							}


							?>

								<<?php echo esc_attr($afreg_field_heading_type); ?> class="<?php echo esc_attr($css_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<?php echo wp_kses_post($afreg_field->post_title); ?>
								</<?php echo esc_attr($afreg_field_heading_type); ?>>

							<?php 
						} elseif ('description' == $afreg_field_type && 'off' != $afreg_field_show_in_registration_form) { 

							if (!empty($afreg_field_css)) {
								$css_class = $afreg_field_css;
							} else {
								$css_class = '';	
							}


							?>

								<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
									<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																			<?php 
																			if (!empty($afreg_field->post_title)) {
																				echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																			?>
																			</label>
									<span class="<?php echo esc_attr($css_class); ?>">
									<?php echo wp_kses_post($afreg_field_description_field); ?>
									</span>

								</p>

							<?php 
						} elseif ('text' == $afreg_field_type) { 
							?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<input type="text" class="input" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="" />
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ('textarea' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required ) {
									?>
										*
										<?php
								} 
								?>
								</span></label>
								<textarea rows="7" cols="31" class="input" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>"></textarea>
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ('email' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<input type="text" class="input" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="" />
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ( 'select' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<select class="inputselect" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
									<?php foreach ($afreg_field_options as $afreg_field_option) { ?>
										<option value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" >
											<?php 
											if (!empty($afreg_field_option['field_text'])) {
												echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg');} 
											?>
										</option>
									<?php } ?>
								</select>
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ( 'multiselect' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<select class="inputmselect" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>[]" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" multiple>
									<?php 
									foreach ($afreg_field_options as $afreg_field_option) {
										?>
										<option value="<?php echo esc_attr($afreg_field_option['field_value']); ?>">
											<?php echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg'); ?>
										</option>
									<?php } ?>
								</select>
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ( 'multi_checkbox' == $afreg_field_type) { ?> 

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<?php foreach ($afreg_field_options as $afreg_field_option) { ?>
									<input type="checkbox" class="inputradio" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>[]" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" />
									<span class="afreg_radio">
									<?php 
									if (!empty($afreg_field_option['field_text'])) {
										echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg');} 
									?>
									</span>
								<?php } ?>
								
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress_checkbox"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ( 'checkbox' == $afreg_field_type) { ?> 

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								
								<input type="checkbox" class="inputcheckbox" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="yes" />
								
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress_checkbox"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ( 'radio' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								
								<?php foreach ($afreg_field_options as $afreg_field_option) { ?>
									<input type="radio" class="inputradio" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="<?php echo esc_attr($afreg_field_option['field_value']); ?>" />
									<span class="afreg_radio">
									<?php 
									if (!empty($afreg_field_option['field_text'])) {
										echo esc_html__(esc_attr($afreg_field_option['field_text']), 'addify_reg');} 
									?>
									</span>
								<?php } ?>
								
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_radio_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ( 'number' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<input type="number" class="input inputnumb" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="" />
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ( 'password' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<input type="password" class="input" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="" />
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo esc_html__($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ('fileupload' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<input type="file" class="input 
								<?php 
								if (!empty($afreg_field_css)) {
									echo esc_attr($afreg_field_css);} 
								?>
								" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="" placeholder="
							<?php 
							if (!empty($afreg_field_placeholder)) {
									echo esc_html__($afreg_field_placeholder , 'addify_reg' );} 
							?>
" />
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ( 'color' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<input type="color" class="input color_sepctrum" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="" />
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ( 'datepicker' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<input type="date" class="input" name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="" />
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ( 'timepicker' == $afreg_field_type) { ?>

							<p class="form-row-wordpress <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">
								<?php 
								if (!empty($afreg_field_required) && 'on' == $afreg_field_required) {
									?>
										*
										<?php
								} 
								?>
									
								</span></label>
								<input type="time" class="input " name="afreg_additional_<?php echo intval($afreg_field->ID); ?>" id="afreg_additional_<?php echo intval($afreg_field->ID); ?>" value="" />
								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

						<?php } elseif ('googlecaptcha' == $afreg_field_type) { ?>

							<p class="form-row <?php echo esc_attr($afreg_main_class); ?>" id="afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>">
								<label for="afreg_additional_<?php echo intval($afreg_field->ID); ?>">
																		<?php 
																		if (!empty($afreg_field->post_title)) {
																			echo esc_html__($afreg_field->post_title , 'addify_reg' );} 
																		?>
								<span class="required">*</span></label>
								
								<div class="g-recaptcha" data-sitekey="<?php echo esc_attr(get_option('afreg_site_key')); ?>"></div>

								<?php if (!empty($afreg_field_description)) { ?>
									<br>
									<span class="afreg_field_message_wordpress"><?php echo wp_kses_post($afreg_field_description, 'addify_reg'); ?></span>
								<?php } ?>
							</p>

							<?php 
						}

						?>

							<!-- Dependable -->
								<?php if ('on' == $afreg_is_dependable && !empty($field_roles)) { ?>

									<style>
										#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?> { display: none; }
									</style>

								<?php } ?>

								<script>

									jQuery(document).on('change', '#afreg_select_user_role', function() {

										var val = this.value;
										var field_roles = new Array();
										var is_dependable = '<?php echo esc_attr($afreg_is_dependable); ?>';
											
											<?php if ( !empty($field_roles)) { ?>
												<?php foreach ($field_roles as $key => $value) { ?>

													field_roles.push('<?php echo esc_attr($value); ?>');

												<?php } ?>

												var match_val = field_roles.includes(val);

												if (match_val == true && is_dependable == 'on') {


													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												} else if (match_val == false && is_dependable == 'on') {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').hide();
												} else {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												}

											<?php } ?>


									});
									jQuery(document).on('ready' , function() {

										var val = jQuery('#afreg_select_user_role').val();
										var field_roles = new Array();
										var is_dependable = '<?php echo esc_attr($afreg_is_dependable); ?>';
											
											<?php if ( !empty($field_roles)) { ?>
												<?php foreach ($field_roles as $key => $value) { ?>

													field_roles.push('<?php echo esc_attr($value); ?>');

												<?php } ?>

												var match_val = field_roles.includes(val);

												if (match_val == true && is_dependable == 'on') {


													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												} else if (match_val == false && is_dependable == 'on') {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').hide();
												} else {

													jQuery('#afreg_additionalshowhide_<?php echo intval($afreg_field->ID); ?>').show();

												}

											<?php } ?>


									});

								</script>

						<?php

					}
				}
				?>
			</div>
			<?php 
		}

		public function aferg_wordpress_registration_errors( $validation_errors, $sanitized_user_login, $user_email) {

			$afreg_args = array( 
				'posts_per_page' => -1,
				'post_type' => 'afreg_fields',
				'post_status' => 'publish',
				'orderby' => 'menu_order',
				'suppress_filters' => false,
				'order' => 'ASC'
			);

			$afreg_extra_fields = get_posts($afreg_args);
			if (!empty($afreg_extra_fields)) {

				if (!empty($_REQUEST['afreg_nonce_field'])) {

					$retrieved_nonce = sanitize_text_field($_REQUEST['afreg_nonce_field']);
				} else {
						$retrieved_nonce = 0;
				}

				if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

					echo '';
				}

				if ( !empty( get_option('afreg_enable_user_role')) && 'yes' == get_option('afreg_enable_user_role')) {
					if ( isset( $_POST['afreg_select_user_role'] ) && empty( $_POST['afreg_select_user_role'] ) ) {

						if ( !empty(get_option('afreg_user_role_field_text'))) {

							$role_field_label = get_option('afreg_user_role_field_text');
						} else {

							$role_field_label = 'Select User Role';
						}

						$validation_errors->add( 'afreg_select_user_role_error', esc_html__( $role_field_label . ' is required!', 'addify_reg' ) );
					}
				}

				foreach ($afreg_extra_fields as $afreg_field) {

					$afreg_field_required  = get_post_meta( intval($afreg_field->ID), 'afreg_field_required', true );
					$afreg_field_type      = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
					$afreg_field_file_type = get_post_meta( intval($afreg_field->ID), 'afreg_field_file_type', true );
					$afreg_field_file_size = get_post_meta( intval($afreg_field->ID), 'afreg_field_file_size', true );

					if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

						$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
					}

					if ('email' == $afreg_field_type) {

						if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_EMAIL) ) {

							$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is not a valid email address!', 'addify_reg' ) );
						}

					}

					if ('multiselect' == $afreg_field_type) {
					
						if (empty($_POST['afreg_additional_' . intval($afreg_field->ID)]) && 'on' == $afreg_field_required) {
							
							$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
							
						}
					}

					if ('number' == $afreg_field_type) {

						if ( isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && !empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) && !filter_var($_POST['afreg_additional_' . intval($afreg_field->ID)], FILTER_VALIDATE_INT) ) {

							$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is not a valid number!', 'addify_reg' ) );
						}

					}

					if ( 'checkbox' == $afreg_field_type || 'radio' == $afreg_field_type) { 

						if ( !isset( $_POST['afreg_additional_' . intval($afreg_field->ID)] ) && ( 'on' == $afreg_field_required ) ) {

							$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
						}

					}

					if ( 'googlecaptcha' == $afreg_field_type) { 
					
						if (isset($_POST['g-recaptcha-response']) && '' != $_POST['g-recaptcha-response']) {
							$ccheck = $this->captcha_check(sanitize_text_field($_POST['g-recaptcha-response']));
							if ('error' == $ccheck) {
								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( 'Invalid reCaptcha!', 'addify_reg' ) );
							}
						} else {
							$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );
						}
					}

					if ('fileupload' == $afreg_field_type) {

						if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on' == $afreg_field_required) {

							$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ' is required!', 'addify_reg' ) );

						}

						if (isset($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && !empty($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']) && 'on ' == $afreg_field_required) {

							$afreg_allowed_types =  explode(',', $afreg_field_file_type);
							$afreg_filename      = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['name']);
							$afreg_ext           = pathinfo($afreg_filename, PATHINFO_EXTENSION);

							if (!in_array($afreg_ext, $afreg_allowed_types) ) {

								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ': File type is not allowed!', 'addify_reg' ) );
							}

							if ( isset( $_FILES['afreg_additional_' . intval($afreg_field->ID)]['size'])) {
								$afreg_filesize = sanitize_text_field($_FILES['afreg_additional_' . intval($afreg_field->ID)]['size']);
							} else {
								$afreg_filesize = '';
							}
							
							$afreg_allowed_size = $afreg_field_file_size * 1000000; // convert from MB to Bytes

							if ($afreg_filesize > $afreg_allowed_size) {

								$validation_errors->add( 'afreg_additional_' . intval($afreg_field->ID) . '_error', esc_html__( $afreg_field->post_title . ': File size is too big!', 'addify_reg' ) );

							}
						}
					}
				}
			}

			return $validation_errors;
		}


		public function captcha_check( $res) {

				$secret = get_option('afreg_secret_key');
	   
				$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $res);
				
				$responseData = json_decode($verifyResponse);

			if ($responseData->success) {
				return 'success';
			} else {
				return 'error';
			}
		}

		//Manual Approve Users

		public function afreg_user_autologout() {

			if ( is_user_logged_in() ) {

				if ( !empty( get_option('afreg_enable_approve_user')) && 'yes' == get_option('afreg_enable_approve_user')) {

					$current_user = wp_get_current_user();
					$user_id      = $current_user->ID;

					$roles = ( array ) $current_user->roles;

					$default_role = $roles[0];

					if (!empty( get_option('afreg_exclude_user_roles_approve_new_user'))) {
						$manual_user_roles = get_option('afreg_exclude_user_roles_approve_new_user');	
					} else {
						$manual_user_roles = array();
					}


					if (!in_array( $default_role, $manual_user_roles)) {
						$approved_status = get_user_meta($user_id, 'afreg_new_user_status', true);
						//if the user hasn't been approved yet by WP Approve User plugin, destroy the cookie to kill the session and log them out
						if ( 'approved' == $approved_status ) {
							return get_permalink(wc_get_page_id('myaccount'));

						} elseif ('pending' == $approved_status) {
							wp_logout();
							return get_permalink(wc_get_page_id('myaccount')) . '?approved=pending';
						} elseif ('disapproved' == $approved_status) {

							wp_logout();
							return get_permalink(wc_get_page_id('myaccount')) . '?approved=disapproved';
						} else {
							return get_permalink(wc_get_page_id('myaccount'));
						}
					} else {
						return get_permalink(wc_get_page_id('myaccount'));
					}
				

				} else {

					return get_permalink(wc_get_page_id('myaccount'));
				}
			}
		}

		public function afreg_user_checkout_autologout() {

			if ( is_user_logged_in() ) {

				if ( !empty( get_option('afreg_enable_approve_user')) && 'yes' == get_option('afreg_enable_approve_user')) {

					$current_user = wp_get_current_user();
					$user_id      = $current_user->ID;

					$roles = ( array ) $current_user->roles;

					$default_role = $roles[0];

					if (!empty( get_option('afreg_exclude_user_roles_approve_new_user'))) {
						$manual_user_roles = get_option('afreg_exclude_user_roles_approve_new_user');	
					} else {
						$manual_user_roles = array();
					}


					if (!in_array( $default_role, $manual_user_roles)) {

						$approved_status = get_user_meta($user_id, 'afreg_new_user_status', true);
						//if the user hasn't been approved yet by WP Approve User plugin, destroy the cookie to kill the session and log them out
						if ( 'approved' == $approved_status ) {
							return;

						} elseif ('pending' == $approved_status) {
							wp_logout();
							WC()->session->set( 'refresh_totals', true );
							throw new Exception( __( get_option('afreg_user_pending_approval_message') , 'addify_reg' ) );
							die();
						} elseif ('disapproved' == $approved_status) {

							wp_logout();
							WC()->session->set( 'refresh_totals', true );
							throw new Exception( __( get_option('afreg_user_disapproved_message') , 'addify_reg' ) );
							die();
						} else {
							return;
						}
					} else {
						return ;
					}
				

				} else {

					return;
				}
			}
		}


		public function afreg_registration_message() {
				

			if ( isset($_REQUEST['approved']) ) {

				$approved = sanitize_text_field($_REQUEST['approved']);
				if ( 'pending' == $approved) {

					echo "<p class='enu_warning'>" . esc_textarea(get_option('afreg_user_pending_approval_message')) . '</p>';
				} elseif ('disapproved' == $approved) {

					echo "<p class='enu_error'>" . esc_textarea(get_option('afreg_user_disapproved_message')) . '</p>';
				}
			} 

		}

		public function afreg_auth_login ( $user) {

			$status = get_user_meta($user->ID, 'afreg_new_user_status', true);
			

			if ( empty( $status ) ) {
				// the user does not have a status so let's assume the user is good to go
				return $user;
			}

			$message = false;
			switch ( $status ) {
				case 'pending':
					$pending_message = get_option('afreg_user_approval_message');
					$message         = new WP_Error( 'pending_approval', $pending_message );
					break;
				case 'disapproved':
					$disapproved_message = get_option('afreg_user_disapproved_message');
					$message             = new WP_Error( 'disapproved_access', $disapproved_message );
					break;
				case 'approved':
					$message = $user;
					break;
			}

			return $message;
		}


		public function afreg_checkout_account_extra_fields( $fields) {

			if (!is_user_logged_in()) { 

				$afreg_args = array( 
					'posts_per_page' => -1,
					'post_type' => 'afreg_fields',
					'post_status' => 'publish',
					'orderby' => 'menu_order',
					'suppress_filters' => false,
					'order' => 'ASC'
				);

				$afreg_extra_fields = get_posts($afreg_args);

				if (!empty($afreg_extra_fields)) {

					foreach ($afreg_extra_fields as $afreg_field) {

						$afreg_field_type        = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
						$afreg_field_options     = unserialize(get_post_meta( intval($afreg_field->ID), 'afreg_field_option', true )); 
						$afreg_field_required    = get_post_meta( intval($afreg_field->ID), 'afreg_field_required', true );
						$afreg_field_width       = get_post_meta( intval($afreg_field->ID), 'afreg_field_width', true );
						$afreg_field_placeholder = get_post_meta( intval($afreg_field->ID), 'afreg_field_placeholder', true );
						$afreg_field_description = get_post_meta( intval($afreg_field->ID), 'afreg_field_description', true );
						$afreg_field_css         = get_post_meta( intval($afreg_field->ID), 'afreg_field_css', true );
						$afreg_field_read_only   = get_post_meta( $afreg_field->ID, 'afreg_field_read_only', true );

						if (!empty($afreg_field_width) && 'full' == $afreg_field_width) {

							$afreg_main_class = 'form-row-wide';

						} elseif (!empty($afreg_field_width) && 'half' == $afreg_field_width) {

							$afreg_main_class = 'half_width';
						}

						if ('select' == $afreg_field_type) {
							$select_options = array();
							foreach ($afreg_field_options as $opt) {
								
								$select_options[$opt['field_value']] = $opt['field_text'];
							}
						}

						if ('multiselect' == $afreg_field_type) {
							$multiselect_options = array();
							foreach ($afreg_field_options as $opt) {
								
								$multiselect_options[$opt['field_value']] = $opt['field_text'];
							}
						}

						if ('radio' == $afreg_field_type) {
							$radio_options = array();
							foreach ($afreg_field_options as $opt) {
								
								$radio_options[$opt['field_value']] = $opt['field_text'];
							}
						}

						if (!empty(get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true ))) {

							$afreg_is_dependable = get_post_meta( intval($afreg_field->ID), 'afreg_is_dependable', true );
						} else {
							$afreg_is_dependable = 'off';
						}

						

						if ('text' == $afreg_field_type && 'off' == $afreg_is_dependable ) {

							$fields['account']['afreg_additional_' . intval($afreg_field->ID)] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => ( 'on' == $afreg_field_required ? true : false ),
								'class'         => array($afreg_main_class, $afreg_field_css),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'text',
								'description'   => $afreg_field_description
							);

						} elseif ('textarea' == $afreg_field_type && 'off' == $afreg_is_dependable) {

							$fields['account']['afreg_additional_' . intval($afreg_field->ID)] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => ( 'on' == $afreg_field_required ? true : false ),
								'class'         => array($afreg_main_class, $afreg_field_css),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'textarea',
								'description'   => $afreg_field_description
							);

						} elseif ('select' == $afreg_field_type && 'off' == $afreg_is_dependable) {

							$fields['account']['afreg_additional_' . intval($afreg_field->ID)] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => ( 'on' == $afreg_field_required ? true : false ),
								'class'         => array($afreg_main_class, $afreg_field_css),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'select',
								'description'   => $afreg_field_description,
								'options'     	=> $select_options,
							);

						} elseif ('multiselect' == $afreg_field_type && 'off' == $afreg_is_dependable) {

							$fields['account']['afreg_additional_' . intval($afreg_field->ID) . '[]'] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => '',
								'class'         => array($afreg_main_class, $afreg_field_css),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'multiselect',
								'description'   => $afreg_field_description,
								'options'     	=> $multiselect_options,
							);

						} elseif ('radio' == $afreg_field_type && 'off' == $afreg_is_dependable) {

							$fields['account']['afreg_additional_' . intval($afreg_field->ID)] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => ( 'on' == $afreg_field_required ? true : false ),
								'class'         => array($afreg_main_class, $afreg_field_css, 'afreg_radio'),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'radio',
								'description'   => $afreg_field_description,
								'options'     	=> $radio_options,
							);

						} elseif ('checkbox' == $afreg_field_type && 'off' == $afreg_is_dependable) {

							

							$fields['account']['afreg_additional_' . intval($afreg_field->ID)] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => ( 'on' == $afreg_field_required ? true : false ),
								'class'         => array($afreg_main_class, $afreg_field_css, 'afreg_radio'),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'checkbox',
								'description'   => $afreg_field_description,
								
							);
							

						} elseif ('email' == $afreg_field_type && 'off' == $afreg_is_dependable) {

							$fields['account']['afreg_additional_' . intval($afreg_field->ID)] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => ( 'on' == $afreg_field_required ? true : false ),
								'class'         => array($afreg_main_class, $afreg_field_css),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'email',
								'description'   => $afreg_field_description
							);

						} elseif ('number' == $afreg_field_type && 'off' == $afreg_is_dependable) {

							$fields['account']['afreg_additional_' . intval($afreg_field->ID)] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => ( 'on' == $afreg_field_required ? true : false ),
								'class'         => array($afreg_main_class, $afreg_field_css),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'number',
								'description'   => $afreg_field_description
							);

						} elseif ('password' == $afreg_field_type && 'off' == $afreg_is_dependable) {

							$fields['account']['afreg_additional_' . intval($afreg_field->ID)] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => ( 'on' == $afreg_field_required ? true : false ),
								'class'         => array($afreg_main_class, $afreg_field_css),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'password',
								'description'   => $afreg_field_description
							);

						} elseif ('datepicker' == $afreg_field_type && 'off' == $afreg_is_dependable) {

							$fields['account']['afreg_additional_' . intval($afreg_field->ID)] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => ( 'on' == $afreg_field_required ? true : false ),
								'class'         => array($afreg_main_class, $afreg_field_css),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'date',
								'description'   => $afreg_field_description
							);

						} elseif ('timepicker' == $afreg_field_type && 'off' == $afreg_is_dependable) {

							$fields['account']['afreg_additional_' . intval($afreg_field->ID)] = array(
								'label'         => esc_html__($afreg_field->post_title , 'addify_reg'),
								'placeholder'   => esc_html__($afreg_field_placeholder, 'addify_reg'),
								'required'      => ( 'on' == $afreg_field_required ? true : false ),
								'class'         => array($afreg_main_class, $afreg_field_css),
								'clear'         => false,
								'id'         	=> 'afreg_additional_' . intval($afreg_field->ID),
								'type'			=> 'time',
								'description'   => $afreg_field_description
							);

						} 

					}
				}


			}

			return $fields;

		}


		public function afreg_custom_multiselect_handler( $field, $key, $args, $value  ) {
					
			$options     = '';
			$ekey        = explode('[', $key);
			$field_id    = explode('afreg_additional_', $ekey[0]);
			$is_required = get_post_meta( intval($field_id[1]), 'afreg_field_required', true );

			if ('' != $is_required) {
				if ('on' == $is_required) {
					$required = '<abbr class="required" title="required">*</abbr>';
				} else {
					$required = '';
				}
			}
			if ( ! empty( $args['options'] ) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					$options .= '<option value="' . esc_attr($option_key) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr($option_text) . '</option>';
				}

				$field = '<p class="form-row ' . implode( ' ', $args['class'] ) . '" id="' . $key . '_field">
		            <label for="' . $key . '" class="' . implode( ' ', $args['label_class'] ) . '">' . $args['label'] . $required . '</label>
		            <select name="' . $key . '" id="' . $key . '" class="select" multiple="multiple">
		                ' . $options . '
		            </select>
		        </p>';
			}

			return $field;
		}

		public function afreg_get_allowed_countries( $countries) {

			// Only on frontend
			if ( is_admin() ) {
				return $countries;
			}

			if ( class_exists( 'WC_Geolocation' ) ) {
				$location = WC_Geolocation::geolocate_ip();

				if ( isset( $location['country'] ) ) {
					$countryCode = $location['country'];
				} else {
					// If there is no country, then return allowed countries
					return $countries;
				}
			} else {
				// If you can't geolocate user country by IP, then return allowed countries
				return $countries;
			}

			// If everything went ok then I filter user country in the allowed countries array
			$user_country_code_array = array( $countryCode );

			$intersect_countries = array_intersect_key( $countries, array_flip( $user_country_code_array ) );

			return $intersect_countries;
		}


		public function afreg_default_fields() {
			$posts = get_posts(array(
			  'post_type' => 'def_reg_fields',
			  'numberposts' => -1,
			  'order'    => 'ASC',
			  'post_status' => 'publish',
			  'suppress_filters' => true,
			  'orderby' => 'menu_order'
			));
			wp_nonce_field( 'afreg_nonce_action', 'afreg_nonce_field' );
			if (0 < count($posts) ) {
				?>
				<div class="afreg_extra_fields">
				<?php
			} else {
				return;
			}

			global $woocommerce;

			$location = WC_Geolocation::geolocate_ip();

			$countries_obj = new WC_Countries();

			$countries = $countries_obj->get_allowed_countries();

			foreach ($posts as $post) :
				$required    = get_post_meta($post->ID, 'is_required', true);
				$width       = get_post_meta($post->ID, 'width', true);
				$message     = get_post_meta($post->ID, 'message', true);
				$placeholder = get_post_meta($post->ID, 'placeholder', true);
				$type        = get_post_meta($post->ID, 'type', true);
				if ( ! empty( $_POST[$post->post_name] ) ) {
					if (!empty($_POST['afreg_nonce_field'])) {
						$retrieved_nonce = sanitize_text_field($_POST['afreg_nonce_field']);
					} else {
						$retrieved_nonce = 0;
					}
					if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {
						echo '';
					}
					$def_value = sanitize_text_field($_POST[$post->post_name]);
				} else {
					$def_value = '';	
				}
				//Text Field
				if ('text' == $type || 'tel' == $type) {
					$name =__( $post->post_title, 'woocommerce' );
					//print_r($name);
					?>
			<p id="<?php echo esc_attr($post->post_name); ?>" class="form-row <?php echo esc_attr($width); ?>_field">
				<label for="<?php echo esc_attr__($post->post_name, 'addify_reg'); ?>"><?php echo esc_html__($name, 'addify_reg'); ?>
						<?php
						if (1 == $required) {
							?>
							 <span class="required">*</span> <?php } ?>
				</label>
				<input type="<?php echo esc_attr($type); ?>" class="input-text" name="<?php echo esc_attr($post->post_name); ?>" id="<?php echo esc_attr($post->post_name); ?>" value="<?php echo esc_attr($def_value); ?>" placeholder="<?php echo esc_html__($placeholder, 'addify_reg'); ?>" />
					<?php if (isset($message) && '' != $message) { ?>
					<span class="fmessage"><?php echo esc_html__($message, 'addify_reg'); ?></span>
				<?php } ?>
			</p>
					<?php
				} elseif ('select' == $type) {
					if ( 'billing_country' == $post->post_name) {
						


						if ( ! empty( $_POST[$post->post_name] ) ) {
							$billing_country = sanitize_text_field($_POST[$post->post_name]);
						} elseif (!empty($location['country'])) {
							$billing_country = $location['country'];
						} else {
							$billing_country = '';	
						}
						$billing_country_title =__( $post->post_title, 'woocommerce' );

						
						
						?>
					<p id="<?php echo esc_attr($post->post_name); ?>" class="form-row <?php echo esc_attr($width); ?>_field">
						<label for="<?php echo esc_attr__($post->post_name, 'addify_reg'); ?>"><?php echo esc_html__($billing_country_title, 'addify_reg'); ?>
							<?php
							if (1 == $required) {
								?>
								 <span class="required">*</span> <?php } ?>
						</label>
						
						<select class="js-example-basic-single input-select" name="<?php echo esc_attr($post->post_name); ?>" onchange="selectState(this.value);">
							<option value=""><?php echo esc_html__('Select a country...', 'addify_reg'); ?></option>
							<?php foreach ($countries as $key => $value) { ?>
								<option value="<?php echo esc_attr($key); ?>" <?php echo selected($billing_country, $key); ?>><?php echo esc_attr($value); ?></option>
							<?php } ?>
						</select>
						<?php if (isset($message) && '' != $message) { ?>
							<span class="fmessage"><?php echo esc_html__($message, 'addify_reg'); ?></span>
						<?php } ?>
					</p>
				<?php } elseif ( 'billing_state' == $post->post_name) { ?>
					<p id="dropdown_state" class="form-row <?php echo esc_attr($width); ?>_field">
						<label for="<?php echo esc_attr__($post->post_name, 'addify_reg'); ?>"><?php echo esc_html__( $post->post_title, 'addify_reg' ); ?>
							<?php
							if (1 == $required) {
								?>
								 <span class="required">*</span> <?php } ?>
						</label>
						<input type="text" class="input-text" name="<?php echo esc_attr($post->post_name); ?>" id="drop_down_state" value="" placeholder="<?php echo esc_html__($placeholder, 'addify_reg'); ?>" />
						
						<?php if (isset($message) && '' != $message) { ?>
							<span class="fmessage"><?php echo esc_html__($message, 'addify_reg'); ?></span>
						<?php } ?>
					</p>
			<?php } ?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('.js-example-basic-single').select2();

					<?php if ( 'billing_country' == $post->post_name) { ?>
						<?php if (isset($_POST['billing_country']) && '' != $_POST['billing_country']) { ?>
						var country = "<?php echo esc_attr(sanitize_text_field($_POST['billing_country'])); ?>";
					<?php } elseif (!empty($location['country'])) { ?>
						var country = "<?php echo esc_attr($location['country']); ?>";
					<?php } else { ?>
						var country = '';
					<?php } ?>
						<?php if (isset($_POST['billing_state']) && '' != $_POST['billing_state']) { ?>
						var af_state = "<?php echo esc_attr(sanitize_text_field($_POST['billing_state'])); ?>";
					<?php } else { ?>
						var af_state = "";
					<?php } ?>
					var ajaxurl = "<?php echo esc_url(admin_url( 'admin-ajax.php')); ?>";
					var name = "<?php echo esc_attr($post->post_name); ?>";
					var label = "<?php echo esc_attr($post->post_title); ?>";
					var message = "<?php echo esc_attr($message); ?>";
					var required = "<?php echo esc_attr($required); ?>";
					var width = "<?php echo esc_attr($width); ?>";
					var nonce = "<?php echo esc_attr(wp_create_nonce('afreg-ajax-nonce')); ?>";

					jQuery.ajax({
						type: 'POST',   // Adding Post method
						url: ajaxurl, // Including ajax file
						data: {"action": "get_states","country":country,"name":name,"label":label,"message":message,"required":required,"width":width,"af_state":af_state,"nonce":nonce},
						success: function(data){

							jQuery('#dropdown_state').html(data);
						}
					});
					<?php } ?>
				});
				function selectState(country) {
					var ajaxurl = "<?php echo esc_url(admin_url( 'admin-ajax.php')); ?>";
					var name = "<?php echo esc_attr($post->post_name); ?>";
					var label = "<?php echo esc_attr($post->post_title); ?>";
					var message = "<?php echo esc_attr($message); ?>";
					var required = "<?php echo esc_attr($required); ?>";
					var width = "<?php echo esc_attr($width); ?>";
					var nonce = "<?php echo esc_attr(wp_create_nonce('afreg-ajax-nonce')); ?>";
					jQuery.ajax({
					type: 'POST',   // Adding Post method
					url: ajaxurl, // Including ajax file
						data: {"action": "get_states","country":country,"name":name,"label":label,"message":message,"required":required,"width":width,"nonce":nonce},
						success: function(data){
							jQuery('#dropdown_state').html(data);
						}
					});
				}
			</script>
			<?php } ?>
			
				<?php
			endforeach;
			?>
			</div>
			<?php
		}

		public function afreg_default_fields_validate( $username, $email, $validation_errors) {
			if ( is_checkout() ) {
				return $validation_errors;
			}
			if (isset( $_POST['first_name'])) {

				if (!empty($_POST['afreg_nonce_field'])) {

					$retrieved_nonce = sanitize_text_field($_POST['afreg_nonce_field']);
				} else {
						$retrieved_nonce = 0;
				}

				if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

					echo '';
				}
			}

			//First Name
			$checkfield = $this->getFieldBySlug('first_name');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['first_name'] ) && empty( $_POST['first_name'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}


			//Last Name
			$checkfield = $this->getFieldBySlug('last_name');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['last_name'] ) && empty( $_POST['last_name'] ) && 1 == $required) {
						$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//Company
			$checkfield = $this->getFieldBySlug('billing_company');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_company'] ) && empty( $_POST['billing_company'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//Country
			$checkfield = $this->getFieldBySlug('billing_country');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_country'] ) && empty( $_POST['billing_country'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//Address Line 1
			$checkfield = $this->getFieldBySlug('billing_address_1');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_address_1'] ) && empty( $_POST['billing_address_1'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}


			//Address Line 2
			$checkfield = $this->getFieldBySlug('billing_address_2');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_address_2'] ) && empty( $_POST['billing_address_2'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//State
			$checkfield = $this->getFieldBySlug('billing_state');

			if (!empty($checkfield) && !is_checkout() ) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_state'] ) && empty( $_POST['billing_state'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//City
			$checkfield = $this->getFieldBySlug('billing_city');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_city'] ) && empty( $_POST['billing_city'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//Post Code
			$checkfield = $this->getFieldBySlug('billing_postcode');

			if (!empty($checkfield) && !is_checkout() ) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_postcode'] ) && empty( $_POST['billing_postcode'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//Phone
			$checkfield = $this->getFieldBySlug('billing_phone');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}

				if ( isset( $_POST['billing_phone'] ) && !empty( $_POST['billing_phone'] ) && 1 == $required && !preg_match('/^[0-9-+\s()]*$/', sanitize_text_field($_POST['billing_phone']))) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', esc_html__( $checkfield[0]->post_title . ' is not valid!', 'addify_reg' ) );
				}
			}


			return $validation_errors;

		}

		public function afreg_default_fields_validate_wordpress( $validation_errors, $username, $email ) {
			if ( is_checkout() ) {
				return $validation_errors;
			}
			if (isset( $_POST['first_name'])) {

				if (!empty($_POST['afreg_nonce_field'])) {

					$retrieved_nonce = sanitize_text_field($_POST['afreg_nonce_field']);
				} else {
						$retrieved_nonce = 0;
				}

				if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

					echo '';
				}
			}

			//First Name
			$checkfield = $this->getFieldBySlug('first_name');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['first_name'] ) && empty( $_POST['first_name'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}


			//Last Name
			$checkfield = $this->getFieldBySlug('last_name');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['last_name'] ) && empty( $_POST['last_name'] ) && 1 == $required) {
						$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//Company
			$checkfield = $this->getFieldBySlug('billing_company');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_company'] ) && empty( $_POST['billing_company'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//Country
			$checkfield = $this->getFieldBySlug('billing_country');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_country'] ) && empty( $_POST['billing_country'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//Address Line 1
			$checkfield = $this->getFieldBySlug('billing_address_1');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_address_1'] ) && empty( $_POST['billing_address_1'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}


			//Address Line 2
			$checkfield = $this->getFieldBySlug('billing_address_2');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_address_2'] ) && empty( $_POST['billing_address_2'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//State
			$checkfield = $this->getFieldBySlug('billing_state');

			if (!empty($checkfield) && !is_checkout() ) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_state'] ) && empty( $_POST['billing_state'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//City
			$checkfield = $this->getFieldBySlug('billing_city');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_city'] ) && empty( $_POST['billing_city'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//Post Code
			$checkfield = $this->getFieldBySlug('billing_postcode');

			if (!empty($checkfield) && !is_checkout() ) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_postcode'] ) && empty( $_POST['billing_postcode'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}
			}

			//Phone
			$checkfield = $this->getFieldBySlug('billing_phone');

			if (!empty($checkfield)) {
				$required = get_post_meta($checkfield[0]->ID, 'is_required', true);

				if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) && 1 == $required) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html( __(  $checkfield[0]->post_title , 'addify_reg' ) ) . esc_html__( ' is required!', 'addify_reg' ) );
				}

				if ( isset( $_POST['billing_phone'] ) && !empty( $_POST['billing_phone'] ) && 1 == $required && !preg_match('/^[0-9-+\s()]*$/', sanitize_text_field($_POST['billing_phone']))) {
					$validation_errors->add( $checkfield[0]->post_name . '_error', '<strong>' . __('Error' , 'addify_reg') . '</strong>: ' . esc_html__( $checkfield[0]->post_title . ' is not valid!', 'addify_reg' ) );
				}
			}


			return $validation_errors;

		}

		public function getFieldBySlug( $slug) {

			$args     = array(
			  'name'        => $slug,
			  'post_type'   => 'def_reg_fields',
			  'post_status' => 'publish',
			  'suppress_filters' => false,
			  'numberposts' => 1
			);
			$my_posts = get_posts($args);
			if ( $my_posts ) :
				return $my_posts;
			endif;
		}

		public function afreg_validate_fields_checkout() { 
			

			global $woocommerce;

			$afreg_args = array( 
						'posts_per_page' => -1,
						'post_type' => 'afreg_fields',
						'post_status' => 'publish',
						'orderby' => 'menu_order',
						'order' => 'ASC'
					);

				$afreg_extra_fields = get_posts($afreg_args);

			if (!empty($afreg_extra_fields)) {
				if (isset($_POST['createaccount']) && 1 == $_POST['createaccount']) { 
						
						
					if (!empty($_POST['afreg_nonce_field'])) {

						$retrieved_nonce = sanitize_text_field($_POST['afreg_nonce_field']);
					} else {
							$retrieved_nonce = 0;
					}

					if (!wp_verify_nonce($retrieved_nonce, 'afreg_nonce_action')) {

						echo '';
					}
					
						
					foreach ($afreg_extra_fields as $afreg_field) { 

						$afreg_field_type        = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
						$afreg_field_options     = unserialize(get_post_meta( intval($afreg_field->ID), 'afreg_field_option', true )); 
						$afreg_field_required    = get_post_meta( intval($afreg_field->ID), 'afreg_field_required', true );
						$afreg_field_width       = get_post_meta( intval($afreg_field->ID), 'afreg_field_width', true );
						$afreg_field_placeholder = get_post_meta( intval($afreg_field->ID), 'afreg_field_placeholder', true );
						$afreg_field_description = get_post_meta( intval($afreg_field->ID), 'afreg_field_description', true );
						$afreg_field_css         = get_post_meta( intval($afreg_field->ID), 'afreg_field_css', true );
						$afreg_field_read_only   = get_post_meta( $afreg_field->ID, 'afreg_field_read_only', true );

						if ( 'on' == $afreg_field_required && 'multiselect' == $afreg_field_type) {

							if (empty( $_POST['afreg_additional_' . intval($afreg_field->ID)] )) { 

									 

										wc_add_notice( __( '<b>' . $afreg_field->post_title . '</b> is required!', 'addify_reg' ), 'error' );
									
							}
						}


							



					}
				}
			}
			

			
		}


		

	   
		


	}

	new Addify_Registration_Fields_Addon_Front();

}
