<template>
    <div class="wperp-container">
        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Add New Tax Rate</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <div class="wperp-panel wperp-panel-default pb-0">
            <div class="wperp-panel-body">
                <form action="" method="post" class="wperp-form">
                    <div class="wperp-row wperp-gutter-20">
                        <div class="wperp-form-group wperp-col-sm-6">
                            <label>Tax Name</label>
                            <div class="wperp-custom-select with-multiselect">
                                <multi-select v-model="tax_name" :options="rate_names"/>
                            </div>
                        </div>
                        <div class="wperp-col-sm-6">
                            <div class="wperp-form-group">
                                <label>Tax Number</label>
                                <input type="text" v-model="tax_number" class="wperp-form-field" placeholder="Enter Tax Number">
                            </div>
                        </div>
                        <div class="wperp-form-group wperp-col-sm-6">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" v-model="is_default" class="form-check-input">
                                    <span class="form-check-sign"></span>
                                    <span class="field-label">Is this tax default?</span>
                                </label>
                            </div>
                        </div>
                        <div class="wperp-form-group wperp-col-sm-6">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" v-model="is_compound" class="form-check-input"
                                           @change="isCompoundTax = !isCompoundTax">
                                    <span class="form-check-sign"></span>
                                    <span class="field-label">Is this tax compound?</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="table-container mt-20">
                        <table class="wperp-table wperp-form-table new-journal-form">
                            <thead>
                            <tr>
                                <th scope="col" class="column-primary">Component Name</th>
                                <th scope="col">Agency</th>
                                <th scope="col">Tax Category</th>
                                <th scope="col">Tax Rate</th>
                                <th scope="col" class="col--actions"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr :class="isRowExpanded ? 'is-row-expanded' : ''" :key="key"
                                v-for="(line,key) in componentLines">
                                <td scope="row" class="col--component-name column-primary">
                                    <input type="text" class="wperp-form-field" v-model="line.component_name">
                                    <button type="button" class="wperp-toggle-row"
                                            @click.prevent="isRowExpanded = !isRowExpanded"></button>
                                </td>
                                <td class="col--agency with-multiselect" data-colname="Agency">
                                    <multi-select v-model="line.agency_id" :options="agencies"/>
                                    <a href="#" @click.prevent="showAgencyModal = true" role="button"
                                       class="after-select-dropdown">Add Tax Agency</a>
                                </td>
                                <td class="col--tax-category with-multiselect" data-colname="Tax Category">
                                    <multi-select v-model="line.tax_category" :options="categories"/>
                                    <a href="#" @click.prevent="showCatModal = true" role="button"
                                       class="after-select-dropdown">Add Tax Category</a>
                                </td>
                                <td class="col--tax-rate" data-colname="Tax Rate">
                                    <input type="text" class="wperp-form-field text-right" v-model="line.tax_rate">
                                </td>
                                <td class="col--actions delete-row" data-colname="Remove Above Selection">
                                    <a @click.prevent="removeRow(key)" href="#"><i class="flaticon-trash"></i></a>
                                </td>
                            </tr>
                            <tr class="add-new-line" v-if="isCompoundTax">
                                <td colspan="9" class="text-left">
                                    <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger"
                                            type="button"><i
                                        class="flaticon-add-plus-button"></i>Add Component
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <new-tax-rate-name v-if="showRateNameModal" @close="showRateNameModal = false"/>
                    <new-tax-category v-if="showCatModal" @close="showCatModal = false"/>
                    <new-tax-agency v-if="showAgencyModal" @close="showAgencyModal = false"/>

                    <div class="wperp-modal-footer pt-0">
                        <!-- buttons -->
                        <div class="buttons-wrapper text-right">
                            <submit-button text="Add New Tax Rate" @click.native.prevent="addNewTaxRate"></submit-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import NewTaxAgency from 'admin/components/tax/NewTaxAgency.vue'
    import NewTaxCategory from 'admin/components/tax/NewTaxCategory.vue'
    import NewTaxRateName from 'admin/components/tax/NewTaxRateName.vue'

    export default {
        name: "NewTaxRate",

        components: {
            HTTP,
            MultiSelect,
            SubmitButton,
            NewTaxAgency,
            NewTaxCategory,
            NewTaxRateName
        },

        data() {
            return {
                tax_name: '',
                tax_number: '',
                tax_category: '',
                is_compound: false,
                is_default: false,
                isCompoundTax: false,
                isRowExpanded: false,
                componentLines: [{}],
                rate_names: [{}],
                categories: [{}],
                agencies: [{}],
                showRateNameModal: false,
                showAgencyModal: false,
                showCatModal: false
            }
        },

        created() {
            this.fetchData();

            this.$root.$on('refetch_tax_data', () => {
                this.fetchData();
            });

            this.$on('remove-row', index => {
                this.$delete(this.componentLines, index);
                this.updateFinalAmount();
            });
        },

        methods: {
            fetchData() {
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
                }).then(() => {
                    //ready
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

            addNewTaxRate() {
                HTTP.post('/taxes', {
                    tax_rate_name: this.tax_name.id,
                    tax_number: this.tax_number,
                    default: this.is_default,
                    is_compound: this.is_compound,
                    tax_components: this.formatLineItems()
                }).then(res => {
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Tax Rate Created!',
                        showConfirmButton: false,
                        timer: 1000
                    });
                }).then(() => {
                    this.resetData();
                });
            },

            formatLineItems() {
                var lineItems = [];

                for(let idx = 0; idx < this.componentLines.length; idx++) {
                    let item = {};
                    item.component_name = this.componentLines[idx].component_name;
                    item.agency_id = this.componentLines[idx].agency_id.id;
                    item.tax_category_id = this.componentLines[idx].tax_category.id;
                    item.tax_rate  = this.componentLines[idx].tax_rate;

                    lineItems.push( item );
                }

                return lineItems;
            },

            updateFinalAmount() {
                let finalAmount = 0;

                this.componentLines.forEach(element => {
                    finalAmount += parseFloat(element.tax_rate);
                });

                return parseFloat(finalAmount).toFixed(2);
            },

            closeModal: function () {
                this.$emit('close');
            },

            addLine() {
                this.componentLines.push({});
            },

            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
            },

            removeRow(index) {
                this.$delete(this.componentLines, index);
            },
        },

        computed: {
            finalTotalAmount() {
                let amount = this.updateFinalAmount();

                if ( Number.isNaN(amount) ) {
                    return 0;
                }

                return amount;
            },
        },

    }
</script>

<style lang="less" scoped>
</style>
