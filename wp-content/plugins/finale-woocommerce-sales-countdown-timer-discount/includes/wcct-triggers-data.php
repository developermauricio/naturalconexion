<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class WCCT_Triggers_Data
 * @package Finale-Lite
 * @author XlPlugins
 */
class WCCT_Triggers_Data {

	public $wcct_triggers_data = null;

	/**
	 * Contains all triggers data
	 * @var array
	 */
	protected $wcct_trigger_data = array();
	protected $wcct_product_metadata = array();


	/**
	 * WCCT_Triggers_Data constructor.
	 * Construct to call hooks and setting up properties
	 */
	public function __construct() {
		$this->cart_default_array       = array(
			'skin'         => 'square_ghost',
			'bg_color'     => '#c46e3c',
			'label_color'  => '#8224e3',
			'timer_font'   => '15',
			'label_font'   => '13',
			'display'      => 'Sale ends in {{countdown_timer}}',
			'label_days'   => 'D',
			'label_hrs'    => 'H',
			'label_mins'   => 'M',
			'label_secs'   => 'S',
			'border_width' => '1',
			'border_color' => '#444444',
			'border_style' => 'solid',
		);
		$this->grid_timer_default_array = array(
			'position'     => '',
			'skin'         => 'default',
			'bg_color'     => '#444444',
			'label_color'  => '#ffffff',
			'timer_font'   => '15',
			'label_font'   => '13',
			'display'      => 'Sale ends in {{countdown_timer}}',
			'label_days'   => 'days',
			'label_hrs'    => 'hrs',
			'label_mins'   => 'mins',
			'label_secs'   => 'secs',
			'border_width' => '1',
			'border_color' => '#444444',
			'border_style' => 'solid',
		);
		$this->grid_bar_default_array   = array(
			'skin'         => 'stripe_animate',
			'edge'         => 'rounded',
			'height'       => '16',
			'bg_color'     => '#dddddd',
			'active_color' => '#ee303c',
			'display'      => '{{counter_bar}} {{sold_units}} units sold out of {{total_units}}',
			'border_width' => '0',
			'border_color' => '#444444',
			'border_style' => 'none',

		);
	}

	/**
	 * Hooked over `wp`
	 * Checks if single product page
	 * Checks and prepare triggers data to be called in core file
	 */
	public function wcct_maybe_process_data( $ID = 0, $return_key = false, $skip_rules = false ) {
		global $wpdb;

		$this->wcct_trigger_data = array();
		$args                    = array(
			'post_type'        => WCCT_Common::get_campaign_post_type_slug(),
			'post_status'      => 'publish',
			'nopaging'         => true,
			'meta_key'         => '_wcct_campaign_menu_order',
			'orderby'          => 'meta_value_num',
			'order'            => 'ASC',
			'fields'           => 'ids',
			'suppress_filters' => false,   //WPML Compatibility
		);

		$xl_transient_obj = XL_Transient::get_instance();
		$xl_cache_obj     = XL_Cache::get_instance();

		$key = 'wcct_campaign_query';

		// handling for WPML
		if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE !== '' ) {
			$key .= '_' . ICL_LANGUAGE_CODE;
		}

		//Handling with PolyLang
		if ( function_exists( 'pll_current_language' ) ) {
			$current_lang = pll_current_language();
			$args['lang'] = $current_lang;
			$key          .= '_' . $current_lang;
		}

		$contents = array();
		do_action( 'wcct_before_query', $ID );

		/**
		 * Setting xl cache and transient for Finale campaign query
		 */
		$cache_data = $xl_cache_obj->get_cache( $key, 'finale' );
		if ( false !== $cache_data ) {
			$contents = $cache_data;
		} else {
			$transient_data = $xl_transient_obj->get_transient( $key, 'finale' );

			if ( false !== $transient_data ) {
				$contents = $transient_data;
			} else {
				$query_result = new WP_Query( $args );
				if ( $query_result instanceof WP_Query && $query_result->have_posts() ) {
					$contents = $query_result->posts;
					$xl_transient_obj->set_transient( $key, $query_result->posts, 7200, 'finale' );
				}
			}
			$xl_cache_obj->set_cache( $key, $contents, 'finale' );
		}

