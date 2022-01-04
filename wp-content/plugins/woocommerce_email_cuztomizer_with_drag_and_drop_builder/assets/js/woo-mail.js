if(jQuery != undefined){
    var $ = jQuery.noConflict();
}
'use strict';

let defaultFontFamily = [
    'inherit',
    'Georgia, serif',
    '\'Palatino Linotype\', \'Book Antiqua\', Palatino, serif',
    '\'Times New Roman\', Times, serif',
    'Arial, Helvetica, sans-serif',
    '\'Arial Black\', Gadget, sans-serif',
    '\'Comic Sans MS\', cursive, sans-serif',
    'Impact, Charcoal, sans-serif',
    '\'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif',
    'Tahoma, Geneva, sans-serif',
    '\'Trebuchet MS\', Helvetica, sans-serif',
    'Verdana, Geneva, sans-serif',
    '\'Courier New\', Courier, monospace',
    '\'Lucida Console\', Monaco, monospace'
];

function woo_email_customizer_enable_loader() {
    jQuery('.woo_emc_loader_outer > .woo_emc_loader').show();
}
function woo_email_customizer_disable_loader() {
    jQuery('.woo_emc_loader_outer > .woo_emc_loader').hide();
}

const confs = {
    storage: {
        /**
         * Get Email from localStorage
         */
        get: function(emailLang, emailType, selectedOrder, callback) {
            if (emailLang == '' ){
                emailLang = 'en_US';
            }
            let postData = {
                action: 'ajaxWooProcess',
                lang: emailLang,
                order_id: selectedOrder,
                email_type: emailType
            };

            return jQuery.get({url: woo_email_customizer_ajax_url, cache: false}, postData).then(response => {
                response = JSON.parse(response);
            try{
                response.email = response.email ? JSON.parse(response.email) : {
                    elements: [],
                    html: '',
                    emailSettings: {
                        options: {
                            paddingTop: "50px",
                            paddingLeft: "5px",
                            paddingBottom: "50px",
                            paddingRight: "5px",
                            backgroundColor: "#edf1e4"
                        },
                        type: 'emailSettings'
                    }
                };
            } catch (e){
                response.email = {
                    elements: [],
                    html: '',
                    emailSettings: {
                        options: {
                            paddingTop: "50px",
                            paddingLeft: "5px",
                            paddingBottom: "50px",
                            paddingRight: "5px",
                            backgroundColor: "#edf1e4"
                        },
                        type: 'emailSettings'
                    }
                }
            }
            if(response.email == null){
                response.email = {
                    elements: [],
                    html: '',
                    emailSettings: {
                        options: {
                            paddingTop: "50px",
                            paddingLeft: "5px",
                            paddingBottom: "50px",
                            paddingRight: "5px",
                            backgroundColor: "#edf1e4"
                        },
                        type: 'emailSettings'
                    }
                }
            }

            if(response.additionalstyles != undefined && response.additionalstyles != ''){
                response.email.additionalstyles = response.additionalstyles;
            }
            if(response.elements != undefined && response.elements != '' && response.elements != null){
                if(jQuery.type( response.elements ) === "string"){
                    response.email.elements = JSON.parse(response.elements);
                } else {
                    response.email.elements = response.elements;
                }
            }
            if(response.emailSettings != undefined && response.emailSettings != '' && response.emailSettings != null){
                if(jQuery.type( response.emailSettings ) === "string"){
                    response.email.emailSettings = JSON.parse(response.emailSettings);
                } else {
                    response.email.emailSettings = response.emailSettings;
                }
            }
            if(response.html != undefined && response.html != ''){
                response.email.html = response.html;
            }
            if(response.styles != undefined && response.styles != '' && response.styles != null){
                if(jQuery.type( response.styles ) === "string"){
                    response.email.styles = JSON.parse(response.styles);
                } else {
                    response.email.styles = response.styles;
                }
            }

            if(response.additional_style != undefined){
                jQuery('#email-builder-additional-style').html(response.additional_style);
            }
            callback && callback(response)
        }, console.error);
        },

        /**
         * Put changed data in Email
         * Emulate server storage with Promise
         * @param selectedOrder
         * @param emailType
         * @param email
         * @param callback
         * @returns {Promise}
         */
        put: function(emailLang, emailType, selectedOrder, email, callback) {
            email.html = utils.removeLineBreaks(email.html);
            email.styles = email.elements.map(function(element) {
                let styles = {}
                styles[element.id] = {}
                let isStyle = ['padding', 'backgroundColor', 'color', 'font']
                Object.keys(element.options).forEach(function(option) {
                    if (isStyle.indexOf(option) !== -1) {
                        let value = element.options[option]
                        if (jQuery.isArray(element.options[option])) {
                            value = element.options[option].join(' ')
                        } else if (option == 'font') {
                            value = element.options[option].family
                        }
                        styles[element.id][utils.camelToSnake(option)] = value
                    }
                })
                return styles
            })

            function getCSS(a) {
                var sheets = document.styleSheets, o = {};
                var isSafari = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && safari.pushNotification));
                for (var i in sheets) {
                    try {
                        var rules = sheets[i].rules || sheets[i].cssRules;
                    } catch(e) {
                        if(e.name !== "SecurityError") {
                            throw e;
                        }
                    }
                    for (var r in rules) {
                        if(a.attr('style') != ''){
                            o = jQuery.extend(o, css2json(a.attr('style')));
                        }
                        if(!isSafari){
                            if (a.is(rules[r].selectorText)) {
                                o = jQuery.extend(o, css2json(rules[r].style), css2json(a.attr('style')));
                            }
                        }
                    }
                }
                return o;
            }

            function css2json(css) {
                var s = {};
                if (!css) return s;
                if (css instanceof CSSStyleDeclaration) {
                    for (var i in css) {
                        if ((css[i]).toLowerCase) {
                            s[(css[i]).toLowerCase()] = (css[css[i]]);
                        }
                    }
                } else if (typeof css == "string") {
                    try {
                        css = css.split("; ");
                        for (var i in css) {
                            var l = css[i].split(": ");
                            s[l[0].toLowerCase()] = (l[1]);
                        }
                    } catch(e) {
                    }

                }
                return s;
            }
            var IDs = {};
            jQuery(".email-builder-content .builder-element").find("[id]").each(function(){
                IDs[this.id]  = getCSS(jQuery('#'+this.id));
            });
            var templateStyles = '';
            jQuery.each(IDs, function(elementId, element_css){
                templateStyles += '#'+elementId+'{';
                jQuery.each(element_css, function(css_property, css_property_value){
                    if(css_property_value != '' && css_property_value != null){
                        templateStyles += css_property+': ';
                        css_property_value = css_property_value.replace(';','');
                        templateStyles += css_property_value+';';
                    }
                });
                templateStyles += '}';
            });
            email.additionalstyles = templateStyles;
            let postData = {
                action: 'ajaxSaveTemplate',
                lang: emailLang,
                order_id: selectedOrder,
                email_type: emailType,
                email: encodeURIComponent(JSON.stringify(email)),
                additionalstyles: email.additionalstyles,
                elements: JSON.stringify(email.elements),
                emailSettings: JSON.stringify(email.emailSettings),
                html: encodeURIComponent(email.html),
                styles: JSON.stringify(email.styles),
            };

            return jQuery.post({url: woo_email_customizer_ajax_url, cache: false}, postData).then(response => {
                callback && callback(JSON.parse(response))
        }, console.error).done(function() {woo_email_customizer_disable_loader();});

        },
        addTableFirstAndLastClass: function(){
            jQuery('.email-container > .builder-element table.em-main').removeClass('em-main-first-con').removeClass('em-main-last-con');
            jQuery('.email-container > .builder-element:first-child table.em-main').addClass('em-main-first-con');
            jQuery('.email-container > .builder-element:last-child table.em-main').addClass('em-main-last-con');
        }
    },
    options: {
        urlToUploadImage: '//uploads.im/api',
        trackEvents: false // You need to add google analytics in index.html
    }
};
let utils = {
    /**
     * Convert string from snake to camel
     * @param str
     * @returns {*}
     */
    snakeToCamel: function(str) {
        if (typeof str !== 'string')  return str;
        return str.replace(/_([a-z])/gi, function(m, w) {
            return "" + w.toUpperCase();
        });
    },
    /**
     * Convert camel to snake
     * @param str
     * @returns {*}
     */
    camelToSnake: function(str) {
        if (typeof str !== 'string') return str;
        return str.replace(/([A-Z])/g, function(m, w) {
            return "_" + w.toLowerCase();
        });
    },
    /**
     * Generate random id
     * @param prefix
     * @returns {string}
     */
    uid: function(prefix) {
        return (prefix || 'id') + (new Date().getTime()) + "RAND" + (Math.ceil(Math.random() * 100000));
    },
    /**
     * Strip email html for unnecessary attributes, classes ...
     * @param htmlToInsert
     * @param settings
     * @returns {string|*|Object|string|string}
     */
    stripTags: function(htmlToInsert, settings) {
        let builderDoc = document.createElement("html");
        jQuery(builderDoc).append(jQuery('<head/>'));
        jQuery(builderDoc).append(jQuery('<body/>'));
        /*let before_html = '<table class="wec_outer_table" height="100%" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%;"><tbody><tr><td valign="top" style="padding: 0;margin: 0;width: 100%;text-align: center">';
        let after_html = ' </td></tr></tbody></table>';
        htmlToInsert = before_html+htmlToInsert+after_html;*/
        // All meta and styles in head
        if (!jQuery(builderDoc).find('head meta[http-equiv="Content-Type"]').length) {
            jQuery(builderDoc).find('head').append(jQuery('<meta/>', {
                'http-equiv': 'Content-Type',
                'content': 'text/html; charset=UTF-8'
            }));
        }
        if (!jQuery(builderDoc).find('head meta[name="viewport"]').length) {
            jQuery(builderDoc).find('head').append(jQuery('<meta/>', {
                'name': 'viewport',
                'content': 'width=device-width',
                'initial-scale': '1.0',
                'user-scalable': 'yes'
            }));
        }
        if (!jQuery(builderDoc).find('head style#builder-styles').length) {
            let builderStyles = jQuery(document).find('style#builder-styles').clone();
            jQuery(builderDoc).find('head').append(builderStyles);
        }
        jQuery(builderDoc).find('body').addClass("email_builder");

        // Body style and html
        jQuery(builderDoc).find('body').css({
            'background': settings.options.backgroundColor,
            'padding': settings.options.paddingTop + ' ' + settings.options.paddingRight + ' ' + settings.options.paddingBottom + ' ' + settings.options.paddingLeft
        }).html(htmlToInsert);

        jQuery(builderDoc).find('i.actions').each(function() {
            jQuery(this).remove();
        });
        jQuery(builderDoc).find('.builder-element').each(function() {
            jQuery(this).replaceWith(jQuery(this).contents());
        });
        // Return shortcodes back
        jQuery(builderDoc).find('[data-shordcode]').each(function() {
            jQuery(this).replaceWith(jQuery(this).data('shordcode'));
        });
        // jQuery(builderDoc).find('span[data-shordcode]').each(function() {
        //     jQuery(this).replaceWith(jQuery(this).data('shordcode'));
        // });
        jQuery(builderDoc).contents().contents().addBack().filter(function() {
            return this.nodeType == Node.COMMENT_NODE;
        }).remove();

        return jQuery(builderDoc)[0].outerHTML;
    },

    /**
     * Notify
     * @param msg
     * @param callback
     * @returns {{log: log, success: success, error: error}}
     */
    notify: function(msg, callback) {
        return {
            log: function() {
                return alertify.log(msg, callback)
            },
            success: function() {
                alertify.success(msg, callback)
            },
            error: function() {
                alertify.error(msg, callback)
            }
        }
    },

    /**
     * Confirm dialog
     * @param msg
     * @param succesFn
     * @param cancelFn
     * @param okBtn
     * @param cancelBtn
     * @returns {IAlertify}
     */
    confirm: function(msg, succesFn, cancelFn, okBtn, cancelBtn) {
        return alertify
            .okBtn(okBtn)
            .cancelBtn(cancelBtn)
            .confirm(msg, succesFn, cancelFn)
    },

    /**
     * Alert dialog
     * @param msg
     * @returns {IAlertify}
     */
    alert: function(msg) {
        return alertify
            .okBtn("Accept")
            .alert(msg)
    },

    /**
     * Prompt dialog
     * @param defaultvalue
     * @param promptMessage
     * @param successFn
     * @param cancelFn
     * @returns {IAlertify}
     */
    prompt: function(defaultvalue, promptMessage, successFn, cancelFn) {
        return alertify
            .defaultValue(defaultvalue)
            .prompt(promptMessage, successFn, cancelFn)
    },

    /**
     * Validate email before save and import
     * @param emailToValidate
     * @returns {boolean}
     */
    validateEmail: function(emailToValidate) {
        return Vue.util.isObject(emailToValidate) &&
            jQuery.isArray(emailToValidate.elements) &&
            typeof emailToValidate.html == 'string' &&
            Vue.util.isObject(emailToValidate.emailSettings) &&
            emailToValidate.emailSettings.type == 'emailSettings' &&
            Vue.util.isObject(emailToValidate.emailSettings.options)
    },

    /**
     * Track events with Google Analytics
     * @param category
     * @param event
     * @param name
     * @returns {*}
     */
    trackEvent: function(category, event, name) {
        if (confs.trackEvents) {
            if (!ga)
                throw new Error('To track events, include Google analytics code in index.html');
            return ga('send', 'event', category, event, name);
        }
    },
    equals: function(obj1, obj2) {
        function _equals(obj1, obj2) {
            let clone = jQuery.extend(true, {}, obj1),
                cloneStr = JSON.stringify(clone);
            return cloneStr === JSON.stringify(jQuery.extend(true, clone, obj2));
        }
        return _equals(obj1, obj2) && _equals(obj2, obj1);
    },
    removeLineBreaks: function(html) {
        return html.replace(/\n\s*\n/gi, '\n');
    },
    initTooltips: function() {
        setTimeout(function() {
            jQuery('i[title], a[title], select[title]').powerTip({
                placement: 'sw-alt' // north-east tooltip position
            });
        }, 100)
    },
    clone(obj) {
        return JSON.parse(JSON.stringify(obj))
    }
};

