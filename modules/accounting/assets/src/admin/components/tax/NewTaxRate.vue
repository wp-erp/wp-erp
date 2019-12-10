<template>
    <div class="wperp-container">
        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Add New Tax Rate', 'erp') }}</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <div class="wperp-panel wperp-panel-default pb-0 new-tax-rate">
            <div class="wperp-panel-body">
                <show-errors :error_msgs="form_errors"></show-errors>

                <form action="" method="post" class="wperp-form">
                    <div class="wperp-row wperp-gutter-20">
                        <div class="wperp-form-group wperp-col-sm-6">
                            <label>{{ __('Tax Zone Name', 'erp') }}<span class="wperp-required-sign">*</span></label>
                            <div class="wperp-custom-select with-multiselect">
                                <multi-select v-model="tax_name" :options="rate_names"/>
                            </div>
                        </div>
                        <div class="wperp-form-group wperp-col-sm-6 compound-checkbox">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" v-model="is_compound" class="form-check-input"
                                        @change="isCompoundTax = !isCompoundTax">
                                    <span class="form-check-sign"></span>
                                    <span class="field-label">{{ __('Is this tax compound', 'erp') }}?</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="table-container mt-20">
                        <table class="wperp-table wperp-form-table new-journal-form">
                            <thead>
                            <tr>
                                <th scope="col" class="column-primary">{{ __('Component Name', 'erp') }}</th>
                                <th scope="col">{{ __('Agency', 'erp') }}</th>
                                <th scope="col">{{ __('Tax Category', 'erp') }}</th>
                                <th scope="col">{{ __('Tax Rate', 'erp') }}</th>
                                <th scope="col" class="col--actions"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr :class="isRowExpanded ? 'is-row-expanded' : ''" :key="key"
                                v-for="(line,key) in componentLines">
                                <td scope="row" class="col--component-name column-primary">
                                    <input type="text" class="wperp-form-field" v-model="line.component_name">
                                    <a href="#" @click.prevent="" class="vis-hide after-select-dropdown">{{ __('component', 'erp') }}</a>

                                    <button type="button" class="wperp-toggle-row"
                                            @click.prevent="isRowExpanded = !isRowExpanded"></button>
                                </td>
                                <td class="col--agency with-multiselect" data-colname="Agency">
                                    <multi-select v-model="line.agency_id" :options="agencies"/>
                                    <a href="#" @click.prevent="showAgencyModal = true" role="button"
                                       class="after-select-dropdown">{{ __('Add Tax Agency', 'erp') }}</a>
                                </td>
                                <td class="col--tax-category with-multiselect" data-colname="Tax Category">
                                    <multi-select v-model="line.tax_category" :options="categories"/>
                                    <a href="#" @click.prevent="showCatModal = true" role="button"
                                       class="after-select-dropdown">{{ __('Add Tax Category', 'erp') }}</a>
                                </td>
                                <td class="col--tax-rate" data-colname="Tax Rate">
                                    <input type="text" class="wperp-form-field text-right" v-model="line.tax_rate">
                                    <a href="#" @click.prevent="" class="vis-hide after-select-dropdown">{{ __('tax rate', 'erp') }}</a>
                                </td>
                                <td class="col--actions delete-row" data-colname="Remove Above Selection">
                                    <a @click.prevent="removeRow(key)" href="#"><i class="flaticon-trash"></i></a>
                                </td>
                            </tr>
                            <tr class="add-new-line" v-if="isCompoundTax">
                                <td colspan="9" class="text-left">
                                    <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger"
                                            type="button"><i
                                        class="flaticon-add-plus-button"></i>{{ __('Add Component', 'erp') }}
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <new-tax-zone v-if="showRateNameModal" @close="showRateNameModal = false"/>
                    <new-tax-category v-if="showCatModal" @close="showCatModal = false"/>
                    <new-tax-agency v-if="showAgencyModal" @close="showAgencyModal = false"/>

                    <div class="wperp-modal-footer pt-0">
                        <!-- buttons -->
                        <div class="buttons-wrapper text-right">
                            <submit-button :text="__( 'Save', 'erp' )" @click.native.prevent="addNewTaxRate"></submit-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import MultiSelect from 'admin/components/select/MultiSelect.vue';
