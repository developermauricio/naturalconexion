<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class hsas_cls_dbquery {

	public static function hsas_content_view($guid = "", $offset = 0, $limit = 0) {

		global $wpdb;

		$arrRes = array();

		$sSql = "SELECT * FROM `".$wpdb->prefix."horizontal_scrolling_hsas` ";

		if($guid <> "") {
			$sSql = $sSql . " WHERE hsas_guid='".$guid."'";
		}
		
		$sSql = $sSql . " order by hsas_id desc";
		$sSql = $sSql . " LIMIT $offset, $limit";
		
		$arrRes = $wpdb->get_results($sSql, ARRAY_A);

		return $arrRes;
	}
	
	public static function hsas_content_display($group = "") {

		global $wpdb;

		$arrRes = array();

		$currentdate = date('Y-m-d');
		$sSql = "SELECT * FROM `".$wpdb->prefix."horizontal_scrolling_hsas` WHERE (hsas_datestart <= '". $currentdate ."' AND hsas_dateend >= '".$currentdate."')";

		if($group <> "") {
			$sSql = $sSql . " AND hsas_group='".$group."'";
		}
		
		$sSql = $sSql . " order by hsas_order";
		
		$arrRes = $wpdb->get_results($sSql, ARRAY_A);

		return $arrRes;
	}
	
	public static function hsas_content_group() {

		global $wpdb;

		$arrRes = array();

		$sSql = "SELECT distinct(hsas_group) FROM `".$wpdb->prefix."horizontal_scrolling_hsas` ";
		
		$sSql = $sSql . " order by hsas_group";
		
		$arrRes = $wpdb->get_results($sSql, ARRAY_A);

		return $arrRes;
	}

	public static function hsas_content_delete($guid = "") {

		global $wpdb;

		if($guid <> "") {
			$sSql = $wpdb->prepare("DELETE FROM `".$wpdb->prefix."horizontal_scrolling_hsas` WHERE `hsas_guid` = %s LIMIT 1", $guid);
			$wpdb->query($sSql);
		}
		
		return true;
	}

	public static function hsas_content_action($data = array(), $action = "insert") {

		global $wpdb;
		
		$hsas_text 		= wp_filter_post_kses($data["hsas_text"]);
		$hsas_link 		= sanitize_text_field($data["hsas_link"]);
		$hsas_target 	= sanitize_text_field($data["hsas_target"]);
		$hsas_order 	= intval($data["hsas_order"]);
		$hsas_group 	= sanitize_text_field($data["hsas_group"]);
		$hsas_datestart = sanitize_text_field($data["hsas_datestart"]);
		$hsas_timestart = 0;
		$hsas_dateend 	= sanitize_text_field($data["hsas_dateend"]);
		$hsas_timeend 	= 0;
		$hsas_css 		= sanitize_text_field($data["hsas_css"]);
		$hsas_guid 		= sanitize_text_field($data["hsas_guid"]);

		if($action == "insert") {
				$guid = hsas_cls_common::hsas_generate_guid(60);
				$sql = $wpdb->prepare("INSERT INTO `".$wpdb->prefix."horizontal_scrolling_hsas`
						(`hsas_guid`, `hsas_text`, `hsas_link`, `hsas_target`, `hsas_order`, `hsas_group`, 
						`hsas_datestart`, `hsas_timestart`, `hsas_dateend`, `hsas_timeend`, `hsas_css`)
						VALUES(%s, %s, %s, %s, %d, %s, %s, %d, %s, %d, %s)", 
						array($guid, $hsas_text, $hsas_link, $hsas_target, $hsas_order, $hsas_group, 
						$hsas_datestart, $hsas_timestart, $hsas_dateend, $hsas_timeend, trim($hsas_css)));
				$wpdb->query($sql);
				return "sus";
		} elseif($action == "update") {
				$sSql = $wpdb->prepare("UPDATE `".$wpdb->prefix."horizontal_scrolling_hsas` SET `hsas_text` = %s, `hsas_link` = %s, `hsas_target` = %s, 
				`hsas_order` = %d, `hsas_group` = %s, `hsas_datestart` = %s, `hsas_timestart` = %d, `hsas_dateend` = %s, `hsas_timeend` = %d, `hsas_css` = %s 
				WHERE hsas_guid = %s LIMIT 1", 
				array($hsas_text, $hsas_link, $hsas_target, $hsas_order, $hsas_group, $hsas_datestart, 
				$hsas_timestart, $hsas_dateend, $hsas_timeend, $hsas_css, $hsas_guid));
				$wpdb->query($sSql);
				return "sus";
		}
	}
	
	public static function hsas_content_count( $guid = "" ) {

		global $wpdb;

		$result = '0';

		if($guid <> "") {
			$sSql = $wpdb->prepare("SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."horizontal_scrolling_hsas` WHERE `hsas_guid` = %s", array($guid));
		} else {
			$sSql = "SELECT COUNT(*) AS `count` FROM `".$wpdb->prefix."horizontal_scrolling_hsas` ";
		}

		$result = $wpdb->get_var( $sSql );

		return $result;
	}

	public static function hsas_content_default() {
		$guid = "";
		$count = hsas_cls_dbquery::hsas_content_count($guid);
		if($count == 0){
			$form['hsas_text'] 		= "This is sample text for demo. Check plugin demo page for more info.";
			$form['hsas_link']		= "";
			$form['hsas_target'] 	= "_blank";
			$form['hsas_order'] 	= 0;
			$form['hsas_group'] 	= "Default";
			$form['hsas_datestart'] = date('Y-m-d');
			$form['hsas_timestart'] = 0;
			$form['hsas_dateend'] 	= "9999-12-31";
			$form['hsas_timeend'] 	= 0;
			$form['hsas_css'] 		= "";
			$form['hsas_guid'] 		= "";
			hsas_cls_dbquery::hsas_content_action($form, "insert");
		}
	}
}