<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class WCCT_Campaign
 * @package Finale-Lite
 * @author XlPlugins
 */
class WCCT_Campaign {

	public static $extend = array();
	private static $ins = null;
	private static $_registered_entity = array(
		'active'   => array(),
		'inactive' => array(),
	);
	public $wcct_data = array();
	public $wp_loaded = false;
	public $loop_campaigns = array();
	public $all_campaigns = array();
	public $is_mini_cart = false;
	public $deals = array();
	public $goals = array();
	public $single_campaign = array();
	public $current_cart_item = null;
	public $single_product_css = array();
	public $product_obj = array();
	public $campaign_goal = array();

	public function __construct() {

		add_action( 'wp', array( $this, 'wcct_reset_logs' ), 1 );

		/**
		 * Removing set global data on the_post hook as we have already hooked this on wc_get_product which already runs on the_post hook by woocommerce
		 * Since 2.1.0
		 */
		//add_action( 'the_post', array( $this, 'wcct_set_global_data' ), 99, 1 );
		add_action( 'wp', array( $this, 'is_flag_loaded' ), 1 );
		add_action( 'wp', array( $this, 'setup_cart_data' ), 1 );

		add_action( 'wp_head', array( $this, 'wcct_page_noindex' ) );

		/**
		 * When Variation on single Product Page loads via AJAX, we need to setup finale data to further managing product attributes.
		 */
		add_filter( 'woocommerce_show_variation_price', array( $this, 'maybe_setup_finale_data' ), 10, 3 );
		/**
		 * Setting up Finale campaign on wc_get_product function 'woocommerce_product_type_query' filter hook
		 */
		add_filter( 'woocommerce_product_type_query', array( $this, 'maybe_setup_finale_campaign' ), 10, 2 );

		/**
		 * Setting up Admin Bar data
		 */
		add_action( 'wp_footer', array( $this, 'wcct_set_admin_bar_data' ), 1 );

		/**
		 *
		 * This is for controlling the behaviour of saving product using Rest Api.
		 */
		add_action( 'woocommerce_update_product', array( $this, 'delete_product_taxonomy_ids_meta' ), 10, 1 );

		$this->wcct_data = new WCCT_Triggers_Data();
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	/**
	 * Set Global campaign data against product id
	 *
	 * Depreciated
	 *
	 * @param $data
	 *
	 * @return
	 * @global $post
	 * @global $product
	 *
	 */
	public function wcct_set_global_data( $data ) {

		if ( WCCT_Common::$is_executing_rule ) {
			return $data;
		}
		global $product, $expiry_text;

		wcct_force_log( 'the_post hook: ' . $data->ID );
		if ( is_object( $product ) ) {
			$tempId = $this->wcct_get_product_parent_id( $product );
			if ( 'grouped' === $product->get_type() ) {
				WCCT_Core()->public->wcct_get_product_obj( $tempId );
				$this->get_single_campaign_pro_data( $tempId, true ); // setting campaign data for parent product (grouped)
				$product->get_children();
				foreach ( $product->get_children() as $child_id ) {
					WCCT_Core()->public->wcct_get_product_obj( $child_id );
					$this->get_single_campaign_pro_data( $child_id, true ); // setting campaign data for childrens
				}
			} else {
				WCCT_Core()->public->wcct_get_product_obj( $tempId );

				$this->get_single_campaign_pro_data( $tempId, true ); // setting campaign data for main product

			}

			if ( is_object( WCCT_Common::$wcct_post ) && ( ( is_singular( 'product' ) && WCCT_Common::$wcct_post->ID == $tempId ) || WCCT_Common::$wcct_post->ID === null ) ) {

				$expiry_text = ( $this->single_campaign[ $tempId ] && isset( $this->single_campaign[ $tempId ]['expiry_text'] ) ) ? $this->single_campaign[ $tempId ]['expiry_text'] : '';

			}
		}

		do_action( 'wcct_data_setup_done', $data->ID );

		return $data;
	}

	/**
	 * Get Product parent id  for both version of woocommerce 2.6 and >3.0
	 *
	 * @param WC_Product $product
	 *
	 * @return integer
	 */
	public function wcct_get_product_parent_id( $product ) {
		$parent_id = 0;

		if ( $product instanceof WC_Product ) {
			$product_type            = $product->get_type();
			$product_variation_types = WCCT_Common::get_variation_league_product_types();

			if ( in_array( $product_type, $product_variation_types, true ) ) {
				$parent_id = WCCT_Common::get_post_parent_id( $product->get_id() );
				if ( false === $parent_id ) {
					$parent_id = $product->get_id();
				}
			} else {
				/** not a variation or subscription variation */
				$parent_id = $product->get_id();
			}
		} elseif ( 0 !== $product ) {
			$parent_id = WCCT_Common::get_post_parent_id( $product );

			if ( empty( $parent_id ) ) {
				$parent_id = (int) $product;
			}
		}

		return $parent_id;
	}

	/**
	 * Get product object if already set using product ID
	 *
	 * @param $product_id
	 *
	 * @return
	 */
	public function wcct_get_product_obj( $product_id ) {
		if ( isset( WCCT_Core()->public->product_obj[ $product_id ] ) && is_object( WCCT_Core()->public->product_obj[ $product_id ] ) ) {
			return WCCT_Core()->public->product_obj[ $product_id ];
		} else {
			$new_obj = wc_get_product( $product_id );

			if ( $new_obj instanceof WC_Product ) {
				WCCT_Core()->public->product_obj[ $product_id ] = $new_obj;
			}

			return $new_obj;
		}
	}

	/**
	 * Retrieve Campaign Data against product id
	 *
	 * @param $id
	 * @param bool $the_post
	 * @param bool $skip_rules
	 * @param bool $force
	 *
	 * @return bool|mixed
	 */
	public function get_single_campaign_pro_data( $id, $the_post = false, $skip_rules = false, $force = false ) {

		if ( true === $the_post && defined( 'DOING_AJAX' ) && ( in_array( filter_input( INPUT_POST, 'action' ), $this->get_restricted_action(), true ) || in_array( filter_input( INPUT_GET, 'action' ), $this->get_restricted_action(), true ) ) ) {
			return false;
		}

		/** Return in case of 3rd party plugin, where there is no need to setup Finale campaign */
		if ( false === apply_filters( 'wcct_force_do_not_run_campaign', true, $this ) ) {
			return false;
		}

		/**
		 * This filter forces data setup & overrides the argument provided.
		 * In Finale deal pages we need data setup to be done while running product loop so that 'grid_timer' & 'grid_bar' can be hooked into the campaign data.
		 */
		$campaign_data_force = apply_filters( 'wcct_campaign_data_force', false, $id, $the_post, $skip_rules, $force );

		if ( true === $campaign_data_force ) {
			$force    = true;
			$the_post = true;
		}

		if ( $force === false && isset( $this->single_campaign[ $id ] ) && is_array( $this->single_campaign[ $id ] ) && count( $this->single_campaign[ $id ] ) > 0 ) {
			$single_data = $this->single_campaign[ $id ];
		} else {
			if ( ! isset( $this->single_campaign[ $id ] ) ) {
				$this->single_campaign[ $id ] = array();
			}

			if ( $the_post === true ) {
				remove_filter( 'woocommerce_product_type_query', array( $this, 'maybe_setup_finale_campaign' ), 10, 2 );
				$this->single_campaign[ $id ] = $this->wcct_data->wcct_maybe_process_data( $id, false, $skip_rules );
				add_filter( 'woocommerce_product_type_query', array( $this, 'maybe_setup_finale_campaign' ), 10, 2 );

				wcct_force_log( 'Product id ' . $id . ' : DATA SET UP : CHECK DATA BELOW' );
				wcct_force_log( print_r( $this->single_campaign[ $id ], true ) );
				do_action( 'wcct_data_setup_completed', $this->single_campaign[ $id ], $id );
			}
			$single_data = $this->single_campaign[ $id ];
		}

		if ( WCCT_Common::$wcct_post && $id == WCCT_Common::$wcct_post->ID && isset( $single_data['custom_css'] ) && count( $single_data['custom_css'] ) > 0 ) {
			foreach ( $single_data['custom_css'] as $key => $val ) {
				$this->setup_custom_css( $key, $val );
			}
		}

		return $single_data;
	}

	public function get_restricted_action() {
		$restricted_actions = array(
			'oembed-cache',
			'image-editor',
			'delete-comment',
			'delete-tag',
			'delete-link',
			'delete-meta',
			'delete-post',
			'trash-post',
			'untrash-post',
			'delete-page',
			'dim-comment',
			'add-link-category',
			'add-tag',
			'get-tagcloud',
			'get-comments',
			'replyto-comment',
			'edit-comment',
			'add-menu-item',
			'add-meta',
			'add-user',
			'closed-postboxes',
			'hidden-columns',
			'update-welcome-panel',
			'menu-get-metabox',
			'wp-link-ajax',
			'menu-locations-save',
			'menu-quick-search',
			'meta-box-order',
			'get-permalink',
			'sample-permalink',
			'inline-save',
			'inline-save-tax',
			'find_posts',
			'widgets-order',
			'save-widget',
			'delete-inactive-widgets',
			'set-post-thumbnail',
			'date_format',
			'time_format',
			'wp-remove-post-lock',
			'dismiss-wp-pointer',
			'upload-attachment',
			'get-attachment',
			'query-attachments',
			'save-attachment',
			'save-attachment-compat',
			'send-link-to-editor',
			'send-attachment-to-editor',
			'save-attachment-order',
			'heartbeat',
			'get-revision-diffs',
			'save-user-color-scheme',
			'update-widget',
			'query-themes',
			'parse-embed',
			'set-attachment-thumbnail',
			'parse-media-shortcode',
			'destroy-sessions',
			'install-plugin',
			'update-plugin',
			'crop-image',
			'generate-password',
			'save-wporg-username',
			'delete-plugin',
			'search-plugins',
			'search-install-plugins',
			'activate-plugin',
			'update-theme',
			'delete-theme',
			'install-theme',
			'get-post-thumbnail-html',
			'get-community-events',
			'edit-theme-plugin-file',
			'wp-privacy-export-personal-data',
			'wp-privacy-erase-personal-data',
			'wcct_quick_view_html',
			'wcct_change_rule_type',
			'woocommerce_json_search_products',
			'woocommerce_json_search_products_and_variations',
			'woocommerce_add_attribute',
			'woocommerce_add_new_attribute',
			'woocommerce_save_attributes',
			'woocommerce_add_variation',
			'woocommerce_load_variations',
			'woocommerce_save_variations',
			'woocommerce_remove_variations',
			'woocommerce_link_all_variations',
			'woocommerce_bulk_edit_variations',
			'woocommerce_toggle_gateway_enabled',
			'woocommerce_get_order_details',
			'woocommerce_do_ajax_product_export',
			'woocommerce_do_ajax_product_import',
			'woocommerce_get_customer_details',
			'woocommerce_load_order_items',
			'woocommerce_add_coupon_discount',
			'woocommerce_remove_order_coupon',
			'woocommerce_add_order_fee',
			'woocommerce_add_order_shipping',
			'woocommerce_remove_order_item',
			'woocommerce_remove_order_tax',
			'woocommerce_calc_line_taxes',
			'woocommerce_save_order_items',
			'woocommerce_refund_line_items',
			'woocommerce_delete_refund',
			'woocommerce_add_order_tax',
			'woocommerce_add_order_note',
			'woocommerce_delete_order_note',
			'woocommerce_grant_access_to_download',
			'woocommerce_revoke_access_to_download',
			'wfocu_add_new_funnel',
			'wfocu_add_offer',
			'wfocu_add_product',
			'wfocu_remove_product',
			'wfocu_save_funnel_steps',
			'wfocu_save_funnel_offer_products',
			'wfocu_save_funnel_offer_settings',
			'wfocu_product_search',
			'wfocu_page_search',
			'wfocu_update_offer',
			'wfocu_update_funnel',
			'wfocu_remove_offer_from_funnel',
			'wfocu_get_custom_page',
			'wfocu_save_rules_settings',
			'wfocu_update_template',
			'wfocu_save_funnel_settings',
			'wfocu_save_global_settings',
			'wfocu_preview_details',
			'wfocu_toggle_funnel_state',
			'wfocu_front_charge',
			'wfocu_front_offer_skipped',
			'wfocu_front_calculate_shipping',
			'wfocu_front_calculate_shipping_variation',
			'wfocu_front_register_views',
			'wfocu_front_offer_expired',
		);
		$restricted_actions = array_unique( $restricted_actions );

		return apply_filters( 'wcct_get_restricted_action', $restricted_actions );
	}

	/**
	 * Hold Custom Css against campaign  id
	 *
	 * @param $j
	 * @param $css
	 *
	 */
	public function setup_custom_css( $j, $css ) {
		if ( empty( $css ) ) {
			return false;
		}
		$this->single_product_css[ $j ] = $css;
	}

	public function wcct_set_admin_bar_data() {
		do_action( 'wcct_data_setup_done' );
	}

	/* ======================================================Cart Section start here ============================================ */

	/**
	 * Checking product type is booking if yes return false
	 *
	 * @param $product_id
	 * @param $return_type
	 *
	 * @return boolean
	 */
	public function wcct_restrict_for_booking_oth( $product_id, $type = false ) {
		$restrict = false;
		if ( false === $type ) {
			$product_global = WCCT_Core()->public->wcct_get_product_obj( $product_id );

			if ( ! $product_global instanceof WC_Product ) {
				return $restrict;
			}
			$type = $product_global->get_type();
		}

		if ( 'booking' === $type ) {
			$restrict = true;
		}

		return $restrict;
	}

	public function setup_cart_data() {

		if ( ( is_cart() || is_checkout() ) && false === WCCT_Common::$is_executing_rule ) {
			$get_cart = WC()->cart->get_cart();
			if ( $get_cart && count( $get_cart ) > 0 ) {
				foreach ( $get_cart as $cartitem ) {

					WCCT_Core()->public->wcct_get_product_obj( $cartitem['product_id'] );
					$this->get_single_campaign_pro_data( $cartitem['product_id'], true );
				}
			}
		}
	}

	/* ======================================================Cart Section End here ============================================ */

	public function wcct_reset_logs() {

		if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && is_singular( 'product' ) ) {
			if ( ( true === WCCT_Common::$is_force_debug ) || ( WP_DEBUG === true && ! is_admin() ) ) {
				wcct_force_log( 'abs', 'force.txt', 'w' );
			}
		}

	}




