<?php

/**
 * Helper class
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 */

/**
 * This class defines all generic attributes and methods.
 *
 * @since      1.0.0
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 * @author     E-goi <egoi@egoi.com>
 */
class Smart_Marketing_Addon_Sms_Order_Helper {

	private $apikey;

	/**
	 * @var array
	 */
	public $payment_map = array(
		'eupago_multibanco' => array(
			'ent' => '_eupago_multibanco_entidade',
			'ref' => '_eupago_multibanco_referencia',
			'val' => '_order_total'
		),
		'eupago_payshop' => array(
			'ref' => '_eupago_payshop_referencia',
			'val' => '_order_total'
		),
		'easypay_mb' => array(
			'ent' => '',
			'ref' => '',
			'val' => '_order_total'
		),
        'sibs_multibanco' => array(
            'ent' => '',
            'ref' => '',
            'val' => '_order_total'
        ),
        'hipaymultibanco' => array(
            'ent' => '',
            'ref' => '',
            'val' => '_order_total'
        ),
        'lusopaygateway' => array(//todo:validate name
            'ent' => '',
            'ref' => '',
            'val' => '_order_total'
        ),
		/*
		'eupago_mbway' => array(
			'ref' => '_eupago_mbway_referencia',
			'val' => '_order_total'
		),
		*/
		'multibanco_ifthen_for_woocommerce' => array(
			'ent' => '_multibanco_ifthen_for_woocommerce_ent',
			'ref' => '_multibanco_ifthen_for_woocommerce_ref',
			'val' => '_multibanco_ifthen_for_woocommerce_val'
		),
		/*
		// TODO - confirm fields for ifThenPay MBWay
		'mbway_ifthen_for_woocommerce' => array(
			'ref' => '_mbway_ifthen_for_woocommerce_ref',
			'val' => '_mbway_ifthen_for_woocommerce_val'
		)
		*/
	);

	public $payment_foreign_table = array(
        'easypay_mb'        => array(
            'table'     => 'easypay_notifications_2',
            'order_id'  => 't_key',
            'ref'       => 'ep_reference',
            'ent'       => 'ep_entity',
        ),
        'sibs_multibanco'        => array(
            'table'     => 'sibs_transaction',
            'order_id'  => 'order_no',
            'ref'       => 'additional_information',
            'ent'       => 'additional_information',
        ),
        'hipaymultibanco'   => array(
            'table'     => 'woocommerce_hipay_mb',
            'order_id'  => 'order_id',
            'ref'       => 'reference',
            'ent'       => 'entity',
        ),
        'lusopaygateway'   => array(
            'table'     => 'magnimeiosreferences',
            'order_id'  => 'id_order',
            'ref'       => 'refMB',
            'ent'       => 'entidade',
        ),
    );

	public $multibanco_bypass = ['lusopaygateway','hipaymultibanco','easypay_mb'];

    /**
     * @var array
     */
	public $sms_payment_info = array(
        'multibanco' => array(
            'first' => array(
                'en' => 'Hello, your order at %shop_name% is waiting for MB payment. Use Ent. %ent% Ref. %ref% Value %total%%currency% Thank you',
                'es' => 'Hola, su pedido en %shop_name% está esperando el pago MB - Ent. %ent% Ref. %ref% Valor %total%%currency% Gracias',
                'pt' => 'Olá, a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado',
                'pt_BR' => 'Olá, a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado'
            ),
            'reminder' => array(
                'en' => 'Hello, we remind you that your order at %shop_name% is waiting for MB. Use Ent. %ent% Ref. %ref% Value %total%%currency% Thank you',
                'es' => 'Hola, recordamos que su pedido en %shop_name% está esperando el pago MB - Ent. %ent% Ref. %ref% Valor %total%%currency% Gracias',
                'pt' => 'Olá, lembramos que a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado',
                'pt_BR' => 'Olá, lembramos que a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado'
            ),
        ),
        'payshop' => array(
            'first' => array(
                'en' => 'Hello, your order at %shop_name% is waiting for MB payment. Use Ent. %ent% Ref. %ref% Value %total%%currency% Thank you',
                'es' => 'Hola, su pedido en %shop_name% está esperando el pago MB - Ent. %ent% Ref. %ref% Valor %total%%currency% Gracias',
                'pt' => 'Olá, a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado',
                'pt_BR' => 'Olá, a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado'
            ),
            'reminder' => array(
                'en' => 'Hello, we remind you that your order at %shop_name% is waiting for MB. Use Ent. %ent% Ref. %ref% Value %total%%currency% Thank you',
                'es' => 'Hola, recordamos que su pedido en %shop_name% está esperando el pago MB - Ent. %ent% Ref. %ref% Valor %total%%currency% Gracias',
                'pt' => 'Olá, lembramos que a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado',
                'pt_BR' => 'Olá, lembramos que a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado'
            ),
        ),
        'billet' => array(
                'first' => array ('pt_BR' => 'Obrigado pela sua encomenda! Pague por %payment_method% usando este Link %billet_url%'),
                'reminder' => array('pt_BR' => 'Olá %billing_name%, relembramos o link para pagamento %billet_url%')
        )
    );

