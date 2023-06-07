<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class hsas_cls_intermediate {
	public static function hsas_announcements() {
		global $wpdb;
		$current_page = isset($_GET['ac']) ? $_GET['ac'] : '';
		switch($current_page) {
			case 'add':
				require_once(HSAS_DIR.'content'.DIRECTORY_SEPARATOR.'content-add.php');
				break;
			case 'edit':
				require_once(HSAS_DIR.'content'.DIRECTORY_SEPARATOR.'content-edit.php');
				break;
			case 'help':
				require_once(HSAS_DIR.'content'.DIRECTORY_SEPARATOR.'content-help.php');
				break;
			default:
				require_once(HSAS_DIR.'content'.DIRECTORY_SEPARATOR.'content-show.php');
				break;
		}
	}

}

class hsas_cls_common {
	public static function hsas_generate_guid($length = 30) {
		$guid = rand();
		$length = 6;
		$rand1 = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, $length);
		$rand2 = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, $length);
		$rand3 = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, $length);
		$rand4 = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, $length);
		$rand5 = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, $length);
		$rand6 = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, $length);
		$guid = $rand1."-".$rand2."-".$rand3."-".$rand4."-".$rand5;
		return $guid;
	}
	
	public static function hsas_special_letters() {
		$string = "/[\'^$%&*()}{@#~?><>,|=_+\"]/";
		return $string;
	}
}

class hsas_cls_security {
	public static function hsas_check_number($value) {
		if(!is_numeric($value)) { 
			die('<p>Security check failed. Are you sure you want to do this?</p>'); 
		}
	}

	public static function hsas_check_guid($value) {
		$value_length1 = strlen($value);
		$value_noslash = str_replace("-", "", $value);
		$value_length2 = strlen($value_noslash);

		if( $value_length1 != 34 || $value_length2 != 30) {
			die('<p>Security check failed. Are you sure you want to do this?</p>'); 
		}

		if (preg_match('/[^a-z]/', $value_noslash)) {
			die('<p>Security check failed. Are you sure you want to do this?</p>'); 
		}
	}
}