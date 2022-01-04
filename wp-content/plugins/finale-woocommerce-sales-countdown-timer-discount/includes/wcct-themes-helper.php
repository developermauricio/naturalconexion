<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'wcct_theme_helper_functions', 100 );

/**
 * Here we have popular themes fallback functions to support our plugin
 * @global type $post
 */
function wcct_theme_helper_functions() {

	if ( class_exists( 'Flatsome_Option' ) ) {
		/**
		 * Flatsome
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/flatsome/flatsome.php';
	} elseif ( function_exists( 'electro_setup' ) ) {
		/**
		 * Electro
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/electro/electro.php';
	} elseif ( defined( 'HCODE_THEME' ) ) {
		/**
		 * HCode
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/hcode/hcode.php';
	} elseif ( defined( 'DENSO_THEME_VERSION' ) ) {
		/**
		 * Denso
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/denso/denso.php';
	} elseif ( function_exists( 'accessories_shop_system_fonts_list' ) ) {
		/**
		 * Accessories Shop
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/accessories-shop/accessories-shop.php';
	} elseif ( defined( 'ETHEME_FW' ) ) {
		/**
		 * X Store
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/x-store/x-store.php';
	} elseif ( defined( 'JAS_CLAUE_PATH' ) ) {
		/**
		 * Claue
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/claue/claue.php';
	} elseif ( defined( 'PRESSCORE_VERSION' ) ) {
		/**
		 * The7
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/the-7/the-7.php';
	} elseif ( function_exists( 'shopkeeper_setup' ) ) {
		/**
		 * Shopkeeper
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/shopkeeper/shopkeeper.php';
	} elseif ( defined( 'FLOAT_TEMPLATE_PATH' ) ) {
		/**
		 * Float
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/float/float.php';
	} elseif ( class_exists( 'WR_Nitro' ) ) {
		/**
		 * Nitro
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/nitro/nitro.php';
	} elseif ( class_exists( 'WowMall' ) ) {
		/**
		 * Wowmall
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/wowmall/wowmall.php';
	} elseif ( defined( 'EVA_OPTIONS_NAME' ) ) {
		/**
		 * Eva
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/eva/eva.php';
	} elseif ( defined( 'THEME_NAME' ) && THEME_NAME == 'betheme' ) {
		/**
		 * Betheme
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/betheme/betheme.php';
	} elseif ( defined( 'TD' ) && TD == 'oxygen' ) {
		/**
		 * Oxygen
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/oxygen/oxygen.php';
	} elseif ( function_exists( 'getbowtied_theme_setup' ) ) {
		/**
		 * Merchandiser
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/merchandiser/merchandiser.php';
	} elseif ( class_exists( 'OCEANWP_Theme_Class' ) ) {
		/**
		 * Oceanwp
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/oceanwp/oceanwp.php';
	} elseif ( defined( 'porto_functions' ) ) {
		/**
		 * Porto
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/porto/porto.php';
	} elseif ( defined( 'TD' ) && TD == 'aurum' ) {
		/**
		 * Aurum
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/aurum/aurum.php';
	} elseif ( defined( 'NM_NAMESPACE' ) && NM_NAMESPACE == 'nm-framework' ) {
		/**
		 * Savoy
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/savoy/savoy.php';
	} elseif ( class_exists( 'Sober_WooCommerce' ) ) {
		/**
		 * Sober
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/sober/sober.php';
	} elseif ( function_exists( 'thegem_setup' ) ) {
		/**
		 * TheGem
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/thegem/thegem.php';
	} elseif ( function_exists( 'boxshop_theme_activation' ) ) {

		/**
		 * BoxShop
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/boxshop/boxshop.php';
	} elseif ( defined( 'TUCSON_THEME_NAME' ) && TUCSON_THEME_NAME == 'tucson' ) {
		/**
		 * tucson
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/tucson/tucson.php';
	} elseif ( function_exists( 'techmarket_breadcrumb' ) ) {
		/**
		 * Techmarket
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/techmarket/techmarket.php';
	} elseif ( defined( 'ASTRA_THEME_VERSION' ) ) {
		/**
		 * Astra
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/astra/astra.php';
	} elseif ( function_exists( 'znhg_kallyas_theme_config' ) ) {
		/**
		 * Kallayas
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/kallayas/kallayas.php';
	} elseif ( defined( 'ST_TRAVELER_VERSION' ) ) {
		/**
		 * Traveler
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/traveler/traveler.php';
	} elseif ( function_exists( 'uncode_truncate' ) ) {
		/**
		 * Uncode
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/uncode/uncode.php';
	} elseif ( defined( 'WOODMART_THEME_DIR' ) ) {
		/**
		 * Astra
		 */
		include_once plugin_dir_path( WCCT_PLUGIN_FILE ) . 'theme-support/woodmart/woodmart.php';
	}
}
