<?php 
/**
 * Plugin Name:       Custom User Registration Fields for WooCommerce
 * Plugin URI:        https://woocommerce.com/products/custom-user-registration-fields-for-woocommerce/
 * Description:       User Registration Plugin for WooCommerce allows you to add extra fields to your registration form. Registration Fields Addon is compatible with both WordPress & WooCommerce. Support 14 types of fields and compatible with Addify plugins. (PLEASE TAKE BACKUP BEFORE UPDATING THE PLUGIN).
 * Version:           1.7.2
 * Author:            Addify
 * Developed By:      Addify
 * Author URI:        http://www.addifypro.com
 * Support:                http://www.addifypro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       addify_reg
 *
 * Woo: 4939159:29ac5e8b0b006fe46a743fc7fb2ad245
 *
 * WC requires at least: 3.0.9
 * WC tested up to: 5.*.*
 */

if (! defined('WPINC') ) {
	die;
}




if (!class_exists('Addify_Registration_Fields_Addon') ) { 

	class Addify_Registration_Fields_Addon {

		public function __construct() {

			$this->afreg_global_constents_vars();

			add_action('after_setup_theme', array( $this, 'afreg_init' ));
			add_action( 'init', array($this, 'afreg_custom_post_type' ));
			add_action( 'init', array($this, 'afdef_custom_post_type' ));
			register_activation_hook( __FILE__, array( $this, 'afreg_installation' ) );

			if (is_admin() ) {
				include_once AFREG_PLUGIN_DIR . 'admin/class-afreg-fields-admin.php';
			} else {
				include_once AFREG_PLUGIN_DIR . 'front/class-afreg-fields-front.php';
			}

			add_action('wp_ajax_get_states', array($this, 'get_states'));
			add_action('wp_ajax_nopriv_get_states', array($this, 'get_states'));  

			add_filter( 'woocommerce_email_classes', array( $this, 'afreg_emails' ), 90, 1 );          
		}

		public function afreg_global_constents_vars() {
			
			if (!defined('AFREG_URL') ) {
				define('AFREG_URL', plugin_dir_url(__FILE__));
			}

			if (!defined('AFREG_BASENAME') ) {
				define('AFREG_BASENAME', plugin_basename(__FILE__));
			}

			if (! defined('AFREG_PLUGIN_DIR') ) {
				define('AFREG_PLUGIN_DIR', plugin_dir_path(__FILE__));
			}
		}

		
		public function afreg_init() {
			if (function_exists('load_plugin_textdomain') ) {
				load_plugin_textdomain('addify_reg', false, dirname(plugin_basename(__FILE__)) . '/languages/');
			}
		}

		public function afreg_custom_post_type() {

			$labels = array(
			'name'                => esc_html__('Registration Fields', 'addify_reg'),
			'singular_name'       => esc_html__('Registration Field', 'addify_reg'),
			'add_new'             => esc_html__('Add New Field', 'addify_reg'),
			'add_new_item'        => esc_html__('Add New Field', 'addify_reg'),
			'edit_item'           => esc_html__('Edit Registration Field', 'addify_reg'),
			'new_item'            => esc_html__('New Registration Field', 'addify_reg'),
			'view_item'           => esc_html__('View Registration Field', 'addify_reg'),
			'search_items'        => esc_html__('Search Registration Field', 'addify_reg'),
			'exclude_from_search' => true,
			'not_found'           => esc_html__('No registration field found', 'addify_reg'),
			'not_found_in_trash'  => esc_html__('No registration field found in trash', 'addify_reg'),
			'parent_item_colon'   => '',
			'all_items'           => esc_html__('All Fields', 'addify_reg'),
			'menu_name'           => esc_html__('Registration Fields', 'addify_reg'),
			);
		
			$args = array(
			'labels' => $labels,
			'menu_icon'  => plugin_dir_url( __FILE__ ) . 'images/small_logo_grey.png',
			'public' => false,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 30,
			'rewrite' => array('slug' => 'addify_reg', 'with_front'=>false ),
			'supports' => array('title')
			);
		
			register_post_type( 'afreg_fields', $args );

		}
		public function afdef_custom_post_type() {

			$def_labels = array(
			'name'                => esc_html__('Default Registration Fields', 'addify_reg'),
			'singular_name'       => esc_html__('Default Registration Fields', 'addify_reg'),
			'edit_item'           => esc_html__('Edit Registration Field', 'addify_reg'),
			'new_item'            => esc_html__('New Registration Field', 'addify_reg'),
			'view_item'           => esc_html__('View Registration Field', 'addify_reg'),
			'search_items'        => esc_html__('Search Registration Field', 'addify_reg'),
			'exclude_from_search' => true,
			'not_found'           => esc_html__('No registration field found', 'addify_reg'),
			'not_found_in_trash'  => esc_html__('No registration field found in trash', 'addify_reg'),
			'parent_item_colon'   => '',
			'all_items'           => esc_html__('All Default Fields', 'addify_reg'),
			'menu_name'           => esc_html__('Default Registration Fields', 'addify_reg'),
			);
		
			$args = array(
			'labels' => $def_labels,
			'public' => false,
			'publicly_queryable' => true,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			//'menu_position' => 30,
			'rewrite' => array('slug' => 'addify_reg', 'with_front'=>false ),
			'supports' => array('title')
			);
		
			register_post_type( 'def_reg_fields', $args );

		}




		public function afreg_installation() {

			$upload_url = wp_upload_dir();


			if (!is_dir($upload_url['basedir'] . '/addify_registration_uploads')) {
				mkdir($upload_url['basedir'] . '/addify_registration_uploads', 0777, true);
			}

			
			$this->afreg_insert_default_fields();

			$this->afreg_insert_emails_default_text();
			
		}

		public function afreg_insert_default_fields() {

			//New code
			$first_name_posts = get_page_by_path( 'first_name', OBJECT, 'def_reg_fields' );
			if ('' == $first_name_posts) {
				$first_name_post = array(
					'post_title'   => __('First Name', 'addify_reg'),
					'post_name'    => 'first_name',
					'post_type'    => 'def_reg_fields',
					'post_status'  => 'unpublish',
					'menu_order'   => 1        
				);
				$first_name_id   = wp_insert_post($first_name_post);
				update_post_meta($first_name_id, 'placeholder', 'Enter your first name');
				update_post_meta($first_name_id, 'is_required', 1);
				update_post_meta($first_name_id, 'width', 'half');
				update_post_meta($first_name_id, 'type', 'text');
				update_post_meta($first_name_id, 'message', '');
			}
			//Last Name
			$last_name_posts = get_page_by_path( 'last_name', OBJECT, 'def_reg_fields' );
			if ('' == $last_name_posts) {
				$last_name_post = array(
					'post_title'   => __('Last Name', 'addify_reg'),
					'post_name'    => 'last_name',
					'post_type'    => 'def_reg_fields',
					'post_status'  => 'unpublish',
					'menu_order'   => 2        
				);
				$last_name_id   = wp_insert_post($last_name_post);
				update_post_meta($last_name_id, 'placeholder', 'Enter your last name');
				update_post_meta($last_name_id, 'is_required', 1);
				update_post_meta($last_name_id, 'width', 'half');
				update_post_meta($last_name_id, 'type', 'text');
				update_post_meta($last_name_id, 'message', '');
			}
			//Company
			$company_posts = get_page_by_path( 'billing_company', OBJECT, 'def_reg_fields' );
			if ('' == $company_posts) {
				$company_post = array(
					'post_title'   => __('Company', 'addify_reg'),
					'post_name'    => 'billing_company',
					'post_type'    => 'def_reg_fields',
					'post_status'  => 'unpublish',
					'menu_order'   => 3       
				);
				$company_id   = wp_insert_post($company_post);
				update_post_meta($company_id, 'placeholder', 'Enter your company');
				update_post_meta($company_id, 'is_required', 0);
				update_post_meta($company_id, 'width', 'full');
				update_post_meta($company_id, 'type', 'text');
				update_post_meta($company_id, 'message', '');
			}
			//Country
			$country_posts = get_page_by_path( 'billing_country', OBJECT, 'def_reg_fields' );
			if ('' == $country_posts) {
				$country_post = array(
					'post_title'   => __('Country', 'addify_reg'),
					'post_name'    => 'billing_country',
					'post_type'    => 'def_reg_fields',
					'post_status'  => 'unpublish',
					'menu_order'   => 4       
				);
				$country_id   = wp_insert_post($country_post);
				update_post_meta($country_id, 'placeholder', 'Select your country');
				update_post_meta($country_id, 'is_required', 1);
				update_post_meta($country_id, 'width', 'full');
				update_post_meta($country_id, 'type', 'select');
				update_post_meta($country_id, 'message', '');
			}
			//Address Line 1
			$address_1_posts = get_page_by_path( 'billing_address_1', OBJECT, 'def_reg_fields' );
			if ('' == $address_1_posts) {
				$address_1_post = array(
					'post_title'   => __('Street Address', 'addify_reg'),
					'post_name'    => 'billing_address_1',
					'post_type'    => 'def_reg_fields',
					'post_status'  => 'unpublish',
					'menu_order'   => 5       
				);
				$address_1_id   = wp_insert_post($address_1_post);
				update_post_meta($address_1_id, 'placeholder', 'House number and street name');
				update_post_meta($address_1_id, 'is_required', 1);
				update_post_meta($address_1_id, 'width', 'full');
				update_post_meta($address_1_id, 'type', 'text');
				update_post_meta($address_1_id, 'message', '');
			}
			//Address Line 2
			$address_2_posts = get_page_by_path( 'billing_address_2', OBJECT, 'def_reg_fields' );
			if ('' == $address_2_posts) {
				$address_2_post = array(
					'post_title'   => __('Address 2', 'addify_reg'),
					'post_name'    => 'billing_address_2',
					'post_type'    => 'def_reg_fields',
					'post_status'  => 'unpublish',
					'menu_order'   => 6       
				);
				$address_2_id   = wp_insert_post($address_2_post);
				update_post_meta($address_2_id, 'placeholder', 'Apartment, suite, unit etc. (optional)');
				update_post_meta($address_2_id, 'is_required', 0);
				update_post_meta($address_2_id, 'width', 'full');
				update_post_meta($address_2_id, 'type', 'text');
				update_post_meta($address_2_id, 'message', '');
			}
			//State
			$state_posts = get_page_by_path( 'billing_state', OBJECT, 'def_reg_fields' );
			if ('' == $state_posts) {
				$state_post = array(
					'post_title'   => __('State / County', 'addify_reg'),
					'post_name'    => 'billing_state',
					'post_type'    => 'def_reg_fields',
					'post_status'  => 'unpublish',
					'menu_order'   => 7       
				);
				$state_id   = wp_insert_post($state_post);
				update_post_meta($state_id, 'placeholder', 'Select your state / county');
				update_post_meta($state_id, 'is_required', 1);
				update_post_meta($state_id, 'width', 'full');
				update_post_meta($state_id, 'type', 'select');
				update_post_meta($state_id, 'message', '');
			}
			//City
			$city_posts = get_page_by_path( 'billing_city', OBJECT, 'def_reg_fields' );
			if ('' == $city_posts) {
				$city_post = array(
					'post_title'   => __('Town / City', 'addify_reg'),
					'post_name'    => 'billing_city',
					'post_type'    => 'def_reg_fields',
					'post_status'  => 'unpublish',
					'menu_order'   => 8       
				);
				$city_id   = wp_insert_post($city_post);
				update_post_meta($city_id, 'placeholder', 'Enter your city');
				update_post_meta($city_id, 'is_required', 1);
				update_post_meta($city_id, 'width', 'half');
				update_post_meta($city_id, 'type', 'text');
				update_post_meta($city_id, 'message', '');
			}
			//Post Code
			$postcode_posts = get_page_by_path( 'billing_postcode', OBJECT, 'def_reg_fields' );
			if ('' == $postcode_posts) {
				$postcode_post = array(
					'post_title'   => __('Postcode / Zip', 'addify_reg'),
					'post_name'    => 'billing_postcode',
					'post_type'    => 'def_reg_fields',
					'post_status'  => 'unpublish',
					'menu_order'   => 9      
				);
				$postcode_id   = wp_insert_post($postcode_post);
				update_post_meta($postcode_id, 'placeholder', 'Enter your postcode / zip');
				update_post_meta($postcode_id, 'is_required', 1);
				update_post_meta($postcode_id, 'width', 'half');
				update_post_meta($postcode_id, 'type', 'text');
				update_post_meta($postcode_id, 'message', '');
			}
			//Phone
			$phone_posts = get_page_by_path( 'billing_phone', OBJECT, 'def_reg_fields' );
			if ('' == $phone_posts) {
				$phone_post = array(
					'post_title'   => __('Phone', 'addify_reg'),
					'post_name'    => 'billing_phone',
					'post_type'    => 'def_reg_fields',
					'post_status'  => 'unpublish',
					'menu_order'   => 10      
				);
				$phone_id   = wp_insert_post($phone_post);
				update_post_meta($phone_id, 'placeholder', 'Enter your phone');
				update_post_meta($phone_id, 'is_required', 1);
				update_post_meta($phone_id, 'width', 'full');
				update_post_meta($phone_id, 'type', 'tel');
				update_post_meta($phone_id, 'message', '');
			}	




		}

		public function get_states() {

			if (isset($_POST['nonce']) && '' != $_POST['nonce']) {

				$nonce = sanitize_text_field( $_POST['nonce'] );
			} else {
				$nonce = 0;
			}

			$country  = '';
			$af_state = '';

			if ( ! wp_verify_nonce( $nonce, 'afreg-ajax-nonce' ) ) {

				echo '';
			}

			if (!empty($_POST['country'])) {

				$country = sanitize_text_field($_POST['country']);
			}

			if (!empty($_POST['width'])) {

				$width = sanitize_text_field($_POST['width']);
			}

			if (!empty($_POST['name'])) {

				$name = sanitize_text_field($_POST['name']);
			}

			if (!empty($_POST['label'])) {

				$label = sanitize_text_field($_POST['label']);
			}

			if (!empty($_POST['message'])) {

				$message = sanitize_text_field($_POST['message']);
			}

			if (!empty($_POST['required'])) {

				$required = sanitize_text_field($_POST['required']);
			}

			if (!empty($_POST['af_state'])) {

				$af_state = sanitize_text_field($_POST['af_state']);
			}
			

			global $woocommerce;
			$countries_obj = new WC_Countries();

			if (!empty($countries_obj)) {
				$states = $countries_obj->get_states( $country );
			}
			
			if (!empty($states) && !empty($country)) {
				?>

			<p id="dropdown_state" class="form-row <?php echo esc_attr($width); ?>">
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_html__( $label, 'addify_reg' ); ?> 
					<?php 
					if (1 == $required) {
						?>
						 <span class="required">*</span> <?php } ?>
				</label>

				<select class="js-example-basic-single" name="billing_state">
					<option value=""><?php echo esc_html__('Select a county / state...', 'addify_reg'); ?></option>
					
					<?php foreach ($states as $key => $value) { ?>
						<option value="<?php echo esc_attr($key); ?>" <?php echo selected($af_state, $key); ?>><?php echo esc_attr($value); ?></option>
					<?php } ?>
				</select>

				<?php if (isset($message) && ''!=$message) { ?>
					<span style="width:100%;float: left"><?php echo esc_html__($message, 'addify_reg'); ?></span>
				<?php } ?>
			</p>

			<?php } elseif (is_array($states) && !empty($country)) { ?>
				
				<p id="dropdown_state" class="form-row <?php echo esc_attr($width); ?>">
					<input type="hidden" name="billing_state" value="<?php echo esc_attr($country); ?>" />
				</p>

			

			<?php } else { ?>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_html__( $label, 'addify_reg' ); ?> 
					<?php 
					if (1 == $required) {
						?>
						 <span class="required">*</span> <?php } ?>
				</label>
				<p id="dropdown_state" class="form-row <?php echo esc_attr($width); ?>">
					<input type="text" name="billing_state" value="<?php echo esc_attr($af_state); ?>" />
				</p>

			<?php } ?>

			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('.js-example-basic-single').select2();
				});

				  
				

			</script>


			<?php 
			die();
		}

		public function afreg_emails( $emails) {

			require_once AFREG_PLUGIN_DIR . 'classes/afreg-admin-email-class.php';
			$emails['afreg_admin_email_new_user'] = new  Addify_Registration_Fields_Admin_Email();
			$emails['afreg_admin_email_new_user']->init_form_fields();


			require_once AFREG_PLUGIN_DIR . 'classes/afreg-admin-email-class-update-account.php';
			$emails['afreg_admin_email_update_user'] = new  Addify_Registration_Fields_Admin_Email_Update_Account();
			$emails['afreg_admin_email_update_user']->init_form_fields();


			require_once AFREG_PLUGIN_DIR . 'classes/afreg-user-email-class.php';
			$emails['afreg_user_email_new_user'] = new  Addify_Registration_Fields_User_Email();


			require_once AFREG_PLUGIN_DIR . 'classes/afreg-pending-user-email-class.php';
			$emails['afreg_pending_user_email_user'] = new  Addify_Registration_Fields_Pending_User_Email();


			require_once AFREG_PLUGIN_DIR . 'classes/afreg-approved-user-email-class.php';
			$emails['afreg_approved_user_email_user'] = new  Addify_Registration_Fields_Approved_User_Email();


			require_once AFREG_PLUGIN_DIR . 'classes/afreg-disapproved-user-email-class.php';
			$emails['afreg_disapproved_user_email_user'] = new  Addify_Registration_Fields_Disapproved_User_Email();

			
			

			return $emails;
		}


		public function afreg_insert_emails_default_text() {

			if (empty(get_option('afreg_admin_email_text'))) {

				$afreg_admin_email_text = '<p>Hi,</p>
<p>A new user has registered to your website. Here are the details,</p>
<p>{customer_details}</p>
<p><em>To disable this email, go to WooCommerce &gt; Settings &gt; Emails &gt; Addify Registration New User Email Admin.</em></p>';

				update_option('afreg_admin_email_text', $afreg_admin_email_text);

			}


			if (empty(get_option('afreg_update_user_admin_email_text'))) {

				$afreg_update_user_admin_email_text = '<p>Hi,</p>
<p>A new user has just updated their information from My Account Page.  Here are the details,</p>
<p>{customer_details}</p>
<p><em>To disable this email, go to WooCommerce &gt; Settings &gt; Emails &gt; Addify Registration Update User Email Admin.</em></p>';

				update_option('afreg_update_user_admin_email_text', $afreg_update_user_admin_email_text);

			}


			if (empty(get_option('afreg_user_email_text'))) {

				$afreg_user_email_text = '<p>Hi,</p>
<p>Thank you for creating new account. Here is glimpse of the details you have submitted.</p>
<p>{customer_details} </p>
<p>Thank you,</p>';

				update_option('afreg_user_email_text', $afreg_user_email_text);

			}



			if (empty(get_option('afreg_pending_approval_email_text'))) {

				$afreg_pending_approval_email_text = '<p>Hi,</p>
<p><br />Thank you for showing interest. To keep the community clean we like to review the new applicants. You will be notified VIA email about the approval status. Here is the glimpse of your details,</p>
<p>{customer_details}<br /><br />Thank you,</p>';

				update_option('afreg_pending_approval_email_text', $afreg_pending_approval_email_text);

			}



			if (empty(get_option('afreg_approved_email_text'))) {

				$afreg_approved_email_text = '<p>Hi,Thank you for showing patience. You account has been approved.</p>
<p>Thank you,</p>';

				update_option('afreg_approved_email_text', $afreg_approved_email_text);

			}



			if (empty(get_option('afreg_disapproved_email_text'))) {

				$afreg_disapproved_email_text = '<p>Hi,</p>
<p>We regret to inform you that your account has been disapproved. If you think its a mistake, please reach out to us.</p>
<p>Thank you,</p>';

				update_option('afreg_disapproved_email_text', $afreg_disapproved_email_text);

			}

		}

	}

	new Addify_Registration_Fields_Addon();

}

