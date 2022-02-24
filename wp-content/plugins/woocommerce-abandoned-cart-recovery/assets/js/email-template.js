jQuery(document).ready(function ($) {
    'use strict';

    var field_focus, field_type;

    $('.wacv-email-content').sortable({
        cursor: 'move',
        placeholder: 'placeholder',
        handle: 'button',
        cancel: '',
        start: function (e, ui) {
            ui.placeholder.height(ui.helper.outerHeight());
            ui.item.css('width', '600px')
        },
        stop: function (ev, ui) {
            getSaveBlock.init()
        }
    });

    const elementsDrag = {

        init: function (els) {
            for (let i in els) {
                if (els[i] !== 'undefined') {
                    this.dragEl(els[i])
                }
            }
        },

        dragEl: function (type) {
            $('.wacv-' + type + '-drag').draggable({
                cursor: 'move',
                helper: function () {
                    return getViewBlock.init(type)
                },
                start: function () {

                },
                stop: function () {
                },
                connectToSortable: '.wacv-email-content'
            })
        },
    }

    const getViewBlock = {
        init: function (type) {
            let field = this.generateField()
            field_focus = field
            field_type = type
            switch (type) {
                case 'text':
                    return this.textField(field)
                case 'image':
                    return this.imageField(field)
                case 'button':
                    return this.buttonField(field)
                case 'cart':
                    return this.cartField(field)
                case 'divider':
                    return this.dividerField(field)
            }
            // getSaveBlock.init();
        },

        generateField: function () {
            return Math.floor(Math.random() * (100000 - 1 + 1) + 57)
        },

        handle: function (field) {
            return '<div class="wacv-handle">' +
                '<button type="button" class="wacv-move" data-field="' + field + '"><i class="dashicons dashicons-move"></i></button>' +
                '<button type="button" class="wacv-remove" data-field="' + field + '"><i class="dashicons dashicons-trash"></i></button>' +
                '</div>'
        },

        dividerField: function (field) {
            let html = '<div class="wacv-block" data-type="divider" data-field="' + field + '">' + this.handle(field) +
                '            <div class="wacv-attr-save"></div>' +
                '            <div class="wacv-content-group wacv-output" style="padding: 12px 40px" >' +
                '            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">' +
                '               <tr >' +
                '                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">' +
                '                 <hr style="padding: 0; margin: 0">' +
                '                   </td>' +
                '               </tr>' +
                '            </table>' +
                '            </div>' +
                '        </div>'

            return $('<div>').addClass('li_' + field + ' form-builder-field').html(html)
        },

        textField: function (field) {
            let html = '<div class="wacv-block" data-type="text" data-field="' + field + '">' + this.handle(field) +
                '            <div class="wacv-attr-save"></div>' +
                '            <div class="wacv-content-group wacv-output" style=" padding: 12px 40px;">' +
                '            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">' +
                '               <tr >' +
                '                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">' +
                '                       <div  class="form-control wacv-text-field wacv-background-color-output" style="padding: 5px 0" data-field="' + field + '">Text</div>' +
                '                   </td>' +
                '               </tr>' +
                '            </table>' +
                '            </div>' +
                '        </div>';

            return $('<div>').addClass('li_' + field + ' form-builder-field').html(html);
        },

        imageField: function (field) {
            let html = '<div class="wacv-block" data-type="image" data-field="' + field + '">' + this.handle(field) +
                '            <div class="wacv-attr-save"></div>' +
                '            <div class="wacv-content-group wacv-output" style=" padding: 12px 40px;">' +
                '            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">' +
                '               <tr >' +
                '                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">' +
                `                <img class="wacv-image" width="100%" src="${wacv_ls.img_src}placeholder.png" style="vertical-align: middle; ">` +
                '                   </td>' +
                '               </tr>' +
                '            </table>' +
                '            </div>' +
                '        </div>';

            return $('<div>').addClass('li_' + field + ' form-builder-field').html(html)
        },

        buttonField: function (field) {
            let html = '<div class="wacv-block" data-type="button" data-field="' + field + '">' + this.handle(field) +
                '            <div class="wacv-attr-save" data-attrs="{&quot;color&quot;:&quot;#ffffff&quot;,&quot;bgColor&quot;:&quot;#000000&quot;,&quot;blockColor&quot;:&quot;#000000&quot;,&quot;paddingTop&quot;:&quot;12&quot;,&quot;paddingLeft&quot;:&quot;24&quot;,&quot;paddingRight&quot;:&quot;24&quot;,&quot;paddingBottom&quot;:&quot;12&quot;,&quot;text&quot;:&quot;&amp;lt;p style=\\&quot;text-align: center;\\&quot;&amp;gt;&amp;lt;span style=\\&quot;font-size: 14pt;\\&quot;&amp;gt;&amp;lt;strong&amp;gt;&amp;lt;a href=\\&quot;{wacv_checkout_btn}\\&quot;&amp;gt;Checkout Now&amp;lt;/a&amp;gt;&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&quot;,&quot;align&quot;:&quot;center&quot;,&quot;buttonWidth&quot;:&quot;50&quot;}"></div>' +
                '            <div class="wacv-content-group wacv-output" style="padding: 12px 40px; color: #ffffff; text-align: center;">' +
                '            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">' +
                '               <tr >' +
                '                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">' +
                `               <button type="button" class="wacv-button-text wacv-background-color-output" style="width:50%; border: none; padding: 5px 0; margin: 0px; color: inherit; background-color: #212121;" >` +
                `              <p style="text-align: center;"><span style="font-size: 14pt;"><strong><a href="{wacv_checkout_btn}">Checkout Now</a></strong></span></p></button>` + // <a href="{wacv_checkout_btn}" style=" text-decoration: none;">
                '                   </td>' +
                '               </tr>' +
                '            </table>' +
                '            </div>' +
                '        </div>'

            return $('<div>').addClass('li_' + field + ' form-builder-field').html(html)
        },

        cartField: function (field) {
            let html = '<div class="wacv-block" data-type="cart" data-field="' + field + '">' + this.handle(field) +
                '            <div class="wacv-attr-save"></div>' +
                '            <div class="wacv-content-group wacv-output" style=" padding: 12px 40px;">' +
                '            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">' +
                '               <tr >' +
                '                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">' +
                `                <table class="wacv-background-color-output field-ctrl" style="border-collapse: collapse;" cellpadding="0" cellspacing="0" height="100%" width="100%">` +
                `                    <tr data="{wacv_cart_detail_start}"></tr>` +
                `                    <tr>` +
                `                    <td align="center" width="140" style="padding: 5px" class="field-ctrl">` +
                `                    <img style="width:140px; vertical-align: middle;" src="${wacv_ls.img_src}product.png">` +
                `                    </td>` +
                `                    <td style="vertical-align: top;padding: 5px" class="field-ctrl">` +
                `                         <p style="line-height: 2; font-weight: bold; ">{product_name}</p>` +
                `                         <p style="line-height: 2;">{product_quantity}</p>` +
                `                         <p style="line-height: 2;">{product_amount}</p>` +
                `                    </td>` +
                `                    </tr>` +
                `                    <tr data="{wacv_cart_detail_end}"></tr>` +
                `                </table>` +
                '                   </td>' +
                '               </tr>' +
                '            </table>' +
                '            </div>' +
                '        </div>'

            return $('<div>').addClass('li_' + field + ' form-builder-field').html(html)
        },
    }

    //Save
    const getSaveBlock = {
        init: function () {
            let el = $('.wacv-email-content .wacv-block'), html = '';

            el.each(function () {
                let $this = $(this), data_type = $this.attr('data-type');
                let outputStyle = $this.find('.wacv-output').attr('style'),
                    content = $this.find('.wacv-output').html(),
                    output = '';
                if (data_type === 'cart') {
                    content = content.replace('<tr data="{wacv_cart_detail_start}"></tr>', '{wacv_cart_detail_start}');
                    content = content.replace('<tr data="{wacv_cart_detail_end}"></tr>', '{wacv_cart_detail_end}');
                    content = content.replace(/http(s?):\/\/.*product.png/i, '{wacv_image_product}');
                    content = content.replace(/http(s?):\/\/.*product-4.jpg/i, '{wacv_image_product}');
                }
                output += `<tr><td align="center" valign="top"  style="word-break:break-word;min-width:600px;border-collapse:collapse;"><table width="600" align="left" valign="center" cellspacing="0" cellpadding="0" border="0"><tr>`;
                output += `<td style="${outputStyle}">`;
                output += content;
                output += `</td>`;
                output += `</tr></table></td></tr>`;
                html += output;
            });

            $('.wacv-html-data-save').find('textarea').val('<table width="600" align="center" valign="center" cellspacing="0" cellpadding="0" border="0" style="font-size: 14px; font-family: Lato, Arial, Helvetica, sans-serif;background-color: #ffffff;">' + html + '</table>');
            // $('.wacv-self-preview').html('<table width="600" align="center" valign="center" cellspacing="0" cellpadding="0" border="0" style="font-size: 14px; font-family: Lato, Arial, Helvetica, sans-serif;background-color: #ffffff;outline: 1px solid #dddddd;">' + html + '</table>');
            var dataEdit = $('.wacv-email-content').html();
            $('.wacv-html-data-edit').find('textarea').val(dataEdit);
        },
    }

    //Control panel
    const controlPanel = {
        attrs: {},

        attrsDefault: {
            color: '#000000',
            bgColor: '#ffffff',
            borderColor: '#dddddd',
            blockColor: '#ffffff',
            paddingTop: 12,
            paddingLeft: 40,
            paddingRight: 40,
            paddingBottom: 12,
            text: 'Text',
            align: 'left',
            buttonWidth: 50,
            imageWidth: 100,
        },

        panelEls: function () {
            return {
                color: `<tr><td>Text color</td><td class="wacv-pos-btn-color"><input type="text" class="wacv-text-color wacv-button-format" value="${this.attrs.color}"></td></tr>`,
                blockColor: `<tr><td>Background color</td><td class="wacv-pos-btn-color"><input type="text" class="wacv-block-color button" value="${this.attrs.blockColor}"></td></tr>`,
                borderColor: `<tr><td>Border color</td><td class="wacv-pos-btn-color"><input type="text" class="wacv-border-color button" value="${this.attrs.borderColor}"></td></tr>`,
                bgColor: `<tr><td>Background color</td><td class="wacv-pos-btn-color"><input type="text" class="wacv-background-color button" value="${this.attrs.bgColor}"></td></tr>`,
                imageSrc: '<tr><td>Image src</td><td><input type="button" class="wacv-open-image button" value="Select Image"></td></tr>',
                padding: `<tr><td>Padding</td><td class="wacv-padding-ctrl"><table><tr><td></td><td><input type="number" class="wacv-padding-top" min="0" max="300" value="${this.attrs.paddingTop}"></td><td></td></tr><tr><td><input type="number" class="wacv-padding-left" min="0" max="300" value="${this.attrs.paddingLeft}"></td><td></td><td><input type="number" class="wacv-padding-right" min="0" max="300" value="${this.attrs.paddingRight}"></td></tr><tr><td></td><td><input type="number" class="wacv-padding-bottom" min="0" max="300" value="${this.attrs.paddingBottom}"></td><td></td></tr></table></td></tr>`,
                align: `<tr><td>Align</td><td><select  class="wacv-element-align" >${this.options(this.attrs.align)}</select></td></tr>`,
                text: `<tr><td colspan="2"><textarea id="wacv-editor-${field_focus}" class="wacv-text-content">${this.attrs.text}</textarea></td></tr>`,
                buttonWidth: `<tr><td>Width (%)</td><td><input type="number" class="wacv-button-width" min="0" max="100" value="${this.attrs.buttonWidth}"></td></tr>`,
                imageWidth: `<tr><td>Width (%)</td><td><input type="number" class="wacv-image-width" min="0" max="100" value="${this.attrs.imageWidth}"></td></tr>`,
            }
        },

        options: function (curr) {
            let options = ['left', 'center', 'right'], out = '';

            for (let value of options) {
                var selected = value === curr ? 'selected' : '';
                out += `<option value="${value}" ${selected}>${value}</option>`;
            }
            return out;
        },

        init: function () {
            $('.wacv-control-table').hide();

            let activePanel = $('.wacv-control-table.i-' + field_focus);

            if (activePanel.length) {
                activePanel.show();

            } else {
                let attrData = $('.li_' + field_focus).find('.wacv-attr-save').attr('data-attrs'),
                    html, target;

                if (attrData) {
                    this.attrs = JSON.parse(attrData)
                } else {
                    this.attrs = this.attrsDefault;
                }

                // $(tinyMCE.editors).each(function () {
                //     tinyMCE.remove(this);
                // });

                let panel = $('#wacv-control-panel')  //.wacv-control-table

                let ctrl = controlPanel.panelEls()

                switch (field_type) {
                    case 'divider':
                        html = '<tr><td class="wacv-control-panel-label" colspan="2">Row</td></tr>' + ctrl.blockColor + ctrl.padding;
                        break
                    case 'text':
                        html = '<tr><td class="wacv-control-panel-label" colspan="2">Content</td></tr>' + ctrl.color + ctrl.bgColor + ctrl.text + '<tr><td class="wacv-control-panel-label" colspan="2">Row</td></tr>' + ctrl.blockColor + ctrl.padding;
                        target = 'wacv-text-field';
                        break
                    case 'image':
                        html = '<tr><td class="wacv-control-panel-label" colspan="2">Content</td></tr>' + ctrl.imageSrc + ctrl.imageWidth + ctrl.align + '<tr><td class="wacv-control-panel-label" colspan="2">Row</td></tr>' + ctrl.blockColor + ctrl.padding
                        break
                    case 'button':
                        html = '<tr><td class="wacv-control-panel-label" colspan="2">Content</td></tr>' + ctrl.buttonWidth + ctrl.align + ctrl.color + ctrl.bgColor + ctrl.text + '<tr><td class="wacv-control-panel-label" colspan="2">Row</td></tr>' + ctrl.blockColor + ctrl.padding  //ctrl.buttonLink +
                        target = 'wacv-button-text';
                        break
                    case 'cart':
                        html = '<tr><td class="wacv-control-panel-label" colspan="2">Content</td></tr>' + ctrl.color + ctrl.bgColor + ctrl.borderColor + '<tr><td class="wacv-control-panel-label" colspan="2">Row</td></tr>' + ctrl.blockColor + ctrl.padding
                        break
                }

                panel.append(`<table class="wacv-control-table i-${field_focus}">` + html + '</table>');

                if (target) {
                    this.editor(target)
                }

                this.eventCtrl()
            }
        },

        editor: function (target) {
            tinymce.init({
                mode: 'exact',
                elements: 'wacv-editor-' + field_focus,
                theme: 'modern',
                skin: 'lightgray',
                menubar: false,
                statusbar: false,
                relative_urls: false,
                convert_urls: false,
                plugins: ["link textcolor colorpicker"],
                toolbar: ['bold italic underline | alignleft aligncenter alignright | link ', 'fontsizeselect forecolor backcolor ', 'shortcode fontselect'],
                setup: function (editor) {
                    editor.addButton('shortcode', {
                        type: 'listbox',
                        text: 'Shortcode',
                        icon: false,
                        onselect: function (e) {
                            editor.insertContent(this.value())
                        },
                        values: [
                            {text: '{customer_name}', value: '{customer_name}'},
                            {text: '{customer_surname}', value: '{customer_surname}'},
                            {text: '{coupon_code}', value: '{coupon_code}'},
                            {text: '{coupon_expire}', value: '{coupon_expire}'},
                            {text: '{unsubscribe_link}', value: '{unsubscribe_link}'},
                            {text: '{wacv_checkout_btn}', value: '{wacv_checkout_btn}'},
                            {text: '{site_title}', value: '{site_title}'},
                            {text: '{store_address}', value: '{store_address}'},
                            {text: '{home_url}', value: '{home_url}'},
                            {text: '{site_url}', value: '{site_url}'},
                            {text: '{shop_url}', value: '{shop_url}'},
                            {text: '{admin_email}', value: '{admin_email}'},
                        ],
                    });
                    editor.on('change keyup', function (e) {
                        $('.li_' + field_focus).find('.wacv-output .' + target).html(editor.getContent());
                        $('#wacv-editor-' + field_focus).html(editor.getContent());
                        controlPanel.attrSave();
                        getSaveBlock.init();
                    })
                }
            })

        },

        attrSave: function () {
            // let ctrlPanel = $('#wacv-control-panel')
            let ctrlPanel = $('#wacv-control-panel .wacv-control-table.i-' + field_focus)
            let saveAttrs = {
                color: ctrlPanel.find('.wacv-text-color').val(),
                bgColor: ctrlPanel.find('.wacv-background-color').val(),
                blockColor: ctrlPanel.find('.wacv-block-color').val(),
                borderColor: ctrlPanel.find('.wacv-border-color').val(),
                paddingTop: ctrlPanel.find('.wacv-padding-top').val(),
                paddingLeft: ctrlPanel.find('.wacv-padding-left').val(),
                paddingRight: ctrlPanel.find('.wacv-padding-right').val(),
                paddingBottom: ctrlPanel.find('.wacv-padding-bottom').val(),
                text: ctrlPanel.find('#wacv-editor-' + field_focus).html(),
                // buttonLink: ctrlPanel.find('.wacv-button-link').val(),
                align: ctrlPanel.find('.wacv-element-align').val(),
                buttonWidth: ctrlPanel.find('.wacv-button-width').val(),
                imageWidth: ctrlPanel.find('.wacv-image-width').val(),
            }
            $('.li_' + field_focus).find('.wacv-attr-save').attr('data-attrs', JSON.stringify(saveAttrs))
        },

        eventCtrl: function () {

            $('.wacv-text-color').wpColorPicker({
                change: function (ev, ui) {
                    let theColor = ui.color.toString()
                    let el = $('.li_' + field_focus).find('.wacv-output');
                    el.css('color', theColor)
                    controlPanel.convertStyle(el);
                    $('.wacv-text-color').val(theColor)
                    controlPanel.attrSave()
                    getSaveBlock.init()
                },
            })

            $('.wacv-background-color').wpColorPicker({
                change: function (ev, ui) {
                    let theColor = ui.color.toString()
                    let el = $('.li_' + field_focus).find('.wacv-background-color-output');
                    el.css('background-color', theColor)
                    controlPanel.convertStyle(el);
                    $('.wacv-background-color').val(theColor)
                    controlPanel.attrSave()
                    getSaveBlock.init()
                }
            })

            $('.wacv-block-color').wpColorPicker({
                change: function (ev, ui) {
                    let theColor = ui.color.toString()
                    let el = $('.li_' + field_focus).find('.wacv-output');
                    el.css('background-color', theColor)
                    controlPanel.convertStyle(el);
                    $('.wacv-block-color').val(theColor)
                    controlPanel.attrSave()
                    getSaveBlock.init()
                }
            })

            $('.wacv-border-color').wpColorPicker({
                change: function (ev, ui) {
                    let theColor = ui.color.toString()
                    $('.li_' + field_focus).find('.wacv-output table.field-ctrl').css('border', '1px solid ' + theColor)
                    $('.li_' + field_focus).find('.wacv-output td.field-ctrl').css({
                        'border-top': '1px solid ' + theColor,
                        'border-bottom': '1px solid ' + theColor
                    })
                    $('.wacv-border-color').val(theColor)
                    controlPanel.attrSave()
                    getSaveBlock.init()
                }
            })

            this.paddingCtrl(['padding-top', 'padding-left', 'padding-right', 'padding-bottom'])

            $('.wacv-button-width').on('change', function () {
                let val = $(this).val()
                $('.li_' + field_focus).find('.wacv-output button').css('width', val + '%')
                $('#wacv-control-table.i-' + field_focus + ' .wacv-button-width').val(val)
                controlPanel.attrSave()
                getSaveBlock.init()
            })


            $('.wacv-image-width').on('change', function () {
                let val = $(this).val()
                $('.li_' + field_focus).find('.wacv-output img').css('width', val + '%')
                $('.wacv-image-width').val(val)
                controlPanel.attrSave()
                getSaveBlock.init()
            })

            $('.wacv-button-link').on('change', function () {
                let val = $(this).val()
                $('.li_' + field_focus).find('.wacv-output a').attr('href', val)
                $('.wacv-button-link').val(val)
                controlPanel.attrSave()
                getSaveBlock.init()
            })

            $('.wacv-element-align').on('change', function () {
                let val = $(this).val()
                $('.li_' + field_focus).find('.wacv-output').css('text-align', val)
                $('.wacv-element-align').val(val)
                controlPanel.attrSave()
                getSaveBlock.init()
            })


            $('.wacv-open-image').on('click', function () {
                let images = wp.media({
                    title: 'Select Image',
                    multiple: false
                }).open().on('select', function (e) {
                    let uploadedImages = images.state().get('selection').first()
                    let selectedImages = uploadedImages.toJSON()
                    $('.li_' + field_focus).find('.wacv-image').attr('src', selectedImages.url)
                    getSaveBlock.init()
                })
            })
        },

        convertStyle: function (el) {
            let match, newColor, Style = el.attr('style');
            let matchColors = /rgb\((\d{1,3}), (\d{1,3}), (\d{1,3})\)/;
            do {
                match = matchColors.exec(Style);
                if (match) {
                    newColor = rgbToHex(match[1], match[2], match[3])
                    Style = Style.replace(matchColors, newColor)
                }
            } while (match)
            el.attr('style', Style.replace(matchColors, newColor));
        },

        paddingCtrl: function (type) {
            for (let i in type) {
                $('.wacv-' + type[i]).on('change', function () {
                    let val = $(this).val()
                    $('.li_' + field_focus).find('.wacv-output').css(type[i], val + 'px')
                    $('.wacv-' + type[i]).val(val)
                    controlPanel.attrSave()
                    getSaveBlock.init()
                })
            }
        }
    }

    const eventHandle = {
        run: function () {
            let $body = $('body')

            // focus block
            $body.on('click', '.wacv-block', function () {
                field_focus = $(this).attr('data-field')
                field_type = $(this).attr('data-type')
                controlPanel.init()
            })

            $body.on('click', '.wacv-remove', function () {
                let field = $(this).attr('data-field')
                $(this).closest('.li_' + field).hide('50', function () {
                    $(this).remove()
                    field_type = ''
                    getSaveBlock.init()
                })
            })

            $body.on('click', '.remove_bal_field', function () {
                let field = $(this).attr('data-field')
                $(this).closest('.li_' + field).hide('50', function () {
                    $(this).remove()
                    field_type = ''
                    getSaveBlock.init()
                })
            })

            $body.on('click', '#wacv-preview a', function (e) {
                e.preventDefault()
            })

            $('#preview_box .inside').on('click', function (e) {
                // $('#wacv-control-panel .wacv-control-table').html('');
            })

            $body.on('change', '.wacv-change-template', function () {
                let html;
                switch ($(this).val()) {
                    case 'temp-1':
                        html = sample;
                        break;
                }
                $('.wacv-email-content').html(html);
                getSaveBlock.init();
            })

            $body.on('click', '.wacv-use-woo-header', function () {
                $('.wacv-email-heading').toggle();
            })

            $body.on('click', '.wacv-send-test-email-btn', function () {
                getSaveBlock.init();
                var data = {
                    action: 'send_test_email',
                    email: $('.wacv-admin-email-test').val(),
                    content: $('.wacv-email-content-html').val(),
                    woo_header: $('.wacv-use-woo-header:checked').length,
                    subject: $('.wacv-subject').val(),
                    heading: $('.wacv-heading').val(),
                    security: wacv_ls.nonce,
                    coupon: $('.wacv-wc-coupon').val()
                };

                $.ajax({
                    type: 'post',
                    // dataType: 'json',
                    url: wacv_ls.ajax_url,
                    data: data,
                    success: function (res) {
                        if (res) {
                            $('.wacv-result-send-test-email').text('Email has sent successfully.').css('color', 'green');
                            setTimeout(function () {
                                $('.wacv-result-send-test-email').text('');
                            }, 3000)
                        } else {
                            $('.wacv-result-send-test-email').text('Email has not sent.').css('color', 'red');
                            setTimeout(function () {
                                $('.wacv-result-send-test-email').text('');
                            }, 3000)
                        }
                    },
                    error: function (res) {
                        console.log(res);
                    },
                    beforeSend: function () {
                        $('.wacv-spinner').toggleClass('is-active');
                    },
                    complete: function () {
                        $('.wacv-spinner').toggleClass('is-active');
                    }
                });
            });

        }
    }


    //Tool function
    function rgbToHex(r, g, b) {
        function hexCode(i) {
            return ("0" + parseInt(i).toString(16)).slice(-2);
        }

        return "#" + hexCode(r) + hexCode(g) + hexCode(b);
    }

    //EXE
    elementsDrag.init(['text', 'divider', 'image', 'cart', 'button']);
    eventHandle.run();
    getSaveBlock.init();

    var sample = `<div class="li_43311 form-builder-field" style="width: 600px; right: auto; height: 43px; bottom: auto;"><div class="wacv-block" data-type="image" data-field="43311"><div class="wacv-handle"><button type="button" class="wacv-move ui-sortable-handle" data-field="43311"><i class="dashicons dashicons-move"></i></button><button type="button" class="wacv-remove ui-sortable-handle" data-field="43311"><i class="dashicons dashicons-trash"></i></button></div>            <div class="wacv-attr-save"></div>            <div class="wacv-content-group wacv-output" style=" padding: 12px 40px;">            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">               <tbody><tr>                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">                <img class="wacv-image" width="100%" src="${wacv_ls.img_src}sample-logo.png" style="vertical-align: middle; ">                   </td>               </tr>            </tbody></table>            </div>        </div></div><div class="li_49114 form-builder-field" style="width: 600px; right: auto; height: 53px; bottom: auto;"><div class="wacv-block" data-type="text" data-field="49114"><div class="wacv-handle"><button type="button" class="wacv-move ui-sortable-handle" data-field="49114"><i class="dashicons dashicons-move"></i></button><button type="button" class="wacv-remove ui-sortable-handle" data-field="49114"><i class="dashicons dashicons-trash"></i></button></div>            <div class="wacv-attr-save" data-attrs="{&quot;color&quot;:&quot;#ffffff&quot;,&quot;bgColor&quot;:&quot;#474747&quot;,&quot;blockColor&quot;:&quot;#474747&quot;,&quot;paddingTop&quot;:&quot;12&quot;,&quot;paddingLeft&quot;:&quot;40&quot;,&quot;paddingRight&quot;:&quot;40&quot;,&quot;paddingBottom&quot;:&quot;12&quot;,&quot;text&quot;:&quot;&amp;lt;p style=\\&quot;text-align: center;\\&quot;&amp;gt;&amp;lt;a style=\\&quot;font-size: 18.6667px; text-align: center;\\&quot; href=\\&quot;{home_url}\\&quot;&amp;gt;Home&amp;lt;/a&amp;gt;&amp;lt;span style=\\&quot;font-size: 18.6667px; text-align: center;\\&quot;&amp;gt;&amp;nbsp;|&amp;nbsp;&amp;lt;/span&amp;gt;&amp;lt;a style=\\&quot;font-size: 18.6667px; text-align: center;\\&quot; href=\\&quot;{shop_url}\\&quot;&amp;gt;Shop&amp;lt;/a&amp;gt;&amp;lt;/p&amp;gt;&quot;}"></div>            <div class="wacv-content-group wacv-output" style="padding: 12px 40px; color: #ffffff; background-color: #474747;">            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">               <tbody><tr>                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">                       <div class="form-control wacv-text-field wacv-background-color-output" style="padding: 5px 0px; background-color: #474747;" data-field="49114"><p style="text-align: center;"><a style="font-size: 18.6667px; text-align: center;" href="{home_url}">Home</a><span style="font-size: 18.6667px; text-align: center;">&nbsp;|&nbsp;</span><a style="font-size: 18.6667px; text-align: center;" href="{shop_url}">Shop</a></p></div>                   </td>               </tr>            </tbody></table>            </div>        </div></div><div class="li_14182 form-builder-field" style="width: 600px; right: auto; height: 53px; bottom: auto;"><div class="wacv-block" data-type="text" data-field="14182"><div class="wacv-handle"><button type="button" class="wacv-move ui-sortable-handle" data-field="14182"><i class="dashicons dashicons-move"></i></button><button type="button" class="wacv-remove ui-sortable-handle" data-field="14182"><i class="dashicons dashicons-trash"></i></button></div>            <div class="wacv-attr-save" data-attrs="{&quot;color&quot;:&quot;#000000&quot;,&quot;bgColor&quot;:&quot;#ffffff&quot;,&quot;blockColor&quot;:&quot;#ffffff&quot;,&quot;paddingTop&quot;:&quot;12&quot;,&quot;paddingLeft&quot;:&quot;40&quot;,&quot;paddingRight&quot;:&quot;40&quot;,&quot;paddingBottom&quot;:&quot;12&quot;,&quot;text&quot;:&quot;&amp;lt;p&amp;gt;&amp;lt;span style=\\&quot;font-size: 12pt;\\&quot;&amp;gt;Hello {customer_name}.&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;\\n&amp;lt;p&amp;gt;&amp;lt;span style=\\&quot;font-size: 12pt;\\&quot;&amp;gt;Looks like you left something fabulous in your shopping cart&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&quot;}"></div>            <div class="wacv-content-group wacv-output" style=" padding: 12px 40px;">            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">               <tbody><tr>                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">                       <div class="form-control wacv-text-field wacv-background-color-output" style="padding: 5px 0" data-field="14182"><p><span style="font-size: 12pt;">Hello {customer_name}.</span></p>
<p><span style="font-size: 12pt;">Looks like you left something fabulous in your shopping cart</span></p></div>                   </td>               </tr>            </tbody></table>            </div>        </div></div><div class="li_75503 form-builder-field" style="width: 600px; right: auto; height: 53px; bottom: auto;"><div class="wacv-block" data-type="text" data-field="75503"><div class="wacv-handle"><button type="button" class="wacv-move ui-sortable-handle" data-field="75503"><i class="dashicons dashicons-move"></i></button><button type="button" class="wacv-remove ui-sortable-handle" data-field="75503"><i class="dashicons dashicons-trash"></i></button></div>            <div class="wacv-attr-save" data-attrs="{&quot;color&quot;:&quot;#ffffff&quot;,&quot;bgColor&quot;:&quot;#474747&quot;,&quot;blockColor&quot;:&quot;#ffffff&quot;,&quot;paddingTop&quot;:&quot;0&quot;,&quot;paddingLeft&quot;:&quot;40&quot;,&quot;paddingRight&quot;:&quot;40&quot;,&quot;paddingBottom&quot;:&quot;0&quot;,&quot;text&quot;:&quot;&amp;lt;p&amp;gt;&amp;lt;span style=\\&quot;font-size: 18.6667px;\\&quot;&amp;gt;&amp;nbsp; &amp;nbsp;Items in your cart&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&quot;}"></div>            <div class="wacv-content-group wacv-output" style="padding: 0px 40px; color: rgb(255, 255, 255);">            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">               <tbody><tr>                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">                       <div class="form-control wacv-text-field wacv-background-color-output" style="padding: 5px 0px; background-color: #474747;" data-field="75503"><p><span style="font-size: 18.6667px;">&nbsp; &nbsp;Items in your cart</span></p></div>                   </td>               </tr>            </tbody></table>            </div>        </div></div><div class="li_8747 form-builder-field" style="width: 600px; right: auto; height: 174px; bottom: auto;"><div class="wacv-block" data-type="cart" data-field="8747"><div class="wacv-handle"><button type="button" class="wacv-move ui-sortable-handle" data-field="8747"><i class="dashicons dashicons-move"></i></button><button type="button" class="wacv-remove ui-sortable-handle" data-field="8747"><i class="dashicons dashicons-trash"></i></button></div>            <div class="wacv-attr-save" data-attrs="{&quot;color&quot;:&quot;#000000&quot;,&quot;bgColor&quot;:&quot;#ffffff&quot;,&quot;blockColor&quot;:&quot;#ffffff&quot;,&quot;borderColor&quot;:&quot;#dddddd&quot;,&quot;paddingTop&quot;:&quot;0&quot;,&quot;paddingLeft&quot;:&quot;40&quot;,&quot;paddingRight&quot;:&quot;40&quot;,&quot;paddingBottom&quot;:&quot;12&quot;}"></div>            <div class="wacv-content-group wacv-output" style="padding: 0px 40px 12px;">            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">               <tbody><tr>                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">                <table class="wacv-background-color-output field-ctrl" style="border-collapse: collapse; border: 1px solid rgb(221, 221, 221);" cellpadding="0" cellspacing="0" height="100%" width="100%">                    <tbody><tr data="{wacv_cart_detail_start}"></tr>                    <tr>                    <td align="center" width="140" style="padding: 5px; border-top: 1px solid rgb(221, 221, 221); border-bottom: 1px solid rgb(221, 221, 221);" class="field-ctrl">                    <img style="width:140px; vertical-align: middle;" src="${wacv_ls.img_src}product.png">                    </td>                    <td style="vertical-align: top; padding: 5px; border-top: 1px solid rgb(221, 221, 221); border-bottom: 1px solid rgb(221, 221, 221);" class="field-ctrl">                         <p style="line-height: 2; font-weight: bold; ">{product_name}</p>                         <p style="line-height: 2;">{product_quantity}</p>                         <p style="line-height: 2;">{product_amount}</p>                    </td>                    </tr>                    <tr data="{wacv_cart_detail_end}"></tr>                </tbody></table>                   </td>               </tr>            </tbody></table>            </div>        </div></div><div class="li_69544 form-builder-field" style="width: 600px; right: auto; height: 88px; bottom: auto;"><div class="wacv-block" data-type="button" data-field="69544"><div class="wacv-handle"><button type="button" class="wacv-move ui-sortable-handle" data-field="69544"><i class="dashicons dashicons-move"></i></button><button type="button" class="wacv-remove ui-sortable-handle" data-field="69544"><i class="dashicons dashicons-trash"></i></button></div>            <div class="wacv-attr-save" data-attrs="{&quot;color&quot;:&quot;#ffffff&quot;,&quot;bgColor&quot;:&quot;#474747&quot;,&quot;blockColor&quot;:&quot;#000000&quot;,&quot;paddingTop&quot;:&quot;12&quot;,&quot;paddingLeft&quot;:&quot;24&quot;,&quot;paddingRight&quot;:&quot;24&quot;,&quot;paddingBottom&quot;:&quot;12&quot;,&quot;text&quot;:&quot;&amp;lt;p style=\\&quot;text-align: center;\\&quot;&amp;gt;&amp;lt;span style=\\&quot;font-size: 14pt;\\&quot;&amp;gt;&amp;lt;strong&amp;gt;&amp;lt;a href=\\&quot;{wacv_checkout_btn}\\&quot;&amp;gt;Checkout Now&amp;lt;/a&amp;gt;&amp;lt;/strong&amp;gt;&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&quot;,&quot;align&quot;:&quot;center&quot;,&quot;buttonWidth&quot;:&quot;50&quot;}"></div>            <div class="wacv-content-group wacv-output" style="padding: 12px 40px; color: #ffffff; text-align: center;">            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">               <tbody><tr>                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">               <button type="button" class="wacv-button-text wacv-background-color-output ui-sortable-handle" style="width: 50%; border: none; padding: 5px 0px; margin: 0px; color: inherit; background-color: #474747;">              <p style="text-align: center;"><span style="font-size: 14pt;"><strong><a href="{wacv_checkout_btn}">Checkout Now</a></strong></span></p></button>                   </td>               </tr>            </tbody></table>            </div>        </div></div><div class="li_16614 form-builder-field" style="width: 600px; right: auto; height: 53px; bottom: auto;"><div class="wacv-block" data-type="text" data-field="16614"><div class="wacv-handle"><button type="button" class="wacv-move ui-sortable-handle" data-field="16614"><i class="dashicons dashicons-move"></i></button><button type="button" class="wacv-remove ui-sortable-handle" data-field="16614"><i class="dashicons dashicons-trash"></i></button></div>            <div class="wacv-attr-save" data-attrs="{&quot;color&quot;:&quot;#000000&quot;,&quot;bgColor&quot;:&quot;#ffffff&quot;,&quot;blockColor&quot;:&quot;#ffffff&quot;,&quot;paddingTop&quot;:&quot;0&quot;,&quot;paddingLeft&quot;:&quot;40&quot;,&quot;paddingRight&quot;:&quot;40&quot;,&quot;paddingBottom&quot;:&quot;12&quot;,&quot;text&quot;:&quot;&amp;lt;p&amp;gt;&amp;lt;span style=\\&quot;font-size: 16px;\\&quot;&amp;gt;If you don’t want receive reminder email. You can unsubscribe&amp;nbsp;&amp;lt;/span&amp;gt;&amp;lt;a style=\\&quot;font-size: 16px;\\&quot; href=\\&quot;{unsubscribe_link}\\&quot;&amp;gt;here&amp;lt;/a&amp;gt;&amp;lt;/p&amp;gt;&quot;}"></div>            <div class="wacv-content-group wacv-output" style="padding: 0px 40px 12px;">            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">               <tbody><tr>                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">                       <div class="form-control wacv-text-field wacv-background-color-output" style="padding: 5px 0" data-field="16614"><p><span style="font-size: 16px;">If you don’t want receive reminder email. You can unsubscribe&nbsp;</span><a style="font-size: 16px;" href="{unsubscribe_link}">here</a></p></div>                   </td>               </tr>            </tbody></table>            </div>        </div></div><div class="li_45819 form-builder-field" style="width: 600px; right: auto; height: 53px; bottom: auto;"><div class="wacv-block" data-type="text" data-field="45819"><div class="wacv-handle"><button type="button" class="wacv-move ui-sortable-handle" data-field="45819"><i class="dashicons dashicons-move"></i></button><button type="button" class="wacv-remove ui-sortable-handle" data-field="45819"><i class="dashicons dashicons-trash"></i></button></div>            <div class="wacv-attr-save" data-attrs="{&quot;color&quot;:&quot;#ffffff&quot;,&quot;bgColor&quot;:&quot;#474747&quot;,&quot;blockColor&quot;:&quot;#474747&quot;,&quot;paddingTop&quot;:&quot;12&quot;,&quot;paddingLeft&quot;:&quot;40&quot;,&quot;paddingRight&quot;:&quot;40&quot;,&quot;paddingBottom&quot;:&quot;12&quot;,&quot;text&quot;:&quot;&amp;lt;p style=\\&quot;text-align: center;\\&quot;&amp;gt;&amp;lt;span style=\\&quot;font-size: 16px; text-align: center;\\&quot;&amp;gt;{site_url}&amp;lt;/span&amp;gt;&amp;lt;/p&amp;gt;&quot;}"></div>            <div class="wacv-content-group wacv-output" style="padding: 12px 40px; color: #ffffff; background-color: #474747;">            <table style="border-collapse: collapse;color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;" cellpadding="0" cellspacing="0" height="100%" width="100%">               <tbody><tr>                   <td style="color:inherit; background-color: inherit; font-size: inherit;text-align: inherit;">                       <div class="form-control wacv-text-field wacv-background-color-output" style="padding: 5px 0px; background-color: #474747;" data-field="45819"><p style="text-align: center;"><span style="font-size: 16px; text-align: center;">{site_url}</span></p></div>                   </td>               </tr>            </tbody></table>            </div>        </div></div>                        `;

    if ($('.wacv-email-content').children().length === 0) {
        $('.wacv-email-content').append(sample);
        getSaveBlock.init();
    }

});


