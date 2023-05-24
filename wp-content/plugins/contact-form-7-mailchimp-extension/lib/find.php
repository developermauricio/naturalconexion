<?php
/*  Copyright 2010-2023 Renzo Johnson (email: renzo.johnson at gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/



function wpcf7_mch_set_autoupdate() {

    global $wpdb;

    $valuecheck = isset($_POST['valcheck']) ? $_POST['valcheck'] : 0;

    if (get_option('chimpmatic-update') !== false) {
        update_option('chimpmatic-update', $valuecheck);

    } else {
        $deprecated = null;
        $autoload = 'no';
        add_option('chimpmatic-update', $valuecheck, $deprecated, $autoload);
    }

    wp_die();
}
add_action('wp_ajax_wpcf7_mch_set_autoupdate', 'wpcf7_mch_set_autoupdate');
add_action('wp_ajax_no_wpcf7_mch_set_autoupdate', 'wpcf7_mch_set_autoupdate');



function get_log_array() {

    $default = array();
    $log = get_option('mce_db_issues_log', $default);

    $chimp_log = '';

    foreach ($log as $item) {

        $chimp_log .= "\n" . '[' . $item['datetxt'] . ' UTC]';
        $chimp_log .= $item ['content'] . "\n";
        $chimp_log .= print_r($item ['object'], true) . "\n\n\n\n";

    }

    echo $chimp_log;

}



function get_php_log_array() {

    $default = array();
    // differentiate row data to save | Daffa Changes | 11 Jan 2023 
    $log = get_option('mce_php_issues_log', $default);

    $chimp_log = '';

    foreach ($log as $item) {

        $chimp_log .= "\n" . '[' . $item['datetxt'] . ' UTC]';
        $chimp_log .= $item ['content'] . "\n";
        $chimp_log .= print_r($item ['object'], true) . "\n\n\n\n";

    }

    echo $chimp_log;

}



function chimpmatic_logreset() {

    global $wpdb;

    $chimp_db_logdb = new chimp_db_log('mce_db_issues', 1, 'api');
    $res = $chimp_db_logdb->chimp_log_delete_db();

    $chimp_log = 'Your Log is clean now!';
    $chimp_log .= get_log_array();

    echo $chimp_log;

    wp_die();

}
add_action('wp_ajax_chimpmatic_logreset', 'chimpmatic_logreset');
add_action('wp_ajax_no_priv_chimpmatic_logreset', 'chimpmatic_logreset');



function chimpmatic_logload() {

    global $wpdb;

    get_log_array();

    wp_die();

}
add_action('wp_ajax_chimpmatic_logload', 'chimpmatic_logload');
add_action('wp_ajax_no_priv_chimpmatic_logload', 'chimpmatic_logload');



// differentiate function to load & delete | Daffa Changes | 12 Jan 2023 
function chimpmatic_phplogload() {

    global $wpdb;

    get_php_log_array();

    wp_die();

}
// add retrieve button action for PHP Logs | Daffa changes | 17 Jan 2023
add_action('wp_ajax_chimpmatic_phplogload', 'chimpmatic_phplogload');
add_action('wp_ajax_no_priv_chimpmatic_phplogload', 'chimpmatic_phplogload');



function chimpmatic_phplogreset() {

    global $wpdb;

    $chimp_db_logdb = new chimp_db_log('mce_php_issues', 1, 'api');
    $res = $chimp_db_logdb->chimp_log_delete_db();

    $chimp_log = 'Your Log is clean now!';
    $chimp_log .= get_php_log_array();

    echo $chimp_log;

    wp_die();

}
// add reset button action for PHP Logs | Daffa changes | 17 Jan 2023
add_action('wp_ajax_chimpmatic_phplogreset', 'chimpmatic_phplogreset');
add_action('wp_ajax_no_priv_chimpmatic_phplogreset', 'chimpmatic_phplogreset');



function wpcf7_mce_loadlistas() {

    global $wpdb;

    $cf7_mch_defaults = array();
    $mce_idformxx = 'cf7_mch_' . wp_unslash($_POST['mce_idformxx']);
    $mceapi = isset($_POST['mceapi']) ? $_POST['mceapi'] : 0;

    $cf7_mch = get_option($mce_idformxx, $cf7_mch_defaults);

    $tmppost = $cf7_mch;

    $logfileEnabled = isset($cf7_mch['logfileEnabled']) ? $cf7_mch['logfileEnabled'] : 0;
    $logfileEnabled = (is_null($logfileEnabled)) ? false : $logfileEnabled;

    unset($tmppost['api'], $tmppost['api-validation'], $tmppost['lisdata']);

    $tmp = wpcf7_mce_validate_api_key($mceapi, $logfileEnabled, $mce_idformxx);

    $apivalid = $tmp['api-validation'];

    $tmppost = $tmppost + $tmp;

    $tmp = wpcf7_mce_listasasociadas($mceapi, $logfileEnabled, $mce_idformxx, $apivalid);
    $listdata = $tmp['lisdata'];
    $tmppost = $tmppost + $tmp;

    $listatags = $cf7_mch['listatags'];

    $tmppost = $tmppost + array('api' => $mceapi);

    update_option($mce_idformxx, $tmppost);

    $cf7_mch = get_option($mce_idformxx, $cf7_mch_defaults);

    /*echo ( '<pre>' ) ;
        var_dump ( $cf7_mch ) ;
    echo ( '</pre>' ) ; */


    $mch_api_connection = get_option("mch_api_connection");
    $access_data = array("status" => "Hasn't success connected");
    if(isset($apivalid)){
        $access_data = array("status" => "Success connected");
    }

    
    if (isset($mch_api_connection['status'])) {
        add_option("mch_api_connection",$access_data);
    } else {
        update_option("mch_api_connection",$access_data);
    }


    mce_html_panel_listmail($apivalid, $listdata, $cf7_mch);

    wp_die();
}
add_action('wp_ajax_wpcf7_mce_loadlistas', 'wpcf7_mce_loadlistas');
add_action('wp_ajax_no_priv_wpcf7_mce_loadlistas', 'wpcf7_mce_loadlistas');



