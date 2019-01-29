<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Pay Purchase</h2>
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
                                <select-vendors @input="getDuePurchases" v-model="basic_fields.vendor"></select-vendors>
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
                                <datepicker v-model="basic_fields.payment_date" :defaultDate="basic_fields.payment_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3 with-multiselect">
                            <label>Transaction From</label>
                            <multi-select :placeholder="`Select Account`" v-model="basic_fields.deposit_to" :options="deposit_accts" />
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

        <div class="wperp-table-responsive" v-if="pay_purchases.length">
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
                    <tr :key="key" v-for="(item,key) in pay_purchases">
                        <td scope="row" class="col--id column-primary">{{key+1}}</td>
                        <td class="col--due-date" data-colname="Due Date">{{item.due_date}}</td>
                        <td class="col--total" data-colname="Total">{{item.total}}</td>
                        <td class="col--due" data-colname="Due">{{item.due}}</td>
                        <td class="col--amount" data-colname="Amount">
                            <input type="number" min="0" :max="item.due" name="amount" v-model="totalAmounts[key]" @keyup="updateFinalAmount" class="text-right"/>
                        </td>
                        <td class="delete-row" data-colname="Remove Above Selection">
                            <a href="#" @click.prevent="remove_item(key)"><i class="flaticon-trash"></i>Remove</a>
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
                                <file-upload v-model="attachments" url="/invoices/attachments"/>
                            </div>
                        </td>
                    </tr>
                    <tfoot>
                    <tr>
                        <td colspan="9" style="text-align: right;">
                            <submit-button text="Pay Purchase" @click.native="SubmitForPayment" :working="isWorking"></submit-button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <template v-if="pay_bill_modal">
            <pay-bill-modal :basic_fields="basic_fields" :pay_purchases="pay_purchases" :attachments="attachments" :finalTotalAmount="finalTotalAmount" :assets_url="acct_assets" />
        </template>

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker from 'admin/components/base/Datepicker.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import PayBillModal from 'admin/components/pay-bill/PayBillModal.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import SelectVendors from 'admin/components/people/SelectVendors.vue'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import CheckFields from 'admin/components/check/CheckFields.vue'

    export default {
        name: 'PayPurchaseCreate',

        components: {
            MultiSelect,
            SelectVendors,
            HTTP,
            Datepicker,
            FileUpload,
            PayBillModal,
            SubmitButton,
            CheckFields
        },

        data() {
            return {
                basic_fields: {
                    vendor: '',
                    trn_ref: '',
                    payment_date: erp_acct_var.current_date,
                    deposit_to: '',
                    billing_address: '',
                    trn_by: ''
                },

                check_data: {
                    payer_name: '',
                    check_no: ''
                },

                pay_methods: [],
                deposit_accts: [],
                pay_purchases: [],
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

            this.fetchAccounts();
            this.getPayMethods();
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

            resetData() {

                    this.basic_fields = {
                        vendor: '',
                        trn_ref: '',
                        payment_date: erp_acct_var.current_date,
                        deposit_to: '',
                        billing_address: ''
                    };

                    this.pay_purchases = [];
                    this.attachments = [];
                    this.totalAmounts = [];
                    this.finalTotalAmount = 0;
                    this.pay_bill_modal = false;
                    this.particulars = '';
                    this.isWorking = false;
            },

            fetchAccounts(){
                HTTP.get('accounts').then( (response) => {
                    this.deposit_accts = response.data;
                } );
            },

            getDuePurchases() {
                let vendorId = this.basic_fields.vendor.id,
                    idx = 0,
                    finalAmount = 0;

                // for modal test. remove later
                if ( undefined === vendorId ) {
                    vendorId = 1;
                }
                this.pay_purchases = [];
                HTTP.get(`/purchases/due/${vendorId}`).then((response) => {
                    response.data.forEach(element => {
                        this.pay_purchases.push({
                            id: element.id,
                            voucher_no: element.voucher_no,
                            due_date: element.due_date,
                            total: parseFloat(element.amount),
                            due: parseFloat(element.due_total)
                        });
                    });
                }).then(() => {
                    this.pay_purchases.forEach(element => {
                        this.totalAmounts[idx++] = parseFloat(element.due);
                        finalAmount += parseFloat(element.due);
                    });

                    this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
                });
            },

            getCustomerAddress() {
                let vendors_id = this.basic_fields.vendor.id;

                HTTP.get(`/vendors/${vendors_id}`).then((response) => {
                    // add more info
                    this.basic_fields.billing_address =`
                        Street: ${response.data.billing.street_1} ${response.data.billing.street_2},
                        City: ${response.data.billing.city}, Country: ${response.data.billing.country}
                        `;
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

                this.pay_purchases.forEach( (element,index) => {
                    element['line_total'] = parseFloat( this.totalAmounts[index] );
                });

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

                HTTP.post('/pay-purchases', {
                    vendor_id: this.basic_fields.vendor.id,
                    ref: this.basic_fields.trn_ref,
                    trn_date: this.basic_fields.payment_date,
                    purchase_details: this.pay_purchases,
                    attachments: this.attachments,
                    type: 'pay_purchase',
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
                        title: 'Pay Purchase Created!',
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
                    this.isWorking = false;
                    this.resetData();
                });
            },

            showPaymentModal() {
                this.getDuePurchases();
                this.pay_bill_modal = true;
            },

            remove_item( index ) {
                this.$delete( this.pay_purchases, index );
                this.$delete( this.totalAmounts, index );
                this.updateFinalAmount();
            }
        },

        watch: {
            finalTotalAmount( newval ) {
                this.finalTotalAmount = newval;
            },

            'basic_fields.vendor'() {
                this.getCustomerAddress();
            }
        },

    }
</script>

<style lang="less">

</style>
