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
                            class : 'column-heading-small'
                        },
                        {
                            title : __( 'Resign Date', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                        {
                            title : __( 'Request Date', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                    ],
                    leave       : [
                        {
                            title : __( 'Reason', 'erp' ),
                            class : 'column-heading-small'
                        },
                        {
                            title : __( 'Start Date', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                        {
                            title : __( 'End Date', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                        {
                            title : __( 'Duration', 'erp' ),
                            class : 'text-center column-heading-small column-heading-mid'
                        },
                    ],
                    remote_work : [
                        {
                            title : __( 'Reason', 'erp' ),
                            class : 'column-heading-small'
                        },
                        {
                            title : __( 'Start Date', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                        {
                            title : __( 'End Date', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                        {
                            title : __( 'Duration', 'erp' ),
                            class : 'text-center column-heading-small column-heading-mid'
                        },
                    ],
                    asset       : [
                        {
                            title : __( 'Item', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                        {
                            title : __( 'Category', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                        {
                            title : __( 'Request Date', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                    ],
                    reimburse   : [
                        {
                            title : __( 'Amount', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                        {
                            title : __( 'Transaction Date', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                        {
                            title : __( 'Request Date', 'erp' ),
                            class : 'column-heading-small column-heading-mid'
                        },
                    ],
                    common      : [
                        {
                            title : __( 'Employee', 'erp' ),
                            class : ''
                        },
                        {
                            title : __( 'Status', 'erp' ),
                            class : 'text-center column-heading-small'
                        },
                        {
                            title : __( 'Actions', 'erp' ),
                            class : 'text-center column-heading-small'
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

                bulkactions: function() {
                    if ( this.activeTopNav == 'resigned' || this.activeTopNav == 'remote_work' ) {
                        return this.bulkActionMap;
                    }

                    return [];
                },

                tableHeaders: function() {
                    let headers = [];

                    headers.push( ...this.tableHeaderMap.common );
                    headers.splice( 1, 0, ...this.tableHeaderMap[this.activeTopNav] );

                    return headers;
                },

                columnCount: function() {
                    return this.tableHeaders.length;
                },

                totalPage: function() {
                    return Math.ceil(this.totalItems / this.perPage);
                },

                paginationClass: function() {
                    return this.perPage >= this.totalItems ? 'one-page' : '';
                },

                items: function() {
                    return this.currentItems;
                },

                headerColSpan: function() {
                    return screen.width <= 601 && ! this.requests.length ? this.columnCount : false;
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
                    var self = this;

                    $( document ).click( function(e) {
                        Array.prototype.forEach.call( $( '.erp-row-actions-btn' ), function(row, index) {
                            if ( typeof row !== 'undefined' && ! row.contains( e.target ) ) {
                                $( '.dropdown-content' )[index].classList.remove('show');
                            }
                        });
                    });

                    $( '#erp-hr-filter-date' ).on( 'change', function(e) {
                        if ( $(this).val() == '' ) {
                            self.date = {};
                        }
                    });

                    if ( screen.width <= 601 ) {
                        $( '#erp-hr-req-cb' ).removeClass( 'vertical-middle' );
                    } else {
                        $( '#erp-hr-req-cb' ).addClass( 'vertical-middle' );
                    }

                },

                toggleDropdown: function() {
                    $("#erp-dropdown-content").toggleClass("show");
                },

                resetDropdown: function() {
                    var self      = this;
                    self.employee = 0;
                    self.status   = '';
                    self.date     = {};

                    $('#select2-erp-hr-filter-employee-container').attr('title', self.allEmployees[self.employee]);
                    $('#select2-erp-hr-filter-employee-container').html(self.allEmployees[self.employee]);
                    $('#select2-erp-hr-filter-status-container').attr('title', self.statusFilter[self.status]);
                    $('#select2-erp-hr-filter-status-container').html(self.statusFilter[self.status]);
                    $("#erp-hr-filter-date").val('');

                    self.resetData();
                    self.getRequestList();
                    self.toggleDropdown();
                },

                resetData: function() {
                    this.currentPage     = 1;
                    this.pageNumberInput = 1;
                },

                filterData: function() {
                    this.resetData();
                    this.getRequestList();
                    this.toggleDropdown();
                },

                triggerAllCheckBox: function(){
                    if ( this.checkAllCheckbox ) {
                        this.checkboxItems = [];

                        for( let key in this.requests ) {
                            this.checkboxItems.push( this.requests[key].id );
                        }

                    } else {
                        this.checkboxItems = [];
                    }
                },

                handleBulkAction: function(action) {
                    var self = this;

                    wp.ajax.send({
                        data : {
                            req_id      : self.checkboxItems,
                            req_type    : self.activeTopNav,
                            action_type : action,
                            action      : "erp_hr_employee_requests_bulk_action",
                            _wpnonce    : self.nonce
                        },
                        success: function(res) {
                            self.showAlert('success', res);
                            self.getRequestList();
                            self.updateNotification();
                        },
                        error: function(error) {
                            swal('', error, 'error');
                        }
                    });
                },

                onActionClick: function(id, action, status, modal) {
                    var self       = this,
                        btnText    = 'Yes',
                        title      = '',
                        btnColor   = '#22b527',
                        ajaxAction = '',
                        modalClass = '';

                    if ( action == 'view' ) {
                        if ( self.activeTopNav == 'resigned' || self.activeTopNav == 'remote_work' ) {
                            if ( self.activeTopNav == 'resigned' ) {
                                ajaxAction = 'erp_hr_employee_get_resign_request';
                                title      = __( 'Resignation', 'erp' );
                                modalClass = 'resign';
                            } else {
                                ajaxAction = 'erp_hr_employee_get_remote_work_request';
                                title      = __( 'Remote Work Request', 'erp' );
                                modalClass = 'remote-work';
                            }

                            wp.ajax.send({
                                data : {
                                    req_id   : id,
                                    action   : ajaxAction,
                                    _wpnonce : self.nonce
                                },
                                success: function(res) {
                                    res.showBtn = false;

                                    if ( res.status.id == 'pending' ) {
                                        res.showBtn = true;
                                    }

                                    if ( res.duration ) {
                                        res.duration = parseInt( res.duration ) > 1
                                                     ? res.duration + __( ' days', 'erp' )
                                                     : res.duration + __( ' day', 'erp' );
                                    }

                                    $.erpPopup({
                                        title: __( 'Resignation', 'erp' ),
                                        id: 'erp-hr-request-single',
                                        content: '',
                                        extraClass: 'smaller',

                                        onReady: function(modal) {
                                            var html = wp.template( 'erp-employee-resquest' )(res);

                                            $( "#erp-hr-request-single .close" ).html( '<span class="dashicons dashicons-no-alt"></span>' );
                                            $( "#erp-hr-request-single .close" ).attr( 'id', 'close-modal' );
                                            $( "#erp-hr-request-single header" ).hide();
                                            $( "#erp-hr-request-single footer" ).hide();
                                            $( "#erp-hr-request-single" ).addClass( `${res.status.id} ${modalClass}` );

                                            $( '.content', this ).html( html );

                                            $( '#erp-req-approve' ).click( function(e) {
                                                e.preventDefault();
                                                self.onActionClick(res.id, 'approved', res.status.id, modal);
                                            });

                                            $( '#erp-req-reject' ).click( function(e) {
                                                e.preventDefault();
                                                self.onActionClick(res.id, 'rejected', res.status.id, modal);
                                            });
                                        }
                                    });
                                },
                                error: function(error) {
                                    swal('', error, 'error');
                                    self.isLoaded = false;
                                }
                            });

                        } else {
                            window.location.href = this.redirectUrl( id, status );
                        }

                        return;
                    }

                    if ( action == 'approved' ) {
                        title    = __( 'Approve this request?', 'erp' );
                        btnText  = __( 'Yes, Approve', 'erp' );
                        btnColor = '#22b527';
                    } else if ( action == 'rejected' ) {
                        title    = __( 'Reject this request?', 'erp' );
                        btnText  = __( 'Yes, Reject', 'erp' );
                        btnColor = '#ff8670';
                    } else if ( action == 'deleted' ) {
                        title    = __( 'Delete this request?', 'erp' );
                        btnText  = __( 'Yes, Delete', 'erp' );
                        btnColor = '#fa4646';
                    }

                    swal({
                        title              : title,
                        type               : 'warning',
                        showCancelButton   : true,
                        cancelButtonText   : __( 'Cancel', 'erp' ),
                        confirmButtonColor : btnColor,
                        confirmButtonText  : btnText,
                        closeOnConfirm     : false
                    },
                    function() {
                        wp.ajax.send({
                            data : {
                                req_id      : id,
                                req_type    : self.activeTopNav,
                                action_type : action,
                                action      : 'erp_hr_employee_requests_bulk_action',
                                _wpnonce    : self.nonce
                            },
                            success: function(res) {
                                self.showAlert('success', res);
                                self.getRequestList();
                                self.updateNotification();

                                if ( modal ) {
                                    modal.closeModal();
                                }
                            },
                            error: function(error) {
                                swal('', error, 'error');
                                self.isLoaded = false;
                            }
                        });
                    });
                },

                updateNotification: function(val) {
                    var selector = '#erp-hr-requests .erp-nav .requests a',
                        child    = 'span.erp-notification',
                        pending  = 0;

                    wp.ajax.send({
                        data: {
                            action: 'erp_hr_get_total_pending_requests'
                        },
                        success: function(response) {
                            pending = response;
                        },
                        error: function(error) {
                            pending = 0;
                        }
                    })
                    .then( function() {
                        if ( pending > 0 ) {
                            if ( ! $( selector ).find( child ).length ) {
                                $( selector ).append( ` <span class="erp-notification">${pending}</span>` );
                            } else {
                                $( `${selector} ${child}` ).html( pending );
                            }
                        } else {
                            if ( $( selector ).find( child ).length ) {
                                $( `${selector} ${child}` ).remove();
                            }
                        }
                    });
                },

                redirectUrl: function(id, status) {
                    switch ( this.activeTopNav ) {
                        case 'leave':
                            return `${erpHrReq.adminurl}?page=erp-hr&section=leave&sub-section=leave-requests&status=${status}`;

                        case 'asset':
                            return `${erpHrReq.adminurl}?page=erp-hr&section=asset&sub-section=asset-request&status=${status}`;

                        case 'reimburse':
                            return `${erpHrReq.adminurl}?page=erp-accounting#/transactions/reimbursements/requests/${id}`;

                        default:
                            return `${erpHrReq.adminurl}?page=erp-hr&section=people&sub-section=requests`;
                    }
                },

                showRowActions: function(index) {
                    $( `#request-row-actions-${index}` ).toggleClass( 'show' );
                },

                hasBulkAction: function() {
                    return this.bulkactions.length > 0;
                },

                hasTopNavFilter: function() {
                    return this.topNavFilter.data.length > 0;
                },

                isTopNavFilterLastItem: function(currentKey) {
                    var keys = Object.keys( this.topNavFilter.data )

                    if ( keys[keys.length-1] == currentKey ) {
                        return true;
                    }
                    return false;
                },

                isCurrentTopNavFilter: function(key) {
                    return this.activeTopNav == key;
                },

                filterTopNav: function(action, label) {
                    this.activeTopNav = action;
                    this.resetDropdown();
                },

                isFirstPage: function() {
                    return this.currentPage == 1;
                },

                isLastPage: function() {
                    return this.currentPage == this.totalPage;
                },

                goFirstPage: function() {
                    this.currentPage     = 1;
                    this.pageNumberInput = this.currentPage;

                    this.getRequestList();
                },

                goLastPage: function() {
                    this.currentPage     = this.totalPage;
                    this.pageNumberInput = this.currentPage;

                    this.getRequestList();
                },

                goToPage: function(direction) {
                    if ( direction == 'prev' ) {
                        this.currentPage--;
                    } else if ( direction == 'next' ) {
                        this.currentPage++;
                    } else {
                        if ( ! isNaN( direction ) ) {
                            this.currentPage = direction > this.totalPage ? this.totalPage : ( direction < 1 ? 1 : direction );
                        }
                    }

                    this.pageNumberInput = this.currentPage;

                    this.getRequestList();

                    return false;
                },

                showAlert: function(type, message, title = '') {
                    swal({
                        title : title,
                        text  : message,
                        type  : type,
                        timer : 2200,
                        showConfirmButton : false,
                    });
                },

                toggleMoreInfo: function(e) {
                    var $row = $(e.target).closest('tr');
                    $row.toggleClass('expanded');

                    $row.find('td').each( function() {
                        $(this).toggleClass( 'decor-additional-info hide-additional-info' );
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