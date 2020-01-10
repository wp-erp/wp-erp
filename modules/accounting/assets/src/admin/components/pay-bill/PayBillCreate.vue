<template>
    <div class="wperp-container pay-bill-create">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('New Bill Payment', 'erp') }}</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <form action="" method="post" @submit.prevent="SubmitForPayment">

        <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
            <div class="wperp-panel-body">

                <show-errors :error_msgs="form_errors"></show-errors>

                    <div class="wperp-row">
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <select-people @input="getDueBills" v-model="basic_fields.people"></select-people>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label>{{ __('Reference', 'erp') }}</label>
                                <input type="text" class="wperp-form-field" v-model="basic_fields.trn_ref"/>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label>{{ __('Payment Date', 'erp') }}<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.payment_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4 with-multiselect">
                            <label>{{ __('Payment Method', 'erp') }}</label>
                            <multi-select v-model="basic_fields.trn_by" :options="pay_methods"></multi-select>
                        </div>
                        <div class="wperp-col-sm-4">
                            <label>{{ __('Transaction From', 'erp') }}</label>
                            <select-accounts v-model="basic_fields.deposit_to" :override_accts="accts_by_chart"></select-accounts>
                        </div>
                        <div class="wperp-col-sm-4">
                            <label>{{ __('Billing Address', 'erp') }}</label>
                            <textarea v-model.trim="basic_fields.billing_address" rows="3" class="wperp-form-field" :placeholder="__('Type here', 'erp')"></textarea>
                        </div>

                        <check-fields v-if="basic_fields.trn_by.id === '3'" @updateCheckFields="setCheckFields"></check-fields>
                    </div>

            </div>
        </div>

        <div class="wperp-table-responsive">
            <!-- Start .wperp-crm-table -->
            <div class="table-container">
                <table class="wperp-table wperp-form-table">
                    <thead>
                    <tr>
                        <th scope="col" class="col--id column-primary">{{ __('Bill No', 'erp') }}</th>
                        <th scope="col">{{ __('Due Date', 'erp') }}</th>
                        <th scope="col">{{ __('Total', 'erp') }}</th>
                        <th scope="col">{{ __('Due', 'erp') }}</th>
                        <th scope="col">{{ __('Amount', 'erp') }}</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="key" v-for="(pay_bill,key) in pay_bills">
                        <td scope="row" class="col--id column-primary">#{{pay_bill.voucher_no}}</td>
                        <td class="col--due-date" data-colname="Due Date">{{pay_bill.due_date}}</td>
                        <td class="col--total" data-colname="Total">{{moneyFormat(pay_bill.amount)}}</td>
                        <td class="col--due" data-colname="Due">{{pay_bill.due}}</td>
                        <td class="col--amount text-right" data-colname="Amount">
                            <input type="text" min="0" :max="pay_bill.due" v-model="totalAmounts[key]" @keyup="updateFinalAmount" class="text-right"/>
                        </td>
                        <td class="delete-row" data-colname="Remove Above Selection">
                            <a @click.prevent="removeRow(key)" href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>

                    <tr class="total-amount-row">
                        <td class="text-right pr-0 hide-sm" colspan="4">{{ __('Total Amount', 'erp') }}</td>
                        <td class="text-right" data-colname="Total Amount">
                            <input type="text" class="text-right" :value="moneyFormat(finalTotalAmount)" readonly disabled/></td>
                        <td class="text-right"></td>
                    </tr>
                    </tbody>
                    <tr class="wperp-form-group">
                        <td colspan="9" style="text-align: left;">
                            <label>{{ __('Particulars', 'erp') }}</label>
                            <textarea v-model="particulars" rows="4" maxlength="250" class="wperp-form-field display-flex" :placeholder="__('Internal Information', 'erp')"></textarea>
                        </td>
                    </tr>
                    <tr class="add-attachment-row" >
                        <td colspan="9" style="text-align: left;">
                            <div class="attachment-container">
                                <label class="col--attachement">{{ __('Attachment', 'erp') }}</label>
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
import { mapState } from 'vuex';

