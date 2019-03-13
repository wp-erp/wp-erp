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

        <form action="" method="post" @submit.prevent="SubmitForPayment">

        <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
            <div class="wperp-panel-body">

                <show-errors :error_msgs="form_errors" ></show-errors>

                <form action="" class="wperp-form" method="post">
                    <div class="wperp-row">
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <!-- @input="getDueInvoices"  -->
                                <select-customers v-model="basic_fields.customer"></select-customers>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label>Reference<span class="wperp-required-sign">*</span></label>
                                <input type="text" v-model="basic_fields.trn_ref"/>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label>Payment Date<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.payment_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4 with-multiselect">
                            <label>Payment Method<span class="wperp-required-sign">*</span></label>
                            <multi-select v-model="basic_fields.trn_by" :options="pay_methods"></multi-select>
                        </div>
                        <div class="wperp-col-sm-4">
                            <label>Deposit to<span class="wperp-required-sign">*</span></label>
                            <select-accounts v-model="basic_fields.deposit_to" :reset="reset" :override_accts="accts_by_chart"></select-accounts>
                        </div>
                        <div class="wperp-col-sm-4">
                            <label>Billing Address</label>
                            <textarea v-model.trim="basic_fields.billing_address" rows="4" class="wperp-form-field" placeholder="Type here"></textarea>
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
                        <th scope="col" class="col--id column-primary">Voucher No</th>
                        <th scope="col">Due Date</th>
                        <th scope="col">Total</th>
                        <th scope="col">Due</th>
                        <th scope="col">Amount</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="key" v-for="(invoice,key) in invoices">
                        <td scope="row" class="col--id column-primary">#{{invoice.invoice_no}}</td>
                        <td class="col--due-date" data-colname="Due Date">{{invoice.due_date}}</td>
                        <td class="col--total" data-colname="Total">{{invoice.amount}}</td>
                        <td class="col--due" data-colname="Due">{{invoice.due}}</td>
                        <td class="col--amount" data-colname="Amount">
                            <input type="text" v-model="totalAmounts[key]" @keyup="updateFinalAmount" class="text-right"/>
                        </td>
                        <td class="delete-row" data-colname="Remove Above Selection">
                            <a @click.prevent="removeRow(key)" href="#"><i class="flaticon-trash"></i></a>
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
                    <tr>
                        <td>
                            <div class="attachment-item" :key="index" v-for="(file, index) in attachments">
                                <img :src="erp_acct_assets + '/images/file-thumb.png'">
                                <span class="remove-file" @click="removeFile(index)">&#10007;</span>

                                <div class="attachment-meta">
                                    <h3>{{ getFileName(file) }}</h3>
                                </div>
                            </div>
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
                            <combo-button :options="createButtons" />
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        </form>

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker from 'admin/components/base/Datepicker.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import SelectCustomers from 'admin/components/people/SelectCustomers.vue'
    import SelectAccounts from 'admin/components/select/SelectAccounts.vue'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import CheckFields from 'admin/components/check/CheckFields.vue'
    import ShowErrors from 'admin/components/base/ShowErrors.vue'
    import ComboButton from 'admin/components/select/ComboButton.vue'

    export default {
        name: 'RecPaymentCreate',

        components: {
            SelectAccounts,
            Datepicker,
            FileUpload,
            SelectCustomers,
            MultiSelect,
            CheckFields,
            ShowErrors,
            ComboButton
        },

        data() {
            return {
                basic_fields: {
                    customer       : '',
                    trn_ref        : '',
                    payment_date   : '',
                    deposit_to     : '',
                    billing_address: '',
                    trn_by         : ''
                },

                check_data: {
                    payer_name: '',
                    check_no  : ''
                },

                createButtons: [
                    {id: 'save', text: 'Receive Payment'},
                    {id: 'new_create', text: 'Receive and New'},
                ],

                form_errors: [],

                editMode        : false,
                voucherNo       : 0,
                invoices        : [],
                attachments     : [],
                totalAmounts    : [],
                pay_methods     : [],
                finalTotalAmount: 0,
                particulars     : '',
                isWorking       : false,
                accts_by_chart  : [],
                erp_acct_assets : erp_acct_var.acct_assets,
                actionType      : null,
                reset           : false
            }
        },

        watch: {
            finalTotalAmount( newval ) {
                this.finalTotalAmount = newval;
            },

            'basic_fields.customer'() {
                this.getCustomerAddress();
                this.getDueInvoices();
            },

            'basic_fields.trn_by'() {
                this.changeAccounts();
            }

        },

        created() {
            this.prepareDataLoad();

            this.$root.$on('combo-btn-select', button => {
                this.actionType = button.id;
            });
        },

        mounted() {
            // `receive payment` request from invoice list row action
            if ( this.$route.params.customer_id ) {
                this.basic_fields.customer  = {
                    id  : parseInt(this.$route.params.customer_id),
                    name: this.$route.params.customer_name
                };
            }
        },

        methods: {
            async prepareDataLoad() {
                /**
                 * ----------------------------------------------
                 * check if editing
                 * -----------------------------------------------
                 */
                if ( this.$route.params.id ) {
                    this.editMode = true;
                    this.voucherNo = this.$route.params.id;

                    /**
                     * Duplicates of
                     *? this.getPayMethods()
                     */
                    let request1 = await HTTP.get('/transactions/payment-methods');
                    let request2 = await HTTP.get(`/invoices/${this.$route.params.id}`);

                    if ( ! request2.data.line_items.length ) {
                        this.showAlert('error', 'Invoice does not exists!');
                        return;
                    }

                    if ( 'awaiting_approval' != request3.data.status ) {
                        this.showAlert('error', 'Can\'t edit');
                        return;
                    }

                    this.pay_methods = request1.data;
                    this.setDataForEdit( request2.data );
                } else {
                    /**
                     * ----------------------------------------------
                     * create a new Receive Payment
                     * -----------------------------------------------
                     */
                    this.basic_fields.payment_date = erp_acct_var.current_date;

                    this.getPayMethods();
                }
            },

            getPayMethods() {
                this.$store.dispatch( 'spinner/setSpinner', true );

                HTTP.get('/transactions/payment-methods').then(response => {
                    this.pay_methods = response.data;

                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } );
            },

            setCheckFields( check_data ) {
                this.check_data = check_data;
            },

            getDueInvoices() {
                this.invoices = [];

                let customerId  = this.basic_fields.customer.id,
                    idx         = 0,
                    finalAmount = 0;

                if ( ! customerId ) {
                    return;
                }

                HTTP.get(`/invoices/due/${customerId}`).then(response => {
                    response.data.forEach(element => {
                        this.invoices.push({
                            id        : element.id,
                            invoice_no: element.voucher_no,
                            due_date  : element.due_date,
                            amount    : parseFloat(element.amount),
                            due       : parseFloat(element.due)
                        });
                    });
                }).then(() => {
                    this.invoices.forEach(element => {
                        this.totalAmounts[idx++] = parseFloat(element.due);
                        finalAmount += parseFloat(element.due);
                    });

                    this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
                });
            },

            getCustomerAddress() {
                let customer_id = this.basic_fields.customer.id;

                if ( ! customer_id ) {
                    this.basic_fields.billing_address = '';
                    return;
                }

                HTTP.get(`/people/${customer_id}`).then(response => {
                    let billing = response.data;

                    let address = `Street: ${billing.street_1} ${billing.street_2} \nCity: ${billing.city} \nState: ${billing.state} \nCountry: ${billing.country}`;

                    this.basic_fields.billing_address = address;
                });
            },

            updateFinalAmount() {
                let finalAmount = 0;

                this.totalAmounts.forEach(element => {
                    finalAmount += parseFloat(element);
                });

                this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
            },

            SubmitForPayment(event) {
                this.validateForm();

                if ( this.form_errors.length ) {
                    window.scrollTo({
                        top: 10,
                        behavior: 'smooth'
                    });
                    return;
                }

                this.invoices.forEach( (element,index) => {
                    element['line_total'] = parseFloat( this.totalAmounts[index] );
                });
                this.$store.dispatch( 'spinner/setSpinner', true );

                HTTP.post('/payments', {
                    customer_id: this.basic_fields.customer.id,
                    ref        : this.basic_fields.trn_ref,
                    trn_date   : this.basic_fields.payment_date,
                    line_items : this.invoices,
                    attachments: this.attachments,
                    type       : 'payment',
                    status     : 4,
                    particulars: this.particulars,
                    deposit_to : this.basic_fields.deposit_to.id,
                    trn_by     : this.basic_fields.trn_by.id,
                    check_no   : parseInt(this.check_data.check_no),
                    name       : this.check_data.payer_name
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );

                    this.showAlert('success', 'Payment Created!');
                    this.reset = true;

                    if ('save' == this.actionType) {
                        this.$router.push({name: 'Sales'});
                    } else if ('new_create' == this.actionType) {
                        this.resetFields();
                    }
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                });

                event.target.reset();
            },

            changeAccounts() {
                if ( '2' === this.basic_fields.trn_by.id || '3' === this.basic_fields.trn_by.id ) {
                    this.accts_by_chart = [];

                    HTTP.get('/ledgers/bank-accounts').then((response) => {
                        this.accts_by_chart = response.data;
                    });
                } else {
                    this.accts_by_chart = [];

                    HTTP.get('/ledgers/cash-accounts').then((response) => {
                        this.accts_by_chart = response.data;
                    });
                }
                this.$root.$emit('account-changed');
            },

            validateForm() {
                this.form_errors = [];

                if ( !this.basic_fields.customer.hasOwnProperty('id') ) {
                    this.form_errors.push('Customer Name is required.');
                }

                if ( !this.basic_fields.trn_ref ) {
                    this.form_errors.push('Transaction Reference is required.');
                }

                if ( !this.basic_fields.payment_date ) {
                    this.form_errors.push('Transaction Date is required.');
                }

                if ( !this.basic_fields.deposit_to.hasOwnProperty('id') ) {
                    this.form_errors.push('Deposit Account is required.');
                }

                if ( !this.basic_fields.trn_by.hasOwnProperty('id') ) {
                    this.form_errors.push('Payment Method is required.');
                }
            },

            showPaymentModal() {
                this.getDueInvoices();
            },

            resetData() {
                this.basic_fields = {
                    customer       : '',
                    trn_ref        : '',
                    payment_date   : erp_acct_var.current_date,
                    deposit_to     : '',
                    billing_address: '',
                    trn_by         : ''
                };

                this.check_data = {
                    payer_name: '',
                    check_no  : ''
                };

                this.form_errors      = [];
                this.invoices         = [];
                this.attachments      = [];
                this.totalAmounts     = [];
                this.finalTotalAmount = 0;
                this.particulars      = '';
                this.isWorking        = false,
                this.reset            = false;
                this.actionType       = null;
            },

            removeRow(index) {
                this.$delete(this.invoices, index);
                this.updateFinalAmount();
            },
        },

    }
</script>

<style lang="less">

</style>
