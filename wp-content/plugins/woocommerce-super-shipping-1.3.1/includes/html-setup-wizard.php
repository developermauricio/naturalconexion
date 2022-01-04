<?php
/**
 * Admin View: Setup wizard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

set_current_screen();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php esc_html_e( 'Super Shipping &rsaquo; Setup Wizard', 'wc-ss' ); ?></title>
		<script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
		<?php do_action( 'admin_enqueue_scripts' ); ?>
		<?php wp_print_scripts( 'wss-setup' )?>
		<?php do_action( 'admin_print_styles' ); ?>
		<?php do_action( 'admin_head' ); ?>
		<style>
			.installation-content-2,
			.installation-content-3{
				display: none;
			}
		</style>
	</head>
	<body class="wp-core-ui">
		<h1><?php _e( 'Hi! Wellcome to the Super Shipping setup wizard', 'wc-ss' ); ?></h1>
		<?php if ( 'migration' == get_transient( 'wss_activation_redirect' ) ) { ?>
			<div class="wc-setup-content">
				<div class="migration-content">
					<p><?php _e( 'This new version include a lot of improvements, fixes and the full integration with the WooCommerce\'s shipping zones manager.', 'wc-ss' );?></p>
					<p><?php _e( 'From now, all new Super Shipping\'s zones and shipping tables will be create and edit from the WooCommerce\'s native manager.', 'wc-ss' ); ?></p>
					<p><iframe src="https://player.vimeo.com/video/329137481" width="640" height="306" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></p>
					<p><?php _e( 'To migrate all shipping zones and tables that you already had to the WooCommerce\'s manager, you only need to click on the button below. That\'s all!', 'wc-ss' ); ?></p>
					<form method="post">
						<?php wp_nonce_field( 'start_migration_wizard' );?>
						<p class="checkbox">
							<input type="checkbox" name="create_backup" id="create_backup" value="yes" checked>
							<label for="create_backup"><?php _e( 'Create a backup of shipping settings before to start the migration', 'wc-ss' ); ?></label>
						</p>
						<button type="submit" id="start-migration" class="button button-primary button-large" value="<?php _e( 'Start the migration', 'wc-ss' ); ?>" name="start_migration"><?php _e( 'Start the migration', 'wc-ss' ); ?></button>
					</form>
				</div>
			</div>
		<?php } ?>
		<?php if ( !get_transient( 'wss_activation_redirect' ) || ( 'install' == get_transient( 'wss_activation_redirect' ) ) ) { ?>
			<ol class="wc-setup-steps">
				<li class="active"><?php _e( 'Create a shipping zone', 'wc-ss' ); ?></li>
				<li><?php _e( 'Set up the shipping method for this zone', 'wc-ss' ); ?></li>
				<li><?php _e( 'Ready!', 'wc-ss' ); ?></li>
			</ol>
			<div class="wc-setup-content">
				<div class="installation-content-1">
					<p><iframe src="https://player.vimeo.com/video/327474222" width="640" height="306" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></p>
					<p class="wc-setup-actions step">
						<button type="button" class="button-primary button button-large button-next"><?php _e( 'Next', 'wc-ss' ); ?></button>
					</p>
				</div>
				<div class="installation-content-2">
					<p><iframe src="https://player.vimeo.com/video/327568515" width="640" height="306" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></p>
					<p class="wc-setup-actions step">
						<button type="button" class="button-primary button button-large button-prev"><?php _e( 'Prev', 'wc-ss' ); ?></button>
						<button type="button" class="button-primary button button-large button-next"><?php _e( 'Next', 'wc-ss' ); ?></button>
					</p>
				</div>
				<div class="installation-content-3">
					<p><?php _e( 'You are ready to start, but before let we recommend you take a look at the Super Shipping\'s documentation. There you\'ll find everything you need to set up your shippings. <a href="https://supershipping.helpscoutdocs.com/" target="_blank"><strong>Click here to see it</strong></a>', 'wc-ss' );?></p>
					<p class="wc-setup-actions step">
						<button type="button" class="button-secondary button button-large button-prev"><?php _e( 'Go back', 'wc-ss' ); ?></button>
						<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=shipping' ); ?>" class="button-primary button button-large button-next"><?php _e( 'Let\'s start!', 'wc-ss' ); ?></a>
					</p>
				</div>
			</div>
		<?php } ?>
	</body>
</html>