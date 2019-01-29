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

        <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
            <div class="wperp-panel-body">
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
                            <label>From Account</label>
                            <select-accounts v-model="basic_fields.deposit_to"></select-accounts>
                        </div>
                        <div class="wperp-col-sm-4 with-multiselect">
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
                        <th scope="col" class="col--id column-primary">ID</th>
                        <th scope="col">Account</th>
                        <th scope="col">Description</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Total</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="key" v-for="(line,key) in transactionLines">
                        <td scope="row" class="col--id column-primary">{{key+1}}</td>
                        <td class="col--account"><multi-select v-model="line.ledger_id" :options="ledgers" /></td>
                        <td class="col--particulars"><textarea v-model="line.description" rows="1" class="wperp-form-field display-flex" placeholder="Particulars"></textarea></td>
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
                    </tbody>
                    <tr class="add-attachment-row">
                        <td colspan="9" style="text-align: left;">
                            <div class="attachment-container">
                                <label class="col--attachement">Attachment</label>
                                <file-upload v-model="attachments" url="/bills/attachments"/>
                            </div>
                        </td>
                    </tr>
                    <tfoot>
                    <tr>
                        <td colspan="9" style="text-align: right;">
                            <submit-button text="Add New Expense" @click.native="SubmitForExpense" :working="isWorking"></submit-button>
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
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import ExpenseModal from 'admin/components/expense/ExpenseModal.vue'
    import SelectPeople from 'admin/components/people/SelectPeople.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import SelectAccounts from 'admin/components/select/SelectAccounts.vue'
    import CheckFields from 'admin/components/check/CheckFields.vue'


    export default {
        name: 'ExpenseCreate',

        components: {
            SelectAccounts,
            HTTP,
            Datepicker,
            MultiSelect,
            FileUpload,
            ExpenseModal,
            SubmitButton,
            SelectPeople,
            CheckFields
        },

        data() {
            return {
                basic_fields: {
                    people: '',
                    trn_ref: '',
                    trn_date: erp_acct_var.current_date,
                    deposit_to: '',
                    trn_by: '',
                    billing_address: ''
                },

                check_data: {
                    payer_name: '',
                    check_no: ''
                },

                transactionLines: [{}],
                selected:[],
                ledgers: [],
                pay_methods: [],
                attachments: [],
                totalAmounts:[],
                finalTotalAmount: 0,
                billModal: false,
                particulars: '',
                isWorking: false,
                acct_assets: erp_acct_var.acct_assets
            }
        },

        created() {
            this.getLedgers();
            this.getPayMethods();
            this.$root.$on('remove-row', index => {
                this.$delete(this.transactionLines, index);
                this.updateFinalAmount();
            });

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

            getPeopleAddress() {
                let user_id = this.basic_fields.people.id;

                HTTP.get(`/people/${user_id}/address`).then((response) => {
                    // add more info
                    this.basic_fields.billing_address = response.data;
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


            SubmitForExpense() {
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

                HTTP.post('/expenses', {
                    people_id: this.basic_fields.people.id,
                    ref: this.basic_fields.trn_ref,
                    trn_date: this.basic_fields.trn_date,
                    trn_by: this.basic_fields.trn_by.id,
                    expense_details: this.formatTrnLines(this.transactionLines),
                    deposit_to: this.basic_fields.deposit_to.id,
                    billing_address: this.basic_fields.billing_address,
                    attachments: this.attachments,
                    type: 'expense',
                    status: 4,
                    particulars: this.particulars,
                    check_no: parseInt(this.check_data.check_no),
                    name: this.check_data.payer_name
                }).then(res => {
                    console.log(res.data);
                    console.log( this.check_data );
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Expense Created!',
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
                } )
                .then(() => {
                    this.resetData();
                    this.isWorking = false;
                });
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
