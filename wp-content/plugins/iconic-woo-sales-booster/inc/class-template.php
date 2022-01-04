<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Iconic_WSB_Template.
 *
 * @class    Iconic_WSB_Template
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Template {

	/**
	 * Include template with arguments
	 *
	 * @param string $__template
	 * @param array $__variables
	 */
	public function include_template( $__template, array $__variables = [] ) {
		if ( $__template = $this->locate_template( $__template ) ) {
			extract( $__variables );
			include $__template;
		}
	}

	/**
	 * Render template with arguments. Return html
	 *
	 * @param string $template
	 * @param array $variables
	 *
	 * @return string
	 */
	public function render_template( $template, array $variables = [] ) {
		ob_start();
		$this->include_template( $template, $variables );
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}


	/**
	 * Locate path to template
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function locate_template( $template ) {

		$file = ICONIC_WSB_TPL_PATH . $template;

		// Allow override plugin frontend templates in theme
		if ( strpos( $template, 'frontend/' ) === 0 ) {

			$theme_template = str_replace( 'frontend/', '', $template );

			if ( $theme_override = locate_template( ICONIC_WSB_DIRNAME . '/' . $theme_template ) ) {
				$file = $theme_override;
			}
		}

		return apply_filters( 'iconic_wsb_locate_template', $file, $template );
	}
}