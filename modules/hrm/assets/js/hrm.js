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
            $( '.erp-hr-depts').on( 'click', 'a#erp-new-dept', this.department.create );
            $( '.erp-hr-depts').on( 'click', 'a.submitdelete', this.department.remove );
            $( '.erp-hr-depts').on( 'click', 'span.edit a', this.department.edit );

            // Designation
            $( '.erp-hr-designation').on( 'click', 'a#erp-new-designation', this.designation.create );
            $( '.erp-hr-designation').on( 'click', 'a.submitdelete', this.designation.remove );
            $( '.erp-hr-designation').on( 'click', 'span.edit a', this.designation.edit );
        },

        department: {

            /**
             * Create new department
             *
             * @param  {event}
             */
            create: function(e) {
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
                    }
                }); //popup
            },

            /**
             * Edit a department in popup
             *
             * @param  {event}
             */
            edit: function(e) {
                e.preventDefault();

                var self = $(this);

                $.erpPopup({
                    title: wpErpHr.popup.dept_update,
                    button: wpErpHr.popup.dept_update,
                    content: wp.template('erp-new-dept')(),
                    extraClass: 'smaller',
                    onReady: function() {
                        var modal = this;

                        $( 'header', modal).after( $('<div class="loader"></div>').show() );

                        wp.ajax.send( 'erp-hr-get-dept', {
                            data: {
                                id: self.data('id'),
                                _wpnonce: wpErpHr.nonce
                            },
                            success: function(response) {
                                $( '.loader', modal).remove();

                                $('#dept-title', modal).val( response.name );
                                $('#dept-desc', modal).val( response.data.description );
                                $('#dept-parent', modal).val( response.data.parent );
                                $('#dept-id', modal).val( response.id );
                                $('#dept-action', modal).val( 'erp-hr-update-dept' );

                                // disable current one
                                $('#dept-parent option[value="' + self.data('id') + '"]', modal).attr( 'disabled', 'disabled' );
                            }
                        });
                    },
                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(response) {
                                var row   = wp.template('erp-dept-row');

                                response.cls = self.closest('tr').attr('class');
                                self.closest('tr').replaceWith( row(response) );

                                modal.closeModal();
                            },
                            error: function(error) {
                                alert( error );
                            }
                        });
                    }
                });
            },

            /**
             * Delete a department
             *
             * @param  {event}
             */
            remove: function(e) {
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

        },

        designation: {

            create: function(e) {
                e.preventDefault();

                $.erpPopup({
                    title: wpErpHr.popup.desig_title,
                    button: wpErpHr.popup.desig_submit,
                    content: wp.template( 'erp-new-desig' )(),
                    extraClass: 'smaller',
                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(response) {
                                var row   = wp.template('erp-desig-row'),
                                    table = $('table.designation-list-table');

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
                    }
                });
            },

            /**
             * Edit a department in popup
             *
             * @param  {event}
             */
            edit: function(e) {
                e.preventDefault();

                var self = $(this);

                $.erpPopup({
                    title: wpErpHr.popup.desig_update,
                    button: wpErpHr.popup.desig_update,
                    content: wp.template( 'erp-new-desig' )(),
                    extraClass: 'smaller',
                    onReady: function() {
                        var modal = this;

                        $( 'header', modal).after( $('<div class="loader"></div>').show() );

                        wp.ajax.send( 'erp-hr-get-desig', {
                            data: {
                                id: self.data('id'),
                                _wpnonce: wpErpHr.nonce
                            },
                            success: function(response) {
                                $( '.loader', modal).remove();

                                $('#desig-title', modal).val( response.name );
                                $('#desig-desc', modal).val( response.data.description );
                                $('#desig-id', modal).val( response.id );
                                $('#desig-action', modal).val( 'erp-hr-update-desig' );
                            }
                        });
                    },
                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(response) {
                                var row   = wp.template('erp-desig-row');

                                response.cls = self.closest('tr').attr('class');
                                self.closest('tr').replaceWith( row(response) );

                                modal.closeModal();
                            },
                            error: function(error) {
                                alert( error );
                            }
                        });
                    }
                });
            },

            /**
             * Delete a department
             *
             * @param  {event}
             */
            remove: function(e) {
                e.preventDefault();

                var self = $(this);

                if ( confirm( wpErpHr.delConfirmDept ) ) {
                    wp.ajax.send( 'erp-hr-del-desig', {
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
        },
    };

    $(function() {
        WeDevs_ERP_HR.initialize();
    });
})(jQuery);