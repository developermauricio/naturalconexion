<?php
/**
 * Settings for Shopflix feeds
 */
class WooSEA_shopflix {
	public $shopflix;

        public static function get_channel_attributes() {

                $sitename = get_option('blogname');

        	$shopflix = array(
			"Feed fields" => array(
				"Name" => array(
					"name" => "name",
					"feed_name" => "name",
					"format" => "required",
					"woo_suggest" => "title",
				),
				"SKU" => array(
					"name" => "SKU",
					"feed_name" => "SKU",
					"format" => "required",
					"woo_suggest" => "sku",
				),
				"Category" => array(
					"name" => "category",
					"feed_name" => "category",
					"format" => "required",
				),
				"EAN" => array(
					"name" => "EAN",
					"feed_name" => "EAN",
					"format" => "required",
				),
				"MPN" => array(
					"name" => "MPN",
					"feed_name" => "MPN",
					"format" => "required",
				),
				"Manufacturer" => array(
					"name" => "manufacturer",
					"feed_name" => "manufacturer",
					"format" => "required",
				),
				"Description" => array(
					"name" => "description",
					"feed_name" => "description",
					"format" => "required",
					"woo_suggest" => "description",
				),
				"Product URL" => array(
					"name" => "product_url",
					"feed_name" => "product_url",
					"format" => "required",
					"woo_suggest" => "link",
				),
				"Image" => array(
					"name" => "image",
					"feed_name" => "image",
					"format" => "required",
					"woo_suggest" => "image",
				),
				"Additional Image" => array(
					"name" => "additional_imageurl",
					"feed_name" => "additional_imageurl",
					"format" => "optional",
				),
				"Price" => array(
					"name" => "price",
					"feed_name" => "price",
					"format" => "required",
					"woo_suggest" => "price",
				),
				"List Price" => array(
					"name" => "list_price",
					"feed_name" => "list_price",
					"format" => "optional",
				),
                                "Quantity" => array(
                                        "name" => "quantity",
                                        "feed_name" => "quantity",
					"format" => "required",
					"woo_suggest" => "quantity",
				),
                                "Offer from" => array(
                                        "name" => "offer_from",
                                        "feed_name" => "offer_from",
					"format" => "optional",
				),
                        	"Offer to" => array(
                                        "name" => "offer_to",
                                        "feed_name" => "offer_to",
					"format" => "optional",
				),
                        	"Offer price" => array(
                                        "name" => "offer_price",
                                        "feed_name" => "offer_price",
					"format" => "optional",
				),
                        	"Offer Quantity" => array(
                                        "name" => "offer_quantity",
                                        "feed_name" => "offer_quantity",
					"format" => "optional",
				),
                        	"Shipping Lead Time" => array(
                                        "name" => "shipping_lead_time",
                                        "feed_name" => "shipping_lead_time",
					"format" => "optional",
				),
                                "Weight" => array(
                                        "name" => "weight",
                                        "feed_name" => "weight",
                                        "format" => "required",
                                ),
                                "Color" => array(
                                        "name" => "color",
                                        "feed_name" => "color",
                                        "format" => "required",
                                ),
			),
		);
		return $shopflix;
	}
}
?>
