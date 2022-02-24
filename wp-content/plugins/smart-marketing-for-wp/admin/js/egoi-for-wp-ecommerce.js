jQuery(document).ready(function() {
    (function ($) {
        const idgoi = 'idgoi';

        const LIMIT = 100;

        var current_request = 0;
        var requests_needed = 0;

        var anim            = 300;
        var loader          = $('#egoi-loader');
        var loader_nr_prod  = $('#egoi-loader-products');

        var modal_import    = $('#importModal');
        var modal_delete    = $('#confirmDeleteModal');

        var sync_catalog        = $('.sync_catalog');
        var force_catalog       = $('.force_catalog');
        var remove_catalog      = $('.remove_catalog');
        var new_catalog_page    = $('#new_catalog_page');
        var ajaxObj             = egoi_config_ajax_object_ecommerce;
        var scopeAjaxSync;

        var table               = $('.smsnf-table >tbody');

        //importation
        var selected_catalog    = $('#selected-import-catalog');
        var display_selected    = $('#display-selected');
        var start_import_btn    = $('#start-import-catalog');
        var number_products     = $('#display-number-products');
        var left_products       = $('#egoi-left-products');
        var import_loader_div   = $('#loading-import');
        var progressbar_import  = $('#progressbar-import');

        //delete
        var s_delete_catalog    = $('#selected-delete-catalog');
        var verified_delete     = $('#verified-delete-catalog');
        var to_delete;

        //successs messsage
        var egoi_success = $('#egoi-success');
        var egoi_success_message  = $('#egoi-success-message');

        //error message
        var egoi_alert          = $('#egoi-alert');
        var egoi_alert_message  = $('#egoi-alert-message');
        var close               = $(".egoi-simple-close-x");


        sync_catalog.change(function () {
            syncCatalog(getCatalogsToSync());
        });

        verified_delete.on('click', function(){
            deleteCatalog(s_delete_catalog.val(),to_delete);
        });

        modal_import.on('hidden.bs.modal', function () {
            start_import_btn.attr("disabled", true);
            resetProgressBar();
            import_loader_div.hide();
        });

        force_catalog.on('click', function () {
            var thisel = $(this);
            thisel.removeClass('egoi-pulsating');
            $('.smsnf-notification').hide(anim);
            var id = thisel.attr(idgoi);
            selected_catalog.val(id);
            display_selected.text(id);
            getCountAjax(id);
            modal_import.modal('show');
        });

        start_import_btn.on('click', function(){
            start_import_btn.attr("disabled", true);
            import_loader_div.show();
            forceImport(selected_catalog.val());
            console.log('importing catalog: '+selected_catalog.val());
        });

        remove_catalog.on('click', function () {
            s_delete_catalog.val($(this).attr(idgoi));
            modal_delete.modal('show');
            to_delete = (this);
            //deleteCatalog($(this).attr(idgoi), (this));
        });

        close.on('click', function () {
            $($($(this).parent()[0]).parent()[0]).hide();
        });

        new_catalog_page.on('click', function () {
            window.location.href = window.location.href + "&subpage=new_catalog";
        });

        function getCatalogsToSync(){
            var catalogsArr = [];
            sync_catalog.each(function () {
                if($(this).is(":checked"))
                    catalogsArr.push($(this).attr(idgoi))
            });
            return catalogsArr;
        }

        function getCountAjax(id){
            loader_nr_prod.show();
            number_products.text('');
            left_products.text('');
            $.get(ajaxObj.ajax_url, {action: 'egoi_count_products',catalog: id}, function(response) {
                loader_nr_prod.hide();
                response = parseResponse(response);
                if(response === false)
                    return false;
                number_products.text(response);
                left_products.text(response);
                current_request = 0;
                requests_needed = Math.ceil(parseInt(response)/LIMIT);
                start_import_btn.attr("disabled", false);
            });
        }

        function leftProductsCalc(){
            var now = parseInt(left_products.text());
            now -= LIMIT;
            if(now < 0){
                now = 0;
            }
            left_products.text(now);
        }

        function deleteCatalog(id,obj){
            var data = {
                security:       ajaxObj.ajax_nonce,
                action:         'egoi_delete_catalog',
                id:             id
            };

            loader.show();
            $(obj).attr("disabled", true);
            $.post(ajaxObj.ajax_url, data, function(response) {
                loader.hide();
                $(obj).attr("disabled", false);
                response = parseResponse(response);
                if(response === false)
                    return false;
                $($($(obj).parent()[0]).parent()[0]).remove();
                setTimeout(function () {
                    sync_catalog.trigger("change");
                    if(table.children().length === 'undefined' || table.children().length < 1)
                        location.reload();
                },200);
            });
        }

        function syncCatalog(data,obj){
            var data = {
                security:       ajaxObj.ajax_nonce,
                action:         'egoi_sync_catalog',
                data:           data
            };
            
            if(typeof scopeAjaxSync != "undefined")
                scopeAjaxSync.abort();
            loader.show();
            $(obj).attr("disabled", true);
            scopeAjaxSync = $.post(ajaxObj.ajax_url, data, function(response) {
                loader.hide();
                $(obj).attr("disabled", false);
                response = parseResponse(response);
                if(response === false)
                    return false;
                return true;
            });
        }

        function forceImport(id,message = '') {
            if(current_request >= requests_needed){//finish
                //DONE!
                setTimeout(function () {
                    import_loader_div.hide(anim);
                    resetProgressBar();
                    modal_import.modal('toggle');
                    displaySuccess(message)
                }, 1000);
                sync_catalog.each(function () {
                    if($(this).attr(idgoi) == id){
                        $(this).prop('checked', true);
                        $(this).trigger("change");
                    }
                });
                return true;
            }
            var data = {
                security:       ajaxObj.ajax_nonce,
                action:         'egoi_force_import_catalog',
                id:             id,
                page:           current_request++
            };
            $.post(ajaxObj.ajax_url, data, function(response) {
                response = parseResponse(response);
                if(response === false)
                    return false;
                if(requests_needed == 0){
                    requests_needed = 1;
                }
                setProgressBarPercent(1/requests_needed*100);
                leftProductsCalc();
                forceImport(id,response);
            });
        }

        function parseResponse(response){
            response = jsonParserLit(response);
            if(typeof response.success != 'undefined' && response.success===false){
                displayError(response.data);
                modal_import.modal('hide');
                return false;
            }
            return response.data;
        }

        function displayError($message){
            egoi_alert_message.text($message);
            console.error($message);
            egoi_alert.show(anim);
        }

        function displaySuccess($message){
            egoi_success_message.text($message);
            egoi_success.show(anim);
        }

        function jsonParserLit(data){
            if(typeof data == "string")
                return JSON.parse(data);
            else
                return  data;
        }

        function setProgressBarPercent(progresss){
            var now = progressbar_import.width() / progressbar_import.parent().width() * 100;//.replace('%','');
            console.log('adding %s percent. now %s',progresss,now);
            if((parseInt(now) + progresss) >= 100)
                progresss = 100;
            else
                progresss += now;
            progressbar_import.width(progresss+'%');
        }

        function resetProgressBar(){
            console.log('reset');
            progressbar_import.css('width','0%');
        }

    })(jQuery);
});