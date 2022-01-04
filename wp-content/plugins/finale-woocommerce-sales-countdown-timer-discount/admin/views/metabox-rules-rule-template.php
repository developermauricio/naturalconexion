<?php
defined( 'ABSPATH' ) || exit;
?>
<td class="rule-type">
	<?php
	$types = apply_filters( 'wcct_wcct_rule_get_rule_types', array() );
	// create field
	$args = array(
		'input'   => 'select',
		'name'    => 'wcct_rule[<%= groupId %>][<%= ruleId %>][rule_type]',
		'class'   => 'rule_type',
		'choices' => $types,
	);

	WCCT_Input_Builder::create_input_field( $args, 'product_select' );
	?>
</td>

<?php
WCCT_Common::render_rule_choice_template( array(
	'group_id'  => 0,
	'rule_id'   => 0,
	'rule_type' => 'product_select',
	'condition' => false,
	'operator'  => false,
) );
?>
<td class="loading" colspan="2" style="display:none;"><?php _e( 'Loading...', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></td>
<td class="add"><a href="#" class="wcct-add-rule button"><?php _e( 'AND', 'finale-woocommerce-sales-countdown-timer-discount' ); ?></a></td>
<td class="remove"><a href="#" class="wcct-remove-rule wcct-button-remove"></a></td>
