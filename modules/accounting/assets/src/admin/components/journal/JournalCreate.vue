<template>
    <div class="wperp-container journal-create">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('New Journal', 'erp') }}</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <form action="" method="post" @submit.prevent="SubmitForJournalCreate">

        <div class="wperp-panel wperp-panel-default pb-0">
            <div class="wperp-panel-body">

                <show-errors :error_msgs="form_errors" ></show-errors>

                <div class="wperp-row">
                    <div class="wperp-col-sm-4">
                        <div class="wperp-form-group">
                            <label>{{ __('Transaction Date', 'erp') }}<span class="wperp-required-sign">*</span></label>
                            <datepicker v-model="basic_fields.trn_date"></datepicker>
                        </div>
                    </div>
                    <div class="wperp-col-sm-4">
                        <div class="wperp-form-group">
                            <label>{{ __('Ref.', 'erp') }}</label>
                            <input type="text" class="wperp-form-field" v-model="basic_fields.trn_ref">
                        </div>
                    </div>
                    <div class="wperp-col-sm-4">
                        <label>{{ __('Particulars', 'erp') }}</label>
                        <textarea v-model="journal_parti" rows="1" class="wperp-form-field display-flex" :placeholder="__('Internal Information', 'erp')"></textarea>
                    </div>
                </div>

            </div>
        </div>

        <div class="wperp-table-responsive">
            <!-- Start .wperp-crm-table -->
            <div class="table-container">
                <table class="wperp-table wperp-form-table new-journal-form">
                    <thead>
                    <tr>
                        <th scope="col" class="column-primary">{{ __('SL No.', 'erp') }}</th>
                        <th scope="col">{{ __('Account', 'erp') }}</th>
                        <th scope="col">{{ __('Particulars', 'erp') }}</th>
                        <th scope="col">{{ __('Debit', 'erp') }}</th>
                        <th scope="col">{{ __('Credit', 'erp') }}</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="key" v-for="(line,key) in transactionLines">
                        <td scope="row" class="column-primary">
                            {{ key+1 }}
                        </td>
                        <td class="col--account" data-colname="Account">
                            <div class="wperp-custom-select with-multiselect">
                               <multi-select v-model="account_ids[key]" :options="ledgers"></multi-select>
                            </div>
                        </td>
                        <td class="col--particulars" data-colname="Particulars">
                            <input type="text" v-model="particulars[key]" class="wperp-form-field">
                        </td>
                        <td class="col--debit" data-colname="Debit">
                            <input type="text" @keyup="calculateAmount(key)" v-model="debitLine[key]" class="wperp-form-field text-right" :required="(Number(creditLine[key]) || 0) === 0 ? true : false">
                        </td>
                        <td class="col--credit" data-colname="Credit">
                            <input type="text" @keyup="calculateAmount(key)" v-model="creditLine[key]" class="wperp-form-field text-right" :required="(Number(debitLine[key]) || 0) === 0 ? true : false">
                        </td>
                        <td class="col--actions delete-row" data-colname="Remove Selection">
                            <a href="#" @click.prevent="remove_item(key)"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="add-new-line">
                        <td colspan="9" style="text-align: left;">
                            <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>{{ __('Add Line', 'erp') }}</button>
                        </td>
                    </tr>
                    <tr class="total-amount-row">
                        <td colspan="3" class="pl-10 text-right col--total-amount">
                            <span>{{ __('Total Amount', 'erp') }}</span>
                        </td>
                        <td data-colname="Debit">
                            <input type="text" class="wperp-form-field text-right"
                            :value="isNaN(totalDebit()) ? debit_total : moneyFormat(totalDebit())" readonly>
                        </td>
                        <td data-colname="Credit">
                            <input type="text" class="wperp-form-field text-right"
                            :value="isNaN(totalCredit()) ? credit_total: moneyFormat(totalCredit())" readonly>
                        </td>
                        <td></td>
                    </tr>
                    </tbody>
                    <tr class="add-attachment-row">
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
                            <submit-button :text="__( 'Save', 'erp' )"></submit-button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        </form>
        <!-- End .wperp-crm-table -->
    </div>
