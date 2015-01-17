/* jshint devel:true */
/* global wpErpHr */
/* global wp */

;(function($) {
    'use strict';

    var Leave = {

        initialize: function() {
            $( '.erp-hr-leave-policy').on( 'click', 'a#erp-leave-policy-new', this.policy.create );
        },

        policy: {
            create: function(e) {
                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                $.erpPopup({
                    title: wpErpHr.popup.policy,
                    button: wpErpHr.popup.policy_create,
                    content: wp.template('erp-leave-policy')({ data: null }),
                    extraClass: 'smaller',
                    onReady: function() {
                        $('.erp-color-picker').wpColorPicker();
                    },
                    onSubmit: function(modal) {
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
                    }
                }); //popup
            }
        }
    };

    $(function() {
        Leave.initialize();

        console.log('leave initialize');
    });

})(jQuery);