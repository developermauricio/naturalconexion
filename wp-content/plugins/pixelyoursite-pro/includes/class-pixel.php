<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

interface Pixel {
	
	public function enabled();
	
	public function configured();

	
	/**
	 * Return array of pixel options for front-end.
	 *
	 * @return array
	 */
	public function getPixelOptions();
	

	
	public function outputNoScriptEvents();
	
}