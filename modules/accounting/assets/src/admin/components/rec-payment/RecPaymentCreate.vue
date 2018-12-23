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
                <form action="" class="wperp-form" method="post">
                    <div class="wperp-row">
                        <div class="wperp-col-sm-3">
                            <div class="wperp-form-group">
                                <select-customers @input="getDueInvoices" v-model="basic_fields.customer"></select-customers>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3">
                            <div class="wperp-form-group">
                                <label>Reference<span class="wperp-required-sign">*</span></label>
                                <input type="text" v-model="basic_fields.trn_ref"></input>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3">
                            <div class="wperp-form-group">
                                <label>Payment Date<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.payment_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3">
                            <label>Deposit to</label>
                            <select v-model="basic_fields.deposit_to" name="deposit-to" class="wperp-form-field">
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
                <table class="wperp-table wperp-form-table">
                    <thead>
                    <tr>
                        <th scope="col" class="col--id column-primary">Invoice ID</th>
                        <th scope="col">Due Date</th>
                        <th scope="col">Total</th>
                        <th scope="col">Due</th>
                        <th scope="col">Amount</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="invoice in invoices">
                        <td scope="row" class="col--id column-primary">{{invoice.id}}</td>
                        <td class="col--due-date" data-colname="Due Date">{{invoice.due_date}}</td>
                        <td class="col--total" data-colname="Total">{{invoice.total}}</td>
                        <td class="col--due" data-colname="Due">$240.00</td>
                        <td class="col--amount" data-colname="Amount">
                            <input type="text" name="amount" class="text-right"/>
                        </td>
                        <td class="delete-row" data-colname="Remove Above Selection">
                            <a href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>

                    <tr class="total-amount-row">
                        <td class="text-right pr-0 hide-sm" colspan="4">Total Amount</td>
                        <td class="text-right" data-colname="Total Amount">
                            <input type="text" name="amount" class="text-right" value="000000" readonly disabled/></td>
                        <td class="text-right"></td>
                    </tr>
                    </tbody>
                    <tr class="wperp-form-group">
                        <td colspan="9" style="text-align: left;">
                            <label for="memo">Particulars</label>
                            <textarea name="memo" id="memo" rows="4" class="wperp-form-field display-flex"
                                      placeholder="Internal Information"></textarea>
                        </td>
                    </tr>
                    <tr class="add-attachment-row">
                        <td colspan="9" style="text-align: left;">
                            <div class="attachment-container">
                                <label class="col--attachement">Attachment</label>
                                <file-upload v-model="attachments" url="/invoices/attachments"/>
                            </div>
                        </td>
                    </tr>
                    <tfoot>
                    <tr>
                        <td colspan="9" style="text-align: right;">
                            <combo-box :options="arrayOfOptions"
                                       :selected="arrayOfOptions[0]"
                            ></combo-box>
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
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import RecPaymentModal from 'admin/components/rec-payment/RecPaymentModal.vue'
    import SelectCustomers from 'admin/components/people/SelectCustomers.vue'
    import ComboBox from 'admin/components/select/ComboBox.vue'


    export default {
        name: 'RecPaymentCreate',

        components: {
            HTTP,
            Datepicker,
            FileUpload,
            RecPaymentModal,
            ComboBox,
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
                finalTotalAmount: 0,
                invoiceModal: false,
                arrayOfOptions: [ { 'name': 'Submit for Approval' },  { 'name': 'Save and Add New' } ]
            }
        },

        created() {
            this.$root.$on('total-updated', amount => {
                this.updateFinalAmount();
            });
        },

        methods: {
            getDueInvoices() {
                let customer_id = this.basic_fields.customer.id;

                HTTP.get(`/invoices/due/${customer_id}`).then((response) => {
                    response.data.forEach(element => {
                        this.invoices.push({
                            id: element.id,
                            voucher_no: element.voucher_no,
                            due_date: element.due_date,
                            total: element.amount
                        });
                    });
                });

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
