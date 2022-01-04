<?php

defined( 'ABSPATH' ) || exit;

class XLWCCT_Admin {

	protected static $instance = null;
	protected static $default;

	public function __construct() {
		$this->setup_default();
		$this->includes();
		$this->hooks();
	}

	public static function setup_default() {
		self::$default = WCCT_Common::get_default_settings();
	}

	/**
	 * Include files
	 */
	public function includes() {
		/**
		 * Loading dependencies
		 */
		include_once $this->get_admin_uri() . 'includes/cmb2/init.php';
		include_once $this->get_admin_uri() . 'includes/cmb2-addons/tabs/CMB2-WCCT-Tabs.php';
		include_once $this->get_admin_uri() . 'includes/cmb2-addons/switch/switch.php';
		include_once $this->get_admin_uri() . 'includes/cmb2-addons/conditional/cmb2-conditionals.php';

		/**
		 * Loading custom classes for product and option page.
		 */
		include_once $this->get_admin_uri() . 'includes/xl-wcct-reports.php';
		include_once $this->get_admin_uri() . 'includes/wcct-admin-cmb2-support.php';
		include_once $this->get_admin_uri() . 'includes/wcct-admin-countdown-options.php';
	}

	/**
	 * Get Admin path
	 * @return string plugin admin path
	 */
	public function get_admin_uri() {
		return plugin_dir_path( WCCT_PLUGIN_FILE ) . '/admin/';
	}

