<?php
/**
 * Create notification object and get message and message type as WooCommerce is inactive
 * also set variable allowed on 0 to disable submit button on step 1 of configuration
 */
$notifications_obj = new WooSEA_Get_Admin_Notifications;
if (!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        $notifications_box = $notifications_obj->get_admin_notifications ( "9", "false" );
} else {
        $notifications_box = $notifications_obj->get_admin_notifications ( '16', 'false' );
}

if (array_key_exists('project_hash', $_GET)){
	$project = WooSEA_Update_Project::get_project_data(sanitize_text_field($_GET['project_hash']));	
	$project_hash = $_GET['project_hash'];
	$step = $_GET['step'];
	
	$projectname = ucfirst($project['projectname']);
}
?>
<div class="wrap">
        <div class="woo-product-feed-pro-form-style-2">
   	<table class="woo-product-feed-pro-table"> 
 		<tbody class="woo-product-feed-pro-body">
                        <div class="woo-product-feed-pro-form-style-2-heading"><?php _e( 'Google Shopping feed analysis','woo-product-feed-pro' );?></div>

                        <div class="<?php _e($notifications_box['message_type']); ?>">
                                <p><?php _e($notifications_box['message'], 'sample-text-domain' ); ?></p>
                        </div>
	
			<tr>
				<td align="center">
					<input type="hidden" id="project_hash" name="project_hash" value="<?php print "$project_hash";?>">
					<input type="hidden" id="step" name="step" value="<?php print "$step";?>">

					<div id="content">

						<div class="chart-container">
							<div id="placeholder" class="chart-placeholder main" style="width:auto;height:400px;"></div>
						</div>	
						<?php
						$gs_notifications = get_option('woosea_gs_analysis_results');
						print_r($gs_notifications);
						?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
</div>
