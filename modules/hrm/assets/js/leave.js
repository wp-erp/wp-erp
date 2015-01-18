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

                $.erpPopup({
                    title: wpErpHr.popup.policy,
                    button: wpErpHr.popup.policy_create,
                    content: wp.template('erp-leave-policy')({ data: null }),
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
                    content: wp.template('erp-leave-policy')(data),
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
        }
    };

    $(function() {
        Leave.initialize();

        console.log('leave initialize');
    });

})(jQuery);