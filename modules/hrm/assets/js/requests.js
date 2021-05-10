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

            methods: {
                init: function() {
                    this.allEmployees[0] = erpHrReq.filterEmployee;
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
            }
        });

    }

})(jQuery);