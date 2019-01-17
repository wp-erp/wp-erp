<template>
    <div id="wperp-new-tax-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>Add New Tax</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>
                <form action="" method="post" class="modal-form edit-customer-modal">
                    <div class="wperp-modal-body">
                        <div class="wperp-row wperp-gutter-20">
                            <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                <label>Tax Name</label>
                                <div class="wperp-custom-select">
                                    <input type="text" placeholder="Enter Tax Name" v-model="tax_name" class="wperp-form-field">
                                </div>
                            </div>
                            <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                <label>Tax Number</label>
                                <input type="text" placeholder="Enter Tax Number" v-model="tax_number" class="wperp-form-field">
                            </div>
                            <div class="wperp-col-sm-6">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" v-model="is_compound" class="form-check-input" @change="isTaxComponent = !isTaxComponent">
                                        <span class="form-check-sign"></span>
                                        <span class="field-label">Is this tax compound?</span>
                                    </label>
                                </div>
                            </div>
                            <div class="wperp-col-sm-6">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" v-model="is_default" class="form-check-input">
                                        <span class="form-check-sign"></span>
                                        <span class="field-label">Is this tax default?</span>
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
                                <tr :class="isRowExpanded ? 'is-row-expanded' : ''" :key="key" v-for="(line,key) in componentLines">
                                    <td scope="row" class="col--component-name column-primary">
                                        <input type="text" class="wperp-form-field" v-model="line.component_name">
                                        <button type="button" class="wperp-toggle-row"
                                                @click.prevent="isRowExpanded = !isRowExpanded"></button>
                                    </td>
                                    <td class="col--agency" data-colname="Agency">
                                        <multi-select v-model="line.agency_id" :options="agencies" />
                                    </td>
                                    <td class="col--tax-category" data-colname="Tax Category">
                                        <multi-select v-model="line.tax_category" :options="categories" />
                                    </td>
                                    <td class="col--tax-rate" data-colname="Tax Rate">
                                        <input type="text" class="wperp-form-field text-right" v-model="line.tax_rate">
                                    </td>
                                    <td class="col--actions delete-row" data-colname="Remove Above Selection">
                                        <a @click.prevent="removeRow(key)" href="#"><i class="flaticon-trash"></i></a>
                                    </td>
                                </tr>
                                <tr class="add-new-line" v-if="isTaxComponent">
                                    <td colspan="9" class="text-left">
                                        <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger" type="button"><i
                                            class="flaticon-add-plus-button"></i>Add Component
                                        </button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <div class="wperp-modal-footer pt-0">
                        <!-- buttons -->
                        <div class="buttons-wrapper text-right">
                            <submit-button text="Add New Tax Rate" @click.native="addNewTaxRate" :working="isWorking"></submit-button>
                            <button class="wperp-btn btn--default modal-close" type="reset" @click="closeModal">Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue';
    import SubmitButton from 'admin/components/base/SubmitButton.vue'

    export default {
        name: "NewTaxRate",

        components: {
            HTTP,
            MultiSelect,
            SubmitButton
        },

        data() {
            return {
                tax_name: '',
                tax_number: '',
                is_default: 0,
                is_compound: 0,
                isTaxComponent: false,
                isRowExpanded: false,
                componentLines: [{}],
                categories: [{}],
                agencies:[{}]
            }
        },

        created() {
            this.getCategories();
            this.getAgencies();

            this.$on('remove-row', index => {
                this.$delete(this.componentLines, index);
            });
        },


        methods: {
            getAgencies() {
                HTTP.get('/tax-agencies').then((response) => {
                    response.data.forEach(element => {
                        this.agencies.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                });
            },

            getCategories() {
                HTTP.get('/tax-cats').then((response) => {
                    response.data.forEach(element => {
                        this.categories.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                });
            },

            addNewTaxRate() {
                HTTP.post('/taxes', {
                    tax_rate_name : this.tax_name,
                    tax_number: this.tax_number,
                    is_compound: this.is_compound,
                    is_default: this.is_default,
                    tax_components: this.componentLines
                }).then(res => {
                    console.log(res.data);
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Tax Agency Created!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }).then(() => {
                    this.resetData();
                    this.isWorking = false;
                });
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
    }
</script>

<style lang="less" scoped>
    .wperp-modal .wperp-modal-dialog {
        max-width: 900px;
    }
</style>