</template>
<script>
import HTTP from 'admin/http';
import Datepicker from 'admin/components/base/Datepicker.vue';
import FileUpload from 'admin/components/base/FileUpload.vue';
import SubmitButton from 'admin/components/base/SubmitButton.vue';
import MultiSelect from 'admin/components/select/MultiSelect.vue';
import ShowErrors from 'admin/components/base/ShowErrors.vue';

export default {
    name: 'JournalCreate',

    components: {
        MultiSelect,
        Datepicker,
        FileUpload,
        SubmitButton,
        ShowErrors
    },

    data() {
        return {
            basic_fields: {
                journal_no: '',
                trn_ref: '',
                trn_date: erp_acct_var.current_date
            },
            form_errors     : [],
            journal_id      : 0,
            account_ids     : [],
            transactionLines: [{}, {}],
            attachments     : [],
            debitLine       : [],
            creditLine      : [],
            ledgers         : [],
            credit_total    : 0,
            debit_total     : 0,
            finalAmount     : 0,
            journal_parti   : '',
            particulars     : [],
            isWorking       : false,
            acct_assets     : erp_acct_var.acct_assets
        };
    },

    created() {
        this.getLedgers();
        this.getNextJournalID();
    },

    methods: {
        getLedgers() {
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get('ledgers').then((response) => {
                this.ledgers = response.data;

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        addLine() {
            this.transactionLines.push({});
        },

        remove_item(index) {
            this.$delete(this.transactionLines, index);
        },

        SubmitForJournalCreate() {
            this.validateForm();

            if (this.form_errors.length) {
                window.scrollTo({
                    top: 10,
                    behavior: 'smooth'
                });

                return;
            }

            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.post('/journals', {
                trn_date   : this.basic_fields.trn_date,
                ref        : this.basic_fields.trn_ref,
                line_items : this.formatLineItems(),
                attachments: this.attachments,
                type       : 'journal',
                particulars: this.journal_parti
            }).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', 'Journal Entry Added!');
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                this.isWorking = false;
                this.$router.push({ name: 'Journals' });
            });

            this.resetFields();
        },

        validateForm() {
            this.form_errors = [];

            if (this.account_ids.length < 2) {
                this.form_errors.push('Accounts are required.');
            }

            if (!this.basic_fields.trn_date) {
                this.form_errors.push('Transaction Date is required.');
            }

            if (!this.debit_total) {
                this.form_errors.push('Total amount can\'t be zero.');
            }

            if (this.isWorking) {
                this.form_errors.push('Debit and Credit must be Equal.');
            }
        },

        calculateAmount(key) {
            if (this.debitLine[key] > 0) {
                this.creditLine[key] = 0;
            } else {
                this.debitLine[key] = 0;
            }
            this.debit_total = this.debitLine.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
            this.credit_total = this.creditLine.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);

            const diff = Math.abs(this.debit_total - this.credit_total);
            this.isWorking = true;
            if (diff === 0) {
                this.isWorking = false;
            }
        },

        formatLineItems() {
            var lineItems = [];

            for (let idx = 0; idx < this.transactionLines.length; idx++) {
                const item = {};
                item.ledger_id = this.account_ids[idx].id;
                item.particulars = this.particulars[idx];
                item.debit  = this.debitLine[idx];
                item.credit = this.creditLine[idx];

                lineItems.push(item);
            }

            return lineItems;
        },

        getNextJournalID() {
            HTTP.get(`/journals/next/`).then((response) => {
                this.journal_id = response.data.id;
            });
        },

        resetFields() {
            this.basic_fields.journal_no = { id: null, name: null };
            this.basic_fields.trn_date   = erp_acct_var.current_date; /* global erp_acct_var */
            this.attachments             = [];
            this.transactionLines        = [{}, {}];
            this.isWorking               = false;
            this.debitLine               = [];
            this.creditLine              = [];
            this.ledgers                 = [];
            this.credit_total            = 0;
            this.debit_total             = 0;
            this.finalAmount             = 0;
            this.journal_parti           = '';
            this.particulars             = [];
        },

        totalDebit() {
            this.debit_total = this.debitLine.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
            return this.debit_total;
        },

        totalCredit() {
            this.creditLine.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
            return this.credit_total;
        }
    },

    watch: {
        isWorking(newval) {
            this.isWorking = newval;
        }
    }

};
</script>

<style lang="less" scoped>
    .journal-create {
        input[type="text"] {
            width: 90%;
        }

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
