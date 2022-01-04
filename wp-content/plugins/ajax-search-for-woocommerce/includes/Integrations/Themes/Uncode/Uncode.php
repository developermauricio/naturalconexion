<?php

namespace DgoraWcas\Integrations\Themes\Uncode;

use DgoraWcas\Abstracts\ThemeIntegration;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uncode extends ThemeIntegration {

	public function __construct() {
		$this->themeSlug = 'uncode';
		$this->themeName = 'Uncode';

		parent::__construct();
	}
}
