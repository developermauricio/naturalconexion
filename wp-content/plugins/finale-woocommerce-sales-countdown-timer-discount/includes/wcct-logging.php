<?php
defined( 'ABSPATH' ) || exit;

/**
 * this add logs in txt file inside WP uploads folder
 *
 * @param string $string
 * @param type $filename
 *
 * @return boolean
 */
if ( ! function_exists( 'wcct_force_log' ) ) {
	function wcct_force_log( $string, $filename = 'force.txt', $mode = 'a' ) {

		if ( empty( $string ) ) {
			return false;
		}

		if ( ( WCCT_Common::$is_force_debug === true ) || ( WP_DEBUG === true && ! is_admin() ) ) {

			$current_date_obj = new DateTime( 'now', new DateTimeZone( WCCT_Common::wc_timezone_string() ) );

			$upload_dir = wp_upload_dir();
			$base_path  = $upload_dir['basedir'] . '/xlplugins/finale';

			if ( ! file_exists( $base_path ) ) {
				mkdir( $base_path, 0777, true );
			}

			$file_path = $base_path . '/' . $filename;
			$file      = fopen( $file_path, $mode );
			$curTime   = $current_date_obj->format( 'M d, Y H.i.s' ) . ': ';
			$string    = "\r\n" . $curTime . $string;
			fwrite( $file, $string );
			fclose( $file );

			return true;
		}

	}
}

if ( ! function_exists( 'xlplugins_force_log' ) ) {
	function xlplugins_force_log( $string, $filename = 'force.txt', $mode = 'a' ) {

		if ( empty( $string ) ) {
			return false;
		}

		$current_date_obj = new DateTime( 'now', new DateTimeZone( 'UTC' ) );

		$upload_dir = wp_upload_dir();
		$base_path  = $upload_dir['basedir'] . '/xlplugins';

		if ( ! file_exists( $base_path ) ) {
			mkdir( $base_path, 0777, true );
		}
		$filename  = str_replace( '.txt', '-' . date( 'Y-m' ) . '.txt', $filename );
		$file_path = $base_path . '/' . $filename;
		$file      = fopen( $file_path, $mode );
		$curTime   = $current_date_obj->format( 'M d, Y H.i.s' ) . ': ';
		$string    = "\r\n" . $curTime . $string;
		fwrite( $file, $string );
		fclose( $file );

		return true;
	}
}
