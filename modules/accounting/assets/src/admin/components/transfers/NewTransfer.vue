<template>
    <div class="wperp-container">
        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Transfer Money</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->
        <div class="wperp-panel wperp-panel-default pb-0">
            <div class="wperp-panel-body">
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
                                <datepicker id="transfer_date" name="transfer_date" v-model="transferdate"></datepicker>
                            </div>
                            <div class="wperp-col-xs-12 wperp-form-group">
                                <label for="particulars">Particulars</label>
                                <textarea name="particulars" id="particulars" rows="3" class="wperp-form-field" placeholder="Type Here" v-model="particulars"></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="wperp-modal-footer pt-0">
                        <button class="wperp-btn btn--primary" type="submit">Transfer Money</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</template>

<script>
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import HTTP from 'admin/http'
    import Datepicker from 'admin/components/base/Datepicker.vue'

    export default {
        name: "Transfer",
        components: {
            MultiSelect,
            HTTP,
            Datepicker
        },

        data() {
            return {
                transferFrom: { balance : 0 },
                transferTo: { balance : 0 },
                accounts: [],
                fa: [],
                ta: [],
                transferdate: erp_acct_var.current_date,
                particulars : '',
                amount: '',
            };
        },

        created(){
            this.fetchAccounts();
        },

        mounted() {
            // `transfer` request from account list row action
            if ( this.$route.params.ac_id ) {
                this.transferFrom  = {
                    id  : parseInt(this.$route.params.ac_id),
                    name: this.$route.params.ac_name
                };
            }
        },
        
        methods: {
            fetchAccounts() {
                HTTP.get('accounts').then( (response) => {
                    this.accounts = response.data;
                    this.fa = response.data;
                    this.ta = response.data;
                } );
            },

            transformBalance( val ) {
                let currency = '$';
                if ( val < 0 ){
                    return `Cr. ${currency} ${Math.abs(val)}`;
                }
                return `Dr. ${currency} ${val}`;
            },

            submitTransfer(){
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.post( '/accounts/transfer', {
                    date : this.transferdate,
                    from_account_id : this.transferFrom.id,
                    to_account_id : this.transferTo.id,
                    amount : this.amount,
                    particulars : this.particulars,
                }).then( res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Transfer Successful!' );
                    this.fetchAccounts();
                    this.resetData();
                    this.$router.push('/transfers');
                }).catch( err => {
                    let msg = err.response.data.message;
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'error', msg );
                });
            },

            resetData(){
                this.transferFrom = { balance : 0 };
                this.transferTo = { balance : 0 };
                this.accounts = [];
                this.transferdate = erp_acct_var.current_date;
                this.particulars = '';
                this.amount = '';
            },
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
