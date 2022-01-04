<?php defined( 'ABSPATH' ) || exit;

global $post, $iconic_wsb_class;
/**
 * @var Iconic_WSB_Order_Bump_At_Checkout|Iconic_WSB_Order_Bump_After_Checkout $bump
 * @var array                                                                  $steps
 */
?>
<style>
	/* ONLY ON THIS PAGE! */
	#minor-publishing {
		display: none;
	}
</style>

<div class="iconic-wsb-edit-page">
	<ul class="iconic-wsb-edit-page-nav">
		<?php $i = 0;
		foreach ( $steps as $step_id => $step ) { ?>
			<li class="iconic-wsb-edit-page-nav__item">
				<a href="#iconic-wsb-step-<?php echo esc_attr( $step_id ); ?>" class="iconic-wsb-edit-page-nav__item-link <?php if ( $i === 0 ) {
					echo 'iconic-wsb-edit-page-nav__item-link--active';
				} ?>">
					<span class="iconic-wsb-edit-page-nav__item-number"><?php echo $i+1; ?></span>
					<span class="iconic-wsb-edit-page-nav__item-title"><?php echo $step['title']; ?></span>
				</a>
			</li>
			<?php $i ++;
		} ?>
	</ul>

	<div class="iconic-wsb-edit-page__container">
		<?php $i = 0;
		foreach ( $steps as $step_id => $step ) { ?>
			<?php do_action( 'iconic_wsb_checkout_ob_before_step', $step, $bump ); ?>
			<div id="iconic-wsb-step-<?php echo esc_attr( $step_id ); ?>" class="iconic-wsb-edit-page__step <?php if ( $i === 0 ) {
				echo 'iconic-wsb-edit-page__step--active';
			} ?>">
				<?php $iconic_wsb_class->template->include_template( $step['template'], array(
					'step' => $step,
					'bump' => $bump,
				) ); ?>
			</div>
			<?php do_action( 'iconic_wsb_checkout_ob_after_step', $step, $bump ); ?>
			<?php $i ++;
		} ?>
	</div>
</div>