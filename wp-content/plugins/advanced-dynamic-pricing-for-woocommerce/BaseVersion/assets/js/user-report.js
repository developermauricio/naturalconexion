/**
 * @var {{ajaxurl:string, i:object, classes:object}} user_report_data
 */

/**
 *
 * @type {{init: rule_tooltip.init, onmouseout: rule_tooltip.onmouseout, showingTooltip: null, onmouseover: rule_tooltip.onmouseover}}
 */
var rule_tooltip = {
    showingTooltip: null,

    onmouseover: function (e) {
        var _this = rule_tooltip;
        var target = e.target;

        var rule_id = target.getAttribute('data-rule-id');
        if (!rule_id && target.parentElement) {
            target = target.parentElement;
            rule_id = target.getAttribute('data-rule-id');
        }

        if (!rule_id || rule_id === "0") return;

        var tooltip_content = '';
        // tooltip_content += "<div>" + user_report_data.i.rule_id + ':' + rule_id + "</div>";
        tooltip_content += "<div>" + user_report_data.i.rule + ' : ' + '"' + rules_storage.get_title(rule_id) + '"' + "</div>";

        if ( target.classList.contains(user_report_data.classes.replaced_by_coupon) ) {
            tooltip_content += "<div>" + user_report_data.i.replaced_by_coupon + "</div>";
        } else if ( target.classList.contains((user_report_data.classes.replaced_by_fee)) ) {
            tooltip_content += "<div>" + user_report_data.i.replaced_by_fee + "</div>";
        }

        var tooltipElem = document.createElement('div');
        tooltipElem.className = 'tooltip';
        tooltipElem.innerHTML = tooltip_content;
        document.body.appendChild(tooltipElem);

        var coords = target.getBoundingClientRect();

        var left = coords.left + (target.offsetWidth - tooltipElem.offsetWidth) / 2;
        if (left < 0) left = 0;

        var top = coords.top - tooltipElem.offsetHeight - 5;
        if (top < 0) {
            top = coords.top + target.offsetHeight + 5;
        }

        tooltipElem.style.left = left + 'px';
        tooltipElem.style.top = top + 'px';

        _this.showingTooltip = tooltipElem;
    },

    onmouseout: function (e) {
        var _this = rule_tooltip;

        if (_this.showingTooltip) {
            document.body.removeChild(_this.showingTooltip);
            _this.showingTooltip = null;
        }

    },

    init: function () {
        var _this = rule_tooltip;

        document.onmouseover = _this.onmouseover;
        document.onmouseout = _this.onmouseout;
    }

};

var rules_storage = {
    rules: {},

    update: function (rules) {
        var storage_rules = {};
        jQuery.each(rules, function(index, rule) {
            storage_rules[index] = rule;
        });
        this.rules = storage_rules;
    },

    get_title: function (rule_id) {
        return this.is_rule_exists(rule_id) ? this.rules[rule_id].title : false;
    },

    get_edit_url: function (rule_id) {
        return this.is_rule_exists(rule_id) ? this.rules[rule_id].edit_page_url : false;
    },

    is_rule_exists: function (rule_id) {
        return typeof this.rules[rule_id] !== 'undefined';
    }
};


