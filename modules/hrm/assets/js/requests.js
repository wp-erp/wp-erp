(function($){

    'use strict';

    if ( $("#erp-hr-requests").length > 0 ) {

        var requests = new Vue({
            el: "#erp-hr-requests",
            
            data: {
                requests         : [],
                employee         : 0,
                totalItems       : 0,
                currentItems     : 0,
                lastPage         : 0,
                currentPage      : 1,
                perPage          : 20,
                status           : '',
                orderBy          : 'id',
                order            : 'DESC',
                date             : {},
                pageNumberInput  : 1,
                hidePagination   : false,
                isLoaded         : false,
                ajaxloader       : false,
                nonce            : erpHrReq.nonce,
                ajaxurl          : erpHrReq.ajaxurl,
                allEmployees     : erpHrReq.employees,
                activeTopNav     : Object.keys( erpHrReq.request_types )[0],
                hideCb           : false,
                checkAllCheckbox : false,
                checkboxItems    : [],
                bulkaction1      : '-1',
                bulkaction2      : '-1',
                topNavFilter     : {
                    data    : erpHrReq.request_types,
                    default : '',
                    field   : 'type',
                }, 
                bulkActionMap    : [
                    {
                        id   : 'approved',
                        text : __( 'Approve', 'erp' ),
                    },
                    {
                        id   : 'rejected',
                        text : __( 'Reject', 'erp' ),
                    },
                ],
                tableHeaderMap    : {
                    resigned    : [
                        {
                            title : __( 'Reason', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Resign Date', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Request Date', 'erp' ),
                            class : ''
                        },
                    ],
                    leave       : [
                        {
                            title : __( 'Reason', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Start Date', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'End Date', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Duration', 'erp' ),
                            class : 'text-center'
                        },
                    ],
                    remote_work : [
                        {
                            title : __( 'Reason', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Start Date', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'End Date', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Duration', 'erp' ),
                            class : 'text-center'
                        },
                    ],
                    asset       : [
                        {
                            title : __( 'Item', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Category', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Request Date', 'erp' ),
                            class : ''
                        },
                    ],
                    reimburse   : [
                        {
                            title : __( 'Amount', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Transaction Date', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Request Date', 'erp' ),
                            class : ''
                        },
                    ],
                    common      : [
                        {
                            title : __( 'Employee', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Status', 'erp' ),
                            class : 'text-center'
                        },
                        {
                            title : __( 'Actions', 'erp' ),
                            class : 'text-center'
                        },
                    ]
                },
            },

            created: function() {
                this.init();
                this.getRequestList();
            },

            ready: function() {
                var self = this;
        
                $('select#erp-hr-filter-employee').on('change', function(e) {
                    self.employee = $(this).val();
                });

                $('select#erp-hr-filter-status').on('change', function(e) {
                    self.status = $(this).val();
                });
            },

            computed: {
                statusFilter: function() {
                    var status = {};

                    switch ( this.activeTopNav ) {
                        case 'resigned':
                        case 'remote_work':
                        case 'asset':
                            status = {
                                ''       : __( 'Status', 'erp' ),
                                pending  : __( 'Pending', 'erp' ),
                                approved : __( 'Approved', 'erp' ),
                                rejected : __( 'Rejected', 'erp' ),
                            };
                            break;

                        case 'leave':
                            status = {
                                '' : __( 'Status', 'erp' ),
                                2  : __( 'Pending', 'erp' ),
                                1  : __( 'Approved', 'erp' ),
                                3  : __( 'Rejected', 'erp' ),
                            };
                            break;

                        case 'reimburse':
                            status = {
                                '' : __( 'Status', 'erp' ),
                                7  : __( 'Closed', 'erp' ),
                                2  : __( 'Awaiting Payment', 'erp' ),
                            }
                            break;
                    }

                    return status;
                },
            },

            methods: {
                init: function() {
                    this.allEmployees[0] = erpHrReq.filterEmployee;
                    
                    this.initDateRangePicker();
                    this.select2Action('erp-hrm-select2');
                    this.initExtraFeatures();
                },

                getRequestList: function() {
                    var self        = this;
                    self.ajaxloader = true;
                    
                    wp.ajax.send({
                        data : {
                            type     : self.activeTopNav,
                            user_id  : self.employee,
                            per_page : self.perPage,
                            page     : self.currentPage,
                            date     : self.date,
                            order_by : self.orderBy,
                            order    : self.order,
                            status   : self.status,
                            action   : "erp_hr_employee_get_requests",
                            _wpnonce : self.nonce
                        },
                        success: function(res) {
                            self.requests = res.data;

                            if (res.data.length) {
                                self.totalItems   = res.total_items;
                                self.currentItems = res.data.length;
                                self.topNavFilter.data[ self.activeTopNav ].count = res.data.length;
                            } else {
                                self.totalItems      = 0;
                                self.currentPage     = 1;
                                self.pageNumberInput = 1;
                                self.currentItems    = 0;
                                self.topNavFilter.data[ self.activeTopNav ].count = 0;
                            }

                            self.requests.forEach( function(request) {
                                switch (self.activeTopNav) {
                                    case 'resigned':
                                    case 'remote_work':
                                        if (request.status.id == 'pending') {
                                            request.actions = [
                                                {
                                                    id    : 'view',
                                                    text  : __( 'View', 'erp' ),
                                                    class : 'dashicons dashicons-visibility view'
                                                },
                                                {
                                                    id    : 'approved',
                                                    text  : __( 'Approve', 'erp' ),
                                                    class : 'dashicons dashicons-yes-alt accept'
                                                },
                                                {
                                                    id    : 'rejected',
                                                    text  : __( 'Reject', 'erp' ),
                                                    class : 'dashicons dashicons-warning reject',
                                                },
                                            ];
                                        } else {
                                            request.actions = [{
                                                id    : 'view',
                                                text  : __( 'View', 'erp' ),
                                                class : 'dashicons dashicons-visibility view',
                                            }];

                                            if ( self.activeTopNav == 'resigned' ) {
                                                request.actions.push({
                                                    id    : 'deleted',
                                                    text  : __( 'Delete', 'erp' ),
                                                    class : 'dashicons dashicons-trash delete',
                                                });
                                            }
                                        }

                                        break;

                                    default :
                                        request.actions = [
                                            {
                                                id    : 'view',
                                                text  : __( 'View', 'erp' ),
                                                class : 'dashicons dashicons-visibility view'
                                            },
                                        ];
                                }

                                if ( request.duration ) {
                                    request.duration = {
                                        key   : parseInt( request.duration ),
                                        value : parseInt( request.duration ) <= 1
                                                ? request.duration + __( ' day', 'erp' )
                                                : request.duration + __( ' days', 'erp' ),
                                    };
                                }
                            });

                            self.isLoaded   = true;
                            self.ajaxloader = false;
                        },
                        error: function(error) {
                            swal('', error, 'error');
                            self.requests   = {};
                            self.isLoaded   = true;
                            self.ajaxloader = false;
                        }
                    });
                },

                initDateRangePicker: function() {
                    var elem = $( "input[name='filter_date']" );
                    var self = this;

                    elem.daterangepicker({
                        autoUpdateInput : false,
                        locale          : {
                            cancelLabel : erpHrReq.clear
                        },
                        ranges : {
                            'Today'      : [ moment(), moment() ],
                            'This Week'  : [ moment().startOf( 'isoWeek' ), moment().endOf( 'isoWeek' ) ],
                            'This Month' : [ moment().startOf( 'month' ), moment().endOf( 'month' ) ],
                            'Last Month' : [ moment().subtract( 1, 'month' ).startOf( 'month' ), moment().subtract( 1, 'month' ).endOf( 'month' ) ],
                            'This Year'  : [ moment().startOf( 'year' ), moment().endOf( 'year' ) ],
                            'Last Year'  : [ moment().subtract( 1, 'year' ).startOf( 'year' ), moment().subtract( 1, 'year' ).endOf( 'year' ) ],
                        }
                    });

                    elem.on( 'apply.daterangepicker', function(event, picker) {
                        $( this ).val( picker.startDate.format( 'MMM DD, YYYY' ) + ' - ' + picker.endDate.format( 'MMM DD, YYYY' ) );

                        self.date = {
                            start : picker.startDate.format( 'DD.MM.YYYY' ),
                            end   : picker.endDate.format( 'DD.MM.YYYY' )
                        }
                    });

                    elem.on( 'cancel.daterangepicker', function(event, picker) {
                        $( this ).val('');
                        self.date = {};
                    });
                },

                select2Action: function(element) {
                    $('.'+element).select2({
                        width: 'element',
                    });
                },

                initExtraFeatures: function() {
                    $( document ).click( function(e) {
                        Array.prototype.forEach.call( $( '.erp-row-actions-btn' ), function(row, index) {
                            if ( typeof row !== 'undefined' && ! row.contains( e.target ) ) {
                                $( '.dropdown-content' )[index].classList.remove('show');
                            }
                        });
                    });
                },
            },

            watch: {
                activeTopNav: function(val) {
                    this.hideCb = ( val !== 'resigned' && val !== 'remote_work' );
                }
            },
        });

    }

})(jQuery);