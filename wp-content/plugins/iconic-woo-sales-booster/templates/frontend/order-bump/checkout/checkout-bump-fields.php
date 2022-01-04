<?php defined( 'ABSPATH' ) || exit;

/**
 * @var Iconic_WSB_Order_Bump_After_Checkout $bump
 */
?>

<div class="iconic-wsb-acb-fields">
	<input type="hidden" value="<?php echo esc_attr($bump->get_id()); ?>" name="iconic-wsb-acb-bump-id">
	<input type="hidden" value="" name="iconic-wsb-acb-action">
	<input type="hidden" value="" name="iconic-wsb-acb-variation-id">
	<input type="hidden" value="" name="iconic-wsb-acb-variation-data"> 
</div>