	/* ======================================================Pricing & stock Section Start here ============================================ */

	/**
	 * Setting Product Goal using campaign setting
	 *
	 * @param WC_Product $product
	 * @param $goals
	 * @param $campaign_id
	 *
	 * @return
	 */
	public function wcct_set_goal_meta( $product, $product_id, $goals, $campaign_id ) {
		$goals_meta = array();
		$start_time = (int) $goals['start_timestamp'];
		$end_time   = (int) $goals['end_timestamp'];

		if ( isset( $this->campaign_goal[ $product_id ] ) ) {
			return $this->campaign_goal[ $product_id ];
		}

		if ( $product_id > 0 && $start_time > 0 && $end_time > 0 && $campaign_id > 0 ) {
			$wcct_deal_meta_key         = "_wcct_goaldeal_{$campaign_id}_{$start_time}_{$end_time}";
			$wcct_sold_out_key          = "_wcct_goaldeal_sold_{$campaign_id}_{$start_time}_{$end_time}";
			$wcct_sold_out_campaign_key = "_wcct_goaldeal_sold_{$campaign_id}";

			$goals_meta                   = get_post_meta( $product_id, $wcct_deal_meta_key, true );
			$wcct_campaign_sold_out       = get_post_meta( $product_id, $wcct_sold_out_key, true );
			$wcct_campaign_total_sold_out = get_post_meta( $product_id, $wcct_sold_out_campaign_key, true );
			$wcct_campaign_sold_out       = (int) ( '' !== $wcct_campaign_sold_out ? $wcct_campaign_sold_out : 0 );

			if ( ! is_array( $goals_meta ) ) {
				if ( 'same' === $goals['type'] ) {

					/**
					 * handling for the variation product, will show for the products which are not managing stock at variable level
					 */
					if ( in_array( $product->get_type(), WCCT_Common::get_variable_league_product_types(), true ) ) {
						if ( WCCT_Common::get_total_stock( $product ) > 0 ) {
							$unit = WCCT_Common::get_total_stock( $product );
						} else {
							return array();
						}
					} else {
						if ( $product->managing_stock() && WCCT_Common::get_total_stock( $product ) ) {
							$unit = WCCT_Common::get_total_stock( $product );
						} else {
							return array();
						}
					}
				} else {
					$unit = (int) $goals['deal_custom_units'];
				}
				if ( (int) $unit < 0 ) {
					$unit = 0;
				}
				$goals_meta = array(
					'quantity'    => $unit,
					'type'        => $goals['type'],
					'campaign_id' => $campaign_id,
				);
				update_post_meta( $product_id, $wcct_deal_meta_key, $goals_meta );

			} else {
				if ( $goals['type'] !== $goals_meta['type'] ) {
					if ( 'same' === $goals['type'] ) {
						$unit = get_post_meta( $product_id, '_stock', true );
					} else {
						$unit = (int) $goals['deal_custom_units'];
					}
					$goals_meta = array(
						'quantity'    => $unit,
						'type'        => $goals['type'],
						'campaign_id' => $campaign_id,
					);
					update_post_meta( $product_id, $wcct_deal_meta_key, $goals_meta );
				} elseif ( 'custom' === $goals_meta['type'] ) {
					if ( (int) $goals_meta['quantity'] !== (int) $goals['deal_custom_units'] ) {
						$goals_meta = array(
							'quantity'    => (int) $goals['deal_custom_units'],
							'type'        => 'custom',
							'campaign_id' => $campaign_id,
						);
						update_post_meta( $product_id, $wcct_deal_meta_key, $goals_meta );
					}
				} elseif ( 'same' === $goals_meta['type'] ) {
					$manage_stock_check = true;
					if ( in_array( $product->get_type(), WCCT_Common::get_simple_league_product_types(), true ) ) {
						$manage_stock_check = $product->managing_stock();
					}

					if ( $manage_stock_check && WCCT_Common::get_total_stock( $product ) > 0 ) {
						$unit = WCCT_Common::get_total_stock( $product );

						if ( 'recurrence' === $goals['inventry_goal_for'] ) {
							$check_total_stock = ( $wcct_campaign_sold_out ? $wcct_campaign_sold_out : 0 ) + $unit;
						}
						if ( 'campaign' === $goals['inventry_goal_for'] ) {
							$check_total_stock = ( $wcct_campaign_total_sold_out ? $wcct_campaign_total_sold_out : 0 ) + $unit;
						}
						if ( $check_total_stock != $goals_meta['quantity'] ) {
							$goals_meta = array(
								'quantity'    => $check_total_stock,
								'type'        => 'same',
								'campaign_id' => $campaign_id,
							);
							update_post_meta( $product_id, $wcct_deal_meta_key, $goals_meta );
						}
					}
				}
			}

			$get_event_sold_units = 0;
			/**
			 * Adding sold units dynamically to the set default units
			 */
			if ( isset( $goals['default_sold_out'] ) ) {
				$get_event_sold_units = $goals['default_sold_out'];

			}

			$goals_meta['sold_out'] = $wcct_campaign_sold_out;
			if ( 'campaign' === $goals['inventry_goal_for'] ) {
				$goals_meta['sold_out'] = $wcct_campaign_total_sold_out;
			}

			/**
			 * Sometime there we have an error about non numeric value set over there.
			 */
			if ( '' === $goals_meta['sold_out'] ) {
				$goals_meta['sold_out'] = 0;
			}
			$goals_meta['campaign_id']         = (int) $campaign_id;
			$goals_meta['price']               = (float) get_post_meta( $product_id, '_price', true );
			$goals_meta['sold_out']            += ( $get_event_sold_units ) ? $get_event_sold_units : 0;
			$goals_meta['sold_out_type']       = $goals['inventry_goal_for'];
			$goals_meta['sold_out_recurrence'] = (int) $wcct_campaign_sold_out;
			$goals_meta['sold_out_campaign']   = (int) $wcct_campaign_total_sold_out;

		}
		$this->campaign_goal[ $product_id ] = $goals_meta;
		wcct_force_log( "Product id {$product_id} : function wcct_set_goal_object / get goals data  " . print_r( $this->campaign_goal[ $product_id ], true ) );

		return $goals_meta;
	}

