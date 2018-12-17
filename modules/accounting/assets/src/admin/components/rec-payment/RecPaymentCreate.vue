<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Receive Payment</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
            <div class="wperp-panel-body">
                <form action="#" class="wperp-form" method="post">
                    <div class="wperp-row">
                        <div class="wperp-col-sm-3">
                            <div class="wperp-form-group">
                                <select-customers v-model="basic_fields.customer"></select-customers>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3">
                            <div class="wperp-form-group">
                                <label for="reference">Reference<span class="wperp-required-sign">*</span></label>
                                <input type="text" v-model="basic_fields.trn_ref"></input>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3">
                            <div class="wperp-form-group">
                                <label for="payment_date">Payment Date<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.payment_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3">
                            <label for="deposit_to">Deposit to</label>
                            <select  v-model="basic_fields.deposit_to" name="deposit-to" class="wperp-form-field">
                                <option value="0">-Select-</option>
                                <option value="1">Cash</option>
                                <option value="2">Bank</option>
                            </select>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <div class="wperp-table-responsive">
            <!-- Start .wperp-crm-table -->
            <div class="table-container">
                <table class="wperp-table wperp-form-table wperp-invoice-table">
                    <thead>
                    <tr>
                        <td scope="col" class="col--check">Invoice ID</td>
                        <th scope="col" class="column-primary">Total</th>
                        <th scope="col">Due</th>
                        <th scope="col">Amount</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row" class="col--check">#001</th>
                        <td class="col--qty column-primary">$500.00</td>
                        <td class="col--uni_price" data-colname="Unit Price">$240.00</td>
                        <td class="col--amount" data-colname="Total">
                            <input type="text" name="amount" class="text-right" value="000000" />
                        </td>
                        <td class="delete-row">
                            <a href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="total-amount-row">
                        <td class="text-right pr-0" colspan="3">Total Amount</td>
                        <td class="text-right"><input type="text" name="amount" id="amount" class="text-right" value="000000" readonly disabled /></td>
                        <td class="text-right"></td>
                    </tr>

                    <tr class="add-new-line">
                        <td style="text-align: left;">
                            <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add Line</button>
                        </td>
                    </tr>
                    <tr class="wperp-form-group">
                        <td colspan="9" style="text-align: left;">
                            <label for="memo">Particulars</label>
                            <textarea name="memo" id="memo" rows="4" class="wperp-form-field display-flex" placeholder="Internal Information"></textarea>
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
                            <submit-button text="Submit for approval" @click.native="SubmitForApproval" :working="isWorking"></submit-button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- End .wperp-crm-table -->
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker from 'admin/components/base/Datepicker.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import RecPaymentTrnRow from 'admin/components/rec-payment/RecPaymentTrnRow.vue'
    import SelectCustomers from 'admin/components/people/SelectCustomers.vue'


    export default {
        name: 'RecPaymentCreate',

        components: {
            HTTP,
            Datepicker,
            FileUpload,
            SubmitButton,
            RecPaymentTrnRow,
            SelectCustomers
        },

        data() {
            return {
                basic_fields: {
                    customer: '',
                    trn_ref: '',
                    payment_date: '',
                    deposit_to: ''
                },

                invoices: [],
                attachments: [],
                transactionLines: [{}],
                finalTotalAmount: 0,
                invoiceModal: false,
                isWorking: false,
            }
        },

        watch: {
            'basic_fields.customer'() {
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
        },

        methods: {
            getProducts() {
                HTTP.get('/products').then((response) => {
                    response.data.forEach(element => {
                        this.products.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                });
            },

            getCustomerAddress() {
                let customer_id = this.basic_fields.customer.id;

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
                    finalAmount += element.totalAmount;
                });

                this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
            }
        }

    }
</script>

<style lang="less">

</style>