let emailComponent = new Vue({
    components: {
        'email-builder-component': {
            data: function() {
                return {
                    preview: false,
                    loading: false,
                    showModal: false,
                    copyTemplate: false,
                    emailTypeFrom: '',
                    emailLangFrom: '',
                    emailCopyType: 'copy_to',
                    emailType: '',
                    emailTypeText: '',
                    emailLang: '',
                    emailLangText: '',
                    selectedOrder: '',
                    orderInfo: {},
                    currentElement: {},
                    elements: [
                        {
                            type: 'image',
                            icon: '',
                            iconClass: 'woombtrl-icon-picture',
                            primary_head: 'Logo',
                            second_head: 'Logo image'
                        },
                        {
                            type: 'title',
                            icon: '',
                            iconClass: 'woombtrl-icon-progress-0',
                            primary_head: 'Title',
                            second_head: 'And subtitle'
                        },
                        {
                            type: 'divider',
                            icon: '',
                            iconClass: 'woombtrl-icon-minus-1',
                            primary_head: 'Divider',
                            second_head: '1px separation line'
                        },
                        {
                            type: 'itemTable',
                            icon: '',
                            iconClass: 'woombtrl-icon-table',
                            primary_head: 'Order Item',
                            second_head: 'Product Items'
                        },
                        {
                            type: 'billingAddress',
                            icon: '',
                            iconClass: 'woombtrl-icon-id-card-o',
                            primary_head: 'Billing Address',
                            second_head: 'Billing'
                        },
                        {
                            type: 'shippingAddress',
                            icon: '',
                            iconClass: 'woombtrl-icon-truck',
                            primary_head: 'Shipping Address',
                            second_head: 'Shipping'
                        },
                        {
                            type: 'text',
                            icon: '',
                            iconClass: 'woombtrl-icon-th-list',
                            primary_head: 'Text',
                            second_head: 'Editable text box'
                        },
                        {
                            type: 'imageText2x2',
                            icon: '',
                            iconClass: 'woombtrl-icon-th-large-1',
                            primary_head: 'Billing and Shipping Address',
                            second_head: '2 columns'
                        },
                        {
                            type: 'imageText3x2',
                            icon: '',
                            iconClass: 'woombtrl-icon-th',
                            primary_head: 'Customer Info and Addresses',
                            second_head: '3 columns'
                        },
                        {
                            type: 'unsubscribe',
                            icon: '',
                            iconClass: 'woombtrl-icon-window-minimize',
                            primary_head: 'Footer',
                            second_head: 'Block with Footer text'
                        },
                        {
                            type: 'social',
                            icon: 'share',
                            primary_head: 'Social Icons',
                            second_head: '4 social icons'
                        },
                        {
                            type: 'button',
                            icon: '&#xE913;',
                            primary_head: 'Button',
                            second_head: 'Clickable URL button'
                        },
                        {
                            type: 'coupon',
                            icon: 'card_giftcard',//crop_free
                            iconClass: 'retainful_coupon',
                            primary_head: 'Coupon',
                            second_head: 'Next order coupon code'
                        }
                    ],
                    defaultOptions: {
                        'title': {
                            type: 'title',
                            options: {
                                align: 'center',
                                title: 'Enter your title here', // Enter your title here
                                subTitle: 'Subtitle', // Subtitle
                                padding: ["30px", "50px", "30px", "50px"],
                                backgroundColor: '#edf1e4',
                                color: '#444444',
                                font: {
                                    family: 'inherit',
                                    familyOptions: defaultFontFamily
                                }
                            }
                        },
                        'divider': {
                            type: 'divider',
                            options: {
                                padding: ['15px', '50px', '0px', '50px'],
                                backgroundColor: '#ffffff'
                            }
                        },
                        'text': {
                            type: 'text',
                            options: {
                                padding: ['10px', '50px', '10px', '50px'],
                                backgroundColor: '#ffffff',
                                font: {
                                    family: 'inherit',
                                    familyOptions: defaultFontFamily
                                },
                                text: '<p style="margin:0 0 10px 0;line-height:22px;font-size:13px;" data-block-id="text-area">Lorem ipsum dolor sit amet, consectetur adipisci elit, sed eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur. Quis aute iure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. </p>'
                            }
                        },
                        'button': {
                            type: 'button',
                            options: {
                                align: 'center',
                                padding: ['15px', '50px', '15px', '50px'],
                                buttonText: 'Click me',
                                url: '#',
                                buttonBackgroundColor: '#3498DB',
                                backgroundColor: '#ffffff',
                                font: {
                                    size: 15,
                                    color: '#ffffff',
                                    weight: 'normal',
                                    weightOptions: ['bold', 'bolder', 'lighter', 'inherit', 'initial', 'normal', 100, 200, 300, 400, 500, 600, 700, 800, 900],
                                    family: 'inherit',
                                    familyOptions: defaultFontFamily
                                }
                            }
                        },
                        'image': {
                            type: 'image',
                            options: {
                                align: 'center',
                                padding: ["15px", "50px", "15px", "50px"],
                                image: woo_email_customizer_page_builder.plugin_url + '/assets/images/woo-logo-vector.png',
                                backgroundColor: '#edf1e4'
                            }
                        },
                        'itemTable': {
                            type: 'itemTable',
                            options: {
                                padding: ["15px", "50px", "15px", "50px"],
                                width: '370',
                                backgroundColor: '#ffffff',
                                font: {
                                    family: 'inherit',
                                    familyOptions: defaultFontFamily
                                },
                                text: '<p style="line-height: 22px;">[woo_mb_items]</p>'
                            }
                        },
                        'shippingAddress': {
                            type: 'shippingAddress',
                            options: {
                                padding: ["15px", "50px", "15px", "50px"],
                                width: '370',
                                backgroundColor: '#ffffff',
                                font: {
                                    family: 'inherit',
                                    familyOptions: defaultFontFamily
                                },
                                text: '<p style="line-height: 22px;"><div><strong>shipping Address:</strong></div>[woo_mb_shipping_address]</p>'
                            }
                        },
                        'billingAddress': {
                            type: 'billingAddress',
                            options: {
                                padding: ["15px", "50px", "15px", "50px"],
                                width: '370',
                                backgroundColor: '#ffffff',
                                font: {
                                    family: 'inherit',
                                    familyOptions: defaultFontFamily
                                },
                                text: '<p style="line-height: 22px;"><div><strong>Billing Address:</strong></div>[woo_mb_billing_address]</p>'
                            }
                        },
                        'imageTextRight': {
                            type: 'imageTextRight',
                            options: {
                                padding: ["15px", "50px", "15px", "50px"],
                                image: woo_email_customizer_page_builder.plugin_url + '/assets/images/340x145.jpg',
                                width: '330',
                                backgroundColor: '#ffffff',
                                font: {
                                    family: 'inherit',
                                    familyOptions: defaultFontFamily
                                },
                                text: '<p style="line-height: 22px;">Lorem ipsum dolor sit amet, consectetur adipisci elit, sed eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam.</p>'
                            }
                        },
                        'imageText2x2': {
                            type: 'imageText2x2',
                            options: {
                                padding: ["15px", "50px", "15px", "50px"],
                                width1: '255',
                                width2: '255',
                                backgroundColor: '#ffffff',
                                font: {
                                    family: 'inherit',
                                    familyOptions: defaultFontFamily
                                },
                                buttons: [
                                    {
                                        active: false,
                                        align: 'center',
                                        backgroundColor: '#3498db',
                                        text: 'Button 1',
                                        link: '#',
                                        fullWidth: false
                                    },
                                    {
                                        active: false,
                                        align: 'center',
                                        backgroundColor: '#3498db',
                                        text: 'Button 2',
                                        link: '#',
                                        fullWidth: false
                                    }
                                ],
                                text1: '<div><strong>Billing Address: </strong><p style="line-height: 22px;">[woo_mb_billing_address]</p></div>',
                                text2: '<div><strong>Shipping Address: </strong><p style="line-height: 22px;">[woo_mb_shipping_address]</p></div>'
                            }
                        },
                        'imageText3x2': {
                            type: 'imageText3x2',
                            options: {
                                padding: ["15px", "50px", "15px", "50px"],
                                // image1Hide: false,
                                // image1: woo_email_customizer_page_builder.plugin_url + '/assets/images/154x160.jpg',
                                // image2Hide: false,
                                // image2: woo_email_customizer_page_builder.plugin_url + '/assets/images/154x160.jpg',
                                // image3Hide: false,
                                // image3: woo_email_customizer_page_builder.plugin_url + '/assets/images/154x160.jpg',
                                width1: '154',
                                width2: '154',
                                width3: '154',
                                backgroundColor: '#ffffff',
                                font: {
                                    family: 'inherit',
                                    familyOptions: defaultFontFamily
                                },
                                text1: '<div><strong>Shipping Address: </strong><p style="line-height: 22px;">[woo_mb_shipping_address]</p></div>',
                                text2: '<div><strong>Customer Information: </strong><p style="line-height: 22px;">[woo_mb_shipping_first_name]</br>[woo_mb_user_email]</p></div>',
                                text3: '<div><strong>Billing Address: </strong><p style="line-height: 22px;">[woo_mb_billing_address]</p></div>'
                            }
                        },
                        'unsubscribe': {
                            type: 'unsubscribe',
                            options: {
                                padding: ['10px', '50px', '10px', '50px'],
                                backgroundColor: '#eeeeee',
                                font: {
                                    family: 'inherit',
                                    familyOptions: defaultFontFamily
                                },
                                text: '<p style="text-align: center; margin: 0px 0px 10px 0px; line-height: 22px; font-size: 13px;" data-block-id="text-area"><span style="font-size: 8pt; color: #333333;">If you\'d like to unsubscribe and stop receiving these emails<span style="color: #0000ff;"> <a style="color: #0000ff;" href="#">click here</a></span>.</span></p>'
                            }
                        },
                        'social': {
                            type: 'social',
                            options: {
                                align: 'center',
                                padding: ['10px', '50px', '10px', '50px'],
                                backgroundColor: '#eeeeee',
                                facebookLink: 'https://www.facebook.com/',
                                facebookImageUrl: '',
                                twitterLink: 'https://twitter.com/',
                                twitterImageUrl: '',
                                linkedinLink: '',
                                linkedinImageUrl: '',
                                youtubeLink: 'https://www.youtube.com/',
                                youtubeImageUrl: '',
                                instagramLink: '',
                                instagramImageUrl: '',
                                pinterestLink: '',
                                pinterestImageUrl: '',
                                googlePlusLink: '',
                                googlePlusImageUrl: ''
                            }
                        },
                        'coupon': {
                            type: 'coupon',
                            options: {
                                padding: ['15px', '50px', '15px', '50px'],
                                backgroundColor: '#ffffff',
                                text: `<div class="coupon-block" style="font-family:font-family:'Trebuchet MS', Helvitica,sans-serif;padding: 30px;background: #f7f7f7;display: inline-block;text-align:center;margin: 10px auto;box-shadow: 0 7px 14px 0 rgba(59,65,94, 0.1), 0 3px 6px 0 rgba(0, 0, 0, .07);border: 1px solid #dddddd;border-radius: 4px;">
                                        <h3 style="font-family:Helvitica,sans-serif;font-size: 25px;font-weight: 500;color: #222;margin: 0 0 15px;text-align:center;">[wec_next_order_coupon_value] Off Your Next Purchase</h3>
                                        <p style="font-family:Helvitica,sans-serif;font-size: 16px;font-weight: 500;color: #555;line-height:1.6;margin:15px 0 20px;text-align:center;">To thank you for being a loyal customer we want to offer you an exclusive coupon for [wec_next_order_coupon_value] off your next order!</p>
                                        <p style="font-family:'Trebuchet MS', Helvitica,sans-serif;text-align: center;">
                                            <span style="font-family:'Trebuchet MS', Helvitica,sans-serif;line-height: 1.6;font-size: 18px;font-weight:500;background:#ffffff;display: block;padding: 10px 20px;border: 2px dashed #8D71DB;color: #8D71DB;text-decoration: none;">
                                                [wec_next_order_coupon]
                                            </span>
                                        </p>
                                        <p style="font-family:'Trebuchet MS', Helvitica,sans-serif;text-align: center;margin: 0;">
                                            <a style="font-family:'Trebuchet MS', Helvitica,sans-serif;line-height: 1.8;font-size: 16px;font-weight:500; background: #8D71DB;display: block;padding: 10px;border: 1px solid #8D71DB;border-radius: 4px;color: #ffffff;text-decoration: none;" href="[woo_mb_site_url_link_with_coupon]" target="_blank">
                                                Go!
                                            </a>
                                        </p>
                                    </div>`,
                            }
                        },
                    },
                    Email: {},
                    clonedEmail: {}
                }
            },
            mounted: function() {
                this.$nextTick(() => {
                    utils.initTooltips();
                // Set email builder as parent form alertify
                //noinspection JSUnresolvedVariable
                alertify.parent(document.getElementById("email-builder"));
            });
            },
            watch: {
                Email: {
                    handler() {
                        utils.initTooltips();
                    },
                    deep: true
                }
            },
            methods: {
                hasChanges() {
                    confs.storage.addTableFirstAndLastClass();
                    return !utils.equals(this.Email, this.clonedEmail);
                },
                editElement(id) {
                    if (!id) {
                        return this.currentElement = {};
                    }
                    let self = this,
                        editElement = id !== 'emailSettings' ? self.Email.elements.find(function(element) {
                            return element.id == id;
                        }) : self.Email[id];

                    if (self.preview || self.currentElement == editElement) return;
                    self.currentElement = {};
                    setTimeout(function() {
                        self.currentElement = editElement;
                    }, 10);
                    confs.storage.addTableFirstAndLastClass();
                },
                removeElement(remElement) {
                    let self = this;
                    return utils.confirm('Are you sure?', function() {
                        self.Email.elements = self.Email.elements.filter(function(element) {
                            return element != remElement;
                        });
                        if (utils.equals(self.currentElement, remElement)) {
                            self.currentElement = {};
                        }
                        confs.storage.addTableFirstAndLastClass();
                    }, null, 'Delete element', 'Don\'t delete');
                },
                saveEmailTemplate() {
                    woo_email_customizer_enable_loader();
                    confs.storage.addTableFirstAndLastClass();
                    // Striping not necessary tags
                    this.Email.html = utils.stripTags(jQuery(this.$refs.emailElements.$el).html(), this.Email.emailSettings);
                    confs.storage.put(this.emailLang, this.emailType, this.selectedOrder, this.Email, (response) => {
                        utils.notify('Email has been saved.').success();
                    this.clonedEmail = utils.clone(this.Email);
                    this.currentElement = {};
                });
                },
                getEmailTemplate(eve) {
                    this.emailLangText = jQuery("#woo_mb_email_lang option:selected").html();
                    this.emailTypeText = jQuery("#woo_mb_email_type_select option:selected").html();

                    if (this.emailType !== '' && this.selectedOrder !== '') {
                        this.loading = true;
                        return confs.storage.get(this.emailLang, this.emailType, this.selectedOrder, response => {
                            this.loading = false;
                        this.Email = response.email;
                        this.clonedEmail = utils.clone(response.email);
                        this.orderInfo = response.order_data;
                        woo_email_customizer_resize_header_height();
                        setTimeout(function(){
                            confs.storage.addTableFirstAndLastClass();
                        }, 1000);
                    });
                    } else {
                        this.emailLang = jQuery('select#woo_mb_email_lang').val();
                    }
                },
                previewEmail() {
                    if (!this.Email.elements.length)
                        return utils.notify('Nothing to preview, please add some elements.').log();
                    this.preview = true;
                    this.currentElement = {};
                },
                sendTestMail(){
                    // Send Test Mail.
                    let postData = {
                        action: 'woo_mb_send_email',
                        mail: woo_email_customizer_user_mail,
                        lang: this.emailLang,
                        order_id: this.selectedOrder,
                        woo_mb_email_type: this.emailType,
                        body: this.Email.html
                    };
                    woo_email_customizer_enable_loader();
                    return jQuery.post(woo_email_customizer_ajax_url, postData).then(() => {
                        return utils.notify('Email has been sent').success()
                    }, console.error).done(function() {woo_email_customizer_disable_loader();});
                },
                copyTemplateFrom(){
                    // Copy email template from another.
                    if(this.emailTypeFrom == ''){
                        return utils.notify('Email type is required').error();
                    }
                    if(this.emailLang == this.emailLangFrom && this.emailType == this.emailTypeFrom){
                        return utils.notify('Please select a different email type or language').error();
                    }
                    let alert_message = 'Your current template content will be replaced from selected template, are you sure?';
                    if(this.emailCopyType == 'copy_to'){
                        alert_message = 'Your selected template content will be replaced by current template, are you sure?';
                    }
                    return utils.confirm(alert_message, () => {
                        let postData = {
                            action: 'ajaxWooEmailCopyTemplateFromAnother',
                            lang: this.emailLang,
                            email_type: this.emailType,
                            lang_from: this.emailLangFrom,
                            email_type_from: this.emailTypeFrom,
                            email_copy_type: this.emailCopyType
                        };
                        if(this.emailCopyType == 'copy_to'){
                            postData.lang = this.emailLangFrom;
                            postData.email_type = this.emailTypeFrom;
                            postData.lang_from = this.emailLang;
                            postData.email_type_from = this.emailType;
                        }
                    woo_email_customizer_enable_loader();
                    return jQuery.post(woo_email_customizer_ajax_url, postData).then(
                        response => {
                        response = JSON.parse(response);
                    if(response.status_code == '200'){
                        utils.notify(response.status_message).success();
                        location.reload();
                    } else {
                        return utils.notify(response.status_message).error()
                    }

                }, console.error).done(function() {woo_email_customizer_disable_loader();});
                }, null, 'Yes, I\'m sure', 'Remain here')
                },
                cloneElement(element) {
                    let newEl = JSON.parse(JSON.stringify(element));
                    newEl.id = utils.uid();
                    this.Email.elements.splice(this.Email.elements.indexOf(element) + 1, 0, newEl);
                },
                clone(obj) {
                    let newElement = jQuery.extend(true, {}, this.defaultOptions[obj.type]);
                    newElement.id = utils.uid();
                    newElement.component = obj.type + 'Template';
                    return newElement;
                },
                orderEmailSelected() {
                    return this.emailType !== '' && this.selectedOrder !== '' && !jQuery.isEmptyObject(this.Email)
                },
                backToAdmin() {
                    if (this.hasChanges()) {
                        return utils.confirm('All unsaved changes will be deleted, are you sure?', () => {
                            location.href = this.$refs.backButton.dataset.url;
                    }, null, 'Yes, I\'m sure', 'Remain here')
                    }
                    return location.href = this.$refs.backButton.dataset.url;
                },
                settings() {
                    let $elem_wooemail = jQuery('#email-builder > div');
                    let $elem_settings = jQuery('#email-builder-settings');
                    $elem_wooemail.hide();
                    $elem_settings.show();
                },
                backToWooEmail() {
                    location.reload();
                    /*let $elem_wooemail = jQuery('#email-builder > div');
                    let $elem_settings = jQuery('#email-builder-settings');
                    $elem_wooemail.show();
                    $elem_settings.hide();*/
                }
            },
            template: '#email-builder-template',
            directives: {
                mdInput: {
                    bind: function(el, binding, vnode) {
                        let $elem = jQuery(el);
                        let updateInput = function() {
                            // clear wrapper classes
                            $elem.closest('.md-input-wrapper').removeClass('md-input-wrapper-danger md-input-wrapper-success md-input-wrapper-disabled');

                            if ($elem.hasClass('md-input-danger')) {
                                $elem.closest('.md-input-wrapper').addClass('md-input-wrapper-danger')
                            }
                            if ($elem.hasClass('md-input-success')) {
                                $elem.closest('.md-input-wrapper').addClass('md-input-wrapper-success')
                            }
                            if ($elem.prop('disabled')) {
                                $elem.closest('.md-input-wrapper').addClass('md-input-wrapper-disabled')
                            }
                            if ($elem.hasClass('label-fixed')) {
                                $elem.closest('.md-input-wrapper').addClass('md-input-filled')
                            }
                            if ($elem.val() != '') {
                                $elem.closest('.md-input-wrapper').addClass('md-input-filled')
                            }
                        };

                        setTimeout(function() {
                            if (!$elem.hasClass('md-input-processed')) {

                                if ($elem.prev('label').length) {
                                    $elem.prev('label').addBack().wrapAll('<div class="md-input-wrapper"/>');
                                } else {
                                    $elem.wrap('<div class="md-input-wrapper"/>');
                                }
                                $elem
                                    .addClass('md-input-processed')
                                    .closest('.md-input-wrapper')
                                    .append('<span class="md-input-bar"/>');
                            }

                            updateInput();

                        }, 100);

                        $elem
                            .on('focus', function() {
                                $elem.closest('.md-input-wrapper').addClass('md-input-focus')
                            })
                            .on('blur', function() {
                                setTimeout(function() {
                                    $elem.closest('.md-input-wrapper').removeClass('md-input-focus');
                                    if ($elem.val() == '') {
                                        $elem.closest('.md-input-wrapper').removeClass('md-input-filled')
                                    } else {
                                        $elem.closest('.md-input-wrapper').addClass('md-input-filled')
                                    }
                                }, 100)
                            });
                    }
                },
                inputFileUpload: {
                    twoWay: true,
                    bind: function(elem, binding, vnode) {
                        let wrapper, inputText;

                        setTimeout(function() {

                            wrapper = jQuery(elem).closest('.md-input-wrapper');
                            inputText = wrapper.children('input:text');

                            inputText.css('paddingRight', '10px');
                            /*wrapper.append('<button type="button" class="md-icon upload-icon">\n    <i class="material-icons">file_upload</i>\n    <input type="file" name="file">\n</button>');

                            wrapper.find('input[type=file]').bind('change', function (event) {

                                if (!confs.options.urlToUploadImage)
                                    throw Error('You don\'t set the \'urlToUploadImage\' in variables.');

                                let inputFile = jQuery(this),
                                    icon = inputFile.prev('i.material-icons'),
                                    oldIconText = icon.text();
                                icon.text('hdr_strong').addClass('icon-spin').css('opacity', '.7');
                                inputFile.prop('disabled', true);
                                let formData = new FormData();
                                formData.append('upload', event.target.files[0]);
                                return jQuery.ajax({
                                    url: confs.options.urlToUploadImage,
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    type: 'POST',
                                    success: function(res){
                                        if (res.status_code == 200) {
                                            let customEvent = new Event('input', { bubbles: true }); // won't work in IE <11
                                            jQuery(elem).val(res.data.img_url);
                                            elem.dispatchEvent(customEvent);
                                            utils.notify('Your image has been uploaded').log()
                                        } else {
                                            utils.notify(res.status_txt).error()
                                        }
                                    },
                                    error: function (err) {
                                        utils.notify(err.statusText).error()
                                    },
                                    complete: function () {
                                        inputFile.prop('disabled', false);
                                        icon.text(oldIconText).removeClass('icon-spin').removeAttr('style');
                                    }
                                });
                            })*/
                        }, 100);

                    },
                    unbind: function(elem) {
                        jQuery(elem).unbind('change');
                    }
                },
                tinymceEditor: {
                    twoWay: true,
                    bind: function(elem) {
                        let self = elem;
                        tinymce.baseURL = woo_email_customizer_page_builder.plugin_url + '/assets/tinymce';
                        setTimeout(function() {
                            tinymce.init({
                                target: self,
                                inline: false,
                                skin: 'lightgray',
                                theme : 'modern',
                                relative_urls : false,
                                convert_urls: false,
                                remove_script_host : false,
                                plugins: ["advlist autolink lists link image charmap", "searchreplace visualblocks code", "insertdatetime media table contextmenu paste", 'textcolor'],
                                toolbar: "undo redo | styleselect | bold italic fontsizeselect forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
                                fontsize_formats: '8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 18pt 24pt 36pt',
                                setup: function(editor) {
                                    // init tinymce
                                    editor.on('init', function() {
                                        editor.setContent(self.value);
                                    });
                                    // when typing keyup event
                                    editor.on('keyup change', function() {
                                        // get new value
                                        let customEvent = new Event('input', { bubbles: true }); // won't work in IE <11
                                        self.value = editor.getContent({format: 'raw'});
                                        elem.dispatchEvent(customEvent);
                                    });
                                }
                            });
                        }, 100)
                    },
                    unbind: function() {
                        tinymce.editors.forEach(function(editor) {
                            return editor.destroy();
                        })
                    }
                }
            },
            filters: {
                makeTitle(value) {
                    if (!value) return '';
                    value = utils.camelToSnake(value);
                    value = value.charAt(0).toUpperCase() + value.slice(1);
                    return value.replace(/_/g, ' ');
                }
            },
            components: {
                titleTemplate: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"  :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main" cellspacing="0" cellpadding="0" border="0" align="center" :style="{backgroundColor: element.options.backgroundColor}" style="display: table;" data-type="title">\n    <tbody>\n    <tr>\n        <td :align="element.options.align" class="em-title" :id="element.id+\'em-title\'" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3]}" style="color: #757575;" data-block-id="background">\n            <h1 v-if="element.options.title.length" :style="{color: element.options.color, fontFamily: element.options.font.family}" style="margin: 0 !important; font-weight: 800; line-height: 42px; font-size: 36px;" data-block-id="main-title" :id="element.id+\'main-title\'" v-html="doShortcode(element.options.title)"></h1>\n            <h4 v-if="element.options.subTitle.length" :style="{color: element.options.color, fontFamily: element.options.font.family}" style="font-weight: 500; margin-bottom: 0; line-height: 22px; font-size: 16px;" data-block-id="sub-title" v-html="doShortcode(element.options.subTitle)"></h4>\n        </td>\n    </tr>\n    </tbody>\n</table>'
                },
                buttonTemplate: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                size: 15,
                                color: '#ffffff',
                                weight: 'normal',
                                weightOptions: ['bold', 'bolder', 'lighter', 'inherit', 'initial', 'normal', 100, 200, 300, 400, 500, 600, 700, 800, 900],
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"  :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main" cellspacing="0" cellpadding="0" border="0" :bgcolor="element.options.backgroundColor" align="center" style="display: table;" data-type="button">    <tbody>    <tr>        <td :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3]}" class="em-buttons-full-width" :id="element.id+\'em-buttons-full-width\'"><table cellspacing="0" cellpadding="0" border="0" :align="element.options.align" class="em-button"><tbody>    <tr>        <td style="margin: 10px 10px 10px 10px;" class="em-button" :id="element.id+\'em-button\'">            <a :style="{backgroundColor: element.options.buttonBackgroundColor, color: element.options.font.color, fontSize: element.options.font.size+\'px\', fontFamily: element.options.font.family, fontWeight: element.options.font.weight}" style="line-height:21px;border-radius: 6px;text-align: center;text-decoration: none;display: block;margin: 0 0; padding: 12px 20px;" class="em-button-1" :href="element.options.url" data-default="1" v-html="doShortcode(element.options.buttonText)"></a>                   <!--[if mso]>             </center>           </v:roundrect>         <![endif]-->        </td>    </tr>    </tbody></table>        </td>    </tr>    </tbody></table>'
                },
                textTemplate: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"   :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main" cellspacing="0" cellpadding="0" border="0" :style="{backgroundColor: element.options.backgroundColor}" style="display: table;" align="center" data-type="text-block">    <tbody>    <tr>        <td class="em-block-text" :id="element.id+\'em-block-text\'" data-block-id="background" align="left" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3], fontFamily: element.options.font.family}" style="font-size: 13px; color: #000000; line-height: 22px;" v-html="doShortcode(this.element.options.text)"> </td>    </tr>    </tbody></table>'
                },
                socialTemplate: {
                    props: ['element'],
                    beforeMount () {
                        if (this.element.options.instagramLink == undefined) {
                            this.element.options.instagramLink = '';
                        }
                        if (this.element.options.pinterestLink == undefined) {
                            this.element.options.pinterestLink = '';
                        }
                        if (this.element.options.googlePlusLink == undefined) {
                            this.element.options.googlePlusLink = '';
                        }
                        let image_urls = {
                            facebookImageUrl: "facebook.png",
                            twitterImageUrl: "twitter.png",
                            linkedinImageUrl: "linkedin.png",
                            youtubeImageUrl: "youtube.png",
                            instagramImageUrl: "instagram.png",
                            pinterestImageUrl: "pinterest.png",
                            googlePlusImageUrl: "google-plus.png"
                        };
                        jQuery.each(image_urls, ( index, value ) => {
                            if(this.element.options[index] == undefined){
                                this.element.options[index] = woo_email_customizer_page_builder.plugin_url + '/assets/images/social/'+value;
                            } else {
                                if(this.element.options[index] == ''){
                                    this.element.options[index] = woo_email_customizer_page_builder.plugin_url + '/assets/images/social/'+value;
                                }
                            }
                        });

                    },
                    template: '<table :bgcolor="element.options.backgroundColor"   class="em-main" :id="element.id" align="center" :width="woo_email_customizer_containerWidth" cellspacing="0" cellpadding="0" border="0" :style="{backgroundColor: element.options.backgroundColor}" style="display: table;" data-type="social-links">\n<tbody>\n<tr>\n<td class="em-social" :id="element.id+\'em-social\'" :align="element.options.align" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3]}">\n<a :href="element.options.facebookLink" target="_blank" style="border: none;text-decoration: none;" class="em-facebook">\n<img border="0" v-if="element.options.facebookLink.length" :src="this.element.options.facebookImageUrl" >\n            </a>\n<a :href="element.options.twitterLink" target="_blank" style="border: none;text-decoration: none;" class="em-twitter">\n<img border="0" v-if="element.options.twitterLink.length" :src="this.element.options.twitterImageUrl">\n</a>\n<a :href="element.options.linkedinLink" target="_blank" style="border: none;text-decoration: none;" class="em-linkedin">\n<img border="0" v-if="element.options.linkedinLink.length" :src="this.element.options.linkedinImageUrl">\n</a>\n<a :href="element.options.youtubeLink" target="_blank" style="border: none;text-decoration: none;" class="em-youtube">\n<img border="0" v-if="element.options.youtubeLink.length" :src="this.element.options.youtubeImageUrl">\n</a>\n <a :href="element.options.instagramLink" target="_blank" style="border: none;text-decoration: none;" class="em-instagram">\n<img border="0" v-if="element.options.instagramLink.length" :src="this.element.options.instagramImageUrl">\n</a>\n<a :href="element.options.pinterestLink" target="_blank" style="border: none;text-decoration: none;" class="em-pinterest">\n<img border="0" v-if="element.options.pinterestLink.length" :src="this.element.options.pinterestImageUrl">\n</a>\n<a :href="element.options.googlePlusLink" target="_blank" style="border: none;text-decoration: none;" class="em-googleplus">\n<img border="0" v-if="element.options.googlePlusLink.length" :src="this.element.options.googlePlusImageUrl">\n</a>\n</td>\n</tr>\n</tbody>\n</table>'
                },
                unsubscribeTemplate: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"   :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main" cellspacing="0" cellpadding="0" border="0" :style="{backgroundColor: element.options.backgroundColor}" style="display: table;" align="center" data-type="text-block">    <tbody>    <tr>        <td data-block-id="background" align="left" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3], fontFamily: element.options.font.family}" style="font-size: 13px; color: #000000; line-height: 22px;" v-html="doShortcode(element.options.text)" :id="element.id+\'em-unsubscribe\'">        </td>    </tr>    </tbody></table>'
                },
                dividerTemplate: {
                    props: ['element'],
                    template: '<table :bgcolor="element.options.backgroundColor"   class="em-main" :id="element.id" :width="woo_email_customizer_containerWidth" :style="{backgroundColor: element.options.backgroundColor}" style="border: 0; display: table;" cellspacing="0" cellpadding="0" border="0" align="center" data-type="divider">    <tbody>    <tr>        <td class="em-divider-simple"  :id="element.id+\'em-divider-simple\'" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3]}"><table width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px solid #DADFE1;">    <tbody>    <tr>        <td width="100%" height="15px"></td>    </tr>    </tbody></table>        </td>    </tr>    </tbody></table>'
                },
                imageTemplate: {
                    props: ['element'],
                    template: '<table :bgcolor="element.options.backgroundColor"   :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main"  cellspacing="0" cellpadding="0" border="0" align="center" :style="{backgroundColor: element.options.backgroundColor}" style="display: table;" data-type="image">    <tbody>    <tr>        <td :align="element.options.align" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3]}" class="em-image" :id="element.id+\'em-image\'"><img border="0" style="display:block;max-width:100%;" :src="element.options.image" tabindex="0">        </td>    </tr>    </tbody></table>'
                },
                itemTableTemplate: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"   :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main" cellspacing="0" cellpadding="0" border="0" align="center"   :style="{backgroundColor: element.options.backgroundColor}" style="display: table;" data-type="itemTable">    <tbody>    <tr>        <td align="left"   class="em-image-text" :id="element.id+\'em-image-text\'"  :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3], fontFamily: element.options.font.family}"     style="font-size: 13px; color: #000000; line-height: 22px;"><div v-html="doShortcode(element.options.text)"></div>        </td>    </tr>    </tbody></table>'
                },
                shippingAddressTemplate: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"   :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main" cellspacing="0" cellpadding="0" border="0" align="center"   :style="{backgroundColor: element.options.backgroundColor}" style="display: table;" data-type="shippingAddress">    <tbody>    <tr>        <td align="left"   class="em-image-text" :id="element.id+\'em-image-text\'" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3], fontFamily: element.options.font.family}"     style="font-size: 13px; color: #000000; line-height: 22px;"><div v-html="doShortcode(element.options.text)"></div>        </td>    </tr>    </tbody></table>'
                },
                billingAddressTemplate: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"   :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main" cellspacing="0" cellpadding="0" border="0" align="center"   :style="{backgroundColor: element.options.backgroundColor}" style="display: table;" data-type="billingAddress">    <tbody>    <tr>        <td align="left"   class="em-image-text" :id="element.id+\'em-image-text\'" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3], fontFamily: element.options.font.family}"     style="font-size: 13px; color: #000000; line-height: 22px;"><div v-html="doShortcode(element.options.text)"></div>        </td>    </tr>    </tbody></table>'
                },
                imageTextRightTemplate: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"   :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main" cellspacing="0" cellpadding="0" border="0" align="center" :style="{backgroundColor: element.options.backgroundColor}" style="display: table;" data-type="imageTextRight">    <tbody>    <tr>        <td class="em-image-text" :id="element.id+\'em-image-text\'" align="left" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3], fontFamily: element.options.font.family}" style="font-size: 13px; color: #000000; line-height: 22px;"><table class="em-image-in-table" width="190" align="left" style="margin: 11px 0;">    <tbody>    <tr>        <td class="em-gap" width="30"></td>        <td width="160">            <img border="0" align="left" :src="element.options.image" :width="element.options.width" style="display: block;margin: 0px;max-width: 340px;padding:5px 5px 0 0;">        </td>    </tr>    </tbody></table><table width="190">    <tbody>    <tr>        <td class="em-text-block" :id="element.id+\'em-text-block\'" v-html="doShortcode(element.options.text)">        </td>    </tr>    </tbody></table>        </td>    </tr>    </tbody></table>'
                },
                imageText2x2Template: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                        if (!this.element.options.buttons) {
                            this.element.options.buttons = []
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"   :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main" cellspacing="0" cellpadding="0" border="0" :bgcolor="element.options.backgroundColor" align="center" style="display: table;" data-type="imageText2x2Template">\n    <tbody>\n    <tr>\n        <td>\n            <table class="em-main-2-column" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="display: table;">\n                <tbody>\n                <tr>\n                    <td width ="50%" valign="top" class="em-image-caption" :id="element.id+\'em-image-caption\'" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3]}" data-block-id="background">\n<div>\n                                <div class="em-image-caption-content text" :id="element.id+\'em-image-caption-content\'" align="left" :style="{fontFamily: element.options.font.family}" style="font-size: 13px;color: #000000;line-height: 22px;" v-html="doShortcode(element.options.text1)">\n                                </div>\n                            </div>\n                            <div v-if="element.options.buttons[0] && element.options.buttons[0].active">\n                                <div width="100%" :align="element.options.buttons[0].align">\n                                    <a :href="element.options.buttons[0].link" :style="{backgroundColor: element.options.buttons[0].backgroundColor, display: element.options.buttons[0].fullWidth ? \'block\' : \'inline-block\'}" data-default="1" class="em-button-1" style="line-height: 21px; border-radius: 2px; text-align: center; text-decoration: none; margin: 0px; padding: 12px 20px; color: rgb(255, 255, 255); font-size: 15px; font-family: inherit; font-weight: normal;">{{element.options.buttons[0].text}}</a>\n                                </div>\n                            </div>\n                            </td><td width="50%" valign="top" class="em-image-caption" :id="element.id+\'em-image-caption2\'" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3]}" data-block-id="background"><div>\n                                <div class="em-image-caption-content text" :id="element.id+\'em-em-image-caption-content-2\'"  align="left" :style="{fontFamily: element.options.font.family}" style="font-size: 13px;color: #000000;line-height: 22px;" v-html="doShortcode(element.options.text2)"></div>\n                            </div>\n                            <div v-if="element.options.buttons[1] && element.options.buttons[1].active">\n                                <div width="100%" :align="element.options.buttons[1].align">\n                                    <a :href="element.options.buttons[1].link" :style="{backgroundColor: element.options.buttons[1].backgroundColor, display: element.options.buttons[1].fullWidth ? \'block\' : \'inline-block\'}" data-default="1" class="em-button-1" :id="element.id+\'em-button-2\'" style="line-height: 21px; border-radius: 2px; text-align: center; text-decoration: none; margin: 0px; padding: 12px 20px; color: rgb(255, 255, 255); font-size: 15px; font-family: inherit; font-weight: normal;">{{element.options.buttons[1].text}}</a>\n                                </div>\n                            </div>\n                   </td>\n                </tr>\n                </tbody>\n            </table>\n        </td>\n    </tr>\n    </tbody>\n</table>'
                },
                imageText3x2Template: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"   :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main" cellspacing="0" cellpadding="0" border="0" align="center"\n       :style="{backgroundColor: element.options.backgroundColor}"\n       style="display: table;" data-type="imageText3x2">\n    <tbody>\n    <tr>\n        <td class="em-image-caption" :id="element.id+\'em-image-caption\'" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3]}">\n            <table class="em-image-caption-container" align="left" border="0" cellpadding="0" cellspacing="0" width="100%">\n                <tbody>\n                <tr>\n                    <td>\n                        <table class="em-image-caption-column" align="left" border="0" cellpadding="0" cellspacing="0" width="160">\n                            <tbody>\n                            <tr>\n                                <td height="15" width="100%"></td>\n                            </tr>\n                            <tr>\n                                <td class="em-image-caption-content"\n :id="element.id+\'em-image-caption-content\'" :style="{fontFamily: element.options.font.family}" style="font-size: 13px; color: #000000;">\n                                    <img :src="element.options.image1"\n                                         :width="element.options.width1"\n                                         style="display: block;" align="2" border="0">\n                                </td>\n                            </tr>\n                            <tr>\n                                <td height="15" width="100%"></td>\n                            </tr>\n                            <tr>\n                                <td class="em-image-caption-content text"\n  :id="element.id+\'em-image-caption-content-1\'" :style="{fontFamily: element.options.font.family}" style="font-size: 13px; color: #000000; line-height: 22px;"\n                                    align="left"\n                                    v-html="doShortcode(element.options.text1)">\n                                </td>\n                            </tr>\n                            <tr>\n                                <td class="em-image-caption-bottom-gap" height="5" width="100%"></td>\n                            </tr>\n                            </tbody>\n                        </table>\n                        </td><td><table class="em-image-caption-column" align="right" border="0" cellpadding="0" cellspacing="0" width="160">\n                            <tbody>\n                            <tr>\n                                <td class="em-image-caption-top-gap" height="15" width="100%"></td>\n                            </tr>\n                            <tr>\n                                <td class="em-image-caption-content"\n :id="element.id+\'em-image-caption-content-2\'" :style="{fontFamily: element.options.font.family}" style="font-size: 13px; color: #000000;">\n                                    <img :src="element.options.image2"\n                                         :width="element.options.width2"\n                                         style="display: block;" align="2" border="0">\n                                </td>\n                            </tr>\n                            <tr>\n                                <td height="15" width="100%"></td>\n                            </tr>\n                            <tr>\n                                <td class="em-image-caption-content text"\n  :id="element.id+\'em-image-caption-content-3\'" :style="{fontFamily: element.options.font.family}" style="font-size: 13px; color: #000000; line-height: 22px;"\n                                    align="left"\n                                    v-html="doShortcode(element.options.text2)">\n                                </td>\n                            </tr>\n                            <tr>\n                                <td class="em-image-caption-bottom-gap" height="5" width="100%"></td>\n                            </tr>\n                            </tbody>\n                        </table></td><td><table class="em-image-caption-column" align="right" border="0" cellpadding="0" cellspacing="0"\n                   width="160">\n                <tbody>\n                <tr>\n                    <td class="em-image-caption-top-gap" height="15" width="100%"></td>\n                </tr>\n                <tr>\n                    <td class="em-image-caption-content"\n :id="element.id+\'em-image-caption-content-4\'" :style="{fontFamily: element.options.font.family}" style="font-size: 13px; color: #000000;">\n                        <img :src="element.options.image3"\n                             :width="element.options.width3"\n                             style="display: block;" align="2" border="0">\n                    </td>\n                </tr>\n                <tr>\n                    <td height="15" width="100%"></td>\n                </tr>\n                <tr>\n                    <td class="em-image-caption-content text"\n :id="element.id+\'em-image-caption-content-5\'" :style="{fontFamily: element.options.font.family}" style="font-size: 13px; color: #000000; line-height: 22px;"\n                        align="left"\n                        v-html="doShortcode(element.options.text3)">\n                    </td>\n                </tr>\n                <tr>\n                    <td height="5" width="100%"></td>\n                </tr>\n                </tbody>\n            </table>\n                    </td>\n                </tr>\n                </tbody>\n            </table>\n            \n        </td>\n    </tr>\n    </tbody>\n</table>'
                },
                // Loading email
                loading: {
                    template: '<h1 class="loading">Loading ...</h1>'
                },
                couponTemplate: {
                    props: ['element'],
                    beforeMount () {
                        if (!this.element.options.font) {
                            this.element.options.font = {
                                family: 'inherit',
                                familyOptions: defaultFontFamily
                            }
                        }
                    },
                    template: '<table :bgcolor="element.options.backgroundColor"   :width="woo_email_customizer_containerWidth" :id="element.id" class="em-main woo_email_coupon_cont" cellspacing="0" cellpadding="0" border="0" :style="{backgroundColor: element.options.backgroundColor}" style="display: table;" align="center" data-type="text-block">    <tbody>    <tr>        <td class="em-block-text" :id="element.id+\'em-block-text\'" data-block-id="background" align="left" :style="{paddingTop: this.element.options.padding[0], paddingRight: this.element.options.padding[1], paddingBottom: this.element.options.padding[2], paddingLeft: this.element.options.padding[3], fontFamily: element.options.font.family}" style="font-size: 13px; color: #000000; line-height: 22px;"><div class="wem_coupon_con_identifier">{wem_coupon_con_identifier}</div><div class="wemc_coupon_code" v-html="doShortcode(this.element.options.text)"></div><div class="wem_coupon_con_identifier">{/wem_coupon_con_identifier}</div> </td>    </tr>    </tbody></table>'
                },
            }
        }
    }
}).$mount('#app');

