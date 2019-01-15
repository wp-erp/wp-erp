<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Tax Categories</h2>
                    <a href="invoice.html" id="erp-customer-new" class="wperp-btn btn--primary" data-modal="wperp-modal-content" @click.prevent="showModal = true">
                        <i class="flaticon-add-plus-button"></i>
                        <span>Add New Category</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="table-container">
	        <table class="wperp-table table-striped table-dark">
	            <thead>
		            <tr>
		                <th scope="col" class="col--name column-primary">Category Name</th>
		                <th scope="col" class="col--description">Description</th>
		                <th scope="col" class="col--actions"></th>
		            </tr>
	            </thead>
	            <tbody>
		            <tr>
		                <td class="col--name column-primary">
		                    Category Name
		                    <button type="button" class="wperp-toggle-row"><span class="screen-reader-text">Show more details</span></button>
		                </td>
		                <td class="col--description" data-colname="Description">Donec porta, elit vestibulum vulputate bibendum, sem felis varius quam.</td>
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
		            <tr>
		                <td class="col--name column-primary">
		                    Category Name
		                    <button type="button" class="wperp-toggle-row"><span class="screen-reader-text">Show more details</span></button>
		                </td>
		                <td class="col--description" data-colname="Description">Donec porta, elit vestibulum vulputate bibendum, sem felis varius quam.</td>
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


	    <!-- add tax category modal -->

	    <!--  add this (wperp-modal-open) class to .wperp-modal for toggle modal -->
	    <div id="wperp-tax-category-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
	        <div class="wperp-modal-dialog">
	            <div class="wperp-modal-content">
	            	<!-- modal body title -->
	                <div class="wperp-modal-header">
	                    <h3>Add Tax Category</h3>
	                    <span class="modal-close"><i class="flaticon-close"></i></span>
	                </div>
	                <!-- end modal body title -->
					<form action="" method="post" class="modal-form edit-customer-modal">
	                	<div class="wperp-modal-body">
	                        
	                        <div class="wperp-form-group">
	                        	<label for="tax_ctg_name">Tax Category Name</label>
	                        	<div class="wperp-custom-select">
	                                <select name="tax_ctg_name" id="tax_ctg_name" class="wperp-form-field wperp-is-select2">
	                                    <option value="0">Select</option>
	                                    <option value="1">Pen Holder</option>
	                                    <option value="2">Pen Holder</option>
	                                </select>
	                                <i class="flaticon-arrow-down-sign-to-navigate"></i>
	                            </div>
	                        </div>

	                        <div class="wperp-form-group mb-0">
	                        	<label for="description">Description</label>
	                        	<textarea name="description" id="description" rows="4" class="wperp-form-field" placeholder="Type here"></textarea>
	                        </div>

	                	</div>

	                	<div class="wperp-modal-footer pt-0">
	                		<!-- buttons -->
	                        <div class="buttons-wrapper text-right">
	                            <button class="wperp-btn btn--primary" type="submit">Add Tax Category</button>
	                            <button class="wperp-btn btn--default modal-close" type="reset">Cancel</button>
	                        </div>
	                	</div>
	                </form>	
	            </div>
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
<style lang="less">
    
</style>
