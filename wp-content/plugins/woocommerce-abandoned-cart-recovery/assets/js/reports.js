jQuery(document).ready(function ($) {
    "use strict";

    //after loaded
    var now = new Date();
    const oneDay = 86400000;
    let today = getDateRange(now);

    $('.wacv-date-from').val(today);
    $('.wacv-date-to').val(today);

    ajax_func({time_option: 'today'});

    $('.wacv-select-time-report').on('change', function () {

        let thisVal = $(this).val();
        let from_date = $('.wacv-date-from');
        let to_date = $('.wacv-date-to');

        if (thisVal !== 'custom') {
            $('.wacv-custom-time-range').hide();

            switch ($(this).val()) {
                case 'today':
                    from_date.val(today);
                    to_date.val(today);
                    break;
                case 'yesterday':
                    let yesterday = new Date(Date.now() - oneDay);
                    yesterday = getDateRange(yesterday);
                    from_date.val(yesterday);
                    to_date.val(yesterday);
                    break;
                case '30days':
                    let _30days = new Date(Date.now() - 30 * oneDay);
                    _30days = getDateRange(_30days);
                    from_date.val(_30days);
                    to_date.val(today);
                    break;
                case '90days':
                    let _90days = new Date(Date.now() - 90 * oneDay);
                    _90days = getDateRange(_90days);
                    from_date.val(_90days);
                    to_date.val(today);
                    break;
                case '365days':
                    let _365days = new Date(Date.now() - 365 * oneDay);
                    _365days = getDateRange(_365days);
                    from_date.val(_365days);
                    to_date.val(today);
                    break;
            }
            ajax_func({time_option: $(this).val()});
        } else {
            $('.wacv-custom-time-range').show();
        }
    });

    $('.wacv-view-reports').on('click', function () {

        $('.wacv-select-time-report').val('custom');

        var data = {
            from_date: new Date($('.wacv-date-from').val()).getTime() / 1000,
            to_date: new Date($('.wacv-date-to').val()).getTime() / 1000 + 86400 - 1
        };

        if (data.from_date < data.to_date) {
            if (data.to_date - data.from_date < 31 * 24 * 60 * 60) {
                ajax_func(data);
            } else {
                alert('Time range more than 30 days. Please select again')
            }
        } else {
            alert('Please select start date less than end date')
        }
    });

    function ajax_func(data) {

        $.ajax({
            type: 'post',
            url: wacv_ls.ajax_url,
            data: {_ajax_nonce: wacv_ls.nonce, data: data, action: 'get_reports'},
            success: function (result) {
                // console.log(result);
                drawChart(result);
                abd_report(result);
            },
            error: function (result) {
                // console.log(result);
            },
            beforeSend: function () {
                // $('.woo-rp-loading-icon').show();
            },
            complete: function () {
                // $('.woo-rp-loading-icon').hide();
            }
        });
    }

    function drawChart(data) {
        let myChart = null;

        if (myChart != null) {
            myChart.destroy();
        }
        var ctx = document.getElementById('myChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',

            data: {
                labels: data.abd_chart_data.label,
                datasets: [{
                    label: 'Abandoned',
                    borderColor: 'red',
                    backgroundColor: 'rgba(255, 0, 0, 0.05)',
                    data: data.abd_chart_data.value,
                    borderWidth: 1,
                    pointBackgroundColor: 'red',
                    pointBorderColor: 'rgba(0, 0, 0, 0)'
                }, {
                    label: 'Recovered',
                    borderColor: '#0071FF',
                    backgroundColor: 'rgba(0, 0, 255, 0.05)',
                    data: data.rcv_chart_data.value,
                    borderWidth: 1,
                    pointBackgroundColor: '#0071FF',
                    pointBorderColor: 'rgba(0, 0, 0, 0)'
                },]
            },

            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 4,
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: true,
                        },
                        ticks: {
                            beginAtZero: true,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Total (' + wacv_ls.currency + ')',
                            fontSize: 16
                        }
                    }]
                },

            },
        });
    }

    function getDateRange(obj) {
        return obj.getFullYear() + "-" + ("0" + (obj.getMonth() + 1)).slice(-2) + "-" + ("0" + obj.getDate()).slice(-2);
    }

    function abd_report(data) {
        var html = `<div class="wacv-cell"><h5>Abandoned</h5><div>Order: ${data.abd_count}</div><div>Total: ${data.abd_total}</div></div>
            <div class="wacv-cell"><h5>Recovered</h5><div>Order: ${data.rcv_count}</div><div>Total: ${data.rcv_total}</div></div>
            <div class="wacv-cell"><h5>Email reminder</h5><div>Email sent: ${data.email_sent}</div><div></div><div>Clicked ratio: ${clicked_ratio(data.email_clicked, data.email_sent)}%</div></div>
            <div class="wacv-cell"><h5>Messenger reminder</h5><div>Messenger sent: ${data.messenger_sent}</div><div></div><div>Clicked ratio: ${clicked_ratio(data.messenger_clicked, data.messenger_sent)}%</div>`;

        $('.wacv-general-reports-group').html(html);
    }

    function clicked_ratio(clicked, total) {
        var clicked_ratio = 0;
        if (parseInt(total)) {
            clicked_ratio = (clicked / total * 100);
        }
        return clicked_ratio.toFixed(1);
    }

    console.log('ssssssss')

});
