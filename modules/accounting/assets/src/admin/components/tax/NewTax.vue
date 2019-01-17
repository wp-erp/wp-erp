<template>
    <div id="new-tax-rate">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Add New Tax</h2>
                </div>
            </div>
        </div>
        <form action="#" class="wperp-form" method="post">

	        <div class="wperp-panel wperp-panel-default pb-0">
		        <div class="wperp-panel-body">
	                <div class="wperp-row wperp-gutter-20">
	                    <div class="wperp-col-sm-6 wperp-col-xs-12">
	                        <div class="wperp-form-group">
	                            <label for="tax_name">Tax Name</label>
	                            <div class="wperp-custom-select">
	                                <select name="tax_name" id="tax_name" class="wperp-form-field">
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
	                            <label for="tax_number">Tax Number</label>
	                            <input type="text" class="wperp-form-field" name="tax_number" id="tax_number" placeholder="Enter Tax Number">
	                        </div>
	                    </div>
	                    <div class="wperp-col-xs-12">
	                        <label for="is_tax_component">Tax Components</label>
	                        <div class="form-check">
		                        <label class="form-check-label">
		                            <input class="form-check-input" type="checkbox" id="is_tax_component" name="is_tax_component">
		                            <span class="form-check-sign"></span>
		                        	<span class="field-label">Is this tax components?</span>
		                        </label>
		                    </div>
	                    </div>
	                </div>
		        </div>
		    </div>

	        <div class="table-container">
		        <table class="wperp-table wperp-form-table new-journal-form">
	                <thead>
	                    <tr>
	                        <th scope="col" class="column-primary">Component Name</th>
	                        <th scope="col">Agency</th>
	                        <th scope="col">Tax Category</th>
	                        <th scope="col">Tax Rate</th>
	                        <th scope="col" class="col--actions"></th>
	                    </tr>
	                </thead>
	                <tbody>
	                    <tr>
	                        <td scope="row" class="col--component-name column-primary">
	                            <input type="text" class="wperp-form-field" placeholder="Component Name">
	                            <button type="button" class="wperp-toggle-row"><span class="screen-reader-text">Show more details</span></button>
	                        </td>
	                        <td class="col--agency" data-colname="Agency">
	                            <input type="text" class="wperp-form-field" placeholder="Agency">
	                        </td>
	                        <td class="col--tax-category" data-colname="Tax Category">
	                            <div class="wperp-custom-select">
	                                <select name="tax_category" class="wperp-form-field">
	                                    <option value="0">Select</option>
	                                    <option value="1">Option1</option>
	                                    <option value="2">Option2</option>
	                                </select>
	                                <i class="flaticon-arrow-down-sign-to-navigate"></i>
	                            </div>
	                        </td>
	                        <td class="col--tax-rate" data-colname="Tax Rate">
	                            <input type="text" class="wperp-form-field text-right" value="$35.00">
	                        </td>
	                        <td class="col--actions delete-row" data-colname="Remove Above Selection">
	                            <a href="#"><i class="flaticon-trash"></i></a>
	                        </td>
	                    </tr>
	                    <tr>
	                        <td scope="row" class="col--component-name column-primary">
	                            <input type="text" class="wperp-form-field" placeholder="Component Name">
	                            <button type="button" class="wperp-toggle-row"><span class="screen-reader-text">Show more details</span></button>
	                        </td>
	                        <td class="col--agency" data-colname="Agency">
	                            <input type="text" class="wperp-form-field" placeholder="Agency">
	                        </td>
	                        <td class="col--tax-category" data-colname="Tax Category">
	                            <div class="wperp-custom-select">
	                                <select class="wperp-form-field">
	                                    <option value="0">Select</option>
	                                    <option value="1">Option1</option>
	                                    <option value="2">Option2</option>
	                                </select>
	                                <i class="flaticon-arrow-down-sign-to-navigate"></i>
	                            </div>
	                        </td>
	                        <td class="col--tax-rate" data-colname="Tax Rate">
	                            <input type="text" class="wperp-form-field text-right" value="$35.00">
	                        </td>
	                        <td class="col--actions delete-row" data-colname="Remove Above Selection">
	                            <a href="#"><i class="flaticon-trash"></i></a>
	                        </td>
	                    </tr>
	                    <tr>
	                        <td scope="row" class="col--component-name column-primary">
	                            <input type="text" class="wperp-form-field" placeholder="Component Name">
	                            <button type="button" class="wperp-toggle-row"><span class="screen-reader-text">Show more details</span></button>
	                        </td>
	                        <td class="col--agency" data-colname="Agency">
	                            <input type="text" class="wperp-form-field" placeholder="Agency">
	                        </td>
	                        <td class="col--tax-category" data-colname="Tax Category">
	                            <div class="wperp-custom-select">
	                                <select class="wperp-form-field">
	                                    <option value="0">Select</option>
	                                    <option value="1">Option1</option>
	                                    <option value="2">Option2</option>
	                                </select>
	                                <i class="flaticon-arrow-down-sign-to-navigate"></i>
	                            </div>
	                        </td>
	                        <td class="col--tax-rate" data-colname="Tax Rate">
	                            <input type="text" class="wperp-form-field text-right" value="$35.00">
	                        </td>
	                        <td class="col--actions delete-row" data-colname="Remove Above Selection">
	                            <a href="#"><i class="flaticon-trash"></i></a>
	                        </td>
	                    </tr>
	                    <tr class="add-new-line">
	                        <td colspan="9" class="text-left">
	                            <button class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add Component</button>
	                        </td>
	                    </tr>
	                </tbody>
	                <tfoot>
	                    <tr>
	                        <td colspan="6" class="text-right">
	                            <button class="wperp-btn btn--primary wperp-dropdown-trigger">Add Entry</button>
	                        </td>
	                    </tr>
	                </tfoot>
	            </table>
		    </div>

		</form>

    </div>
</template>

<script>

    export default {
        name: 'NewTax',

        components: {

        },

        data () {
            return {
                
            };
        },
   	}
</script>
<style lang="less" scoped>
    input.wperp-form-field {
    	height: 37px;
    }
    .col--tax-rate, .col--tax-category {
    	width: 15%;
    }
    .wperp-form-table tfoot tr td {
    	border-top: 1px solid #e2e2e2;
    }
    .wperp-form-table .add-new-line td {
    	padding-top: 10px !important;
    }
    @media (max-width: 782px){
    	.wperp-form-table {
    		td {
    			padding-top: 10px !important;
    			&.column-primary {
    				input {
    					width: 92%;
    				}
    			}
    			&.col--actions.delete-row {
    				text-align: right;
    				&:before {
    					text-align: left;
    				}
    			}
    		}
    		.wperp-toggle-row {
    			top: 23px;
    		}
    	}
    }
</style>