    public $email_payment_info = array(
        'multibanco' => array(
            'reminder' => array(
                'en' => 'Hello, we remind you that your order at %shop_name% is waiting for MB.

%mb_table%
                
Thank you',
                'es' => 'Hola, recordamos que su pedido en %shop_name% está esperando el pago MB.

%mb_table%
                
Gracias',
                'pt' => 'Olá, lembramos que a sua encomenda em %shop_name% está aguardar pagamento MB.

%mb_table%
                
Obrigado',
                'pt_BR' => 'Olá, lembramos que a sua encomenda em %shop_name% está aguardar pagamento MB.
                
%mb_table% 
                
Obrigado'
            )
        ),
        'payshop' => array(
            'reminder' => array(
                'en' => 'Hello, we remind you that your order at %shop_name% is waiting for MB.

%mb_table% 
                
Thank you',
                'es' => 'Hola, recordamos que su pedido en %shop_name% está esperando el pago MB.

%mb_table%
                
Gracias',
                'pt' => 'Olá, lembramos que a sua encomenda em %shop_name% está aguardar pagamento MB.

%mb_table%
                
Obrigado',
                'pt_BR' => 'Olá, lembramos que a sua encomenda em %shop_name% está aguardar pagamento MB.
                
%mb_table%
                
Obrigado'
            )
        ),
        'billet' => array(
                'reminder' => array('pt_BR' => 'Olá %billing_name%, relembramos o link para pagamento %billet_url%')
        )
    );

