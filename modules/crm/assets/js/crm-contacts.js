;( function($) {
    Vue.config.debug = 1;

    var mixin = {
        methods: {
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

            printObjectValue: function( key, obj, defaultVal ) {
                defaultVal = ( typeof defaultVal == 'undefined' ) ? '—' : defaultVal;
                value = ( obj[key] != '' && obj[key] != '-1' ) ? obj[key] : defaultVal;
                return value;
            },

            handlePostboxToggle: function() {
                var self = $(event.target),
                    postboxDiv = self.closest('.postbox');

                if ( postboxDiv.hasClass('closed') ) {
                    postboxDiv.removeClass('closed');
                } else {
                    postboxDiv.addClass('closed');
                }
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
            },
        }
    }

    if ( $( '.erp-crm-customer-listing' ).length > 0 ) {

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
                class: 'erp-filter-contact-owner',
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
            mixins: [mixin],
            data : {
                wpnonce: wpVueTable.nonce,
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
                        callback: 'contact_view_link'

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

                contact_view_link: function( action, item ) {
                    return '<span class="view"><a href="' + item.details_url + '" title="View this contact">View</a><span> | </span></span>';
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
                        title: 'Edit this' + wpErpCrm.contact_type,
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

                setContactOwnerSearchValue: function() {
                    var value = this.$refs.vtable.getParamByName('filter_assign_contact');
                    if ( value ) {
                        $('select#erp-select-user-for-assign-contact')
                            .append('<option value="' + this.$refs.vtable.customData.filter_assign_contact.id + '" selected>' + this.$refs.vtable.customData.filter_assign_contact.display_name + '</option>').trigger('change')
                    }
                }

            },

            ready: function() {
                var self = this;

                $( 'body' ).on( 'click', 'a#erp-set-customer-photo', this.setPhoto );
                $( 'body' ).on( 'click', 'a.erp-remove-photo', this.removePhoto );
                $( 'body' ).on( 'focusout', 'input#erp-crm-new-contact-email', this.checkEmailForContact );
                $( 'body' ).on( 'click', 'a#erp-crm-create-contact-other-type', this.makeUserAsContact );
                this.initSearchCrmAgent();
                this.setContactOwnerSearchValue();
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
    }

    if ( $( '.erp-single-customer' ).length > 0 ) {
        Vue.component( 'contact-company-relation', {
            props: [ 'id', 'title', 'type', 'addButtonTxt' ],

            mixins:[mixin],

            template:
                '<div class="postbox customer-company-info">'
                    + '<div class="erp-handlediv" @click.prevent="handlePostboxToggle()" title="Click to toggle"><br></div>'
                    + '<h3 class="erp-hndle" @click.prevent="handlePostboxToggle()"><span>{{ title }}</span></h3>'
                    + '<div class="inside company-profile-content">'
                        + '<div class="company-list">'
                            + '<div v-for="item in items" class="postbox closed">'
                                + '<div class="erp-handlediv" @click.prevent="handlePostboxToggle()" title="Click to toggle"><br></div>'
                                + '<h3 class="erp-hndle" @click="handlePostboxToggle()">'
                                    + '<span class="customer-avatar">{{{ item.contact_details.avatar.img }}}</span>'
                                    + '<span class="customer-name">'
                                        + '<a href="{{ item.contact_details.details_url }}" target="_blank" v-if="isCompany( item.contact_details.types )">{{ item.contact_details.company }}</a>'
                                        + '<a href="{{ item.contact_details.details_url }}" target="_blank" v-else>{{ item.contact_details.first_name }}&nbsp;{{ item.contact_details.last_name }}</a>'
                                    + '</span>'
                                + '</h3>'
                                + '<div class="action">'
                                    + '<a href="#" @click.prevent="removeCompany( item )" class="erp-customer-delete-company" data-id="{{ item.contact_details.id }}"><i class="fa fa-trash-o"></i></a>'
                                + '</div>'
                                + '<div class="inside company-profile-content">'
                                    + '<ul class="erp-list separated">'
                                        + '<li><label>Phone</label><span class="sep"> : </span><span class="value" v-if="item.contact_details.phone"><a href="tel:{{ item.contact_details.phone }}">{{ printObjectValue( \'phone\', item.contact_details ) }}</a></span><span v-else>—</span></li>'
                                        + '<li><label>Mobile</label><span class="sep"> : </span><span class="value" v-if="item.contact_details.mobile"><a href="tel:{{ item.contact_details.mobile }}">{{ printObjectValue( \'mobile\', item.contact_details ) }}</a></span><span v-else>—</span></li>'
                                        + '<li><label>Fax</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'fax\', item.contact_details ) }}</span></li>'
                                        + '<li><label>Website</label><span class="sep"> : </span><span class="value" v-if="item.contact_details.website"><a href="{{ item.contact_details.website }}">{{ printObjectValue( \'website\', item.contact_details ) }}</a></span><span v-else>—</span></li>'
                                        + '<li><label>Street 1</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'street_1\', item.contact_details ) }}</span></li>'
                                        + '<li><label>Street 2</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'street_2\', item.contact_details ) }}</span></li>'
                                        + '<li><label>City</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'city\', item.contact_details ) }}</span></li>'
                                        + '<li><label>State</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'state\', item.contact_details ) }}</span></li>'
                                        + '<li><label>Country</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'country\', item.contact_details ) }}</span></li>'
                                        + '<li><label>Postal Code</label><span class="sep"> : </span><span class="value">{{ printObjectValue( \'postal_code\', item.contact_details ) }}</span></li>'
                                    + '</ul>'
                                + '</div>'
                            + '</div>'
                            + '<a href="#" @click.prevent="addCompany()" data-id="" data-type="assign_company" title="{{ addButtonTxt }}" class="button button-primary" id="erp-customer-add-company"><i class="fa fa-plus"></i> {{ addButtonTxt }}</a>'
                        + '</div>'
                    + '</div>'
                + '</div><!-- .postbox -->',

            data: function() {
                return {
                    items : [],
                    assignType : ''
                }
            },

            computed: {
                assignType: function() {
                    return ( wpErpCrm.contact_type == 'contact' ) ? 'assign_company' : 'assign_customer';
                }
            },

            methods: {

                isCompany: function( type ) {
                    return $.inArray( 'company', type ) < 0 ? false : true
                },

                removeCompany: function( item ) {
                    var self = this

                    if ( confirm( wpErpCrm.confirm ) ) {
                        wp.ajax.send( 'erp-crm-customer-remove-company', {
                            data: {
                                id: item.id,
                                _wpnonce: wpErpCrm.nonce
                            },
                            success: function( res ) {
                                self.items.$remove(item);
                            }
                        });
                    }
                },

                addCompany: function() {
                    var self = this,
                        data = {
                            id : this.id,
                            type : this.assignType,
                        };

                    $.erpPopup({
                        title: this.addButtonTxt,
                        button: wpErpCrm.save_submit,
                        id: 'erp-crm-single-contact-company',
                        content: wperp.template('erp-crm-new-assign-company')( data ).trim(),
                        extraClass: 'smaller',
                        onSubmit: function(modal) {
                            modal.disableButton();

                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function(res) {
                                    console.log( res );
                                    self.fetchData();
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

                fetchData: function() {
                    var self = this,
                        data = {
                            id: this.id,
                            action: 'erp-crm-get-contact-companies',
                            type: this.type,
                            _wpnonce: wpErpCrm.nonce
                        };

                    jQuery.post( wpErpCrm.ajaxurl, data, function( resp ) {
                        if ( resp.success ) {
                            self.items = resp.data;
                        } else {
                            alert(resp);
                        }
                    } );
                }
            },

            ready: function() {
                this.fetchData();
            }
        });

        Vue.component( 'contact-assign-group', {
            props: [ 'id', 'title', 'addButtonTxt' ],

            mixins:[mixin],

            template:
                '<div class="postbox customer-mail-subscriber-info">'
                    + '<div class="erp-handlediv" @click.prevent="handlePostboxToggle()" title="Click to toggle"><br></div>'
                    + '<h3 class="erp-hndle" @click.prevent="handlePostboxToggle()"><span>{{ title }}</span></h3>'
                    + '<div class="inside contact-group-content">'
                        + '<div v-if="items" class="contact-group-list">'
                            + '<p v-for="item in items">{{ item.groups.name }}'
                                + '<tooltip :content="subscriberInfo( item )" :title="subscribeInfoToolTip(item)"></tooltip>'
                            + '</p>'
                            + '<a href="#" @click.prevent="assigContactGroup()" id="erp-contact-update-assign-group" data-id="" title="{{ addButtonTxt }}"><i class="fa fa-plus"></i> {{ addButtonTxt }}</a>'
                        + '</div>'
                    + '</div>'
                + '</div><!-- .postbox -->',

            data: function() {
                return {
                    items: []
                }
            },

            methods: {

                subscriberInfo: function( item ) {
                    return '<i class="fa fa-info-circle"></i>';
                },

                subscribeInfoToolTip: function ( item ) {
                    if ( 'subscribe' == item.status ) {
                        return 'Subscribed at ' + wperp.dateFormat( item.subscribe_at, 'Y-m-d' );
                    } else {
                        return 'Unsubscribed at ' + wperp.dateFormat( item.unsubscribe_at, 'Y-m-d' );
                    }

                    return '';
                },

                assigContactGroup: function() {
                    var self = this,
                    query_id = self.id;

                    $.erpPopup({
                        title: self.title,
                        button: wpErpCrm.update_submit,
                        id: 'erp-crm-edit-contact-subscriber',
                        extraClass: 'smaller',
                        onReady: function() {
                            var modal = this;

                            $( 'header', modal).after( $('<div class="loader"></div>').show() );

                            wp.ajax.send( 'erp-crm-edit-contact-subscriber', {
                                data: {
                                    id: query_id,
                                    _wpnonce: wpErpCrm.nonce
                                },
                                success: function( res ) {
                                    var html = wp.template( 'erp-crm-assign-subscriber-contact' )( { group_id : res.groups, user_id: query_id } );
                                    $( '.content', modal ).html( html );
                                    _.each( $( 'input[type=checkbox].erp-crm-contact-group-class' ), function( el, i) {
                                        var optionsVal = $(el).val();
                                        if( _.contains( res.groups, optionsVal ) && res.results[optionsVal].status == 'subscribe' ) {
                                            $(el).prop('checked', true );
                                        }
                                        if ( _.contains( res.groups, optionsVal ) && res.results[optionsVal].status == 'unsubscribe' ) {
                                            $(el).closest('label').find('span.checkbox-value')
                                                .append('<span class="unsubscribe-group">' + res.results[optionsVal].unsubscribe_message + '</span>');
                                        };
                                    });

                                    $( '.loader', modal ).remove();
                                }
                            });
                        },

                        onSubmit: function(modal) {
                            modal.disableButton();

                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function(res) {
                                    self.fetchData();
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

                fetchData: function() {
                    var self = this,
                        data = {
                            id: this.id,
                            action: 'erp-crm-get-assignable-group',
                            _wpnonce: wpErpCrm.nonce
                        };

                    jQuery.post( wpErpCrm.ajaxurl, data, function( resp ) {
                        if ( resp.success ) {
                            self.items = resp.data;
                        } else {
                            alert(resp);
                        }
                    } );
                }
            },

            ready: function() {
                this.fetchData();
            }
        });


        var contactSingle = new Vue({
            el: '#wp-erp',

            mixins: [mixin],

            methods: {

                editContact: function( type, id, title ) {
                    var self = this;

                    $.erpPopup({
                        title: title,
                        button: wpErpCrm.update_submit,
                        id: 'erp-customer-edit',
                        onReady: function() {
                            var modal = this;

                            $( 'header', modal).after( $('<div class="loader"></div>').show() );

                            wp.ajax.send( 'erp-crm-customer-get', {
                                data: {
                                    id: id,
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
                                success: function(response) {
                                    modal.enableButton();
                                    modal.closeModal();
                                    $( '.erp-single-customer-row' ).load( window.location.href + ' .left-content' );
                                },
                                error: function(error) {
                                    modal.enableButton();
                                    alert( error );
                                }
                            });
                        }
                    });
                },

                assignContact: function() {
                    var mainWrap = $(event.target).closest('.erp-crm-assign-contact');

                    mainWrap.find('.user-wrap').hide();
                    this.initSearchCrmAgent();
                    mainWrap.find('.assign-form').fadeIn();
                },

                saveAssignContact: function() {
                    var self = this;

                    var target = $(event.target),
                        data = {
                            action : 'erp-crm-save-assign-contact',
                            _wpnonce: wpErpCrm.nonce,
                            formData: target.closest('form').serialize()
                        };

                    wp.ajax.send( {
                        data: data,
                        success: function( res ) {
                            $('.erp-crm-assign-contact').load( window.location.href + ' .inner-wrap', function() {
                                self.initSearchCrmAgent();
                            } );

                        },
                        error: function(error) {
                            alert( error );
                        }
                    });
                },

                cancelAssignContact: function() {
                    var target = $(event.target);
                    var mainWrap = target.closest('.erp-crm-assign-contact');

                    mainWrap.find('.assign-form').hide();
                    mainWrap.find('.user-wrap').fadeIn();
                }
            }
        });
    }

})(jQuery)
