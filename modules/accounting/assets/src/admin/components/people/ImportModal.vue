<template>
    <div id="people-modal">
        <div class="wperp-container">
            <div id="wperp-import-customer-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
                <div class="wperp-modal-dialog">
                    <div class="wperp-modal-content">
                        <div class="wperp-modal-header">
                            <h3>{{ title }}</h3>
                            <span class="modal-close">
                                <i class="flaticon-close" @click="$parent.$emit('modal-close')"></i>
                            </span>
                        </div>

                        <form action="" method="post" class="modal-form edit-customer-modal" id="import_form" @submit.prevent="importCsv">
                            <div class="wperp-modal-body">
                                <div v-if="showError" class="notice notice-error erp-error-notice" id="erp-csv-import-error">
                                    <ul class="erp-list" v-if="isObject(errors)">
                                        <li v-for="(error, index) in errors" :key="index" v-html="error"></li>
                                    </ul>

                                    <span v-else v-html="errors"></span>
                                </div>

                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th>
                                                <label for="csv_file">{{ __( 'CSV File', 'erp' ) }} <span class="required">*</span></label>
                                            </th>
                                            <td>
                                                <input type="file" name="csv_file" id="csv_file" @change.prevent="processFields()" required />

                                                <p class="description">
                                                    {{ __('Upload a csv file.', 'erp') }}
                                                    <span class="erp-help-tip .erp-tips" :title="__('Make sure CSV meets the sample CSV format exactly.', 'erp')"></span>
                                                </p>

                                                <p id="download_sample_wrap" v-if="sampleUrl">
                                                    <button class="button button-primary"
                                                        id="erp-employee-sample-csv"
                                                        @click.prevent="downloadSample">
                                                        {{ __( 'Download Sample CSV', 'erp' ) }}
                                                    </button>
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>

                                    <tbody v-if="fieldsHtml" v-html="fieldsHtml" id="erp-csv-fields-container"></tbody>
                                </table>

                                <input type="hidden" name="type" :value="peopleType">
                                <input type="hidden" name="action" value="erp_import_csv">
                                <input type="hidden" name="_wpnonce" :value="nonce">

                            </div>

                            <div class="wperp-modal-footer pt-0">
                                <!-- buttons -->
                                <div class="buttons-wrapper text-right">
                                    <button class="wperp-btn btn--default modal-close" @click="$parent.$emit('modal-close')" type="reset">{{ __('Cancel', 'erp') }}</button>
                                    <button class="wperp-btn btn--primary" type="submit">{{ __('Import', 'erp') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'ImportModal',

    props: {
        type: {
            type: String
        },
        title: {
            required: true
        },
    },

    data() {
        return {
            sampleUrl: '',
            peopleFields: [],
            peopleType: '',
            fieldsHtml: '',
            nonce: '',
            errors: '',
            showError: false,
        };
    },

    created() {
        var self = this;

        this.peopleFields = erp_acct_var.erp_fields;
        this.nonce = erp_acct_var.export_import_nonce;
        this.peopleType = 'customers' == this.type ? 'customer' : 'vendor';

        wp.ajax.send({
            data: {
                action: 'erp_acct_get_sample_csv_url',
                type: this.type,
                path: this.$router.currentRoute.path
            },
            success: function(response) {
                self.sampleUrl = response;
            }
        });
    },

    methods: {
        downloadSample() {
            window.location.href = this.sampleUrl;
        },

        importCsv() {
            var self = this,
                data = new FormData( jQuery( 'form#import_form' ).get(0) );

            wp.ajax.send({
                data: data,
                processData: false,
                contentType: false,
                success: function(response) {
                    self.$root.$emit('imported-people');
                    self.$store.dispatch('spinner/setSpinner', false);
                    self.showAlert('success', response);
                },
                error: function(error) {
                    self.showError = true;
                    self.errors     = error;

                    jQuery("#import_form > div").animate( {
                        scrollTop: 0
                    }, 'fast' );
                }
            });
        },

        processFields() {
            var required  = '',
                reqSpan   = '',
                fields    = [],
                reqFields = [];

            if (this.peopleFields[this.peopleType]) {
                fields    = this.peopleFields[this.peopleType].fields;
                reqFields = this.peopleFields[this.peopleType].required_fields;
            }

            for (var i = 0; i < fields.length; i++) {
                required = '';
                reqSpan  = '';

                if (reqFields.indexOf(fields[i]) !== -1) {
                    required = 'required';
                    reqSpan = ' <span class="required">*</span>';
                }

                this.fieldsHtml += `<tr>
                                        <th>
                                            <label for="fields[${fields[i]}]" class="csv_field_labels">${this.strTitleCase(fields[i])}${reqSpan}</label>
                                        </th>
                                        <td>
                                            <select name="fields[${fields[i]}]" class="csv_fields" ${required}>
                                            </select>
                                        </td>
                                    </tr>`;
            }

            this.mapCsvFields(document.getElementById('csv_file'), '.csv_fields');
        },

        mapCsvFields(fileSelector, fieldSelector) {
            var file      = fileSelector.files[0],
                reader    = new FileReader(),
                first5000 = file.slice(0, 5000),
                self      = this;

            reader.readAsText(first5000);

            reader.onload = function (e) {
                var csv             = reader.result,
                    lines           = csv.split('\n'),
                    columnNamesLine = lines[0],
                    columnNames     = columnNamesLine.split(','),
                    html            = '';

                html += '<option value="">&mdash; Select Field &mdash;</option>';

                columnNames.forEach(function (item, index) {
                    item = item.replace(/"/g, "");

                    html += `<option value="${index}">${item}</option>`;
                });

                if (html) {
                    jQuery(fieldSelector).html(html);

                    jQuery(fieldSelector).each(function () {
                        var fieldLabel   = jQuery(this).parent().parent().find('label').text(),
                            options      = jQuery(this).find('option'),
                            targetOption = jQuery(options).filter(function () {
                                var optionText = jQuery(this).html(),
                                    regEx      = new RegExp(fieldLabel, 'i');

                                return regEx.test(optionText);
                            });

                        if (targetOption) {
                            jQuery(options).removeAttr("selected");
                            jQuery(this).val(jQuery(targetOption).val());
                        }
                    });
                }
            };
        },

        strTitleCase(string) {
            var str = string.replace(/_/g, ' ');

            return str.toLowerCase().split(' ').map(function (word) {
                return (word.charAt(0).toUpperCase() + word.slice(1));
            }).join(' ');
        },

        isObject(item) {
            return typeof item === 'object' || typeof item === 'array';
        },
    },
};
</script>

<style lang="less" scoped>
    .modal-close {
        .flaticon-close {
            font-size: inherit;
        }
    }

    .errors {
        margin: 0 20px;
        color: #f44336;

        li {
            background: #f3f3f3;
            padding: 2px 10px;
        }
    }

    #erp-csv-import-error {
        font-size: 16px !important;
        padding: 5px !important;
    }
</style>