var wdp_reporter = {
    container: null,

    ajaxurl: user_report_data.ajaxurl,
    import_key: user_report_data.import_key,

    format_price: function ($price) {
        $price = parseFloat($price);

        return Math.round($price * 100) / 100;
    },

    format_difference: function ($price) {
        return $price > 0 ? "+" + this.format_price($price) : this.format_price($price);
    },

    format_decimals: function ($price) {
        $price = parseFloat($price);

        return Math.round($price * 100) / 100;
    },

    template_manager: {
        templates: {
            'tab': '#wdp_reporter_tab_template',
            'tab_link': '#wdp_reporter_tab_link_template',
            'history_chunk': '#wdp_reporter_history_chunk_template',


            'cart_is_empty': '#wdp_reporter_tab_cart_empty_template',
            'cart_items': '#wdp_reporter_tab_cart_items_template',
            'cart_coupons': '#wdp_reporter_tab_cart_coupons_template',
            'cart_fees': '#wdp_reporter_tab_cart_fees_template',
            'single_item': '#wdp_reporter_tab_items_single_item_template',
            'cart_coupon': '#wdp_reporter_tab_items_single_coupon_template',
            'cart_adj_history_chunk': '#wdp_reporter_tab_items_cart_adj_history_chink_template',
            'cart_merged_coupon_chunk': '#wdp_reporter_tab_items_cart_merged_coupon_chink_template',
            'cart_merged_coupon_dist_item_chunk': '#wdp_reporter_tab_items_cart_merged_coupon_dist_item_chink_template',
            'empty_history': '#wdp_reporter_tab_items_single_item_empty_history_template',
            'gifted_history': '#wdp_reporter_tab_items_single_item_gifted_history_template',
            'cart_fee': '#wdp_reporter_tab_items_single_fee_template',

            'cart_shipping': '#wdp_reporter_tab_cart_shipping_template',
            'cart_shipping_package': '#wdp_reporter_tab_cart_shipping_package_template',
            'shipping_rate': '#wdp_reporter_tab_items_single_shipping_rate_template',
            'free_wdp_shipping_rate': '#wdp_reporter_tab_items_single_free_shipping_rate_template',

            'products': '#wdp_reporter_tab_products_template',
            'product_row': '#wdp_reporter_tab_products_single_product_template',

            'rules': '#wdp_reporter_tab_rules_template',
            'product_rules_table': '#wdp_reporter_tab_rules_products_table_template',
            'cart_rules_table': '#wdp_reporter_tab_rules_cart_table_template',
            'rule_row': '#wdp_reporter_tab_rules_single_rule_template',

            'export_buttons': '#wdp_reporter_tab_reports_buttons_template',
        },

        get_template: function (name, variables) {
            var template_selector = wdp_reporter.template_manager.templates[name] || '';
            if (!template_selector) {
                return '';
            }

            if ( jQuery(template_selector).length === 0 ) {
                console.log("%c Template %s not found", "color:red;",  name);
                return '';
            }

            var template = jQuery(template_selector).html();

            var required_variable_keys = [];
            var regExp = /{(\w+)}/g;
            var match = regExp.exec(template);

            while (match != null) {
                required_variable_keys.push(match[1]);
                match = regExp.exec(template);
            }

            for (var i = 0; i < required_variable_keys.length; i++) {
                var required_key = required_variable_keys[i];

                if (Object.keys(variables).indexOf(required_key) !== -1) {
                    template = template.replace(new RegExp('{' + required_key + '}', 'g'), variables[required_key]);
                } else {
                    console.log("%c Key %s not found in template \"%s\"", "color:red;", required_key, name);
                    template = '';
                }
            }

            return template;
        }

    },

    update: function ($) {
      let data = {
        action: 'get_user_report_data',
        import_key: this.import_key,
      };

      data[user_report_data.security_param] = user_report_data.security;

        jQuery.ajax({
            url: wdp_reporter.ajaxurl,
            data: data,
            dataType: 'json',
            type: 'POST',
            beforeSend: function() {
                jQuery("#progress_div").show();
            },
            success: function (response) {

		if (!response.data.processed_cart) {
		    return;
		}
                rules_storage.update(response.data.rules);
                wdp_reporter.fill_tabs(response.data);
            },
            error: function (response) {
                console.log("%c Update ajax error", "color:red;");
                console.log(response);
            },
            complete: function() {
                jQuery("#progress_div").hide();
            }
        });
    },

    fill_tabs: function (data) {
        jQuery('#wdp-report-tab-window').html('');
        wdp_reporter.tab_cart.fill(data.processed_cart);
        wdp_reporter.tab_products.fill(data.processed_products);
        wdp_reporter.tab_rules.fill(data);
        wdp_reporter.tab_get_report.fill();
    },

    tab_rules: {
        key: 'rules',
        label: user_report_data.i.rules,

        fill: function(data) {
            var rules = [];
            if (data.rules) {
                rules = data.rules;
            }
            var $cart_rules = '';
            var $cart_table_classes_formatted = '';
            var $product_rules = '';
            var $product_table_classes_formatted = '';
            var _this = this;

            if (rules) {
                var index = 1;
                jQuery.each(rules, function (id, rule) {
                    $cart_rules += _this.make_row(rule, id, index++)
                });
            } else {
                $cart_table_classes_formatted = 'hide ';
            }

            if (rules) { //need to get $product_rules from data.processed_products. how to get exec_time?
                var index = 1;
                jQuery.each(rules, function (id, rule) {
                    $product_rules += _this.make_row(rule, id, index++);
                });
            } else {
                $product_table_classes_formatted = 'hide ';
            }

            var $cart_table_html = wdp_reporter.template_manager.get_template('cart_rules_table', {
                'rule_rows': $cart_rules,
            });

            var $products_table_html = wdp_reporter.template_manager.get_template('product_rules_table', {
                'rule_rows': $product_rules,
            });

            var $tab_content_html = wdp_reporter.template_manager.get_template('rules', {
                'cart_table': $cart_table_html,
                'products_table': /* $products_table_html */'',
                'cart_table_classes': $cart_table_classes_formatted,
                'products_table_classes': /* $product_table_classes_formatted */'hide ',
            });

            var $tab_product_content = wdp_reporter.template_manager.get_template('tab', {
                'tab_key': this.key,

                'active': '',

                'sub_tabs_selector_html': '',
                'sub_tabs_selector_class': '',

                'tab_content_html': $tab_content_html,
            });

            jQuery('#wdp-report-tab-window').append($tab_product_content);
        },

        make_row: function (rule, id, index) {
            return wdp_reporter.template_manager.get_template('rule_row', {
                'rule_id': id,
                'index': index,
                'title': rules_storage.get_title(id),
                'edit_page_url': rules_storage.get_edit_url(id),
                'timing': /* rule.exec_time >= 0.01 ? wdp_reporter.format_decimals(rule.exec_time) : '< 0.01' */'',
            });
        },
    },

    tab_products: {
        key: 'products',
        label: user_report_data.i.products,

        fill: function(products) {
            var $products = '';
            var _this = this;
            var index = 1;

            jQuery.each(products, function (product_id, product) {
                $products += _this.make_row(product, product_id, index++);
            });

            var $tab_content_html = wdp_reporter.template_manager.get_template('products', {
                'product_rows': $products,
            });

            var $tab_product_content = wdp_reporter.template_manager.get_template('tab', {
                'tab_key': 'products',

                'active': '',

                'sub_tabs_selector_html': '',
                'sub_tabs_selector_class': '',

                'tab_content_html': $tab_content_html,
            });

            jQuery('#wdp-report-tab-window').append($tab_product_content);
        },

        make_row: function (product, product_id, index) {
            if(!product.length) {
                return '';
            }
            var $history = '';
            var $original_price = product[0].results.original_price;

            if (product[0].results.are_rule_applied) {
                jQuery.each(product[0].rules, function (rule_id, amount) {
                    var $amount = parseFloat(amount[0]);

                    $history += wdp_reporter.template_manager.get_template('history_chunk',
                        {
                            'rule_id': rule_id,
                            'old_price': wdp_reporter.format_price($original_price),
                            'amount': wdp_reporter.format_difference((-1) * $amount),
                            'new_price': wdp_reporter.format_price($original_price - $amount),
                            'is_replaced': '',
                        }
                    );
                    $original_price -= $amount;
                });
            } else {
                $history += wdp_reporter.template_manager.get_template('empty_history', {});
            }

            return wdp_reporter.template_manager.get_template('product_row', {
                'product_id': product_id,
                'parent_product_id': product[0].results.parent_id,
                'index': index,
                'name': product[0].results.name,
                'page_url': product[0].results.page_url,
                'original_price': wdp_reporter.format_price(product[0].results.original_price),
                'discounted_price': wdp_reporter.format_price(product[0].results.calculated_price),
                'history': $history,
            });
        },
    },


    tab_cart: {
        key: 'cart',
        label: user_report_data.i.cart,

        tab_process: {
            key: 'process',

            is_show: function (data) {
                return data.length > 0;
            },

            get_selector_html: function ($index) {
                var selected = $index == 0;
                return wdp_reporter.template_manager.get_template('tab_link', {
                    'selected': selected ? 'selected' : '',
                    'tab_key': this.key + '_' + $index,
                    'tab_label': 'Process ' + ($index+1),
                });
            },

            get_content_html: function ($sub_tabs_html, $sub_tabs_selector_html, $index) {
                var $active = '';
                if($index == 0) {
                    $active = 'active';
                }
                return wdp_reporter.template_manager.get_template('tab', {
                    'tab_key': this.key + '_' + $index,

                    'active': $active,

                    'sub_tabs_selector_html': $sub_tabs_selector_html,
                    'sub_tabs_selector_class': '',

                    'tab_content_html': $sub_tabs_html,
                });
            },
        },

        tab_items: {
            key: 'items',
            label: user_report_data.i.items,

            is_show: function (data) {
                return Object.keys(data.items).length > 0;
            },

            get_items_html: function (items) {
                var $items_tab_content = '';
                var $index = 1;

                jQuery.each(items, function (hash, data) {
                    var $qty = data.clear.quantity;
                    var $original_price;
                    if(data.our_data.orig != null) {
                        $original_price = data.our_data.orig.original_price;
                    } else {
                        $original_price = data.clear.data.price_edit;
                    }
                    var $original_price_history = $original_price;

                    var $our_data_history;
                    if(data.our_data.history != null) {
                        $our_data_history = data.our_data.history;
                    } else {
                        $our_data_history = [];
                    }
                    var is_on_adp_sale = Object.keys($our_data_history).length > 0;
                    var is_adp_gifted;

                    if(data.our_data.attr) {
                        is_adp_gifted = data.our_data.attr.includes("free");
                    }

                    var $history = '';
                    if (is_on_adp_sale) {
                        if (is_adp_gifted) {
                            var $rule_id = parseInt(Object.keys(data.our_data.history)[0]);

                            var $is_replaced = false;
                            jQuery.each(data.coupon_replacements, function (index, coupon) {
                                if(coupon.ruleId == $rule_id) {
                                    $is_replaced = true;
                                    return false;
                                }
                            });

                            $history += wdp_reporter.template_manager.get_template('gifted_history', {
                                'rule_id': $rule_id,
                                'is_replaced': $is_replaced ? user_report_data.classes.replaced_by_coupon : '',
                            });
                        } else {
                            jQuery.each(data.our_data.history, function (rule_id, history_chunk) {
                                var $is_replaced = false;
                                jQuery.each(data.coupon_replacements, function (index, coupon) {
                                    if(coupon.ruleId == rule_id) {
                                        $is_replaced = true;
                                        return false;
                                    }
                                });

                                var $rule_id = parseInt(rule_id);
                                var $amount = parseFloat(history_chunk[0]);

                                var replaced_by = '';
                                if ($is_replaced) {
                                    if ($amount > 0) {
                                        replaced_by = user_report_data.classes.replaced_by_coupon;
                                    } else if ($amount < 0) {
                                        replaced_by = user_report_data.classes.replaced_by_fee;
                                    }
                                }

                                $history += wdp_reporter.template_manager.get_template('history_chunk',
                                    {
                                        'rule_id': $rule_id,
                                        'old_price': wdp_reporter.format_price($original_price_history),
                                        'amount': wdp_reporter.format_difference((-1) * $amount),
                                        'new_price': wdp_reporter.format_price($original_price_history - $amount),
                                        'is_replaced': replaced_by,
                                    }
                                );
                                $original_price_history -= $amount;
                            });
                        }
                    } else {
                        $history += wdp_reporter.template_manager.get_template('empty_history', {});
                    }


                    $items_tab_content += wdp_reporter.template_manager.get_template('single_item', {
                        'hash': hash,
                        'index': $index++,
                        'quantity': $qty,
                        'title': data.clear.data.name,
                        'original_price': wdp_reporter.format_price($original_price),
                        'price': wdp_reporter.format_price(data.clear.data.price_edit),
                        'history': $history
                    });
                });

                return wdp_reporter.template_manager.get_template('cart_items', {'items': $items_tab_content});
            },

            get_selector_html: function (selected, index) {
                return wdp_reporter.template_manager.get_template('tab_link', {
                    'selected': selected ? 'selected' : '',
                    'tab_key': this.key + "_" + index,
                    'tab_label': this.label
                });
            },

            get_content_html: function (data, index) {
                return wdp_reporter.template_manager.get_template('tab', {
                    'tab_key': this.key + "_" + index,

                    'active': 'active',

                    'sub_tabs_selector_html': '',
                    'sub_tabs_selector_class': 'hide',

                    'tab_content_html': this.get_items_html(data.items),
                });
            },
        },

        tab_coupons: {
            key: 'coupons',
            label: user_report_data.i.coupons,

            is_show: function (data) {
                return Object.keys(data.coupons).length > 0;
            },

            get_coupons_html: function(coupons) {
                if ( coupons['use_merged'] !== undefined && coupons['use_merged'] === true ) {
                  return this.get_coupons_html_merged(coupons);
                } else {
                  return this.get_coupons_html_legacy(coupons);
                }
            },

            get_coupons_html_legacy: function (coupons) {
                var $cart_coupons_tab_content = '';

                jQuery.each(coupons.applied, function ($index, $name) {
                    var $rules = '';
                    var $coupon;
                    var $amount;

                    if(coupons.adp.grouped.hasOwnProperty($name)) {
                        var $rules_amount = [];
                        $coupon = coupons.adp.grouped[$name];
                        jQuery.each($coupon, function($index, $group_coupon) {
                            if(!$rules_amount[$group_coupon.ruleId]) {
                                $rules_amount[$group_coupon.ruleId] = $group_coupon.value;
                            }
                            else {
                                $rules_amount[$group_coupon.ruleId] += $group_coupon.value;
                            }
                            $amount = $group_coupon.amount;
                        });
                        $rules_amount.forEach(function ($amount, $rule_id) {
                            $rules += wdp_reporter.template_manager.get_template('cart_adj_history_chunk', {
                                'rule_id': $rule_id,
                                'amount': wdp_reporter.format_price($amount)
                            });
                        });
                    }
                    else if(coupons.adp.single.hasOwnProperty($name)) {
                        $coupon = coupons.adp.single[$name];
                        $rules += wdp_reporter.template_manager.get_template('cart_adj_history_chunk', {
                            'rule_id': $coupon.ruleId,
                            'amount': wdp_reporter.format_price($coupon.amount)
                        });
                        $amount = $coupon.amount;
                    }

                    // jQuery.each(data.rules, function ($rule_id, $amount) {
                    //     $rules += wdp_reporter.template_manager.get_template('cart_adj_history_chunk', {
                    //         'rule_id': $rule_id,
                    //         'amount': wdp_reporter.format_price($amount)
                    //     });
                    // });

                    $cart_coupons_tab_content += wdp_reporter.template_manager.get_template('cart_coupon', {
                        'index': $index+1,
                        'coupon_code': $name,
                        'coupon_amount': $amount,
                        'affected_rules': $rules,
                    });
                });

                return wdp_reporter.template_manager.get_template('cart_coupons', {
                    'coupons': $cart_coupons_tab_content
                });
            },

            get_coupons_html_merged: function (coupons) {
              let cartCouponsTabContent = '';

              jQuery.each(coupons['adp_merged_coupons'], function (index, data) {
                let parts = '';

                jQuery.each(data.parts, function(index, couponPart) {
                  let totalByItemsHtml = "";

                  jQuery.each(couponPart.dist, function(index, distItem) {
                    totalByItemsHtml += wdp_reporter.template_manager.get_template('cart_merged_coupon_dist_item_chunk', {
                      'name': distItem.name,
                      'qty': distItem.qty,
                      'amount': distItem.amount,
                    });
                  });

                  parts += wdp_reporter.template_manager.get_template('cart_merged_coupon_chunk', {
                    'rule_id': couponPart.rule_id,
                    'type_title': couponPart.type_title,
                    'total_by_items_html': totalByItemsHtml,
                  });
                });

                cartCouponsTabContent += wdp_reporter.template_manager.get_template('cart_coupon', {
                  'index': index+1,
                  'coupon_code': data['code'],
                  'coupon_amount': data['amount_for_display'],
                  'affected_rules': parts,
                });
              })

              return wdp_reporter.template_manager.get_template('cart_coupons', {
                'coupons': cartCouponsTabContent
              });
            },

            get_selector_html: function (selected, index) {
                return wdp_reporter.template_manager.get_template('tab_link', {
                    'selected': selected ? 'selected' : '',
                    'tab_key': this.key + "_" + index,
                    'tab_label': this.label
                });
            },

            get_content_html: function (data, index) {
                return wdp_reporter.template_manager.get_template('tab', {
                    'tab_key': this.key + "_" + index,

                    'active': '',

                    'sub_tabs_selector_html': '',
                    'sub_tabs_selector_class': 'hide',

                    'tab_content_html': this.get_coupons_html(data.coupons),
                });
            },
        },

        tab_fees: {
            key: 'fees',
            label: user_report_data.i.fees,

            is_show: function (data) {
                return Object.keys(data.fees).length > 0;
            },

            get_fees_html: function (fees) {
                var $cart_fees_tab_content = '';
                var $index = 1;

                jQuery.each(fees.applied, function ($id, $fee) {
                    var $rules = '';
                    var $name = $fee.name;

                    jQuery.each(fees.adp, function ($index, $adp_fee) {
                        if($adp_fee.name == $name) {
                            $rules += wdp_reporter.template_manager.get_template('cart_adj_history_chunk', {
                                'rule_id': $adp_fee.ruleId,
                                'amount': $adp_fee.amount
                            });
                        }
                    });

                    $cart_fees_tab_content += wdp_reporter.template_manager.get_template('cart_fee', {
                        'index': $index++,
                        'fee_id': $id,
                        'fee_name': $fee.name,
                        'fee_amount': $fee.amount,
                        'affected_rules': $rules,
                    });
                });

                return wdp_reporter.template_manager.get_template('cart_fees', {
                    'fees': $cart_fees_tab_content
                });
            },

            get_selector_html: function (selected, index) {
                return wdp_reporter.template_manager.get_template('tab_link', {
                    'selected': selected ? 'selected' : '',
                    'tab_key': this.key + "_" + index,
                    'tab_label': this.label
                });
            },

            get_content_html: function (data, index) {
                return wdp_reporter.template_manager.get_template('tab', {
                    'tab_key': this.key + "_" + index,

                    'active': '',

                    'sub_tabs_selector_html': '',
                    'sub_tabs_selector_class': 'hide',

                    'tab_content_html': this.get_fees_html(data.fees),
                });
            },
        },

        tab_shipping: {
            key: 'shipping',
            label: user_report_data.i.shipping,

            is_show: function (data) {
                // var at_least_one_rate_exists = false;
                // for (var package_title in data.shipping.packages) {
                //     if ( data.shipping.packages.hasOwnProperty(package_title) ) {
                //         at_least_one_rate_exists = Object.keys(data.shipping.packages[package_title]).length > 0;

                //         if ( at_least_one_rate_exists ) {
                //             break;
                //         }
                //     }
                // }

                return data.shipping.packages.length > 0;
            },

            get_shipping_rates_html: function (shipping) {
                var $shipping_packages_html = '';
                var _this = this;

                jQuery.each(shipping.packages, function (index, package) {
                    $shipping_packages_html += _this.get_single_package_html(package, shipping.methods, index);
                });

                return wdp_reporter.template_manager.get_template('cart_shipping', {
                    'shipping_packages': $shipping_packages_html
                });
            },

            get_single_package_html: function(package, shipping_rates, index) {
                var $shipping_rates_html = '';
                var $index = 1;

                jQuery.each(shipping_rates, function (instance_id, data) {
                    var $rules = '';

                    if ( data.is_adp_free ) {
                        var $rule_id = data.rules[0].ruleId;
                        $rules += wdp_reporter.template_manager.get_template('free_wdp_shipping_rate', {
                            'rule_id': $rule_id,
                        });
                    } else {
                        var $original_price = data.original_cost;
                        jQuery.each(data.rules, function ($index, $rule) {
                            $rules += wdp_reporter.template_manager.get_template('history_chunk',
                                {
                                    'rule_id': $rule.ruleId,
                                    'old_price': wdp_reporter.format_price($original_price),
                                    'amount': wdp_reporter.format_difference(-$rule.amount),
                                    'new_price': wdp_reporter.format_price($original_price - $rule.amount),
                                    'is_replaced': '',
                                }
                            );

                            $original_price -= $rule.amount;
                        });
                    }

                    $shipping_rates_html += wdp_reporter.template_manager.get_template('shipping_rate', {
                        'index': $index++,
                        'instance_id': instance_id,
                        'label': data.label,
                        'initial_cost': data.original_cost,
                        'cost': data.cost,
                        'affected_rules': $rules,
                    });
                });

                return wdp_reporter.template_manager.get_template('cart_shipping_package', {
                    'package_title': "Package " + (index + 1),
                    'shipping_rates': $shipping_rates_html
                });
            },

            get_selector_html: function (selected, index) {
                return wdp_reporter.template_manager.get_template('tab_link', {
                    'selected': selected ? 'selected' : '',
                    'tab_key': this.key + "_" + index,
                    'tab_label': this.label
                });
            },

            get_content_html: function (data, index) {
                return wdp_reporter.template_manager.get_template('tab', {
                    'tab_key': this.key + "_" + index,

                    'active': '',

                    'sub_tabs_selector_html': '',
                    'sub_tabs_selector_class': 'hide',

                    'tab_content_html': this.get_shipping_rates_html(data.shipping),
                });
            },
        },

        fill: function (data) {
            var $tab_content_html = '';
            var $process_selector_html = '';

            var $tab_cart_sub_tabs = [this.tab_items, this.tab_coupons, this.tab_fees, this.tab_shipping];
            var $tab_process = this.tab_process;

            var $sub_tabs_selector_html;

            data.forEach(function (process, index) {
                var $process_tab_content_html = '';
                $sub_tabs_selector_html = '';
                var $all_tabs_empty = true;
                $tab_cart_sub_tabs.forEach(function (sub_tab) {
                    if (sub_tab.is_show(process.processFinished)) {
                        $all_tabs_empty = false;

                        var selected = !$sub_tabs_selector_html;
                        $sub_tabs_selector_html += sub_tab.get_selector_html(selected, index);
                        $process_tab_content_html += sub_tab.get_content_html(process.processFinished, index);
                    }
                });
                if ($all_tabs_empty) {
                    $process_tab_content_html = wdp_reporter.template_manager.get_template('cart_is_empty', {});
                }

                $process_selector_html += $tab_process.get_selector_html(index);
                $tab_content_html += $tab_process.get_content_html($process_tab_content_html, $sub_tabs_selector_html, index);
            });

            if(!$tab_content_html) {
                $tab_content_html = wdp_reporter.template_manager.get_template('cart_is_empty', {});
            }

            var $tab_cart_content = wdp_reporter.template_manager.get_template('tab', {
                'tab_key': 'cart',

                'active': 'active',

                'sub_tabs_selector_html': $process_selector_html,
                'sub_tabs_selector_class': '',

                'tab_content_html': $tab_content_html,
            });

            jQuery('#wdp-report-tab-window').append($tab_cart_content);

        }

    },

    tab_get_report: {
        key: 'reports',
        label: user_report_data.i.get_system_report,

        fill: function() {
            var $tab_content_html = wdp_reporter.template_manager.get_template('export_buttons', {
                'import_key': wdp_reporter.import_key,
            });

            var $tab_reports_content = wdp_reporter.template_manager.get_template('tab', {
                'tab_key': this.key,

                'active': '',

                'sub_tabs_selector_html': '',
                'sub_tabs_selector_class': '',

                'tab_content_html': $tab_content_html,
            });

            jQuery('#wdp-report-tab-window').append($tab_reports_content);
            this.set_button_handlers();
        },

        set_button_handlers: function () {
            jQuery('#wdp-report-tab-window #export_all').on('click', function (event) {
                var src = wdp_reporter.ajaxurl + (wdp_reporter.ajaxurl.indexOf('?') === -1 ? '?' : '&') + 'action=download_report&import_key=' + wdp_reporter.import_key + '&reports=all';
                src += "&" + user_report_data.security_param + "=" + user_report_data.security;
                jQuery('#wdp_export_new_window_frame').attr("src", src);
            });
        },
    },

    init: function () {
        wdp_reporter.container = jQuery('#wdp-report-window');

        /** Resize handle */
        var maxheight = (jQuery(window).height() - wdp_reporter.container.outerHeight());
        var startY, startX, resizerHeight;

        jQuery(document).on('mousedown', '#wdp-report-resizer', function (event) {
            resizerHeight = jQuery(this).outerHeight() - 1;
            startY = wdp_reporter.container.outerHeight() + event.clientY;
            startX = wdp_reporter.container.outerWidth() + event.clientX;

            jQuery(document).on('mousemove', do_resizer_drag);
            jQuery(document).on('mouseup', stop_resizer_drag);
        });

        function do_resizer_drag(event) {
            var h = (startY - event.clientY);
            if (h >= resizerHeight && h <= maxheight) {
                wdp_reporter.container.height(h);
            }
        }

        function stop_resizer_drag(event) {
            jQuery(document).off('mousemove', do_resizer_drag);
            jQuery(document).off('mouseup', stop_resizer_drag);
        }

        /** Close handle */
        wdp_reporter.container.on('click', '#wdp-report-window-close .dashicons', function (event) {
            wdp_reporter.container.hide();
        });

        /** Open handle */
        jQuery('#wp-toolbar').find('.wdp-report-visibility-control').click(function (e) {
            wdp_reporter.container.show();
        });
    },

};


