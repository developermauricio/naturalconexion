<?php
/**
 * Created by PhpStorm.
 * User: Villatheme-Thanh
 * Date: 19-04-19
 * Time: 4:55 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table width="600" align="center" valign="center" cellspacing="0" cellpadding="0" border="0"
       style="font-size: 14px; font-family: Lato, Arial, Helvetica, sans-serif;background-color: #ffffff;">
    <tr>
        <td>
            <table width="600" align="left" valign="center" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style=" padding: 12px 40px;">
                        <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;"
                               cellpadding="0" cellspacing="0" height="100%" width="100%">
                            <tbody>
                            <tr>
                                <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">
                                    <img class="wacv-image" width="100%"
                                         src="<?php echo WACVP_IMAGES ?>sample-logo.png"
                                         style="vertical-align: middle; "></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="600" align="left" valign="center" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="padding: 12px 40px; color: #ffffff; background-color: #474747;">
                        <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;"
                               cellpadding="0" cellspacing="0" height="100%" width="100%">
                            <tbody>
                            <tr>
                                <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">
                                    <div class="form-control wacv-text-field wacv-background-color-output"
                                         style="padding: 5px 0px; background-color: #474747;" data-field="49114"><p
                                                style="text-align: center;"><a
                                                    style="font-size: 18.6667px; text-align: center;" href="{home_url}">Home</a><span
                                                    style="font-size: 18.6667px; text-align: center;">&nbsp;|&nbsp;</span><a
                                                    style="font-size: 18.6667px; text-align: center;" href="{shop_url}">Shop</a>
                                        </p></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="600" align="left" valign="center" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style=" padding: 12px 40px;">
                        <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;"
                               cellpadding="0" cellspacing="0" height="100%" width="100%">
                            <tbody>
                            <tr>
                                <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">
                                    <div class="form-control wacv-text-field wacv-background-color-output"
                                         style="padding: 5px 0" data-field="14182"><p><span style="font-size: 12pt;">Hello {customer_name}.</span>
                                        </p>
                                        <p><span style="font-size: 12pt;">Looks like you left something fabulous in your shopping cart</span>
                                        </p></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="600" align="left" valign="center" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="padding: 0px 40px; color: rgb(255, 255, 255);">
                        <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;"
                               cellpadding="0" cellspacing="0" height="100%" width="100%">
                            <tbody>
                            <tr>
                                <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">
                                    <div class="form-control wacv-text-field wacv-background-color-output"
                                         style="padding: 5px 0px; background-color: #474747;" data-field="75503"><p>
                                            <span style="font-size: 18.6667px;">&nbsp; &nbsp;Items in your cart</span>
                                        </p></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="600" align="left" valign="center" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="padding: 0px 40px 12px;">
                        <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;"
                               cellpadding="0" cellspacing="0" height="100%" width="100%">
                            <tbody>
                            <tr>
                                <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">
                                    <table class="wacv-background-color-output field-ctrl"
                                           style="border-collapse: collapse; border: 1px solid rgb(221, 221, 221);"
                                           cellpadding="0" cellspacing="0" height="100%" width="100%">
                                        <tbody>{wacv_cart_detail_start}
                                        <tr>
                                            <td align="center" width="140"
                                                style="padding: 5px; border-top: 1px solid rgb(221, 221, 221); border-bottom: 1px solid rgb(221, 221, 221);"
                                                class="field-ctrl"><img style="width:140px; vertical-align: middle;"
                                                                        src="{wacv_image_product}"></td>
                                            <td style="vertical-align: top; padding: 5px; border-top: 1px solid rgb(221, 221, 221); border-bottom: 1px solid rgb(221, 221, 221);"
                                                class="field-ctrl"><p style="line-height: 2; font-weight: bold; ">
                                                    {product_name}</p>
                                                <p style="line-height: 2;">{product_quantity}</p>
                                                <p style="line-height: 2;">{product_amount}</p></td>
                                        </tr>
                                        {wacv_cart_detail_end}
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="600" align="left" valign="center" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="padding: 12px 40px; color: #ffffff; text-align: center;">
                        <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;"
                               cellpadding="0" cellspacing="0" height="100%" width="100%">
                            <tbody>
                            <tr>
                                <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">
                                    <button type="button"
                                            class="wacv-button-text wacv-background-color-output ui-sortable-handle"
                                            style="width: 50%; border: none; padding: 5px 0px; margin: 0px; color: inherit; background-color: #474747;">
                                        <p style="text-align: center;"><span style="font-size: 14pt;"><strong><a
                                                            href="{wacv_checkout_btn}">Checkout Now</a></strong></span>
                                        </p></button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="600" align="left" valign="center" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="padding: 0px 40px 12px;">
                        <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;"
                               cellpadding="0" cellspacing="0" height="100%" width="100%">
                            <tbody>
                            <tr>
                                <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">
                                    <div class="form-control wacv-text-field wacv-background-color-output"
                                         style="padding: 5px 0" data-field="16614"><p><span style="font-size: 16px;">If you don’t want to receive this reminder email. You can unsubscribe&nbsp;</span><a
                                                    style="font-size: 16px;" href="{unsubscribe_link}">here</a></p>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="600" align="left" valign="center" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="padding: 12px 40px; color: #ffffff; background-color: #474747;">
                        <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;"
                               cellpadding="0" cellspacing="0" height="100%" width="100%">
                            <tbody>
                            <tr>
                                <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">
                                    <div class="form-control wacv-text-field wacv-background-color-output"
                                         style="padding: 5px 0px; background-color: #474747;" data-field="45819"><p
                                                style="text-align: center;"><span
                                                    style="font-size: 16px; text-align: center;">{site_url}</span></p>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
