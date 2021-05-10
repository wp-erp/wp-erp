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
        });

    }

})(jQuery);