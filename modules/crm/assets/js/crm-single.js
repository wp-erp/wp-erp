;( function($) {
    var tableColumns = [
        {
            name: 'name',
            title: 'Full Name',
            callback: 'full_name',
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
            callback: 'life_stage',
        },
        {
            name: 'created',
            title: 'Created At',
            sortField: 'created'
        }
    ];

    var bulkactions = {

        defaultAction: [
            {
                'id' : 'delete',
                'text' : 'Delete'
            },

            {
                'id' : 'assing_group',
                'text' : 'Assing Group'
            }
        ]

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
                    action: 'delete'
                }
            ],
            topNavFilter: {
                data: wpErpCrm.statuses,
                default: 'all',
                field: 'status'
            },
            bulkactions: bulkactions
        },

        methods: {
            full_name: function( value, item ) {
                var link  = '<a href="' + item.details_url + '"><strong>' + item.first_name + ' '+ item.last_name + '</strong></a>';
                return item.avatar.img + link;
            },

            life_stage: function( value, item ) {
                return wpErpCrm.life_stages[value];
            },

            initFields: function() {
                $( '.erp-date-field').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-100:+0',
                });

                $( '.select2' ).select2({
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

                                // if ( is_single == '1' ) {
                                //     $('body').trigger( 'erp-crm-after-customer-new-company', [res]);
                                // } else {
                                //     WeDevs_ERP_CRM.customer.pageReload();
                                // }

                                modal.enableButton();
                                modal.closeModal();
                                self.$refs.vtable.tableData.unshift(res);
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
                                $( '.select2' ).select2();
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
                                console.log(res);
                                // if ( single_view == '1' ) {
                                //     $( '.erp-single-customer-row' ).load( window.location.href + ' .left-content' );
                                // } else {
                                //     WeDevs_ERP_CRM.customer.pageReload();
                                // }
                                modal.enableButton();
                                modal.closeModal();
                                self.$refs.vtable.tableData.$set( index, res );

                            },
                            error: function(error) {
                                modal.enableButton();
                                alert( error );
                            }
                        });
                    }
                });

            }
        },

        events: {
            'vtable:action': function( action, data, index ) {
                if ( 'edit' == action ) {
                    this.editContact( data, index );
                }
            },

            'vtable:default-bulk-action': function( action, ids ) {
                // Handle bulk action when action is something with ID's
                console.log( action, ids );
                this.$nextTick(function() {
                    this.$broadcast('vtable:refresh')
                })

            }
        }
    });

})(jQuery)
