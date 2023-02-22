<?php
//http://example.com/wp-content/plugins/doppler-form/test/test-curl.php
$APITOKEN = "";

function wpCurl(){
    global $APITOKEN;
    require_once("./../../../../../wp-load.php");

    $headers =array(
        "Accept" => "application/json",
        "Content-Type" => "application/json",
        "X-Doppler-Subscriber-Origin" => "Wordpress",
        "Authorization" => "token " . $APITOKEN
         );
    
    $response = "";
    $method['httpMethod'] = 'get';
    
    $url = "https://restapi.fromdoppler.com";
    
    try{
        switch($method['httpMethod']){
        case 'get':
            $response = wp_remote_get( $url, array( 'headers' => $headers, 'timeout' => 0 ) );
            break;
        }
    }
    catch(\Exception $e){
        return "ERROR catched -> " . $e->getMessage();;
    }
    
    return $response ;

}

function vanillaCurl(){
    global $APITOKEN;
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://restapi.fromdoppler.com',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 20,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: token '. $APITOKEN,
        'X-Doppler-Subscriber-Origin: Wordpress',
        'Content-Type: application/json'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);

    return $response;
}

//print_r(wpCurl());

print_r(vanillaCurl());