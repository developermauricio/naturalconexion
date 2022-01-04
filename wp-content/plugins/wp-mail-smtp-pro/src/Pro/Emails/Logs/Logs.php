<?php

namespace WPMailSMTP\Pro\Emails\Logs;

use WPMailSMTP\Admin\Area;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Options;
use WPMailSMTP\Pro\Emails\Logs\Providers\Common;
use WPMailSMTP\Pro\Emails\Logs\Providers\SMTP;
use WPMailSMTP\Pro\Tasks\EmailLogCleanupTask;
use WPMailSMTP\Providers\MailerAbstract;
use WPMailSMTP\WP;

/**
 * Class Logs.
 *
 * @since 1.5.0
 */
class Logs {

	/**
	 * Used for SMTP, because it has several points of failures
	 * and we need to store email and check its status in different places.
	 * API-based mailers are sent and checked at the same place
	 * and don't need this state.
	 *
	 * @var int
	 */
	private $current_email_id = 0;

	/**
	 * Logs constructor.
	 *
	 * @since 1.5.0
	 */
	public function __construct() {

		$this->init();

		if ( is_admin() && $this->is_enabled() ) {
			new Migration();
		}
	}

	/**
	 * Initialize the Logs functionality.
	 *
	 * @since 1.5.0
	 */
	public function init() {

		// Redefine default Lite CSS file.
		add_filter( 'wp_mail_smtp_admin_enqueue_assets_logs_css', function () {

			return \wp_mail_smtp()->pro->assets_url . '/css/smtp-pro-logs.min.css';
		} );

		// Redefine default Lite JS file.
		add_filter( 'wp_mail_smtp_admin_enqueue_assets_logs_js', function () {

			return \wp_mail_smtp()->pro->assets_url . '/js/smtp-pro-logs' . WP::asset_min() . '.js';
		} );

		add_action( 'wp_mail_smtp_admin_area_enqueue_assets', array( $this, 'enqueue_assets' ) );

		// Redefine the Logs page display class.
		add_filter( 'wp_mail_smtp_admin_display_get_logs_fqcn', function () {

			if ( wp_mail_smtp()->pro->get_logs()->is_archive() ) {
				return \WPMailSMTP\Pro\Emails\Logs\Admin\ArchivePage::class;
			} else {
				return \WPMailSMTP\Pro\Emails\Logs\Admin\SinglePage::class;
			}
		} );

		// Add a new Email Log tab under General.
		add_filter( 'wp_mail_smtp_admin_get_pages', function ( $pages ) {

			$misc = $pages['misc'];
			unset( $pages['misc'] );

			$pages['logs'] = new Admin\SettingsTab();
			$pages['misc'] = $misc;

			return $pages;
		}, 0 );

		// Filter admin area options save process.
		add_filter( 'wp_mail_smtp_options_set', array( $this, 'filter_options_set' ) );

		// Track single email Preview and Deletion.
		add_action( 'admin_init', array( $this, 'process_email_preview' ) );
		add_action( 'admin_init', array( $this, 'process_email_delete' ) );

		// Display notices.
		add_action( 'admin_init', array( $this, 'display_notices' ) );

		/**
		 * Actually log emails.
		 */
		if ( $this->is_enabled() ) {
			// SMTP.
			add_action( 'wp_mail_smtp_mailcatcher_smtp_pre_send_before', array( $this, 'process_smtp_pre_send_before' ) );
			add_action( 'wp_mail_smtp_mailcatcher_smtp_send_before', array( $this, 'process_smtp_send_before' ) );
			add_action( 'wp_mail_smtp_mailcatcher_smtp_send_after', array( $this, 'process_smtp_send_after' ), 10, 7 );
			add_action( 'wp_mail_failed', array( $this, 'process_smtp_fails' ) );
			// Catch All.
			add_action( 'wp_mail_smtp_mailcatcher_send_after', array( $this, 'process_log_save' ), 10, 2 );
		}

		// Initialize screen options for the logs admin archive page.
		add_action( 'load-wp-mail-smtp_page_wp-mail-smtp-logs', array( $this, 'archive_screen_options' ) );
		add_filter( 'set-screen-option', array( $this, 'set_archive_screen_options' ), 10, 3 );
		add_filter( 'set_screen_option_wp_mail_smtp_log_entries_per_page', [ $this, 'set_archive_screen_options' ], 10, 3 );
	}

