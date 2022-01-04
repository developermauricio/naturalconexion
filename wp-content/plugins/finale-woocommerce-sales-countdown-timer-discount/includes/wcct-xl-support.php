<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class WCCT_XL_Support
 * @package Finale-Lite
 * @author XlPlugins
 */
class WCCT_XL_Support {

	public static $_instance = null;
	public $full_name = WCCT_FULL_NAME;
	public $is_license_needed = false;
	public $license_instance;
	public $expected_url;

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = 'Sales Triggers';
	protected $slug = 'wcct';

	public function __construct() {
		$this->expected_url = admin_url( 'admin.php?page=xlplugins' );

		/**
		 * XL CORE HOOKS
		 */
		add_filter( 'xl_optin_notif_show', array( $this, 'wcct_xl_show_optin_pages' ), 10, 1 );

		add_action( 'admin_init', array( $this, 'wcct_xl_expected_slug' ), 9.1 );

		add_action( 'admin_init', array( $this, 'modify_api_args_if_wcct_dashboard' ), 20 );
		add_filter( 'extra_plugin_headers', array( $this, 'extra_woocommerce_headers' ) );

		add_filter( 'add_menu_classes', array( $this, 'modify_menu_classes' ) );

		add_action( 'xl_licenses_submitted', array( $this, 'process_licensing_form' ) );
		add_action( 'xl_deactivate_request', array( $this, 'maybe_process_deactivation' ) );

		add_filter( 'xl_dashboard_tabs', array( $this, 'wcct_modify_tabs' ), 999, 1 );
		add_filter( 'xl_after_license_table_notice', array( $this, 'wcct_after_license_table_notice' ), 999, 1 );

		//        add_action('xl_support_right_area', array($this, 'wcct_add_right_section_on_xlpages'));
		//        add_action('xl_licenses_right_content', array($this, 'wcct_add_right_section_on_xlpages'));
		add_action( 'wcct_options_page_right_content', array( $this, 'wcct_options_page_right_content' ), 10 );

		add_action( 'admin_menu', array( $this, 'add_menus' ), 86.1 );
		add_action( 'admin_menu', array( $this, 'add_wcct_menu' ), 85.2 );

		add_action( 'xl_tabs_modal_licenses', array( $this, 'schedule_license_check' ), 1 );
		add_filter( 'xl_uninstall_reasons', array( $this, 'modify_uninstall_reason' ) );

		add_filter( 'xl_uninstall_reason_threshold_' . WCCT_PLUGIN_BASENAME, function () {
			return 12;
		} );
		add_filter( 'xl_default_reason_' . WCCT_PLUGIN_BASENAME, function () {
			return 8;
		} );
		// tools
		add_action( 'admin_init', array( $this, 'download_tools_settings' ), 2 );
		add_action( 'xl_tools_after_content', array( $this, 'export_tools_after_content' ) );
		add_action( 'xl_tools_after_content', array( $this, 'export_xl_tools_right_area' ) );
		add_action( 'xl_fetch_tools_data', array( $this, 'xl_fetch_tools_data' ), 10, 2 );

		add_filter( 'xl_in_update_message_support', array( $this, 'finale_update_message' ), 10 );
	}

	/**
	 * @return null|WCCT_XL_Support
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function wcct_xl_show_optin_pages( $is_show ) {
		return true;
	}

	public function wcct_xl_expected_slug() {
		if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'xlplugins' || $_GET['page'] == 'xlplugins-support' || $_GET['page'] == 'xlplugins-addons' ) ) {
			XL_dashboard::set_expected_slug( $this->slug );
		}
		XL_dashboard::set_expected_url( $this->expected_url );

		/**
		 * Pushing notifications for invalid licenses found in ecosystem
		 */
		$licenses         = XL_licenses::get_instance()->get_data();
		$invalid_licenses = array();
		if ( $licenses && count( $licenses ) > 0 ) {
			foreach ( $licenses as $key => $license ) {
				if ( $license['product_status'] == 'invalid' ) {
					$invalid_licenses[] = $license['plugin'];
				}
			}
		}

