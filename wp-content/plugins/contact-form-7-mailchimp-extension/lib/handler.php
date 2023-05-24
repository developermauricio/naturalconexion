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



$plugins = get_option('active_plugins');
$plugchimpmail = 'chimpmail/chimpmail.php';

if (in_array($plugchimpmail, $plugins)) {
    //do_action( 'ep_before_list',$panels);
} else {
    add_filter('wpcf7_editor_panels', 'show_mch_metabox');
    add_action('wpcf7_after_save', 'wpcf7_mch_save_mailchimp');
    add_filter('wpcf7_form_response_output', 'spartan_mce_author_wpcf7', 40, 4);
    add_action('wpcf7_before_send_mail', 'wpcf7_mch_subscribe_remote');
    add_filter('wpcf7_form_class_attr', 'spartan_mce_class_attr');
}


function wpcf7_mch_add_mailchimp($args) {

    if (function_exists("wpcf7_chimp_add_mailchimp")) {
        return;
    }

    $host = esc_url_raw($_SERVER['HTTP_HOST']);
    $url = $_SERVER['REQUEST_URI'];
    $urlactual = $url;

    $cf7_mch_defaults = [];
    $cf7_mch = get_option('cf7_mch_' . $args->id(), $cf7_mch_defaults);

    if ($cf7_mch=="") {
        $cf7_mch_defaults = array();
        update_option('cf7_mch_' . $args->id(), $cf7_mch_defaults);
    }

    $mce_txcomodin = $args->id();
    $listatags = wpcf7_mce_form_tags();

    if ((!isset($cf7_mch['listatags'])) or is_null($cf7_mch['listatags'])) {
        unset($cf7_mch['listatags']);
        // $cf7_mch = $cf7_mch + ['listatags' => $listatags]; // unsupported method on php7.4 and above
        $cf7_mch['listatags'] = $listatags;
        update_option('cf7_mch_' . $args->id(), $cf7_mch);
    }

    $logfileEnabled = (isset($cf7_mch['logfileEnabled'])) ? $cf7_mch['logfileEnabled'] : 0;

    $mceapi = (isset($cf7_mch['api'])) ? $cf7_mch['api'] : null;


    $apivalid = (isset($cf7_mch['api-validation'])) ? $cf7_mch['api-validation'] : null;


    $listdata = (isset($cf7_mch['lisdata'])) ? $cf7_mch['lisdata'] : null;

    $chm_valid = '<span class="chmm valid"><span class="dashicons dashicons-yes"></span>API Key</span>';
    $chm_invalid = '<span class="chmm invalid"><span class="dashicons dashicons-no"></span>API Key</span>';

    include SPARTAN_MCE_PLUGIN_DIR . '/lib/view.php';
}



function wpcf7_mch_save_mailchimp($args) {

    if (function_exists("wpcf7_chimp_save_mailchimp")) {
        return;
    }


    if (!empty($_POST)) {
        $default = [];
        $cf7_mch = get_option('cf7_mch_' . $args->id(), $default);

        $apivalid = (isset($cf7_mch['api-validation'])) ? $cf7_mch['api-validation'] : 0;
        $listdata = (isset($cf7_mch['lisdata'])) ? $cf7_mch['lisdata'] : 0;

        $globalarray = $_POST['wpcf7-mailchimp'];

        if (!isset($_POST['wpcf7-mailchimp']['api-validation'])) {
            $globalarray += ['api-validation' => $apivalid];
        }

        if (!isset($_POST['wpcf7-mailchimp']['lisdata'])) {
            $globalarray += ['lisdata' => $listdata];
        }

        update_option('cf7_mch_' . $args->id(), $globalarray);
    }
}



function show_mch_metabox($panels) {

    $new_page = ['MailChimp-Extension' => ['title' => __('ChimpMatic Lite', 'contact-form-7'), 'callback' => 'wpcf7_mch_add_mailchimp']];

    $panels = array_merge($panels, $new_page);

    return $panels;
}



function spartan_mce_author_wpcf7($mce_supps, $class, $content, $args) {

    $cf7_mch_defaults = [];
    $cf7_mch = get_option('cf7_mch_' . $args->id(), $cf7_mch_defaults);
    $cfsupp = (isset($cf7_mch['cf-supp'])) ? $cf7_mch['cf-supp'] : 0;

    if (1 == $cfsupp) {
        $mce_supps .= mce_referer();
        $mce_supps .= mce_author();
    } else {
        $mce_supps .= mce_referer();
        $mce_supps .= '<!-- Chimpmatic extension by Renzo Johnson -->';
    }
    return $mce_supps;
}



