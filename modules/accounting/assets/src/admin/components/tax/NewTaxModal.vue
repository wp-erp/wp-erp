<template>
    <div id="wperp-new-tax-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>Add New Tax</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>
                <!-- end modal body title -->
                <form action="" method="post" class="modal-form edit-customer-modal">
                    <div class="wperp-modal-body">
                        <!-- add new product form -->
                        <div class="wperp-row wperp-gutter-20">
                            <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                <label for="tax_name">Tax Name</label>
                                <div class="wperp-custom-select">
                                    <select name="tax_name" id="tax_name" class="wperp-form-field">
                                        <option value="0">Select</option> 
                                        <option value="1">Option1</option> 
                                        <option value="2">Option2</option>
                                    </select> 
                                    <i class="flaticon-arrow-down-sign-to-navigate"></i>
                                </div>
                            </div>
                            <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                <label for="tax_number">Tax Number</label>
                                <input type="text" name="tax_number" id="tax_number" placeholder="Enter Tax Number" class="wperp-form-field">
                            </div>
                            <div class="wperp-col-xs-12">
                                <label for="is_tax_component">Tax Components</label> 
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" id="is_tax_compound" name="is_taxcompound" class="form-check-input" @change="isTaxComponent = !isTaxComponent"> 
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
                                    <tr :class="isRowExanded ? 'is-row-expanded' : ''">
                                        <td scope="row" class="col--component-name column-primary">
                                            <input type="text" class="wperp-form-field" placeholder="Component Name">
                                            <button type="button" class="wperp-toggle-row" @click.prevent="isRowExanded = !isRowExanded"><span class="screen-reader-text">Show more details</span></button>
                                        </td>
                                        <td class="col--agency" data-colname="Agency">
                                            <input type="text" class="wperp-form-field" placeholder="Agency">
                                        </td>
                                        <td class="col--tax-category" data-colname="Tax Category">
                                            <div class="wperp-custom-select">
                                                <select name="tax_category" class="wperp-form-field">
                                                    <option value="0">Select</option>
                                                    <option value="1">Option1</option>
                                                    <option value="2">Option2</option>
                                                </select>
                                                <i class="flaticon-arrow-down-sign-to-navigate"></i>
                                            </div>
                                        </td>
                                        <td class="col--tax-rate" data-colname="Tax Rate">
                                            <input type="text" class="wperp-form-field text-right" value="$35.00">
                                        </td>
                                        <td class="col--actions delete-row" data-colname="Remove Above Selection">
                                            <a href="#"><i class="flaticon-trash"></i></a>
                                        </td>
                                    </tr>
                                    <tr class="add-new-line" v-if="isTaxComponent">
                                        <td colspan="9" class="text-left">
                                            <button class="wperp-btn btn--primary add-line-trigger" type="button"><i class="flaticon-add-plus-button"></i>Add Component</button>
                                        </td>
                                    </tr>
                                </tbody>
                                <!-- <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-right">
                                            <button class="wperp-btn btn--primary wperp-dropdown-trigger">Add Entry</button>
                                        </td>
                                    </tr>
                                </tfoot> -->
                            </table>
                        </div>

                    </div>

                    <div class="wperp-modal-footer pt-0">
                        <!-- buttons -->
                        <div class="buttons-wrapper text-right">
                            <button class="wperp-btn btn--primary" type="submit">Add New</button>
                            <button class="wperp-btn btn--default modal-close" type="reset" @click="closeModal">Cancel</button>
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
    export default {
        name: "NewTaxModal",
        components: {
            MultiSelect,
        },
        props: {
            
        },
        data() {
            return {
               isTaxComponent: false,
               isRowExanded: false
            }
        },
        methods: {
            closeModal: function(){
                this.$emit('close');
            }
        },
        created() {
           
        }
    }
</script>

<style lang="less" scoped>
    .wperp-modal .wperp-modal-dialog {
        max-width: 900px;
    }
</style>
