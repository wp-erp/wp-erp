<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Pay Bill</h2>
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
                            <label>Transaction From</label>
                            <select-accounts v-model="basic_fields.deposit_to"></select-accounts>
                        </div>
                        <div class="wperp-col-sm-6">
                            <label>Billing Address</label>
                            <textarea v-model.trim="basic_fields.billing_address" rows="3" class="wperp-form-field" placeholder="Type here"></textarea>
                        </div>
                        <div class="wperp-col-sm-6 with-multiselect">
                            <label>Payment Method</label>
                            <multi-select v-model="basic_fields.trn_by" :options="pay_methods"></multi-select>
                        </div>

                        <check-fields v-if="basic_fields.trn_by.id === '3'" @updateCheckFields="setCheckFields"></check-fields>
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
                        <td class="col--total" data-colname="Total">{{pay_bill.amount}}</td>
                        <td class="col--due" data-colname="Due">{{pay_bill.due}}</td>
                        <td class="col--amount" data-colname="Amount">
                            <input type="text" min="0" :max="pay_bill.due" v-model="totalAmounts[key]" @keyup="updateFinalAmount" class="text-right"/>
                        </td>
                        <td class="delete-row" data-colname="Remove Above Selection">
                            <a @click.prevent="removeRow(key)" href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>

                    <tr class="total-amount-row">
                        <td class="text-right pr-0 hide-sm" colspan="4">Total Amount</td>
                        <td class="text-right" data-colname="Total Amount">
                            <input type="text" class="text-right" v-model="finalTotalAmount" readonly disabled/></td>
                        <td class="text-right"></td>
                    </tr>
                    </tbody>
                    <tr class="wperp-form-group">
                        <td colspan="9" style="text-align: left;">
                            <label>Particulars</label>
                            <textarea v-model="particulars" rows="4" class="wperp-form-field display-flex" placeholder="Internal Information"></textarea>
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

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker from 'admin/components/base/Datepicker.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import SelectPeople from 'admin/components/people/SelectPeople.vue'
    import SelectAccounts from 'admin/components/select/SelectAccounts.vue'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import CheckFields from 'admin/components/check/CheckFields.vue'

    export default {
        name: 'PayBillCreate',

        components: {
            SelectAccounts,
            SelectPeople,
            HTTP,
            Datepicker,
            FileUpload,
            SubmitButton,
            MultiSelect,
            CheckFields
        },

        data() {
            return {
                basic_fields: {
                    people: '',
                    trn_ref: '',
                    payment_date: '',
                    deposit_to: '',
                    billing_address: '',
                    trn_by: ''
                },

                check_data: {
                    payer_name: '',
                    check_no: ''
                },

                pay_bills: [],
                attachments: [],
                dueAmounts: [],
                totalAmounts:[],
                pay_methods: [],
                finalTotalAmount: 0,
                particulars: '',
                isWorking: false,
                acct_assets: erp_acct_var.acct_assets
            }
        },

        created() {
            this.getPayMethods();

            this.$root.$on('remove-row', index => {
                this.$delete(this.pay_bills, index);
                this.updateFinalAmount();
            });
        },

        methods: {
            getPayMethods() {
                HTTP.get('/transactions/payment-methods').then((response) => {
                    response.data.forEach(element => {
                        this.pay_methods.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                });
            },

            setCheckFields( check_data ) {
                this.check_data = check_data;
            },

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
                        if ( element.due !== null && element.due > 0 ) {
                            this.pay_bills.push({
                                id: element.id,
                                voucher_no: element.voucher_no,
                                due_date: element.due_date,
                                amount: parseFloat(element.amount),
                                due: parseFloat(element.due)
                            });
                        }
                    });
                }).then(() => {
                    this.pay_bills.forEach(element => {
                        this.totalAmounts[idx++] = parseFloat(element.due);
                        finalAmount += parseFloat(element.due);
                    });

                    this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
                });
            },

            getPeopleAddress() {
                let people_id = this.basic_fields.people.id;

                HTTP.get(`/people/${people_id}`).then((response) => {
                    // add more info
                    this.basic_fields.billing_address =
                        `Street: ${response.data.street_1} ${response.data.street_2},
                        City: ${response.data.city}, Country: ${response.data.country}`;
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

                if ( !this.basic_fields.deposit_to.hasOwnProperty('id') ) {
                    this.$swal({
                        position: 'center',
                        type: 'info',
                        title: 'Please Select an Account',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    return;
                }

                this.pay_bills.forEach( (element,index) => {
                    element['amount'] = parseFloat( this.totalAmounts[index] );
                });

                HTTP.post('/pay-bills', {
                    people_id: this.basic_fields.people.id,
                    ref: this.basic_fields.trn_ref,
                    trn_date: this.basic_fields.trans_date,
                    due_date: this.basic_fields.due_date,
                    bill_details: this.pay_bills,
                    attachments: this.attachments,
                    type: 'pay_bill',
                    status: 4,
                    particulars: this.particulars,
                    deposit_to: this.basic_fields.deposit_to.id,
                    trn_by: this.basic_fields.trn_by.id,
                    check_no: parseInt(this.check_data.check_no),
                    name: this.check_data.payer_name
                }).then(res => {
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Pay-Bill Created!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }).catch( error => {
                    this.$swal({
                        position: 'center',
                        type: 'error',
                        title: 'Something went Wrong!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }).then(() => {
                    this.resetData();
                    this.isWorking = false;
                });
            },

            showPaymentModal() {
                this.getDueBills();
            },

            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
            },

            removeRow(index) {
                this.$delete(this.transactionLines, index);
                this.updateFinalAmount();
            },
        },

        watch: {
            finalTotalAmount( newval ) {
                this.finalTotalAmount = newval;
            },

            'basic_fields.people'() {
                this.getPeopleAddress();
            }
        },

    }
</script>

<style lang="less">

</style>