jQuery(document).ready(function () {

    // For Improve UI Process (Language Button Active/De-Active).
    jQuery(document).on('click', '.btn_lang_switch', function () {
        jQuery('.btn_lang_switch').removeClass('active');
        jQuery(this).addClass('active');
    });

    // Prevent jQuery UI dialog from blocking focusin
    jQuery(document).on('focusin', function(e) {
        if (jQuery(e.target).closest(".mce-window, .moxman-window").length) {
            e.stopImmediatePropagation();
        }
    });

    jQuery(document).on('change', 'input[name="settings[show_product_image]"]', function () {
        if($(this).val() == '1'){
            jQuery('.show_product_image_option').show();
        } else {
            jQuery('.show_product_image_option').hide();
        }
    });
    jQuery('input[name="settings[show_product_image]"]:checked').trigger('change');

    var wrapper = jQuery( '#email-builder' );
    try {
        jQuery( '.woocommerce-help-tip', wrapper ).tipTip({
            'attribute': 'data-tip',
            'fadeIn':    50,
            'fadeOut':   50,
            'delay':     200
        });
    } catch (e) {}


    woo_email_customizer_resize_header_height();
    jQuery( window ).resize(function() {
        woo_email_customizer_resize_header_height();
    });

    jQuery(document).on('click', '.woo_email_content_edit_toggle', function () {
        var isExpand = jQuery(this).data('expand');
        if(isExpand == "0"){
            jQuery(this).closest(".woo_email_content_edit_con").css('width', 'auto');
            jQuery(this).data('expand', "1");
            jQuery(this).html('<i class="actions material-icons">arrow_forward</i> Shrink');
        } else {
            jQuery(this).closest(".woo_email_content_edit_con").css('width', '300px');
            jQuery(this).data('expand', "0");
            jQuery(this).html('<i class="actions material-icons">arrow_back</i> Expand');
        }
    });

    jQuery('#woo-mail-settings_toggle .nav-tabs a').click(function(){
        jQuery('#woo-mail-settings_toggle .nav-tabs li').removeClass('active');
        jQuery(this).parent('li').addClass('active');
        var selectedID = jQuery(this).attr('href');
        jQuery('#woo-mail-settings_toggle .tab-content .tab-pane').removeClass('active');
        jQuery('#woo-mail-settings_toggle .tab-content '+selectedID).addClass('active');
        return false;
    });

    var woo_mb_email_lang_value = jQuery("#woo_mb_email_lang option[selected='selected']").attr("value");
    jQuery("#woo_mb_email_lang").val(woo_mb_email_lang_value).trigger('change');
});

