<?php

namespace DgoraWcas\Integrations\Themes\OpenShopPro;

use DgoraWcas\Abstracts\ThemeIntegration;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OpenShopPro extends ThemeIntegration {

	public function __construct() {
		$this->themeSlug = 'open-shop-pro';
		$this->themeName = 'Open Shop Pro';

		parent::__construct();
	}

	/**
	 * Overwrite search
	 *
	 * @return void
	 */
	protected function maybeOverwriteSearch() {
		// We load partial from free version of theme
		$partialPath = DGWT_WCAS_DIR . 'partials/themes/open-shop.php';
		if ( $this->canReplaceSearch() && file_exists( $partialPath ) ) {
			require_once( $partialPath );
		}
	}
}