jQuery(document).ready(function ($) {
    jQuery('#wdp-report-main-window .tab-content:first').addClass('active');

    rule_tooltip.init();
    wdp_reporter.init();
    wdp_reporter.update();

    var params = window.location.search;
    if(params !== "" && params.indexOf('wdp_debug_kill=1') > 0) {
        //jQuery('#export_all').trigger('click');
        var src = wdp_reporter.ajaxurl + (wdp_reporter.ajaxurl.indexOf('?') === -1 ? '?' : '&') + 'action=download_report&import_key=' + wdp_reporter.import_key + '&reports=all';
        src += "&" + user_report_data.security_param + "=" + user_report_data.security;
        jQuery('#wdp_export_new_window_frame').attr("src", src);

        setTimeout('window.close()',5000);
    }

    jQuery(document).on('click', '#wdp-report-window .tab-links-list .tab-link', function (e) {
        var $tab_key = jQuery(this).data('tab-id');

        jQuery(this).siblings('.selected').removeClass('selected');
        jQuery(this).addClass('selected');

        jQuery('#wdp-report-' + $tab_key + '-tab').siblings('.active').removeClass('active');
        jQuery('#wdp-report-' + $tab_key + '-tab').addClass('active');
    });

    jQuery(document).on('click', '#wdp-report-window-refresh button', function (e) {
         wdp_reporter.update();
         $('#wdp-report-main-tab-selector').children().removeClass('selected');
         $('#wdp-report-main-tab-selector').find('[data-tab-id=cart]').addClass('selected');
    });

});
