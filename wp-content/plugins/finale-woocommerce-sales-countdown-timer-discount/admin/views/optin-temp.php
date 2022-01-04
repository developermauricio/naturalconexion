<?php
defined( 'ABSPATH' ) || exit;

global $current_user;
$user_instance = 'Hey';
if ( is_object( $current_user ) ) {
	$user_instance .= ' ' . $current_user->display_name . ',';
}
$finale_link = esc_url( 'https://xlplugins.com/finale-woocommerce-sales-countdown-timer-discount-plugin/?utm_source=wpplugin&utm_campaign=finale&utm_medium=text&utm_term=optin' );
$accept_link = esc_url( wp_nonce_url( add_query_arg( array(
	'xl-optin-choice' => 'yes',
	'ref'             => filter_input( INPUT_GET, 'page' ),
) ), 'xl_optin_nonce', '_xl_optin_nonce' ) );
$skip_link   = esc_url( wp_nonce_url( add_query_arg( 'xl-optin-choice', 'no' ), 'xl_optin_nonce', '_xl_optin_nonce' ) );
?>
<div id="xlo-wrap" class="wrap">
    <div class="xlo-logos">
        <img class="xlo-plugin-icon" width="80" height="80" src="<?php echo plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/wc.png'; ?>"/>
        <i class="dashicons dashicons-plus xlo-first"></i>
        <img class="xlo-wrap-logo" width="80" height="80" src="<?php echo plugin_dir_url( WCCT_PLUGIN_FILE ) . 'admin/assets/img/xlplugins.png'; ?>"/>
    </div>
    <div class="xlo-content">
        <p><?php echo $user_instance; ?><br></p>
        <h2>Thank you for choosing Finale!</h2>
        <p>We are constantly improving the plugin and building in new features.</p>
        <p>Never miss an update! Opt in for security, feature updates and non-sensitive diagnostic tracking. Click Allow &amp; Continue'!</p>
    </div>
    <div class="xlo-actions" data-source="Finale">
        <a href="<?php echo $skip_link; ?>" class="button button-secondary" data-status="no">Skip</a>
        <a href="<?php echo $accept_link; ?>" class="button button-primary" data-status="yes">Allow &amp; Continue</a>
        <div style="display: none" class="xlo_loader">&nbsp;</div>
    </div>
    <div class="xlo-permissions">
        <a class="xlo-trigger" href="#" tabindex="1">What permissions are being granted?</a>
        <ul>
            <li id="xlo-permission-profile" class="xlo-permission xlo-profile">
                <i class="dashicons dashicons-admin-users"></i>
                <div>
                    <span>Your Profile Overview</span>
                    <p>Name and email address</p>
                </div>
            </li>
            <li id="xlo-permission-site" class="xlo-permission xlo-site">
                <i class="dashicons dashicons-admin-settings"></i>
                <div>
                    <span>Your Site Overview</span>
                    <p>Site URL, WP version, PHP info, plugins &amp; themes</p>
                </div>
            </li>
        </ul>
    </div>
    <div class="xlo-terms">
        <a href="https://xlplugins.com/non-sensitive-usage-tracking/?utm_source=wpplugin&utm_campaign=finale&utm_medium=text&utm_term=optin" target="_blank">Non-Sensitive Usage Tracking</a>
    </div>
</div>
<script type="text/javascript">
    (function ($) {
        $('.xlo-permissions .xlo-trigger').on('click', function () {
            $('.xlo-permissions').toggleClass('xlo-open');

            return false;
        });
        $('.xlo-actions a').on('click', function (e) {
            e.preventDefault();
            var $this = $(this);
            var source = $this.parents('.xlo-actions').data('source');
            var status = $this.data('status');
            $this.parents('.xlo-actions').find(".xlo_loader").show();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'xlo_optin_call',
                    source: source,
                    status: status,
                },
                success: function (result) {
                    window.location = $this.attr('href');
                }
            });
        })
    })(jQuery);
</script>

