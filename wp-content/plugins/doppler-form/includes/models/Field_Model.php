<?php


final class DPLR_Field_Model extends DPLR_Base_Model {

  static $primary_key = 'id';

  static function init() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tablemame = self::_table();

    if($wpdb->get_var("SHOW TABLES LIKE '$tablemame'") != $tablemame) {
      $sql = "CREATE TABLE ". $tablemame . "("
  		 . "id mediumint(9) NOT NULL AUTO_INCREMENT,"
       . "name tinytext NOT NULL,"
       . "form_id mediumint(9) NOT NULL,"
       . "type tinytext DEFAULT NULL,"
       . "sort_order int DEFAULT 1,"
  		 . "PRIMARY KEY (id),"
       . "FOREIGN KEY (form_id) REFERENCES " . DPLR_Form_Model::_table() . "(id) ON DELETE CASCADE"
  		. ") $charset_collate;";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
    }

    self::initSettings();
  }
}

 ?>
