<?php

namespace PixelYourSite;

use URL;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


/**
 * Check if WPML plugin installed and activated.
 *
 * @return bool
 */
function isWPMLActive() {

    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' );
}
/**
 * Check if Pixel Cost of goods plugin installed and activated.
 *
 * @return bool
 */
function isPixelCogActive() {

	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	return is_plugin_active( 'pixel-cost-of-goods/pixel-cost-of-goods.php' );

}

function isPinterestActive( $checkCompatibility = true ) {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	$active = is_plugin_active( 'pixelyoursite-pinterest/pixelyoursite-pinterest.php' );
	
	if ( $checkCompatibility ) {
		return $active && ! isPinterestVersionIncompatible()
               && function_exists( 'PixelYourSite\Pinterest' )
               && Pinterest() instanceof Plugin; // false for dummy
	} else {
		return $active;
	}
	
}

function isPinterestVersionIncompatible() {
	
	if ( ! function_exists( 'get_plugin_data' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	$data = get_plugin_data( WP_PLUGIN_DIR . '/pixelyoursite-pinterest/pixelyoursite-pinterest.php', false, false );
	
	return ! version_compare( $data['Version'], PYS_PINTEREST_MIN_VERSION, '>=' );
	
}

function isSuperPackActive( $checkCompatibility = true  ) {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	$active = is_plugin_active( 'pixelyoursite-super-pack/pixelyoursite-super-pack.php' );
	
	if ( $checkCompatibility ) {
		return $active && function_exists( 'PixelYourSite\SuperPack' ) && ! isSuperPackVersionIncompatible();
	} else {
		return $active;
	}
	
}

function isSuperPackVersionIncompatible() {
	
	if ( ! function_exists( 'get_plugin_data' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	$data = get_plugin_data( WP_PLUGIN_DIR . '/pixelyoursite-super-pack/pixelyoursite-super-pack.php', false, false );
	
	return ! version_compare( $data['Version'], PYS_SUPER_PACK_MIN_VERSION, '>=' );
	
}

function isBingActive( $checkCompatibility = true ) {

    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    $active = is_plugin_active( 'pixelyoursite-bing/pixelyoursite-bing.php' );

    if ( $checkCompatibility ) {
        return $active && ! isBingVersionIncompatible()
            && function_exists( 'PixelYourSite\Bing' )
            && Bing() instanceof Plugin; // false for dummy
    } else {
        return $active;
    }

}

function isBingVersionIncompatible() {

    if ( ! function_exists( 'get_plugin_data' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    $data = get_plugin_data( WP_PLUGIN_DIR . '/pixelyoursite-bing/pixelyoursite-bing.php', false, false );

    return ! version_compare( $data['Version'], PYS_BING_MIN_VERSION, '>=' );

}

/**
 * Check if WooCommerce plugin installed and activated.
 *
 * @return bool
 */
function isWooCommerceActive() {
    return function_exists( 'WC' );
}

/**
 * Check if Easy Digital Downloads plugin installed and activated.
 *
 * @return bool
 */
function isEddActive() {
    return function_exists( 'EDD' );
}

/**
 * Check if Product Catalog Feed Pro plugin installed and activated.
 *
 * @return bool
 */
function isProductCatalogFeedProActive() {
	return class_exists( 'wpwoof_product_catalog' );
}

/**
 * Check if EDD Products Feed Pro plugin installed and activated.
 *
 * @return bool
 */
function isEddProductsFeedProActive() {
	return class_exists( 'Wpeddpcf_Product_Catalog' );
}

function isBoostActive() {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	return is_plugin_active( 'boost/boost.php' );
	
}

/**
 * Check if Smart OpenGraph plugin installed and activated.
 *
 * @return bool
 */
function isSmartOpenGraphActive() {
    
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    
    return is_plugin_active( 'smart-opengraph/catalog-plugin.php' );
    
}

function isVisualComposerActive() {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	return is_plugin_active( 'js_composer/js_composer.php' );
	
}

function isMagicRowActive() {
	
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	
	return is_plugin_active( 'magic-row/magic-row.php' );
	
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 *
 * @return string|array
 */
function deepSanitizeTextField( $var ) {

    if ( is_array( $var ) ) {
        return array_map( 'deepSanitizeTextField', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }

}

function getAvailableUserRoles() {
	
	$wp_roles   = new \WP_Roles();
	$user_roles = array();
	
	foreach ( $wp_roles->get_names() as $slug => $name ) {
		$user_roles[ $slug ] = $name;
	}
	
	return $user_roles;
	
}

/**
 * @param \WC_Product $product
 * @return array
 */
function getAvailableProductCog($product) {


    $cost_type = get_post_meta( $product->get_id(), '_pixel_cost_of_goods_cost_type', true );
    $product_cost = get_post_meta( $product->get_id(), '_pixel_cost_of_goods_cost_val', true );

    if(!$product_cost && $product->is_type("variation")) {
        $cost_type = get_post_meta( $product->get_parent_id(), '_pixel_cost_of_goods_cost_type', true );
        $product_cost = get_post_meta( $product->get_parent_id(), '_pixel_cost_of_goods_cost_val', true );
    }


	if ($product_cost) {
		$cog = array(
			'type' => $cost_type,
			'val' => $product_cost
		);
	} else {
        $cog_term_val = get_product_cost_by_cat( $product->get_id() );
        if ($cog_term_val) {
            $cog = array(
                'type' => get_product_type_by_cat( $product->get_id() ),
                'val' => $cog_term_val
            );
        } else {
            $cog = array(
                'type' => get_option( '_pixel_cost_of_goods_cost_type'),
                'val' => get_option( '_pixel_cost_of_goods_cost_val')
            );
        }
    }

	return $cog;

}

function getAvailableProductCogOrder($args) {

	$order_total = 0.0;
	$cost = 0;
	$notice = '';
	$custom_total = 0;
	$cat_isset = 0;
    $isWithoutTax = get_option( '_pixel_cog_tax_calculating')  == 'no';

    foreach ($args['products'] as $productData) {
        $order_total += (float)$productData['total'];
        if(!$isWithoutTax) {
            $order_total += (float)$productData['total_tax'];
        }

        $product_id = $productData['product_id'];
        $productObject = wc_get_product($product_id);
        if(!$productObject) continue;

        $cost_type = get_post_meta( $productObject->get_id(), '_pixel_cost_of_goods_cost_type', true );
        $product_cost = get_post_meta( $productObject->get_id(), '_pixel_cost_of_goods_cost_val', true );

        if(!$product_cost && $productObject->is_type("variation")) {
            $cost_type = get_post_meta( $productObject->get_parent_id(), '_pixel_cost_of_goods_cost_type', true );
            $product_cost = get_post_meta( $productObject->get_parent_id(), '_pixel_cost_of_goods_cost_val', true );
        }

        $args = array( 'qty'   => 1, 'price' => $productObject->get_price());
        $qlt = $productData['quantity'];

        if($isWithoutTax) {
            $price = wc_get_price_excluding_tax($productObject, $args);
        } else {
            $price = wc_get_price_including_tax($productObject,$args);
        }

        if ($product_cost) {
            $cost = ($cost_type == 'percent') ? $cost + ($price * ($product_cost / 100) * $qlt) : $cost + ($product_cost * $qlt);
            $custom_total = $custom_total + ($price * $qlt);
        } else {
            $product_cost = get_product_cost_by_cat( $product_id );
            $cost_type = get_product_type_by_cat( $product_id );
            if ($product_cost) {
                $cost = ($cost_type == 'percent') ? $cost + ($price * ($product_cost / 100) * $qlt) : $cost + ($product_cost * $qlt);
                $custom_total = $custom_total + ($price * $qlt);
                $notice = "Category Cost of Goods was used for some products.";
                $cat_isset = 1;
            } else {
                $product_cost = get_option( '_pixel_cost_of_goods_cost_val');
                $cost_type = get_option( '_pixel_cost_of_goods_cost_type' );
                if ($product_cost) {
                    $cost = ($cost_type == 'percent') ? (float) $cost + ((float) $price * ((float) $product_cost / 100) * $qlt) : (float) $cost + ((float) $product_cost * $qlt);
                    $custom_total = $custom_total + ($price * $qlt);
                    if ($cat_isset == 1) {
                        $notice = "Global and Category Cost of Goods was used for some products.";
                    } else {
                        $notice = "Global Cost of Goods was used for some products.";
                    }
                } else {
                    $notice = "Some products don't have Cost of Goods.";
                }
            }
        }
    }

    return $order_total - $cost;

}

function getAvailableProductCogCart() {
	$cart_total = 0.0;
	$cost = 0;
	$notice = '';
	$custom_total = 0;
	$cat_isset = 0;
    $isWithoutTax = get_option( '_pixel_cog_tax_calculating')  == 'no';

    $shipping = WC()->cart->get_shipping_total();
    $cart_total = WC()->cart->get_total('edit') - $shipping;

    if($isWithoutTax) {
        $cart_total -=  WC()->cart->get_total_tax();
    } else {
        $cart_total -= WC()->cart->get_shipping_tax();
    }

	foreach ( WC()->cart->cart_contents as $cart_item_key => $item ) {
		$product_id = ( isset( $item['variation_id'] ) && 0 != $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );

        $product = wc_get_product($product_id);

        $cost_type = get_post_meta( $product->get_id(), '_pixel_cost_of_goods_cost_type', true );
        $product_cost = get_post_meta( $product->get_id(), '_pixel_cost_of_goods_cost_val', true );

        if(!$product_cost && $product->is_type("variation")) {
            $cost_type = get_post_meta( $product->get_parent_id(), '_pixel_cost_of_goods_cost_type', true );
            $product_cost = get_post_meta( $product->get_parent_id(), '_pixel_cost_of_goods_cost_val', true );
        }

        $args = array( 'qty'   => 1, 'price' => $product->get_price());
        if($isWithoutTax) {
            $price = wc_get_price_excluding_tax($product, $args);
        } else {
            $price = wc_get_price_including_tax($product,$args);
        }
        $qlt = $item['quantity'];


		if ($product_cost) {
			$cost = ($cost_type == 'percent') ? $cost + ($price * ($product_cost / 100) * $qlt) : $cost + ($product_cost * $qlt);
			$custom_total = $custom_total + ($price * $qlt);
		} else {
			$product_cost = get_product_cost_by_cat( $product_id );
			$cost_type = get_product_type_by_cat( $product_id );
			if ($product_cost) {
				$cost = ($cost_type == 'percent') ? $cost + ($price * ($product_cost / 100) * $qlt) : $cost + ($product_cost * $qlt);
				$custom_total = $custom_total + ($price * $qlt);
				$notice = "Category Cost of Goods was used for some products.";
				$cat_isset = 1;
			} else {
				$product_cost = get_option( '_pixel_cost_of_goods_cost_val');
				$cost_type = get_option( '_pixel_cost_of_goods_cost_type' );
				if ($product_cost) {
					$cost = ($cost_type == 'percent') ? $cost + ((float) $price * ((float) $product_cost / 100) * $qlt) : (float) $cost + ((float) $product_cost * $qlt);
					$custom_total = $custom_total + ($price * $qlt);
					if ($cat_isset == 1) {
						$notice = "Global and Category Cost of Goods was used for some products.";
					} else {
						$notice = "Global Cost of Goods was used for some products.";
					}
				} else {
					$notice = "Some products don't have Cost of Goods.";
				}
			}
		}
	}

	return $cart_total - $cost;

}

/**
 * get_product_type_by_cat.
 *
 * @version 1.0.0
 * @since   1.0.0
 */
function get_product_type_by_cat( $product_id ) {
	$term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'ids'));
	$cost = array();
	foreach ($term_list as $term_id) {
		$cost[$term_id] = array(
			get_term_meta( $term_id, '_pixel_cost_of_goods_cost_val', true ),
			get_term_meta( $term_id, '_pixel_cost_of_goods_cost_type', true )
		);
	}
	if ( empty( $cost ) ) {
		return '';
	} else {
		asort( $cost );
		$max = end( $cost );
		return $max[1];
	}
}

/**
 * get_product_cost_by_cat.
 *
 * @version 1.0.0
 * @since   1.0.0
 */
function get_product_cost_by_cat( $product_id ) {
	$term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'ids'));
	$cost = array();
	foreach ($term_list as $term_id) {
		$cost[$term_id] = get_term_meta( $term_id, '_pixel_cost_of_goods_cost_val', true );
	}
	if ( empty( $cost ) ) {
		return '';
	} else {
		asort( $cost );
		$max = end( $cost );
		return $max;
	}
}

function isDisabledForCurrentRole() {

	$user = wp_get_current_user();
	$disabled_for = PYS()->getOption( 'do_not_track_user_roles' );

	foreach ( (array) $user->roles as $role ) {

		if ( in_array( $role, $disabled_for ) ) {

			add_action( 'wp_head', function() {
				echo "<script type='application/javascript'>console.warn('PixelYourSite is disabled for current user role.');</script>\r\n";
			} );

			return true;

		}

	}

	return false;

}



function getStandardParams() {
    global $post;
    $cpt = get_post_type();
    $params = array(
        'page_title' => "",
        'post_type' => $cpt,
        'post_id' => "",
        'plugin' => "PixelYourSite"
    );

    if(PYS()->getOption("enable_event_url_param")) {
        $url = getCurrentPageUrl(true);
        $params['event_url'] = $url;
    }
    if(PYS()->getOption("enable_user_role_param")) {
        $params['user_role'] = getUserRoles();
    }



    if(is_singular( 'post' )) {
        $params['page_title'] = $post->post_title;
        $params['post_id']   = $post->ID;

    } elseif( is_singular( 'page' ) || is_home()) {
        $params['post_type']    = 'page';
        $params['post_id']      = is_home() ? null : $post->ID;
        $params['page_title']   = is_home() == true ? get_bloginfo( 'name' ) : $post->post_title;

    } elseif (isWooCommerceActive() && is_shop()) {
        $page_id = (int) wc_get_page_id( 'shop' );
        $params['post_type'] = 'page';
        $params['post_id']   = $page_id;
        $params['page_title'] = get_the_title( $page_id );

    } elseif ( is_category() ) {
        $cat  = get_query_var( 'cat' );
        $term = get_category( $cat );
        $params['post_type']    = 'category';
        $params['post_id']      = $cat;
        $params['page_title'] = $term->name;

    } elseif ( is_tag() ) {
        $slug = get_query_var( 'tag' );
        $term = get_term_by( 'slug', $slug, 'post_tag' );
        $params['post_type']    = 'tag';
        if($term) {
            $params['post_id']      = $term->term_id;
            $params['page_title']   = $term->name;
        }


    } elseif (is_tax()) {
        $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
        $params['post_type'] = get_query_var( 'taxonomy' );
        if ( $term ) {
            $params['post_id']      = $term->term_id;
            $params['page_title'] = $term->name;
        }

    } elseif ((isWooCommerceActive() && $cpt == 'product') ||
        (isEddActive() && $cpt == 'download') ) {
        $params['page_title'] = $post->post_title;
        $params['post_id']   = $post->ID;

    } else if ($post instanceof \WP_Post) {
        $params['page_title'] = $post->post_title;
        $params['post_id']   = $post->ID;
    }


    if(!PYS()->getOption("enable_post_type_param")) {
        unset($params['post_type']);
    }
    if(!PYS()->getOption("enable_post_id_param")) {
        unset($params['post_id']);
    }

    if(isWcfStep()) {

        $step = getWcfCurrentStep();
        $flow_id = $step->get_flow_id();

        if(PYS()->getOption('wcf_global_cartflows_parameter_enabled'))
            $params["cartlows"] = "yes";

        if(PYS()->getOption('wcf_global_cartflows_flow_parameter_enabled'))
            $params["cartflows_flow"] = get_the_title($flow_id);

        if(PYS()->getOption('wcf_global_cartflows_step_parameter_enabled'))
            $params["cartflows_step"] = $step->get_step_type();
    }

    return $params;
}


/**
 * @param string $taxonomy Taxonomy name
 *
 * @return array Array of object term names
 */
function getObjectTerms( $taxonomy, $post_id ) {

	$terms   = get_the_terms( $post_id, $taxonomy );
	$results = array();

	if ( is_wp_error( $terms ) || empty ( $terms ) ) {
		return array();
	}

	// decode special chars
	foreach ( $terms as $term ) {
		$results[$term->term_id] = html_entity_decode( $term->name );
	}

	return $results;

}

/**
 * @param string $taxonomy Taxonomy name
 *
 * @return array Array of object term names and id
 */
function getObjectTermsWithId( $taxonomy, $post_id ) {

    $terms   = get_the_terms( $post_id, $taxonomy );
    $results = array();

    if ( is_wp_error( $terms ) || empty ( $terms ) ) {
        return array();
    }

    // decode special chars
    foreach ( $terms as $term ) {
        $results[] = [
            'name' => html_entity_decode( $term->name ),
            'id'   => $term->term_id
        ];
    }

    return $results;

}

/**
 * @param array  $params
 * @param string $key
 *
 * @return mixed
 */
function safeGetArrayValue( $params, $key, $fallback = null ) {
	return isset( $params[ $key ] ) ? $params[ $key ] : $fallback;
}

/**
 * Sanitize event name. Only letters, numbers and underscores allowed.
 *
 * @param string $name
 *
 * @return string
 */
function sanitizeKey( $name ) {

	$name = str_replace( ' ', '_', $name );
	$name = preg_replace( '/[^0-9a-zA-z_]/', '', $name );

	return $name;

}

function removeProtocolFromUrl( $url ) {

	if ( extension_loaded( 'mbstring' ) ) {

		$un = new URL\Normalizer();
		$un->setUrl( $url );
		$url = $un->normalize();

	}

	// remove fragment component
	$url_parts = parse_url( $url );
	if( isset( $url_parts['fragment'] ) ) {
		$url = preg_replace( '/#'. $url_parts['fragment'] . '$/', '', $url );
	}
	
	// remove scheme and www and current host if any
	$url = str_replace( array( 'http://', 'https://', 'http://www.', 'https://www.', 'www.' ), '', $url );
	$url = trim( $url );
	$url = ltrim( $url, '/' );
	// $url = rtrim( $url, '/' );

	return $url;

}

function getCurrentPageUrl($removeQuery = false) {
    if($removeQuery) {
        return $_SERVER['HTTP_HOST'] . str_replace("?".$_SERVER['QUERY_STRING'],"",$_SERVER['REQUEST_URI']);
    }
	return  $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ;
}

function startsWith( $haystack, $needle ) {
	// search backwards starting from haystack length characters from the end
	return $needle === "" || strrpos( $haystack, $needle, -strlen( $haystack ) ) !== false;
}

/**
 * Compare single URL or array of URLs with base URL. If base URL is not set, current page URL will be used.
 *
 * @param string|array $url
 * @param string       $base
 * @param string       $rule
 *
 * @return bool
 */
function compareURLs( $url, $base = '', $rule = 'match' ) {

	// use current page url if not set
	if ( empty( $base ) ) {
		$base = getCurrentPageUrl();
	}

	$base = removeProtocolFromUrl( $base );

	if ( is_string( $url ) ) {

		if ( empty( $url ) || '*' === $url ) {
			return true;
		}

		$url = rtrim( $url, '*' );  // for backward capability
		$url = removeProtocolFromUrl( $url );
        
        if ( ! $rule || $rule == 'match' ) {
            return $base == $url;
        }
        
        if ( $rule == 'contains' ) {
        
            if ( $base == $url ) {
                return true;
            }

            if(empty($base) || empty($url)) {
                return false;
            }

            if ( strpos( $base, $url ) !== false ) {
                return true;
            }
            
            return false;
            
        }

        return false;
        
	} else {

		// recursively compare each url
		foreach ( $url as $single_url ) {

			if ( compareURLs( $single_url['value'], $base, $single_url['rule'] ) ) {
				return true;
			}

		}

		return false;

	}

}

/**
 * Add attribute with value to a HTML tag.
 *
 * @param string $attr_name  Attribute name, eg. "class"
 * @param string $attr_value Attribute value
 * @param string $content    HTML content where attribute should be inserted
 * @param bool   $overwrite  Override existing value of attribute or append it
 * @param string $tag        Selector name, eg. "button". Default "a"
 *
 * @return string Modified HTML content
 */
function insertTagAttribute( $attr_name, $attr_value, $content, $overwrite = false, $tag = 'a' ) {

	// do not modify js attributes
	if ( $attr_name == 'on' ) {
		return $content;
	}

	$attr_value = trim( $attr_value );

	try {

		$doc = new \DOMDocument();

		// old libxml does not support options parameter
		if ( defined( 'LIBXML_DOTTED_VERSION' ) && version_compare( LIBXML_DOTTED_VERSION, '2.6.0', '>=' ) &&
		     version_compare( phpversion(), '5.4.0', '>=' )
		) {
			@$doc->loadHTML( '<?xml encoding="UTF-8">' . $content, LIBXML_NOEMPTYTAG );
		} else {
			@$doc->loadHTML( '<?xml encoding="UTF-8">' . $content );
		}

		// select top-level tag if it is not specified in args
		if ( $tag == 'any' ) {

			/** @var \DOMNodeList $node */
			$node = $doc->getElementsByTagName( 'body' );

			if ( $node->length == 0 ) {
				throw new \Exception( 'Empty or wrong tag passed to filter.' );
			}

			$node = $node->item( 0 )->childNodes->item( 0 );

		} else {
			$node = $doc->getElementsByTagName( $tag )->item( 0 );
		}

		if ( is_null( $node ) ) {
			return $content;
		}

		/** @noinspection PhpUndefinedMethodInspection */
		$attribute = $node->getAttribute( $attr_name );

		// add attribute or override old one
		if ( empty( $attribute ) || $overwrite ) {

			/** @noinspection PhpUndefinedMethodInspection */
			$node->setAttribute( $attr_name, $attr_value );

			return str_replace( array( '<?xml encoding="UTF-8">', '<html>', '</html>', '<body>', '</body>' ), null, $doc->saveHTML() );

		}

		// append value to exist attribute
		if ( $overwrite == false ) {
            if(strpos($attribute,$attr_value) !== false) {
                return $content;
            }
			$value = $attribute . ',' . $attr_value;
			/** @noinspection PhpUndefinedMethodInspection */
			$node->setAttribute( $attr_name, $value );

			return str_replace( array( '<?xml encoding="UTF-8">', '<html>', '</html>', '<body>', '</body>' ), null, $doc->saveHTML() );

		}

	} catch ( \Exception $e ) {
		error_log( $e );
	}

	return $content;

}
function getUserRoles() {
    $user = wp_get_current_user();

    if ( $user->ID !== 0 ) {
        $user_roles = implode( ',', $user->roles );
    } else {
        $user_roles = 'guest';
    }
    return $user_roles;
}

function getUtms () {
    $utm = array();

    $utmTerms = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
    foreach ($utmTerms as $utmTerm) {
        if(isset($_GET[$utmTerm])) {
            $utm[$utmTerm] = filterEmails($_GET[$utmTerm]);
        } elseif (isset($_COOKIE["pys_".$utmTerm])) {
            $utm[$utmTerm] =filterEmails( $_COOKIE["pys_".$utmTerm]);
        } else {
          //  $utm[$utmTerm] = "undefined";
        }
    }

    return $utm;
}

function filterEmails($value) {
    return validateEmail($value) ? "undefined" : $value;
}

function validateEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function getTrafficSource () {
    $referrer = "";
    $source = "";
    try {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referrer = $_SERVER['HTTP_REFERER'];
        }

        $direct = empty($referrer);
        $internal = $direct ? false : strpos(site_url(), $referrer) == 0;
        $external = !$direct && !$internal;
        $cookie = !isset($_COOKIE['pysTrafficSource']) ? false : $_COOKIE['pysTrafficSource'];

        if (!$external) {
            $source = $cookie ? $cookie : 'direct';
        } else {
            $source = $cookie && $cookie === $referrer ? $cookie : $referrer;
        }

        if ($source !== 'direct') {
            $parse = parse_url($source);

            if(isset($parse['host'])) {
                return $parse['host'];// leave only domain (Issue #70)
            } else {
                return "direct";
            }
        } else {
            return $source;
        }
    } catch (\Exception $e) {
        return "direct";
    }
}

function sanitizeParams( $params ) {
	
	$sanitized = array();

	foreach ( $params as $key => $value ) {

		// skip empty (but not zero)
		if ( ! isset( $value )  ||
            (is_string($value) && $value == "") ||
            (is_array($value) && count($value) == 0)
        ) {
			continue;
		}
        
		$key = sanitizeKey( $key );

		if ( is_array( $value ) ) {
			$sanitized[ $key ] = sanitizeParams( $value );
		} elseif ( $key == 'value' ) {
			$sanitized[ $key ] = (float) $value; // do not encode value to avoid error messages on Pinterest
		} elseif ( is_bool( $value ) ) {
			$sanitized[ $key ] = (bool) $value;
		} elseif (is_numeric($value)) {
            $sanitized[ $key ] = $value;
        } else {
			$sanitized[ $key ] = stripslashes(html_entity_decode( $value ));
		}



	}

	return $sanitized;

}

/**
 * Checks if specified event enabled at least for one configured pixel
 *
 * @param string $eventName
 *
 * @return bool
 */
function isEventEnabled( $eventName ) {

	foreach ( PYS()->getRegisteredPixels() as $pixel ) {
		/** @var Pixel|Settings $pixel */
		
		if ( $pixel->configured() && $pixel->getOption( $eventName ) ) {
			return true;
		}

	}

	return false;

}

function pys_round( $val, $precision = 2, $mode = PHP_ROUND_HALF_UP )  {
    if ( ! is_numeric( $val ) ) {
        $val = floatval( $val );
    }
    return round( $val, $precision, $mode );
}

/**
 * Currency symbols
 *
 * @return array
 * */

function getPysCurrencySymbols() {
    return array(
        'AED' => '&#x62f;.&#x625;',
        'AFN' => '&#x60b;',
        'ALL' => 'L',
        'AMD' => 'AMD',
        'ANG' => '&fnof;',
        'AOA' => 'Kz',
        'ARS' => '&#36;',
        'AUD' => '&#36;',
        'AWG' => 'Afl.',
        'AZN' => 'AZN',
        'BAM' => 'KM',
        'BBD' => '&#36;',
        'BDT' => '&#2547;&nbsp;',
        'BGN' => '&#1083;&#1074;.',
        'BHD' => '.&#x62f;.&#x628;',
        'BIF' => 'Fr',
        'BMD' => '&#36;',
        'BND' => '&#36;',
        'BOB' => 'Bs.',
        'BRL' => '&#82;&#36;',
        'BSD' => '&#36;',
        'BTC' => '&#3647;',
        'BTN' => 'Nu.',
        'BWP' => 'P',
        'BYR' => 'Br',
        'BYN' => 'Br',
        'BZD' => '&#36;',
        'CAD' => '&#36;',
        'CDF' => 'Fr',
        'CHF' => '&#67;&#72;&#70;',
        'CLP' => '&#36;',
        'CNY' => '&yen;',
        'COP' => '&#36;',
        'CRC' => '&#x20a1;',
        'CUC' => '&#36;',
        'CUP' => '&#36;',
        'CVE' => '&#36;',
        'CZK' => '&#75;&#269;',
        'DJF' => 'Fr',
        'DKK' => 'DKK',
        'DOP' => 'RD&#36;',
        'DZD' => '&#x62f;.&#x62c;',
        'EGP' => 'EGP',
        'ERN' => 'Nfk',
        'ETB' => 'Br',
        'EUR' => '&euro;',
        'FJD' => '&#36;',
        'FKP' => '&pound;',
        'GBP' => '&pound;',
        'GEL' => '&#x20be;',
        'GGP' => '&pound;',
        'GHS' => '&#x20b5;',
        'GIP' => '&pound;',
        'GMD' => 'D',
        'GNF' => 'Fr',
        'GTQ' => 'Q',
        'GYD' => '&#36;',
        'HKD' => '&#36;',
        'HNL' => 'L',
        'HRK' => 'kn',
        'HTG' => 'G',
        'HUF' => '&#70;&#116;',
        'IDR' => 'Rp',
        'ILS' => '&#8362;',
        'IMP' => '&pound;',
        'INR' => '&#8377;',
        'IQD' => '&#x639;.&#x62f;',
        'IRR' => '&#xfdfc;',
        'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
        'ISK' => 'kr.',
        'JEP' => '&pound;',
        'JMD' => '&#36;',
        'JOD' => '&#x62f;.&#x627;',
        'JPY' => '&yen;',
        'KES' => 'KSh',
        'KGS' => '&#x441;&#x43e;&#x43c;',
        'KHR' => '&#x17db;',
        'KMF' => 'Fr',
        'KPW' => '&#x20a9;',
        'KRW' => '&#8361;',
        'KWD' => '&#x62f;.&#x643;',
        'KYD' => '&#36;',
        'KZT' => 'KZT',
        'LAK' => '&#8365;',
        'LBP' => '&#x644;.&#x644;',
        'LKR' => '&#xdbb;&#xdd4;',
        'LRD' => '&#36;',
        'LSL' => 'L',
        'LYD' => '&#x644;.&#x62f;',
        'MAD' => '&#x62f;.&#x645;.',
        'MDL' => 'MDL',
        'MGA' => 'Ar',
        'MKD' => '&#x434;&#x435;&#x43d;',
        'MMK' => 'Ks',
        'MNT' => '&#x20ae;',
        'MOP' => 'P',
        'MRU' => 'UM',
        'MUR' => '&#x20a8;',
        'MVR' => '.&#x783;',
        'MWK' => 'MK',
        'MXN' => '&#36;',
        'MYR' => '&#82;&#77;',
        'MZN' => 'MT',
        'NAD' => 'N&#36;',
        'NGN' => '&#8358;',
        'NIO' => 'C&#36;',
        'NOK' => '&#107;&#114;',
        'NPR' => '&#8360;',
        'NZD' => '&#36;',
        'OMR' => '&#x631;.&#x639;.',
        'PAB' => 'B/.',
        'PEN' => 'S/',
        'PGK' => 'K',
        'PHP' => '&#8369;',
        'PKR' => '&#8360;',
        'PLN' => '&#122;&#322;',
        'PRB' => '&#x440;.',
        'PYG' => '&#8370;',
        'QAR' => '&#x631;.&#x642;',
        'RMB' => '&yen;',
        'RON' => 'lei',
        'RSD' => '&#x434;&#x438;&#x43d;.',
        'RUB' => '&#8381;',
        'RWF' => 'Fr',
        'SAR' => '&#x631;.&#x633;',
        'SBD' => '&#36;',
        'SCR' => '&#x20a8;',
        'SDG' => '&#x62c;.&#x633;.',
        'SEK' => '&#107;&#114;',
        'SGD' => '&#36;',
        'SHP' => '&pound;',
        'SLL' => 'Le',
        'SOS' => 'Sh',
        'SRD' => '&#36;',
        'SSP' => '&pound;',
        'STN' => 'Db',
        'SYP' => '&#x644;.&#x633;',
        'SZL' => 'L',
        'THB' => '&#3647;',
        'TJS' => '&#x405;&#x41c;',
        'TMT' => 'm',
        'TND' => '&#x62f;.&#x62a;',
        'TOP' => 'T&#36;',
        'TRY' => '&#8378;',
        'TTD' => '&#36;',
        'TWD' => '&#78;&#84;&#36;',
        'TZS' => 'Sh',
        'UAH' => '&#8372;',
        'UGX' => 'UGX',
        'USD' => '&#36;',
        'UYU' => '&#36;',
        'UZS' => 'UZS',
        'VEF' => 'Bs F',
        'VES' => 'Bs.S',
        'VND' => '&#8363;',
        'VUV' => 'Vt',
        'WST' => 'T',
        'XAF' => 'CFA',
        'XCD' => '&#36;',
        'XOF' => 'CFA',
        'XPF' => 'Fr',
        'YER' => '&#xfdfc;',
        'ZAR' => '&#82;',
        'ZMW' => 'ZK',
    );
}