	public function hooks() {

		add_action( 'admin_enqueue_scripts', array( $this, 'wcct_post_wcct_load_assets' ), 19 );

		add_filter( 'cmb2_init', array( $this, 'wcct_add_options_quick_view_metabox' ), 10 );
		add_filter( 'cmb2_init', array( $this, 'wcct_shortcode_metabox' ), 11 );
		add_filter( 'cmb2_init', array( $this, 'wcct_add_order_report_metabox' ), 10 );
		/**
		 * Running product meta info setup
		 */
		add_filter( 'cmb2_init', array( $this, 'wcct_add_options_countdown_metabox' ) );

		/**
		 * Running product meta info setup
		 */
		add_filter( 'cmb2_init', array( $this, 'wcct_add_options_menu_order_metabox' ), 12 );

		/**
		 * Loading js and css
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'wcct_enqueue_admin_assets' ), 20 );
		/**
		 * Remove Plugin update transient
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'wcct_remove_plugin_update_transient' ), 10 );
		/**
		 * Loading cmb2 assets
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'cmb2_load_toggle_button_assets' ), 20 );
		/**
		 * Allowing conditionals to work on custom page
		 */
		add_filter( 'xl_cmb2_add_conditional_script_page', array( 'WCCT_Admin_CMB2_Support', 'wcct_push_support_form_cmb_conditionals' ) );
		/**
		 * Handle tabs ordering
		 */
		add_filter( 'wcct_cmb2_modify_field_tabs', array( $this, 'wcct_admin_reorder_tabs' ), 99 );
		/**
		 * Adds HTML field to cmb2 config
		 */
		add_action( 'cmb2_render_wcct_html_content_field', array( $this, 'wcct_html_content_fields' ), 10, 5 );
		/**
		 * Keeping meta box open
		 */
		add_filter( 'postbox_classes_product_wcct_product_option_tabs', array( $this, 'wcct_metabox_always_open' ) );
		/**
		 * Pushing Deactivation For XL Core
		 */
		add_filter( 'plugin_action_links_' . WCCT_PLUGIN_BASENAME, array( $this, 'wcct_plugin_actions' ) );
		/**
		 * Adding New Tab in WooCommerce Settings API
		 */
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'modify_woocommerce_settings' ), 99 );
		/**
		 * Adding Customer HTML On setting page for WooCommerce
		 */
		add_action( 'woocommerce_settings_' . WCCT_Common::get_wc_settings_tab_slug(), array( $this, 'wcct_woocommerce_options_page' ) );
		/**
		 * Modifying Publish meta box for our posts
		 */
		add_action( 'post_submitbox_misc_actions', array( $this, 'wcct_post_publish_box' ) );
		/**
		 * Adding `Return To` Notice Out Post Pages
		 */
		add_action( 'edit_form_top', array( $this, 'wcct_edit_form_top' ) );

		/**
		 * Modifying Post update messages
		 */
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		/**
		 * Hooks to check if activation and deactivation request for post.
		 */
		add_action( 'admin_init', array( $this, 'maybe_activate_post' ) );
		add_action( 'admin_init', array( $this, 'maybe_deactivate_post' ) );

		add_action( 'admin_init', array( $this, 'maybe_duplicate_post' ) );
		add_action( 'save_post_' . WCCT_Common::get_campaign_post_type_slug(), array( $this, 'save_menu_order' ), 99, 2 );
		add_action( 'save_post_product', array( $this, 'delete_product_taxonomy_ids_meta' ), 99 );
		add_filter( 'quick_edit_show_taxonomy', array( $this, 'delete_product_taxonomy_ids_meta_quick_edit' ), 99, 3 );

		/**
		 * CMB2 AFTER SAVE METADATA HOOK
		 */
		add_action( 'cmb2_save_post_fields_wcct_campaign_settings', array( $this, 'clear_transients' ), 1000 );

		add_action( 'do_meta_boxes', array( $this, 'wcct_do_meta_boxes' ), 999, 3 );
		add_action( 'wp_print_scripts', array( $this, 'wcct_wp_print_scripts' ), 999 );

		add_action( 'admin_menu', array( $this, 'wcct_wc_admin_menu' ), 10 );

		/**
		 * Add text for  help popup
		 */
		add_action( 'admin_footer', array( $this, 'wcct_add_merge_tag_text' ) );

		add_filter( 'plugin_row_meta', array( $this, 'wcct_plugin_row_actions' ), 10, 2 );

		add_action( 'admin_head', array( $this, 'maybe_throw_notice_for_cache' ) );

		add_filter( 'admin_footer_text', array( $this, 'wcct_admin_footer_text' ), 999 );

		add_action( 'delete_post', array( $this, 'clear_transients_on_delete' ), 10 );
		add_action( 'post_updated', array( $this, 'restrict_to_publish_when_campaign_is_disabled' ), 10, 3 );

		add_filter( 'admin_notices', array( $this, 'maybe_show_advanced_update_notification' ), 999 );

		/** Metabox when counter bar enabled but inventory bar not */
		add_action( 'edit_form_after_title', array( $this, 'show_counter_bar_error' ) );

		/** Delete post data transient */
		add_action( 'pre_post_update', array( $this, 'delete_post_data_transient' ), 99, 2 );

		/** Validating & removing scripts on page load */
		add_action( 'admin_print_styles', array( $this, 'removing_scripts_finale_campaign_load' ), - 1 );
		add_action( 'admin_print_scripts', array( $this, 'removing_scripts_finale_campaign_load' ), - 1 );
		add_action( 'admin_print_footer_scripts', array( $this, 'removing_scripts_finale_campaign_load' ), - 1 );
	}

	public function restrict_to_publish_when_campaign_is_disabled( $post_ID, $post_after, $post_before ) {
		remove_action( 'post_updated', array( $this, 'restrict_to_publish_when_campaign_is_disabled' ), 10 );
		WCCT_Common::wcct_maybe_clear_cache();

		if ( isset( $_GET['page'] ) && $_GET['page'] === 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] === WCCT_Common::get_wc_settings_tab_slug() ) { // WPCS: input var ok, CSRF ok.
		} else {
			if ( $post_before->post_status === 'wcctdisabled' && ! isset( $_GET['wcct_action'] ) ) { // WPCS: input var ok, CSRF ok.
				$post_after->post_status = 'wcctdisabled';
				$temp                    = wp_json_encode( $post_after );
				$post_after              = json_decode( $temp, true );
				wp_update_post( $post_after );
			}
		}
	}

	/**
	 * Return an instance of this class.
	 * @return    object    A single instance of this class.
	 * @since     1.0.0
	 */
	public static function get_instance() {
		if ( ! is_super_admin() ) {
			return;
		}
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Hooked over Activation
	 * Checks and insert plugin options(data)  in wp_options
	 */
	public static function handle_activation() {
		/**
		 * Handle optIn option
		 */
		$sample_campaign = array(
			'wcct_timer_bar' => array(
				'title' => __( 'Countdown Timer + Bar', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'meta'  => array(
					'_wcct_campaign_type'                           => 'fixed_date',
					'_wcct_campaign_fixed_recurring_start_date'     => date( 'Y-m-d', strtotime( '-1 days' ) ),
					'_wcct_campaign_fixed_recurring_start_time'     => '12:00 AM',
					'_wcct_campaign_fixed_end_date'                 => date( 'Y-m-d', strtotime( '+5 days' ) ),
					'_wcct_campaign_fixed_end_time'                 => '12:00 AM',
					'_wcct_deal_enable_goal'                        => '1',
					'_wcct_deal_units'                              => 'custom',
					'_wcct_deal_custom_units'                       => '8',
					'_wcct_deal_inventory_goal_for'                 => 'recurrence',
					'_wcct_deal_custom_units_allow_backorder'       => 'no',
					'_wcct_deal_end_campaign'                       => 'no',
					'_wcct_location_timer_show_single'              => '1',
					'_wcct_location_timer_single_location'          => '4',
					'_wcct_appearance_timer_single_skin'            => 'round_ghost',
					'_wcct_appearance_timer_single_bg_color'        => '#8cc63f',
					'_wcct_appearance_timer_single_text_color'      => '#444444',
					'_wcct_appearance_timer_single_font_size_timer' => '26',
					'_wcct_appearance_timer_single_font_size'       => '13',
					'_wcct_appearance_timer_single_label_days'      => 'days',
					'_wcct_appearance_timer_single_label_hrs'       => 'hrs',
					'_wcct_appearance_timer_single_label_mins'      => 'mins',
					'_wcct_appearance_timer_single_label_secs'      => 'secs',
					'_wcct_appearance_timer_single_border_width'    => '1',
					'_wcct_appearance_timer_single_border_color'    => '#f2f2f2',
					'_wcct_appearance_timer_single_border_style'    => 'none',
					'_wcct_appearance_timer_single_display'         => "{{countdown_timer}}\nPrices go up when the timer hits zero.",
					'_wcct_location_bar_show_single'                => '1',
					'_wcct_location_bar_single_location'            => '4',
					'_wcct_appearance_bar_single_skin'              => 'stripe_animate',
					'_wcct_appearance_bar_single_edges'             => 'rounded',
					'_wcct_appearance_bar_single_orientation'       => 'rtl',
					'_wcct_appearance_bar_single_bg_color'          => '#dddddd',
					'_wcct_appearance_bar_single_active_color'      => '#ee303c',
					'_wcct_appearance_bar_single_height'            => '12',
					'_wcct_appearance_bar_single_display'           => "Hurry up! Just <span>{{remaining_units}}</span> items left in stock\n{{counter_bar}}",
					'_wcct_appearance_bar_single_border_style'      => 'none',
					'_wcct_appearance_bar_single_border_width'      => '0',
					'_wcct_appearance_bar_single_border_color'      => '#444444',
					'_wcct_campaign_menu_order'                     => 0,
				),
			),
		);

		$ids_array = get_option( 'wcct_posts_sample_ids', array() );
		$ids_array = $ids_array === '' ? array() : $ids_array;

		foreach ( $sample_campaign as $key => $val ) {
			if ( ! isset( $ids_array[ $key ] ) || $ids_array[ $key ] == 0 || $ids_array[ $key ] === null ) {
				$id = wp_insert_post( array(
					'post_type'   => WCCT_Common::get_campaign_post_type_slug(),
					'post_title'  => __( $val['title'], 'finale-woocommerce-sales-countdown-timer-discount' ),
					'post_status' => WCCT_SHORT_SLUG . 'disabled',
				) );
				if ( ! is_wp_error( $id ) ) {
					$ids_array[ $key ] = $id;
					$meta_fields       = $val['meta'];
					if ( is_array( $meta_fields ) && count( $meta_fields ) > 0 ) {
						foreach ( $meta_fields as $mkey => $mval ) {
							update_post_meta( $id, $mkey, $mval );
						}
					}
				}
			}
		}
		if ( count( $ids_array ) > 0 ) {
			update_option( 'wcct_posts_sample_ids', $ids_array, false );
			delete_transient( 'WCCT_INSTANCES' );
		}
	}

	/**
	 * Sorter function to sort array by internal key called priority
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public static function _sort_by_priority( $a, $b ) {
		if ( $a['position'] === $b['position'] ) {
			return 0;
		}

		return ( $a['position'] < $b['position'] ) ? - 1 : 1;
	}

	public static function add_metaboxes() {
		if ( WCCT_Common::wcct_valid_admin_pages( 'single' ) ) {
			add_meta_box( 'wcct_rules', 'Rules', array( __CLASS__, 'rules_metabox' ), WCCT_Common::get_campaign_post_type_slug(), 'normal', 'high' );
		}
	}

	public static function rules_metabox() {
		include_once 'views/metabox-rules.php';
	}

	public function wcct_add_options_countdown_metabox() {
		WCCT_Admin_CountDown_Post_Options::prepere_default_config();
		WCCT_Admin_CountDown_Post_Options::setup_fields();
	}

	public function wcct_add_options_menu_order_metabox() {
		WCCT_Admin_CountDown_Post_Options::menu_order_metabox_fields();
	}

	public function wcct_add_options_quick_view_metabox() {
		WCCT_Admin_CountDown_Post_Options::quick_view_metabox_fields();
	}

	public function wcct_shortcode_metabox() {
		WCCT_Admin_CountDown_Post_Options::shortcode_metabox_fields();
	}

	public function wcct_add_order_report_metabox() {

		WCCT_Admin_CountDown_Post_Options::wcct_report_order_metabox_fields();
	}

	/**
	 * Render options for woocommerce custom option page
	 */
	public function wcct_woocommerce_options_page() {
		if ( 'blank' === get_option( 'xlp_is_opted', 'blank' ) ) {
			include_once( 'views/optin-temp.php' );

			return;
		}
		if ( filter_input( INPUT_GET, 'section' ) === 'settings' ) {
			?>
            <div class="notice">
                <p><?php _e( 'Back to <a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) . '">' . WCCT_FULL_NAME . '</a> listing.', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></p>
            </div>
            <div class="wrap wcct_global_option">
                <h1 class="wp-heading-inline"><?php echo __( 'Settings', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></h1>
                <div id="poststuff">
                    <div class="inside">
                        <div class="wcct_options_page_col2_wrap">
                            <div class="wcct_options_page_left_wrap">
                                <div class="postbox">
                                    <div class="inside">
                                        <div class="wcct_options_common wcct_options_settings">
                                            <div class="wcct_h20"></div>
											<?php cmb2_metabox_form( 'wcct_global_settings', 'wcct_global_options' ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="wcct_options_page_right_wrap">
								<?php do_action( 'wcct_options_page_right_content' ); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
			<?php
		} else {
			require_once( $this->get_admin_uri() . 'includes/wcct-post-table.php' );
			?>
            <style>
                body {
                    position: relative;
                    height: auto;
                }
            </style>
            <div class="wrap cmb2-options-page wcct_global_option">
                <h1 class="wp-heading-inline"><?php _e( 'Finale Campaigns', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></h1>
                <a href="<?php echo admin_url( 'post-new.php?post_type=' . WCCT_Common::get_campaign_post_type_slug() ); ?>"
                   class="page-title-action"><?php _e( 'Add New Campaign', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></a>
                <a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=settings' ); ?>"
                   class="page-title-action"><?php echo __( 'Settings', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></a>
                <div class="clearfix"></div>
				<?php WCCT_Admin_CMB2_Support::render_trigger_nav(); ?>
                <div id="poststuff">
                    <div class="inside">
                        <div class="inside">
                            <div class="wcct_options_page_col2_wrap">
                                <div class="wcct_options_page_left_wrap">
									<?php
									$table       = new WCCT_Post_Table();
									$table->data = WCCT_Common::get_post_table_data( WCCT_Admin_CMB2_Support::get_current_trigger() );

									$table->prepare_items();
									$table->display();
									?>
                                </div>
                                <div class="wcct_options_page_right_wrap">
									<?php do_action( 'wcct_options_page_right_content' ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}
	}

	/**
	 * Loading additional assets for toggle/switch button
	 */
	public function cmb2_load_toggle_button_assets() {
		wp_enqueue_style( 'cmb2_switch-css', $this->get_admin_url() . 'includes/cmb2-addons/switch/switch_metafield.css', false, XLWCCT_VERSION );
		//CMB2 Switch Styling
		wp_enqueue_script( 'cmb2_switch-js', $this->get_admin_url() . 'includes/cmb2-addons/switch/switch_metafield.js', '', XLWCCT_VERSION, true );
	}

	/**
	 * Get Admin path
	 * @return string plugin admin path
	 */
	public function get_admin_url() {
		return plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/';
	}

	/**
	 * Hooked over `admin_enqueue_scripts`
	 * Enqueue scripts and css to wp-admin
	 */
	public function wcct_enqueue_admin_assets() {
		if ( true === WCCT_Common::wcct_valid_admin_pages() ) {
			wp_enqueue_style( 'xl-confirm-css', $this->get_admin_url() . 'assets/css/jquery-confirm.min.css', XLWCCT_VERSION );
			wp_enqueue_style( 'wcct_admin-css', $this->get_admin_url() . 'assets/css/wcct-admin-style.css', XLWCCT_VERSION );
			wp_enqueue_style( 'cmb2-styles' );
			wp_enqueue_script( 'xl-confirm-js', $this->get_admin_url() . 'assets/js/jquery-confirm.min.js', XLWCCT_VERSION );
			wp_enqueue_script( 'wcct_admin-js', $this->get_admin_url() . 'assets/js/wcct-admin.min.js', array( 'jquery', 'cmb2-scripts', 'wcct-cmb2-conditionals' ), XLWCCT_VERSION, true );
			wp_register_script( 'wcct-modal', $this->get_admin_url() . 'assets/js/wcct-modal.min.js', array( 'jquery' ), XLWCCT_VERSION );
			wp_register_style( 'wcct-modal', $this->get_admin_url() . 'assets/css/wcct-modal.css', null, XLWCCT_VERSION );

			wp_enqueue_script( 'wcct-modal' );
			wp_enqueue_style( 'wcct-modal' );

			wp_enqueue_script( 'jquery' );

			$go_pro_link = add_query_arg( array(
				'utm_source'   => 'finale-lite',
				'utm_medium'   => 'modals-click',
				'utm_campaign' => 'optin-modals',
				'utm_term'     => 'go-pro-{current_slug}',
			), 'https://xlplugins.com/lite-to-pro-upgrade-page/' );

			wp_localize_script( 'wcct_admin-js', 'buy_pro_helper', array(
				'buy_now_link'        => $go_pro_link,
				'call_to_action_text' => __( 'Upgrade To PRO', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' &nbsp;<i class="dashicons dashicons-arrow-right-alt"></i>',
				'protabs'             => array(
					'#wcct_events_settings',
					'#wcct_actions_settings',
					'#wcct_misc_settings',
					'#wcct_coupon_settings',
				),
				'proacc'              => array(
					'sticky-header',
					'sticky-footer',
					'custom-css',
					'custom-text',
				),
				'popups'              => array(
					'inventory_adv'          => array(
						'title'   => __( 'Advanced inventory is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/inventory-advanced.png" /></div><div class="hcontent">' . __( 'Different items will have different units. Advanced Inventory allows you to set up different inventory based on the different units available. Want it for your store?', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
					'inventory_range'        => array(
						'title'   => __( 'Invenory Range is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/inventory-range.png" /></div><div class="hcontent">' . __( 'Inventory Range allows you to set up random inventory based on the input range. This randomization makes the stock scarcity look genuine as different products have different units left. Want it for your store?', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
					'recurring'              => array(
						'title'   => __( 'Recurring Campaign is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/recurring.gif" /></div><div class="hcontent">' . __( 'Recurring Campaign allows you to automate your Campaigns. No more coming back to the WordPress dashboard to re-start campaigns & set up rules all over again. Want it for your store?', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
					'evergreen'              => array(
						'title'   => __( 'Evergreen Campaign is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/evergreen.png" /></div><div class="hcontent">' . __( 'Evergreen campaigns allow you to set up campaigns with a unique deadline for every user. Each new user has their own deadline! Now make more money with personalized, time-limited campaigns instead of standard one-size fits all campaigns!', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' <strong>' . __( '(Available in Business Plan)', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</strong></div>',
					),
					'#wcct_events_settings'  => array(
						'title'   => __( 'Events is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/events.gif" /></div><div class="hcontent">' . __( 'Use Events to offer early bird discounts or increase/decrease discount when your campaign is close to expiring. You could also vary the discount amount based on stocks left. No other tool offers this level of control over the real-time store dynamics. Want it for your store?', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
					'#wcct_actions_settings' => array(
						'title'   => __( 'Actions is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/actions.jpg" /></div><div class="hcontent">' . __( "Use Action to change the product availability status. Launching a new product? Make the 'Add to Cart' button invisible to build hype during pre-launch campaign. It'll become invisible once your product hits the shelf. Running scarcity marketing campaign? Set it up to make the CTA button invisible once the campaign expires. Want it for your store?", 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
					'#wcct_misc_settings'    => array(
						'title'   => __( 'Advanced Settings is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/advanced.png" /></div><div class="hcontent">' . __( 'Use Advanced feature in Finale Pro to change the Call to Action button text during the campaign. You can even write the custom texts to be shown after the timer expires. Want it for your store?', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
					'sticky-header'          => array(
						'title'   => __( 'Sticky Header is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/sticky_header.gif" /></div><div class="hcontent">' . __( 'Sticky header beautifully sits on top of your pages and subtly reminds visitors about the on-going campaigns. Exploit the massive power of subtle nudge. Want it for your store?', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
					'sticky-footer'          => array(
						'title'   => __( 'Sticky Footer is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/sticky_footer.gif" /></div><div class="hcontent">' . __( 'Sticky footer beautifully sits on bottom of your pages and subtly reminds visitors about the on-going campaigns. Exploit the massive power of subtle nudge. Want it for your store?', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
					'custom-text'            => array(
						'title'   => __( 'Custom Text is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/custom_text.png" /></div><div class="hcontent">' . __( 'Use the custom text box to display compelling messages that lead to action. Inform visitors about instant discounts if any or let them know about the estimated delivery details to slay their objections. It comes loaded with copy paste, dynamic merge tags. Want it for your store?', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
					'custom-css'             => array(
						'title'   => __( 'Custom CSS is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/custom_css.png" /></div><div class="hcontent">' . __( 'Use the custom css box to add your own css in a campaign. Easy to use, straight adding of css, no style tag required. Want it for your store?', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
					'#wcct_coupon_settings'  => array(
						'title'   => __( 'Coupons is a Premium Feature', 'finale-woocommerce-sales-countdown-timer-discount' ),
						'content' => '<div class="himage"><img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/modules/coupons.gif" /></div><div class="hcontent">' . __( "Now make your coupons time-bound. Show your shoppers the exact time left before their coupon code expires. Great for supercharging people's response to coupons and increase uptakes! Want it for your store?", 'finale-woocommerce-sales-countdown-timer-discount' ) . '</div>',
					),
				),
			) );
		}
	}

	/**
	 * Hooked over `admin_enqueue_scripts`
	 * Force remove Plugin update transient
	 */
	public function wcct_remove_plugin_update_transient() {
		if ( isset( $_GET['remove_update_transient'] ) && $_GET['remove_update_transient'] == '1' ) {
			delete_option( '_site_transient_update_plugins' );
		}
	}

	/**
	 * Hooked over `wcct_cmb2_modify_field_tabs`
	 * Sorts Tabs for settings
	 *
	 * @param $tabs : Array of tabs
	 *
	 * @return mixed Sorted array
	 */
	public function wcct_admin_reorder_tabs( $tabs ) {
		usort( $tabs, array( $this, '_sort_by_priority' ) );

		return $tabs;
	}

	/**
	 * Hooked over `cmb2_render_wcct_html_content_field`
	 * Render Html for `wcct_html_content` Field
	 *
	 * @param $field : CMB@ Field object
	 * @param $escaped_value : Value
	 * @param $object_id object ID
	 * @param $object_type Object Type
	 * @param $field_type_object : Field Tpe Object
	 */
	public function wcct_html_content_fields( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$switch                 = '';
		$conditional_value      = ( isset( $field->args['attributes']['data-conditional-value'] ) ? 'data-conditional-value="' . esc_attr( $field->args['attributes']['data-conditional-value'] ) . '"' : '' );
		$conditional_id         = ( isset( $field->args['attributes']['data-conditional-id'] ) ? ' data-conditional-id="' . esc_attr( $field->args['attributes']['data-conditional-id'] ) . '"' : '' );
		$wcct_conditional_value = ( isset( $field->args['attributes']['data-wcct-conditional-value'] ) ? 'data-wcct-conditional-value="' . esc_attr( $field->args['attributes']['data-wcct-conditional-value'] ) . '"' : '' );
		$wcct_conditional_id    = ( isset( $field->args['attributes']['data-wcct-conditional-id'] ) ? ' data-wcct-conditional-id="' . esc_attr( $field->args['attributes']['data-wcct-conditional-id'] ) . '"' : '' );
		$switch                 = '<div ' . $conditional_value . $conditional_id . $wcct_conditional_value . $wcct_conditional_id . ' class="cmb2-wcct_html" id="' . $field->args['id'] . '">';

		if ( isset( $field->args['content_cb'] ) ) {
			$switch .= call_user_func( $field->args['content_cb'] );
		} elseif ( isset( $field->args['content'] ) ) {
			$switch .= ( $field->args['content'] );
		}

		$switch .= '</div>';

		echo $switch;
	}

	/**
	 * Hooked over `postbox_classes_product_wcct_product_option_tabs`
	 * Always open for meta boxes
	 * removing closed class
	 *
	 * @param $classes : classes
	 *
	 * @return mixed array of classes
	 */
	public function wcct_metabox_always_open( $classes ) {
		if ( ( $key = array_search( 'closed', $classes ) ) !== false ) {
			unset( $classes[ $key ] );
		}

		return $classes;
	}

	/**
	 * Hooked over 'plugin_action_links_{PLUGIN_BASENAME}' WordPress hook to add deactivate popup support
	 *
	 * @param array $links array of existing links
	 *
	 * @return array modified array
	 */
	public function wcct_plugin_actions( $links ) {
		$go_pro_link         = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'text-click',
			'utm_campaign' => 'plugin-actions',
			'utm_term'     => 'go-pro',
		), 'https://xlplugins.com/lite-to-pro-upgrade-page/' );
		$links['settings']   = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() ) . '" class="edit">Settings</a>';
		$links['deactivate'] .= '<i class="xl-slug" data-slug="' . WCCT_PLUGIN_BASENAME . '"></i>';
		$links['go_pro']     = '<a style="font-weight: 700; color:#39b54a" href="' . $go_pro_link . '" class="go_pro_a">' . __( 'Go Pro', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>';

		return $links;
	}

	/**
	 * Hooked to `woocommerce_settings_tabs_array`
	 * Adding new tab in woocommerce settings
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function modify_woocommerce_settings( $settings ) {
		$settings[ WCCT_Common::get_wc_settings_tab_slug() ] = __( 'Finale Lite: XLPlugins', 'finale-woocommerce-sales-countdown-timer-discount' );

		return $settings;
	}

	/**
	 * Loading assets for Rules functionality
	 *
	 * @param $handle : handle current page
	 */
	public function wcct_post_wcct_load_assets( $handle ) {
		global $post_type, $woocommerce;

		wp_enqueue_style( 'wcct-admin-all', $this->get_admin_url() . '/assets/css/wcct-admin-all.css' );
		if ( ( $handle === 'post-new.php' || $handle === 'post.php' || $handle === 'edit.php' ) && ( $post_type === WCCT_Common::get_campaign_post_type_slug() || $post_type === 'countdown' ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style( 'wcct_flicons', $this->get_admin_url() . 'assets/fonts/flicon.css' );
			wp_enqueue_style( 'wcct_admin_style', $woocommerce->plugin_url() . '/assets/css/admin.css' );
			wp_enqueue_style( 'wcct-admin-app', $this->get_admin_url() . 'assets/css/wcct-admin-app.css' );
			wp_enqueue_style( 'xl-chosen-css', $this->get_admin_url() . 'assets/css/chosen.css' );
			wp_register_script( 'xl-chosen', $this->get_admin_url() . 'assets/js/chosen/chosen.jquery.min.js', array( 'jquery' ), XLWCCT_VERSION );
			wp_register_script( 'xl-ajax-chosen', $this->get_admin_url() . 'assets/js/chosen/ajax-chosen.jquery.min.js', array( 'jquery', 'xl-chosen' ), XLWCCT_VERSION );
			wp_enqueue_script( 'xl-ajax-chosen' );
			wp_enqueue_script( 'wcct-admin-app', $this->get_admin_url() . 'assets/js/wcct-admin-app.min.js', array( 'jquery', 'jquery-ui-datepicker', 'underscore', 'backbone', 'xl-ajax-chosen' ) );

			$data = array(
				'ajax_nonce'            => wp_create_nonce( 'wcctaction-admin' ),
				'plugin_url'            => plugin_dir_url( WCCT_PLUGIN_FILE ),
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'ajax_chosen'           => wp_create_nonce( 'json-search' ),
				'search_products_nonce' => wp_create_nonce( 'search-products' ),
				'text_or'               => __( 'or', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'text_apply_when'       => __( 'Apply this Campaign when these conditions are matched', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'remove_text'           => __( 'Remove', 'finale-woocommerce-sales-countdown-timer-discount' ),
			);
			wp_localize_script( 'wcct-admin-app', 'WCCTParams', $data );
		}
	}

	public function wcct_post_publish_box() {
		global $post;
		if ( WCCT_Common::get_campaign_post_type_slug() !== $post->post_type ) {
			return;
		}

		$deactivation_url = wp_nonce_url( add_query_arg( array(
			'wcct_action' => 'wcct-post-deactivate',
			'postid'      => get_the_ID(),
			'post'        => get_the_ID(),
			'action'      => 'edit',
		), network_admin_url( 'post.php' ) ), 'wcct-post-deactivate' );

		$trigger_status = __( 'Activated', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' (<a href="' . $deactivation_url . '">' . __( 'Deactivate', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>)';
		if ( $post->post_status === 'trash' || $post->post_status === 'wcctdisabled' ) {
			$deactivation_url = wp_nonce_url( add_query_arg( array(
				'wcct_action' => 'wcct-post-activate',
				'postid'      => get_the_ID(),
				'post'        => get_the_ID(),
				'action'      => 'edit',
			), network_admin_url( 'post.php' ) ), 'wcct-post-activate' );
			$trigger_status   = __( 'Deactivated', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' (<a href="' . $deactivation_url . '">' . __( 'Activate', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>)';
		}

		if ( $post->post_date ) {
			$date_format  = get_option( 'date_format' );
			$date_format  = $date_format ? $date_format : 'M d, Y';
			$publish_date = date( $date_format, strtotime( $post->post_date ) );
		}
		if ( $post->post_status !== 'auto-draft' ) {
			?>
            <div class="misc-pub-section misc-pub-post-status wcct_always_show">
				<?php _e( 'Status', 'finale-woocommerce-sales-countdown-timer-discount' ); ?>: <span id="post-status-display"><?php echo $trigger_status; ?></span>
            </div>
			<?php
		}
		if ( $post->post_date ) {
			?>
            <div class="misc-pub-section curtime misc-pub-curtime wcct_always_show">
                <span id="timestamp"><?php _e( 'Added on', 'finale-woocommerce-sales-countdown-timer-discount' ); ?>: <b><?php echo $publish_date; ?></b></span>
            </div>
			<?php
		}

		$output      = '';
		$data        = WCCT_Common::get_item_data( $post->ID );
		$data_format = get_option( 'date_format' );
		if ( isset( $data['campaign_fixed_recurring_start_date'] ) && $data['campaign_fixed_recurring_start_date'] !== '' ) {
			$start_date    = $data['campaign_fixed_recurring_start_date'];
			$start_time    = $data['campaign_fixed_recurring_start_time'];
			$date1         = new Datetime( $start_date . ' ' . $start_time );
			$campaign_type = '';
			if ( $data['campaign_type'] === 'fixed_date' ) {
				$campaign_type = __( 'Fixed Date', 'finale-woocommerce-sales-countdown-timer-discount' );
			}
			$output = '';
			if ( ! empty( $campaign_type ) ) {
				$output .= '' . __( 'Type', 'finale-woocommerce-sales-countdown-timer-discount' ) . ': ' . $campaign_type . '<br/>';
			}
			$starts = sprintf( '%s %s<br/>', $date1->format( $data_format ), $start_time );
			$output .= $starts;
			if ( $data['campaign_type'] === 'fixed_date' ) {
				$end_date      = $data['campaign_fixed_end_date'];
				$end_time      = $data['campaign_fixed_end_time'];
				$date2         = new Datetime( $end_date . ' ' . $end_time );
				$interval      = $date2->diff( $date1 );
				$days          = $interval->format( '%a' );
				$hrs           = $interval->format( '%H' );
				$min           = $interval->format( '%I' );
				$duration_only = sprintf( '%s %s %s', ( $days > '1' ) ? $days . ' days' : $days . ' day', ( $hrs > '1' ) ? $hrs . ' hrs' : $hrs . ' hr', ( $min > '1' ) ? $min . ' mins' : $min . ' min' );
				$output        .= '' . __( 'Duration', 'finale-woocommerce-sales-countdown-timer-discount' ) . ': ' . $duration_only;
			}
		}
		$data            = ( $output );
		$timezone_format = _x( 'Y-m-d H:i:s', 'timezone date format' );

		$state = WCCT_Common::wcct_set_campaign_status( $post->ID );

		if ( $state === 'Paused' ) {
			$icon_class_state = 'controls-pause';
		}
		if ( $state === 'Scheduled' ) {
			$icon_class_state = 'schedule';
		}

		if ( $state === 'Running' ) {
			$icon_class_state = 'controls-play';
		}

		if ( $state === 'Finished' ) {
			$icon_class_state = 'yes';
		}

		if ( $campaign_type === 'Fixed Date' ) {
			$icon_type_class = 'calendar-alt';
		}

		if ( $campaign_type === 'Recurring' ) {
			$icon_type_class = 'controls-repeat';
		}
		?>
        <div class="misc-pub-section curtime misc-pub-curtime wcct_always_show">
			<span class=""><i style="color:#82878c"
                              class="dashicons dashicons-clock"></i> Current Time: <b> <?php echo date_i18n( $timezone_format ) . '(' . WCCT_Common::wc_timezone_string() . ')'; ?></b></span>

        </div>
		<?php
	}

	/*     * ******** Functions For Rules Functionality Starts ************************************* */

	public function wcct_edit_form_top() {
		global $post;

		if ( WCCT_Common::get_campaign_post_type_slug() !== $post->post_type ) {
			return;
		}
		?>
        <div class="notice">
            <p><?php echo __( 'Back to', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' <a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) . '">' . WCCT_FULL_NAME . '</a> ' . __( 'settings', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></p>
        </div>
		<?php
	}

	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages[ WCCT_Common::get_campaign_post_type_slug() ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( 'Countdown timer updated.', 'finale-woocommerce-sales-countdown-timer-discount' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
			2  => __( 'Custom field updated.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			3  => __( 'Custom field deleted.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			4  => sprintf( __( 'Countdown timer updated. ', 'finale-woocommerce-sales-countdown-timer-discount' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Trigger restored to revision from %s', 'finale-woocommerce-sales-countdown-timer-discount' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Countdown timer updated. ', 'finale-woocommerce-sales-countdown-timer-discount' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
			7  => sprintf( __( 'Trigger saved. ', 'finale-woocommerce-sales-countdown-timer-discount' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
			8  => sprintf( __( 'Countdown timer updated. ', 'finale-woocommerce-sales-countdown-timer-discount' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
			9  => sprintf( __( 'Trigger scheduled for: <strong>%1$s</strong>.', 'finale-woocommerce-sales-countdown-timer-discount' ), date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) ) ),
			10 => __( 'Trigger draft updated.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			11 => sprintf( __( 'Countdown timer updated. ', 'finale-woocommerce-sales-countdown-timer-discount' ), admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '' ) ),
		);

		return $messages;
	}

	public function maybe_activate_post() {
		if ( isset( $_GET['action'] ) && ( $_GET['action'] === 'wcct-post-activate' || ( isset( $_GET['wcct_action'] ) && $_GET['wcct_action'] === 'wcct-post-activate' ) ) ) { // WPCS: input var ok, CSRF ok.
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'wcct-post-activate' ) ) { // WPCS: input var ok, CSRF ok.

				$postID  = filter_input( INPUT_GET, 'postid' );
				$section = filter_input( INPUT_GET, 'trigger' );

				if ( $postID ) {
					wp_update_post( array(
						'ID'          => $postID,
						'post_status' => 'publish',
					) );
					WCCT_Common::wcct_maybe_clear_cache();

					if ( isset( $_GET['wcct_action'] ) ) { // WPCS: input var ok, CSRF ok.
						wp_safe_redirect( admin_url( 'post.php?post=' . $_GET['postid'] . '&action=edit' ) ); // WPCS: input var ok, CSRF ok.
					} else {
						$redirect_url = admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=' . $section );
						if ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) { // WPCS: input var ok, CSRF ok.
							$redirect_url = add_query_arg( array(
								'paged' => $_GET['paged'], // WPCS: input var ok, CSRF ok.
							), $redirect_url );
						}

						wp_safe_redirect( $redirect_url );
					}
				}
			} else {
				die( __( 'Unable to Activate', 'finale-woocommerce-sales-countdown-timer-discount' ) );
			}
		}
	}

	public function maybe_deactivate_post() {
		if ( isset( $_GET['action'] ) && ( $_GET['action'] === 'wcct-post-deactivate' || ( isset( $_GET['wcct_action'] ) && $_GET['wcct_action'] === 'wcct-post-deactivate' ) ) ) { // WPCS: input var ok, CSRF ok.

			if ( wp_verify_nonce( $_GET['_wpnonce'], 'wcct-post-deactivate' ) ) { // WPCS: input var ok, CSRF ok.

				$postID  = filter_input( INPUT_GET, 'postid' );
				$section = filter_input( INPUT_GET, 'trigger' );
				if ( $postID ) {

					wp_update_post( array(
						'ID'          => $postID,
						'post_status' => WCCT_SHORT_SLUG . 'disabled',
					) );
					WCCT_Common::wcct_maybe_clear_cache();

					if ( isset( $_GET['wcct_action'] ) ) { // WPCS: input var ok, CSRF ok.
						wp_safe_redirect( admin_url( 'post.php?post=' . $_GET['postid'] . '&action=edit' ) ); // WPCS: input var ok, CSRF ok.
					} else {
						$redirect_url = admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=' . $section );
						if ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) { // WPCS: input var ok, CSRF ok.
							$redirect_url = add_query_arg( array(
								'paged' => $_GET['paged'], // WPCS: input var ok, CSRF ok.
							), $redirect_url );
						}

						wp_safe_redirect( $redirect_url );
					}
				}
			} else {
				die( __( 'Unable to Deactivate', 'finale-woocommerce-sales-countdown-timer-discount' ) );
			}
		}
	}

	public function save_menu_order( $post_id, $post = null ) {
		//Check it's not an auto save routine
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		//Perform permission checks! For example:
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( class_exists( 'XL_Transient' ) ) {
			$xl_transient_obj = XL_Transient::get_instance();
		}

		//Check your nonce!
		//If calling wp_update_post, unhook this function so it doesn't loop infinitely
		remove_action( 'save_post_' . WCCT_Common::get_campaign_post_type_slug(), array( $this, 'save_menu_order' ), 99, 2 );
		if ( $post !== null ) {
			if ( $post && $post->post_type === WCCT_Common::get_campaign_post_type_slug() ) {
				if ( isset( $_POST['_wcct_data_menu_order'] ) ) { // WPCS: input var ok, CSRF ok.
					wp_update_post( array(
						'ID'         => $post_id,
						'post_name'  => sanitize_title( $_POST['post_title'] ) . '_' . $post_id, // WPCS: input var ok, CSRF ok.
						'menu_order' => $_POST['_wcct_data_menu_order'], // WPCS: input var ok, CSRF ok.
					) );
				}

				if ( is_array( $_POST ) && isset( $_POST['action'] ) && 'editpost' === $_POST['action'] ) { // WPCS: input var ok, CSRF ok.
					// don't delete transients as this is a single post save call
				} elseif ( class_exists( 'XL_Transient' ) ) {
					$xl_transient_obj->delete_all_transients( 'finale' );
				}

				if ( ! wp_next_scheduled( 'wcct_schedule_reset_state', array( $post_id ) ) ) {
					wp_schedule_single_event( time() + 5, 'wcct_schedule_reset_state', array( $post_id ) );
				}
			}
		}
	}

	public function delete_product_taxonomy_ids_meta( $post_id ) {
		delete_post_meta( $post_id, '_wcct_product_taxonomy_term_ids' );
	}

	public function delete_product_taxonomy_ids_meta_quick_edit( $flag, $tax, $post_type ) {
		if ( isset( $_POST['post_ID'] ) && ! empty( $_POST['post_ID'] ) && 'product' === $post_type ) {
			delete_post_meta( $_POST['post_ID'], '_wcct_product_taxonomy_term_ids' );
		}

		return $flag;
	}

	/**
	 * removing extra meta boxes on page, added by 3rd party plugin etc
	 *
	 * @param $post_type
	 * @param $cur_context
	 * @param $post
	 *
	 * @global $wp_meta_boxes
	 *
	 */
	public function wcct_do_meta_boxes( $post_type, $cur_context, $post ) {
		global $wp_meta_boxes;
		if ( $post_type === 'wcct_countdown' ) {
			$allowed_side_metaboxes   = array( 'wcct_campaign_shortcode_settings', 'wcct_campaign_menu_order_settings' );
			$allowed_normal_metaboxes = array( 'wcct_campaign_settings', 'wcct_rules' );

			if ( isset( $wp_meta_boxes['wcct_countdown']['side']['high'] ) ) {
				unset( $wp_meta_boxes['wcct_countdown']['side']['high'] );
			}
			if ( isset( $wp_meta_boxes['wcct_countdown']['advanced'] ) ) {
				unset( $wp_meta_boxes['wcct_countdown']['advanced'] );
			}
			if ( isset( $wp_meta_boxes['wcct_countdown']['normal']['low'] ) ) {
				unset( $wp_meta_boxes['wcct_countdown']['normal']['low'] );
			}
			if ( is_array( $wp_meta_boxes['wcct_countdown']['side']['low'] ) && count( $wp_meta_boxes['wcct_countdown']['side']['low'] ) > 0 ) {
				$meta_box_keys = array_keys( $wp_meta_boxes['wcct_countdown']['side']['low'] );
				if ( is_array( $meta_box_keys ) && count( $meta_box_keys ) > 0 ) {
					foreach ( $meta_box_keys as $metabox_id ) {
						if ( ! in_array( $metabox_id, $allowed_side_metaboxes ) ) {
							unset( $wp_meta_boxes['wcct_countdown']['side']['low'][ $metabox_id ] );
						}
					}
				}
			}
			$meta_box_keys = array();
			if ( is_array( $wp_meta_boxes['wcct_countdown']['normal']['high'] ) && count( $wp_meta_boxes['wcct_countdown']['normal']['high'] ) > 0 ) {
				$meta_box_keys = array_keys( $wp_meta_boxes['wcct_countdown']['normal']['high'] );
				if ( is_array( $meta_box_keys ) && count( $meta_box_keys ) > 0 ) {
					foreach ( $meta_box_keys as $metabox_id ) {
						if ( ! in_array( $metabox_id, $allowed_normal_metaboxes ) ) {
							unset( $wp_meta_boxes['wcct_countdown']['normal']['high'][ $metabox_id ] );
						}
					}
				}
			}
		}
	}

	/**
	 * dequeue script from single campaign page
	 * @global $wp_scripts
	 */
	public function wcct_wp_print_scripts() {
		global $wp_scripts;

		if ( WCCT_Common::wcct_valid_admin_pages() ) {
			?>
            <style>
                .wrap.woocommerce p.submit {
                    display: none
                }

                #WCCT_MB_ajaxContent ol {
                    font-weight: bold
                }
            </style>
			<?php
		}
	}

	public function wcct_wc_admin_menu() {
		add_submenu_page( 'woocommerce', __( 'Campaigns', 'finale-woocommerce-sales-countdown-timer-discount' ), __( 'Campaigns', 'finale-woocommerce-sales-countdown-timer-discount' ), 'manage_woocommerce', 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() );
	}

	public function wcct_add_merge_tag_text() {
		if ( true === WCCT_Common::wcct_valid_admin_pages() ) {
			?>
            <div style="display:none;" class="wcct_tb_content" id="wcct_merge_tags_inventory_bar_help">
                <p>Here are the merge tags which you can use.</p>
                <p>
                    <em><strong>{{total_units}}</strong></em>: Outputs total quantity to be sold during the campaign.
                    Example, Total Units: 10.<br/>
                    <em><strong>{{sold_units}}</strong></em>: Outputs total quantity sold during the campaign. Example,
                    Currently Sold: 5.<br/>
                    <em><strong>{{remaining_units}}</strong></em>: Outputs total quantity left during the campaign.
                    Example, Currently Left: 5.<br/><br/>
                    <em><strong>{{total_units_price}}</strong></em>: Outputs total price value of total quantity to be
                    sold during the campaign. Example, Total Funds To Be Raised: $100.<br/>
                    <em><strong>{{sold_units_price}}</strong></em>: Outputs price value of quantity sold during the
                    campaign. Example, Funds To Raised Till Now: $50.<br/><br/>
                    <em><strong>{{sold_percentage}}</strong></em>: Outputs percentage of quantity sold during the
                    campaign. Example, Campaign Goal: 51% achieved.<br/>
                    <em><strong>{{remaining_percentage}}</strong></em>: Outputs percentage of remaining quantity left
                    during the campaign. Example, Campaign Goal: 49% left.
                </p>
            </div>
            <div style="display:none;" class="wcct_tb_content" id="wcct_inventory_sold_unit_help">
                <br/>
                <p>We understand that this may be tricky option to grasp but carefully read the instructions below to
                    understand how each of these options play up for Recurring & One Time Campaigns.</p>
                <h3>Overall Campaign</h3>
                <p><strong>Recurring Campaign</strong><br/>Say you have 'X' units to sell and set up recurring
                    campaigns. It may be the case that your units don't entirely sell in the first recurrence. You would
                    want the campaign to re-start but still carry forward total sold units during all the previous
                    recurrences. If that's the case, set 'Calculate Sold Units' to 'Overall Campaign'.</p>
                <p><strong>One Time Campaign</strong><br/>Say you have 'X' units to sell and set up one time campaigns.
                    It may be the case that your units don't sell and you want to extend the date of the campaign. And
                    include previously sold units in calculation. If that's the case, set 'Calculate Sold Units' to
                    'Overall Campaign'.</p>
                <br/>
                <h3>Current Occurrence</h3>
                <p><strong>Recurring Campaign</strong><br/>Say you are a pizza shop which has the capacity to serve 'X'
                    pizzas daily. And you have set up a recurring schedule. You would want the campaign to re-start
                    daily but also reset sold units for latest recurrence. In this case, you would set 'Calculate Sold
                    Units' to 'Current Occurrence'.</p>
                <p><strong>One Time Campaign</strong><br/>Say you have 'X' units to sell and set up one time
                    campaigns.It may be the case that you want to extend the time of the campaign. But want to reset the
                    previously sold units. If that's the case, set 'Calculate Sold Units' to 'Current Occurrence'.</p>

            </div>
            <div style="display:none;" class="wcct_tb_content" id="wcct_inventory_out_of_stock_help">
                <br/>
                <p>Finale dynamically changes the amount of units that can be sold during the Campaign based on the Inventory settings.</p>
                <p>Some of your products can be Out-of-Stock before the start of a Campaign or can go Out-of-Stock during the Campaign.</p>
                <p>Keep this setting to <strong><u>NO</u></strong> if you don't have the ability to fulfill the Out-of-Stock products.</p>
                <p>Keep this setting to <strong><u>YES</u></strong> if you can fulfill the Out-of-Stock products.</p>
            </div>
			<?php
		}
	}

	public function wcct_plugin_row_actions( $links, $file ) {
		if ( $file === WCCT_PLUGIN_BASENAME ) {
			$links[] = '<a href="' . add_query_arg( array(
					'utm_source'   => 'finale-lite',
					'utm_campaign' => 'plugin-listing',
					'utm_medium'   => 'plugin_action_link',
					'utm_term'     => 'Docs',
				), 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/' ) . '">' . esc_html__( 'Docs', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>';
			$links[] = '<a href="' . admin_url( 'admin.php?page=xlplugins&tab=support' ) . '">' . esc_html__( 'Support', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>';
			$links[] = '<a href="https://wordpress.org/support/view/plugin-reviews/finale-woocommerce-sales-countdown-timer-discount/" target="_blank"><span class="dashicons dashicons-thumbs-up"></span> Rate 5 stars!</a>';

			/** Bundle */
			$bundle_link = add_query_arg( array(
				'utm_source'   => 'finale-lite',
				'utm_campaign' => 'plugin-listing',
				'utm_medium'   => 'plugin_action_link',
				'utm_term'     => 'Get Bundle',
			), 'https://xlplugins.com/exclusive-offers/' );
			$links[]     = '<a href="' . $bundle_link . '" target="_blank"><span class="dashicons dashicons-megaphone"></span> Get Conversion Bundle</a>';
		}

		return $links;
	}

	public function maybe_throw_notice_for_cache() {
		$screen   = get_current_screen();
		$get_name = $this->get_caching_plugin_name();
		if ( ! empty( $get_name ) && is_object( $screen ) && isset( $screen->post_type ) && $screen->post_type == 'wcct_countdown' ) {
			?>
            <div id="message" class="notice notice-error">
                <p>
					<?php printf( __( '<strong>Important:</strong> We have noticed %s activated in your WordPress, Please reset/delete your cache after making changes to your campaign.', 'finale-woocommerce-sales-countdown-timer-discount' ), $get_name ); ?></p>
            </div>
			<?php
		}
	}

	protected function get_caching_plugin_name() {
		if ( defined( 'W3TC' ) ) {
			return 'W3 Total Cache';
		} elseif ( function_exists( 'wpsc_init' ) ) {
			return 'WP Super Cache';
		} elseif ( class_exists( 'WpFastestCache' ) ) {
			return 'WP Fastest Cache';
		} elseif ( isset( $GLOBALS['wp_php_rv']['rv'] ) ) {
			return 'Comet Cache';
		} elseif ( defined( 'CE_BASE' ) ) {
			return 'Cache Enabler';
		} elseif ( defined( 'WP_ROCKET_VERSION' ) ) {
			return 'WP Rocket';
		}

		return __return_empty_string();
	}

	public function wcct_admin_footer_text( $footer_text ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $footer_text;
		}
		if ( true === WCCT_Common::wcct_valid_admin_pages() ) {
			$footer_text = sprintf( __( 'If you like the <strong>Finale - WooCommerce Sales Countdown Timer</strong> plugin, give us a %s rating. A huge thanks in advance!', 'woocommerce' ), '<a href="https://wordpress.org/support/plugin/finale-woocommerce-sales-countdown-timer-discount/reviews?rate=5#new-post" target="_blank" class="wc-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'woocommerce' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );
		}

		return $footer_text;
	}


	public function maybe_duplicate_post() {
		global $wpdb;
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'wcct-duplicate' ) { // WPCS: input var ok, CSRF ok.

			if ( wp_verify_nonce( $_GET['_wpnonce'], 'wcct-duplicate' ) ) { // WPCS: input var ok, CSRF ok.

				$original_id = filter_input( INPUT_GET, 'postid' );
				$section     = filter_input( INPUT_GET, 'trigger' );
				if ( $original_id ) {

					// Get the post as an array
					$duplicate = get_post( $original_id, 'ARRAY_A' );

					$settings = $defaults = array(
						'status'                => 'same',
						'type'                  => 'same',
						'timestamp'             => 'current',
						'title'                 => __( 'Copy', 'post-duplicator' ),
						'slug'                  => 'copy',
						'time_offset'           => false,
						'time_offset_days'      => 0,
						'time_offset_hours'     => 0,
						'time_offset_minutes'   => 0,
						'time_offset_seconds'   => 0,
						'time_offset_direction' => 'newer',
					);

					// Modify some of the elements
					$appended                = ( $settings['title'] !== '' ) ? ' ' . $settings['title'] : '';
					$duplicate['post_title'] = $duplicate['post_title'] . ' ' . $appended;
					$duplicate['post_name']  = sanitize_title( $duplicate['post_name'] . '-' . $settings['slug'] );

					// Set the status
					if ( $settings['status'] !== 'same' ) {
						$duplicate['post_status'] = $settings['status'];
					}

					// Set the type
					if ( $settings['type'] !== 'same' ) {
						$duplicate['post_type'] = $settings['type'];
					}

					// Set the post date
					$timestamp     = ( $settings['timestamp'] === 'duplicate' ) ? strtotime( $duplicate['post_date'] ) : current_time( 'timestamp', 0 );
					$timestamp_gmt = ( $settings['timestamp'] === 'duplicate' ) ? strtotime( $duplicate['post_date_gmt'] ) : current_time( 'timestamp', 1 );

					if ( $settings['time_offset'] ) {
						$offset = intval( $settings['time_offset_seconds'] + $settings['time_offset_minutes'] * 60 + $settings['time_offset_hours'] * 3600 + $settings['time_offset_days'] * 86400 );
						if ( $settings['time_offset_direction'] === 'newer' ) {
							$timestamp     = intval( $timestamp + $offset );
							$timestamp_gmt = intval( $timestamp_gmt + $offset );
						} else {
							$timestamp     = intval( $timestamp - $offset );
							$timestamp_gmt = intval( $timestamp_gmt - $offset );
						}
					}
					$duplicate['post_date']         = date( 'Y-m-d H:i:s', $timestamp );
					$duplicate['post_date_gmt']     = date( 'Y-m-d H:i:s', $timestamp_gmt );
					$duplicate['post_modified']     = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
					$duplicate['post_modified_gmt'] = date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) );

					// Remove some of the keys
					unset( $duplicate['ID'] );
					unset( $duplicate['guid'] );
					unset( $duplicate['comment_count'] );

					// Insert the post into the database
					$duplicate_id = wp_insert_post( $duplicate );

					// Duplicate all the taxonomies/terms
					$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
					foreach ( $taxonomies as $taxonomy ) {
						$terms = wp_get_post_terms( $original_id, $taxonomy, array(
							'fields' => 'names',
						) );
						wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
					}

					// Duplicate all the custom fields
					$custom_fields = get_post_custom( $original_id );
					foreach ( $custom_fields as $key => $value ) {
						if ( is_array( $value ) && count( $value ) > 0 ) {
							foreach ( $value as $i => $v ) {
								$result = $wpdb->insert( $wpdb->prefix . 'postmeta', array(
									'post_id'    => $duplicate_id,
									'meta_key'   => $key,
									'meta_value' => $v,
								) );
							}
						}
					}

					do_action( 'wcct_post_duplicated', $original_id, $duplicate_id, $settings );

					wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '&section=' . $section ) );
				}
			} else {
				die( __( 'Unable to Duplicate', 'finale-woocommerce-sales-countdown-timer-discount' ) );
			}
		}
	}

	/**
	 * @hooked over `cmb2 after field save`
	 *
	 * @param $post_id
	 */
	public function clear_transients( $post_id ) {
		if ( class_exists( 'XL_Transient' ) ) {
			$xl_transient_obj = XL_Transient::get_instance();
			$xl_transient_obj->delete_all_transients( 'finale' );
		}

		WCCT_Common::wcct_maybe_clear_cache();
	}

	/**
	 * @hooked over `delete_post`
	 *
	 * @param $post_id
	 */
	public function clear_transients_on_delete( $post_id ) {
		$get_post_type = get_post_type( $post_id );

		if ( WCCT_Common::get_campaign_post_type_slug() === $get_post_type ) {
			if ( class_exists( 'XL_Transient' ) ) {
				$xl_transient_obj = XL_Transient::get_instance();
				$xl_transient_obj->delete_all_transients( 'finale' );
			}

			WCCT_Common::wcct_maybe_clear_cache();
		}
	}


	/**
	 * Check the screen and check if plugins update available to show notification to the admin to update the plugin
	 */
	public function maybe_show_advanced_update_notification() {

		$screen = get_current_screen();

		if ( is_object( $screen ) && ( 'plugins.php' === $screen->parent_file || 'index.php' === $screen->parent_file || WCCT_Common::get_wc_settings_tab_slug() === filter_input( INPUT_GET, 'tab' ) ) ) {
			$plugins = get_site_transient( 'update_plugins' );
			if ( isset( $plugins->response ) && is_array( $plugins->response ) ) {
				$plugins = array_keys( $plugins->response );
				if ( is_array( $plugins ) && count( $plugins ) > 0 && in_array( WCCT_PLUGIN_BASENAME, $plugins, true ) ) {
					?>
                    <div class="notice notice-warning is-dismissible">
                        <p>
							<?php
							_e( sprintf( 'Attention: There is an update available of <strong>%s</strong> plugin. &nbsp;<a href="%s" class="">Go to updates</a>', WCCT_FULL_NAME, admin_url( 'plugins.php?s=finale&plugin_status=all' ) ), 'finale-woocommerce-sales-countdown-timer-discount' );
							?>
                        </p>
                    </div>
					<?php

				}
			}
		}

	}

	/**
	 * Display Counter bar error when inventory bar is disabled.
	 * @since 2.6.0
	 */
	public function show_counter_bar_error() {
		global $post;
		if ( $post instanceof WP_Post && $post->post_type === WCCT_Common::get_campaign_post_type_slug() ) {
			$meta = WCCT_Common::get_item_data( $post->ID );
			if ( $meta['location_bar_show_single'] == 1 && $meta['deal_enable_goal'] != '1' ) {
				?>
                <div class="notice notice-error">
                    <p><strong>Finale</strong>: You have enabled the Counter Bar on this campaign, but Inventory is OFF. It should be ON for Counter Bar to be visible and work.</p>
                </div>
				<?php
			}
		}
	}

	public function delete_post_data_transient( $post_id, $post = null ) {

		/** Check it's not an auto save routine */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		/** Perform permission checks */
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		/** Return of xl_transient class not exist */
		if ( ! class_exists( 'XL_Transient' ) ) {
			return;
		}

		/** If calling wp_update_post, unhook this function so it doesn't loop infinitely */
		remove_action( 'save_post', array( $this, 'delete_post_data_transient' ), 99, 2 );

		WCCT_Common::delete_post_data( $post_id );
	}

	/**
	 * Remove CMB2 any style or script that have cmb2 name in the src
	 */
	public function removing_scripts_finale_campaign_load() {
		global $wp_scripts, $wp_styles;

		if ( false === WCCT_Common::wcct_valid_admin_pages( 'single' ) ) {
			return;
		}

		$mod_wp_scripts = $wp_scripts;
		$assets         = $wp_scripts;

		if ( 'admin_print_styles' === current_action() ) {
			$mod_wp_scripts = $wp_styles;
			$assets         = $wp_styles;
		}

		if ( is_object( $assets ) && isset( $assets->registered ) && count( $assets->registered ) > 0 ) {
			foreach ( $assets->registered as $handle => $script_obj ) {
				if ( ! isset( $script_obj->src ) || empty( $script_obj->src ) ) {
					continue;
				}
				$src = $script_obj->src;

				/** Remove scripts of massive VC addons plugin */
				if ( strpos( $src, 'mpc-massive/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of visual-products-configurator-for-woocommerce plugin */
				if ( strpos( $src, 'visual-products-configurator-for-woocommerce/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of clever mega menu plugin */
				if ( strpos( $src, 'clever-mega-menu/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of VC addons from ronneby theme */
				if ( strpos( $src, 'ronneby/inc/vc_custom/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove css from ronneby theme */
				if ( strpos( $src, 'ronneby/assets/css/admin-panel.css' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of Swift Framework plugin */
				if ( strpos( $src, 'swift-framework/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** If script doesn't belong to a plugin continue */
				if ( strpos( $src, '/tt-proven/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** If script doesn't belong to a theplus_elementor_addon continue */
				if ( strpos( $src, 'plus-options/cmb2-conditionals.js' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of content-tooltip plugin */
				if ( strpos( $src, 'content-tooltip/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of nav-menus plugin */
				if ( strpos( $src, 'nav-menus/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}
				/** Remove scripts of widget-contexts plugin */
				if ( strpos( $src, 'widget-contexts/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}

				/** Remove scripts of wp hr manager plugin */
				if ( strpos( $src, 'wp-hr-manager/' ) !== false ) {
					unset( $mod_wp_scripts->registered[ $handle ] );
				}
        
				/** If no cmb2 in src continue */
				if ( strpos( $src, 'cmb2' ) === false ) {
					continue;
				}

				/** If script doesn't belong to a theme continue */
				if ( strpos( $src, 'themes/' ) === false ) {
					continue;
				}

				/** Allow assets of ascend_premium theme */
				if ( strpos( $src, 'themes/ascend_premium' ) !== false ) {
					continue;
				}

				/** Unset cmb2 script */
				unset( $mod_wp_scripts->registered[ $handle ] );
			}
		}

		if ( 'admin_print_styles' === current_action() ) {
			$wp_styles = $mod_wp_scripts;
		} else {
			$wp_scripts = $mod_wp_scripts;
		}

	}

}

new XLWCCT_Admin();

