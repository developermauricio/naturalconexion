<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/admin
 * @author     E-goi <egoi@egoi.com>
 */
class Smart_Marketing_Addon_Sms_Order_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * The ID of parent of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $parent_plugin_name    The ID of parent of this plugin.
     */
    private $parent_plugin_name = 'egoi-for-wp';

    private $apikey;

    protected $helper;

    public $sms_sent = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

	    $this->helper = new Smart_Marketing_Addon_Sms_Order_Helper();

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$apikey = get_option('egoi_api_key');
		$this->apikey = $apikey['api_key'];

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function smsonw_enqueue_styles() {

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smart-marketing-addon-sms-order-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function smsonw_enqueue_scripts($hook) {

        if(strpos($hook,'post.php') !== false || strpos($hook,'smart-marketing-addon-sms-order-config') !== false) {
            wp_enqueue_script('smsonw-meta-box-ajax-script', plugin_dir_url(__FILE__) . 'js/smsonw_order_action_sms_meta_box.js', array('jquery'));
            wp_localize_script('smsonw-meta-box-ajax-script', 'smsonw_meta_box_ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('egoi_send_order_sms'),
            ));
            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smart-marketing-addon-sms-order-admin.js', array( 'jquery' ), $this->version, false );
            wp_localize_script( $this->plugin_name, 'smsonw_config_ajax_object', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce('egoi_add_custom_carrier'),
            ) );
        }
	}

    /**
     * Add an options page to smart marketing menu
     *
     * @since  1.0.0
     */
    public function smsonw_add_options_page() {
        $this->plugin_screen_hook_suffix = add_submenu_page(
            $this->parent_plugin_name,
            __( 'SMS Notifications', 'smart-marketing-addon-sms-order' ),
            __( 'SMS Notifications', 'smart-marketing-addon-sms-order' ),
            'manage_options',
            'smart-marketing-addon-sms-order-config',
            array( $this, 'smsonw_display_plugin_sms_order_config' )
        );
    }

    /**
     * Render the options page for plugin
     *
     * @since  1.0.0
     */
    public function smsonw_display_plugin_sms_order_config() {
        include_once 'partials/smart-marketing-addon-sms-order-admin-config.php';
    }

	/**
	 * Save sms order configs in wordpress options
	 *
	 * @param $post
	 *
	 * @return bool
	 */
    public function smsonw_process_config_form($post) {

        try {
            $form_id = sanitize_text_field($post['form_id']);
            check_admin_referer($form_id);

            if (isset($form_id) && $form_id == 'form-sms-order-senders') {

                $sender = array (
                    'sender_hash' => sanitize_text_field($post['sender_hash']),
                    'admin_prefix' => filter_var($post['admin_prefix'], FILTER_SANITIZE_NUMBER_INT),
                    'admin_phone' => sanitize_text_field($post['admin_phone'])
                );

                $recipients = array();
                foreach ($this->helper->smsonw_get_order_statuses() as $status => $name) {
                    $recipients = array_merge($recipients, array(
                        'egoi_sms_order_customer_'.$status => $this->helper->smsonw_sanitize_boolean_field('egoi_sms_order_customer_'.$status),
                        'egoi_sms_order_admin_'.$status => $this->helper->smsonw_sanitize_boolean_field('egoi_sms_order_admin_'.$status)
                    ));
                }

                $egoi_reminders_time = (int) filter_var($post['egoi_reminders_time'], FILTER_SANITIZE_NUMBER_INT);
                $egoi_email_reminders_time = (int) filter_var($post['egoi_email_reminders_time'], FILTER_SANITIZE_NUMBER_INT);
//                $egoi_reminders_billet_time = (int) filter_var($post['egoi_reminders_billet_time'], FILTER_SANITIZE_NUMBER_INT);

                $recipients = array_merge($recipients, array(
                    'notification_option' => $this->helper->smsonw_sanitize_boolean_field('notification_option'),
                    'egoi_payment_info' => $this->helper->smsonw_sanitize_boolean_field('egoi_payment_info'),
                    'egoi_reminders' => $this->helper->smsonw_sanitize_boolean_field('egoi_reminders'),
                    'egoi_email_reminders' => $this->helper->smsonw_sanitize_boolean_field('egoi_email_reminders'),
                    'egoi_reminders_time' => empty($egoi_reminders_time) ? 48 : $egoi_reminders_time,
                    'egoi_email_reminders_time' => empty($egoi_email_reminders_time) ? 48 : $egoi_email_reminders_time,
                    'egoi_payment_info_billet' => $this->helper->smsonw_sanitize_boolean_field('egoi_payment_info_billet'),
                    'egoi_reminders_billet' => $this->helper->smsonw_sanitize_boolean_field('egoi_reminders_billet'),
                    'egoi_email_reminders_billet' => $this->helper->smsonw_sanitize_boolean_field('egoi_email_reminders_billet'),
//                    'egoi_reminders_billet_time' => empty($egoi_reminders_billet_time) ? 48 : $egoi_reminders_billet_time
                ));

                update_option('egoi_sms_order_sender', json_encode($sender));
                if($post['sender_hash'] == 'off')
                    delete_option('egoi_sms_order_sender');
                update_option('egoi_sms_order_recipients', json_encode($recipients));

            } else if (isset($form_id) && $form_id == 'form-sms-order-texts') {

                $texts = json_decode(get_option('egoi_sms_order_texts'), true);
                $lang = sanitize_text_field($post['sms_text_language']);

                foreach ($this->helper->smsonw_get_order_statuses() as $status => $name) {
                    if (trim($post['egoi_sms_order_text_customer_'.$status]) != '') {
                        $messages['egoi_sms_order_text_customer_' . $status] = sanitize_textarea_field($post['egoi_sms_order_text_customer_' . $status]);
                    }
                    if (trim($post['egoi_sms_order_text_admin_'.$status]) != '') {
                        $messages['egoi_sms_order_text_admin_' . $status] = sanitize_textarea_field($post['egoi_sms_order_text_admin_' . $status]);

                    }
                }

                $texts[$lang] = $messages;

                update_option('egoi_sms_order_texts', json_encode($texts));

            } else if (isset($form_id) && $form_id == 'form-sms-order-payment-texts') {

                $texts = json_decode(get_option('egoi_sms_order_payment_texts'), true);
                $method = sanitize_text_field($post['sms_payment_method']);

                foreach ($this->helper->smsonw_get_languages() as $code => $lang) {
                    if (trim($post['egoi_sms_order_payment_text_'.$code]) != '') {
                        $messages['egoi_sms_order_payment_text_' . $code] = sanitize_textarea_field($post['egoi_sms_order_payment_text_' . $code]);
                    }
                    if (trim($post['egoi_sms_order_reminder_text_'.$code]) != '') {
                        $messages['egoi_sms_order_reminder_text_' . $code] = sanitize_textarea_field($post['egoi_sms_order_reminder_text_' . $code]);
                    }
                }

                $texts[$method] = $messages;

                update_option('egoi_sms_order_payment_texts', json_encode($texts));

            } else if (isset($form_id) && $form_id == 'form-email-order-payment-texts') {

                $texts = json_decode(get_option('egoi_email_order_payment_texts'), true);
                $method = sanitize_text_field($post['email_payment_method']);

                foreach ($this->helper->smsonw_get_languages() as $code => $lang) {
                    if (trim($post['egoi_sms_order_reminder_email_text_'.$code]) != '') {
                        $messages['egoi_sms_order_reminder_email_text_' . $code] = sanitize_textarea_field($post['egoi_sms_order_reminder_email_text_' . $code]);
                    }
                }

                $texts[$method] = $messages;

                update_option('egoi_email_order_payment_texts', json_encode($texts));

            } else if (isset($form_id) && $form_id == 'form-sms-order-tests') {

                $prefix = filter_var($post['recipient_prefix'], FILTER_SANITIZE_NUMBER_INT);
                $phone = sanitize_text_field($post['recipient_phone']);
                $message = sanitize_textarea_field($post['message']);

	            $recipient = $this->helper->smsonw_get_valid_recipient($phone, null, $prefix);
                $response = $this->helper->smsonw_send_sms($recipient, $message, 'test', 0);

                if (isset($response->errorCode)) {
                    return false;
                }
            } else if (isset($form_id) && $form_id == 'form-sms-order-tracking-texts') {
                unset(
                    $post['_wpnonce'],
                    $post['_wp_http_referer'],
                    $post['form_id']
                );

                update_option('egoi_tracking_carriers_urls', json_encode($post));

            } else if( isset($form_id) && $form_id == 'form-sms-follow-price'){
                unset(
                    $post['_wpnonce'],
                    $post['_wp_http_referer'],
                    $post['form_id']
                );

                update_option('egoi_sms_follow_price', json_encode($post));
            } else if( isset($form_id) && $form_id == 'form-sms-order-abandoned-cart'){
                unset(
                    $post['_wpnonce'],
                    $post['_wp_http_referer'],
                    $post['form_id']
                );

                update_option('egoi_sms_abandoned_cart', json_encode($post));
            }
	        return true;
        } catch (Exception $e) {
            $this->helper->smsonw_save_logs('process_config_form: ' . $e->getMessage());
        }

    }

	/**
	 * Process SMS reminders (CRON every fifteen minutes)
	 */
    public function smsonw_sms_order_reminder() {
        try {

            if (date('G') >= 10 && date('G') <= 22) {

                global $wpdb;

                $table_name = $wpdb->prefix . 'egoi_sms_order_reminders';

                $sql = " SELECT DISTINCT order_id FROM $table_name ";
                $order_ids = $wpdb->get_col($sql);

                $orders = $this->helper->smsonw_get_not_paid_orders('egoi_reminders_time');

                if (isset($orders)) {

                    $recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);
                    $count = 0;

                    foreach ($orders as $order) {

                        if ($count >= 40) {
                            break;
                        }

                        $order_data = $order->get_data();

                        if ($recipient_options['notification_option']) {
                            $sms_notification = (bool)get_post_meta($order->get_id(), 'egoi_notification_option')[0];
                        } else {
                            $sms_notification = 1;
                        }

                        $lang = $this->helper->smsonw_get_lang($order->get_billing_country());
                        $messages = json_decode(get_option('egoi_sms_order_payment_texts'), true);
                        $payment_method = $this->helper->smsonw_get_option_payment_method($order->get_payment_method());
                        $text = isset($messages[$payment_method]['egoi_sms_order_reminder_text_'.$lang])
                            ? $messages[$payment_method]['egoi_sms_order_reminder_text_'.$lang]
                            : $this->helper->sms_payment_info[$payment_method]['reminder'][$lang];
                        $send_message = false;

                        if (!$order->is_paid() && !in_array($order->get_id(), $order_ids) && $sms_notification &&
                            $recipient_options['egoi_reminders'] && array_key_exists($order->get_payment_method(), $this->helper->payment_map)) {

                            $message = $this->helper->smsonw_get_tags_content($order_data, $text);
                            $send_message = $message ? true : false;

                        } else if (!$order->is_paid() && !in_array($order->get_id(), $order_ids) && $sms_notification &&
                            $recipient_options['egoi_reminders_billet'] && $order_data['payment_method'] == 'pagseguro') {

                            $code = $this->smsonw_get_billet_code($order->get_id());
                            if ($code) {
                                $message = $this->helper->smsonw_get_tags_content($order_data, $text, $code);
                                $send_message = $message ? true : false;
                            }
                        }

                        if ($send_message) {
                            $recipient = $this->helper->smsonw_get_valid_recipient($order->billing_phone, $order->billing_country);
                            $this->helper->smsonw_send_sms($recipient, $message, $order->get_status(), $order->get_id());
                            $count++;

                            $wpdb->insert($table_name, array(
                                "time" => current_time('mysql'),
                                "order_id" => $order->get_id()
                            ));
                        }
                    }
                }
            }

        } catch (Exception $e) {
	        $this->helper->smsonw_save_logs('sms_order_reminder: ' . $e->getMessage());
        }
    }

    /**
	 * Process SMS reminders (CRON every fifteen minutes)
	 */
    public function smsonw_email_order_reminder() {
        try {

            global $wpdb;

            $table_name = $wpdb->prefix . 'egoi_email_order_reminders';

            $sql = " SELECT DISTINCT order_id FROM $table_name ";
            $order_ids = $wpdb->get_col($sql);

            $orders = $this->helper->smsonw_get_not_paid_orders('egoi_email_reminders_time');

            if (isset($orders)) {

                $recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);
                $count = 0;

                foreach ($orders as $order) {

                    if ($count >= 40) {
                        break;
                    }

                    $order_data = $order->get_data();

                    $lang = $this->helper->smsonw_get_lang($order->get_billing_country());
                    $messages = json_decode(get_option('egoi_email_order_payment_texts'), true);
                    $payment_method = $this->helper->smsonw_get_option_payment_method($order->get_payment_method());
                    $text = isset($messages[$payment_method]['egoi_sms_order_reminder_email_text_'.$lang])
                        ? $messages[$payment_method]['egoi_sms_order_reminder_email_text_'.$lang]
                        : $this->helper->email_payment_info[$payment_method]['reminder'][$lang];
                    $send_message = false;

                    if (!$order->is_paid() && !in_array($order->get_id(), $order_ids) &&
                        $recipient_options['egoi_email_reminders'] && array_key_exists($order->get_payment_method(), $this->helper->payment_map)) {
                            set_transient( 'teste_egoi_1',$order->get_data());

                        $message = $this->helper->smsonw_get_tags_content($order_data, $text);
                        $send_message = $message ? true : false;

                    } else if (!$order->is_paid() && !in_array($order->get_id(), $order_ids) &&
                        $recipient_options['egoi_email_reminders_billet'] && $order_data['payment_method'] == 'pagseguro') {

                        $code = $this->smsonw_get_billet_code($order->get_id());
                        if ($code) {
                            $message = $this->helper->smsonw_get_tags_content($order_data, $text, $code);
                            $send_message = $message ? true : false;
                        }
                    }

                    if ($send_message && !empty($order_data['billing']['email'])) {
                        
                        $this->helper->smsonw_send_email($order_data['billing']['email'], $message, $order->get_id());
                        $count++;

                        $wpdb->insert($table_name, array(
                            "time" => current_time('mysql'),
                            "order_id" => $order->get_id()
                        ));
                    }
                }
            }

        } catch (Exception $e) {
	        $this->helper->smsonw_save_logs('email_order_reminder: ' . $e->getMessage());
        }
    }

    /**
     * Send SMS when order status changed
     *
     * @param $order_id
     * @return bool
     */
	public function smsonw_order_send_sms_new_status($order_id) {

        if (!$this->sms_sent) {
            $recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);

            if ($recipient_options['notification_option']) {
                $sms_notification = (bool) get_post_meta($order_id, 'egoi_notification_option')[0];
            } else {
                $sms_notification = 1;
            }

            $sender = json_decode(get_option('egoi_sms_order_sender'), true);

            if(empty($sender))
                return false;

            $order = wc_get_order($order_id)->get_data();

            $types = array('admin' => $sender['admin_prefix'] . '-' . $sender['admin_phone']);
            if ($sms_notification) {
                $types['customer'] = $order['billing']['phone'];
            }

            foreach ($types as $type => $phone) {
                $message = $this->helper->smsonw_get_sms_order_message($type, $order);
                if ($message !== false) {
                    $recipient = $type == 'customer' ? $this->helper->smsonw_get_valid_recipient($phone, $order['billing']['country']) : $phone;
                    $this->helper->smsonw_send_sms($recipient, $message, $order['status'], $order['id']);
                }
            }
            return true;
        }
        return false;
	}

	/**
     * Send SMS with payment instructions when order is closed
     *
	 * @param $order_id
	 */
    public function smsonw_order_send_sms_payment_data($order_id) {

	    $recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);

	    $order = wc_get_order($order_id)->get_data();

	    if ($recipient_options['notification_option']) {
		    $sms_notification = (bool) get_post_meta($order_id, 'egoi_notification_option')[0];
	    } else {
		    $sms_notification = 1;
	    }

        $lang = $this->helper->smsonw_get_lang($order['billing']['country']);
        $messages = json_decode(get_option('egoi_sms_order_payment_texts'), true);
        $payment_method = $this->helper->smsonw_get_option_payment_method($order['payment_method']);
        $text = isset($messages[$payment_method]['egoi_sms_order_payment_text_'.$lang])
            ? $messages[$payment_method]['egoi_sms_order_payment_text_'.$lang]
            : $this->helper->sms_payment_info[$payment_method]['first'][$lang];

        if ($sms_notification && $recipient_options['egoi_payment_info'] && array_key_exists($order['payment_method'], $this->helper->payment_map)) {

            $message = $this->helper->smsonw_get_tags_content($order, $text);
            $send_message = $message ? true : false;

        } else if ($sms_notification && $recipient_options['egoi_payment_info_billet'] && $payment_method == 'billet') {

            $code = $this->smsonw_save_billet($order_id);
            if ($code) {
                $message = $this->helper->smsonw_get_tags_content($order, $text, $code);
                $send_message = $message ? true : false;
            }
        }

        if ($send_message) {
            $recipient = $this->helper->smsonw_get_valid_recipient($order['billing']['phone'], $order['billing']['country']);
            $this->helper->smsonw_send_sms($recipient, $message,'order', $order_id);
            $this->sms_sent = true;
        }
    }

	/**
	 * Add SMS meta box to order admin page
	 */
	public function smsonw_order_add_sms_meta_box() {
		add_meta_box(
			'woocommerce-order-my-custom',
			__('Send SMS to buyer', 'smart-marketing-addon-sms-order'),
			array( $this, 'smsonw_order_display_sms_meta_box' ),
			'shop_order',
			'side',
			'core'
		);

	}

    /**
     * Add tracking number meta box to order admin page
     */
    public function smsonw_order_add_track_number_box() {
        add_meta_box(
            'woocommerce-order-my-custom-tracking',
            __('SMS Tracking code', 'smart-marketing-addon-sms-order'),
            array( $this, 'smsonw_order_display_tracking_meta_box' ),
            'shop_order',
            'side',
            'core'
        );
    }

    /**
     * The meta box content
     *
     * @param $post
     */
    public function smsonw_order_display_tracking_meta_box($post) {
        $order = wc_get_order($post->ID)->get_data();
        $codes = $this->helper->smsonw_get_tracking_codes($post->ID);
        $carriers = $this->helper->smsonw_get_tracking_carriers(true);
        ?>
            <div class="wide" id="egoi_tracking_for_sms">
                <input type="hidden" name="egoi_sms_order_id" id="egoi_send_order_sms_order_id"
                       value="<?php echo $order['id']; ?>"/>
                <input type="hidden" name="egoi_sms_order_country" id="egoi_send_order_sms_order_country"
                       value="<?php echo $order['billing']['country']; ?>"/>

                <div class="smsonw-tracking-code__list">
                    <strong><?php echo esc_html_e( 'Tracking code:', 'smart-marketing-addon-sms-order' ); ?></strong>
                    <ul>
                        <?php foreach ($codes as $val){ ?>
                            <li id="<?php echo $val['tracking_code']; ?>">
                                <span class="tracking-code-link"><?php echo $carriers[$val['carrier']].': '?></span>
                                <a href="#" class="tracking-code-link" title="<?php echo $carriers[$val['carrier']]; ?>"><?php echo $val['tracking_code']; ?></a>
                                <a class="egoi_close_x select2-selection__clear" id="tracking-<?php echo $val['tracking_code']; ?>"  href="#" >×</a>
                                </li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="wide" id="egoi_tracking_for_sms_insert" <?php echo (! empty($codes))?'disabled':''; ?> style="<?php echo (! empty($codes))?'display: none;':''; ?>">
                    <label for="egoi-add-tracking-code"><?php esc_html_e( 'Add tracking code', 'smart-marketing-addon-sms-order' ); ?></label>
                    <input type="text" id="egoi-add-tracking-code" value="" style="width: 100%;"/>
                    <div style="display: flex;flex-direction: row;flex-wrap: nowrap;">
                        <select id="egoi_add_tracking_carrier" style="height: 28px !important;width: 100%;box-sizing: border-box;float: left;">
                            <option value=""><?php _e('Select an option...', 'smart-marketing-addon-sms-order'); ?></option>
                            <?php foreach ($carriers as $key => $carrier){ ?>
                                <option value="<?php echo $key; ?>"><?php echo $carrier ?></option>
                            <?php } ?>
                        </select>
                        <div id="egoi_add_tracking" class="button wc-reload" disabled ><span><?php _e('Apply', 'smart-marketing-addon-sms-order'); ?></span></div>
                    </div>
                </div>
            </div>

        <style>
            .egoi_close_x{

                color: #444;
                margin-left: .4em;
                text-decoration: none;
                vertical-align: baseline;
            }
        </style>
        <?php
        }

	/**
	 * The meta box content
	 *
	 * @param $post
	 */
	public function smsonw_order_display_sms_meta_box($post) {

		$recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);
		$order = wc_get_order($post->ID)->get_data();

		if ($recipient_options['notification_option']) {
			$sms_notification = (bool) get_post_meta($post->ID, 'egoi_notification_option')[0];
		} else {
			$sms_notification = 1;
		}

		if ($sms_notification) {
			$recipient = $order['billing']['phone'];
			?>
            <div id="egoi_send_order_sms">
                <input type="hidden" name="egoi_sms_order_id" id="egoi_send_order_sms_order_id"
                       value="<?php echo $order['id']; ?>"/>
                <input type="hidden" name="egoi_sms_order_country" id="egoi_send_order_sms_order_country"
                       value="<?php echo $order['billing']['country']; ?>"/>
                <input type="hidden" name="egoi_sms_recipient" id="egoi_send_order_sms_recipient"
                       value="<?= $recipient ?>"/>
                <p>
                    <label for="egoi_send_order_sms_message"><?php _e('Message', 'smart-marketing-addon-sms-order');?></label><br>
                    <textarea name="egoi_sms_message" id="egoi_send_order_sms_message" style="width: 100%;"></textarea>
                </p>
                <p>
                    <button type="button" class="button" id="egoi_send_order_sms_button"><?php _e('Send', 'smart-marketing-addon-sms-order'); ?></button>
                    <span id="egoi_send_order_sms_error" style="display: none; color: red;"><?php _e('You can\'t send a empty SMS', 'smart-marketing-addon-sms-order');?></span>
                    <span id="egoi_send_order_sms_notice" style="display: none;"><?php _e('Sending... Wait please', 'smart-marketing-addon-sms-order');?></span>
                </p>
            </div>
			<?php
		} else {
		    _e('The customer doesn\'t want to receive sms', 'smart-marketing-addon-sms-order');
        }
	}

	/**
	 * Send SMS and add note to admin order page
	 */
	public function smsonw_order_action_sms_meta_box() {
        check_ajax_referer( 'egoi_send_order_sms', 'security' );

        $cellphone = sanitize_text_field($_POST['recipient']);
        $country = sanitize_text_field($_POST['country']);
        $message = sanitize_textarea_field($_POST['message']);
        $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);

		$recipient = $this->helper->smsonw_get_valid_recipient($cellphone, $country);

		$result = $this->helper->smsonw_send_sms($recipient, $message, 'order', $order_id);

		if (!isset($result->errorCode)) {
			$order = wc_get_order($order_id);
			$order->add_order_note('SMS: '.$message);

			$note = array(
				"message" => 'SMS: '.$message,
				"date" => __('added on', 'smart-marketing-addon-sms-order').' '.current_time(get_option('date_format').' '.get_option('time_format'))
			);
			echo json_encode($note);
		} else {
			echo json_encode($result);
		}
		wp_die();
	}

    /**
     * Add track number
     */
    public function smsonw_order_add_tracking_number() {
        check_ajax_referer( 'egoi_send_order_sms', 'security' );

        $order_id       = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
        $tracking_code  = sanitize_text_field($_POST['code']);
        $carrier        = sanitize_text_field($_POST['carrier']);
        $order          = wc_get_order($order_id);

        if ( method_exists( $order, 'get_meta' ) ) {
            $obj = $order->get_meta( '_tracking_code_egoi' );
        } else {
            $obj = $order->correios_tracking_code;
        }

        $obj = json_decode($obj,true);
        if(json_last_error() !== JSON_ERROR_NONE)
            $obj = [];

        foreach ($obj as $track){
            if(false !== array_search($tracking_code, array_column($track,'tracking_code'))){
                echo json_encode(['ERROR' => 'ALREADY_EXISTS']);
                wp_die();
            }
        }

        $obj[]=[
            'carrier'       => $carrier,
            'tracking_code' => $tracking_code
        ];

        if ( method_exists( $order, 'update_meta_data' ) ) {
            $order->update_meta_data( '_tracking_code_egoi', json_encode($obj) );
            $order->save();
        } else {
            update_post_meta( $order->id, '_tracking_code_egoi', json_encode($obj) );
        }


        $order->add_order_note( sprintf( __( 'Added a E-goi tracking: %s', 'smart-marketing-addon-sms-order' ), $tracking_code ) );
        echo json_encode(['RESPONSE' => 'SUCCESS']);
        wp_die();
    }

    /*
     * Delete tracking number
     * */
    public function smsonw_order_delete_tracking_number(){
        check_ajax_referer( 'egoi_send_order_sms', 'security' );
        $order_id       = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
        $tracking_code  = sanitize_text_field($_POST['code']);
        $order          = wc_get_order($order_id);

        if ( method_exists( $order, 'get_meta' ) ) {
            $obj = $order->get_meta( '_tracking_code_egoi' );
        } else {
            $obj = $order->correios_tracking_code;
        }

        $obj = $this->jsonValidOrEmpryArray($obj);

        $newObjs = [];
        foreach ($obj as $track){
            if(false == array_search($tracking_code, array_column($track,'tracking_code'))){
                continue;
            }
            $newObjs[] = $track;
        }

        if ( method_exists( $order, 'update_meta_data' ) ) {
            $order->update_meta_data( '_tracking_code_egoi', json_encode($newObjs) );
            $order->save();
        } else {
            update_post_meta( $order->id, '_tracking_code_egoi', json_encode($newObjs) );
        }

        $order->add_order_note( sprintf( __( 'Removed a E-goi tracking: %s', 'woocommerce-correios' ), $tracking_code ) );
        echo json_encode(['RESPONSE' => 'SUCCESS']);
        wp_die();
    }

    public function smsonw_add_custom_carrier(){
        check_ajax_referer( 'egoi_add_custom_carrier', 'security' );
        $carrier    = trim($_POST['name']);
        $url        = trim($_POST['url']);
        $obj = get_option('egoi_custom_carriers');


        $obj = $this->jsonValidOrEmpryArray($obj);


        if(false !== array_search($carrier, array_column($obj,'carrier'))){
            echo json_encode(['ERROR' => __('One carrier with the same name already exists!', 'smart-marketing-addon-sms-order')]);
            wp_die();
        }

        $obj[] = [
            'carrier' => $carrier,
            'url' => $url
        ];

        update_option('egoi_custom_carriers', json_encode(array_values($obj)));

        echo json_encode(['RESPONSE' => 'SUCCESS']);
        wp_die();
    }

    public function smsonw_remove_custom_carrier(){
        check_ajax_referer( 'egoi_add_custom_carrier', 'security' );
        $carrier    = trim($_POST['name']);
        $obj = get_option('egoi_custom_carriers');

        $obj = $this->jsonValidOrEmpryArray($obj);

        if(false !== $index = array_search($carrier, array_column($obj,'carrier'))){
            unset($obj[$index]);
            update_option('egoi_custom_carriers', json_encode(array_values($obj)));
            echo json_encode(['RESPONSE' => $obj]);
            wp_die();
        }
        echo json_encode(['ERROR' => __('The carrier you are trying to delete was not found.', 'smart-marketing-addon-sms-order') ]);
        wp_die();
    }

    private function jsonValidOrEmpryArray($obj){
        $obj = json_decode($obj,true);
        if(json_last_error() !== JSON_ERROR_NONE || empty($obj))
            $obj = [];
        return $obj;
    }


    /**
     * Show a error notice if don't have WooCommerce
     */
    public function smsonw_woocommerce_dependency_notice() {
        if ( !class_exists( 'WooCommerce' ) ) {
            ?>
            <div class="notice notice-error is-dismissible">
            <p><?php _e('To use this plugin, you first need to install WooCommerce', 'smart-marketing-addon-sms-order'); ?></p>
            </div>
            <?php
        }
    }

    function smsonw_save_billet($order_id) {
        $order = wc_get_order( $order_id );
        $data = $order->get_meta( '_wc_pagseguro_payment_data' );
        if (isset($data['link'])) {
            global $wpdb;

            $code = uniqid();

            $wpdb->insert("{$wpdb->prefix}egoi_sms_order_billets", array(
                    'time' => current_time('mysql'),
                    'order_id' => $order_id,
                    'link' => $data['link'],
                    'code' => $code
            ));

            return $code;
        }
        return false;
    }

    function smsonw_get_billet_code($order_id) {
        global $wpdb;
        return $wpdb->get_var( "SELECT code FROM {$wpdb->prefix}egoi_sms_order_billets WHERE order_id = '$order_id'" );
    }

    function smsonw_billet_endpoint() {
        register_rest_route( 'smsonw/v1', '/billet', array(
            'methods' => 'GET',
            'callback' => array( $this, 'smsonw_billet_redirect'),
            'args' => array(
                'c' => array(
                    'sanitize_callback'  => 'sanitize_text_field'
                ),
            ),
        ) );
    }

    function smsonw_billet_redirect( WP_REST_Request $request ) {
        $params = $request->get_query_params('c');

        global $wpdb;

        $link = $wpdb->get_var("SELECT link FROM {$wpdb->prefix}egoi_sms_order_billets WHERE code = '$params[c]'");

        if ($link) {
            wp_redirect($link);
            exit;
        }
        return 'Not Found';
    }

	/**
	 * Process SMS reminders for abandoned carts (CRON every fifteen minutes)
	 */
    function smsonw_sms_abandoned_cart_process()
    {

	    try {

		    if ( date( 'G' ) >= 10 && date( 'G' ) <= 22 ) {
			    global $wpdb;

			    $table_name = $wpdb->prefix . 'egoi_sms_abandoned_carts';
			    $limit_time = 3600 * 96;
			    $seconds = 172800;
			    $recipients = json_decode(get_option('egoi_sms_order_recipients'), true);

			    if(!empty($recipients['egoi_reminders_time'])){
				    $seconds = 3600 * (int) $recipients['egoi_reminders_time'];
			    }

			    $startTime = date('Y-m-d H:i:s', (time() - $limit_time));
			    $endTime = date('Y-m-d H:i:s', (time() - $seconds));

			    $betweenStatement = "BETWEEN '" . $startTime . "' AND '" . $endTime . "'";

			    $filterStatus = 'standby';

			    $abandonedCarts = $wpdb->get_results("SELECT id, woo_session_key, php_session_key, cellphone FROM $table_name WHERE `time` $betweenStatement AND status='$filterStatus'");

			    if (!empty($abandonedCarts)) {
				    $count = 0;

				    $abandoned_cart_obj = json_decode(get_option('egoi_sms_abandoned_cart'), true);

				    foreach ($abandonedCarts as $cart) {

					    $cart = (array)  $cart;

					    if ( $count >= 40 ) {
						    break;
					    }

					    $cartUrl = self::getCartUrl($cart['woo_session_key']);

                        file_put_contents('/home/admin/web/wp.wemakethings.pt/log.txt', __LINE__ . " - " . __FUNCTION__ . ": \n". print_r($cartUrl, true) . "\n\n");

					    if(empty($cartUrl)){
						    $wpdb->update($table_name, ['status' => 'cart_not_found'], ['id' => $cart['id']]);
					    }

					    $cartUrl = $this->helper->shortener($cartUrl, 'wp egoi cart recover');
					    $message = null;

					    if(!empty($abandoned_cart_obj["message"])){

					        $message = str_replace(array('%shop_name%', '%link%'), array(get_bloginfo('name'), $cartUrl['fullLink']), $abandoned_cart_obj["message"]);

					        if (!empty($message)) {

							    $mesageSend = $this->helper->smsonw_send_sms($cart['cellphone'], $message, 'abandoned_cart', 0);
						        file_put_contents('/home/admin/web/wp.wemakethings.pt/log.txt', __LINE__ . " - " . __FUNCTION__ . ": \n". print_r($mesageSend, true) . "\n\n");

							    if(!empty($mesageSend)) {
								    if (isset($mesageSend->errorCode)) {
									    return false;
								    }
								    $wpdb->update($table_name, ['status' => 'send'], ['id' => $cart['id']]);
							    }
							    $count++;
						    }
					    }
				    }
			    }
		    }
	    } catch (Exception $e) {
		    file_put_contents('/home/admin/web/wp.wemakethings.pt/log.txt', __LINE__ . ": " . __FUNCTION__ . " ERROR:\n".$e->getMessage(), FILE_APPEND);
		    $this->helper->smsonw_save_logs('sms_abandoned_cart_process: ' . $e->getMessage());
        }
    }

	/**
	 * @param $new
	 */
    function update_the_product_price( $new ){
        $product = wc_get_product( $new->id );
        if($new->regular_price != $product->regular_price
        || $new->price != $product->price
        || $new->sale_price != $product->sale_price)
        {
            foreach ( $this->getAllMobilesToNotify($new->id) as $notify){
                $follow_price = json_decode(get_option('egoi_sms_follow_price'), true);
                if( isset($follow_price["follow_price_message"] ) && $follow_price["follow_price_message"]!= ''){
                    $response = $this->helper->smsonw_send_sms($notify['mobile'], $this->prepareMessage($follow_price["follow_price_message"], $new->id, $product->name), 'test', 0);
                }
            }
        }
    }

    function getAllMobilesToNotify($product_id){
        global $wpdb;
        return $wpdb->get_results("SELECT mobile From {$wpdb->prefix}egoi_sms_follow_price where  product_id = '".$product_id."'", ARRAY_A);
    }

    function prepareMessage($message, $product_id, $product_name){
        $url = get_permalink( $product_id ) ;

        $follow_price = json_decode(get_option('egoi_sms_follow_price'), true);
        if(isset($follow_price["follow_price_shortener"]) && $follow_price["follow_price_shortener"] == "on"){
            $url = $this->helper->shortener($url);
            $url = $url['fullLink'];
        }

	    $message = str_replace(array('%shop_name%', '%link%', '%product_name%', '%product%'), array(get_bloginfo('name'), $url, $product_name, $product_name), $message);


	    return str_replace("%link%", $url, $message);
    }

	/**
	 * @param $wc_session
	 * @return string
	 */
	public static function getCartUrl($wc_session){
		$cartArray = self::getCartBySessionId($wc_session);
		if(empty($cartArray)){
		    return false;
		}

        $url = self::cartArrayToUrlParam($cartArray,$wc_session);

		if(empty($url)){
		    return false;
		}

		return wc_get_checkout_url().$url;
	}

	/**
	 * @param $wc_session
	 * @return array|false
	 */
	private static function getCartBySessionId($wc_session)
    {

		global $wpdb;
		$query = sprintf(
			"SELECT %s FROM %s%s WHERE %s = '%s'",
			'session_value',
			$wpdb->prefix,
			'woocommerce_sessions',
			'session_key',
			$wc_session
		);

		$result = $wpdb->get_var($query);

	    if(empty($result)){return false;}
		$cart = unserialize(unserialize($result)['cart']);

		$output = [];
		foreach ($cart as $item){
			if(!empty($item['variation_id'])){
				$output[$item['variation_id']] = $item['quantity'];
				continue;
			}
			$output[$item['product_id']] = $item['quantity'];
		}

	    return $output;
	}

	/**
	 * @param $cartArray
	 * @param $wc_session
	 * @return string
	 */
	public static function cartArrayToUrlParam($cartArray,$wc_session)
    {
		global $wpdb;
		$query = sprintf(
			"SELECT %s FROM %s%s WHERE %s = '%s' AND %s = '%s'",
			'php_session_key',
			$wpdb->prefix,
			'egoi_sms_abandoned_carts',
			'woo_session_key',
			$wc_session,
			'status',
			'standby'
		);

	    $result = $wpdb->get_var($query);

	    if(!empty($result)){
		    $output = '?sid_eg='.$result . '&create-cart=';
	    } else {
		    return false;
	    }

		foreach ($cartArray as $product_id => $quantity){
			$output .= "$product_id:$quantity,";
		}

	    $output = rtrim($output, ',');

	    return $output;
	}

}
