<div class="wrap tab wrap-addon" id="tab-sms-abandoned-cart">
    <div class="wrap egoi4wp-settings" id="tab-forms">
        <div class="row">

            <div id="abandoned_cart_message">
                <?php
                if (isset($_POST['form_id']) && $_POST['form_id'] == 'form-sms-order-abandoned-cart') {
                    if ($result) {
                        $this->helper->smsonw_admin_notice_success();
                    } else {
                        $this->helper->smsonw_admin_notice_error();
                    }
                }
                ?>
            </div>

            <div class="main-content col col-12" style="margin:0 0 20px;">

                <p class="label_text"><?php _e('Use this to add a lost cart sms trigger.', 'smart-marketing-addon-sms-order');?></p>

                <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-abandoned-cart">
                    <?php wp_nonce_field( 'form-sms-order-abandoned-cart' ); ?>
                    <input name="form_id" type="hidden" value="form-sms-order-abandoned-cart" />
                    <div id="sms_abandoned_cart">
                        <table border="0" class="widefat striped" style="max-width: 900px;">
                            <thead>
                            <tr>
                                <th><?php _e('Configurations', 'smart-marketing-addon-sms-order');?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><span><?php _e('Message', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <textarea name="message" id="message" style="min-width: 400px;width: 100%;"><?php
                                        echo (isset($abandoned_cart_obj["message"]) && trim($abandoned_cart_obj["message"]) != '') ? $abandoned_cart_obj["message"] : '';
                                        ?></textarea>
                                    <p>
                                        <?php _e('Use %link% to choose the position of the link otherwise the link will be placed at the end.','smart-marketing-addon-sms-order');?><br>
                                        <?php _e('Use %shop_name% for shop name display.','smart-marketing-addon-sms-order');?><br>
                                    </p>
                                </td>
                            </tr>


                            <tr>
                                <td><span><?php _e('Title Pop', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <div>
                                        <input type="text" id="title_pop" name="title_pop" style="width: 100%;"
                                               value="<?php
                                               echo (isset($abandoned_cart_obj["title_pop"]) ) ? $abandoned_cart_obj["title_pop"] : ''; ?>"
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Text on send button', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <div>
                                        <input type="text" id="button_name" name="button_name" style="width: 100%;"
                                               value="<?php
                                               echo (isset($abandoned_cart_obj["button_name"]) ) ? $abandoned_cart_obj["button_name"] : ''; ?>"
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Text on cancel button', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <div>
                                        <input type="text" id="button_cancel_name" name="button_cancel_name" style="width: 100%;"
                                               value="<?php
			                                   echo (isset($abandoned_cart_obj["button_cancel_name"]) ) ? $abandoned_cart_obj["button_cancel_name"] : ''; ?>"
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Enabled', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <div>
                                        <input type="checkbox" id="enable" name="enable"
                                            <?php
                                            echo (isset($abandoned_cart_obj["enable"]) && $abandoned_cart_obj["enable"] == "on") ? 'checked' : ''; ?>
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Shortener', 'egoi_sms_follow_price_enable_title');?></span></td>
                                <td>
                                    <div>
                                        <input type="checkbox" id="shortener" name="shortener"
                                            <?php
                                            echo (isset($abandoned_cart_obj["shortener"]) && $abandoned_cart_obj["shortener"] == "on") ? 'checked' : ''; ?>
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
	                                <b><?php _e( 'Styles for dialog', 'egoi_sms_follow_price_enable_title' ); ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div>
                                        <div class="smsnf-input-group">
                                            <label for="bar-text-color" style="font-size: 13px;"><?php _e( 'Background Color', 'egoi_sms_follow_price_enable_title' ); ?></label>

                                            <div class="colorpicker-wrapper">
                                                <div style="background-color:<?= esc_attr( $abandoned_cart_obj['background_color'] ) ?>" class="view" ></div>
                                                <input id="background_color" type="text" name="background_color" value="<?= esc_attr( $abandoned_cart_obj['background_color'] ) ?>"  autocomplete="off" />
                                                <p><?= _e( 'Select Color', 'egoi_sms_follow_price_enable_title' ) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div>
                                        <div class="smsnf-input-group">
                                            <label for="bar-text-color" style="font-size: 13px;"><?php _e( 'Text Color', 'egoi_sms_follow_price_enable_title' ); ?></label>

                                            <div class="colorpicker-wrapper">
                                                <div style="background-color:<?= esc_attr( $abandoned_cart_obj['text_color'] ) ?>" class="view" ></div>
                                                <input id="text_color" type="text" name="text_color" value="<?= esc_attr( $abandoned_cart_obj['text_color'] ) ?>"  autocomplete="off" />
                                                <p><?= _e( 'Select Color', 'egoi_sms_follow_price_enable_title' ) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <div>
                                        <div class="smsnf-input-group">
                                            <label for="bar-text-color" style="font-size: 13px;"><?php _e( 'Button Color', 'egoi_sms_follow_price_enable_title' ); ?></label>

                                            <div class="colorpicker-wrapper">
                                                <div style="background-color:<?= esc_attr( $abandoned_cart_obj['button_color'] ) ?>" class="view" ></div>
                                                <input id="button_color" type="text" name="button_color" value="<?= esc_attr( $abandoned_cart_obj['button_color'] ) ?>"  autocomplete="off" />
                                                <p><?= _e( 'Select Color', 'egoi_sms_follow_price_enable_title' ) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <div>
                                        <div class="smsnf-input-group">
                                            <label for="bar-text-color" style="font-size: 13px;"><?php _e( 'Button Text Color', 'egoi_sms_follow_price_enable_title' ); ?></label>

                                            <div class="colorpicker-wrapper">
                                                <div style="background-color:<?= esc_attr( $abandoned_cart_obj['button_text_color'] ) ?>" class="view" ></div>
                                                <input id="button_text_color" type="text" name="button_text_color" value="<?= esc_attr( $abandoned_cart_obj['button_text_color'] ) ?>"  autocomplete="off" />
                                                <p><?= _e( 'Select Color', 'egoi_sms_follow_price_enable_title' ) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <div id="sms_order_abandoned_cart">
                        <?php submit_button(); ?>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>