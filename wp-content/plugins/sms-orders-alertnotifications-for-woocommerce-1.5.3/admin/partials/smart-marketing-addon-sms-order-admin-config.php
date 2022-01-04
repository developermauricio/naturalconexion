<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/admin/partials
 */

$this->smsonw_woocommerce_dependency_notice();

if (isset($_POST['form_id']) && strlen($_POST['form_id'])) {
    $result = $this->smsonw_process_config_form($_POST);
}

$sender_option = json_decode(get_option('egoi_sms_order_sender'), true);
$recipients = json_decode(get_option('egoi_sms_order_recipients'), true);
$texts = json_decode(get_option('egoi_sms_order_texts'), true);
$payment_texts = json_decode(get_option('egoi_sms_order_payment_texts'), true);
$payment_email_texts = json_decode(get_option('egoi_email_order_payment_texts'), true);
$follow_price = json_decode(get_option('egoi_sms_follow_price'), true);
$abandoned_cart_obj = json_decode(get_option('egoi_sms_abandoned_cart'), true);
$transactional_email = get_option('egoi_transactional_email');

if (empty($abandoned_cart_obj)) {
	$abandoned_cart_obj = [
		"message"            => __('Olá relembramos que deixou produtos no seu carrinho, clique aqui para recuperar %link%', 'smart-marketing-addon-sms-order'),
		"title_pop"          => __('Olá deixe aqui o seu contacto para conseguirmos entrar em contacto consigo.', 'smart-marketing-addon-sms-order'),
		"button_name"        => __('Enviar', 'smart-marketing-addon-sms-order'),
		"button_cancel_name" => __('Cancelar', 'smart-marketing-addon-sms-order'),
		"enable"             => "",
		"shortener"          => "on",
		"background_color"   => "#000000",
		"text_color"         => "#ffffff",
		"button_color"       => "#a19fa1",
		"button_text_color"  => "#000000"
	];
	update_option('egoi_sms_abandoned_cart', json_encode($abandoned_cart_obj));
}

if (empty($follow_price)) {
	$follow_price = [
		"follow_price_message"     => __( 'Olá, alteramos o preço de %product%, clique aqui para ver as alterações %link%', 'smart-marketing-addon-sms-order' ),
		"follow_title_pop"         => __( 'Olá deixe aqui o seu contacto para conseguirmos entrar em contacto consigo.', 'smart-marketing-addon-sms-order' ),
		"follow_price_button_name" => __( 'Enviar', 'smart-marketing-addon-sms-order' ),
		"enable"                   => "",
		"shortener"                => "on",
		"background_color"         => "#000000",
		"text_color"               => "#ffffff",
		"button_color"             => "#a19fa1",
		"button_text_color"        => "#000000"
	];
	update_option('egoi_sms_follow_price', json_encode($follow_price));
}

if (empty($follow_price["follow_background_color"])) {
	$follow_price["follow_background_color"] = "#000000";
	$follow_price["follow_text_color"] = "#ffffff";
	$follow_price["follow_button_color"] = "#a19fa1";
	$follow_price["follow_button_text_color"] = "#000000";
}


$senders = $this->helper->smsonw_get_senders();
$soap_error = $this->helper->smsonw_get_soap_error();
$balance = $this->helper->smsonw_get_balance();
$reminder_times = array('1','12', '24', '36', '48', '72');

?>
<span id="form_info" data-form-id="<?php esc_html_e($_POST['form_id']);?>" data-form-lang="<?php esc_html_e($_POST['sms_text_language']);?>" data-form-method="<?php esc_html_e($_POST['sms_payment_method']);?>"></span>
<!-- head -->
<h1 class="logo">Smart Marketing - <?php _e( 'SMS Notifications', 'smart-marketing-addon-sms-order' ); ?></h1>
<p class="breadcrumbs">
    <span class="prefix"><?php _e( 'You are here: ', 'smart-marketing-addon-sms-order' ); ?></span>
    <strong>Smart Marketing</a> &rsaquo;
        <span class="current-crumb"><?php _e( 'SMS Notifications', 'smart-marketing-addon-sms-order' ); ?></strong></span>
</p>
<hr/>

<p class="nav-tab-wrapper">
    <a class="nav-tab nav-tab-addon nav-tab-active" id="nav-tab-sms-senders">
		<?php _e('General settings', 'smart-marketing-addon-sms-order'); ?>
    </a>

    <a class="nav-tab nav-tab-addon" id="nav-tab-sms-texts">
        <?php _e('SMS Messages', 'smart-marketing-addon-sms-order'); ?>
    </a>

    <a class="nav-tab nav-tab-addon" id="nav-tab-sms-payment-texts">
        <?php _e('Payments SMS', 'smart-marketing-addon-sms-order'); ?>
    </a>

    <a class="nav-tab nav-tab-addon" id="nav-tab-sms-emailpayment-texts">
        <?php _e('Payments Email', 'smart-marketing-addon-sms-order'); ?>
    </a>

    <a class="nav-tab nav-tab-addon" id="nav-tab-sms-tracking-texts">
        <?php _e('SMS URL Tracking', 'smart-marketing-addon-sms-order'); ?>
    </a>

    <a class="nav-tab nav-tab-addon" id="nav-tab-sms-follow-price">
        <?php _e('SMS Follow Price', 'smart-marketing-addon-sms-order'); ?>
    </a>

    <a class="nav-tab nav-tab-addon" id="nav-tab-sms-abandoned-cart">
        <?php _e('Abandoned Cart', 'smart-marketing-addon-sms-order'); ?>
    </a>

    <a class="nav-tab nav-tab-addon" id="nav-tab-sms-help">
        <?php _e('Help', 'smart-marketing-addon-sms-order'); ?>
    </a>
</p>

