<?php
require_once("common.php");
global $woocommerce_wpwoof_common;


function wpwoof_delete_feed( $id ) {
        global $wpdb;
        wpwoof_delete_feed_file($id);
        return $wpdb->query("DELETE FROM ".$wpdb->prefix."options WHERE option_id='".(int)$id."' AND option_name LIKE 'wpwoof_feedlist_%'	");
}

function wpwoof_update_feed( $option_value, $option_id,$flag=false,$feed_name='' ) {     
        global $wpdb;
        //wpwoof_delete_feed_file($id);
        if(!$flag) {
            if (empty($option_value['status_feed'])) {
                $option_value['status_feed'] = "";
            }
            $tmpdata = unserialize(wpwoof_get_feed($option_id));

            if (!empty($tmpdata['status_feed'])) {
                $option_value['status_feed'] = $tmpdata['status_feed'];
            }
            $upload_dir = wpwoof_feed_dir( $option_value['feed_name'], $option_value['feed_type'] == "adsensecustom" ? "csv" : "xml");
            $fileurl = $upload_dir['url'];
            $option_value['url'] = $fileurl;
            
        }
        
        $option_value = serialize($option_value);
        $table = "{$wpdb->prefix}options";
        $data = array('option_value'=>$option_value);
        if($feed_name) {
            $data['option_name'] = 'wpwoof_feedlist_'. $feed_name;
        }    
        //trace($data,1) ;        
        $where = array('option_id'=>$option_id);

        $sSet = " option_value=%s".(isset( $data['option_name']) ? ", option_name=%s" : "");
        $aData= array();
        array_push($aData,$option_value);
        if(isset( $data['option_name'])){
            array_push($aData,$data['option_name']);
        }
        array_push($aData, $option_id );
        array_push($aData,'wpwoof_feedlist_%' );

        return $wpdb->query( $wpdb->prepare(" update ".$table." SET  ".$sSet." WHERE option_id=%d AND option_name LIKE %s ", $aData ) );

}


function wpwoof_get_feeds( $search = "" ) {

    global $wpdb;
    $option_name="wpwoof_feedlist_";
    if( $search != '' )
    	$option_name = $search;

    $query = $wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s;", "%".$option_name."%");
    $result = $wpdb->get_results($query, 'ARRAY_A');

    return $result;

}

function wpwoof_get_feed( $option_id ) {
    global $wpdb;

    $query = $wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_id='%s' AND option_name LIKE 'wpwoof_feedlist_%%'", $option_id);
    $result = $wpdb->get_var($query);
    $result = unserialize($result);
    $result['edit_feed'] = $option_id;
    $result = serialize($result);
    return $result;
}

function wpwoof_feed_dir( $feedname, $file_type = 'xml' ) {
    $feedname = str_replace(' ', '-', trim( $feedname ) );
    $feedname = strtolower($feedname);
    $upload_dir = wp_upload_dir();
    $base = $upload_dir['basedir'];
    $baseurl = $upload_dir['baseurl'];

    $path       = $base . "/wpwoof-feed/" . $file_type;
    $baseurl    = $baseurl . "/wpwoof-feed/" . $file_type;
    $file       = $path . "/" . $feedname . "." . $file_type;
    $fileurl    = $baseurl . "/" . $feedname . "." . $file_type;
    
    return array('path'       => $file,
                 'url'        => $fileurl,
                 'file'       => $feedname . '.'.$file_type,
                 'pathtofile' => $path);
}

