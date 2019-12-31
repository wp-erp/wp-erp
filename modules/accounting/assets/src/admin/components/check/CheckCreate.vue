<template>
    <div class="wperp-container check-create">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('New Check', 'erp') }}</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <form action="" method="post" @submit.prevent="submitCheckForm">

        <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
            <div class="wperp-panel-body">

                <show-errors :error_msgs="form_errors" ></show-errors>

                <div class="wperp-row">
                    <div class="wperp-col-sm-4">
                        <div class="wperp-form-group">
                            <select-people v-model="basic_fields.people"></select-people>
                        </div>
                    </div>
                    <div class="wperp-col-sm-4">
                        <div class="wperp-form-group">
                            <label>{{ __('Check No', 'erp') }}<span class="wperp-required-sign">*</span></label>
                            <input type="text" class="wperp-form-field" v-model="basic_fields.check_no" required>
                        </div>
                    </div>
                    <div class="wperp-col-sm-4">
                        <div class="wperp-form-group">
                            <label>{{ __('Payment Date', 'erp') }}<span class="wperp-required-sign">*</span></label>
                            <datepicker v-model="basic_fields.trn_date"></datepicker>
                        </div>

                    </div>
                    <div class="wperp-col-sm-4 with-multiselect">
                        <label>{{ __('From Account', 'erp') }}<span class="wperp-required-sign">*</span></label>
                        <multi-select v-model="basic_fields.deposit_to" :options="bank_accts"></multi-select>
                    </div>
                    <div class="wperp-col-sm-4">
                        <label>{{ __('Billing Address', 'erp') }}</label>
                        <textarea v-model.trim="basic_fields.billing_address" rows="3" class="wperp-form-field" :placeholder="__('Type here', 'erp')"></textarea>
                    </div>
                </div>

            </div>
        </div>

        <div class="wperp-table-responsive">
            <!-- Start .wperp-crm-table -->
            <div class="table-container">
                <table class="wperp-table wperp-form-table">
                    <thead>
                    <tr>
                        <th scope="col" class="col--id column-primary">{{ __('SL No.', 'erp') }}</th>
                        <th scope="col">{{ __('Account', 'erp') }}</th>
                        <th scope="col">{{ __('Description', 'erp') }}</th>
                        <th scope="col">{{ __('Amount', 'erp') }}</th>
                        <th scope="col">{{ __('Total', 'erp') }}</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="key" v-for="(line,key) in transactionLines">
                        <td scope="row" class="col--id column-primary">{{key+1}}</td>
                        <td class="col--account with-multiselect"><multi-select v-model="line.ledger_id" :options="ledgers" /></td>
                        <td class="col--particulars">
                            <textarea v-model="line.particulars" rows="1" maxlength="250" class="wperp-form-field display-flex" :placeholder="__('Particulars', 'erp')"></textarea>
                        </td>
                        <td class="col--amount" data-colname="Amount">
                            <input type="number" min="0" step="0.01" name="amount" v-model="line.amount"
                                @keyup="updateFinalAmount" class="text-right wperp-form-field" :required="line.ledger_id ? true : false">
                        </td>
                        <td class="col--total" data-colname="Total">
                            <input type="text" :value="moneyFormat(line.amount)" class="text-right wperp-form-field" readonly disabled/>
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
                        <td class="text-right pr-0 hide-sm" colspan="4">{{ __('Total Amount', 'erp') }}</td>
                        <td class="text-right" data-colname="Total Amount">
                            <input type="text" class="text-right wperp-form-field" name="finalamount" :value="moneyFormat(finalTotalAmount)" readonly disabled>
                        </td>
                        <td class="text-right"></td>
                    </tr>

                    <tr class="wperp-form-group">
                        <td colspan="9" style="text-align: left;">
                            <label>{{ __('Particulars', 'erp') }}</label>
                            <textarea v-model="particulars" rows="4" maxlength="250" class="wperp-form-field display-flex" :placeholder="__('Internal Information', 'erp')"></textarea>
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
                                <label class="col--attachement">{{ __('Attachment', 'erp') }}</label>
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
import { mapState } from 'vuex';

