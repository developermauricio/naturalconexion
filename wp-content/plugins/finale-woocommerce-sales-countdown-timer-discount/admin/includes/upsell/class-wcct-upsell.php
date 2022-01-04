<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WCCT_Upsell {

	protected static $instance = null;
	protected $name = '';
	protected $year = '';
	protected $year_full = '';
	protected $bf_time = '';
	protected $notice_time = array();
	protected $notice_displayed = false;
	protected $plugin_path = WCCT_PLUGIN_FILE;

	/**
	 * construct
	 */
	public function __construct() {
		$this->name      = 'Finale: WooCommerce Countdown Timer';
		$this->year      = date( 'y' );
		$this->year_full = date( 'Y' );
		if ( 1 === absint( date( 'n' ) ) ) {
			$this->year      = $this->year - 1;
			$this->year_full = $this->year_full - 1;
		}

		/** Black friday dates */
		$this->bf_time = 0;
		if ( '11' === date( 'n' ) ) {
			$z = new DateTime();
			$z->setTime( '0', '0', '0' );
			if ( '2020' === date( 'Y' ) ) {
				$z->setDate( '2020', '11', '27' );
				$this->bf_time = $z->getTimestamp();
			} elseif ( '2021' === date( 'Y' ) ) {
				$z->setDate( '2021', '11', '26' );
				$this->bf_time = $z->getTimestamp();
			} elseif ( '2022' === date( 'Y' ) ) {
				$z->setDate( '2022', '11', '25' );
				$this->bf_time = $z->getTimestamp();
			} elseif ( '2023' === date( 'Y' ) ) {
				$z->setDate( '2023', '11', '24' );
				$this->bf_time = $z->getTimestamp();
			} elseif ( '2024' === date( 'Y' ) ) {
				$z->setDate( '2024', '11', '29' );
				$this->bf_time = $z->getTimestamp();
			} elseif ( '2025' === date( 'Y' ) ) {
				$z->setDate( '2025', '11', '28' );
				$this->bf_time = $z->getTimestamp();
			} elseif ( '2026' === date( 'Y' ) ) {
				$z->setDate( '2026', '11', '27' );
				$this->bf_time = $z->getTimestamp();
			} elseif ( '2027' === date( 'Y' ) ) {
				$z->setDate( '2027', '11', '26' );
				$this->bf_time = $z->getTimestamp();
			} elseif ( '2028' === date( 'Y' ) ) {
				$z->setDate( '2028', '11', '24' );
				$this->bf_time = $z->getTimestamp();
			} elseif ( '2029' === date( 'Y' ) ) {
				$z->setDate( '2029', '11', '23' );
				$this->bf_time = $z->getTimestamp();
			} elseif ( '2030' === date( 'Y' ) ) {
				$z->setDate( '2030', '11', '29' );
				$this->bf_time = $z->getTimestamp();
			}
			$z = null;
		}

		$this->hooks();
		$this->set_notice_timings();
	}

	/**
	 * Getting class instance
	 * @return null|instance
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Initiate hooks
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'xl_notice_variable' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'notice_enqueue_scripts' ) );
		add_action( 'wp_ajax_finale_upsells_dismiss', array( $this, 'xl_dismiss_notice' ) );

		add_action( 'admin_notices', array( $this, 'xl_christmas_sale_notice' ), 10 );
		add_action( 'admin_notices', array( $this, 'xl_bfcm_sale_notice' ), 10 );
		add_action( 'admin_notices', array( $this, 'xl_pre_black_friday_sale_notice' ), 10 );
		add_action( 'admin_notices', array( $this, 'xl_halloween_sale_notice' ), 10 );

		add_action( 'admin_notices', array( $this, 'xl_upsells_notice_html_finale' ), 10 );
		add_action( 'admin_notices', array( $this, 'xl_upsells_notice_html_nextmove' ), 10 );
		add_action( 'admin_notices', array( $this, 'xl_upsells_notice_html_autonami' ), 10 );

		add_action( 'admin_notices', array( $this, 'xl_html_finale_review' ), 10 );

		add_action( 'admin_notices', array( $this, 'xl_upsells_notice_js' ), 20 );
	}

	/**
	 * Assigning plugin notice timings
	 * Always use 2 time period as 'no'
	 */
	public function set_notice_timings() {
		$finale_notice_time          = array(
			'0' => 3 * DAY_IN_SECONDS, // +3 days
			'1' => 3 * DAY_IN_SECONDS, // +3 days
		);
		$this->notice_time['finale'] = $finale_notice_time;

		$finale_review_notice_time          = array(
			'0' => 10 * DAY_IN_SECONDS, // +10 days
			'1' => 10 * DAY_IN_SECONDS, // +10 days
		);
		$this->notice_time['finale_review'] = $finale_review_notice_time;

		$nextmove_notice_time          = array(
			'0' => 15 * DAY_IN_SECONDS, // +15 days
			'1' => 3 * DAY_IN_SECONDS, // +3 days
		);
		$this->notice_time['nextmove'] = $nextmove_notice_time;

		$autonami_notice_time          = array(
			'0' => 7 * DAY_IN_SECONDS, // +7 days
			'1' => 3 * DAY_IN_SECONDS, // +3 days
		);
		$this->notice_time['autonami'] = $autonami_notice_time;

		$halloween_sale_notice_time                           = array(
			'0' => 0.05 * DAY_IN_SECONDS, // +1.2 hrs
			'1' => 1 * DAY_IN_SECONDS, // +1 day
		);
		$this->notice_time[ 'halloween_sale_' . $this->year ] = $halloween_sale_notice_time;

		$pre_bf_sale_notice_time                                = array(
			'0' => 0.05 * DAY_IN_SECONDS, // +1.2 hrs
			'1' => 1 * DAY_IN_SECONDS, // +1 day
		);
		$this->notice_time[ 'pre_black_friday_' . $this->year ] = $pre_bf_sale_notice_time;

		$bfcm_sale_notice_time                      = array(
			'0' => 0.05 * DAY_IN_SECONDS, // +1.2 hrs
			'1' => 1 * DAY_IN_SECONDS, // +1 day
		);
		$this->notice_time[ 'bfcm_' . $this->year ] = $bfcm_sale_notice_time;

		$christmas_sale_notice_time                      = array(
			'0' => 0.05 * DAY_IN_SECONDS, // +1.2 hrs
			'1' => 1 * DAY_IN_SECONDS, // +1 day
		);
		$this->notice_time[ 'christmas_' . $this->year ] = $christmas_sale_notice_time;
	}

	/**
	 * Assign notice variable to false if not set
	 * @global boolean $xl_upsells_notice_active
	 */
	public function xl_notice_variable() {
		global $xl_upsells_notice_active;
		if ( '' == $xl_upsells_notice_active ) {
			$xl_upsells_notice_active = false;
		}
	}

	/**
	 * Enqueue assets
	 */
	public function notice_enqueue_scripts() {
		wp_enqueue_style( 'xl-notices-css', plugin_dir_url( $this->plugin_path ) . 'admin/assets/css/wcct-xl-notice.css', array(), XLWCCT_VERSION );
		wp_enqueue_script( 'wp-util' );
	}

	/**
	 * Upsell notice html - NextMove
	 */
	public function xl_upsells_notice_html_nextmove() {
		global $xl_upsells_notice_active;
		$short_slug = 'nextmove';
		if ( true === $xl_upsells_notice_active ) {
			return;
		}
		if ( true === $this->plugin_already_installed( $short_slug ) ) {
			return;
		}
		if ( true === $this->hide_notice() ) {
			return;
		}
		if ( ! isset( $this->notice_time[ $short_slug ] ) ) {
			return;
		}
		$this->main_plugin_activation( $short_slug );
		if ( true === $this->notice_dismissed( $short_slug ) ) {
			return;
		}
		$this->notice_displayed = true;
		echo $this->nextmove_notice_html();
		$xl_upsells_notice_active = true;
	}

	/**
	 * Upsell notice html - Finale
	 * @return type
	 * @global boolean $xl_upsells_notice_active
	 */
	public function xl_upsells_notice_html_finale() {
		global $xl_upsells_notice_active;
		$short_slug = 'finale';
		if ( true === $xl_upsells_notice_active ) {
			return;
		}
		if ( true === $this->plugin_already_installed( $short_slug ) ) {
			return;
			/** As this is a lite plugin */
		}
		if ( true === $this->hide_notice() ) {
			return;
		}
		if ( ! isset( $this->notice_time[ $short_slug ] ) ) {
			return;
		}
		$this->main_plugin_activation( $short_slug );
		if ( true === $this->notice_dismissed( $short_slug ) ) {
			return;
		}
		$this->notice_displayed = true;
		echo $this->finale_notice_html();
		$xl_upsells_notice_active = true;
	}

	/**
	 * Upsell notice html - Autonami
	 * @return type
	 * @global boolean $xl_upsells_notice_active
	 */
	public function xl_upsells_notice_html_autonami() {
		global $xl_upsells_notice_active;
		$short_slug = 'autonami';
		if ( true === $xl_upsells_notice_active ) {
			return;
		}
		if ( true === $this->plugin_already_installed( $short_slug ) ) {
			return;
		}
		if ( true === $this->hide_notice() ) {
			return;
		}
		if ( ! isset( $this->notice_time[ $short_slug ] ) ) {
			return;
		}
		$this->main_plugin_activation( $short_slug );
		if ( true === $this->notice_dismissed( $short_slug ) ) {
			return;
		}
		$this->notice_displayed = true;
		echo $this->autonami_notice_html();
		$xl_upsells_notice_active = true;
	}

	/**
	 * Upsell notice html - Finale
	 */
	public function xl_html_finale_review() {
		global $xl_upsells_notice_active;
		$short_slug = 'finale_review';
		if ( true === $xl_upsells_notice_active ) {
			return;
		}
		if ( true === $this->hide_notice() ) {
			return;
		}
		if ( ! isset( $this->notice_time[ $short_slug ] ) ) {
			return;
		}

		$wcct_posts_sample_ids = get_option( 'wcct_posts_sample_ids' );

		if ( empty( $wcct_posts_sample_ids ) ) {
			return;
		}

		$wcct_posts_sample_ids = end( $wcct_posts_sample_ids );

		if ( empty( $wcct_posts_sample_ids ) ) {
			return;
		}
		$wcct_posts_created_date = strtotime( get_the_date( '', $wcct_posts_sample_ids ) );

		if ( ( $wcct_posts_created_date + ( DAY_IN_SECONDS * 7 ) ) > time() ) {
			return;
		}

		$this->main_plugin_activation( $short_slug );
		if ( true === $this->notice_dismissed( $short_slug ) ) {
			return;
		}

		$this->notice_displayed = true;
		echo $this->finale_review_notice_html();
		$xl_upsells_notice_active = true;
	}

	/**
	 * Halloween Sale notice html
	 * @throws Exception
	 */
	public function xl_halloween_sale_notice() {
		global $xl_upsells_notice_active;
		$short_slug = 'halloween_sale_' . $this->year;
		if ( true === $xl_upsells_notice_active ) {
			return;
		}
		if ( true === $this->hide_notice() ) {
			return;
		}
		if ( true === $this->valid_time_duration( $short_slug ) ) {
			return;
		}
		$this->main_plugin_activation( $short_slug );
		if ( true === $this->notice_dismissed( $short_slug ) ) {
			return;
		}
		$this->notice_displayed = true;
		echo $this->halloween_sale_notice_html();
		$xl_upsells_notice_active = true;
	}

	/**
	 * Pre Black Friday Sale notice html
	 * @throws Exception
	 */
	public function xl_pre_black_friday_sale_notice() {
		global $xl_upsells_notice_active;
		$short_slug = 'pre_black_friday_' . $this->year;
		if ( true === $xl_upsells_notice_active ) {
			return;
		}
		if ( true === $this->hide_notice() ) {
			return;
		}
		if ( true === $this->valid_time_duration( $short_slug ) ) {
			return;
		}
		$this->main_plugin_activation( $short_slug );
		if ( true === $this->notice_dismissed( $short_slug ) ) {
			return;
		}
		$this->notice_displayed = true;
		echo $this->pre_black_friday_notice_html();
		$xl_upsells_notice_active = true;
	}

	/**
	 * Black Friday Cyber Monday Sale notice html
	 * @throws Exception
	 */
	public function xl_bfcm_sale_notice() {
		global $xl_upsells_notice_active;
		$short_slug = 'bfcm_' . $this->year;
		if ( true === $xl_upsells_notice_active ) {
			return;
		}
		if ( true === $this->hide_notice() ) {
			return;
		}
		if ( true === $this->valid_time_duration( $short_slug ) ) {
			return;
		}
		$this->main_plugin_activation( $short_slug );
		if ( true === $this->notice_dismissed( $short_slug ) ) {
			return;
		}
		$this->notice_displayed = true;
		echo $this->bfcm_notice_html();
		$xl_upsells_notice_active = true;
	}

	/**
	 * Christmas New Year Sale notice html
	 * @throws Exception
	 */
	public function xl_christmas_sale_notice() {
		global $xl_upsells_notice_active;
		if ( true === $xl_upsells_notice_active ) {
			return;
		}
		if ( true === $this->hide_notice() ) {
			return;
		}
		if ( true === $this->valid_time_duration( 'christmas_' . $this->year ) ) {
			return;
		}
		$this->main_plugin_activation( 'christmas_' . $this->year );
		if ( true === $this->notice_dismissed( 'christmas_' . $this->year ) ) {
			return;
		}
		$this->notice_displayed = true;
		echo $this->christmas_notice_html();
		$xl_upsells_notice_active = true;
	}

	/**
	 * Checking if plugin already installed
	 * @return boolean
	 */
	public function plugin_already_installed( $plugin_short_name ) {
		if ( 'nextmove' == $plugin_short_name ) {
			if ( class_exists( 'XLWCTY_Core' ) ) {
				return true;
			}
		} elseif ( 'finale' == $plugin_short_name ) {
			if ( class_exists( 'WCCT_Core' ) ) {
				return true;
			}
		} elseif ( 'autonami' == $plugin_short_name ) {
			if ( class_exists( 'BWFAN_Core' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Hide upsell notice on defined pages.
	 * @return boolean
	 */
	public function hide_notice() {
		$screen     = get_current_screen();
		$base_array = array( 'plugin-install', 'update-core', 'post', 'export', 'import', 'upload', 'media', 'edit', 'edit-tags' );
		$post_type  = 'wcct_countdown';
		if ( is_object( $screen ) && in_array( $screen->base, $base_array ) ) {
			if ( 'post' == $screen->base && $post_type == $screen->post_type ) {
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * First time assigning display timings for plugin upsell
	 *
	 * @param type $plugin_short_name
	 */
	public function main_plugin_activation( $plugin_short_name ) {
		$notice_displayed_count = get_option( $plugin_short_name . '_upsell_displayed', '0' );
		if ( '0' == $notice_displayed_count ) {
			if ( isset( $this->notice_time[ $plugin_short_name ][ $notice_displayed_count ] ) && '' != $this->notice_time[ $plugin_short_name ][ $notice_displayed_count ] ) {
				$this->plugin_upsell_set_values( (int) $this->notice_time[ $plugin_short_name ][ $notice_displayed_count ], $plugin_short_name, ( (int) $notice_displayed_count + 1 ) );
			} else {
				// set expiration for an year
				$this->plugin_upsell_set_values( YEAR_IN_SECONDS, $plugin_short_name );
			}
		}
	}

	/**
	 * Setting values in transient or option for upsell plugin
	 *
	 * @param type $expire_time
	 * @param type $plugin_short_name
	 * @param type $notice_displayed_count
	 */
	public function plugin_upsell_set_values( $expire_time, $plugin_short_name, $notice_displayed_count = 100 ) {
		$this->set_xl_transient( $plugin_short_name . '_upsell_hold_time', 'yes', $expire_time );
		update_option( $plugin_short_name . '_upsell_displayed', $notice_displayed_count, false );
	}

	/**
	 * Check if the notice is dismissed
	 *
	 * @param type $plugin_short_name
	 *
	 * @return boolean
	 */
	public function notice_dismissed( $plugin_short_name ) {
		$upsell_dismissed_forever = get_option( $plugin_short_name . '_upsell_displayed', false );
		if ( '100' == $upsell_dismissed_forever ) {
			return true;
		}
		$notice_display = $this->get_xl_transient( $plugin_short_name . '_upsell_hold_time' );
		if ( false === $notice_display ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the notice display duration is correct
	 *
	 * @param $plugin_short_name
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function valid_time_duration( $plugin_short_name ) {
		$current_date_obj = new DateTime( 'now', new DateTimeZone( 'America/New_York' ) );
		if ( 'halloween_sale_' . $this->year == $plugin_short_name ) {
			$from = strtotime( $this->year_full . '-10-28' );
			$to   = $from + ( 5 * DAY_IN_SECONDS );
			if ( $current_date_obj->getTimestamp() < $from || $current_date_obj->getTimestamp() > $to ) {
				return true;
			}
		} elseif ( 'pre_black_friday_' . $this->year == $plugin_short_name ) {
			if ( $this->bf_time === 0 ) {
				return true;
			}
			$from = $this->bf_time - ( 8 * DAY_IN_SECONDS );
			$to   = $this->bf_time;
			if ( $current_date_obj->getTimestamp() < $from || $current_date_obj->getTimestamp() > $to ) {
				return true;
			}
		} elseif ( 'bfcm_' . $this->year == $plugin_short_name ) {
			if ( $this->bf_time === 0 ) {
				return true;
			}
			$from = $this->bf_time;
			$to   = $this->bf_time + ( 8 * DAY_IN_SECONDS );
			if ( $current_date_obj->getTimestamp() < $from || $current_date_obj->getTimestamp() > $to ) {
				return true;
			}
		} elseif ( 'christmas_' . $this->year == $plugin_short_name ) {
			$from = strtotime( $this->year_full . '-12-21' );
			$to   = $from + ( 12 * DAY_IN_SECONDS );
			if ( $current_date_obj->getTimestamp() < $from || $current_date_obj->getTimestamp() > $to ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Dismiss the notice via Ajax
	 * @return void
	 */
	public function xl_dismiss_notice() {
		if ( isset( $_POST['notice_displayed_count'] ) && ( '' != $_POST['notice_displayed_count'] ) ) {
			$notice_displayed_count = $_POST['notice_displayed_count'];
		} else {
			$notice_displayed_count = '100';
		}
		$this->dismiss_notice( $_POST['plugin'], $notice_displayed_count );
		wp_send_json_success();
	}

	/**
	 * Dismiss notice
	 *
	 * @param type $plugin_short_name
	 * @param type $notice_displayed_count
	 *
	 * @return void
	 */
	public function dismiss_notice( $plugin_short_name, $notice_displayed_count = '' ) {
		if ( empty( $notice_displayed_count ) ) {
			$notice_displayed_count = get_option( $plugin_short_name . '_upsell_displayed', '0' );
		}
		if ( '+1' == $notice_displayed_count ) {
			$notice_time = $this->notice_time[ $plugin_short_name ];
			end( $notice_time );
			$key = key( $notice_time );
			if ( isset( $notice_time[ $key ] ) && ( '' != $notice_time[ $key ] ) ) {
				$this->plugin_upsell_set_values( (int) $notice_time[ $key ], $plugin_short_name, ( (int) $key ) );

				return;
			}
		}
		if ( isset( $this->notice_time[ $plugin_short_name ][ $notice_displayed_count ] ) && ( '' != $this->notice_time[ $plugin_short_name ][ $notice_displayed_count ] ) ) {
			$this->plugin_upsell_set_values( (int) $this->notice_time[ $plugin_short_name ][ $notice_displayed_count ], $plugin_short_name, ( (int) $notice_displayed_count + 1 ) );
		} else {
			// set expiration for an year
			$this->plugin_upsell_set_values( YEAR_IN_SECONDS, $plugin_short_name );
		}
	}

	/**
	 * Upsell notice js
	 * common per plugin
	 */
	public function xl_upsells_notice_js() {
		if ( true === $this->notice_displayed ) {
			ob_start();
			?>
            <script type="text/javascript">
                (function ($) {
                    var noticeWrap = $('#xl_notice_type_3');
                    var pluginShortSlug = noticeWrap.attr("data-plugin");
                    var pluginSlug = noticeWrap.attr("data-plugin-slug");
                    $('body').on('click', '.xl-notice-dismiss', function (e) {
                        e.preventDefault();
                        var $this = $(this);
                        var xlDisplayedMode = $this.attr("data-mode");
                        if (xlDisplayedMode == 'dismiss') {
                            xlDisplayedCount = '100';
                        } else if (xlDisplayedMode == 'later') {
                            xlDisplayedCount = '+1';
                        }
                        noticeWrap = $this.parents('#xl_notice_type_3');
                        pluginShortSlug = noticeWrap.attr("data-plugin");
                        pluginSlug = noticeWrap.attr("data-plugin-slug");
                        wp.ajax.send('finale_upsells_dismiss', {
                            data: {
                                plugin: pluginShortSlug,
                                notice_displayed_count: xlDisplayedCount,
                            },
                        });
                        $this.closest('.updated').slideUp('fast', function () {
                            $this.remove();
                        });
                    });
                    $(document).on('wp-plugin-install-success', function (e, args) {
                        if (args.slug == pluginSlug) {
                            wp.ajax.send('finale_upsells_dismiss', {
                                data: {
                                    plugin: pluginShortSlug,
                                    notice_displayed_count: '100',
                                },
                            });
                        }
                    });
                })(jQuery);
            </script>
			<?php
			echo ob_get_clean();
		}
	}

	protected function external_template( $notice_slug, $plugin_name, $plugin_url, $heading, $sub_heading, $image, $button_texts = [] ) {
		$button_texts = ( ! is_array( $button_texts ) ) ? [] : $button_texts;
		if ( ! isset( $button_texts['main'] ) || empty( $button_texts['main'] ) ) {
			$button_texts['main'] = 'Explore this Amazing Offer';
		}
		if ( ! isset( $button_texts['later'] ) || empty( $button_texts['later'] ) ) {
			$button_texts['later'] = 'May be later';
		}
		if ( ! isset( $button_texts['no_thx'] ) || empty( $button_texts['no_thx'] ) ) {
			$button_texts['no_thx'] = 'No, thanks';
		}
		?>
        <div class="updated" id="xl_notice_type_3" data-offer="yes" data-plugin="<?php echo $notice_slug ?>">
            <div class="xl_upsell_area">
                <div class="upsell_left_abs">
                    <img width="70" src="<?php echo $image ?>" alt="<?php echo $plugin_name ?>"/>
                </div>
                <div class="upsell_main_abs">
                    <h3><?php echo $heading ?></h3>
                    <p><?php echo $sub_heading ?></p>
                </div>
                <div class="upsell_right_abs">
                    <div id="plugin-filter" class="upsell_xl_plugin_btn">
                        <a class="button-primary" href="<?php echo $plugin_url; ?>" data-name="<?php echo $plugin_name ?>" target="_blank"><?php echo $button_texts['main'] ?></a>
                        <span class="dashicons dashicons-calendar"></span>
                        <a class="xl-notice-dismiss" data-mode="later" href="javascript:void(0)"><?php echo $button_texts['later'] ?></a>
                        <span class="dashicons dashicons-hidden"></span>
                        <a class="xl-notice-dismiss" data-mode="dismiss" title="Dismiss forever" href="javascript:void(0)"><?php echo $button_texts['no_thx'] ?></a></p>
                    </div>
                </div>
            </div>
            <span class="dashicons dashicons-megaphone"></span>
        </div>
		<?php
	}

	protected function repo_template( $plugin_slug, $plugin_short_slug, $plugin_name, $plugin_url, $heading, $sub_heading, $image ) {
		?>
        <div class="updated" id="xl_notice_type_3" data-plugin="<?php echo $plugin_short_slug ?>" data-plugin-slug="<?php echo $plugin_slug; ?>">
            <div class="xl_upsell_area">
                <div class="upsell_left_abs">
                    <img src="<?php echo $image; ?>" alt="<?php echo $plugin_name ?>">
                </div>
                <div class="upsell_main_abs">
                    <h3><?php echo $heading ?></h3>
                    <p><?php echo $sub_heading ?></p>
                </div>
                <div class="upsell_right_abs">
                    <div id="plugin-filter" class="upsell_xl_plugin_btn plugin-card plugin-card-<?php echo $plugin_slug; ?>">
                        <a class="button-primary install-now button" data-slug="<?php echo $plugin_slug; ?>" href="<?php echo $plugin_url; ?>" aria-label="Install <?php echo $plugin_name ?>" data-name="Install <?php echo $plugin_name ?>">Try
                            for Free</a>
                        <span class="dashicons dashicons-calendar"></span>
                        <a class="xl-notice-dismiss" data-mode="later" href="javascript:void(0)">May be later</a>
                        <span class="dashicons dashicons-hidden"></span>
                        <a class="xl-notice-dismiss" data-mode="dismiss" title="Dismiss forever" href="javascript:void(0)">No, thanks</a>
                    </div>
                </div>
            </div>
            <span class="dashicons dashicons-megaphone"></span>
        </div>
		<?php
	}

	/**
	 * NextMove upsell notice html
	 * @return type
	 */
	protected function nextmove_notice_html() {
		$plugin_slug       = 'woo-thank-you-page-nextmove-lite';
		$plugin_short_slug = 'nextmove';
		$plugin_name       = 'WooCommerce Thank You Page – NextMove Lite';
		$plugin_url        = wp_nonce_url( add_query_arg( array(
			'action' => 'install-plugin',
			'plugin' => $plugin_slug,
			'from'   => 'import',
		), self_admin_url( 'update.php' ) ), 'install-plugin_' . $plugin_slug );
		$heading           = 'Say good bye to templated & lousy Thank You pages. Hack your growth with NextMove.';
		$sub_heading       = 'Use NextMove to create profit-pulling Thank You pages with plug & play components and watch your repeats orders explode.';
		$image             = plugin_dir_url( $this->plugin_path ) . 'admin/assets/img/nextmove.png';

		ob_start();
		$this->repo_template( $plugin_slug, $plugin_short_slug, $plugin_name, $plugin_url, $heading, $sub_heading, $image );

		return ob_get_clean();
	}

	/**
	 * Finale upsell notice html
	 * @return type
	 */
	protected function finale_notice_html() {
		$plugin_name = $this->name;
		$plugin_url  = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'notice',
			'utm_campaign' => 'finale',
			'utm_term'     => 'more-info',
		), 'https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/' );
		$heading     = "Set up profit-pulling promotions in your WooCommerce Store this season.";
		$sub_heading = "Finale helps store owners run seasonal offers, flash sales, deals of the day & festive offers to boost conversions.";
		$image       = plugin_dir_url( $this->plugin_path ) . 'admin/assets/img/finale.png';

		$notice_slug  = 'finale';
		$button_texts = [ 'main' => 'Get More Info' ];
		ob_start();
		$this->external_template( $notice_slug, $plugin_name, $plugin_url, $heading, $sub_heading, $image, $button_texts );

		return ob_get_clean();
	}

	/**
	 * Autonami upsell notice html
	 * @return type
	 */
	protected function autonami_notice_html() {
		$plugin_slug       = 'wp-marketing-automations';
		$plugin_short_slug = 'autonami';
		$plugin_name       = 'Autonami Marketing Automations For WordPress';
		$plugin_url        = wp_nonce_url( add_query_arg( array(
			'action' => 'install-plugin',
			'plugin' => $plugin_slug,
			'from'   => 'import',
		), self_admin_url( 'update.php' ) ), 'install-plugin_' . $plugin_slug );
		$heading           = 'Struggling with abandoned carts? Deploy the smart automation engine, Autonami for Free.';
		$sub_heading       = 'Autonami live captures emails on the checkout page, allows you to track abandoned and recovered carts through an intuitive dashboard, set delays in your emails, and more. The best part? You can segment your emails based on cart total, items in cart, coupons used & other such rules. No other cart recovery plugin comes close. Setup in under 20 secs to recover the lost revenue.';
		$image             = plugin_dir_url( $this->plugin_path ) . 'admin/assets/img/autonami.png';

		ob_start();
		$this->repo_template( $plugin_slug, $plugin_short_slug, $plugin_name, $plugin_url, $heading, $sub_heading, $image );

		return ob_get_clean();
	}

	/**
	 * Finale upsell notice html
	 * @return type
	 */
	protected function finale_review_notice_html() {
		$plugin_name = $this->name;
		$plugin_url  = 'https://wordpress.org/support/plugin/finale-woocommerce-sales-countdown-timer-discount/reviews/?filter=5#new-post';
		$heading     = "Hey, I noticed you created campaigns with Finale - that's awesome!";
		$sub_heading = "Could you please do us a BIG favor and give a 5-star rating on WordPress to help us spread the word and boost our motivation?";
		$image       = plugin_dir_url( $this->plugin_path ) . 'admin/assets/img/finale.png';

		$notice_slug  = 'finale_review';
		$button_texts = [ 'main' => 'Ok, you deserve it', 'later' => 'Nope, maybe later', 'no_thx' => 'I already did' ];

		ob_start();
		$this->external_template( $notice_slug, $plugin_name, $plugin_url, $heading, $sub_heading, $image, $button_texts );

		return ob_get_clean();
	}

	/**
	 * Halloween Sale notice html
	 * @return type
	 */
	protected function halloween_sale_notice_html() {
		$plugin_name = $this->name;
		$plugin_url  = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'notice',
			'utm_campaign' => 'halloween-' . $this->year,
			'utm_term'     => 'sale',
		), 'https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/' );
		$heading     = "Upgrade to FINALE PRO and Save 20%! Use coupon 'XLHALLOWEEN'";
		$sub_heading = "This <em>one-time</em> spooky deal ends on <em>November 2nd</em> 12 AM EST. 'Act Fast!'";
		$image       = plugin_dir_url( $this->plugin_path ) . 'admin/assets/img/halloween.jpg';

		$notice_slug = 'halloween_sale_' . $this->year;

		ob_start();
		$this->external_template( $notice_slug, $plugin_name, $plugin_url, $heading, $sub_heading, $image );

		return ob_get_clean();
	}

	/**
	 * Pre Black Friday Sale notice html
	 * @return false|string
	 * @throws Exception
	 */
	protected function pre_black_friday_notice_html() {
		$plugin_name = $this->name;
		$plugin_link = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'notice',
			'utm_campaign' => 'pre-black-friday-' . $this->year,
			'utm_term'     => 'sale',
		), 'https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/' );

		$day = new DateTime();
		$day->setTimestamp( $this->bf_time );

		$heading     = 'Prepare your store for Black Friday Sale. Upgrade to FINALE PRO and Save 20%!';
		$sub_heading = "Use coupon 'XLPREBFCM'. Deal expires on <em>{$day->format('F jS')}</em> 12 AM EST. 'Act Fast!'";
		$image       = plugin_dir_url( $this->plugin_path ) . 'admin/assets/img/black-friday.jpg';

		$notice_slug = 'pre_black_friday_' . $this->year;

		ob_start();
		$this->external_template( $notice_slug, $plugin_name, $plugin_link, $heading, $sub_heading, $image );

		return ob_get_clean();
	}

	/**
	 * Black Friday Cyber Monday Sale notice html
	 * @return false|string
	 * @throws Exception
	 */
	protected function bfcm_notice_html() {
		$plugin_name = $this->name;
		$plugin_link = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'notice',
			'utm_campaign' => 'bfcm_' . $this->year,
			'utm_term'     => 'sale',
		), 'https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/' );

		$to  = $this->bf_time + ( 8 * DAY_IN_SECONDS );
		$day = new DateTime();
		$day->setTimestamp( $to );

		$heading     = "Upgrade to FINALE PRO and Save 30%! Use coupon 'XLBFCM'";
		$sub_heading = "Get a super high 30% off on our plugins. Act fast! Deal expires on <em>{$day->format('F jS')}</em> 12 AM EST.";
		$image       = plugin_dir_url( $this->plugin_path ) . 'admin/assets/img/black-friday.jpg';

		$notice_slug = 'bfcm_' . $this->year;

		ob_start();
		$this->external_template( $notice_slug, $plugin_name, $plugin_link, $heading, $sub_heading, $image );

		return ob_get_clean();
	}

	/**
	 * Christmas New Year Sale notice html
	 * @return type
	 */
	protected function christmas_notice_html() {
		$plugin_name = $this->name;
		$plugin_link = add_query_arg( array(
			'utm_source'   => 'finale-lite',
			'utm_medium'   => 'notice',
			'utm_campaign' => 'christmas-' . $this->year,
			'utm_term'     => 'sale',
		), 'https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/' );
		$heading     = "Upgrade to FINALE PRO and Save 25%! Use coupon 'XLCHRISTMAS'";
		$sub_heading = "Get a super high 25% off on our plugins. Act fast! Deal expires on <em>January 2nd</em> 12 AM EST.";
		$image       = plugin_dir_url( $this->plugin_path ) . 'admin/assets/img/christmas.jpg';

		$notice_slug = 'christmas_' . $this->year;

		ob_start();
		$this->external_template( $notice_slug, $plugin_name, $plugin_link, $heading, $sub_heading, $image );

		return ob_get_clean();
	}


	/**
	 * Set custom transient as native transient sometimes don't save when cache plugins active
	 *
	 * @param type $key
	 * @param type $value
	 * @param type $expiration
	 */
	public function set_xl_transient( $key, $value, $expiration ) {
		$array = array(
			'time'  => time() + (int) $expiration,
			'value' => $value,
		);
		update_option( '_xl_transient_' . $key, $array, false );
	}

	/**
	 * get custom transient value
	 *
	 * @param type $key
	 *
	 * @return boolean
	 */
	public function get_xl_transient( $key ) {
		$data = get_option( '_xl_transient_' . $key, false );
		if ( false === $data ) {
			return false;
		}
		$current_time = time();
		if ( is_array( $data ) && isset( $data['time'] ) ) {
			if ( $current_time > (int) $data['time'] ) {
				delete_option( '_xl_transient_' . $key );

				return false;
			} else {
				return $data['value'];
			}
		}

		return false;
	}
}

WCCT_Upsell::get_instance();
