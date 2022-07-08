<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * This class handles the communication 
 * with the api to connect or disconnect
 * the app integration.
 *  
 * @since 1.0.2
 * @author Hernán Galván <hgalvan@makingsense.com>
 */
class Doppler_For_WooCommerce_App_Connect {

	const INTEGRATION = 'woocommerce';
	const DEBUG_MODE = true;
	private $api_account;
	private $api_key;
	private $api_url;
	private $origin;
	private $api_keys_description;

	/**
	 *	  
	 * @param string	$api_account	Doppler username
	 * @param string	$api_key		Doppler API Key
	 * @param string	$api_url		The Doppler API URL.
	 * @param string	$origin			The authorized origin header parameter (WordPress, WooCommerce, etc.)
	 *
	 */
    public function __construct( $api_account, $api_key, $api_url, $origin ) {
		$this->api_key = $api_key;
		$this->api_account = $api_account;
		$this->api_url = $api_url;
		$this->origin = $origin;
        $this->api_keys_description = 'Doppler App integration'; 
    }
	
	private function get_api_account() {
        return $this->api_account;
    }

    private function get_api_key() {
		return $this->api_key;		
    }

    private function get_api_url() {
        return $this->api_url;
    }

    private function get_api_keys_description() {
        return $this->api_keys_description;
	}
	private function get_origin() {
        return $this->origin;
	}

    /**
     * Header to use in the requests to API.
	 * 
	 * @since 1.0.2
	 * @return string
     */
    private function set_request_header() {
        return array(
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "X-Doppler-Subscriber-Origin" => $this->get_origin(),
            "Authorization" => "token ". $this->get_api_key(),
        );
	}
	
	/**
	 * Handles the requests to API
	 * 
	 * @since 1.0.2
	 * 
	 * @param array 	$body 	An array with the body to be sent
	 * @param string	$method	The http method to be used.
	 * @return array|object 
	 * 
	 */
	public function do_request( $body = array() , $method ) {
		
		$api_url = $this->get_api_url();
		$account = $this->get_api_account();
		
		if(empty($account) || empty($api_url)) return false;
		
		$url = $api_url . 'accounts/'. $account. '/integrations/' . self::INTEGRATION;
		 return wp_remote_request($url, array(
			'method' => $method,
			'headers'=> $this->set_request_header(),
			'timeout' => 12,
			'body'=> json_encode($body)
		));		
	}

    /**
	 * Send API credentials to Doppler API
	 * to establish connection.
	 * 
	 * @since 1.0.2
	 * @return array|object
	 */
    public function connect() {
		//just in case, remove previous keys.
		$this->remove_keys();
		$keys = $this->generate_WC_Api_keys();
		if(!$keys) return false;
		$body = array(
			'accessToken'=> $keys['consumer_key'], 
			'accountName' => get_site_url(), 
			'refreshToken' => $keys['consumer_secret']
		);
		$response = $this->do_request($body, 'PUT');
		return $response;
    }

	/**
	 * Delete ingregration with Doppler,
	 * remove keys from WC
	 * 
	 * @since 1.0.2
	 * @return array|object
	 */
    public function disconnect(){
		return $this->do_request([], 'DELETE');		
	}

	/**
	 * Delete current keys. 
	 *  @since 1.1.0
	 */
	public function remove_keys() {
		global $wpdb;
		try{
			$wpdb->DELETE(
				$wpdb->prefix . 'woocommerce_api_keys',
				array(
					'description' => $this->get_api_keys_description(),
				)
			);
		}
		catch( Exception $e ){
			return false;
		}
	}

    /**
	 * Generate api keys
	 *
	 * @since 1.0.2
	 * @return array|boolean
	 */
	private function generate_WC_Api_keys() {
		global $wpdb;
		if( ! function_exists('wp_current_user_can') ){
			include_once(ABSPATH . 'wp-includes/pluggable.php');
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return false;
		}
		$response = array();
		try {
			
			$key_id = 0;
			$description = sanitize_text_field( wp_unslash( $this->get_api_keys_description() ));
			$permissions = 'read';
			$user_id     = get_current_user_id();
			// Check if current user can edit other users.
			if ( $user_id && ! current_user_can( 'edit_user', $user_id ) ) {
				if ( get_current_user_id() !== $user_id ) {
					throw new Exception( __( 'You do not have permission to assign API Keys to the selected user.', 'woocommerce' ) );
				}
			}
			
			$consumer_key    = 'ck_' . $this->rand_hash();
			$consumer_secret = 'cs_' . $this->rand_hash();

			$data = array(
				'user_id'         => $user_id,
				'description'     => $description,
				'permissions'     => $permissions,
				'consumer_key'    => $this->api_hash( $consumer_key ),
				'consumer_secret' => $consumer_secret,
				'truncated_key'   => substr( $consumer_key, -7 ),
			);

			$wpdb->insert(
				$wpdb->prefix . 'woocommerce_api_keys',
				$data,
				array(
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);

			//$wpdb->print_error();				
			$key_id                      = $wpdb->insert_id;
			$response                    = $data;
			$response['consumer_key']    = $consumer_key;
			$response['consumer_secret'] = $consumer_secret;
			$response['message']         = __( 'API Key generated successfully. Make sure to copy your new keys now as the secret key will be hidden once you leave this page.', 'woocommerce' );
			$response['revoke_url']      = '<a style="color: #a00; text-decoration: none;" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'revoke-key' => $key_id ), admin_url( 'admin.php?page=wc-settings&tab=advanced&section=keys' ) ), 'revoke' ) ) . '">' . __( 'Revoke key', 'woocommerce' ) . '</a>';
		
		} catch ( Exception $e ) {
			//return array( 'message' => $e->getMessage());
			return false;
		}

		return $response;
	}

	private function rand_hash(){
		
		if ( ! function_exists( 'openssl_random_pseudo_bytes' ) ) {
			return sha1( wp_rand() );
		}

		return bin2hex( openssl_random_pseudo_bytes( 20 ) ); // @codingStandardsIgnoreLine
	}

	private function api_hash( $data ) {
		return hash_hmac( 'sha256', $data, 'wc-api' );
	}


}