function woo_email_customizer_resize_header_height(){
    setTimeout(function(){
        var woo_email_header_height = jQuery('.email-builder-header').outerHeight();
        jQuery(".email-builder-content").css('margin-top', woo_email_header_height+'px');
    }, 1000);

}

let doShortcode = Vue.filter('doShortcode', function(value) {
    let data = emailComponent.$children[0];
    value = value.replace(new RegExp(Object.keys(data.orderInfo).map(v => {
        return v.replace(/[|\\{}()[\]^$+*?.]/g, '\\$&');
}).join('|'), 'g'), m => {
        if (data.orderInfo[m] != undefined) {
            let spanTags = ['[woo_mb_shipping_method]','[woo_mb_order_link]','[woo_mb_user_name]', '[woo_mb_billing_first_name]', '[woo_mb_billing_last_name]', '[woo_mb_user_id]',
                '[woo_mb_user_email]', '[woo_mb_shipping_first_name]', '[woo_mb_shipping_last_name]', '[woo_mb_billing_phone]', '[woo_mb_order_date]',
                '[woo_mb_order_total]', '[woo_mb_order_sub_total]', '[woo_mb_payment_method]', '[woo_mb_billing_company]', '[woo_mb_billing_address_1]',
                '[woo_mb_billing_address_2]', '[woo_mb_billing_city]', '[woo_mb_billing_state]', '[woo_mb_billing_postcode]', '[woo_mb_billing_country]',
                '[woo_mb_shipping_company]', '[woo_mb_shipping_address_1]', '[woo_mb_shipping_address_2]', '[woo_mb_shipping_city]', '[woo_mb_shipping_state]', '[woo_mb_order_refund]',
                '[woo_mb_shipping_postcode]', '[woo_mb_shipping_country]', '[woo_mb_order_id]', '[woo_mb_order_number]', '[woo_mb_billing_email]', '[wec_next_order_coupon]', '[wec_next_order_coupon_value]'];
            if(spanTags.includes(m) || m.startsWith('[woo_mb_order_meta-') || m.startsWith('[wec_') || m.startsWith('[woo_mb_wcs_subscription_')){
                return `<span data-shordcode="${m}">${data.orderInfo[m]}</span>`;
            } else {
                return `<div data-shordcode="${m}">${data.orderInfo[m]}</div>`;
            }
        }
    });
    return value;
});


