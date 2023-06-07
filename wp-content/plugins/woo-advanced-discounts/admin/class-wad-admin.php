<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.orionorigin.com/
 * @since      0.1
 *
 * @package    Wad
 * @subpackage Wad/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wad
 * @subpackage Wad/admin
 * @author     ORION <support@orionorigin.com>
 */
class Wad_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;



	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name ) {

		$this->plugin_name = $plugin_name;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wad_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wad_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wad-admin.css', array(), WAD_VERSION, 'all' );
		wp_enqueue_style( 'o-flexgrid', plugin_dir_url( __FILE__ ) . 'css/flexiblegs.css', array(), WAD_VERSION, 'all' );
		wp_enqueue_style( 'o-ui', plugin_dir_url( __FILE__ ) . 'css/UI.css', array(), WAD_VERSION, 'all' );
		wp_enqueue_style( 'o-datepciker', plugin_dir_url( __FILE__ ) . 'js/o-datepicker/css/datepicker.css', array(), WAD_VERSION, 'all' );
		wp_enqueue_style( 'wad-datetimepicker', plugin_dir_url( __FILE__ ) . 'js/o-datetimepicker/jquery.datetimepicker.css', array(), WAD_VERSION, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wad-admin.js', array( 'jquery', 'o-admin' ), WAD_VERSION, false );
		wp_enqueue_script( 'o-admin', plugin_dir_url( __FILE__ ) . 'js/o-admin.js', array( 'jquery' ), WAD_VERSION, false );
		wp_enqueue_script( 'wad-tabs', plugin_dir_url( __FILE__ ) . 'js/SpryAssets/SpryTabbedPanels.js', array( 'jquery' ), WAD_VERSION, false );
		wp_enqueue_script( 'wad-serializejson', plugin_dir_url( __FILE__ ) . 'js/jquery.serializejson.min.js', array( 'jquery' ), WAD_VERSION, false );
		wp_enqueue_script( 'wad-datetimepicker', plugin_dir_url( __FILE__ ) . 'js/o-datetimepicker/jquery.datetimepicker.full.min.js', array( 'jquery' ), WAD_VERSION, false );
	}

		/*
	 * disable acf timepicker script as needed
	 */
	function acf_pro_dequeue_script() {
		if ( class_exists( 'acf' ) && is_admin() ) {
			$data = filter_input( INPUT_GET, 'post' );
		}

		if ( strpos( $_SERVER['REQUEST_URI'], '?post_type=o-discount' ) || get_post_type( $data ) == 'o-discount' ) {
				wp_dequeue_script( 'acf-timepicker' );
		}
	}

	public static function get_max_input_vars_php_ini() {
		$total_max_normal = ini_get( 'max_input_vars' );
		$msg              = wp_sprintf( esc_html__( "Your max input var is %1s$total_max_normal%2s but this page contains %1s{nb}%2s fields. You may experience a lost of data after saving. In order to fix this issue, please increase %1sthe max_input_vars%2s value in your php.ini file.", 'vpc' ), '<strong>', '</strong>' );
		?>
		  <script type="text/javascript">
			  var o_max_input_vars = <?php echo esc_attr( $total_max_normal ); ?>;
			  var o_max_input_msg = "<?php echo esc_attr( $msg ); ?>";
		  </script>
		<?php
	}

	/**
	 * Builds all the plugin menu and submenu
	 */
	public function add_wad_menu() {
		$parent_slug = 'edit.php?post_type=o-discount';
		add_submenu_page( $parent_slug, esc_html__( 'Products Lists', 'woo-advanced-discounts' ), esc_html__( 'Products Lists', 'woo-advanced-discounts' ), 'manage_product_terms', 'edit.php?post_type=o-list', false );
		// add_submenu_page($parent_slug, esc_html__('Settings', 'woo-advanced-discounts'), esc_html__('Settings', 'woo-advanced-discounts'), 'manage_product_terms', 'wad-manage-settings', array($this, 'get_wad_settings_page'));
		add_submenu_page( $parent_slug, esc_html__( 'Pro features', 'woo-advanced-discounts' ), esc_html__( 'Pro features', 'woo-advanced-discounts' ), 'manage_product_terms', 'wad-pro-features', array( $this, 'get_wad_pro_features_page' ) );
		add_submenu_page( $parent_slug, esc_html__( 'User Manual', 'woo-advanced-discounts' ), esc_html__( 'User Manual', 'woo-advanced-discounts' ), 'manage_product_terms', 'wad-user-manual', array( $this, 'redirect_to_user_manual' ) );
		add_submenu_page( $parent_slug, esc_html__( 'Submit a ticket', 'woo-advanced-discounts' ), esc_html__( 'Submit a ticket', 'woo-advanced-discounts' ), 'manage_product_terms', 'wad-submit-a-ticket', array( $this, 'redirect_to_support' ) );
	}


	/**
	 * Redirect to the documentation of the plugin.
	 *
	 * @return void
	 */
	public function redirect_to_user_manual() {
		wp_redirect( 'https://www.orionorigin.com/tutorials-guides/how-to-create-your-first-discount/?utm_source=WAD%20free&utm_medium=user%20manual%20submenu&utm_campaign=wordpress.org' );
		exit();
	}

	/**
	 * Redirect to the ticket support for send a issue.
	 *
	 * @return void
	 */
	public function redirect_to_support() {
		wp_redirect( 'https://www.orionorigin.com/contact/?utm_source=WAD%20free&utm_medium=get%25support%20submenu&utm_campaign=wordpress.org' );
		exit();
	}

	public function get_wad_settings_page() {
		$posted_data = filter_input_array(
			INPUT_POST,
			array(
				'wad-options' => array(
					'flags' => FILTER_REQUIRE_ARRAY,
				),
			)
		);
		if ( ( isset( $posted_data['wad-options'] ) && ! empty( $posted_data['wad-options'] ) ) ) {
			update_option( 'wad-options', $posted_data['wad-options'] );
		}
		wad_remove_transients();
		?>
		<div class="o-wrap cf">
			<h1><?php esc_html_e( 'Conditional Discounts for WooCommerce Settings', 'woo-advanced-discounts' ); ?></h1>
			<form method="POST" action="" class="mg-top">
				<div class="postbox" id="wad-options-container">
					<?php
					$begin = array(
						'type'  => 'sectionbegin',
						'id'    => 'wad-datasource-container',
						'table' => 'options',
					);
					/*
					$enable_cache = array(
						'title' => esc_html__('Cache discounts', 'woo-advanced-discounts'),
						'name' => 'wad-options[enable-cache]',
						'type' => 'select',
						'options' => array(0 => "No", 1 => "Yes"),
						'desc' => esc_html__('whether or not to store the discounts in the cache to increase the pages load speed. Cache is valid for 12hours', 'woo-advanced-discounts'),
						'default' => '',
					);*/

					$end      = array( 'type' => 'sectionend' );
					$settings = array(
						$begin,
						// $enable_cache,
						$end,
					);
					echo O_Utils::admin_fields( $settings );
					?>
				</div>
				<input type="submit" class="button button-primary button-large" value="<?php esc_html_e( 'Save', 'woo-advanced-discounts' ); ?>">
			</form>
		</div>
		<?php
		global $o_row_templates;
		?>
		<script>
			var o_rows_tpl =<?php echo json_encode( $o_row_templates ); ?>;
		</script>
		<?php
	}

	function get_wad_pro_features_page() {
		$messages = $this->get_pro_features_messages();

		?>
		<div class="wrap">
			<h1>Need more features? Let's go pro!</h1>
			<div id="wad-pro-features">
				<div class="o-wrap">
					<?php
					foreach ( $messages as $message_key => $message ) {
						?>
					<div class="col xl-1-3 wad-infox">
						<p>
						<h3><?php echo esc_attr( $message_key ); ?></h3>
						</p>
						<p>
							<?php echo esc_attr( ucfirst( $message ) ); ?>
						</p>

						<a href="https://www.orionorigin.com/product/conditional-discounts-for-woocommerce/?utm_source=Installed+free+plugin&utm_medium=Pro+features+page&utm_term=Term+name&utm_campaign=WAD&utm_medium=cpc&utm_term=<?php echo esc_attr( urlencode( $message_key ) ); ?>" class="button"  target="_blank">Click here to unlock</a></p>
					</div>
								<?php
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	function get_pro_features_messages() {
		$messages = array(
			esc_html__( 'Improved Speed', 'woo-advanced-discounts' ) => esc_html__( 'Do you feel the plugin is a bit slow? Upgrade to make it faster in order to handle up to thousand of products.', 'woo-advanced-discounts' ),
			esc_html__( 'Bulk discounts per category', 'woo-advanced-discounts' ) => esc_html__( 'Create a quantity based pricing per product category by setting the quantities intervals (minimum and maximum quantities) and apply a percentage or fixed amount discount off each product price.', 'woo-advanced-discounts' ),
			esc_html__( 'Bulk discounts per user role', 'woo-advanced-discounts' ) => esc_html__( 'Create a quantity based pricing per customer role by setting the quantities intervals (minimum and maximum quantities) and apply a percentage or fixed amount discount off each product price.', 'woo-advanced-discounts' ),
			esc_html__( 'First time order discounts', 'woo-advanced-discounts' ) => esc_html__( 'Increase your chances to convert a first time visitor to a customer by automatically applying a discount to his first order.', 'woo-advanced-discounts' ),
			esc_html__( 'N-th order discount', 'woo-advanced-discounts' ) => esc_html__( 'Reward and reinforce your customers loyalty by assigning a dynamic discount to those who purchased from your store a certain number of times.', 'woo-advanced-discounts' ),
			esc_html__( 'Discounts based on the customer email domain', 'woo-advanced-discounts' ) => esc_html__( 'Offer any type of discount to any customer who registers using an email address based on a specific domain name.', 'woo-advanced-discounts' ),
			esc_html__( 'Free gifts', 'woo-advanced-discounts' ) => esc_html__( 'create a "Buy one, get one for free" kind of discount', 'woo-advanced-discounts' ),
			esc_html__( 'Shipping Country', 'woo-advanced-discounts' ) => esc_html__( 'apply a discount based on the shipping country.', 'woo-advanced-discounts' ),
			esc_html__( 'Billing Country', 'woo-advanced-discounts' ) => esc_html__( 'apply a discount based on the billing country.', 'woo-advanced-discounts' ),
			esc_html__( 'Payment gateways', 'woo-advanced-discounts' ) => esc_html__( 'apply a discount if the customers checks out with a specific payment gateway.', 'woo-advanced-discounts' ),
			esc_html__( 'Discount on shipping fees', 'woo-advanced-discounts' ) => esc_html__( 'Apply discount on shipping fees', 'woo-advanced-discounts' ),
			esc_html__( 'Usage limit', 'woo-advanced-discounts' ) => esc_html__( 'limits the number of customers who can use a discount.', 'woo-advanced-discounts' ),
			esc_html__( 'Periodic discounts', 'woo-advanced-discounts' ) => esc_html__( 'automatically enable a discount periodically.', 'woo-advanced-discounts' ),
			esc_html__( 'Groups based discounts', 'woo-advanced-discounts' ) => esc_html__( 'apply a discount is the customer belong to a specific group.', 'woo-advanced-discounts' ),
			esc_html__( 'Newsletters based discounts', 'woo-advanced-discounts' ) => esc_html__( 'offer a discount if the customer subscribed to your newsletters.', 'woo-advanced-discounts' ),
			esc_html__( 'Taxes inclusion', 'woo-advanced-discounts' ) => esc_html__( 'apply discounts on subtotal with or without the taxes.', 'woo-advanced-discounts' ),
			esc_html__( 'Specific users discounts', 'woo-advanced-discounts' ) => esc_html__( 'apply discounts for specific(s) customer(s).', 'woo-advanced-discounts' ),
			esc_html__( 'Currency based discounts', 'woo-advanced-discounts' ) => esc_html__( 'apply discounts depending on the customer selected currency (useful for currency switchers).', 'woo-advanced-discounts' ),
			esc_html__( 'Previous purchases discounts', 'woo-advanced-discounts' ) => esc_html__( 'ability to define a discount based on previously purchased products.', 'woo-advanced-discounts' ),
			esc_html__( 'Coupons deactivation', 'woo-advanced-discounts' ) => esc_html__( 'ability to disable coupons when a dynamic discount is applied.', 'woo-advanced-discounts' ),
		);
		return $messages;
	}

	function get_ad_messages() {
		global $pagenow;
		$messages           = $this->get_pro_features_messages();
		$random_message_key = array_rand( $messages );
		$post_type          = filter_input( INPUT_GET, 'post_type' );
		$page               = filter_input( INPUT_GET, 'page' );
		if ( ( $pagenow == 'post-new.php' || $pagenow == 'post.php' || $post_type == 'o-discount' || $post_type == 'product' || $page == 'o-list' ) && $page != 'wad-pro-features' ) {
			echo wp_kses(
				'<div class="wad-info">
               <p><strong>' . $random_message_key . '</strong>: ' . $messages[ $random_message_key ] . ' <a href="https://www.orionorigin.com/product/conditional-discounts-for-woocommerce/?utm_source=Free%20Trial&utm_medium=cpc&utm_term=' . urlencode( $random_message_key ) . '&utm_campaign=WAD" class="button"  target="_blank">Click here to unlock</a></p>
            </div>',
				O_Utils::get_allowed_tags()
			);
		}

	}

	function get_review_suggestion_notice() {
		$dismiss_transient = get_transient( 'wad_review_submitted' );

		if ( get_transient( 'wad-hide-reviews' ) == 'hide' ) {
			return;
		}

		if ( $dismiss_transient != 'no' ) {
			?>
				<div class="wad-review update-nag notice notice-info" style="border-left: 4px solid rgb(0, 160, 210); ">

					<!-- <span><p class="wad-logo"></p></span> -->
					<span class="wad-notice-title">
						<span><img style="width:80px; vertical-align:middle;" src="<?php echo esc_attr( WAD_URL . 'admin/images/wad_80x80.png' ); ?>"> </span>
						<span><strong><?php esc_html_e( 'Conditional Discounts for WooCommerce', 'wad' ); ?></strong></span>
					</span>
					<p class="wad-notice-body">
					<?php
					esc_html_e(
						"Hello,
                        You've been using our discount plugin for a bit now. Do you mind please leaving us a review?
                        This means the WORLD to us and help us reach new users. ",
						'wad'
					);
					?>
												</p>
					<span>
					<button type="submit" class="button button-primary"><a id="submit-a-review" href="https://wordpress.org/support/plugin/woo-advanced-discounts/reviews/#new-post" target="_blank" style="text-decoration:none; color:white;"><?php esc_html_e( 'Submit a review', 'wad' ); ?></a></button>
					<button type="submit" class="button button-primary" style="background-color: unset;background: unset; border: unset;color: rgb(128, 128, 128);box-shadow: none; text-shadow: unset;"><a class="wad-dismiss-notice" style="text-decoration:none; color:grey;"><?php esc_html_e( 'Not now', 'wad' ); ?></a></button></button>
					</span>
				</div>
			<?php
		}

	}

	// Ignore function that gets ran at admin init to ensure any messages that were dismissed get marked
	public function admin_notice_ignore() {
		// If user clicks to ignore the notice, update the option to not show it again
		if ( ( filter_input( INPUT_GET, 'wad_admin_notice_temp_ignore' ) != '' ) && current_user_can( 'manage_product_terms' ) ) {
				update_option( 'wad_admin_notice_ignore', true );
				$query_str = remove_query_arg( 'wad_admin_notice_ignore' );
				wp_redirect( $query_str );
				exit;
		}
	}

	// Temp Ignore function that gets ran at admin init to ensure any messages that were temp dismissed get their start date changed
	public function admin_notice_temp_ignore() {

		// If user clicks to temp ignore the notice, update the option to change the start date - default interval of 14 days

		if ( ( filter_input( INPUT_GET, 'wad_admin_notice_temp_ignore' ) != '' ) && current_user_can( 'manage_product_terms' ) ) {
			$interval = filter_input( INPUT_GET, 'wad_int' ) ?? 14;
			set_transient( 'wad_notice_dismiss', true, MINUTE_IN_SECONDS * $interval * DAY_IN_SECONDS );
			$query_str = remove_query_arg( array( 'wad_admin_notice_temp_ignore', 'wad_int' ) );
			wp_redirect( $query_str );
			exit;
		}
	}

	/**
	 * Redirects the plugin to the about page after the activation
	 */
	function wad_redirect() {
		if ( get_option( 'wad_do_activation_redirect', false ) ) {
			delete_option( 'wad_do_activation_redirect' );
			wp_redirect( admin_url( 'edit.php?post_type=o-discount&page=wad-pro-features' ) );
		}
	}

	/**
	 * Checking if product list is define.
	 */
	function check_product_list() {
		$product_lists        = new WAD_Products_List( false );
		$product_lists_counts = $product_lists->get_all();
		global $post_type,$pagenow;
		$current_page = filter_input( INPUT_GET, 'page' );
		if ( 'o-discount' == $post_type || 'o-list' == $post_type || ( 'edit.php' == $pagenow || $current_page != 'wad-pro-features' ) ) {
			if ( isset( $product_lists_counts ) && empty( $product_lists_counts ) ) {
				  $url  = admin_url( 'post-new.php?post_type=o-list' );
				  $html = "<a href='" . $url . "'>here</a>";
				?>
						<div class="wad notice notice-error">
							<p>
					  <?php
						echo wp_sprintf( esc_html__( "You haven't created a products list. You need one in order to apply a discount on multiple products. You can create one %s .", 'woo-advanced-discounts' ), $html );
						?>
							</p>
						</div>
					<?php
			}
		}
	}

	/*
	 *
	 * Newsletter
	 */
	function wad_subscribe() {
		$email = filter_input( INPUT_POST, 'email' );

		if ( preg_match( '#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $email ) ) {
			$url      = 'https://orionorigin.com/service/osubscribe/v1/subscribe/?email=' . $email;
			$args     = array( 'timeout' => 120 );
			$response = wp_remote_get( $url, $args );

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				echo wp_kses_post( "Something went wrong: $error_message" );
				die();
			}
			if ( isset( $response['body'] ) ) {
				$answer = $response['body'];
				if ( $answer == 'true' ) {
					update_option( 'o-wad-subscribe', 'subscribed' );
					echo wp_kses_post( $answer );
				} else {
					echo wp_kses_post( $answer );
				}

				die();
			}
		} else {
			echo wp_kses_post( 'Please enter a valid email address' );
			die();
		}
	}


	public function wad_hide_notice() {
		set_transient( 'wad-hide-notice', 'hide', 2 * WEEK_IN_SECONDS );
		echo wp_kses_post( 'ok' );
			die();
	}

	/**
	 * Ajax function for hide the review notice.
	 *
	 * @return void
	 */
	public function hide_review() {
		set_transient( 'wad-hide-reviews', 'hide', 2 * WEEK_IN_SECONDS );
		echo wp_kses_post( 'ok' );
		wp_die();
	}

	/**
	 * Render the subscription notice.
	 *
	 * @return void
	 */
	function get_subscription_notice() {
		if ( ! get_option( 'o-wad-subscribe' ) && get_transient( 'wad-hide-notice' ) != 'hide' ) {
			?>
			<div id="subscription-notice" class="notice notice-info">

				<div >
					<img id="plug-logo" style="vertical-align:middle; height:50px; width: 50px"src="<?php echo wp_kses_post( WAD_URL ); ?>/admin/images/WAD-logo.svg">
					<span style="    display: table; margin-left: 55px; margin-top: -41px; z-index: -1;">
						<?php esc_html_e( '<strong>Conditional Discounts for WooCommerce</strong>: Sign up now to receive new releases notices and important bugs fixes directly into your inbox! ', 'wad' ); ?>
				</span>

				</div>

				<div id="plug-sucribe-form">
					<input type="email" id="o_user_email" name="usermail" placeholder="Your email here" value="<?php echo wp_kses_post( get_option( 'admin_email' ) ); ?>">
					<img id="wad-subscribe-loader" style="display:none;" src="<?php echo wp_kses_post( WAD_URL ); ?>/admin/images/loader.gif" >
					<button id="wad-subscribe" class="button button-primary"><?php esc_html_e( 'Subscribe', 'wad' ); ?></button>
					<a class="wad-dismiss-newsletters"><?php esc_html_e( 'Not now', 'wad' ); ?></a>
				</div>
			</div>
			<?php
		}

		?>
		<div id="subscription-success-notice" class="notice notice-info is-dismissible" style="display:none;">
				<img src="<?php echo wp_kses_post( WAD_URL ); ?>/admin/images/WAD-logo.svg">
				<div> <?php esc_html_e( '<strong>Woocommerce All Discounts</strong>: Thank you for subscribing! ', 'wad' ); ?></div>
		</div>
		<?php
	}

}