<style>
    #xlo-wrap {
        width: 480px;
        -moz-box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        -webkit-box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        margin: 30px 0;
        max-width: 100%
    }

    #xlo-wrap .xlo-content {
        background: #fff;
        padding: 0 20px 15px
    }

    #xlo-wrap .xlo-content p {
        margin: 0 0 1em;
        padding: 0;
        font-size: 1.1em
    }

    #xlo-wrap .xlo-actions {
        padding: 10px 20px;
        background: #C0C7CA;
        position: relative
    }

    #xlo-wrap .xlo-actions .xlo_loader {
        background: url("<?php echo admin_url( 'images/spinner.gif' ); ?>") no-repeat rgba(238, 238, 238, 0.5);
        background-position: center;
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0
    }

    #xlo-wrap .xlo-actions .button {
        padding: 0 10px 1px;
        line-height: 35px;
        height: 37px;
        font-size: 16px;
        margin-bottom: 0
    }

    #xlo-wrap .xlo-actions .button .dashicons {
        font-size: 37px;
        margin-left: -8px;
        margin-right: 12px
    }

    #xlo-wrap .xlo-actions .button.button-primary {
        padding-right: 15px;
        padding-left: 15px
    }

    #xlo-wrap .xlo-actions .button.button-primary:after {
        content: ' \279C'
    }

    #xlo-wrap .xlo-actions .button.button-primary {
        float: right
    }

    #xlo-wrap.xlo-anonymous-disabled .xlo-actions .button.button-primary {
        width: 100%
    }

    #xlo-wrap .xlo-permissions {
        padding: 10px 20px;
        background: #FEFEFE;
        -moz-transition: background .5s ease;
        -o-transition: background .5s ease;
        -ms-transition: background .5s ease;
        -webkit-transition: background .5s ease;
        transition: background .5s ease
    }

    #xlo-wrap .xlo-permissions .xlo-trigger {
        font-size: .9em;
        text-decoration: none;
        text-align: center;
        display: block
    }

    #xlo-wrap .xlo-permissions ul {
        height: 0;
        overflow: hidden;
        margin: 0
    }

    #xlo-wrap .xlo-permissions ul li {
        margin-bottom: 12px
    }

    #xlo-wrap .xlo-permissions ul li:last-child {
        margin-bottom: 0
    }

    #xlo-wrap .xlo-permissions ul li i.dashicons {
        float: left;
        font-size: 40px;
        width: 40px;
        height: 40px
    }

    #xlo-wrap .xlo-permissions ul li div {
        margin-left: 55px
    }

    #xlo-wrap .xlo-permissions ul li div span {
        font-weight: 700;
        text-transform: uppercase;
        color: #23282d
    }

    #xlo-wrap .xlo-permissions ul li div p {
        margin: 2px 0 0
    }

    #xlo-wrap .xlo-permissions.xlo-open {
        background: #fff
    }

    #xlo-wrap .xlo-permissions.xlo-open ul {
        height: auto;
        margin: 20px 20px 10px
    }

    #xlo-wrap .xlo-logos {
        padding: 20px;
        line-height: 0;
        background: #fafafa;
        height: 84px;
        position: relative
    }

    #xlo-wrap .xlo-logos .xlo-wrap-logo {
        position: absolute;
        left: 58%;
        top: 20px
    }

    #xlo-wrap .xlo-logos .xlo-plugin-icon {
        position: absolute;
        top: 20px;
        left: 30%;
        margin-left: -40px
    }

    #xlo-wrap .xlo-logos .xlo-plugin-icon, #xlo-wrap .xlo-logos img, #xlo-wrap .xlo-logos object {
        width: 80px;
        height: 80px
    }

    #xlo-wrap .xlo-logos .dashicons-plus {
        position: absolute;
        top: 50%;
        font-size: 30px;
        margin-top: -10px;
        color: #bbb
    }

    #xlo-wrap .xlo-logos .dashicons-plus.xlo-first {
        left: 45%
    }

    #xlo-wrap .xlo-logos .xlo-plugin-icon, #xlo-wrap .xlo-logos .xlo-wrap-logo {
        border: 1px solid #ccc;
        padding: 1px;
        background: #fff
    }

    #xlo-wrap .xlo-terms {
        text-align: center;
        font-size: .85em;
        padding: 5px;
        background: rgba(0, 0, 0, 0.05)
    }

    #xlo-wrap .xlo-terms, #xlo-wrap .xlo-terms a {
        color: #999
    }

    #xlo-wrap .xlo-terms a {
        text-decoration: none
    }

    #xlo-theme_connect_wrapper #xlo-wrap {
        top: 0;
        text-align: left;
        display: inline-block;
        vertical-align: middle;
        margin-top: 52px;
        margin-bottom: 20px
    }

    #xlo-theme_connect_wrapper #xlo-wrap .xlo-terms {
        background: rgba(140, 140, 140, 0.64)
    }

    #xlo-theme_connect_wrapper #xlo-wrap .xlo-terms, #xlo-theme_connect_wrapper #xlo-wrap .xlo-terms a {
        color: #c5c5c5
    }
</style>
