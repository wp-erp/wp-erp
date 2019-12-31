<template>
    <div class="wperp-container bill-create">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ editMode ? 'Edit' : 'New' }} {{ __('Bill', 'erp') }}</h2>
                </div>
            </div>
        </div> <!-- End .header-section -->

        <form action="" method="post" @submit.prevent="submitBillForm">

            <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
                <div class="wperp-panel-body">

                    <show-errors :error_msgs="form_errors" ></show-errors>

                    <form action="" class="wperp-form" method="post">
                        <div class="wperp-row">
                            <div class="wperp-col-sm-4">
                                <div class="wperp-form-group">
                                    <select-people v-model="basic_fields.user"></select-people>
                                </div>
                            </div>
                            <div class="wperp-col-sm-4">
                                <div class="wperp-form-group">
                                    <label>{{ __('Bill Date', 'erp') }}<span class="wperp-required-sign">*</span></label>
                                    <datepicker v-model="basic_fields.trn_date"></datepicker>
                                </div>
                            </div>
                            <div class="wperp-col-sm-4">
                                <div class="wperp-form-group">
                                    <label>{{ __('Due Date', 'erp') }}<span class="wperp-required-sign">*</span></label>
                                    <datepicker v-model="basic_fields.due_date"></datepicker>
                                </div>
                            </div>
                            <div class="wperp-col-sm-6">
                                <label>{{ __('Reference No', 'erp') }}</label>
                                <input type="text" v-model="basic_fields.ref" rows="4" class="wperp-form-field" />
                            </div>
                            <div class="wperp-col-sm-6">
                                <label>{{ __('Billing Address', 'erp') }}</label>
                                <textarea v-model="basic_fields.billing_address" rows="4" class="wperp-form-field" :placeholder="__('Type here', 'erp')"></textarea>
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
                            <th scope="col" class="col--id column-primary">{{ __('SL No', 'erp') }}.</th>
                            <th scope="col">{{ __('Account', 'erp') }}</th>
                            <th scope="col">{{ __('Description', 'erp') }}</th>
                            <th scope="col">{{ __('Amount', 'erp') }}</th>
                            <th scope="col">{{ __('Total', 'erp') }}</th>
                            <th scope="col" class="col--actions"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr :key="key" v-for="(line, key) in transactionLines">
                            <td scope="row" class="col--id column-primary">{{key+1}}</td>
                            <td class="col--account with-multiselect"><multi-select v-model="line.ledger_id" :options="ledgers" /></td>
                            <td class="col--particulars"><textarea v-model="line.description" rows="1" maxlength="250" class="wperp-form-field display-flex" :placeholder="__('Particulars', 'erp')"></textarea></td>
                            <td class="col--amount" data-colname="Amount">
                                <input type="text" name="amount" v-model="line.amount" @keyup="updateFinalAmount" class="wperp-form-field text-right" :required="line.ledger_id ? true : false"/>
                            </td>
                            <td class="col--total" style="text-align: center" data-colname="Total">
                                <input type="text" class="wperp-form-field text-right" :value="moneyFormat(line.amount)" readonly disabled/>
                            </td>
                            <td class="delete-row" data-colname="Remove Above Selection">
                                <a @click.prevent="removeRow(key)" href="#"><i class="flaticon-trash"></i></a>
                            </td>
                        </tr>
                        <tr class="add-new-line">
                            <td colspan="9" style="text-align: left;">
                                <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>{{ __('Add Line', 'erp') }}</button>
                            </td>
                        </tr>

                        <tr class="total-amount-row">
                            <td class="text-right pr-0 hide-sm" colspan="4">{{ __('Total Amount', 'erp') }}</td>
                            <td class="text-right" data-colname="Total Amount">
                                <input type="text" class="wperp-form-field text-right" name="finalamount" :value="moneyFormat(finalTotalAmount)" readonly disabled/></td>
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

                        <component
                            v-for="(component, compKey) in extraFields"
                            :key="'key-' + compKey"
                            :is="component"
                            tran-type="Bill" />
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
import SelectPeople from 'admin/components/people/SelectPeople.vue';
import Datepicker from 'admin/components/base/Datepicker.vue';
import FileUpload from 'admin/components/base/FileUpload.vue';
import ShowErrors from 'admin/components/base/ShowErrors.vue';
import MultiSelect from 'admin/components/select/MultiSelect.vue';
import ComboButton from 'admin/components/select/ComboButton.vue';

