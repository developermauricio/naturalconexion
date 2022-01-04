<?php
/**
 * CSS files.
 *
 * @version 1.0
 * @package xts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

return array(
	// Widgets.
	'widget-calendar'                   => array(
		array(
			'title' => esc_html__( 'Widget calendar', 'woodmart' ),
			'name'  => 'widget-calendar',
			'file'  => '/css/parts/widget-calendar',
			'rtl'   => true,
		),
	),
	'widget-rss'                        => array(
		array(
			'title' => esc_html__( 'Widget rss', 'woodmart' ),
			'name'  => 'widget-rss',
			'file'  => '/css/parts/widget-rss',
		),
	),
	'widget-tag-cloud'                  => array(
		array(
			'title' => esc_html__( 'Widget tag cloud', 'woodmart' ),
			'name'  => 'widget-tag-cloud',
			'file'  => '/css/parts/widget-tag-cloud',
		),
	),
	'widget-recent-post-comments'       => array(
		array(
			'title' => esc_html__( 'Widget recent post or comments', 'woodmart' ),
			'name'  => 'widget-recent-post-comments',
			'file'  => '/css/parts/widget-recent-post-comments',
			'rtl'   => true,
		),
	),
	'widget-wd-recent-posts'            => array(
		array(
			'title' => esc_html__( '[Woodmart] Widget recent post', 'woodmart' ),
			'name'  => 'widget-wd-recent-posts',
			'file'  => '/css/parts/widget-wd-recent-posts',
			'rtl'   => true,
		),
	),
	'widget-nav-mega-menu'              => array(
		array(
			'title' => esc_html__( '[Woodmart] Widget mega menu', 'woodmart' ),
			'name'  => 'widget-nav-mega-menu',
			'file'  => '/css/parts/widget-nav-mega-menu',
		),
	),
	'widget-nav'                        => array(
		array(
			'title' => esc_html__( '[Woodmart] Widget navigation', 'woodmart' ),
			'name'  => 'widget-nav',
			'file'  => '/css/parts/widget-nav',
			'rtl'   => true,
		),
	),
	'widget-wd-layered-nav-product-cat' => array(
		array(
			'title' => esc_html__( '[Woodmart] Widget layered navigation & product categories', 'woodmart' ),
			'name'  => 'widget-wd-layered-nav-product-cat',
			'file'  => '/css/parts/woo-widget-wd-layered-nav-product-cat',
			'rtl'   => true,
		),
	),
	'widget-layered-nav-stock-status'   => array(
		array(
			'title' => esc_html__( 'Widget layered navigation & stock status', 'woodmart' ),
			'name'  => 'widget-layered-nav-stock-status',
			'file'  => '/css/parts/woo-widget-layered-nav-stock-status',
			'rtl'   => true,
		),
	),
	'widget-active-filters'             => array(
		array(
			'title' => esc_html__( 'Widget active filters', 'woodmart' ),
			'name'  => 'widget-active-filters',
			'file'  => '/css/parts/woo-widget-active-filters',
			'rtl'   => true,
		),
	),
	'widget-price-filter'               => array(
		array(
			'title' => esc_html__( '[Woodmart] Widget price filter', 'woodmart' ),
			'name'  => 'widget-price-filter',
			'file'  => '/css/parts/woo-widget-price-filter',
		),
	),
	'widget-product-list'               => array(
		array(
			'title' => esc_html__( 'Widget products', 'woodmart' ),
			'name'  => 'widget-product-list',
			'file'  => '/css/parts/woo-widget-product-list',
			'rtl'   => true,
		),
	),
	'widget-product-upsells'            => array(
		array(
			'title' => esc_html__( 'Widget products upsells', 'woodmart' ),
			'name'  => 'widget-product-upsells',
			'file'  => '/css/parts/woo-widget-upsells',
		),
	),
	'widget-shopping-cart'              => array(
		array(
			'title' => esc_html__( 'Widget shopping cart', 'woodmart' ),
			'name'  => 'widget-shopping-cart',
			'file'  => '/css/parts/woo-widget-shopping-cart',
			'rtl'   => true,
		),
	),
	'widget-slider-price-filter'        => array(
		array(
			'title' => esc_html__( 'Widget price filter slider', 'woodmart' ),
			'name'  => 'widget-slider-price-filter',
			'file'  => '/css/parts/woo-widget-slider-price-filter',
			'rtl'   => true,
		),
	),
	'widget-user-panel'                 => array(
		array(
			'title' => esc_html__( 'Widget user panel', 'woodmart' ),
			'name'  => 'widget-user-panel',
			'file'  => '/css/parts/woo-widget-user-panel',
			'rtl'   => true,
		),
	),
	'widget-woo-other'                  => array(
		array(
			'title' => esc_html__( 'Widget woocommerce other', 'woodmart' ),
			'name'  => 'widget-woo-other',
			'file'  => '/css/parts/woo-widget-other',
		),
	),
	// Blog.
	'blog-loop-base'                    => array(
		array(
			'title' => esc_html__( 'Blog loop base', 'woodmart' ),
			'name'  => 'blog-loop-base',
			'file'  => '/css/parts/blog-loop-base',
			'rtl'   => true,
		),
	),
	'blog-loop-base-old'                => array(
		array(
			'title' => esc_html__( 'Blog loop base old', 'woodmart' ),
			'name'  => 'blog-loop-base-old',
			'file'  => '/css/parts/blog-loop-base-old',
			'rtl'   => true,
		),
	),
	'blog-loop-design-meta-image'       => array(
		array(
			'title' => esc_html__( 'Blog loop design meta image', 'woodmart' ),
			'name'  => 'blog-loop-design-meta-image',
			'file'  => '/css/parts/blog-loop-design-meta-image',
			'rtl'   => true,
		),
	),
	'blog-loop-design-default'          => array(
		array(
			'title' => esc_html__( 'Blog loop design default', 'woodmart' ),
			'name'  => 'blog-loop-design-default',
			'file'  => '/css/parts/blog-loop-design-default',
		),
	),
	'blog-loop-design-default-alt'      => array(
		array(
			'title' => esc_html__( 'Blog loop design default alternative', 'woodmart' ),
			'name'  => 'blog-loop-design-default-alt',
			'file'  => '/css/parts/blog-loop-design-default-alt',
			'rtl'   => true,
		),
	),
	'blog-loop-design-small-img-chess'  => array(
		array(
			'title' => esc_html__( 'Blog loop design small images & chess', 'woodmart' ),
			'name'  => 'blog-loop-design-small-img-chess',
			'file'  => '/css/parts/blog-loop-design-smallimg-chess',
			'rtl'   => true,
		),
	),
	'blog-loop-design-mask'             => array(
		array(
			'title' => esc_html__( 'Blog loop design mask', 'woodmart' ),
			'name'  => 'blog-loop-design-mask',
			'file'  => '/css/parts/blog-loop-design-mask',
			'rtl'   => true,
		),
	),
	'blog-loop-design-masonry'          => array(
		array(
			'title' => esc_html__( 'Blog loop design masonry', 'woodmart' ),
			'name'  => 'blog-loop-design-masonry',
			'file'  => '/css/parts/blog-loop-design-masonry',
			'rtl'   => true,
		),
	),
	'blog-single-base'                  => array(
		array(
			'title' => esc_html__( 'Blog single', 'woodmart' ),
			'name'  => 'blog-single-base',
			'file'  => '/css/parts/blog-single-base',
			'rtl'   => true,
		),
	),
	// Modules.
	'animations'                        => array(
		array(
			'title' => esc_html__( 'Animations module', 'woodmart' ),
			'name'  => 'animations',
			'file'  => '/css/parts/mod-animations',
		),
	),
	'notices-fixed'                     => array(
		array(
			'title' => esc_html__( 'Sticky notifications module', 'woodmart' ),
			'name'  => 'notices-fixed',
			'file'  => '/css/parts/mod-notices-fixed',
			'rtl'   => true,
		),
	),
	'page-navigation'                   => array(
		array(
			'title' => esc_html__( 'Page navigation', 'woodmart' ),
			'name'  => 'page-navigation',
			'file'  => '/css/parts/mod-page-navigation',
			'rtl'   => true,
		),
	),
	'load-more-button'                  => array(
		array(
			'title' => esc_html__( 'Load more button', 'woodmart' ),
			'name'  => 'load-more-button',
			'file'  => '/css/parts/mod-load-more-button',
			'rtl'   => true,
		),
	),
	'sticky-loader'                     => array(
		array(
			'title' => esc_html__( 'Sticky loader', 'woodmart' ),
			'name'  => 'sticky-loader',
			'file'  => '/css/parts/mod-sticky-loader',
		),
	),
	// Footer.
	'footer-base'                       => array(
		array(
			'title' => esc_html__( 'Footer base', 'woodmart' ),
			'name'  => 'footer-base',
			'file'  => '/css/parts/footer-base',
			'rtl'   => true,
		),
	),
	'footer-widget-collapse'            => array(
		array(
			'title' => esc_html__( 'Footer widget collapse', 'woodmart' ),
			'name'  => 'footer-widget-collapse',
			'file'  => '/css/parts/footer-widget-collapse',
			'rtl'   => true,
		),
	),
	'footer-sticky'                     => array(
		array(
			'title' => esc_html__( 'Footer sticky', 'woodmart' ),
			'name'  => 'footer-sticky',
			'file'  => '/css/parts/footer-sticky',
		),
	),
	// Header.
	'header-base'                       => array(
		array(
			'title' => esc_html__( 'Header base', 'woodmart' ),
			'name'  => 'header-base',
			'file'  => '/css/parts/header-base',
			'rtl'   => true,
		),
		array(
			'title' => esc_html__( 'Module tools', 'woodmart' ),
			'name'  => 'mod-tools',
			'file'  => '/css/parts/mod-tools',
			'rtl'   => true,
		),
	),
	'header-boxed'                      => array(
		array(
			'title' => esc_html__( 'Header boxed', 'woodmart' ),
			'name'  => 'header-boxed',
			'file'  => '/css/parts/header-boxed',
		),
	),
	'header-elements-base'              => array(
		array(
			'title' => esc_html__( 'Header base elements', 'woodmart' ),
			'name'  => 'header-elements-base',
			'file'  => '/css/parts/header-el-base',
			'rtl'   => true,
		),
	),
	'header-fullscreen-menu'            => array(
		array(
			'title' => esc_html__( 'Header fullscreen menu', 'woodmart' ),
			'name'  => 'fullscreen-menu',
			'file'  => '/css/parts/header-el-fullscreen-menu',
			'rtl'   => true,
		),
	),
	'header-categories-nav'             => array(
		array(
			'title' => esc_html__( 'Header category navigation', 'woodmart' ),
			'name'  => 'header-categories-nav',
			'file'  => '/css/parts/header-el-category-nav',
			'rtl'   => true,
		),
	),
	'header-my-account'                 => array(
		array(
			'title' => esc_html__( 'Header my account', 'woodmart' ),
			'name'  => 'header-my-account',
			'file'  => '/css/parts/header-el-my-account',
			'rtl'   => true,
		),
	),
	'header-my-account-dropdown'        => array(
		array(
			'title' => esc_html__( 'Header my account dropdown', 'woodmart' ),
			'name'  => 'header-my-account-dropdown',
			'file'  => '/css/parts/header-el-my-account-dropdown',
			'rtl'   => true,
		),
	),
	'header-my-account-sidebar'         => array(
		array(
			'title' => esc_html__( 'Header my account sidebar', 'woodmart' ),
			'name'  => 'header-my-account-sidebar',
			'file'  => '/css/parts/header-el-my-account-sidebar',
			'rtl'   => true,
		),
	),
	'header-search'                     => array(
		array(
			'title' => esc_html__( 'Header search', 'woodmart' ),
			'name'  => 'header-search',
			'file'  => '/css/parts/header-el-search',
			'rtl'   => true,
		),
	),
	'header-search-form'                => array(
		array(
			'title' => esc_html__( 'Header search form', 'woodmart' ),
			'name'  => 'header-search-form',
			'file'  => '/css/parts/header-el-search-form',
			'rtl'   => true,
		),
	),
	'header-search-fullscreen'          => array(
		array(
			'title' => esc_html__( 'Header search fullscreen', 'woodmart' ),
			'name'  => 'header-search-fullscreen',
			'file'  => '/css/parts/header-el-search-fullscreen',
			'rtl'   => true,
		),
	),
	'header-cart'                       => array(
		array(
			'title' => esc_html__( 'Header cart', 'woodmart' ),
			'name'  => 'header-cart',
			'file'  => '/css/parts/header-el-cart',
			'rtl'   => true,
		),
	),
	'header-cart-design-3'              => array(
		array(
			'title' => esc_html__( 'Header cart design 3', 'woodmart' ),
			'name'  => 'header-cart-design-3',
			'file'  => '/css/parts/header-el-cart-design-3',
			'rtl'   => true,
		),
	),
	'header-cart-side'                  => array(
		array(
			'title' => esc_html__( 'Header cart-side', 'woodmart' ),
			'name'  => 'header-cart-side',
			'file'  => '/css/parts/header-el-cart-side',
			'rtl'   => true,
		),
	),
	// Layouts.
	'layout-wrapper-boxed'              => array(
		array(
			'title' => esc_html__( 'Layout wrapper boxed', 'woodmart' ),
			'name'  => 'layout-wrapper-boxed',
			'file'  => '/css/parts/layout-wrapper-boxed',
			'rtl'   => true,
		),
	),
	// Woocommerce options.
	'bordered-product'                  => array(
		array(
			'title' => esc_html__( 'Bordered product', 'woodmart' ),
			'name'  => 'bordered-product',
			'file'  => '/css/parts/woo-opt-bordered-product',
			'rtl'   => true,
		),
	),
	'shop-filter-area'                  => array(
		array(
			'title' => esc_html__( 'Shop filter area', 'woodmart' ),
			'name'  => 'shop-filter-area',
			'file'  => '/css/parts/woo-opt-shop-filter-area',
		),
	),
	'shop-title-categories'             => array(
		array(
			'title' => esc_html__( 'Shop page title categories', 'woodmart' ),
			'name'  => 'shop-title-categories',
			'file'  => '/css/parts/woo-opt-shop-title-categories',
			'rtl'   => true,
		),
	),
	// Woocommerce.
	'colorbox-popup'                    => array(
		array(
			'title' => esc_html__( 'Color box popup library', 'woodmart' ),
			'name'  => 'colorbox-popup',
			'file'  => '/css/parts/woo-lib-colorbox-popup',
		),
	),
	'woocommerce-base'                  => array(
		array(
			'title' => esc_html__( 'WooCommerce base', 'woodmart' ),
			'name'  => 'woocommerce-base',
			'file'  => '/css/parts/woocommerce-base',
			'rtl'   => true,
		),
	),
	'brands'                            => array(
		array(
			'title' => esc_html__( 'Brands element', 'woodmart' ),
			'name'  => 'brands',
			'file'  => '/css/parts/el-brand',
			'rtl'   => true,
		),
	),
	'product-tabs'                      => array(
		array(
			'title' => esc_html__( 'Product tabs element', 'woodmart' ),
			'name'  => 'product-tabs',
			'file'  => '/css/parts/el-product-tabs',
			'rtl'   => true,
		),
	),
	'slick'                             => array(
		array(
			'title' => esc_html__( 'Slick slider library', 'woodmart' ),
			'name'  => 'slick',
			'file'  => '/css/parts/woo-lib-slick-slider',
		),
	),
	'add-to-cart-popup'                 => array(
		array(
			'title' => esc_html__( 'Add to cart popup option', 'woodmart' ),
			'name'  => 'add-to-cart-popup',
			'file'  => '/css/parts/woo-opt-add-to-cart-popup',
		),
	),
	'size-guide'                        => array(
		array(
			'title' => esc_html__( 'Size guide', 'woodmart' ),
			'name'  => 'size-guide',
			'file'  => '/css/parts/woo-opt-size-guide',
		),
	),
	'social-login'                      => array(
		array(
			'title' => esc_html__( 'Social login', 'woodmart' ),
			'name'  => 'social-login',
			'file'  => '/css/parts/woo-opt-social-login',
			'rtl'   => true,
		),
	),
	'sticky-add-to-cart'                => array(
		array(
			'title' => esc_html__( 'Sticky add to cart', 'woodmart' ),
			'name'  => 'sticky-add-to-cart',
			'file'  => '/css/parts/woo-opt-sticky-add-to-cart',
			'rtl'   => true,
		),
	),
	'page-cart'                         => array(
		array(
			'title' => esc_html__( 'Cart page', 'woodmart' ),
			'name'  => 'page-cart',
			'file'  => '/css/parts/woo-page-cart',
			'rtl'   => true,
		),
	),
	'page-checkout'                     => array(
		array(
			'title' => esc_html__( 'Checkout page', 'woodmart' ),
			'name'  => 'page-checkout',
			'file'  => '/css/parts/woo-page-checkout',
			'rtl'   => true,
		),
	),
	'page-compare'                      => array(
		array(
			'title' => esc_html__( 'Compare page', 'woodmart' ),
			'name'  => 'page-compare',
			'file'  => '/css/parts/woo-page-compare',
			'rtl'   => true,
		),
	),
	'page-wishlist'                     => array(
		array(
			'title' => esc_html__( 'Wishlist page', 'woodmart' ),
			'name'  => 'page-wishlist',
			'file'  => '/css/parts/woo-page-wishlist',
			'rtl'   => true,
		),
	),
	'page-my-account'                   => array(
		array(
			'title' => esc_html__( 'My account page', 'woodmart' ),
			'name'  => 'page-my-account',
			'file'  => '/css/parts/woo-page-my-account',
			'rtl'   => true,
		),
	),
	'page-shop'                         => array(
		array(
			'title' => esc_html__( 'Shop page', 'woodmart' ),
			'name'  => 'page-shop',
			'file'  => '/css/parts/woo-page-shop',
			'rtl'   => true,
		),
	),
	'page-single-product'               => array(
		array(
			'title' => esc_html__( 'Single product page', 'woodmart' ),
			'name'  => 'page-single-product',
			'file'  => '/css/parts/woo-page-single-product',
			'rtl'   => true,
		),
	),
	'product-loop'                      => array(
		array(
			'title' => esc_html__( 'Product loop', 'woodmart' ),
			'name'  => 'product-loop',
			'file'  => '/css/parts/woo-product-loop',
			'rtl'   => true,
		),
	),
	'product-loop-button-info-alt'      => array(
		array(
			'title' => esc_html__( 'Product loop "Standard button" & "Full info on hover"', 'woodmart' ),
			'name'  => 'product-loop-button-info-alt',
			'file'  => '/css/parts/woo-product-loop-button-info-alt',
			'rtl'   => true,
		),
	),
	'product-loop-info'                 => array(
		array(
			'title' => esc_html__( 'Product loop "Full info on image"', 'woodmart' ),
			'name'  => 'product-loop-info',
			'file'  => '/css/parts/woo-product-loop-info',
			'rtl'   => true,
		),
	),
	'product-loop-alt'                  => array(
		array(
			'title' => esc_html__( 'Product loop "Icons and add to cart on hover"', 'woodmart' ),
			'name'  => 'product-loop-alt',
			'file'  => '/css/parts/woo-product-loop-alt',
			'rtl'   => true,
		),
	),
	'product-loop-icons'                => array(
		array(
			'title' => esc_html__( 'Product loop "Icons on hover"', 'woodmart' ),
			'name'  => 'product-loop-icons',
			'file'  => '/css/parts/woo-product-loop-icons',
		),
	),
	'product-loop-quick'                => array(
		array(
			'title' => esc_html__( 'Product loop "Quick"', 'woodmart' ),
			'name'  => 'product-loop-quick',
			'file'  => '/css/parts/woo-product-loop-quick',
			'rtl'   => true,
		),
	),
	'product-loop-base'                 => array(
		array(
			'title' => esc_html__( 'Product loop "Show summary on hover"', 'woodmart' ),
			'name'  => 'product-loop-base',
			'file'  => '/css/parts/woo-product-loop-base',
			'rtl'   => true,
		),
	),
	'product-loop-standard'             => array(
		array(
			'title' => esc_html__( 'Product loop "Standard button"', 'woodmart' ),
			'name'  => 'product-loop-standard',
			'file'  => '/css/parts/woo-product-loop-standard',
			'rtl'   => true,
		),
	),
	'product-loop-tiled'                => array(
		array(
			'title' => esc_html__( 'Product loop "Tiled"', 'woodmart' ),
			'name'  => 'product-loop-tiled',
			'file'  => '/css/parts/woo-product-loop-tiled',
			'rtl'   => true,
		),
	),
	'product-loop-list'                 => array(
		array(
			'title' => esc_html__( 'Product loop "List"', 'woodmart' ),
			'name'  => 'product-loop-list',
			'file'  => '/css/parts/woo-product-loop-list',
			'rtl'   => true,
		),
	),
	'select2'                           => array(
		array(
			'title' => esc_html__( 'Select2 library', 'woodmart' ),
			'name'  => 'select2',
			'file'  => '/css/parts/woo-lib-select2',
			'rtl'   => true,
		),
	),
	'categories-loop'                   => array(
		array(
			'title' => esc_html__( 'Categories loop', 'woodmart' ),
			'name'  => 'categories-loop',
			'file'  => '/css/parts/woo-categories-loop',
			'rtl'   => true,
		),
	),
	'categories-loop-default'           => array(
		array(
			'title' => esc_html__( 'Categories loop default', 'woodmart' ),
			'name'  => 'categories-loop-default',
			'file'  => '/css/parts/woo-categories-loop-default',
			'rtl'   => true,
		),
	),
	'categories-loop-center'            => array(
		array(
			'title' => esc_html__( 'Categories loop center title', 'woodmart' ),
			'name'  => 'categories-loop-center',
			'file'  => '/css/parts/woo-categories-loop-center',
		),
	),
	'categories-loop-replace-title'     => array(
		array(
			'title' => esc_html__( 'Categories loop replace title', 'woodmart' ),
			'name'  => 'categories-loop-replace-title',
			'file'  => '/css/parts/woo-categories-loop-replace-title',
		),
	),
	'woo-gutenberg'                     => array(
		array(
			'title' => esc_html__( 'WooCommerce gutenberg', 'woodmart' ),
			'name'  => 'woo-gutenberg',
			'file'  => '/css/parts/woo-gutenberg',
			'rtl'   => true,
		),
	),
	// Base.
	'page-title'                        => array(
		array(
			'title' => esc_html__( 'Page title', 'woodmart' ),
			'name'  => 'page-title',
			'file'  => '/css/parts/page-title',
		),
	),
	'blog-base'                         => array(
		array(
			'title' => esc_html__( 'Blog base', 'woodmart' ),
			'name'  => 'blog-base',
			'file'  => '/css/parts/blog-base',
			'rtl'   => true,
		),
	),
	'portfolio-base'                    => array(
		array(
			'title' => esc_html__( 'Portfolio base', 'woodmart' ),
			'name'  => 'portfolio-base',
			'file'  => '/css/parts/portfolio-base',
			'rtl'   => true,
		),
	),
	'page-404'                          => array(
		array(
			'title' => esc_html__( 'Page 404', 'woodmart' ),
			'name'  => 'page-404',
			'file'  => '/css/parts/page-404',
		),
	),
	'wp-gutenberg'                      => array(
		array(
			'title' => esc_html__( 'Gutenberg', 'woodmart' ),
			'name'  => 'wp-gutenberg',
			'file'  => '/css/parts/wp-gutenberg',
			'rtl'   => true,
		),
	),
	'page-search-results'               => array(
		array(
			'title' => esc_html__( 'Search page', 'woodmart' ),
			'name'  => 'page-search-results',
			'file'  => '/css/parts/page-search-results',
			'rtl'   => true,
		),
	),
	// Options.
	'collapsible-content'               => array(
		array(
			'title'    => esc_html__( 'Collapsible content', 'woodmart' ),
			'name'     => 'collapsible-content',
			'file'     => '/css/parts/elem-opt-collapsible-content',
			'wpb_file' => '/css/parts/wpb-opt-collapsible-content',
		),
	),
	'age-verify'                        => array(
		array(
			'title' => esc_html__( 'Age verify option', 'woodmart' ),
			'name'  => 'age-verify',
			'file'  => '/css/parts/opt-age-verify',
		),
	),
	'bottom-toolbar'                    => array(
		array(
			'title' => esc_html__( 'Button navbar option', 'woodmart' ),
			'name'  => 'bottom-toolbar',
			'file'  => '/css/parts/opt-bottom-toolbar',
			'rtl'   => true,
		),
		array(
			'title' => esc_html__( 'Module tools', 'woodmart' ),
			'name'  => 'mod-tools',
			'file'  => '/css/parts/mod-tools',
			'rtl'   => true,
		),
	),
	'cookies-popup'                     => array(
		array(
			'title' => esc_html__( 'Cookies popup option', 'woodmart' ),
			'name'  => 'cookies-popup',
			'file'  => '/css/parts/opt-cookies',
			'rtl'   => true,
		),
	),
	'header-banner'                     => array(
		array(
			'title' => esc_html__( 'Header banner option', 'woodmart' ),
			'name'  => 'header-banner',
			'file'  => '/css/parts/opt-header-banner',
			'rtl'   => true,
		),
	),
	'lazy-loading'                      => array(
		array(
			'title' => esc_html__( 'Lazy loading option', 'woodmart' ),
			'name'  => 'lazy-loading',
			'file'  => '/css/parts/opt-lazy-load',
		),
	),
	'off-canvas-sidebar'                => array(
		array(
			'title' => esc_html__( 'Off canvas sidebar option', 'woodmart' ),
			'name'  => 'off-canvas-sidebar',
			'file'  => '/css/parts/opt-off-canvas-sidebar',
			'rtl'   => true,
		),
	),
	'promo-popup'                       => array(
		array(
			'title' => esc_html__( 'Promo popup option', 'woodmart' ),
			'name'  => 'promo-popup',
			'file'  => '/css/parts/opt-promo-popup',
		),
	),
	'scroll-top'                        => array(
		array(
			'title' => esc_html__( 'Scroll to top option', 'woodmart' ),
			'name'  => 'scroll-top',
			'file'  => '/css/parts/opt-scrolltotop',
			'rtl'   => true,
		),
	),
	'sticky-social-buttons'             => array(
		array(
			'title' => esc_html__( 'Sticky social buttons option', 'woodmart' ),
			'name'  => 'sticky-social-buttons',
			'file'  => '/css/parts/opt-sticky-social',
			'rtl'   => true,
		),
	),
	// Libraries.
	'justified'                         => array(
		array(
			'title' => esc_html__( 'Justified gallery library', 'woodmart' ),
			'name'  => 'justified',
			'file'  => '/css/parts/lib-justified-gallery',
			'rtl'   => true,
		),
	),
	'mfp-popup'                         => array(
		array(
			'title' => esc_html__( 'Magnific popup library', 'woodmart' ),
			'name'  => 'mfp-popup',
			'file'  => '/css/parts/lib-magnific-popup',
			'rtl'   => true,
		),
	),
	'owl-carousel'                      => array(
		array(
			'title' => esc_html__( 'Owl carousel library', 'woodmart' ),
			'name'  => 'owl-carousel',
			'file'  => '/css/parts/lib-owl-carousel',
			'rtl'   => true,
		),
	),
	'photoswipe'                        => array(
		array(
			'title' => esc_html__( 'Photoswipe library', 'woodmart' ),
			'name'  => 'photoswipe',
			'file'  => '/css/parts/lib-photoswipe',
			'rtl'   => true,
		),
	),
	// Integrations.
	'wpbakery-base'                     => array(
		array(
			'title' => esc_html__( 'WPBakery integration', 'woodmart' ),
			'name'  => 'wpbakery-base',
			'file'  => '/css/parts/int-wpbakery-base',
			'rtl'   => true,
		),
	),
	'elementor-base'                    => array(
		array(
			'title' => esc_html__( 'Elementor integration', 'woodmart' ),
			'name'  => 'elementor-base',
			'file'  => '/css/parts/int-elementor-base',
			'rtl'   => true,
		),
	),
	'elementor-pro-base'                => array(
		array(
			'title' => esc_html__( 'Elementor Pro integration', 'woodmart' ),
			'name'  => 'elementor-pro-base',
			'file'  => '/css/parts/int-elementor-pro',
			'rtl'   => true,
		),
	),
	'advanced-nocaptcha'                => array(
		array(
			'title' => esc_html__( 'Advanced Nocaptcha integration', 'woodmart' ),
			'name'  => 'advanced-nocaptcha',
			'file'  => '/css/parts/int-advanced-nocaptcha',
		),
	),
	'bbpress'                           => array(
		array(
			'title' => esc_html__( 'BBPress integration', 'woodmart' ),
			'name'  => 'bbpress',
			'file'  => '/css/parts/int-bbpress',
		),
	),
	'wpcf7'                             => array(
		array(
			'title' => esc_html__( 'Contacts form 7 integration', 'woodmart' ),
			'name'  => 'wpcf7',
			'file'  => '/css/parts/int-wpcf7',
			'rtl'   => true,
		),
	),
	'woo-curr-switch'                   => array(
		array(
			'title' => esc_html__( 'WC currency switcher integration', 'woodmart' ),
			'name'  => 'woo-curr-switch',
			'file'  => '/css/parts/int-woo-curr-switch',
		),
	),
	'woo-dokan-vend'                    => array(
		array(
			'title' => esc_html__( 'Dokan integration', 'woodmart' ),
			'name'  => 'woo-dokan-vend',
			'file'  => '/css/parts/int-woo-dokan-vend',
			'rtl'   => true,
		),
	),
	'woo-germanized'                    => array(
		array(
			'title' => esc_html__( 'Germanized integration', 'woodmart' ),
			'name'  => 'woo-germanized',
			'file'  => '/css/parts/int-woo-germanized',
		),
	),
	'mc4wp'                             => array(
		array(
			'title'    => esc_html__( 'Mailchimp for wordpress integration', 'woodmart' ),
			'name'     => 'mc4wp',
			'file'     => '/css/parts/int-mc4wp',
		),
	),
	'mc4wp-deprecated'                  => array(
		array(
			'title' => esc_html__( 'Mailchimp for wordpress integration (deprecated)', 'woodmart' ),
			'name'  => 'mc4wp-deprecated',
			'file'  => '/css/parts/int-mc4wp-deprecated',
		),
	),
	'woo-paypal-express'                => array(
		array(
			'title' => esc_html__( 'PayPal Express integration', 'woodmart' ),
			'name'  => 'woo-paypal-express',
			'file'  => '/css/parts/int-woo-paypal-express',
			'rtl'   => true,
		),
	),
	'revolution-slider'                 => array(
		array(
			'title' => esc_html__( 'Slider Revolution integration', 'woodmart' ),
			'name'  => 'revolution-slider',
			'file'  => '/css/parts/int-revolution-slider',
		),
	),
	'woo-stripe'                        => array(
		array(
			'title' => esc_html__( 'Stripe integration', 'woodmart' ),
			'name'  => 'woo-stripe',
			'file'  => '/css/parts/int-woo-stripe',
			'rtl'   => true,
		),
	),
	'woo-wcfm-fm'                       => array(
		array(
			'title' => esc_html__( 'WCFM – Frontend Manager integration', 'woodmart' ),
			'name'  => 'woo-wcfm-fm',
			'file'  => '/css/parts/int-woo-wcfm-fm',
		),
	),
	'woo-wc-marketplace'                => array(
		array(
			'title' => esc_html__( 'Multivendor Marketplace integration', 'woodmart' ),
			'name'  => 'woo-wc-marketplace',
			'file'  => '/css/parts/int-woo-wc-marketplace',
		),
	),
	'woo-wc-vendors'                    => array(
		array(
			'title' => esc_html__( 'WC Vendors integration', 'woodmart' ),
			'name'  => 'woo-wc-vendors',
			'file'  => '/css/parts/int-woo-wc-vendors',
		),
	),
	'wpml'                              => array(
		array(
			'title' => esc_html__( 'WPML integration', 'woodmart' ),
			'name'  => 'wpml',
			'file'  => '/css/parts/int-wpml',
			'rtl'   => true,
		),
	),
	'woo-yith-compare'                  => array(
		array(
			'title' => esc_html__( 'YITH Compare integration', 'woodmart' ),
			'name'  => 'woo-yith-compare',
			'file'  => '/css/parts/int-woo-yith-compare',
		),
	),
	'woo-yith-vendor'                   => array(
		array(
			'title' => esc_html__( 'YITH Vendor integration', 'woodmart' ),
			'name'  => 'woo-yith-vendor',
			'file'  => '/css/parts/int-woo-yith-vendor',
		),
	),
	'woo-yith-req-quote'                => array(
		array(
			'title' => esc_html__( 'YITH Request Quote integration', 'woodmart' ),
			'name'  => 'woo-yith-req-quote',
			'file'  => '/css/parts/int-woo-yith-req-quote',
			'rtl'   => true,
		),
	),
	'woo-yith-wishlist'                 => array(
		array(
			'title' => esc_html__( 'YITH Wishlist integration', 'woodmart' ),
			'name'  => 'woo-yith-wishlist',
			'file'  => '/css/parts/int-woo-yith-wishlist',
		),
	),
	// Elements options.
	'product-arrows'                    => array(
		array(
			'title' => esc_html__( 'Product arrows', 'woodmart' ),
			'name'  => 'product-arrows',
			'file'  => '/css/parts/el-opt-product-arrows',
		),
	),
	'highlighted-product'               => array(
		array(
			'title' => esc_html__( 'Highlighted product', 'woodmart' ),
			'name'  => 'highlighted-product',
			'file'  => '/css/parts/el-opt-highlight-product',
			'rtl'   => true,
		),
	),
	// Elements.
	'tabs'                              => array(
		array(
			'title' => esc_html__( 'Tabs element', 'woodmart' ),
			'name'  => 'tabs',
			'file'  => '/css/parts/el-tabs',
			'rtl'   => true,
		),
	),
	'accordion'                         => array(
		array(
			'title' => esc_html__( 'Accordion element', 'woodmart' ),
			'name'  => 'accordion',
			'file'  => '/css/parts/el-accordion',
			'rtl'   => true,
		),
	),
	'360degree'                         => array(
		array(
			'title' => esc_html__( '360 element', 'woodmart' ),
			'name'  => '360degree',
			'file'  => '/css/parts/el-360deg',
			'rtl'   => true,
		),
	),
	'banner'                            => array(
		array(
			'title'    => esc_html__( 'Banner element', 'woodmart' ),
			'name'     => 'banner',
			'file'     => '/css/parts/el-banner',
			'wpb_file' => '/css/parts/wpb-el-banner',
		),
	),
	'countdown'                         => array(
		array(
			'title' => esc_html__( 'Countdown element', 'woodmart' ),
			'name'  => 'countdown',
			'file'  => '/css/parts/el-countdown-timer',
			'rtl'   => true,
		),
	),
	'counter'                           => array(
		array(
			'title' => esc_html__( 'Counter element', 'woodmart' ),
			'name'  => 'counter',
			'file'  => '/css/parts/el-counter',
		),
	),
	'image-gallery'                     => array(
		array(
			'title' => esc_html__( 'Image gallery element', 'woodmart' ),
			'name'  => 'image-gallery',
			'file'  => '/css/parts/el-gallery',
		),
	),
	'image-hotspot'                     => array(
		array(
			'title' => esc_html__( 'Image hotspot element', 'woodmart' ),
			'name'  => 'image-hotspot',
			'file'  => '/css/parts/el-hotspot',
		),
	),
	'info-box'                          => array(
		array(
			'title' => esc_html__( 'Info box element', 'woodmart' ),
			'name'  => 'info-box',
			'file'  => '/css/parts/el-info-box',
			'rtl'   => true,
		),
	),
	'instagram'                         => array(
		array(
			'title' => esc_html__( 'Instagram element', 'woodmart' ),
			'name'  => 'instagram',
			'file'  => '/css/parts/el-instagram',
			'rtl'   => true,
		),
	),
	'list'                              => array(
		array(
			'title' => esc_html__( 'List element', 'woodmart' ),
			'name'  => 'list',
			'file'  => '/css/parts/el-list',
			'rtl'   => true,
		),
	),
	'map'                               => array(
		array(
			'title' => esc_html__( 'Google maps element', 'woodmart' ),
			'name'  => 'map',
			'file'  => '/css/parts/el-map',
			'rtl'   => true,
		),
	),
	'menu-price'                        => array(
		array(
			'title' => esc_html__( 'Menu price element', 'woodmart' ),
			'name'  => 'menu-price',
			'file'  => '/css/parts/el-menu-price',
			'rtl'   => true,
		),
	),
	'pricing-table'                     => array(
		array(
			'title' => esc_html__( 'Pricing table element', 'woodmart' ),
			'name'  => 'pricing-table',
			'file'  => '/css/parts/el-pricing-table',
			'rtl'   => true,
		),
	),
	'responsive-text'                   => array(
		array(
			'title' => esc_html__( 'Responsive text element', 'woodmart' ),
			'name'  => 'responsive-text',
			'file'  => '/css/parts/el-responsive-text',
			'rtl'   => true,
		),
	),
	'text-block'                        => array(
		array(
			'title' => esc_html__( 'Text block element', 'woodmart' ),
			'name'  => 'text-block',
			'file'  => '/css/parts/el-text-block',
		),
	),
	'dividers'                          => array(
		array(
			'title' => esc_html__( 'Dividers element', 'woodmart' ),
			'name'  => 'dividers',
			'file'  => '/css/parts/el-row-divider',
		),
	),
	'section-title'                     => array(
		array(
			'title' => esc_html__( 'Section title element', 'woodmart' ),
			'name'  => 'section-title',
			'file'  => '/css/parts/el-section-title',
			'rtl'   => true,
		),
	),
	'slider'                            => array(
		array(
			'title' => esc_html__( 'Slider element', 'woodmart' ),
			'name'  => 'slider',
			'file'  => '/css/parts/el-slider',
			'rtl'   => true,
		),
	),
	'social-icons'                      => array(
		array(
			'title' => esc_html__( 'Social icons element', 'woodmart' ),
			'name'  => 'social-icons',
			'file'  => '/css/parts/el-social-icons',
		),
	),
	'team-member'                       => array(
		array(
			'title' => esc_html__( 'Team member element', 'woodmart' ),
			'name'  => 'team-member',
			'file'  => '/css/parts/el-team-member',
		),
	),
	'testimonial'                       => array(
		array(
			'title' => esc_html__( 'Testimonial element', 'woodmart' ),
			'name'  => 'testimonial',
			'file'  => '/css/parts/el-testimonial',
			'rtl'   => true,
		),
	),
	'testimonial-old'                   => array(
		array(
			'title' => esc_html__( 'Testimonial old element', 'woodmart' ),
			'name'  => 'testimonial-old',
			'file'  => '/css/parts/el-testimonial-old',
			'rtl'   => true,
		),
	),
	'timeline'                          => array(
		array(
			'title' => esc_html__( 'Timeline element', 'woodmart' ),
			'name'  => 'timeline',
			'file'  => '/css/parts/el-timeline',
			'rtl'   => true,
		),
	),
	'twitter'                           => array(
		array(
			'title' => esc_html__( 'Twitter element', 'woodmart' ),
			'name'  => 'twitter',
			'file'  => '/css/parts/el-twitter',
			'rtl'   => true,
		),
	),
);