function wpwoof_create_feed($data, $schedule = true){
    global $wpdb;
    //trace($data,1);
    if(!isset($data['feed_type'])) exit();
    $feedname = sanitize_text_field($data['feed_name']);
    $upload_dir = wpwoof_feed_dir($feedname,  $data['feed_type'] == "adsensecustom" ? "csv" : "xml");
    $file = $upload_dir['path'];
    $fileurl = $upload_dir['url'];
    $file_name = $upload_dir['file'];
    $data['url'] = $fileurl;
    if ($schedule) {
        wp_schedule_single_event( time(), 'wpwoof_generate_feed', array((int)$data['edit_feed'] ) );
        return $fileurl;
    }
    
    if( update_option('wpwoof_feedlist_' . $feedname, $data) ){
        $row = $wpdb->get_row("SELECT * FROM ".$wpdb->options." WHERE option_name = 'wpwoof_feedlist_" . $feedname. "'", ARRAY_A);
        if(empty($row['option_id'])){
            $r = $wpdb->get_row("SELECT MAX(option_id)+1 as id from ".$wpdb->options, ARRAY_A);
            $wpdb->query("update ".$wpdb->options." SET option_id='".$r['id']."' where option_name = 'wpwoof_feedlist_" . $feedname. "'");
            if(!isset($data['edit_feed'])) $data['edit_feed'] = $r['id'];
        } elseif(!isset($data['edit_feed'])) $data['edit_feed'] = $row['option_id'];
    }

    $dir_path = str_replace( $file_name, '', $file );
    if (wpwoof_checkDir($dir_path)) {
        if(!wpwoofeed_generate_feed($data,  $data['feed_type'] == "adsensecustom" ? "csv" : "xml")) return false;
    }
    return $fileurl;
}

function wpwoof_checkDir($path){
    if (!file_exists($path)) {
       return wp_mkdir_p($path);
    }
    return true;
}

function wpwoof_delete_feed_file($id){
    $option_id = $id;
    $feed = wpwoof_get_feed($option_id);
    $wpwoof_values = unserialize($feed);
    $feed_name = sanitize_text_field($wpwoof_values['feed_name']);
    $upload_dir = wpwoof_feed_dir($feed_name);
    $file = $upload_dir['path'];
    $fileurl = $upload_dir['url'];

    if( file_exists($file))
        unlink($file);
}

function wpwoof_refresh($message = '') {
    $settings_page = $_SERVER['REQUEST_URI'];
    if ( strpos( $settings_page, '&' ) !== false ) {
        $settings_page = substr( $settings_page, 0, strpos( $settings_page, '&' ) );
    }
    if ( ! empty( $message ) ) {
        $settings_page .= '&show_msg=true&wpwoof_message=' . $message;
    }
    if(!WPWOOF_DEBUG) header("Location:".$settings_page);
}



add_action('wp_ajax_wpwoofgtaxonmy', 'ajax_wpwoofgtaxonmy');
function ajax_wpwoofgtaxonmy(){
    error_reporting(E_ALL & ~E_NOTICE);

    $lang = 'en-US';
    $file = "http://www.google.com/basepages/producttype/taxonomy.{$lang}.txt";

    $reader = new LazyTaxonomyReader();

    $line_no = (isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : null);
    $taxonomy = (isset($_POST['taxonomy'])  ?  $_POST['taxonomy'] : null);
    $lvl = 1;
    $result[0] =  $reader->categories['values'];
    $tmpCatLvl = $reader->categories;
    foreach ( explode($reader->separator,$taxonomy) as $value) {
        if(isset($tmpCatLvl[$value])) {
            $result[$lvl++] = $tmpCatLvl[$value]["values"];
            $tmpCatLvl = $tmpCatLvl[$value];
        } else {
            break;
        }

    }
//    $result = $reader->getDirectDescendants($line_no);
    echo json_encode($result);

    die();
}




class LazyTaxonomyReader {

    private $base = null;
    public  $separator = ' > ';
    protected $lines;
    public $categories = array();

