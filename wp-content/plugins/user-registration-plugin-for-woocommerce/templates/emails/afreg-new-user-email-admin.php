<?php
/**
 * New User Registration notification to admin
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/afreg-new-user-email-admin.php.
 *
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
		
	   $content = wpautop( wptexturize( $email_content ) );

		echo wp_kses_post( $content );
	?>
</p>
<?php

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
