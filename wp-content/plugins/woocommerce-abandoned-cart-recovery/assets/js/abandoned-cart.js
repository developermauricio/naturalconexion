jQuery(document).ready(function ($) {
    'use strict';

    let userRef = '';

    if (typeof wacvParams === 'undefined') {
        return;
    }

    const wacvCookie = {
        get(cname) {
            let name = cname + "=",
                decodedCookie = decodeURIComponent(document.cookie),
                ca = decodedCookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        },

        set(cname, cvalue, exdays = 86400) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 1000));//* 24 * 60 * 60
            var expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }
    };

    const wacvFbCheckBox = {

        checkedStt: false,
        cbRender: false,
        fbCbRequire: !!(parseInt(wacvParams.fbCbRequire)),

        init() {
            this.checkAjaxSend = false;
            if (!$('#wacv-modal').length) {
                this.setFbCheckBox();
            }
            this.confirmOptin();
            this.requireCheckbox();
        },

        setFbCheckBox() {
            let _this = this;
            if (wacvCookie.get('wacv_fb_checkbox')) return;

            if (!(parseInt(wacvParams.appID) > 0 && parseInt(wacvParams.pageID) > 0)) return;

            userRef = Date.now();

            let html = `<div class='fb-messenger-checkbox' origin='${wacvParams.homeURL}' page_id='${wacvParams.pageID}' messenger_app_id='${wacvParams.appID}' user_ref='${userRef}'
                        allow_login='true' size='large' ref='wacv_ref_message' skin='${wacvParams.appSkin}'></div>`;

            $('.fb-messenger-checkbox-container').append(html);

            (function (d, s, id) {  //connect fb to render checkbox plugin
                // if (Fbook.appID && Fbook.userToken) {
                let js, fjs = d.getElementsByTagName(s)[0], lang = wacvParams.appLang || 'en_US';
                if (d.getElementById(id)) {
                    return;
                }
                js = d.createElement(s);
                js.id = id;
                js.src = "https://connect.facebook.net/" + lang + "/sdk.js"; // whole SDK
                fjs.parentNode.insertBefore(js, fjs);

            }(document, 'script', 'facebook-jssdk'));

            window.fbAsyncInit = function () {

                FB.init({
                    appId: wacvParams.appID,
                    autoLogAppEvents: true,
                    xfbml: true,
                    version: "v9.0"
                });

                FB.Event.subscribe('messenger_checkbox', function (e) {
                    switch (e.event) {
                        case 'rendered':
                            _this.cbRender = true;
                            // console.log("rendered");
                            break;
                        case 'checkbox':
                            if (e.state === 'checked') {
                                _this.checkedStt = true;
                                wacvCookie.set('wacv_fb_checkbox', true);
                            }
                            break;
                        case 'not_you':
                            // console.log("User clicked 'not you'");
                            break;
                    }
                });

                FB.getLoginStatus(function (response) {
                    if (response.status === 'connected') {
                        //console.log(response);
                    } else if (response.status === 'not_authorized') {
                        //console.log('not connected to app');
                    } else if (response.status === 'unknown') {
                        _this.fbCbRequire = false;
                        //console.log('not logged in to fb');
                    }
                });
            };
        },

        getUserRef(atcButton) {
            let _this = this;
            $.ajax({
                url: wacvParams.ajaxUrl,
                type: 'post',
                data: {action: 'wacv_get_info', user_ref: userRef},
                success(res) {
                    _this.checkAjaxSend = true;
                    atcButton.trigger('click');
                }
            });
        },

        confirmOptin() {
            let _this = this;
            $('form.cart button[type=submit], .single_add_to_cart_button, .ajax_add_to_cart').on('click', function (e) {
                if (_this.checkedStt && !_this.checkAjaxSend) {
                    let thisAtcButton = $(this);
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    FB.AppEvents.logEvent('MessengerCheckboxUserConfirmation', null, {
                        'app_id': wacvParams.appID,
                        'page_id': wacvParams.pageID,
                        'ref': 'wacv_ref_message',
                        'user_ref': userRef
                    });

                    _this.getUserRef(thisAtcButton);
                }
            });
        },

        requireCheckbox() {
            let _this = this;
            $('form.cart button[type=submit], .single_add_to_cart_button, .ajax_add_to_cart, .wacv-add-to-cart-btn').on('click', function (e) {
                if (!_this.fbCbRequire) return;
                if (!_this.checkedStt && _this.cbRender) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    $('.fb-messenger-checkbox').css({'border': '1px solid red', 'border-radius': '5px'});
                }
            });
        }
    };

    wacvFbCheckBox.init();

    const wacvGetEmail = {
        atcButton: '',
        redirectAfterATC: '',
        popup: '',

        init() {
            this.popup = $('#wacv-modal');
            this.showPopup();
            this.chooseAction();
            this.afterAjaxATC();
        },

        showPopup: function () {
            if (!this.popup.length) return;

            let _this = this;

            $('form.cart button[type=submit], .single_add_to_cart_button, .ajax_add_to_cart, .add_to_cart_button, .fw-pricing .fw-button-row a.button,.wacv-open-popup').on('mousedown', function (e) {
                $('[tabindex="-1"]').removeAttr('tabindex');
                if (wacvCookie.get('wacv_get_email')) return;
                e.stopImmediatePropagation();
                e.preventDefault();
                _this.atcButton = $(this);

                if (!_this.atcButton.hasClass('ajax_add_to_cart') && wacvParams.redirect) {
                    _this.atcButton.after(`<input type="hidden" name="wacv_redirect" value="${wacvParams.redirect}">`);
                } else {
                    _this.redirectAfterATC = true;
                }

                _this.popup.fadeIn(300);

                wacvFbCheckBox.setFbCheckBox();
            });
        },

        chooseAction() {
            let _this = this;
            $('.wacv-add-to-cart-btn').on('click', function () {
                $('.wacv-email-invalid-notice, .wacv-phone-number-invalid-notice').hide();

                let email = $('.wacv-popup-input-email').val(),
                    phone = $('.wacv-popup-input-phone-number').val(),
                    gdprCb = $('.wacv-gdpr-checkbox'),
                    error = false;
                phone = _this.formatPhoneNumber(phone);

                if (parseInt(wacvParams.gdprField)) {
                    gdprCb.removeClass('wacv-error');
                    let checkChecked = $('.wacv-gdpr-checkbox:checked').length;
                    if (checkChecked === 0) {
                        gdprCb.addClass('wacv-error');
                        error = true;
                    }
                }

                if (!_this.emailValidation(email) && parseInt(wacvParams.emailField)) {
                    $('.wacv-email-invalid-notice').show();
                    error = true;
                }

                if (!_this.phoneValidation(phone) && parseInt(wacvParams.phoneField) && wacvParams.style === 'template-1') {
                    $('.wacv-phone-number-invalid-notice').show();
                    error = true;
                }

                if (!error) _this.importInfo();

            });

            $('.wacv-close-popup').on('click', function () { //, .wacv-modal-get-email
                wacvCookie.set('wacv_get_email', true, wacvParams.dismissDelay);//wacvParams.dismissDelay
                _this.popup.fadeOut(300);
                let events = $._data(_this.atcButton.get(0), 'events');
                let hasClick = events && events.click && events.click.length > 0 ? true : false;

                if (_this.atcButton.is('a') && !hasClick) {
                    window.location.href = _this.atcButton.attr('href');
                } else {
                    _this.atcButton.trigger('click');
                }
            });
        },

        emailValidation(email) {
            email = email ? email.trim() : email;
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
        },

        phoneValidation(phone) {
            return /^\d{9,10}$/im.test(phone);
            // return /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im.test(phone)
        },

        formatPhoneNumber(phone) {
            if (phone) {
                phone = phone.replace(/\(|\)|-|\.|\s|\+/gi, '');
                let firstChar = phone.slice(0, 1);
                phone = parseInt(firstChar) === 0 ? phone.slice(1) : phone;
            }

            return phone;
        },

        importInfo() {
            let _this = this;
            this.popup.fadeOut(200);

            let email = $('.wacv-popup-input-email').val(),
                countryCallingCode = $('.wacv-country-calling-code').val(),
                phone = _this.formatPhoneNumber($('.wacv-popup-input-phone-number').val());

            phone = countryCallingCode + phone;

            let data = {
                action: 'wacv_get_info',
                billing_email: email,
                billing_phone: phone || '',
                status: 'subscribe'
            };

            if (wacvFbCheckBox.checkedStt) data.user_ref = userRef;

            $.ajax({
                url: wacvParams.ajaxUrl,
                data: data,
                type: "post",
                xhrFields: {withCredentials: true},
                beforeSend: function () {
                    $('.wacv-add-to-cart-btn').addClass('loading');
                },
                success: function (res) {
                    wacvCookie.set('wacv_get_email', true);

                    let events = $._data(_this.atcButton.get(0), 'events');
                    let hasClick = events && events.click && events.click.length > 0 ? true : false;

                    if (_this.atcButton.is('a') && !hasClick) {
                        window.location.href = _this.atcButton.attr('href');
                    } else {
                        _this.atcButton.trigger('click');
                    }
                },
                error: function (res) {
                    // console.log(res);
                }
            });
        },

        afterAjaxATC() {
            $(document).ajaxComplete(function (event, xhr, settings) {
                if (settings.url === "/?wc-ajax=add_to_cart" && wacvGetEmail.redirectAfterATC === true && wacvParams.redirect) {
                    window.location.replace(wacvParams.redirect);
                } else {
                    wacvGetEmail.showPopup();
                }
            });
        },

    };

    wacvGetEmail.init();

});