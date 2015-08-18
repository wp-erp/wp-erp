/* jshint devel:true */
/* global wpErpHr */
/* global wp */

;(function($) {
    'use strict';

    var Leave = {

        initialize: function() {
            var self = this;

            $( '.erp-hr-leave-policy').on( 'click', 'a#erp-leave-policy-new', self, this.policy.create );
            $( '.erp-hr-leave-policy').on( 'click', 'a.link, span.edit a', self, this.policy.edit );
            $( '.erp-hr-leave-policy').on( 'click', 'a.submitdelete', self, this.policy.remove );
            $( '.erp-hr-leave-request-new').on( 'change', '.erp-date-field', self, this.leave.requestDates );
            $( '.erp-employee-single' ).on('submit', 'form#erp-hr-empl-leave-history', this.leave.showHistory );

            //Holiday
            $( '.erp-hr-holiday-wrap').on( 'click', 'a#erp-hr-new-holiday', self, this.holiday.create );
        },

        initDateField: function() {
            $( '.erp-leave-date-field').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });

            $( ".erp-leave-date-picker-from" ).datepicker({
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                numberOfMonths: 1,
                onClose: function( selectedDate ) {

                    $( ".erp-leave-date-picker-to" ).datepicker( "option", "minDate", selectedDate );
                }
            });
            $( ".erp-leave-date-picker-to" ).datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                onClose: function( selectedDate ) {
                    $( ".erp-leave-date-picker-from" ).datepicker( "option", "maxDate", selectedDate );
                }
            });
        },

        holiday: {
            create: function(e) {
                e.preventDefault();
             
                $.erpPopup({
                    title: wpErpHr.popup.holiday,
                    button: wpErpHr.popup.holiday_create,
                    id: 'erp-hr-holiday-create-popup',
                    content: wperp.template('erp-hr-holiday-js-tmp')({ data: null }).trim(),
                    extraClass: 'smaller',
                    onReady: function() {
                        Leave.initDateField();
                    },
                    onSubmit: function(modal) {
                        e.data.policy.submit.call(this, modal);
                    }
                }); //popup
            },
        },

        policy: {
            submit: function(modal) {
                wp.ajax.send( {
                    data: this.serializeObject(),
                    success: function() {
                        modal.closeModal();

                        $( '.list-table-wrap' ).load( window.location.href + ' .list-wrap-inner' );
                    },
                    error: function(error) {
                        modal.enableButton();
                        alert( error );
                    }
                });
            },
            create: function(e) {
                e.preventDefault();

                $.erpPopup({
                    title: wpErpHr.popup.policy,
                    button: wpErpHr.popup.policy_create,
                    id: 'erp-hr-leave-policy-create-popup',
                    content: wp.template('erp-leave-policy')({ data: null }).trim(),
                    extraClass: 'smaller',
                    onReady: function() {
                        $('.erp-color-picker').wpColorPicker();
                    },
                    onSubmit: function(modal) {
                        e.data.policy.submit.call(this, modal);
                    }
                }); //popup
            },

            edit: function(e) {
                e.preventDefault();

                var self = $(this),
                    data = self.closest('tr').data('json');

                $.erpPopup({
                    title: wpErpHr.popup.policy,
                    button: wpErpHr.popup.update_status,
                    id: 'erp-hr-leave-policy-edit-popup',
                    content: wp.template('erp-leave-policy')(data).trim(),
                    extraClass: 'smaller',
                    onReady: function() {
                        $('.erp-color-picker').wpColorPicker();
                    },
                    onSubmit: function(modal) {
                        e.data.policy.submit.call(this, modal);
                    }
                }); //popup
            },

            remove: function(e) {
                e.preventDefault();

                var self = $(this);

                if ( confirm( wpErpHr.delConfirmEmployee ) ) {
                    wp.ajax.send( 'erp-hr-leave-policy-delete', {
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

        leave: {
            requestDates: function() {
                var from = $('#leave_from').val(),
                    to = $('#leave_to').val(),
                    submit = $(this).closest('form').find('input[type=submit]'),
                    user_id = parseInt( $( '#employee_id').val() );

                if ( from !== '' && to !== '' ) {

                    wp.ajax.send( 'erp-hr-leave-request-req-date', {
                        data: {
                            '_wpnonce': wpErpHr.nonce,
                            from: from,
                            to: to,
                            employee_id: user_id
                        },
                        success: function(resp) {
                            var html = wp.template('erp-leave-days')(resp);

                            $('li.show-days').html( html );
                            submit.removeAttr('disabled');
                        },
                        error: function(response) {
                            $('li.show-days').empty();
                            submit.attr( 'disabled', 'disable' );
                            alert( response );
                        }
                    });
                }
            },

            showHistory: function(e) {
                e.preventDefault();

                var form = $(this);

                wp.ajax.send( 'erp-hr-empl-leave-history', {
                    data: form.serializeObject(),
                    success: function(resp) {
                        $('table#erp-hr-empl-leave-history tbody').html(resp);
                    }
                } );
            }
        }
    };

    $(function() {
        Leave.initialize();
    });

})(jQuery);