<template>
    <div class="wperp-container invoice-create">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">New Invoice</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <form action="" method="post" @submit.prevent="submitInvoiceForm">

        <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
            <div class="wperp-panel-body">
                <!-- <form action="#" class="wperp-form" method="post"> -->
                    <div class="wperp-row">
                        <div class="wperp-col-sm-4">
                            <select-customers :reset="reset" v-model="basic_fields.customer"></select-customers>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label for="trans_date">Transaction Date<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.trans_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label for="due_date">Due Date<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.due_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-xs-12">
                            <label for="billing_address">Billing Address</label>
                            <textarea v-model.trim="basic_fields.billing_address" rows="4" class="wperp-form-field" placeholder="Type here"></textarea>
                        </div>
                    </div>
                <!-- </form> -->

            </div>
        </div>

            <div class="wperp-table-responsive">
                <!-- Start .wperp-crm-table -->
                <div class="table-container">
                    <table class="wperp-table wperp-form-table">
                        <thead>
                            <tr>
                                <td scope="col" class="col--check">Product/Service</td>
                                <th scope="col" class="column-primary">Qty</th>
                                <th scope="col">Unit Price</th>
                                <th scope="col">Discount</th>
                                <th scope="col">Tax(%)</th>
                                <th scope="col">Tax Amount</th>
                                <th scope="col">Amount</th>
                                <th scope="col" class="col--actions"></th>
                            </tr>
                        </thead>
                        <tbody id="test">
                            <invoice-trn-row
                                :line="line"
                                :products="products"
                                :key="index"
                                v-for="(line, index) in transactionLines"
                            ></invoice-trn-row>

                            <tr class="total-amount-row">
                                <td colspan="6" class="text-right">
                                    <span>Total Amount = </span>
                                </td>
                                <td><input type="text" v-model="finalTotalAmount" readonly></td>
                                <td></td>
                            </tr>
                            <tr class="add-new-line">
                                <td colspan="9" style="text-align: left;">
                                    <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add Line</button>
                                </td>
                            </tr>
                            <tr class="add-attachment-row" >
                                <td colspan="9" style="text-align: left;">
                                    <div class="attachment-container">
                                        <label class="col--attachement">Attachment</label>
                                        <file-upload v-model="attachments" url="/invoices/attachments"/>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9" style="text-align: right;">
                                    <combo-button :options="buttons" />
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </form>

        <!-- End .wperp-crm-table -->
    </div>
</template>

<script>
import HTTP from 'admin/http'
import Datepicker from 'admin/components/base/Datepicker.vue'
import FileUpload from 'admin/components/base/FileUpload.vue'
import ComboButton from 'admin/components/select/ComboButton.vue';
import SubmitButton from 'admin/components/base/SubmitButton.vue'
import InvoiceTrnRow from 'admin/components/invoice/InvoiceTrnRow.vue'
import SelectCustomers from 'admin/components/people/SelectCustomers.vue';

import PrintPreview from 'admin/components/base/PrintPreview.vue';

export default {
    name: 'InvoiceCreate',

    components: {
        HTTP,
        Datepicker,
        FileUpload,
        ComboButton,
        PrintPreview,
        SubmitButton,
        InvoiceTrnRow,
        SelectCustomers
    },

    data() {
        return {
            basic_fields: {
                customer: '',
                trans_date: erp_acct_var.current_date,
                due_date: erp_acct_var.current_date,
                billing_address: ''
            },

            buttons: [
                {id: 'save', text: 'Save'},
                {id: 'save_create', text: 'Save & Create'},
                {id: 'save_draft', text: 'Create Draft'},
            ],

            products: [],
            attachments: [],
            transactionLines: [{}],
            finalTotalAmount: 0,

            isWorking: false,

            reset: false,

            showPrintPreview: false,

            actionType: null,
        }
    },

    watch: {
        'basic_fields.customer'() {
            this.showPrintPreview = true;
            this.reset = false;

            this.getCustomerAddress();
        }
    },

    created() {
        this.getProducts();

        this.$root.$on('remove-row', index => {
            this.$delete(this.transactionLines, index);
            this.updateFinalAmount();
        });

        this.$root.$on('total-updated', amount => {
            this.updateFinalAmount();
        });

        this.$root.$on('combo-btn-select', button => {
            this.actionType = button.id;
        });
    },

    methods: {
        getProducts() {
            HTTP.get('/products').then((response) => {
                response.data.forEach(element => {
                    this.products.push({
                        id: element.id,
                        name: element.name,
                        sale_price: element.sale_price,
                        type_name: element.type_name
                    });
                });
            });
        },

        getCustomerAddress() {
            let customer_id = this.basic_fields.customer.id;

            if ( ! customer_id ) {
                this.basic_fields.billing_address = '';
                return;
            }

            HTTP.get(`/customers/${customer_id}`).then((response) => {
                // add more info
                this.basic_fields.billing_address = `
                    Street: ${response.data.billing.street_1} ${response.data.billing.street_2},
                    City: ${response.data.billing.city},
                `;
            });
        },

        addLine() {
            this.transactionLines.push({});
        },

        updateFinalAmount() {
            let finalAmount = 0;

            this.transactionLines.forEach(element => {
                finalAmount += parseFloat(element.totalAmount);
            });

            this.finalTotalAmount = finalAmount.toFixed(2);

        },

        formatLineItems() {
            var lineItems = [];

            this.transactionLines.forEach(line => {
                lineItems.push({
                    product_id: line.selectedProduct.id,
                    product_type: line.selectedProduct.type,
                    qty: line.qty,
                    unit_price: line.unitPrice,
                    tax: line.taxAmount,
                    discount: line.discount,
                    item_total: line.totalAmount,
                    tax_percent: 0
                });
            });

            return lineItems;
        },

        submitInvoiceForm() {
            this.isWorking = true;

            HTTP.post('/invoices', {
                customer_id: this.basic_fields.customer.id,
                date: this.basic_fields.trans_date,
                due_date: this.basic_fields.due_date,
                billing_address: this.basic_fields.billing_address,
                line_items: this.formatLineItems(),
                attachments: this.attachments,
                type: 'invoice',
                status: '1',
                trn_by: 'cash',
                estimate: 0,
            }).then(res => {
                this.$swal({
                    position: 'center',
                    type: 'success',
                    title: 'Invoice Created!',
                    showConfirmButton: false,
                    timer: 1500
                });
            }).then(() => {
                this.isWorking = false;
                this.reset = true;

                if ( 'save' == this.actionType ) {
                    this.$router.push({ name: 'Sales' });
                } else if ( 'save_create' == this.actionType ) {
                    this.resetFields();
                }

            });
        },

        resetFields() {
            this.basic_fields.customer = '';
            this.basic_fields.trans_date = '';
            this.basic_fields.due_date = '';
            this.basic_fields.billing_address = '';
            this.attachments = [];
            this.transactionLines = [{}];
            this.finalTotalAmount = 0;
            this.isWorking = false;
            this.reset = false;
            this.actionType = null;
        }
    }

}
</script>

