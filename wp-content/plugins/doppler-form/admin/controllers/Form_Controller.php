<?php

class DPLR_Form_Controller
{
  
  private $doppler_service;

  function __construct($doppler_service)
  {
    $this->doppler_service = $doppler_service;
  }

  function comparator($object1, $object2) {
      return $object1->name > $object2->name;
  }

  function create( $form = null ) {

    if (isset($form) && count($form) > 0) {
      
      $result_code = 0;

      // saco de $form la config necesaria para hacer el POST.
      if($form["settings"]["form_doble_optin"] === "yes") {
        $form_data["fromName"] = $form["settings"]["form_email_confirmacion_nombre_remitente"];
        $form_data["fromEmail"] = $form["settings"]["form_email_confirmacion_email_remitente"];
        $form_data["subject"] = $form["settings"]["form_email_confirmacion_asunto"];
        $form_data["preheader"] = $form["settings"]["form_email_confirmacion_pre_encabezado"];
        if($form["settings"]["form_pagina_confirmacion"] == 'url'){
          $form_data["urlLanding"] = $form["settings"]["form_pagina_confirmacion_url"];
        }
        else{
          $landing_url = get_page($form["settings"]["form_pagina_confirmacion_select_landing"])->guid;
          $form_data["urlLanding"] = $landing_url;
        }

        if(isset($form["settings"]["form_email_reply_to"]) && !empty($form["settings"]["form_email_reply_to"])) {
          $form_data["replyTo"] = $form["settings"]["form_email_reply_to"];
        }
        $form_data["name"] = $form["settings"]["form_name"];

        $form["content"] = str_replace('href=\"[[[ConfirmationLink]]]\"', "href=[[[ConfirmationLink]]]", $form["content"]);
        $form["content"] = str_replace('href="[[[ConfirmationLink]]]"', "href=[[[ConfirmationLink]]]", $form["content"]);
        $form["content"] = str_replace('href="http://[[[ConfirmationLink]]]"', "href=[[[ConfirmationLink]]]", $form["content"]);
        $form["content"] = str_replace('href="\"http://[[[ConfirmationLink]]]\""', "href=[[[ConfirmationLink]]]", $form["content"]);
        $form["content"] = str_replace('href=\"http://[[[ConfirmationLink]]]\"', "href=[[[ConfirmationLink]]]", $form["content"]);

        $method["route"] = "DobleOptinTemplate";
        $method["httpMethod"] = "post";
        // aca creo la plantilla y la asocio a este form_id.
        // plugins/doppler-form/includes/DopplerAPIClient/DopplerService.php
        $response = $this->doppler_service->call($method, '', $form_data);

        $this->doppler_service->pluginLogger(array("form" => $form_data, "response" => $response), 'form_create');

        if($response["response"]["code"] === 201) {
          $body = json_decode($response["body"]);
          $form_to_update["settings"]["form_plantilla_id"] = $body->createdResourceId;
          $form["settings"]["form_plantilla_id"] = $body->createdResourceId;

          // // hay que hacer otra llamada a la API, con el endpoint /plantilla_id/content.
          // // para poder setear el contenido html del template.
          $method["route"] = "DobleOptinTemplate/" . $form_to_update["settings"]["form_plantilla_id"] . '/content';
          $method["httpMethod"] = "put";
          unset($form_data);
          $form_data = $form["content"];

          $response2 = $this->doppler_service->call($method, '', $form_data);
        } else {
          $body = json_decode($response["body"]);
          $result_code = $body->errorCode;
        }
      } else {
        unset($form["settings"]["form_email_confirmacion_nombre_remitente"]);
        unset($form["settings"]["form_email_confirmacion_email_remitente"]);
        unset($form["settings"]["form_email_confirmacion_asunto"]);
        unset($form["settings"]["form_email_confirmacion_pre_encabezado"]);
        unset($form["settings"]["form_email_reply_to"]);
        unset($form["settings"]["form_name"]);
        unset($form["settings"]["form_pagina_confirmacion"]);
        unset($form["settings"]["form_pagina_confirmacion_select_landing"]);
        unset($form["settings"]["form_pagina_confirmacion_url"]);
      }

      if($result_code == 0) {

        DPLR_Form_Model::insert(['name'=>$form['name'], 'title' => $form['title'], 'list_id' => $form['list_id']]);
        $form_id =  DPLR_Form_Model::insert_id();

        $form["settings"]["form_email_confirmacion_email_contenido"] = $form["content"]; 
        DPLR_Form_Model::setSettings($form_id, $form["settings"]);

        $field_position_counter = 1;

        $form['fields'] = isset($form['fields']) ? $form['fields'] : [];

        foreach ($form['fields'] as $key => $value) {

          $mod = ['name' => $key, 'type' => $value['type'], 'form_id' => $form_id, 'sort_order' => $field_position_counter++];
          DPLR_Field_Model::insert($mod);

          $field_id =  DPLR_Field_Model::insert_id();
          $field_settings = $value['settings'];

          DPLR_Field_Model::setSettings($field_id, $field_settings);
        }

      }

      return $result_code;

    } 
  
  }

