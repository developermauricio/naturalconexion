<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class mic_cls_dbquery {

	public static function mic_count($id = 0) {

		global $wpdb;
		$result = '0';
		
		if($id <> "" && $id > 0) {
			$sSql = $wpdb->prepare("SELECT COUNT(*) AS count FROM " . $wpdb->prefix . "marquee_img_crawler WHERE mic_id = %d", array($id));
		} 
		else {
			$sSql = "SELECT COUNT(*) AS count FROM " . $wpdb->prefix . "marquee_img_crawler";
		}
		
		$result = $wpdb->get_var($sSql);
		return $result;
	}
	
	public static function mic_select_bygroup($group = "") {

		global $wpdb;
		$arrRes = array();
		$sSql = "SELECT * FROM " . $wpdb->prefix . "marquee_img_crawler";

		if($group <> "") {
			$sSql = $sSql . " WHERE mic_group = %s order by mic_id desc";
			$sSql = $wpdb->prepare($sSql, array($group));
		}
		else {
			$sSql = $sSql . " order by mic_group, mic_id desc";
		}

		$arrRes = $wpdb->get_results($sSql, ARRAY_A);
		return $arrRes;
	}
	
	public static function mic_select_byid($id = "") {

		global $wpdb;
		$arrRes = array();
		$sSql = "SELECT * FROM " . $wpdb->prefix . "marquee_img_crawler";

		if($id <> "") {
			$sSql = $sSql . " WHERE mic_id = %d LIMIT 1";
			$sSql = $wpdb->prepare($sSql, array($id));
			$arrRes = $wpdb->get_row($sSql, ARRAY_A);
		}
		else {
			$sSql = $sSql . " order by mic_group, mic_order";
			$arrRes = $wpdb->get_results($sSql, ARRAY_A);
		}
		
		return $arrRes;
	}
	
	public static function mic_select_bygroup_rand($group = "") {

		global $wpdb;
		$arrRes = array();
		$sSql = "SELECT * FROM " . $wpdb->prefix . "marquee_img_crawler";

		if($group <> "") {
			$sSql = $sSql . " WHERE mic_group = %s order by rand() LIMIT 100";
			$sSql = $wpdb->prepare($sSql, array($group));
		}
		else {
			$sSql = $sSql . " order by rand() LIMIT 100";
		}

		$arrRes = $wpdb->get_results($sSql, ARRAY_A);
		return $arrRes;
	}
	
	public static function mic_group() {

		global $wpdb;
		$arrRes = array();
		$sSql = "SELECT distinct(mic_group) FROM " . $wpdb->prefix . "marquee_img_crawler order by mic_group";
		$arrRes = $wpdb->get_results($sSql, ARRAY_A);
		return $arrRes;
	}

	public static function mic_delete($id = "") {

		global $wpdb;

		if($id <> "") {
			$sSql = $wpdb->prepare("DELETE FROM " . $wpdb->prefix . "marquee_img_crawler WHERE mic_id = %s LIMIT 1", $id);
			$wpdb->query($sSql);
		}
		
		return true;
	}

	public static function mic_action_ins($data = array(), $action = "insert") {

		global $wpdb;
		
		if($action == "insert") {
			$sql = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "marquee_img_crawler
				(mic_image, mic_link, mic_title, mic_width, mic_group, mic_status) VALUES (%s, %s, %s, %d, %s, %s)", 
				array($data["mic_image"], $data["mic_link"], $data["mic_title"], $data["mic_width"], $data["mic_group"], $data["mic_status"]));
			$wpdb->query($sql);
			return "inserted";
		}
		elseif($action == "update") {
			$sSql = $wpdb->prepare("UPDATE " . $wpdb->prefix . "marquee_img_crawler SET mic_image = %s, mic_link = %s, mic_title = %s, 
				mic_width = %d, mic_group = %s, mic_status = %s WHERE mic_id = %d LIMIT 1", 
				array($data["mic_image"], $data["mic_link"], $data["mic_title"], $data["mic_width"], $data["mic_group"], $data["mic_status"], $data["mic_id"]));
			$wpdb->query($sSql);
			return "update";
		}
	}
	


	public static function mic_default() {

		$count = mic_cls_dbquery::mic_count($id = 0);
		if($count == 0){
			$folderpath = plugin_dir_url( __DIR__ );
			if (mic_cls_dbquery::endswith($folderpath, '/') == false) {
				$folderpath = $folderpath . "/";
			}
			
			$sing_1 = $folderpath . 'sample/sing_1.jpg';
			$sing_2 = $folderpath . 'sample/sing_2.jpg';
			$sing_3 = $folderpath . 'sample/sing_3.jpg';
			$sing_4 = $folderpath . 'sample/sing_4.jpg';
			
			$data['mic_image'] = $sing_1;
			$data['mic_link'] = $sing_1;
			$data['mic_title'] = 'Sample Image';
			$data['mic_width'] = '0';
			$data['mic_group'] = 'Group1';
			$data['mic_status'] = 'Yes';
			mic_cls_dbquery::mic_action_ins($data, "insert");
			
			$data['mic_image'] = $sing_2;
			$data['mic_link'] = $sing_2;
			mic_cls_dbquery::mic_action_ins($data, "insert");
			
			$data['mic_image'] = $sing_3;
			$data['mic_link'] = $sing_3;
			mic_cls_dbquery::mic_action_ins($data, "insert");
			
			$data['mic_image'] = $sing_4;
			$data['mic_link'] = $sing_4;
			mic_cls_dbquery::mic_action_ins($data, "insert");

		}
	}
	
	public static function mic_common_text($value) {
		
		$returnstring = "";
		switch ($value) 
		{
			case "Yes":
				$returnstring = '<span style="color:#006600;">Yes</span>';
				break;
			case "No":
				$returnstring = '<span style="color:#FF0000;">No</span>';
				break;
			case "_blank":
				$returnstring = '<span style="color:#006600;">New window</span>';
				break;
			case "_self":
				$returnstring = '<span style="color:#0000FF;">Same window</span>';
				break;
			default:
       			$returnstring = $value;
		}
		return $returnstring;
	}
	
	public static function endswith($fullstr, $needle)
    {
        $strlen = strlen($needle);
        $fullstrend = substr($fullstr, strlen($fullstr) - $strlen);
        return $fullstrend == $needle;
    }
}