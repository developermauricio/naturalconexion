/* global jQuery, wpc_postboxes, ajaxurl, wdp_data */
jQuery(document).ready(function ($) {

    var bulk_adjustment = (function () {
        var $available_types = wdp_data.bulk_rule;
        var $persistence_available_types = wdp_data.persistence_bulk_rule;
        var $rule = null;

        var init_events = function ($container, $rule, blocks) {
            $rule.find('.wdp_bulk_adjustment_remove').click(function () {
                destroy($container, $rule, blocks);
            });
            $container.find('.bulk-adjustment-type').on('change', function () {
                update_selectors($container, $rule);
            });
            $container.find('.bulk-qty_based-type').on('change', function () {
                update_selectors($container, $rule);
            });
            make_select2_products($container.find('[data-field="autocomplete"]'));
        };

        var destroy = function ($container, $rule, blocks) {
            $rule.find('.wdp-btn-add-bulk').show();
            blocks.setBulkDiscountsOpen(false)
            blocks.updateView()
            $container.hide();
            flushInputs($container);
            $container.find('.wdp-range').remove();
            $container.find('.wdp-ranges-empty').show();

            // Unconditionally hide all sortable handlers
            $rule.find(".wdp-drag-handle").hide();
            $rule.find(".sortable-apply-mode-block").hide();
            // Hide label with checkbox in role discount
            $rule.find('.dont-apply-bulk-if-roles-matched-check').hide();
        };

        var update_selectors = function ($container, $rule) {
            let persistence = $container.closest('.postbox').find(".rule-type select").val() === "persistent";

            var $adj_type = $container.find('.bulk-adjustment-type').val();
            var $qty_based = $container.find('.bulk-qty_based-type').val();
            var $discount_type = $container.find('.bulk-discount-type').val();

            var $available_qty_based;
            if ( persistence ) {
              $available_qty_based = get_available_qty_based_persistence_types($adj_type);
              if (!check_qty_based_persistence_availability($adj_type, $qty_based)) {
                $qty_based = Object.keys(get_available_qty_based_persistence_types($adj_type))[0];
              }
            } else {
              $available_qty_based = get_available_qty_based_types($adj_type);
              if (!check_qty_based_availability($adj_type, $qty_based)) {
                $qty_based = Object.keys(get_available_qty_based_types($adj_type))[0];
              }
            }
            $container.find('.bulk-qty_based-type').html("");
            $.each($available_qty_based, function ($key, $item) {
                $container.find('.bulk-qty_based-type').append(make_option($key, $item.label))
            });

            var $available_discount_types
            if ( persistence ) {
              var $available_discount_types = get_available_discount_persistence_types($adj_type, $qty_based);
              if (!check_discount_type_persistence_availability($adj_type, $qty_based, $discount_type)) {
                $discount_type = Object.keys(get_available_discount_persistence_types($adj_type, $qty_based))[0];
              }
            } else {
              var $available_discount_types = get_available_discount_types($adj_type, $qty_based);
              if (!check_discount_type_availability($adj_type, $qty_based, $discount_type)) {
                $discount_type = Object.keys(get_available_discount_types($adj_type, $qty_based))[0];
              }
            }
            $container.find('.bulk-discount-type').html("");
            $.each($available_discount_types, function ($key, $label) {
                $container.find('.bulk-discount-type').append(make_option($key, $label))
            });

            $container.find('.bulk-qty_based-type').val($qty_based);
            $container.find('.bulk-discount-type').val($discount_type);

            if ($qty_based === 'product_selected_categories') {
                $container.find('.bulk-selected_categories-type').show();
            } else {
                $container.find('.bulk-selected_categories-type').hide();
            }

            if ($qty_based === 'selected_products') {
                $container.find('.bulk-selected_products-type').show();
            } else {
                $container.find('.bulk-selected_products-type').hide();
            }
        };

        var make_option = function ($value, $label, $classes) {
            if (typeof $classes === 'undefined') {
                $classes = [];
            }

            var option = $("<option></option>");

            option.val($value).text($label);

            $classes.forEach(function ($class) {
                option.addClass($class);
            });

            return option;
        };

        var get_available_qty_based_types = function ($adj_type) {
            return $available_types[$adj_type]
        };

        var check_qty_based_availability = function ($adj_type, $qty_based) {
            return typeof $available_types[$adj_type][$qty_based] !== 'undefined';
        };

        var get_available_discount_types = function ($adj_type, $qty_based) {
            return $available_types[$adj_type][$qty_based].items;
        };

        var check_discount_type_availability = function ($adj_type, $qty_based, $discount_type) {
            return typeof $available_types[$adj_type][$qty_based].items[$discount_type] !== 'undefined';
        };

      var get_available_qty_based_persistence_types = function ($adj_type) {
        return $persistence_available_types[$adj_type]
      };

      var check_qty_based_persistence_availability = function ($adj_type, $qty_based) {
        return typeof $persistence_available_types[$adj_type][$qty_based] !== 'undefined';
      };

      var get_available_discount_persistence_types = function ($adj_type, $qty_based) {
        return $persistence_available_types[$adj_type][$qty_based].items;
      };

      var check_discount_type_persistence_availability = function ($adj_type, $qty_based, $discount_type) {
        return typeof $persistence_available_types[$adj_type][$qty_based].items[$discount_type] !== 'undefined';
      };

        return {
            // init: function ($available_types) {
            //     this.$available_types = $available_types;
            // },

            add: function ($container, $data, blocks) {
                $rule = $container.closest('.postbox');

                if ( ! blocks ) {
                  blocks = new RuleBlocks($container.closest('.postbox'))
                  blocks.setBulkDiscountsOpen(true)
                }
                blocks.updateView()
                $rule.find('.wdp-btn-add-bulk').hide();

                // selector categories
                $container.find('.bulk-selected_categories-type').hide();
                $container.find('.bulk-selected_products-type').hide();

                $rule.find('.bulk-adjustment-type').find('option:first-child').prop("selected", "selected");
                $rule.find('.bulk-qty_based-type').find('option:first-child').prop("selected", "selected");
                $rule.find('.bulk-discount-type').find('option:first-child').prop("selected", "selected");

                init_events($container, $rule, blocks);

                update_selectors($container, $rule);

                if ($data) {
                    $container.find('.bulk-adjustment-type').val($data.type);

                    if ($data.discount_type) {
                        $container.find('.bulk-discount-type').val($data.discount_type);
                    }

                    if ($data.qty_based) {
                        $container.find('.bulk-qty_based-type').val($data.qty_based);
                    }

                    if ($data.selected_categories) {
                        var html = '';
                        $.each($data.selected_categories, function (i, id) {
                            var title = wdp_data.titles['product_categories'] && wdp_data.titles['product_categories'][id] ? wdp_data.titles['product_categories'][id] : id;
                            html += '<option selected value="' + id + '">' + title + '</option>';
                        });
                        $container.find('.bulk-selected_categories-type select').html(html);
                    }

                    if ($data.selected_products) {
                        var html = '';
                        $.each($data.selected_products, function (i, id) {
                            var title = wdp_data.titles['products'] && wdp_data.titles['products'][id] ? wdp_data.titles['products'][id] : id;
                            html += '<option selected value="' + id + '">' + title + '</option>';
                        });
                        $container.find('.bulk-selected_products-type select').html(html);
                    }

                    if ($data.ranges) {
                        var $range_button = $container.find('.add-range');
                        $.each($data.ranges, function (index, item) {
                            add_range($range_button, item);
                        });
                    }

                    if ($data.table_message) {
                        $container.find('.bulk-table-message').val($data.table_message);
                    }
                }

                update_selectors($container, $rule);
            }

        }

    });



    // make rule blocks collapsable and sortable
    wpc_postboxes.add_postbox_toggles( $('#rules-container') );

    // update priority field on reorder
    wpc_postboxes._on_reorder = function() {
        // make aray of objects [ { id, priority }, ... ]
        var data = $('#rules-container .postbox').map(function(i, el) {
            $('.rule-priority', el).val( i );
            return {
                id: $('.rule-id', el).val(),
                priority: wdp_data.paged && wdp_data.options.rules_per_page ? (wdp_data.paged - 1) * wdp_data.options.rules_per_page + i : i,
            }
        }).toArray();

        let new_params = {
            action: 'wdp_ajax',
            method: 'reorder_rules',
            items: data
        };
        new_params[wdp_data.security_query_arg] = wdp_data.security;

        $.post(
            ajaxurl,
            new_params,
            $.noop,
            'json'
        );
    };

    function finishLoadRule(new_rule, data) {
        if ( ! new_rule.hasClass('not-initialized') ) {
            return false;
        }
        if ( ! data ) {
            wdp_data.rules.forEach(function (rule_data) {
                if (rule_data.id.toString() === new_rule.find('.rule-id').val()) {
                    data = rule_data;
                    return false;
                }

                return true;
            });
        }

        addEventHandlersToRule(new_rule);

        if (data) {
            setRuleData(new_rule, data);
        } else {
          flushInputs(new_rule.find('.wdp-product-adjustments'));
        }

        let filters_count = new_rule.find('.wdp_product_filter .wdp-filter-item').length;
        let persistence = new_rule.closest('.postbox').find(".rule-type select").val() === "persistent";
        if (filters_count === 0 && persistence) {
          add_product_filter(new_rule.find('.wdp-filter-block'));
        }

        new_rule.removeClass('not-initialized')
    }

	wpc_postboxes._on_expand = function (new_rule, data) {
		new_rule.find('.rule-trigger-coupon-code input').prop('readonly', false);
		new_rule.find('.rule-date-from-to input[name="rule[additional][date_from]"]').datepicker( "option", "disabled", false ).css("background-color", "white");
		new_rule.find('.rule-date-from-to input[name="rule[additional][date_to]"]').datepicker( "option", "disabled", false ).css("background-color", "white");
		finishLoadRule(new_rule, data);
	};

	wpc_postboxes._on_close = function (new_rule, data) {
		new_rule.find('.rule-trigger-coupon-code input').prop('readonly', true);
    new_rule.find('.rule-date-from-to input[name="rule[additional][date_from]"]').datepicker( "option", "disabled", true ).css("background-color", "#f0f0f1");
    new_rule.find('.rule-date-from-to input[name="rule[additional][date_to]"]').datepicker( "option", "disabled", true ).css("background-color", "#f0f0f1");
	};

    // load saved rules
    if (wdp_data.rules) {
	    var promises = [];
	    wdp_data.rules.forEach( function ( data ) {
		    promises.push( add_rule( data ) );
	    } );
	    Promise.all( promises ).then( function ( responses ) {
		    $( "#rules-container" ).removeClass( "loading" );
		    $( '#no-rules' ).removeClass( "loading" );
		    $( '.add-rule' ).removeClass( "loading" );
		    $( "#progress_div" ).hide();
	    } ).catch( function ( reason ) {
		    console.log( reason );
	    } );

        if ( wdp_data.selected_rule && wdp_data.selected_rule > 0 ) {
            $('#rules-container .rule-id[value="' + wdp_data.selected_rule + '"]').siblings(".hndle").trigger("click");
        }

        if ( wdp_data.action_rules && wdp_data.action_rules == 'add' ) {
            var new_rule;
            if(wdp_data.product_title && wdp_data.product_title != -1) {
                var rule_data = {title: wdp_data.product_title, exclusive: 0, enabled: "on", additional: {disabled_by_plugin: 0}};
                new_rule = add_rule(rule_data);
                new_rule.removeClass('closed');
                new_rule.addClass('dirty');
            }
            else {
                new_rule = add_rule();
            }
            finishLoadRule(new_rule);
            if(wdp_data.product && wdp_data.product > 0) {

                var filter_data = {qty: "1", type: "products", method: "in_list", value: {0: wdp_data.product}};
                add_product_filter(new_rule.find('.wdp-filter-block'), filter_data);
            }

        }
    }

    // create new rule when click 'Add rule' button
    $('.add-rule').click(function (e) {
        e.preventDefault();
        // $('.wdp-count-all-rules').text(Number($('.wdp-count-all-rules').text()) + 1);
        // $('.wdp-count-active-rules').text(Number($('.wdp-count-active-rules').text()) + 1);
        var new_rule = add_rule();
        new_rule.find('.rule-trigger-coupon-code input').prop('readonly', false);
        new_rule.find('.rule-date-from-to input[name="rule[additional][date_from]"]').datepicker( "option", "disabled", false ).css("background-color", "white");
        new_rule.find('.rule-date-from-to input[name="rule[additional][date_to]"]').datepicker( "option", "disabled", false ).css("background-color", "white");
        finishLoadRule(new_rule);
        new_rule.find('.wdp-discount-type-selector').show();
        new_rule.find('.wdp-title').focus();
      $("html, body").animate({ scrollTop: $(document).height() }, "slow"); //scroll to bottom
    });

    /* Template functions */

    function add_rule(data) {
        window.wdpPreloadRule = true;
        let ruleType = 'common';
        if (data) {
          if (data.exclusive === "1" || data.exclusive === true) {
            ruleType = 'exclusive';
          } else {
            ruleType = data.rule_type || data.type;
          }
          if (data.rule_type !== ruleType) {
            data.rule_type = ruleType;
          }
        }
        var template_options = {
            c: 0,
            p: (data && data.priority) ? data.priority : get_last_priority(),
            type: (data && data.type) ? data.type : 'package',
            rule_type: ruleType,
        };

        // prepare template
        var rule_template = get_template(template_options.rule_type + '_rule', template_options);
        if (rule_template === '') {
	    rule_template = get_template('rule', template_options);
	}

        var new_rule = $(rule_template);
        if (data && data.rule_type === 'persistent') {
          new_rule.find('.rule-type select').attr('disabled', 'disabled');
        }
        preAddEventHandlersToRule(new_rule, data);

        // add new rule to rules list
        $('#rules-container').append(new_rule);

        preSetRuleData(new_rule, data);
        $('#no-rules').hide();
        window.wdpPreloadRule = false;
        if (!data) {
          new_rule.closest('.postbox').find('.rule-type select').val('common');
        }
        set_type_label_color(new_rule);

        return new_rule;
    }

    function blockRuleTypeSelector($rule) {
      let $ruleTypeSelect = $rule.find('.rule-type select');
      let rule_type = $ruleTypeSelect.val();
      if (rule_type === 'persistent') {
        $ruleTypeSelect.attr('disabled', 'disabled');
      }
    }

    function saveRuleEvt($rule) {
      var $form = $rule.closest('form');

      $form.attr('disabled', true);
      var beforeSendValidation = true;
      var $filtersByProduct = $form.find('.wdp-product-filter-container .wdp-filter-item');
      var $cartConditions = $form.find('.wdp-conditions-container');
      var $adjRanges = $form.find('.wdp-adjustment-ranges');
      if ($cartConditions.length) {
        $cartConditions.each(function () {
          let $condition_block = $( this ).find('.wdp-condition');
          if ( $condition_block.length ) {
            let $searchField = $condition_block.find( '.select2-search__field' );
            if ( ! $searchField.attr('disabled') ) {
              let $selectionRender = $condition_block.find( '.select2-selection__rendered' );
              let $inputFields = $condition_block.find( 'input' );
              let $textAreas = $condition_block.find( 'textarea' );
              let $qtyInputs = $condition_block.find( '.wdp-condition-field-qty' );
              let elements = [];
              let diff = [];
              for (let idx = 0; idx < $qtyInputs.length; idx++) {
                if (idx % 2 === 0) {
                  diff.push($qtyInputs[idx]);
                }
              }
              let attachErrorTo = [];
              if ($selectionRender.length) {
                $selectionRender.each(function() {
                  elements.push($(this));
                });
              }
              if ($inputFields.length) {
                $inputFields.each(function () {
                  let $parent = $(this).parent();
                  let parentClasses = $parent.attr('class');
                  if ($(this).attr('type') !== 'checkbox'
                    && ! parentClasses.includes('select2')
                    && ! parentClasses.includes('qty')
                  ) {
                    elements.push($(this));
                  }
                });
              }
              if ($textAreas.length) {
                elements = elements.concat($textAreas);
              }
              elements.filter(x => diff.includes(x));
              elements.forEach(function (el) {
                if (el[0].localName !== 'input' && el[0].localName !== 'textarea') {
                  if (! el[0].textContent.length || el[0].textContent === 'Select value') {
                    beforeSendValidation = false;
                    attachErrorTo.push(el);
                  }
                } else if (! el.val().length) {
                  beforeSendValidation = false;
                  attachErrorTo.push(el);
                }
              });
              attachErrorTo.forEach(function (val) {
                if (!beforeSendValidation && !val.next('.cart-conditions__error-wrapper').length) {
                  let $elEmptyValue = $("<div class=\"cart-conditions__error-wrapper\"><span class=\"cart-conditions__onempty-error\"></span></div>");
                  val.after($elEmptyValue);
                  $('.cart-conditions__onempty-error').text('You must provide at least one value');

                  setTimeout(function () {
                    $form.find('.inside .cart-conditions__error-wrapper').remove();
                  }, 5000);
                }
              });
            }
          }
        });
      }
      if ($adjRanges.length) {
        let adjustments = [];
        $adjRanges.each(function() {
          let rows = $(this).find('.wdp-range');
          if (rows.length) {
            rows.each(function() {
              adjustments.push({
                from: $(this).find('.adjustment-from')[0].valueAsNumber,
                to: $(this).find('.adjustment-to')[0].valueAsNumber
              })
            })
          }
        });
        for (let i = adjustments.length - 1; i >= 0; i--) {
          let rowValid = isNaN(adjustments[i].to) ? !isNaN(adjustments[i].from) : adjustments[i].from <= adjustments[i].to;
          let less = false;
          let include = false;
          if (i - 1 >= 0) {
            less = adjustments[i].from < adjustments[i-1].from;
            if (!less && !isNaN(adjustments[i].to) && !isNaN(adjustments[i-1].to)) {
                less = adjustments[i].to < adjustments[i-1].to;
            }
            if (!isNaN(adjustments[i-1].to)) {
                include = adjustments[i].from < adjustments[i-1].to;
            }
          }
          if (!rowValid || less || include) {
            beforeSendValidation = false;
            break;
          }
        }
        if (!beforeSendValidation) {
          let $attachTo = $($adjRanges[0].children[0]);
          if (!$attachTo.next('.wdp-adjustment-ranges__error-wrapper').length) {
            let $rangeMissValue = $("<div class=\"wdp-adjustment-ranges__error-wrapper\"><span class=\"wdp-adjustment-ranges__onmiss-error\"></span></div>");
            $attachTo.after($rangeMissValue);
            $('.wdp-adjustment-ranges__onmiss-error').text(`The ranges sequence must be ascending`);

            setTimeout(function () {
              $form.find('.inside .wdp-adjustment-ranges__error-wrapper').remove();
            }, 5000);
          }
        }
      }
      let currentRangeError        = "";
      let innerErrorContainerClass = "";

      if ( $form.closest('.postbox').find(".rule-type select").val() === "persistent" && $filtersByProduct.length !== 1 ) {
        currentRangeError   = "You must add product filter";
        innerErrorContainerClass = "products-filter__range-error";

        if ( currentRangeError !== "" ) {
          beforeSendValidation = false;
          if ( ! $form.find('.inside').children('.products-filter__error-wrapper').length ) {
            let $wrongRange = $( "<div class=\"products-filter__error-wrapper\"><span class=\"" + innerErrorContainerClass + "\"></span></div>");
            $form.find('.inside').prepend( $wrongRange );
            $( "." + innerErrorContainerClass ).text( currentRangeError );

            setTimeout(function() {
              $form.find('.inside .products-filter__error-wrapper').remove();
            }, 5000);
          }
        }
      }

      if ( $filtersByProduct.length ) {
        $filtersByProduct.each( function( index ) {
          //Check if values in filters are present
          let $condition_block = $( this ).find('.wdp-column.wdp-condition-field-value');
          if ( $condition_block.length ) {
            let $selectedOptions = $condition_block.find( '.select2-selection__choice' );
            if ( ! $selectedOptions.length ) {
              beforeSendValidation = false;
              let $attachErrorTo = $( this ).find( '.select2.select2-container' ).first();
              if ( ! $attachErrorTo.next('.products-filter__error-wrapper').length ) {
                let $elEmptyValue  = $( "<div class=\"products-filter__error-wrapper\"><span class=\"products-filter__onempty-error\"></span></div>");
                $attachErrorTo.after( $elEmptyValue );
                $('.products-filter__onempty-error').text('You must select at least one value');
              }
            }
          }
          //Check if values in range are correct
          let $rangeStart              = $( this ).find( '.wdp-condition-field-qty');
          let $rangeEnd                = $( this ).find( '.wdp-condition-field-qty-end' );
          let rangeStartValue          = $.trim( $rangeStart.children().val() );
          let rangeEndValue            = "";
          let rangeEndEmptiness        = "";
          currentRangeError        = "";
          innerErrorContainerClass = "";
          if ( $rangeEnd.length ) {
            rangeEndValue     = $.trim( $rangeEnd.children().val() )
            rangeEndEmptiness = rangeEndValue === "";
          } else {
            rangeEndEmptiness = false;
          }

          if ( !( rangeStartValue === "" ) && ! rangeEndEmptiness ) {
            let expr         = /^\d+(\.\d{1,2})?$/;
            let startRegResult = expr.exec( rangeStartValue );
            let endRegResult   = true;

            if ( $rangeEnd.length ) {
              endRegResult = expr.exec( rangeEndValue );
            }

            if ( startRegResult != null && endRegResult != null ) {
              if (  ( endRegResult !== true ) && ( parseInt( rangeStartValue ) > parseInt( rangeEndValue ) ) ) {
                currentRangeError   = "Second value in the range must be larger than the first one";
                innerErrorContainerClass = "products-filter__range-error";
              }
            } else {
              currentRangeError    = "Wrong number format.";
              innerErrorContainerClass  = "products-filter__input-error"
            }

          } else {
            currentRangeError    = "Inputs cannot be empty";
            innerErrorContainerClass  = "products-filter__emptiness-error"
          }

          if ( currentRangeError !== "" ) {
            beforeSendValidation = false;
            if ( ! $( this ).find( '.two-on-two-column.left-column' ).children('.products-filter__error-wrapper').length ) {
              let $wrongRange = $( "<div class=\"products-filter__error-wrapper\"><span class=\"" + innerErrorContainerClass + "\"></span></div>");
              $( this ).find( '.two-on-two-column.left-column' ).append( $wrongRange );
              $( "." + innerErrorContainerClass ).text( currentRangeError );
            }
          }

        } );
      }
      let filtersByFreeProduct = $form.find('.wdp-get-products-block .wdp-filter-item');
      if ( filtersByFreeProduct.length ) {
        filtersByFreeProduct.each(function (index) {
          //Check if values in filters are present
          let $condition_block = $(this).find('.wdp-column.wdp-condition-field-value');
          if ($condition_block.length) {
            let $selectedOptions = $condition_block.find('.select2-selection__choice');
            if (!$selectedOptions.length && !$condition_block.find(".select2-hidden-accessible").prop('disabled')) {
              beforeSendValidation = false;
              let $attachErrorTo = $(this).find('.select2.select2-container').first();
              if (!$attachErrorTo.next('.products-filter__error-wrapper').length) {
                let $elEmptyValue = $("<div class=\"products-filter__error-wrapper\"><span class=\"products-filter__onempty-error\"></span></div>");
                $attachErrorTo.after($elEmptyValue);
                $('.products-filter__onempty-error').text('You must select at least one value');
              }
            }
          }
        });
      }

      setTimeout( function() {
        //remove errors
        var $all_filters = $('.wdp-product-filter-container .wdp-row.wdp-filter-item');
        $.each( $all_filters, function( key, value ) {
          let $selectValuesField = $( value ).find( '.wdp-column.wdp-condition-field-value' );
          remove_user_input_errors( $selectValuesField, '.products-filter__error-wrapper' );
          let searchIn = $( value ).find( '.two-on-two-column.left-column' );
          remove_user_input_errors( searchIn, '.products-filter__error-wrapper' );
        } );

        $.each( filtersByFreeProduct, function( key, value ) {
          let $selectValuesField = $( value ).find( '.wdp-column.wdp-condition-field-value' );
          remove_user_input_errors( $selectValuesField, '.products-filter__error-wrapper' );
          let searchIn = $( value ).find( '.two-on-two-column.left-column' );
          remove_user_input_errors( searchIn, '.products-filter__error-wrapper' );
        } );
      }, 5000 );
      if ( beforeSendValidation ) {
        let new_params = $form.serialize();
        new_params += "&" + wdp_data.security_query_arg + "=" + wdp_data.security;

        $.post(
          ajaxurl,
          new_params,
          function (response) {
            $form.attr('disabled', false);

            var id = response.data;
            $form.find('.rule-id').val(id);

            $form.removeClass('dirty');
            $form.find( '.wdp-discount-type-selector').hide();
            $form.removeClass('disabled-by-plugin');
            $form.find('.wdp-disabled-automatically-prefix').hide();
          },
          'json'
        );
      }
      return false;
    }

    // function showAllRules(rules){
    //   rules.show(0);
    // }
    //
    // function showActiveRules(iter, rule){
    //   let toggler = $(rule).find('.wdp-field-enabled select');
    //   if(toggler.val() === 'on'){
    //     $(rule).show(0);
    //   }else {
    //     $(rule).hide(0);
    //   }
    // }
    //
    // function showInactiveRules(iter, rule){
    //   let toggler = $(rule).find('.wdp-field-enabled select');
    //   if(toggler.val() === 'off'){
    //     $(rule).show(0);
    //   }else {
    //     $(rule).hide(0);
    //   }
    // }

    function preAddEventHandlersToRule(new_rule) {
        // on change rule title
        // $('.wdp-section-rule-status-choice').click(function(){
        //   if($(this).parent().find('span.wdp-count-all-rules').length){
        //     $('form.postbox.closed').show(0);
        //   }else if($(this).parent().find('span.wdp-count-active-rules').length){
        //     $('form.postbox.closed').each(showActiveRules);
        //   }else if($(this).parent().find('span.wdp-count-inactive-rules').length){
        //     $('form.postbox.closed').each(showInactiveRules);
        //   }
        // });
        new_rule.find('.wdp-title').on('change input', function () {
            var $postbox = $(this).closest('.postbox');
            var value = $(this).val();
            if ( value.length ) {
                $postbox.find('.wdp-no-name').addClass('cross-not-visible');
            } else {
                $postbox.find('.wdp-no-name').removeClass('cross-not-visible');
            }
            $postbox.find('[data-wdp-title]').text(value);
        });

        // listeners for buttons
        new_rule.find('.wdp_remove_rule').on('click', function () {
            if (!confirm(wdp_data.labels.confirm_remove_rule)) return;

            var $rule = $(this).closest('.postbox');
            $rule.addClass('removing');
            // var $toggler = $rule.find('.wdp-field-enabled select');
            // var disabled = $toggler.val() === 'off';
            // $('.wdp-count-all-rules').text(Number($('.wdp-count-all-rules').text()) - 1);
            // if(disabled){
            //   $('.wdp-count-inactive-rules').text(Number($('.wdp-count-inactive-rules').text()) - 1);
            // }else {
            //   $('.wdp-count-active-rules').text(Number($('.wdp-count-active-rules').text()) - 1);
            // }

            var rule_id = $rule.find('.rule-id').val();
            if (!rule_id) {
                $rule[0].remove();
                return;
            }

            let new_params = {
                action: 'wdp_ajax',
                method: 'remove_rule',
                rule_id: rule_id
            };
            new_params[wdp_data.security_query_arg] = wdp_data.security;

            $.post(
                ajaxurl,
                new_params,
                function () {
                    $rule[0].remove();
                },
                'json'
            );
        });

        new_rule.find('.wdp_copy_rule').on('click', function () {
            Promise.all( [finishLoadRule(new_rule)] ).then( function ( responses ) {
                var temp = new_rule.serialize();
                temp = deparam(temp);

                var cloned_data = temp.rule;
                cloned_data.id = '';
                cloned_data.priority = get_last_priority();

                var cloned_new_rule = add_rule(cloned_data);
                finishLoadRule(cloned_new_rule, cloned_data);

                // open rule and focus on title
                cloned_new_rule.find('.hndle').trigger('click');
                cloned_new_rule.find('.wdp-title').focus();
            } ).catch( function ( reason ) {
                console.log( reason );
            } );
        });

        // on save rule
        new_rule.find('.save-rule').click(function (e) {
            e.preventDefault();
            saveRuleEvt(new_rule);
            blockRuleTypeSelector(new_rule);
        });

        // init flipswitch
        new_rule.find('[data-role="flipswitch"]').flipswitch();

        var save_rule_callback = function (new_rule) {

            update_rule_title(new_rule);

            if (!window.wdpPreloadRule) {
                Promise.all([finishLoadRule(new_rule)]).then(function (responses) {
                    saveRuleEvt(new_rule);
                }).catch(function (reason) {
                    console.log(reason);
                });
            }
        };

        new_rule.find('.wdp-field-enabled select').change(function() { //dd
	        save_rule_callback(new_rule);
          set_type_label_color(new_rule);
          // if(!new_rule.hasClass('not-initialized')){
          //   let activeRules = $('.wdp-count-active-rules');
          //   let inactiveRules = $('.wdp-count-inactive-rules');
          //   if($(this).val() === 'on'){
          //     activeRules.text(Number(activeRules.text()) + 1);
          //     inactiveRules.text(Number(inactiveRules.text()) - 1);
          //   }else {
          //     activeRules.text(Number(activeRules.text()) - 1);
          //     inactiveRules.text(Number(inactiveRules.text()) + 1);
          //   }
          // }
	});
        new_rule.find('.rule-type select').change(function(){

	    if ($(this).val() === 'exclusive') {
		new_rule.find('input[name="rule[exclusive]"]').val(1);
	    } else {
		new_rule.find('input[name="rule[exclusive]"]').val(0);
	    }

	    var value = $(this).val();

	    Promise.all( [finishLoadRule(new_rule)] ).then( function ( responses ) {
                var temp = new_rule.serialize();
                temp = deparam(temp);

                var cloned_data = temp.rule;

                var cloned_new_rule = reload_template_rule(cloned_data, new_rule, value + '_rule');
                finishLoadRule(cloned_new_rule, cloned_data);

		// open rule and focus on title
                cloned_new_rule.find('.hndle').trigger('click');
                set_type_label_color(cloned_new_rule);

		save_rule_callback(cloned_new_rule);

            } ).catch( function ( reason ) {
                console.log( reason );
            } );
        });

		new_rule.find('.rule-trigger-coupon-code input').change(function () {
			new_rule.addClass('dirty');
		});

        new_rule.find('.wdp-field-enabled').click(function (event) {
            event.preventDefault();
            return false;
        });

        // update title on some changes
        new_rule.find('.wdp-adjustments-repeat, .cart-adjustment-type').change(function () {
            update_rule_title(new_rule);
        });
        update_rule_title(new_rule);


      let jqDateFrom = new_rule.find('.rule-date-from-to input[name="rule[additional][date_from]"]');
      let jqDateTo = new_rule.find('.rule-date-from-to input[name="rule[additional][date_to]"]');

      jqDateFrom.removeClass('hasDatepicker').datepicker({
        dateFormat: "yy-mm-dd",
        beforeShow: function () {
          let maxDate = new Date(jqDateTo.val());

          jqDateFrom.datepicker("option", "maxDate", maxDate);
        },
        disabled: true,
      }).css("background-color", "#f0f0f1");

      jqDateTo.removeClass('hasDatepicker').datepicker({
        dateFormat: "yy-mm-dd",
        beforeShow: function () {
          let minDate = new Date(jqDateFrom.val());
          minDate.setDate(minDate.getDate() + 1)

          jqDateTo.datepicker("option", "minDate", minDate);
        },
        disabled: true,
      }).css("background-color", "#f0f0f1");
      }

    function set_type_label_color($rule) {
      let $select = $rule.find('.rule-type select');
      const value = $select.val();
      const color = value === 'persistent' ? '#c8f7d5a6' : value === 'common' ? '#f3f33f33' : '#d7e8ff';
      $select.css('background-color', color);
    }

    function resetAll(rule) {
      //remove filter
      let blocks = new RuleBlocks(rule);
      let $rule = $(rule);
      $rule.find('.adjustment-split').remove();
      $rule.find('.wdp-filter-item').remove();
      blocks.setProductFiltersOpen(false)
      $(rule).find('.wdp-btn-add-product-filter').show();

      //remove product discount
      $rule.find('.wdp-btn-add-product-adjustment').show();
      blocks.setProductDiscountsOpen(false)

      //remove role discount
      blocks.setRoleDiscountsOpen(false);
      $rule.find( '.wdp-role-discount' ).remove();
      $rule.find( '.wdp-btn-add-role-discount' ).show();
      $rule.find(".wdp-drag-handle").hide();
      $rule.find(".sortable-apply-mode-block").hide();

      //remove bulk
      $rule.find('.wdp-btn-add-bulk').show();
      blocks.setBulkDiscountsOpen(false);
      $rule.find('.wdp-range').remove();
      $rule.find('.wdp-ranges-empty').show();
      // Unconditionally hide all sortable handlers
      $rule.find(".wdp-drag-handle").hide();
      $rule.find(".sortable-apply-mode-block").hide();
      // Hide label with checkbox in role discount
      $rule.find('.dont-apply-bulk-if-roles-matched-check').hide();

      //remove free products
      $rule.find('.wdp-filter-item').remove();
      blocks.setFreeProductsOpen(false);
      $rule.find('.wdp-btn-add-getproduct').show();

      //remove auto add to cart
      $rule.closest('.wdp-auto-add').remove();
      $rule.find('.wdp-filter-item').remove();
      blocks.setAutoAddToCartOpen(false);
      $rule.find('.wdp-btn-add-autoadd').show();

      //remove cart ajusment
      $rule.find('.wdp-cart-adjustment').remove();
      $rule.find('.wdp-cart-adjustments .wdp-cart-adjustment').remove()
      blocks.setCartAdjustmentsOpen(false)
      $rule.find('.wdp-btn-add-cart-adjustment').show();

      //rempve conditions
      $rule.find('.wdp-condition').remove();
      $rule.find('.wdp-conditions .wdp-condition').remove();
      blocks.setConditionsOpen(false)
      $rule.find('.wdp-btn-add-condition').show();
    }

    function show_product_discount_type(rule_type_selector, new_rule){
      let linkOnExample = jQuery(rule_type_selector).parent().find('a');
      linkOnExample.attr('href', 'https://docs.algolplus.com/algol_pricing/product-discounts-help/').show();
      add_product_filter(new_rule.find('.wdp-filter-block'));
      add_product_adjustment(new_rule.find('.wdp-product-adjustments'));
    }

    function show_gifts_discount_type(rule_type_selector, new_rule){
      let linkOnExample = $(rule_type_selector).parent().find('a');
      linkOnExample.attr('href', 'https://docs.algolplus.com/algol_pricing/gifts-help/').show();
      add_get_products(new_rule.find('.wdp-get-products'));
      add_condition(new_rule.find('.wdp-btn-add-condition'));
    }

    function show_bogo_discount_type(rule_type_selector, new_rule){
      let linkOnExample = $(rule_type_selector).parent().find('a');
      linkOnExample.attr('href', 'https://docs.algolplus.com/algol_pricing/bogo-free-help/').show();
      add_product_filter(new_rule.find('.wdp-filter-block'));
      add_get_products(new_rule.find('.wdp-get-products'));
    }

    function show_pro_bogo_discount_type(rule_type_selector, new_rule){
      let linkOnExample = $(rule_type_selector).parent().find('a');
      linkOnExample.attr('href', 'https://docs.algolplus.com/algol_pricing/bogo-discount-help/').show();
      add_product_filter(new_rule.find('.wdp-filter-block'));
      add_auto_add(new_rule.find('.wdp-auto-add'));
    }

    function show_bulk_discount_type(rule_type_selector, new_rule){
      let linkOnExample = $(rule_type_selector).parent().find('a');
      linkOnExample.attr('href', 'https://docs.algolplus.com/algol_pricing/bulk-help/').show();
      add_product_filter(new_rule.find('.wdp-filter-block'));
      add_bulk_adjustment(new_rule.find('.wdp-bulk-adjustments'));
    }

    function show_role_bulk_discount_type(rule_type_selector, new_rule){
      let linkOnExample = $(rule_type_selector).parent().find('a');
      linkOnExample.attr('href', 'https://docs.algolplus.com/algol_pricing/role-bulk-help/').show();
      add_bulk_adjustment(new_rule.find('.wdp-bulk-adjustments'));
      add_condition(new_rule.find('.wdp-btn-add-condition'));  //cart_subtotal
      $('select[name="rule[conditions][0][type]"]').val('customer_role')
      prev_field_values = {};
      prev_field_values.options = {};
      prev_field_values.data_list = {};
      prev_field_values['type'] = 'prev_field_values';
      update_condition_fields($('select[name="rule[conditions][0][type]"]'), prev_field_values);
    }

    function show_role_discount_type(rule_type_selector, new_rule){
      let linkOnExample = $(rule_type_selector).parent().find('a');
      linkOnExample.attr('href', 'https://docs.algolplus.com/algol_pricing/role-discount-help/').show();
      add_role_discount(new_rule.find('.wdp-btn-add-role-discount'));
    }

    function show_cart_discount_type(rule_type_selector, new_rule){
      let linkOnExample = $(rule_type_selector).parent().find('a');
      linkOnExample.attr('href', 'https://docs.algolplus.com/algol_pricing/cart-discount-help/').show();
      add_cart_adjustment(new_rule.find('.wdp-btn-add-cart-adjustment'));
      add_condition(new_rule.find('.wdp-btn-add-condition'));
    }

    function addEventHandlersToRule(new_rule) {
        // ** Buttons in rule

        // Add condition
        new_rule.find('.add-condition, .wdp-btn-add-condition').click(function () {
            add_condition($(this));
        });

        new_rule.find('select[name="discount-type"]').change(function(){
          resetAll(new_rule);
          switch($(this).val()){
            case '0':
              (new RuleBlocks(new_rule)).updateView();
              $(this).parent().find('a').hide(0);
              break;
            case 'product_discount':
              show_product_discount_type(this, new_rule);
              break;
            case 'gifts_discount':
              show_gifts_discount_type(this, new_rule);
              break;
            case 'pro_bogo_discount':
              show_pro_bogo_discount_type(this, new_rule);
              break;
            case 'bogo_discount':
              show_bogo_discount_type(this, new_rule);
              break;
            case 'bulk_discount':
              show_bulk_discount_type(this, new_rule);
              break;
            case 'role_bulk_discount':
              show_role_bulk_discount_type(this, new_rule);
              break;
            case 'role_discount':
              show_role_discount_type(this, new_rule);
              break;
            case 'cart_discount':
              show_cart_discount_type(this, new_rule);
              break;
          }
        });

        // Add discount message
        new_rule.find('.wdp-btn-add-discount-message').click(function () {
          add_advertising(new_rule.find('.wdp-discount-messages'));
        });

        new_rule.find('.wdp-btn-add-condition-message').click(function () {
          add_condition_message(new_rule.find('.wdp-condition-message'));
        });

        // Add limit
        new_rule.find('.add-limit, .wdp-btn-add-limit').click(function () {
            add_limit($(this));
        });

        // Add range (bulk)
        new_rule.find('.add-range').click(function () {
            add_range($(this));
        });

        // Add product filter
        new_rule.find('.add-product-filter, .wdp-btn-add-product-filter').click(function () {
            add_product_filter(new_rule.find('.wdp-filter-block'));
        });

        // Add product filter for 'Add product adjustment' block
        new_rule.find('.wdp-btn-add-product-adjustment').click(function () {
            add_product_adjustment(new_rule.find('.wdp-product-adjustments'));
        });

        // Add bulk adjustment
        new_rule.find('.wdp-btn-add-bulk').click(function () {
            add_bulk_adjustment(new_rule.find('.wdp-bulk-adjustments'));

	        // Hide or show all sortable handlers depends on role discounts visibility
	        if ( new_rule.find( ".wdp-role-discounts" ).is( ":hidden" ) ) {
	            new_rule.find(".sortable-apply-mode-block").hide();
		        new_rule.find( ".wdp-drag-handle" ).hide();
	        } else {
                new_rule.find(".sortable-apply-mode-block").show();
		        new_rule.find( ".wdp-drag-handle" ).show();
	        }

	        // Show that label with checkbox when role discount on first position
	        if (new_rule.find( ".wdp-sortable-blocks > div:nth-child(2) .dont-apply-bulk-if-roles-matched-check"  ).length) {
		        new_rule.find( '.dont-apply-bulk-if-roles-matched-check' ).show();
	        } else {
		        new_rule.find( '.dont-apply-bulk-if-roles-matched-check' ).hide();
	        }

			add_range($(this));
        });

        // Add cart adjustment
        new_rule.find('.add-cart-adjustment, .wdp-btn-add-cart-adjustment').click(function () {
            add_cart_adjustment($(this));
        });

	    // Add cart role discount
	    new_rule.find('.add-role-discount, .wdp-btn-add-role-discount').click(function () {
		    add_role_discount($(this));


		    if ( new_rule.find( ".wdp-bulk-adjustments" ).is( ":hidden" ) ) {
			    // Hide or show all sortable handlers depends on bulk adjustments visibility
			    new_rule.find( ".wdp-drag-handle" ).hide();

			    new_rule.find( '.dont-apply-bulk-if-roles-matched-check' ).hide();
                new_rule.find(".sortable-apply-mode-block").hide();
		    } else {
			    new_rule.find( ".wdp-drag-handle" ).show();

			    // Show that label with checkbox only when bulk adjustments is not empty and role discounts on first position
			    if (new_rule.find( ".wdp-sortable-blocks > div:first-child .dont-apply-bulk-if-roles-matched-check"  ).length) {
				    new_rule.find( '.dont-apply-bulk-if-roles-matched-check' ).show();
			    } else {
				    new_rule.find( '.dont-apply-bulk-if-roles-matched-check' ).hide();
			    }
                new_rule.find(".sortable-apply-mode-block").show();
		    }
	    });

      // Add product filter for 'Get products' block
      new_rule.find('.add-filter-get-product, .wdp-btn-add-getproduct').click(function () {
        add_get_products(new_rule.find('.wdp-get-products'));
      });

      // Add product filter for 'Auto add to cart' block
      new_rule.find('.add-filter-auto-add, .wdp-btn-add-autoadd').click(function () {
        add_auto_add(new_rule.find('.wdp-auto-add'));
      });

        // make lists inside rule sortable
        make_sortable(new_rule.find('.wdp-sortable'));

	    new_rule.find( '.wdp-sortable-blocks' ).sortable( {
		    containment: 'parent',
		    items: '.wdp-sortable-block',
		    cursor: 'move',
		    axis: 'y',
		    opacity: 0.65,
		    handle: '.wdp-drag-handle',
		    update: function( event, ui ) {
			    new_rule.trigger( 'change' );

			    // Show that label with checkbox when role discount on first position
			    if (new_rule.find( ".wdp-sortable-blocks > div:nth-child(2) .dont-apply-bulk-if-roles-matched-check"  ).length) {
				    new_rule.find( '.dont-apply-bulk-if-roles-matched-check' ).show();
			    } else {
				    new_rule.find( '.dont-apply-bulk-if-roles-matched-check' ).hide();
			    }
            }
	    } );

        new_rule.on('change', function(e) {

            // ignore bulk action selector
            if ( $(e.target).hasClass("bulk-action-mark") ) {
              return true
            }

            new_rule.addClass('dirty');
        });

        new_rule.find('.wdp-get-products-repeat select').change(function () {
            update_get_products_options_visibility(new_rule);
        });

        new_rule.find('.wdp-auto-add-repeat select').change(function () {
            update_auto_add_options_visibility(new_rule);
        });
    }

    function preSetRuleData(new_rule, data) {
        // apply data
        if (data) {
          if (data.additional.disabled_by_plugin) {
            new_rule.addClass('disabled-by-plugin');
            new_rule.find('.wdp-disabled-automatically-prefix').show();
          } else {
            new_rule.find('.wdp-disabled-automatically-prefix').hide();
          }

          if (data.rule_type) {
            if (data.rule_type === "exclusive") {
              new_rule.find('.rule-type select').val('exclusive');
              new_rule.find('input[name="rule[exclusive]"]').val("1");
            } else {
              new_rule.find('.rule-type select').val(data.rule_type);
              new_rule.find('input[name="rule[exclusive]"]').val("0");
            }
          } else {
            if (data.exclusive === "1") {
              new_rule.find('.rule-type select').val('exclusive');
              new_rule.find('input[name="rule[exclusive]"]').val("1");
            } else {
              new_rule.find('.rule-type select').val('common');
              new_rule.find('input[name="rule[exclusive]"]').val("0");
            }
          }
			if (data.additional.trigger_coupon_code) {
				new_rule.find('.rule-trigger-coupon-code input').val(data.additional.trigger_coupon_code);
			}

          if (data.additional.date_from) {
            new_rule.find('input[name="rule[additional][date_from]"]').val(data.additional.date_from);
          }

          if (data.additional.date_to) {
            new_rule.find('input[name="rule[additional][date_to]"]').val(data.additional.date_to);
          }

            if(data.id) {
                new_rule.find('.rule-id').val(data.id);
            }
            new_rule.find('label.rule-id').text(data.id);

            if ( data.title ) {
                new_rule.find('.wdp-no-name').addClass('cross-not-visible');
            }

            new_rule.find('[data-wdp-title]').text(data.title);
            new_rule.find('.wdp-title').val(data.title);
            new_rule.find('[data-role="flipswitch"]').val(data.enabled);
            new_rule.find('[data-role="flipswitch"]').flipswitch('refresh');
            new_rule.removeClass('dirty');

            if (data.exclusive === "1" || data.rule_type === "exclusive") {
                new_rule.addClass('exclusive');
            }
            // let activeRules = $('.wdp-count-active-rules');
            // let inactiveRules = $('.wdp-count-inactive-rules');
            // $('.wdp-count-all-rules').text(Number($('.wdp-count-all-rules').text()) + 1);
            // if(data.enabled == 'on'){
            //   activeRules.text(Number(activeRules.text()) + 1);
            // }else {
            //   inactiveRules.text(Number(inactiveRules.text()) + 1);
            // }
        } else {
            new_rule.find('.wdp-disabled-automatically-prefix').hide();
            new_rule.removeClass('closed');
            new_rule.addClass('dirty');
        }
    }

  function setRuleData (new_rule, data) {
    if (data.sortable_blocks_priority && data.sortable_blocks_priority.length) {
      var $sorted_blocks = new_rule.find('.wdp-sortable-blocks')
      data.sortable_blocks_priority.forEach(function (data_item) {
        $.each(new_rule.find('input.priority_block_name'), function (el_index, el_item) {
          if (data_item === $(el_item).val()) {
            $(el_item).parent().appendTo($sorted_blocks)
          }
        })

      })

    }

    if (data.additional.is_replace) {
      var $replace_checkbox = new_rule.find('.replace-adjustments input:checkbox')
      $replace_checkbox.prop('checked', true)
    }

    if (data.additional.replace_name) {
      var $replace_name = new_rule.find('.replace-adjustments input:text')
      $replace_name.val(data.additional.replace_name)
    }

    if (data.additional.is_replace_free_products_with_discount) {
      new_rule.find('.replace-free-products input:checkbox').prop('checked', true)
    }

    if (data.additional.free_products_replace_name) {
      new_rule.find('.replace-free-products input:text').val(data.additional.free_products_replace_name)
    }

    if (data.additional.is_replace_auto_add_products_with_discount) {
      new_rule.find('.replace-auto-add input:checkbox').prop('checked', true)
    }

    if (data.additional.auto_add_products_replace_name) {
      new_rule.find('.replace-auto-add input:text').val(data.additional.auto_add_products_replace_name)
    }

    if (data.additional.auto_add_cant_be_removed_from_cart) {
      new_rule.find('.auto-add-remove-disable input:checkbox').prop('checked', true)
    }

    if (data.additional.auto_add_show_as_recommended_product) {
      new_rule.find('.auto-add-recommended-product input:checkbox').prop('checked', true)
    }

    if (data.get_products.max_amount_for_gifts) {
      new_rule.find('input.max-amount-for-gifts').val(data.get_products.max_amount_for_gifts)
    }

    let blocks = new RuleBlocks(new_rule)
    blocks.applyPreloadedData(data)

    if (data.options) {
      fill_options(new_rule.find('.wdp-options'), data.options)
    }

    if (blocks.isProductFiltersOpen()) {
      var $wdp_product_filter = new_rule.find('.wdp-filter-block')
      $.each(data.filters, function (i, filter) {
        add_product_filter($wdp_product_filter, filter, blocks)
      })
    }

    if (data.additional.conditions_relationship) {
      var $radios = new_rule.find('.wdp-conditions-relationship input:radio')
      $radios.filter('[value=' + data.additional.conditions_relationship + ']').prop('checked', true)
    }

    var $btn
    if (blocks.isConditionsOpen()) {
      $btn = new_rule.find('.wdp-btn-add-condition')
      $.each(data.conditions, function (i, condition) {
        add_condition($btn, condition, blocks)
      })
    }

    if (blocks.isLimitsOpen()) {
      $btn = new_rule.find('.wdp-btn-add-limit')
      $.each(data.limits, function (i, limit) {
        add_limit($btn, limit, blocks)
      })
    }

    if (blocks.isProductDiscountsOpen()) {
      add_product_adjustment(new_rule.find('.wdp-product-adjustments'), data.product_adjustments, blocks)
    } else {
      flushInputs(new_rule.find('.wdp-product-adjustments'))
    }

    if (blocks.isCartAdjustmentsOpen()) {
      $btn = new_rule.find('.wdp-btn-add-cart-adjustment')
      $.each(data.cart_adjustments, function (i, cart_adjustment) {
        add_cart_adjustment($btn, cart_adjustment, blocks)
      })
    }

    if (data.additional.sortable_apply_mode) {
      new_rule.find('.sortable-apply-mode').val(data.additional.sortable_apply_mode)
    }

    if (blocks.isRoleDiscountsOpen()) {
      $btn = new_rule.find('.wdp-btn-add-role-discount')
      $.each(data.role_discounts.rows, function (i, role_discount) {
        add_role_discount($btn, role_discount, blocks)
      })

      new_rule.find('[name="rule[role_discounts][dont_apply_bulk_if_roles_matched]"]').attr('checked', false)
      new_rule.find('.dont-apply-bulk-if-roles-matched-check').hide()

      // Hide role discount sortable handler if bulk adjustments is empty
      if (!blocks.isBulkDiscountsOpen()) {
        new_rule.find('.wdp-role-discounts .wdp-drag-handle').hide()
        new_rule.find('.sortable-apply-mode-block').hide()
      } else {
        // Show that label with checkbox when role discount on first position
        if (new_rule.find('.wdp-sortable-blocks > div:nth-child(2) .dont-apply-bulk-if-roles-matched-check').length) {
          new_rule.find('.dont-apply-bulk-if-roles-matched-check').show()
        } else {
          new_rule.find('.dont-apply-bulk-if-roles-matched-check').hide()
        }

        if (typeof data.role_discounts.dont_apply_bulk_if_roles_matched !== 'undefined' && data.role_discounts.dont_apply_bulk_if_roles_matched === '1') {
          new_rule.find('[name="rule[role_discounts][dont_apply_bulk_if_roles_matched]"]').attr('checked', true)
        }
        new_rule.find('.sortable-apply-mode-block').show()
      }
    }

    if (blocks.isBulkDiscountsOpen()) {
      add_bulk_adjustment(new_rule.find('.wdp-bulk-adjustments'), data.bulk_adjustments, blocks)

      // Hide bulk adjustments sortable handler if role discounts is empty
      if (!(data.role_discounts && data.role_discounts.rows)) {
        new_rule.find('.wdp-bulk-adjustments .wdp-drag-handle').hide()
        new_rule.find('.sortable-apply-mode-block').hide()
      }
    }

    if (blocks.isFreeProductsOpen()) {
      fill_get_products_options(new_rule.find('.wdp-get-products-block'), data.get_products)
      var $wdp_product_adjustments = new_rule.find('.wdp-get-products')
      $.each(data.get_products && data.get_products.value ? data.get_products.value : [], function (i, filter) {
        add_get_products($wdp_product_adjustments, filter, blocks)
      })
    }

    if (blocks.isAutoAddToCartOpen()) {
      fill_auto_add_options(new_rule.find('.wdp-auto-add-block'), data.auto_add_products)
      var $wdp_auto_add_products = new_rule.find('.wdp-auto-add')
      $.each(data.auto_add_products && data.auto_add_products.value ? data.auto_add_products.value : [], function (i, filter) {
        add_auto_add($wdp_auto_add_products, filter, blocks)
      })
    }

    if (blocks.isAdvertisingOpen()) {
      add_advertising(new_rule.find('.wdp-discount-messages'), data.advertising, blocks)
    }

    if (blocks.isConditionMessageOpen()) {
      add_condition_message(new_rule.find('.wdp-condition-message'), data.condition_message, blocks)
    }
  }

    function fill_options($container, data) {
        if (data) {
            if (data.repeat) {
                $container.find('.wdp-repeat select').val(data.repeat);
            }
            if (data.apply_to) {
                $container.find('.wdp-apply-to select').val(data.apply_to);
            }
        }
    }

    function add_product_filter($container, data, blocks) {
        if ( ! blocks ) {
          blocks = new RuleBlocks($container.closest('.postbox'))
          blocks.setProductFiltersOpen(true)
        }
        // hide message 'no rules' when add new filter
        blocks.updateView()

        if ( blocks.isProductFiltersOpen() ) {
          $container.closest('.postbox').find('.wdp-btn-add-product-filter').hide();
        } else {
          $container.closest('.postbox').find('.wdp-btn-add-product-filter').show();
        }

        var product_filter_index = get_new_product_filter_index($container);

        var template = get_template_by_element($container.closest('.postbox').find('.wdp-filter-item-qty-options'), {
            r: get_current_rule_index($container),
            f: product_filter_index,
            t: 'filters'
        });

        if (!template) {
            template = get_template('filter_item_qty', {
                r: get_current_rule_index($container),
                f: product_filter_index,
                t: 'filters'
            });
        }

        var $product_filter = $(template);
        var $product_filter_selector = $product_filter.find('.wdp-filter-type');

        // add filter into rule containter
        $container.find('.wdp-product-filter-container').append($product_filter);

		$product_filter.find('.wdp-condition-field-qty input').change(function() {
			var $product_filter_container = $(this).closest('.wdp-filter-block');
			var filters_count = $product_filter_container.find('.wdp-filter-item').length;
			if( $(this).val() == 1 && filters_count === 1 ) {
                $product_filter_container.find('.wdp-select-filter-priority').hide();
			}
			else {
                $product_filter_container.find('.wdp-select-filter-priority').show();
			}

      if ($(this).val() == 1) {
        $product_filter.find('.wdp-limitation').hide();
      } else {
        $product_filter.find('.wdp-limitation').show();
      }
		});

		var filters_count = $container.find('.wdp-filter-item').length;
        if (filters_count > 1) {
          $container.find('.wdp-filter-warning').show();
        } else {
          $container.find('.wdp-filter-warning').hide();
        }
        // load data for existing filter
        if (data) {
            if (data.type && $product_filter_selector.find('option[value="'+ data.type +'"]').length) {
              $product_filter_selector.val(data.type);
            } else {
              data.value = [];
              data.type = $product_filter_selector.val();
            }
            var qty = data.qty ? data.qty : false;
            var qty_end = data.qty_end ? data.qty_end : data.qty;

          if (qty) {
            $product_filter.find('.wdp-condition-field-qty input').val(qty);

            if (qty == 1) {
              $product_filter.find('.wdp-limitation').hide();
            }

            if (qty > 1 && filters_count === 1) {
              $('.wdp-select-filter-priority').show()
            }
          }
	        if (qty_end) $product_filter.find('.wdp-condition-field-qty-end input').val(qty_end);
		}

      if( $product_filter.find('.wdp-condition-field-qty input').val() == 1 && filters_count === 1 ) {
        $container.find('.wdp-select-filter-priority').hide();
      }
      else {
        $container.find('.wdp-select-filter-priority').show();
      }

      if ($product_filter.find('.wdp-condition-field-qty input').val() == 1) {
        $product_filter.find('.wdp-limitation').hide();
      } else {
        $product_filter.find('.wdp-limitation').show();
      }

        // hook for remove filter
        $product_filter.find('.wdp_filter_remove').click(function () {
            var $product_filter_container = $(this).closest('.wdp-filter-block');
            $product_filter.closest('.postbox').find('.adjustment-split[data-index=' + $product_filter.attr('data-index') + ']').remove();
            $product_filter.remove();

            var filters_count = $product_filter_container.find('.wdp-filter-item').length;
            if (filters_count === 0) {
                blocks.setProductFiltersOpen(false)
                blocks.updateView()
                $container.closest('.postbox').find('.wdp-btn-add-product-filter').show();
                $container.trigger('change');
			}

          if(filters_count > 1) {
            $container.closest('.postbox').find('.adjustment-mode-split').attr('disabled', false);
            $container.closest('.postbox').find('.condition-message-mode-split').attr('disabled', false);
            $container.closest('.postbox').find('.wdp-filter-warning').show();
          } else {
            $container.closest('.postbox').find('.wdp-filter-warning').hide();
            $container.closest('.postbox').find('.adjustment-mode-split').prop('checked', false);
            $container.closest('.postbox').find('.adjustment-mode-total').prop('checked', true);
            $container.closest('.postbox').find('.adjustment-mode-split').attr('disabled', 'disabled');

            $container.closest('.postbox').find('.condition-message-mode-split').prop('checked', false);
            $container.closest('.postbox').find('.condition-message-mode-total').prop('checked', true);
            $container.closest('.postbox').find('.condition-message-mode-split').attr('disabled', 'disabled');
          }
			if (filters_count === 1) {
                $product_filter_container.find('.wdp-condition-field-qty input').trigger('change');
            }

            if (filters_count === 1 && $product_filter.find('.wdp-condition-field-qty input').val() === 1) {
                $product_filter_container.find('.wdp-select-filter-priority').hide();
            }

            $container.find(".wdp-matched-previous-filters-container").first().hide();
        });

        if (!wdp_data.options.enable_product_exclude) {
            $container.find(".wdp-product-exclude").hide();
            $container.find(".wdp-exclude-title").hide();
            $container.find(".wdp-exclude-on-wc-sale-container").hide();
            $container.find(".wdp-exclude-already-affected-container").hide();
            $container.find(".wdp-exclude-backorder-container").hide();
            $container.find(".wdp-matched-previous-filters-container").hide();
        }

        if ( product_filter_index === 0 ) {
          $container.find(".wdp-matched-previous-filters-container").hide();
        }

        // render controls for selected filter type
        $product_filter_selector.change(function () {
            update_product_filter_fields($(this));
        });
        update_product_filter_fields($product_filter_selector, data);

        if(filters_count > 1) {
            $container.closest('.postbox').find('.adjustment-mode-split').attr('disabled', false);
            $container.closest('.postbox').find('.condition-message-mode-split').attr('disabled', false);
        } else {
          $container.closest('.postbox').find('.adjustment-mode-split').prop('checked', false);
          $container.closest('.postbox').find('.adjustment-mode-total').prop('checked', true);
            $container.closest('.postbox').find('.adjustment-mode-split').attr('disabled', 'disabled');

          $container.closest('.postbox').find('.condition-message-mode-split').prop('checked', false);
          $container.closest('.postbox').find('.condition-message-mode-total').prop('checked', true);
          $container.closest('.postbox').find('.condition-message-mode-split').attr('disabled', 'disabled');
        }

        let type = 'total';
        if ( $container.closest('.postbox').find('.adjustment-mode-split').prop('checked')) {
          type = 'split'
        }

        add_product_adjustment_split($container.closest('.wdp-filter-block'), product_filter_index);
        updateElementsVisibilityDiscountSplit($container.closest('.postbox').find('.wdp-product-adjustments'), $container.closest('.postbox'));
        updateElementsVisisibilyInRowForElementValue(type, $container.closest('.postbox').find('.wdp-product-adjustments'))

        type = 'total';
        if ( $container.closest('.postbox').find('.condition-message-mode-split').prop('checked')) {
          type = 'split'
        }

        add_condition_message_split($container.closest('.wdp-filter-block'), product_filter_index)
        updateElementsVisisibilyInRowForElementValue(type, $container.closest('.postbox').find('.wdp-condition-message'))
    }

    function remove_user_input_errors( searchIn, errorEl ) {
        let errorFound = $( searchIn ).find( errorEl );
        if ( errorFound.length ) {
            errorFound.remove();
        }
    }

    function update_fields_qty_type($product_filter) {
        var type = $product_filter.find('.wdp-condition-field-qty-type select').val();
        if (type == 'any') {
            $product_filter.find('.wdp-condition-field-qty').hide();
            $product_filter.find('.wdp-condition-field-range').hide();
        } else if (type == 'qty') {
            $product_filter.find('.wdp-condition-field-qty').show();
            $product_filter.find('.wdp-condition-field-range').hide();
        } else if (type == 'range') {
            $product_filter.find('.wdp-condition-field-qty').hide();
            $product_filter.find('.wdp-condition-field-range').show();
        }
    }

	function updateGiftableProductFilterFields(type, ruleIndex, filterIndex, container, data, option) {
		container = container.closest('.wdp-filter-item');

		// prepare template for filter type
		let template = get_template('filter_' + type, {
			r: ruleIndex,
			f: filterIndex,
			t: option || 'filters'
		});

		container.find('.wdp-condition-field-sub').html(template);

		// load data for existing filter
		if (data) {
			if (data.method) {
				container.find('.wdp-filter-field-method select').val(data.method);
			}

			if (data.value) {
				var html = '';
				$.each(data.value, function (i, id) {
					var title = wdp_data.titles[type] && wdp_data.titles[type][id] ? wdp_data.titles[type][id] : id;
					var link = wdp_data.links && wdp_data.links[type] && wdp_data.links[type][id] ? wdp_data.links[type][id] : '';
					html += '<option selected data-link="' + link + '" value="' + id + '">' + title + '</option>';
				});
				container.find('.wdp-condition-field-value select').append(html);
			}
		}

		make_select2_products(container.find('[data-field="autocomplete"]'));
		make_select2_product_taxonomies(container.find('[data-field="autocomplete"][data-list="product_taxonomies"]'));
	}

  function updateAutoAddProductFilterFields(type, ruleIndex, filterIndex, container, data, option) {
    container = container.closest('.wdp-filter-item');

    // prepare template for filter type
    let template = get_template('filter_' + type, {
      r: ruleIndex,
      f: filterIndex,
      t: option || 'filters'
    });

    container.find('.wdp-condition-field-sub').html(template);

    // load data for existing filter
    if (data) {
      if (data.method) {
        container.find('.wdp-filter-field-method select').val(data.method);
      }

      if (data.value) {
        var html = '';
        $.each(data.value, function (i, id) {
          var title = wdp_data.titles[type] && wdp_data.titles[type][id] ? wdp_data.titles[type][id] : id;
          html += '<option selected value="' + id + '">' + title + '</option>';
        });
        container.find('.wdp-condition-field-value select').append(html);
      }
    }

    make_select2_products(container.find('[data-field="autocomplete"]'));
    make_select2_product_taxonomies(container.find('[data-field="autocomplete"][data-list="product_taxonomies"]'));
  }

    function update_product_filter_fields($el, data, option) {
        var $container = $el.closest('.wdp-filter-item');
        var type = $el.val();

        // prepare template for filter type
        var template = get_template('filter_' + type, {
            r: get_current_rule_index($el),
            f: get_current_product_filter_index($el),
            t: option || 'filters'
        });

        $container.find('.wdp-condition-field-sub').html(template);
      if ( $container.closest('.postbox').find(".rule-type select").val() === "persistent" ) {
        $container.find('.wdp-condition-field-sub .wdp-filter-field-method select').remove();
      }

        // load data for existing filter
        if (data) {
            if (data.method) {
                $container.find('.wdp-filter-field-method select').val(data.method);
            }

            if (data.value) {
                var html = '';
                $.each(data.value, function (i, id) {
                    var title = wdp_data.titles[data.type] && wdp_data.titles[data.type][id] ? wdp_data.titles[data.type][id] : id;
					var link = wdp_data.links && wdp_data.links[data.type] && wdp_data.links[data.type][id] ? wdp_data.links[data.type][id] : '';
                    html += '<option selected data-link="' + link + '" value="' + id + '">' + title + '</option>';
                });
                $container.find('.wdp-condition-field-value select').append(html);
            }

            if ( data.product_exclude ) {
                if ( data.product_exclude.values ) {
                    var product_exclude_html = '';
                    var pr_excl_type = 'products';
                    $.each(data.product_exclude.values, function (i, id) {
                        var title = wdp_data.titles[pr_excl_type] && wdp_data.titles[pr_excl_type][id] ? wdp_data.titles[pr_excl_type][id] : id;
                        var link = wdp_data.links && wdp_data.links[pr_excl_type] && wdp_data.links[pr_excl_type][id] ? wdp_data.links[pr_excl_type][id] : '';
						product_exclude_html += '<option selected data-link="' + link + '" value="' + id + '">' + title + '</option>';
                    });
                    $container.find('.wdp-product-exclude select').append(product_exclude_html);
                }

                if (data.product_exclude.on_wc_sale) {
                    $container.find('.wdp-exclude-on-wc-sale-container input').prop('checked', true);
                }

                if (data.product_exclude.already_affected) {
                    $container.find('.wdp-exclude-already-affected-container input').prop('checked', true);
                }

				if (data.product_exclude.backorder) {
					$container.find('.wdp-exclude-backorder-container input').prop('checked', true);
				}

              if (data.product_exclude.matched_previous_filters) {
                $container.find('.wdp-matched-previous-filters-container input').prop('checked', true);
              }
            }

            if (data.limitation) {
                $container.find('.wdp-limitation select').val(data.limitation);
            }

            /** pro version functionality */
            if ( data.select_priority ) {
                $container.find('.wdp-select-filter-priority select').val(data.select_priority);
            }
        }

        make_select2_products($container.find('[data-field="autocomplete"]'));
	    make_select2_product_taxonomies($container.find('[data-field="autocomplete"][data-list="product_taxonomies"]'));
    }

    function add_condition($el, data, blocks) {
        if ( ! blocks ) {
          blocks = new RuleBlocks($el.closest('.postbox'))
          blocks.setConditionsOpen(true)
        }
        blocks.updateView()

        if ( blocks.isConditionsOpen() ) {
          $el.closest('.postbox').find('.wdp-btn-add-condition').hide();
        } else {
          $el.closest('.postbox').find('.wdp-btn-add-condition').show();
        }

        var condition_template = get_template('condition_row', {
            r: get_current_rule_index($el),
            c: get_new_condition_index($el)
        });

        var $condition = $(condition_template);
        $el.closest('.postbox').find('.wdp-conditions-container').append($condition);

        $condition.find('.wdp-condition-remove').click(function () {
            var $rule = $(this).closest('.postbox');
            $(this).closest('.wdp-condition').remove();

            var conditions_count = $rule.find('.wdp-conditions .wdp-condition').length;
            if (conditions_count === 0) {
                blocks.setConditionsOpen(false)
                blocks.updateView()
                $el.closest('.postbox').find('.wdp-btn-add-condition').show();
            }
        });

        var $condition_type_selector = $condition.find('.wdp-condition-field-type select');
        if (data && data.type) {
            $condition_type_selector.val(data.type);
        }

        if (!$condition_type_selector.val()) {
            var new_val = $condition_type_selector.find('option').prop('value');
            $condition_type_selector.val(new_val);
        }

        var prev_field_values;

        update_condition_fields($condition_type_selector, data);
        $condition_type_selector.change(function () {
            update_condition_fields($(this), prev_field_values);
        });

        //save previous field values of condition to apply it to next chosen condition
        $condition_type_selector.focus(function () {
          prev_field_values = {};
          prev_field_values.options = {};
          prev_field_values.data_list = {};
          prev_field_values['type'] = 'prev_field_values';

          const $container = $(this).closest('.wdp-condition');
          const fields = $container.find('.wdp-condition-subfield');

          fields.each( function(index, field) {
            let value_field;
            let key = $(field).find('select, input, textarea').attr('name').match(/options]\[(\w+)]/i);
            key = key[key.length - 1];

            value_field = $('select[data-list]', field);
            if (value_field.length) {
              prev_field_values.options[key] = value_field.val();
              prev_field_values.data_list[key] = value_field.data('list');
              return;
            }

            value_field = $('select', field);
            if (value_field.length) {
              prev_field_values.options[key] = value_field.val();
              return;
            }

            value_field = $('input', field);
            const value_field_checkbox = $('input[type="checkbox"]', field);
            if (value_field_checkbox.length) {
              prev_field_values.options[key] = value_field_checkbox.prop('checked');
            } else if (value_field.length) {
              prev_field_values.options[key] = value_field.val();
            }

            value_field = $('textarea', field);
            if ( value_field.length ) {
              prev_field_values.options[key] = value_field.text();
            }
          });
        })
    }

    function update_condition_fields($el, data) {
        var $container = $el.closest('.wdp-condition');
        var type = $el.val();

        var template = get_template(type, {
            r: get_current_rule_index($el),
            c: $container.data('index')
        });

        $container.find('.wdp-condition-field-sub').html(template);

        if (/\w+_all\w*/.test(type)) {
          function appendTemplate() {
            let subcondition = type.replace(/(\w+)_all/, "$1");
            let measure = $container.find('.wdp-condition-field-measure select').val();
            if (measure && measure !== 'qty') {
              subcondition = measure + '_' + subcondition;
            }
            let template =  get_template(subcondition, {
              r: get_current_rule_index($el),
              c: $container.data('index')
            });

            $container.find('.wdp-condition-field-product-all-sub').html(template);

            make_select2_tags($container.find('[data-field="tags"]'));
            make_select2_products($container.find('[data-field="autocomplete"]'));
            make_select2_product_taxonomies($container.find('[data-field="autocomplete"][data-list="product_taxonomies"]'));
            make_select2_preloaded($container.find('[data-field="preloaded"]'));
          }

          let fieldMeasure = $container.find('.wdp-condition-field-measure select');
          if (data.options['product_measure']) {
            fieldMeasure.val(data.options['product_measure']);
          }
          fieldMeasure.change(appendTemplate);

          appendTemplate();
        }

        if (data && data.options) {
            const is_prev_fields_data = data && data.type === 'prev_field_values';
            var fields = $container.find('.wdp-condition-subfield');

            fields.each( function(index, field) {
                var value_field;
                var key = $(field).find('select, input, textarea').attr('name').match(/options]\[(\w+)]/i);
                key = key[key.length - 1];

                value_field = $('select[data-list]', field);
                if (value_field.length) {
                  if (!is_prev_fields_data ||
                    (is_prev_fields_data && data.data_list[key] && data.data_list[key] === value_field.data('list'))) {
                    var titles = [], get_title;
                    if (value_field.data('field') === 'autocomplete') {
                      if (value_field.data('list') === 'product_taxonomies') {
                        titles = wdp_data.titles[value_field.data('taxonomy')];
                      } else {
                        titles = wdp_data.titles[value_field.data('list')];
                      }
                      get_title = function (id) {
                        return typeof titles !== 'undefined' ? titles[id] : id;
                      }
                    } else if (value_field.data('field') === 'preloaded') {
                      titles = wdp_data.lists[value_field.data('list')];
                      get_title = function (id) {
                        for (var i = 0; i < titles.length; i++) {
                          if (titles[i].id.toString() === id) return titles[i].text;
                        }
                        return id;
                      }
                    }

                    $.each(data.options[key], function (i, val) {
                      // value_field.find('[value="' + val + '"]').prop('selected', 'selected');
                      value_field.append('<option selected value="' + val + '">' + get_title(val) + '</option>');
                    });
                    return;
                  }
                }

                value_field = $('select', field);
                if (value_field.length) {
                  const opts = $.map($('option', value_field), function (option) {
                    return option.value;
                  });
                  if (opts.includes(data.options[key])) {
                    value_field.val(data.options[key]);
                  }
                  return;
                }

                value_field = $('input', field);
              var value_field_checkbox = $('input[type="checkbox"]', field);
              if (value_field_checkbox.length) {
                if (data.options[key]) {
                  value_field_checkbox.attr('checked', 'checked');
                }
              } else if (value_field.length && data.options[key]) {
                value_field.val(data.options[key]);
              }

                value_field = $('textarea', field);
                if ( value_field.length && data.options[key] ) {
                    value_field.text( data.options[key] );
                }
            });
        }

        $container.find('[data-field="date"]').removeClass('hasDatepicker').datepicker({dateFormat:"yy-mm-dd"});
        $container.find('[class="datetimepicker"]').datetimepicker();
        make_select2_tags($container.find('[data-field="tags"]'));

        make_select2_products($container.find('[data-field="autocomplete"]'));
	    make_select2_product_taxonomies($container.find('[data-field="autocomplete"][data-list="product_taxonomies"]'));
        make_select2_preloaded($container.find('[data-field="preloaded"]'));

        $container.find('.wdp-condition-field-method-coupon select').change( function() {
            var disabled = [ 'at_least_one_any', 'none_at_all' ].indexOf( $( this ).val() ) >= 0;
            $container.find('.wdp-condition-field-value-coupon select').prop('disabled', disabled);
            if ( disabled ) {
                $container.find('.wdp-condition-field-value-coupon select').val([]).trigger('change');
            }
        } );

        $container.find('.wdp-condition-field-method-coupon select').each( function() {
            var disabled = [ 'at_least_one_any', 'none_at_all' ].indexOf( $( this ).val() ) >= 0;
            $container.find('.wdp-condition-field-value-coupon select').prop('disabled', disabled);
            if ( disabled ) {
                $container.find('.wdp-condition-field-value-coupon select').val([]).trigger('change');
            }
        } );

	    $container.find( '.wdp-condition-field-method select' ).change( function () {
		    var enable_last = 'in_range' === $( this ).val();
		    $container.find( '.wdp-condition-field-value-last' ).toggle( enable_last );
	    } );

	    $container.find( '.wdp-condition-field-method select' ).each( function () {
		    var enable_last = 'in_range' === $( this ).val();
		    $container.find( '.wdp-condition-field-value-last' ).toggle( enable_last );
        } );

        var combination_any_update = function() {
          let combination_any_input = $container.find( '#combination-any' );
          let select = combination_any_input.closest( '.wdp-column' ).find('.wdp-condition-field-value select');
          if ( combination_any_input.is(":checked") ) {
            if ( select.val().length > 0 ) {
              select.val('').trigger('change');
            }
            select.prop('disabled', true);
          } else {
            select.prop('disabled', false);
          }
        };

      $container.find( '#combination-any' ).click(combination_any_update);
      combination_any_update();

    }

    function add_advertising($container, data, blocks) {
      if ( ! blocks ) {
        blocks = new RuleBlocks($container.closest('.postbox'))
        blocks.setAdvertisingOpen(true)
      }

      blocks.updateView()

      var $rule = $container.closest('.postbox');

      if ( blocks.isAdvertisingOpen() ) {
        $rule.find('.wdp-btn-add-discount-message').hide();
      } else {
        $rule.find('.wdp-btn-add-discount-message').show();
      }

      $container.find('.wdp-btn-remove').click(function () {
        $rule.find('.wdp-btn-add-discount-message').show();
        blocks.setAdvertisingOpen(false)
        blocks.updateView()
        flushInputs($container);
      });

      if (data) {
        if ( data.enabled_timer ) {
            $container.find('[name="rule[advertising][enabled_timer]"]').prop('checked', true);
        }

        if ( data.timer_message ) {
            $container.find('[name="rule[advertising][timer_message]"]').val(data.timer_message);
        }

        if ( data.discount_message ) {
            $container.find('[name="rule[advertising][discount_message]"]').val(data.discount_message);
        }

        if ( data.discount_message_cart_item ) {
          $container.find('[name="rule[advertising][discount_message_cart_item]"]').val(data.discount_message_cart_item);
        }

        if ( data.long_discount_message ) {
          $container.find('[name="rule[advertising][long_discount_message]"]').val(data.long_discount_message);
        }

        if ( data.sale_badge ) {
          $container.find('[name="rule[advertising][sale_badge]"]').val(data.sale_badge);
        }
      }
    }

    function add_condition_message($container, data, blocks) {
      if ( ! blocks ) {
        blocks = new RuleBlocks($container.closest('.postbox'))
        blocks.setConditionMessageOpen(true)
      }

      blocks.updateView()

      var $rule = $container.closest('.postbox');

      if ( blocks.isConditionMessageOpen() ) {
        $rule.find('.wdp-btn-add-condition-message').hide();
      } else {
        $rule.find('.wdp-btn-add-condition-message').show();
      }

      $container.find('.wdp-btn-remove').click(function () {
        $rule.find('.wdp-btn-add-condition-message').show();
        blocks.setConditionMessageOpen(false)
        blocks.updateView()
        flushInputs($container);
      });

      var type;
      if (data) {
            type = data.type;
            $container.find('.condition-message-mode-' + type).attr('checked', 'checked').prop('checked', true);

            if (data.total) {
                $container.find('.condition-message-total-message').val(data.total.message);
            }

            $container.find('.condition-message-split').each(function(index) {
                if (data.split && data.split[index]) {
                    fill_condition_message_split($(this), data.split[index]);
                }
                else {
                    fill_condition_message_split($(this));
                }
            });
            if (data['beginning_message']) {
                $container.find('.condition-message-beginning-message').val(data['beginning_message']);
            }
            if (data['end_message']) {
                $container.find('.condition-message-end-message').val(data['end_message']);
            }
        } else {
            type = 'total';
            $container.find('.condition-message-mode-total').attr('checked', 'checked').prop('checked', true);

            $container.find('.condition-message-split').each(function() {
                fill_condition_message_split($(this));
            });
        }

        $container.find('.condition-message-mode').change(function () {
            updateElementsVisisibilyInRowForElementValue($(this).val(), $(this).closest('.wdp-condition-message'));
        });
        updateElementsVisisibilyInRowForElementValue(type, $container);
    }

    function add_limit($el, data, blocks) {
        if ( ! blocks ) {
          blocks = new RuleBlocks($el.closest('.postbox'))
          blocks.setLimitsOpen(true)
        }
        blocks.updateView()

        if ( blocks.isLimitsOpen() ) {
          $el.closest('.postbox').find('.wdp-btn-add-limit').hide();
        } else {
          $el.closest('.postbox').find('.wdp-btn-add-limit').show();
        }

        var template = get_template('limit_row', {
            l: get_new_limit_index($el)
        });

        var $limit = $(template);
        $el.closest('.postbox').find('.wdp-limits-container').append($limit);

        if (data) {
            $limit.find('.wdp-limit-type select').val(data.type);
        }

        $limit.find('.wdp-limit-remove').click(function () {
            var $rule = $(this).closest('.postbox');
            $(this).closest('.wdp-limit').remove();

            var limits_count = $rule.find('.wdp-limits .wdp-limit').length;
            if (limits_count === 0) {
                blocks.setLimitsOpen(false)
                blocks.updateView()
                $el.closest('.postbox').find('.wdp-btn-add-limit').show();
            }
        });

        var prev_field_values = {};

        var $limit_type_selector = $limit.find('.wdp-limit-type select');
        update_limit_fields($limit_type_selector, data);
        $limit_type_selector.change(function () {
            update_limit_fields($(this), prev_field_values);
        });

        $limit_type_selector.focus(function () {
          prev_field_values = {};
          const $container = $(this).closest('.wdp-limit');
          prev_field_values.options = $container.find('.wdp-limit-value input').val();
        })
    }

    function update_limit_fields($el, data) {
        var $container = $el.closest('.wdp-limit');
        var type = $el.val();

        var template = get_template(type + '_limit', {
            l: $container.data('index'),
        });

        $container.find('.wdp-limit-field-sub').html(template);

        if (data && data.options) {
            $container.find('.wdp-limit-value input').val( data.options );
        }
    }

    function add_range($el, data) {
        var $postbox = $el.closest('.postbox');

        var template = get_template('adjustment_bulk', {
            r: get_current_rule_index($el),
            b: get_new_range_index($el)
        });

        $postbox.find('.wdp-ranges-empty').hide();

        var last_range_to_value;
        var el_last_range_to_value = $postbox.find('.wdp-ranges .wdp-range:last .adjustment-to');
        if (el_last_range_to_value.length) {
            last_range_to_value = el_last_range_to_value.val();
        }

        var $range = $(template);

        $postbox.find('.wdp-ranges').append($range);

        if (last_range_to_value) {
            $range.find('.adjustment-from').val(parseInt(last_range_to_value)+1);
            $range.find('.adjustment-to').focus();
        } else {
            $range.find('.adjustment-from').focus();
		}

        if (data) {
            $range.find('.adjustment-from').val(data.from);
            $range.find('.adjustment-to').val(data.to);
            $range.find('.adjustment-value').val(data.value);
        }

        $range.find('.wdp-range-remove').click(function () {
            var $rule = $(this).closest('.postbox');
            $(this).closest('.wdp-range').remove();

            var ranges_count = $rule.find('.wdp-ranges .wdp-range').length;
            if (ranges_count === 0) {
                $postbox.find('.wdp-ranges-empty').show();
            }
        });
    }

    function fill_get_products_options($container, data) {
        if (data) {
            if (data.repeat) {
                $container.find('.wdp-get-products-repeat select').val(data.repeat);
            }

            if (data.repeat_subtotal) {
                $container.find('.wdp-get-products-repeat .repeat-subtotal-value').val(data.repeat_subtotal);
            }
        }

        update_get_products_options_visibility($container);
    }

    function fill_auto_add_options($container, data) {
      if (data) {
        if (data.repeat) {
          $container.find('.wdp-auto-add-repeat select').val(data.repeat);
        }

        if (data.repeat_subtotal) {
          $container.find('.wdp-auto-add-repeat .repeat-subtotal-value').val(data.repeat_subtotal);
        }
      }

      update_auto_add_options_visibility($container);
    }

    function add_get_products($container, data, blocks) {
        if ( ! blocks ) {
          blocks = new RuleBlocks($container.closest('.postbox'))
          blocks.setFreeProductsOpen(true)
        }
        blocks.updateView()

        if ( blocks.isFreeProductsOpen() ) {
          $container.closest('.postbox').find('.wdp-btn-add-getproduct').hide();
        } else {
          $container.closest('.postbox').find('.wdp-btn-add-getproduct').show();
        }

        var template = get_template('adjustment_deal', {
            r: get_current_rule_index($container),
            f: get_new_product_filter_index($container)
        });

        var $product_filter = $(template);
		let productFilterItem = $product_filter;

        $container.append($product_filter);

        let giftTypeSelector = $product_filter.find('.wdp-condition-field-gift-mode select');

        if (data) {
            if (data['qty']) $product_filter.find('.wdp-condition-field-qty input').val(data['qty']);

			if (data['gift_mode']) {
				giftTypeSelector.val(data['gift_mode']);
			}
        }

        var prev_field_values = {};

        let giftTypeSelectorOnChange = function(value, addProductData) {
			if ( value === "use_product_from_filter" || value === "use_only_first_product_from_filter" ) {
				addProductData = {
					'value': {},
				}
				updateGiftableProductFilterFields(
					"giftable_products",
					get_current_rule_index(productFilterItem),
					get_current_product_filter_index(productFilterItem),
					productFilterItem,
					addProductData,
					'get_products][value'
				);

				$product_filter.find('.wdp-condition-field-sub select').prop("disabled", true);
			} else if ( value === "allow_to_choose" || value === "giftable_products" || value === "require_to_choose" || value === "giftable_products_in_rotation" ) {
        if (addProductData && addProductData.data_list && addProductData.data_list !== "giftable_products") {
          addProductData = {};
        }
				updateGiftableProductFilterFields(
					"giftable_products",
					get_current_rule_index(productFilterItem),
					get_current_product_filter_index(productFilterItem),
					productFilterItem,
					addProductData,
					'get_products][value'
				);
			} else if ( value === "allow_to_choose_from_product_cat" || value === "require_to_choose_from_product_cat" ) {
        if (addProductData && addProductData.data_list && addProductData.data_list !== "giftable_categories") {
          addProductData = {};
        }
				updateGiftableProductFilterFields(
					"giftable_categories",
					get_current_rule_index(productFilterItem),
					get_current_product_filter_index(productFilterItem),
					productFilterItem,
					addProductData,
					'get_products][value'
				);
			}
		}

		giftTypeSelector.on('change', function (e) {
			giftTypeSelectorOnChange(this.value, prev_field_values);
		})
      giftTypeSelector.on('focus', function() {
        prev_field_values = {};
        const container = $(this).closest('.wdp-get-products');
        const select = container.find('select[data-list]');
        prev_field_values['value'] = select.val();
        prev_field_values['data_list'] = select.data('list');
      });
		giftTypeSelectorOnChange(giftTypeSelector.val(), data);

        var $rule = $container.closest('.postbox');

        $product_filter.find('.wdp_filter_remove').click(function () {
            var $product_filter_container = $(this).closest('.wdp-get-products');
            $product_filter.remove();

            var filters_count = $product_filter_container.find('.wdp-filter-item').length;
            if (filters_count === 0) {
                blocks.setFreeProductsOpen(false)
                blocks.updateView()
                $container.closest('.postbox').find('.wdp-btn-add-getproduct').show();
            }
        });

    }

  function add_auto_add($container, data, blocks) {
    if ( ! blocks ) {
      blocks = new RuleBlocks($container.closest('.postbox'))
      blocks.setAutoAddToCartOpen(true)
    }
    blocks.updateView()

    if ( blocks.isAutoAddToCartOpen() ) {
      $container.closest('.postbox').find('.wdp-btn-add-autoadd').hide();
    } else {
      $container.closest('.postbox').find('.wdp-btn-add-autoadd').show();
    }

    var template = get_template('auto_add_product', {
      r: get_current_rule_index($container),
      f: get_new_product_filter_index($container)
    });

    var $product_filter = $(template);
    let productFilterItem = $product_filter;

    $container.append($product_filter);

    let autoAddTypeSelector = $product_filter.find('.wdp-condition-field-auto-add-mode select');

    if (data) {
      if (data['qty']) $product_filter.find('.wdp-condition-field-qty input').val(data['qty']);

      if (data['auto_add_mode']) {
        autoAddTypeSelector.val(data['auto_add_mode']);
      }

      if (data['discount_type']) {
        $product_filter.find('.auto-add-products-discount-type').val(data['discount_type']);
      }

      if (data['discount_value']) {
        $product_filter.find('.auto-add-products-discount-value').val(data['discount_value']);
      }
    }

    let autoAddTypeSelectorOnChange = function(value, addProductData) {
      if ( value === "use_product_from_filter" ) {
        addProductData = {
          'value': {},
        }
        updateAutoAddProductFilterFields(
          "auto_add_products",
          get_current_rule_index(productFilterItem),
          get_current_product_filter_index(productFilterItem),
          productFilterItem,
          addProductData,
          'auto_add_products][value'
        );

        $product_filter.find('.wdp-condition-field-sub select').prop("disabled", true);
      } else if ( value === "auto_add_products" || value === "auto_add_products_in_rotation" ) {
        updateAutoAddProductFilterFields(
          "auto_add_products",
          get_current_rule_index(productFilterItem),
          get_current_product_filter_index(productFilterItem),
          productFilterItem,
          addProductData,
          'auto_add_products][value'
        );
      }
    }

    autoAddTypeSelector.on('change', function (e) {
      autoAddTypeSelectorOnChange(this.value, {});
    })
    autoAddTypeSelectorOnChange(autoAddTypeSelector.val(), data);

    var $rule = $container.closest('.postbox');

    $product_filter.find('.wdp_filter_remove').click(function () {
      var $product_filter_container = $(this).closest('.wdp-auto-add');
      $product_filter.remove();

      var filters_count = $product_filter_container.find('.wdp-filter-item').length;
      if (filters_count === 0) {
        blocks.setAutoAddToCartOpen(false)
        blocks.updateView()
        $container.closest('.postbox').find('.wdp-btn-add-autoadd').show();
      }
    });

  }

    function add_product_adjustment($container, data, blocks) {
        if ( ! blocks ) {
          blocks = new RuleBlocks($container.closest('.postbox'))
          blocks.setProductDiscountsOpen(true)
        }
        blocks.updateView()

        if ( blocks.isProductDiscountsOpen() ) {
          $container.closest('.postbox').find('.wdp-btn-add-product-adjustment').hide();
        } else {
          $container.closest('.postbox').find('.wdp-btn-add-product-adjustment').show();
        }

        var $rule = $container.closest('.postbox');

        $rule.find('.wdp_product_adjustment_remove').click(function () {
            $rule.find('.wdp-btn-add-product-adjustment').show();
            blocks.setProductDiscountsOpen(false)
            blocks.updateView()
            flushInputs($container);
        });

        var type;
        if (data) {
            type = data.type;
            $container.find('.adjustment-mode-' + type).attr('checked', 'checked').prop('checked', true);

            if (data.total) {
                $container.find('.adjustment-total-type').val(data.total.type);
                $container.find('.adjustment-total-value').val(data.total.value);
            }

            $container.find('.adjustment-split').each(function(index) {
                if (data.split && data.split[index]) {
                    fill_product_adjustment_split($(this), data.split[index]);
                }
                else {
                    fill_product_adjustment_split($(this));
                }
            });
            if (data['max_discount_sum']) {
                $container.find('.product-adjustments-max-discount').val(data['max_discount_sum']);
            }
            if (data['split_discount_by']) {
              $container.find('.split-discount-by-' + data['split_discount_by']).attr('selected', 'selected');
            }
        } else {
            type = 'total';
            $container.find('.adjustment-mode-total').attr('checked', 'checked').prop('checked', true);
            $container.find('.adjustment-total-type').find('option:first-child').prop('selected', 'selected');
            $container.find('.adjustment-split-discount-type').find('option:first-child').prop('selected', 'selected');

            $container.find('.adjustment-split').each(function() {
                fill_product_adjustment_split($(this));
            });
        }

        $container.find('.adjustment-mode').change(function () {
            updateElementsVisisibilyInRowForElementValue($(this).val(), $(this).closest('.wdp-product-adjustments'));
        });
        updateElementsVisisibilyInRowForElementValue(type, $container);

      $rule.change(function() {
        updateElementsVisibilityDiscountSplit($(this).find('.wdp-product-adjustments'), $(this));
      })
      updateElementsVisibilityDiscountSplit($container, $rule);
    }

    function add_product_adjustment_split($container, adj_index, data) {
        var template = get_template('adjustment_split_row', {
            adj: adj_index
        });

        var $split_adjustment = $(template);

        $container.closest('.postbox')
            .find('.wdp-product-adjustments-split-container').append($split_adjustment);

        fill_product_adjustment_split($split_adjustment, data);
    }

    function fill_product_adjustment_split($split_adjustment, data) {
        if (data) {
            $split_adjustment.find('.adjustment-split-type').val(data.type);
            $split_adjustment.find('.adjustment-split-value').val(data.value);
        }
        else {
            $split_adjustment.find('.adjustment-split-type').find('option:first-child').prop('selected', 'selected');
        }
    }

    function add_condition_message_split($container, adj_index, data) {
        var template = get_template('condition_message_split_row', {
            adj: adj_index
        });

        var $split_adjustment = $(template);

        $container.closest('.postbox')
            .find('.wdp-condition-message-split-container').append($split_adjustment);

        fill_condition_message_split($split_adjustment, data);
    }

    function fill_condition_message_split($split_adjustment, data) {
        if (data) {
            $split_adjustment.find('.condition-message-split-message').val(data.message);
        }
    }

    function add_bulk_adjustment($container, data, blocks) {
        bulk_adjustment().add($container, data, blocks);
    }

    function add_cart_adjustment($el, data, blocks) {
        if ( ! blocks ) {
          blocks = new RuleBlocks($el.closest('.postbox'))
          blocks.setCartAdjustmentsOpen(true)
        }
        blocks.updateView()

        if ( blocks.isCartAdjustmentsOpen() ) {
          $el.closest('.postbox').find('.wdp-btn-add-cart-adjustment').hide();
        } else {
          $el.closest('.postbox').find('.wdp-btn-add-cart-adjustment').show();
        }

        var template = get_template('cart_adjustment_row', {
            ca: get_new_cart_adjustment_index($el)
        });

        var $cart_adjustment = $(template);

        $el.closest('.postbox').find('.wdp-cart-adjustments-container').append($cart_adjustment);

        if (data) {
            $cart_adjustment.find('.wdp-cart-adjustment-type select').val(data.type);
        }

        $cart_adjustment.find('.wdp-cart-adjustment-remove').click(function () {
            var $rule = $(this).closest('.postbox');
            $(this).closest('.wdp-cart-adjustment').remove();

            var adjs_count = $rule.find('.wdp-cart-adjustments .wdp-cart-adjustment').length;
            if (adjs_count === 0) {
                blocks.setCartAdjustmentsOpen(false)
                blocks.updateView()
                $el.closest('.postbox').find('.wdp-btn-add-cart-adjustment').show();
            }
        });

        var $adj_type_selector = $cart_adjustment.find('.wdp-cart-adjustment-type select');
        var prev_field_values = {};
        update_cart_adjustment_fields($adj_type_selector, data);
        $adj_type_selector.change(function () {
            update_cart_adjustment_fields($(this), prev_field_values);
        });

      $adj_type_selector.on('focus', function() {
        const $container = $(this).closest('.wdp-cart-adjustment');
        const fields = $container.find('.wdp-cart-adjustment-value');
        if (!fields.length) {
          return;
        }
        prev_field_values = {};
        prev_field_values.options = {};
        prev_field_values.data_list = {};
        prev_field_values.elem_types = {};
        prev_field_values['type'] = 'prev_field_values';

        fields.each( function(i, field) {
          let value_field = $(field).find('select, input, textarea');
          if (!value_field.length) {
            return;
          }
          let index = value_field.attr('name').match(/options]\[(\w+)]/i);
          if ( index === null ) {
            return;
          }
          index = index[index.length - 1];

          value_field = $('select[data-list]', field);
          if (value_field.length) {
            prev_field_values.options[index] = value_field.val();
            prev_field_values.data_list[index] = value_field.data('list');
            prev_field_values.elem_types[index] = 'select_data-list';
            return;
          }

          value_field = $('select', field);
          if (value_field.length) {
            prev_field_values.options[index] = value_field.val();
            prev_field_values.elem_types[index] = 'select';
            return;
          }

          value_field = $('input', field);
          if (value_field.length) {
            prev_field_values.options[index] = value_field.val();
            prev_field_values.elem_types[index] = 'input_' + value_field.attr('type');
          }
        });
      })
    }

	function add_role_discount( $el, data, blocks ) {
    if ( ! blocks ) {
      blocks = new RuleBlocks($el.closest('.postbox'))
      blocks.setRoleDiscountsOpen(true)
    }
    blocks.updateView()

    if ( blocks.isRoleDiscountsOpen() ) {
      $el.closest( '.postbox' ).find( '.wdp-btn-add-role-discount' ).hide();
    } else {
      $el.closest( '.postbox' ).find( '.wdp-btn-add-role-discount' ).show();
    }

		var template = get_template( 'role_discount_row', {
			indx: get_new_role_discount_index( $el )
		} );

		var $role_discount = $( template );

		$el.closest( '.postbox' ).find( '.wdp-role-discounts-container' ).append( $role_discount );

		if ( data ) {
			$role_discount.find( 'input.wdp-role-discount-value, select.wdp-role-discount-value' ).each(
				function ( index, el ) {
					var field_name = $( el ).data( 'field-name' );
					var field_value = data[field_name];
					if ( field_value !== undefined ) {
						if ( "roles" === field_name ) {
							var html = '';
							$.each( field_value, function ( i, id ) {
								html += '<option selected value="' + id + '">' + get_role_label(id) + '</option>';
							} );
							$( this ).append( html );
						} else {
							$( this ).val( field_value );
						}
					}
				} );
		}

		make_select2_preloaded( $role_discount.find( '[data-field="preloaded"]' ) );

		$role_discount.find( '.wdp_role_discount_remove' ).click( function () {
			var $rule = $( this ).closest( '.postbox' );
			$( this ).closest( '.wdp-role-discount' ).remove();

			var role_discounts_count = $rule.find( '.wdp-role-discounts .wdp-role-discount' ).length;
			if ( role_discounts_count === 0 ) {
        blocks.setRoleDiscountsOpen(false)
        blocks.updateView()
				$el.closest( '.postbox' ).find( '.wdp-btn-add-role-discount' ).show();

				// Unconditionally hide all sortable handlers
                $rule.find(".wdp-drag-handle").hide();
                $rule.find(".sortable-apply-mode-block").hide();
            }
        } );

	}

    function update_cart_adjustment_fields($el, data) {
        var $container = $el.closest('.wdp-cart-adjustment');
        var type = $el.val();

        var template = get_template(type + '_cart_adjustment', {
            ca: $container.data('index'),
        });

        $container.find('.wdp-cart-adjustment-field-sub').html(template);

        if (data && data.options) {
                // if (data.options[index] !== undefined) {
                //     jQuery(this).val(data.options[index]);
                // }

              var fields = $container.find('.wdp-cart-adjustment-value');
              fields.each( function(index, field) {
                var value_field;

                value_field = $('select[data-list]', field);
                if (value_field.length) {
                  if (data['type'] === 'prev_field_values') {
                    if (!data.elem_types[index] || data.elem_types[index] !== 'select_data-list') {
                      return;
                    }
                    if (!data['data_list'][index] || data['data_list'][index] !== value_field.data('list')) {
                      return;
                    }
                  }
                  var titles = [], get_title;
                  if (value_field.data('field') === 'autocomplete') {
                    if (value_field.data('list') === 'product_taxonomies') {
                      titles = wdp_data.titles[value_field.data('taxonomy')];
                    } else {
                      titles = wdp_data.titles[value_field.data('list')];
                    }
                    get_title = function (id) {
                      return typeof titles !== 'undefined' ? titles[id] : id;
                    }
                  } else if (value_field.data('field') === 'preloaded') {
                    titles = wdp_data.lists[value_field.data('list')];
                    get_title = function(id) {
                      for (var i = 0; i < titles.length; i++) {
                        if (titles[i].id.toString() === id) return titles[i].text;
                      }
                      return id;
                    }
                  }

                  if ( typeof data.options[index] === "string" ) {
                    value_field.append('<option selected value="' + data.options[index] + '">' + get_title(data.options[index]) + '</option>');
                  } else {
                    $.each(data.options[index], function (i, val) {
                      value_field.append('<option selected value="' + val + '">' + get_title(val) + '</option>');
                    });
                  }
                }

                value_field = $('select', field);
                if (value_field.length) {
                  if (data['type'] === 'prev_field_values') {
                    if (!data.elem_types[index] || data.elem_types[index] !== 'select') {
                      return;
                    }
                  }
                  value_field.val(data.options[index]);
                }

                value_field = $('input[type="number"]', field);
                if (value_field.length) {
                  if (data['type'] === 'prev_field_values') {
                    if (!data.elem_types[index] || data.elem_types[index] !== 'input_number') {
                      return;
                    }
                  }
                  value_field.val(data.options[index]);
                }

                value_field = $('input[type="text"]', field);
                if (value_field.length) {
                  if (data['type'] === 'prev_field_values') {
                    if (!data.elem_types[index] || data.elem_types[index] !== 'input_text') {
                      return;
                    }
                  }
                  value_field.val(data.options[index]);
                }
              });
        }

        make_select2_products($container.find('[data-field="autocomplete"]'));
        make_select2_preloaded($container.find('[data-field="preloaded"]'));
    }

    function updateDealOption($row, type) {
        var before = '', after = '';
        if (type === 'free') {
            $row.find('.wdp-condition-field-deal-options').hide();
            return;
        } else if (type === 'price__fixed') {
            after = wdp_data.labels.currency_symbol;
        } else if (type === 'discount__percentage') {
            after = '%';
        } else if (type === 'discount__amount') {
            before = '-';
            after = wdp_data.labels.currency_symbol;
        }

        $row.find('.wdp-condition-field-deal-options').show();
        $row.find('.wdp-condition-field-deal-options--before').html(before);
        $row.find('.wdp-condition-field-deal-options--after').html(after);
        $row.find('.wdp-condition-field-deal-options input').val('');
    }

    function update_get_products_options_visibility($rule) {
        var $type_val = $rule.find('.wdp-get-products-repeat select').val();

        if ( $type_val === 'based_on_subtotal' || $type_val === 'based_on_subtotal_after_discount'
          || $type_val === 'based_on_subtotal_inc' || $type_val === 'based_on_subtotal_after_discount_inc' ) {
            $rule.find('.wdp-get-products-repeat .repeat-subtotal').show();
        } else {
            $rule.find('.wdp-get-products-repeat .repeat-subtotal').hide();
        }
    }

  function update_auto_add_options_visibility($rule) {
    var $type_val = $rule.find('.wdp-auto-add-repeat select').val();

    if ( $type_val === 'based_on_subtotal' || $type_val === 'based_on_subtotal_after_discount'
      || $type_val === 'based_on_subtotal_inc' || $type_val === 'based_on_subtotal_after_discount_inc' ) {
      $rule.find('.wdp-auto-add-repeat .repeat-subtotal').show();
    } else {
      $rule.find('.wdp-auto-add-repeat .repeat-subtotal').hide();
    }
  }

    function update_get_products_auto_visibility($rule) {
        var $items = $rule.find('.wdp-get-products .wdp-filter-item'),
            filter_val = $items.find('.wdp-condition-field-value select').val(),
            filter_type = $items.find('.wdp-filter-type').val(),
            deal_type = $items.find('.wdp-condition-field-deal-type select').val();

        var show = $items.length === 1 && filter_type === 'products' && deal_type === 'free' &&
                filter_val && filter_val.length === 1;

        $rule.find('.wdp-get-products-auto').toggle(show);
    }


    /* Utils */
    // find template by id, replace variables by values and return string
        function get_template(name, variables) {
        var template = $('#' + name + '_template').html() || '';
        for (var v in variables) {
            template = template.replace(new RegExp('{' + v + '}', 'g'), variables[v]);
        }
        return template;
    }

        function get_template_by_element(element, variables) {
        var template = element.html() || '';
        for (var v in variables) {
            template = template.replace(new RegExp('{' + v + '}', 'g'), variables[v]);
        }
        return template;
    }

    // find next index for condition row
    function get_new_condition_index($el) {
        var newIndex = 0;

        $el.closest('.postbox').find('.wdp-conditions .wdp-condition').each(function (i, el) {
            var index = ~~ $(el).data('index');
            if (index >= newIndex) newIndex = index + 1;
        });

        return newIndex;
    }

    // returns index rule where eleemnt placed
    function get_current_rule_index($el) {
        return $el.closest('.postbox').data('index');
    }

    // find next index for filter row
    function get_new_product_filter_index($container) {
        var newIndex = 0;

        $container.find('.wdp-filter-item').each(function (i, el) {
            var index = ~~$(el).data('index');
            if (index >= newIndex) newIndex = index + 1;
        });

        return newIndex;
    }

    // returns index of filter where element placed
    function get_current_product_filter_index($el) {
        return $el.closest('.wdp-filter-item').data('index');
    }

    // find next index for limit row
    function get_new_limit_index($el) {
        var newIndex = 0;

        $el.closest('.postbox').find('.wdp-limits .wdp-limit').each(function (i, el) {
            var index = ~~$(el).data('index');
            if (index >= newIndex) newIndex = index + 1;
        });

        return newIndex;
    }

    // find next index for cart adjustment row
    function get_new_cart_adjustment_index($el) {
        var newIndex = 0;

        $el.closest('.postbox').find('.wdp-cart-adjustments .wdp-cart-adjustment').each(function (i, el) {
            var index = ~~$(el).data('index');
            if (index >= newIndex) newIndex = index + 1;
        });

        return newIndex;
    }

	function get_new_role_discount_index( $el ) {
		var newIndex = 0;

		$el.closest( '.postbox' ).find( '.wdp-role-discounts .wdp-role-discount' ).each( function ( i, el ) {
			var index = ~ ~ $( el ).data( 'index' );
			if ( index >= newIndex ) {
				newIndex = index + 1;
			}
		} );

		return newIndex;
	}

    // find next index for range row
    function get_new_range_index($el) {
        var newIndex = 0;

        $el.closest('.postbox').find('.wdp-ranges .wdp-range').each(function (i, el) {
            var index = ~~$(el).data('index');
            if (index >= newIndex) newIndex = index + 1;
        });

        return newIndex;
    }

    function get_last_priority() {
        var newIndex = 0;

        $('#rules-container .postbox').each(function (i, el) {
            var index = ~~ $('.rule-priority', el).val();
            if (index >= newIndex) newIndex = index + 1;
        });

        return newIndex;
    }

    // make select to select2 autocomplete
    function make_select2_products($els) {
        $els.each(function (index, el) {
            var $el = $(el);

	        if ( $el.data( 'list' ) === 'product_taxonomies' ) {
		        return true;
	        }

            $el.select2({
                width: '100%',
				closeOnSelect: wdp_data.options.close_on_select,
				minimumInputLength: 1,
                placeholder: $el.data('placeholder'),
                escapeMarkup: function (text) { return text; },
                language: {
                    errorLoading: function () {
						return wdp_data.labels.select2_error_loading;
					},
					inputTooLong: function (args) {
						var overChars = args.input.length - args.maximum;

						var message = wdp_data.labels.select2_input_too_long.replace('%d', overChars);

						if (overChars != 1) {
							message += 's';
						}

						return message;
					},
					inputTooShort: function (args) {
						var remainingChars = args.minimum - args.input.length;

						var message = wdp_data.labels.select2_input_too_short.replace('%d', remainingChars);

						return message;
						},
						loadingMore: function () {
						return 'Loading more results…';
					},
					maximumSelected: function (args) {
						var message = wdp_data.labels.select2_maximum_selected.replace('%d', args.maximum);

						if (args.maximum != 1) {
							message += 's';
						}

						return message;
					},
					noResults: function () {
						return wdp_data.labels.select2_no_results;
					},
					searching: function () {
						return wdp_data.labels.select2_searching;
					},
                },
                ajax: {
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
						let $rule = $el.closest('.postbox');
                        let new_params = {
                            query: params.term,
                            action: 'wdp_ajax',
                            method: $el.data('list') || 'product',
                            selected: $el.val(),
							current_rule: $rule.find('.rule-id').val()
                        };
                        new_params[wdp_data.security_query_arg] = wdp_data.security;
                        return new_params;
                    },
                    processResults: function (response) {
                        return { results: response.data || [] };
                    }
                },
				templateSelection: function (data) {
					if (!data.id) {
					  return data.text;
					}
					var link = data.link || $(data.element).data('link');

					if(!link) {
						return data.text;
					}

					var $option = $("<span></span>");
					var $preview = $('<a target="_blank" href="' + link + '">' + data.text + '</a>');

					$preview.on('mouseup', function (e) {
						e.stopPropagation();
					});
					
					$option.append($preview);
					return $option;
				  }
            });

            $el.on('select2:select', function (e) {
                var type  = $el.attr('data-list');

                var id    = e.params.data.id;
                var title = e.params.data.text;

                if (typeof e.params.data.bundle !== 'undefined') {
                    var bundle = e.params.data.bundle;
                    var current = $(this).val() ? $(this).val() : [];
                    var exclude = [id];

                    for (var i = 0; i < bundle.length; i++) {
                        id = bundle[i].id;
                        title = bundle[i].text;

                        if (!$(this).find("option[value='" + id + "']").length) {
                            $(this).append(new Option(title, id, false, false));
                            wdp_data.titles[type][id] = title;
                        }

                        if (current.indexOf(id) !== -1) {
                            exclude.push(id);
                        } else {
                            current.push(id);
                        }
                    }

                    current = current.filter(function (item) {
                        return exclude.indexOf(item) === -1;
                    });

                    $(this).val(current).trigger('change');
                } else {
                  if ( typeof wdp_data.titles[type] !== 'undefined') {
                    wdp_data.titles[type][id] = title;
                  }
                }
            });

            $el.parent().find('.select2-search__field').css('width', '100%');
        });
    }

	function make_select2_product_taxonomies($els) {
		$els.each(function (index, el) {
			var $el = $(el);

			$el.select2({
				width: '100%',
				closeOnSelect: wdp_data.options.close_on_select,
				minimumInputLength: 1,
				placeholder: $el.data('placeholder'),
				escapeMarkup: function (text) { return text; },
				language: {
					errorLoading: function () {
						return wdp_data.labels.select2_error_loading;
					},
					inputTooLong: function (args) {
						var overChars = args.input.length - args.maximum;

						var message = wdp_data.labels.select2_input_too_long.replace('%d', overChars);

						if (overChars != 1) {
							message += 's';
						}

						return message;
					},
					inputTooShort: function (args) {
						var remainingChars = args.minimum - args.input.length;

						var message = wdp_data.labels.select2_input_too_short.replace('%d', remainingChars);

						return message;
						},
						loadingMore: function () {
						return 'Loading more results…';
					},
					maximumSelected: function (args) {
						var message = wdp_data.labels.select2_maximum_selected.replace('%d', args.maximum);

						if (args.maximum != 1) {
							message += 's';
						}

						return message;
					},
					noResults: function () {
						return wdp_data.labels.select2_no_results;
					},
					searching: function () {
						return wdp_data.labels.select2_searching;
					},
				},
				ajax: {
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					delay: 250,
					data: function (params) {
						let new_params = {
							query: params.term,
							action: 'wdp_ajax',
							method: $el.data('list') || 'product',
							taxonomy: $el.data('taxonomy') || '',
							selected: $el.val()
                        };

                        new_params[wdp_data.security_query_arg] = wdp_data.security;
                        return new_params;
					},
					processResults: function (response) {
						return { results: response.data || [] };
					}
				}
			});

			$el.on('select2:select', function (e) {
				var type  = $el.attr('data-taxonomy');
				var id    = e.params.data.id;
				var title = e.params.data.text;

				wdp_data.titles[type][id] = title;
			});

			$el.parent().find('.select2-search__field').css('width', '100%');
		});
	}


    function make_select2_preloaded($els) {
        $els.each(function (index, el) {
            var $el = $(el);
            var data = wdp_data.lists[ $el.data('list') ];

            let close_on_select = $el.attr("multiple") !== undefined ? wdp_data.options.close_on_select : true
            let minimumInputLength = $el.attr("multiple") !== undefined ? 1 : 0

            $el.select2({
                width: '100%',
				closeOnSelect: close_on_select,
                escapeMarkup: function (text) { return text; },
                minimumInputLength: minimumInputLength,
                placeholder: $el.data('placeholder'),
                language: {
                    errorLoading: function () {
						return wdp_data.labels.select2_error_loading;
					},
					inputTooLong: function (args) {
						var overChars = args.input.length - args.maximum;

						var message = wdp_data.labels.select2_input_too_long.replace('%d', overChars);

						if (overChars != 1) {
							message += 's';
						}

						return message;
					},
					inputTooShort: function (args) {
						var remainingChars = args.minimum - args.input.length;

						var message = wdp_data.labels.select2_input_too_short.replace('%d', remainingChars);

						return message;
						},
						loadingMore: function () {
						return 'Loading more results…';
					},
					maximumSelected: function (args) {
						var message = wdp_data.labels.select2_maximum_selected.replace('%d', args.maximum);

						if (args.maximum != 1) {
							message += 's';
						}

						return message;
					},
					noResults: function () {
						return wdp_data.labels.select2_no_results;
					},
					searching: function () {
						return wdp_data.labels.select2_searching;
					},
                },
                data: data
            });

            $el.parent().find('.select2-search__field').css('width', '100%');
        });
    }

    // make select to select2 with tags
    function make_select2_tags($els) {
        $els.each(function (index, el) {
            var $el = $(el);
            $el.select2({ width: '100%' });
            $el.parent().find('.select2-search__field').css('width', '100%');
        });
    }

    // update rule title
    function update_rule_title($rule) {
        var title = $rule.find('h2.hndle.ui-sortable-handle > span');

        // check if rule enabled
        var $toggler = $rule.find('.wdp-field-enabled select');
        var disabled = $toggler.val() === 'off';
        title.toggleClass('wdp-title-disabled', disabled);
        $rule.toggleClass('disabled', disabled);

        // check if bulk rule repeated
        var repeat = false;
        var $checkbox_repeat = $rule.find('.wdp-adjustments-repeat');
        if ($checkbox_repeat.length) {
            repeat = $checkbox_repeat.prop('checked');
        }
        title.toggleClass('wdp-title-repeat', repeat);

        // check if cart rule provide discount or fee
        var cart_adjustment_type = $rule.find('.cart-adjustment-type ').val() || '';
        title.toggleClass('wdp-title-discount', cart_adjustment_type.indexOf('discount') === 0);
        title.toggleClass('wdp-title-fee', cart_adjustment_type.indexOf('fee') === 0);
    }

    // make lists inside rule sortable
    function make_sortable($container) {
        $container.sortable({
            containment: 'parent',
            items: '.wdp-row',
            cursor: 'move',
            axis:   'y',
            opacity: 0.65
        });
    }

    function updateElementsVisisibilyInRowForElementValue(value, $container) {
        var $row_elements = $container.find('[data-show-if]');
        $row_elements.each(function (i, el) {
            var $el = $(el);
            var show_if = $el.data('show-if').split(',');
            var visible = show_if.indexOf(value) >= 0;
            $el.toggle( visible );

            if (!visible) {
                // flushInputs($el);
            }
        });
    }

    function flushInputs($container) {
        $container.find('input:not([data-readonly]), select:not([data-readonly]), textarea:not([data-readonly])').val('');
    }

    $('.hide-disabled-rules').change(function () {
        var checked = $(this).prop('checked');
        remove_get_parameter('hide_inactive');
        remove_get_parameter('paged');

        if (checked) {
            window.location.href += '&hide_inactive=1';
        } else {
            window.location.reload();
        }
        // $('#rules-container').toggleClass('hide-disabled', $(this).val() );
    });

	$('.hide-rules-coupons-applied').change(function () {
		var checked = $(this).prop('checked');
		var result  = checked === true ? 1 : 0;
		remove_get_parameter('disable_all_rules_coupon_applied');
		remove_get_parameter('paged');

		window.location.href += '&disable_all_rules_coupon_applied=' + result;
	});

    function remove_get_parameter(parameterName) {
        var result = null,
            clean_uri = null,
            tmp = [];

        location.search
            .substr(1)
            .split("&")
            .forEach(function (item) {
                tmp = item.split("=");
                if (tmp[0] === parameterName) {
                    result = decodeURIComponent(tmp[1]);
                    clean_uri = window.location.toString().replace("&" + tmp[0] + "=" + tmp[1], "");
                    clean_uri = clean_uri.replace(tmp[0] + "=" + tmp[1], "");
                    clean_uri = clean_uri.replace(/\?$/ig, "");
                }
            });

        if (result && clean_uri) {
            window.history.replaceState({}, document.title, clean_uri)
        }
        return result;
    };

    function deparam(params){

        var digitTest = /^\d+$/,
            keyBreaker = /([^\[\]]+)|(\[\])/g,
            plus = /\+/g,
            paramTest = /([^?#]*)(#.*)?$/;

        if(! params || ! paramTest.test(params) ) {
            return {};
        }


        var data = {},
            pairs = params.split('&'),
            current;

        for(var i=0; i < pairs.length; i++){
            current = data;
            var pair = pairs[i].split('=');

            // if we find foo=1+1=2
            if(pair.length != 2) {
                pair = [pair[0], pair.slice(1).join("=")]
            }

            var key = decodeURIComponent(pair[0].replace(plus, " ")),
                value = decodeURIComponent(pair[1].replace(plus, " ")),
                parts = key.match(keyBreaker);

            for ( var j = 0; j < parts.length - 1; j++ ) {
                var part = parts[j];
                if (!current[part] ) {
                    // if what we are pointing to looks like an array
                    current[part] = digitTest.test(parts[j+1]) || parts[j+1] == "[]" ? [] : {}
                }
                current = current[part];
            }
            lastPart = parts[parts.length - 1];
            if(lastPart == "[]"){
                current.push(value)
            }else{
                current[lastPart] = value;
            }
        }
        return data;
    }

    function get_role_label(id) {
        var roles_list = wdp_data.lists.user_roles;

        for (var i = 0; i < roles_list.length; i++) {
            if (typeof roles_list[i] !== 'undefined' && roles_list[i].id === id) {
                return roles_list[i].text;
            }
        }

        return id
    }

    function updateElementsVisibilityDiscountSplit($container, $rule) {
      let totalTypeValue = $container.find('.adjustment-total-type').val()
      let perItemDiscountSelected = totalTypeValue === 'discount__amount_per_item' || totalTypeValue === 'price__fixed_per_item'

      if ( isPackageRule($rule) ) {
        $container.find('.adjustment-total-type option[value="discount__amount_per_item"]').show();
        $container.find('.adjustment-total-type option[value="price__fixed_per_item"]').show();

        if ( perItemDiscountSelected ) {
          $container.find(".split-discount-controls").hide();
        } else {
          $container.find(".split-discount-controls").show();
        }
      } else{
        $container.find(".split-discount-controls").hide();

        if ( perItemDiscountSelected ) {
          $rule.find('.adjustment-total-type').find('option:first-child').prop("selected", "selected");
        }
        $container.find('.adjustment-total-type option[value="discount__amount_per_item"]').hide();
        $container.find('.adjustment-total-type option[value="price__fixed_per_item"]').hide();
      }
    }

  function isPackageRule($container) {
    let $product_filter_container = $container.find('.wdp-filter-block');
    let filters = $product_filter_container.find('.wdp-filter-item');

    if (filters.length === 1) {
      let filter = filters.first()
      let qty = filter.find('.wdp-condition-field-qty input').val();
      let qtyEnd = filter.find('.wdp-condition-field-qty-end input').val();

      return parseInt(qty) > 1 || (qtyEnd !== undefined && parseInt(qtyEnd) > 1);
    } else if (filters.length > 1) {
      return true
    }

    return false;
  }

  function reload_template_rule(data, el, template) {
        window.wdpPreloadRule = true;

        let rule_type = (data && data.rule_type) ? data.rule_type : (data ? (data.exclusive === "1" ? 'exclusive' : 'persistent') : 'common');
        var template_options = {
            c: 0,
            p: (data && data.priority) ? data.priority : get_last_priority(),
            rule_type: rule_type,
        };

        // prepare template
        var rule_template = get_template(template, template_options);
        if (rule_template === '') {
	    rule_template = get_template('rule', template_options);
	}

        var new_rule = $(rule_template);

        preAddEventHandlersToRule(new_rule, data);

        // add new rule to rules list
        el[0].replaceWith(new_rule[0]);

    if (rule_type === 'persistent') {
      if (typeof data.filters !== 'undefined') {
        if (data.filters.length > 1) {
          data.filters = [data.filters[0]];
        }
      } else {
        data.filters = [];
      }

      if ( typeof data.product_adjustments.type !== 'undefined' ) {
        data.product_adjustments.type = "total";
      }
    }

        preSetRuleData(new_rule, data);

        window.wdpPreloadRule = false;

        return new_rule;
    }

  function disableControls() {
    $("#rules-action-controls button").attr("disabled", "disabled");
  }

  function enableControls() {
    $("#rules-action-controls button").removeAttr("disabled");
  }

  function isInViewportPaginators(element) {
    const rect = element.getBoundingClientRect();
    return (
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  let paginators = document.getElementsByClassName('tablenav-pages');
  paginators[1].parentElement.style.display = isInViewportPaginators(paginators[0]) ? 'none' : 'block';

  window.addEventListener('scroll', function(e) {

    let paginators = document.getElementsByClassName('tablenav-pages');
    paginators[1].parentElement.style.display = isInViewportPaginators(paginators[0]) ? 'none' : 'block';

  });

  function isInViewportBtns(element) {
    const rect = element.getBoundingClientRect();
    return (
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  let btns = document.getElementsByClassName('add-rule');
  btns[1].parentElement.style.display = isInViewportBtns(btns[0]) ? 'none' : 'block';

  window.addEventListener('scroll', function(e) {

    let btns = document.getElementsByClassName('add-rule');
    btns[1].parentElement.style.display = isInViewportBtns(btns[0]) ? 'none' : 'block';

  });

  $('.wdp-rebuild-run').click(function () {
    progressParentBlock.start();
    progress.start();

    $("#progress_div").show();
    disableControls();
    $("#rules-action-controls button").attr("disabled", "disabled");

    let props = {
      action: 'wdp_ajax',
      method: 'start_partial_' + $('select[name=recalculace_selector]').val(),
    };
    props[wdp_data.security_query_arg] = wdp_data.security;

    let requestPromise = $.post({
      url: ajaxurl,
      data: props,
      dataType: 'json'
    });

    async function runPartial(data, textStatus, jqXHR) {
      let totalCount = typeof data.data !== 'undefined' && typeof data.data.count !== 'undefined' ? data.data.count : null;

      if (!totalCount) {
        progress.setProgress(100);
        $("#progress_div").hide();
        enableControls();
        return;
      }

      const PAGE_SIZE = 100;

      for (let i = 0; i < totalCount; i += PAGE_SIZE) {
        let props = {
          action: 'wdp_ajax',
          method: 'partial_' + $('select[name=recalculace_selector]').val(),
          from: i,
          count: PAGE_SIZE
        };
        props[wdp_data.security_query_arg] = wdp_data.security;

        let responseData = await $.post({
          url: ajaxurl,
          data: props,
          dataType: 'json',
        });

        if ( typeof responseData.success === 'undefined' || !responseData.success) {
          let msg = "The operation is failed";
          if ( typeof responseData.data !== 'undefined' && responseData.data ) {
            msg += ' : ' + responseData.data;
          }
          progressParentBlock.setNotification(msg, true, 3.5);

          $("#progress_div").hide();
          enableControls();
          return;
        }

        let percent = ((i + PAGE_SIZE) < totalCount) ? ((i + PAGE_SIZE) / totalCount) * 100 : 100;
        percent = Math.round(percent);
        progress.setProgress(percent);
      }
      progressParentBlock.setNotification('The operation is completed', false, 2.5);

      $("#progress_div").hide();
      enableControls();
    }

    requestPromise.then(runPartial);
  });

  $('#bulk-action').submit(function (e) {
    let form = $(this)
    $('.bulk-action-mark').each(function () {
      if ($(this).prop('checked') === false) {
        return true
      }
      let ruleId = $(this).closest('.postbox').find('input.rule-id').val()
      let input = $('<input>')
        .attr('type', 'hidden')
        .attr('name', 'rules[]').val(ruleId)
      form.append(input)
    })
  })

  $('#bulk-action-select-all').change(function(e) {
    let active = $(this).prop('checked') === true;

    $('.bulk-action-mark').each(function () {
      $(this).prop('checked', active).trigger('change')
    });
  })

  let ProgressBar = (function () {
    function ProgressBar(element) {
      this.el = element;
      this.subscribers = [];
      this.init();
    }

    ProgressBar.prototype.init = function () {
      this.el.css("width", "100%")
        .css("background", "#292929")
        .css("border", "1px solid #111")
        .css("border-radius", "5px")
        .css("overflow", "hidden")
        .css("box-shadow", "0 0 5px #333")
        .css("float", "right");

      let subElement = $("<div></div>");
      subElement.css("height", "100%")
        .css("color", "#fff")
        .css("text-align", "right")
        .css("font-size", "12px")
        .css("line-height", "22px")
        .css("text-align", "right")
        .css("background-color", "#1a82f7")
        .css("background", "-webkit-gradient(linear, 0% 0%, 0% 100%, from(#0099FF), to(#1a82f7))")
        .css("background", "-webkit-linear-gradient(top, #0099FF, #1a82f7)")
        .css("background", "-moz-linear-gradient(top, #0099FF, #1a82f7)")
        .css("background", "-ms-linear-gradient(top, #0099FF, #1a82f7)")
        .css("background", "-o-linear-gradient(top, #0099FF, #1a82f7)");

      this.el.append(subElement);
      this.setProgress(0);
    }

    ProgressBar.prototype.start = function () {
      this.setProgress(0);
    }

    ProgressBar.prototype.finish = function () {
      this.setProgress(100);
    }

    ProgressBar.prototype.setProgress = function (percent) {
      if (percent === 0) {
        this.el.find('div').html(percent + "%&nbsp;").animate({width: 0}, 0);
      } else {
        var progressBarWidth = percent * this.el.width() / 100;
        this.el.find('div').html(percent + "%&nbsp;").animate({width: progressBarWidth}, 200);
      }

      this.__notify(percent);
    }

    ProgressBar.prototype.addSubscriber = function (sub) {
      this.subscribers.push(sub);
    }

    ProgressBar.prototype.__notify = function (percent) {
      for (let i in this.subscribers) {
        let subscriber = this.subscribers[i];
        subscriber.react(percent);
      }
    }

    return ProgressBar;
  })();

  let ProgressBarBlock = (function () {
    function ProgressBarBlock(element) {
      this.el = element;
      this.el.hide();
      this.hideTimer = null;
    }

    ProgressBarBlock.prototype.react = function (percent) {
      if (this.hideTimer) {
        clearTimeout(this.hideTimer);
        this.hideTimer = null;
      }

      if (percent === 100) {
        let that = this;
        this.hideTimer = setTimeout(function () {
          that.el.hide();
        }, 2000);
      }
    }

    ProgressBarBlock.prototype.start = function () {
      this.el.show();
    }

    ProgressBarBlock.prototype.setError = function (msg) {
      let errorEl = $(`<div id="progress-error-msg" style="color: red;">${msg}</div>`);
      this.el.append(errorEl);

      let that = this;
      setTimeout(function () {
        that.el.hide();
        errorEl.remove();
      }, 2000);
    }

    ProgressBarBlock.prototype.setNotification = function (msg, isErr, durationSec) {
      let notificationEl = $(`<div id="progress-notification-msg" style="color: ${isErr ? 'red' : 'green'};">${msg}</div>`);
      this.el.append(notificationEl);

      let that = this;
      setTimeout(function () {
        that.el.hide();
        notificationEl.remove();
      }, durationSec * 1000);
    }

    return ProgressBarBlock;
  })();

  let progress = new ProgressBar($("#progressBar"));
  let progressParentBlock = new ProgressBarBlock($("#progressBarBlock"));
  progress.addSubscriber(progressParentBlock);

  $('#bulk-action').submit(function (e) {
    if ($('#bulk-action-selector').val() === 'delete') {
      return confirm(wdp_data.labels.are_you_sure_to_delete_selected_rules)
    }
  })
});

let RuleBlocks = (function () {
  function RuleBlocks (element) {
    this.PRODUCT_FILTERS = 'productFilters'
    this.PRODUCT_DISCOUNTS = 'productDiscounts'
    this.ROLE_DISCOUNTS = 'roleDiscounts'
    this.BULK_DISCOUNTS = 'bulkDiscounts'
    this.FREE_PRODUCTS = 'freeProducts'
    this.AUTO_ADD_TO_CART = 'autoAddToCart'
    this.ADVERTISING = 'advertising'
    this.CONDITION_MESSAGE = 'conditionMessage'
    this.CART_ADJUSTMENTS = 'cartAdjustments'
    this.CONDITIONS = 'conditions'
    this.LIMITS = 'limits'

    this.__el = element
  }

  RuleBlocks.prototype.applyPreloadedData = function (data) {
    if (typeof data === 'undefined' || typeof data.additional === 'undefined') {
      return
    }

    if (data.additional.blocks && data.additional.blocks.productFilters) {
      this.setProductFiltersOpen(data.additional.blocks.productFilters.isOpen === '1')
    } else {
      let isNotEmpty = Array.isArray(data.filters) && data.filters.length
      this.setProductFiltersOpen(isNotEmpty)
    }

    if (data.additional.blocks && data.additional.blocks.productDiscounts) {
      this.setProductDiscountsOpen(data.additional.blocks.productDiscounts.isOpen === '1')
    } else {
      let typeIsNotEmpty = data.product_adjustments && data.product_adjustments.type
      let totalAdjustmentsIsNotEmpty = typeIsNotEmpty && 'total' === data.product_adjustments.type && data.product_adjustments['total']['type']
      let splitAdjustmentsIsNotEmpty = typeIsNotEmpty && 'split' === data.product_adjustments.type && typeof data.product_adjustments['split'][0] !== 'undefined' && data.product_adjustments['split'][0]['type']
      this.setProductDiscountsOpen(totalAdjustmentsIsNotEmpty || splitAdjustmentsIsNotEmpty)
    }

    if (data.additional.blocks && data.additional.blocks.roleDiscounts) {
      this.setRoleDiscountsOpen(data.additional.blocks.roleDiscounts.isOpen === '1')
    } else {
      let rangesIsNotEmpty = data.role_discounts && data.role_discounts.rows
      this.setRoleDiscountsOpen(rangesIsNotEmpty)
    }

    if (data.additional.blocks && data.additional.blocks.bulkDiscounts) {
      this.setBulkDiscountsOpen(data.additional.blocks.bulkDiscounts.isOpen === '1')
    } else {
      let rangesIsNotEmpty = data.bulk_adjustments && data.bulk_adjustments.ranges
      this.setBulkDiscountsOpen(rangesIsNotEmpty)
    }

    if (data.additional.blocks && data.additional.blocks.freeProducts) {
      this.setFreeProductsOpen(data.additional.blocks.freeProducts.isOpen === '1')
    } else {
      let valueIsNotEmpty = data.get_products && data.get_products.value
      this.setFreeProductsOpen(valueIsNotEmpty)
    }

    if (data.additional.blocks && data.additional.blocks.autoAddToCart) {
      this.setAutoAddToCartOpen(data.additional.blocks.autoAddToCart.isOpen === '1')
    } else {
      let valueIsNotEmpty = data.auto_add_products && data.auto_add_products.value
      this.setAutoAddToCartOpen(valueIsNotEmpty)
    }

    if (data.additional.blocks && data.additional.blocks.cartAdjustments) {
      this.setCartAdjustmentsOpen(data.additional.blocks.cartAdjustments.isOpen === '1')
    } else {
      let isNotEmpty = Array.isArray(data.cart_adjustments) && data.conditions.cart_adjustments
      this.setCartAdjustmentsOpen(isNotEmpty)
    }

    if (data.additional.blocks && data.additional.blocks.advertising) {
      this.setAdvertisingOpen(data.additional.blocks.advertising.isOpen === '1')
    } else {
      let atLeastOneAdvertisingFieldFilled = false
      if (data.advertising) {
        Object.keys(data.advertising).forEach(key => {
          if (data.advertising[key]) {
            atLeastOneAdvertisingFieldFilled = true
            return true
          }
        })
      }

      this.setAdvertisingOpen(atLeastOneAdvertisingFieldFilled)
    }

    if (data.additional.blocks && data.additional.blocks.conditions) {
      this.setConditionsOpen(data.additional.blocks.conditions.isOpen === '1')
    } else {
      let isNotEmpty = Array.isArray(data.conditions) && data.conditions.length
      this.setConditionsOpen(isNotEmpty)
    }

    if (data.additional.blocks && data.additional.blocks.limits) {
      this.setLimitsOpen(data.additional.blocks.limits.isOpen === '1')
    } else {
      let isNotEmpty = Array.isArray(data.limits) && data.limits.length
      this.setLimitsOpen(isNotEmpty)
    }

    if (data.additional.blocks && data.additional.blocks.conditionMessage) {
      this.setConditionMessageOpen(data.additional.blocks.conditionMessage.isOpen === '1')
    } else {
      let conditionMessageTypeIsNotEmpty = data.condition_message && data.condition_message.type
      let totalConditionMessageIsNotEmpty = conditionMessageTypeIsNotEmpty && 'total' === data.condition_message.type && data.condition_message['total']['message']
      let splitConditionMessageIsNotEmpty = conditionMessageTypeIsNotEmpty && 'split' === data.condition_message.type && typeof data.condition_message['split'][0] !== 'undefined' && data.condition_message['split'][0]['message']
      this.setConditionMessageOpen(totalConditionMessageIsNotEmpty || splitConditionMessageIsNotEmpty)
    }
  }

  RuleBlocks.prototype.updateView = function () {
    if (this.isOpen(this.PRODUCT_FILTERS)) {
      this.__el.find('.wdp-filter-block').show()
    } else {
      this.__el.find('.wdp-filter-block').hide()
    }

    if (this.isOpen(this.PRODUCT_DISCOUNTS)) {
      this.__el.find('.wdp-product-adjustments').show()
    } else {
      this.__el.find('.wdp-product-adjustments').hide()
    }

    if (this.isOpen(this.CART_ADJUSTMENTS)) {
      this.__el.find('.wdp-cart-adjustments').show()
    } else {
      this.__el.find('.wdp-cart-adjustments').hide()
    }

    if (this.isOpen(this.ROLE_DISCOUNTS)) {
      this.__el.find('.wdp-role-discounts').show()
    } else {
      this.__el.find('.wdp-role-discounts').hide()
    }

    if (this.isOpen(this.BULK_DISCOUNTS)) {
      this.__el.find('.wdp-bulk-adjustments').show()
    } else {
      this.__el.find('.wdp-bulk-adjustments').hide()
    }

    if (this.isOpen(this.AUTO_ADD_TO_CART)) {
      this.__el.find('.wdp-auto-add-block').show()
    } else {
      this.__el.find('.wdp-auto-add-block').hide()
    }

    if (this.isOpen(this.FREE_PRODUCTS)) {
      this.__el.find('.wdp-get-products-block').show()
    } else {
      this.__el.find('.wdp-get-products-block').hide()
    }

    if (this.isOpen(this.ADVERTISING)) {
      this.__el.find('.wdp-discount-messages').show()
    } else {
      this.__el.find('.wdp-discount-messages').hide()
    }

    if (this.isOpen(this.CONDITION_MESSAGE)) {
      this.__el.find('.wdp-condition-message').show()
    } else {
      this.__el.find('.wdp-condition-message').hide()
    }

    if (this.isOpen(this.CONDITIONS)) {
      this.__el.find('.wdp-conditions').show()
    } else {
      this.__el.find('.wdp-conditions').hide()
    }

    if (this.isOpen(this.LIMITS)) {
      this.__el.find('.wdp-limits').show()
    } else {
      this.__el.find('.wdp-limits').hide()
    }
  }

  RuleBlocks.prototype.setOpen = function (block, value) {
    this.__el.find(`input[name='rule[additional][blocks][${block}][isOpen]']`).val(!!value ? '1' : '0')
  }

  RuleBlocks.prototype.isOpen = function (block) {
    return this.__el.find(`input[name='rule[additional][blocks][${block}][isOpen]']`).val() === '1'
  }

  RuleBlocks.prototype.setProductFiltersOpen = function (value) {
    this.setOpen(this.PRODUCT_FILTERS, value)
  }

  RuleBlocks.prototype.isProductFiltersOpen = function () {
    return this.isOpen(this.PRODUCT_FILTERS)
  }

  RuleBlocks.prototype.setProductDiscountsOpen = function (value) {
    this.setOpen(this.PRODUCT_DISCOUNTS, value)
  }

  RuleBlocks.prototype.isProductDiscountsOpen = function () {
    return this.isOpen(this.PRODUCT_DISCOUNTS)
  }

  RuleBlocks.prototype.setRoleDiscountsOpen = function (value) {
    this.setOpen(this.ROLE_DISCOUNTS, value)
  }

  RuleBlocks.prototype.isRoleDiscountsOpen = function () {
    return this.isOpen(this.ROLE_DISCOUNTS)
  }

  RuleBlocks.prototype.setBulkDiscountsOpen = function (value) {
    this.setOpen(this.BULK_DISCOUNTS, value)
  }

  RuleBlocks.prototype.isBulkDiscountsOpen = function () {
    return this.isOpen(this.BULK_DISCOUNTS)
  }

  RuleBlocks.prototype.setFreeProductsOpen = function (value) {
    this.setOpen(this.FREE_PRODUCTS, value)
  }

  RuleBlocks.prototype.isFreeProductsOpen = function () {
    return this.isOpen(this.FREE_PRODUCTS)
  }

  RuleBlocks.prototype.setAutoAddToCartOpen = function (value) {
    this.setOpen(this.AUTO_ADD_TO_CART, value)
  }

  RuleBlocks.prototype.isAutoAddToCartOpen = function () {
    return this.isOpen(this.AUTO_ADD_TO_CART)
  }

  RuleBlocks.prototype.setAdvertisingOpen = function (value) {
    this.setOpen(this.ADVERTISING, value)
  }

  RuleBlocks.prototype.isAdvertisingOpen = function () {
    return this.isOpen(this.ADVERTISING)
  }

  RuleBlocks.prototype.setConditionMessageOpen = function (value) {
    this.setOpen(this.CONDITION_MESSAGE, value)
  }

  RuleBlocks.prototype.isConditionMessageOpen = function () {
    return this.isOpen(this.CONDITION_MESSAGE)
  }

  RuleBlocks.prototype.setCartAdjustmentsOpen = function (value) {
    this.setOpen(this.CART_ADJUSTMENTS, value)
  }

  RuleBlocks.prototype.isCartAdjustmentsOpen = function () {
    return this.isOpen(this.CART_ADJUSTMENTS)
  }

  RuleBlocks.prototype.setConditionsOpen = function (value) {
    this.setOpen(this.CONDITIONS, value)
  }

  RuleBlocks.prototype.isConditionsOpen = function () {
    return this.isOpen(this.CONDITIONS)
  }

  RuleBlocks.prototype.setLimitsOpen = function (value) {
    this.setOpen(this.LIMITS, value)
  }

  RuleBlocks.prototype.isLimitsOpen = function () {
    return this.isOpen(this.LIMITS)
  }

  return RuleBlocks
})()
