<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Pay Bill</h2>

                    <!-- Print Dialogue -->

                    <a href="#" class="wperp-btn btn--primary" @click.prevent="showPaymentModal">
                        <span>Print</span>
                    </a>
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
                                <select-people @input="getDueBills" v-model="basic_fields.people"></select-people>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3">
                            <div class="wperp-form-group">
                                <label>Reference<span class="wperp-required-sign">*</span></label>
                                <input type="text" v-model="basic_fields.trn_ref"/>
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
                        <div class="wperp-col-xs-12">
                            <label>Billing Address</label>
                            <textarea v-model.trim="basic_fields.billing_address" rows="3" class="wperp-form-field" placeholder="Type here"></textarea>
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
                        <th scope="col" class="col--id column-primary">Bill ID</th>
                        <th scope="col">Due Date</th>
                        <th scope="col">Total</th>
                        <th scope="col">Due</th>
                        <th scope="col">Amount</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="key" v-for="(pay_bill,key) in pay_bills">
                        <td scope="row" class="col--id column-primary">{{key+1}}</td>
                        <td class="col--due-date" data-colname="Due Date">{{pay_bill.due_date}}</td>
                        <td class="col--total" data-colname="Total">{{pay_bill.total}}</td>
                        <td class="col--due" data-colname="Due">{{pay_bill.total}}</td>
                        <td class="col--amount" data-colname="Amount">
                            <input type="text" name="amount" v-model="totalAmounts[key]" @keyup="updateFinalAmount" class="text-right"/>
                        </td>
                        <td class="delete-row" data-colname="Remove Above Selection">
                            <a href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>

                    <tr class="total-amount-row">
                        <td class="text-right pr-0 hide-sm" colspan="4">Total Amount</td>
                        <td class="text-right" data-colname="Total Amount">
                            <input type="text" class="text-right" name="finalamount" v-model="finalTotalAmount" readonly disabled/></td>
                        <td class="text-right"></td>
                    </tr>
                    </tbody>
                    <tr class="wperp-form-group">
                        <td colspan="9" style="text-align: left;">
                            <label>Particulars</label>
                            <textarea v-model="particulars" rows="4" class="wperp-form-field display-flex" placeholder="Internal Information"></textarea>
                        </td>
                    </tr>
                    <tr class="add-attachment-row">
                        <td colspan="9" style="text-align: left;">
                            <div class="attachment-container">
                                <label class="col--attachement">Attachment</label>
                                <file-upload v-model="attachments" url="/bills/attachments"/>
                            </div>
                        </td>
                    </tr>
                    <tfoot>
                    <tr>
                        <td colspan="9" style="text-align: right;">
                            <submit-button text="Pay Bill" @click.native="SubmitForPayment" :working="isWorking"></submit-button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <template v-if="pay_bill_modal">
            <pay-bill-modal :basic_fields="basic_fields" :pay_bills="pay_bills" :attachments="attachments" :finalTotalAmount="finalTotalAmount" :assets_url="acct_assets" />
        </template>

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker from 'admin/components/base/Datepicker.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import PayBillModal from 'admin/components/pay-bill/PayBillModal.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import SelectPeople from "admin/components/people/SelectPeople.vue";


    export default {
        name: 'PayBillCreate',

        components: {
            SelectPeople,
            HTTP,
            Datepicker,
            FileUpload,
            PayBillModal,
            SubmitButton,
        },

        data() {
            return {
                basic_fields: {
                    people: '',
                    trn_ref: '',
                    payment_date: '',
                    deposit_to: '',
                    billing_address: ''
                },

                pay_bills: [],
                attachments: [],
                totalAmounts:[],
                finalTotalAmount: 0,
                pay_bill_modal: false,
                particulars: '',
                isWorking: false,
                acct_assets: erp_acct_var.acct_assets
            }
        },

        created() {
            this.$root.$on('pay-bill-modal-close', () => {
                this.pay_bill_modal = false;
            });
        },

        methods: {
            getDueBills() {
                let peopleId = this.basic_fields.people.id,
                    idx = 0,
                    finalAmount = 0;

                // for modal test. remove later
                if ( undefined === peopleId ) {
                    peopleId = 1;
                }

                HTTP.get(`/bills/due/${peopleId}`).then((response) => {
                    response.data.forEach(element => {
                        this.pay_bills.push({
                            id: element.id,
                            voucher_no: element.voucher_no,
                            due_date: element.due_date,
                            total: parseFloat(element.total)
                        });
                    });
                }).then(() => {
                    this.pay_bills.forEach(element => {
                        this.totalAmounts[idx++] = parseFloat(element.total);
                        finalAmount += parseFloat(element.total);
                    });

                    this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
                });
            },

            getCustomerAddress() {
                let people_id = this.basic_fields.people.id;

                HTTP.get(`/customers/${people_id}`).then((response) => {
                    // add more info
                    this.basic_fields.billing_address =
                        `Street: ${response.data.billing.street_1} ${response.data.billing.street_2},
                        City: ${response.data.billing.city}, Country: ${response.data.billing.country}`;
                });
            },

            updateFinalAmount() {
                let finalAmount = 0;

                this.totalAmounts.forEach(element => {
                    finalAmount += parseFloat(element);
                });

                this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
            },

            SubmitForPayment() {
                HTTP.post('/pay-bills', {
                    vendor_id: this.basic_fields.people.id,
                    ref: this.basic_fields.trn_ref,
                    trn_date: this.basic_fields.trans_date,
                    due_date: this.basic_fields.due_date,
                    bill_details: this.pay_bills,
                    attachments: this.attachments,
                    type: 'pay_bill',
                    status: 'paid',
                    particulars: this.particulars,
                    trn_by: this.basic_fields.deposit_to,
                }).then(res => {
                    console.log(res.data);
                    this.$swal({
                        position: 'top-end',
                        type: 'success',
                        title: 'Pay-Bill Created!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }).then(() => {
                    this.isWorking = false;
                });
            },

            showPaymentModal() {
                this.getDueBills();
                this.pay_bill_modal = true;
            }
        },

        watch: {
            finalTotalAmount( newval ) {
                this.finalTotalAmount = newval;
            },

            'basic_fields.customer'() {
                this.getCustomerAddress();
            }
        },

    }
</script>

<style lang="less">

</style>
