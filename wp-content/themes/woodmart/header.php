<?php
/**
 * The Header template for our theme
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<meta name="google-site-verification" content="Ammoj7Y9GZ5Cmtx32wRkg-XcfHEB3gpnaHmvHzf5Fac" />
	<!-- Global site tag (gtag.js) - Google Ads: 644710920 --> 
	<script async src="https://www.googletagmanager.com/gtag/js?id=AW-644710920"></script>
	<script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'AW-644710920'); </script>

	<!-- Event snippet for Compra (1) conversion page --> 
	<script> gtag('event', 'conversion', { 'send_to': 'AW-644710920/72JKCKK8tJkDEIiEtrMC', 'value': 1.0, 'currency': 'COP', 'transaction_id': '' }); </script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php if ( function_exists( 'wp_body_open' ) ) : ?>
		<?php wp_body_open(); ?>
	<?php endif; ?>

	<?php do_action( 'woodmart_after_body_open' ); ?>

	<div class="website-wrapper">
		<?php if ( woodmart_needs_header() ) : ?>
			<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) : ?>
				<header <?php woodmart_get_header_classes(); // phpcs:ignore ?>>
					<?php whb_generate_header(); ?>
				</header>
			<?php endif ?>

			<?php woodmart_page_top_part(); ?>
		<?php endif ?>