function cf7_mch_tag_replace($pattern, $subject, $posted_data, $html = false) {

    if (preg_match($pattern, $subject, $matches) > 0) {
        if (isset($posted_data[$matches[1]])) {
            $submitted = $posted_data[$matches[1]];

            if (is_array($submitted)) {
                $replaced = join(', ', $submitted);
            } else {
                $replaced = $submitted;
            }

            if ($html) {
                $replaced = strip_tags($replaced);
                $replaced = wptexturize($replaced);
            }

            $replaced = apply_filters('wpcf7_mail_tag_replaced', $replaced, $submitted, '', '');

            return stripslashes($replaced);
        }


        if ($special = apply_filters('wpcf7_special_mail_tags', '', $matches[1], '', '')) {
            return $special;
        }


        return $matches[0];
    }
    return $subject;
}



// Adding error handler as a callback  | Daffa changes | 18 Jan 2023
function errorHandler($errno, $errstr, $errfile, $errline, $errContext = []) {

    $val = "$errstr <br><b>Custom error:</b> [$errline] $errfile in $errline <br>";

    if (strpos($errfile, 'contact-form-7-mailchimp-extension')) {
        $chimp_php_log = new chimp_db_log('mce_php_issues', true, 'api');
        $chimp_php_log->chimp_log_insert_db(1, ' ===============  PARAMETER ERROR  =============== ' . "\n", $val);
    }
    return " Error on line $errline in $errfile<br>";
}



