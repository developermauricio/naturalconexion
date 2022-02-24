jQuery(document).ready(function ($) {

    var lineChartData = {
        labels: wacvParams.xAxis,
        datasets: [{
            label: 'Recovered',
            borderColor: 'green',
            backgroundColor: 'rgba(0,255,0,0.1)',
            borderWidth: 1,
            pointBackgroundColor: 'green',
            pointBorderColor: 'transparent',
            pointRadius: 2,
            fill: true,
            data: wacvParams.recoveredLine,
            yAxisID: 'y-axis-1',
        }, {
            label: 'Reminder',
            borderColor: '#0077ff',
            backgroundColor: 'rgba(0, 0, 255, 0.1)',
            borderWidth: 1,
            pointBackgroundColor: '#0077ff',
            pointBorderColor: 'transparent',
            pointRadius: 2,
            fill: true,
            data: wacvParams.reminderLine,
            yAxisID: 'y-axis-2'
        }]
    };

    window.onload = function () {
        var ctx = document.getElementById('canvas').getContext('2d');
        window.myLine = Chart.Line(ctx, {
            data: lineChartData,
            options: {
                responsive: true,
                hoverMode: 'index',
                stacked: false,
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                        }
                    }],
                    yAxes: [
                        {
                            type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                            display: true,
                            position: 'left',
                            id: 'y-axis-1',
                            gridLines: {
                                drawOnChartArea: false, // only want the grid lines for one axis to show up
                            },
                        },
                        {
                            type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                            display: true,
                            position: 'right',
                            id: 'y-axis-2',
                            gridLines: {
                                drawOnChartArea: false, // only want the grid lines for one axis to show up
                            },
                        }
                    ],
                }
            }
        });
    };
});