<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'wpwoof_admin_notices_hook' ) ) {
	add_action( 'admin_notices', 'wpwoof_admin_notices_hook',9999);
	function wpwoof_admin_notices_hook() {
			if ( false == current_user_can( 'manage_options' ) ) {
				return;
			}

		    $notice_body='';
			## switch suitable notices set
		    $errs = get_option("wpwoofeed_errors",null);
		    if($errs ){
				$notice_body="<span><a href='".get_dashboard_url()."?page=wpwoof-settings'>Product Catalog Plugin:</a></span>";
				$notice_body.=" Regeneration failed for one or several feeds. Please open the <a href='".get_dashboard_url()."?page=wpwoof-settings'>Product Catalog Plugin</a> and Edit your feed.";

			}else return;

			## nothing to show
			if( empty( $notice_body ) ) {
				return;
			}?>
			<style type="text/css">
				.wpwoof-notice p > span {color: #dd4e4e; font-weight: bold;}
				.wpwoof-notice p a {color: #F4524D;	}
			</style>
			<div class="notice-error notice is-dismissible wpwoof-notice">
				<p><?php echo $notice_body; ?></p>
			</div><script type='text/javascript'>jQuery(function($){$.ajax({url: ajaxurl,data: {action: 'wpwoof_dismiss_admin_notice'}})});</script>
		<?php			
	}

}
if ( ! function_exists( 'wpwoof_dismiss_admin_notice_ajax' ) ) {
	add_action( 'wp_ajax_wpwoof_dismiss_admin_notice', 'wpwoof_dismiss_admin_notice_ajax' );
	function wpwoof_dismiss_admin_notice_ajax() {
		if ( false == current_user_can( 'manage_options' ) ) {
			exit();
		}
		delete_option("wpwoofeed_errors");
		exit("OK");
	}
}