	public $sms_text_new_status = array (
        'pt' => array (
            'egoi_sms_order_text_customer_pending' => 'Olá, %billing_name% a sua encomenda em %shop_name% encontra-se pendente de pagamento. Obrigado',
            'egoi_sms_order_text_customer_processing' => 'Olá, %billing_name% a sua encomenda em %shop_name% encontra-se em processamento. Obrigado',
            'egoi_sms_order_text_customer_on-hold' => 'Olá, %billing_name% a sua encomenda em %shop_name% encontra-se aguardar confirmação de pagamento. Obrigado',
            'egoi_sms_order_text_customer_completed' => 'Olá, %billing_name% a sua encomenda em %shop_name% está concluída. Obrigado',
            'egoi_sms_order_text_customer_cancelled' => 'Olá, %billing_name% a sua encomenda em %shop_name% está cancelada. Obrigado',
            'egoi_sms_order_text_customer_refunded' => 'Olá, %billing_name% a sua encomenda em %shop_name% foi reembolsada. Obrigado',
            'egoi_sms_order_text_customer_failed' => 'Olá, %billing_name% a sua encomenda em %shop_name% falhou. Obrigado',
        ),
        'en' => array (
            'egoi_sms_order_text_customer_pending' => 'Hello, %billing_name% Your order at %shop_name% is pending payment. Thank you',
            'egoi_sms_order_text_customer_processing' => 'Hi, %billing_name% Your order at %shop_name% is currently processing. Thank you',
            'egoi_sms_order_text_customer_on-hold' => 'Hi, %billing_name% Your order at %shop_name% is waiting for payment confirmation. Thank you',
            'egoi_sms_order_text_customer_completed' => 'Hi, %billing_name% Your order at %shop_name% is now complete. Thank you',
            'egoi_sms_order_text_customer_cancelled' => 'Hello, %billing_name% Your order at %shop_name% is canceled. Thank you',
            'egoi_sms_order_text_customer_refunded' => 'Hi, %billing_name% Your order at %shop_name% was refunded. Thank you',
            'egoi_sms_order_text_customer_failed' => 'Hello, %billing_name% Your order at %shop_name% failed. Thank you',
        ),
        'es' => array (
            'egoi_sms_order_text_customer_pending' => 'Hola, %billing_name% su pedido en %shop_name% se encuentra pendiente de pago. Gracias',
            'egoi_sms_order_text_customer_processing' => 'Hola, %billing_name% su pedido en %shop_name% se encuentra en proceso. Gracias',
            'egoi_sms_order_text_customer_on-hold' => 'Hola, %billing_name% su pedido en %shop_name% está esperando la confirmación de pago. Gracias',
            'egoi_sms_order_text_customer_completed' => 'Hola, %billing_name% su pedido en %shop_name% ha finalizado. Gracias',
            'egoi_sms_order_text_customer_cancelled' => 'Hola, %billing_name% su pedido en %shop_name% está cancelada. Gracias',
            'egoi_sms_order_text_customer_refunded' => 'Hola, %billing_name% su pedido en %shop_name% se ha reembolsado. Gracias',
            'egoi_sms_order_text_customer_failed' => 'Hola, %billing_name% su pedido en %shop_name% à fracasado. Gracias',
        ),
        'pt_BR' => array (
            'egoi_sms_order_text_customer_pending' => 'Olá, %billing_name% a sua encomenda em %shop_name% encontra-se pendente de pagamento. Obrigado',
            'egoi_sms_order_text_customer_processing' => 'Olá, %billing_name% a sua encomenda em %shop_name% encontra-se em processamento. Obrigado',
            'egoi_sms_order_text_customer_on-hold' => 'Olá, %billing_name% a sua encomenda em %shop_name% encontra-se aguardar confirmação de pagamento. Obrigado',
            'egoi_sms_order_text_customer_completed' => 'Olá, %billing_name% a sua encomenda em %shop_name% está concluída. Obrigado',
            'egoi_sms_order_text_customer_cancelled' => 'Olá, %billing_name% a sua encomenda em %shop_name% está cancelada. Obrigado',
            'egoi_sms_order_text_customer_refunded' => 'Olá, %billing_name% a sua encomenda em %shop_name% foi reembolsada. Obrigado',
            'egoi_sms_order_text_customer_failed' => 'Olá, %billing_name% a sua encomenda em %shop_name% falhou. Obrigado',
        ),
    );

    /**
     * @var array
     */
    public $currency = array(
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
        'BRL' => 'R$'
    );

    /**
     * @var array List of SMS text tags
     */
    public $sms_text_tags = array(
        "order_id"          => '%order_id%',
        "order_status"      => '%order_status%',
        "total"             => '%total%',
        "currency"          => '%currency%',
        "payment_method"    => '%payment_method%',
        "reference"         => '%ref%',
        "entity"            => '%ent%',
        "shop_name"         => '%shop_name%',
        "billing_name"      => '%billing_name%',
        "billet_URL"        => '%billet_url%',
        "tracking_name"     => '%tracking_name%',
        "tracking_code"     => '%tracking_code%',
        "tracking_url"      => '%tracking_url%',
    );

    public $email_text_tags = array(
        "order_id"          => '%order_id%',
        "order_status"      => '%order_status%',
        "total"             => '%total%',
        "currency"          => '%currency%',
        "payment_method"    => '%payment_method%',
        "reference"         => '%ref%',
        "entity"            => '%ent%',
        "shop_name"         => '%shop_name%',
        "billing_name"      => '%billing_name%',
        "billet_URL"        => '%billet_url%',
        "tracking_name"     => '%tracking_name%',
        "tracking_code"     => '%tracking_code%',
        "tracking_url"      => '%tracking_url%',
        "mb_table"          => '%mb_table%'
    );

    /**
     * @var SoapClient
     */
    protected $egoi_api_client;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
	    try{
            $this->egoi_api_client = new SoapClient('http://api.e-goi.com/v2/soap.php?wsdl');
        }catch (Exception $e){

	    }

