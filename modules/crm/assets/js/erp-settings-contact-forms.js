;(function($) {
    'use strict';

    if ($('.cfi-table').length) {
        $('p.submit').remove();
    }

    if (crmContactFormsSettings.scriptDebug) {
        Vue.config.debug = true;
    }

    // when get_option returns null, localized var for mappedData prints "" instead of {}
    if ( '[object Object]' !== Object.prototype.toString.call(crmContactFormsSettings.mappedData) ) {
        crmContactFormsSettings.mappedData = {};
    }

    var vueInstances = {};

    $('.cfi-table').each(function () {
        var table = $(this),
            id = table.attr('id'),
            plugin = table.data('plugin'),
            formId = table.data('form-id');

        if ( '[object Object]' !== Object.prototype.toString.call(crmContactFormsSettings.mappedData[plugin]) ) {
            crmContactFormsSettings.mappedData[plugin] = {};
        }

        vueInstances[plugin + '_' + formId] = new Vue({
            el: '#' + id,
            data: {
                plugin: plugin,
                formId: formId,
                formData: crmContactFormsSettings.forms[plugin][formId],
                crmOptions: crmContactFormsSettings.crmOptions,
                activeDropDown: null
            },

            methods: {

                getCRMOptionTitle: function (field) {
                    var option = this.formData.map[field],
                        title = '';

                    if (option && option.indexOf('.') < 0) {
                        title = this.crmOptions[ option ];

                    } else if (option) {
                        var arr = option.split('.');
                        title = this.crmOptions[arr[0]].title + ' - ' + this.crmOptions[arr[0]].options[arr[1]];
                    }

                    return title ? title : crmContactFormsSettings.notMapped;
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

            },

            watch: {
                'formData.map': {
                    deep: true,
                    handler: function (newVal) {
                        crmContactFormsSettings.mappedData[plugin][formId] = newVal;
                    }
                }
            }
        });
    });

    // Save Settings
    $('.cfi-settings-submit').on('click', function (e) {
        e.preventDefault();

        var button = $(this),
            plugin = button.data('plugin'),
            formId = button.data('form-id');

        $.ajax({
            url: ajaxurl,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'erp_settings_contact_forms',
                _wpnonce: crmContactFormsSettings.nonce,
                plugin: plugin,
                formId: formId,
                map: crmContactFormsSettings.mappedData[plugin][formId]
            }

        }).done(function (response) {
            var type = response.success ? 'success' : 'error';

            if (response.msg) {
                swal({
                    title: '',
                    text: response.msg,
                    type: type,
                    confirmButtonText: crmContactFormsSettings.labelOK,
                    confirmButtonColor: '#008ec2'
                });
            }
        });
    });

    $('.cfi-settings-reset').on('click', function (e) {
        e.preventDefault();

        var button = $(this),
            plugin = button.data('plugin'),
            formId = button.data('form-id');

        $.ajax({
            url: ajaxurl,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'erp_settings_contact_forms_reset',
                _wpnonce: crmContactFormsSettings.nonce,
                plugin: plugin,
                formId: formId,
            }

        }).done(function (response) {

            if (response.success) {
                vueInstances[plugin + '_' + formId].$set('formData.map', response.map);
            }

            var type = response.success ? 'success' : 'error';

            if (response.msg) {
                swal({
                    title: '',
                    text: response.msg,
                    type: type,
                    confirmButtonText: crmContactFormsSettings.labelOK,
                    confirmButtonColor: '#008ec2'
                });
            }

        });
    });

})(jQuery);