		do_action( 'wcct_after_query', $ID );

		if ( is_array( $contents ) && count( $contents ) > 0 ) {
			foreach ( $contents as $content_single ) {

				/**
				 * post instance extra checking added as some plugins may modify wp_query args on pre_get_posts filter hook
				 */
				$content_id = ( $content_single instanceof WP_Post && is_object( $content_single ) ) ? $content_single->ID : $content_single;

				$rule_result = WCCT_Common::match_groups( $content_id, $ID );

				if ( $skip_rules || true === $rule_result ) {
					$cache_key = 'wcct_countdown_post_meta_' . $content_id;

					/**
					 * Setting xl cache and transient for Finale single campaign meta
					 */
					$cache_data = $xl_cache_obj->get_cache( $cache_key, 'finale' );
					if ( false !== $cache_data ) {
						$parseObj = $cache_data;
					} else {
						$transient_data = $xl_transient_obj->get_transient( $cache_key, 'finale' );

						if ( false !== $transient_data ) {
							$parseObj = $transient_data;
						} else {
							$get_product_wcct_meta = get_post_meta( $content_id );
							$product_meta          = WCCT_Common::get_parsed_query_results_meta( $get_product_wcct_meta );
							$parseObj              = wp_parse_args( $product_meta, $this->parse_default_args_by_trigger( $product_meta ) );
							$xl_transient_obj->set_transient( $cache_key, $parseObj, 7200, 'finale' );
						}
						$xl_cache_obj->set_cache( $cache_key, $parseObj, 'finale' );
					}
					if ( ! $parseObj ) {
						continue;
					}
					$get_parsed_data                        = $this->parse_key_value( $parseObj );
					$this->wcct_trigger_data[ $content_id ] = $get_parsed_data;
				}
			}
		}