		$apikey = get_option('egoi_api_key');
		$this->apikey = $apikey['api_key'];
		//check if api is on
		$this->ping();
	}

    /**
     * @return bool
     */
    public function smsonw_get_soap_error() {
        if(empty($this->egoi_api_client))
            return true;
        return false;
    }


    /**
     * @return array
     */
    public function smsonw_get_order_statuses() {
        return array(
            'pending' => __('Pending payment', 'smart-marketing-addon-sms-order'),
            'processing' => __('Processing', 'smart-marketing-addon-sms-order'),
            'on-hold' => __('On Hold', 'smart-marketing-addon-sms-order'),
            'completed' => __('Completed', 'smart-marketing-addon-sms-order'),
            'cancelled' => __('Cancelled', 'smart-marketing-addon-sms-order'),
            'refunded' => __('Refunded', 'smart-marketing-addon-sms-order'),
            'failed' => __('Failed', 'smart-marketing-addon-sms-order'),
        );
    }

    /**
     * @return array
     */
    public function smsonw_get_languages() {
        return array(
            'en' =>  __('English', 'smart-marketing-addon-sms-order'),
            'es' =>  __('Spanish', 'smart-marketing-addon-sms-order'),
            'pt' =>  __('Portuguese', 'smart-marketing-addon-sms-order'),
            'pt_BR' =>  __('Brazilian Portuguese', 'smart-marketing-addon-sms-order'),
        );
    }

    /**
     * @return array
     */
    public function smsonw_get_payment_methods() {
        return array(
            'multibanco' =>  __('Multibanco (euPago, IfthenPay, easypay, hipaymultibanco, sibs, lusopay)', 'smart-marketing-addon-sms-order'),
            'payshop' =>  __('Payshop (euPago)', 'smart-marketing-addon-sms-order'),
            'billet' =>  __('PagSeguro', 'smart-marketing-addon-sms-order'),
        );
    }

	/**
	 * Function to get cellphone senders from E-goi account
	 *
	 * @return array with senders
	 */
	public function smsonw_get_senders() {

	    if(empty($this->egoi_api_client))
	        return [];
        $result = $this->egoi_api_client->getSenders(array(
            'apikey' 		=> $this->apikey,
            'channel' 		=> 'telemovel'
        ));

		return $result;
	}

    /**
     * @return string
     */
	public function smsonw_get_balance() {

        if(empty($this->egoi_api_client))
            return '0.00$';

        $credits = explode(' ',$this->egoi_api_client->getClientData(array('apikey' => $this->apikey))['CREDITS']);
        return $credits[1].$this->currency[$credits[0]];

    }

	/**
	 * Get not paid orders over 48 hours
	 *
	 * @return mixed
	 */
	public function smsonw_get_not_paid_orders($time) {

		$recipients = json_decode(get_option('egoi_sms_order_recipients'), true);
        $limit_time = 3600 * 96;
		$seconds = 172800;

		if(!empty($recipients[$time])){
			$seconds = 3600 * (int) $recipients[$time];
        }

		$args = array(
			"status" => array(
				"pending",
				"on-hold"
			),
			"date_created" => (time() - $limit_time) . '...' . (time() - $seconds),
			'limit' => -1
		);
		return wc_get_orders($args);
	}

	/**
	 * Get SMS text from configs
	 *
	 * @param $recipient_type
	 * @param $order
	 *
	 * @return bool|mixed
	 */
	public function smsonw_get_sms_order_message($recipient_type, $order) {
		$recipients = json_decode(get_option('egoi_sms_order_recipients'), true);
		$texts = json_decode(get_option('egoi_sms_order_texts'), true);
		$lang = $this->smsonw_get_lang($order['billing']['country']);

        if (isset($texts[$lang]['egoi_sms_order_text_' . $recipient_type . '_' . $order['status']])
            && isset($recipients['egoi_sms_order_' . $recipient_type . '_' . $order['status']])
            && $recipients['egoi_sms_order_' . $recipient_type . '_' . $order['status']] == 1
        ) {
            return $this->smsonw_get_tags_content($order, $texts[$lang]['egoi_sms_order_text_' . $recipient_type . '_' . $order['status']]);
		} else if (isset($this->sms_text_new_status[$lang]['egoi_sms_order_text_' . $recipient_type . '_' . $order['status']])
            && isset($recipients['egoi_sms_order_' . $recipient_type . '_' . $order['status']])
            && $recipients['egoi_sms_order_' . $recipient_type . '_' . $order['status']] == 1
        ) {
            return $this->smsonw_get_tags_content($order, $this->sms_text_new_status[$lang]['egoi_sms_order_text_' . $recipient_type . '_' . $order['status']]);
        }
		return false;
	}

	/**
	 * @param $country
	 *
	 * @return bool|string
	 */
	public function smsonw_get_lang($country) {
		$country_codes = unserialize(COUNTRY_CODES);
		$lang = $country_codes[$country]['language'];
		$lang_allowed = array('en', 'pt', 'es');
		if ($lang == 'pt-BR') {
			return 'pt_BR';
		} else if (in_array(substr($lang, 0, 2), $lang_allowed)) {
			return substr($lang, 0, 2);
		}
		return 'en';
    }

    private function priv_get_data_table($method,$action, $order_id){
        global $wpdb;

        $easyPayQuery = sprintf(
                "SELECT %s as %s FROM %s%s WHERE %s = '%s'",
                $this->payment_foreign_table[$method][$action], //$method=easy_pay $action=ref|ent result = ep_reference|ep_entity
                $action,
                $wpdb->prefix,
                $this->payment_foreign_table[$method]['table'],
                $this->payment_foreign_table[$method]['order_id'],
                $order_id
        );


        $result = $wpdb->get_results($easyPayQuery, ARRAY_A);
        if($method == 'sibs_multibanco'){
            $entEref = explode('|',$result[0][$action]);
            if($action == 'ref')
                return $entEref[1];
            else
                return $entEref[0];
        }

        if (!empty($result[0][$action])) {
            return $result[0][$action];
        }
    }

	/**
	 * Get order payment instructions
	 *
	 * @param $order
	 * @param $field
	 *
	 * @return bool
	 */
	public function smsonw_get_payment_data($order, $field) {
		$order_meta = get_post_meta($order['id']);

		//fix
        if(key_exists($order['payment_method'], $this->payment_foreign_table)){
            return $this->priv_get_data_table($order['payment_method'], $field, $order['id']);
        }

		if (isset($this->payment_map[$order['payment_method']][$field])) {
			$payment_field = $this->payment_map[$order['payment_method']][ $field ];
			return $order_meta[$payment_field][0];
		}
		return false;
	}

	/**
	 * Prepare recipient to E-goi
	 */
	public function smsonw_get_valid_recipient($phone, $country, $prefix = null) {
		$prefix = preg_replace('/[^0-9]/', '', $prefix);
		$recipient = preg_replace('/[^0-9]/', '', $phone);

		if ($prefix) {
			return $prefix.'-'.$recipient;
		} else if ($country) {

			$prefixes = unserialize( COUNTRY_CODES );
			$len = strlen($prefixes[$country]['prefix']);
		    if ($prefixes[$country]['prefix'] != substr($recipient, 0, $len)) {
			    return $prefixes[ $country ]['prefix'] . '-' . $recipient;
		    } else {
		        return substr($recipient, 0, $len) . '-' . substr($recipient, $len);
            }

		} else {
            if(!file_exists(plugin_dir_path( __DIR__ ).'../smart-marketing-for-wp/includes/class-egoi-for-wp.php')){
                return $phone;
            }
            require_once plugin_dir_path( __DIR__ ).'../smart-marketing-for-wp/includes/class-egoi-for-wp.php';
            if(!class_exists('Egoi_For_Wp') || !method_exists('Egoi_For_Wp','smsnf_get_valid_phone')){ return $phone; }
            $phone = Egoi_For_Wp::smsnf_get_valid_phone($phone);
        }

		return $phone;
	}

    /**
     * replace tags with order data
     *
     * @param $order
     * @param $message
     * @param $billet_code
     * @return string
     */
    public function smsonw_get_tags_content($order, $message, $billet_code = false)
    {
        $codes = $this->smsonw_get_tracking_codes($order['id']);
        $carriers = $this->smsonw_get_tracking_carriers(true);
        $carriers_url = $this->smsonw_get_tracking_carriers_urls(true);

        $entity = $this->smsonw_get_payment_data($order, 'ent');
        $reference = $this->smsonw_get_payment_data($order, 'ref');
        $lang = $this->smsonw_get_lang($order['billing']['country']);
        $mb_image = plugin_dir_url( __FILE__ ) . "../admin/img/multibanco-logo.png";
        
        $tags = array(
            '%order_id%'        => $order['id'],
            '%order_status%'    => $order['status'],
            '%total%'           => $order['total'],
            '%currency%'        => $order['currency'],
            '%payment_method%'  => $order['payment_method'],
            '%ref%'             => $this->smsonw_get_payment_data($order, 'ref'),
            '%ent%'             => $this->smsonw_get_payment_data($order, 'ent'),
            '%shop_name%'       => get_bloginfo('name'),
            '%billing_name%'    => $order['billing']['first_name'] . ' ' . $order['billing']['last_name'],

            '%tracking_name%'   => (isset($codes[0]['carrier']) &&isset($carriers[$codes[0]['carrier']]))
                ?$carriers[$codes[0]['carrier']]
                :'',

            '%tracking_code%'   => (isset($codes[0]['tracking_code']))
                ?$codes[0]['tracking_code']
                :'',

            '%tracking_url%'    => (isset($carriers_url[$codes[0]['carrier']]))
                ?$carriers_url[$codes[0]['carrier']]
                :'',
            '%mb_table%'         => $this->smsonw_get_mb_table_html(
                $entity, 
                $reference,
                $order['total'].$order['currency'], 
                $mb_image,
                $lang)
        );


        if ($billet_code) {
            $tags['%billet_url%'] = get_site_url(null, '/wp-json/smsonw/v1/billet?c=' . $billet_code);
            $order_data = wc_get_order( $order['id'] );
            $data = $order_data->get_meta( '_wc_pagseguro_payment_data' );
            if (strpos($data['method'], 'Billet') !== false) {
                $tags['%payment_method%'] = 'Boleto';
            } else if (strpos($data['method'], 'Bank Transfer') !== false) {
                $tags['%payment_method%'] = 'Transferência Bancária';
            }
        }

        foreach ($tags as $tag => $content) {
            if ($tag == '%ref%' && $this->smsonw_get_payment_data($order, 'ref') == false) {
                $message = str_replace('Ref. %ref%', '', $message);
                continue;
            }
            if ($tag == '%ent%' && $this->smsonw_get_payment_data($order, 'ent') == false) {
                $message = str_replace('Ent. %ent%', '', $message);
                continue;
            }
            $message = str_replace($tag, $content, $message);
        }


        return $message;
    }

    public function smsonw_get_mb_table_html($entity, $reference, $total, $img_src, $lang){
        $img = '';
        $ins = 'Payment instructions';
        $ent = 'Entity: ';
        $ref = 'Reference: ';
        $val = 'Value: ';

        if($lang == 'es'){
            $ins = 'Instrucciones de pago';
            $ent = 'Entidad: ';
            $ref = 'Referencia: ';
            $val = 'valor: ';
        }else if($lang == 'pt' || $lang == 'pt-BR'){
            $ins = 'Instruções de pagamento';
            $ent = 'Entidade: ';
            $ref = 'Referência: ';
            $val = 'Valor: ';
        }

        $img ='<img src="'.$img_src.'" style="width:70%;display:block;margin-left: auto; margin-right: auto; padding:10px 0 10px 0">';
       

        $content = '<table style="width:50%;margin-left: auto !important; margin-right: auto !important;" >
        <tbody>
        <tr>
          <th colspan="2" style="width:100%;padding:10px 10px 10px 10px">'.$ins.' '.$img.'</th>
        </tr>
        <tr >
          <td style="padding:20px 0px 10px 0px">
            '.$ent.'</td>
            <td style="text-align:right;">'.$entity.'</td>
        </tr>
        <tr>
        <td style="padding:10px 0px 10px 0px">'.$ref.'</td>
        <td style="text-align:right;">'.$reference.'</td>
        </tr>
        <tr>
        <td style="padding:10px 0px 20px 0px">'.$val.'</td>
        <td style="text-align:right;">'.$total.'</td>
        </tr>
        </tbody>
      </table>';

      return $content;
    }

	/**
	 * Method to send SMS
	 *
	 * @param $recipient
	 * @param $message
	 * @param $type
	 * @param $order_id
	 * @param bool $gsm
	 *
	 * @return mixed
	 */
	public function smsonw_send_sms($recipient, $message, $type, $order_id, $gsm = false, $max_count = 3) {
		$url = 'http://dev-web-agency.e-team.biz/smaddonsms/sms'; 

		$sender = json_decode(get_option('egoi_sms_order_sender'), true);

		$sms_params = array(
            "apikey" => $this->apikey,
			"sender_hash" => $sender['sender_hash'],
			"message" => $message,
			"recipient" => $recipient,
			"type" => $type,
			"order_id" => $order_id,
			"gsm" => $gsm,
            "max_count" => $max_count
		);

		$response = wp_remote_post($url, array(
            'timeout' => 60,
            'body' => $sms_params
        ));

		$result = json_encode($response['body']);

		if ($response['response']['code'] == 200) {
            $sms_counter = get_option('egoi_sms_counter');
            $counter = $sms_counter ? $sms_counter+1 : 1;
            update_option('egoi_sms_counter', $counter);
        } else {
		    return false;
        }

		return $result;
    }
    
    /**
	 * Method to send SMS
	 *
	 * @param $recipient
	 * @param $message
	 * @param $type
	 * @param $order_id
	 * @param bool $gsm
	 *
	 * @return mixed
	 */
	public function smsonw_send_email($email, $message, $order) {
        
        $subject = '['.get_bloginfo( 'name' ).']:'.  __( 'Order', 'smart-marketing-addon-sms-order' ).' #'.$order.' - '.__( 'Payment reminder', 'smart-marketing-addon-sms-order' );
        $content = str_replace(array('\n'), '<br>', $message);

        $title = get_bloginfo('name');
        $thumbnail = '';
        $blog_info = array(
            'description' => get_bloginfo('description'),
        );       
        
        $template_file = apply_filters('egoi_email_remider', plugin_dir_path( __DIR__ ).'../smart-marketing-for-wp/admin/partials/emailcampaignwidget/email_campaign.php', $title, $content, $thumbnail, $blog_info);

        ob_start();
        include $template_file;
        $template = ob_get_contents();
        ob_end_clean();

        $headers = array('Content-Type: text/html; charset=UTF-8');

        $result = wp_mail( $email, $subject, '<td><tr>'.$template.'</tr></td>', $headers);

		return $result;
	}

	/**
	 * Save logs in /logs/smart-marketing-addon-sms-order.log
	 *
	 * @param $log
	 */
	public function smsonw_save_logs($log) {
		$path = dirname(__FILE__).'/logs/';

		$file = fopen($path.'smart-marketing-addon-sms-order.log', 'a+');
		fwrite($file, $log."\xA");
		fclose($file);
	}

	/**
	 * Div to success notices
	 */
	public function smsonw_admin_notice_success() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Changes saved successfully', 'smart-marketing-addon-sms-order' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Div to error notices
	 */
	public function smsonw_admin_notice_error() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'Irks! An error has occurred.', 'smart-marketing-addon-sms-order' ); ?></p>
		</div>
		<?php
	}

    /**
     * Return all (not all) possible positions for price drop button
     * @return array|string
     */
    public function smsonw_admin_follow_price_positions() {
	    return array(
		    'woocommerce_before_single_product'         => 'Before Single Product',
		    'woocommerce_before_single_product_summary' => 'Before Single Product Summary',
		    'woocommerce_after_single_product_summary'  => 'After Single Product Summary',
		    'woocommerce_product_thumbnails'            => 'Before Single Product Thumbnails',
		    'woocommerce_single_product_summary'        => 'Single Product Summary',
		    'woocommerce_simple_add_to_cart'            => 'Before add to cart Button',
		    'woocommerce_after_add_to_cart_button'      => 'After add to cart Button',
		    'woocommerce_after_add_to_cart_form'        => 'After add to cart form',
		    'woocommerce_grouped_add_to_cart'           => 'Before add to cart Button in grouped products',
	    );
    }

	public function smsonw_sanitize_boolean_field($field) {
        if (isset($_POST[$field]) && filter_var($_POST[$field], FILTER_VALIDATE_BOOLEAN)) {
            return filter_var($_POST[$field], FILTER_SANITIZE_NUMBER_INT);
        } else {
            return 0;
        }
    }

    /**
     * @param $order_id
     * @return bool|int
     */
    public function smsonw_check_notification_option($order_id) {
        $recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);

        if ($recipient_options['notification_option']) {
            return (bool) get_post_meta($order_id, 'egoi_notification_option')[0];
        } else {
            return 1;
        }
    }

    public function smsonw_get_option_payment_method($order_payment_method) {
        if (strpos($order_payment_method, 'multibanco') !== false || in_array($order_payment_method,$this->multibanco_bypass) ) {
            return 'multibanco';
        } else if (strpos($order_payment_method, 'payshop') !== false) {
            return 'payshop';
        } else if  (strpos($order_payment_method, 'pagseguro') !== false) {
            return 'billet';
        }

        return $order_payment_method;
    }

	/**
	 * @return array|mixed
	 */
	protected function ping ()
	{
		$egoiV3    = 'https://api.egoiapp.com';
		$pluginkey = '2f711c62b1eda65bfed5665fbd2cdfc9';

		try {
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL            => $egoiV3 . "/ping",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => "",
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 10,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_HTTPHEADER     => array(
					"cache-control: no-cache",
					"Apikey: " . $this->apikey,
					"Pluginkey: " . $pluginkey
				),
			));

			curl_exec($curl);
			curl_close($curl);
			return true;

		} catch (Exception $e) {
			return true;
		}
	}

    /**
     * Short a link using transacional.
     *
     * @param  link|string.
     * @param  name|string.
     *
     * @return array
     */
	public function shortener($link, $name = ""){
        $slingshot    = 'https://www51.e-goi.com';

        $data = array(
            "apikey" => $this->apikey,
            "name" => ($name == "")? $link : $name,
            "originalLink" => $link
        );

        try {
            // API URL
            $ch = curl_init($slingshot . "/api/public/shortener");
            $payload = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            return json_decode($result, true);
        } catch (Exception $e) {
            die;
        }
    }

    /**
     * Get tracking codes from order.
     *
     * @param  WC_Order|int $order Order ID or order data.
     *
     * @return array
     */
    function smsonw_get_tracking_codes( $order ) {

        if (is_numeric($order))
            $order = wc_get_order($order);

        if(! $order instanceof WC_Order)//order not found
            return[];

        if ( method_exists( $order, 'get_meta' ) ) {
            $codes = $order->get_meta( '_tracking_code_egoi' );
        } else {
            $codes = isset($order->_tracking_code_egoi)?isset($order->_tracking_code_egoi):'[]';
        }

        return json_decode($codes, true);
    }

    function smsonw_get_tracking_carriers($flag = false){
        $methods = WC()->shipping->get_shipping_methods();
        $output = [];
        foreach ($methods as $key => $value){

            if(empty( (is_array($value)?$value['method_title']:$value->method_title) ))
                continue;

            $output[$key] = is_array($value)?$value['method_title']:$value->method_title;
        }

        if($flag == false)
            return $output;

        $customs = $this->smsonw_get_custom_tracking_carriers();
        foreach ($customs as $custom){
            $output[$custom['carrier']] = $custom['carrier'];
        }
        return $output;
    }

    function smsonw_get_tracking_carriers_urls($flag = false){
        $objs = get_option('egoi_tracking_carriers_urls');

        $objs_costum = ($flag === true)?$this->smsonw_get_custom_tracking_carriers():[];

        if(empty($objs))
            $objs = [];
        else
            $objs = json_decode($objs,true);

        if(empty($objs_costum))
            $objs_costum = [];
        else
            $objs_costum = json_decode($objs_costum,true);

        foreach ($objs_costum as $costum){
            $objs[$costum['carrier']] = $costum['url'];
        }

        return $objs;
    }

    function smsonw_get_custom_tracking_carriers(){
        $objs = get_option('egoi_custom_carriers');
        if(empty($objs))
            return [];
        $objs = json_decode($objs,true);
        return (json_last_error() !== JSON_ERROR_NONE)?[]:$objs;
    }



}