function wpcf7_mch_subscribe_remote($obj) {

    if (function_exists("wpcf7_chimp_subscribe")) {

        return;
    }

    $cf7_mch = get_option('cf7_mch_' . $obj->id());
    $idform = 'cf7_mch_' . $obj->id();

    $chimp_db_log = new chimp_db_log('mce_db_issues', true, 'api', $idform);

    if ($cf7_mch['api-validation'] != 1) {

        return;
    }

    $api = isset($cf7_mch['api']) ? $cf7_mch['api'] : '';
    $pos = strpos($api, "-");

    if ($pos === false) {

        return;
    }

    $submission = WPCF7_Submission::get_instance();

    $logfileEnabled = isset($cf7_mch['logfileEnabled']) && !is_null($cf7_mch['logfileEnabled']) ? $cf7_mch['logfileEnabled'] : false;


    if ($cf7_mch) {

        $subscribe = false;

        $regex = '/\[\s*([a-zA-Z_][0-9a-zA-Z:._-]*)\s*\]/';
        $callback = [&$obj, 'cf7_mch_callback'];

        $email = cf7_mch_tag_replace($regex, $cf7_mch['email'], $submission->get_posted_data());
        $name = cf7_mch_tag_replace($regex, $cf7_mch['name'], $submission->get_posted_data());

        $lists = cf7_mch_tag_replace($regex, $cf7_mch['list'], $submission->get_posted_data());
        $listarr = explode(',', $lists);

    
        $api = $cf7_mch['api'];
        $dc = explode("-", $api);
        $urlmcv3 = "https://$dc[1].api.mailchimp.com/3.0";
        $list = $lists;
    
        $url_get_merge_fields = "$urlmcv3/lists/$list/merge-fields";  //// $urlmcv3
        
        // initiate & set what function will handle PHP Error  | Daffa changes | 18 Jan 2023
        set_error_handler("errorHandler");

        // Add initial value to prevent error undefined | Daffa changes | 17 Nov 2022
        $birtday_field = 'birthday';
        // get Merge Fields that registered on Mailchimp | Daffa changes | 17 Nov 2022
        $arraymerger_ = callApiGet($dc[0], $url_get_merge_fields);
        
        // Find mergetags that have birthday type   | Daffa changes | 17 Nov 2022
        foreach ($arraymerger_[0]['merge_fields'] as $key => $value) {
            if($value['type'] == 'birthday'){

                $birtday_field = $value['tag'];

            }else if($value['type'] == 'imageurl'){

                $birtday_field = $value['tag'];

            }
        }

        $merge_vars = ['FNAME' => $name];// *x1
        
        // *x2
        $parts = explode(" ", $name);
        if (count($parts) > 1) { // *x3
            $lastname = array_pop($parts);
            $firstname = implode(" ", $parts);
            $merge_vars = ['FNAME' => $firstname, 'LNAME' => $lastname];
        } else { // *x4
            $merge_vars = ['FNAME' => $name];// *x5
        }
        
        $new_submission = $submission->get_posted_data();

        for ($i = 1; $i <= 20; $i++) {

            if (isset($cf7_mch['CustomKey' . $i]) && isset($cf7_mch['CustomValue' . $i]) && strlen(trim($cf7_mch['CustomValue' . $i])) != 0) {
                // Find Formtags that registered for birthday and Reformat date value. | Daffa changes | 17 Nov 2022
                if ($cf7_mch['CustomKey' . $i] == $birtday_field) {
                    $date = DateTime::createFromFormat('Y-m-d',  cf7_mch_tag_replace($regex, trim($cf7_mch['CustomValue' . $i]), $new_submission));
                    if ($date) {

                        $output = $date->format("m/d");
                        $cf7_mch['CustomValue' . $i] = $output;
                    }
                }

                $CustomFields[] = ['Key' => trim($cf7_mch['CustomKey' . $i]), 'Value' => cf7_mch_tag_replace($regex, trim($cf7_mch['CustomValue' . $i]), $new_submission)];
                $NameField = trim($cf7_mch['CustomKey' . $i]);
                $NameField = strtr($NameField, "[", "");
                $NameField = strtr($NameField, "]", "");
                $merge_vars = $merge_vars + [$NameField => cf7_mch_tag_replace($regex, trim($cf7_mch['CustomValue' . $i]), $new_submission)];
            }
        }

        $mce_csu = 'subscribed';


        $chimp_db_log->chimp_log_insert_db(1, ' ===============  ACCEPT DATA  =============== ' . "\n", $new_submission);


        if (isset($cf7_mch['confsubs']) && strlen($cf7_mch['confsubs']) != 0) {
            $mce_csu = 'pending';

        } else {


            if (isset ($cf7_mch['addunsubscr'])) {
                if (isset($cf7_mch['accept']) && strlen($cf7_mch['accept']) != 0) {

                    $accept = cf7_mch_tag_replace($regex, trim($cf7_mch['accept']), $new_submission);

                    if (strlen(trim($accept)) != 0) {

                        if (isset($new_submission['quiero'])) {

                            if (count($new_submission['quiero']) > 0) {

                                if (strlen(trim($new_submission['quiero'][0])) > 0) {

                                    $mce_csu = 'subscribed';
                                } else {
                                    $mce_csu = 'unsubscribed';
                                }
                            }

                        }

                        $mce_csu = 'subscribed';
                    } else {

                        $mce_csu = 'unsubscribed';
                    }
                } else {

                    $mce_csu = 'subscribed';
                }


            } else {

                if (isset($cf7_mch['accept']) && strlen($cf7_mch['accept']) != 0) {

                    $accept = cf7_mch_tag_replace($regex, trim($cf7_mch['accept']), $new_submission);

                    if (strlen(trim($accept)) != 0) {

                        if (isset($new_submission['quiero'])) {

                            if (count($new_submission['quiero']) > 0) {

                                if (strlen(trim($new_submission['quiero'][0])) > 0) {

                                    $mce_csu = 'subscribed';
                                } else {
                                    return;
                                }
                            }

                        }


                    } else {

                        return;
                    }
                } else {

                    $mce_csu = 'subscribed';
                }

            }

        }

        if ($mce_csu == '') {

            return;
        }

        try {

            $cad_mergefields = "";
            $cuentarray = count($merge_vars);

            //Armando mergerfields
            foreach ($merge_vars as $clave => $valor) {
                $cadvar = '"' . $clave . '":"' . $valor . '", ';
                $cad_mergefields = $cad_mergefields . $cadvar;
            }

            $cad_mergefields = substr($cad_mergefields, 0, strlen($cad_mergefields) - 2);


            // rj tests
            // ================================================================
            $api = $cf7_mch['api'];
            $dc = explode("-", $api);
            $urlmcv3 = "https://$dc[1].api.mailchimp.com/3.0";
            $list = $lists;


            // 1
            // ================================================================
            $url_get_merge_fields = "$urlmcv3/lists/$list/merge-fields";  //// $urlmcv3


            $arraymerger = callApiGet($dc[0], $url_get_merge_fields);

            $chimp_db_log->chimp_log_insert_db(1, ' ===============  ARRAY MERGE  =============== ' . "\n", ($arraymerger[0]));

            if (isset($arraymerger[0]['merge_fields'])) {

                $campreque = array_column($arraymerger[0]['merge_fields'], 'required', 'merge_id'); // arr de req campos

                foreach ($campreque as $clave => $valor) {

                    if ($valor) {

                        $cadreq = '{"required":false}';
                        $url_edit = "$urlmcv3/lists/$list/merge-fields/$clave"; //// $urlmcv3

                        $resptres = callApiPatch($dc[0], $url_edit, $cadreq);

                        $chimp_db_log->chimp_log_insert_db(1, ' ===============  PATCH FIELD  =============== ' . "\n", $resptres);

                    }
                }
            }

            // 2
            // ================================================================
            $url_put = "$urlmcv3/lists/$list";  //// $urlmcv3
            $info = '{"members": [

                    { "email_address": "' . $email . '",
                      "status": "' . $mce_csu . '",
                      "merge_fields":{ ' . $cad_mergefields . ' }
                    }

                  ],
                  "update_existing": true}';

            $chimp_db_log->chimp_log_insert_db(1, ' ===============  JSON INFO  =============== ' . "\n", $info);

            $respo = callApiPost($dc[0], $url_put, $info);
            
            // get all error and retrieve it on PHP View Error TAB  | Daffa changes | 19 Jan 2023
            
            $chimp_php_log = new chimp_db_log('mce_php_issues', $logfileEnabled, 'api', $idform);
            foreach ($respo[0]['errors'] as $value) {

                // add error if users missed spelled / input wrong parameter formtags. | Daffa changes | 12 Jan 2023
                $err_trigger2 = trigger_error( $value['field_message'] , E_USER_NOTICE);
                $errContext = ['id'=>$idform];
                restore_error_handler();
            }

            mce_save_contador();


            $chimp_db_log = new chimp_db_log('mce_db_issues', $logfileEnabled, 'api', $idform);


            $chimp_db_log->chimp_log_insert_db(1, ' ===============  POST DATA  =============== ' . "\n", $url_put);
            $chimp_db_log->chimp_log_insert_db(1, ' ===============  PAYLOAD  =============== ' . "\n", $respo[0]);
            $chimp_db_log->chimp_log_insert_db(1, ' ===============  RESPONSE  =============== ' . "\n", $respo[1]);
            $chimp_db_log->chimp_log_insert_db(1, ' ===============  URL  =============== ' . "\n", $url_get_merge_fields);
        } catch (Exception $e) {

            $chimp_db_log = new chimp_db_log('mce_db_issues', $logfileEnabled, 'api', $idform);
            $chimp_db_log->chimp_log_insert_db(4, 'Contact Form 7 response: Try Catch  ' . $e->getMessage(), $e);
        }
    }
}



