<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Check</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <form action="" method="post" @submit.prevent="submitCheckForm">

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
                                <label>Check No<span class="wperp-required-sign">*</span></label>
                                <input type="text" v-model="basic_fields.check_no"/>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label>Payment Date<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.trn_date"></datepicker>
                            </div>

                        </div>
                        <div class="wperp-col-sm-4">
                            <label>Billing Address</label>
                            <textarea v-model.trim="basic_fields.billing_address" rows="3" class="wperp-form-field" placeholder="Type here"></textarea>
                        </div>
                        <div class="wperp-col-sm-4 with-multiselect">
                            <label>From Account<span class="wperp-required-sign">*</span></label>
                            <select-accounts v-model="basic_fields.deposit_to"></select-accounts>
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
                    <tr :key="key" v-for="(line,key) in transactionLines">
                        <td scope="row" class="col--id column-primary">{{key+1}}</td>
                        <td class="col--account with-multiselect"><multi-select v-model="line.ledger_id" :options="ledgers" /></td>
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
    import SelectPeople from 'admin/components/people/SelectPeople.vue'
    import ComboButton from 'admin/components/select/ComboButton.vue';
    import SelectAccounts from 'admin/components/select/SelectAccounts.vue'
    import ShowErrors from 'admin/components/base/ShowErrors.vue'

    export default {
        name: 'CheckCreate',

        components: {
            SelectAccounts,
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
                    people: '',
                    check_no: '',
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
                    {id: 'save', text: 'Create Check'},
                    {id: 'send_create', text: 'Create and Send'},
                    {id: 'new_create', text: 'Create and New'},
                ],

                updateButtons: [
                    {id: 'update', text: 'Update Check'},
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
                erp_acct_assets : erp_acct_var.acct_assets
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
                    this.editMode = true;
                    this.voucherNo = this.$route.params.id;

                    /**
                     * Duplicates of
                     *? this.getLedgers()
                     */
                    let request1 = await HTTP.get('/ledgers');
                    let request2 = await HTTP.get(`/expenses/${this.$route.params.id}`);

                    if ( ! request2.data.bill_details.length ) {
                        this.showAlert('error', 'Check does not exists!');
                        return;
                    }

                    if ( 'awaiting_approval' != request2.data.status ) {
                        this.showAlert('error', 'Can\'t edit');
                        return;
                    }

                    this.ledgers   = request1.data;
                    this.setDataForEdit( request2.data );

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
                }
            },

            getLedgers() {
                HTTP.get('ledgers').then((response) => {
                    this.ledgers = response.data;
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


            updateCheck(requestData) {
                HTTP.put(`/expenses/${this.voucherNo}`, requestData).then(res => {
                    this.showAlert('success', 'Check Updated!');
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

            createCheck(requestData) {
                HTTP.post('/expenses', requestData).then(res => {
                    this.showAlert('success', 'Check Created!');
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

            submitCheckForm() {
                this.validateForm();

                if ( this.form_errors.length ) {
                    return;
                }

                let requestData = {
                    people_id: this.basic_fields.people.id,
                    check_no: parseInt(this.basic_fields.check_no),
                    trn_date: this.basic_fields.trn_date,
                    trn_by: '3',
                    expense_details: this.formatTrnLines(this.transactionLines),
                    deposit_to: this.basic_fields.deposit_to.id,
                    billing_address: this.basic_fields.billing_address,
                    attachments: this.attachments,
                    type: 'check',
                    status: 'paid',
                    particulars: this.particulars,
                    name: this.check_data.payer_name
                };

                if ( this.editMode ) {
                    this.updateCheck(requestData);
                } else {
                    this.createCheck(requestData);
                }
            },

            validateForm() {
                if ( !this.basic_fields.people.hasOwnProperty('id') ) {
                    this.form_errors.push('People Name is required.');
                }

                if ( !this.basic_fields.check_no ) {
                    this.form_errors.push('Check No is required.');
                }

                if ( !this.basic_fields.trn_date ) {
                    this.form_errors.push('Transaction Date is required.');
                }

                if ( !this.basic_fields.deposit_to.hasOwnProperty('id') ) {
                    this.form_errors.push('Transaction Account is required.');
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