	/**
	 * Register the screen options for the email logs archive page.
	 *
	 * @since 1.9.0
	 */
	public function archive_screen_options() {

		$screen = get_current_screen();

		if (
			! is_object( $screen ) ||
			strpos( $screen->id, 'wp-mail-smtp_page_wp-mail-smtp-logs' ) === false ||
			isset( $_REQUEST['mode'] ) //phpcs:ignore
		) {
			return;
		}

		add_screen_option(
			'per_page',
			array(
				'label'   => esc_html__( 'Number of log entries per page:', 'wp-mail-smtp-pro' ),
				'option'  => 'wp_mail_smtp_log_entries_per_page',
				'default' => EmailsCollection::PER_PAGE,
			)
		);
	}

	/**
	 * Set the screen options for the archive logs page.
	 *
	 * @since 1.9.0
	 *
	 * @param bool   $keep   Whether to save or skip saving the screen option value.
	 * @param string $option The option name.
	 * @param int    $value  The number of items to use.
	 *
	 * @return bool|int
	 */
	public function set_archive_screen_options( $keep, $option, $value ) {

		if ( 'wp_mail_smtp_log_entries_per_page' === $option ) {
			return (int) $value;
		}

		return $keep;
	}

	/**
	 * Sanitize admin area options.
	 *
	 * @since 1.5.0
	 *
	 * @param array $options Currently processed options passed to a filter hook.
	 *
	 * @return array
	 */
	public function filter_options_set( $options ) {

		if ( ! isset( $options['logs'] ) ) {
			// All options are off by default.
			$options['logs'] = [
				'enabled'              => false,
				'log_email_content'    => false,
				'log_retention_period' => 0,
			];

			return $options;
		}

		foreach ( $options['logs'] as $key => $value ) {
			if ( $key === 'log_retention_period' ) {
				$options['logs'][ $key ] = intval( $value );

				$old_value = Options::init()->get( 'logs', 'log_retention_period' );

				// If this option has changed, cancel the recurring cleanup task.
				if ( $options['logs'][ $key ] !== $old_value ) {
					( new EmailLogCleanupTask() )->cancel();
				}
			} else {
				$options['logs'][ $key ] = (bool) $value;
			}
		}

		return $options;
	}

	/**
	 * Get admin area page URL for Logs, regardless of page mode, default is Archive page.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function get_admin_page_url() {

		return wp_mail_smtp()->get_admin()->get_admin_page_url( Area::SLUG . '-logs' );
	}

	/**
	 * Enqueue required JS and CSS.
	 *
	 * @since 1.5.0
	 */
	public function enqueue_assets() {

		if ( ! wp_mail_smtp()->get_admin()->is_admin_page( 'logs' ) ) {
			return;
		}

		$settings = array(
			'text_email_delete_sure' => esc_html__( 'Are you sure that you want to delete this email log? This action cannot be undone.', 'wp-mail-smtp-pro' ),
		);

		\wp_localize_script(
			'wp-mail-smtp-admin-logs',
			'wp_mail_smtp_logs',
			$settings
		);
	}

