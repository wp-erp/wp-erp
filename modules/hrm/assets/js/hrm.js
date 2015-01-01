/* jshint devel:true */
/* global wpErpHr */
/* global wp */

;(function($) {
    'use strict';

    var WeDevs_ERP_HR = {

        /**
         * Initialize the events
         *
         * @return {void}
         */
        initialize: function() {
            // Department
            $( '.erp-hr-depts').on( 'click', 'a#erp-new-dept', this.modalNewDepartment );
            $( '.erp-hr-depts').on( 'click', 'a.submitdelete', this.deleteDepartment );

            // Designation
            $( '.erp-hr-designation').on( 'click', 'a#erp-new-designation', this.modalNewDesignation );
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
                title: wpErpHr.popup.dept_title,
                button: wpErpHr.popup.dept_submit,
                content: wp.template('erp-new-dept')(),
                extraClass: 'smaller',
                onSubmit: function(modal) {

                    wp.ajax.send( {
                        data: this.serialize(),
                        success: function(response) {
                            var row   = wp.template('erp-dept-row'),
                                table = $('table.department-list-table');

                            if ( table ) {
                                var cls = ( $('tr:last', table).attr('class') === 'even' ) ? 'alternate' : 'even';

                                response.cls = cls;
                                table.append( row(response) );

                                modal.closeModal();
                            }
                        },
                        error: function(error) {
                            alert( error );
                        }
                    });
                } // onSubmit
            }); //popup
        },

        /**
         * Delete a department
         *
         * @param  {event}
         */
        deleteDepartment: function(e) {
            e.preventDefault();

            var self = $(this);

            if ( confirm( wpErpHr.delConfirmDept ) ) {
                wp.ajax.send( 'erp-hr-del-dept', {
                    data: {
                        '_wpnonce': wpErpHr.nonce,
                        id: self.data( 'id' )
                    },
                    success: function() {
                        self.closest('tr').fadeOut( 'fast', function() {
                            $(this).remove();
                        });
                    },
                    error: function(response) {
                        alert( response );
                    }
                });
            }
        },

        modalNewDesignation: function(e) {
            e.preventDefault();

            $.erpPopup({
                title: wpErpHr.popup.desig_title,
                button: wpErpHr.popup.desig_submit,
                content: $('#erp-tmpl-new-dept').html(),
                extraClass: 'smaller',
                onSubmit: function(modal) {

                    console.log(modal);
                }
            });
        }
    };


    $(function() {
        WeDevs_ERP_HR.initialize();
    });
})(jQuery);