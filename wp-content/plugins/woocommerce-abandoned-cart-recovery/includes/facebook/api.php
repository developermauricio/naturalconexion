<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 04-06-19
 * Time: 4:21 PM
 */

namespace WACVP\Inc\Facebook;

use Facebook;

use Facebook\Exceptions\FacebookSDKException;
use Facebook\Exceptions\FacebookResponseException;

use WACVP\Inc\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Api {

	protected static $instance = null;
	private $ver = 'v5.0';
	protected $fb;

	private function __construct() {
		add_action( 'init', [ $this, 'connect_init' ] );
	}

	/**
	 * Setup instance attributes
	 *
	 * @since     1.0.0
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function connect_init() {
		$this->fb = $this->connect();

	}

	public function check_token_live( $token ) {
		$fb = $this->connect();
		try {
			$extoken  = $fb->getOAuth2Client();
			$ex_token = $extoken->debugToken( $token );

			return $ex_token->getIsValid();
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e->getMessage();
		}
	}

	public function connect() {
		$data = Data::get_params();
		try {
			$fb = new Facebook\Facebook( [
				'app_id'                => $data['app_id'],
				'app_secret'            => $data['app_secret'],
				'default_graph_version' => $this->ver,
			] );
		} catch ( \Facebook\Exceptions\FacebookResponseException $e ) {
			return $e;
		} catch ( \Facebook\Exceptions\FacebookSDKException $e ) {
			return $e;
		}

		return $fb;
	}

	public function get_link_login( $link_callback, $permissions = '' ) {
		if ( empty( $link_callback ) ) {
			return array( 'status' => false, 'msg' => 'Link Callback not found!' );
		}
		$fb     = $this->connect();
		$helper = $fb->getRedirectLoginHelper();
		if ( empty( $permissions ) ) {
			$permissions = [ 'email' ];
		}
		$loginUrl = $helper->getLoginUrl( $link_callback, $permissions );

		return $loginUrl;
	}

	public function get_Token( $link_call_back ) {
		$fb = $this->connect();

		$helper = $fb->getRedirectLoginHelper();
		if ( isset( $_GET['state'] ) ) {
			$helper->getPersistentDataHandler()->set( 'state', $_GET['state'] );
		}
		try {
			$accessToken = $helper->getAccessToken( $link_call_back );

			return $accessToken;
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			// When Graph returns an error
			//return $e;
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			//return $e;
			return $e->getMessage();
		}
	}

	public function extoken( $token ) {
		$fb = $this->connect();
		try {
			$extoken  = $fb->getOAuth2Client();
			$ex_token = $extoken->getLongLivedAccessToken( $token );

			return $ex_token->getValue();
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
//			return "error";
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e->getMessage();
//			return "error";
		}
	}

	public function Get_List_Page( $token ) {
		$fb = $this->connect();
		try {
			$response = $fb->get( '/me?fields=accounts.limit(9999){picture{url},name,id,access_token}', $token );   // only get picture, name, id , access_token
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e;
		}
		$user = $response->getGraphObject()->asArray();

		return $user;
	}

	public function Get_Access_Token_Page( $token, $id_page ) {
		$fb = $this->connect();
		try {
			$response = $fb->get( "/$id_page?fields=access_token", $token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e->getMessage();
		}
		$access_token = $response->getGraphObject()->asArray();

		return $access_token;
	}

	public function List_Domain_APP( $id_page, $page_access_token ) {
		$fb = $this->connect();
		try {
			//$page=$this->Me($token)['accounts'];
			$response = $fb->get( "/$id_page/thread_settings?fields=whitelisted_domains", $page_access_token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e->getMessage();
		}
		$list_domain = $response->getGraphList()->asArray();

		return $list_domain;
	}

	public function Set_Domain_APP( $id_page, $domain, $page_access_token ) {
		$fb   = $this->connect();
		$Data = [
			'setting_type'        => 'domain_whitelisting',
			'whitelisted_domains' => $domain,
			'domain_action_type'  => 'add'
		];
		try {
			// Returns a `Facebook\FacebookResponse` object
			$response = $fb->post( "/$id_page/messenger_profile", $Data, $page_access_token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e->getMessage();
		}
		$graphNode = $response->getGraphNode();

		return $graphNode;//$graphNode['result'];
	}

	public function Page_SubScriber_Webhook_APP( $page_access_token, $id_page ) {
		$fb   = $this->connect();
		$data = array( 'subscribed_fields' => 'messages,messaging_postbacks,messaging_optins' );
		try {
			// Returns a `Facebook\FacebookResponse` object
			$response = $fb->post( "/$id_page/subscribed_apps", $data, $page_access_token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e->getMessage();
		}
		$graphNode = $response->getGraphNode();

		return $graphNode;
	}


	public function send_message_text_user_id( $page_id, $page_token, $content_message, $user_id ) {
		$fb = $this->connect();

		$Data = [
			"recipient" => array( "id" => $user_id ),
			"message"   => array( "text" => $content_message ),
		];

		try {
			// Returns a `Facebook\FacebookResponse` object
			$response = $fb->post( "/$page_id/messages", $Data, $page_token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e->getMessage();
		}
		$graphNode = $response->getGraphNode();

		return $graphNode["message_id"];
	}

	public function send_message_text_user_ref( $page_id, $page_token, $content_message, $user_ref ) {
		$fb = $this->connect();

		$Data = [
			"recipient" => array( "user_ref" => $user_ref ),
			"message"   => array( "text" => $content_message ),
		];

		try {
			// Returns a `Facebook\FacebookResponse` object
			$response = $fb->post( "/$page_id/messages", $Data, $page_token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e->getMessage();
		}
		$graphNode = $response->getGraphNode();

		return $graphNode["message_id"];
	}

	public function send_message_abd_cart_user_ref( $page_id, $page_token, $user_ref, $array_product ) {
		$fb   = $this->connect();
		$Data = [
			"recipient" => array( "user_ref" => $user_ref ),
			"message"   => array(
				"attachment" => array(
					"type"    => "template",
					"payload" => array(
						"template_type"      => "generic",
						"image_aspect_ratio" => "square",
						"elements"           => $array_product,
					)
				)
			)
		];
		try {
			// Returns a `Facebook\FacebookResponse` object
			$response = $fb->post( "/$page_id/messages", $Data, $page_token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e->getMessage();
		}
		$graphNode = $response->getGraphNode();

		return $graphNode;//$graphNode['result'];
	}

	public function get_user_profile( $page_token, $user_id ) {
		try {
			$response = $this->fb->get( "/$user_id/?fields=first_name,last_name", $page_token );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			return $e->getMessage();
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			return $e->getMessage();
		}

		return $response->getGraphObject()->asArray();
	}
}
