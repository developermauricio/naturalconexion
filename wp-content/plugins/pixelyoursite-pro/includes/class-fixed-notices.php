<?php
namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


class NoticesFixed {

    private static $_instance;
    private $dismissedKey = "pys_pro_fixed_dismissed_notices";

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;

    }

    public function __construct() {
        add_action( 'init', [$this,'init'] );
    }

    function init() {
        if ( ! current_user_can( 'manage_pys' ) ) {
            return;
        }

        add_action( 'admin_notices', [$this,'showNotices'] );
        add_action( 'wp_ajax_pys_fixed_notice_dismiss', [$this,'catchOnCloseNotice'] );

    }

    function showNotices() {

        require_once PYS_PATH . '/notices/fixed.php';
        $user_id = get_current_user_id();

        $this->isNeedToShow(adminGetFixedNotices(),(array)get_user_meta( $user_id, $this->dismissedKey,true ));
    }


    function  catchOnCloseNotice() {

        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'pys_fixed_notice_dismiss' ) ) {
            return;
        }

        if ( empty( $_REQUEST['user_id'] ) || empty( $_REQUEST['addon_slug'] ) || empty( $_REQUEST['meta_key'] ) ) {
            return;
        }
        $userId = sanitize_text_field( $_REQUEST['user_id'] );
        $dismissedSlugs = (array)get_user_meta( $userId, $this->dismissedKey,true);
        $dismissedSlugs[] = sanitize_text_field( $_REQUEST['meta_key'] );

        // save dismissed notice
        update_user_meta($userId, $this->dismissedKey, $dismissedSlugs );
    }

    private function renderNotice($notice) {

        if ( ! current_user_can( 'manage_pys' ) ) {
            return;
        }

        if ( ! $notice ) {
            return;
        }

        $user_id = get_current_user_id();

        ?>
        <div class="notice notice-info is-dismissible pys-fixed-notice" data-slug="<?=$notice['slug']?>">
            <p><?php echo $notice['message']; ?></p>
        </div>
        <script type='application/javascript'>
            jQuery(document).on('click', '.pys-fixed-notice .notice-dismiss', function () {
                jQuery.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'pys_fixed_notice_dismiss',
                        nonce: '<?php esc_attr_e( wp_create_nonce( 'pys_fixed_notice_dismiss' ) ); ?>',
                        user_id: '<?php esc_attr_e( $user_id ); ?>',
                        addon_slug: 'pro',
                        meta_key: jQuery(this).parents('.pys-fixed-notice').data('slug')
                    }
                });
            });
        </script>
        <?php
    }

    private function isNeedToShow($notices,$showedNoticesSlug) {
        $activePlugins = [];

        if(isWooCommerceActive()) {
            $activePlugins[]='woo';
        }
        if(isWcfActive()) {
            $activePlugins[]='wcf';
        }
        if(isEddActive()) {
            $activePlugins[]='edd';
        }

        foreach ($notices as $notice) {
            // check is notice has some plugin dependencies
            if( count($notice['plugins']) == 0
                || (count(array_intersect($notice['plugins'], $activePlugins)) == count($activePlugins)
                    && count($notice['plugins']) == count($activePlugins))
            ) {

                // check is this notice was showed
                if(!in_array($notice['slug'],$showedNoticesSlug)) {
                    $this->renderNotice($notice);
                }
            }
        }
    }

}

/**
 * @return NoticesFixed
 */
function NoticesFixed() {
    return NoticesFixed::instance();
}

NoticesFixed();