import SubmitButton from 'admin/components/base/SubmitButton.vue';
import NewTaxAgency from 'admin/components/tax/NewTaxAgency.vue';
import NewTaxCategory from 'admin/components/tax/NewTaxCategory.vue';
import NewTaxZone from 'admin/components/tax/NewTaxZone.vue';
import ShowErrors from 'admin/components/base/ShowErrors.vue';

export default {
    name: 'NewTaxRate',

    components: {
        MultiSelect,
        SubmitButton,
        NewTaxAgency,
        NewTaxCategory,
        NewTaxZone,
        ShowErrors
    },

    data() {
        return {
            tax_name: '',
            // tax_number: '',
            tax_category: '',
            is_compound: false,
            // is_default: false,
            isCompoundTax: false,
            isRowExpanded: false,
            componentLines: [{}],
            rate_names: [],
            categories: [{}],
            agencies: [{}],
            showRateNameModal: false,
            showAgencyModal: false,
            showCatModal: false,
            form_errors: []
        };
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
                        name: element.tax_rate_name
                    });
                });
            }).catch((error) => {
                throw error;
            });

            HTTP.get('/tax-agencies').then((response) => {
                this.agencies = [];
                this.agencies = response.data;
            }).catch((error) => {
                throw error;
            });

            HTTP.get('/tax-cats').then((response) => {
                this.categories = [];
                this.categories = response.data;
            }).catch((error) => {
                throw error;
            });
        },

        addNewTaxRate(event) {
            this.validateForm();

            if (this.form_errors.length) {
                window.scrollTo({
                    top: 10,
                    behavior: 'smooth'
                });
                return;
            }

            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.post('/taxes', {
                tax_rate_name: this.tax_name.id,
                is_compound: this.is_compound,
                tax_components: this.formatLineItems()
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', 'Tax Rate Created!');
            }).then(() => {
                this.$router.push({ name: 'TaxRates' });
                this.resetData();
            });

            // event.target.reset();
            this.resetData();
        },

        formatLineItems() {
            var lineItems = [];

            for (let idx = 0; idx < this.componentLines.length; idx++) {
                const item                 = {};
                item.component_name = this.componentLines[idx].component_name;
                item.agency_id = this.componentLines[idx].agency_id.id;
                item.tax_category_id = this.componentLines[idx].tax_category.id;
                item.tax_rate = this.componentLines[idx].tax_rate;

                lineItems.push(item);
            }

            return lineItems;
        },

        validateForm() {
            this.form_errors = [];

            if (!Object.prototype.hasOwnProperty.call(this.tax_name, 'id')) {
                this.form_errors.push('Tax Zone Name is required.');
            }
        },

        updateFinalAmount() {
            let finalAmount = 0;

            this.componentLines.forEach(element => {
                finalAmount += parseFloat(element.tax_rate);
            });

            return parseFloat(finalAmount).toFixed(2);
        },

        closeModal() {
            this.$emit('close');
        },

        addLine() {
            this.componentLines.push({});
        },

        resetData() {
            Object.assign(this.$data, this.$options.data.call(this));

            this.fetchData();
        },

        removeRow(index) {
            this.$delete(this.componentLines, index);
        }
    },

    computed: {
        finalTotalAmount() {
            const amount = this.updateFinalAmount();

            if (Number.isNaN(amount)) {
                return 0;
            }

            return amount;
        }
    }

};
</script>

<style lang="less" scoped>
    .new-tax-rate {
        .compound-checkbox {
            display: flex;
            align-items: center;
            margin: 0;

            .form-check-label {
                width: 200px;
            }
        }

        .vis-hide {
            visibility: hidden;
        }

        .col--actions.delete-row {
            vertical-align: unset;

            a {
                display: block;
                margin-top: 10px;
            }
        }

        .modal-close {
            .flaticon-close {
                font-size: inherit;
            }
        }
    }
</style>