	/**
	 * Hooked over `wp`
	 * Marking flag wp loaded so that other functions can apply that flag to not run before this flag set
	 */
	public function is_flag_loaded() {
		$this->wp_loaded = true;
	}

	public function wcct_trigger_counter_bar_hide( $status, $sold_quantity ) {
		if ( $sold_quantity == 0 ) {
			return false;
		}

		return $status;
	}

	public function change_price_according_to_date( $deal_percetage, $product_gloabal, $data ) {
		$goals_meta = $this->wcct_get_goal_object( $data['goals'], $product_gloabal->id );
		if ( count( $goals_meta ) > 0 ) {
			$remainig_pr = $goals_meta['quantity'] - $goals_meta['sold_out'];
			if ( $remainig_pr <= 50 && $remainig_pr > 40 ) {
				$deal_percetage = 40;
			} elseif ( $remainig_pr <= 40 && $remainig_pr > 30 ) {
				$deal_percetage = 20;
			} else {
				$deal_percetage = 10;
			}
		}

		return $deal_percetage;
	}

	/**
	 * Retrieve Final Goal Object for counter bar against product id
	 *
	 * @param $goals
	 * @param $product_id
	 * @param $is_rule : check if it is a rule
	 *
	 * @return array
	 */
	public function wcct_get_goal_object( $goals, $product_id, $is_rule = false ) {
		$goals_meta = array();

		if ( $goals && is_array( $goals ) && count( $goals ) > 0 ) {
			$product = WCCT_Core()->public->wcct_get_product_obj( $product_id );
			if ( ! $product ) {
				return $goals_meta;
			}

			if ( isset( $this->campaign_goal[ $product_id ] ) && is_array( $this->campaign_goal[ $product_id ] ) && count( $this->campaign_goal[ $product_id ] ) > 0 ) {
				return $this->campaign_goal[ $product_id ];
			}
		}

		return __return_empty_array();

	}

