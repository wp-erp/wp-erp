'use strict';

var labels;
var months   = moment.months();
var datasets = [];
var colors   = [
                'rgba(244,67,54, .5)',
                'rgba(103,58, 183, .5)',
                'rgba(3,169,244, .5)',
                'rgba(255,193,7, .5)',
                'rgb(102, 255, 153, .5)',
                'rgb(255, 102, 255, .5)',
                'rgb(255, 153, 51, .5)',
                'rgb(102, 153, 153, .5)',
                'rgb(153, 51, 0, .5)',
                'rgb(153, 153, 102, .5)',
                'rgb(0, 0, 204, .5)',
                'rgb(153, 51, 102, .5)'
            ];

if ( 'this_year' == growthReport.type ) {
    labels = months;

    for ( var i = 0, len = labels.length; i < len; i++ ) {
        if ( ! growthReport.reports[ labels[i] ] ) {
            growthReport.reports[ labels[i] ] = {};
        }
    }
} else if ( 'custom' == growthReport.type ) {
    labels = Object.keys( growthReport.reports );
}

var slugs = Object.keys( growthReport.stages );

for( var i = 0; i < slugs.length; ++i) {

    datasets[i] = {
        label : __( growthReport.stages[ slugs[i] ], 'erp' ),
        backgroundColor: colors[i],
        borderColor: colors[i],
        data: [],
    };

    for ( var j = 0; j < labels.length; ++j) {
        var tempData = growthReport.reports[ labels[j] ];

        if ( tempData ) {
            datasets[i].data.push( tempData[ slugs[i] ] );
        }
    }
}

// Reference the chart canvas
var ctx = document.getElementById('growth-chart').getContext('2d');

var chart = new Chart(ctx, {
    type: 'bar',

    data: {
        labels: labels,
        datasets: datasets
    },

    options: {
        maintainAspectRatio: false,
        scales: {
            x: {
                stacked: true,
                gridLines: {
                    display: false
                }
            },
            y: {
                stacked: true,
                gridLines: {
                    display: true
                }
            }
        }
    }
});
