jQuery(document).ready(function ($) {
    'use strict';

    const abandonedCartList = {
        init() {
            this.selectAllRecord();
            this.viewReminderLogs();
            this.viewDetail();
            this.sendReminderManual();
            this.removeRecord();
        },

        selectAllRecord() {
            $('.wacv-check-all').on('click', function () {
                $("input[type=checkbox]").prop('checked', $(this).prop('checked'));
            });
        },

        viewReminderLogs() {
            function display_email_history(item) {
                let sent_time, clicked, opened;

                if (item.type === 'messenger') {
                    sent_time = item.sent_time ? `<li class="email-sent">Sent to messenger: ${item.sent_time}</li>` : '';
                    opened = item.opened ? `<li class="email-opened">Opened: ${item.opened}</li>` : '';
                    clicked = item.clicked ? `<li class="email-clicked">Clicked link: ${item.clicked}</li>` : '';
                } else if (item.type === 'email') {
                    sent_time = item.sent_time ? `<li class="email-sent">Sent to email: ${item.sent_time}</li>` : '';
                    opened = item.opened ? `<li class="email-opened">Opened email: ${item.opened}</li>` : '';
                    clicked = item.clicked ? `<li class="email-clicked">Clicked link: ${item.clicked}</li>` : '';
                } else if (item.type === 'sms_cart') {
                    sent_time = item.sent_time ? `<li class="email-sent">Sent to sms: ${item.sent_time}</li>` : '';
                    opened = item.opened ? `<li class="email-opened">Opened sms: ${item.opened}</li>` : '';
                    clicked = item.clicked ? `<li class="email-clicked">Clicked link: ${item.clicked}</li>` : '';
                }

                return sent_time + opened + clicked;
            }

            $('.wacv-get-logs').on('click', function () {
                let data = {action: 'wacv_get_email_history', id: $(this).attr('data-id')};
                $.ajax({
                    url: wacv_ls.ajax_url,
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    beforeSend() {
                        $('.wacv-get-logs.' + data.id + ' .wacv-loading.icon').addClass('circle notch loading');
                    },
                    success(res) {
                        let target = $('.wacv-email-reminder-popup.' + data.id);
                        if (res.length === 0) {
                            let html = '<li>No history</li>';
                            target.html('<ul style="width: fit-content">' + html + '</ul>').css({
                                'background-color': 'white',
                                'border': '1px solid #eee'
                            });
                        } else {
                            let html = res.map(display_email_history).join('');
                            if (html.length !== 0) {
                                target.html('<ul style="width:fit-content">' + html + '</ul>').css({
                                    'background-color': 'white',
                                    'border': '1px solid #eee'
                                });
                            }
                        }
                    },
                    error(res) {
                    }
                }).complete(function () {
                    $('.wacv-get-logs.' + data.id + ' i').removeClass('circle notch loading');
                });
            });
        },

        viewDetail() {
            //Load abandonded cart  detail
            $('.wacv-get-abd-cart-detail').on('click', function () {
                let id = $(this).attr('data-id');
                $.ajax({
                    url: wacv_ls.ajax_url,
                    type: 'post',
                    data: {action: 'wacv_get_abd_cart_detail', id: id},
                    beforeSend() {
                        $('.wacv-get-abd-cart-detail.' + id + ' i').addClass('circle notch loading');
                    },
                    complete() {
                        $('.wacv-get-abd-cart-detail.' + id + ' i').removeClass('circle notch loading');
                    },
                    success(res) {
                        // console.log(res);
                        if (res.length) {
                            let html = res.map(displayAbdCartDetail).join('');
                            let target = $('.wacv-get-abd-cart-detail.' + id);
                            target.after('<table class="wacv-abd-cart-detail">' + html + '</table>');
                        }
                    },
                    error(res) {
                        console.log(res);
                    }
                });
            });

            function displayAbdCartDetail(item) {
                return `<tr><td><img width="50" src="${item.img}"></td><td>${item.name} x ${item.quantity}</td><td class="last-col"> = ${item.amount}</td></tr>`;
            }
        },

        sendReminderManual() {
            $('.wp-list-table.abandoneds').before('<div class="wacv-send-mail-progress"><progress class="wacv-send-email-manual-progress" value="0" max="100" ></progress></div>');
            //Send email abandoned manual

            $('.wacv-send-email-manual').on('click', function () {
                let temp = $('.wacv-template').val();
                var lists = [];
                $('.wacv-checkbox-bulk-action:checked').each(function (i) {
                    let id = $(this).attr('data-id');
                    let time = $(this).attr('data-time');
                    lists[i] = {id, time};
                });

                if (lists.length > 0) {
                    sendEmail_Manual(0, lists, temp);
                }
            });

            function sendEmail_Manual(index, lists, temp) {
                let progressBar = $('.wacv-send-email-manual-progress');
                if (index === 0) {
                    progressBar.show(100);
                    progressBar.val(0);
                }
                $.ajax({
                    url: wacv_ls.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'send_email_abd_manual',
                        id: lists[index].id,
                        time: lists[index].time,
                        temp: temp,
                        nonce: wacv_ls.ajax_nonce
                    },
                    success(res) {
                        progressBar.val(((index + 1) / lists.length) * 100);

                        if (res === true) {
                            let time = parseInt(lists[index].time) + 1;
                            $('.wacv-reminder-number.' + lists[index].id).text(time);
                            $('.wacv-checkbox-bulk-action.' + lists[index].id).attr({'data-time': time});
                        }

                        if (index + 1 < lists.length) {
                            sendEmail_Manual(index + 1, lists, temp);
                        }
                        if (index + 1 === lists.length) {
                            setTimeout(function () {
                                progressBar.hide(300);
                            }, 2000)
                        }
                    },
                    error(res) {

                    }
                });
            }

        },

        removeRecord() {
            /*Remove records*/
            $('.wacv-remove-record').on('click', function () {
                let lists = [];
                $('.wacv-checkbox-bulk-action:checked').each(function (i) {
                    let id = $(this).attr('data-id');
                    lists.push(id);
                });
                if (lists.length > 0) {
                    removeRecord(0, lists);
                }
            });

            function removeRecord(index, lists) {
                let progressBar = $('.wacv-send-email-manual-progress'), id = lists[index];
                if (index === 0) progressBar.val(0).show(100);

                $.ajax({
                    url: wacv_ls.ajax_url,
                    type: 'POST',
                    data: {action: 'wacv_remove_record', id: id,},
                    success(res) {
                        progressBar.val(((index + 1) / lists.length) * 100);

                        if (index + 1 < lists.length) removeRecord(index + 1, lists);

                        if (index + 1 === lists.length) {
                            setTimeout(function () {
                                progressBar.hide(300);
                            }, 2000);
                        }

                        if (res.success) {
                            if (res.data) $('.wacv-checkbox-bulk-action.' + id).closest('tr').remove();
                        }

                    }
                });
            }

        }
    };

    abandonedCartList.init();

// Export CSV
    const exportCSV = {
        init() {
            this.filename = 'abandoned_cart_' + Date.now();
            $('body').on('click', this.closeExportPanel);
            $('.wacv-toggle-export-panel').on('click', this.toggle);
            $('.wacv-do-export').on('click', {self: this}, this.export);
        },

        closeExportPanel(e) {
            if ($(e.target).closest('.wacv-export-csv-section').length === 0) $('.wacv-export-csv-form').removeClass('wacv-open');
        },

        toggle() {
            $(this).next().toggleClass('wacv-open');
        },

        export(e) {
            let $this = e.data.self;
            $this.ajaxExport();
        },

        ajaxExport(step = 1) {
            let exportBtn = $('.wacv-toggle-export-panel');
            let $this = this;
            let data = {
                action: 'wacv_export_csv',
                nonce: wacv_ls.ajax_nonce,
                from: $('.wacv-export-from').val(),
                to: $('.wacv-export-to').val(),
                step: step,
                filename: this.filename
            };

            $.ajax({
                url: wacv_ls.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: data,
                beforeSend() {
                    exportBtn.addClass('loading')
                },
                success(res) {
                    if (res.success) {
                        if (res.data.complete) {
                            exportBtn.removeClass('loading');
                            window.location = res.data.url + `&filename=${$this.filename}`;
                        } else {
                            $this.ajaxExport(step + 1);
                        }
                    }
                }
            });
        }
    };

    exportCSV.init();
});