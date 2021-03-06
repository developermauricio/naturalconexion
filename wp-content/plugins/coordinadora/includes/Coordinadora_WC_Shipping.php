<?php
class Coordinadora_WC_Shipping
{
    private $logger;

    public function __construct()
    {
        $this->logger = new WC_Logger();
    }

    public function addLog($data)
    {
        $this->logger->add('coordinadora-calculo-envio', print_r($data, true));
    }

    /**
     * calculate_shipping function.
     *
     */
    public function calculate_shipping($title, $package = array(), $url_carrier, $api_key)
    {
        if (!$package["destination"]["city"]) {
            return;
        }

        $line_items = $package['contents'];

        $product_without_configuration = '';
        foreach ($package['contents'] as $key => $item) {
            $product_id =  $item['data']->get_id();
            $product    = wc_get_product($product_id);

            if ($item['variation_id'] > 0) {
                $warehouse = get_post_meta($product->get_parent_id(), '_coordinadora_warehouse', true);
            } else {
                $warehouse = get_post_meta($product->get_id(), '_coordinadora_warehouse', true);
            }

            $length = $product->get_length();
            $width = $product->get_width();
            $height = $product->get_height();
            $weight = $product->get_weight();

            if (
                empty($length) || empty($width) || empty($height) || empty($weight) || empty($warehouse)
            ) {
                $product_without_configuration = $product->get_title() . " $height x $width x $length peso $weight bodega $warehouse";
                break;
            }

            $product_data['origin_city'] = $warehouse;
            $product_data['price'] = $item['line_total'];
            $product_data['quantity'] = $item['quantity'];
            $product_data['length'] = floatval($length);
            $product_data['width'] = floatval($width);
            $product_data['height'] = floatval($height);
            $product_data['weight'] = floatval($weight);

            $products[] = $product_data;
        }

        if (count($products) != count($line_items)) {
            $this->addLog('El producto "' . $product_without_configuration . '" est?? sin configurar');
            return;
        }

        $body = json_encode(array(
            'destination_city'  => substr($package['destination']['city'], -9, 8), // Destination city got by 'places plugin' with format 'MEDELLIN (ANT) (05001000)',
            'products'          => $products,
        ));

        $args = array(
            'body'    => $body,
            'headers' => array(
                'content-type'            => 'application/json',
                'wc-coordinadora-api-key' => $api_key
            )
        );

        $this->addLog("petici??n para el c??lculo de env??o $title.");
        $this->addLog($body);

        $response = wp_remote_post($url_carrier, $args);

        $this->addLog("Respuesta c??lculo de env??o $title:");
        $this->addLog($response['response']);
        $this->addLog($response['body']);

        if (is_wp_error($response) || $response['response']['code'] !== 201 || $response['body'] == 'false') {
            return false;
        }

        $rate = array(
            'label'   => $title,
            'cost'     => (int)$response['body'],
            'calc_tax' => 'per_item'
        );

        return $rate;
    }
}
