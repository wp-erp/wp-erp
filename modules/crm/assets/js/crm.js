/* jshint devel:true */
/* global wpErpCrm */
/* global wp */

;(function($) {
    'use strict';

    var WeDevs_ERP_CRM = {

        initialize: function() {

            // Customer
            $( '.erp-crm-customer' ).on( 'click', 'a#erp-contact-new', this.customer.create );

            // photos
            $( 'body' ).on( 'click', 'a#erp-set-customer-photo', this.customer.setPhoto );
            $( 'body' ).on( 'click', 'a.erp-remove-photo', this.customer.removePhoto );

        },

        customer: {

            /**
             * Reload the department area
             *
             * @return {void}
             */
            pageReload: function() {
                $( '.erp-crm-customer' ).load( window.location.href + ' .erp-crm-customer' );
            },

            /**
             * Set photo popup
             *
             * @param {event}
             */
            setPhoto: function(e) {
                e.preventDefault();
                e.stopPropagation();

                var frame;

                if ( frame ) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: wpErpHr.customer_upload_photo,
                    button: { text: wpErpHr.customer_set_photo }
                });

                frame.on('select', function() {
                    var selection = frame.state().get('selection');

                    selection.map( function( attachment ) {
                        attachment = attachment.toJSON();

                        var html = '<img src="' + attachment.url + '" alt="" />';
                            html += '<input type="hidden" id="customer-photo-id" name="photo_id" value="' + attachment.id + '" />';
                            html += '<a href="#" class="erp-remove-photo">&times;</a>';

                        $( '.photo-container', '.erp-customer-form' ).html( html );
                    });
                });

                frame.open();
            },

            /**
             * Remove an employees avatar
             *
             * @param  {event}
             */
            removePhoto: function(e) {
                e.preventDefault();

                var html = '<a href="#" id="erp-set-customer-photo" class="button button-small">' + wpErpHr.emp_upload_photo + '</a>';
                    html += '<input type="hidden" name="photo_id" id="custossmer-photo-id" value="0">';

                $( '.photo-container', '.erp-customer-form' ).html( html );
            },

            /**
             * Create New customer
             *
             * @param  {object} e
             *
             * @return {void}
             */
            create: function(e) {
                e.preventDefault();

                var self = $(this);

                $.erpPopup({
                    title: wpErpCrm.popup.customer_title,
                    button: wpErpCrm.popup.customer_submit,
                    id: 'erp-crm-new-contact',
                    content: wperp.template('erp-crm-new-contact')(  wpErpCrm.customer_empty  ).trim(),
                    extraClass: 'midium',
                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(res) {
                                WeDevs_ERP_CRM.customer.pageReload();
                                modal.closeModal();
                            },
                            error: function(error) {
                                alert( error );
                            }
                        });
                    }
                }); //popup
            }

        }

    }

    $(function() {
        WeDevs_ERP_CRM.initialize();
    });

})(jQuery);