		if ( ! XL_admin_notifications::has_notification( 'license_needs_attention' ) && count( $invalid_licenses ) > 0 ) {
			$license_invalid_text = sprintf( __( '<p>You are <strong>not receiving</strong> Latest Updates, New Features, Security Updates &amp; Bug Fixes for <strong>%1$s</strong>. <a href="%2$s">Click Here To Fix This</a>.</p>', 'finale-woocommerce-sales-countdown-timer-discount' ), implode( ',', $invalid_licenses ), add_query_arg( array(
				'tab' => 'licenses',
			), $this->expected_url ) );

			XL_admin_notifications::add_notification( array(
				'license_needs_attention' => array(
					'type'           => 'error',
					'is_dismissable' => false,
					'content'        => $license_invalid_text,
				),
			) );
		}
	}

	public function wcct_metabox_always_open( $classes ) {
		if ( ( $key = array_search( 'closed', $classes ) ) !== false ) {
			unset( $classes[ $key ] );
		}

		return $classes;
	}

	public function modify_api_args_if_wcct_dashboard() {
		if ( XL_dashboard::get_expected_slug() == $this->slug ) {
			add_filter( 'xl_api_call_agrs', array( $this, 'modify_api_args_for_gravityxl' ) );
			XL_dashboard::register_dashboard( array(
				'parent' => array(
					'woocommerce' => 'WooCommerce Add-ons',
				),
				'name'   => $this->slug,
			) );
		}
	}

	public function xlplugins_page() {
		if ( ! isset( $_GET['tab'] ) ) {
			$licenses = apply_filters( 'xl_plugins_license_needed', array() );
			if ( empty( $licenses ) ) {
				XL_dashboard::$selected = 'support';
			} else {
				XL_dashboard::$selected = 'licenses';
			}
		}
		XL_dashboard::load_page();
	}

	public function xlplugins_support_page() {
		if ( ! isset( $_GET['tab'] ) ) {
			XL_dashboard::$selected = 'support';
		}
		XL_dashboard::load_page();
	}

	public function xlplugins_plugins_page() {
		XL_dashboard::$selected = 'plugins';
		XL_dashboard::load_page();
	}

	public function modify_api_args_for_gravityxl( $args ) {
		if ( isset( $args['edd_action'] ) && $args['edd_action'] == 'get_xl_plugins' ) {
			$args['attrs']['tax_query'] = array(
				array(
					'taxonomy' => 'xl_edd_tax_parent',
					'field'    => 'slug',
					'terms'    => 'woocommerce',
					'operator' => 'IN',
				),
			);
		}
		$args['purchase'] = WCCT_PURCHASE;

		return $args;
	}

	/**
	 * Adding XL Header to tell WordPress to read one extra params while reading plugin's header info/. <br/>
	 * Hooked over `extra_plugin_headers`
	 *
	 * @param array $headers already registered arrays
	 *
	 * @return type
	 * @since 1.0.0
	 *
	 */
	public function extra_woocommerce_headers( $headers ) {
		array_push( $headers, 'XL' );

		return $headers;
	}

	public function modify_menu_classes( $menu ) {
		return $menu;
	}

	public function process_licensing_form( $posted_data ) {
		if ( isset( $posted_data['license_keys'][ WCCT_PLUGIN_BASENAME ] ) ) {
			$shortname = $this->edd_slugify_module_name( $this->full_name );
			update_option( 'xl_licenses_' . $shortname, $posted_data['license_keys'][ WCCT_PLUGIN_BASENAME ], false );
			$this->license_instance->activate_license( $posted_data['license_keys'][ WCCT_PLUGIN_BASENAME ] );
		}
	}

	/**
	 * License management helper function to create a slug that is friendly with edd
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public function edd_slugify_module_name( $name ) {
		return preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $name ) ) );
	}

	/**
	 * Validate is it is for email product deactivation
	 *
	 * @param $posted_data
	 */
	public function maybe_process_deactivation( $posted_data ) {
		if ( isset( $posted_data['filepath'] ) && $posted_data['filepath'] === WCCT_PLUGIN_BASENAME ) {
			$this->license_instance->deactivate_license();
			wp_safe_redirect( 'admin.php?page=' . $posted_data['page'] . '&tab=' . $posted_data['tab'] );
		}
	}

	public function wcct_modify_tabs( $tabs ) {
		if ( $this->slug === XL_dashboard::get_expected_slug() ) {
			return array();
		}

		return $tabs;
	}

	public function wcct_after_license_table_notice( $notice ) {
		return 'Note: You need to have a valid license key to receiving updates for these plugins. Click here to get your <a href="https://xlplugins.com/manage-licenses/" target="_blank">License Keys</a>.';
	}

	public function wcct_add_right_section_on_xlpages() {

	}

	/**
	 * Adding WooCommerce sub-menu for global options
	 */
	public function add_menus() {
		if ( ! XL_dashboard::$is_core_menu ) {

			add_menu_page( __( 'XLPlugins', 'finale-woocommerce-sales-countdown-timer-discount' ), __( 'XLPlugins', 'finale-woocommerce-sales-countdown-timer-discount' ), 'manage_woocommerce', 'xlplugins', array(
				$this,
				'xlplugins_page',
			), '', '59.5' );

			$licenses = apply_filters( 'xl_plugins_license_needed', array() );
			if ( ! empty( $licenses ) ) {
				add_submenu_page( 'xlplugins', __( 'Licenses', 'finale-woocommerce-sales-countdown-timer-discount' ), __( 'License', 'finale-woocommerce-sales-countdown-timer-discount' ), 'manage_woocommerce', 'xlplugins' );
			}

			XL_dashboard::$is_core_menu = true;
		}
	}

	public function add_wcct_menu() {
		add_submenu_page( 'xlplugins', WCCT_FULL_NAME, 'Finale Lite', 'manage_woocommerce', 'admin.php?page=wc-settings&tab=' . WCCT_Common::get_wc_settings_tab_slug() . '', false );
	}

	public function wcct_options_page_right_content() {
		$get_instance_access_link = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'ebook-download',
			'utm_term'     => '13-promotional-campaign-ideas',
		), 'https://xlplugins.com/high-converting-woocommerce-sales-promotional-campaigs/' );
		$go_pro_link              = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'plugin-resource',
			'utm_term'     => 'buy_now',
		), 'https://xlplugins.com/lite-to-pro-upgrade-page/' );
		$demo_link                = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'plugin-resource',
			'utm_term'     => 'demo',
		), 'http://demo.xlplugins.com/finale/' );
		$support_link             = add_query_arg( array(
			'pro'          => 'finale',
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'plugin-resource',
			'utm_term'     => 'support',
		), 'https://xlplugins.com/support/' );
		$documentation_link       = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'plugin-resource',
			'utm_term'     => 'documentation',
		), 'https://xlplugins.com/documentation/' );

		$other_products = array();

		/** Finale */
		$finale_link = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'other-products',
			'utm_term'     => 'finale',
		), 'https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/' );
		if ( ! class_exists( 'WCCT_Core' ) ) {
			$other_products['finale'] = array(
				'image' => 'finale.png',
				'link'  => $finale_link,
				'head'  => 'Finale WooCommerce Sales Countdown Timer',
				'desc'  => 'Run Urgency Marketing Campaigns On Your Store And Move Buyers to Make A Purchase',
			);
		}

		/** Sales Trigger */
		$sales_trigger_link = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'other-products',
			'utm_term'     => 'sales-trigger',
		), 'https://xlplugins.com/woocommerce-sales-triggers/' );
		if ( ! defined( 'WCST_SLUG' ) ) {
			$other_products['sales_trigger'] = array(
				'image' => 'sales-trigger.png',
				'link'  => $sales_trigger_link,
				'head'  => 'XL WooCommerce Sales Triggers',
				'desc'  => 'Use 7 Built-in Sales Triggers to Optimise Single Product Pages For More Conversions',
			);
		}

		/** NextMove */
		$nextmove_link = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'sidebar',
			'utm_campaign' => 'other-products',
			'utm_term'     => 'nextmove',
		), 'https://xlplugins.com/woocommerce-thank-you-page-nextmove/' );
		if ( ! class_exists( 'XLWCTY_Core' ) ) {
			$other_products['nextmove'] = array(
				'image' => 'nextmove.png',
				'link'  => $nextmove_link,
				'head'  => 'NextMove WooCommerce Thank You Pages',
				'desc'  => 'Get More Repeat Orders With 17 Plug n Play Components',
			);
		}

		if ( is_array( $other_products ) && count( $other_products ) > 0 ) {
			$offer_link   = add_query_arg( array(
				'utm_source'   => 'finale-lite',
				'utm_medium'   => 'sidebar',
				'utm_campaign' => 'other-products',
				'utm_term'     => 'easter-offer',
			), 'https://xlplugins.com/exclusive-offers/' );
			$bundle_link  = add_query_arg( array(
				'utm_source'   => 'finale-lite',
				'utm_medium'   => 'sidebar',
				'utm_campaign' => 'other-products',
				'utm_term'     => 'exclusive-bundle',
			), 'https://xlplugins.com/exclusive-offers/' );
			$easter_offer = false;
			if ( date( 'Ymd' ) > 20180320 && date( 'Ymd' ) < 20180403 ) {
				$easter_offer = true;
			}

			if ( true === $easter_offer ) {
				?>
                <h3>Checkout Offer & Plugins:</h3>
                <div class="postbox wcct_side_content wcct_xlplugins wcct_xlplugins_easter_offer">
                    <a href="<?php echo $offer_link; ?>" target="_blank"></a>
                    <img src="<?php echo plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/easter.png'; ?>"/>
                    <div class="wcct_plugin_head">EASTER SPL.: UPTO 20% OFF!</div>
                    <div class="wcct_plugin_desc">Upgrade yourself to the full-feature plan or buy the plugin bundle at a hugely discounted price! Click here.</div>
                </div>
				<?php
			} else {
				$bundle_text = 'Get up to <strong><u>20% off</u></strong> on our bundles. Club our best-seller Finale with our other conversion-lifting plugins.<br>Act fast!';

				$current_date_obj = new DateTime( 'now', new DateTimeZone( 'America/New_York' ) );
				/** Black friday */
				if ( $current_date_obj->getTimestamp() > 1542945600 && $current_date_obj->getTimestamp() < 1543550400 ) {
					$bundle_text = 'Get flat <strong><u>30% off</u></strong> on our bundles. Club our best-seller Finale with our other conversion-lifting plugins.<br>Act fast!';
				} /** Christmas */ elseif ( $current_date_obj->getTimestamp() > 1545278400 && $current_date_obj->getTimestamp() < 1546401600 ) {
					$bundle_text = 'Get flat <strong><u>25% off</u></strong> on our bundles. Club our best-seller Finale with our other conversion-lifting plugins.<br>Act fast!';
				}
				?>
                <h3>Conversion Essentials Bundle</h3>
                <div class="postbox wcct_side_content wcct_xlplugins wcct_xlplugins_bundle">
                    <a href="<?php echo $bundle_link; ?>" target="_blank"></a>
                    <img src="<?php echo plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/special-offers.png'; ?>">
                    <div class="wcct_plugin_head">Considering Finale? Here's a great deal for you!</div>
                    <div class="wcct_plugin_desc"><?php echo $bundle_text; ?></div>
                </div>
                <h3>Unlock Premium Finale Features</h3>
                <div class="postbox wcct_side_content wcct_xlplugins wcct_xlplugins_finale">
                    <a href="<?php echo $finale_link; ?>" target="_blank"></a>
                    <img src="<?php echo plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/finale.png'; ?>">
                    <div class="wcct_plugin_desc">Now create Recurring and Evergreen campaigns too! Or set up dedicated Deals pages, Sticky Header and Footer and more. Create Campaigns that convert
                        with Finale Pro.
                    </div>
                </div>
                <h3>Checkout Our Other Plugins</h3>
				<?php
			}
			foreach ( $other_products as $product_short_name => $product_data ) {
				?>
                <div class="postbox wcct_side_content wcct_xlplugins wcct_xlplugins_<?php echo $product_short_name; ?>">
                    <a href="<?php echo $product_data['link']; ?>" target="_blank"></a>
                    <img src="<?php echo plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/' . $product_data['image']; ?>"/>
                    <div class="wcct_plugin_head"><?php echo $product_data['head']; ?></div>
                    <div class="wcct_plugin_desc"><?php echo $product_data['desc']; ?></div>
                </div>
				<?php
			}
			?>
			<?php
		}
		?>
        <div class="postbox wcct_side_content">
            <div class="inside">
                <h3>Resources</h3>
                <ul>
                    <li><a href="<?php echo $go_pro_link; ?>" target="_blank">Get PRO</a></li>
                    <li><a href="<?php echo $demo_link; ?>" target="_blank">Demo</a></li>
                    <li><a href="<?php echo $support_link; ?>" target="_blank">Support</a></li>
                    <li><a href="<?php echo $documentation_link; ?>" target="_blank">Documentation</a></li>
                </ul>
            </div>
        </div>
		<?php
	}

	public function schedule_license_check() {
		wp_schedule_single_event( time() + 10, 'wcct_maybe_schedule_check_license' );
	}

	public function modify_uninstall_reason( $reasons ) {
		$reasons_our = $reasons;

		$reason_other = array(
			'id'                => 7,
			'text'              => __( 'Other', 'finale-woocommerce-sales-countdown-timer-discount' ),
			'input_type'        => 'textfield',
			'input_placeholder' => __( 'Other', 'finale-woocommerce-sales-countdown-timer-discount' ),
		);

		$countdown_timer_debug_doc_link = add_query_arg( array(
			'utm_source' => 'finale-lite',
			'utm_medium' => 'deactivation-modal',
			'utm_term'   => 'timer-bar-not-showing',
		), 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/troubleshooting-guides/unable-to-see-countdown-timer-or-counter-bar/' );

		$explore_feature_link = add_query_arg( array(
			'utm_source' => 'finale-lite',
			'utm_medium' => 'deactivation-modal',
			'utm_term'   => 'explore-finale',
		), 'https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/' );

		$supoort_ticket_link = admin_url( 'admin.php?page=xlplugins' );

		$product_position_mismatch_link = add_query_arg( array(
			'utm_source' => 'finale-lite',
			'utm_medium' => 'deactivation-modal',
			'utm_term'   => 'timer-bar-position-mismatch',
		), 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/troubleshooting-guides/countdown-timer-or-counter-bar-render-at-wrong-positions/' );

		$xl_contact_link = add_query_arg( array(
			'utm_source' => 'finale-lite',
			'utm_medium' => 'deactivation-modal',
			'utm_term'   => 'contact',
		), 'https://xlplugins.com/contact/' );

		$reasons_our[ WCCT_PLUGIN_BASENAME ] = array(
			array(
				'id'                => 8,
				'text'              => __( 'I am going to upgrade to PRO version', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( "Smart choice! Finale PRO has tons of additional features to boost your revenue. <a href='" . $explore_feature_link . "' target='_blank'>Explore the features</a>.", 'finale-woocommerce-sales-countdown-timer-discount' ),
			),

			array(
				'id'                => 29,
				'text'              => __( 'Countdown Timer or Counter Bar didn\'t show even while campaign was running', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'There could be multiple reasons for this. Take 2 mins and read <a href="' . $countdown_timer_debug_doc_link . '" target="_blank">step by step guide</a> to get resolution.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			),
			array(
				'id'                => 30,
				'text'              => __( 'Expected discount amount didn\'t appear', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'There could be a caching plugin, try clearing Cache.<br/>OR you could be using other plugins that modify pricing such as currency switcher, discounting plugin, etc. Then Raise a <a href="' . $supoort_ticket_link . '">Support ticket</a> and will try & help resolve this.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			),
			array(
				'id'                => 31,
				'text'              => __( 'Campaigns were not restricted as per rules', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Raise a <a href="' . $supoort_ticket_link . '">Support ticket</a> with the screenshot of rules settings and will help you resolve this.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			),
			array(
				'id'                => 32,
				'text'              => __( 'Countdown Timer or Counter Bar didn\'t appear at right positions', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'It seems your theme modified the native WooCommerce positions. Take 2 mins and read <a href="' . $product_position_mismatch_link . '" target="_blank">step by step guide</a> to get resolution.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			),
			array(
				'id'                => 33,
				'text'              => __( 'Finale Activation caused PHP Errors or blank white screen', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Ensure you have the latest version of WooCommerce & Finale. There could be a possibility of conflict with other plugins. Raise a <a href="' . $supoort_ticket_link . '">Support ticket</a> and will help you resolve this.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			),
			array(
				'id'                => 34,
				'text'              => __( 'Add to Cart wasn\'t working', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Check Finale\'s Inventory settings or see if you have order with \'Pending Payment\' state. As they may block product inventory.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			),
			array(
				'id'                => 41,
				'text'              => __( 'Troubleshooting conflicts with other plugins', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Hope you could resolve conflicts soon.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			),
			array(
				'id'                => 35,
				'text'              => __( 'Doing Testing', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Hope to see you using it again.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			),
			array(
				'id'                => 1,
				'text'              => __( 'I no longer need the plugin', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'html'              => __( 'Sorry to know that! How can we better your experience? We may be able to fix what we are aware of. Please <a href="' . $xl_contact_link . '" target="_blank">let us know</a>.', 'finale-woocommerce-sales-countdown-timer-discount' ),
			),
		);

		array_push( $reasons_our[ WCCT_PLUGIN_BASENAME ], $reason_other );

		return $reasons_our;
	}

	public function export_tools_after_content( $model ) {
		$system_info = XL_Support::get_instance()->prepare_system_information_report() . "\r" . $this->xl_support_system_info();
		?>
        <div class="xl_core_tools">
            <h2><?php echo __( 'Finale Lite' ); ?></h2>
            <form method="post">
                <div class="xl_core_tools_inner" style="min-height: 300px;">
                    <textarea name="xl_tools_system_info" readonly style="width:100%;height: 280px;"><?php echo $system_info; ?></textarea>
                </div>
                <div style="clear: both;"></div>
                <div class="xl_core_tools_button" style="margin-bottom: 10px;">
                    <a class="button button-primary button-large xl_core_tools_btn" data-plugin="nextmove-lite" href="
					<?php
					echo add_query_arg( array(
						'content'  => 'wcct_countdown',
						'download' => 'true',
					), admin_url( 'export.php' ) )
					?>
					"><?php echo __( 'Export Finale Campaign', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></a>
                    <button type="submit" class="button button-primary button-large xl_core_tools_btn" name="xl_tools_export_setting"
                            value="finale-lite"><?php echo __( 'Export Settings', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></button>
                </div>
                <br>
            </form>
        </div>
		<?php
	}

	public function xl_support_system_info( $return = false ) {
		$setting_report   = array();
		$nm_options       = array();
		$setting_report[] = '#### Finale Lite Settings start here ####';

		$free_shipping = $this->get_shipping_method();
		if ( is_array( $free_shipping ) && count( $free_shipping ) > 0 ) {
			$nm_options['free_coupon_method'] = $free_shipping;
			$setting_report[]                 = "\r***Avaiable Free Shipping Method***\r";
			foreach ( $free_shipping as $sk => $shipping ) {
				$sk ++;
				$setting_report[] = "\t{$sk} title - {$shipping["title"]} ";
				$setting_report[] = "\trequires - {$shipping["requires"]} ";
				$setting_report[] = "\tmin_amount - {$shipping["min_amount"]} \r";
			}
		}

		$free_shipping_coupon = $this->get_free_shipping_coupon();
		if ( is_array( $free_shipping_coupon ) && count( $free_shipping_coupon ) > 0 ) {
			$nm_options['free_coupon_method_coupons'] = $free_shipping_coupon;
			$setting_report[]                         = "\r***Avaiable Free Shipping Method Coupons (recent 10 only)*** \r";
			foreach ( $free_shipping_coupon as $sk => $shipping_coupon ) {
				$setting_report[] = "\tcoupon id-{$shipping_coupon["id"]} ";
				if ( isset( $shipping_coupon['date_expires'] ) && $shipping_coupon['date_expires'] != '' ) {
					$date_expires     = gmdate( 'Y-m-d', $shipping_coupon['date_expires'] );
					$setting_report[] = "\tdate_expires - {$date_expires} (yy-mm-dd)";
				}
				if ( isset( $shipping_coupon['expiry_date'] ) && $shipping_coupon['expiry_date'] != '' ) {
					$setting_report[] = "\texpiry_date - {$shipping_coupon["expiry_date"]} ";
				}
				if ( isset( $shipping_coupon['coupon_code'] ) && $shipping_coupon['coupon_code'] != '' ) {
					$setting_report[] = "\tcoupon_code - {$shipping_coupon["coupon_code"]} \r";
				}
			}
		}

		if ( $return ) {
			return array(
				'finale_settings' => $nm_options,
			);

		}

		$setting_report[] = '#### Finale Lite Settings  end here ####';

		return implode( "\r", $setting_report );
	}

	public function get_shipping_method() {
		global $wpdb;
		$output     = array();
		$freeMethod = $wpdb->get_results( "select * from {$wpdb->prefix}woocommerce_shipping_zone_methods where method_id='free_shipping'", ARRAY_A );
		if ( is_array( $freeMethod ) && count( $freeMethod ) > 0 ) {
			foreach ( $freeMethod as $method ) {
				$free_shipping = get_option( "woocommerce_free_shipping_{$method["method_order"]}_settings", array() );
				if ( is_array( $free_shipping ) && count( $free_shipping ) > 0 ) {
					$output[] = $free_shipping;
				}
			}
		}

		return $output;

	}

	public function get_free_shipping_coupon() {
		global $wpdb;
		$free_coupon = $wpdb->get_results( "select p.id,p.post_title from {$wpdb->prefix}postmeta as m join {$wpdb->prefix}posts as p on m.post_id=p.id where m.meta_key='free_shipping' and m.meta_value='yes' and p.post_type='shop_coupon' and p.post_status='publish' order by p.post_date desc limit 10 ", ARRAY_A );
		if ( is_array( $free_coupon ) && count( $free_coupon ) > 0 ) {
			foreach ( $free_coupon as $key => $value ) {
				$date_expires                        = get_post_meta( $value['id'], 'date_expires', true );
				$expiry_date                         = get_post_meta( $value['id'], 'expiry_date', true );
				$free_coupon[ $key ]['date_expires'] = $date_expires;
				$free_coupon[ $key ]['expiry_date']  = $expiry_date;
				$post_title                          = $free_coupon[ $key ]['post_title'];
				unset( $free_coupon[ $key ]['post_title'] );
				$free_coupon[ $key ]['coupon_code'] = $post_title;
			}
		}

		return $free_coupon;
	}

	public function export_xl_tools_right_area() {
		//      echo "Hello right content";
	}

	public function download_tools_settings() {
		if ( isset( $_POST['xl_tools_export_setting'] ) && $_POST['xl_tools_export_setting'] == 'finale-lite' && isset( $_POST['xl_tools_system_info'] ) && $_POST['xl_tools_system_info'] != '' ) {
			$system_info = XL_Support::get_instance()->prepare_system_information_report( true ) + $this->xl_support_system_info( true );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=finale_lite.json' );
			echo( json_encode( $system_info ) );
			exit;
		}
	}

	public function xl_fetch_tools_data( $file, $post ) {

		if ( $file == 'finale-woocommerce-sales-countdown-timer-discount-plugin-lite.php' ) {
			$xl_support_url = '';
			$system_info    = XL_Support::get_instance()->prepare_system_information_report( true ) + $this->xl_support_system_info( true );
			$upload_dir     = wp_upload_dir();
			$basedir        = $upload_dir['basedir'];
			$baseurl        = $upload_dir['baseurl'];
			if ( is_writable( $basedir ) ) {
				$xl_support     = $basedir . '/xl_support';
				$xl_support_url = $baseurl . '/xl_support';
				if ( ! file_exists( $xl_support ) ) {
					mkdir( $xl_support, 0755, true );
				}
				if ( count( $system_info ) > 0 ) {
					$xl_support_file_path = $xl_support . '/finale-lite-support.json';
					$success              = file_put_contents( $xl_support_file_path, json_encode( $system_info ) );
					if ( $success ) {
						$xl_support_url .= '/finale-lite-support.json';
					}
				}
			}
			echo $xl_support_url;
		}
	}

	public function finale_update_message( $config ) {
		$config[ WCCT_PLUGIN_BASENAME ] = 'https://plugins.svn.wordpress.org/finale-woocommerce-sales-countdown-timer-discount/trunk/readme.txt';

		return $config;
	}

}

if ( class_exists( 'WCCT_XL_Support' ) ) {
	WCCT_Core::register( 'xl_support', 'WCCT_XL_Support' );
}
