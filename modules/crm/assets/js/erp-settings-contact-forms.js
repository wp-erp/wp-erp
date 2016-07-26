;(function($) {
    'use strict';

    // remove default submit button
    if ($('.cfi-hide-submit').length) {
        $('p.submit').remove();
    }

    Vue.config.debug = crmContactFormsSettings.scriptDebug;

    // when get_option returns null, localized var for mappedData prints "" instead of {}
    if ( '[object Object]' !== Object.prototype.toString.call(crmContactFormsSettings.mappedData) ) {
        crmContactFormsSettings.mappedData = {};
    }

    // vue instances for every mapping table
    $('.cfi-table').each(function () {
        var table = $(this),
            id = table.attr('id'),
            plugin = table.data('plugin'),
            formId = table.data('form-id');

        if ( '[object Object]' !== Object.prototype.toString.call(crmContactFormsSettings.mappedData[plugin]) ) {
            crmContactFormsSettings.mappedData[plugin] = {};
        }

        new Vue({
            el: '#' + id,
            data: {
                i18n: crmContactFormsSettings.i18n,
                plugin: plugin,
                formId: formId,
                formData: crmContactFormsSettings.forms[plugin][formId],
                totalFields: 0,
                crmOptions: crmContactFormsSettings.crmOptions,
                contactGroups: crmContactFormsSettings.contactGroups,
                contactOwners: crmContactFormsSettings.contactOwners,
                activeDropDown: null
            },

            computed: {
                totalFields: function () {
                    return Object.keys(this.formData.fields).length;
                }
            },

            methods: {

                lastOfTypeClass: function (index) {
                    return index === (this.totalFields - 1) ? 'cfi-mapping-row-last' : '';
                },

                getCRMOptionTitle: function (field) {
                    var option = this.formData.map[field],
                        title = '';

                    if (option && option.indexOf('.') < 0) {
                        title = this.crmOptions[ option ];

                    } else if (option) {
                        var arr = option.split('.');
                        title = this.crmOptions[arr[0]].title + ' - ' + this.crmOptions[arr[0]].options[arr[1]];
                    }

                    return title ? title : this.i18n.notMapped;
                },

                optionIsAnObject: function (option) {
                    return '[object Object]' === Object.prototype.toString.call(this.crmOptions[option]);
                },

                mapOption: function (field, option) {
                    this.formData.map[field] = option;
                },

                mapChildOption: function (field, option, childOption) {
                    this.formData.map[field] = option + '.' + childOption;
                },

                isMapped: function (field) {
                    return !this.formData.map[field];
                },

                isOptionMapped: function (field, option) {
                    return this.formData.map[field] === option;
                },

                isChildOptionMapped: function (field, option, childOption) {
                    return this.formData.map[field] === (option + '.' + childOption);
                },

                resetMapping: function (field) {
                    this.formData.map[field] = null;
                },

                setActiveDropDown: function (field) {
                    this.activeDropDown = (field === this.activeDropDown) ? null: field;
                },

                save_mapping: function (e) {
                    e.preventDefault();
                    this.makeAjaxRequest('erp_settings_save_contact_form');
                },

                reset_mapping: function (e) {
                    e.preventDefault();
                    this.makeAjaxRequest('erp_settings_reset_contact_form');
                },

                makeAjaxRequest: function (action) {
                    var self = this;

                    $.ajax({
                        url: ajaxurl,
                        method: 'post',
                        dataType: 'json',
                        data: {
                            action: action,
                            _wpnonce: crmContactFormsSettings.nonce,
                            plugin: this.plugin,
                            formId: this.formId,
                            map: self.formData.map,
                            contactGroup: self.formData.contactGroup,
                            contactOwner: self.formData.contactOwner,
                        }

                    }).done(function (response) {

                        if ('erp_settings_reset_contact_form' === action && response.success) {
                            self.$set('formData.map', response.map);
                            self.$set('formData.contactGroup', response.contactGroup);
                            self.$set('formData.contactOwner', response.contactOwner);
                        }

                        var type = response.success ? 'success' : 'error';

                        if (response.msg) {
                            swal({
                                title: '',
                                text: response.msg,
                                type: type,
                                confirmButtonText: self.i18n.labelOK,
                                confirmButtonColor: '#008ec2'
                            });
                        }

                    });
                }

            },

            watch: {
                'formData.map': {
                    deep: true,
                    handler: function (newVal) {
                        this.formData.map = newVal;
                    }
                },

                'formData.contactGroup': function (newVal) {
                    this.formData.contactGroup = newVal;
                }
            }
        });
    });
})(jQuery);
