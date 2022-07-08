<?php

ob_start();
require_once dirname(__FILE__).'/../../inc/countries.php';
class WpWoof_Feed_Manage_list extends Wpwoof_Feed_List_Table {

    public $item_index;
    protected $_wpnotice;
    /** ************************************************************************
     * Normally we would be querying data from a database and manipulating that
     * for use in your list table. For this example, we're going to simplify it
     * slightly and create a pre-built array. Think of this as the data that might
     * be returned by $wpdb->query()
     *
     * In a real-world scenario, you would make your own custom query inside
     * this class' prepare_items() method.
     *
     * @var array
     **************************************************************************/


    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct() {
        global $status, $page;
        $this->item_index = 0;

        //Set parent defaults
        parent::__construct(array(
            'singular' => __('feed'),     //singular name of the listed records
            'plural' => __('feeds'),    //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));

        $this->_wpnotice = wp_create_nonce( 'wooffeed-nonce' );

    }



    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title()
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as
     * possible.
     *
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     *
     * For more detailed insight into how columns are handled, take a look at
     * WP_List_Table::single_row_columns()
     *
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name) {


        
        $option_name = $item['option_name'];
        $option_id   = $item['option_id'];
        $itemInfo = unserialize($item['option_value']);
        if (!is_array($itemInfo))            return false;


        $menu_url  = menu_page_url('wpwoof-settings', false);
                
        global $wpdb, $table_prefix,$feed_pro_countries,$woocommerce_wpwoof_common;
        $isPro = $woocommerce_wpwoof_common->isPro($itemInfo);
        
        switch ($column_name) {
            
            case 'feednumber':
                    $this->item_index = $this->item_index + 1;
                    return $isPro?$this->item_index:$this->item_index.'&nbsp;<input '.( empty($itemInfo['noGenAuto']) ? 'checked="true"' : '' ).'" onchange="jQuery.fn.wpwoofSwicher('.$option_id.',jQuery(this).prop(\'checked\'));" type="checkbox" class="ios-switch toggleFeed" />';
            
            case 'feedname':
//                trace($itemInfo,1);
                $addStr ="";
                if(!empty($itemInfo['status_feed']) && $itemInfo['status_feed']!='finished' && $itemInfo['status_feed']!='starting' ){ /**/
                    $addStr = $itemInfo['status_feed'];
                    $addStr='<a class="wpfooalarm" title="'.htmlspecialchars($addStr,ENT_QUOTES).'"><span class="dashicons dashicons-warning" style="color:#dd4e4e;"></span></a>&nbsp;';
                }
                $feedPro = $isPro?"<p class='proFeedNotif'>This feed was created in the PRO version and doesn't work with the FREE version.<br> Consider upgrading, or delete the feed.</p>":"";
                return  '<p><img id="spinner'.$option_id.'" src="'.WPWOOF_ASSETS_URL.'img/spinner-2x.gif" style="width:16px;display: none;" title="In progress" alt="In progress" />&nbsp;' .  $addStr.$itemInfo['feed_name'] . '</p>'.$feedPro;
            case 'feed_type':
                switch($itemInfo['feed_type']){
                    case "google": return "Google Merchant";
                    case "adsensecustom": return "Google Adwords Remarketing Custom";
                    case "pinterest": return "Pinterest";
                    default : return "Facebook Product Catalog";
                }
               // return ( empty($itemInfo['feed_type']) || $itemInfo['feed_type']=='all') ? "facebook" : $itemInfo['feed_type'];
            case 'feedtaxcountry':
                $answ = "";
                if(isset($itemInfo['field_mapping']['tax']['value']) && $itemInfo['field_mapping']['tax']['value']=="true"){
                    if( get_option("woocommerce_tax_based_on")/*!='base'*/ ){
                        if(isset($itemInfo['field_mapping']['tax_countries']['value'])
                            && !empty($itemInfo['field_mapping']['tax_countries']['value'])
                            ){
                            $sValTMP = $itemInfo['field_mapping']['tax_countries']['value'];
                            $taxRate="";
                            if(strpos($sValTMP,"-")){
                                $sValTMP = explode("-",$itemInfo['field_mapping']['tax_countries']['value']);
                                $id=$sValTMP[1];
                                $taxRate = $woocommerce_wpwoof_common->getTaxRateCountries($id);
                                if(count( $taxRate)==1) $taxRate="&nbsp;(".$taxRate[0]['rate'].")";
                                else $taxRate="&nbsp;(n/a)";
                                $sValTMP=$sValTMP[0];
                            }
                            $answ.=WpWoof_get_feed_pro_countries( $sValTMP ).$taxRate;
                        }
                   }
                    
                }else{
                    $answ = "--";
                }
                return $answ;
            
            case 'feeddate':
                $out = "";
                $date = new DateTime();
                $date->setTimestamp(isset($itemInfo['generated_time'])?$itemInfo['generated_time']:$itemInfo['added_time']);
                $date->setTimezone(new DateTimeZone($woocommerce_wpwoof_common->getWpTimezone()));
                $out .= $date->format('d/m/Y H:i:s');
                $nextRun = wp_get_scheduled_event('wpwoof_generate_feed', array((int)$option_id));
                if (!empty($nextRun) && !empty($nextRun->timestamp)) {
                    $date->setTimestamp($nextRun->timestamp);
                    $out .= '<br>Next update:<br>'.$date->format('d/m/Y H:i:s');
                }
                return $out;
            
            case 'feedaction':
                //echo "itemInfo::<br>";
                //trace($itemInfo);
                if ($isPro) return;
                $view = isset($itemInfo['url']) ? $itemInfo['url'] : '';

                if($itemInfo['feed_type']=="adsensecustom" && !empty($view) ){
                    $view = str_replace("/xml/","/csv/",str_replace(".xml",".csv",$view));
                }
                $copy_link = add_query_arg( array('copy'=>$option_id, '_wpnonce'=>$this->_wpnotice ), $menu_url);
                //$return  = "<a ".( empty($view) ? "disabled='disabled'" : "" )." target='_blank' class='button' href='$view'>" . ($itemInfo['feed_type']=="adsensecustom"  ? __('CSV Direct Link', 'woocommerce_wpwoof') : __('View') ) . "</a>&nbsp;";
                $return  = "<a disabled='disabled'  class='wpwoof-button-forlist green' href='".$view."' onclick='return copyWoofLink(this.href);'>" .  __('Copy feed URL', 'woocommerce_wpwoof') . "</a>";
                $return .= "<a disabled='disabled'  class='wpwoof-button-forlist gray' target='_blank' class='button' href='".$view."'>" . ($itemInfo['feed_type']=="adsensecustom"  ? __('CSV Link', 'woocommerce_wpwoof') : __('Open') ) . "</a>";
                $return .= "<a disabled='disabled'  class='wpwoof-button-forlist gray' class='button' href='".$copy_link."'>" .  __('Duplicate')  . "</a>";

                
                return $return;

            case 'feedupdate':
                $edit   = add_query_arg( array('tab'=>1,'edit'  =>$option_id, '_wpnonce'=>$this->_wpnotice ), $menu_url);
                $delete = add_query_arg( array('tab'=>0,'delete'=>$option_id, '_wpnonce'=>$this->_wpnotice ), $menu_url);
                $update = add_query_arg( array('tab'=>0,'update'=>$option_id, '_wpnonce'=>$this->_wpnotice ), $menu_url);

                $return = "<a disabled='disabled' class='wpwoof-button-forlist gray' href='".$edit."'>" . __('Edit') . '</a>';
                $return .= "<a disabled='disabled' id='wpwoof_status_".$option_id."a' class='wpwoof-button-forlist gray regenerate' href='$update'>" . __('Regenerate') . "</a>"."<div class='wpwoof_statusbar' id='wpwoof_status_".$option_id."' data-feedid='".$option_id."'><img id='wpwoof_img_".$option_id."' style='margin-left: -2px;' src='".WPWOOF_URL."/assets/img/bar.png' /></div>";
                $deleteBtn = "<a disabled='disabled' class='wpwoof-button-forlist gray' href='".$delete."'>" . __('Delete') . '</a>';
               return $isPro?$deleteBtn:$return.$deleteBtn;

            case 'feeddownload':
                if ($isPro) return;
                $temp = time();
                $upload_dir = wpwoof_feed_dir($temp);
                $filename = !empty($itemInfo['url']) ? basename($itemInfo['url'], '.xml') : '';
                $download = $upload_dir['path'];
                $download = str_replace($temp . '.xml', '', $download);
                
                $feedname = $itemInfo['feed_name'];
                $feedname = str_replace(' ', '-', $feedname);
                $feedname = strtolower($feedname);

                $file = htmlentities($feedname);
                $nonce = wp_create_nonce( 'wpwoof_download_nonce' );
                $download_xml = add_query_arg(array('feed'=>$option_id, 'wpwoofeedxmldownload'=>$nonce), $menu_url);
                $download_csv = add_query_arg(array('feed'=>$option_id, 'wpwoofeedcsvdownload'=>$nonce), $menu_url);

                $return = ($itemInfo['feed_type']=="adsensecustom" ) ? '' : '<a disabled="disabled" id="wpwoof_status_'.$option_id.'x" class="wpwoof-button-forlist gray" href="'.$download_xml.'" target="_blank">XML</a>';
                $return .= '<a disabled="disabled" id="wpwoof_status_'.$option_id.'c" class="wpwoof-button-forlist gray" href="'.$download_csv.'" target="_blank">CSV</a>';
               
                return $return;
            default:
                return false;

        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     *
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     *
     *
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_option_name($item) {
        //Build row actions
        $edit_nonce = wp_create_nonce('wpwoof_edit_nonce');
        $delete_nonce = wp_create_nonce('wpwoof_delete_nonce');
        //$title = '<strong>' . $item['option_name'] . '</strong>';
        
	
		$actions = array(
          'edit' => sprintf('<a disabled=\'disabled\' href="?page=%s&action=%s&feed=%s&_wpnonce=%s">' . __('Edit', 'woo-feed') . '</a>', esc_attr($_REQUEST['page']), 'edit-feed', $item['option_name'], $edit_nonce),
          'delete' => sprintf('<a disabled=\'disabled\' val="?page=%s&action=%s&feed=%s&_wpnonce=%s" class="single-feed-delete" style="cursor: pointer;">' . __('Delete', 'woo-feed') . '</a>', esc_attr($_REQUEST['page']), 'delete-feed', absint($item['option_id']), $delete_nonce)
        );
	  
        //Return the title contents
        $name = str_replace("wpwoof_feedlist_", "", $item['option_name']);
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/
            $name,
            /*$2%s*/
            $item['option_id'],
            /*$3%s*/
            $this->row_actions($actions)
        );
    }

    public static function get_feeds($search = "") {
        global $wpdb;
        $var="wpwoof_feedlist_";

        $query = $wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s AND option_name <> 'wpwoof_feedlist_' Order By option_id DESC;",$var."%");
        $result = $wpdb->get_results($query, 'ARRAY_A');
        //trace($result);
        if($result) foreach($result as $id => $row){
            if(empty($row['option_id'])){
                $r = $wpdb->get_row("SELECT MAX(option_id)+1 as id from ".$wpdb->options, ARRAY_A);
                $wpdb->query("update ".$wpdb->options." SET option_id='".$r['id']."' where option_name = '".$row['option_name']."'");
                $result[$id]['option_id']=$r['id'];
            }  
        }
        
        return $result;
    }

    /**
     * Delete a Feed.
     *
     * @param int $id Feed ID
     * @return false|int
     */
    public static function delete_feed($id) {
       global $wpdb;
      self::delete_feed_file($id);
      return $wpdb->delete(
          "{$wpdb->prefix}options",
          array('option_id' => $id),
          array('%d')
      );
    }

    /**
     * Delete a Feed File.
     *
     * @param int $id customer ID
     * @return false|int
     */
    public static function delete_feed_file($id) {
        global $wpdb;
        $mylink = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}options WHERE option_id = $id");
        $option_name = $mylink->option_name;
        if(!is_array(get_option($option_name))){
            $feedInfo = unserialize(get_option($option_name));
        }        

        $upload_dir = wp_upload_dir();
        $base = $upload_dir['basedir'];
        if(isset($feedInfo['feedrules']['provider']) && isset( $feedInfo['feedrules']['feedType'])){
            $path = $base . "/woo-feed/" . $feedInfo['feedrules']['provider'] . "/" . $feedInfo['feedrules']['feedType'];
            $file = $path . "/" . $feedInfo['feedrules']['filename'] . "." . $feedInfo['feedrules']['feedType'];
            unlink($file);    
        }
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}options WHERE option_name like 'wpwoof_feedlist_%'";
        return $wpdb->get_var($sql);
    }

