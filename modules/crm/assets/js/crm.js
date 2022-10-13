/* jshint devel:true */
/* global wpErpCrm */
/* global wp */

;(function ($) {
    'use strict';

    var isRequestDone = false;

    var WeDevs_ERP_CRM = {

        initialize: function () {
            // Contact Group
            $('.erp-crm-contact-group').on('click', 'a.erp-new-contact-group', this.contactGroup.create);
            $('.erp-crm-contact-group').on('click', 'span.edit a', this.contactGroup.edit);
            $('.erp-crm-contact-group').on('click', 'a.submitdelete', this.contactGroup.remove);

            //contact Tag
            $('document').ready(this.contactTag.init);
            $('#add-crm-tag').on('click', this.contactTag.add);
            $('.erp-customer-tag-div').on('keypress', '.newtag', this.contactTag.add);

            // Subscriber contact
            $('.erp-crm-subscriber-contact').on('click', 'a.erp-new-subscriber-contact', this.subscriberContact.create);
            $('.erp-crm-subscriber-contact').on('click', 'span.edit a', this.subscriberContact.edit);
            $('.erp-crm-subscriber-contact').on('click', 'a.submitdelete', this.subscriberContact.remove);

            // Populate state according to country
            $('body').on('change', 'select.erp-country-select', this.populateState);

            // handle postbox toggle
            $('body').on('click', 'div.erp-handlediv', this.handlePostboxToggle);

            // When create modal open
            $('body').on( 'click', '#erp-customer-new', this.whenOpenCRMModal );
            $('body').on( 'click', '#erp-company-new', this.whenOpenCRMModal );
            $('body').on( 'click', '#erp-customer-edit', this.whenOpenCRMModal );
            $('body').on( 'click', '#erp-crm-new-contact', this.whenOpenCRMModal );

            // CRM Dashboard
            $('.crm-dashboard').on('click', 'a.erp-crm-dashbaord-show-details-schedule', this.dashboard.showScheduleDetails);

            $('body').on('change', 'input[type=checkbox][name="all_day"]', this.triggerCustomerScheduleAllDay);
            $('body').on('change', 'input[type=checkbox][name="allow_notification"]', this.triggerCustomerScheduleAllowNotification);
            $('body').on('change', 'select#erp-crm-feed-log-type', this.triggerLogType);

            // Report
            if ( 'this_year' == $('#crm-filter-duration').val() ) {
                $('.custom-filter').hide();
            }

            $( 'body').on( 'change', '#crm-filter-duration', this.report.customFilter );

            $('body').on( 'change', '.wp-list-table', function(e) {
                var selector = $('.wp-list-table tbody tr th input[type="checkbox"]');

                if ( selector.is(':checked') ) {
                    $('.tablenav .bulkactions').show();
                } else {
                    $('.tablenav .bulkactions').hide();
                }
            });

            // CRM tag
            this.initTagAddByEnterPressed();

            // Erp ToolTips using tiptip
            this.initContactListAjax();
            this.initTipTips();
        },

        initTagAddByEnterPressed: function() {
            var enter_key = 13;

            $( '.newtag' ).on( 'keyup', function(e) {
                var code = e.keyCode || e.which;

                if ( code == enter_key ) {
                    $( '#add-crm-tag' ).trigger('click');
                }
            } );
        },

        initTipTips: function () {
            $('.erp-crm-tips').tipTip({
                defaultPosition: "top",
                fadeIn: 100,
                fadeOut: 100,
            });
        },

        initDateField: function () {
            $('.erp-crm-date-field').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
            });
        },

        /**
         * Timepicker initialize
         *
         * @return {[void]}
         */
        initTimePicker: function () {
            $('.erp-time-field').timepicker({
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
        handlePostboxToggle: function (e) {
            e.preventDefault();
            var self = $(this),
                postboxDiv = self.closest('.postbox');

            if (postboxDiv.hasClass('closed')) {
                postboxDiv.removeClass('closed');
            } else {
                postboxDiv.addClass('closed');
            }
        },
        initContactListAjax: function() {
            $( 'select.erp-crm-contact-list-dropdown' ).select2({
                allowClear: true,
                placeholder: $(this).attr('data-placeholder'),
                minimumInputLength: 1,
                ajax: {
                    url: wpErpCrm.ajaxurl,
                    dataType: 'json',
                    delay: 250,
                    escapeMarkup: function (m) {
                        return m;
                    },
                    data: function (params) {
                        return {
                            s: params.term, // search term
                            _wpnonce: wpErpCrm.nonce,
                            types: $(this).attr('data-types').split(','),
                            action: 'erp-search-crm-contacts'
                        };
                    },
                    processResults: function (data, params) {
                        var terms = [];

                        if (data) {
                            $.each(data.data, function (id, text) {
                                terms.push({
                                    id: id,
                                    text: text
                                });
                            });
                        }

                        if (terms.length) {
                            return {results: terms};
                        } else {
                            return {results: ''};
                        }
                    },
                    cache: true
                }
            });
        },

        /**
         * When open CRM modal to create contact
         *
         * @param  {object} e
         *
         * @return {void}
         */
        whenOpenCRMModal: function(e) {
            $( '#advanced_fields' ).click( function( evt ) {
                if ( $( this ).is(' :checked ') ) {
                    $( '.others-info' ).show();
                    $( '.contact-group' ).show();
                    $( '.additional-info' ).show();
                    $( '.social-info' ).show();
                } else {
                    $( '.others-info' ).hide();
                    $( '.contact-group' ).hide();
                    $( '.social-info' ).hide();
                    $( '.additional-info' ).hide();
                }
            } );
        },
        /**
         * Populate the state dropdown based on selected country
         *
         * @return {void}
         */
        populateState: function () {

            wpErpCrm.wpErpCountries = wpErpCountries;

            if (typeof wpErpCrm.wpErpCountries === 'undefined') {
                return false;
            }

            var self = $(this),
                country = self.val(),
                parent = self.closest(self.data('parent')),
                empty = '<option value="">- '+ __('Select', 'erp') +' -</option>';

            if (wpErpCrm.wpErpCountries[country]) {
                var options = '',
                    state = wpErpCrm.wpErpCountries[country];

                for (var index in state) {
                    options = options + '<option value="' + index + '">' + state[index] + '</option>';
                }

                if ($.isArray(wpErpCrm.wpErpCountries[country])) {
                    $('.erp-state-select').html(empty);
                } else {
                    $('.erp-state-select').html(options);
                }

            } else {
                $('.erp-state-select').html(empty);
            }
        },

        triggerCustomerScheduleAllDay: function () {
            var self = $(this);

            if (self.is(':checked')) {
                self.closest('div.schedule-datetime').find('.erp-time-field').attr('disabled', 'disabled').hide();
                self.closest('div.schedule-datetime').find('.datetime-sep').hide();
            } else {
                self.closest('div.schedule-datetime').find('.erp-time-field').removeAttr('disabled').show();
                self.closest('div.schedule-datetime').find('.datetime-sep').show();
            }
            ;
        },

        triggerCustomerScheduleAllowNotification: function () {
            var self = $(this);

            if (self.is(':checked')) {
                self.closest('.erp-crm-new-schedule-wrapper').find('#schedule-notification-wrap').show();
            } else {
                self.closest('.erp-crm-new-schedule-wrapper').find('#schedule-notification-wrap').hide();
            }
        },

        triggerLogType: function () {
            var self = $(this);

            if (self.val() == 'meeting') {
                self.closest('.feed-log-activity').find('.log-email-subject').hide();
                self.closest('.feed-log-activity').find('.log-selected-contact').show();
            } else if (self.val() == 'email') {
                self.closest('.feed-log-activity').find('.log-email-subject').show();
                self.closest('.feed-log-activity').find('.log-selected-contact').hide();
            } else {
                self.closest('.feed-log-activity').find('.log-email-subject').hide();
                self.closest('.feed-log-activity').find('.log-selected-contact').hide();
            }
        },

        dashboard: {

            showScheduleDetails: function (e) {
                e.preventDefault();
                var self = $(this),
                    scheduleId = self.data('schedule_id');

                $.erpPopup({
                    title: self.attr('data-title'),
                    button: '',
                    id: 'erp-customer-edit',
                    onReady: function () {
                        var modal = this;

                        $('header', modal).after($('<div class="loader"></div>').show());

                        wp.ajax.send('erp-crm-get-single-schedule-details', {
                            data: {
                                id: scheduleId,
                                _wpnonce: wpErpCrm.nonce
                            },

                            success: function (response) {
                                var startDate = wperp.dateFormat(response.start_date, 'j F'),
                                    startTime = wperp.timeFormat(response.start_date),
                                    endDate = wperp.dateFormat(response.end_date, 'j F'),
                                    endTime = wperp.timeFormat(response.end_date);

                                if (response.extra.all_day == 'true') {
                                    if (wperp.dateFormat(response.start_date, 'Y-m-d') == wperp.dateFormat(response.end_date, 'Y-m-d')) {
                                        var datetime = startDate;
                                    } else {
                                        var datetime = startDate + ' to ' + endDate;
                                    }
                                } else {
                                    if (wperp.dateFormat(response.start_date, 'Y-m-d') == wperp.dateFormat(response.end_date, 'Y-m-d')) {
                                        var datetime = startDate + ' at ' + startTime + ' to ' + endTime;
                                    } else {
                                        var datetime = startDate + ' at ' + startTime + ' to ' + endDate + ' at ' + endTime;
                                    }
                                }

                                var html = wp.template('erp-crm-single-schedule-details')({
                                    date: datetime,
                                    schedule: response
                                });
                                $('.content', modal).html(html);
                                $('.loader', modal).remove();

                                $('.erp-tips').tipTip({
                                    defaultPosition: "top",
                                    fadeIn: 100,
                                    fadeOut: 100,
                                });
                            },

                            error: function (response) {
                                modal.showError(response);
                            }

                        });
                    }
                });

            }
        },

        contactGroup: {

            pageReload: function () {
                $('.erp-crm-contact-group-list-table-wrap').load(window.location.href + ' .erp-crm-contact-group-list-table-inner');
            },

            create: function (e) {
                e.preventDefault();
                var self = $(this);
                $.erpPopup({
                    title: self.attr('title'),
                    button: wpErpCrm.add_submit,
                    id: 'erp-crm-new-contact-group',
                    content: wperp.template('erp-crm-new-contact-group')({data: {}}).trim(),
                    extraClass: 'smaller',

                    onSubmit: function (modal) {
                        modal.disableButton();

                        wp.ajax.send({
                            data: this.serialize(),
                            success: function (res) {
                                WeDevs_ERP_CRM.contactGroup.pageReload();
                                modal.enableButton();
                                modal.closeModal();
                            },
                            error: function (error) {
                                modal.enableButton();
                                modal.showError(error);
                            }
                        });
                    },

                    onReady: function () {
                        var modal = this;

                        $('div.row[data-checked]', modal).each(function (key, val) {
                            var self = $(this),
                                checked = self.data('checked');

                            if (checked !== '') {
                                self.find('input[value="' + checked + '"]').attr('checked', 'checked');
                            }
                        });
                    }
                }); //popup
            },

            edit: function (e) {
                e.preventDefault();

                var self = $(this),
                    query_id = self.data('id');

                $.erpPopup({
                    title: self.attr('title'),
                    button: wpErpCrm.update_submit,
                    id: 'erp-crm-edit-contact-group',
                    extraClass: 'smaller',
                    onReady: function () {
                        var modal = this;

                        $('header', modal).after($('<div class="loader"></div>').show());

                        wp.ajax.send('erp-crm-edit-contact-group', {
                            data: {
                                id: query_id,
                                _wpnonce: wpErpCrm.nonce
                            },
                            success: function (res) {
                                var html = wp.template('erp-crm-new-contact-group')(res);
                                $('.content', modal).html(html);
                                $('.loader', modal).remove();

                                $('div.row[data-checked]', modal).each(function (key, val) {
                                    var self = $(this),
                                        checked = self.data('checked');

                                    if (checked !== '') {
                                        self.find('input[value="' + checked + '"]').attr('checked', 'checked');
                                    }
                                });
                            }
                        });
                    },

                    onSubmit: function (modal) {
                        modal.disableButton();

                        wp.ajax.send({
                            data: this.serialize(),
                            success: function (res) {
                                WeDevs_ERP_CRM.contactGroup.pageReload();
                                modal.enableButton();
                                modal.closeModal();
                            },
                            error: function (error) {
                                modal.enableButton();
                                modal.showError(error);
                            }
                        });
                    }

                });
            },

            remove: function (e) {
                e.preventDefault();

                var self = $(this);

                if (confirm(wpErpCrm.delConfirm)) {
                    wp.ajax.send('erp-crm-contact-group-delete', {
                        data: {
                            '_wpnonce': wpErpCrm.nonce,
                            id: self.data('id')
                        },
                        success: function () {
                            self.closest('tr').fadeOut('fast', function () {
                                $(this).remove();
                                WeDevs_ERP_CRM.contactGroup.pageReload();
                            });
                        },
                        error: function (response) {
                            alert(response);
                        }
                    });
                }
            }
        },

        contactTag: {
            init:function () {
                $(document).on('click', '.ntdelbutton', function () {
                    var tags = $('#tax-input-erp_crm_tag').val();
                    var contact_id = $('#contact_id').val();

                    wp.ajax.send('erp_crm_update_contact_tag', {
                        data: {
                            _wpnonce: wpErpCrm.nonce,
                            tags: tags,
                            contact_id: contact_id,
                        },
                        success: function (res) {

                        }
                    });
                });
            },
            add: function () {
                var tags = $('#tax-input-erp_crm_tag').val();
                var contact_id = $('#contact_id').val();

                wp.ajax.send('erp_crm_update_contact_tag', {
                    data: {
                        _wpnonce: wpErpCrm.nonce,
                        tags: tags,
                        contact_id: contact_id,
                    },
                    success: function (res) {
                        console.log(res);
                    }
                });
            },
        },

        subscriberContact: {
            pageReload: function () {
                $('.erp-crm-subscriber-contact-list-table-wrap').load(window.location.href + ' .erp-crm-subscriber-contact-list-table-inner');
            },

            create: function (e) {
                e.preventDefault();
                var self = $(this);

                $.erpPopup({
                    title: self.attr('title'),
                    button: wpErpCrm.add_submit,
                    id: 'erp-crm-assign-subscriber-contact',
                    extraClass: 'smaller',
                    onReady: function (modal) {

                        var modal = this;

                        $('header', modal).after($('<div class="loader"></div>').show());

                        wp.ajax.send('erp-crm-exclued-already-assigned-contact', {
                            data: {
                                _wpnonce: wpErpCrm.nonce
                            },
                            success: function (res) {
                                var html = wp.template('erp-crm-assign-subscriber-contact')({data: {}});
                                $('.content', modal).html(html);

                                _.each($('.select2').find('option'), function (el, i) {
                                    var optionVal = $(el).val();
                                    if (_.contains(res, optionVal)) {
                                        $(el).attr('disabled', 'disabled');
                                    }
                                    ;
                                });

                                WeDevs_ERP_CRM.initContactListAjax();
                                $('.loader', modal).remove();
                            }
                        });

                    },

                    onSubmit: function (modal) {

                        if ($("input:checkbox:checked").length > 0) {
                            modal.disableButton();
                            wp.ajax.send({
                                data: this.serialize(),
                                success: function (res) {
                                    WeDevs_ERP_CRM.subscriberContact.pageReload();
                                    modal.enableButton();
                                    modal.closeModal();
                                },
                                error: function (error) {
                                    modal.enableButton();
                                    alert(error);
                                }
                            });
                        } else {
                            modal.showError(wpErpCrm.checkedConfirm);
                        }
                    }
                }); //popup
            },

            edit: function (e) {
                e.preventDefault();

                var self = $(this),
                    query_id = self.data('id'),
                    name = self.data('name');

                $.erpPopup({
                    title: self.attr('title'),
                    button: wpErpCrm.update_submit,
                    id: 'erp-crm-edit-contact-subscriber',
                    extraClass: 'smaller',
                    onReady: function () {
                        var modal = this;

                        $('header', modal).after($('<div class="loader"></div>').show());

                        wp.ajax.send('erp-crm-edit-contact-subscriber', {
                            data: {
                                id: query_id,
                                name: name,
                                _wpnonce: wpErpCrm.nonce
                            },
                            success: function (res) {
                                var html = wp.template('erp-crm-assign-subscriber-contact')({
                                    group_id: res.groups,
                                    user_id: query_id
                                });
                                $('.content', modal).html(html);
                                _.each($('input[type=checkbox].erp-crm-contact-group-class'), function (el, i) {
                                    var optionsVal = $(el).val();
                                    if (_.contains(res.groups, optionsVal) && res.results[optionsVal].status == 'subscribe') {
                                        $(el).prop('checked', true);
                                    }
                                    if (_.contains(res.groups, optionsVal) && res.results[optionsVal].status == 'unsubscribe') {
                                        $(el).closest('label').find('span.checkbox-value')
                                            .append('<span class="unsubscribe-group">' + res.results[optionsVal].unsubscribe_message + '</span>');
                                    }
                                    ;

                                });

                                $('.loader', modal).remove();
                            }
                        });
                    },

                    onSubmit: function (modal) {
                        modal.disableButton();

                        wp.ajax.send({
                            data: this.serialize(),
                            success: function (res) {
                                if (e.target.id == 'erp-contact-update-assign-group') {
                                    $('.contact-group-content').load(window.location.href + ' .contact-group-list', function () {
                                        WeDevs_ERP_CRM.initTipTips();
                                    });
                                } else {
                                    WeDevs_ERP_CRM.subscriberContact.pageReload();
                                }

                                modal.enableButton();
                                modal.closeModal();
                            },
                            error: function (error) {
                                modal.enableButton();
                                modal.showError(error);
                            }
                        });
                    }

                });
            },
            remove: function (e) {
                e.preventDefault();

                var self = $(this);

                if (confirm(wpErpCrm.delConfirm)) {
                    wp.ajax.send('erp-crm-contact-subscriber-delete', {
                        data: {
                            '_wpnonce': wpErpCrm.nonce,
                            group_id: self.data('group_id'),
                            id: self.data('id')
                        },
                        success: function () {
                            self.closest('tr').fadeOut('fast', function () {
                                $(this).remove();
                                WeDevs_ERP_CRM.contactGroup.pageReload();
                            });
                        },
                        error: function (response) {
                            alert(response);
                        }
                    });
                }
            }
        },

        report: {
            customFilter: function () {
                if ( 'custom' == this.value ) {
                    $('.custom-filter').show();
                } else {
                    $('.custom-filter').hide();
                }
            }
        }
    };

    $(function () {
        WeDevs_ERP_CRM.initialize();
    });

})(jQuery);