<!-- wrap SMS Senders/Recipients -->
<div class="wrap tab wrap-addon" id="tab-sms-senders">
    <div class="wrap egoi4wp-settings">
        <div class="row">

            <?php
                if (isset($_POST['form_id']) && $_POST['form_id'] == 'form-sms-order-senders') {
                    if ($result) {
                        $this->helper->smsonw_admin_notice_success();
                    } else {
                        $this->helper->smsonw_admin_notice_error();
                    }
                }
            ?>

            <?php if (empty($senders) && !$soap_error)  { ?>

                <div class="notice notice-error" style="max-width: 800px;">
                    <p>
                        <?php _e( 'To use this plugin you need to add a sender and activate the SMS transactional within your E-goi account.', 'smart-marketing-addon-sms-order' ); ?>
                        <br>
                        <a id="button_view_help" target="_blank"><?php _e('View help','smart-marketing-addon-sms-order');?></a>
                    </p>
                </div>

            <?php } ?>

            <?php

            if ($soap_error)  {
                    $senders=null;

            ?>

            <div class="notice notice-error" style="max-width: 800px;">
                <p>
                    <?php _e( 'We are having some technical difficulties reaching the endpoint. Please try again latter.', 'smart-marketing-addon-sms-order' ); ?>
                </p>
            </div>

            <?php } ?>

            <div class="main-content col col-12" style="margin:0 0 20px;<?php echo empty($senders) ? "display: none;" : null;?>">

                <table style="max-width: 900px;">
                    <tr>
                        <td width="50%" valign="top" style="padding-right: 20px;">
                            <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-senders">
                                <?php wp_nonce_field( 'form-sms-order-senders' ); ?>
                                <input name="form_id" type="hidden" value="form-sms-order-senders" />

                                <p class="label_text" style="margin-top: 8px;"><?php _e('E-goi SMS Sender', 'smart-marketing-addon-sms-order');?></p>

                                <select class="e-goi-option-select-admin-forms" style="width: 100%;" name="sender_hash" id="sender_hash" required>
                                    <option value="off" selected>
                                        <?php _e('Disable', 'smart-marketing-addon-sms-order');?>
                                    </option>
                                    <?php
                                    if (isset($senders) && count($senders) > 0) {
                                        foreach ($senders as $sender) {
                                            ?>
                                            <option value="<?=$sender['FROMID']?>" <?php selected($sender['FROMID'], $sender_option['sender_hash']);?> >
                                                <?=$sender['SENDER']?>
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>

                                <p class="label_text"><?php _e('Admin Cellphone', 'smart-marketing-addon-sms-order');?></p>

                                <select name="admin_prefix" class="e-goi-option-select-admin-forms" style="width: 49%; float: left;" required >
                                    <?php
                                    foreach (unserialize(COUNTRY_CODES) as $key => $value) {
                                        $string = ucwords(strtolower($value['country_pt']))." (+".$value['prefix'].")";
                                        ?>
                                        <option value="<?=$value['prefix']?>" <?php selected($value['prefix'], $sender_option['admin_prefix']);?> ><?=$string?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <input type="text" id="admin_phone" name="admin_phone" class="regular-text" style="width: 50%; height: 38px;"
                                       value="<?php echo isset($sender_option['admin_phone']) ? $sender_option['admin_phone'] : null; ?>"
                                />


                                <p class="label_text">
                                    <?php _e('Order SMS notifications', 'smart-marketing-addon-sms-order');?>
                                </p>
                                <p class="label_text_mini">
                                    <?php _e('Select to which order status SMS will be sent', 'smart-marketing-addon-sms-order');?>
                                </p>

                                <table border='0' class="widefat striped" style="width: 100%;">
                                    <thead>
                                    <tr>
                                        <th><?php _e('Order Status', 'smart-marketing-addon-sms-order');?></th>
                                        <th style="text-align: center;"><?php _e('Customer', 'smart-marketing-addon-sms-order');?></th>
                                        <th style="text-align: center;"><?php _e('Admin', 'smart-marketing-addon-sms-order');?></th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    <?php foreach ($this->helper->smsonw_get_order_statuses() as $cod => $name) { ?>
                                        <tr>
                                            <td><?=$name?></td>
                                            <td align="center">
                                                <input class="input-checkbox" type="checkbox" name="egoi_sms_order_customer_<?=$cod?>" value="1"
                                                    <?php checked($recipients['egoi_sms_order_customer_'.$cod], 1);?>
                                                />
                                            </td>
                                            <td align="center">
                                                <input class="input-checkbox admin-order-status" type="checkbox" name="egoi_sms_order_admin_<?=$cod?>" value="1"
                                                    <?php checked($recipients['egoi_sms_order_admin_'.$cod], 1);?>
                                                />
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>

                                <p class="label_text"><?php _e('Send SMS notifications to customers', 'smart-marketing-addon-sms-order');?></p>

                                <p class="label_text_mini">
                                    <input type="radio" name="notification_option" id="notification_option_0" value="0" required
                                        <?php echo !isset($recipients['notification_option']) || $recipients['notification_option'] == 0 ? 'checked' : null ; ?>
                                    />
                                    <label class="label_text_mini" for="notification_option_0"><?php _e('All customers', 'smart-marketing-addon-sms-order');?></label>
                                </p>
                                <p class="label_text_mini" style="margin-bottom: 20px;">
                                    <input type="radio" name="notification_option" id="notification_option_1" value="1" <?php checked($recipients['notification_option'], 1);?> />
                                    <label class="label_text_mini" for="notification_option_1"><?php _e('Only customers who ask for it in checkout', 'smart-marketing-addon-sms-order');?></label>
                                </p>

                                <hr>

                                <p class="label_text"><?php _e('Configurations Multibanco (Portuguese payment method)', 'smart-marketing-addon-sms-order');?></p>

                                <p class="label_text_mini">
                                    <input type="checkbox" name="egoi_payment_info" id="egoi_payment_info" value="1"
                                        <?php echo !isset($recipients['egoi_payment_info']) || $recipients['egoi_payment_info'] == 1 ? 'checked' : null;?>
                                    />
                                    <label for="egoi_payment_info"><?php _e('Send SMS to your customers with Multibanco payment information', 'smart-marketing-addon-sms-order');?></label>
                                </p>
                                <p class="label_text_mini" >

                                <?php if (constant("ALTERNATE_WP_CRON") !== false) { ?>
                                    <input type="checkbox" name="egoi_reminders" id="egoi_reminders" value="1"
                                        <?php checked($recipients['egoi_reminders'], 1);?>
                                    />
                                    <label for="egoi_reminders"><?php _e('Send SMS to remind the information for payment Multibanco', 'smart-marketing-addon-sms-order');?></label>

                                    <?php if ( $transactional_email['check_transactional_email'] !== 0) { ?>
                                        <p>
                                        <input type="checkbox" name="egoi_email_reminders" id="egoi_email_reminders" value="1"
                                            <?php checked($recipients['egoi_email_reminders'], 1);?>
                                        />
                                        <label for="egoi_email_reminders"><?php _e('Send Email to remind the information for payment Multibanco', 'smart-marketing-addon-sms-order');?></label>
                                        </p>
                                    <?php } else { ?>
                                        <p>
                                        <input type="checkbox" disabled />
                                        <?php _e('Send Email to remind the information for payment Multibanco', 'smart-marketing-addon-sms-order');?>
                                        </p>
                                        <div style="width: 100%; background-color: white; text-align: center; border: 1px solid #dddddd; margin-top: 10px;">
                                            <p class="label_text_mini"><?php _e('You need to enable Egoi Transacional Email.', 'smart-marketing-addon-sms-order');?>
                                            <a href="<?php echo admin_url() . '/admin.php?page=egoi-4-wp-transactional-email' ?>">
                                                <?php _e('Enable here.', 'smart-marketing-addon-sms-order');?>
                                            </a>
                                            </p>
                                        </div>
                                    <?php } ?>


                                <?php } else { ?>
                                    <p>
                                    <input type="checkbox" disabled />
                                    <?php _e('Send SMS to remind the information for payment Multibanco', 'smart-marketing-addon-sms-order');?>
                                    </p>
                                    <p>
                                    <input type="checkbox" disabled />
                                    <?php _e('Send Email to remind the information for payment Multibanco', 'smart-marketing-addon-sms-order');?>
                                    </p>
                                    <div style="width: 100%; background-color: white; text-align: center; border: 1px solid #dddddd; margin-top: 10px;">
                                        <p class="label_text_mini"><?php _e('You need to enable wp_cron in wp-config, use:', 'smart-marketing-addon-sms-order');?></p>
                                        <pre>define ('ALTERNATE_WP_CRON', true);</pre>
                                    </div>
                                <?php } ?>
                                </p>


                                <hr>

                                <p class="label_text"><?php _e('Configurations Pagseguro (Brazilian Gateway)', 'smart-marketing-addon-sms-order');?></p>

                                <p class="label_text_mini">
                                    <input type="checkbox" name="egoi_payment_info_billet" id="egoi_payment_info_billet" value="1"
                                        <?php echo !isset($recipients['egoi_payment_info_billet']) || $recipients['egoi_payment_info_billet'] == 1 ? 'checked' : null;?>
                                    />
                                    <label for="egoi_payment_info_billet"><?php _e('Send SMS to your customers with PagSeguro payment information', 'smart-marketing-addon-sms-order');?></label>
                                </p>
                                <p class="label_text_mini">
                                <?php if (constant("ALTERNATE_WP_CRON") !== false) { ?>
                                    <input type="checkbox" name="egoi_reminders_billet" id="egoi_reminders_billet" value="1"
                                        <?php checked($recipients['egoi_reminders_billet'], 1);?>
                                    />
                                    <label for="egoi_reminders_billet"><?php _e('Send SMS to remind the payment information of the PagSeguro', 'smart-marketing-addon-sms-order');?></label>

                                    <?php if ( $transactional_email['check_transactional_email'] !== 0) { ?>
                                        <p>
                                        <input type="checkbox" name="egoi_email_reminders_billet" id="egoi_email_reminders_billet" value="1"
                                            <?php checked($recipients['egoi_email_reminders_billet'], 1);?>
                                        />
                                        <label for="egoi_email_reminders_billet"><?php _e('Send Email to remind the payment information of the PagSeguro', 'smart-marketing-addon-sms-order');?></label>
                                        </p>
                                    <?php } else { ?>
                                        <p>
                                        <input type="checkbox" disabled />
                                        <?php _e('Send Email to remind the payment information of the PagSeguro', 'smart-marketing-addon-sms-order');?>
                                        </p>
                                        <div style="width: 100%; background-color: white; text-align: center; border: 1px solid #dddddd; margin-top: 10px;">
                                            <p class="label_text_mini"><?php _e('You need to enable Egoi Transacional Email.', 'smart-marketing-addon-sms-order');?>
                                            <a href="<?php echo admin_url() . '/admin.php?page=egoi-4-wp-transactional-email' ?>">
                                                <?php _e('Enable here.', 'smart-marketing-addon-sms-order');?>
                                            </a>
                                            </p>
                                        </div>
                                    <?php } ?>

                                <?php } else { ?>
                                    <p>
                                    <input type="checkbox" disabled />
                                    <?php _e('Send SMS to remind the information for PagSeguro payment', 'smart-marketing-addon-sms-order');?>
                                    </p>
                                    <p>
                                    <input type="checkbox" disabled />
                                    <?php _e('Send Email to remind the information for PagSeguro payment', 'smart-marketing-addon-sms-order');?>
                                    </p>
                                    <div style="width: 100%; background-color: white; text-align: center; border: 1px solid #dddddd; margin-top: 10px;">
                                        <p class="label_text_mini"><?php _e('You need to enable wp_cron in wp-config, use:', 'smart-marketing-addon-sms-order');?></p>
                                        <pre>define ('ALTERNATE_WP_CRON', true);</pre>
                                    </div>
                                <?php } ?>
                                </p>

                                <hr>
                                <p class="label_text"><?php _e('Choose the amount of time to send the reminder.', 'smart-marketing-addon-sms-order');?></p>

                                <select name="egoi_reminders_time" id="egoi_reminders_time" class="e-goi-option-select-admin-forms" style="width: 49%;">
                                    <?php $recipients['egoi_reminders_time'] = empty($recipients['egoi_reminders_time']) ? 48 : $recipients['egoi_reminders_time']; ?>
                                    <?php foreach ($reminder_times as $value) { ?>
                                        <option value="<?=$value?>" <?php selected($value, $recipients['egoi_reminders_time']);?>><?=$value?>h</option>
                                    <?php } ?>
                                </select>

                                <hr>
                                <p class="label_text"><?php _e('Choose the amount of time to send the email reminder.', 'smart-marketing-addon-sms-order');?></p>

                                <select name="egoi_email_reminders_time" id="egoi_email_reminders_time" class="e-goi-option-select-admin-forms" style="width: 49%;">
                                    <?php $recipients['egoi_reminders_time'] = empty($recipients['egoi_email_reminders_time']) ? 48 : $recipients['egoi_email_reminders_time']; ?>
                                    <?php foreach ($reminder_times as $value) { ?>
                                        <option value="<?=$value?>" <?php selected($value, $recipients['egoi_email_reminders_time']);?>><?=$value?>h</option>
                                    <?php } ?>
                                </select>


                                <?php submit_button(); ?>
                            </form>
                        </td>
                        <td valign="top">
                            <div id="egoi-account-info" style="background-color: #ffffff; padding: 1px 20px 15px 20px; border: 1px solid #dddddd">
                                <p class="label_text_mini" style="font-size: 16px;">
                                    <?php _e('E-goi account information', 'smart-marketing-addon-sms-order');?>
                                </p>
                                <p class="label_text_mini">
                                    <?php echo __('Current balance of your E-goi account', 'smart-marketing-addon-sms-order').': ';?>
                                    <span style="background-color: #00aeda; color: white; padding: 5px; border-radius: 2px; margin-left: 5px; font-size: 14px;"><?=$balance?></span>
                                </p>
                            </div>
                            <div id="test-sms">
                                <form action="#" method="post">
                                    <?php
                                    if ($sender_option['sender_hash']) {
                                        $disabled = null;
                                    } else {
                                        $disabled = array('disabled' => 1);
                                    }
                                    ?>
                                    <?php wp_nonce_field( 'form-sms-order-tests' ); ?>
                                    <input name="form_id" type="hidden" value="form-sms-order-tests" />

                                    <p class="label_text">
                                        <?php _e('Send a test SMS', 'smart-marketing-addon-sms-order');?>
                                    </p>
                                    <p class="label_text_mini">
                                        <?php _e('Send a test SMS to verify that your service is active. This test will have the cost of a normal SMS. You need to have balance on your E-goi account to perform this test. ', 'smart-marketing-addon-sms-order');?>
                                    </p>

                                    <select name="recipient_prefix" class="e-goi-option-select-admin-forms" style="width: 49%; float: left;" required <?php echo $disabled ? 'disabled' : null;?> >
                                        <?php
                                        foreach (unserialize(COUNTRY_CODES) as $key => $value) {
                                            $string = ucwords(strtolower($value['country_pt']))." (+".$value['prefix'].")";
                                            ?>
                                            <option value="<?=$value['prefix']?>" ><?=$string?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <input type="text" name="recipient_phone" class="regular-text" style="width: 50%; height: 38px;" required
                                        <?php echo $disabled ? 'disabled' : null;?>
                                    />

                                    <br>

                                    <textarea name="message" style="width: 100%;" rows="5" required <?php echo $disabled ? 'disabled' : null;?>></textarea>

                                    <button type="submit" class="button send-sms-button"><?php _e('Send SMS', 'smart-marketing-addon-sms-order');?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>



<!-- wrap SMS Texts -->
<div class="wrap tab wrap-addon" id="tab-sms-texts">
    <div class="wrap egoi4wp-settings">
        <div class="row">

            <?php
            if (isset($_POST['form_id']) && $_POST['form_id'] == 'form-sms-order-texts') {
                if ($result) {
                    $this->helper->smsonw_admin_notice_success();
                } else {
                    $this->helper->smsonw_admin_notice_error();
                }
            }
            ?>

            <div class="main-content col col-12" style="margin:0 0 20px;">

                <p class="label_text"><?php _e('Select the language', 'smart-marketing-addon-sms-order');?></p>

                <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-texts">
                    <?php wp_nonce_field( 'form-sms-order-texts' ); ?>
                    <input name="form_id" type="hidden" value="form-sms-order-texts" />
                    <div id="sms_texts_select_lang">
                        <select class="e-goi-option-select-admin-forms" style="width: 400px;" name="sms_text_language" id="sms_text_language">
                            <option value="" disabled selected>
                                <?php _e('Selected the language', 'smart-marketing-addon-sms-order');?>
                            </option>
                            <?php foreach ($this->helper->smsonw_get_languages() as $code => $language) { ?>
                                <option value="<?=$code?>" <?php selected($_POST['sms_text_language'], $code);?>>
                                    <?=$language?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div id="sms_texts_tags">
                        <p class="label_text" style="margin-bottom: 20px;">
		                    <?php _e('You can edit the SMS messages of each order status', 'smart-marketing-addon-sms-order');?>
                            <br>
                            <span style="font-size: 13px;"><?php _e('If you want to include custom information in your SMS, use the following tags', 'smart-marketing-addon-sms-order');?></span>
                        </p>
                        <?php foreach ($this->helper->sms_text_tags as $tag_name => $tag_cod) { ?>
                            <button type="button" class="button button-default sms_texts_tags_button" data-text-cod="<?=$tag_cod?>">
                                <?php echo ucwords(str_replace('_', ' ', $tag_name)); ?>
                            </button>
                        <?php } ?>
                    </div>

                    <?php foreach ($this->helper->smsonw_get_languages() as $code => $lang) { ?>
                        <div id="sms_order_texts_<?=$code?>">
                            <table border="0" class="widefat striped" style="max-width: 900px;">
                                <thead>
                                    <tr>
                                        <th><?php _e('Order Status', 'smart-marketing-addon-sms-order');?></th>
                                        <th><?php _e('Customer', 'smart-marketing-addon-sms-order');?></th>
                                        <th><?php _e('Admin', 'smart-marketing-addon-sms-order');?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($this->helper->smsonw_get_order_statuses() as $cod => $name) { ?>
                                    <tr>
                                        <td><?php _e($name, 'smart-marketing-addon-sms-order');?></td>
                                        <td>
                                            <?php
                                            $text = '';
                                            if (isset($texts[$code]["egoi_sms_order_text_customer_".$cod]) && trim($texts[$code]["egoi_sms_order_text_customer_".$cod]) != '') {
                                                $text = $texts[$code]["egoi_sms_order_text_customer_" . $cod];
                                            } else {
                                                $text = $this->helper->sms_text_new_status[$code]['egoi_sms_order_text_customer_'.$cod];
                                            }
                                            ?>
                                            <textarea name="egoi_sms_order_text_customer_<?=$cod?>" cols="40" rows="4" id="egoi_sms_order_text_customer_<?=$cod?>"><?=$text?></textarea>
                                        </td>
                                        <td>
                                            <textarea name="egoi_sms_order_text_admin_<?=$cod?>" cols="40" rows="4" id="egoi_sms_order_text_admin_<?=$cod?>"><?=$texts[$code]["egoi_sms_order_text_admin_".$cod]?></textarea>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <?php submit_button(); ?>
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
</div>




<!-- wrap SMS Payment Info Texts -->
<div class="wrap tab wrap-addon" id="tab-sms-payment-texts">
    <div class="wrap egoi4wp-settings">
        <div class="row">

            <?php
            if (isset($_POST['form_id']) && $_POST['form_id'] == 'form-sms-order-payment-texts') {
                if ($result) {
                    $this->helper->smsonw_admin_notice_success();
                } else {
                    $this->helper->smsonw_admin_notice_error();
                }
            }
            ?>

            <div class="main-content col col-12" style="margin:0 0 20px;">

                <p class="label_text"><?php _e('This plugin is integrated with Multibanco (euPago, Ifthenpay, easypay, Hipay Compra Facil, Sibs, LusoPay), Payshop (euPago), WooCommerce PagSeguro', 'smart-marketing-addon-sms-order');?></p>

                <p class="label_text"><?php _e('Select payment method', 'smart-marketing-addon-sms-order');?></p>

                <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-payment-texts">
                    <?php wp_nonce_field( 'form-sms-order-payment-texts' ); ?>
                    <input name="form_id" type="hidden" value="form-sms-order-payment-texts" />
                    <div id="sms_select_payment_method">
                        <select class="e-goi-option-select-admin-forms" style="width: 400px;" name="sms_payment_method" id="sms_payment_method">
                            <option value="" disabled selected>
                                <?php _e('Seclect payment method', 'smart-marketing-addon-sms-order');?>
                            </option>
                            <?php foreach ($this->helper->smsonw_get_payment_methods() as $code => $method) { ?>
                                <option value="<?=$code?>" <?php selected($_POST['sms_payment_method'], $code);?>>
                                    <?=$method?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div id="sms_payment_texts_tags">
                        <p class="label_text" style="margin-bottom: 20px;">
                            <?php _e('You can edit the SMS messages sent by each payment method', 'smart-marketing-addon-sms-order');?>
                            <br>
                            <span style="font-size: 13px;"><?php _e('If you want to include custom information in your SMS, use the following tags', 'smart-marketing-addon-sms-order');?></span>
                        </p>
                        <?php foreach ($this->helper->sms_text_tags as $tag_name => $tag_cod) { ?>
                            <button type="button" class="button button-default sms_texts_tags_button" data-text-cod="<?=$tag_cod?>">
                                <?php echo ucwords(str_replace('_', ' ', $tag_name)); ?>
                            </button>
                        <?php } ?>
                    </div>

                    <?php foreach ($this->helper->smsonw_get_payment_methods() as $method_code => $method) { ?>
                    <div id="sms_order_payment_texts_<?=$method_code?>">
                        <table border="0" class="widefat striped" style="max-width: 900px;">
                            <thead>
                            <tr>
                                <th><?php _e('Language', 'smart-marketing-addon-sms-order');?></th>
                                <th><?php _e('First Message', 'smart-marketing-addon-sms-order');?></th>
                                <th><?php _e('Reminder', 'smart-marketing-addon-sms-order');?></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach ($this->helper->smsonw_get_languages() as $lang_code => $lang) { ?>
                                <tr>
                                    <td><?php _e($lang, 'smart-marketing-addon-sms-order');?></td>
                                    <td>
                                        <?php
                                        $text = '';
                                        if (isset($payment_texts[$method_code]["egoi_sms_order_payment_text_".$lang_code]) && trim($payment_texts[$method_code]["egoi_sms_order_payment_text_".$lang_code]) != '') {
                                            $text = $payment_texts[$method_code]["egoi_sms_order_payment_text_".$lang_code];
                                        } else {
                                            $text = $this->helper->sms_payment_info[$method_code]['first'][$lang_code];
                                        }
                                        ?>
                                        <textarea name="egoi_sms_order_payment_text_<?=$lang_code?>" cols="40" rows="4" id="egoi_sms_order_payment_text_<?=$lang_code?>"><?=$text?></textarea>
                                    </td>
                                    <td>
                                        <?php
                                        $text = '';
                                        if (isset($payment_texts[$method_code]["egoi_sms_order_reminder_text_".$lang_code]) && trim($payment_texts[$method_code]["egoi_sms_order_reminder_text_".$lang_code]) != '') {
                                            $text = $payment_texts[$method_code]["egoi_sms_order_reminder_text_".$lang_code];
                                        } else {
                                            $text = $this->helper->sms_payment_info[$method_code]['reminder'][$lang_code];
                                        }
                                        ?>
                                        <textarea name="egoi_sms_order_reminder_text_<?=$lang_code?>" cols="40" rows="4" id="egoi_sms_order_reminder_text_<?=$lang_code?>"><?=$text?></textarea>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                        <?php submit_button(); ?>
                    </div>
                    <?php } ?>

                </form>
            </div>
        </div>
    </div>
</div>


<!-- wrap Email Payment Info Texts -->
<div class="wrap tab wrap-addon" id="tab-sms-emailpayment-texts">
    <div class="wrap egoi4wp-settings">
        <div class="row">
            <?php
                if (isset($_POST['form_id']) && $_POST['form_id'] == 'form-email-order-payment-texts') {
                    if ($result) {
                        $this->helper->smsonw_admin_notice_success();
                    } else {
                        $this->helper->smsonw_admin_notice_error();
                    }
                }
            ?>
            <div class="main-content col col-12" style="margin:0 0 20px;">

                <p class="label_text"><?php _e('This plugin is integrated with Multibanco (euPago, Ifthenpay, easypay, Hipay Compra Facil, Sibs, LusoPay), Payshop (euPago), WooCommerce PagSeguro', 'smart-marketing-addon-sms-order');?></p>

                <p class="label_text"><?php _e('Select payment method', 'smart-marketing-addon-sms-order');?></p>

                <form action="#" method="post" class="form-sms-order-config" id="form-email-order-payment-texts">
                    <?php wp_nonce_field( 'form-email-order-payment-texts' ); ?>
                    <input name="form_id" type="hidden" value="form-email-order-payment-texts" />
                    <div id="sms_select_payment_method">
                        <select class="e-goi-option-select-admin-forms" style="width: 400px;" name="email_payment_method" id="email_payment_method">
                            <option value="" disabled selected>
                                <?php _e('Seclect payment method', 'smart-marketing-addon-sms-order');?>
                            </option>
                            <?php foreach ($this->helper->smsonw_get_payment_methods() as $code => $method_email) { ?>
                                <option value="<?=$code?>" <?php selected($_POST['sms_payment_method'], $code);?>>
                                    <?=$method_email?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div id="email_payment_texts_tags" >
                        <p class="label_text" style="margin-bottom: 20px;">
                            <?php _e('You can edit the Email message sent by each payment method', 'smart-marketing-addon-sms-order');?>
                            <br>
                            <span style="font-size: 13px;"><?php _e('If you want to include custom information in your Email, use the following tags', 'smart-marketing-addon-sms-order');?></span>
                        </p>
                        <?php foreach ($this->helper->email_text_tags as $tag_name_email => $tag_cod) { ?>
                            <button type="button" class="button button-default sms_texts_tags_button" data-text-cod="<?=$tag_cod?>">
                                <?php echo ucwords(str_replace('_', ' ', $tag_name_email)); ?>
                            </button>
                        <?php } ?>
                    </div>
                    <br>

                    <?php foreach ($this->helper->smsonw_get_payment_methods() as $method_code_email => $method_email) { ?>
                    <div id="email_order_payment_texts_<?=$method_code_email?>">
                        <table border="0" class="widefat striped" style="max-width: 900px;">
                            <thead>
                            <tr>
                                <th><?php _e('Language', 'smart-marketing-addon-sms-order');?></th>
                                <th><?php _e('Reminder', 'smart-marketing-addon-sms-order');?></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach ($this->helper->smsonw_get_languages() as $lang_code_email => $lang) { ?>
                                <tr>
                                    <td><?php _e($lang, 'smart-marketing-addon-sms-order');?></td>
                                    <td>
                                        <?php
                                        $text = '';
                                        if (isset($payment_email_texts[$method_code_email]["egoi_sms_order_reminder_email_text_".$lang_code_email]) && trim($payment_email_texts[$method_code_email]["egoi_sms_order_reminder_email_text_".$lang_code_email]) != '') {
                                            $text = $payment_email_texts[$method_code_email]["egoi_sms_order_reminder_email_text_".$lang_code_email];
                                        } else {
                                            $text = $this->helper->email_payment_info[$method_code_email]['reminder'][$lang_code_email];
                                        }
                                        ?>
                                        <textarea name="egoi_sms_order_reminder_email_text_<?=$lang_code_email?>" cols="40" rows="8" id="egoi_sms_order_reminder_email_text_<?=$lang_code_email?>" style="width: 720px;"><?=$text?></textarea>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                        <?php submit_button(); ?>
                    </div>
                    <?php } ?>

                </form>

            </div>
        
        </div>
    </div>
</div>


<!-- wrap SMS FOLLOW PRICES -->
<div class="wrap tab wrap-addon" id="tab-sms-follow-price">
    <div class="wrap egoi4wp-settings" id="tab-forms">
        <?php
        if (isset($_POST['form_id']) && $_POST['form_id'] == 'form-sms-follow-price') {
            if ($result) {
                $this->helper->smsonw_admin_notice_success();
            } else {
                $this->helper->smsonw_admin_notice_error();
            }
        }
        ?>
        <div class="main-content col col-12" style="margin:0 0 20px;">
            <p class="label_text"><?php _e('Use this plugin to send Follow price SMS.', 'smart-marketing-addon-sms-order'); ?></p>
            <br>
            <form action="#" method="post" class="form-sms-follow-price" id="form-sms-follow-price">
                <?php wp_nonce_field( 'form-sms-follow-price' ); ?>
                <input name="form_id" type="hidden" value="form-sms-follow-price" />

                <div id="sms_follow_price">
                    <table border="0" class="widefat striped" style="max-width: 900px;">
                         <thead>
                            <tr>
                                <th><?php _e('Configurations', 'smart-marketing-addon-sms-order');?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span><?php _e('Positions', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <select name="follow_price_position" id="egoi_sms_follow_price_position" style="min-width: 400px;width: 100%;">
                                        <?php

                                        foreach ($this->helper->smsonw_admin_follow_price_positions() as $k => $v) {
                                            $selected = '';

                                            if(isset($follow_price['follow_price_position'])){
                                                $selected = ($follow_price['follow_price_position'] == $k) ? 'selected' : '';
                                            }

                                            echo '<option ' .$selected. ' value="'.$k.'">';
                                            _e($v, "smart-marketing-addon-sms-order");
                                            echo '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><span><?php _e('Message', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <textarea name="follow_price_message" id="egoi_sms_follow_price_message" style="min-width: 400px;width: 100%;"><?php
                                            echo (isset($follow_price["follow_price_message"]) && trim($follow_price["follow_price_message"]) != '') ? $follow_price["follow_price_message"] : '';
                                    ?></textarea>
                                    <p>
                                        <?php _e('Use <b>%link%</b> to choose the position of the link otherwise the link will be placed at the end','smart-marketing-addon-sms-order');?><br>
	                                    <?php _e('Use <b>%shop_name%</b> for shop name display.','smart-marketing-addon-sms-order');?><br>
	                                    <?php _e('Use <b>%product%</b> for product name.','smart-marketing-addon-sms-order');?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Title Pop', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <div>
                                        <input type="text" id="follow_title_pop" name="follow_title_pop" style="width: 100%;"
                                               value="<?php
			                                   echo (isset($follow_price["follow_title_pop"]) ) ? $follow_price["follow_title_pop"] : ''; ?>"
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Text on button', 'egoi_sms_follow_price_button_name');?></span></td>
                                <td>
                                    <div>
                                        <input type="text" id="follow_price_button_name" name="follow_price_button_name" style="width: 100%;"
                                               value="<?php
                                            echo (isset($follow_price["follow_price_button_name"]) ) ? $follow_price["follow_price_button_name"] : ''; ?>"
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Enabled', 'egoi_sms_follow_price_enable_title');?></span></td>
                                <td>
                                    <div>
                                      <input type="checkbox" id="follow_price_enable" name="follow_price_enable"
                                       <?php
                                        echo (isset($follow_price["follow_price_enable"]) && $follow_price["follow_price_enable"] == "on") ? 'checked' : ''; ?>
                                      >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Shortener', 'egoi_sms_follow_price_enable_title');?></span></td>
                                <td>
                                    <div>
                                        <input type="checkbox" id="follow_price_shortener" name="follow_price_shortener"
                                            <?php
                                            echo (isset($follow_price["follow_price_shortener"]) && $follow_price["follow_price_shortener"] == "on") ? 'checked' : ''; ?>
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <b><?php _e( 'Styles for dialog', 'egoi_sms_follow_price_enable_title' ); ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div>
                                        <div class="smsnf-input-group">
                                            <label for="bar-text-color" style="font-size: 13px;"><?php _e( 'Background Color', 'egoi_sms_follow_price_enable_title' ); ?></label>

                                            <div class="colorpicker-wrapper">
                                                <div style="background-color:<?= esc_attr( $follow_price['follow_background_color'] ) ?>" class="view" ></div>
                                                <input id="follow_background_color" type="text" name="follow_background_color" value="<?= esc_attr( $follow_price['follow_background_color'] ) ?>"  autocomplete="off" />
                                                <p><?= _e( 'Select Color', 'egoi_sms_follow_price_enable_title' ) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div>
                                        <div class="smsnf-input-group">
                                            <label for="bar-text-color" style="font-size: 13px;"><?php _e( 'Text Color', 'egoi_sms_follow_price_enable_title' ); ?></label>

                                            <div class="colorpicker-wrapper">
                                                <div style="background-color:<?= esc_attr( $follow_price['follow_text_color'] ) ?>" class="view" ></div>
                                                <input id="follow_text_color" type="text" name="follow_text_color" value="<?= esc_attr( $follow_price['follow_text_color'] ) ?>"  autocomplete="off" />
                                                <p><?= _e( 'Select Color', 'egoi_sms_follow_price_enable_title' ) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <div>
                                        <div class="smsnf-input-group">
                                            <label for="bar-text-color" style="font-size: 13px;"><?php _e( 'Button Color', 'egoi_sms_follow_price_enable_title' ); ?></label>

                                            <div class="colorpicker-wrapper">
                                                <div style="background-color:<?= esc_attr( $follow_price['follow_button_color'] ) ?>" class="view" ></div>
                                                <input id="follow_button_color" type="text" name="follow_button_color" value="<?= esc_attr( $follow_price['follow_button_color'] ) ?>"  autocomplete="off" />
                                                <p><?= _e( 'Select Color', 'egoi_sms_follow_price_enable_title' ) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <div>
                                        <div class="smsnf-input-group">
                                            <label for="bar-text-color" style="font-size: 13px;"><?php _e( 'Button Text Color', 'egoi_sms_follow_price_enable_title' ); ?></label>

                                            <div class="colorpicker-wrapper">
                                                <div style="background-color:<?= esc_attr( $follow_price['follow_button_text_color'] ) ?>" class="view" ></div>
                                                <input id="follow_button_text_color" type="text" name="follow_button_text_color" value="<?= esc_attr( $follow_price['follow_button_text_color'] ) ?>"  autocomplete="off" />
                                                <p><?= _e( 'Select Color', 'egoi_sms_follow_price_enable_title' ) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>


                        </tbody>
                    </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
</div>


<!-- wrap SMS Help -->
<div class="wrap tab wrap-addon" id="tab-sms-help">
    <div class="wrap egoi4wp-settings" id="tab-forms">
        <div class="row">
            <div class="main-content col col-12" style="margin:0 0 20px;">

                <p class="help-title"><?php _e('First steps', 'smart-marketing-addon-sms-order');?></p>
                <p><?php _e('Help information to start sending SMS notifications to your customers.', 'smart-marketing-addon-sms-order');?></p>
                <br>

                <p class="help-title"><?php _e('1. Transactional Activation', 'smart-marketing-addon-sms-order');?></p>
                <p><?php _e('In order to send SMS notifications, you must first activate Transactional in your E-goi account.', 'smart-marketing-addon-sms-order');?>
                    <br>
                    <?php _e('To activate Transactional in your E-goi account, go to the Apps menu and choose to activate Slingshot.', 'smart-marketing-addon-sms-order');?>
                    <a href="<?php _e('https://helpdesk.e-goi.com/index.php?type=page&urlcode=708772&title=Sending-transactional-messages-with-E-gois-Slingshot', 'smart-marketing-addon-sms-order');?>" target="_blank">
                        <?php _e('See more help', 'smart-marketing-addon-sms-order');?>
                    </a>
                </p>
                <br>

                <p class="help-title"><?php _e('2. Adding a sender', 'smart-marketing-addon-sms-order');?></p>
                <p>
                    <?php _e('In your E-goi account you must add a Sender to send sms,', 'smart-marketing-addon-sms-order');?>
                    <a href="<?php _e('https://helpdesk.e-goi.com/index.php?type=page&urlcode=694373&title=Adding-a-sender', 'smart-marketing-addon-sms-order');?>" target="_blank">
                        <?php _e('See how!', 'smart-marketing-addon-sms-order');?>
                    </a>
                    <br>
                    <?php
                        _e('(You can add a sender with the number that will appear in the sms sent and allows you to receive replies or text,
                            such as the name of your company or website, 
                            but not allowing you to receive replies)', 'smart-marketing-addon-sms-order');
                    ?>
                </p>
                <br>

                <p class="help-title"><?php _e('3. Add balance', 'smart-marketing-addon-sms-order');?></p>
                <p>
                    <?php
                        _e('All free E-goi Plans have included 10 SMS and all Starter Plans have 50 included SMS that you can use to test the plugin. If you do not have enough balance available, log in to your E-goi Account, go to the menu related to your account (top, right) 
                            and the Balance information choose Top Up.', 'smart-marketing-addon-sms-order');
                    ?>
                </p>
                <br>

                <p class="help-title"><?php _e('4. Select SMS Sender E-goi', 'smart-marketing-addon-sms-order');?></p>
                <p>
                    <?php
                        _e('If you have already done the above steps, you can go back to the SMS plugin to select the E-goi SMS 
                            Sender previously entered into the E-goi account and complete the setup process. 
                            This sender will be the one used to send your SMS notifications.', 'smart-marketing-addon-sms-order');
                    ?>
                </p>
                <br>

                <p class="help-title"><?php _e('SMS Tracking', 'smart-marketing-addon-sms-order');?></p>
                <p>
                    <?php
                    _e('To send SMS with the shipping info, you must configure the carriers you want to use and their tracking or website URLs. You need to include the customization codes (tracking_code, tracking_url, tracking_name) in the order statuses and messages you want to send. The plugin will add a widget on the order page where you can choose which carrier will be used for the shipment and its tracking code. This data will be used in SMS.', 'smart-marketing-addon-sms-order');
                    ?>
                </p>
                <br>

                <p class="help-title"><?php _e('More information', 'smart-marketing-addon-sms-order');?></p>
                <p>
                    <?php
                        _e('The option of sending SMS notification with the Multibanco (Entity, Reference, Value) 
                            for instant cash payment is only compatible with Eupago, 
                            Ifthenpay and easypay portuguese payment gateways.', 'smart-marketing-addon-sms-order');
                    ?>
                    <br>
                    <?php
                        _e('Option of sending SMS with the data of Pagseguro is only 
                            compatible with the plugin WooCommerce PagSeguro', 'smart-marketing-addon-sms-order');
                    ?>
                    <br>
                    <?php
                        _e('The balance amount displayed in the plugin may temporarily show differences, 
                            relative to the balance within your E-goi account.', 'smart-marketing-addon-sms-order');
                    ?>
                </p>
                <p>
                    <?php _e('You can consult the article of our blog about this plugin.', 'smart-marketing-addon-sms-order'); ?>
                    <a href="<?php _e('https://blog.e-goi.com/alerts-by-sms-for-your-ecommerce-orders/', 'smart-marketing-addon-sms-order');?>" target="_blank">
                        <?php _e('View blog post', 'smart-marketing-addon-sms-order');?>
                    </a>
                    <br>
                    <?php _e('You can check at this link the cost of each sms and E-goi account plans.', 'smart-marketing-addon-sms-order'); ?>
                    <a href="<?php _e('https://www.e-goi.com/pricing/', 'smart-marketing-addon-sms-order');?>" target="_blank">
                        <?php _e('Check Rates', 'smart-marketing-addon-sms-order');?>
                    </a>
                </p>


            </div>
        </div>
    </div>
</div>


<!-- wrap SMS Payment Info Texts -->
<div class="wrap tab wrap-addon" id="tab-sms-tracking-texts">
    <div class="wrap egoi4wp-settings" id="tab-forms">
        <div class="row">

            <div id="tracking_texts_message">
                <?php
                if (isset($_POST['form_id']) && $_POST['form_id'] == 'form-sms-order-tracking-texts') {
                    if ($result) {
                        $this->helper->smsonw_admin_notice_success();
                    } else {
                        $this->helper->smsonw_admin_notice_error();
                    }
                }
                ?>
            </div>

            <div class="main-content col col-12" style="margin:0 0 20px;">

                <p class="label_text"><?php _e('Use this plugin to send SMS with information about shipping orders. You can use the carriers already configured in your WooCommerce or insert the ones you want.', 'smart-marketing-addon-sms-order');?></p>

                <p class="label_text"><?php _e('Carriers already configured in your WooCommerce. Add tracking url or website.', 'smart-marketing-addon-sms-order');?></p>
                <br/>
                <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-tracking-texts">
                    <?php wp_nonce_field( 'form-sms-order-tracking-texts' ); ?>
                    <input name="form_id" type="hidden" value="form-sms-order-tracking-texts" />

                    <div id="sms_order_tracking_urls">
                        <table border="0" class="widefat striped" style="max-width: 900px;">
                            <thead>
                                <tr>
                                    <th><?php _e('Carriers Name', 'smart-marketing-addon-sms-order');?></th>
                                    <th><?php _e('Carriers URL', 'smart-marketing-addon-sms-order');?>&nbsp;&nbsp;<a href="#" class="egoi_tooltip" id="help_carrier_url"><i class="qtip tip-right" data-tip=" <?php echo __('This url will be replacing the %tracking_url% shortcode.', 'smart-marketing-addon-sms-order'); ?>">?</i></a></th>

                                </tr>
                            </thead>
                            <tbody>

                            <?php
                            $urls = $this->helper->smsonw_get_tracking_carriers_urls();
                            ?>

                            <?php foreach ($this->helper->smsonw_get_tracking_carriers() as $tracking_code_name => $tracking_name) { ?>
                                <tr>
                                    <td><span><?=$tracking_name?></span></td>
                                    <td>
                                        <input name="<?=$tracking_code_name?>" id="egoi_sms_order_tracking_<?=$tracking_code_name?>" style="min-width: 400px;width: 100%;" value="<?php echo isset($urls[$tracking_code_name])?$urls[$tracking_code_name]:''; ?>"/>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                        <br/>
                        <p class="label_text"><?php _e('Need more Carriers? Please add it here. Insert tracking url or website.', 'smart-marketing-addon-sms-order');?></p>
                        <br/>
                        <div class="" id="egoi_custom_carrier">
                            <table border="0" class="widefat striped" style="max-width: 900px;">
                                <thead>
                                <tr>
                                    <th><?php _e('Carriers Name', 'smart-marketing-addon-sms-order');?></th>
                                    <th><?php _e('Carriers URL', 'smart-marketing-addon-sms-order');?>&nbsp;&nbsp;<a href="#" class="egoi_tooltip" id="help_carrier_url"><i class="qtip tip-right" data-tip=" <?php echo __('This url will be replacing the %tracking_url% shortcode.', 'smart-marketing-addon-sms-order'); ?>">?</i></a></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody id = "custom_carriers_rows" >
                                <?php
                                    $custom_carriers = $this->helper->smsonw_get_custom_tracking_carriers();
                                ?>
                                <tr>
                                    <td>
                                        <input name="add_egoi_sms_order_tracking_name" id="add_egoi_sms_order_tracking_name" style="width: 100%;" value=""/>
                                    </td>
                                    <td>
                                        <input name="add_egoi_sms_order_tracking_url" id="add_egoi_sms_order_tracking_url" style="min-width: 400px;width: 100%;" value=""/>
                                    </td>
                                    <td>
                                        <div class="button" id="add_egoi_sms_order_tracking_button">+</div>
                                    </td>
                                </tr>

                                <?php foreach ($custom_carriers as $carrier){ ?>

                                    <tr id = "custom_carrier_<?php echo $carrier['carrier']; ?>">
                                        <td>
                                            <span style="width: 100%;" value=""><?php echo $carrier['carrier']; ?></span>
                                        </td>
                                        <td>
                                            <span style="min-width: 400px;width: 100%;" value=""><?php echo $carrier['url']; ?></span>
                                        </td>
                                        <td>
                                            <div class="button remove_carrier remove-button" id="remove_custom_carrier_<?php echo $carrier['carrier']; ?>">x</div>
                                        </td>
                                    </tr>

                                <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <?php submit_button(); ?>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once  plugin_dir_path( __FILE__ ) . 'smart-marketing-addon-sms-order-admin-config-abandonned-cart.php'?>