	public function wcct_page_noindex() {
		$post_type = WCCT_Common::get_campaign_post_type_slug();
		if ( is_singular( $post_type ) ) {
			echo "<meta name='robots' content='noindex,follow' />\n";
		}
	}

	public function maybe_setup_finale_data( $is_show_variation, $variable, $variation ) {

		if ( $variable instanceof WC_Product ) {
			WCCT_Core()->public->get_single_campaign_pro_data( $variable->get_id(), true );

		}

		return $is_show_variation;
	}

	/**
	 * @param $value
	 * @param $product_id
	 *
	 * @return mixed
	 */
	public function maybe_setup_finale_campaign( $value, $product_id ) {
		global $post, $product;
		$maybe_run = apply_filters( 'wcct_maybe_setup_finale_campaign', true );

		if ( true === $maybe_run ) {
			/** Check if valid WooCommerce product */
			if ( $post instanceof WP_Post && $post->ID == $product_id ) {
				if ( ! in_array( $post->post_type, array( 'product', 'product_variation' ), true ) ) {
					return $value;
				}
			} elseif ( $product instanceof WC_Product && $product->get_id() == $product_id ) {
				if ( ! in_array( $product->get_type(), array( 'product', 'product_variation' ), true ) ) {
					return $value;
				}
			} else {
				$post_data = WCCT_Common::get_post_data( $product_id );
				if ( is_object( $post_data ) && ! in_array( $post_data->post_type, array( 'product', 'product_variation' ), true ) ) {
					return $value;
				}
			}

			$parent_id = WCCT_Core()->public->wcct_get_product_parent_id( $product_id );

			remove_filter( 'woocommerce_product_type_query', array( $this, 'maybe_setup_finale_campaign' ), 10, 2 );
			WCCT_Core()->public->wcct_get_product_obj( $parent_id );
			WCCT_Core()->public->get_single_campaign_pro_data( $parent_id, true );
			add_filter( 'woocommerce_product_type_query', array( $this, 'maybe_setup_finale_campaign' ), 10, 2 );
		}

		return $value;
	}

	public function delete_product_taxonomy_ids_meta( $post_id ) {
		delete_post_meta( $post_id, '_wcct_product_taxonomy_term_ids' );
	}


}

if ( class_exists( 'WCCT_Core' ) ) {
	WCCT_Core::register( 'public', 'WCCT_Campaign' );
}
