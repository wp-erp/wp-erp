/* jshint devel:true */
/* global wpErpCrm */
/* global wp */

;(function($) {
    'use strict';

    var WeDevs_ERP_CRM = {

        initialize: function() {

            // Customer
            $( '.erp-crm-customer' ).on( 'click', 'a#erp-contact-new', this.customer.create );
            $( '.erp-crm-customer' ).on( 'click', 'span.edit a', this.customer.edit );
            $( '.erp-crm-customer' ).on( 'click', 'a.submitdelete', this.customer.remove );

            // photos
            $( 'body' ).on( 'click', 'a#erp-set-customer-photo', this.customer.setPhoto );
            $( 'body' ).on( 'click', 'a.erp-remove-photo', this.customer.removePhoto );

            $( 'body' ).on('change', 'select.erp-country-select', this.populateState );

        },

        /**
         * Populate the state dropdown based on selected country
         *
         * @return {void}
         */
        populateState: function() {

            if ( typeof wpErpCrm.wpErpCountries === 'undefined' ) {
                return false;
            }

            var self = $(this),
                country = self.val(),
                parent = self.closest( self.data('parent') ),
                empty = '<option val="">-------------</option>';

            if ( wpErpCrm.wpErpCountries[ country ] ) {
                var options = '',
                    state = wpErpCrm.wpErpCountries[ country ];

                for ( var index in state ) {
                    options = options + '<option value="' + index + '">' + state[ index ] + '</option>';
                }

                if ( $.isArray( wpErpCrm.wpErpCountries[ country ] ) ) {
                    parent.find('select.erp-state-select').html( empty );
                } else {
                    parent.find('select.erp-state-select').html( options );
                }

            } else {
                parent.find('select.erp-state-select').html( empty );
            }
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
                    title: wpErpCrm.customer_upload_photo,
                    button: { text: wpErpCrm.customer_set_photo }
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

                var html = '<a href="#" id="erp-set-customer-photo" class="button button-small">' + wpErpCrm.customer_upload_photo + '</a>';
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
                    button: wpErpCrm.add_submit,
                    id: 'erp-crm-new-contact',
                    content: wperp.template('erp-crm-new-contact')(  wpErpCrm.customer_empty  ).trim(),
                    extraClass: 'midium',
                    onReady: function() {
                        $( '.select2' ).select2();
                    },
                    onSubmit: function(modal) {
                        modal.disableButton();

                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(res) {
                                WeDevs_ERP_CRM.customer.pageReload();
                                modal.enableButton();
                                modal.closeModal();
                            },
                            error: function(error) {
                                modal.enableButton();
                                alert( error );
                            }
                        });
                    }
                }); //popup
            },

            /**
             * Edit customer data
             *
             * @param  {[object]} e
             *
             * @return {[void]}
             */
            edit: function(e) {
                e.preventDefault();

                var self = $(this);

                $.erpPopup({
                    title: wpErpCrm.popup.customer_update_title,
                    button: wpErpCrm.update_submit,
                    id: 'erp-customer-edit',
                    onReady: function() {
                        var modal = this;

                        $( 'header', modal).after( $('<div class="loader"></div>').show() );

                        wp.ajax.send( 'erp-crm-customer-get', {
                            data: {
                                id: self.data( 'id' ),
                                _wpnonce: wpErpCrm.nonce
                            },
                            success: function( response ) {
                                var html = wp.template('erp-crm-new-contact')( response );
                                $( '.content', modal ).html( html );
                                $( '.loader', modal).remove();

                                $( 'li[data-selected]', modal ).each(function() {
                                    var self = $(this),
                                        selected = self.data('selected');

                                    if ( selected !== '' ) {
                                        self.find( 'select' ).val( selected );
                                    }
                                });

                                $( '.select2' ).select2();
                                $( 'select.erp-country-select').change();

                                $( 'li[data-selected]', modal ).each(function() {
                                    var self = $(this),
                                        selected = self.data('selected');

                                    if ( selected !== '' ) {
                                        self.find( 'select' ).val( selected );
                                    }
                                });
                            }
                        });
                    },
                    onSubmit: function(modal) {
                        modal.disableButton();

                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(response) {
                                WeDevs_ERP_CRM.customer.pageReload();
                                modal.enableButton();
                                modal.closeModal();
                            },
                            error: function(error) {
                                modal.enableButton();
                                alert( error );
                            }
                        });
                    }
                });
            },

            /**
             * Remove customer data with meta
             *
             * @return {[void]}
             */
            remove: function(e) {
                var self = $(this);

                if ( confirm( wpErpCrm.delConfirmCustomer ) ) {
                    wp.ajax.send( 'erp-crm-customer-delete', {
                        data: {
                            '_wpnonce': wpErpCrm.nonce,
                            id: self.data( 'id' ),
                            hard: self.data( 'hard' )
                        },
                        success: function() {
                            self.closest('tr').fadeOut( 'fast', function() {
                                $(this).remove();
                                WeDevs_ERP_CRM.customer.pageReload();
                            });
                        },
                        error: function(response) {
                            alert( response );
                        }
                    });
                }

            }

        }

    }

    $(function() {
        WeDevs_ERP_CRM.initialize();
    });

})(jQuery);