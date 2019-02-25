<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Expense</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <form action="" method="post" @submit.prevent="SubmitForExpense">

            <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
                <div class="wperp-panel-body">

                    <show-errors :error_msgs="form_errors" ></show-errors>

                    <form action="" class="wperp-form" method="post">
                        <div class="wperp-row">
                            <div class="wperp-col-sm-4">
                                <div class="wperp-form-group">
                                    <select-people v-model="basic_fields.people"></select-people>
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
                                    <label>Expense Date<span class="wperp-required-sign">*</span></label>
                                    <datepicker v-model="basic_fields.trn_date"></datepicker>
                                </div>

                            </div>
                            <div class="wperp-col-sm-4">
                                <label>Billing Address</label>
                                <textarea v-model.trim="basic_fields.billing_address" rows="3" class="wperp-form-field" placeholder="Type here"></textarea>
                            </div>
                            <div class="wperp-col-sm-4 with-multiselect">
                                <label>Transaction From<span class="wperp-required-sign">*</span></label>
                                <select-accounts v-model="basic_fields.deposit_to"></select-accounts>
                            </div>
                            <div class="wperp-col-sm-4 with-multiselect">
                                <label>Payment Method<span class="wperp-required-sign">*</span></label>
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
                            <th scope="col" class="col--id column-primary">ID</th>
                            <th scope="col">Account</th>
                            <th scope="col">Description</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Total</th>
                            <th scope="col" class="col--actions"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr :key="key" v-for="(line, key) in transactionLines">
                            <td scope="row" class="col--id column-primary">{{key+1}}</td>
                            <td class="col--account with-multiselect">
                                <multi-select v-model="line.ledger_id" :options="ledgers" />
                            </td>
                            <td class="col--particulars">
                                <textarea v-model="line.particulars" rows="1" class="wperp-form-field display-flex" placeholder="Particulars"></textarea>
                            </td>
                            <td class="col--amount" data-colname="Amount">
                                <input type="text" name="amount" v-model="line.amount" @keyup="updateFinalAmount" class="text-right"/>
                            </td>
                            <td class="col--total" style="text-align: center" data-colname="Total">
                                <input type="text" :value="line.amount" readonly disabled/>
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
                        <tr class="add-new-line">
                            <td colspan="9" style="text-align: left;">
                                <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add Line</button>
                            </td>
                        </tr>
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
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="9" style="text-align: right;">
                                <combo-button v-if="editMode" :options="updateButtons" />
                                <combo-button v-else :options="createButtons" />
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
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import ComboButton from 'admin/components/select/ComboButton.vue';
    import SelectPeople from 'admin/components/people/SelectPeople.vue'
    import SelectAccounts from 'admin/components/select/SelectAccounts.vue'
    import CheckFields from 'admin/components/check/CheckFields.vue'
    import ShowErrors from 'admin/components/base/ShowErrors.vue'

    export default {
        name: 'ExpenseCreate',

        components: {
            SelectAccounts,
            Datepicker,
            MultiSelect,
            FileUpload,
            ComboButton,
            SelectPeople,
            CheckFields,
            ShowErrors
        },

        data() {
            return {
                basic_fields: {
                    people: '',
                    trn_ref: '',
                    trn_date: '',
                    deposit_to: '',
                    trn_by: '',
                    billing_address: ''
                },

                check_data: {
                    payer_name: '',
                    check_no: ''
                },

                form_errors: [],

                createButtons: [
                    {id: 'save', text: 'Create Expense'},
                    {id: 'send_create', text: 'Create and Send'},
                    {id: 'new_create', text: 'Create and New'},
                ],

                updateButtons: [
                    {id: 'update', text: 'Update Expense'},
                    {id: 'send_update', text: 'Update and Send'},
                    {id: 'new_update', text: 'Update and New'},
                ],

                editMode        : false,
                voucherNo       : 0,
                transactionLines: [],
                selected        : [],
                ledgers         : [],
                pay_methods     : [],
                attachments     : [],
                totalAmounts    : [],
                finalTotalAmount: 0,
                billModal       : false,
                particulars     : '',
                isWorking       : false,
                acct_assets     : erp_acct_var.acct_assets
            }
        },

        watch: {
            finalTotalAmount( newval ) {
                this.finalTotalAmount = newval;
            },

            'basic_fields.people'() {
                this.getPeopleAddress();
            }
        },

        created() {
            this.prepareDataLoad();

            this.$root.$on('remove-row', index => {
                this.$delete(this.transactionLines, index);
                this.updateFinalAmount();
            });

        },

        methods: {
            async prepareDataLoad() {
                /**
                 * ----------------------------------------------
                 * check if editing
                 * -----------------------------------------------
                 */
                if ( this.$route.params.id ) {
                    this.editMode  = true;
                    this.voucherNo = this.$route.params.id;

                    /**
                     * Duplicates of
                     *? this.getLedgers()
                     *? this.getPayMethods()
                     */
                    let [request1, request2] = await Promise.all([
                        HTTP.get('/ledgers'),
                        HTTP.get('/transactions/payment-methods')
                    ]);

                    let request3 = await HTTP.get(`/expenses/${this.$route.params.id}`);

                    this.ledgers     = request1.data;
                    this.pay_methods = request2.data;
                    this.setDataForEdit( request3.data );

                } else {
                    /**
                     * ----------------------------------------------
                     * create a new expense
                     * -----------------------------------------------
                     */
                    this.getLedgers();
                    this.getPayMethods();

                    this.basic_fields.trn_date = erp_acct_var.current_date;
                    this.basic_fields.due_date = erp_acct_var.current_date;
                    this.transactionLines.push({}, {}, {});
                }
            },

            setDataForEdit(expense) {
                this.basic_fields.people          = { id: parseInt(expense.people_id), name: expense.people_name };
                this.basic_fields.deposit_to      = { id: parseInt(expense.deposit_to) };
                this.basic_fields.trn_by          = this.pay_methods.find(method => method.id === expense.trn_by);
                this.basic_fields.billing_address = expense.address;
                this.basic_fields.trn_date        = expense.trn_date;
                this.basic_fields.trn_ref         = expense.ref;
                this.status                       = expense.status;
                this.particulars                  = expense.particulars;
                this.attachments                  = expense.attachments;

                // format transaction lines
                expense.bill_details.forEach(detail => {
                    this.transactionLines.push({
                        id         : detail.id,
                        ledger_id  : { id: detail.ledger_id, name: detail.ledger_name },
                        particulars: detail.particulars,
                        amount     : detail.amount
                    });
                });

                this.updateFinalAmount();
            },

            getLedgers() {
                HTTP.get('/ledgers').then((response) => {
                    this.ledgers = response.data;
                });
            },

            getPayMethods() {
                HTTP.get('/transactions/payment-methods').then((response) => {
                    this.pay_methods = response.data;
                });
            },

            setCheckFields( check_data ) {
                this.check_data = check_data;
            },

            getPeopleAddress() {
                let user_id = this.basic_fields.people.id;

                if ( ! user_id ) {
                    this.basic_fields.billing_address = '';
                    return;
                }

                HTTP.get(`/people/${user_id}/address`).then((response) => {
                    let billing = response.data;

                    if ( 'string' == typeof billing ) {
                        this.basic_fields.billing_address = billing;
                        return;
                    }

                    let address = `Street: ${billing.street_1} ${billing.street_2} \nCity: ${billing.city} \nState: ${billing.state} \nCountry: ${billing.country}`;

                    this.basic_fields.billing_address = address;
                });
            },

            updateFinalAmount() {
                let finalAmount = 0;

                this.transactionLines.forEach(element => {
                    finalAmount += parseFloat(element.amount);
                });

                this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
            },

            addLine() {
                this.transactionLines.push({});
            },

            updateExpense(requestData) {
                HTTP.put(`/expenses/${this.voucherNo}`, requestData).then(res => {
                    this.showAlert('success', 'Expense Updated!');
                }).then(() => {
                    this.isWorking = false;
                    this.reset = true;

                    if ('update' == this.actionType) {
                        this.$router.push({name: 'Expense'});
                    } else if ('new_update' == this.actionType) {
                        this.resetFields();
                    }
                });
            },

            createExpense(requestData) {
                HTTP.post('/expenses', requestData).then(res => {
                    this.showAlert('success', 'Expense Created!');
                }).then(() => {
                    this.isWorking = false;
                    this.reset = true;

                    if ('save' == this.actionType) {
                        this.$router.push({name: 'Expense'});
                    } else if ('new_create' == this.actionType) {
                        this.resetFields();
                    }
                });
            },

            SubmitForExpense() {
                this.validateForm();

                if ( this.form_errors.length ) {
                    return;
                }

                this.isWorking = true;

                let requestData = {
                    people_id: this.basic_fields.people.id,
                    ref: this.basic_fields.trn_ref,
                    trn_date: this.basic_fields.trn_date,
                    trn_by: this.basic_fields.trn_by.id,
                    bill_details: this.formatTrnLines(this.transactionLines),
                    deposit_to: this.basic_fields.deposit_to.id,
                    billing_address: this.basic_fields.billing_address,
                    attachments: this.attachments,
                    type: 'expense',
                    status: 'paid',
                    particulars: this.particulars,
                    check_no: parseInt(this.check_data.check_no),
                    name: this.check_data.payer_name
                };

                if ( this.editMode ) {
                    this.updateExpense(requestData);
                } else {
                    this.createExpense(requestData);
                }
            },

            validateForm() {
                this.form_errors = [];

                if ( !this.basic_fields.people.hasOwnProperty('id') ) {
                    this.form_errors.push('People Name is required.');
                }

                if ( !this.basic_fields.trn_ref ) {
                    this.form_errors.push('Transaction Reference is required.');
                }

                if ( !this.basic_fields.trn_date ) {
                    this.form_errors.push('Transaction Date is required.');
                }

                if ( !this.basic_fields.deposit_to.hasOwnProperty('id') ) {
                    this.form_errors.push('Transaction Account is required.');
                }

                if ( !this.basic_fields.trn_by.hasOwnProperty('id') ) {
                    this.form_errors.push('Payment Method is required.');
                }
            },

            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
            },

            removeRow(index) {
                this.$delete(this.transactionLines, index);
                this.updateFinalAmount();
            },

            formatTrnLines( trl_lines ) {
                trl_lines.forEach(element => {
                    element.ledger_id = element.ledger_id.id;
                });

                return trl_lines;
            }

        }

    }
</script>

<style lang="less">
    input:disabled {
        background: #eee;
        color: #333;
    }
</style>
