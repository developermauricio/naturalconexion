<?php

class Smart_Marketing_Addon_Sms_Order_Cellphone_Popup{

    const TRIGGERS = ['on_leave', 'on_click'];

    const DEFAULT_CONFIG = [
        'type' => 'abandoned_cart',
        'trigger' => 'on_leave'
    ];

    protected $abandonedCart = [];

	/**
	 * Smart_Marketing_Addon_Sms_Order_Cellphone_Popup constructor.
	 *
	 * @param string[] $config
	 */
    public function __construct($config = self::DEFAULT_CONFIG)
    {
	    $this->abandonedCart = json_decode(get_option('egoi_sms_abandoned_cart'), true);
    }

	/**
	 * @return bool
	 */
    public function printPopup()
    {

        if(empty($this->abandonedCart) || !empty($_SESSION['sid_eg'])) {
            return true;
        }
        ?>

        <style>
            .egoi-public-modal-cellphone{
                position: fixed;
                z-index: 999999;
                top: 39%;
                left: 39%;
                background: <?=$this->abandonedCart['background_color']; ?>;
                padding: 2em;
                border: 2px solid <?=$this->abandonedCart['background_color']; ?>;
                border-radius: 5px;
                filter: drop-shadow(9px 9px 11px grey);
            }

            .egoi-public-modal-cellphone input[type=text] {
                width: 190px;
                height: 30px;
                border: none;
                background-color: #fff;
                -moz-border-radius: 4px;
                border-radius: 4px;
                padding-left: 10px;
                padding-right: 10px;
                border: 1px solid #ccc;
            }

            .egoi-public-modal-cellphone input[type=submit],
            .egoi-public-modal-cellphone input[type=button]{
                font-size: 100%;
                margin: 0;
                line-height: 1;
                cursor: pointer;
                position: relative;
                text-decoration: none;
                overflow: visible;
                padding: .618em 1em;
                font-weight: 700;
                border-radius: 3px;
                left: auto;
                color: <?=$this->abandonedCart['button_text_color']; ?>;
                background-color: <?=$this->abandonedCart['button_color']; ?>;
                border: 0;
                display: inline-block;
                background-image: none;
                box-shadow: none;
                text-shadow: none;
            }
        </style>

        <div id="modal-cellphone" class="egoi-public-modal-cellphone" style="display:none">
            <form method="POST" action="#">
                <?php wp_nonce_field('egoi-public-modal-cellphone'); ?>
                <input type="hidden" name="action" value="egoiSaveCellphone" />
                <p style="color: <?=$this->abandonedCart['text_color']; ?>;"> <?=$this->abandonedCart['title_pop']; ?> </p>
                <p>  <span style="color: <?=$this->abandonedCart['text_color']; ?>;">+</span> <input type="text" name="prefixEgoiphone" placeholder="351" style="width: 50px;" /> <input type="text" name="egoiPhone" placeholder="917789988" /> </>
                <p> <input type="submit" value="<?=$this->abandonedCart['button_name']; ?>" />  <input type="button" id="egoi_cancel_abandoned_cart_popop" value="<?=$this->abandonedCart['button_cancel_name']; ?>" /> </p>
            </form>
        </div>

        <script type="text/javascript">
            (function( $ ) {
                $( document ).ready(function() {
                    var showPopup = true;
                    function addEvent(obj, evt, fn) {
                        if (obj.addEventListener) {
                            obj.addEventListener(evt, fn, false);
                        } else if (obj.attachEvent) {
                            obj.attachEvent("on" + evt, fn);
                        }
                    }

                    addEvent(window, "load", function (e) {
                        addEvent(document, "mouseout", function (e) {
                            if(!showPopup) return ;
                            e = e ? e : window.event;
                            var from = e.relatedTarget || e.toElement;
                            if (!from || from.nodeName == "HTML") {
                                showPopUP();
                            }
                        });

                        $("#egoi_cancel_abandoned_cart_popop").click(function () {
                            $('#modal-cellphone').hide();
                            showPopup = false;

                        });
                    });

                    function showPopUP() {
                        $('#modal-cellphone').show();
                    }
                });

            })(jQuery);
        </script>

        <?php
    }
}