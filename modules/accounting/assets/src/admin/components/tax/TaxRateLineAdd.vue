<template>
    <div id="wperp-tax-rate-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>{{ __('Add New', 'erp') }}</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>

                <div class="wperp-invoice-table">
                    <div class="wperp-panel-body">
                        <div class="wperp-row">
                            <div class="wperp-col-sm-3 wperp-col-xs-12">
                                <label>{{ __('Component', 'erp') }}</label>
                                <input type="text" v-model="component_name" />
                            </div>
                            <div class="wperp-col-sm-3 wperp-col-xs-12 with-multiselect">
                                <label>{{ __('Agency', 'erp') }}</label>
                                <multi-select
                                    v-model="agency"
                                    :options="agencies"/>
                            </div>
                            <div class="wperp-col-sm-3 wperp-col-xs-12 with-multiselect">
                                <label>{{ __('Tax Category', 'erp') }}</label>
                                    <multi-select
                                    v-model="category"
                                    :options="categories" />
                            </div>
                            <div class="wperp-col-sm-3 wperp-col-xs-12">
                                <label>{{ __('Tax Rate', 'erp') }}</label>
                                <input type="text" v-model="tax_rate"/>
                            </div>

                            <div class="wperp-col-sm-12">
                                <div class="wperp-form-group text-right mt-10 mb-0">
                                    <submit-button :text="__( 'Save', 'erp' )" @click.native.prevent="addTaxRate"></submit-button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</template>

<script>
import HTTP from 'admin/http';
import SubmitButton from 'admin/components/base/SubmitButton.vue';
import MultiSelect from 'admin/components/select/MultiSelect.vue';

export default {
    name: 'TaxRateLineEdit',

    components: {
        MultiSelect,
        SubmitButton
    },

    data() {
        return {
            component_name: '',
            agency: '',
            category: '',
            tax_rate: '',
            agencies: [],
            categories: []
        };
    },

    created() {
        this.fetchData();
    },

    methods: {
        closeModal: function() {
            this.$emit('close');
        },

        addTaxRate() {
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.post(`/taxes/${this.$route.params.id}/line-add`, {
                tax_id: this.$route.params.id,
                component_name: this.component_name,
                agency_id: this.agency.id,
                tax_cat_id: this.category.id,
                tax_rate: this.tax_rate
            }).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', 'Tax Rate Updated!');
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                this.resetData();
                this.isWorking = false;
                this.$emit('line_close');
                this.$root.$emit('refetch_tax_data');
            });
        },

        fetchData() {
            HTTP.get('/tax-agencies').then((response) => {
                this.agencies = response.data;
            }).catch(error => {
                throw error;
            });

            HTTP.get('/tax-cats').then((response) => {
                this.categories = response.data;
            }).catch(error => {
                throw error;
            });
        },

        resetData() {
            Object.assign(this.$data, this.$options.data.call(this));
        }

    }
};
</script>
<style lang="less">
    .wperp-modal-dialog {
        max-width: 1000px !important;
    }
</style>
