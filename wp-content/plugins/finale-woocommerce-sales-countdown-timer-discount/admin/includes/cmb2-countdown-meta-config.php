<?php
defined( 'ABSPATH' ) || exit;

$doc_link = 'https://xlplugins.com/docs/finale-woocommerce-sales-countdown-timer-scheduler-documentation';
// one time campaign doc text
$onetime_content  = __( 'One Time option allows you to run single campaign between two fixed dates.', 'finale-woocommerce-sales-countdown-timer-discount' );
$onetime_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$onetime_content  .= __( 'Need Help with setting up One-Time campaign?', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' ';
$onetime_doc_link = add_query_arg( array(
	'utm_source'   => 'finale-lite',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'one-time-campaign',
), $doc_link . '/schedule/' );
$onetime_content  .= '<a href="' . $onetime_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>';

// discount doc text
$discount_content  = __( 'Enable this to set up sale on your products for the campaign duration.', 'finale-woocommerce-sales-countdown-timer-discount' );
$discount_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$discount_content  .= __( 'Need Help with setting up Discounts?', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' ';
$discount_doc_link = add_query_arg( array(
	'utm_source'   => 'finale-lite',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'discounts',
), $doc_link . '/discount/' );
$discount_content  .= '<a href="' . $discount_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>';

// inventory doc text
$inventory_content  = __( 'Enable this to define units of item to be sold during campaign.', 'finale-woocommerce-sales-countdown-timer-discount' );
$inventory_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$inventory_content  .= __( 'Need Help with setting up Inventory?', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' ';
$inventory_doc_link = add_query_arg( array(
	'utm_source'   => 'finale-lite',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'inventory',
), $doc_link . '/inventory/' );
$inventory_content  .= '<a href="' . $inventory_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>';

// position mismatch check
$position_mismatch_content = __( 'Select Position for Single Product Page.', 'finale-woocommerce-sales-countdown-timer-discount' );
$position_mismatch_content .= '<br/><i class="dashicons dashicons-editor-help"></i> ';
$position_mismatch_content .= __( 'Unable to see this element on product page? Follow this quick ', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' ';
$position_doc_link         = add_query_arg( array(
	'utm_source'   => 'finale-lite',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'position_mismatch',
), 'https://xlplugins.com/documentation/finale-woocommerce-sales-countdown-timer-scheduler-documentation/troubleshooting-guides/unable-to-see-countdown-timer-or-counter-bar/' );
$position_mismatch_content .= '<a href="' . $position_doc_link . '" target="_blank">' . __( 'troubleshooting guide', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>';

// inventory doc text
$elements_ct_content  = __( 'Enable this to show Countdown Timer.', 'finale-woocommerce-sales-countdown-timer-discount' );
$elements_ct_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$elements_ct_content  .= __( 'Need Help with setting up Countdown Timer?', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' ';
$elements_ct_doc_link = add_query_arg( array(
	'utm_source'   => 'finale-lite',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'countdown-timer',
), $doc_link . '/appearance/countdown-timer/' );
$elements_ct_content  .= '<a href="' . $elements_ct_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>';

// inventory doc text
$elements_cb_content  = __( 'Enable this to show Counter Bar.<br/><strong>Inventory Goal</strong> should be <strong>enabled</strong> to display the Counter Bar.', 'finale-woocommerce-sales-countdown-timer-discount' );
$elements_cb_content  .= '<br/><br/><i class="dashicons dashicons-editor-help"></i> ';
$elements_cb_content  .= __( 'Need Help with setting up Counter Bar?', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' ';
$elements_cb_doc_link = add_query_arg( array(
	'utm_source'   => 'finale-lite',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'counter-bar',
), $doc_link . '/appearance/counter-bar/' );
$elements_cb_content  .= '<a href="' . $elements_cb_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>';

// counter-bar skin text
$elements_cb_skin_content = '<i class="dashicons dashicons-editor-help"></i>' . __( ' Note: These skins are indicative designs. The counter bar would automatically move once a purchase is made during the campaign. However, you can adjust sold units using Events to give campaigns a kickstart.', 'finale-woocommerce-sales-countdown-timer-discount' );
$elements_cb_skin_content .= '<br/>';
$elements_cb_skin_content .= __( 'Learn more about Events here.', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' ';
$elements_cb_doc_link     = add_query_arg( array(
	'utm_source'   => 'finale-lite',
	'utm_campaign' => 'doc',
	'utm_medium'   => 'text-click',
	'utm_term'     => 'counter-bar-events',
), $doc_link . '/events/' );
$elements_cb_skin_content .= '<a href="' . $elements_cb_doc_link . '" target="_blank">' . __( 'Watch Video or Read Docs', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>';

// range html
ob_start();
?>
    <ul class="cmb2-radio-list cmb2-list">
        <li>
            <input type="radio" class="cmb2-option" name="_wcct_inventory_range_html" value="range" checked="checked"/>
            <label><?php echo __( 'Basic', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></label>
        </li>
        <li class="wcct_round_radio_html">
            <a href="javascript:void(0)" onclick="show_modal_pro('inventory_range');"><?php echo __( 'Range', 'finale-woocommerce-sales-countdown-timer-discount' ); ?>
                <i class="dashicons dashicons-lock"></i>
            </a>
        </li>
        <li class="wcct_round_radio_html">
            <a href="javascript:void(0)" onclick="show_modal_pro('inventory_adv');"><?php echo __( 'Advanced', 'finale-woocommerce-sales-countdown-timer-discount' ); ?>
                <i class="dashicons dashicons-lock"></i>
            </a>
        </li>
    </ul>
<?php
$quantity_before_html = ob_get_clean();

return array(
	array(
		'id'       => 'wcct_campaign_settings',
		'title'    => '<i class="flicon flicon-weekly-calendar"></i> ' . __( 'Schedule', 'finale-woocommerce-sales-countdown-timer-discount' ) . '<span class="wcct_load_spin wcct_load_tab_campaign"></span>',
		'position' => 3,
		'fields'   => array(
			array(
				'name'        => __( 'Type', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_campaign_type',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_radio_btn', 'wcct_no_border' ),
				'options'     => array(
					'fixed_date' => __( 'One Time', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'recurring'  => __( 'Recurring', 'finale-woocommerce-sales-countdown-timer-discount' ) . " <i class='dashicons dashicons-lock wcct_lock_upgrade'></i>",
					'evergreen'  => __( 'Evergreen', 'finale-woocommerce-sales-countdown-timer-discount' ) . " <i class='dashicons dashicons-lock wcct_lock_upgrade'></i>",
				),
				'attributes'  => array(
					'onclick' => 'show_purchase_pop_on_change(event,this,\'campaign_type\');',
				),
			),
			array(
				'content'     => $onetime_content,
				'id'          => '_wcct_campaign_fixed_date_title',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'fixed_date',
				),
			),
			// fixed date
			array(
				'name'        => __( 'Start Date & Time', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_campaign_fixed_recurring_start_date',
				'type'        => 'text_date',
				'row_classes' => array( 'wcct_combine_2_field_start' ),
				'date_format' => 'Y-m-d',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'fixed_date',
				),
			),
			array(
				'name'        => __( 'Start Time', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_campaign_fixed_recurring_start_time',
				'type'        => 'text_time',
				'row_classes' => array( 'wcct_combine_2_field_end' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'fixed_date',
				),
			),
			array(
				'name'        => __( 'End Date & Time', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_campaign_fixed_end_date',
				'type'        => 'text_date',
				'row_classes' => array( 'wcct_combine_2_field_start' ),
				'date_format' => 'Y-m-d',
				'attributes'  => array(
					'data-validation'        => 'required',
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'fixed_date',
				),
			),
			array(
				'name'        => __( 'End Time', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_campaign_fixed_end_time',
				'type'        => 'text_time',
				'row_classes' => array( 'wcct_combine_2_field_end' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_campaign_type',
					'data-conditional-value' => 'fixed_date',
				),
			),
		),
	),
	array(
		'id'       => 'wcct_deal_price_settings',
		'title'    => '<i class="flicon flicon-money-bill-of-one"></i> ' . __( 'Discount', 'finale-woocommerce-sales-countdown-timer-discount' ) . '<span class="wcct_load_spin wcct_load_tab_deal"></span>',
		'position' => 6,
		'fields'   => array(
			array(
				'name'                     => __( 'Enable', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'                       => '_wcct_deal_enable_price_discount',
				'type'                     => 'wcct_switch',
				'row_classes'              => array( 'wcct_no_border' ),
				'default'                  => 0,
				'label'                    => array(
					'on'  => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'off' => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'wcct_accordion_title'     => __( 'Pricing Discount', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'content'     => $discount_content,
				'id'          => '_wcct_deal_discount_amount_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => __( 'Discount Amount', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_deal_amount',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_extra_small' ),
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'step'                   => '0.01',
					'data-conditional-id'    => '_wcct_deal_enable_price_discount',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Type', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_deal_type',
				'type'        => 'select',
				'options'     => array(
					'percentage_sale' => __( 'Percentage % on Sale Price', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'percentage'      => __( 'Percentage % on Regular Price', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'fixed_sale'      => __( 'Fixed Amount ' . get_woocommerce_currency_symbol() . ' on Sale Price', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'fixed_price'     => __( 'Fixed Amount ' . get_woocommerce_currency_symbol() . ' on Regular Price', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'row_classes' => array( 'wcct_combine_2_field_end' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_enable_price_discount',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'       => __( 'Override Discount', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'         => '_wcct_deal_override_price_discount',
				'desc'       => __( 'Override this discount if Sale is set locally.', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'type'       => 'checkbox',
				'attributes' => array(
					'data-conditional-id'    => '_wcct_deal_enable_price_discount',
					'data-conditional-value' => '1',
				),
			),
		),
	),
	array(
		'id'       => 'wcct_deal_inventory_settings',
		'title'    => '<i class="flicon flicon-text-file-filled-interface-paper-sheet"></i> ' . __( 'Inventory', 'finale-woocommerce-sales-countdown-timer-discount' ) . '<span class="wcct_load_spin wcct_load_tab_deal"></span>',
		'position' => 9,
		'fields'   => array(
			array(
				'name'                     => __( 'Enable', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'                       => '_wcct_deal_enable_goal',
				'type'                     => 'wcct_switch',
				'label'                    => array(
					'on'  => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'off' => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'row_classes'              => array( 'wcct_detect_checkbox_change', 'wcct_gif_location', 'wcct_gif_appearance', 'wcct_no_border' ),
				'wcct_accordion_title'     => __( 'Inventory Goal', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'content'     => $inventory_content,
				'id'          => '_wcct_deal_inventory_goal_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => __( 'Quantity to be Sold', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_deal_units',
				'type'        => 'radio_inline',
				'row_classes' => array( 'wcct_no_border' ),
				'options'     => array(
					'custom' => __( 'Custom Stock Quantity', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'same'   => __( 'Existing Stock Quantity', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_enable_goal',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Same Inventory Label', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_deal_same_inventory_html',
				'content'     => __( 'This will pick up stock quantity of individual product and applicable when Manage Stock in product is ON.', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0' ),
				'attributes'  => array(
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'same',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),
			array(
				'name'        => __( 'Quantity', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_deal_custom_units',
				'type'        => 'text_small',
				'before'      => $quantity_before_html,
				'row_classes' => array( 'wcct_text_extra_small', 'wcct_border_top', 'wcct_no_border', 'cmb-inline' ),
				'attributes'  => array(
					'type'                        => 'number',
					'min'                         => '0',
					'pattern'                     => '\d*',
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'custom',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),
			array(
				'name'        => __( 'Inventory Advanced HTML', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_deal_inventory_advanced_html',
				'content'     => __( 'Custom Quantity is the new overall quantity of a product available for purchase.', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_label_gap', 'wcct_pt0', 'wcct_pb10', 'row_title_classes', 'wcct_small_text' ),
				'attributes'  => array(
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'custom',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),
			array(
				'name'        => __( 'Calculate Sold Units (for counter bar)', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_deal_inventory_goal_for',
				'type'        => 'radio_inline',
				'desc'        => __( 'Need help?', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' <a href="javascript:void(0);" onclick="wcct_show_tb(\'' . __( 'Inventory Sold Units Help', 'finale-woocommerce-sales-countdown-timer-discount' ) . '\',\'wcct_inventory_sold_unit_help\');">' . __( 'Learn More', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>',
				'options'     => array(
					'recurrence' => __( 'Current Occurrence', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'campaign'   => __( 'Overall Campaign', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'row_classes' => array( 'wcct_text_extra_small', 'wcct_light_desc' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_enable_goal',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Setup campaign on Out of Stock Products', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_deal_custom_units_allow_backorder',
				'type'        => 'radio_inline',
				'desc'        => __( 'Need help?', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' <a href="javascript:void(0);" onclick="wcct_show_tb(\'' . __( 'Setup campaign on Out of Stock Products Help', 'finale-woocommerce-sales-countdown-timer-discount' ) . '\',\'wcct_inventory_out_of_stock_help\');">' . __( 'Learn More', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>',
				'row_classes' => array( 'wcct_text_extra_small', 'wcct_light_desc' ),
				'options'     => array(
					'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'attributes'  => array(
					'data-wcct-conditional-id'    => '_wcct_deal_units',
					'data-wcct-conditional-value' => 'custom',
					'data-conditional-id'         => '_wcct_deal_enable_goal',
					'data-conditional-value'      => '1',
				),
			),
			array(
				'name'        => __( 'End Campaign', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_deal_end_campaign',
				'type'        => 'radio_inline',
				'options'     => array(
					'yes' => __( 'Yes', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'no'  => __( 'No', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'desc'        => __( 'When all the units set up in the campaign are sold.', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'row_classes' => array( 'wcct_text_extra_small', 'wcct_light_desc' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_deal_enable_goal',
					'data-conditional-value' => '1',
				),
			),
		),
	),
	array(
		'id'       => 'wcct_appearance_settings',
		'title'    => '<i class="flicon flicon-old-elevator-levels-tool"></i> ' . __( 'Elements', 'finale-woocommerce-sales-countdown-timer-discount' ) . '<span class="wcct_load_spin wcct_load_tab_appearance"></span>',
		'position' => 15,
		'fields'   => array(
			// countdown timer single product
			array(
				'name'                     => __( 'Visibility', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'                       => '_wcct_location_timer_show_single',
				'type'                     => 'wcct_switch',
				'row_classes'              => array( 'wcct_no_border' ),
				'default'                  => 0,
				'label'                    => array(
					'on'  => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'off' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'wcct_accordion_title'     => __( 'Single Product Countdown Timer', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'id'          => '_wcct_location_timer_show_single_html',
				'content'     => $elements_ct_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => __( 'Position', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_location_timer_single_location',
				'type'        => 'select',
				'row_classes' => array( 'wcct_no_border' ),
				'options'     => array(
					'1' => __( 'Above the Title', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'2' => __( 'Below the Title', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'3' => __( 'Below the Review Rating', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'4' => __( 'Below the Price', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'5' => __( 'Below Short Description', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'6' => __( 'Below Add to Cart Button', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'id'          => '_wcct_location_timer_position_html',
				'content'     => $position_mismatch_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Countdown Timer', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_appearance_timer_single_skin',
				'type'        => 'radio_inline',
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Skins', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'options'     => array(
					'round_fill'   => __( 'Round Fill', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'round_ghost'  => __( 'Round Ghost', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'square_fill'  => __( 'Square Fill', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'square_ghost' => __( 'Square Ghost', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'highlight_1'  => __( 'Highlight', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'default'      => __( 'Default', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'row_classes' => array( 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'content'     => '<img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'assets/img/timer_circle.jpg" />',
				'id'          => '_wcct_appearance_timer_single_round_fill_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'round_fill',
				),
			),
			array(
				'content'     => '<img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'assets/img/timer_ghost.jpg" />',
				'id'          => '_wcct_appearance_timer_single_round_ghost_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'round_ghost',
				),
			),
			array(
				'content'     => '<img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'assets/img/timer_square.jpg" />',
				'id'          => '_wcct_appearance_timer_single_square_fill_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'square_fill',
				),
			),
			array(
				'content'     => '<img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'assets/img/timer_square_ghost.jpg" />',
				'id'          => '_wcct_appearance_timer_single_square_ghost_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'square_ghost',
				),
			),
			array(
				'content'     => '<img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'assets/img/timer_text.jpg" />',
				'id'          => '_wcct_appearance_timer_single_highlight_1_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'highlight_1',
				),
			),
			array(
				'content'     => '<img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'assets/img/timer_text_simple.jpg" />',
				'id'          => '_wcct_appearance_timer_single_default_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'wcct_hide_label', 'wcct_label_gap', 'wcct_p0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_timer_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_timer_single_skin',
					'data-wcct-conditional-value' => 'default',
				),
			),
			array(
				'content'     => __( 'Note: You may need to adjust the default appearance settings in case you switch the default skin.', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_html_coutdown_help_2',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Timer Color',
				'id'          => '_wcct_appearance_timer_single_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Background/Border', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Text Color',
				'id'          => '_wcct_appearance_timer_single_text_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Label', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Timer Font Size (px)',
				'id'          => '_wcct_appearance_timer_single_font_size_timer',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Timer Font Size (px)', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Font Size',
				'id'          => '_wcct_appearance_timer_single_font_size',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_text_gap', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Label Font Size (px)', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Timer Days',
				'id'          => '_wcct_appearance_timer_single_label_days',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Timer Labels', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">' . __( 'days', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Timer Hours',
				'id'          => '_wcct_appearance_timer_single_label_hrs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">' . __( 'hours', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Timer Minutes',
				'id'          => '_wcct_appearance_timer_single_label_mins',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_text_gap' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">' . __( 'minutes', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Timer Seconds',
				'id'          => '_wcct_appearance_timer_single_label_secs',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_text_gap', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">&nbsp;</p>',
				'after'       => '<p class="wcct_mt5 wcct_mb5">' . __( 'seconds', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Display Single Product',
				'id'          => '_wcct_appearance_timer_single_display',
				'type'        => 'textarea_small',
				'desc'        => __( '{{countdown_timer}}: Outputs the countdown timer. <br/> {{campaign_start_date}}: Shows campaign start date <br/> {{campaign_end_date}}: Shows campaign end date', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'row_classes' => array( 'wcct_hide_label', 'wcct_light_desc', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Display', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Single Border Style',
				'id'          => '_wcct_appearance_timer_single_border_style',
				'type'        => 'select',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_small', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Border Style', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'options'     => array(
					'dotted' => __( 'Dotted', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'dashed' => __( 'Dashed', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'solid'  => __( 'Solid', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'double' => __( 'Double', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'none'   => __( 'None', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Single Border Width',
				'id'          => '_wcct_appearance_timer_single_border_width',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Border Width (px)', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Single Border Color',
				'id'          => '_wcct_appearance_timer_single_border_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_gap', 'wcct_hide_label', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Border Color', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),

			array(
				'name'        => 'Mobile Timer',
				'id'          => '_wcct_appearance_timer_mobile_reduction',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_text_color', 'wcct_pt0' ),
				'after_row'   => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Reduce Countdown Timer Size on Mobile (%)', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_timer_show_single',
					'data-conditional-value' => '1',
				),
			),

			// counter bar single product
			array(
				'name'                     => __( 'Visibility', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'                       => '_wcct_location_bar_show_single',
				'type'                     => 'wcct_switch',
				'row_classes'              => array( 'wcct_no_border' ),
				'label'                    => array(
					'on'  => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'off' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'wcct_accordion_title'     => __( 'Single Product Counter Bar', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'wcct_is_accordion_opened' => true,
			),
			array(
				'id'          => '_wcct_location_bar_show_single_html',
				'content'     => $elements_cb_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
			),
			array(
				'name'        => 'Position',
				'id'          => '_wcct_location_bar_single_location',
				'type'        => 'select',
				'row_classes' => array( 'wcct_no_border' ),
				'options'     => array(
					'1' => __( 'Above the Title', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'2' => __( 'Below the Title', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'3' => __( 'Below the Review Rating', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'4' => __( 'Below the Price', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'5' => __( 'Below Short Description', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'6' => __( 'Below Add to Cart Button', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'id'          => '_wcct_location_bar_position_html',
				'content'     => $position_mismatch_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_dashicons_color' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => __( 'Counter Bar', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_appearance_bar_single_skin',
				'type'        => 'radio_inline',
				'options'     => array(
					'stripe'         => '<img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'assets/img/bar-capsule-lines.jpg" />',
					'stripe_animate' => '<img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'assets/img/bar-capsule-animated.gif" />',
					'fill'           => '<img src="' . plugin_dir_url( WCCT_PLUGIN_FILE ) . 'assets/img/bar-capsule.jpg" />',
				),
				'row_classes' => array( 'wcct_img_options', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Skins', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'id'          => '_wcct_appearance_bar_skin_html',
				'content'     => $elements_cb_skin_content,
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_p0', 'wcct_dashicons_color', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Edges',
				'id'          => '_wcct_appearance_bar_single_edges',
				'type'        => 'radio_inline',
				'options'     => array(
					'rounded' => __( 'Rounded', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'smooth'  => __( 'Smooth', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'sharp'   => __( 'Sharp', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'row_classes' => array( 'wcct_hide_label', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Edges', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Direction',
				'id'          => '_wcct_appearance_bar_single_orientation',
				'type'        => 'radio_inline',
				'options'     => array(
					'ltr' => __( 'Left to Right', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' ( <i class="dashicons dashicons-arrow-right-alt"></i> )',
					'rtl' => __( 'Right to Left', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' ( <i class="dashicons dashicons-arrow-left-alt"></i> )',
				),
				'row_classes' => array( 'wcct_hide_label', 'wcct_no_border', 'wcct_pb5' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Direction', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'content'     => __( 'This moves counter bar left to right. Use this when you want to indicate increase in sales.', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_appearance_bar_ltr_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_bar_single_orientation',
					'data-wcct-conditional-value' => 'ltr',
				),
			),
			array(
				'content'     => __( 'This moves counter bar right to left. Use this when you want to indicate decrease in stocks.', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'          => '_wcct_appearance_bar_rtl_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_small_text', 'wcct_label_gap', 'wcct_pt0', 'wcct_pb0', 'wcct_no_border' ),
				'attributes'  => array(
					'data-conditional-id'         => '_wcct_location_bar_show_single',
					'data-conditional-value'      => '1',
					'data-wcct-conditional-id'    => '_wcct_appearance_bar_single_orientation',
					'data-wcct-conditional-value' => 'rtl',
				),
			),
			array(
				'name'        => 'Counter Bar',
				'id'          => '_wcct_appearance_bar_single_bg_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_hide_label', 'wcct_pb0' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Background/Border', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Bar Active Color',
				'id'          => '_wcct_appearance_bar_single_active_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Active', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Bar Height',
				'id'          => '_wcct_appearance_bar_single_height',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_color', 'wcct_hide_label', 'wcct_pb0', 'wcct_no_border' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Height (px)', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '5',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Bar Display',
				'id'          => '_wcct_appearance_bar_single_display',
				'type'        => 'textarea_small',
				'row_classes' => array( 'wcct_hide_label', 'wcct_light_desc', 'wcct_pb0', 'wcct_no_border' ),
				'desc'        => '{{counter_bar}}: Outputs the counter bar. <br/> {{campaign_start_date}}: Shows campaign start date <br/> {{campaign_end_date}}: Shows campaign end date<br/><a href="javascript:void(0);" onclick="wcct_show_tb(\'Counter Bar Merge Tags\',\'wcct_merge_tags_inventory_bar_help\');">Click here to learn to set up more dynamic merge tags in counter bar</a>',
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Display', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Bar Border Style',
				'id'          => '_wcct_appearance_bar_single_border_style',
				'type'        => 'select',
				'row_classes' => array( 'wcct_combine_2_field_start', 'wcct_text_small', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Border Style', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'options'     => array(
					'dotted' => __( 'Dotted', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'dashed' => __( 'Dashed', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'solid'  => __( 'Solid', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'double' => __( 'Double', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'none'   => __( 'None', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Bar Border Width',
				'id'          => '_wcct_appearance_bar_single_border_width',
				'type'        => 'text_small',
				'row_classes' => array( 'wcct_combine_2_field_middle', 'wcct_text_color', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Border Width (px)', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'attributes'  => array(
					'type'                   => 'number',
					'min'                    => '0',
					'pattern'                => '\d*',
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			array(
				'name'        => 'Bar Border Color',
				'id'          => '_wcct_appearance_bar_single_border_color',
				'type'        => 'colorpicker',
				'row_classes' => array( 'wcct_combine_2_field_end', 'wcct_text_gap', 'wcct_hide_label' ),
				'before'      => '<p class="wcct_mt5 wcct_mb5">' . __( 'Border Color', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</p>',
				'after_row'   => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'attributes'  => array(
					'data-conditional-id'    => '_wcct_location_bar_show_single',
					'data-conditional-value' => '1',
				),
			),
			// sticky header
			array(
				'name'                     => __( 'Visibility', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'                       => '_wcct_location_timer_show_sticky_header',
				'type'                     => 'wcct_html_content_field',
				'label'                    => array(
					'on'  => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'off' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'after_row'                => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'content'                  => '',
				'wcct_accordion_title'     => __( 'Sticky Header', 'finale-woocommerce-sales-countdown-timer-discount' ) . '<i class="dashicons dashicons-lock wcct_lock_upgrade"></i>',
				'wcct_is_accordion_opened' => false,
			),
			// sticky footer
			array(
				'name'                     => __( 'Visibility', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'                       => '_wcct_location_timer_show_sticky_footer',
				'type'                     => 'wcct_html_content_field',
				'default'                  => 0,
				'content'                  => '',
				'label'                    => array(
					'on'  => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'off' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'after_row'                => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'wcct_accordion_title'     => __( 'Sticky Footer', 'finale-woocommerce-sales-countdown-timer-discount' ) . '<i class="dashicons dashicons-lock wcct_lock_upgrade"></i>',
				'wcct_is_accordion_opened' => false,
			),
			// custom text
			array(
				'name'                     => __( 'Visibility', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'                       => '_wcct_location_timer_show_custom_text',
				'type'                     => 'wcct_html_content_field',
				'default'                  => 0,
				'content'                  => '',
				'label'                    => array(
					'on'  => __( 'Show', 'finale-woocommerce-sales-countdown-timer-discount' ),
					'off' => __( 'Hide', 'finale-woocommerce-sales-countdown-timer-discount' ),
				),
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'after_row'                => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'wcct_accordion_title'     => __( 'Custom Text', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' <i class="dashicons dashicons-lock wcct_lock_upgrade"></i>',
				'wcct_is_accordion_opened' => false,
			),
			// custom css
			array(
				'name'                     => __( 'CSS', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'id'                       => '_wcct_appearance_custom_css',
				'before_row'               => array( 'WCCT_Admin_CMB2_Support', 'cmb_before_row_cb' ),
				'row_classes'              => array( 'wcct_textarea_full' ),
				'wcct_accordion_title'     => __( 'Custom CSS', 'finale-woocommerce-sales-countdown-timer-discount' ) . ' <i class="dashicons dashicons-lock wcct_lock_upgrade"></i>',
				'wcct_is_accordion_opened' => false,
				'after_row'                => array( 'WCCT_Admin_CMB2_Support', 'cmb_after_row_cb' ),
				'desc'                     => __( 'Enter Custom CSS to modify the visual.', 'finale-woocommerce-sales-countdown-timer-discount' ),
				'type'                     => 'wcct_html_content_field',
				'content'                  => '',
			),
		),
	),
	array(
		'id'       => 'wcct_help_settings',
		'title'    => '<i class="flicon flicon-lightbulb-filled-interface-sign"></i> ' . __( 'Ideas Factory', 'finale-woocommerce-sales-countdown-timer-discount' ),
		'position' => 16,
		'fields'   => array(
			array(
				'content'     => self::wcct_ideas_inner(),
				'id'          => '_wcct_help_video_html',
				'type'        => 'wcct_html_content_field',
				'row_classes' => array( 'row_title_classes', 'wcct_row_3col' ),
			),
		),
	),
	array(
		'id'       => 'wcct_coupon_settings',
		'title'    => '<i class="flicon flicon-lock-square-locked-filled-padlock"></i> ' . __( 'Coupons', 'finale-woocommerce-sales-countdown-timer-discount' ),
		'position' => 17,
		'fields'   => array(
			array(
				'name'    => '',
				'id'      => '_wcct_coupon_buy_pro_html',
				'type'    => 'wcct_html_content_field',
				'content' => __( 'COUPON TEXT HERE', 'finale-woocommerce-sales-countdown-timer-discount' ) . '<br/>' . __( 'Unlock Advanced settings and more awesome features.', 'finale-woocommerce-sales-countdown-timer-discount' ) . "<br/><a href='#'>" . __( 'Upgrade to PRO', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>',
			),
		),
	),
	array(
		'id'       => 'wcct_events_settings',
		'title'    => '<i class="flicon flicon-lock-square-locked-filled-padlock"></i> ' . __( 'Events', 'finale-woocommerce-sales-countdown-timer-discount' ),
		'position' => 18,
		'fields'   => array(
			array(
				'name'    => '',
				'id'      => '_wcct_events_buy_pro_html',
				'type'    => 'wcct_html_content_field',
				'content' => __( 'Want to Tweak Prices and Inventory During Campaigns?', 'finale-woocommerce-sales-countdown-timer-discount' ) . '<br/>' . __( 'Unlock Events and more awesome features.', 'finale-woocommerce-sales-countdown-timer-discount' ) . "<br/><a href='#'>" . __( 'Upgrade to PRO', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>',
			),
		),
	),
	array(
		'id'       => 'wcct_actions_settings',
		'title'    => '<i class="flicon flicon-lock-square-locked-filled-padlock"></i> ' . __( 'Actions', 'finale-woocommerce-sales-countdown-timer-discount' ),
		'position' => 21,
		'fields'   => array(
			array(
				'name'    => '',
				'id'      => '_wcct_actions_buy_pro_html',
				'type'    => 'wcct_html_content_field',
				'content' => __( 'Create Genuine Scarcity by changing stock status , product visibility or hiding Add to Cart during or after campaigns.', 'finale-woocommerce-sales-countdown-timer-discount' ) . '<br/>' . __( 'Unlock Actions and more awesome features.', 'finale-woocommerce-sales-countdown-timer-discount' ) . "<br/><a href='#'>" . __( 'Upgrade to PRO', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>',
			),
		),
	),
	array(
		'id'       => 'wcct_misc_settings',
		'title'    => '<i class="flicon flicon-lock-square-locked-filled-padlock"></i> ' . __( 'Advanced', 'finale-woocommerce-sales-countdown-timer-discount' ),
		'position' => 24,
		'fields'   => array(
			array(
				'name'    => '',
				'id'      => '_wcct_misc_buy_pro_html',
				'type'    => 'wcct_html_content_field',
				'content' => __( 'Fine Tune Campaigns by changing Add to Cart Button Text and much more.', 'finale-woocommerce-sales-countdown-timer-discount' ) . '<br/>' . __( 'Unlock Advanced settings and more awesome features.', 'finale-woocommerce-sales-countdown-timer-discount' ) . "<br/><a href='#'>" . __( 'Upgrade to PRO', 'finale-woocommerce-sales-countdown-timer-discount' ) . '</a>',
			),
		),
	),
);
