<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Record Sales Tax Payment</h2>
                    <a href="invoice.html" id="erp-customer-new" class="wperp-btn btn--primary" data-modal="wperp-modal-content" @click.prevent="showModal = true">
                        <i class="flaticon-add-plus-button"></i>
                        <span>Add New Category</span>
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
	                            <label for="bank_account">Bank Account</label>
	                            <div class="wperp-custom-select">
	                                <select name="bank_account" id="bank_account" class="wperp-form-field">
	                                    <option value="0">Select</option>
	                                    <option value="1">Option1</option>
	                                    <option value="2">Option2</option>
	                                </select>
	                                <i class="flaticon-arrow-down-sign-to-navigate"></i>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group">
	                            <label for="payment_to">Payment To</label>
	                            <input type="text" class="wperp-form-field" name="payment_to" id="payment_to" placeholder="Enter Tax Number">
	                        </div>
	                    </div>
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group">
	                            <label for="payment_date">Payment Date</label>
	                            <div class="wperp-has-datepicker">
	                                <input type="date" class="wperp-form-field datepicker" name="payment_date" id="payment_date" value="16-05-2018">
	                                <i class="flaticon-calendar datepicker-icon"></i>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group">
	                            <label for="tax_period_ending">Tax Period Ending</label>
	                            <div class="wperp-has-datepicker">
	                                <input type="date" class="wperp-form-field datepicker" name="tax_period_ending" id="tax_period_ending" value="16-05-2018">
	                                <i class="flaticon-calendar datepicker-icon"></i>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group">
	                            <label for="tax_payment">Tax Payment</label>
	                            <input type="text" class="wperp-form-field" name="tax_payment" id="tax_payment" placeholder="Enter Tax Number">
	                        </div>
	                    </div>
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group available-balance">
	                            <label>Available Balance: <span class="text-theme">$1,52000.00</span></label>
	                        </div>
	                    </div>
	                    <div class="wperp-col-xs-12">
	                        <label for="is_tax_component">Note</label>
	                        <textarea name="description" id="description" rows="3" class="wperp-form-field" placeholder="Enter Note"></textarea>
	                    </div>
	                    <div class="wperp-col-xs-12">
	                    	<div class="wperp-form-group text-right mt-10 mb-0">
	                        	<button href="#" class="wperp-btn btn--primary" type="submit"><i class="flaticon-add-plus-button"></i> Add Component</button>
	                        </div>
	                    </div>
	                </div>
	            </form>
	            
	        </div>
	    </div>


    </div>
</template>

<script>

    export default {
        name: 'TaxCategories',

        components: {

        },

        data () {
            return {
                
            };
        },
   	}
</script>
<style lang="less" scoped>
    .available-balance {
    	margin-top: 37px;
    	.text-theme {
			color: #1A9ED4;
			font-weight: 400;
			margin-left: 10px;
    	}
    }
</style>
