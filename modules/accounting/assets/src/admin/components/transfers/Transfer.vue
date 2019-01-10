<template>
    <div class="wperp-container">
        <div id="wperp-transfer-money-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
            <div class="wperp-modal-dialog">
                <div class="wperp-modal-content">
                    <!-- modal body title -->
                    <div class="wperp-modal-header">
                        <h3>Transfer Money</h3>
                        <span class="modal-close"><i class="flaticon-close"></i></span>
                    </div>
                    <!-- end modal body title -->
                    <form action="" method="post" class="modal-form edit-customer-modal" @submit.prevent="submitTransfer">
                        <div class="wperp-modal-body">
                            <!-- add new product form -->
                            <div class="wperp-row wperp-gutter-20">
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="transfer_funds_from">Transfer Funds From</label>
                                    <div class="wperp-custom-select with-multiselect">
                                        <multi-select id="transfer_funds_from" name="from" v-model="transferFrom" :multiple="false" :options="fa" placeholder="Select Account"></multi-select>
                                    </div>
                                    <span class="balance mt-10 display-inline-block">Balance: {{transformBalance(transferFrom.balance)}}</span>
                                </div>
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="transfer_funds_to">Transfer Funds To</label>

                                    <div class="wperp-custom-select with-multiselect">
                                        <multi-select id="transfer_funds_to" name="to" v-model="transferTo" :multiple="false" :options="ta" placeholder="Select Account"></multi-select>
                                    </div>
                                    <span class="balance mt-10 display-inline-block">Balance: {{transformBalance(transferTo.balance)}}</span>
                                </div>
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="transfer_amount">Transfer Amount</label>
                                    <input required min="0" type="number" name="transfer_amount" id="transfer_amount" class="wperp-form-field" placeholder="$100.00" v-model="amount">
                                </div>
                                <div class="wperp-form-group wperp-col-sm-6 wperp-col-xs-12">
                                    <label for="transfer_date">Transfer Date</label>
                                    <datepicker id="transfer_date" name="transfer_date" v-model="transferdate" :defaultDate="transferdate"></datepicker>
                                </div>
                                <div class="wperp-col-xs-12 wperp-form-group">
                                    <label for="transfer_memo">Memo</label>
                                    <textarea name="transfer_memo" id="transfer_memo" rows="3" class="wperp-form-field" placeholder="Type Here" v-model="remarks"></textarea>
                                </div>
                                <!--<div class="wperp-col-xs-12">-->
                                    <!--<div class="attachment-container">-->
                                        <!--<label class="col&#45;&#45;attachement">Attachment</label>-->
                                        <!--<div class="attachment-preview">-->
                                            <!--<img src="assets/images/img-thumb.png" alt="attachment image">-->
                                            <!--<i class="flaticon-close remove-attachment"></i>-->
                                        <!--</div>-->
                                        <!--<div class="attachment-placeholder">-->
                                            <!--To attach <input type="file" id="attachment" name="attachment" class="display-none"> <label class="mt-0" for="attachment">Select files</label> from your computer-->
                                        <!--</div>-->
                                    <!--</div>-->
                                <!--</div>-->
                            </div>

                        </div>

                        <div class="wperp-modal-footer pt-0">
                            <button class="wperp-btn btn--primary" type="submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import MultiSelect from "admin/components/select/MultiSelect.vue";
    import HTTP from 'admin/http';
    import Datepicker from 'admin/components/base/Datepicker.vue';
    export default {
        name: "Transfer",
        components: { MultiSelect, HTTP, Datepicker },

        data() {
            return {
                transferFrom: { balance : 0 },
                transferTo: { balance : 0 },
                accounts: [],
                fa: [],
                ta: [],
                transferdate: erp_acct_var.current_date,
                remarks : '',
                amount: '',

            };
        },

        created(){
            this.fetchAccounts();
        },

        methods: {
            fetchAccounts(){
                HTTP.get('transfer-voucher').then( (response) => {
                    this.accounts = response.data;
                    this.fa = response.data;
                    this.ta = response.data;

                } );
            },

            transformBalance( val ){
                let currency = '$';
                if ( val < 0 ){
                    return `Cr. ${currency} ${Math.abs(val)}`;
                }

                return `Dr. ${currency} ${val}`;
            },

            submitTransfer(){
                HTTP.post( '/transfer-voucher/transfer', {
                    date : this.transferdate,
                    from_account_id : this.transferFrom.id,
                    to_account_id : this.transferTo.id,
                    amount : this.amount,
                    remarks : this.remarks,
                } ).then( res => {
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Transfer Successful!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    this.fetchAccounts();
                    this.resetData();
                } ).catch( err => {
                    let msg = err.response.data.message;
                    this.$swal({
                        position: 'center',
                        type: 'error',
                        title: msg,
                        showConfirmButton: true,
                        timer: 0
                    });
                } );
            },

            resetData(){
                this.transferFrom = { balance : 0 };
                this.transferTo = { balance : 0 };
                this.accounts = [];
                this.transferdate = erp_acct_var.current_date;
                this.remarks = '';
                this.amount = '';
            }
        },

        watch: {
            'transferFrom'(){
                let id = this.transferFrom.id;
                this.ta = jQuery.grep(this.accounts, function(e){
                    return e.id != id;
                });
            },
            'transferTo'(){
                let id = this.transferTo.id;
                this.fa = jQuery.grep(this.accounts, function(e){
                    return e.id != id;
                });
            }

        }
    }
</script>

<style lang="less">
    .wperp-modal {
        z-index: 999 !important;
    }
</style>
