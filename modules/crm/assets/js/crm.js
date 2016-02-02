/* jshint devel:true */
/* global wpErpCrm */
/* global wp */

;(function($) {
    'use strict';

    var WeDevs_ERP_CRM = {

        initialize: function() {
            // Customer
            $( 'body' ).on( 'click', 'a.erp-contact-new', this.customer.create );
            $( '.erp-crm-customer' ).on( 'click', 'span.edit a', this.customer.edit );
            $( '.erp-crm-customer' ).on( 'click', 'a.submitdelete', this.customer.remove );
            $( '.erp-crm-customer' ).on( 'click', 'a.restoreCustomer', this.customer.restore );

            // Customer single view
            $( '.erp-single-customer' ).on( 'click', '#erp-customer-add-company', this.customerSingle.addCompany );
            $( '.erp-single-customer' ).on( 'click', 'a.erp-customer-edit-company', this.customerSingle.editCompany );
            $( '.erp-single-customer' ).on( 'click', 'a.erp-customer-delete-company', this.customerSingle.removeCompany );

            // Contact Group
            $( '.erp-crm-contact-group' ).on( 'click', 'a.erp-new-contact-group', this.contactGroup.create );

            // photos
            $( 'body' ).on( 'click', 'a#erp-set-customer-photo', this.customer.setPhoto );
            $( 'body' ).on( 'click', 'a.erp-remove-photo', this.customer.removePhoto );

            $( 'body' ).on('change', 'select.erp-country-select', this.populateState );

            // Trigger
            $( 'body' ).on( 'erp-crm-after-customer-new-company', this.customer.afterNew );

            $('body').on( 'click', 'div.erp-handlediv', this.handlePostboxToggle );

            $('body').on( 'change', 'input[type=checkbox][name="all_day"]', this.triggerCustomerScheduleAllDay );
            $('body').on( 'change', 'input[type=checkbox][name="allow_notification"]', this.triggerCustomerScheduleAllowNotification );

            $('.erp-crm-tips').tipTip( {
                defaultPosition: "top",
                fadeIn: 100,
                fadeOut: 100,
                // content: content
            } );

        },

        triggerCustomerScheduleAllDay: function() {
            var self = $(this);

            if ( self.is(':checked') ) {
                self.closest('div.schedule-datetime').find('.erp-time-field').hide();
                self.closest('div.schedule-datetime').find('.datetime-sep').hide();
            } else {
                self.closest('div.schedule-datetime').find('.erp-time-field').show();
                self.closest('div.schedule-datetime').find('.datetime-sep').show();
            };
        },

        triggerCustomerScheduleAllowNotification: function() {
            var self = $(this);

            if ( self.is(':checked') ) {
                self.closest('.erp-crm-customer-feed-edit-wrapper').find('#schedule-notification-wrap').show();
            } else {
                self.closest('.erp-crm-customer-feed-edit-wrapper').find('#schedule-notification-wrap').hide();
            };
        },

        /**
         * Timepicker initialize
         *
         * @return {[void]}
         */
        initTimePicker: function() {
            $( '.erp-time-field' ).timepicker({
                'scrollDefault': 'now',
                'step': 15
            });
        },

        /**
         * Handle postbox toggle effect
         *
         * @param  {object} e
         *
         * @return {void}
         */
        handlePostboxToggle: function(e) {
            e.preventDefault();
            var self = $(this),
                postboxDiv = self.closest('.postbox');

            if ( postboxDiv.hasClass('closed') ) {
                postboxDiv.removeClass('closed');
            } else {
                postboxDiv.addClass('closed');
            }
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
             * After create new customer
             *
             * @return false
             */
            afterNew: function( e, res ) {
                var selectdrop = $('.erp-crm-customer-company-dropdown');
                wperp.scriptReload( 'erp-crm-customer-company-reload', 'tmpl-erp-crm-new-assign-company' );
                if ( res.type == 'company' ) {
                    selectdrop.append('<option selected="selected" value="'+res.id+'">'+res.company+'</option>');
                } else {
                    selectdrop.append('<option selected="selected" value="'+res.id+'">' + res.first_name + ' ' + res.last_name + '</option>');
                };
                selectdrop.select2("val", res.id);
            },

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

                var self    = $(this),
                is_single   = self.data( 'single' );

                wpErpCrm.customer_empty.type = self.data('type');

                $.erpPopup({
                    title: self.attr('title'),
                    button: wpErpCrm.add_submit,
                    id: 'erp-crm-new-contact',
                    content: wperp.template('erp-crm-new-contact')(  wpErpCrm.customer_empty  ).trim(),
                    extraClass: 'midium',
                    onReady: function() {
                        WeDevs_ERP_CRM.customer.select2Action('erp-crm-select2');
                        $( 'body' ).find('select#erp-customer-type').trigger('change');
                    },
                    onSubmit: function(modal) {
                        modal.disableButton();

                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function( res ) {

                                if ( is_single == '1' ) {
                                    $('body').trigger( 'erp-crm-after-customer-new-company', [res]);
                                } else {
                                    WeDevs_ERP_CRM.customer.pageReload();
                                }

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
             * select2 action
             *
             * @return  {void}
             */
            select2Action: function(element) {
                $('.'+element).select2({
                    width: 'element',
                });
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

                var self = $(this),
                single_view = self.data( 'single_view' );

                $.erpPopup({
                    title: self.attr('title'),
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

                                $('select#erp-customer-type').trigger('change');
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
                                if ( single_view == '1' ) {
                                    $( '.erp-single-customer-row' ).load( window.location.href + ' .left-content' );
                                } else {
                                    WeDevs_ERP_CRM.customer.pageReload();
                                }
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
             * @param {object} e
             *
             * @return {[void]}
             */
            remove: function(e) {
                e.preventDefault();

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
            },

            /**
             * Restore customer from trash
             *
             * @param  {[object]} e
             *
             * @return {[void]}
             */
            restore: function(e) {
                e.preventDefault();

                var self = $(this);

                if ( confirm( wpErpCrm.confirm ) ) {
                    wp.ajax.send( 'erp-crm-customer-restore', {
                        data: {
                            '_wpnonce': wpErpCrm.nonce,
                            id: self.data( 'id' ),
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
        },

        /**
         * Customer single page functionality
         *
         * @type {Object}
         *
         * @return {mixed}
         */
        customerSingle: {

            pageReload: function(e) {
                $( '.erp-single-customer' ).load( window.location.href + ' .erp-single-customer' );
            },

            addCompany: function(e) {
                e.preventDefault();

                var self = $(this),
                    data = {
                        id : self.data('id'),
                        type : self.data('type'),
                    };

                $.erpPopup({
                    title: self.attr('title'),
                    button: wpErpCrm.save_submit,
                    id: 'erp-crm-single-contact-company',
                    content: wperp.template('erp-crm-new-assign-company')( data ).trim(),
                    extraClass: 'smaller',
                    onReady: function() {
                        WeDevs_ERP_CRM.customerSingle.select2AddMoreContent();
                    },

                    onSubmit: function(modal) {
                        modal.disableButton();

                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(res) {
                                $( '.company-profile-content' ).load( window.location.href + ' .company-list' );
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
             * Customer Single Edit Company
             */
            editCompany: function(e) {
                e.preventDefault();

                var self = $( this ),
                query_id = self.data( 'id' );

                $.erpPopup({
                    title: wpErpCrm.popup.customer_update_title,
                    button: wpErpCrm.update_submit,
                    id: 'erp-crm-single-edit-company',
                    extraClass: 'smaller',
                    onReady: function() {
                        var modal = this;

                        $( 'header', modal).after( $('<div class="loader"></div>').show() );

                        wp.ajax.send( 'erp-crm-customer-edit-company', {
                            data: {
                                id: query_id,
                                _wpnonce: wpErpCrm.nonce
                            },
                            success: function( res ) {
                                var html = wp.template( 'erp-crm-customer-edit-company' )( res );
                                $( '.content', modal ).html( html );
                                $( '.loader', modal ).remove();

                                $( '.row[data-selected]', modal ).each(function() {
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

                });

            },

            /**
             *  Remove company from customer single profile
             */
            removeCompany: function(e) {
                e.preventDefault();

                var self = $(this);

                if ( confirm( wpErpCrm.confirm ) ) {
                    wp.ajax.send( self.data('action'), {
                        data: {
                            id: self.data('id'),
                            _wpnonce: wpErpCrm.nonce
                        },
                        success: function( res ) {
                            self.closest('div.postbox').fadeOut();
                        }
                    });
                }
            },

            /**
             * select2 with add more button content
             *
             * @return  {void}
             */
            select2AddMoreContent: function() {
                var selects = $('.erp-crm-select2-add-more');
                $.each( selects, function( key, element ) {
                   WeDevs_ERP_CRM.customerSingle.select2AddMoreActive(element);
                });
            },

            /**
             * select2 with add more button active
             *
             * @return  {void}
             */
            select2AddMoreActive: function(element) {
                var id      = $(element).data('id'),
                type        = $(element).data('type'),
                single      = $(element).data('single');

                $(element).select2({
                    width: 'element',
                    "language": {
                        noResults: function(){
                           return '<a href="#" class="button button-primary '+id+'" data-type="'+type+'" data-single="'+single+'">Add New</a>';
                        }
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }

                });
            },

            /**
             * select2 action
             *
             * @return  {void}
             */
            select2Action: function(element) {
                $('.'+element).select2({
                    width: 'element',
                });
            },
        },

        contactGroup : {

            pageReload: function() {
                $( '.erp-crm-contact-group' ).load( window.location.href + ' .erp-crm-contact-group' );
            },

            create: function(e) {
                e.preventDefault();

                var self    = $(this);

                $.erpPopup({
                    title: self.attr('title'),
                    button: wpErpCrm.add_submit,
                    id: 'erp-crm-new-contact-group',
                    content: wperp.template('erp-crm-new-contact-group')({ data:{} }).trim(),
                    extraClass: 'smaller',

                    onSubmit: function(modal) {
                        modal.disableButton();

                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function( res ) {
                                WeDevs_ERP_CRM.contactGroup.pageReload();
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
            }
        }
    }

    $(function() {
        WeDevs_ERP_CRM.initialize();
    });

})(jQuery);