    /** Text displayed when no data is available */
    public function no_items() {
        _e('No feed available.', 'woo-feed');
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     *
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item) {
        global $woocommerce_wpwoof_common;
        $itemInfo = unserialize($item['option_value']);
        $isPro = $woocommerce_wpwoof_common->isPro($itemInfo);
        return $isPro?"":sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/
            $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/
            $item['option_id']                //The value of the checkbox should be the record's id
        );
    }


    function column_name($item) {
        $edit_nonce = wp_create_nonce('wpwoof_edit_nonce');
        $delete_nonce = wp_create_nonce('wpwoof_delete_nonce');
        $title = '<strong>' . $item['option_name'] . '</strong>';
        
		$actions = array(
            'edit' => sprintf('<a disabled=\'disabled\' href="?page=%s&action=%s&feed=%s&_wpnonce=%s">' . __('Edit', 'woo-feed') . '</a>', esc_attr($_REQUEST['page']), 'edit-feed', absint($item['option_id']), $edit_nonce),
            'delete' => sprintf('<a disabled=\'disabled\' val="?page=%s&action=%s&feed=%s&_wpnonce=%s" class="single-feed-delete" style="cursor: pointer;">' . __('Delete', 'woo-feed') . '</a>', esc_attr($_REQUEST['page']), 'delete-feed', absint($item['option_id']), $delete_nonce)
        );
        return $title . $this->row_actions($actions);
    }

    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     *
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns() {
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'feednumber'    => __('#'),
            'feedname'      => __('Feed Name'),
            'feed_type'      => __('Feed Type'),
            'feedtaxcountry'=> __('Tax Сountry'),
            'feeddate'      => __('Last updated'),
            'feedaction'    => __("Action"),
            'feedupdate'    => __("Regenerate"),
            'feeddownload'  => __("Download"),
        );
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
     * you will need to register it here. This should return an array where the
     * key is the column that needs to be sortable, and the value is db column to
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     *
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
           // 'feednumber'=>array('feednumber'),
           // 'feedname' => array('feedname')
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     *
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     *
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     *
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'bulk-delete' => __('Delete'),
            'bulk-turn-on' => __('Turn ON'),
            'bulk-turn-off' => __('Turn OFF'),
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     **************************************************************************/
    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if ('delete-feed' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'wpwoof_delete_nonce')) {
                update_option('wpwoof_message', 'Failed To Delete Feed. You do not have sufficient permission to delete.');
                wp_redirect(admin_url("admin.php?page=category_mapping&wpwoof_message=error"));
            } else {
                if (self::delete_feed(absint($_GET['feed']))) {

                    update_option('wpwoof_message', 'Feed Deleted Successfully');
                    wp_redirect(admin_url("admin.php?page=wpwoof-settings&wpwoof_message=success"));
                } else {
                    update_option('wpwoof_message', 'Failed To Delete Feed');
                    wp_redirect(admin_url("admin.php?page=wpwoof-settings&wpwoof_message=error"));
                }

            }
        }
        //Detect when a bulk action is being triggered...
        if ('edit-feed' === $this->current_action()) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'wpwoof_edit_nonce')) {
                die('Cheating huh!');
            } else {

            }
        }


        // If the delete bulk action is triggered
        if ((isset($_POST['feed'])) && (isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
            || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
        ) {
            if ('bulk-delete' === $this->current_action()) {
                $nonce = esc_attr($_REQUEST['_wpnonce']);
                if (!wp_verify_nonce($nonce, "bulk-" . $this->_args['plural'])) {
                    die('cheating huh!');
                } else {
                    $delete_ids = esc_sql($_POST['feed']);
                    // loop over the array of record IDs and delete them
                    if (count($delete_ids)) {
                        foreach ($delete_ids as $id) {
                            self::delete_feed($id);

                        }
                        update_option('wpwoof_message', 'Feed Deleted Successfully');
                        wp_redirect(admin_url("admin.php?page=wpwoof-settings&wpwoof_message=success"));
                    }
                }
            }
        }
		
		// If the Turn ON or Turn OFF bulk action is triggered
		if ((isset($_POST['feed'])) && (isset($_POST['action']) && in_array($_POST['action'], array('bulk-turn-on', 'bulk-turn-off')))
			|| (isset($_POST['action2']) && in_array($_POST['action2'], array('bulk-turn-on', 'bulk-turn-off')))
		) {
			$nonce = esc_attr($_REQUEST['_wpnonce']);
			if (!wp_verify_nonce($nonce, 'bulk-' . $this->_args['plural'])) {
				die('cheating huh!');
			} else {
				$changeStatusAction = ($this->current_action() == 'bulk-turn-on') ? 0 : 1;
				$feed_ids = esc_sql($_POST['feed']);
				if (count($feed_ids)) {
					foreach ($feed_ids as $id) {
						if (!$id || !is_numeric($id))
							continue;
						
						$value = wpwoof_get_feed($id);
						if (!empty($value['feed_name'])) {
							$value['noGenAuto'] = $changeStatusAction;
							wpwoof_update_feed($value, $id, true);
                                                        wpwoof_product_catalog::schedule_feed($value);
						}
					}
					$wpwoof_message = ($changeStatusAction === 0) ? 'Feed Activated Successfully' : 'Feed Deactivated Successfully';
					update_option('wpwoof_message', $wpwoof_message);
					wp_redirect(admin_url('admin.php?page=wpwoof-settings&wpwoof_message=success'));
				}
			}
		}
    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     *
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 10;


        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();


        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        if (isset($_POST['s'])) {
            $data = $this->get_feeds($_POST['s']);
        } else {
            
            $data = $this->get_feeds();
        }


        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a, $b)
        {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'option_id'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        usort($data, 'usort_reorder');


        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ));

//        $this->set_pagination_args( array(
//            'total_items' => $total_items,                  //WE have to calculate the total number of items
//            'per_page'    => $per_page                     //WE have to determine how many items to show on a page
//        ) );

        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;
    }


    public function get_category_name($ids) {

        $category_str = '';
        if(!empty($ids) && is_array($ids)){

            foreach ($ids as $key => $term_id) {
                $term = get_term($term_id, 'product_cat');
                if( !is_wp_error($term ))
                    $category_str .= $term->name.', ';

            }
        }

        $category_str = rtrim($category_str, ', ');


        return $category_str;
    }

}
