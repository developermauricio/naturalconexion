<?php

/**
 * Doppler Service v2.0.0
 */

if( ! class_exists( 'Doppler_Service' ) ) :

class Doppler_Service
{

  private $config;

  private $resources;

  private $httpClient;

  private $errors;

  private $origin;

  function __construct($credentials = null) {
    
    $this->config = ['credentials' => []];

    $this->error = 0;

    $this->origin = 'Wordpress';

    $usr_account = '';

    if ($credentials)
      $this->setCredentials($credentials);
    
    $this->resources = [
	  'home'	=> new Doppler_Service_Home_Resource(
	    $this,
      array(
        'methods' => array(
            'get' => array(
              'route' => '',
              'httpMethod' => 'get',
              'parameters' => null
            )
          )
      )
	  ),
      'lists'   => new Doppler_Service_Lists_Resource(
        $this,
        array(
          'methods' => array(
            'get' => array(
              'route'        => 'lists/:listId',
              'httpMethod'  => 'get',
              'parameters'  => array(
                'listId' => array(
                  'on_query_string' => false,
                )
              )
            ),
            'list' => array(
              'route'       => 'lists',
              'httpMethod'  => 'get',
              'state' => 'active',
              'parameters'  => array(
                'page' => array(
                  'on_query_string' => true
                ),
                'per_page' => array(
                  'on_query_string'=>true
                )
              )
            ),
            'new' => array(
              'route' => 'lists',
              'httpMethod' => 'post',
              'parameters' => array()
            ),
            'delete' => array(
              'route' => 'lists/:listId',
              'httpMethod' => 'delete',
              'parameters'  => array(
                'listId' => array(
                  'on_query_string' => false,
                )
              )
            )
          )
        )
      ),
      'fields'  => new Doppler_Service_Fields(
        $this,
        array(
          'methods' => array(
            'list' => array(
              'route'       => 'fields',
              'httpMethod'  => 'get',
              'parameters'  => null
            )
          )
        )
      ),
      'subscribers'  => new Doppler_Service_Subscribers(
        $this,
        array(
          'methods' => array(
            'post' => array(
              'route'       => 'lists/:listId/subscribers',
              'httpMethod'  => 'post',
              'parameters'  => array(
                'listId' => array(
                  'on_query_string' => false,
                )
              )
            ),
            'get' => array(
              'route'       => 'lists/:listId/subscribers',
              'httpmethod'  => 'get',
              'parameters'  => array(
                'listId' => array(
                  'on_query_string' => false,
                )
              )
            ),
            'import' => array(
              'route'   => 'lists/:listId/subscribers/import',
              'httpMethod'  => 'post',
              'parameters'  => array(
                'listId' => array(
                  'on_query_string' => false,
                )
              )
            )
          )
        )
      )
    ];
  }

  public function set_origin( $origin ) {
    $this->origin = $origin;
  }

  public function get_origin() {
    return $this->origin;
  }

  /**
   * Set credentials
   * It wont check API connection anymore.
   */
  public function setCredentials( $credentials = array() ) {
    $this->config['credentials'] = array_merge($credentials, $this->config['credentials'] );
    return true;
  }

  public function unsetCredentials(){
    $this->config['credentials'] = array();
    update_option('dplr_settings', array('dplr_option_apikey'=>'','dplr_option_useraccount'=>''));
  }

  public function connectionStatus() {
    $response = $this->call(array('route' => '', 'httpMethod' => 'get'));
	  return $response;
  }

  function call( $method, $args=null, $body=null ) {
    $url = 'https://restapi.fromdoppler.com/accounts/'. $this->config['credentials']['user_account'] . '/';

    $url .= $method[ 'route' ];
  
    $query = array();
    
    if( $args && count($args)>0 ){
      
      $resourceArg = $method[ 'parameters' ];
      
      foreach ($args as $name => $val) {
        
        isset($resourceArg[ $name ])? $parameter = $resourceArg[ $name ] : $parameter = ''; 
        
        if( $parameter && $parameter[ 'on_query_string' ] ){
          $query[] = $name . "=" . $val ;
        }else{
          $url = str_replace(":".$name, $val, $url);
        }
      
      }

    }


    if(!empty($query)){
      $url.='?'.implode('&',$query);
    }

    $headers=array(
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "X-Doppler-Subscriber-Origin" => $this->get_origin(),
            "Authorization" => "token ". $this->config["credentials"]["api_key"]
             );
             
    $response = "";

    try{

      switch($method['httpMethod']){
        case 'get':
            $response = wp_remote_get($url, array(
              'headers'=>$headers,
              'timeout' => 40
            ));
            break;
        case 'post':  
            $response = wp_remote_post($url, array(
              'headers'=>$headers,
              'timeout' => 40,
              'body'=> json_encode($body)
            ));
            break;
        case 'delete':
            $response = wp_remote_request($url, array(
              'method' => 'DELETE',
              'headers'=>$headers,
              'timeout' => 40,
              'body'=> json_encode($body)
            ));
            break;
      }

      if(WP_DEBUG_LOG_DOPPLER_PLUGINS){

        $msg1 = serialize($method);
        $msg2 = serialize($args);
        $msg3 = serialize($body);
        error_log(
          "\n restapi call-> method: " . $msg1 . "\n args: " . $msg2 . "\n body: " . $msg3, 
          3, 
          wp_upload_dir()['basedir'] . "/doppler-plugins.log"
        );
        error_log(
          "\n restapi -> response: " . print_r($response,true), 
          3, 
          wp_upload_dir()['basedir'] . "/doppler-plugins.log"
        );
      }

      if(empty($response)){
        throw new Exception('Error.');
      }

    }
    catch(\Exception $e){
      return $this->throwConnectionErr($e->getMessage());
    }

    if( is_wp_error( $response ) ) {
      return $this->throwConnectionErr($response->get_error_message());
    }

    return $response;		  

  }

