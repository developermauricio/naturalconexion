<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * This class handles all functionality related
 * to the managment of abandoned carts.
 * 
 * @since      1.0.2
 * @package    Doppler_For_Woocommerce
 * @subpackage Doppler_For_Woocommerce/includes
 * @author     Doppler LLC <info@fromdoppler.com>
 */

 class Doppler_For_Woocommerce_Abandoned_Cart {

    protected $cart_session_table;

    public function __construct( $table ) {
        $this->cart_session_table = $table; 
    }

    private function get_cart_session_table() {
        return $this->cart_session_table;
    }

    /**
     * Get content of the current cart.
     * 
     * @since  1.0.2
	 * Return: Array
     */
    private function get_cart() {
        global $woocommerce;

		$cart_total = WC()->cart->total;
		$cart_currency = get_woocommerce_currency();
        $current_time = current_time( 'mysql', true ); //work with GMT time.
        
		//Retrieving customer ID from WooCommerce sessions variable in order to use it as a session_id value	
		$session_id = WC()->session->get_customer_id();

		//Retrieving cart
		$products = $woocommerce->cart->cart_contents;
		$product_array = array();
				
		foreach($products as $product){
			$item = wc_get_product($product['data']->get_id());
			$product_title = $item->get_title();
			$product_image = wp_get_attachment_url($item->get_image_id());
			$product_description = $item->get_description();
			$product_link = $item->get_permalink();
			$product_quantity = $product['quantity'];
			$product_variation_price = $product['line_total'];
			
			// Handling product variations
			if($product['variation_id']){ //If user has chosen a variation
				$single_variation = new WC_Product_Variation($product['variation_id']);
				$variation_data  = $product['variation'];            
				//Handling variable product title output with attributes
				$product_attributes = $this->attribute_slug_to_title($single_variation->get_variation_attributes());
				$product_variation_id = $product['variation_id'];
			}else{
				$product_attributes = false;
				$product_variation_id = '';
				$variation_data = false;
			}

			//Inserting Product title, Variation and Quantity into array
			$product_array[] = array(
				'product_title' => $product_title . $product_attributes,
				'quantity' => $product_quantity,
				'product_id' => $product['product_id'],
				'product_variation_id' => $product_variation_id,
				'product_variation_price' => $product_variation_price,
				'product_variation_data' => $variation_data,
				'product_image' => $product_image,
				'product_description' => $product_description,
			);
		}

        return  array(  'cart_total' => $cart_total, 
                        'cart_currency' => $cart_currency, 
                        'current_time' => $current_time, 
                        'session_id' => $session_id, 
                        'product_array' => $product_array );

    }

    /**
	 * Returns product attribute
	 *
	 * @since    1.0.2
	 * Return: String
	 */
	private function attribute_slug_to_title( $product_variations ) {
		//global $woocommerce;
		$attribute_array = array();
		
		if($product_variations){
			foreach($product_variations as $product_variation_key => $product_variation_name){
				$value = '';
				if ( taxonomy_exists( esc_attr( str_replace( 'attribute_', '', $product_variation_key )))){
					$term = get_term_by( 'slug', $product_variation_name, esc_attr( str_replace( 'attribute_', '', $product_variation_key )));
					if (!is_wp_error($term) && !empty($term->name)){
						$value = $term->name;
						if(!empty($value)){
							$attribute_array[] = $value;
						}
					}
				}else{
					$value = apply_filters( 'woocommerce_variation_option_name', $product_variation_name );
					if(!empty($value)){
						$attribute_array[] = $value;
					}
				}
			}
			
			//Generating attribute output			
			$total_variations = count($attribute_array);
			$increment = 0;
			$product_attribute = '';
			foreach($attribute_array as $attribute){
				if($increment === 0 && $increment != $total_variations - 1){ //If this is first variation and we have multiple variations
					$colon = ': ';
					$comma = ', ';
				}
				elseif($increment === 0 && $increment === $total_variations - 1){ //If we have only one variation
					$colon = ': ';
					$comma = false;
				}
				elseif($increment === $total_variations - 1) { //If this is the last variation
					$comma = '';
					$colon = false;
				}else{
					$comma = ', ';
					$colon = false;
				}
				$product_attribute .= $colon . $attribute . $comma;
				$increment++;
			}
			return $product_attribute;
		}
		else{
			return;
		}
	}

    /**
	 * Automatically saves a cart if a logged in user adds, 
     * removes or update something from cart.
	 * @since    1.0.2
	 */
	function save_cart_session() {
        
        if(!is_user_logged_in()) return false;
        
        global $wpdb;
		$table_name = $this->get_cart_session_table();
		$token = openssl_random_pseudo_bytes(16);
		$token = bin2hex($token);
		$cart_url = wc_get_cart_url();

        //Retrieving cart array consisting of currency, cart total, time, session id and products and their quantities
        $cart_data = $this->get_cart();
        $cart_total = $cart_data['cart_total'];
        $cart_currency = $cart_data['cart_currency'];
        $current_time = $cart_data['current_time'];
        $session_id = $cart_data['session_id'];
        $product_array = $cart_data['product_array'];
        //Where is this setted?
		$dplr_cart_session_id = WC()->session->get('dplr_cart_session_id');

        //In case if the user updates the cart and takes out all items from the cart
        if(empty($product_array)){
            $this->clear_cart_session();
            return;
        }

        $abandoned_cart = '';

		//Check in the database if the current user has got an abandoned cart already
        if( $dplr_cart_session_id === NULL ){
            $abandoned_cart = $wpdb->get_row($wpdb->prepare(
                "SELECT session_id FROM ". $table_name ."
                WHERE session_id = %d", get_current_user_id())
			);
			if(!empty($abandoned_cart->session_id)) $dplr_cart_session_id = $abandoned_cart->session_id;
        }

        $current_session_exist_in_db = $this->current_session_exist_in_db($dplr_cart_session_id);
		
		//If the current user has got an abandoned cart already 
        //or if we have already inserted the Users session ID in Session variable and it is not NULL 
        //and already inserted the Users session ID in Session variable we update the abandoned cart row
        if( $current_session_exist_in_db && (!empty($abandoned_cart) || $dplr_cart_session_id !== NULL )){
            //If the user has got an abandoned cart previously, we set session ID back
            if(!empty($abandoned_cart)){
                $session_id = $abandoned_cart->session_id;
                //Storing session_id in WooCommerce session
                WC()->session->set('dplr_cart_session_id', $session_id);

            }else{
                $session_id = $dplr_cart_session_id;
            }
            
            //Updating row where user's Session id = same as prevously saved in Session
            //Updating only Cart related data since the user can change his data only in the Checkout form
            $wpdb->prepare('%s',
                $wpdb->update(
                    $table_name,
                    array(
                        'cart_contents'	=>	serialize($product_array),
                        'cart_total'	=>	sanitize_text_field( $cart_total ),
                        'currency'		=>	sanitize_text_field( $cart_currency ),
						'time'			=>	sanitize_text_field( $current_time ),
						'cart_url'		=>  $cart_url,
                    ),
                    array('session_id' => $session_id),
                    array('%s', '%0.2f', '%s', '%s', '%s'),
                    array('%s')
                )
            );
            
        }else{

            //Looking if a user has previously made an order
            //If not, using default WordPress assigned data
            $current_user = wp_get_current_user(); 

            $name = $current_user->billing_first_name? $current_user->billing_first_name : $current_user->user_firstname;
            $surname = $current_user->billing_last_name? $current_user->billing_last_name : $current_user->user_lastname;
            $email = $current_user->billing_email?  $current_user->billing_email : $current_user->user_email;
            $phone = $current_user->billing_phone;

            //Handling users address
            if($current_user->billing_country){
                $country = $current_user->billing_country;
                if($current_user->billing_state){ //checking if the state was entered
                    $city = ", ". $current_user->billing_state;
                }else{
                    $city = '';
                }
                $location = $country . $city; 
            }else{
                $location = WC_Geolocation::geolocate_ip(); //Getting users country from his IP address
                $location = $location['country'];
            }

            //Inserting row into Database
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO ". $table_name ."
                    ( name, lastname, email, phone, location, cart_contents, cart_total, currency, time, session_id, cart_url, token )
                    VALUES ( %s, %s, %s, %s, %s, %s, %0.2f, %s, %s, %s, %s, %s)",
                    array(
                        sanitize_text_field( $name ),
                        sanitize_text_field( $surname ),
                        sanitize_email( $email ),
                        filter_var($phone, FILTER_SANITIZE_NUMBER_INT),
                        sanitize_text_field( $location ),
                        serialize($product_array),
                        sanitize_text_field( $cart_total ),
                        sanitize_text_field( $cart_currency ),
                        sanitize_text_field( $current_time ),
						sanitize_text_field( $session_id ),
						$cart_url,
						$token
                    ) 
                )
            );

            //print($wpdb->last_error);

            //Storing session_id in WooCommerce session
            WC()->session->set('dplr_cart_session_id', $session_id);

            //Is this necessary?
            //$this->increase_captured_abandoned_cart_count(); //Increasing total count of captured abandoned carts
        }
	}

	/**
	 * Save user's data with ajax after changing email from checkout.
	 */
	function save_frontend_user_data() {
	
		if ( isset( $_POST["dplrwoo_email"] ) ) {
			global $wpdb;
			$table_name = $this->get_cart_session_table();
			$cart_url = wc_get_cart_url();
			//Generate a random string.
			$token = openssl_random_pseudo_bytes(16);
			$token = bin2hex($token);

			$cart_data = $this->get_cart();
			$cart_total = $cart_data['cart_total'];
			$cart_currency = $cart_data['cart_currency'];
			$current_time = $cart_data['current_time'];
			$session_id = $cart_data['session_id'];
			$product_array = $cart_data['product_array'];
			$dplr_cart_session_id = WC()->session->get('dplr_cart_session_id');

			//If cart has no items delete the abandoned cart.
			if(empty($product_array)){
				$this->delete_cart_session();
				return;
			}
			
			(isset($_POST['dplrwoo_name'])) ? $name = $_POST['dplrwoo_name'] : $name = '';
			(isset($_POST['dplrwoo_surname'])) ? $surname = $_POST['dplrwoo_lastname'] : $surname = '';
			(isset($_POST['dplrwoo_phone'])) ? $phone = $_POST['dplrwoo_phone'] : $phone = '';
			(isset($_POST['dplrwoo_country'])) ? $country = $_POST['dplrwoo_country'] : $country = '';
			(isset($_POST['dplrwoo_city']) && $_POST['dplrwoo_city'] != '') ? $city = ", ". $_POST['dplrwoo_city'] : $city = '';
			(isset($_POST['dplrwoo_billing_company'])) ? $company = $_POST['dplrwoo_billing_company'] : $company = '';
			(isset($_POST['dplrwoo_billing_address_1'])) ? $address_1 = $_POST['dplrwoo_billing_address_1'] : $address_1 = '';
			(isset($_POST['dplrwoo_billing_address_2'])) ? $address_2 = $_POST['dplrwoo_billing_address_2'] : $address_2 = '';
			(isset($_POST['dplrwoo_billing_state'])) ? $state = $_POST['dplrwoo_billing_state'] : $state = '';
			(isset($_POST['dplrwoo_billing_postcode'])) ? $postcode = $_POST['dplrwoo_billing_postcode'] : $postcode = '';
			(isset($_POST['dplrwoo_shipping_first_name'])) ? $shipping_name = $_POST['dplrwoo_shipping_first_name'] : $shipping_name = '';
			(isset($_POST['dplrwoo_shipping_last_name'])) ? $shipping_surname = $_POST['dplrwoo_shipping_last_name'] : $shipping_surname = '';
			(isset($_POST['dplrwoo_shipping_company'])) ? $shipping_company = $_POST['dplrwoo_shipping_company'] : $shipping_company = '';
			(isset($_POST['dplrwoo_shipping_country'])) ? $shipping_country = $_POST['dplrwoo_shipping_country'] : $shipping_country = '';
			(isset($_POST['dplrwoo_shipping_address_1'])) ? $shipping_address_1 = $_POST['dplrwoo_shipping_address_1'] : $shipping_address_1 = '';
			(isset($_POST['dplrwoo_shipping_address_2'])) ? $shipping_address_2 = $_POST['dplrwoo_shipping_address_2'] : $shipping_address_2 = '';
			(isset($_POST['dplrwoo_shipping_city'])) ? $shipping_city = $_POST['dplrwoo_shipping_city'] : $shipping_city = '';
			(isset($_POST['dplrwoo_shipping_state'])) ? $shipping_state = $_POST['dplrwoo_shipping_state'] : $shipping_state = '';
			(isset($_POST['dplrwoo_shipping_postcode'])) ? $shipping_postcode = $_POST['dplrwoo_shipping_postcode'] : $shipping_postcode = '';
			(isset($_POST['dplrwoo_order_comments'])) ? $comments = $_POST['dplrwoo_order_comments'] : $comments = '';
			(isset($_POST['dplrwoo_create_account'])) ? $create_account = $_POST['dplrwoo_create_account'] : $create_account = '';
			(isset($_POST['dplrwoo_ship_elsewhere'])) ? $ship_elsewhere = $_POST['dplrwoo_ship_elsewhere'] : $ship_elsewhere = '';
			
			$other_fields = array(
				'dplrwoo_billing_company' 		=> $company,
				'dplrwoo_billing_address_1' 		=> $address_1,
				'dplrwoo_billing_address_2' 		=> $address_2,
				'dplrwoo_billing_state' 			=> $state,
				'dplrwoo_billing_postcode' 		=> $postcode,
				'dplrwoo_shipping_first_name' 	=> $shipping_name,
				'dplrwoo_shipping_last_name' 	=> $shipping_surname,
				'dplrwoo_shipping_company' 		=> $shipping_company,
				'dplrwoo_shipping_country' 		=> $shipping_country,
				'dplrwoo_shipping_address_1' 	=> $shipping_address_1,
				'dplrwoo_shipping_address_2' 	=> $shipping_address_2,
				'dplrwoo_shipping_city' 			=> $shipping_city,
				'dplrwoo_shipping_state' 		=> $shipping_state,
				'dplrwoo_shipping_postcode' 		=> $shipping_postcode,
				'dplrwoo_order_comments' 		=> $comments,
				'dplrwoo_create_account' 		=> $create_account,
				'dplrwoo_ship_elsewhere' 		=> $ship_elsewhere
			);
			
			$location = $country . $city;

			$current_session_exist_in_db = $this->current_session_exist_in_db($dplr_cart_session_id);

			if( $current_session_exist_in_db && $dplr_cart_session_id !== NULL ){
				$wpdb->prepare('%s',

					$wpdb->update(
						$table_name,
						array(
							'name'			=>	sanitize_text_field( $name ),
							'lastname'		=>	sanitize_text_field( $surname ),
							'email'			=>	sanitize_email( $_POST['dplrwoo_email'] ),
							'phone'			=>	filter_var( $phone, FILTER_SANITIZE_NUMBER_INT),
							'location'		=>	sanitize_text_field( $location ),
							'cart_contents'	=>	serialize($product_array),
							'cart_total'	=>	sanitize_text_field( $cart_total ),
							'currency'		=>	sanitize_text_field( $cart_currency ),
							'time'			=>	sanitize_text_field( $current_time ),
							'other_fields'	=>	sanitize_text_field( serialize($other_fields) ),
							'cart_url '=> $cart_url,
						),
						array('session_id' => $dplr_cart_session_id),
						array('%s', '%s', '%s', '%s', '%s', '%s', '%0.2f', '%s', '%s', '%s', '%s'),
						array('%s')
					)
				);

			}else{
				
				$wpdb->query(
					$wpdb->prepare(
						"INSERT INTO ". $table_name ."
						( name, lastname, email, phone, location, cart_contents, cart_total, currency, time, session_id, other_fields, cart_url, token )
						VALUES ( %s, %s, %s, %s, %s, %s, %0.2f, %s, %s, %s, %s, %s, %s)",
						array(
							sanitize_text_field( $name ),
							sanitize_text_field( $surname ),
							sanitize_email( $_POST['dplrwoo_email'] ),
							filter_var($phone, FILTER_SANITIZE_NUMBER_INT),
							sanitize_text_field( $location ),
							serialize($product_array),
							sanitize_text_field( $cart_total ),
							sanitize_text_field( $cart_currency ),
							sanitize_text_field( $current_time ),
							sanitize_text_field( $session_id ),
							sanitize_text_field( serialize($other_fields) ),
							$cart_url,
							$token
						)
					)
				);
				
				WC()->session->set('dplr_cart_session_id', $session_id);
				//$this->increase_captured_abandoned_cart_count();
				wp_die();
			}
		}
	}
	
	/**
	 * Save cart session for users who are not logged in
	 */
	function update_cart_session() {

		if(is_user_logged_in()) return false;

		$dplr_cart_session_id = WC()->session->get('dplr_cart_session_id');

		if( $dplr_cart_session_id !== NULL ){
			
			global $wpdb;
			$table_name = $this->get_cart_session_table();
			$cart_data = $this->get_cart();
			$product_array = $cart_data['product_array'];
			$cart_total = $cart_data['cart_total'];
			$cart_currency = $cart_data['cart_currency'];
			$current_time = $cart_data['current_time'];

			//In case if the cart has no items in it, we need to delete the abandoned cart
			if(empty($product_array)){
				$this->clear_cart_session();
				return;
			}

			//Updating row in the Database where users Session id = same as prevously saved in Session
			$wpdb->prepare('%s',
				$wpdb->update(
					$table_name,
					array(
						'cart_contents'	=>	serialize($product_array),
						'cart_total'	=>	sanitize_text_field( $cart_total ),
						'currency'		=>	sanitize_text_field( $cart_currency ),
						'time'			=>	sanitize_text_field( $current_time )
					),
					array('session_id' => $dplr_cart_session_id),
					array('%s', '%0.2f', '%s', '%s'),
					array('%s')
				)
			);
		}
	}


   /**
	 * Function to clear cart session row
	 *
	 * @since    1.0.2
	 */
	function clear_cart_session() {
		global $wpdb;
		$table_name = $this->get_cart_session_table();
		
        //If a new Order is added from the WooCommerce admin panel, 
        //we must check if WooCommerce session is set. Otherwise we would get a Fatal error.
		if(isset(WC()->session)){

			$dplr_cart_session_id = WC()->session->get('dplr_cart_session_id');
			if(isset($dplr_cart_session_id)){

				$cart_data = $this->get_cart();
				$cart_currency = $cart_data['cart_currency'];
				$current_time = $cart_data['current_time'];
				
				//Cleaning Cart data
				$wpdb->prepare('%s',
					$wpdb->update(
						$table_name,
						array(
							'cart_contents'	=>	'',
							'cart_total'	=>	0,
							'currency'		=>	sanitize_text_field( $cart_currency ),
							'time'			=>	sanitize_text_field( $current_time )
						),
						array('session_id' => $dplr_cart_session_id),
						array('%s', '%s'),
						array('%s')
					)
				);
			}
		}
	}
	
	/**
	 * Delete row from table if the user completed the checkout
	 */
	function delete_cart_session() {
		global $wpdb;
		$table_name = $this->get_cart_session_table();
		if(isset(WC()->session)){

			$dplr_cart_session_id = WC()->session->get('dplr_cart_session_id');
			if(isset($dplr_cart_session_id)){
				//Deleting row from database
				//$wpdb->show_errors();
				$resp = $wpdb->query(
					$wpdb->prepare(
						"DELETE FROM ". $table_name ."
						 WHERE session_id = %s",
						sanitize_key($dplr_cart_session_id)
					)
				);
				
				//$wpdb->print_error();
				//$this->decrease_captured_abandoned_cart_count( $count = false ); //Decreasing total count of captured abandoned carts
			}
			
			$this->unset_session_id();
		}
	}
    
    /**
	 * Check if current user session ID exists in database
	 *
	 * @since    1.0.2
	 * @return  boolean
	 */
	function current_session_exist_in_db($dplr_cart_session_id) {
		if( $dplr_cart_session_id !== NULL ){
			global $wpdb;
			$main_table = $this->get_cart_session_table();

			//Check if abandoned cart already exists in database
			return $wpdb->get_var($wpdb->prepare(
				"SELECT session_id
				FROM ". $main_table ."
				WHERE session_id = %s",
				$dplr_cart_session_id
			));

		}else{
			return false;
		}
	}

	/**
	 * Function decreases the total count of captured abandoned carts
	 *
	 * @since    3.0
	 */
	/*
	function decrease_captured_abandoned_cart_count($count){
		if(!$count){
			$count = 1;
		}
		$previously_captured_abandoned_cart_count = get_option('captured_abandoned_cart_count');
		update_option('captured_abandoned_cart_count', $previously_captured_abandoned_cart_count - $count); //Decreasing the count by one abandoned cart
	}*/

	/**
	 * Function unsets session variable
	 *
	 * @since    3.0
	 */
	function unset_session_id() {
		//Removing stored ID value from WooCommerce Session
		WC()->session->__unset('dplr_cart_session_id');
	}

	/**
	 * Returns cart items from abandoned cart.
	 * Validates if token is valid.
	 * @return array | array of cart items.
	 */
	private function get_cart_contents_from_session( $session_id, $token ) {
		global $wpdb;
		$main_table = $this->get_cart_session_table();
		$response = $wpdb->get_row( $wpdb->prepare("SELECT cart_contents, token FROM {$main_table} WHERE session_id = %d", $session_id));
		if( $response->token !== $token ) return false;
		return unserialize($response->cart_contents);
	}

	/**
	 * When recieving a session id from Doppler we can 
	 * restore the cart items from the session id.
	 */
	public function restore_cart() {
		global $wpdb;

		if(empty($_GET['cart_session']) && empty($_GET['token']) ) return false;
		//TODO: Validate token.
		if( is_page( 'cart' ) || is_cart() ){
			
			$items = $this->get_cart_contents_from_session($_GET['cart_session'], $_GET['token']);
			if( empty( $items ) ) return false;

			//Clear cart
			WC()->cart->empty_cart();
			
			if(count($items)>0){
				foreach($items as $item){
					WC()->cart->add_to_cart( $item['product_id'], $item['quantity'], 
					$item['product_variation_id'], $item['product_variation_data']);
				}
			}

			$wpdb->update( 
				$this->get_cart_session_table(), 
				array("restored"=>1), 
				array("session_id"=> $_GET['cart_session']) 
			);
		}
	}

}