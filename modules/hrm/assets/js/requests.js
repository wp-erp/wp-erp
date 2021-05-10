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
            },
        });

    }

})(jQuery);