function woo_email_customizer_saveWooEmailCustomizerSettings(id) {
    if(id != undefined){
        var currentObject = jQuery(id);
        var btn_type = currentObject.attr('data-btn_type');
        if(btn_type != undefined && btn_type == 'next_order_coupon'){
            currentObject.html('Connecting please wait...')
        }
    }

    var formData = jQuery( "#woo-mail-settings" ).serializeArray();
    formData.push({'name':'action','value':'ajaxSaveEmailCustomizerSettings'});
    return jQuery.ajax({
        url: woo_email_customizer_ajax_url,
        dataType : 'json',
        data: formData,
        async : false,
        type: 'POST',
        beforeSend: function (xhr) {
            woo_email_customizer_enable_loader();
        },
        success: function(res){
            if (res['status_code'] == 200) {
                utils.notify(res['status_message']).success()
            } else {
                utils.notify(res['status_message']).error()
            }
            if(res['validated_next_order_api'] != undefined){
                jQuery('.validated_next_order_coupon_text').removeClass('show').removeClass('hide');
                if(res['validated_next_order_api'] == 1){
                    jQuery('.validated_next_order_coupon_text.validated').addClass('show');
                    jQuery('.validated_next_order_coupon_text.invalid').addClass('hide');
                } else {
                    jQuery('.validated_next_order_coupon_text.validated').addClass('hide');
                    jQuery('.validated_next_order_coupon_text.invalid').addClass('show');
                }
            }
            if(res['licence_key_message'] != undefined){
                jQuery('#wemc_licence_status_con').html(res['licence_key_message']);
            }
            if(res['licence_key_message_detail'] != undefined){
                jQuery('#emc-licence-update-msg-con').html(res['licence_key_message_detail']);
            } else {
                jQuery('#emc-licence-update-msg-con').html('');
            }
        },
        error: function (err) {
            utils.notify(err.statusText).error()
        },
        complete: function () {
            woo_email_customizer_disable_loader()
            if(id != undefined && btn_type != undefined && btn_type == 'next_order_coupon'){
                currentObject.html('Connect');
            }
        }
    });
}