  function update($form_id, $form_to_update = NULL) {
    if (isset($form_to_update) && count($form_to_update) > 0) {
      
      $result_code = 0;

      if($form_to_update["settings"]["form_doble_optin"] === "yes"){

        $form = DPLR_Form_Model::get($form_id, true);
        if(!isset($form_to_update["settings"]["form_email_confirmacion_nombre_remitente"]))
          $form_to_update["settings"]["form_email_confirmacion_nombre_remitente"] = $form->settings["form_email_confirmacion_nombre_remitente"];
        if(!isset($form_to_update["settings"]["form_email_confirmacion_email_remitente"]))
          $form_to_update["settings"]["form_email_confirmacion_email_remitente"] = $form->settings["form_email_confirmacion_email_remitente"];
        if(!isset($form_to_update["settings"]["form_email_confirmacion_asunto"]))
          $form_to_update["settings"]["form_email_confirmacion_asunto"] = $form->settings["form_email_confirmacion_asunto"];
        if(!isset($form_to_update["settings"]["form_email_confirmacion_pre_encabezado"]))
          $form_to_update["settings"]["form_email_confirmacion_pre_encabezado"] = $form->settings["form_email_confirmacion_pre_encabezado"];
        if(!isset($form_to_update["settings"]["form_email_reply_to"]))
          $form_to_update["settings"]["form_email_reply_to"] = $form->settings["form_email_reply_to"];
        if(!isset($form_to_update["settings"]["form_name"]))
          $form_to_update["settings"]["form_name"] = $form->settings["form_name"];

        $form_data["fromName"] = $form_to_update["settings"]["form_email_confirmacion_nombre_remitente"];
        $form_data["fromEmail"] = $form_to_update["settings"]["form_email_confirmacion_email_remitente"];
        $form_data["subject"] = $form_to_update["settings"]["form_email_confirmacion_asunto"];
        $form_data["preheader"] = $form_to_update["settings"]["form_email_confirmacion_pre_encabezado"];
        if(isset($form_to_update["settings"]["form_email_reply_to"]) && !empty($form_to_update["settings"]["form_email_reply_to"])) {
          $form_data["replyTo"] = $form_to_update["settings"]["form_email_reply_to"];
        }

        $form_data["name"] = $form_to_update["settings"]["form_name"];

        if($form_to_update["settings"]["form_pagina_confirmacion"] == 'url'){
          $form_data["urlLanding"] = $form_to_update["settings"]["form_pagina_confirmacion_url"];
        }
        else{
          $landing_url = get_page($form_to_update["settings"]["form_pagina_confirmacion_select_landing"])->guid;
          $form_data["urlLanding"] = $landing_url;
        }

        $method["route"] = "DobleOptinTemplate";
        $method["httpMethod"] = "post";
        // aca creo la plantilla y la asocio a este form_id.
        // plugins/doppler-form/includes/DopplerAPIClient/DopplerService.php
        $response = $this->doppler_service->call($method, '', $form_data);

        $this->doppler_service->pluginLogger(array("form" => $form_data, "response" => $response), 'form_update');

        $form_to_update["content"] = str_replace('href=\"[[[ConfirmationLink]]]\"', "href=[[[ConfirmationLink]]]", $form_to_update["content"]);
        $form_to_update["content"] = str_replace('href="[[[ConfirmationLink]]]"', "href=[[[ConfirmationLink]]]", $form_to_update["content"]);
        $form_to_update["content"] = str_replace('href="http://[[[ConfirmationLink]]]"', "href=[[[ConfirmationLink]]]", $form_to_update["content"]);
        $form_to_update["content"] = str_replace('href="\"http://[[[ConfirmationLink]]]\""', "href=[[[ConfirmationLink]]]", $form_to_update["content"]);
        $form_to_update["content"] = str_replace('href=\"http://[[[ConfirmationLink]]]\"', "href=[[[ConfirmationLink]]]", $form_to_update["content"]);

        $form_data = $form_to_update["content"];
        $form_to_update["settings"]["form_email_confirmacion_email_contenido"] = $form_data; 

        if($response["response"]["code"] === 201){
          $body = json_decode($response["body"]);
          $form_to_update["settings"]["form_plantilla_id"] = $body->createdResourceId;

          // hay que hacer otra llamada a la API, con el endpoint /plantilla_id/content.
          // para poder setear el contenido html del template.
          $method["route"] = "DobleOptinTemplate/" . $form_to_update["settings"]["form_plantilla_id"] . '/content';
          $method["httpMethod"] = "put";
          unset($form_data);
          // $form_data = $form_to_update["settings"]["form_email_confirmacion_email_contenido"];
          $form_data = $form_to_update["content"];
          $form_to_update["settings"]["form_email_confirmacion_email_contenido"] = $form_data; 
         

          $response2 = $this->doppler_service->call($method, '', $form_data);
        } else {
          $body = json_decode($response["body"]);
          $result_code = $body->errorCode;
        }
      } else {
        unset($form_to_update["settings"]["form_email_confirmacion_nombre_remitente"]);
        unset($form_to_update["settings"]["form_email_confirmacion_email_remitente"]);
        unset($form_to_update["settings"]["form_email_confirmacion_asunto"]);
        unset($form_to_update["settings"]["form_email_confirmacion_pre_encabezado"]);
        unset($form_to_update["settings"]["form_email_reply_to"]);
        unset($form_to_update["settings"]["form_name"]);
        unset($form_to_update["settings"]["form_pagina_confirmacion"]);
        unset($form_to_update["settings"]["form_pagina_confirmacion_select_landing"]);
        unset($form_to_update["settings"]["form_pagina_confirmacion_url"]);
      }

      if($result_code == 0) {

      	  DPLR_Form_Model::update($form_id, ['name'=>$form_to_update['name'], 'title' => $form_to_update['title'], 'list_id' => $form_to_update['list_id'] ]);

	      DPLR_Form_Model::setSettings($form_id, $form_to_update["settings"]);

	      $field_position_counter = 1;

	      $form_to_update['fields'] = isset($form_to_update['fields']) ? $form_to_update['fields'] : [];

	      DPLR_Field_Model::deleteWhere(['form_id' => $form_id]);

	      foreach ($form_to_update['fields'] as $key => $value) {
	        
	        $mod = ['name' => $key, 'type' => $value['type'], 'form_id' => $form_id, 'sort_order' => $field_position_counter++];

	        DPLR_Field_Model::insert($mod);

	        $field_id =  DPLR_Field_Model::insert_id();

	        $field_settings = $value['settings'];

	        $res = DPLR_Field_Model::setSettings($field_id, $field_settings);

	      }
	  }

      return $result_code;    
    }
  }

