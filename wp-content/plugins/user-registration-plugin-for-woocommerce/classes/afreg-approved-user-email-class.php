<?php


if ( ! defined( 'WPINC' ) ) {
	die; 
}

if ( !class_exists( 'Addify_Registration_Fields_Approved_User_Email' ) ) { 

	class Addify_Registration_Fields_Approved_User_Email extends WC_Email {

		/**
		 * Constructor of membership activated.
		 */
		public function __construct() { 
			$this->id             = 'afreg_approved_user_email_user'; // Unique ID to Store Emails Settings
			$this->title          = __( 'Addify Registration Approved User Email to Customer', 'addify_reg' ); // Title of email to show in Settings
			$this->customer_email = true; // Set true for customer email and false for admin email.
			$this->description    = __( 'This email will be sent to customer when account is approved.', 'addify_reg' ); // description of email
			$this->template_base  = AFREG_PLUGIN_DIR; // Base directory of template 
			$this->template_html  = 'templates/emails/afreg-approved-user-email-user.php'; // HTML template path
			$this->template_plain = 'templates/emails/plain/afreg-approved-user-email-user.php'; // Plain template path

			$this->placeholders = array( // Placeholders/Variables to be used in email
				

			);

			// Call to the  parent constructor.
			parent::__construct(); // Must call constructor of parent class


			// Trigger function.
			add_action( 'afreg_approved_user_email_notification_user', array( $this, 'trigger' ), 10, 1 ); // action hook(s) to trigger email 
			
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {

			//Old versions compatibility.
			if (!empty(get_option('afreg_approved_email_subject'))) {

				return __(get_option('afreg_approved_email_subject'), 'addify_reg');

			} else {

				return __( 'Congradulations your {site_title} account has been approved!', 'addify_reg' );

			}

			
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Welcome to {site_title}', 'addify_reg' );
		}

		public function getFieldBySlug( $slug ) {

			$args     = array(
			'name'        => $slug,
			'post_type'   => 'def_reg_fields',
			'post_status' => 'publish',
			'numberposts' => 1,
			);
			$my_posts = get_posts($args);
			if ($my_posts ) :
				return $my_posts;
			endif;
		}


		public function trigger( $customer_id ) {

			$this->setup_locale();

					$customer = new WP_User($customer_id);

					$customer_details = '';
				
			if (!empty($customer)) {

						

				$user_meta = get_userdata($customer_id);

				

				$user_login = stripslashes($customer->user_login);
				$user_email = stripslashes($customer->user_email);

				//custom message
				$email_content = get_option('afreg_approved_email_text');
						
				$customer_details .= '<p><b>' . esc_html__('Username: ', 'addify_reg') . '</b>' . $user_login . '</p>';
				$customer_details .= '<p><b>' . esc_html__('E-mail: ', 'addify_reg') . '</b>' . $user_email . '</p>';

				// Default Fields

				$def_fiels_email_fields = '';
				// First Name
				$fname = get_user_meta($customer_id, 'first_name', true);
				if (!empty($fname) ) {
					

					$checkfield = $this->getFieldBySlug('first_name');

					if (! empty($checkfield) ) {

						$title = $checkfield[0]->post_title;
					} else {
						$title = 'First Name';
					}

					$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_b2b') . '</b>' . esc_attr($fname) . '</p>';

				}

				// Last Name
				$lname = get_user_meta($customer_id, 'last_name', true);
				if (!empty($lname) ) {
					

					$checkfield = $this->getFieldBySlug('last_name');

					if (! empty($checkfield) ) {

						$title = $checkfield[0]->post_title;
					} else {
						$title = 'Last Name';
					}

					$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_b2b') . '</b>' . esc_attr($lname) . '</p>';

				}

				// Company
				$company = get_user_meta($customer_id, 'billing_company', true);
				if (!empty($company) ) {
					

					$checkfield = $this->getFieldBySlug('billing_company');

					if (! empty($checkfield) ) {

						$title = $checkfield[0]->post_title;
					} else {
						$title = 'Company';
					}

					$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_b2b') . '</b>' . esc_attr($company) . '</p>';

				}

				// country
				$country = get_user_meta($customer_id, 'billing_country', true);
				if ( !empty($country) ) {
					

					$checkfield = $this->getFieldBySlug('billing_country');

					if (! empty($checkfield) ) {

						$title = $checkfield[0]->post_title;
					} else {
						$title = 'Country';
					}

					$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_b2b') . '</b>' . esc_attr($country) . '</p>';

				}

				// address 1
				$billing_address_1 = get_user_meta($customer_id, 'billing_address_1', true);
				if ( !empty($billing_address_1 )) {
					

					$checkfield = $this->getFieldBySlug('billing_address_1');

					if (! empty($checkfield) ) {

						$title = $checkfield[0]->post_title;
					} else {
						$title = 'Address 1';
					}

					$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_b2b') . '</b>' . esc_attr($billing_address_1 ) . '</p>';

				}

				// address 2
				$billing_address_2 = get_user_meta($customer_id, 'billing_address_2', true);
				if ( !empty($billing_address_2)) {
					

					$checkfield = $this->getFieldBySlug('billing_address_2');

					if (! empty($checkfield) ) {

						$title = $checkfield[0]->post_title;
					} else {
						$title = 'Address 2';
					}

					$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_b2b') . '</b>' . esc_attr($billing_address_2) . '</p>';

				}

				// city
				$billing_city = get_user_meta($customer_id, 'billing_city', true);
				if ( !empty($billing_city) ) {
					

					$checkfield = $this->getFieldBySlug('billing_city');

					if (! empty($checkfield) ) {

						$title = $checkfield[0]->post_title;
					} else {
						$title = 'City';
					}

					$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_b2b') . '</b>' . esc_attr($billing_city) . '</p>';

				}

				// state
				$billing_state = get_user_meta($customer_id, 'billing_state', true);
				if ( !empty($billing_state)) {
					
					$checkfield = $this->getFieldBySlug('billing_state');

					if (! empty($checkfield) ) {

						$title = $checkfield[0]->post_title;
					} else {
						$title = 'State';
					}

					$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_b2b') . '</b>' . esc_attr($billing_state) . '</p>';

				}

				// postcode
				$billing_postcode = get_user_meta($customer_id, 'billing_postcode', true);
				if ( !empty($billing_postcode)) {
					

					$checkfield = $this->getFieldBySlug('billing_postcode');

					if (! empty($checkfield) ) {

						$title = $checkfield[0]->post_title;
					} else {
						$title = 'Post Code';
					}

					$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_b2b') . '</b>' . esc_attr($billing_postcode) . '</p>';

				}

				// phone
				$billing_phone = get_user_meta($customer_id, 'billing_phone', true);
				if ( !empty($billing_phone)) {
					

					$checkfield = $this->getFieldBySlug('billing_phone');

					if (! empty($checkfield) ) {

						$title = $checkfield[0]->post_title;
					} else {
						$title = 'Phone';
					}

					$def_fiels_email_fields .= '<p><b>' . esc_html__($title . ': ', 'addify_b2b') . '</b>' . esc_attr($billing_phone) . '</p>';

				}


				if (!empty($def_fiels_email_fields)) {

					$customer_details .= $def_fiels_email_fields;
				}


				//Additional Fields
				$afreg_args = array( 
					'posts_per_page' => -1,
					'post_type' => 'afreg_fields',
					'post_status' => 'publish',
					'orderby' => 'menu_order',
					'order' => 'ASC'
				);

				$afreg_extra_fields = get_posts($afreg_args);
				if (!empty($afreg_extra_fields)) {
					foreach ($afreg_extra_fields as $afreg_field) {
						
						$afreg_field_type = get_post_meta( intval($afreg_field->ID), 'afreg_field_type', true );
						$afregcheck       = get_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), true );

						if (!empty($afregcheck)) {

							$value = get_user_meta( $customer_id, 'afreg_additional_' . intval($afreg_field->ID), true );
									
							if ( 'checkbox' == $afreg_field_type) {
								if ('yes' == $value) {
									$customer_details .= '<p><b>' . esc_html__($afreg_field->post_title . ': ', 'addify_reg') . '</b>' . esc_html__('Yes', 'addify_reg') . '</p>';
								} else {
									$customer_details .= '<p><b>' . esc_html__($afreg_field->post_title . ': ', 'addify_reg') . '</b>' . esc_html__('No', 'addify_reg') . '</p>';
								}
										
							} elseif ( 'fileupload' == $afreg_field_type) {

										
								$upload_url = wp_upload_dir();

								$current_file = '';

								$curr_image_new_folder = $upload_url['basedir'] . '/addify_registration_uploads/' . $value;
						
								$curr_image = esc_url(AFREG_URL . 'uploaded_files/' . $value);

								if (file_exists($curr_image_new_folder)) {

									$current_file = esc_url($upload_url['baseurl'] . '/addify_registration_uploads/' . $value);

								} elseif (file_exists($curr_image)) {

									$current_file = esc_url(AFREG_URL . 'uploaded_files/' . $value);

								}
										
								$customer_details .= '<p><b>' . esc_html__($afreg_field->post_title . ': ', 'addify_reg') . '</b>' . $current_file . '</p>';

							} elseif ( in_array( $afreg_field_type , array( 'multiselect' , 'multi_checkbox' , 'select', 'radio') ) ) {
								 $val_array           = explode(', ' , $value );
								 $afreg_field_options = unserialize(get_post_meta(  intval($afreg_field->ID) , 'afreg_field_option', true )); 
								 $value               = '';
								foreach ( $val_array as $option_val ) {
									foreach ($afreg_field_options as $afreg_field_option ) { 
										if ( esc_attr( $option_val ) == $afreg_field_option['field_value'] ) {
											$value .=  $afreg_field_option['field_text'] . ', ';
										}
									}
								}

								$customer_details .= '<p><b>' . esc_html__($afreg_field->post_title . ': ', 'addify_reg') . '</b>' . rtrim($value, ', ') . '</p>';
							} elseif ('timepicker' == $afreg_field_type) {

								$customer_details .= '<p><b>' . esc_html__($afreg_field->post_title . ': ', 'addify_reg') . '</b><input type="time" value="' . $value . '" readonly="readonly"></p>';
										
							} else {
								$customer_details .= '<p><b>' . esc_html__($afreg_field->post_title . ': ', 'addify_reg') . '</b>' . $value . '</p>';
							}

						}
					}
				}




				$email_content = str_replace('{customer_details}', $customer_details, $email_content);

				$this->email_content = $email_content;
				$this->object        = $customer;
				$this->recipient     = $user_email;
			}
				
			

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}


		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'customer'             => $this->object,
					'email_heading'      => $this->get_heading(),
					'email_content'      => $this->email_content,
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
				),
				$this->template_base,
				$this->template_base
			);
		}

	
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'customer'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'email_content'      => $this->email_content,
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
				),
				$this->template_base,
				$this->template_base
			);
		}


		


	}

	

}
