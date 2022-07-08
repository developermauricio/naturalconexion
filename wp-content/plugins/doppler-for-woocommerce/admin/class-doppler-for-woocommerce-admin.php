<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.fromdoppler.com/
 * @since      1.0.0
 *
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/admin
 * @author     Doppler LLC <info@fromdoppler.com>
 */
class Doppler_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $doppler_service;

	private $admin_notice;

	private $success_message;

	private $error_message;

	private $required_doppler_version;

	private $origin;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $doppler_service ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->doppler_service = $doppler_service;
		$this->success_message = false;
		$this->error_message = false;
		$this->required_doppler_version = '2.1.5';
		$this->origin = $this->set_origin();
		$this->set_credentials();
		$this->check_current_account();
		$this->update_database();
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function set_error_message($message) {
		$this->error_message = $message;
	}

	public function set_success_message($message) {
		$this->success_message = $message;
	}

	public function get_error_message() {
		return $this->error_message;
	}

	public function get_success_message() {
		return $this->success_message;
	}

	public function get_required_doppler_version() {
		return $this->required_doppler_version;
	}

	public function set_origin() {
		$this->doppler_service->set_origin(DOPPLER_FOR_WOOCOMMERCE_ORIGIN);
	}

	public function display_error_message() {
		if($this->get_error_message()!=''):
		?>
		<div id="displayErrorMessage" class="messages-container blocker">
			<p><?php echo $this->get_error_message(); ?></p>
		</div>
		<?php
		endif;
	}

	public function display_success_message() {
		if($this->get_success_message()!=''):
		?>
		<div id="displaySuccessMessage" class="messages-container info">
			<p><?php echo $this->get_success_message(); ?></p>
		</div>
		<?php
		endif;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'css/doppler-for-woocommerce-admin.css', 
			array(), 
			$this->version, 'all' 
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 
				$this->plugin_name, 
				plugin_dir_url( __FILE__ ) . 'js/doppler-for-woocommerce-admin.js', 
				array( 'jquery', 'Doppler' ), 
				$this->version, false 
		);
		wp_localize_script( $this->plugin_name, 'ObjWCStr', array( 
			  'invalidUser'		=> __( 'Ouch! Enter a valid Email.', 'doppler-for-woocommerce' ),
			  'emptyField'		=> __( 'Ouch! The Field is empty.', 'doppler-for-woocommerce'),
			  'wrongData'		=> __( 'Ouch! There\'s something wrong with your Username or API Key. Please, try again.', 'doppler-for-woocommerce'),
			  'listSavedOk'   	=> __( 'The List has been created correctly.', 'doppler-for-woocommerce'),
			  'maxListsReached' => __( 'Ouch! You\'ve reached the maximum number of Lists created.', 'doppler-for-woocommerce'),
			  'duplicatedName'	=> __( 'Ouch! You\'ve already used this name for another List.', 'doppler-for-woocommerce'),	
			  'tooManyConn'		=> __( 'Ouch! You\'ve made several actions in a short period of time. Please wait a few minutes before making another one.', 'doppler-for-woocommerce'),
			  'validationError'	=> __( 'Ouch! List name is invalid. Please choose another name.', 'doppler-for-woocommerce'),
			  'Save'            => __( 'Save', 'doppler-for-woocommerce'),
			  'Cancel'          => __( 'Cancel', 'doppler-for-woocommerce'),
			  'listsSyncError'  => __( 'Ouch! The Lists couldn\'t be synchronized.', 'doppler-for-woocommerce'),
			  'listsSyncOk'  	=> __( 'Your Lists has been syncronized and saved succesfully.', 'doppler-for-woocommerce'),
			  'Synchronizing'   => __( 'We\'re synchronizing your Customers with your Doppler List...', 'doppler-for-woocommerce' ),
			  'selectAList'		=> __( 'Select the Doppler Lists where you want to import your Customers. When synchronized, those Customers already registered and future customers will be sent automatically.', 'doppler-for-woocommerce'),	
			  'default_buyers_list' => __('WooCommerce Buyers', 'doppler-for-woocommerce'),
			  'default_contacts_list' => __('WooCommerce Contacts', 'doppler-for-woocommerce') 
		));
	}

	public function dplrwoo_check_parent() {
		if ( !is_plugin_active( 'doppler-form/doppler-form.php' ) )  {
			$this->admin_notice = array( 'error', __('Ouch! <strong>Doppler for WooCommerce</strong> requires the <a href="https://wordpress.org/plugins/doppler-form/">Doppler Forms</a> plugin to be installed and active.', 'doppler-for-woocommerce') );
			$this->deactivate();
		}else if( version_compare( get_option('dplr_version'), $this->get_required_doppler_version(), '<' ) ){
			$this->admin_notice = array( 'error', sprintf(__('Ouch! <strong>Doppler for WooCommerce</strong> requires at least <strong>Doppler Forms v%s</strong> to be active. Please <a href="%splugins.php">upgrade</a> Doppler Forms.', 'doppler-for-woocommerce'), $this->get_required_doppler_version(), admin_url()));
			$this->deactivate();
		}
	}

	private function deactivate() {
		deactivate_plugins( DOPPLER_FOR_WOOCOMMERCE_PLUGIN ); 
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

	private function is_plugin_allowed() {
		$version = get_option('dplr_version');
		if( class_exists('DPLR_Doppler') && class_exists('WooCommerce') && version_compare($version, $this->get_required_doppler_version(), '>=') ){
			return true;
	    }
		return false;
	}

	/**
	 * Set the credentials to doppler service
	 * before running api calls.
	 */
	private function set_credentials() {
		$options = get_option('dplr_settings');
		if( empty($options['dplr_option_apikey']) || empty($options['dplr_option_useraccount']) )  return;
		$this->doppler_service->setCredentials(array(	
			'api_key' => $options['dplr_option_apikey'], 
			'user_account' => $options['dplr_option_useraccount'])
		);
	}

	/**
	 * Check if connected Doppler account
	 * matches the account used to integrate with
	 * the App.
	 * If not, create keys and activate integration.
	 * 
	 * @since 1.0.2
	 */
	private function check_current_account() {
		if(is_admin()){
			$options = get_option('dplr_settings');
			if( empty($options['dplr_option_apikey']) || empty($options['dplr_option_useraccount']) )  return;
			//If status is empty, api is not connected.
			$status = get_option('dplrwoo_api_connected');
			if( !empty($status) && !empty($options) && 
				($options['dplr_option_useraccount'] != $status['account']) )
				{
					$dplr_app_connect = new Doppler_For_WooCommerce_App_Connect(
						$options['dplr_option_useraccount'],
						$options['dplr_option_apikey'],
						DOPPLER_WOO_API_URL,
						DOPPLER_FOR_WOOCOMMERCE_ORIGIN
					);
					//delete previous keys.
					$dplr_app_connect->disconnect();
					//generate new keys, associates it in Doppler with request account.
					$connect_response = $dplr_app_connect->connect();
					if($connect_response['response']['code'] === 200){
						//save flag with current account.
						update_option('dplrwoo_api_connected', array(
							'account' => $options['dplr_option_useraccount'],
							'status' => 'on'
						));
					}
				}
		}
	}

	/**
	 * Registers the admin menu
	 */
	public function dplrwoo_init_menu() {
		if($this->is_plugin_allowed()):
			add_submenu_page(
				'doppler_forms_menu',
				__('Doppler for WooCommerce', 'doppler-for-woocommerce'),
				__('Doppler for WooCommerce', 'doppler-for-woocommerce'),
				'manage_options',
				'doppler_woocommerce_menu',
				array($this, 'dplrwoo_admin_page')
			);
		endif;
	}

	/**
	 * Display the admin settings screen
	 */
	public function dplrwoo_admin_page() {
		include('partials/doppler-for-woocommerce-settings.php');
	}

	/**
	 * Display the Fields Mapping screen
	 * deprecated
	 */
	/*
	public function dplrwoo_mapping_page() {
		$fields = $this->get_checkout_fields();
	}
	*/

	/**
	 * Sanitizes & validate before saving new Doppler List.
	 */
	public function dplrwoo_save_list() {
		if( !empty($_POST['listName']) && ( strlen($_POST['listName']) < 100) ){
			echo $this->create_list(sanitize_text_field($_POST['listName']));
		}
		wp_die();
	}

	/*
	* Validate date formats
	*/
	function validateDate($date, $format = 'Y-m-d H:i:s') {
		// Create the format date
		$d = DateTime::createFromFormat($format, $date);
		// Return the comparison    
		return $d && $d->format($format) === $date;
	}

	/**
	 * Saves new Doppler List.
	 */
	private function create_list($list_name) {
		$subscriber_resource = $this->doppler_service->getResource('lists');
		$this->set_origin();
		return $subscriber_resource->saveList( $list_name )['body'];
	}

	/**
	 * Find a list id in a lists array
	 * by a given name.
	 */
	private function find_list_by_name($list_name, $lists) {
		if(empty($lists)) return false;
		$resp = array_filter($lists, function($var) use($list_name){
			return $var['name'] == $list_name;
		});
		reset($resp);
	    return ($resp!=='null')? key($resp) : false;
	}

	/**
	 * Set buyers and contacts last synch value to 0.
	 */
	public function reset_buyers_and_contacts_last_synch() {
		$last_synch = get_option('dplrwoo_last_synch');
		
		//Synch!
		if(!empty($last_synch)){
			
			//bayers reset
			$buyer_list_id = get_option('dplr_subscribers_list')['buyers'];

			if(!empty($buyer_list_id) && isset($last_synch['buyers'][$buyer_list_id])) {
				$last_synch['buyers'][$buyer_list_id] = 0;
			}

			//contacts reset
			$contact_list_id = get_option('dplr_subscribers_list')['contacts'];

			if(!empty($contact_list_id) && isset($last_synch['contacts'][$contact_list_id])) {
				$last_synch['contacts'][$contact_list_id]['orders'] = 0;	
				$last_synch['contacts'][$contact_list_id]['users'] = 0;
			}
			
			update_option('dplrwoo_last_synch', $last_synch);
		} else {
			return false;
		}
	}

	/**
	 * Get the WooCommerce customer's fields.
	 */
	public function sync_all_buyers() {
		$last_synch = get_option('dplrwoo_last_synch');
		
		//Synch!
		if(!empty($last_synch)){
			
			//bayers reset
			$buyer_list_id = get_option('dplr_subscribers_list')['buyers'];

			if(!empty($buyer_list_id) && isset($last_synch['buyers'][$buyer_list_id])) {
				$last_synch['buyers'][$buyer_list_id] = 0;
			}

			//contacts reset
			$contact_list_id = get_option('dplr_subscribers_list')['contacts'];

			if(!empty($contact_list_id) && isset($last_synch['contacts'][$contact_list_id])) {
				$last_synch['contacts'][$contact_list_id]['orders'] = 0;	
				$last_synch['contacts'][$contact_list_id]['users'] = 0;
			}
			
			update_option('dplrwoo_last_synch', $last_synch);
		} else {
			return false;
		}
	}

	/**
	 * Get the WooCommerce customer's fields.
	 */
	public function get_checkout_fields() {
		if ( ! class_exists( 'WC_Session' ) ) {
			include_once( WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-session.php' );
		}
		WC()->session = new WC_Session_Handler;
		WC()->customer = new WC_Customer;
		return WC()->checkout->checkout_fields;
	}

	/**
	 * Compares field types between WooCommerce and Doppler
	 * for showing the correct options to map.
	 */
	function check_field_type( $wc_field_type, $dplr_field_type ) {
		
		empty($wc_field_type)? $wc_field_type = 'string' : '';
		
		switch($wc_field_type){
			case 'string':
					if($dplr_field_type === 'string'){
						return true;
					}
					return false;
				break;
			case 'state':
					if($dplr_field_type === 'string'){
						return true;
					}
					return false;
				break;
			case 'radio':
					if($dplr_field_type === 'gender'){
						return true;
					}
					return false;
				break;
			case 'email':
					if($dplr_field_type === 'email'){
						return true;
					}
					return false;
				break;
			case 'country':
					if( $dplr_field_type === 'country' || $dplr_field_type === 'string' ){
						return true;
					}
					return false;
				break;
			case 'tel':
					if($dplr_field_type === 'phone'){
						return true;
					}
					return false;
				break;
			case 'date':
					if($dplr_field_type === 'date'){
						return true;
					}
					return false;
				break;
			case 'datetime':
					if($dplr_field_type === 'date'){
						return true;
					}
					return false;
				break;
			case 'datetime-local':
					if($dplr_field_type === 'date'){
						return true;
					}
					return false;
				break;
			case 'number':
				if($dplr_field_type === 'number'){
						return true;
					}
					return false;
				break;
			case 'checkbox':
				if($dplr_field_type === 'boolean'){
						return true;
					}
					return false;
				break;
			default:
					return true;
				break;
		}

	}

	/**
	 * Get lists
	 */
	public function get_alpha_lists() {
		$dplr_lists_arr = array();
		$list_resource = $this->doppler_service->getResource('lists');
		$this->set_origin();
		$dplr_lists = $list_resource->getAllLists();
		if(is_array($dplr_lists) && !empty($dplr_lists)){
			foreach($dplr_lists as $k=>$v){
			  if(is_array($v)):
				foreach($v as $i=>$j){
				  $dplr_lists_aux[$j->listId] = array('name'=>trim($j->name), 'subscribersCount'=>$j->subscribersCount);
				}
			  endif;
			}
			$dplr_lists_arr = $dplr_lists_aux;
		}
		return $dplr_lists_arr;	
	}

	/**
	 * After creating account from checkout, saves
	 * customer to contact List.
	 */
	public function dplrwoo_created_customer( $customer_id, $customer_data, $customer_password ) {
		if( wp_verify_nonce( $_POST['woocommerce-register-nonce'], 'woocommerce-register' ) ){
			$fields_map = get_option('dplrwoo_mapping');
			$list_id = get_option('dplr_subscribers_list')['contacts'];
			if( !empty($list_id) ){
				$fields = array();
				if(!empty($fields_map)){
					foreach($fields_map as $k=>$v){
						if($v!=''){
							$fields[] = array('name'=>$v, 'value'=>sanitize_text_field($_POST[$k]) );
						}
					}
				}
				$this->subscribe_customer($list_id, $customer_data['user_email'], $fields);
			}
		}
	}

	/**
	 * Subscribe customer after registering
	 * from my-account.
	 */
	public function dprwoo_after_register( $user_id ) {
		$list_id = get_option('dplr_subscribers_list')['contacts'];
		$user_info = get_userdata($user_id);
		if(empty($list_id) || empty($user_id) || empty($user_info->user_email)) return false;
		$meta_fields = get_user_meta($user_id);
		$fields_map = get_option('dplrwoo_mapping');
		$fields = $this->extract_meta_from_user( $fields_map, $meta_fields );
		$this->subscribe_customer($list_id, $user_info->user_email, $fields);
	}
	
	/**
	 * Send subscriptor to buyers List
	 * after order status is completed.
	 */
	public function dplrwoo_order_status_changed( $order_id, $old_status, $new_status, $instance ) {
		if( $new_status == "completed" ) {
			$list_id = get_option('dplr_subscribers_list')['buyers'];
			$order = wc_get_order( $order_id );
			$order_data = $order->get_data();
			$fields = $this->get_mapped_fields($order);
			$this->subscribe_customer($list_id, $order_data['billing']['email'], $fields);
		}
	}

	/**
	 * Subscribe user to a Contact List
	 * after checking out.
	 * Only WC > 3.0.
	 */
	public function dplrwoo_customer_checkout_success( $order_id ) {
		$list_id = get_option('dplr_subscribers_list')['contacts'];
		$order = wc_get_order( $order_id );
		$order_data = $order->get_data();
		$fields = $this->get_mapped_fields($order);
		$this->subscribe_customer($list_id, $order_data['billing']['email'], $fields);
	}

	/**
	 * Get "Registered" users
	 *
	 * Registered users are also saved
	 * to Contact List. 
	 * 
	 * Used in Synch
	 * 
	 */
	private function get_registered_users() {
		$users = get_users( array('role'=>'Customer') );
		$fields_map = get_option('dplrwoo_mapping');
		$registered_users = array();
		if(!empty($users)){
			foreach($users as $k=>$user){
				$meta_fields = get_user_meta($user->ID);
				$fields = $this->extract_meta_from_user($fields_map, $meta_fields);
				if(isset($meta_fields['billing_email']) && !empty($meta_fields['billing_email'])){
					$aux = array_values($meta_fields['billing_email']);
					$email = array_shift($aux);
					$registered_users[$email] = $fields;
				}
			}
		}
		return $registered_users;
	}

	/**
	 * Get Doppler mapped fields from WC user fields
	 */
	function extract_meta_from_user( $fields_map, $meta_fields ) {
		$fields = array();
		if(!empty($fields_map)){
			foreach($fields_map as $k=>$v){
				if($v!=''){
					if(isset($meta_fields[$k])){
						$aux = array_values($meta_fields[$k]);
						$value = sanitize_text_field(array_shift($aux));
					}else{
						$value = '';
					}
					$fields[] = array('name'=>$v, 'value'=>$value);
				}
			}
		}
		return $fields;
	}

	/**
	 * Synch trhough ajax.
	 */
	public function dplrwoo_ajax_synch() {
		if( empty($_POST['buyers_list']) || empty($_POST['contacts_list']) ) return false;
		echo $this->dplrwoo_synch( $_POST['buyers_list'], $_POST['contacts_list']);
	}


	/**
	 * Syncrhonizes "Contacts" or "Buyers"
	 *
	 * Contacts are customers who completed 
	 * a checkout form or are registered users.
	 * Buyers are users who has orders that
	 * have been completed.
	 * 
	 * DEPRECATED old method.
	 * 
	 */
	/*
	public function dplrwoo_synch( $list_id , $list_type) {

		$orders_by_email = array();

		$args = array(
			'limit'		=> -1,
			'orderby'	=> 'date',
			'order'		=> 'DESC'
		);
		
		if( $list_type === 'contacts' ){
			$registered_users = $this->get_registered_users();
		}else if( $list_type === 'buyers' ){
			$args['status'] = 'completed';
		}

		$orders = wc_get_orders($args);

		if(!empty($orders)){
			foreach($orders as $k=>$order){
				$orders_by_email[$order->get_data()['billing']['email']] = $this->get_mapped_fields($order);
			}
		}

		if( $list_type === 'contacts' ){
			$users = array_merge($registered_users,$orders_by_email);
		}else{
			$users = $orders_by_email;
		}
		
		$subscribers['items'] =  array();
		$subscribers['fields'] =  array();

		if(empty($users) || empty($list_id)){
			echo '0';
			wp_die();
		};

		foreach($users as $email=>$fields){
			$subscribers['items'][] = array('email'=>$email, 'fields'=>$fields);
		}
	
		$subscriber_resource = $this->doppler_service->getResource( 'subscribers' );
		$this->set_origin();
		return $subscriber_resource->importSubscribers($list_id, $subscribers)['body'];
		wp_die();

	}*/

	public function dplrwoo_synch( $buyers_list , $contacts_list) {

		//Save chosen lists
		update_option('dplr_subscribers_list', 
			array('buyers'=>$buyers_list, 'contacts'=>$contacts_list));

		wp_die();

	}

	/**
	 * Goes through complete orders
	 * and upload emails to Buyers list
	 */
	private function dplrwoo_synch_buyers_cron(){
		global $wpdb;
		$log = '';
		//Get list.
		$list_id = get_option('dplr_subscribers_list')['buyers'];
		if(empty($list_id)) return false;

		$last_id = 0;
		$condition = '';
		
		$fields_map = get_option('dplrwoo_mapping');
		
		//Get last synch info.
		$last_synch = get_option('dplrwoo_last_synch');
		
		//Synch!
		if(!empty($last_synch) && isset($last_synch['buyers'][$list_id])){
			//synch orders from the beginning
			$condition.=" AND id > ".$last_synch['buyers'][$list_id]." ";
		}

		//get registered users
		$registered_users = $this->get_registered_users();
		
		//get completed orders
		$query = "SELECT p.ID, pm.meta_value as email FROM ".$wpdb->prefix."posts p ";
		$query.= "JOIN ".$wpdb->prefix."postmeta pm ON p.ID = pm.post_id ";
		$query.= "WHERE post_type = 'shop_order' AND p.post_status='wc-completed' AND pm.meta_key = '_billing_email' AND pm.meta_value != '' ";
		$query.= $condition;
		$query.= "ORDER BY p.ID ASC LIMIT 200";
		$response = $wpdb->get_results($query);

		if(!empty($response)){
			foreach($response as $k=>$v){
				$order = wc_get_order( $v->ID );
				$completed_orders_by_email[$v->email] = $this->get_mapped_fields($order);
				$last_id = $v->ID;
			}
		}

		$subscribers['items'] =  array();
		$subscribers['fields'] =  array();

		if(empty($completed_orders_by_email)){
			wp_die();
		};

		foreach($completed_orders_by_email as $email=>$fields){
			$subscribers['items'][] = array('email'=>$email, 'fields'=>$fields);

			foreach ($fields as $k => $v) {
				if(!in_array($v['name'], $subscribers['fields'])) {
					$subscribers['fields'][] = $v['name'];
				}
			}
		}
			
		$subscriber_resource = $this->doppler_service->getResource( 'subscribers' );
		$this->set_origin();
		$response = json_decode($subscriber_resource->importSubscribers($list_id, $subscribers)['body']);
		if(!empty($response->createdResourceId)){
			if(!empty($last_id)){
				$last_synch['buyers'][$list_id] = $last_id;	
			}
			update_option('dplrwoo_last_synch', $last_synch);
		}
	}

	/**
	 * Goes through complete orders and users (subscribers + customers)
	 * and upload emails to Contact list
	 */
	public function dplrwoo_synch_contacts_cron(){
		global $wpdb;

		//Get list.
		$list_id = get_option('dplr_subscribers_list')['contacts'];
		if(empty($list_id)) return false;

		$last_user_id = 0;
		$last_order_id = 0;
		$condition_users = '';
		$condition_orders = '';
		$orders_by_email = array();
		$registered_users = array();		
		
		$fields_map = get_option('dplrwoo_mapping');
		
		//Get last synch info.
		$last_synch = get_option('dplrwoo_last_synch');
		
		//Synch!
		if(!empty($last_synch) && isset($last_synch['contacts'][$list_id])){
			//synch orders from the beginning
			$condition_orders.=" AND id > ".$last_synch['contacts'][$list_id]['orders']." ";
			$condition_users.= " AND id > ".$last_synch['contacts'][$list_id]['users']." ";
		}
		
		//get users
		$query = "SELECT u.ID, u.user_email FROM ".$wpdb->prefix."users u ";
		$query.= "JOIN ".$wpdb->prefix."usermeta um ON u.ID = um.user_id WHERE um.meta_key = 'wp_capabilities' ";
		$query.= $condition_users;
		$query.= "AND ( um.meta_value LIKE '%customer%' OR um.meta_value LIKE '%subscriber%' ) ";
		$query.= "ORDER BY u.ID ASC LIMIT 150";
		$response = $wpdb->get_results($query);
		if(!empty($response)){
			foreach($response as $k=>$v){
				$meta_fields = get_user_meta($v->ID);
				$fields = $this->extract_meta_from_user($fields_map, $meta_fields);
				if(isset($meta_fields['billing_email']) && !empty($meta_fields['billing_email'])){
					$aux = array_values($meta_fields['billing_email']);
					$email = array_shift($aux);
					$registered_users[$email] = $fields;
				}
				$last_user_id = $v->ID;
			}
		}

		//get uncomplete orders
		$query = "SELECT p.ID, pm.meta_value as email FROM ".$wpdb->prefix."posts p ";
		$query.= "JOIN ".$wpdb->prefix."postmeta pm ON p.ID = pm.post_id ";
		$query.= "WHERE post_type = 'shop_order' AND p.post_status!='wc-completed' AND pm.meta_key = '_billing_email' AND pm.meta_value != '' ";
		$query.= $condition_orders;
		$query.= "ORDER BY p.ID ASC LIMIT 150";
		$response = $wpdb->get_results($query);
		if(!empty($response)){
			foreach($response as $k=>$v){
				$order = wc_get_order( $v->ID );
				$orders_by_email[$v->email] = $this->get_mapped_fields($order);
				$last_order_id = $v->ID;
			}
		}

		$users = array_merge($registered_users,$orders_by_email);

		$subscribers['items'] =  array();
		$subscribers['fields'] =  array();

		if(empty($users) || empty($list_id)){
			echo '0';
			wp_die();
		};

		foreach($users as $email=>$fields){
			$subscribers['items'][] = array('email'=>$email, 'fields'=>$fields);

			foreach ($fields as $k => $v) {
				if(!in_array($v['name'], $subscribers['fields'])) {
					$subscribers['fields'][] = $v['name'];
				}
			}
		}
	
		$subscriber_resource = $this->doppler_service->getResource( 'subscribers' );
		$this->set_origin();
		$response = json_decode($subscriber_resource->importSubscribers($list_id, $subscribers)['body']);
		if(!empty($response->createdResourceId)){
			if(!empty($last_order_id)) $last_synch['contacts'][$list_id]['orders'] = $last_order_id;	
			if(!empty($last_user_id)) $last_synch['contacts'][$list_id]['users'] = $last_user_id;
			update_option('dplrwoo_last_synch', $last_synch);
		}

	}

	//Excecution of sync cron functions for buyers and contacts.
	public function dplrwoo_synch_cron_func() {
		$this->dplrwoo_synch_buyers_cron();
		$this->dplrwoo_synch_contacts_cron();
	}

	/**
	 * Clear buyers and contacts List.
	 */
	public function dplrwoo_clear_lists() {
		update_option( 'dplr_subscribers_list', array('buyers','') );
		update_option( 'dplr_subscribers_list', array('contacts','') );
		echo '1';
		wp_die();
	}

	/**
	* Update Subscribers count
	*
	* After synchronizing update 
	* the subscribers counter
	* next to the lists selector.
	*
	* This one should be deprected.
	*
	*/
	public function update_subscribers_count() {
		$c_count = 0;
		$b_count = 0;
		$list_resource = $this->doppler_service->getResource( 'lists' );
		$c_list_id = get_option('dplr_subscribers_list')['contacts'];
		if(!empty($c_list_id)){
			$c_count = $list_resource->getList($c_list_id)->subscribersCount;
		}
		$b_list_id = get_option('dplr_subscribers_list')['buyers'];
		if(!empty($b_list_id)){
			$b_count = $list_resource->getList($b_list_id)->subscribersCount;
		}
		echo json_encode(array('contacts'=>$c_count, 'buyers'=>$b_count));
		wp_die();
	}
	
	/**
	 * If want to show an admin message, 
	 * set $this->admin_notice = array( $class, $text), 
	 * where class is success, warning, etc.
	 * 
	 * Also, will show messages from 
	 * Doppler_For_WooCommerce_Admin_Notice class, that
	 * persists through page redirects.
	 */
	public function show_admin_notice() {
		$class = $this->admin_notice[0];
		$text = $this->admin_notice[1];
		if( !empty($class) && !empty($text) ){
			?>
				<div class="notice notice-<?php echo $class?> is-dismissible">
					<p><?php echo $text ?></p>
				</div>
			<?php
		}
		Doppler_For_WooCommerce_Admin_Notice::display_admin_notice();
	}
	
	/**
	 * Get the mapped fields of a given order.
	 */
	private function get_mapped_fields( $order ) {
		$order_data = $order->get_data();
		$fields = array();
		if(empty($order_data)) return $fields;
		
		$fields_map = get_option('dplrwoo_mapping');
		//Map default fields.
		foreach($order_data as $key=>$fieldgroup){
			if( $key === 'shipping' || $key === 'billing' ){	
				foreach($fieldgroup as $fieldname=>$v){
					$f = $key.'_'.$fieldname;
					if( isset($fields_map[$f]) && $fields_map[$f] != '' ){
						if( $f === 'billing_country' || $f === 'shipping_country' ){
							//If is mapped doppler field is string translate this to the full country name.
							if ($fields_map[$f] != 'COUNTRY') $v = $this->get_country_from_code($v);
						}
						//For billing state or shipping state translate the code to string name of the state.
						if( in_array($f,array('billing_state','shipping_state')) ){
							$c = new WC_Countries();
							$states = $c->get_states($order_data[$key]['country']);
							$v = $states[$order_data[$key]['state']];
						}
						$fields[] = array('name'=>$fields_map[$f], 'value'=>$v);
					}
				}
			}
		}
		//Map custom fields
		if(!empty($fields_map)){
			foreach($fields_map as $wc_field=>$dplr_field){
				if( !empty($order->get_meta('_'.$wc_field)) && !empty($dplr_field) ){
					if(array_search($dplr_field, array_column($fields, 'name'))===false) {
						$fields[] = array('name'=>$dplr_field, 'value'=>$order->get_meta('_'.$wc_field));
					}
				}
			}
		}
		return $fields;
	}

	/**
	 * Main function for sending subscriber's data to Doppler.
	 */
	private function subscribe_customer( $list_id, $email, $fields ) {
		if( !empty($list_id) && !empty($email) ){
			$subscriber['email'] = $email;
			$subscriber['fields'] = $fields; 
			$subscriber_resource = $this->doppler_service->getResource('subscribers');
			$this->set_credentials();
			$this->set_origin();
			$result = $subscriber_resource->addSubscriber($list_id, $subscriber);
		}
	}

	/**
	 * Validate subscribers lists
	 */
	private function validate_subscribers_list( $list) {
		return is_array($list) && array_key_exists('buyers',$list) && array_key_exists('contacts',$list);
	}

	/**
	 * Sanitize lists ID array.
	 */
	private function sanitize_subscribers_list( $list ) {
		return array_filter($list,'is_numeric');
	}

	/**
	 * Sanitizes an array of text strings.
	 * Used to sanitize map fields array.
	 */
	private function sanitize_text_array( $list ) {
		return array_map(function($item){
			return sanitize_text_field($item);
		},$list);
	}

	/**
	 * Get country string from a given country code.
	 * Used to send country to doppler only if the mapped
	 * Doppler field for billing country or shipping country
	 * is a string. If for any reason the code isn't found
	 * in WC countries array, just return the code.
	 */
	private function get_country_from_code( $code ) {
		if(!class_exists('WC_Countries')) return $code;
		$c = new WC_Countries();
		$countries = $c->get_countries();
		return !empty($countries[$code])? $countries[$code] : $code;
	}

	/**
	 * Check if list id exists in an array of lists.
	 * Lists must have list_id as key
	 */
	private function list_exists( $list_id, $lists) {
		return in_array($list_id, array_keys($lists));
	}

	/**
	 * Check if a key is saved (already connected) when synching. 
	 * If not, generate a WC API KEY
	 * submit to App, wait for response, and save flag.
	 * 
	 * @return object
	 */
	public function dplrwoo_verify_keys() {
		if(!empty(get_option('dplrwoo_api_connected'))){
			wp_send_json_success();
		}else{
			$options = get_option('dplr_settings');
		    if( !empty($options['dplr_option_useraccount']) &&  !empty($options['dplr_option_apikey'] )){
				
				$app_connect = new Doppler_For_WooCommerce_App_Connect(
					$options['dplr_option_useraccount'],
					$options['dplr_option_apikey'], 
					DOPPLER_WOO_API_URL,
					DOPPLER_FOR_WOOCOMMERCE_ORIGIN
				);

				$response = $app_connect->connect();
				if($response['response']['code'] === 200){
					update_option('dplrwoo_api_connected', array(
						'account' => $options['dplr_option_useraccount'],
						'status' => 'on'
					));
					wp_send_json_success();
				}
			}		
		}
		wp_send_json_error();
	}

	/**
	 * Define custom API endpoint
	 */
	public function dplrwoo_abandoned_endpoint( $controllers ) {
		//Register abadoned cart endpoint.
		register_rest_route( 'wc/v3', 'abandoned-carts', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_abandoned_carts')
		));
		//Register product views endpoint.
		register_rest_route( 'wc/v3', 'viewed-products', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_viewed_products')
		));
	}

	/**
	 * Get abandoned carts.
	 */

	function fix_get_user_data_by_consumer_key( $consumer_key ) {
		return hash_hmac( 'sha256', sanitize_text_field( $consumer_key ), 'wc-api' );
    }

	function validateKeys($result){
		if(stripos($_REQUEST['consumer_key'], '_')){
			$request_ck = substr($_REQUEST['consumer_key'], stripos($_REQUEST['consumer_key'], '_') + 1);
		}
		else{
			$request_ck = $_REQUEST['consumer_key'];
		}

		if( $request_ck == $result->consumer_key ) {
			return true;
		}
		return false;
	}

	function get_abandoned_carts() {
		global $wpdb;
		$result = $wpdb->get_row("SELECT consumer_secret, consumer_key FROM {$wpdb->prefix}woocommerce_api_keys WHERE description = 'Doppler App integration'");
		$temporal = $this->fix_get_user_data_by_consumer_key($_REQUEST['consumer_key']);
		$new_verification = $this->validateKeys($result); 
		if( 
			( $temporal == $result->consumer_key ) ||
			( $new_verification ) ||
			(
				(
					!empty($result->consumer_secret) && 
					!empty($_SERVER['PHP_AUTH_PW']) && 
					($_SERVER['PHP_AUTH_PW'] === $result->consumer_secret)
				) || 
				substr(PHP_SAPI, 0, 3) == 'cgi' 
			)
		){
			if(empty($_GET['from']) || empty($_GET['to'])){
				return array("code"=>"woocommerce_rest_wrong_parameter_count","message"=>"Wrong parameter count","data"=>array("status"=>400));
			}
			if(!$this->validateDate($_GET['from']) || !$this->validateDate($_GET['to'])){
				return array("code"=>"woocommerce_rest_wrong_parameter_count","message"=>"Invalid parameter","data"=>array("status"=>400));
			}
			return $wpdb->get_results( $wpdb->prepare("SELECT id, name, lastname, email, phone, location, cart_contents, cart_total,
			currency, time, session_id, other_fields, cart_url, restored 
			FROM ". $wpdb->prefix . DOPPLER_ABANDONED_CART_TABLE . 
			" WHERE time BETWEEN '%s' AND '%s' ", $_GET['from'], $_GET['to']));
		}else{
			return array("code"=>"woocommerce_rest_cannot_view","message"=>"forbidden","data"=>array("status"=>401));
		}
	}

	/**
	 * Get viewed products.
	 */
	function get_viewed_products() {
		global $wpdb;
		$result = $wpdb->get_row("SELECT consumer_secret, consumer_key FROM {$wpdb->prefix}woocommerce_api_keys WHERE description = 'Doppler App integration'");
		$temporal = $this->fix_get_user_data_by_consumer_key($_REQUEST['oauth_consumer_key']);
		if(
		    ($temporal == $result->consumer_key) ||
			(
				(
					!empty($result->consumer_secret) && 
					!empty($_SERVER['PHP_AUTH_PW']) && 
					($_SERVER['PHP_AUTH_PW'] === $result->consumer_secret)
				) || 
				substr(PHP_SAPI, 0, 3) == 'cgi' 
			)
		){
				if(empty($_GET['from']) || empty($_GET['to'])){
					return array("code"=>"woocommerce_rest_wrong_parameter_count","message"=>"Wrong parameter count","data"=>array("status"=>400));
				}
				if(!$this->validateDate($_GET['from']) || !$this->validateDate($_GET['to'])){
					return array("code"=>"woocommerce_rest_wrong_parameter_count","message"=>"Invalid parameter","data"=>array("status"=>400));
				}
				return $wpdb->get_results( $wpdb->prepare(
					"SELECT id, user_id, user_name, user_lastname, user_email, product_id, product_name, 
					product_slug, product_link, product_price, product_regular_price, product_description, currency, visited_time, product_image
					FROM ". $wpdb->prefix . DOPPLER_VISITED_PRODUCTS_TABLE .
					" WHERE visited_time BETWEEN '%s' AND '%s' ", $_GET['from'], $_GET['to']));
		}else{
			return array("code"=>"woocommerce_rest_cannot_view","message"=>"forbidden","data"=>array("status"=>401));
		}
	}

	/**
	 * Delete old abandoned carts registers. (older than 1 day).
	 */
	function dplrwoo_delete_carts() {
		global $wpdb;
		$result = $wpdb->query("DELETE FROM {$wpdb->prefix}dplrwoo_abandoned_cart 
			WHERE time < NOW() - INTERVAL 7 DAY " );
	}

	/**
	 * Delete old product views. (older than 7 days).
	 */
	function dplrwoo_delete_product_views() {
		global $wpdb;
		$result = $wpdb->query("DELETE FROM {$wpdb->prefix}dplrwoo_visited_products 
			WHERE visited_time < NOW() - INTERVAL 7 DAY " );
	}

	/**
	 * Check database version, update if necessary.
	 * This is done to update db older than version 1.0.2
	 */
	private function update_database() {
		global $wpdb;
		$options = get_option('dplr_settings');

		$abandoned_table_name = $wpdb->prefix . 'dplrwoo_abandoned_cart';
		$visited_table_name = $wpdb->prefix . 'dplrwoo_visited_products';
		if(
			!($wpdb->get_var("SHOW TABLES LIKE '$visited_table_name'") == $visited_table_name) || 
			!($wpdb->get_var("SHOW TABLES LIKE '$abandoned_table_name'") == $abandoned_table_name)){
			require_once DOPPLER_FOR_WOOCOMMERCE_PLUGIN_DIR_PATH . 'includes/class-doppler-for-woocommerce-activator.php';
			Doppler_For_Woocommerce_Activator::activate();
		}
		//Connect if version es < 1.1.0 and connect flag is false.
		$current_version =  get_option('dplrwoo_version');
		if( version_compare($current_version, '1.1.0', '<') ){
			if( empty(get_option('dplrwoo_api_connected'))	&&
			!empty($options['dplr_option_useraccount']) && 
			!empty($options['dplr_option_apikey']) ){
				$DopplerAppConnect = new Doppler_For_WooCommerce_App_Connect(
					$options['dplr_option_useraccount'], $options['dplr_option_apikey'],
					DOPPLER_WOO_API_URL, DOPPLER_FOR_WOOCOMMERCE_ORIGIN
				);
				$response = $DopplerAppConnect->connect();
				if($response['response']['code']==200){
					//save flag with current account.
					update_option('dplrwoo_api_connected', array(
						'account' => $options['dplr_option_useraccount'],
						'status' => 'on'
					));
				}
			}
			update_option('dplrwoo_version', DOPPLER_FOR_WOOCOMMERCE_VERSION);
		}
	}

	/**
	 * Display confirm window for deactivation
	 */
	public function display_deactivation_confirm_html() {
		?>
		<div id="dplrwoo-dialog" title="<?php _e("Doppler for WooCommerce", "doppler-for-woocommerce")?>">
			<p><span class="dashicons dashicons-warning" style="float:left; margin:12px 12px 20px 0;"></span>
			<?php _e("By confirming this action some automations in your Doppler Woocommerce activation can be lost. <br> ¿Do yo wish to continue? ", "doppler-for-woocommerce")?></p>
		</div>
		<?php
	}
}