(function($) {

    var isRequestDone = false;

    var ERP_Accounting = {

        initialize: function() {
            this.incrementField();
            this.select2AddMoreContent();
            this.initFields();
            $('.erp-color-picker').wpColorPicker();

            // journal entry
            $( 'table.erp-ac-transaction-table.journal-table' ).on( 'click', '.remove-line', this.journal.onChange );
            $( 'table.erp-ac-transaction-table.journal-table' ).on( 'change', 'input.line_debit, input.line_credit', this.journal.onChange );

            // chart of accounts
            $( 'form#erp-ac-accounts-form').on( 'change', 'input#code', this.accounts.checkCode );
            $( '.chart-of-accounts').on( 'click', '.erp-ac-remove-account', this.accounts.remove );
            $('.erp-ac-form-wrap').on('change', '.erp-ac-payment-receive', this.paymentReceive );

            //invoice and vendor credit calculate
            $('.erp-ac-form-wrap').on( 'keyup', '.erp-ac-line-due', this.lineDue );
            $('.erp-ac-bank-account-wrap').on( 'click', '.erp-ac-transfer-money-btn', this.transferMoney );
            $('body').on( 'change', '.erp-ac-bank-ac-drpdwn', this.checkSameAccount );
            $('.erp-ac-form-wrap').on( 'change', '.erp-ac-voucher-bank', this.individualBankBalance );
            $('.erp-ac-form-wrap').on( 'change', '.erp-ac-vendor-drop', this.vendorVocher );

            $('.erp-ac-form-wrap').on( 'change', '.erp-ac-customer-drop', this.customerAddress );
            $('.erp-ac-form-wrap').on( 'change', '.erp-ac-vendor-drop', this.vendorAddress );
            $('.invoice-preview-wrap').on( 'click', 'a.add-invoice-payment', self, this.invoicePayment );
           // $('.invoice-preview-wrap').on( 'click', 'a.add-vendor-credit-payment', self, this.vendoerCreditPayment );

            $('.invoice-preview-wrap').on( 'click', 'a.erp-ac-print', this.print );

            $('.erp-ac-customer-list-table-wrap, .erp-ac-vendor-list-table-wrap').on( 'click', 'a.erp-ac-submitdelete', this.customer.remove );
            $('.erp-ac-customer-list-table-wrap, .erp-ac-vendor-list-table-wrap' ).on( 'click', 'a.erp-ac-restoreCustomer', this.customer.restore );
            $('.erp-ac-receive-payment-table, .erp-ac-voucher-table-wrap' ).on( 'click', '.erp-ac-remove-line', this.removePartialLine );
            $('body' ).on( 'change', '.erp-ac-reference-field', this.reference );
            $('body' ).on( 'keyup', '.erp-ac-reference-field', this.keyupReference );

            $('body' ).on( 'keyup', '.erp-ac-check-invoice-number', this.keyupInvoice );
            $('body' ).on( 'change', '.erp-ac-check-invoice-number', this.changeInvoice );

            $('body' ).on( 'click', '.erp-ac-not-found-btn-in-drop', this.dropDownAddMore );
            $('.erp-ac-transaction-report').on('click', this.transactionReport );

            this.initTipTip();
            $(document.body ).on( 'keyup change', '.line_price, .line_credit, .line_debit, .erp-ac-line-due', this.keyUpNumberFormating );

            //checking user existance
            $('.erp-ac-users-wrap').on('focusout', 'input[name="email"]', this.users.checkUsers );
            $('.erp-ac-users-wrap').on('click', '.erp-ac-convert-user-info', this.users.convertUser );

            //tax
            $('.erp-settings').on( 'click', '#erp-ac-new-tax-add-btn', this.tax.new );
            $('body').on( 'change', '.erp-ac-tax-radio', this.tax.radio );
            $('body').on( 'click', '.erp-ac-multi-tax-add-more', this.tax.moreField );
            $('body').on( 'click', '.erp-ac-remove-field', this.tax.removeField );
            $('.erp-settings').on( 'click', '.erp-ac-click-tax-details', this.tax.details );
            $('.erp-settings').on( 'click', '.erp-ac-tax-edit', this.tax.new );
            $('body').on( 'change', '#erp-ac-compound', this.tax.compound );
            $('.erp-settings').on( 'click', '.erp-ac-tax-delete', this.tax.delete );

            // invoice
            this.invoice.initialize();
            $( 'body' ).on( 'click', '.invoice-duplicate', this.invoice.duplicate );
            $( 'body' ).on( 'click', '.invoice-get-link', this.invoice.getLink );
            $( 'body' ).on( 'click', '.invoice-send-email', this.invoice.sendEmail );
            $( 'body' ).on( 'click', '.invoice-email-new-receiver', this.invoice.addNewReceiver );
            this.invoice.copyReadonlyLink();

            // payment
            this.payment.initialize();
            $( 'body' ).on( 'click', '.payment-duplicate', this.payment.duplicate );
            $( 'body' ).on( 'click', '.payment-send-email', this.invoice.sendEmail );

            //trns form submit
            $( '.erp-form' ).on( 'click', '.erp-ac-trns-form-submit-btn', this.transaction.submit );

            //Transaction table row action
            $( '.erp-accounting' ).on( 'click', '.erp-accountin-trns-row-del', this.transaction.rowDelete );
            $( '.erp-accounting' ).on( 'click', '.erp-accounting-trash', this.transaction.trash );
            $( '.erp-accounting' ).on( 'click', '.erp-accounting-void', this.transaction.void );
            $( '.erp-accounting' ).on( 'click', '.erp-accounting-redo', this.transaction.redo );
        },

        transaction: {
            pageReload: function() {
                $('.erp-accounting').load( window.location.href + ' #erp-accounting' );
            },

            redo: function(e) {
                e.preventDefault();
                var self = $(this),
                    id   = self.data('id');

                swal({
                    title: ERP_AC.message.confirm,
                    type: "warning",
                    cancelButtonText: ERP_AC.message.cancel,
                    //confirmButtonText: 'asdfasd',
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: ERP_AC.message.redo,
                    closeOnConfirm: false,
                    showCancelButton: true,   closeOnConfirm: false,   showLoaderOnConfirm: true,
                },
                function(){

                    wp.ajax.send('erp-ac-trns-redo', {
                        data: {
                            'id': id,
                            '_wpnonce': ERP_AC.nonce
                        },
                        success: function(res) {
                            swal("", res.success, "success");
                            location.reload();
                            //ERP_Accounting.transaction.pageReload();
                        },
                        error: function(error) {
                            swal({
                                title: error.error,
                                text: error,
                                type: "error",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#DD6B55"
                            });
                        }
                    });

                });
            },

            void: function(e) {
                e.preventDefault();
                var self = $(this),
                    id   = self.data('id');

                swal({
                    title: ERP_AC.message.confirm,
                    type: "warning",
                    cancelButtonText: ERP_AC.message.cancel,
                    //confirmButtonText: 'asdfasd',
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: ERP_AC.message.void,
                    closeOnConfirm: false,
                    showCancelButton: true,   closeOnConfirm: false,   showLoaderOnConfirm: true,
                },
                function(){

                    wp.ajax.send('erp-ac-trns-void', {
                        data: {
                            'id': id,
                            '_wpnonce': ERP_AC.nonce
                        },
                        success: function(res) {
                            swal("", res.success, "success");
                            location.reload();
                            //ERP_Accounting.transaction.pageReload();
                        },
                        error: function(error) {
                            swal({
                                title: error.error,
                                text: error,
                                type: "error",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#DD6B55"
                            });
                        }
                    });

                });
            },

            trash: function(e) {
                e.preventDefault();
                var self = $(this),
                    id   = self.data('id'),
                    type = self.data('type');

                $.erpPopup({
                    title: ERP_AC.message.transaction_status,
                    button: ERP_AC.message.submit,
                    id: 'erp-ac-tax-items-details-popup-content',
                    content: wperp.template('erp-ac-trash-form-popup')({}).trim(),
                    extraClass: 'smaller',

                    onSubmit: function(modal) {

                        wp.ajax.send('erp-ac-trns-restore', {
                            data: {
                                'id': id,
                                'type' : type,
                                'data' : this.serialize(),
                                '_wpnonce': ERP_AC.nonce
                            },
                            success: function(res) {
                                //swal("", res.success, "success");
                                location.href = res.url;
                                //ERP_Accounting.transaction.pageReload();
                            },
                            error: function(error) {

                            }
                        });
                    }
                });
            },

            rowDelete: function(e) {
                e.preventDefault();
                var self = $(this),
                    id   = self.data('id');

                swal({
                    title: ERP_AC.message.confirm,
                    type: "warning",
                    cancelButtonText: ERP_AC.message.cancel,
                    //confirmButtonText: 'asdfasd',
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: ERP_AC.message.delete,
                    closeOnConfirm: false,
                    showCancelButton: true,   closeOnConfirm: false,   showLoaderOnConfirm: true,
                },
                function(){

                    wp.ajax.send('erp-ac-trns-row-del', {
                        data: {
                            'id': id,
                            '_wpnonce': ERP_AC.nonce
                        },
                        success: function(res) {
                            swal("", res.success, "success");
                            location.reload();
                            //ERP_Accounting.transaction.pageReload();
                        },
                        error: function(error) {
                            swal({
                                title: error.error,
                                text: error,
                                type: "error",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#DD6B55"
                            });
                        }
                    });

                });
            },

            submit: function(e) {
                e.preventDefault();

                var self = $(this),
                    form = self.closest('form'),
                    redirect = self.data('redirect'),
                    btn_status = self.data('btn_status'),
                    issue_date = $('input[name="issue_date"]').val(),
                    due_date = $('input[name="due_date"]').val(),
                    form_type = $('input[name="form_type"]').val();

                if ( form_type == 'invoice' || form_type == 'vendor_credit' ) {
                    if ( new Date(issue_date) > new Date(due_date)) {
                        $('input[name="due_date"]').val('');
                    }
                }

                $('#erp-ac-redirect').val(redirect);
                $('#erp-ac-btn-status').val(btn_status);
                form.find( 'input[name="submit_erp_ac_trans"]' ).trigger('click');

                return false;

                wperp.swalSpinnerVisible();

                wp.ajax.send( 'erp_ac_trans_form_submit', {
                    data: {
                        '_wpnonce': ERP_AC.nonce,
                        'btn_status' : btn_status,
                        'form_data': form.serialize()
                    },
                    success: function(res) {
                        wperp.swalSpinnerHidden();
                        swal({ title: "", text: res.message, type: "success"}, function() {
                            if ( add_another === '1' ) {
                                location.reload();
                            } else {
                                location.href = res.return_url;
                            }
                        });
                    },
                    error: function(res) {
                        wperp.swalSpinnerHidden();
                        swal({
                            title: ERP_AC.message.error,
                            text: res.message,
                            type: "error",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#DD6B55"
                        });
                    }
                });

            }
        },

        users: {
            clickOff: function() {
                $('.erp-ac-users-wrap').off('click', '.erp-ac-convert-user-info', ERP_Accounting.users.convertUser );
            },

            clickOn: function() {
                $('.erp-ac-users-wrap').on('click', '.erp-ac-convert-user-info', ERP_Accounting.users.convertUser );
            },

            convertUser: function(e) {
                e.preventDefault();

                var self = $(this);

                ERP_Accounting.users.clickOff();

                $('.erp-loader').show();

                wp.ajax.send( 'erp_people_convert', {
                    data: {
                        '_wpnonce': ERP_AC.nonce,
                        'type'    : self.data('type'),
                        'people_id': self.data('people_id')
                    },
                    success: function(res) {
                        window.location.href = res.redirect;
                        ERP_Accounting.users.clickOn();
                    },
                    error: function(res) {
                        ERP_Accounting.users.clickOn();
                        $('.erp-loader').hide();
                        $('input[name="submit_erp_ac_customer"]').prop('disabled', false);
                    }
                });
            },

            checkUsers: function(e) {
                e.preventDefault();
                var self = $(this),
                    form = self.closest('form'),
                    email = self.val(),
                    type = form.find('input[name=type]').val(),
                    id   = form.find('input[name=field_id]').val();

                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

                if ( email == '' || !re.test( email ) ) {
                    return false;
                }

                if ( id != '0' ) {
                    return false;
                }

                wp.ajax.send( 'erp_people_exists', {
                    data: {
                        email: email
                    },
                    success: function(res) {
                        $('#message').slideUp(500);
                        $('input[name="submit_erp_ac_customer"]').prop('disabled', false);
                    },
                    error: function(res) {
                        if ( $.inArray( 'customer', res.types ) != -1 || $.inArray( 'vendor', res.types ) != -1 ) {
                            $('#message').slideUp(100);
                            $('#message').html( '<p><span class="erp-ac-user-exsitance-notice">' + ERP_AC.message.alreadyExist + '</span></p>' );
                            $('#message').slideDown(500);
                            $('input[name="submit_erp_ac_customer"]').prop('disabled', true);
                        } else {
                            $('#message').slideUp(100);
                            $('.erp-ac-convert-user-info').attr( 'data-people_id', res.id );
                            $('#message').slideDown(500);
                            $('input[name="submit_erp_ac_customer"]').prop('disabled', true);
                        }
                    }
                });

            },
        },

        tax: {

            pageReload: function() {
                $( '.erp-ac-tax-td-wrap' ).load( window.location.href + ' .erp-ac-setting-tax-wrap' );
            },

            delete: function(e) {
                e.preventDefault();

                var self = $(this);

                swal({
                    title: ERP_AC.message.confirm,
                    type: "warning",
                    cancelButtonText: ERP_AC.message.cancel,
                    //confirmButtonText: 'asdfasd',
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: ERP_AC.message.delete,
                    closeOnConfirm: false,
                    showCancelButton: true,   closeOnConfirm: false,   showLoaderOnConfirm: true,
                },
                function(){

                    wp.ajax.send('erp-ac-delete-tax', {
                        data: {
                            'tax_id': self.data('tax_id'),
                            '_wpnonce': ERP_AC.nonce
                        },
                        success: function(res) {
                            swal("", ERP_AC.message.tax_deleted, "success");
                            ERP_Accounting.tax.pageReload();
                        },
                        error: function(error) {
                            swal({
                                title: ERP_AC.message.error,
                                text: error,
                                type: "error",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#DD6B55"
                            });
                        }
                    });

                });
            },

            compound: function(e) {
                e.preventDefault();
                var self = $(this),
                    is_checked = self.prop('checked');

                if ( is_checked ) {
                    $('.erp-ac-multi-tax-add-more').show();
                    $('.erp-ac-multiple-sub-tax-field').find('.row').show();

                    var  sub_tx_wrap = $('.erp-ac-multiple-sub-tax-field').find('.row').length;

                    if ( sub_tx_wrap > 2 ) {
                        $('.erp-ac-remove-field').show();
                    }

                } else {
                    $('.erp-ac-multi-tax-add-more').hide();
                    $('.erp-ac-multiple-sub-tax-field').find('.row:gt(1)').hide();
                    $('.erp-ac-remove-field').hide();
                }

            },

            details: function(e) {
                e.preventDefault();

                var self = $(this),
                    data = self.data('items'),
                    id   = self.data('id');

                $.erpPopup({
                    title: ERP_AC.message.tax_item,
                    button: '',
                    id: 'erp-ac-tax-items-details-popup-content',
                    content: wperp.template('erp-ac-items-details-popup')({ 'data' : data, 'id' : id }).trim(),
                    extraClass: 'large',
                });
            },

            selectable: function() {
                var checked = $('[data-checkbox]');

                $.each( checked, function( key, dom ) {
                    var self     = $(dom),
                        data_val       = self.data('checkbox'),
                        checkbox_field = self.find( 'input[type="checkbox"]' ),
                        checkbox_value = checkbox_field.val();

                    if ( data_val === checkbox_value ) {
                        checkbox_field.prop( 'checked', true );
                    }

                    $('input[type="radio"][value="'+data_val+'"]').prop('checked', true );
                });

                var selected = $('[data-selected]');

                $.each( selected, function( key, dom ) {

                    var self     = $(dom),
                        data_val = self.data('selected');
                        if ( data_val !== '' ) {
                            self.find('select').val(data_val);
                        }
                });
            },

            new: function(e) {
                e.preventDefault();

                var self = $(this),
                    content = self.data('items'),
                    content = ( content === undefined ) ? [] : content,
                    id = self.data('id'),
                    app_accounts = self.data('app_accounts'),
                    is_edit = self.data('is_edit');

                $.erpPopup({
                    title: ERP_AC.message.new_tax,
                    button: is_edit ? ERP_AC.message.tax_update : ERP_AC.message.new,
                    id: 'erp-ac-tax-popup-content',
                    content: wperp.template('erp-ac-new-tax-form-popup')({ 'content' : content, 'id' : id, 'app_accounts' : app_accounts, 'is_edit' : is_edit }).trim(),
                    extraClass: 'large',

                    onReady: function(modal) {
                        ERP_Accounting.tax.selectable();
                        ERP_Accounting.tax.radio_status();
                        $('.erp-select2').select2({
                            'theme': 'classic'
                        }).change(function(event) {

                        });
                    },

                    onSubmit: function(modal) {

                        wp.ajax.send('erp-ac-new-tax', {
                            data: {
                                'post': this.serialize(),
                                '_wpnonce': ERP_AC.nonce
                            },
                            success: function(res) {

                                modal.closeModal();
                                ERP_Accounting.tax.pageReload();
                                //location.reload();
                            },
                            error: function(error) {
                                modal.showError( error );
                            }
                        });
                    }
                });
            },

            radio: function(e) {
                e.preventDefault();
                ERP_Accounting.tax.radio_status();
            },

            radio_status: function() {

                var self = $('input[type="radio"][value="multiple_tax"]').is(':checked') ? $('input[type="radio"][value="multiple_tax"]') : $('input[type="radio"][value="single_tax"]'),
                    value = self.attr('value');

                if ( value == 'single_tax' ) {
                    $('.erp-ac-multiple-tax-field').find('input[type="text"]').attr('disabled', 'disabled');
                    $('.erp-ac-single-tax-field').find('input[type="text"]').removeAttr('disabled');

                    $('.erp-ac-single-tax-field').slideDown(200);
                    $('.erp-ac-multiple-tax-field').slideUp(200);
                } else {
                    $('.erp-ac-single-tax-field').find('input[type="text"]').attr('disabled', 'disabled');
                    $('.erp-ac-multiple-tax-field').find('input[type="text"]').removeAttr('disabled');

                    $('.erp-ac-single-tax-field').slideUp(200);
                    $('.erp-ac-multiple-tax-field').slideDown(200);
                }
            },

            moreField: function(e) {
                e.preventDefault();
                var clone = $('.erp-ac-tax-field-clone').find('.row').clone();
                $('.erp-ac-multiple-sub-tax-field').append(clone).find('.fa-times-circle').show();
            },

            removeField: function(e) {
                e.preventDefault();

                var self = $(this),
                    sub_tx_wrap = $('.erp-ac-multiple-sub-tax-field'),
                    row_length = sub_tx_wrap.find('.row').length;

                if ( row_length > 2 ) {
                    self.closest('.row').remove();

                    if ( row_length <= 3 ) {
                        sub_tx_wrap.find('.fa-times-circle').hide();
                    }
                }
            }
        },

        invoice: {
            initialize: function () {

                var moreActions, theme, openOn, target, content;

                moreActions = $('.invoice-buttons');

                if ( moreActions.length > 0 ) {
                    target = moreActions.find('.drop-target');
                    theme = moreActions.data('theme');
                    openOn = 'click';
                    content =  $('.more-action-content').html();
                    moreActions.addClass(theme);

                    if ( target[0] ) {
                        var drop = new Drop({
                            target: target[0],
                            classes: theme,
                            position: 'bottom center',
                            constrainToWindow: true,
                            constrainToScrollParent: false,
                            openOn: openOn,
                            content: content
                        });
                    }
                }
            },

            duplicate: function (e) {
                e.preventDefault();
            },

            getLink: function(e) {
                e.preventDefault();

                $('#get-readonly-link').hide();
                $('#copy-readonly-link').show();
                $('#erp-tips-get-link').tipTip();
            },

            copyReadonlyLink: function() {
                clipboard = new Clipboard('.copy-readonly-invoice');

                clipboard.on('success', function() {
                    clipboard.destroy();
                    $('#erp-tips-get-link').tipTip({content:'copied'});
                    $('#erp-tips-get-link').mouseover();

                });
            },

            sendEmail: function(e) {
                e.preventDefault();

                var self, type, title, button, sender, receiver, subject, transaction_id;

                self = $(this);
                type = self.data('type');
                title = self.data('title');
                button = self.data('button');
                url = self.data('url');
                sender = self.data('sender');
                receiver = self.data('receiver');
                subject = self.data('subject');
                transactionId = self.data('transaction-id');

                $.erpPopup({
                    title: title,
                    button: button,
                    id: 'erp-ac-invoice-send-email',
                    content: wperp.template( 'erp-ac-send-email-invoice-pop' )({type: type, sender: sender, receiver: receiver, subject: subject, transactionId: transactionId, url: url}).trim(),
                    extraClass: 'large',

                    onReady: function(modal) {
                        $('#erp-ac-email-body').val(url);
                    },

                    onSubmit: function(modal) {
                        modal.disableButton();
                        wp.ajax.send({
                            data: this.serialize(),
                            success: function (response) {
                                swal({
                                    title: ERP_AC.emailConfirm,
                                    timer: 2000,
                                    text: ERP_AC.emailConfirmMsg,
                                    type: 'success'
                                });
                                modal.enableButton();
                                modal.closeModal();
                            },
                            error: function (error) {
                                modal.showError(error);
                            }
                        });
                    }
                });
            },

            addNewReceiver: function( e ) {
                e.preventDefault();
                $('.subject').before('<span class="single-receiver"><div class="row">' +
                    '<label>&nbsp;</label>' +
                    '<input type="text" name="email-to[]" placeholder="name@example.com">' +
                    '<a class="receiver-filed-remove" style="cursor:pointer"><i class="fa fa-close remove-receiver"></i></a>' +
                    '</div></span>');

                $('.remove-receiver').on('click', function(e) {
                    e.preventDefault();

                    $(e.target).closest('.single-receiver').remove();
                });

            }
        },

        payment: {
            initialize: function () {

                var moreActions, theme, openOn, target, content;

                moreActions = $('.payment-buttons');

                if ( moreActions.length > 0 ) {
                    target = moreActions.find('.drop-target');
                    theme = moreActions.data('theme');
                    openOn = 'click';
                    content =  $('.more-action-content').html();
                    moreActions.addClass(theme);

                    var drop = new Drop({
                        target: target[0],
                        classes: theme,
                        position: 'bottom center',
                        constrainToWindow: true,
                        constrainToScrollParent: false,
                        openOn: openOn,
                        content: content
                    });

                }
            },

            duplicate: function ( e ) {
                e.preventDefault();
                alert('duplicate');
            }
        },

        keyUpNumberFormating: function(e) {
            e.preventDefault();

            var self = $(this),
                current_value  = self.val(),
                prev_value     = self.data('value'),
                decimal_sep    = ERP_AC.decimal_separator,
                number_decimal = ERP_AC.number_decimal,
                decimal_count  = ( current_value.split(decimal_sep).length ) - 1;

            if ( decimal_count > 1 ) {
                var split      = current_value.split(decimal_sep),
                    first_term = split.shift() + decimal_sep,
                    last_term  = split.join('').slice(0, number_decimal),
                    new_val    = first_term + last_term;
                    self.val( new_val );
                    ERP_Accounting.paymentVoucher.calculate();
                    ERP_Accounting.journal.calculate();
            }

            var regex    = new RegExp( '[^\-0-9\%\\'+ERP_AC.decimal_separator+']+', 'gi' ),
                newvalue = current_value.replace( regex, '' );

            if ( current_value !== newvalue ) {
                self.val( newvalue );
            }
        },

        calNumNormal: function( $number ) {
            return $number.replace(",", ".");
        },

        numFormating: function( $number ) {
            var options = {
                symbol : ERP_AC.symbol,
                decimal : ERP_AC.decimal_separator,
                thousand: '',//ERP_AC.thousand_separator,
                precision : ERP_AC.number_decimal,
                format: "%v" //with currency "%s%v"
            };

            return accounting.formatMoney( $number, options);
        },

        initTipTip: function() {
            $( 'body .erp-tips' ).tipTip( {
                defaultPosition: "top",
                fadeIn: 100,
                fadeOut: 100,
                class: 'erp-ac-toltip'
            } );
        },

        initFields: function() {
            $( '.erp-date-field').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
            });

            ERP_Accounting.dueDateField();

            $( '.erp-select2' ).select2({
                placeholder: $(this).attr('data-placeholder'),
            });


            $('#erp-ac-hidden-new-payment').find('.erp-select2').select2('destroy');
            $('#erp-ac-new-payment-voucher').find('.erp-select2').select2('destroy');
            // $('#erp-ac-hidden-new-payment').find('span.select2').remove();

            ERP_Accounting.setCountryStateValue();
        },

        setCountryStateValue: function() {
            $('select.erp-country-select').trigger('change');

            var element = $( 'ul.erp-form-fields' ).find( 'li.row-state' ),
                selectedVal = element.data('selected');

            if ( selectedVal !== '' ) {
                element.find( 'select' ).val( selectedVal );
            }

            $('select.erp-state-select').trigger('change');

        },

        dueDateField: function() {
            var dateToday = new Date();
            var yrRange = "-45:" + (dateToday.getFullYear() + 2);

            $( '.erp-due-date-field').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: yrRange,
            });
        },

        transactionReport: function(e) {
            e.preventDefault();
            var transaction_id = $(this).data('transaction_id');
            ERP_Accounting.getTransactionReport( transaction_id );
        },

        getTransactionReport: function( transaction_id ) {
            $.erpPopup({
                title: ERP_AC.message.transaction,
                button: '',
                id: 'erp-ac-transaction-report-view-popup',
                extraClass: 'large',

                onReady: function(modal) {

                    wp.ajax.send( 'erp-ac-transaction-report', {
                        data: {
                            'transaction_id': transaction_id,
                            '_wpnonce': ERP_AC.nonce
                        },
                        success: function(res) {
                            $("#"+modal.id).find('.content').html(res.content);

                        },
                        error: function() {
                        }
                    });
                },
            });
        },

        /**
         * Reload the department area
         *
         * @return {void}
         */
        reload: function( wrap, content, res ) {
            $( '.'+wrap ).load( window.location.href + ' .' + content, function() {

                var select = $('.'+wrap).find( '.'+content ).find( '.erp-ac-not-found-in-drop' );

                select.find('option[value="'+res.id+'"]').attr('selected', 'selected');
                ERP_Accounting.select2AddMoreContent();
            } );

        },

        dropDownAddMore: function(e) {
            e.preventDefault();
            var self = $(this),
                content_id = self.data('content');

            $.erpPopup({
                title: content_id == 'erp-ac-new-customer-content-pop' ? ERP_AC.message.new_customer : ERP_AC.message.new_vendor,
                button: ERP_AC.message.new,
                id: 'erp-ac-customer-vendor-popup',
                content: wperp.template(content_id)().trim(),
                extraClass: 'large',

                onReady: function(modal) {
                    $('form.erp-modal-form').addClass('erp-form');
                    $('#'+modal.id).css({ 'z-index': '9999'});
                },
                onSubmit: function(modal) {

                    wp.ajax.send('erp-ac-new-customer-vendor', {
                        data: {
                            'post': this.serialize(),
                            '_wpnonce': ERP_AC.nonce
                        },
                        success: function(res) {
                            ERP_Accounting.reload( 'erp-ac-replace-wrap', 'erp-ac-replace-content', res );
                            modal.closeModal();
                        },
                        error: function(error) {
                            modal.showError( error );
                        }
                    });
                }
            });
        },

        /**
         * select2 with add more button content
         *
         * @return  {void}
         */
        select2AddMoreContent: function() {
            var selects = $('.erp-ac-not-found-in-drop');
            $.each( selects, function( key, element ) {
               ERP_Accounting.select2AddMoreActive(element);
            });
        },

        /**
         * select2 with add more button active
         *
         * @return  {void}
         */
        select2AddMoreActive: function(element) {
            var id = $(element).data('content');

            $(element).select2({
                width: 'element',
                "language": {
                    noResults: function(){
                       return '<a href="#" data-content="'+id+'" class="button button-primary erp-ac-not-found-btn-in-drop">Add New</a>';
                    }
                },
                escapeMarkup: function (markup) {
                    return markup;
                }

            });
        },

        keyupInvoice: function(e) {
            e.preventDefault();

            $('input[name="submit_erp_ac_trans"]').prop('disabled',true);
            $('input[name="submit_erp_ac_trans_draft"]').prop('disabled',true);
            $('input[name="submit_erp_ac_journal"]').prop('disabled',true);
            $('button[type="submit"]').prop('disabled',true);
        },

        changeInvoice: function(e) {
            e.preventDefault();

            var self = $(this),
                old_invoice = self.data('old_val');

            wp.ajax.send('erp-ac-check-invoice-number', {
                data: {
                    '_wpnonce': ERP_AC.nonce,
                    invoice: self.val(),
                    form_type : $('input[name="form_type"]').val(),
                },

                success: function(res) {
                    $('input[name="submit_erp_ac_trans"]').prop('disabled',false);
                    $('input[name="submit_erp_ac_trans_draft"]').prop('disabled',false);
                    $('input[name="submit_erp_ac_journal"]').prop('disabled',false);
                    $('button[type="submit"]').prop('disabled',false);
                },

                error: function(res) {
                    self.val(old_invoice);
                    alert(res);
                   // $('input[name="submit_erp_ac_trans"]').prop('disabled',false);
                   // $('input[name="submit_erp_ac_trans_draft"]').prop('disabled',false);
                   // $('input[name="submit_erp_ac_journal"]').prop('disabled',false);
                }
            });
        },

        keyupReference: function(e) {
            e.preventDefault();
            var self = $(this);

            if ( self.val() == '' ) {
                $('input[name="submit_erp_ac_trans"]').prop('disabled',false);
                $('input[name="submit_erp_ac_trans_draft"]').prop('disabled',false);
                $('input[name="submit_erp_ac_journal"]').prop('disabled',false);
                $('button[type="submit"]').prop('disabled',false);
            } else {
                $('input[name="submit_erp_ac_trans"]').prop('disabled',true);
                $('input[name="submit_erp_ac_trans_draft"]').prop('disabled',true);
                $('input[name="submit_erp_ac_journal"]').prop('disabled',true);
                $('button[type="submit"]').prop('disabled',true);
            }
        },

        reference: function(e) {
            e.preventDefault();

            var self = $(this);

            wp.ajax.send('erp-ac-reference', {
                data: {
                    '_wpnonce': ERP_AC.nonce,
                    reference: self.val(),
                },

                success: function(res) {
                    $('input[name="submit_erp_ac_trans"]').prop('disabled',false);
                    $('input[name="submit_erp_ac_trans_draft"]').prop('disabled',false);
                    $('input[name="submit_erp_ac_journal"]').prop('disabled',false);
                    $('button[type="submit"]').prop('disabled',false);
                },

                error: function(res) {
                    self.val('');
                    alert(res);
                    $('input[name="submit_erp_ac_trans"]').prop('disabled',false);
                    $('input[name="submit_erp_ac_trans_draft"]').prop('disabled',false);
                    $('input[name="submit_erp_ac_journal"]').prop('disabled',false);
                    $('button[type="submit"]').prop('disabled',false);
                }
            });
        },

        removePartialLine: function(e) {
            if ( typeof e !== 'undefined' ) {
                e.preventDefault();
            }

            if ( ! confirm( ERP_AC.message.confirm ) ) {

            }

            var self = $(this),
                table = self.closest( 'table' );

            if ( table.find('tbody > tr').length < 2 ) {
                return;
            }

            self.closest('tr').remove();
            ERP_Accounting.lineDue(e);

        },

        allCheck: function(e) {
            e.preventDefault();

            if ( $(this).prop('checked') ) {
                $('.erp-ac-single-check').prop('checked', true);
            } else {
                $('.erp-ac-single-check').prop('checked', false);
            }

        },

        singleCheck: function(e) {
            e.preventDefault();
            var single_check = $('.erp-ac-single-check');

            $('.erp-ac-all-check').prop('checked', true);

            $.each( single_check, function( key, value ) {
                if ( ! $(value).prop('checked') ) {
                    $('.erp-ac-all-check').prop('checked', false);
                    return false;
                }
            });
        },

        customer: {
            pageReload: function() {
                $( '.inner-table-wrap' ).load( window.location.href + ' .list-table-inner' );
            },

            remove: function(e) {
                e.preventDefault();
                var self = $(this);

                wp.ajax.send( 'erp-ac-user-delete-status', {
                    data: {
                        '_wpnonce': ERP_AC.nonce,
                        id: self.data( 'id' ),
                        hard: self.data( 'hard' ),
                        type: self.data( 'type' )
                    },
                    success: function() {
                       ERP_Accounting.customer.delete_action( self );
                    },
                    error: function(response) {
                        alert( response );
                    }
                });
            },

            delete_action: function( self ) {

                if ( confirm( wpErpCrm.delConfirmCustomer ) ) {
                    wp.ajax.send( 'erp-ac-customer-delete', {
                        data: {
                            '_wpnonce': ERP_AC.nonce,
                            id: self.data( 'id' ),
                            hard: self.data( 'hard' ),
                            type: self.data( 'type' )
                        },
                        success: function() {
                            self.closest('tr').fadeOut( 'fast', function() {
                                $(this).remove();
                                ERP_Accounting.customer.pageReload();
                            });
                        },
                        error: function(response) {
                            alert( response.data );
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
                    wp.ajax.send( 'erp-ac-customer-restore', {
                        data: {
                            '_wpnonce': ERP_AC.nonce,
                            id: self.data( 'id' ),
                            type: self.data( 'type' )
                        },
                        success: function() {
                            self.closest('tr').fadeOut( 'fast', function() {
                                $(this).remove();
                                ERP_Accounting.customer.pageReload();
                            });
                        },
                        error: function(response) {
                            alert( response );
                        }
                    });
                }
            },
        },

        print: function(e) {
            e.preventDefault();
            window.print();
        },

        invoicePayment: function(e) {
            e.preventDefault();
            var self = $(this);
            $.erpPopup({
                title: 'Invoice',
                button: 'submit',
                id: 'erp-ac-invoice-payment-popup',
                content: wperp.template('erp-ac-invoice-payment-pop')({
                    customer_id : self.data('customer_id'),
                    due_amount : self.data('due_amount'),
                    partial_id : self.data('transaction_id'),
                    type : self.data('type')
                }).trim(),

                extraClass: 'large',

                onReady: function(modal) {
                    var type = $('.erp-ac-check-invoice-number').data('type');
                    wp.ajax.send( {
                        data: {
                            action: 'erp-ac-get-invoice-number',
                            type : type,
                            _wpnonce : ERP_AC.nonce
                        },
                        success: function(res) {
                            $('.erp-ac-check-invoice-number').val( res.invoice_number );
                        },
                        error: function(error) {
                        }
                    });
                    $('#erp-ac-invoice-payment-popup').find('.erp-ac-chart-drop-down').addClass('select2');
                    ERP_Accounting.initFields();
                },
                onSubmit: function(modal) {
                    wp.ajax.send( {
                        data: this.serialize()+'&_wpnonce='+ERP_AC.nonce,
                        success: function(res) {
                            modal.closeModal();
                            location.reload();
                        },
                        error: function(error) {
                            modal.showError( error );
                        }
                    });
                }
            }); //popup
        },

        // vendoerCreditPayment: function(e) {
        //     e.preventDefault();
        //     var self = $(this);
        //     $.erpPopup({
        //         title: 'Invoice',
        //         button: 'submit',
        //         id: 'erp-ac-vendor-credit-popup',
        //         content: wperp.template('erp-ac-vendoer-credit-single-payment')({
        //             customer_id : self.data('customer_id'),
        //             due_amount : self.data('due_amount'),
        //             partial_id : self.data('transaction_id'),
        //         }).trim(),
        //         extraClass: 'large',
        //         onReady: function(modal) {
        //             var type = $('.erp-ac-check-invoice-number').data('type');
        //             wp.ajax.send( {
        //                 data: {
        //                     action: 'erp-ac-get-invoice-number',
        //                     type : type,
        //                     _wpnonce : ERP_AC.nonce
        //                 },
        //                 success: function(res) {
        //                     $('.erp-ac-check-invoice-number').val( res.invoice_number );
        //                 },
        //                 error: function(error) {
        //                 }
        //             });
        //             $('#erp-ac-invoice-payment-popup').find('.erp-ac-chart-drop-down').addClass('select2');
        //             ERP_Accounting.initFields();
        //         },
        //         onSubmit: function(modal) {
        //             wp.ajax.send( {
        //                 data: this.serialize()+'&_wpnonce='+ERP_AC.nonce,
        //                 success: function(res) {
        //                     modal.closeModal();
        //                     location.reload();
        //                 },
        //                 error: function(error) {
        //                     modal.showError( error );
        //                 }
        //             });
        //         }
        //     }); //popup
        // },

        vendorAddress: function(e) {
            e.preventDefault();
            var self = $(this);

            wp.ajax.send( {
                data: {
                    action: 'erp_ac_vendor_address',
                    _wpnonce: ERP_AC.nonce,
                    vendor_id: self.val()
                },

                success: function(res) {
                    $('textarea[name="billing_address"]').val(res);
                },

                error: function() {
                    $('textarea[name="billing_address"]').val('');
                }
            } );

        },

        customerAddress: function(e) {
            e.preventDefault();
            var self = $(this);

            wp.ajax.send( {
                data: {
                    action: 'erp_ac_customer_address',
                    _wpnonce: ERP_AC.nonce,
                    customer_id: self.val()
                },

                success: function(res) {
                    $('textarea[name="billing_address"]').val(res);
                },

                error: function() {
                    $('textarea[name="billing_address"]').val('');
                }
            } );

        },

        vendorVocher: function(e) {
            e.preventDefault();
            var self = $(this);

            wp.ajax.send( {
                data: {
                    action: 'erp_ac_vendor_voucher',
                    _wpnonce: ERP_AC.nonce,
                    vendor: self.val(),
                    //account_id: $('.erp-ac-deposit-dropdown').val()
                },

                success: function(res) {
                    $('.erp-form').find('.erp-ac-voucher-table-wrap').html(res);
                    $('.erp-form').find( 'input[name="submit_erp_ac_trans_draft"]' ).hide();
                    ERP_Accounting.initTipTip();
                },

                error: function() {
                    var clone_form = $('.erp-ac-voucher-table-wrap-clone').html();
                    if ( typeof( clone_form ) == 'undefined' ) {
                        return;
                    }

                    $('.erp-form').find('.erp-ac-voucher-table-wrap').html(clone_form);
                    $('.erp-form').find( 'input[name="submit_erp_ac_trans_draft"]' ).show();
                    //$('.erp-form').find( '.erp-ac-selece-custom' ).addClass('erp-select2');
                    $('.erp-select2').select2();
                    ERP_Accounting.incrementField();
                    ERP_Accounting.initFields();
                }
            } );
        },

        individualBankBalance: function(e) {
            e.preventDefault();
            var self = $(this),
                bank_id  = self.val();
            if ( bank_id == '' ) {
                $('.balance-wrap').find( '.erp-ac-bank-amount' ).html(0);
                return;
            }

            wp.ajax.send( {
                data: {
                    action: 'ac_bank_balance',
                    bank_id : bank_id,
                    _wpnonce: ERP_AC.nonce,
                },
                success: function(res) {
                    $('.balance-wrap').find( '.erp-ac-bank-amount' ).html(res.total_amount);
                },
                error: function(error) {
                    //alert( error );
                }
            });
        },

        checkSameAccount: function(e) {
            e.preventDefault();
            var self = $(this),
                from = $('.erp-ac-bank-ac-drpdwn-frm').val(),
                to   = $('.erp-ac-bank-ac-drpdwn-to').val(),
                submit_btn = self.closest('form').find( 'button[type=submit]' );

            if ( from == '' || to == '' ) {
                return;
            }

            if ( from === to ) {
                submit_btn.prop( 'disabled', true );
                alert( 'Please choose another account' );
                self.select2('val', '');
                return;
            } else {
                submit_btn.prop( 'disabled', false );
            }
        },

        transferMoney: function(e) {
            e.preventDefault();
            $.erpPopup({
                title: 'Transfer Money',
                button: 'submit',
                id: 'erp-ac-transfer-popup',
                content: wperp.template('erp-ac-transfer-money-pop')().trim(),
                extraClass: 'larger',
                onReady: function(modal) {
                    $('.erp-ac-transfer-popup').find('.erp-ac-chart-drop-down').addClass('select2');
                    $('#erp-ac-transfer-popup').on( 'change', '.erp-ac-bank-ac-drpdwn', ERP_Accounting.checkBankBalance );
                    modal.disableButton();
                    ERP_Accounting.initFields();
                },
                onSubmit: function(modal) {
                    wp.ajax.send( {
                        data: this.serialize()+'&_wpnonce='+ERP_AC.nonce,
                        success: function(res) {
                            modal.closeModal();
                            location.reload();
                        },
                        error: function(error) {
                            modal.showError( error );
                        }
                    });
                }
            }); //popup
        },

        checkBankBalance: function(e) {
            e.preventDefault();

            var self = $(this),
                bank_id  = self.val();
                fld_name = self.attr('name'),
                banlance_wrap = ( fld_name == 'form_account_id' ) ? 'balance-wrap-from' : 'balance-wrap-to';

            if ( bank_id == '' ) {
                $('.'+banlance_wrap).find( '.erp-ac-bank-amount' ).html(0);
                return;
            }

            wp.ajax.send( {
                data: {
                    action: 'ac_bank_balance',
                    bank_id : bank_id,
                    _wpnonce: ERP_AC.nonce,
                },
                success: function(res) {
                    $('.'+banlance_wrap).find( '.erp-ac-bank-amount' ).html(res.total_amount);
                },
                error: function(error) {
                    //alert( error );
                }
            });
        },

        incrementField: function() {
            $( 'table.erp-ac-transaction-table' ).on( 'click', '.add-line', this.table.addRow );
            $( 'table.erp-ac-transaction-table' ).on( 'click', '.remove-line', this.table.removeRow );
            $( 'table.erp-ac-transaction-table.payment-voucher-table' ).on( 'click', '.remove-line', this.paymentVoucher.onChange );
            $( 'table.erp-ac-transaction-table.payment-voucher-table' ).on( 'change', 'input.line_qty, input.line_price, input.line_dis, select.line_tax', this.paymentVoucher.onChange );
            //$( 'table.erp-ac-transaction-table.payment-voucher-table' ).on( 'change', 'select.erp-ac-tax-dropdown', this.paymentVoucher.onChange );
        },

        lineDue: function(e) {
            e.preventDefault();
            var line_due_total = 0;

            $.each( $('.erp-ac-line-due'), function( key, line_due ) {
                var due = $(line_due).val()  === '' ? '0' : ERP_Accounting.calNumNormal( $(line_due).val() );
                line_due_total = parseFloat( due ) + parseFloat( line_due_total );
            } );

            $('.erp-ac-total-due').val( ERP_Accounting.numFormating( line_due_total ) );
        },

        paymentReceive: function(e) {
            e.preventDefault();
            var self = $(this),
                user = self.val();

            wp.ajax.send( {
                data: {
                    action: 'erp_ac_payment_receive',
                    _wpnonce: ERP_AC.nonce,
                    user_id: user,
                    //account_id: $('.erp-ac-deposit-dropdown').val()
                },

                success: function(res) {
                    $('.erp-form').find('.erp-ac-receive-payment-table').html(res);
                    $('.erp-form').find( 'input[name="submit_erp_ac_trans_draft"]' ).hide();
                    ERP_Accounting.initTipTip();

                },

                error: function() {
                    var clone_form = $('.erp-ac-receive-payment-table-clone').html();

                    if ( clone_form == '' ) {
                        return;
                    }

                    $('.erp-form').find('.erp-ac-receive-payment-table').html(clone_form);
                    $('.erp-form').find( 'input[name="submit_erp_ac_trans_draft"]' ).show();
                    $('.erp-select2').select2();

                    ERP_Accounting.incrementField();
                    ERP_Accounting.initFields();
                }
            } );
        },


        /**
         * Table related general functions
         *
         * @type {Object}
         */
        table: {
            removeRow: function(e) {
                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                var self = $(this),
                    table = self.closest( 'table' );

                if ( table.find('tbody > tr').length < 2 ) {
                    return;
                }

                self.closest('tr').remove();
            },

            addRow: function(e) {
                e.preventDefault();

                var self = $(this),
                    table = self.closest( 'table' );

                // destroy the last select2 for proper cloning
                table.find('tbody > tr:last').find('select').not('select.erp-ac-tax-dropdown').select2('destroy');

                var tr = table.find('tbody > tr:last'),
                    clone = tr.clone();

                clone.find('input').val('');
                clone.find('input[name="line_qty[]"]').val(1);

                tr.after( clone );

                // re-initialize selec2
                $('.erp-ac-transaction-form').find('.erp-ac-transaction-table .erp-select2').select2();
            }
        },

        /**
         * Payment voucher
         *
         * @type {Object}
         */
        paymentVoucher: {

            calculate: function() {
                var table = $('table.payment-voucher-table');
                var total = 0.00;
                var total_tax = [];

                table.find('tbody > tr').each(function(index, el) {

                    if ( ! $(el).is(":visible") ) {
                        return;
                    }

                    var row        = $(el);
                    var qty        = ( row.find('input.line_qty').val() ) || 1;
                    var line_price = ( row.find('input.line_price').val() ) || '00';
                    var discount   = ( row.find('input.line_dis').val() ) || '00';
                    var tax_id     = row.find('select.line_tax').val();
                    var line_tax   = parseFloat('0.00');
                    var tax_amount = parseFloat('0.00');



                    qty        = ERP_Accounting.calNumNormal( qty );
                    line_price = ERP_Accounting.calNumNormal( line_price );
                    discount   = ERP_Accounting.calNumNormal( discount );


                    var price = parseFloat( qty ) * parseFloat( line_price );

                    if ( discount > 0 ) {
                        price -= ( price * discount ) / 100;
                    }

                    if ( tax_id != '-1' ) {
                        var tax_info = erp_ac_tax.rate[tax_id];
                            line_tax =  parseFloat( tax_info.rate );

                        tax_amount = ( parseFloat( price ) * parseFloat( line_tax ) ) / 100;
                    }

                    var prev_tax = isNaN( total_tax[tax_id] ) ? parseFloat('0.00') : total_tax[tax_id];
                    total_tax[tax_id] = tax_amount + prev_tax;

                    total += price;

                    row.find('input.line_total').val( ERP_Accounting.numFormating( price ) );
                    row.find('input.line_total_disp').val( ERP_Accounting.numFormating( price ) );
                    row.find('input.line_tax_amount').val( ERP_Accounting.numFormating( tax_amount ) );
                    row.find('input.line_tax_rate').val( ERP_Accounting.numFormating( line_tax ) );

                    // console.log(qty, line_price, discount);
                });

                var total_tax_amout = parseFloat( 0.00 );

                $.each( total_tax, function( tax_id, tax_amounts ) {
                    if ( typeof tax_amounts != 'undefined' ) {
                        total_tax_amout = parseFloat( tax_amounts ) + total_tax_amout;
                        $('input[data-tax_id="'+tax_id+'"]').val( ERP_Accounting.numFormating( tax_amounts ) );
                    }
                });

                var sub_total = total,
                    total     = sub_total + total_tax_amout;

                table.find('tfoot input.sub-total').val( ERP_Accounting.numFormating( sub_total ) );
                table.find('tfoot input.price-total').val( ERP_Accounting.numFormating( total ) );
            },

            onChange: function() {
                ERP_Accounting.paymentVoucher.taxDropdown();
                ERP_Accounting.paymentVoucher.calculate();
            },

            taxDropdown: function() {

                var table = $('.erp-ac-transaction-form').find('.erp-ac-transaction-form-table'),
                    taxs = table.find('.erp-ac-tax-dropdown'),
                    taxs_id = [];

                $.each( taxs, function( key, value ) {
                    var id = $(value).val();
                    if ( id != '-1' ) {
                        taxs_id.push(id);
                    }

                });

                var unique_taxs = $.unique( taxs_id ),
                    clone_tr    = $('.erp-ac-tr-wrap');

                if ( unique_taxs.length == '0' ) {
                    table.find('.erp-ac-tr-wrap').remove();
                    return;
                }
                table.find('.erp-ac-tr-wrap').remove();

                var tr_wrap  = $('#erp-ac-hidden-tax-table').find('.erp-ac-tr-wrap');

                $.each( unique_taxs, function( key, tax_id ) {
                   var  tax_info = erp_ac_tax.rate[tax_id],
                        text     = tax_info.name +' '+tax_info.rate+ '% ('+tax_info.number +')';

                    tr_wrap.find('.erp-ac-tax-text').text(text);
                    tr_wrap.find('.erp-ac-tax-total').attr( 'data-tax_id', tax_id );

                    var clone = tr_wrap.clone();
                    table.find('.erp-ac-price-total-wrap').before(clone);

                });
            }
        },

        /**
         * Journal entry
         *
         * @type {Object}
         */
        journal: {
            calculate: function() {

                var table = $('table.journal-table');
                var debit_total = credit_total = 0;

                table.find('tbody > tr').each(function(index, el) {
                    var row    = $(el);
                    var debit  = ( row.find('input.line_debit').val() ) || '0';
                    var credit = ( row.find('input.line_credit').val() ) || '0';

                    var debit = ERP_Accounting.calNumNormal( debit );
                    var credit = ERP_Accounting.calNumNormal( credit );

                    // both are filled
                    if ( debit > 0 && credit > 0 ) {
                        debit = 0;
                        row.find('input.line_debit').val( ERP_Accounting.numFormating( 0 ) );
                    }

                    debit_total +=  parseFloat( debit );
                    credit_total += parseFloat( credit );
                });

                var diff = debit_total - credit_total;

                table.find('tfoot input.debit-price-total').val( ERP_Accounting.numFormating( debit_total ) );
                table.find('tfoot input.credit-price-total').val( ERP_Accounting.numFormating( credit_total ) );

                if ( diff !== 0 ) {
                    table.find('th.col-diff').addClass('invalid').text( ERP_Accounting.numFormating( diff ) );
                    $( '#submit_erp_ac_journal' ).attr('disabled', 'disabled');

                } else {
                    table.find('th.col-diff').removeClass('invalid').text( ERP_Accounting.numFormating( diff ) );
                    $( '#submit_erp_ac_journal' ).removeAttr('disabled');
                }

            },

            onChange: function() {
                ERP_Accounting.journal.calculate();
            }
        },

        /**
         * Chart of accounts
         *
         * @type {Object}
         */
        accounts: {
            remove: function(e) {
                e.preventDefault();

                var self = $(this),
                    id   = self.data('id');


                swal({
                    title: ERP_AC.message.confirm,
                    type: "warning",
                    cancelButtonText: ERP_AC.message.cancel,
                    //confirmButtonText: 'asdfasd',
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: ERP_AC.message.delete,
                    closeOnConfirm: false,
                    showCancelButton: true,   closeOnConfirm: false,   showLoaderOnConfirm: true,
                },
                function(){

                    wp.ajax.send( 'erp-ac-remove-account', {
                        data: {
                            id: id,
                            _wpnonce: ERP_AC.nonce,
                        },
                        success: function(res) {
                            swal("", res.success, "success");
                            self.closest('tr').remove();
                        },
                        error: function(res) {

                            swal({
                                title: ERP_AC.message.error,
                                text: res.error,
                                type: "error",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#DD6B55"
                            });
                        }
                    });
                });


            },

            checkCode: function() {
                var self = $(this);
                var li = self.closest('li');

                wp.ajax.send( {
                    data: {
                        action: 'erp_ac_ledger_check_code',
                        '_wpnonce': ERP_AC.nonce,
                        code: self.val()
                    },
                    success: function(res) {
                        li.removeClass('invalid');
                    },
                    error: function(error) {
                        li.addClass('invalid');
                        alert( 'This code already exists, please try another.' );
                    }
                });
            }
        }
    };

    $(function() {
        ERP_Accounting.initialize();

        $( 'select.erp-ac-customer-search' ).select2({
            minimumInputLength: 3,
            allowClear: true,
            ajax: {
                cache: true,
                url: ajaxurl,
                dataType: 'json',
                quietMillis: 250,
                data: function( term ) {
                    return {
                        term: term,
                        action: 'erp_ac_customer_search',
                        _wpnonce: ''
                    };
                },
            }
        });
    });

})(jQuery);

