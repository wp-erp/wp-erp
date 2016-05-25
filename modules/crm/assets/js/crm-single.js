;( function($) {
    Vue.config.debug = 1;

    var tableColumns = [
        {
            name: 'name',
            title: ( wpErpCrm.contact_type == 'contact') ? 'Contact name' : 'Company name',
            callback: 'fullName',
            sortField: 'id',
        },
        {
            name: 'email',
            title: 'Email Address'

        },
        {
            name: 'phone',
            title: 'Phone'
        },
        {
            name: 'life_stage',
            title: 'Life stage',
            callback: 'lifeStage',
        },

        {
            name: 'contact_owner',
            title: 'Owner',
            callback: 'contactOwner',
        },

        {
            name: 'created',
            title: 'Created At',
            sortField: 'created'
        }
    ];

    var bulkactions = [
        {
            id : 'delete',
            text : 'Delete',
            showIf : 'whenNotTrased'
        },

        {
            id : 'permanent_delete',
            text : 'Permanent Delete',
            showIf : 'onlyTrased'
        },

        {
            id : 'restore',
            text : 'Restore',
            showIf : 'onlyTrased'
        },

        {
            id : 'assign_group',
            text : 'Assign Group',
            showIf : 'whenNotTrased'
        }
    ];

    var extraBulkAction = {
        'filterContactOwner' : {
            name: 'filter_assign_contact',
            type: 'select', // or text|email|number|url|datefield
            id: 'erp-select-user-for-assign-contact',
            class: 'test-class',
            placeholder: 'Select an agent',
            options: [
                {
                    id : '',
                    text: ''
                }
            ]
        }
    }

    var contact = new Vue({
        el: '#wp-erp',
        data : {
            fields: tableColumns,
            itemRowActions: [
                {
                    title: 'Edit',
                    attrTitle: 'Edit this contact',
                    class: 'edit',
                    action: 'edit'
                },
                {
                    title: 'View',
                    attrTitle: 'View this contact',
                    class: 'view',
                    action: 'view',
                },
                {
                    title: 'Delete',
                    attrTitle: 'Delete this contact',
                    class: 'delete',
                    action: 'delete',
                    showIf: 'whenNotTrased'
                },
                {
                    title: 'Permanent Delete',
                    attrTitle: 'Permanent Delete this contact',
                    class: 'delete',
                    action: 'permanent_delete',
                    showIf: 'onlyTrased'
                },
                {
                    title: 'Restore',
                    attrTitle: 'Restore this contact',
                    class: 'restore',
                    action: 'restore',
                    showIf: 'onlyTrased'
                },
            ],
            topNavFilter: {
                data: wpErpCrm.statuses,
                default: 'all',
                field: 'status'
            },
            bulkactions: bulkactions,
            extraBulkAction: extraBulkAction,
            additionalParams: {
                'type' : wpErpCrm.contact_type
            },
            search: {
                params: 's',
                wrapperClass: '',
                screenReaderText: ( wpErpCrm.contact_type == 'company' ) ? 'Search Compnay' : 'Search Contact',
                inputId: 'search-input',
                btnId: 'search-submit',
                placeholder: ( wpErpCrm.contact_type == 'company' ) ? 'Search Compnay' : 'Search Contact',
            },
            isRequestDone: false
        },

        methods: {
            fullName: function( value, item ) {
                if ( wpErpCrm.contact_type == 'contact' ) {
                    var link  = '<a href="' + item.details_url + '"><strong>' + item.first_name + ' '+ item.last_name + '</strong></a>';
                } else {
                    var link  = '<a href="' + item.details_url + '"><strong>' + item.company + '</strong></a>';
                }
                return item.avatar.img + link;
            },

            lifeStage: function( value, item ) {
                return wpErpCrm.life_stages[value];
            },

            contactOwner: function( value, item ) {
                return ( Object.keys( item.assign_to ).length > 0 ) ? '<a href="#">' + item.assign_to.display_name + '</a>' : '—';
            },

            onlyTrased: function( rowAction ) {
                if ( this.$refs.vtable.currentTopNavFilter == 'trash' ) {
                    return true;
                }
                return false;
            },

            whenNotTrased: function( rowAction ) {
                if ( this.$refs.vtable.currentTopNavFilter != 'trash' ) {
                    return true;
                }
                return false;
            },

            initFields: function() {
                $( '.erp-date-field').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-100:+0',
                });

                $( '.erp-select2' ).select2({
                    placeholder: $(this).attr('data-placeholder')
                });

            },

            addContact: function( type, title ) {
                var self = this;

                $.erpPopup({
                    title: title,
                    button: wpErpCrm.add_submit,
                    id: 'erp-crm-new-contact',
                    content: wperp.template('erp-crm-new-contact')(  wpErpCrm.customer_empty  ).trim(),
                    extraClass: 'midium',
                    onReady: function() {
                        self.initFields();
                    },
                    onSubmit: function(modal) {
                        modal.disableButton();

                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function( res ) {
                                modal.enableButton();
                                modal.closeModal();
                                self.$refs.vtable.tableData.unshift(res.data);
                                self.$refs.vtable.topNavFilter.data = res.statuses;
                            },
                            error: function(error) {
                                modal.enableButton();
                                alert( error );
                            }
                        });
                    }
                });
            },

            editContact: function( data, index ) {
                var self = this;

                $.erpPopup({
                    title: 'Edit this customer',
                    button: wpErpCrm.update_submit,
                    id: 'erp-customer-edit',
                    onReady: function() {
                        var modal = this;
                        $( 'header', modal).after( $('<div class="loader"></div>').show() );
                        wp.ajax.send( 'erp-crm-customer-get', {
                            data: {
                                id: data.id,
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
                                $( '.erp-select2' ).select2();
                                $( 'select.erp-country-select').change();

                                $( 'li[data-selected]', modal ).each(function() {
                                    var self = $(this),
                                        selected = self.data('selected');

                                    if ( selected !== '' ) {
                                        self.find( 'select' ).val( selected );
                                    }
                                });

                                _.each( $( 'input[type=checkbox].erp-crm-contact-group-class' ), function( el, i) {
                                    var optionsVal = $(el).val();
                                    if( _.contains( response.group_id, optionsVal ) ) {
                                        $(el).prop('checked', true );
                                    }
                                });

                                self.initFields();
                            }
                        });
                    },
                    onSubmit: function(modal) {
                        modal.disableButton();

                        wp.ajax.send( {
                            data: this.serialize(),
                            success: function(res) {
                                modal.enableButton();
                                modal.closeModal();
                                self.$refs.vtable.tableData.$set( index, res.data );
                                self.$refs.vtable.topNavFilter.data = res.statuses;
                            },
                            error: function(error) {
                                modal.enableButton();
                                alert( error );
                            }
                        });
                    }
                });
            },

            deleteContact: function( data, type, hard, isBulk ) {
                var self = this;

                if ( isBulk ) {
                    self.$refs.vtable.ajaxloader = true;
                }

                if ( confirm( wpErpCrm.delConfirmCustomer ) ) {
                    wp.ajax.send( 'erp-crm-customer-delete', {
                        data: {
                            _wpnonce: wpErpCrm.nonce,
                            id: ( isBulk ) ? data : data.id,
                            hard: ( hard == true ) ? 1 : 0,
                            type: type
                        },
                        success: function(res) {
                            if ( isBulk ) {
                                self.$nextTick(function() {
                                    this.$broadcast('vtable:reload')
                                });
                                self.$refs.vtable.ajaxloader = false;
                            } else {
                                self.$refs.vtable.tableData.$remove( data );
                                self.$nextTick(function() {
                                    this.$broadcast('vtable:reload')
                                });
                            }
                            self.$refs.vtable.topNavFilter.data = res.statuses;
                        },
                        error: function(res) {
                            alert( res );
                        }
                    });
                } else {
                    self.$refs.vtable.ajaxloader = false;
                }
            },

            restoreContact: function( data, type, isBulk ) {
                var self = this;

                if ( isBulk ) {
                    self.$refs.vtable.ajaxloader = true;
                }

                if ( confirm( wpErpCrm.confirm ) ) {
                    wp.ajax.send( 'erp-crm-customer-restore', {
                        data: {
                            _wpnonce: wpErpCrm.nonce,
                            id: ( isBulk ) ? data : data.id,
                            type: type
                        },
                        success: function(res) {
                            if ( isBulk ) {
                                self.$nextTick(function() {
                                    this.$broadcast('vtable:reload')
                                });
                                self.$refs.vtable.ajaxloader = false;
                            } else {
                                self.$refs.vtable.tableData.$remove( data );
                                self.$nextTick(function() {
                                    this.$broadcast('vtable:reload')
                                });
                            }
                            self.$refs.vtable.topNavFilter.data = res.statuses;
                        },
                        error: function(res) {
                            alert( res );
                        }
                    });
                } else {
                    self.$refs.vtable.ajaxloader = false;
                }
            },

            assignContact: function( ids, type ) {
                var self = this;

                if ( ids.length > 0 ) {
                    $.erpPopup({
                        title: wpErpCrm.popup.customer_assing_group,
                        button: wpErpCrm.add_submit,
                        id: 'erp-crm-customer-bulk-assign-group',
                        content: wperp.template('erp-crm-new-bulk-contact-group')({ user_id:ids }).trim(),
                        extraClass: 'smaller',

                        onSubmit: function(modal) {
                            modal.disableButton();

                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function( res ) {
                                    modal.enableButton();
                                    modal.closeModal();
                                    self.$broadcast('vtable:refresh');
                                },
                                error: function(error) {
                                    modal.enableButton();
                                    alert( error );
                                }
                            });
                        }
                    }); //popup

                } else {
                    alert( wpErpCrm.checkedConfirm );
                }
            },

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

            removePhoto: function(e) {
                e.preventDefault();

                var html = '<a href="#" id="erp-set-customer-photo" class="button button-small">' + wpErpCrm.customer_upload_photo + '</a>';
                    html += '<input type="hidden" name="photo_id" id="custossmer-photo-id" value="0">';

                $( '.photo-container', '.erp-customer-form' ).html( html );
            },

            checkEmailForContact: function(e) {

                var self = $(e.target),
                    form = self.closest('form'),
                    val = self.val(),
                    type = form.find('#erp-customer-type').val(),
                    id   = form.find('#erp-customer-id').val();

                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

                if ( val == '' || !re.test( val ) ) {
                    return false;
                }

                if ( id != '0' ) {
                    return false;
                }

                wp.ajax.send( 'erp_people_exists', {
                    data: {
                        email: val,
                        _wpnonce: wpErpCrm.nonce
                    },
                    success: function() {
                        form.find('.modal-suggession').fadeOut( 300, function() {
                            $(this).remove();
                            form.find('.content-container').css({ 'marginTop': '0px' });
                        });
                        form.find('button[type=submit]' ).removeAttr( 'disabled' );
                    },
                    error: function( response ) {
                        form.find('button[type=submit]' ).attr( 'disabled', 'disabled');

                        if ( $.inArray( 'contact', response.types ) != -1 || $.inArray( 'company', response.types ) != -1 ) {
                            form.find('.modal-suggession').remove();
                            form.find('header.modal-header').append('<div class="modal-suggession">' + wpErpCrm.contact_exit + '</div>');
                        } else {
                            form.find('.modal-suggession').remove();
                            form.find('header.modal-header').append('<div class="modal-suggession">' + wpErpCrm.make_contact_text + ' ' + type + ' ? <a href="#" id="erp-crm-create-contact-other-type" data-type="'+ type +'" data-user_id="'+ response.id +'">' + wpErpCrm.create_contact_text + ' ' + type + '</a></div>');
                        }

                        $('.modal-suggession').hide().slideDown( function() {
                            form.find('.content-container').css({ 'marginTop': '15px' });
                        });
                    }
                });
            },

            makeUserAsContact: function(e) {
                e.preventDefault();

                var selfVue = this;

                var self = $(e.target),
                    type = self.data('type'),
                    user_id = self.data('user_id');


                if ( this.isRequestDone ) {
                    return;
                }

                this.isRequestDone = true;
                self.closest('.modal-suggession').append('<div class="erp-loader" style="top:9px; right:10px;"></div>');

                wp.ajax.send( 'erp-crm-convert-user-to-contact', {
                    data: {
                        user_id: user_id,
                        type: type,
                        _wpnonce: wpErpCrm.nonce
                    },
                    success: function() {
                        this.isRequestDone = false;
                        self.closest('.modal-suggession').find('.erp-loader').remove();
                        self.closest('.erp-modal').remove();
                        $('.erp-modal-backdrop').remove();

                        $.erpPopup({
                            title: wpErpCrm.update_submit + ' ' + type,
                            button: wpErpCrm.update_submit,
                            id: 'erp-customer-edit',
                            onReady: function() {
                                var modal = this;

                                $( 'header', modal).after( $('<div class="loader"></div>').show() );

                                wp.ajax.send( 'erp-crm-customer-get', {
                                    data: {
                                        id: user_id,
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
                                        $( '.erp-select2' ).select2();
                                        $( 'select.erp-country-select').change();

                                        $( 'li[data-selected]', modal ).each(function() {
                                            var self = $(this),
                                                selected = self.data('selected');

                                            if ( selected !== '' ) {
                                                self.find( 'select' ).val( selected );
                                            }
                                        });

                                        _.each( $( 'input[type=checkbox].erp-crm-contact-group-class' ), function( el, i) {
                                            var optionsVal = $(el).val();
                                            if( _.contains( response.group_id, optionsVal ) ) {
                                                $(el).prop('checked', true );
                                            }
                                        });


                                        selfVue.initFields();
                                    }
                                });
                            },
                            onSubmit: function(modal) {
                                modal.disableButton();

                                wp.ajax.send( {
                                    data: this.serialize(),
                                    success: function(response) {
                                        modal.enableButton();
                                        modal.closeModal();
                                        selfVue.$refs.vtable.tableData.unshift(response.data);
                                        selfVue.$refs.vtable.topNavFilter.data = response.statuses;
                                    },
                                    error: function(error) {
                                        modal.enableButton();
                                        alert( error );
                                    }
                                });
                            }
                        });

                    },
                    error: function( response ) {
                        isRequestDone = false;
                        alert(response);
                    }
                });
            },

            initSearchCrmAgent: function() {
                $( 'select#erp-select-user-for-assign-contact' ).select2({
                    allowClear: true,
                    placeholder: 'Select an Agent',
                    minimumInputLength: 3,
                    ajax: {
                        url: wpErpCrm.ajaxurl,
                        dataType: 'json',
                        delay: 250,
                        escapeMarkup: function( m ) {
                            return m;
                        },
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                _wpnonce: wpErpCrm.nonce,
                                action: 'erp-search-crm-user'
                            };
                        },
                        processResults: function ( data, params ) {
                            var terms = [];

                            if ( data) {
                                $.each( data.data, function( id, text ) {
                                    terms.push({
                                        id: id,
                                        text: text
                                    });
                                });
                            }

                            if ( terms.length ) {
                                return { results: terms };
                            } else {
                                return { results: '' };
                            }
                        },
                        cache: true
                    }
                });
            }

        },

        ready: function() {
            var self = this;

            $( 'body' ).on( 'click', 'a#erp-set-customer-photo', this.setPhoto );
            $( 'body' ).on( 'click', 'a.erp-remove-photo', this.removePhoto );
            $( 'body' ).on( 'focusout', 'input#erp-crm-new-contact-email', this.checkEmailForContact );
            $( 'body' ).on( 'click', 'a#erp-crm-create-contact-other-type', this.makeUserAsContact );

            this.initSearchCrmAgent();
        },

        events: {
            'vtable:action': function( action, data, index ) {
                if ( 'edit' == action ) {
                    this.editContact( data, index );
                }

                if ( 'delete' == action ) {
                    this.deleteContact( data, wpErpCrm.contact_type, false, false );
                }

                if ( 'restore' == action ) {
                    this.restoreContact( data, wpErpCrm.contact_type,false );
                }

                if ( 'permanent_delete' == action ) {
                    this.deleteContact( data, wpErpCrm.contact_type, true, false );
                }
            },

            'vtable:default-bulk-action': function( action, ids ) {
                // Handle bulk action when action is something with ID's
                if ( 'delete' === action ) {
                    this.deleteContact( ids, wpErpCrm.contact_type, false, true );
                }

                if ( 'permanent_delete' === action ) {
                    this.deleteContact( ids, wpErpCrm.contact_type, true, true );
                }

                if ( 'restore' === action ) {
                    this.restoreContact( ids, wpErpCrm.contact_type, true );
                }

                if ( 'assign_group' === action ) {
                    this.assignContact( ids, wpErpCrm.contact_type );
                }
            }
        }
    });

})(jQuery)
