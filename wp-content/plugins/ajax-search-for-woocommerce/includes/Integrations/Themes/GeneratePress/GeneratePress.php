<?php

namespace DgoraWcas\Integrations\Themes\GeneratePress;

use DgoraWcas\Abstracts\ThemeIntegration;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeneratePress extends ThemeIntegration {

	public function __construct() {
		$this->themeSlug = 'generatepress';
		$this->themeName = 'GeneratePress';

		parent::__construct();
	}
}