function mce_save_contador() {

    $option_name = 'mce_sent';
    $new_value = 1;
    $valorvar = get_option($option_name);

    if ($valorvar !== false) {
        update_option($option_name, $valorvar + 1);
    } else {
        $deprecated = null;
        $autoload = 'no';
        add_option($option_name, $new_value, $deprecated, $autoload);
    }
}



function mce_get_contador() {

    $option_name = 'mce_sent';
    $new_value = 1;

    $valorvar = get_option($option_name);

    if ($valorvar !== false) {
        echo 'Contador: ' . $valorvar;
    } else {
        echo 'Contador: 0';
    }
}



function spartan_mce_class_attr($class) {

    $class .= ' mailchimp-ext-' . SPARTAN_MCE_VERSION;
    return $class;
}



if (!function_exists('array_column')) {

    function array_column(array $input, $columnKey, $indexKey = null)
    {
        $array = [];
        foreach ($input as $value) {

            if (!array_key_exists($columnKey, $value)) {

                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {

                $array[] = $value[$columnKey];
            } else {

                if (!array_key_exists($indexKey, $value)) {

                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {

                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}



function mce_set_welcomebanner() {

    $Defaultpanel = '<p class="about-description">Hello. My name is Renzo, I <span alt="f487" class="dashicons dashicons-heart red-icon"> </span> WordPress and I develop this free plugin to help users like you. I drink copious amounts of coffee to keep me running longer <span alt="f487" class="dashicons dashicons-smiley red-icon"> </span>. If you' . "'" . 've found this plugin useful, please consider making a donation.</p><br>
      <p class="about-description">Would you like to <a class="button-primary" href="http://bit.ly/cafe4renzo" target="_blank">buy me a coffee?</a> or <a class="button-primary" href="http://bit.ly/cafe4renzo" target="_blank">Donate with Paypal</a></p>';

    $banner = $Defaultpanel;

    if (get_site_option('mce_conten_panel_welcome') == null) {

        add_site_option('mce_conten_panel_welcome', $Defaultpanel);
        $banner = $Defaultpanel;
    } else {

        $grabbanner = trim(get_site_option('mce_conten_panel_welcome'));
        $banner = ($grabbanner == '') ? $Defaultpanel : $grabbanner;
    }

    return $banner;
}



function mce_get_bannerladilla(&$check, &$tittle) {

    $check = 0;
    $url = "https://renzojohnson.com/wp-json/wp/v2/posts?categories=16&orderby=modified&order=desc";


    $response = callApiGetWithoutToken($url);

    if (is_wp_error($response[1])) {

        $check = -1;
        return '';
    }


    if (empty($posts[0]) or is_null($response[0])) {

        $check = -2;
        return '';
    }

    if ($response[1]["response"]["code"] != 200) {

        $check = -3;
        return '';
    }

    if (!empty($posts)) {

        foreach ($posts as $post) {

            $fordate = $post->modified;
            $tittle = $post->title->rendered;
            return $post->content->rendered;
        }
    }
}



function mce_lateral_banner() {

    ?>
    <div id="informationdiv_aux" class="postbox mce-move mc-lateral" style="display:none">
        <?php echo mce_set_lateralbanner() ?>
    </div>
    <?php
}



function mce_set_lateralbanner() {

  $Defaultpanel = '';

    $DefaultpanelOLD = '<h3>ChimpMatic Pro</h3>
      <div class="inside">
        <p>Chimpmatic PRO is best tool to integrate <b>Contact Form 7</b> & <b>Mailchimp.com</b> mailing lists. We have new nifty features:</p>
        <ol>
          <li><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=Tag-Existing" target="_blank"><span class="anew">NEW</span> Tag Existing Subscribers</a></li>
          <li><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=Group-Existing" target="_blank"><span class="anew">NEW</span> Group Existing Subscribers</a></li>
          <li><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=GEmail-Verifications" target="_blank"><span class="anew">NEW</span> Email Verification</a></li>
          <li><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=Unsubscribe" target="_blank"><span class="anew">NEW</span> Unsubscribe</a></li>
          <li><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=Archive" target="_blank"><span class="anew">NEW</span> Archive</a></li>
          <li><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=Delete-Contact" target="_blank"><span class="anew">NEW</span> Delete (Permanently)</a></li>
          <li><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=UnlimitedFields" target="_blank">Unlimited Fields</a></li>
          <li><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=UnlimitedAudiences" target="_blank">Unlimited Audiences</a></li>
          <li><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=html-txt" target="_blank">Let visitors choose HTML or Plain text</a></li>
          <li><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=sync" target="_blank">Sync WordPress Users</a></li>
        </ol>
        <p><a href="https://chimpmatic.com?utm_source=ChimpMatic&utm_campaign=LearnMore" class="dops-button is-primary" target="_blank">Learn More</a></p>
      </div>';

    $Defaultpanel .= '
      <div class="inside  bg-f2"><h3>Upgrade to PRO</h3>
        <p>We have the the best tool to integrate <b>Contact Form 7</b> & <b>Mailchimp.com</b> mailing lists. We have new nifty features:</p>
        <ul>
          <li>Tag Existing Subscribers</li>
          <li>Group Existing Subscribers</li>
          <li>Email Verification</li>
          <li>AWESOME Support And more!</li>
        </ul>

      </div>';

        $Defaultpanel .='<div class="promo-2022">';
          $Defaultpanel .='<h1>40<span>%</span> Off!</h1>';
          $Defaultpanel .='<p class="interesting">Submit your name and email and we’ll send you a coupon for <b>40% off</b> your upgrade to the pro version.</p>';

          $Defaultpanel .= '<div class="wpcf7 cm-form" id="wpcf7-f10181-p10182-o1"><div action="https://chimpmatic.com/almost-there" target="_blank" method="post" class="wpcf7-form" novalidate="novalidate" data-status="init" _lpchecked="1"><div style="display: none;"> <input type="hidden" name="_wpcf7" value="10181"> <input type="hidden" name="_wpcf7_version" value="5.5.6"> <input type="hidden" name="_wpcf7_locale" value="en_US"> <input type="hidden" name="_wpcf7_unit_tag" value="wpcf7-f10181-p10182-o1"> <input type="hidden" name="_wpcf7_container_post" value="10182"> <input type="hidden" name="_wpcf7_posted_data_hash" value=""></div><p><label><span class=" your-name"><input type="text" name="your-name" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="Your Name"></span></label><br> <label><span class=" your-email"><input type="email" name="your-email" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-email wpcf7-validates-as-required wpcf7-validates-as-email" aria-required="true" aria-invalid="false" placeholder="Your Email"></span></label><br> <input type="submit" value="Send me the coupon" class="button cm-submit wpcf7-submit" formaction="https://chimpmatic.com/almost-there" target="_blank"></p></div></div>';
        $Defaultpanel .='</div>';

    $banner = $Defaultpanel;
    //delete_site_option('mce_conten_panel_lateralbanner');

    if (get_site_option('mce_conten_panel_lateralbanner') == null) {

        add_site_option('mce_conten_panel_lateralbanner', $Defaultpanel);
        $banner = $Defaultpanel;

    } else {

        $grabbanner = trim(get_site_option('mce_conten_panel_lateralbanner'));
        $banner = ($grabbanner == '') ? $Defaultpanel : $grabbanner;

    }

    return $banner;
}



function mce_get_bannerlateral(&$check, &$tittle) {

    $check = 0;

    $url = "https://renzojohnson.com/wp-json/wp/v2/posts?categories=25&orderby=modified&order=desc";

    $response = callApiGetWithoutToken($url);

    if (is_wp_error($response[1])) {

        $check = -1;
        return '';
    }


    if (empty($posts[0]) or is_null($posts[0])) {

        $check = -2;
        return '';
    }

    if ($response[1]["response"]["code"] != 200) {

        $check = -3;
        return '';
    }

    if (!empty($posts)) {

        foreach ($posts as $post) {

            $fordate = $post->modified;
            $tittle = $post->title->rendered;
            return $post->content->rendered;
        }
    }
}




function submit_pointer(){




    if ( isset( $_POST['action'] ) && $_POST['action'] =='submit_deactivation' ) {

        global $wpdb;
        $theme_data = wp_get_theme();
        $theme      = $theme_data->Name . ' ' . $theme_data->Version;

        $email = "no.email.provided@gmail.com";
        $reason_details ="these are the details of the reason.";

        $access_data = "Hasnt success connected";
        $mch_api_connection = get_option("mch_api_connection");
        if(isset($mch_api_connection['status'])){
            $access_data = "Success connected";
        }

        // $cf7_mch = get_option('cf7_mch_' . $obj->id());
        // $audience_counter = $cf7_mch ['list'];
        

        if(isset($_POST['email']) && $_POST['email'] != ""){
            $email = $_POST['email'] ;
        }



        $sys_info = '== Site Info' . "<br> \n";
        $sys_info .= '================================================' . "<br> \n";
        $sys_info .= 'Why:                      ' . $_POST['reason'] . '. ' . $_POST['reason_detail'] .  "<br> \n";
        $sys_info .= 'Connection Status:        ' . $access_data .  "<br> \n";
        // $sys_info .= 'Total Audience List:      ' . $audience_counter .  "<br> \n";
        // $sys_info .= 'Detail Reason:            ' . $_POST['reason_detail'] . "<br> \n";
        $sys_info .= 'Email :                   ' . $email . "<br> \n";
        $sys_info .= 'WP:                       ' . get_bloginfo( 'version' ) . "<br> \n";
        $sys_info .= 'Site:                     ' . site_url() . "<br> \n";
        $sys_info .= 'Lang:                     ' . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . "<br> \n";
        $sys_info .= 'Theme:                    ' . $theme . "<br><br> \n";

        $sys_info .= "\n" . '== Webserver Configuration' . "<br> \n";
        $sys_info .= '================================================' . "<br> \n";
        $sys_info .= 'PHP:                      ' . PHP_VERSION . "<br> \n";
        $sys_info .= 'MySQL:                    ' . $wpdb->db_version() . "<br> \n";
        $sys_info .= 'Webserver:                ' . $_SERVER['SERVER_SOFTWARE'] . "<br> \n";
        $sys_info .= 'OS:                       ' . php_uname()  . "<br> \n";
        $sys_info .= 'HHost:                    ' . $_SERVER['HTTP_HOST'] . "<br> \n";
        $sys_info .= 'SName:                    ' . $_SERVER['SERVER_NAME'] . "<br><br> \n";

        $sys_info .= "\n" . '== PHP Extensions' . "<br> \n";
        $sys_info .= '================================================' . "<br> \n";
        // $sys_info .= 'Remote Post:              ' . $WP_REMOTE_POST . "<br> \n";
        $sys_info .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "<br> \n";
        $sys_info .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "<br> \n";
        $sys_info .= 'allow_url_fopen:          ' . ini_get( 'allow_url_fopen' ) . "<br> <br> \n";
        
        if(isset($_POST['php_log'])){
            
            $default = array();
            $log = get_option('mce_php_issues_log', $default);
            $sys_info .= "\n" . '== PHP Error Log' . "<br> \n";
            $sys_info .= '================================================' . "<br> \n\n\n\n";
            foreach ($log as $item) {
                $sys_info .= "\n" . '[' . $item['datetxt'] . ' UTC]';
                $sys_info .= $item ['content'] . "\n";
                $sys_info .= print_r($item ['object'], true) . "\n\n\n\n";
            }

        }
        
        // Function to change sender name
        function mce_sender_name( $original_email_from ) {
            return 'Chimpmatic Stats';
        }
        add_filter( 'wp_mail_from_name', 'mce_sender_name' );




        $cur_sec = date('i'); 
        $cur_hour = date('H'); 

        $recipient = 'chimpmatic@gmail.com';
        $subject = '[Deact: '.$cur_hour.$cur_sec.'] ' . $_POST['reason'] . ' | ' . $_SERVER['HTTP_HOST'];
        $body = ' ';
        $body .= $sys_info;
        $headers = array(
          'Content-Type: text/html; charset=UTF-8',
          'Reply-To: '.$_SERVER['HTTP_HOST'].' <' . $email . '>'
        );

        if(!function_exists('wp_mail')) {

          $res = wp_mail( $recipient, $subject, $body, $headers );

        }else{

          $mailResult = wp_mail( $recipient, $subject, $body, $headers );
        }

        header($_POST['action_url']);
        exit();



    }

}
add_action( 'admin_init', 'submit_pointer' );



if( !function_exists('mce_plugin_deactivate_notice') ){

	function mce_plugin_deactivate_notice(){

		ob_start(); ?>

		<script type="text/javascript">

      function openBoxInfo(){

        event.preventDefault();

        var detailBox = document.querySelector(".mce-detail-info");

        if(detailBox.style.display == "none"){

          detailBox.style.display = "block";

        }else{

          detailBox.style.display = "none";

        }

      }

      function closePointer(){
          jQuery('#deactivate-contact-form-7-mailchimp-extension').pointer('close');
      }

      function emailChecked() {

          var checkBox = document.getElementById("05");
          var text = document.getElementById("email-text");
          var button = document.getElementById("submit_pointer");
      
          if (checkBox.checked == true){
              button.disabled = false;
              text.style.display = "block";
          } else {
              text.style.display = "none";
          }
      }

      function radioChecked() {

          jQuery('#reason01').on('change', function () {
          var checkBox = document.getElementById("reason01");
              
              if(jQuery("#reason01").prop("checked")){

                  document.getElementById("reason01_desc").style.display = "block";
                  document.getElementById("reason02_desc").style.display = "none";
                  document.getElementById("reason03_desc").style.display = "none";
                  document.getElementById("reason04_desc").style.display = "none";
                  
                  document.getElementById("reason01_desc").setAttribute('required', '');
                  document.getElementById("reason02_desc").removeAttribute('required');
                  document.getElementById("reason03_desc").removeAttribute('required');
                  document.getElementById("reason04_desc").removeAttribute('required');

              }
          });
          

          jQuery('#reason02').on('change', function () {
          var checkBox = document.getElementById("reason02");
              
              if(jQuery("#reason02").prop("checked")){

                  document.getElementById("reason01_desc").style.display = "none";
                  document.getElementById("reason02_desc").style.display = "block";
                  document.getElementById("reason03_desc").style.display = "none";
                  document.getElementById("reason04_desc").style.display = "none";

                  document.getElementById("reason01_desc").removeAttribute('required');
                  document.getElementById("reason02_desc").setAttribute('required', '');
                  document.getElementById("reason03_desc").removeAttribute('required');
                  document.getElementById("reason04_desc").removeAttribute('required');

              }
          });
          

          jQuery('#reason03').on('change', function () {
          var checkBox = document.getElementById("reason03");
              
              if(jQuery("#reason03").prop("checked")){

                  document.getElementById("reason01_desc").style.display = "none";
                  document.getElementById("reason02_desc").style.display = "none";
                  document.getElementById("reason03_desc").style.display = "block";
                  document.getElementById("reason04_desc").style.display = "none";

                  document.getElementById("reason01_desc").removeAttribute('required');
                  document.getElementById("reason02_desc").removeAttribute('required');
                  document.getElementById("reason03_desc").setAttribute('required', '');
                  document.getElementById("reason04_desc").removeAttribute('required');

              }
          });
          

          jQuery('#reason04').on('change', function () {
          var checkBox = document.getElementById("reason04");
              
              if(jQuery("#reason04").prop("checked")){

                  document.getElementById("reason01_desc").style.display = "none";
                  document.getElementById("reason02_desc").style.display = "none";
                  document.getElementById("reason03_desc").style.display = "none";
                  document.getElementById("reason04_desc").style.display = "block";

                  document.getElementById("reason01_desc").removeAttribute('required');
                  document.getElementById("reason02_desc").removeAttribute('required');
                  document.getElementById("reason03_desc").removeAttribute('required');
                  document.getElementById("reason04_desc").setAttribute('required', '');

              }
          });
          
          var button = document.getElementById("submit_pointer");
          button.disabled = false;
      }

      jQuery(document).on('click', '.submit_pointer', function(event){
          
          event.preventDefault(); // stop post action
          var reason = "";
          var reason_detail = "";
          var php_log = false;

          
          if(jQuery("#reason01").prop("checked")){
            if(jQuery("#reason01_desc").val() == "" || jQuery("#reason01_desc").val().trim() == ""){
                alert("Please provide What can we do to make it better");
                event.preventDefault();
                return ;
            }else{
                if(reason == ""){
                    reason = jQuery("#reason01").val()
                }else{
                    reason = reason + " & " +  jQuery("#reason01").val()
                }
                reason_detail = jQuery("#reason01_desc").val()
            }
              
          }
          

          if(jQuery("#reason02").prop("checked")){
            if(jQuery("#reason02_desc").val() == "" || jQuery("#reason02_desc").val().trim() == ""){
                alert("Please provide What is broken");
                event.preventDefault();
                return ;
            }else{
                if(reason == ""){
                    reason = jQuery("#reason02").val()
                }else{
                    reason = reason + " & " +  jQuery("#reason02").val()
                }
                reason_detail = jQuery("#reason02_desc").val()
            }
            php_log = true
          }
          

          if(jQuery("#reason03").prop("checked")){
            if(jQuery("#reason03_desc").val() == "" || jQuery("#reason03_desc").val().trim() == ""){
                alert("Please provide What solution you choose");
                event.preventDefault();
                return ;
            }else{
                if(reason == ""){
                    reason = jQuery("#reason03").val()
                }else{
                    reason = reason + " & " +  jQuery("#reason03").val()
                }
                reason_detail = jQuery("#reason03_desc").val()
            }
              
          }
          

          if(jQuery("#reason04").prop("checked")){
            if(jQuery("#reason04_desc").val() == "" || jQuery("#reason04_desc").val().trim() == ""){
                alert("Please let us know what can we do better");
                event.preventDefault();
                return ;
            }else{
                if(reason == ""){
                    reason = jQuery("#reason04").val()
                }else{
                    reason = reason + " & " +  jQuery("#reason04").val()
                }
                reason_detail = jQuery("#reason04_desc").val()
            }
              
          }


          var email = "-"
          if (jQuery('#email-text').val() != undefined) {
              email = jQuery('#email-text').val();
          }

          var action_url = "-"
          if (jQuery('#ajaxformid').attr('action') != undefined) {
              action_url = jQuery('#ajaxformid').attr('action');
          }


          jQuery.ajax({

              type: "POST",

              url: ajaxurl,

              data: {
                  email: email,
                  reason: reason,
                  action:'submit_deactivation',
                  action_url:action_url,
                  reason_detail: reason_detail,
                  php_log : php_log
              },

              success: function(data){
                  console.log(data);
          }
          });
          
          window.location = action_url;
      });
            
			jQuery('#deactivate-contact-form-7-mailchimp-extension ').on('click', function(e){

				e.preventDefault();
                
        var urlRedirect = jQuery(this).attr('href');

        jQuery(
            
            function() {
              
                jQuery('#deactivate-contact-form-7-mailchimp-extension').pointer({

                    content:
                    "<form id='ajaxformid' class='mce-bye' action='"+urlRedirect+"' method='POST'>" +
                    "<div class='mce-deactivate-header'>" +

                    "    <h5>Sorry to see you go!</h5>" +

                    "</div> " +

                    "<div class='mce-wraper-pointer' >" +

                        "<div id='checklist'> " +
                          "<p><strong>We would appreciate if you let us know why you're deactivating Chimpmatic Lite!</strong></p>" +


                        "<fieldset class='mce-deactivate-list'> " +

                          "<label><input id='reason01' name='reasons' type='radio' onclick='radioChecked()' value='It does not work on this site'> " +
                          "It does not work on this site </label> " +

                          "<textarea rows='2' name='comments' id='reason01_desc' required class='regular-text ltr' placeholder='What can we do better?' style='display: none;'></textarea>" +

                          "<label><input id='reason02' name='reasons' type='radio' onclick='radioChecked()' value='It broke my site'> " +
                          "It broke my site </label> " +

                          "<textarea rows='2' name='comments' id='reason02_desc' required class='regular-text ltr' placeholder='Please tell us. What is broken?' style='display: none;'></textarea>" +

                          "<label><input id='reason03' name='reasons' type='radio' onclick='radioChecked()' value='I found a better solution'> " +
                          "I found a better solution </label> " +

                          "<textarea rows='2' name='comments' id='reason03_desc' required class='regular-text ltr' placeholder='What is your better solution?' style='display: none;'></textarea>" +

                          "<label><input id='reason04' name='reasons' type='radio' onclick='radioChecked()' value='I am just disabling temporarily'> " +
                          "I am just disabling temporarily </label> " +

                          "<textarea rows='2' name='comments' id='reason04_desc' required class='regular-text ltr' placeholder='Awesome, lets us know what can we do better!' style='display: none;'></textarea>" +

                          "<hr class='mce-separator'> " +

                            "<label><input id ='05' onclick='emailChecked()' type='checkbox' value='Provided email address'> I would like be contacted about my experience with Chimpmatic Lite.</label> " +
                            " <input type='email' id ='email-text' class='regular-text ltr' placeholder='john.doe@gmail.com' style='display:none '></li>" +

                        "</fieldset> <br> " +

                          "<div>" +

                              "<div style='display:none'>" +

                                  "<a class='mce-info-box' href='#' onclick='openBoxInfo();return false;' >What info do we collect?</a>" +

                                  "<div class='mce-detail-info' style='display:none;'>" +

                                    "<p>Below is a detailed view of all data that Optimizing Matters will receive if you fill in this survey. Your email address is only shared if you explicitly fill it in, your IP addres is never sent.</p>" +

                                      "<ul style=''>" +

                                          "<li><strong>Plugin version </strong> <code id='mce-plugin_version'> 3.1.6 </code></li>" +

                                          "<hr class='mce-separator'> " +

                                          "<li><strong>WordPress version </strong> <code id='core-version'> 6.2 </code></li>" +

                                          "<hr class='mce-separator'> " +

                                          "<li><strong>Current website:</strong> <code> https://dev-quality-chimp.pantheonsite.io/ </code></li>" +

                                          "<hr class='mce-separator'> " +

                                          "<li><strong>Uninstall reason </strong> <i> Selected reason from the above survey </i></li>" +

                                          "<hr class='mce-separator'> " +

                                      "</ul>" +

                                  "</div>" +

                              "</div>" +

                          "</div>" +


                    "</div> " +


                    "</div> " +
                    "<div class='mce-deactivate-footer'>" +

                      " <a id='close_pointer' class='close button' onclick='closePointer()'>Cancel</a> <input id='submit_pointer' class='submit_pointer button action' type='submit' value='Submit & Deactivate' disabled>" +

                    "</div> " +
                    "</form>",

                    position:
                        {
                            edge:  'top',
                            align: 'left'
                        },

                    pointerClass:
                        'wp-pointer arrow-right',

                    pointerWidth: 420,

                    close: function() {
                    },
  
                    
               }).pointer('open');
            }
        );
			    
			});

		</script>
		<?php
		echo ob_get_clean();

	}
}
add_action( 'admin_footer', 'mce_plugin_deactivate_notice' );