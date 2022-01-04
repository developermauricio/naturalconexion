<?php
/**
 * Cross-sell functions.
 *
 * @package iconic-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'Iconic_WSB_Core_Cross_Sells' ) ) {
	return;
}

/**
 * Iconic_WSB_Core_Cross_Sells.
 *
 * @class    Iconic_WSB_Core_Cross_Sells
 * @version  1.0.0
 */
class Iconic_WSB_Core_Cross_Sells {
	/**
	 * Single instance of the Iconic_WSB_Core_Licence object.
	 *
	 * @var Iconic_WSB_Core_Licence
	 */
	public static $single_instance = null;

	/**
	 * Class args.
	 *
	 * @var array
	 */
	public static $args = array();

	/**
	 * Array of selected plugins.
	 *
	 * @var array
	 */
	private static $selected_plugins = array();

	/**
	 * Creates/returns the single instance Iconic_WSB_Core_Licence object.
	 *
	 * @param array $args Arguments.
	 *
	 * @return Iconic_WSB_Core_Licence
	 */
	public static function run( $args = array() ) {
		if ( null === self::$single_instance ) {
			self::$args            = $args;
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Iconic_WSB_Core_Cross_Sells constructor.
	 */
	public function __construct() {
		self::$selected_plugins = self::get_selected_plugins( self::$args['plugins'] );
	}

	/**
	 * Get plugins.
	 *
	 * @return array
	 */
	private static function get_plugins() {
		return array(
			'iconic-woo-show-single-variations'       => array(
				'class'       => 'JCK_WSSV',
				'title'       => 'WooCommerce Show Single Variations',
				'url'         => 'https://iconicwp.com/products/woocommerce-show-single-variations/',
				'description' => __( 'Display individual product variations of a variable product in your product listings. Make it easy for your customers to view and filter product variations.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2016/02/woocommerce-show-single-variations-featured-twitter-790x395.png',
				'testimonial' => array(
					'title' => 'Fantastic Plugin',
					'text'  => 'Well worth the investment! This plugin helps us showcase our many variations in ways that were previously not possible.',
					'cite'  => "John O'Brien",
				),
				'plugin_id'   => 1036,
				'plan_id'     => 1442,
				'public_key'  => 'pk_e6402c968382fd116b38f146a3c83',
			),
			'iconic-woothumbs'                        => array(
				'class'       => 'Iconic_WooThumbs',
				'title'       => 'WooThumbs for WooCommerce',
				'url'         => 'https://iconicwp.com/products/woothumbs/',
				'description' => __( 'Enable zoom, sliders, video, fullscreen, multiple images per variation, and customisable layout options for your product imagery.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2016/05/woothumbs-featured-twitter-790x395.png',
				'testimonial' => array(
					'title' => 'Solid Build – Stellar Support',
					'text'  => 'I highly recommend both WooThumbs as a plugin and Iconic as a solid WP developer!',
					'cite'  => 'André Giæver, Human Web Agency',
				),
				'plugin_id'   => 869,
				'plan_id'     => 1215,
				'public_key'  => 'pk_3e970b87cd0ed00b398a760433a79',
			),
			'iconic-woo-delivery-slots'               => array(
				'class'       => 'Iconic_WDS',
				'title'       => 'WooCommerce Delivery Slots',
				'url'         => 'https://iconicwp.com/products/woocommerce-delivery-slots/',
				'description' => __( 'Choose a delivery date and time for each order. Add a limit to the number of allowed reservations, restrict time slots to specific delivery methods, and so much more.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2016/02/woocommerce-delivery-slots-featured-twitter-790x395.png',
				'testimonial' => array(
					'title' => 'Awesome Plugin with Awesome Support',
					'text'  => 'Never before have I seen such a proactive developer/owner than James at Iconic. A brilliant plugin + A super dooper Dev = Awesome Product.',
					'cite'  => 'Praveen Kumar, AC',
				),
				'plugin_id'   => 1038,
				'plan_id'     => 1649,
				'public_key'  => 'pk_ae98776906ff416522057aab876c0',
			),
			'iconic-woo-linked-variations'            => array(
				'class'       => 'Iconic_Woo_Linked_Variations',
				'title'       => 'WooCommerce Linked Variations',
				'url'         => 'https://iconicwp.com/products/woocommerce-linked-variations/',
				'description' => __( 'Link a group of WooCommerce products together by their attributes; a new way to handle product variations.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2018/01/woocommerce-linked-variations-featured-twitter-790x395.png',
				'testimonial' => array(
					'title' => 'Excellent Support and Perfect Plugin',
					'text'  => 'WooCommerce Linked Variations solved one of my biggest issues to link products on product level. I tried several other plugins but I think this one is unique. Very easy to use. I highly recommend IconicWP!',
					'cite'  => 'Johan van der Werf',
				),
				'plugin_id'   => 1641,
				'plan_id'     => 2379,
				'public_key'  => 'pk_87add52278df025bc244015c9ef2a',
			),
			'iconic-woo-product-configurator'         => array(
				'class'       => 'jckpc',
				'title'       => 'WooCommerce Product Configurator',
				'url'         => 'https://iconicwp.com/products/woocommerce-product-configurator/',
				'description' => __( 'Use transparent image layers for your variable products, removing the need to create hundreds of final product variation images.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2016/02/woocommerce-product-configurator-featured-twitter-790x395.png',
				'testimonial' => array(
					'title' => 'Nothing But Good!',
					'text'  => 'This plugin is a user experience dream! It works as it should and allows my web visitors to easily create their own custom products. On top of that, the customer service is absolutely wonderful.',
					'cite'  => 'Michael Bainbridge, AHA Factory GmbH',
				),
				'plugin_id'   => 1039,
				'plan_id'     => 1790,
				'public_key'  => 'pk_fed17532221f66e11a200b70db56c',
			),
			'iconic-woo-quicktray'                    => array(
				'class'       => 'Iconic_Woo_QuickTray',
				'title'       => 'WooCommerce QuickTray',
				'url'         => 'https://iconicwp.com/products/woocommerce-quicktray/',
				'description' => __( 'A modern take on quick view for WooCommerce; expand product details within your product listings for a convenient shopping experience.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2017/11/woocommerce-quicktray-featured-twitter-790x395.png',
				'testimonial' => false,
				'plugin_id'   => 1324,
				'plan_id'     => 1871,
				'public_key'  => 'pk_2bc0461424587726347d21608c95e',
			),
			'iconic-woo-account-pages'                => array(
				'class'       => 'Iconic_Woo_Account_Pages',
				'title'       => 'WooCommerce Account Pages',
				'url'         => 'https://iconicwp.com/products/woocommerce-account-pages/',
				'description' => __( 'Add and manage pages in your WooCommerce "My Account" area using the native WordPress "Pages" functionality.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2017/08/woocommerce-account-pages-featured-twitter-790x395.png',
				'testimonial' => false,
				'plugin_id'   => 1265,
				'plan_id'     => 1768,
				'public_key'  => 'pk_ff29a26b1437ad8e58d32b90b7e7a',
			),
			'iconic-woo-quickview'                    => array(
				'class'       => 'jckqv',
				'title'       => 'WooCommerce Quickview',
				'url'         => 'https://iconicwp.com/products/woocommerce-quickview/',
				'description' => __( 'Quickly view any product from the catalog, without reloading the page. Encourage sales with easy and efficient product browsing.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2016/02/woocommerce-quickview-featured-twitter-790x395.png',
				'testimonial' => array(
					'title' => 'Most Useful Plugins',
					'text'  => 'Great plugin to compliment the rest of my IconicWP collection. Had a request for a modification that was handled immediately. Amazing and fast response. By far some of the best and most useful plugins!',
					'cite'  => 'superbrecs',
				),
				'plugin_id'   => 1037,
				'plan_id'     => 1813,
				'public_key'  => 'pk_cbcb0552db131fd591137450c33d7',
			),
			'iconic-woo-attribute-swatches'           => array(
				'class'       => 'Iconic_Woo_Attribute_Swatches',
				'title'       => 'WooCommerce Attribute Swatches',
				'url'         => 'https://iconicwp.com/products/woocommerce-attribute-swatches/',
				'description' => __( 'Bored of the standard dropdown fields when viewing a variable product? Turn them into colour, image, or text swatches!', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2016/05/woocommerce-attribute-swatches-featured-twitter-790x395.png',
				'testimonial' => array(
					'title' => 'Great Plugin and Great Support',
					'text'  => 'We’ve used similar plugins in the past but this plugin has better features and it’s also easier to manage backend. The support is great! Fast replies and never a problem getting things sorted.',
					'cite'  => 'Anna Matsson',
				),
				'plugin_id'   => 1041,
				'plan_id'     => 1765,
				'public_key'  => 'pk_7b128a35b24f5882ab7935dc845d4',
			),
			'iconic-woo-custom-fields-for-variations' => array(
				'class'       => 'Iconic_CFFV',
				'title'       => 'WooCommerce Custom Fields for Variations',
				'url'         => 'https://iconicwp.com/products/woocommerce-custom-fields-variations/',
				'description' => __( 'Easily add custom fields to your product variations; the perfect way to display organised additional product data to your customers.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2016/06/woocommerce-custom-fields-for-variations-featured-twitter-790x395.png',
				'testimonial' => false,
				'plugin_id'   => 1040,
				'plan_id'     => 2168,
				'public_key'  => 'pk_b581bd1b38e6033604046bacc8dc9',
			),
			'iconic-woo-bundled-products'             => array(
				'class'       => 'Iconic_Woo_Bundled_Products',
				'title'       => 'WooCommerce Bundled Products',
				'url'         => 'https://iconicwp.com/products/woocommerce-bundled-products/',
				'description' => __( 'Bundle a selection of products on a single product page. It even works with variable products!', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2016/12/woocommerce-bundled-products-featured-twitter-790x395.png',
				'testimonial' => false,
				'plugin_id'   => 1042,
				'plan_id'     => 1932,
				'public_key'  => 'pk_a669b9e5ac3ad536e2f0c30f549a4',
			),
			'iconic-woo-sales-booster'                => array(
				'class'       => 'Iconic_Woo_Sales_Booster',
				'title'       => 'Iconic Sales Booster',
				'url'         => 'https://iconicwp.com/products/sales-booster-for-woocommerce/',
				'description' => __( 'Use proven cross-selling techniques to increase the average order value of your store.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2020/08/sales-booster-banner-4.png',
				'testimonial' => false,
				'plugin_id'   => 3212,
				'plan_id'     => 5115,
				'public_key'  => 'pk_3ff1f2e5cb38f67915e2b154565d6',
			),
			'flux-checkout'                           => array(
				'class'       => 'flux_is_active',
				'title'       => 'Flux Checkout',
				'url'         => 'https://iconicwp.com/products/flux-checkout-for-woocommerce/',
				'description' => __( 'Prevent abandoned carts with a slick multi-step checkout experience, designed for your customer’s device.', 'iconic-wsb' ),
				'image_src'   => 'https://iconicwp.com/wp-content/uploads/2020/08/flux-checkout-banner-dark-5.png',
				'testimonial' => false,
				'plugin_id'   => 5383,
				'plan_id'     => 8934,
				'public_key'  => 'pk_62e3953ae5e35b8b1163a29e035a7',
			),
		);
	}

	/**
	 * Get selected plugins.
	 *
	 * @param array $slugs Slugs.
	 *
	 * @return bool|array
	 */
	public static function get_selected_plugins( $slugs = array() ) {
		if ( empty( $slugs ) ) {
			return false;
		}

		$plugins          = self::get_plugins();
		$selected_plugins = array();

		foreach ( $slugs as $slug ) {
			if ( empty( $plugins[ $slug ] ) ) {
				continue;
			}

			if ( class_exists( $plugins[ $slug ]['class'] ) || function_exists( $plugins[ $slug ]['class'] ) ) {
				continue;
			}

			$selected_plugins[ $slug ] = $plugins[ $slug ];
		}

		if ( empty( $selected_plugins ) ) {
			return false;
		}

		return $selected_plugins;
	}

	/**
	 * Output cross sells.
	 *
	 * @return bool|string
	 */
	public static function get_output() {
		if ( empty( self::$selected_plugins ) ) {
			return false;
		}

		$utm = '?utm_source=Iconic&utm_medium=Plugin&utm_campaign=iconic-wsb&utm_content=cross-sell';

		ob_start();
		?>
		<div class="iconic-cross-sells-wrapper">
			<div class="iconic-cross-sells-columns iconic-cross-sells-columns--plugins">
				<div class="iconic-cross-sells">
					<?php foreach ( self::$selected_plugins as $cross_sell ) { ?>
						<div class="iconic-cross-sell">
							<a class="iconic-cross-sell__image-link" href="<?php echo esc_url( $cross_sell['url'] . $utm ); ?>" target="_blank">
								<img class="iconic-cross-sell__image" src="<?php echo esc_url( $cross_sell['image_src'] ); ?>" alt="<?php echo esc_attr( $cross_sell['title'] ); ?>">
							</a>
							<div class="iconic-cross-sell__border">
								<h4 class="iconic-cross-sell__title">
									<a href="<?php echo esc_url( $cross_sell['url'] . $utm ); ?>" target="_blank">
										<?php echo esc_html( $cross_sell['title'] ); ?>
									</a>
								</h4>
								<p class="iconic-cross-sell__description"><?php echo esc_html( $cross_sell['description'] ); ?></p>
								<div class="iconic-cross-sell__footer">
									<a class="iconic-cross-sell__link button-secondary" href="<?php echo esc_attr( $cross_sell['url'] . $utm ); ?>" target="_blank">
										<?php echo esc_html( __( 'Learn More', 'iconic-wsb' ) ); ?>
										<span class="dashicons dashicons-external"></span>
									</a>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>

				<a href="https://iconicwp.com/products/<?php echo esc_url( $utm ); ?>" class="button-secondary"><?php echo esc_html( __( 'View All Iconic Plugins', 'iconic-wsb' ) ); ?></a>
			</div>

			<div class="iconic-cross-sells-columns iconic-cross-sells-columns--testimonials">
				<p class="iconic-trust">
					<span class="dashicons dashicons-thumbs-up"></span> <?php echo esc_html( __( 'Trusted by over 10,000 WooCommerce Businesses and Online Shops', 'iconic-wsb' ) ); ?>
				</p>
				<?php foreach ( self::$selected_plugins as $cross_sell ) { ?>
					<?php
					if ( ! $cross_sell['testimonial'] ) {
						continue;
					}
					?>

					<div class="iconic-testimonial">
						<div class="iconic-testimonial__stars">
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
						</div>
						<h5 class="iconic-testimonial__title"><?php echo esc_html( $cross_sell['testimonial']['title'] ); ?></h5>
						<p class="iconic-testimonial__text"><?php echo esc_html( $cross_sell['testimonial']['text'] ); ?></p>
						<p class="iconic-testimonial__cite">
							<strong><?php echo esc_html( $cross_sell['testimonial']['cite'] ); ?></strong> on
							<a href="<?php echo esc_attr( $cross_sell['url'] . $utm ); ?>" target="_blank"><?php echo esc_html( $cross_sell['title'] ); ?></a>
						</p>
					</div>
				<?php } ?>
			</div>
		</div>

		<style>
			.wpsf-tab .wpsf-section-description--cross-sells {
				margin: 0;
				padding: 20px 40px 40px;
				border: none;
			}

			.iconic-cross-sells-wrapper {
				overflow: hidden;
				padding-bottom: 1px;
			}

			.iconic-cross-sells {
				margin: 0 -2%;
				overflow: hidden;
			}

			.iconic-cross-sell {
				display: inline-block;
				float: left;
				width: 46%;
				margin: 0 2% 30px !important;
				padding: 0;
				border-radius: 3px;
				overflow: hidden;
				max-width: 395px;
			}

			.iconic-cross-sell:nth-child( 2n+1 ) {
				clear: both;
			}

			.iconic-cross-sell__border {
				border: 1px solid #CCCCCC;
				border-top: none;
				border-bottom-width: 2px;
				overflow: hidden;
				padding: 25px 0 0;
			}

			.iconic-cross-sell__image-link {
				display: block;
				margin: 0;
			}

			.iconic-cross-sell__image {
				max-width: 100%;
				width: 100%;
				height: auto;
				margin: 0;
				display: block;
			}

			.iconic-cross-sell__title,
			.iconic-cross-sell__description {
				margin: 0 25px 15px;
			}

			.iconic-cross-sell__title {
				font-size: 1.2em;
			}

			.iconic-cross-sell__description {
				font-size: 1em !important;
				margin-bottom: 0;
			}

			.iconic-cross-sell__footer {
				background: #F1F1F1;
				margin: 25px 0 0;
				padding: 15px 25px;
				border-top: 1px solid #CCCCCC;
			}

			.iconic-cross-sell__link span {
				font-size: 15px;
				vertical-align: text-top;
				height: 15px;
				width: 15px;
			}

			.iconic-trust {
				margin: 0 0 30px;
			}

			.iconic-testimonial {
				margin: 0 0 30px;
			}

			.iconic-testimonial:last-of-type {
				margin-bottom: 0;
			}

			.iconic-testimonial__stars {
				color: #F7D550;
			}

			.iconic-testimonial__title {
				font-size: 1.2em;
				margin: 10px 0 15px;
			}

			.iconic-testimonial__text,
			.iconic-testimonial__cite {
				font-size: 1em !important;
			}

			.iconic-cross-sells-columns {
				float: left;
			}

			.iconic-cross-sells-columns--plugins {
				width: 65%;
				max-width: 800px;
			}

			.iconic-cross-sells-columns--testimonials {
				width: 35%;
				max-width: 420px;
				padding-left: 60px;
				box-sizing: border-box;
			}

			@media only all and (max-width: 1090px) {
				.iconic-cross-sells-columns--plugins,
				.iconic-cross-sells-columns--testimonials {
					width: 100%;
				}

				.iconic-cross-sells-columns--testimonials {
					padding-left: 0;
					margin: 60px 0 0;
				}
			}

			@media only all and (max-width: 640px) {
				.iconic-cross-sell {
					width: 96%;
					max-width: none;
				}
			}
		</style>
		<?php
		return ob_get_clean();
	}
}