  function getResource( $resourceName ) {
    return $this->resources[ $resourceName ];
  }

  function throwConnectionErr($msg) {
    if( $this->error == 0 && is_admin() ){
      $error = [
        "headers"=>'',
        "body"=>json_encode([
          "title"=>'cURL 28',
          "detail"=> $msg ,
          "errorCode"=> 1,
          "status"=> 528
        ]),
        "response"=>[
          "code"=>528
        ]
      ];
      return $error;
    }
    $this->error = 1;
  }

}

endif;

/**
 * These classes represent the different resources of the API.
 */

if( ! class_exists( 'Doppler_Service_Home_Resource' ) ) :

  class Doppler_Service_Home_Resource {
    
  	private $service;

    private $client;

    private $methods;

    function __construct( $service, $args ){
      $this->service = $service;
      $this->methods = isset($args['methods']) ? $args['methods'] : null;
    }

  }

endif;

if( ! class_exists( 'Doppler_Service_Lists_Resource' ) ) :

  class Doppler_Service_Lists_Resource {

    private $service;

    private $client;

    private $methods;

    function __construct( $service, $args ) {
      $this->service = $service;
      $this->methods = isset($args['methods']) ? $args['methods'] : null;
    }

    public function getList( $listId ) {
  
      $method = $this->methods['get'];
      return json_decode($this->service->call($method, array("listId" => $listId))['body']);
    
    }

    /**
     * Get all lists recursively
     */
    public function getAllLists( $listId = null, $lists = [], $page = 1, $per_page = 200 ) {
      $method = $this->methods['list'];
      $z = json_decode($this->service->call($method, array("listId" => $listId, 'page' => $page, 'per_page' => $per_page))['body']);
      if(!isset($z->items)) return $lists; 
      $lists[] = $z->items;
      if($z->currentPage < $z->pagesCount && $page<1){
        $page = $page+1;
        return $this->getAllLists(null, $lists, $page);
      }else{
        return $lists;
      }
      
    }

    public function getListsByPage( $page = 1, $per_page = 200 ) {
      $method = $this->methods['list'];
      return json_decode($this->service->call($method, array("listId" => null, 'page' => $page, 'per_page' => $per_page))['body']);
    }

    public function saveList( $list_name ) {
      
      if(!empty($list_name)){
        $method = $this->methods['new'];
        return $this->service->call( $method, null, array('name'=>$list_name)  );
      }
    
    }

    public function deleteList($list_id) {
      
      if(!empty($list_id)){
        $method = $this->methods['delete'];
        return $this->service->call( $method, array('listId'=>$list_id) );
      }
    
    }
    
  }

endif;


if( ! class_exists( 'Doppler_Service_Fields' ) ) :

  class Doppler_Service_Fields {

    private $service;

    private $client;

    private $methods;

    function __construct( $service, $args )
    {
      $this->service = $service;
      $this->methods = isset($args['methods']) ? $args['methods'] : null;
    }

    public function getAllFields( $listId = null ){
      $method = $this->methods['list'];
      return json_decode($this->service->call($method, array("listId" => $listId) )['body']);
    }

  }

endif;

if( ! class_exists( 'Doppler_Service_Subscribers' ) ) :

  class Doppler_Service_Subscribers {

    private $service;

    private $client;

    private $methods;

    function __construct( $service, $args )
    {
      $this->service = $service;
      $this->methods = isset($args['methods']) ? $args['methods'] : null;
    }

    public function addSubscriber( $listId, $subscriber ){
      $method = $this->methods['post'];
      return $this->service->call( $method, array( 'listId' => $listId ),  $subscriber );
    }

    public function importSubscribers($listId, $subscribers){
      $method = $this->methods['import'];
      return $this->service->call( $method, array( 'listId' => $listId ), $subscribers);
    }

    public function getSubscribers( $listId, $page = 1 ) {
      
      /*
      $method = $this->methods['list'];
      $z = json_decode($this->service->call($method, array("listId" => $listId, 'page' => $page))['body']);
      $lists[] = $z->items;

      if($z->currentPage < $z->pagesCount && $page<4){
        $page = $page+1;
        return $this->getAllLists(null, $lists, $page);
      }else{
        return $lists;
      }*/

      $method = $this->methods['get'];
      return $this->service->call( $method, array( 'listId' => $listId ) );
    }

  }

endif;

if( ! class_exists( 'Doppler_Exception_Invalid_Account' ) ){
  class Doppler_Exception_Invalid_Account extends Exception {};
}

if( ! class_exists( 'Doppler_Exception_Invalid_APIKey' ) ){
  class Doppler_Exception_Invalid_APIKey extends Exception {};
}

?>