<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Bill</h2>
                </div>
            </div>
        </div> <!-- End .header-section -->

        <form action="" method="post" @submit.prevent="submitBillForm">

            <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
                <div class="wperp-panel-body">

                    <show-errors :error_msgs="form_errors" ></show-errors>

                    <form action="" class="wperp-form" method="post">
                        <div class="wperp-row">
                            <div class="wperp-col-sm-3">
                                <div class="wperp-form-group">
                                    <select-people v-model="basic_fields.user"></select-people>
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
                                    <label>Bill Date<span class="wperp-required-sign">*</span></label>
                                    <datepicker v-model="basic_fields.trn_date"></datepicker>
                                </div>
                            </div>
                            <div class="wperp-col-sm-3">
                                <div class="wperp-form-group">
                                    <label>Due Date<span class="wperp-required-sign">*</span></label>
                                    <datepicker v-model="basic_fields.due_date"></datepicker>
                                </div>
                            </div>
                            <div class="wperp-col-xs-12">
                                <label>Billing Address</label>
                                <textarea v-model="basic_fields.billing_address" rows="4" class="wperp-form-field" placeholder="Type here"></textarea>
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
                            <td class="col--account with-multiselect"><multi-select v-model="line.ledger_id" :options="ledgers" /></td>
                            <td class="col--particulars"><textarea v-model="line.description" rows="1" class="wperp-form-field display-flex" placeholder="Particulars"></textarea></td>
                            <td class="col--amount" data-colname="Amount">
                                <input type="text" name="amount" v-model="line.amount" @keyup="updateFinalAmount" class="text-right"/>
                            </td>
                            <td class="col--total" style="text-align: center" data-colname="Total">
                                <input type="text" class="text-right" :value="line.amount" readonly disabled/>
                            </td>
                            <td class="delete-row" data-colname="Remove Above Selection">
                                <a @click.prevent="removeRow(key)" href="#"><i class="flaticon-trash"></i></a>
                            </td>
                        </tr>
                        <tr class="add-new-line">
                            <td colspan="9" style="text-align: left;">
                                <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add Line</button>
                            </td>
                        </tr>

                        <tr class="total-amount-row">
                            <td class="text-right pr-0 hide-sm" colspan="4">Total Amount</td>
                            <td class="text-right" data-colname="Total Amount">
                                <input type="text" class="text-right" name="finalamount" v-model="finalTotalAmount" readonly disabled/></td>
                            <td class="text-right"></td>
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
                                    <file-upload v-model="attachments" url="/bills/attachments"/>
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
    import { mapState, mapActions } from 'vuex'

    import HTTP from 'admin/http'
    import SelectPeople from 'admin/components/people/SelectPeople.vue'
    import Datepicker from 'admin/components/base/Datepicker.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import ShowErrors from 'admin/components/base/ShowErrors.vue'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import ComboButton from 'admin/components/select/ComboButton.vue';

    export default {
        name: 'BillCreate',

        components: {
            HTTP,
            Datepicker,
            MultiSelect,
            FileUpload,
            ComboButton,
            SelectPeople,
            ShowErrors
        },

        data() {
            return {
                basic_fields: {
                    user           : '',
                    trn_ref        : '',
                    trn_date       : '',
                    due_date       : '',
                    billing_address: ''
                },

                form_errors: [],

                createButtons: [
                    {id: 'save', text: 'Create Bill'},
                    // {id: 'send_create', text: 'Create and Send'},
                    {id: 'new_create', text: 'Create and New'},
                    {id: 'draft', text: 'Save as Draft'},
                ],

                updateButtons: [
                    {id: 'update', text: 'Update Bill'},
                    // {id: 'send_update', text: 'Update and Send'},
                    {id: 'new_update', text: 'Update and New'},
                    {id: 'draft', text: 'Save as Draft'},
                ],

                editMode        : false,
                voucherNo       : 0,
                transactionLines: [],
                selected        : [],
                ledgers         : [],
                attachments     : [],
                totalAmounts    : 0,
                finalTotalAmount: 0,
                particulars     : '',
                erp_acct_assets : erp_acct_var.acct_assets
            }
        },

         computed: {
            ...mapState({ actionType: state => state.combo.btnID })
        },

        created() {
            this.prepareDataLoad();

            this.$on('remove-row', index => {
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
                    this.editMode = true;
                    this.voucherNo = this.$route.params.id;

                    /**
                     * Duplicates of
                     *? this.getLedgers()
                     */
                    let expense_chart_id = 5;

                    let request1 = await HTTP.get(`/ledgers/${expense_chart_id}/accounts`);
                    let request2 = await HTTP.get(`/bills/${this.$route.params.id}`);

                    if ( ! request2.data.bill_details.length ) {
                        this.showAlert('error', 'Bill does not exists!');
                        return;
                    }

                    if ( 'pending' !== request2.data.status ) {
                        this.showAlert('error', 'Can\'t edit');
                        return;
                    }

                    this.ledgers   = request1.data;
                    this.setDataForEdit( request2.data );

                    // initialize combo button id with `update`
                    this.$store.dispatch('combo/setBtnID', 'update');

                } else {
                    /**
                     * ----------------------------------------------
                     * create a new bill
                     * -----------------------------------------------
                     */
                    this.getLedgers();

                    this.basic_fields.trn_date = erp_acct_var.current_date;
                    this.basic_fields.due_date = erp_acct_var.current_date;
                    this.transactionLines.push({}, {}, {});

                    // initialize combo button id with `save`
                    this.$store.dispatch('combo/setBtnID', 'save');
                }
            },

            setDataForEdit(bill) {
                this.basic_fields.user            = { id: parseInt(bill.vendor_id), name: bill.vendor_name };
                this.basic_fields.billing_address = bill.billing_address;
                this.basic_fields.trn_ref         = bill.ref;
                this.basic_fields.trn_date        = bill.trn_date;
                this.basic_fields.due_date        = bill.due_date;
                this.status                       = bill.status;
                this.finalTotalAmount             = bill.debit;
                this.particulars                  = bill.particulars;
                this.attachments                  = bill.attachments;

                // format transaction lines
                bill.bill_details.forEach(detail => {
                    this.transactionLines.push({
                        id         : detail.id,
                        ledger_id  : { id: detail.ledger_id, name: detail.ledger_name },
                        description: detail.particulars,
                        amount     : detail.amount
                    });
                });

                this.updateFinalAmount();
            },

            getLedgers() {
                let expense_chart_id = 5;
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.get(`/ledgers/${expense_chart_id}/accounts`).then(response => {
                    this.ledgers = response.data;
                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } );
            },

            getPeopleAddress() {
                let people_id = this.basic_fields.user.id;

                if ( ! people_id ) {
                    this.basic_fields.billing_address = '';
                    return;
                }

                HTTP.get(`/people/${people_id}`).then(response => {
                    let billing = response.data;

                    let address = `Street: ${billing.street_1} ${billing.street_2} \nCity: ${billing.city} \nState: ${billing.state} \nCountry: ${billing.country}`;

                    this.basic_fields.billing_address = address;
                });
            },

            updateFinalAmount() {
                let finalAmount = 0;

                this.transactionLines.forEach(element => {
                    if ( element.amount ) {
                        finalAmount += parseFloat(element.amount);
                    }
                });

                this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
            },

            addLine() {
                this.transactionLines.push({});
            },

            updateBill(requestData) {
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.put(`/bills/${this.voucherNo}`, requestData).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert('success', 'Bill Updated!');
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).then(() => {
                    this.reset = true;

                    if ('update' == this.actionType || 'draft' == this.actionType) {
                        this.$router.push({name: 'Expenses'});
                    } else if ('new_update' == this.actionType) {
                        this.resetFields();
                    }
                });
            },

            createBill(requestData) {
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.post('/bills', requestData).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert('success', 'Bill Created!');
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).then(() => {
                    this.reset = true;

                    if ('save' == this.actionType || 'draft' == this.actionType) {
                        this.$router.push({name: 'Expenses'});
                    } else if ('new_create' == this.actionType) {
                        this.resetFields();
                    }
                });
            },

            submitBillForm() {
                this.validateForm();

                if ( this.form_errors.length ) {
                    window.scrollTo({
                        top: 10,
                        behavior: 'smooth'
                    });
                    return;
                }

                let trn_status = null;
                if ( 'draft' === this.actionType) {
                    trn_status = 1;
                } else {
                    trn_status = 3;
                }

                let requestData = {
                    vendor_id      : this.basic_fields.user.id,
                    ref            : this.basic_fields.trn_ref,
                    trn_date       : this.basic_fields.trn_date,
                    due_date       : this.basic_fields.due_date,
                    bill_details   : this.formatTrnLines(this.transactionLines),
                    attachments    : this.attachments,
                    billing_address: this.basic_fields.billing_address,
                    type           : 'bill',
                    status         : trn_status,
                    particulars    : this.particulars
                };

                if ( this.editMode ) {
                    this.updateBill(requestData);
                } else {
                    this.createBill(requestData);
                }
            },

            resetFields() {
                this.transactionLines  = [];
                this.attachments       = [];
                this.totalAmounts      = 0;
                this.finalTotalAmount  = 0;
                this.particulars       = '';
                this.form_errors       = [];

                this.basic_fields = {
                    user           : { id: null, name: null},
                    trn_ref        : '',
                    trn_date       : erp_acct_var.current_date,
                    due_date       : erp_acct_var.current_date,
                    billing_address: ''
                };

                this.transactionLines.push({}, {}, {});

                // initialize combo button id with `save`
                this.$store.dispatch('combo/setBtnID', 'save');
            },

            validateForm() {
                this.form_errors = [];

                if ( !this.basic_fields.user.hasOwnProperty('id') ) {
                    this.form_errors.push('People Name is required.');
                }

                if ( !this.basic_fields.trn_ref ) {
                    this.form_errors.push('Transaction Reference is required.');
                }

                if ( !this.basic_fields.trn_date ) {
                    this.form_errors.push('Transaction Date is required.');
                }

                if ( !this.basic_fields.due_date ) {
                    this.form_errors.push('Due Date is required.');
                }
            },

            formatTrnLines( trl_lines ) {
                trl_lines.forEach(element => {
                    if ( element.length ) {
                        element.ledger_id = element.ledger_id.id;
                    }
                });

                return trl_lines;
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

            'basic_fields.user'() {
                this.getPeopleAddress();
            }
        },

    }
</script>

<style lang="less">

</style>
