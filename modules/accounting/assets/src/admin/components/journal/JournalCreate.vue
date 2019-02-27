<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">New Journal</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <div class="wperp-panel wperp-panel-default pb-0">
            <div class="wperp-panel-body">

                <show-errors :error_msgs="form_errors" ></show-errors>

                <form action="#" class="wperp-form" method="post">
                    <div class="wperp-row">
                        <div class="wperp-col-sm-6">
                            <div class="wperp-form-group">
                                <label>Journal No.</label>
                                <input type="text" :value="journal_id">
                            </div>
                        </div>
                        <div class="wperp-col-sm-6">
                            <div class="wperp-form-group">
                                <label>Transaction Date<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.trn_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-xs-12">
                            <label>Particulars</label>
                            <textarea v-model="journal_parti" rows="4" class="wperp-form-field display-flex" placeholder="Internal Information"></textarea>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <div class="wperp-table-responsive">
            <!-- Start .wperp-crm-table -->
            <div class="table-container">
                <table class="wperp-table wperp-form-table new-journal-form">
                    <thead>
                    <tr>
                        <th scope="col" class="column-primary">SL No.</th>
                        <th scope="col">Account</th>
                        <th scope="col">Particulars</th>
                        <th scope="col">Debit</th>
                        <th scope="col">Credit</th>
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
                            <input type="text" @keyup="calculateAmount(key)" v-model="debitLine[key]" class="wperp-form-field text-right">
                        </td>
                        <td class="col--credit" data-colname="Credit">
                            <input type="text" @keyup="calculateAmount(key)" v-model="creditLine[key]" class="wperp-form-field text-right">
                        </td>
                        <td class="col--actions delete-row" data-colname="Remove Selection">
                            <a href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="total-amount-row">
                        <td colspan="3" class="pl-10 text-right col--total-amount">
                            <span>Total Amount</span>
                        </td>
                        <td data-colname="Debit"><input type="text" class="text-right" :value="totalDebit" readonly ></td>
                        <td data-colname="Credit"><input type="text" class="text-right" :value="totalCredit" readonly ></td>
                        <td></td>
                    </tr>
                    <tr class="add-new-line">
                        <td colspan="9" style="text-align: left;">
                            <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add Line</button>
                        </td>
                    </tr>
                    </tbody>
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
                            <submit-button text="Create Journal" @click.native="SubmitForJournalCreate"></submit-button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- End .wperp-crm-table -->
    </div>
</template>
<script>
    import HTTP from 'admin/http'
    import Datepicker from 'admin/components/base/Datepicker.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import ShowErrors from 'admin/components/base/ShowErrors.vue'

    export default {
        name: "JournalCreate",

        components: {
            MultiSelect,
            HTTP,
            Datepicker,
            FileUpload,
            SubmitButton,
            ShowErrors
        },

        data() {
            return {
                basic_fields: {
                    journal_no: '',
                    trn_date: '',
                },

                form_errors: [],

                journal_id: 0,
                account_ids: [],
                transactionLines: [{}],
                attachments: [],
                debitLine:[],
                creditLine:[],
                ledgers:[],
                credit_total: 0,
                debit_total: 0,
                finalAmount: 0,
                journal_parti: '',
                particulars: [],
                isWorking: false,
                acct_assets: erp_acct_var.acct_assets
            }
        },

        created() {
            this.getLedgers();
            this.getNextJournalID();
        },

        methods: {
            getLedgers() {
                HTTP.get('ledgers').then((response) => {
                    response.data.forEach(element => {
                        this.ledgers.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                });
            },

            addLine() {
                this.transactionLines.push({});
            },

            SubmitForJournalCreate() {
                let validation = this.validateForm();

                if ( !validation ) {
                    window.scrollTo({
                        top: 10,
                        behavior: 'smooth'
                    });
                    return;
                }

                HTTP.post('/journals', {
                    trn_date: this.basic_fields.trans_date,
                    line_items: this.formatLineItems(),
                    attachments: this.attachments,
                    type: 'journal',
                    particulars: this.journal_parti,
                }).then(res => {
                    //console.log(res.data);
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Journal Entry Added!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }).then(() => {
                    this.isWorking = false;
                });
            },

            validateForm() {
                this.form_errors = [];

                if ( !this.basic_fields.payment_date ) {
                    this.form_errors.push('Transaction Date is required.');
                }

                if ( ! this.isWorking ) {
                    this.form_errors.push('Debit and Credit must be Equal.');
                }
            },

            calculateAmount(key) {
                if( this.debitLine[key] > 0 ) {
                    this.creditLine[key] = 0;
                } else {
                    this.debitLine[key] = 0;
                }

                let diff = Math.abs( this.debit_total - this.credit_total );

                if( diff === 0 ) {
                    this.isWorking = false;
                } else {
                    this.isWorking = true;
                }
            },

            formatLineItems() {
                var lineItems = [];

                for(let idx = 0; idx < this.transactionLines.length; idx++) {
                    let item = {};
                    item.ledger_id = this.account_ids[idx].id;
                    item.particulars = this.particulars[idx];
                    item.debit  = this.debitLine[idx];
                    item.credit = this.creditLine[idx];

                    lineItems.push( item );
                }


                return lineItems;
            },

            getNextJournalID() {
                HTTP.get(`/journals/next/`).then((response) => {
                    this.journal_id = response.data.id;
                })
            }
        },

        computed: {
            totalDebit(key) {
                return this.debit_total = this.debitLine.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
            },
            totalCredit() {
                return this.credit_total = this.creditLine.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
            }
        },

        watch: {
            isWorking( newval ) {
                this.isWorking = newval;
            }
        },


    }
</script>

<style scoped>

</style>
