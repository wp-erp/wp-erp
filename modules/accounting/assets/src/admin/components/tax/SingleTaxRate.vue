<template>
    <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
        <div class="wperp-panel-body">

            <div class="wperp-modal-header with-multiselect">
                <h3 v-if="tax_update"><multi-select type="text" v-model="tax_rate.tax_name" :options="rate_names"/></h3>
                <h3 v-else>{{tax_rate.tax_name}}</h3>
            </div>

            <div class="wperp-invoice-table">
                <div class="wperp-panel-body">
                    <tax-rate-row
                        :key="index"
                        :tax_component="component"
                        v-for="(component, index) in tax_rate.tax_components"
                        :agencies="agencies"
                        :categories="categories"
                        :is_update="tax_update"
                    />
                </div>
                <div class="wperp-col-sm-12">
                    <div class="wperp-form-group text-right mt-10 mb-0">
                        <submit-button v-if="tax_update" text="Update Tax Rate" @click.native.prevent="UpdateTaxRate"></submit-button>
                    </div>
                </div>

            </div>
        </div>
    </div>

</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import TaxRateRow from 'admin/components/tax/TaxRateRow.vue'

    export default {
        name: 'SingleTaxRate',

        components: {
            HTTP,
            MultiSelect,
            SubmitButton,
            TaxRateRow
        },

        props: {
            tax_id: {
                type: [ Number, String ]
            },
        },

        data() {
            return {
                tax_rate: {},
                agency: '',
                category: '',
                isWorking: false,
                rate_names: [],
                agencies: [],
                categories: [],
                tax_update: true
            };
        },

        created() {
            this.fetchData();
        },

        methods: {
            closeModal: function(){
                this.$emit('close');
            },

            fetchData() {
                let taxid = this.$route.params.id;

                HTTP.get(`/taxes/${taxid}`).then((response) => {
                    this.tax_rate = response.data;
                }).catch((error) => {
                    console.log(error);
                });

                HTTP.get('/tax-rate-names').then((response) => {
                    this.rate_names = [];
                    response.data.forEach(element => {
                        this.rate_names.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                }).catch((error) => {
                    console.log(error);
                });

                HTTP.get('/tax-agencies').then((response) => {
                    this.agencies = [];
                    response.data.forEach(element => {
                        this.agencies.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                }).catch((error) => {
                    console.log(error);
                });

                HTTP.get('/tax-cats').then((response) => {
                    this.categories = [];
                    response.data.forEach(element => {
                        this.categories.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                }).catch((error) => {
                    console.log(error);
                });
            },

            UpdateTaxRate() {
                let tax_id = this.$route.params.id;

                HTTP.put(`/taxes/${tax_id}`, {
                    tax_rate_name: tax_id,
                    tax_number: this.tax_rate.tax_number,
                    default: this.tax_rate.is_default,
                    tax_components: this.tax_rate.tax_components
                }).then(res => {
                    console.log(res.data);
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Tax Rate Updated!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }).then(() => {
                    this.resetData();
                    this.isWorking = false;
                    this.$emit('close');
                    this.$root.$emit('refetch_tax_data');
                });
            },

            formatLineItems(componentLines) {
                console.log( componentLines );
                var lineItems = [];

                for(let idx = 0; idx < componentLines.length; idx++) {
                    let item = {};
                    item.component_name = componentLines[idx].component_name;
                    item.agency_id = componentLines[idx].agency.id;
                    item.tax_category_id = componentLines[idx].category.id;
                    item.tax_rate  = componentLines[idx].tax_rate;

                    lineItems.push( item );
                }

                return lineItems;
            },

            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
            },

        },
    }
</script>
<style lang="less">
</style>
