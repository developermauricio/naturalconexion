<?php

class DPLR_Base_Model {

  static $primary_key = 'id';

  public static function _table() {
    global $wpdb;
    $tablename = strtolower( get_called_class() );
    $tablename = str_replace( '_model', '', $tablename );
    return $wpdb->prefix . $tablename;
  }

  private static function _settings_table() {
    global $wpdb;
    $tablename = strtolower( get_called_class() );
    $tablename = str_replace( '_model', '_settings', $tablename );
    return $wpdb->prefix . $tablename;
  }

  private static function _fetch_sql( $value ) {
    global $wpdb;
    return $wpdb->prepare( 
            "SELECT *, NULL AS settings FROM ".self::_table()." WHERE ".static::$primary_key." = %s",
            $value );
  }

  public static function getBy($conditions , $order_by = null, $with_settings = false) {
    global $wpdb;

    $where = "";
    foreach ($conditions as $key => $value) {
      $and = !empty($where) ? ' AND ' : ' WHERE ';
      $where .= $and . $key . ' = ' . $value;
    }

    $order_by = $order_by == null ? '' : ' ORDER BY ' . implode(", ", $order_by) . ' ASC';

    $sql = sprintf( 'SELECT *, NULL AS settings FROM %s %s %s', self::_table(), $where, $order_by );
    
    $result = $wpdb->get_results($sql);

    if ($with_settings) self::groupSettings($result);

    return $result;
  }

  static function get( $value, $with_settings = false ) {
    global $wpdb;
    if(empty($value)) return false;
    $result = $wpdb->get_row( self::_fetch_sql( $value ));
    if($with_settings) {
      self::groupSettings($result);
    }

    return $result;
  }

  static function insert( $data ) {
    global $wpdb;
    $wpdb->insert( self::_table(), $data );
    return $wpdb->insert_id;
  }
  
  static function update( $id, $data ) {
    global $wpdb;
    $wpdb->update( self::_table(), $data, [self::$primary_key => $id] );
  }
  
  static function delete( $value ) {
    global $wpdb;
    return $wpdb->query( 
      $wpdb->prepare( "DELETE FROM " . self::_table() . " WHERE " . self::$primary_key . " = %d", 
        array( $value ) ) 
    );
  }

  static function deleteWhere( $condition ) {
    global $wpdb;
    $wpdb->delete( self::_table(), $condition );
  }

  static function getAll($with_settings = false, $order_by = null) {
    
    global $wpdb;
    $order_by = $order_by == null ? '' : ' ORDER BY ' . implode(", ", $order_by) . ' ASC';
    $sql = sprintf( 'SELECT * FROM %s %s', self::_table(), $order_by);
    return $wpdb->get_results( $sql );
  }
  static function insert_id() {
    global $wpdb;
    return $wpdb->insert_id;
  }
  static function time_to_date( $time ) {
    return gmdate( 'Y-m-d H:i:s', $time );
  }
  static function now() {
    return self::time_to_date( time() );
  }
  static function date_to_time( $date ) {
    return strtotime( $date . ' GMT' );
  }

  static function setSettings($id, $settings) {
    global $wpdb;

    $wpdb->delete( self::_settings_table(), ['parent_id' => $id] );
    foreach ($settings as $key => $value) {
      $row = ['parent_id' => $id, 'setting_key' => $key, 'value' => $value];
      $wpdb->insert( self::_settings_table(), $row );
    }

  }

  private static function groupSettings(& $rows) {
    if ($rows == NULL) return;
    global $wpdb;
    
    //$elements = count($rows) == 1 ? (is_array($rows) ? $rows : [$rows]) : $rows;
    is_object($rows)? $elements = [$rows] : $elements = $rows;
    
    foreach ($elements as $to_attach) {
        
        //$sql = sprintf("SELECT setting_key, value FROM %s WHERE parent_id = %d", self::_settings_table(), $to_attach->id);
        $table = self::_settings_table();
        $sql = "SELECT setting_key, value FROM {$table} WHERE parent_id = %d";
        $settings_result = $wpdb->get_results( $wpdb->prepare($sql, array($to_attach->id)), 'ARRAY_N');

        foreach ($settings_result as $setting_result) {
          $to_attach->settings[$setting_result[0]] = $setting_result[1];
        }
    }
  }

  protected static function initSettings() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tablemame = self::_settings_table();

    if($wpdb->get_var("SHOW TABLES LIKE '$tablemame'") != $tablemame) {
      $sql = "CREATE TABLE ". $tablemame . "("
      . "id mediumint(11) NOT NULL AUTO_INCREMENT,"
      . "parent_id mediumint(9) NOT NULL,"
      . "setting_key TEXT NOT NULL,"
      . "value TEXT NOT NULL,"
      . "PRIMARY KEY (id),"
      . "FOREIGN KEY (parent_id) REFERENCES " . self::_table() . "(".self::$primary_key.") ON DELETE CASCADE"
      . ") $charset_collate;";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

      dbDelta( $sql );
    }
  }

}
?>
