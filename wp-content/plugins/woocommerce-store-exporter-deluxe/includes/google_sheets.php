<?php
if( !class_exists( 'Google_Client' ) ) {
	if( file_exists( WOO_CD_PATH . '/classes/GoogleSheets/php-google-oauth/Google_Client.php' ) )
		require_once( WOO_CD_PATH . '/classes/GoogleSheets/php-google-oauth/Google_Client.php' );
}	

if( file_exists( WOO_CD_PATH . '/classes/GoogleSheets/autoload.php' ) ) {
	include_once( WOO_CD_PATH . '/classes/GoogleSheets/autoload.php' );	
	use Google\Spreadsheet\DefaultServiceRequest;
	use Google\Spreadsheet\ServiceRequestFactory;
}

function woo_ce_validate_google_sheets_access_code( $post_ID = 0, $access_code = '' ) {

	if( empty( $post_ID ) )
		return;

	if( empty( $access_code ) )
		return;

	$access_token = WOO_CD_Google_Sheet::preauth( $post_ID, $access_code );
	if( !empty( $access_token ) )
		return true;

}

class WOO_CD_Google_Sheet {

	private $token;
	private $spreadsheet;
	private $worksheet;

	const clientId = '921518827300-j9pet7uk3um73i3h6pjbvjrhgt6dhh39.apps.googleusercontent.com';
	const clientSecret = 'C8Z27titIya2t4WeOjXnN2Yo';
	const redirect = 'urn:ietf:wg:oauth:2.0:oob';
		
	public function __construct() {

	}

	//constructed on call
	public static function preauth( $post_ID = 0, $access_code = '' ) {

		if( empty( $post_ID ) )
			return;

		if( empty( $access_code ) )
			return;

		$client = new Google_Client();
		$client->setClientId(WOO_CD_Google_Sheet::clientId);
		$client->setClientSecret(WOO_CD_Google_Sheet::clientSecret);
		$client->setRedirectUri(WOO_CD_Google_Sheet::redirect);
		$scopes = array(
			'https://spreadsheets.google.com/feeds'
		);
		$client->setScopes( $scopes );
		
		try {
			$client->authenticate( $access_code );
		} catch( Exception $e ) {
			$message = sprintf( 'Failed to authenticate with Google Sheets, reason: %s', $e->getMessage() );
			woo_ce_error_log( sprintf( 'Error: Google Sheets preauth(): %s', $message ) );
			// delete_post_meta( $post_ID, '_method_google_sheets_access_code' );
			// delete_post_meta( $post_ID, '_method_google_sheets_access_token' );
			return;
		}
		
		$tokenData = json_decode( $client->getAccessToken(), true );
		$access_token = WOO_CD_Google_Sheet::updateToken( $post_ID, $tokenData );
		return $access_token;

	}

	public static function updateToken( $post_ID, $tokenData ) {

		if( empty( $post_ID ) )
			return;

		if( empty( $tokenData ) )
			return;

		$tokenData['expire'] = time() + intval( $tokenData['expires_in'] );
		$tokenJson = json_encode( $tokenData );
		try {
			update_post_meta( $post_ID, '_method_google_sheets_access_token', $tokenJson );
		} catch( Exception $e ) {
			$message = sprintf( 'Failed to write access token against Scheduled Export, reason: %s', $e->getMessage() );
			woo_ce_error_log( sprintf( 'Error: Google Sheets updateToken(): %s', $message ) );
		}
		return $tokenJson;

	}

	public function auth( $post_ID = 0 ) {

		if( empty( $post_ID ) )
			return;

		$tokenData = json_decode( get_post_meta( $post_ID, '_method_google_sheets_access_token', true ), true );
		
		if( time() > $tokenData['expire'] ) {
			$client = new Google_Client();
			$client->setClientId( WOO_CD_Google_Sheet::clientId );
			$client->setClientSecret( WOO_CD_Google_Sheet::clientSecret );
			try {
				$client->refreshToken( $tokenData['refresh_token'] );
			} catch( Exception $e ) {
				$tokenData = array_merge( $tokenData, json_decode( $client->getAccessToken(), true ) );
				WOO_CD_Google_Sheet::updateToken( $post_ID, $tokenData );
			}
		}
		
		/* this is needed */
		echo '111';
		print_r( $tokenData );
		exit();
		$serviceRequest = new DefaultServiceRequest( $tokenData['access_token'] );
		ServiceRequestFactory::setInstance( $serviceRequest );

	}

	//preg_match is a key of error handle in this case
	public function set_spreadsheet_title( $title ) {

		$this->spreadsheet = $title;

	}

	//finished setting the title
	public function set_worksheet_title( $title ) {

		$this->worksheet = $title;

	}

	//choosing the worksheet
	public function add_row( $data ) {

		$spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
		$spreadsheetFeed = false;
		try {
			$spreadsheetFeed = $spreadsheetService->getSpreadsheets();
		} catch( Exception $e ) {
			$message = sprintf( 'Failed to fetch spreadsheets, reason: %s', $e->getMessage() );
			woo_ce_error_log( sprintf( 'Error: Google Sheets add_row(): %s', $message ) );
		}
		if( !empty( $spreadsheetFeed ) ) {
			$spreadsheet = $spreadsheetFeed->getByTitle( $this->spreadsheet );
			$worksheetFeed = $spreadsheet->getWorksheets();
			$worksheet = $worksheetFeed->getByTitle( $this->worksheet );
			$listFeed = $worksheet->getListFeed();
			//$row = array('date'=>'3/22/2015', 'your-name'=>'asdf', 'your-email'=>'asdf@asd.com', 'your-subject'=>'HI!', 'your-message'=>'there.');
			$listFeed->insert( $data );
		}

	}

}
?>