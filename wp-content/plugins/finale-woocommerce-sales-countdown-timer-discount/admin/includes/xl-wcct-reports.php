<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class XL_WCCT_Reports
 * This class controls all the actions related to reporting for any attribute of WC environment say, products , order etc.
 * @since 1.1.0
 */
class XL_WCCT_Reports {

	protected static $instance = null;

	protected $order_meta_key = '_wcct_running_camps_';
	public $order_id = '';

	/**
	 * XL_WCCT_Reports constructor.
	 */
	public function __construct() {

	}


	/**
	 * Return an instance of this class.
	 * @since     1.0.0
	 * @return    XL_WCCT_Reports    A single instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * handle metabox output for the order
	 * get campaigns for the order and generates markup
	 *
	 * @return string Markup
	 */
	public function order_running_campaign_view() {

		$order_id = $this->order_id;

		if ( ! $order_id ) {
			return ( 'Unable to get Running campaigns for this order.' );
		}

		//getting campaigns from meta
		$get_raw_campaigns = $this->get_campaigns_from_order( $order_id );

		if ( empty( $get_raw_campaigns ) ) {
			return ( 'No campaigns found' );

		}

		//getting unique campaigns from the found ones
		$get_unique = $this->get_unique_campaigns( $get_raw_campaigns );

		//fetching data from found campaigns
		//validating the found campaigns
		$get_data = $this->get_campaigns_data( $get_unique );

		if ( empty( $get_data ) ) {
			return ( 'No campaigns found' );

		}

		//generating html markup
		return $this->generate_markup( 'order_mb', $get_data );
	}


	/**
	 * Fetch campaigns info from order meta
	 *
	 * @param integer $order_id
	 *
	 * @return bool|array array of campaigns on success , false otherwise
	 */
	protected function get_campaigns_from_order( $order_id ) {

		$campaigns = get_post_meta( $order_id, $this->order_meta_key, true );

		if ( $campaigns && ! empty( $campaigns ) ) {
			return $campaigns;
		}

		return false;

	}


	/**
	 * Iterate over found campaigns and get unique campaigns
	 *
	 * @param array $campaigns
	 *
	 * @return array filtered array pf campaigns
	 */
	protected function get_unique_campaigns( $campaigns ) {

		$collected_campaigns = array();
		if ( ! empty( $campaigns ) ) {

			foreach ( $campaigns as $items ) {

				$collected_campaigns = array_merge( $items['campaigns']['running'], $collected_campaigns );
			}
		}

		return array_unique( $collected_campaigns );
	}

	/**
	 * Fetch campaign data and create data array
	 *
	 * @param array $campaigns
	 *
	 * @return array
	 */
	protected function get_campaigns_data( $campaigns ) {

		$campaigns_data = array();
		if ( ! empty( $campaigns ) ) {

			foreach ( $campaigns as $campaign ) {

				$get_campaign = get_post( $campaign );

				if ( $get_campaign && $campaign == $get_campaign->ID ) {
					$campaigns_data[ $campaign ] = WCCT_Common::get_the_title( $campaign );
				}
			}
		}

		return $campaigns_data;
	}

	/**
	 * Generates HTML markup/view for the selected view type
	 *
	 * @param string $view_type unique identifier to let function know what to render
	 * @param array $data helping data
	 *
	 * @return string
	 */
	protected function generate_markup( $view_type, $data ) {

		ob_start();
		if ( $view_type === 'order_mb' ) {

			?>
            <p class="post-attributes-label-wrapper" style="margin-top: 0px;">
                <label class="post-attributes-label">
					<?php echo __( 'Following campaigns were running during this order.', 'finale-woocommerce-sales-countdown-timer-discount' ); ?>
                </label>
            </p>

            <ul style="list-style-type: disc;padding-left: 17px;">
				<?php
				foreach ( $data as $campaign_key => $campaign_info ) {
					printf( '<li>%s #<a href="%s">%s</a></li>', $campaign_info, WCCT_Common::get_edit_post_link( $campaign_key ), $campaign_key );
				}
				?>
            </ul>
			<?php

		}

		return ob_get_clean();
	}


}
