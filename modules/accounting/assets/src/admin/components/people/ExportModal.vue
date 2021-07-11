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

                        <form action="" method="post" class="modal-form edit-customer-modal" id="export_form">
                            <div class="wperp-modal-body">
                                <div class="erp-grid-container">
                                    <div class="row">
                                        <div class="col-3">
                                            <label for="fields">
                                                <h3>{{ description }}<span class="required"> *</span></h3>
                                            </label>
                                        </div>

                                        <div class="col-3">
                                            <h3>
                                                <input type="checkbox" id="selecctall" @change.prevent="selectFields" /> 
                                                {{ __('Select all', 'erp') }}
                                            </h3>
                                        </div>
                                    </div>

                                    <div class="row" id="fields">
                                        <div v-for="(field, key) in peopleFields" :key="key" class="col-2">
                                            <label>
                                                <input type="checkbox" name="fields[]" :value="field" :checked="selectAll">
                                                {{ strTitleCase(field) }}
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="row"></div>

                                    <div class="row">
                                        <p class="description">{{ __( '**Only selected fields will be on the csv file.', 'erp' ) }}</p>
                                    </div>
                                </div>

                                <input type="hidden" name="type" :value="peopleType">
                                <input type="hidden" name="erp_export_csv" value="1">
                                <input type="hidden" name="_wpnonce" :value="nonce">

                            </div>

                            <div class="wperp-modal-footer pt-0">
                                <div class="buttons-wrapper text-right">
                                    <button class="wperp-btn btn--default modal-close" @click="$parent.$emit('modal-close')" type="reset">{{ __('Cancel', 'erp') }}</button>
                                    <button class="wperp-btn btn--primary" type="submit">{{ __('Export', 'erp') }}</button>
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
    name: 'ExportModal',

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
            fieldsHtml: '',
            nonce: '',
            description: '',
            peopleType: '',
            selectAll: false,
        };
    },

    created() {
        this.peopleType   = 'customers' == this.type ? 'customer' : 'vendor';
        this.peopleFields = erp_acct_var.erp_fields ? erp_acct_var.erp_fields[this.peopleType].fields : [];
        this.nonce        = erp_acct_var.export_import_nonce;
        this.description  = 'customer' === this.peopleType
                          ? __('Select customer fields to export', 'erp')
                          : __('Select vendor fields to export', 'erp');
    },

    methods: {
        selectFields() {
            this.selectAll = ! this.selectAll;
        },

        strTitleCase(string) {
            var str = string.toString().replace(/_/g, ' ');

            return str.toLowerCase().split(' ').map(function (word) {
                return (word.charAt(0).toUpperCase() + word.slice(1));
            }).join(' ');
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

    .description {
        color: grey;
    }
</style>