function woo_email_customizer_resetDefaultTemplate() {
    alertify.parent(document.getElementById("email-builder"));
    return utils.confirm('All your saved template will be reset to default, are you sure?', () => {
        var postData = {
            action: 'ajaxResetTemplate'
        };
    woo_email_customizer_enable_loader();

    return jQuery.post(woo_email_customizer_ajax_url, postData).then(
        response => {
        response = JSON.parse(response);
    if(response.status_code == '200'){
        return utils.notify(response.status_message).success();
    } else {
        return utils.notify(response.status_message).error()
    }

}, console.error).done(function() {
        woo_email_customizer_disable_loader();
    });

}, null, 'Yes, I\'m sure', 'Remain here');
}

function woo_email_customizer_resetSingleTemplate() {
    alertify.parent(document.getElementById("email-builder"));
    var woo_mb_email_lang_reset = jQuery("#woo_mb_email_lang_reset").val();
    var woo_mb_email_type_reset = jQuery("#woo_mb_email_type_reset").val();
    if(woo_mb_email_type_reset == ''){
        return utils.notify("Select the template to reset").error()
    }
    return utils.confirm('Your saved template for selected language and status will be empty, are you sure?', () => {
        var postData = {
            action: 'ajaxResetSingleTemplate',
            email_lang: woo_mb_email_lang_reset,
            email_type: woo_mb_email_type_reset
        };
    woo_email_customizer_enable_loader();

    return jQuery.post(woo_email_customizer_ajax_url, postData).then(
        response => {
        response = JSON.parse(response);
    if(response.status_code == '200'){
        return utils.notify(response.status_message).success();
    } else {
        return utils.notify(response.status_message).error()
    }

}, console.error).done(function() {
        woo_email_customizer_disable_loader();
    });

}, null, 'Yes, I\'m sure', 'Remain here');
}

function woo_email_customizer_export_templates() {
    alertify.parent(document.getElementById("email-builder"));
    var split_url = woo_email_customizer_ajax_url.split('?');
    if(split_url[1] == undefined){
        var request_url = woo_email_customizer_ajax_url+'?action=ajax_wec_export_templates';
    } else {
        var request_url = woo_email_customizer_ajax_url+'&action=ajax_wec_export_templates';
    }
    document.location.href = request_url;
}