    public function __construct($file='') {
        if( empty($file) )
            $file = plugin_dir_path(__FILE__) . 'google-taxonomy.en.txt';

        $this->lines = file($file, FILE_IGNORE_NEW_LINES);
        // remove first line that has version number
        if (substr($this->lines[0], 0, 1) == '#')
            unset($this->lines[0]);
        $this->categories['values'] = array();
        $tcat[0] = $this->categories;
        foreach ($this->lines as $line) {
            $tarr = explode($this->separator,$line);
            if (count($tarr)>1) { 
            $val = end($tarr);
            unset($tarr[count($tarr)-1]);
            $arpath = '["'.implode('"]["', $tarr).'"]';
            eval("\$this->categories".$arpath.'["values"][]="'.$val.'";');
            }
            else {
                $this->categories["values"][]=$tarr[0];
            }
        }
//        trace($this->categories);
    }
}

add_action('wp_ajax_wpwoofcategories', 'ajax_wpwoofcategories');
function ajax_wpwoofcategories(){
    wpwoofcategories( $_POST );
    die();
}

function wpwoofcategories( $options = array() ) {
    global $wp_version;
    $options = array_merge(array(), $options);
?>
    <p><b>Please select categories</b></p>
    <?php
    $terms = null;
    if( version_compare( floatval( $wp_version ), '4.5', '>=' ) ) {
        $args = array(
           'taxonomy'      => array('product_cat'),
           'hide_empty'    => false,
            'orderby'       => 'name',
            'order'         => 'ASC'
        );


        $terms =  get_terms( $args );
    }else{
        $terms =  get_terms( 'product_cat', 'orderby=name&order=ASC&hide_empty=0' );
    }





    if( empty( $options['feed_category'] ) ) {
        $options['feed_category'] = array();
        $options['feed_category_all'] = '-1';
        $options['feed_category'][] = '0';
        foreach($terms as $key => $term) {
            $options['feed_category'][] = $term->term_id;
        }
        
    }

    ?>
    <p class="description">You can also select multiple categories</p>
    <ul>
        <li><input type="checkbox" value="-1" name="feed_category_all" id="feed_category_all" class="feed_category" <?php checked( -1, (isset($options['feed_category_all']) ? $options['feed_category_all'] : '0'), true); ?>>
        <label for="feed_category_all">All Categories</label></li>
        <?php foreach ($terms as $key => $term) { 
            $haystacks = isset($options['feed_category']) ? $options['feed_category'] : array();
            $cat_key = array_search($term->term_id, $haystacks);
            $cat_id = isset($haystacks[$cat_key]) ? $haystacks[$cat_key] : -1;
            ?>
            <li><input type="checkbox" value="<?php echo $term->term_id; ?>" name="feed_category[]" id="feed_category_<?php echo $term->term_id; ?>" class="feed_category" <?php checked( $term->term_id, $cat_id, true); ?>><label for="<?php echo 'feed_category_' . $term->term_id; ?>"><?php echo $term->name; ?> &nbsp; &nbsp; (<?php echo $term->count; ?>)</label></li> 
        <?php } ?>
    </ul>
    <br>
    <div id="wpwoof-popup-bottom"><a href="#done" class="button button-secondary wpwoof-popup-done">Done</a></div>
        
<?php
}
function wpwoof_create_csv($path, $file, $content, $columns, $info=array()) {
    $info = array_merge(array('delimiter'=>'tab', 'enclosure' => 'double' ), $info);
    if(wpwoof_checkDir($path)) {
        $fp = fopen($file, "w");
        $delimiter = $info['delimiter'];
        if ($delimiter == 'tab') {
            $delimiter = "\t";
        }
        $enclosure = $info['enclosure'];
        if ($enclosure == "double")
            $enclosure = chr(34);
        else if ($enclosure == "single")
            $enclosure = chr(39);
        else
            $enclosure = '"';
        if (!empty($columns) ) {
            $header = array();
            foreach ($columns as $column_name => $value) {
                $header[] = $column_name;
            }
            fputcsv($fp, $header, $delimiter, $enclosure);
        }
        if (!empty($content) ) {
            foreach ($content as $fields) {
                if( count($fields) != count($columns) )
                    continue;
                fputcsv($fp, $fields, $delimiter, $enclosure);
            }
        }
        fclose($fp);
        return true;
    } else {
        return false;
    }
}

