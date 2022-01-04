<?php

namespace WPMailSMTP\Pro\Providers\AmazonSES;

use WPMailSMTP\Debug;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Providers\MailerAbstract;

/**
 * Class Mailer implements Mailer functionality.
 *
 * @since 1.5.0
 */
class Mailer extends MailerAbstract {

	/**
	 * The response object from AWS SDK email sending request.
	 *
	 * @since 2.4.0
	 *
	 * @var WPMailSMTP\Vendor\Aws\Result
	 */
	protected $response;

	/**
	 * Not really used since we are using AWS SDK library.
	 * Is here to pass some checks in parent::__construct.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	protected $url = 'https://email.us-east-1.amazonaws.com';

	/**
	 * Set the already configured MailCatcher object.
	 *
	 * @since 1.5.0
	 *
	 * @param MailCatcherInterface $phpmailer The MailerCatcher instance.
	 */
	public function process_phpmailer( $phpmailer ) {

		// Make sure that we have access to PHPMailer class methods.
		if ( ! wp_mail_smtp()->is_valid_phpmailer( $phpmailer ) ) {
			return;
		}

		$this->phpmailer = $phpmailer;
	}

	/**
	 * Use AWS SDK to send emails.
	 *
	 * @since 1.5.0
	 * @since 2.4.0 Switch to AWS SDK.
	 */
	public function send() {

		// Prepare the auth and client objects.
		$auth = new Auth();

		$data = [
			'RawMessage' => [
				'Data' => $this->phpmailer->getSentMIMEMessage(),
			],
		];

		try {
			$response = $auth->get_client()->sendRawEmail( $data );

			$this->process_response( $response );
		} catch ( \Exception $e ) {
			Debug::set(
				'Mailer: Amazon SES' . "\r\n" .
				$e->getMessage()
			);
		}
	}

	/**
	 * Check the correct output of the response.
	 *
	 * @since 1.5.0
	 *
	 * @param WPMailSMTP\Vendor\Aws\Result $response Response object from AWS SDK request.
	 */
	protected function process_response( $response ) {

		$this->response = $response;

		$error = '';

		if ( empty( $this->response ) ) {
			$error = esc_html__( 'Amazon SES request failed (empty response).', 'wp-mail-smtp-pro' );
		}

		if ( is_object( $this->response ) && empty( $this->response->get( 'MessageId' ) ) ) {
			$error = esc_html__( 'Something went wrong. Please try again.', 'wp-mail-smtp-pro' );
		}

		// Save the error text.
		if ( ! empty( $error ) ) {
			Debug::set(
				'Mailer: Amazon SES' . "\r\n" .
				$error
			);
		}
	}

	/**
	 * Whether the email was successfully sent.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function is_email_sent() {

		$is_sent = false;

		if ( is_object( $this->response ) && ! empty( $this->response->get( 'MessageId' ) ) ) {
			$is_sent = true;

			Debug::clear();
		}

		return apply_filters( 'wp_mail_smtp_providers_mailer_is_email_sent', $is_sent, $this->mailer );
	}

	/**
	 * Get mailer debug information, that is helpful during support.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function get_debug_info() {

		$debug_items = [];

		$auth = new Auth();

		$debug_items[] = '<strong>Access Key ID/Secret:</strong> ' . ( $auth->is_clients_saved() ? 'Yes' : 'No' );

		return implode( '<br>', $debug_items );
	}

	/**
	 * Whether the mailer has all its settings correctly set up and saved.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function is_mailer_complete() {

		if ( ! $this->is_php_compatible() ) {
			return false;
		}

		$auth = new Auth();

		if (
			$auth->is_clients_saved() &&
			! $auth->is_auth_required()
		) {
			return true;
		}

		return false;
	}
}
