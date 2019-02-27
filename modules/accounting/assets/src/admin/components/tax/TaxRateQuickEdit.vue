<template>
    <div id="wperp-tax-rate-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>{{tax_rate.tax_name}}</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>

                <div class="wperp-invoice-table">
                    <div class="wperp-col-sm-12">
                        <label> Tax Number </label>
                        <input type="text" value="0" v-model="tax_number"/>
                    </div>

                    <div class="wperp-form-group wperp-col-sm-12">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" v-model="is_default" class="form-check-input">
                                <span class="form-check-sign"></span>
                                <span class="field-label">Is this tax default?</span>
                            </label>
                        </div>
                    </div>

                    <div class="wperp-col-sm-12">
                        <div class="wperp-form-group text-right mt-10 mb-0">
                            <submit-button text="Update Tax Rate" @click.native.prevent="UpdateTaxRate"></submit-button>
                        </div>
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
        name: 'TaxRateQuickEdit',

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
            tax_update: Boolean
        },

        data() {
            return {
                tax_number: null,
                is_default: null,
                tax_rate: {},
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
                console.log( this.tax_id );
                let taxid = this.tax_id;

                HTTP.get(`/taxes/${taxid}`).then((response) => {
                    this.tax_rate = response.data;
                    this.tax_number = this.tax_rate.tax_number;
                    this.is_default = this.tax_rate.default;
                }).catch((error) => {
                    console.log(error);
                });
            },

            UpdateTaxRate() {
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.put(`/taxes/${this.tax_id}/quick-edit`, {
                    tax_number: this.tax_number,
                    default: this.is_default,
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Tax Rate Updated!' );
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