import HTTP           from 'admin/http';
import Datepicker     from 'admin/components/base/Datepicker.vue';
import FileUpload     from 'admin/components/base/FileUpload.vue';
import SelectPeople   from 'admin/components/people/SelectPeople.vue';
import SelectAccounts from 'admin/components/select/SelectAccounts.vue';
import MultiSelect    from 'admin/components/select/MultiSelect.vue';
import CheckFields    from 'admin/components/check/CheckFields.vue';
import ShowErrors     from 'admin/components/base/ShowErrors.vue';
import ComboButton    from 'admin/components/select/ComboButton.vue';

export default {
    name: 'PayBillCreate',

    components: {
        SelectAccounts,
        SelectPeople,
        Datepicker,
        FileUpload,
        MultiSelect,
        CheckFields,
        ShowErrors,
        ComboButton
    },

    data() {
        return {
            basic_fields: {
                people         : {},
                trn_ref        : '',
                payment_date   : erp_acct_var.current_date, /* global erp_acct_var */
                deposit_to     : '',
                billing_address: '',
                trn_by         : ''
            },

            check_data: {
                bank_name: '',
                payer_name: '',
                check_no: ''
            },

            createButtons: [
                { id: 'save', text: 'Save' },
                { id: 'new_create', text: 'Save and New' },
                { id: 'draft', text: 'Save as Draft' }
            ],

            form_errors     : [],
            pay_bills       : [],
            attachments     : [],
            dueAmounts      : [],
            totalAmounts    : [],
            pay_methods     : [],
            finalTotalAmount: 0,
            particulars     : '',
            isWorking       : false,
            accts_by_chart  : [],
            acct_assets     : erp_acct_var.acct_assets
        };
    },

    computed: {
        ...mapState({ actionType: state => state.combo.btnID })
    },

    created() {
        this.getPayMethods();

        this.$root.$on('remove-row', index => {
            this.$delete(this.pay_bills, index);
            this.updateFinalAmount();
        });

        // initialize combo button id with `save`
        this.$store.dispatch('combo/setBtnID', 'save');
    },

    mounted() {
        this.basic_fields.people  = {
            id  : parseInt(this.$route.params.vendor_id),
            name: this.$route.params.vendor_name
        };
    },

    methods: {
        getPayMethods() {
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get('/transactions/payment-methods').then(response => {
                this.pay_methods = response.data;

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        setCheckFields(check_data) {
            this.check_data = check_data;
        },

        getDueBills() {
            this.pay_bills = [];

            const peopleId = this.basic_fields.people.id;
            let idx = 0;
            let finalAmount = 0;

            if (isNaN(peopleId)) {
                return;
            }

            HTTP.get(`/bills/due/${peopleId}`).then((response) => {
                response.data.forEach(element => {
                    if (element.due !== null && element.due > 0) {
                        this.pay_bills.push({
                            id        : element.id,
                            voucher_no: element.voucher_no,
                            due_date  : element.due_date,
                            amount    : parseFloat(element.amount),
                            due       : parseFloat(element.due)
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
            const people_id = this.basic_fields.people.id;

            if (isNaN(people_id)) {
                return;
            }

            HTTP.get(`/people/${people_id}`).then(response => {
                const billing = response.data;

                const address = `${billing.street_1} ${billing.street_2} \n${billing.city} \n${billing.state} ${billing.postal_code} \n${billing.country}`;

                this.basic_fields.billing_address = address;
            });
        },

        updateFinalAmount() {
            let finalAmount = 0;

            for (let idx = 0; idx < this.totalAmounts.length; idx++) {
                finalAmount += parseFloat(this.totalAmounts[idx]);
            }

            this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
        },

        SubmitForPayment() {
            this.validateForm();

            if (this.form_errors.length) {
                window.scrollTo({
                    top: 10,
                    behavior: 'smooth'
                });
                return;
            }

            this.pay_bills.forEach((element, index) => {
                element['amount'] = parseFloat(this.totalAmounts[index]);
            });
            this.$store.dispatch('spinner/setSpinner', true);

            let trn_status = null;
            if (this.actionType === 'draft') {
                trn_status = 1;
            } else {
                trn_status = 4;
            }

            let deposit_id = this.basic_fields.deposit_to.id;

            if (this.basic_fields.trn_by.id === 4) {
                deposit_id = this.basic_fields.deposit_to.people_id;
            }

            HTTP.post('/pay-bills', {
                vendor_id   : this.basic_fields.people.id,
                ref         : this.basic_fields.trn_ref,
                trn_date    : this.basic_fields.payment_date,
                due_date    : this.basic_fields.due_date,
                bill_details: this.pay_bills,
                attachments : this.attachments,
                type        : 'pay_bill',
                status      : trn_status,
                particulars : this.particulars,
                deposit_to  : deposit_id,
                trn_by      : this.basic_fields.trn_by.id,
                check_no    : parseInt(this.check_data.check_no),
                name        : this.check_data.payer_name,
                bank        : this.check_data.bank_name
            }).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', 'Pay-Bill Created!');

                if (this.actionType === 'save' || this.actionType === 'draft') {
                    this.$router.push({ name: 'Expenses' });
                } else if (this.actionType === 'new_create') {
                    this.resetFields();
                }
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('error', 'Something went wrong!');
                throw error;
            }).then(() => {
                this.resetFields();
                this.isWorking = false;
            });
        },

        changeAccounts() {
            this.accts_by_chart = [];
            if (this.basic_fields.trn_by.id === '2' || this.basic_fields.trn_by.id === '3') {
                HTTP.get('/ledgers/bank-accounts').then((response) => {
                    this.accts_by_chart = response.data;
                    this.accts_by_chart.forEach(element => {
                        if (!Object.prototype.hasOwnProperty.call(element, 'balance')) {
                            element.balance = 0;
                        }
                    });
                });
            } else if (this.basic_fields.trn_by.id === '1') {
                HTTP.get('/ledgers/cash-accounts').then((response) => {
                    this.accts_by_chart = response.data;
                    this.accts_by_chart.forEach(element => {
                        if (!Object.prototype.hasOwnProperty.call(element, 'balance')) {
                            element.balance = 0;
                        }
                    });
                });
                /* global erp_reimbursement_var */
            } else if (this.basic_fields.trn_by.id === '4') {
                if (erp_reimbursement_var.erp_reimbursement_module !== 'undefined' &&  erp_reimbursement_var.erp_reimbursement_module === '1') {
                    HTTP.get('/people-transactions/balances').then((response) => {
                        this.accts_by_chart = response.data;
                        this.accts_by_chart.forEach(element => {
                            if (!Object.prototype.hasOwnProperty.call(element, 'balance')) {
                                element.balance = 0;
                            }
                        });
                    });
                }
            }
            this.$root.$emit('account-changed');
        },

        validateForm() {
            this.form_errors = [];

            if (!Object.prototype.hasOwnProperty.call(this.basic_fields.people, 'id')) {
                this.form_errors.push('People Name is required.');
            }

            if (!this.basic_fields.payment_date) {
                this.form_errors.push('Transaction Date is required.');
            }

            if (!Object.prototype.hasOwnProperty.call(this.basic_fields.deposit_to, 'id')) {
                this.form_errors.push('Transaction Account is required.');
            }

            if (!Object.prototype.hasOwnProperty.call(this.basic_fields.trn_by, 'id')) {
                this.form_errors.push('Payment Method is required.');
            }

            if (parseFloat(this.basic_fields.deposit_to.balance) < parseFloat(this.finalTotalAmount)) {
                this.form_errors.push('Not enough balance in selected account.');
            }

            if (!parseFloat(this.finalTotalAmount)) {
                this.form_errors.push('Total amount can\'t be zero.');
            }
        },

        showPaymentModal() {
            this.getDueBills();
        },

        resetFields() {
            this.basic_fields = {
                people         : { id: null, name: null },
                trn_ref        : '',
                payment_date   : erp_acct_var.current_date,
                deposit_to     : '',
                billing_address: '',
                trn_by         : ''
            };

            this.check_data = {
                bank_name: '',
                payer_name: '',
                check_no: ''
            };

            this.form_errors      = [];
            this.attachments      = [];
            this.dueAmounts       = [];
            this.totalAmounts     = [];
            this.finalTotalAmount = 0;
            this.particulars      = '';
            this.isWorking        = false;

            // initialize combo button id with `save`
            this.$store.dispatch('combo/setBtnID', 'save');
        },

        removeRow(index) {
            this.$delete(this.pay_bills, index);
            this.$delete(this.totalAmounts, index);
            this.updateFinalAmount();
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

<style lang="less" scoped>
.pay-bill-create {
    input[type="text"] {
        width: 90%;
    }

    .dropdown {
        width: 100%;
    }

    .col--amount {
        width: 200px;
    }
}
</style>
