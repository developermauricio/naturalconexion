<?php
/**
 * Settings for Google Local Product Inventory feeds
 */
class WooSEA_google_local {
	public $google_local;

        public static function get_channel_attributes() {

                $sitename = get_option('blogname');

        	$google_local = array(
			"Local product inventory fields" => array(
				"Itemid" => array(
					"name" => "itemid",
					"feed_name" => "g:itemid",
					"format" => "required",
					"woo_suggest" => "id",
				),
				"Store code" => array(
					"name" => "Store code",
					"feed_name" => "g:store_code",
					"format" => "required",
				),
				"Quantity" => array(
					"name" => "Quantity",
					"feed_name" => "g:quantity",
					"format" => "required",
					"woo_suggest" => "quantity",
				),
				"Price" => array(
					"name" => "Price",
					"feed_name" => "g:price",
					"format" => "required",
					"woo_suggest" => "price",
				),
				"Sale price" => array(
					"name" => "Sale price",
					"feed_name" => "g:sale_price",
					"format" => "optional",
					"woo_suggest" => "sale_price",
				),
                                "Sale price effective date" => array(
                                        "name" => "Sale price effective date",
                                        "feed_name" => "g:sale_price_effective_date",
                                        "format" => "optional",
                                        "woo_suggest" => "sale_price_effective_date",
                                ),
                                "Availability" => array(
                                        "name" => "Availability",
                                        "feed_name" => "g:availability",
                                        "format" => "optional",
                                        "woo_suggest" => "availability",
                                ),
                                "Weeks of supply" => array(
                                        "name" => "Weeks of supply",
                                        "feed_name" => "g:weeks_of_supply",
                                        "format" => "optional",
                                ),
                                "Pickup method" => array(
                                        "name" => "Pickup method",
                                        "feed_name" => "g:pickup_method",
                                        "format" => "optional",
                                ),
                                "Pickup sla" => array(
                                        "name" => "Pickup sla",
                                        "feed_name" => "g:pickup_sla",
                                        "format" => "optional",
                                ),
                                "Webitemid" => array(
                                        "name" => "Webitemid",
                                        "feed_name" => "g:webitemid",
                                        "format" => "optional",
                                ),
			),
		);
		return $google_local;
	}
}
?>
