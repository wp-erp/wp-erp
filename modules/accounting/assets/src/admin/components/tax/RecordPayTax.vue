<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Record Sales Tax Payment</h2>
                    <a href="invoice.html" id="erp-customer-new" class="wperp-btn btn--primary" data-modal="wperp-modal-content" @click.prevent="showModal = true">
                        <i class="flaticon-add-plus-button"></i>
                        <span>Pay Tax</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="wperp-panel wperp-panel-default pb-0">
	        <div class="wperp-panel-body">
	            <form action="#" class="wperp-form" method="post">
	                <div class="wperp-row wperp-gutter-20">
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group">
	                            <label>Bank Account</label>
	                            <div class="wperp-custom-select">
                                    <multi-select v-model="ledger" :options="ledgers" />
	                                <i class="flaticon-arrow-down-sign-to-navigate"></i>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group">
	                            <label>Payment To</label>
                                <multi-select v-model="agency" :options="agencies" />
	                        </div>
	                    </div>
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group">
	                            <label>Payment Date</label>
	                            <div class="wperp-has-datepicker">
                                    <datepicker v-model="trn_date"></datepicker>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group">
	                            <label>Tax Period Ending</label>
	                            <div class="wperp-has-datepicker">
                                    <datepicker v-model="tax_period"></datepicker>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group">
	                            <label>Tax Amount</label>
	                            <input type="text" v-model="tax_amount" class="wperp-form-field" placeholder="Enter Tax Amount">
	                        </div>
	                    </div>
                        <div class="wperp-col-sm-6 wperp-col-xs-12">
                            <div class="wperp-form-group">
                                <label>Voucher Type</label>
                                <input type="text" v-model="voucher_type" class="wperp-form-field" placeholder="Enter Voucher Type">
                            </div>
                        </div>
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group available-balance">
	                            <label>Available Balance: <span class="text-theme">$1,52000.00</span></label>
	                        </div>
	                    </div>
	                    <div class="wperp-col-xs-12">
	                        <label>Particulars</label>
	                        <textarea rows="3" v-model="particulars" class="wperp-form-field" placeholder="Enter Particulars"></textarea>
	                    </div>
	                    <div class="wperp-col-xs-12">
	                    	<div class="wperp-form-group text-right mt-10 mb-0">
                                <submit-button text="Pay Tax" @click.native="SubmitForTaxPay" :working="isWorking"></submit-button>
                            </div>
	                    </div>
	                </div>
	            </form>
	            
	        </div>
	    </div>


    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker from 'admin/components/base/Datepicker.vue'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'

    export default {
        name: 'RecordPayTax',

        components: {
            Datepicker,
            MultiSelect,
            MultiSelect,
            SubmitButton,
        },

        data () {
            return {
                ledgers: [],
                agencies: [],
                ledger: '',
                agency: '',
                trn_date: '',
                tax_period: '',
                voucher_type: '',
                tax_amount: 0,
                particulars: '',
                isWorking: false
            };
        },

        created() {
            this.getLedgersAgencies();
        },

        methods: {
            getLedgersAgencies() {
                this.ledgers = [
                    { id: 501, name: "Ledger 1" },
                    { id: 502, name: "Ledger 2" },
                ];

                this.agencies = [
                    { id: 101, name: "NBR" },
                    { id: 102, name: "BOE" },
                ]
            },

            SubmitForTaxPay() {
                HTTP.post('/taxes/pay-tax', {
                    ledger_id: this.ledger,
                    agency_id: this.agency,
                    trn_date: this.trn_date,
                    tax_period: this.tax_period,
                    particulars: this.particulars,
                    voucher_type: this.voucher_type,
                    amount: parseFloat(this.tax_amount),
                }).then(res => {
                    console.log(res.data);
                    this.$swal({
                        position: 'top-end',
                        type: 'success',
                        title: 'Tax Paid!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }).then(() => {
                    this.resetData();
                    this.isWorking = false;
                });
            },

            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
            },
        },

   	}
</script>
<style lang="less" scoped>
    .available-balance {
    	margin-top: -15px;
    	.text-theme {
			color: #1A9ED4;
			font-weight: 400;
			margin-left: 10px;
    	}
    }
</style>
