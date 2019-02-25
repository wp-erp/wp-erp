<template>
    <div class="wperp-row">
        <div class="wperp-col-sm-3 wperp-col-xs-12">
            <label>Component</label>
            <template v-if="is_update"><input type="text" v-model="tax_component.component_name"/></template>
            <template v-else>{{tax_component.component_name}}</template>
        </div>
        <div class="wperp-col-sm-3 wperp-col-xs-12 with-multiselect">
            <label>Agency</label>
            <template v-if="is_update">
            <multi-select
                v-model="tax_component.agency"
                :options="agencies"/>
            </template>
            <template v-else>{{tax_component.agency_name}}</template>
        </div>
        <div class="wperp-col-sm-3 wperp-col-xs-12 with-multiselect">
            <label>Tax Category</label>
            <template v-if="is_update">
                <multi-select
                v-model="tax_component.category"
                :options="categories"/>
            </template>
            <template v-else>{{tax_component.tax_cat_name}}</template>
        </div>
        <div class="wperp-col-sm-3 wperp-col-xs-12">
            <label>Tax Rate</label>
            <template v-if="is_update"><input type="text" v-model="tax_component.tax_rate"/></template>
            <template v-else>{{tax_component.tax_rate}}</template>
        </div>
    </div>
</template>

<script>
    import MultiSelect from 'admin/components/select/MultiSelect.vue'

    export default {
        name: 'TaxRateRow',

        components: {
            MultiSelect
        },

        props: {
            tax_component: {
                type: Object
            },
            agencies: {
                type: Array
            },
            categories: {
                type: Array
            },
            is_update: {
                type: Boolean
            }
        },

        watch: {
            'tax_component.agency'(newVal) {
                this.tax_component.agency = newVal;
            },
        },

        created() {
        },

    };
</script>

<style scoped>
   .wperp-row {
       padding: 10px !important;
   }
</style>
