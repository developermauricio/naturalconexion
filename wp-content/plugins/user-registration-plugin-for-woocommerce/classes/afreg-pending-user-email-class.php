<?php


if ( ! defined( 'WPINC' ) ) {
	die; 
}

if ( !class_exists( 'Addify_Registration_Fields_Pending_User_Email' ) ) { 

	class Addify_Registration_Fields_Pending_User_Email extends WC_Email {

		/**
		 * Constructor of membership activated.
		 */
		public function __construct() { 
			$this->id             = 'afreg_pending_user_email_user'; // Unique ID to Store Emails Settings
			$this->title          = __( 'Addify Registration Pending User Email to Customer', 'addify_reg' ); // Title of email to show in Settings
			$this->customer_email = true; // Set true for customer email and false for admin email.
			$this->description    = __( 'This email will be sent to customer when account is pending for approval.', 'addify_reg' ); // description of email
			$this->template_base  = AFREG_PLUGIN_DIR; // Base directory of template 
			$this->template_html  = 'templates/emails/afreg-pending-user-email-user.php'; // HTML template path
			$this->template_plain = 'templates/emails/plain/afreg-pending-user-email-user.php'; // Plain template path

			$this->placeholders = array( // Placeholders/Variables to be used in email
				

			);

			// Call to the  parent constructor.
			parent::__construct(); // Must call constructor of parent class


			// Trigger function.
			add_action( 'afreg_pending_user_email_notification_user', array( $this, 'trigger' ), 10, 2 ); // action hook(s) to trigger email 
			
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {

			//Old versions compatibility.
			if (!empty(get_option('afreg_pending_approval_email_subject'))) {

				return __(get_option('afreg_pending_approval_email_subject'), 'addify_reg');

			} else {

				return __( 'Your {site_title} account has been created and pending for approval!', 'addify_reg' );

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


		public function trigger( $customer_id, $default_fields ) {

			$this->setup_locale();

					$customer = new WP_User($customer_id);

					$customer_details = '';
				
			if (!empty($customer)) {

						

				$user_meta = get_userdata($customer_id);

				$user_role = $user_meta->roles;

				$user_login = stripslashes($customer->user_login);
				$user_email = stripslashes($customer->user_email);

				//custom message
				$email_content = get_option('afreg_pending_approval_email_text');
						
				$customer_details .= '<p><b>' . esc_html__('Username: ', 'addify_reg') . '</b>' . $user_login . '</p>';
				$customer_details .= '<p><b>' . esc_html__('E-mail: ', 'addify_reg') . '</b>' . $user_email . '</p>';

				//Default Fields
				if (!empty($default_fields)) {

					$customer_details .= $default_fields;
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
