<?php

require_once("./../../../../wp-load.php");

// Datos de su cuenta:
$email_account = "ffernandez@makingsense.com";
$id_list = "28466729";
$API_KEY = "2091E0F289A66BF45C81DB1B578C3FFF";

// Datos de mi cuenta - roman
// $email_account = "theoskoh@hotmail.com";
// $id_list = "27839966";
// $API_KEY = "DFD629C6393B531C0C54F874BE70496B";



$url = 'https://restapi.fromdoppler.com/accounts/'. $email_account . '/lists' . '/' . $id_list . '/subscribers';

$headers=array(
    "Accept" => "application/json",
    "Content-Type" => "application/json",
    "X-Doppler-Subscriber-Origin" => "WooCommerce",
    "Authorization" => "token " . $API_KEY 
     );
     
$response = "";

$method['httpMethod'] = 'post';

/// $log = 'a:2:{s:5:"items";a:1:{i:0;a:2:{s:5:"email";s:19:"mapeo11@hotmail.com";s:6:"fields";a:3:{i:0;a:2:{s:4:"name";s:9:"FIRSTNAME";s:5:"value";s:5:"mapea";}i:1;a:2:{s:4:"name";s:8:"LASTNAME";s:5:"value";s:7:"mapea11";}i:2;a:2:{s:4:"name";s:13:"campo_perso_1";s:5:"value";s:17:"empresa empresita";}}}}s:6:"fields";a:0:{}}a:2:{s:5:"items";a:1:{i:0;a:2:{s:5:"email";s:19:"mapeo13@hotmail.com";s:6:"fields";a:3:{i:0;a:2:{s:4:"name";s:9:"FIRSTNAME";s:5:"value";s:5:"mapea";}i:1;a:2:{s:4:"name";s:8:"LASTNAME";s:5:"value";s:7:"mapea13";}i:2;a:2:{s:4:"name";s:13:"campo_perso_1";s:5:"value";s:17:"empresa empresita";}}}}s:6:"fields";a:0:{}}';

// $body = unserialize($log);

$body = [
    "email" => "error-ffernadez003@hotmail.com",
    "fields" => [
        "0" => [
            "name" => "FIRSTNAME",
            "value" => "error"
        ],
        "1" => [
            "name" => "LASTNAME",
            "value" => "ffernandez"
        ]
        // "2" => [
        //     "name" => "campo_perso_1",
        //     "value" => "a custom-field"
        // ]
    ]
];

$query = array();

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
            'timeout' => 20,
            'body'=> json_encode($body)
          ));
          break;
        }
}

catch(\Exception $e){
    $this->throwConnectionErr($e->getMessage());
    return;
}

$response_body = json_decode($response['body']);

echo "<pre>";
print_r($response);
echo "</pre>";

echo "<pre>";
print_r($response_body);
echo "</pre>";
