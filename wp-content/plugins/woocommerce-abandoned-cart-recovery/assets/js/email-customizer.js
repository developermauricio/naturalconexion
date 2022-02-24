jQuery(document).ready(function ($) {
    'use strict';

    if (typeof viWecCacheProducts === 'undefined') var viWecCacheProducts = [];

    ViWec.Components.registerCategory('abandoned', 'WC Abandoned Cart');

    ViWec.Components.add({
        category: 'abandoned',
        type: "html/abandoned_cart",
        name: 'Abandoned Cart',
        icon: 'abandoned-cart',
        html: `<div class="viwec-abandoned-items">
                    <table class="viwec-item-row" border='0' cellpadding='0' cellspacing='0' style="border-collapse: collapse; width: 100%; border-style: solid;">
                        <tr>
                            <td class="viwec-product-img" style="width: 150px;"><img style="width: 100%" src="${viWecParams.product}"></td>
                            <td style="width:10px;"> </td>
                            <td class="viwec-product-detail" valign="middle">
                                <p class="viwec-product-name">Product name</p>
                                <p class="viwec-product-quantity"><span class="viwec-text-quantity">Quantity:</span> 2</p>
                                <p class="viwec-product-price"><span class="viwec-text-price">Price:</span> ${viWecParams.suggestProductPrice}</p>
                            </td>
                        </tr>
                    </table>
                    <div class="viwec-product-distance" style="padding: 10px 0px 0px;"></div>
                    <table class="viwec-item-row" border='0' cellpadding='0' cellspacing='0' style="border-collapse: collapse; width: 100%; border-style: solid;">
                        <tr>
                            <td class="viwec-product-img" style="width: 150px;"><img style="width: 100%" src="${viWecParams.product}"></td>
                            <td style="width:10px;"></td>
                            <td class="viwec-product-detail" valign="middle">
                                <p class="viwec-product-name">Product name</p>
                                <p class="viwec-product-quantity"><span class="viwec-text-quantity">Quantity:</span> 2</p>
                                <p class="viwec-product-price"><span class="viwec-text-price">Price:</span> ${viWecParams.suggestProductPrice}</p>
                            </td>
                        </tr>
                    </table>
                </div>`,

        properties: [
            {
                key: "translate_text",
                inputType: SectionInput,
                name: false,
                target: '.viwec-text-quantity',
                section: contentSection,
                data: {header: "Translate text"}
            },
            {
                name: "Quantity",
                key: "quantity",
                target: '.viwec-text-quantity',
                htmlAttr: "innerHTML",
                section: contentSection,
                col: 16,
                inline: true,
                inputType: TextInput
            },
            {
                name: "Price",
                key: "price",
                target: '.viwec-text-price',
                htmlAttr: "innerHTML",
                section: contentSection,
                col: 16,
                inline: true,
                inputType: TextInput
            },
            {
                key: "table_style",
                inputType: SectionInput,
                name: false,
                target: '.viwec-item-row',
                section: styleSection,
                data: {header: "Abandoned items"}
            },
            {
                name: "Background",
                key: "background-color",
                target: '.viwec-item-row',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                inputType: ColorInput
            },
            {
                name: "Image size",
                key: "width",
                target: '.viwec-product-img',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                unit: 'px',
                inputType: NumberInput
            },
            // {
            //     name: "Product detail",
            //     key: "width",
            //     target: '.viwec-product-detail',
            //     htmlAttr: "childStyle",
            //     section: styleSection,
            //     col: 8,
            //     inline: true,
            //     unit: 'px',
            //     inputType: NumberInput,
            //     hidden: true
            // },
            {
                name: "Border width",
                key: "border-width",
                target: '.viwec-item-row',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                unit: 'px',
                inputType: NumberInput,
                data: {value: 20},
            },
            {
                name: "Border color",
                key: "border-color",
                target: '.viwec-item-row',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                inputType: ColorInput,
                data: {value: 20},
            },
            {
                name: "Items distance",
                key: "padding-top",
                target: '.viwec-product-distance',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                unit: 'px',
                inputType: NumberInput,
            },
            {
                key: "product_name",
                inputType: SectionInput,
                name: false,
                target: '.viwec-product-name',
                section: styleSection,
                data: {header: "Product name"}
            },
            {
                name: "Font size",
                key: "font-size",
                target: '.viwec-product-name',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                unit: 'px',
                inputType: NumberInput
            },
            {
                name: "Font weight",
                key: "font-weight",
                target: '.viwec-product-name',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                inputType: SelectInput,
                data: {
                    options: viWecFontWeightOptions
                }
            },
            {
                name: "Color",
                key: "color",
                target: '.viwec-product-name',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                inputType: ColorInput
            },
            {
                name: "Line height (px)",
                key: "line-height",
                target: '.viwec-product-name',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                unit: 'px',
                inline: true,
                inputType: NumberInput
            },
            {
                key: "product_quantity",
                inputType: SectionInput,
                name: false,
                target: '.viwec-product-quantity',
                section: styleSection,
                data: {header: "Product quantity"}
            },
            {
                name: "Font size",
                key: "font-size",
                target: '.viwec-product-quantity',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                unit: 'px',
                inputType: NumberInput
            },
            {
                name: "Font weight",
                key: "font-weight",
                target: '.viwec-product-quantity',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                inputType: SelectInput,
                data: {
                    options: viWecFontWeightOptions
                }
            },
            {
                name: "Color",
                key: "color",
                target: '.viwec-product-quantity',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                inputType: ColorInput
            },
            {
                name: "Line height (px)",
                key: "line-height",
                target: '.viwec-product-quantity',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                unit: 'px',
                inline: true,
                inputType: NumberInput
            },
            {
                key: "product_price",
                inputType: SectionInput,
                name: false,
                target: '.viwec-product-price',
                section: styleSection,
                data: {header: "Product price"}
            },
            {
                name: "Font size",
                key: "font-size",
                target: '.viwec-product-price',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                unit: 'px',
                inputType: NumberInput
            },
            {
                name: "Font weight",
                key: "font-weight",
                target: '.viwec-product-price',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                inputType: SelectInput,
                data: {
                    options: viWecFontWeightOptions
                }
            },
            {
                name: "Color",
                key: "color",
                target: '.viwec-product-price',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                inline: true,
                inputType: ColorInput
            },
            {
                name: "Line height (px)",
                key: "line-height",
                target: '.viwec-product-price',
                htmlAttr: "childStyle",
                section: styleSection,
                col: 8,
                unit: 'px',
                inline: true,
                inputType: NumberInput
            }
        ],
        // inheritProp: ['padding', 'background']//, 'border'] //'text',
    });

    if (typeof wacvParams !== 'undefined') {
        if (wacvParams.emailTemplateFree) {
            ViWec.Components.add({
                category: 'abandoned',
                type: "html/coupon",
                name: 'Coupon',
                icon: 'coupon',
                html: `<div class="viwec-coupon" style="display: inline-block;border:2px solid #cfcfcf; padding: 15px 30px; background-color: #eeeeee;"><div class="viwec-coupon-text">COUPONCODE</div></div>`,

                properties: [
                    {
                        key: "coupon_setting",
                        inputType: SectionInput,
                        name: false,
                        section: contentSection,
                        data: {header: "Setting"},
                    },
                    {
                        name: "Coupon type",
                        key: "data-coupon-type",
                        htmlAttr: 'data-coupon-type',
                        section: contentSection,
                        classes: 'viwec-coupon-type',
                        col: 16,
                        inputType: SelectInput,
                        data: {
                            options: [
                                {id: '1', text: "Existing coupon"},
                                {id: '2', text: "Generate coupon"},
                            ]
                        },
                        onChange(element, value) {
                            let selectCoupon = $('.viwec-select-coupon');
                            let generateCoupon = $('.viwec-generate-coupon');
                            switch (value) {
                                case '1':
                                    selectCoupon.show();
                                    generateCoupon.hide();
                                    element.click();
                                    break;
                                case '2':
                                    selectCoupon.hide();
                                    generateCoupon.show();
                                    element.find('.viwec-coupon-text').text('COUPONCODE');
                                    break;

                            }
                            return element;
                        }
                    },
                    {
                        name: "Select coupon",
                        key: "data-coupon-code",
                        htmlAttr: 'innerHTML',
                        target: '.viwec-coupon-text',
                        section: contentSection,
                        classes: 'viwec-select-coupon',
                        col: 16,
                        inputType: SelectInput,
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '1' ? row.show() : row.hide();
                            row.find('select').select2({
                                width: '100%',
                                minimumInputLength: 2,
                                ajax: {
                                    url: viWecParams.ajaxUrl,
                                    dataType: 'json',
                                    type: "POST",
                                    quietMillis: 50,
                                    delay: 250,
                                    data: function (params) {
                                        return {q: params.term, action: 'viwec_search_coupon', nonce: viWecParams.nonce};
                                    },
                                    processResults: function (data) {
                                        return {results: data};
                                    },
                                },
                            });

                            return row;
                        }
                    },
                    {
                        name: "Discount type",
                        key: "data-discount-type",
                        htmlAttr: 'data-discount-type',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: SelectInput,
                        data: {
                            options: [
                                {id: 'percentage', text: "Percentage discount"},
                                {id: 'fixed_cart', text: "Fixed cart discount"},
                                {id: 'fixed_product', text: "Fixed product discount"},
                            ]
                        },
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        name: "Coupon amount",
                        key: "data-coupon-amount",
                        htmlAttr: 'data-coupon-amount',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: TextInput,
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        name: "Expire after x days",
                        key: "data-coupon-expiry-date",
                        htmlAttr: 'data-coupon-expiry-date',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: TextInput,
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        name: "Minimum spend",
                        key: "data-coupon-min-spend",
                        htmlAttr: 'data-coupon-min-spend',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: NumberInput,
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        name: "Maximum spend",
                        key: "data-coupon-max-spend",
                        htmlAttr: 'data-coupon-max-spend',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: NumberInput,
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        name: "Products",
                        key: "data-coupon-include-product",
                        htmlAttr: 'data-coupon-include-product',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: Select2Input,
                        data: {options: viWecCacheProducts, multiple: true},
                        setup(row) {
                            let $_this = this;
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();

                            row.find('select').select2({
                                width: '100%',
                                cache: true,
                                minimumInputLength: 3,
                                ajax: {
                                    url: viWecParams.ajaxUrl,
                                    dataType: 'json',
                                    type: "GET",
                                    quietMillis: 50,
                                    delay: 250,
                                    data: function (params) {
                                        return {
                                            term: params.term,
                                            action: 'woocommerce_json_search_products_and_variations',
                                            security: wc_enhanced_select_params.search_products_nonce
                                        };
                                    },
                                    processResults: function (data) {
                                        var terms = [];
                                        if (data) {
                                            $.each(data, function (id, text) {
                                                terms.push({id: id, text: text});
                                            });
                                        }

                                        $_this.data.options = [...$_this.data.options, ...terms];

                                        return {results: terms};
                                    },
                                },
                            });
                            return row;
                        }
                    },
                    {
                        name: "Exclude products",
                        key: "data-coupon-exclude-product",
                        htmlAttr: 'data-coupon-exclude-product',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: Select2Input,
                        data: {options: viWecCacheProducts, multiple: true},
                        setup(row) {
                            let $_this = this;
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();

                            row.find('select').select2({
                                width: '100%',
                                cache: true,
                                minimumInputLength: 3,
                                ajax: {
                                    url: viWecParams.ajaxUrl,
                                    dataType: 'json',
                                    type: "GET",
                                    quietMillis: 50,
                                    delay: 250,
                                    data: function (params) {
                                        return {
                                            term: params.term,
                                            action: 'woocommerce_json_search_products_and_variations',
                                            security: wc_enhanced_select_params.search_products_nonce
                                        };
                                    },
                                    processResults: function (data) {
                                        var terms = [];
                                        if (data) {
                                            $.each(data, function (id, text) {
                                                terms.push({id: id, text: text});
                                            });
                                        }

                                        $_this.data.options = [...$_this.data.options, ...terms];

                                        return {results: terms};
                                    },
                                },
                            });
                            return row;
                        }
                    },
                    {
                        name: "Categories",
                        key: "data-coupon-include-categories",
                        htmlAttr: 'data-coupon-include-categories',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: Select2Input,
                        data: {options: viWecParams.product_categories, multiple: true},
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();

                            row.find('select').select2({width: '100%'});
                            return row;
                        }
                    },
                    {
                        name: "Exclude categories",
                        key: "data-coupon-exclude-categories",
                        htmlAttr: 'data-coupon-exclude-categories',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: Select2Input,
                        data: {options: viWecParams.product_categories, multiple: true},
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();

                            row.find('select').select2({width: '100%'});
                            return row;
                        }
                    },
                    {
                        name: "Usage limit per coupon",
                        key: "data-coupon-limit-quantity",
                        htmlAttr: 'data-coupon-limit-quantity',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: NumberInput,
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        name: "Limit usage to X items",
                        key: "data-coupon-limit-items",
                        htmlAttr: 'data-coupon-limit-items',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: NumberInput,
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        name: "Usage limit per user",
                        key: "data-coupon-limit-users",
                        htmlAttr: 'data-coupon-limit-users',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: NumberInput,
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        name: "Allow free shipping",
                        key: "data-coupon-allow-free-shipping",
                        htmlAttr: 'data-coupon-allow-free-shipping',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: CheckboxInput,
                        data: {
                            options: [
                                {id: 'no', text: "No"},
                                {id: 'yes', text: "Yes"},
                            ]
                        },
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        name: "Individual use only",
                        key: "data-coupon-individual",
                        htmlAttr: 'data-coupon-individual',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: CheckboxInput,
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        name: "Exclude sale items",
                        key: "data-coupon-exclude-sale",
                        htmlAttr: 'data-coupon-exclude-sale',
                        section: contentSection,
                        classes: 'viwec-generate-coupon',
                        col: 16,
                        inputType: CheckboxInput,
                        setup(row) {
                            $('.viwec-coupon-type').find('select').val() === '2' ? row.css('display', 'inline-block') : row.hide();
                            return row;
                        }
                    },
                    {
                        key: "button_header",
                        inputType: SectionInput,
                        name: false,
                        section: styleSection,
                        data: {header: "Coupon"}
                    },
                    {
                        name: "Border width",
                        key: "border-width",
                        htmlAttr: "childStyle",
                        target: '.viwec-coupon',
                        section: styleSection,
                        col: 8,
                        inline: true,
                        inputType: NumberInput,
                        unit: 'px',
                        data: {min: 0, max: 10, step: 1}
                    },
                    {
                        name: "Border radius",
                        key: "border-radius",
                        htmlAttr: "childStyle",
                        target: '.viwec-coupon',
                        section: styleSection,
                        col: 8,
                        inline: true,
                        inputType: NumberInput,
                        unit: 'px',
                        data: {min: 0, max: 50, step: 1}
                    },
                    {
                        name: "Border color",
                        key: "border-color",
                        htmlAttr: "childStyle",
                        target: '.viwec-coupon',
                        section: styleSection,
                        col: 8,
                        inline: true,
                        inputType: ColorInput
                    },
                    {
                        name: "Border style",
                        key: "border-style",
                        htmlAttr: "childStyle",
                        target: '.viwec-coupon',
                        section: styleSection,
                        col: 8,
                        inline: true,
                        data: {
                            options: [
                                {id: 'solid', text: 'Solid'},
                                {id: 'dotted', text: 'Dotted'},
                                {id: 'dashed', text: 'Dashed'},
                            ]
                        },
                        inputType: SelectInput
                    },
                    {
                        name: "Background color",
                        key: "background-color",
                        htmlAttr: "childStyle",
                        target: '.viwec-coupon',
                        section: styleSection,
                        col: 8,
                        inline: true,
                        inputType: ColorInput
                    },
                    {
                        key: "padding_el_header",
                        inputType: SectionInput,
                        name: false,
                        section: styleSection,
                        data: {header: "Padding (px)"}
                    },
                    {
                        name: "Left",
                        key: "padding-left",
                        htmlAttr: "childStyle",
                        target: '.viwec-coupon',
                        section: styleSection,
                        col: 4,
                        inline: true,
                        unit: 'px',
                        inputType: NumberInput,
                        data: {id: 20, max: 250}

                    },
                    {
                        name: "Top",
                        key: "padding-top",
                        htmlAttr: "childStyle",
                        target: '.viwec-coupon',
                        section: styleSection,
                        col: 4,
                        inline: true,
                        unit: 'px',
                        inputType: NumberInput,
                        data: {id: 20}

                    },
                    {
                        name: "Right",
                        key: "padding-right",
                        htmlAttr: "childStyle",
                        target: '.viwec-coupon',
                        section: styleSection,
                        col: 4,
                        inline: true,
                        unit: 'px',
                        inputType: NumberInput,
                        data: {id: 20, max: 250}

                    },
                    {
                        name: "Bottom",
                        key: "padding-bottom",
                        htmlAttr: "childStyle",
                        target: '.viwec-coupon',
                        section: styleSection,
                        col: 4,
                        inline: true,
                        unit: 'px',
                        inputType: NumberInput,
                        data: {id: 20}
                    }
                ],
                inheritProp: ['text', 'alignment', 'margin']//, 'background']
            });
        }
    }

    ViWec.Components.add({
        category: 'abandoned',
        type: "html/recover_button",
        name: 'Recover button',
        icon: 'button',
        info: `<div class="wacv dashicons dashicons-info">
                    <span class="wacv-recover-button-notice">
                        If you use coupon element, put this button below coupon element to auto add coupon to this button
                    </span>
                </div>`,
        html: `<a href="#" class="viwec-button viwec-background viwec-padding" 
                style="border-style:solid;display:inline-block;width:auto;padding: 10px 20px;text-decoration: none;text-align: center;max-width: 100%;background-color: #dddddd">
                    <span class="viwec-text-content">Checkout</span>
                </a>`,

        properties: [
            {
                key: "text_header",
                inputType: SectionInput,
                name: false,
                section: contentSection,
                data: {header: "Text"},
            },
            {
                key: "text",
                htmlAttr: 'innerHTML',
                target: '.viwec-text-content',
                section: contentSection,
                col: 16,
                inputType: TextInput,
                renderShortcode: true,
                data: {shortcodeTool: true}
            },
            {
                key: "button_header",
                inputType: SectionInput,
                name: false,
                section: styleSection,
                data: {header: "Button"},
            },
            {
                name: "Border width",
                key: "border-width",
                htmlAttr: "childStyle",
                target: 'a',
                section: styleSection,
                col: 8,
                inline: true,
                inputType: NumberInput,
                unit: 'px',
                data: {min: 0, max: 10, step: 1}
            },
            {
                name: "Border radius",
                key: "border-radius",
                htmlAttr: "childStyle",
                target: 'a',
                section: styleSection,
                col: 8,
                inline: true,
                inputType: NumberInput,
                unit: 'px',
                data: {min: 0, max: 50, step: 1}
            },
            {
                name: "Border color",
                key: "border-color",
                htmlAttr: "childStyle",
                target: 'a',
                section: styleSection,
                col: 8,
                inline: true,
                inputType: ColorInput
            },
            {
                name: "Button color",
                key: "background-color",
                htmlAttr: "childStyle",
                target: 'a',
                section: styleSection,
                col: 8,
                inline: true,
                inputType: ColorInput
            },
            {
                name: "Width (px)",
                key: "width",
                htmlAttr: "childStyle",
                target: 'a',
                section: styleSection,
                col: 8,
                inputType: NumberInput,
                unit: 'px',
                data: {min: 0, max: 600}
            },
            {
                key: "padding_el_header",
                inputType: SectionInput,
                name: false,
                section: styleSection,
                data: {header: "Padding (px)"}
            },
            {
                name: "Left",
                key: "padding-left",
                htmlAttr: "childStyle",
                target: 'a',
                section: styleSection,
                col: 4,
                inline: true,
                unit: 'px',
                inputType: NumberInput,
                data: {id: 20, max: 250},
            },
            {
                name: "Top",
                key: "padding-top",
                htmlAttr: "childStyle",
                target: 'a',
                section: styleSection,
                col: 4,
                inline: true,
                unit: 'px',
                inputType: NumberInput,
                data: {id: 20},
            },
            {
                name: "Right",
                key: "padding-right",
                htmlAttr: "childStyle",
                target: 'a',
                section: styleSection,
                col: 4,
                inline: true,
                unit: 'px',
                inputType: NumberInput,
                data: {id: 20, max: 250},
            },
            {
                name: "Bottom",
                key: "padding-bottom",
                htmlAttr: "childStyle",
                target: 'a',
                section: styleSection,
                col: 4,
                inline: true,
                unit: 'px',
                inputType: NumberInput,
                data: {id: 20},
            }
        ],
        inheritProp: ['text', 'alignment', 'margin']
    });

});
