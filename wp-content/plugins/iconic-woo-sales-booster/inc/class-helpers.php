<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'Iconic_WSB_Helpers' ) ) {
	return;
}

/**
 * Iconic_WSB_Helpers.
 *
 * @class    Iconic_WSB_Helpers
 * @version  1.0.0
 * @category Class
 * @author   Iconic
 */
class Iconic_WSB_Helpers {
	
	/**
     * uses wc_dropdown_variation_attribute_options() to show the atributes dropdown
	 * If the product is a variation then adds 'wsb_select_readonly' class for those
	 * attributes which don't have 'any' value. On frontend JS code will make
	 * '.wsb_select_readonly' readonly
     *
     * @param array $args Arguments same as wc_dropdown_variation_attribute_options()
     */
    public static function wc_dropdown_variation_attribute_options( $args = array() ) {
		
		if( $args['product']->is_type("variable") ) {
			wc_dropdown_variation_attribute_options( $args );
		}
		else if( $args['product']->is_type("variation") ) {
			
			$product         = $args['product'];
			$parent          = wc_get_product( $args['product']->get_parent_id() ); 
			$attribute       = $product->get_attribute( $args['attribute'] );
			$args['product'] = $parent;
			
			//if attribute is preselected then 1) hide the dropdown 2) only show the label
			if( $attribute !== "" ) {
				$args['class'] .= " wsb_select_readonly ";
				echo "<div style='display:none'>";
				wc_dropdown_variation_attribute_options( $args );
				echo "</div>";
				echo "<span class='iconic-wsb-variation__select_replace_label'>$attribute</span>";
			}
			else {
				wc_dropdown_variation_attribute_options( $args );
			}
				
		}
	}

}