function mce_html_panel_listmail($apivalid, $listdata, $cf7_mch) {

    $vlist = (isset($cf7_mch['list'])) ? $cf7_mch['list'] : ' ';
    $i = 0;
    //if ( !isset ( $listdata['lists'] ) ) return ;

    $count = isset ($listdata['lists']) ? count($listdata['lists']) : 0;

    //if ( $count == 0 ) return ;
    ?>

    <small><input type="hidden" id="mce_txcomodin2" name="wpcf7-mailchimp[mce_txtcomodin2]"
                  value="<?php echo (isset($apivalid)) ? esc_textarea($apivalid) : ''; ?>"/></small>

    <?php

    if (isset($apivalid) && '1' == $apivalid) {

        ?>
        <label for="wpcf7-mailchimp-list"><?php echo esc_html(__('Total mailchimp.com Audiences: ' . $count . ' ', 'wpcf7')); ?></label>
        <br/>
        <select id="wpcf7-mailchimp-list" name="wpcf7-mailchimp[list]" style="width:45%;">
            <?php
            foreach ($listdata['lists'] as $list) {
                $i = $i + 1;
                ?>
                <option value="<?php echo $list['id'] ?>"
                    <?php if ($vlist == $list['id']) {
                        echo 'selected="selected"';
                    } ?>>
                    <?php echo $i . ' - ' . $list['name'] . ' - Unique id: ' . $list['id'] . '' ?></option>
                <?php
            }
            ?>
        </select>
        <?php

    }

}


function mce_html_selected_tag($nomfield, $listatags, $cf7_mch, $filtro) {

    if ($nomfield != 'email') {

        $r = array_filter($listatags, function ($e) use ($filtro) {
            return $e['basetype'] == $filtro or $e['basetype'] == 'textarea';
        });

    } else {

        $r = array_filter($listatags, function ($e) use ($filtro) {
            return $e['basetype'] == $filtro;
        });

    }

    $listatags = $r;


    $ggCustomValue = (isset($cf7_mch[$nomfield])) ? $cf7_mch[$nomfield] : ' ';


    $ggCustomValue = (($nomfield == 'email' && $ggCustomValue == ' ') ? '[your-email]' : $ggCustomValue);

    ?>
    <select class="chm-select" id="wpcf7-mailchimp-<?php echo $nomfield; ?>"
            name="wpcf7-mailchimp[<?php echo $nomfield; ?>]" style="width:95%">
        <?php if ($nomfield != 'email') { ?>
            <option value=" "
                <?php if ($ggCustomValue == ' ') {
                    echo 'selected="selected"';
                } ?>>
                <?php echo(($nomfield == 'email') ? 'Required by MailChimp' : 'Choose.. ') ?></option>
            <?php
        }
        foreach ($listatags as $listdos) {
            $vfield = '[' . trim($listdos['name']) . ']';
            if ('opt-in' != trim($listdos['name']) && '' != trim($listdos['name'])) {
                ?>
                <option value="<?php echo $vfield ?>" <?php if (trim($ggCustomValue) == $vfield) {
                    echo 'selected="selected"';
                } ?>>
                    <?php echo '[' . $listdos['name'] . ']' ?> <span
                            class="mce-type"><?php echo ' - type :' . $listdos['basetype']; ?></span>
                </option>
                <?php
            }
        }
        ?>
    </select>
    <?php
}