import HTTP from 'admin/http';
import Datepicker from 'admin/components/base/Datepicker.vue';
import MultiSelect from 'admin/components/select/MultiSelect.vue';
import FileUpload from 'admin/components/base/FileUpload.vue';
import SelectPeople from 'admin/components/people/SelectPeople.vue';
import ComboButton from 'admin/components/select/ComboButton.vue';
import ShowErrors from 'admin/components/base/ShowErrors.vue';

export default {
    name: 'CheckCreate',

    components: {
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
                people: '',
                check_no: '',
                trn_date: '',
                deposit_to: '',
                trn_by: '',
                billing_address: ''
            },

            check_data: {
                bank_name: '',
                payer_name: '',
                check_no: ''
            },

            form_errors: [],

            createButtons: [
                { id: 'save', text: 'Save' },
                // {id: 'send_create', text: 'Create and Send'},
                { id: 'new_create', text: 'Save and New' },
                { id: 'draft', text: 'Save as Draft' }
            ],

            updateButtons: [
                { id: 'update', text: 'Update' },
                // {id: 'send_update', text: 'Update and Send'},
                { id: 'new_update', text: 'Update and New' },
                { id: 'draft', text: 'Save as Draft' }
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
            accts_by_chart  : [],
            bank_accts      : [],
            erp_acct_assets : erp_acct_var.acct_assets  /* global erp_acct_var */
        };
    },

    computed: {
        ...mapState({ actionType: state => state.combo.btnID })
    },

    created() {
        this.getBanks();
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
            if (this.$route.params.id) {
                this.editMode = true;
                this.voucherNo = this.$route.params.id;

                /**
                 * Duplicates of
                 *? this.getLedgers()
                */
                const request1 = await HTTP.get('/ledgers');
                const request2 = await HTTP.get(`/expenses/checks/${this.$route.params.id}`);

                if (!request2.data.bill_details.length) {
                    this.showAlert('error', 'Check does not exists!');
                    return;
                }

                this.ledgers = request1.data;
                this.setDataForEdit(request2.data);

                // initialize combo button id with `update`
                this.$store.dispatch('combo/setBtnID', 'update');
            } else {
                /**
                 * ----------------------------------------------
                 * create a new check
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

        setDataForEdit(check) {
            this.basic_fields.people          = { id: parseInt(check.people_id), name: check.people_name };
            this.basic_fields.deposit_to      = { id: parseInt(check.deposit_to) };
            this.basic_fields.trn_by          = this.pay_methods.find(method => method.id === check.trn_by);
            this.basic_fields.billing_address = check.address;
            this.basic_fields.trn_date        = check.trn_date;
            this.basic_fields.check_no        = check.ref;
            this.status                       = check.status;
            this.particulars                  = check.particulars;
            this.attachments                  = check.attachments;

            // format transaction lines
            check.bill_details.forEach(detail => {
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
            const expense_chart_id = 5;
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get(`/ledgers/${expense_chart_id}/accounts`).then(response => {
                this.ledgers = response.data;

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        getBanks() {
            const bank_chart_id = 7;
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get(`/ledgers/${bank_chart_id}/accounts`).then(response => {
                this.bank_accts = response.data;

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        setCheckFields(check_data) {
            this.check_data = check_data;
        },

        getPeopleAddress() {
            const people_id = this.basic_fields.people.id;

            if (!people_id) {
                this.basic_fields.billing_address = '';
                return;
            }

            HTTP.get(`/people/${people_id}`).then(response => {
                const billing = response.data;

                const address = `Street: ${billing.street_1} ${billing.street_2} \nCity: ${billing.city} \nState: ${billing.state} \nCountry: ${billing.country}`;

                this.basic_fields.billing_address = address;
            });
        },

        updateFinalAmount() {
            let finalAmount = 0;

            this.transactionLines.forEach(element => {
                if (element.amount) {
                    finalAmount += parseFloat(element.amount);
                }
            });

            this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
        },

        addLine() {
            this.transactionLines.push({});
        },

        updateCheck(requestData) {
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.put(`/expenses/${this.voucherNo}`, requestData).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', 'Check Updated!');
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                this.isWorking = false;
                this.reset = true;

                if (this.actionType === 'update' || this.actionType === 'draft') {
                    this.$router.push({ name: 'Expenses' });
                } else if (this.actionType === 'new_update') {
                    this.resetFields();
                }
            });
        },

        createCheck(requestData) {
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.post('/expenses', requestData).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', 'Check Created!');
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                this.isWorking = false;
                this.reset = true;

                if (this.actionType === 'save' || this.actionType === 'draft') {
                    this.$router.push({ name: 'Expenses' });
                } else if (this.actionType === 'new_create') {
                    this.resetFields();
                }
            });
        },

        submitCheckForm() {
            this.validateForm();

            if (this.form_errors.length) {
                window.scrollTo({
                    top: 10,
                    behavior: 'smooth'
                });
                return;
            }

            let trn_status = null;
            if (this.actionType === 'draft') {
                trn_status = 1;
            } else {
                trn_status = 4;
            }

            const requestData = {
                people_id      : this.basic_fields.people.id,
                check_no       : this.basic_fields.check_no,
                trn_date       : this.basic_fields.trn_date,
                trn_by         : '3',
                bill_details   : this.formatTrnLines(this.transactionLines),
                deposit_to     : this.basic_fields.deposit_to.id,
                billing_address: this.basic_fields.billing_address,
                attachments    : this.attachments,
                type           : 'check',
                status         : trn_status,
                particulars    : this.particulars,
                name           : this.check_data.payer_name
            };

            if (this.editMode) {
                this.updateCheck(requestData);
            } else {
                this.createCheck(requestData);
            }
        },

        validateForm() {
            this.form_errors = [];

            if (!Object.prototype.hasOwnProperty.call(this.basic_fields.people, 'id')) {
                this.form_errors.push('Pay to is required.');
            }

            if (!this.basic_fields.check_no) {
                this.form_errors.push('Check No is required.');
            }

            if (!this.basic_fields.trn_date) {
                this.form_errors.push('Transaction Date is required.');
            }

            if (!Object.prototype.hasOwnProperty.call(this.basic_fields.deposit_to, 'id')) {
                this.form_errors.push('Transaction Account is required.');
            }

            if (parseFloat(this.basic_fields.deposit_to.balance) < parseFloat(this.finalTotalAmount)) {
                this.form_errors.push('Not enough balance in selected account.');
            }

            if (!parseFloat(this.finalTotalAmount)) {
                this.form_errors.push('Total amount can\'t be zero.');
            }

            if (this.noFulfillLines(this.transactionLines, 'ledger_id')) {
                this.form_errors.push('Please select an account.');
            }
        },

        resetFields() {
            this.basic_fields = {
                people: { id: null, name: null },
                check_no: '',
                trn_date: erp_acct_var.current_date,
                deposit_to: '',
                trn_by: '',
                billing_address: ''
            };

            this.check_data = {
                bank_name: '',
                payer_name: '',
                check_no: ''
            };

            this.form_errors = [];
            this.transactionLines = [];
            this.attachments = [];
            this.totalAmounts = [];
            this.finalTotalAmount = 0;
            this.particulars = '';
            this.isWorking = false;

            this.transactionLines.push({}, {}, {});
            this.$store.dispatch('combo/setBtnID', 'save');
        },

        removeRow(index) {
            this.$delete(this.transactionLines, index);
            this.updateFinalAmount();
        },

        formatTrnLines(trn_lines) {
            const line_items = [];

            trn_lines.forEach(element => {
                if (Object.prototype.hasOwnProperty.call(element, 'ledger_id')) {
                    element.ledger_id = element.ledger_id.id;
                    line_items.push(element);
                }
            });

            return line_items;
        }

    },

    watch: {
        finalTotalAmount(newval) {
            this.finalTotalAmount = newval;
        },

        'basic_fields.people'() {
            this.getPeopleAddress();
        },

        'basic_fields.trn_by'() {
            this.changeAccounts();
        }
    }

};
</script>

<style lang="less">
    .check-create {
        .dropdown {
            width: 100%;
        }

        .col--account {
            width: 300px;
        }

        .col--particulars {
            width: 400px;
        }
    }
</style>
