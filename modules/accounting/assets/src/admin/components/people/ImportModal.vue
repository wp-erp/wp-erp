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

                        <div v-if="showError" v-html="error" class="notice is-dismissible" id="erp-csv-import-error"></div>

                        <!-- end modal body title -->
                        <form action="" method="post" class="modal-form edit-customer-modal" id="import_form" @submit.prevent="importCsv">
                            <div class="wperp-modal-body">
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
            error: '',
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
</style>