  function getAll() {
    
    return DPLR_Form_Model::getAll(false, array('id'));
    
  }

  function delete($id) {
    
    return DPLR_Form_Model::delete($id);
    
  }

  public function showCreateEditForm($form_id = NULL) {
    
    $options = get_option('dplr_settings');
    $list_resource = $this->doppler_service->getResource('lists');
    $fields_resource = $this->doppler_service->getResource('fields');

    $dplr_lists = $list_resource->getAllLists();
    foreach($dplr_lists as $k=>$v){
      foreach($v as $i=>$j){
        $dplr_lists_aux[] = $j;
      }
    }
    
    $dplr_lists = $dplr_lists_aux;
    $dplr_fields = $fields_resource->getAllFields();
    $dplr_fields = isset($dplr_fields->items) ? $dplr_fields->items : [];
    usort($dplr_fields, function($a, $b) {
      return strtolower($a->name) > strtolower($b->name);
    });

    if ($form_id != NULL) {
      $form = DPLR_Form_Model::get($form_id, true);

      if($_POST) {
      	if($form->settings["form_doble_optin"] == 'no' && $_POST['settings']['form_doble_optin'] == 'yes') {
      		$form_doble_optin_enabled = true;
      	}

      	$form->name = $_POST['name'];
      	$form->list_id = $_POST['list_id'];

      	//get form fields
		$field_position = 1;
		$form_fields = isset($_POST['fields']) ? $_POST['fields'] : [];
		$fields = [];
		foreach ($form_fields as $k => $v) {
			array_push($fields, ['name' => $k, 'type' => $v['type'], 'form_id' => 0, 'sort_order' => $field_position++, 'settings' => $v['settings']]);
		}

      	$form->title = $_POST['title'];
      	$form->settings["button_text"] = isset($_POST['settings']['button_text']) ? $_POST['settings']['button_text'] : '';
      	$form->settings["button_position"] = isset($_POST['settings']['button_position']) ? $_POST['settings']['button_position'] : '';
      	$form->settings["change_button_bg"] = isset($_POST['settings']['change_button_bg']) ? $_POST['settings']['change_button_bg'] : '';
      	$form->settings["button_color"] = isset($_POST['settings']['button_color']) ? $_POST['settings']['button_color'] : '';

      	$form->settings["use_thankyou_page"] = isset($_POST['settings']['use_thankyou_page']) ? $_POST['settings']['use_thankyou_page'] : '';
      	$form->settings["thankyou_page_url"] = isset($_POST['settings']['thankyou_page_url']) ? $_POST['settings']['thankyou_page_url'] : '';
      	$form->settings["message_success"] = isset($_POST['settings']['message_success']) ? $_POST['settings']['message_success'] : '';

      	$form->settings["use_consent_field"] = isset($_POST['settings']['use_consent_field']) ? $_POST['settings']['use_consent_field'] : '';
      	$form->settings["form_orientation"] = isset($_POST['settings']['form_orientation']) ? $_POST['settings']['form_orientation'] : '';

      	$form->settings["consent_field_text"] = isset($_POST['settings']['consent_field_text']) ? $_POST['settings']['consent_field_text'] : '';
      	$form->settings["consent_field_url"] = isset($_POST['settings']['consent_field_url']) ? $_POST['settings']['consent_field_url'] : '';

      	$form->settings["form_doble_optin"] = isset($_POST['settings']['form_doble_optin']) ? $_POST['settings']['form_doble_optin'] : '';

      	$form->settings["form_email_confirmacion_asunto"] = isset($_POST['settings']['form_email_confirmacion_asunto']) ? $_POST['settings']['form_email_confirmacion_asunto'] : '';
      	$form->settings["form_email_confirmacion_pre_encabezado"] = isset($_POST['settings']['form_email_confirmacion_pre_encabezado']) ? $_POST['settings']['form_email_confirmacion_pre_encabezado'] : '';
      	$form->settings["form_email_confirmacion_email_remitente"] = isset($_POST['settings']['form_email_confirmacion_email_remitente']) ? $_POST['settings']['form_email_confirmacion_email_remitente'] : '';
      	$form->settings["form_email_confirmacion_nombre_remitente"] = isset($_POST['settings']['form_email_confirmacion_nombre_remitente']) ? $_POST['settings']['form_email_confirmacion_nombre_remitente'] : '';
      	$form->settings["form_name"] = isset($_POST['settings']['form_name']) ? $_POST['settings']['form_name'] : '';
      	$form->settings["form_email_reply_to"] = isset($_POST['settings']['form_email_reply_to']) ? $_POST['settings']['form_email_reply_to'] : '';

      	$form->settings["form_email_confirmacion_email_contenido"] = isset($_POST['content']) ? $_POST['content'] : '';

      	$form->settings["form_pagina_confirmacion"] = isset($_POST['settings']['form_pagina_confirmacion']) ? $_POST['settings']['form_pagina_confirmacion'] : '';
      	$form->settings["form_pagina_confirmacion_select_landing"] = isset($_POST['settings']['form_pagina_confirmacion_select_landing']) ? $_POST['settings']['form_pagina_confirmacion_select_landing'] : '';
      	$form->settings["form_pagina_confirmacion_url"] = isset($_POST['settings']['form_pagina_confirmacion_url']) ? $_POST['settings']['form_pagina_confirmacion_url'] : '';
      } else {
    	$fields = DPLR_Field_Model::getBy(['form_id' => $form_id],['sort_order'], true);
	  }
      
      include plugin_dir_path( __FILE__ ) . "../partials/forms-edit.php";
    } else {
      $form = $_POST;

      //get form fields
      $field_position_counter = 1;
      $form['fields'] = isset($form['fields']) ? $form['fields'] : [];
      $fields = [];
      foreach ($form['fields'] as $key => $value) {
        array_push($fields, ['name' => $key, 'type' => $value['type'], 'form_id' => 0, 'sort_order' => $field_position_counter++, 'settings' => $value['settings']]);
      }

      include plugin_dir_path( __FILE__ ) . "../partials/forms-create.php";
    }

  }

}

?>