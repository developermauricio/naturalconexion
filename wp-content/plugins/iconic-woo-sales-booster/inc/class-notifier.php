<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Notifier.
 *
 * @class    Iconic_WSB_Notifier
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Notifier {

	const ERROR = 'error';

	const WARNING = 'warning';

	const SUCCESS = 'success';

	const INFO = 'info';

	private static $key = 'iconic_wsb_admin_notifications';

	public static function run() {
		self::$key .= get_current_user_id();
		self::process();
	}

	/**
	 * Add message to show on admin_notices action
	 *
	 * @param string $message
	 * @param string $type
	 * @param bool $is_dismissible
	 */
	public static function push( $message, $type = self::SUCCESS, $is_dismissible = false ) {

		$dismissible = $is_dismissible ? 'is-dismissible' : '';

		add_action( 'admin_notices', function () use ( $message, $type, $dismissible ) {
			echo "<div class='notice notice-{$type} {$dismissible}'><p>{$message}</p></div>";
		} );
	}

	/**
	 * Save flash message to show during next request
	 *
	 * @param string $message
	 * @param string $type
	 * @param bool $is_dismissible
	 */
	public static function flash( $message, $type = self::SUCCESS, $is_dismissible = false ) {
		$message = [ 'message' => $message, 'type' => $type, 'dismissible' => $is_dismissible ];

		$messages = get_transient( self::$key );

		if ( ! is_array( $messages ) ) {
			$messages = [];
		}

		$messages[] = $message;

		set_transient( self::$key, $messages, MINUTE_IN_SECONDS );
	}

	/**
	 * Show flash messages
	 */
	private static function process() {
		$messages = get_transient( self::$key );

		if ( is_array( $messages ) ) {

			delete_transient( self::$key );

			foreach ( $messages as $message ) {
				self::push( $message['message'], $message['type'], $message['dismissible'] );
			}
		}
	}
}