<template>
    <div class="wperp-row">
        <div class="wperp-col-sm-3 wperp-col-xs-12">
            <label>{{ __('Component', 'erp') }}</label>
            <template v-if="is_update"><input type="text" class="wperp-form-field" v-model="component_line.component_name"/></template>
            <template v-else>{{component_line.component_name}}</template>
        </div>
        <div class="wperp-col-sm-3 wperp-col-xs-12 with-multiselect">
            <label>{{ __('Agency', 'erp') }}</label>
            <template v-if="is_update">
            <multi-select
                v-model="component_line.agency"
                :options="agencies"/>
            </template>
            <template v-else>{{component_line.agency_name}}</template>
        </div>
        <div class="wperp-col-sm-3 wperp-col-xs-12 with-multiselect">
            <label>{{ __('Tax Category', 'erp') }}</label>
            <template v-if="is_update">
                <multi-select
                v-model="component_line.category"
                :options="categories" />
            </template>
            <template v-else>{{component_line.tax_cat_name}}</template>
        </div>
        <div class="wperp-col-sm-3 wperp-col-xs-12">
            <label>{{ __('Tax Rate', 'erp') }}</label>
            <template v-if="is_update"><input class="wperp-form-field" type="text" v-model="component_line.tax_rate"/></template>
            <template v-else>{{component_line.tax_rate}}</template>
        </div>

        <div class="wperp-col-sm-12">
            <div class="wperp-form-group text-right mt-10 mb-0">
                <submit-button v-if="is_update" :text="__( 'Update', 'erp' )" @click.native.prevent="UpdateTaxRate"></submit-button>
            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import MultiSelect from 'admin/components/select/MultiSelect.vue';
import SubmitButton from 'admin/components/base/SubmitButton.vue';

export default {
    name: 'TaxRateRow',

    components: {
        MultiSelect,
        SubmitButton
    },

    props: {
        index: {
            type: [Number, String]
        },
        component_line: {
            type: Object
        },
        agencies: {
            type: Array
        },
        categories: {
            type: Array
        }
    },

    created() {
        this.setAgency();
        this.setCategory();
    },

    data() {
        return {
            is_update: true
        };
    },

    methods: {
        setAgency() {
            const agency_id   = parseInt(this.component_line.agency_id);
            const agency_name = this.component_line.agency_name;

            this.component_line.agency = { id: agency_id, name: agency_name };
        },

        setCategory() {
            const tax_cat_id   = parseInt(this.component_line.tax_cat_id);
            const tax_cat_name = this.component_line.tax_cat_name;

            this.component_line.category = { id: tax_cat_id, name: tax_cat_name };
        },

        UpdateTaxRate() {
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.put(`/taxes/${this.component_line.tax_id}/line-edit`, {
                db_id: this.component_line.db_id,
                tax_id: this.component_line.tax_id,
                row_id: parseInt(this.index),
                component_name: this.component_line.component_name,
                agency_id: this.component_line.agency.id,
                tax_cat_id: this.component_line.category.id,
                tax_rate: this.component_line.tax_rate
            }).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', __('Tax Rate Updated!', 'erp'));
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                this.resetData();
                this.isWorking = false;
                this.$emit('line_close');
                this.$root.$emit('refetch_tax_data');
                this.$router.push({ name: 'TaxRates' });
            });
        },

        resetData() {
            Object.assign(this.$data, this.$options.data.call(this));
        }

    }

};
</script>

<style scoped>
   .wperp-row {
       padding: 10px 40px !important;
   }
</style>
