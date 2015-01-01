/* jshint devel:true */
/* global wpErp */
/* global _ */

;(function($) {
    'use strict';

    var WeDevs_ERP_HR = {

        /**
         * Initialize the events
         *
         * @return {void}
         */
        initialize: function() {
            // new department
            $( '.erp-hr-depts').on( 'click', 'a#erp-new-dept', this.modalNewDepartment ); // new department
        },

        /**
         * Create new department
         *
         * @param  {event}
         *
         * @return {void}
         */
        modalNewDepartment: function(e) {
            e.preventDefault();

            $.erpPopup({
                title: wpErp.popup.dept_title,
                button: wpErp.popup.dept_submit,
                content: $('#erp-tmpl-new-dept').html(),
                extraClass: 'smaller',
                onSubmit: function(modal) {
                    var input = this.serialize();

                    $.post(wpErp.ajaxurl, input, function(resp) {
                        // console.log(resp.data);

                        if ( resp.success === true ) {
                            var row   = $('#erp-tmpl-dept-row').html(),
                                table = $('table.department-list-table');

                            if ( table ) {
                                var cls = $('tr:last', table).attr('class'),
                                    cls = ( cls === 'even' ) ? 'alternate' : 'even';

                                resp.data.cls = cls;
                                row = _.template( row, resp.data );
                                table.append(row);

                                modal.closeModal();
                            }
                        } else {
                            alert( resp.data );
                        }
                    }); // $.post
                } // onSubmit
            }); //popup
        },
    };

    $(function() {
        WeDevs_ERP_HR.initialize();
    });
})(jQuery);