export default {
    name: 'BillCreate',

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
                user           : '',
                trn_date       : '',
                due_date       : '',
                ref            : '',
                billing_address: ''
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
            attachments     : [],
            totalAmounts    : 0,
            finalTotalAmount: 0,
            particulars     : '',
            erp_acct_assets : erp_acct_var.acct_assets,
            extraFields     : window.acct.hooks.applyFilters('acctBillExtraFields', [])
        };
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
            if (this.$route.params.id) {
                this.editMode = true;
                this.voucherNo = this.$route.params.id;

                /**
                 * Duplicates of
                 *? this.getLedgers()
                */
                const expenseChartId = 5;

                const request1 = await HTTP.get(`/ledgers/${expenseChartId}/accounts`);
                const request2 = await HTTP.get(`/bills/${this.$route.params.id}`);

                if (!request2.data.bill_details.length) {
                    this.showAlert('error', 'Bill does not exists!');
                    return;
                }

                const canEdit = Boolean(Number(request2.data.editable));

                if (!canEdit) {
                    this.showAlert('error', 'Can\'t edit');
                    return;
                }

                this.ledgers = request1.data;
                this.setDataForEdit(request2.data);

                // initialize combo button id with `update`
                this.$store.dispatch('combo/setBtnID', 'update');
            } else {
                /**
                     * ----------------------------------------------
                     * create a new bill
                     * -----------------------------------------------
                     */
                this.getLedgers();

                /* global erp_acct_var */
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
            this.basic_fields.trn_date        = bill.trn_date;
            this.basic_fields.ref             = bill.ref;
            this.basic_fields.due_date        = bill.due_date;
            this.status                       = bill.status;
            this.finalTotalAmount             = bill.debit;
            this.particulars                  = bill.particulars;
            this.attachments                  = bill.attachments;

            // format transaction lines
            bill.bill_details.forEach(detail => {
                this.transactionLines.push({
                    id: detail.id,
                    ledger_id: { id: detail.ledger_id, name: detail.ledger_name },
                    description: detail.particulars,
                    amount: detail.amount
                });
            });

            this.updateFinalAmount();
        },

        getLedgers() {
            const expenseChartId = 5;
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get(`/ledgers/${expenseChartId}/accounts`).then(response => {
                this.ledgers = response.data;
                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        getPeopleAddress() {
            const peopleId = this.basic_fields.user.id;

            if (!peopleId) {
                this.basic_fields.billing_address = '';
                return;
            }

            HTTP.get(`/people/${peopleId}`).then(response => {
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

        updateBill(requestData) {
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.put(`/bills/${this.voucherNo}`, requestData).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', 'Bill Updated!');
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                this.reset = true;

                if (this.actionType === 'update' || this.actionType === 'draft') {
                    this.$router.push({ name: 'Expenses' });
                } else if (this.actionType === 'new_update') {
                    this.resetFields();
                }
            });
        },

        createBill(requestData) {
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.post('/bills', requestData).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', 'Bill Created!');
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                this.reset = true;

                if (this.actionType === 'save' || this.actionType === 'draft') {
                    this.$router.push({ name: 'Expenses' });
                } else if (this.actionType === 'new_create') {
                    this.resetFields();
                }
            });
        },

        submitBillForm() {
            this.validateForm();

            if (this.form_errors.length) {
                window.scrollTo({
                    top: 10,
                    behavior: 'smooth'
                });
                return;
            }

            let trnStatus = null;

            if (this.actionType === 'draft') {
                trnStatus = 1;
            } else {
                trnStatus = 2;
            }

            const requestData = window.acct.hooks.applyFilters('requestData', {
                vendor_id      : this.basic_fields.user.id,
                ref            : this.basic_fields.ref,
                trn_date       : this.basic_fields.trn_date,
                due_date       : this.basic_fields.due_date,
                bill_details   : this.formatTrnLines(this.transactionLines),
                attachments    : this.attachments,
                billing_address: this.basic_fields.billing_address,
                type           : 'bill',
                status         : trnStatus,
                particulars    : this.particulars
            });

            if (this.editMode) {
                this.updateBill(requestData);
            } else {
                this.createBill(requestData);
            }
        },

        resetFields() {
            this.transactionLines = [];
            this.attachments = [];
            this.totalAmounts = 0;
            this.finalTotalAmount = 0;
            this.particulars = '';
            this.form_errors = [];

            this.basic_fields = {
                user: { id: null, name: null },
                ref: '',
                trn_date: erp_acct_var.current_date,
                due_date: erp_acct_var.current_date,
                billing_address: ''
            };

            this.transactionLines.push({}, {}, {});

            // initialize combo button id with `save`
            this.$store.dispatch('combo/setBtnID', 'save');
        },

        validateForm() {
            this.form_errors = [];

            if (!Object.prototype.hasOwnProperty.call(this.basic_fields.user, 'id')) {
                this.form_errors.push('People Name is required.');
            }

            if (!this.basic_fields.trn_date) {
                this.form_errors.push('Transaction Date is required.');
            }

            if (!this.basic_fields.due_date) {
                this.form_errors.push('Due Date is required.');
            }

            if (!parseFloat(this.finalTotalAmount)) {
                this.form_errors.push('Total amount can\'t be zero.');
            }

            if (this.noFulfillLines(this.transactionLines, 'ledger_id')) {
                this.form_errors.push('Please select an account.');
            }
        },

        formatTrnLines(trnLines) {
            const lineItems = [];

            trnLines.forEach(element => {
                if (Object.prototype.hasOwnProperty.call(element, 'ledger_id')) {
                    element.ledger_id = element.ledger_id.id;
                    lineItems.push(element);
                }
            });

            return lineItems;
        },

        removeRow(index) {
            this.$delete(this.transactionLines, index);
            this.updateFinalAmount();
        }

    },

    watch: {
        finalTotalAmount(newval) {
            this.finalTotalAmount = newval;
        },

        'basic_fields.user'() {
            this.getPeopleAddress();
        }
    }

};
</script>

<style lang="less">
    .bill-create {
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