	/**
	 * Whether the logging to DB functionality is enabled or not.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function is_enabled() {

		return (bool) Options::init()->get( 'logs', 'enabled' );
	}

	/**
	 * Whether the DB table exists.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function is_valid_db() {

		global $wpdb;

		$table = self::get_table_name();

		return (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s;', $table ) );
	}

	/**
	 * Whether the email content logging is enabled or not.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function is_enabled_content() {

		return (bool) Options::init()->get( 'logs', 'log_email_content' );
	}

	/**
	 * Whether we are on a Logs page (archive, list of all emails).
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function is_archive() {

		return wp_mail_smtp()->get_admin()->is_admin_page( 'logs' ) && ! isset( $_GET['mode'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Whether we are previewing the single email HTML.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function is_preview() {

		// Nonce verification.
		if (
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( $_GET['_wpnonce'], 'wp_mail_smtp_pro_logs_log_preview' ) // phpcs:ignore
		) {
			return false;
		}

		return wp_mail_smtp()->get_admin()->is_admin_page( 'logs' ) &&
			isset( $_GET['mode'] ) &&
			$_GET['mode'] === 'preview' &&
			! empty( $_GET['email_id'] );
	}

	/**
	 * Whether we are deleting email(s) now.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function is_deleting() {

		$is_nonce_good = false;

		// Nonce verification.
		if (
			isset( $_REQUEST['_wpnonce'] ) &&
			(
				wp_verify_nonce( $_REQUEST['_wpnonce'], 'wp_mail_smtp_pro_logs_log_delete' ) || // phpcs:ignore
				wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-emails' ) // phpcs:ignore
			)
		) {
			$is_nonce_good = true;
		}

		if ( ! $is_nonce_good ) {
			return false;
		}

		return wp_mail_smtp()->get_admin()->is_admin_page( 'logs' ) &&
			! empty( $_REQUEST['email_id'] ) &&
			(
				( // Single email deletion.
					isset( $_REQUEST['mode'] ) && $_REQUEST['mode'] === 'delete'
				) ||
				( // Bulk email deletion.
					isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'delete' ||
					isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] === 'delete'
				)
			);
	}

	/**
	 * Generate an email preview and display it for users.
	 *
	 * @since 1.5.0
	 */
	public function process_email_preview() {

		if ( ! $this->is_preview() ) {
			return;
		}

		$email = new Email( (int) $_GET['email_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated

		// It's a raw HTML (with html/body tags), so print as is.
		echo $email->is_html() ? $email->get_content() : nl2br( esc_html( $email->get_content() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		exit;
	}

	/**
	 * Delete the email log entry.
	 *
	 * @since 1.5.0
	 */
	public function process_email_delete() {

		if ( ! $this->is_deleting() ) {
			return;
		}

		$emails = null;

		if ( is_array( $_REQUEST['email_id'] ) ) { // phpcs:ignore
			$emails = new EmailsCollection( array( 'ids' => $_REQUEST['email_id'] ) ); // phpcs:ignore
		} elseif ( is_numeric( $_REQUEST['email_id'] ) ) { // phpcs:ignore
			$emails = new EmailsCollection( array( 'id' => $_REQUEST['email_id'] ) ); // phpcs:ignore
		}

		$deleted = 0;

		if ( $emails !== null ) {
			$deleted = $emails->delete();
		}

		if ( $deleted === 1 ) {
			$url = add_query_arg( 'message', 'deleted_one', $this->get_admin_page_url() );
		} elseif ( $deleted > 1 ) {
			$url = add_query_arg( 'message', 'deleted_some', $this->get_admin_page_url() );
		} else {
			$url = add_query_arg( 'message', 'deleted_none', $this->get_admin_page_url() );
		}

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Display notices on Logs page when needed.
	 *
	 * @since 1.5.0
	 */
	public function display_notices() {

		$message = isset( $_GET['message'] ) ? sanitize_key( $_GET['message'] ) : ''; // phpcs:ignore

		if (
			empty( $message ) ||
			! current_user_can( 'manage_options' ) ||
			! wp_mail_smtp()->get_admin()->is_admin_page( 'logs' )
		) {
			return;
		}

		switch ( $message ) {
			case 'deleted_one':
				WP::add_admin_notice(
					esc_html__( 'Email Log entry was successfully deleted.', 'wp-mail-smtp-pro' ),
					WP::ADMIN_NOTICE_SUCCESS
				);
				break;

			case 'deleted_some':
				WP::add_admin_notice(
					esc_html__( 'Email Log entries were successfully deleted.', 'wp-mail-smtp-pro' ),
					WP::ADMIN_NOTICE_SUCCESS
				);
				break;

			case 'deleted_none':
				WP::add_admin_notice(
					esc_html__( 'There was an error while processing your request, and no email log entries were deleted. Please try again.', 'wp-mail-smtp-pro' ),
					WP::ADMIN_NOTICE_WARNING
				);
				break;
		}
	}

	/**
	 * Save the email that is going to be sent right now.
	 *
	 * @since 2.1.2
	 *
	 * @param MailCatcherInterface $mailcatcher The MailCatcher object.
	 */
	public function process_smtp_pre_send_before( $mailcatcher ) {

		if ( ! $this->is_valid_db() ) {
			return;
		}

		$this->set_current_email_id(
			( new SMTP() )->set_source( $mailcatcher )->save_before()
		);
	}

	/**
	 * Update the email that is going to be sent right now.
	 * It was originally saved before in `process_smtp_pre_send_before`.
	 *
	 * @since 1.5.0
	 * @since 2.1.2 Update the Email before it will be sent.
	 *              The saving was already done in `process_smtp_pre_send_before`.
	 *
     * @param MailCatcherInterface $mailcatcher The MailCatcher object.
	 */
	public function process_smtp_send_before( $mailcatcher ) {

		if ( ! $this->is_valid_db() ) {
			return;
		}

		( new SMTP() )->set_source( $mailcatcher )->update_before( $this->get_current_email_id() );
	}

	/**
	 * Save to DB emails sent through both SMTP and mail().
	 *
	 * @since 1.5.0
	 *
	 * @param bool $is_sent
	 * @param array $to
	 * @param array $cc
	 * @param array $bcc
	 * @param string $subject
	 * @param string $body
	 * @param string $from
	 *
	 * @throws \Exception When email saving failed.
	 */
	public function process_smtp_send_after( $is_sent, $to, $cc, $bcc, $subject, $body, $from ) {

		if ( ! $this->is_valid_db() ) {
			return;
		}

		( new SMTP() )->save_after( $this->get_current_email_id(), $is_sent );
	}

	/**
	 * Save all emails as sent regardless of the actual status. Will be improved in the future.
	 * Supports every mailer, except SMTP, which is handled separately.
	 *
	 * @since 1.5.0
	 *
	 * @param \WPMailSMTP\Providers\MailerAbstract $mailer      The Mailer object.
	 * @param MailCatcherInterface                 $mailcatcher The MailCatcher object.
	 */
	public function process_log_save( MailerAbstract $mailer, MailCatcherInterface $mailcatcher ) {

		if ( ! $this->is_valid_db() ) {
			return;
		}

		( new Common( $mailer ) )->set_source( $mailcatcher )->save();
	}

	/**
	 * Get the current email ID.
	 *
	 * @since 1.5.0
	 *
	 * @return int
	 */
	public function get_current_email_id() {

		return (int) $this->current_email_id;
	}

	/**
	 * Set the email ID that is currently processing.
	 *
	 * @since 1.5.0
	 *
	 * @param int $email_id
	 */
	public function set_current_email_id( $email_id ) {

		$this->current_email_id = (int) $email_id;
	}

	/**
	 * Get the table name.
	 *
	 * @since 1.5.0
	 *
	 * @return string Table name, prefixed.
	 */
	public static function get_table_name() {

		global $wpdb;

		return $wpdb->prefix . 'wpmailsmtp_emails_log';
	}

	/**
	 * Process the failed email sending with SMTP.
	 *
	 * @since 2.1.0
	 *
	 * @param \WP_Error $error The WP Error thrown in WP core: `wp_mail_failed` hook.
	 */
	public function process_smtp_fails( $error ) {

		if ( ! $this->is_valid_db() || ! is_wp_error( $error ) ) {
			return;
		}

		// Process this error only for the Other SMTP, old pepipost SMTP and the default PHP mailer.
		if ( ! in_array( Options::init()->get( 'mail', 'mailer' ), [ 'smtp', 'pepipost', 'mail' ], true ) ) {
			return;
		}

		( new SMTP() )->failed( $this->get_current_email_id(), $error );
	}
}