function wpcf7_mce_validate_api_key($input, $logfileEnabled, $idform = '') {

    $sRpta = 0;

    try {

        $chimp_db_log = new chimp_db_log('mce_db_issues', $logfileEnabled, 'api', $idform);


        if (!isset($input) or trim($input) == "") {
            $tmp = array('api-validation' => 0);

            $chimp_db_log->chimp_log_insert_db(4, 'API Key Response : Empty field for API Key', '');

            return $tmp;
        }


        $api = (isset($input)) ? $input : "XXXX-XXXX";

        $acount = substr_count($input, "-");

        if ($acount == 0) {

            $tmp = array('api-validation' => 0);
            $chimp_db_log->chimp_log_insert_db(4, 'API Key Response : ', 'Invalid format for API Key');

            return $tmp;

        }

        $dc = explode("-", $api);

        $urltoken = "https://$dc[1].api.mailchimp.com/3.0/ping";
        $respo = callApiGet($dc[0], $urltoken);
        $resp = $respo[0];

        if (is_wp_error($respo[2])) {

            $tmp = array('api-validation' => 0);

            $chimp_db_log->chimp_log_insert_db(4, ' ===============  PING  =============== ' . "\n", $resp);
            $chimp_db_log->chimp_log_insert_db(4, ' ===============  Failed URL  =============== ' . "\n", $urltoken);
            $chimp_db_log->chimp_log_insert_db(4, ' ===============  Failed $opts  =============== ' . "\n", $respo[1]);

            return $tmp;
        }


        if (isset ($respo[2]['response']['code'])) {

            if ($respo[2]['response']['code'] != 200) {

                $tmp = array('api-validation' => 0);
                $chimp_db_log->chimp_log_insert_db(4, ' ===============  API KEY RESPONSE A  =============== ' . "\n", $respo[2]['response']);

                return $tmp;
            }

        }

        $sRpta = 1;
        $tmp = array('api-validation' => 1);

        $chimp_db_log->chimp_log_insert_db(1, ' ===============  API KEY RESPONSE B   =============== ' . "\n", $resp);

        return $tmp;

    } catch (Exception $e) {

        $tmp = array('api-validation' => 0);

        $chimp_db_log = new chimp_db_log('mce_db_issues', $logfileEnabled, 'api', $idform);
        $chimp_db_log->chimp_log_insert_db(1, 'API Key Response - Result: error Try Catch ' . $e->getMessage(), $e);

        return $tmp;
    }

}



function wpcf7_mce_listasasociadas($apikey, $logfileEnabled, $idform, $apivalid) {

    try {

        $chimp_db_log = new chimp_db_log('mce_db_issues', $logfileEnabled, 'api', $idform);

        if ($apivalid == 0) {
            //Poner un mensaje no repusimos listas
            $list_data = array('id' => 0, 'name' => 'sin lista',);

            $tmp = array('lisdata' => array('lists' => $list_data));

            $chimp_db_log->chimp_log_insert_db(4, ' ===============  List ID RESPONSE  =============== ', 'No Lists, Invalid API key: ' . $apikey);
            $chimp_db_log->chimp_log_insert_db(4, ' ===============  List ID RESPONSE  =============== ', 'No Lists, Invalid api valid: ' . $apivalid);

            return $tmp;
        }

        $api = $apikey;
        $dc = explode("-", $api);

        $urltoken = "https://$dc[1].api.mailchimp.com/3.0/lists?count=9999";

        $respo = callApiGet($dc[0], $urltoken);
        $resp = $respo[0];

        if (is_wp_error($respo[2])) {

            $list_data = array('id' => 0, 'name' => 'sin lista',);

            $tmp = array('lisdata' => array('lists' => $list_data));

            $chimp_db_log->chimp_log_insert_db(4, ' ===============  List ID RESPONSE  ===============  ', $tmp);

            return $tmp;
        }


        $tmp = array('lisdata' => $resp);

        $chimp_db_log->chimp_log_insert_db(1, ' ===============  List ID RESPONSE =============== ', $tmp);

        return $tmp;

    } catch (Exception $e) {

        $list_data = array('id' => 0, 'name' => 'sin lista',);
        $tmp = array('lisdata' => array('lists' => $list_data));

        $chimp_db_log = new chimp_db_log('mce_db_issues', $logfileEnabled, 'api', $idform);

        $chimp_db_log->chimp_log_insert_db(1, ' ===============  List ID - Result: error Try Catch  =============== ' . $e->getMessage(), $e);
        return $tmp;

    }
}



function chmp_new_usr() {

    $new_user = '';
    $new_user .= '<h2>';
    $new_user .= '<a href="https://chimpmatic.com/how-to-find-your-mailchimp-api-key' . vc_utm() . 'NewUserMC-api" class="helping-field" target="_blank" title="get help with MailChimp API Key:"> Learn how find your Mailchimp API Key Here. </a>';
    $new_user .= '</h2>';

    echo $new_user;

}



function wpcf7_mce_form_tags() {

    $manager = WPCF7_FormTagsManager::get_instance();
    $form_tags = $manager->get_scanned_tags();
    return $form_tags;
}