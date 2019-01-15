<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Tax Rates</h2>
                    <a href="invoice.html" id="erp-customer-new" class="wperp-btn btn--primary" data-modal="wperp-modal-content" @click.prevent="showModal = true">
                        <i class="flaticon-add-plus-button"></i>
                        <span>Add New</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="table-container">
	        <table class="wperp-table table-striped table-dark">
	            <thead>
		            <tr>
		                <td scope="col" class="col--check">
		                    <div class="form-check">
		                        <label class="form-check-label">
		                            <input id="cb-select-all" class="form-check-input" type="checkbox" value="">
		                            <span class="form-check-sign">
		                                <span class="check"></span>
		                            </span>
		                        </label>
		                    </div>
		                </td>
		                <th scope="col" class="col--tax-name column-primary">Tax Name</th>
		                <th scope="col" class="col--components">Components</th>
		                <th scope="col" class="col--agencies">Agencies</th>
		                <th scope="col" class="col--rate">Rate</th>
		                <th scope="col" class="col--actions"></th>
		            </tr>
	            </thead>
	            <tbody>
		            <tr>
		                <th scope="row" class="col--check">
		                    <div class="form-check">
		                        <label class="form-check-label">
		                            <input class="form-check-input" type="checkbox">
		                            <span class="form-check-sign"></span>
		                        </label>
		                    </div>
		                </th>
		                <td class="col--tax-name column-primary">
		                    
		                    <a href="#">Government Tax</a>
		                    <button type="button" class="wperp-toggle-row"><span class="screen-reader-text">Show more details</span></button>
		                </td>
		                <td class="col--components" data-colname="Components">Components name</td>
		                <td class="col--agencies" data-colname="Agencies">Donec porta, elit vestibule</td>
		                <td class="col--rate" data-colname="Rate">$24000.00</td>
		                <td class="col--actions">
		                    <div class="wperp-has-dropdown dropdown right--middle">
		                        <a href="#" class="dropdown-trigger"><i class="flaticon-menu"></i></a>
		                        <ul class="dropdown-menu" role="menu">
		                            <li><a href="#"><i class="flaticon-edit"></i>Edit</a></li>
		                            <li><a href="#"><i class="flaticon-quick-edit"></i>Create Invoice</a></li>
		                            <li><a href="#"><i class="flaticon-trash"></i>Trash</a></li>
		                        </ul>
		                    </div>
		                </td>
		            </tr>
	            </tbody>
	        </table>
	    </div>

    </div>
</template>

<script>

    export default {
        name: 'TaxRates',

        components: {

        },

        data () {
            return {
                
            };
        },
   	}
</script>
<style lang="less">
    
</style>