		if ( count( $this->wcct_trigger_data ) > 0 ) {

			return $this->wcct_triggers_public_data( $this->wcct_trigger_data, $return_key, $ID );
		} else {
			return array();
		}

	}

	public function parse_default_args_by_trigger( $data ) {
		$field_option_data = WCCT_Common::get_default_settings();

		foreach ( $field_option_data as $slug => $value ) {
			if ( strpos( $slug, '_wcct_' ) !== false ) {
				$data[ $slug ] = $value;
			}
		}

		return $data;
	}

	/**
	 * Parse and prepare data for single trigger
	 *
	 * @param $data Array Options data
	 * @param $trigger String Trigger slug
	 * @param string $mode options|product
	 *
	 * @return array
	 */
	public function parse_key_value( $data ) {
		$trigger_data = array();
		$prepare_key  = '_wcct_';
		foreach ( $data as $key => $field_val ) {
			if ( strpos( $key, $prepare_key ) === false ) {
				continue;
			}
			$key                  = str_replace( $prepare_key, '', $key );
			$trigger_data[ $key ] = apply_filters( 'wcct_filter_values', maybe_unserialize( $field_val ), $key );
		}

		return apply_filters( 'wcct_filter_trigger_data', $trigger_data );
	}

	public function wcct_triggers_public_data( $meta_data, $return_key = false, $product_id = 0 ) {
		$campaign_meta = $uniqueArr = $show_on_cart = $single_timer = $grid_timer = $goals = $grid_bar = $single_bar = $deals = $during_deal_campaign_final = $during_goal_campaign_final = $misc = $during_misc_deal = $during_misc_goal = $custom_css = array();

		$deal_end_time          = $deal_start_time = $goal_start_time = $goal_end_time = 0;
		$menu_order_campaign_id = $menu_order_start_time = $menu_order_end_time = 0;
		$expired_camp           = array();

		$running_camp = array();

		if ( is_array( $meta_data ) && count( $meta_data ) ) {
			foreach ( $meta_data as $campaign_id => $val ) {
				$threshold_reach_out   = false;
				$j                     = $campaign_id;
				$start_end             = WCCT_Common::start_end_timestamp( $val );
				$start_end['campaign'] = $j;

				$start_date_timestamp = $start_end['start_date_timestamp'];
				$end_date_timestamp   = $start_end['end_date_timestamp'];
				$todayDate            = $start_end['todayDate'];

				if ( $start_date_timestamp > 0 && $end_date_timestamp > 0 && $todayDate > 0 ) {
					$campaignType        = array(
						'type'            => isset( $val['campaign_type'] ) ? $val['campaign_type'] : '',
						'start_timestamp' => $start_date_timestamp,
						'end_timestamp'   => $end_date_timestamp,
					);
					$campaign_meta[ $j ] = array(
						'campaign_id' => $campaign_id,
						'start_time'  => $start_date_timestamp,
						'end_time'    => $end_date_timestamp,
					);

					if ( $todayDate >= $start_date_timestamp && $todayDate < $end_date_timestamp && ( $start_date_timestamp != $end_date_timestamp ) ) {
						$uniqueArr[ $j ]['campaign'] = $campaignType;

						/** Deal */
						if ( $product_id != '0' && isset( $val['deal_enable_price_discount'] ) && $val['deal_enable_price_discount'] == '1' && ( isset( $val['deal_amount'] ) && $val['deal_amount'] != '' ) ) {
							if ( $deal_start_time == 0 ) {
								$deal_start_time        = $start_date_timestamp;
								$deal_end_time          = $end_date_timestamp;
								$menu_order_campaign_id = $j;

							}
							$uniqueArr[ $j ]['deals'] = array(
								'type'          => $val['deal_type'],
								'deal_amount'   => $val['deal_amount'],
								'start_time'    => $deal_start_time,
								'end_time'      => $end_date_timestamp,
								'campaign_type' => $campaignType['type'],
								'override'      => ( isset( $val['deal_override_price_discount'] ) && $val['deal_override_price_discount'] == 'on' ) ? true : false,
								'campaign_id'   => $j,
							);
							$deals[ $j ]              = $uniqueArr[ $j ]['deals'];
						}

						/** Goal */
						if ( $product_id != '0' && isset( $val['deal_enable_goal'] ) && $val['deal_enable_goal'] == '1' ) {
							$allow_backorder = 'no';
							if ( isset( $val['deal_units'] ) ) {
								if ( $val['deal_units'] == 'custom' ) {
									if ( isset( $val['deal_custom_units_allow_backorder'] ) && $val['deal_custom_units_allow_backorder'] == 'yes' ) {
										$allow_backorder = 'yes';
									}
								}
								$uniqueArr[ $j ]['goals'] = array(
									'threshold'         => $val['deal_threshold_units'],
									'type'              => $val['deal_units'],
									'default_sold_out'  => 0,
									'deal_custom_units' => $val['deal_custom_units'],
									'is_custom'         => $val['deal_units'] == 'custom' ? 1 : 0,
									'start_timestamp'   => $start_date_timestamp,
									'end_timestamp'     => $end_date_timestamp,
									'campaign_type'     => $campaignType['type'],
									'allow_backorder'   => $allow_backorder,
									'campaign_id'       => $j,

								);
								if ( isset( $val['deal_inventory_goal_for'] ) && $val['deal_inventory_goal_for'] != '' ) {
									$uniqueArr[ $j ]['goals']['inventry_goal_for'] = $val['deal_inventory_goal_for'];
								}

								if ( $deal_start_time > 0 ) {
									$uniqueArr[ $j ]['goals']['start_timestamp'] = $deal_start_time;
									$uniqueArr[ $j ]['goals']['end_timestamp']   = $deal_end_time;
								} else {
									if ( $goal_start_time == 0 ) {
										$goal_start_time                             = $start_date_timestamp;
										$goal_end_time                               = $end_date_timestamp;
										$uniqueArr[ $j ]['goals']['start_timestamp'] = $goal_start_time;
										$uniqueArr[ $j ]['goals']['end_timestamp']   = $goal_end_time;
										$menu_order_campaign_id                      = $j;

									}
								}

								$goals[ $j ] = $uniqueArr[ $j ]['goals'];
							}
						}

						if ( isset( $goals[ $j ] ) && is_array( $goals[ $j ] ) && count( $goals[ $j ] ) > 0 && $product_id != '0' ) {
							$product_obj = WCCT_Core()->public->wcct_get_product_obj( $product_id );
							if ( $product_obj instanceof WC_Product ) {

								/** Added to run events login to modify goals data */
								$goal_out = WCCT_Core()->public->wcct_set_goal_meta( $product_obj, $product_obj->get_id(), $goals[ $j ], $j );

								if ( $goal_out == '' || ! is_array( $goal_out ) ) {
									$threshold_reach_out = false;
								} else {
									if ( is_array( $goal_out ) && count( $goal_out ) > 0 ) {
										/** threshold */
										$sold_qty_final = 0;
										if ( (int) $goal_out['quantity'] > 0 ) {
											$sold_qty_final = (int) $goal_out['sold_out'];
											if ( $goals[ $j ]['inventry_goal_for'] == 'campaign' ) {
												$sold_qty_final = (int) $goal_out['sold_out_campaign'];
											}
											$is_manage_stock = $product_obj->managing_stock();
											$threshold_qty   = (int) $goals[ $j ]['threshold'];

											if ( $is_manage_stock === true && ( (int) $goal_out['quantity'] - $sold_qty_final ) <= $threshold_qty ) {
												unset( $goals[ $j ] );
												if ( isset( $val['deal_end_campaign'] ) && $val['deal_end_campaign'] == 'yes' ) {
													$threshold_reach_out = true;
												}
											}
										}

										/** checking if sold out is greater than total custom quantity if yes please end campaign */
										if ( $goal_out['type'] == 'custom' && isset( $goals[ $j ] ) ) {
											$sold_qty_final = $sold_qty_final + $goals[ $j ]['default_sold_out'];
											if ( $sold_qty_final >= $goal_out['quantity'] ) {
												unset( $goals[ $j ] );
												if ( isset( $val['deal_end_campaign'] ) && $val['deal_end_campaign'] == 'yes' ) {
													$threshold_reach_out = true;
												}
											}
										}
									}
								}
							}
						}

						if ( $threshold_reach_out == true ) {
							unset( $deals[ $j ] );
							unset( $goals[ $j ] );
							$uniqueArr[ $j ] = array();
							array_push( $expired_camp, $j );
						} else {
							array_push( $running_camp, $j );
							if ( $menu_order_campaign_id == 0 ) {
								$menu_order_campaign_id = $j;
							}

							if ( isset( $val['location_timer_show_single'] ) && $val['location_timer_show_single'] == '1' ) {
								$uniqueArr[ $j ]['single_timer'] = array(
									'position'        => isset( $val['location_timer_single_location'] ) ? $val['location_timer_single_location'] : '',
									'skin'            => isset( $val['appearance_timer_single_skin'] ) ? $val['appearance_timer_single_skin'] : '',
									'bg_color'        => isset( $val['appearance_timer_single_bg_color'] ) ? $val['appearance_timer_single_bg_color'] : '',
									'label_color'     => isset( $val['appearance_timer_single_text_color'] ) ? $val['appearance_timer_single_text_color'] : '',
									'timer_font'      => isset( $val['appearance_timer_single_font_size_timer'] ) ? $val['appearance_timer_single_font_size_timer'] : '',
									'label_font'      => isset( $val['appearance_timer_single_font_size'] ) ? $val['appearance_timer_single_font_size'] : '',
									'display'         => isset( $val['appearance_timer_single_display'] ) ? $val['appearance_timer_single_display'] : '',
									'label_days'      => isset( $val['appearance_timer_single_label_days'] ) ? $val['appearance_timer_single_label_days'] : '',
									'label_hrs'       => isset( $val['appearance_timer_single_label_hrs'] ) ? $val['appearance_timer_single_label_hrs'] : '',
									'label_mins'      => isset( $val['appearance_timer_single_label_mins'] ) ? $val['appearance_timer_single_label_mins'] : '',
									'label_secs'      => isset( $val['appearance_timer_single_label_secs'] ) ? $val['appearance_timer_single_label_secs'] : '',
									'border_width'    => isset( $val['appearance_timer_single_border_width'] ) ? $val['appearance_timer_single_border_width'] : '',
									'border_color'    => isset( $val['appearance_timer_single_border_color'] ) ? $val['appearance_timer_single_border_color'] : '',
									'border_style'    => isset( $val['appearance_timer_single_border_style'] ) ? $val['appearance_timer_single_border_style'] : '',
									'timer_mobile'    => isset( $val['appearance_timer_mobile_reduction'] ) ? $val['appearance_timer_mobile_reduction'] : '',
									'start_timestamp' => $start_date_timestamp,
									'end_timestamp'   => $end_date_timestamp,
									'campaign_type'   => $campaignType['type'],
								);

								$single_timer[ $j ] = $uniqueArr[ $j ]['single_timer'];

								$add_timer_to_grid_filter = apply_filters( 'wcct_add_timer_to_grid', array() );
								$add_timer_to_grid_filter = apply_filters( "wcct_add_timer_to_grid_{$j}", $add_timer_to_grid_filter );
								if ( count( $add_timer_to_grid_filter ) > 0 ) {
									$add_timer_to_grid_filter            = wp_parse_args( $add_timer_to_grid_filter, $this->grid_timer_default_array );
									$grid_timer[ $j ]                    = $add_timer_to_grid_filter;
									$grid_timer[ $j ]['start_timestamp'] = $start_date_timestamp;
									$grid_timer[ $j ]['end_timestamp']   = $end_date_timestamp;
									$grid_timer[ $j ]['campaign_type']   = $campaignType['type'];
								}

								$add_timer_to_cart = apply_filters( 'wcct_add_timer_to_cart', array() );
								$add_timer_to_cart = apply_filters( "wcct_add_timer_to_cart_{$j}", $add_timer_to_cart );
								if ( count( $add_timer_to_cart ) > 0 ) {
									$add_timer_to_cart                     = wp_parse_args( $add_timer_to_cart, $this->cart_default_array );
									$show_on_cart[ $j ]                    = $add_timer_to_cart;
									$show_on_cart[ $j ]['start_timestamp'] = $start_date_timestamp;
									$show_on_cart[ $j ]['end_timestamp']   = $end_date_timestamp;
									$show_on_cart[ $j ]['campaign_type']   = $campaignType['type'];
								}
							}

							/** countdown bar array preparation */
							if ( isset( $uniqueArr[ $j ]['goals'] ) && is_array( $uniqueArr[ $j ]['goals'] ) && count( $uniqueArr[ $j ]['goals'] ) > 0 ) {
								if ( isset( $val['location_bar_show_single'] ) && $val['location_bar_show_single'] == '1' ) {
									$countBarr_style = array(
										'position'        => isset( $val['location_bar_single_location'] ) ? $val['location_bar_single_location'] : '',
										'skin'            => isset( $val['appearance_bar_single_skin'] ) ? $val['appearance_bar_single_skin'] : '',
										'edge'            => isset( $val['appearance_bar_single_edges'] ) ? $val['appearance_bar_single_edges'] : '',
										'orientation'     => isset( $val['appearance_bar_single_orientation'] ) ? $val['appearance_bar_single_orientation'] : 'ltr',
										'height'          => isset( $val['appearance_bar_single_height'] ) ? $val['appearance_bar_single_height'] : '',
										'bg_color'        => isset( $val['appearance_bar_single_bg_color'] ) ? $val['appearance_bar_single_bg_color'] : '',
										'active_color'    => isset( $val['appearance_bar_single_active_color'] ) ? $val['appearance_bar_single_active_color'] : '',
										'display'         => isset( $val['appearance_bar_single_display'] ) ? $val['appearance_bar_single_display'] : '',
										'border_width'    => isset( $val['appearance_bar_single_border_width'] ) ? $val['appearance_bar_single_border_width'] : '',
										'border_color'    => isset( $val['appearance_bar_single_border_color'] ) ? $val['appearance_bar_single_border_color'] : '',
										'border_style'    => isset( $val['appearance_bar_single_border_style'] ) ? $val['appearance_bar_single_border_style'] : '',
										'start_timestamp' => $start_date_timestamp,
										'end_timestamp'   => $end_date_timestamp,
										'campaign_type'   => $campaignType['type'],
									);

									$uniqueArr[ $j ]['bar_single'] = $countBarr_style;
									$single_bar[ $j ]              = $uniqueArr[ $j ]['bar_single'];
									$wcct_add_bar_to_grid          = apply_filters( 'wcct_add_bar_to_grid', array() );
									$wcct_add_bar_to_grid          = apply_filters( "wcct_add_bar_to_grid_{$j}", $wcct_add_bar_to_grid );

									if ( is_array( $wcct_add_bar_to_grid ) && count( $wcct_add_bar_to_grid ) > 0 ) {
										$wcct_add_bar_to_grid              = wp_parse_args( $wcct_add_bar_to_grid, $this->grid_bar_default_array );
										$grid_bar[ $j ]                    = $wcct_add_bar_to_grid;
										$grid_bar[ $j ]['start_timestamp'] = $start_date_timestamp;
										$grid_bar[ $j ]['end_timestamp']   = $end_date_timestamp;
										$grid_bar[ $j ]['campaign_type']   = $campaignType['type'];
									}
								}
							}

							$misc[ $j ] = array();

							if ( isset( $deals[ $j ] ) && is_array( $deals[ $j ] ) && count( $deals[ $j ] ) > 0 ) {
								if ( is_array( $during_deal_campaign_final ) && count( $during_deal_campaign_final ) == 0 ) {
									$during_deal_campaign_final['deal_enables'] = true;
									$during_deal_campaign_final['campaign_id']  = $j;
								}
								if ( count( $during_misc_deal ) == 0 ) {
									$during_misc_deal                 = $misc[ $j ];
									$during_misc_deal['deal_enables'] = true;
									$during_misc_deal['campaign_id']  = $j;
								}
							}
							if ( isset( $uniqueArr[ $j ]['goals'] ) && is_array( $uniqueArr[ $j ]['goals'] ) && count( $uniqueArr[ $j ]['goals'] ) > 0 && ! isset( $deals[ $j ] ) ) {
								if ( count( $during_misc_goal ) == 0 ) {
									$during_misc_goal                 = $misc[ $j ];
									$during_misc_goal['goal_enables'] = true;
									$during_misc_goal['campaign_id']  = $j;
								}
							}
						}
					} else {
						array_push( $expired_camp, $j );
					}
				}

				unset( $val );
			}
		}

		reset( $deals );
		reset( $goals );

		if ( is_array( $single_timer ) && count( $single_timer ) > 0 ) {
			$first_key      = key( $single_timer );
			$first_key_data = reset( $single_timer );
			$single_timer   = array(
				$first_key => $first_key_data,
			);
		}

		$global_settings = WCCT_Common::get_global_default_settings();
		if ( 'yes' == $global_settings['wcct_timer_hide_multiple'] ) {
			if ( is_array( $single_timer ) && count( $single_timer ) > 0 ) {
				$first_key      = key( $single_timer );
				$first_key_data = reset( $single_timer );
				$single_timer   = array(
					$first_key => $first_key_data,
				);
			}
		}

		$return = array(
			'campaign_meta' => $campaign_meta,
			'grid_bar'      => $grid_bar,
			'single_bar'    => $single_bar,
			'show_on_cart'  => $show_on_cart,
			'grid_timer'    => $grid_timer,
			'single_timer'  => $single_timer,
			'goals'         => current( $goals ),
			'deals'         => current( $deals ),
			'misc'          => count( $misc ) > 0 ? current( $misc ) : array(),
			'expired'       => $expired_camp,
			'running'       => $running_camp,
		);

		if ( $return_key && isset( $return[ $return_key ] ) ) {
			return $return[ $return_key ];
		}

		return $return;
	}


	/**
	 * Calling non public property will return data from property `wcct_trigger_data`
	 *
	 * @param $name name if property to be called
	 *
	 * @return bool|mixed Data on success, false otherwise
	 */
	public function __get( $name ) {

		return ( $name == 'data' ) ? $this->wcct_trigger_data : false;
	}

}
