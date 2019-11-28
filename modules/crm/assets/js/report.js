'use strict';

var labels;
var months = moment.months();
var data = [
    { 'subscriber' : [] },
    { 'opportunity': [] },
    { 'lead'       : [] },
    { 'customer'   : [] }
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

for ( var i = 0; i < labels.length; i++) {
    var tempData = growthReport.reports[ labels[i] ];

    if ( tempData ) {
        data[0].subscriber.push( tempData.subscriber ),
        data[1].opportunity.push( tempData.opportunity ),
        data[2].lead.push( tempData.lead ),
        data[3].customer.push( tempData.customer )
    }
}

// Reference the chart canvas
var ctx = document.getElementById('growth-chart').getContext('2d');

var chart = new Chart(ctx, {
    type: 'bar',

    data: {
        labels: labels,
        datasets: [
            {
                label: __('Subscriber', 'erp'),
                backgroundColor: 'rgba(244,67,54, .5)',
                borderColor: 'rgba(244,67,54, .5)',
                data: data[0].subscriber
            },
            {
                label: __('Opportunity', 'erp'),
                backgroundColor: 'rgba(103,58, 183, .5)',
                borderColor: 'rgba(103,58, 183, .5)',
                data: data[1].opportunity
            },
            {
                label: __('Lead', 'erp'),
                backgroundColor: 'rgba(3,169,244, .5)',
                borderColor: 'rgba(3,169,244, .5)',
                data: data[2].lead
            },
            {
                label: __('Customer', 'erp'),
                backgroundColor: 'rgba(255,193,7, .5)',
                borderColor: 'rgba(255,193,7, .5)',
                data: data[3].customer
            }
        ]
    },

    options: {
        maintainAspectRatio: false,
        scales: {
            xAxes: [{
                stacked: true,
                gridLines: {
                    display: false
                 }
            }],
            yAxes: [{
                stacked: true,
                gridLines: {
                    display: true
                 }
            }]
        }
    }
});
