<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">New Invoice</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
            <div class="wperp-panel-body">
                <form action="#" class="wperp-form" method="post">
                    <div class="wperp-row">
                        <invoice-customers></invoice-customers>

                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label for="trans_date">Transaction Date<span class="wperp-required-sign">*</span></label>
                                <input type="date" class="wperp-form-field datepicker" name="trans_date" id="trans_date" placeholder="16-05-2018">
                            </div>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label for="due_date">Due Date<span class="wperp-required-sign">*</span></label>
                                <input type="date" class="wperp-form-field datepicker" name="due_date" id="due_date" placeholder="16-05-2018">
                            </div>
                        </div>
                        <div class="wperp-col-xs-12">
                            <label for="billing_address">Billing Address</label>
                            <textarea name="billing_address" id="billing_address" rows="4" class="wperp-form-field" placeholder="Type here"></textarea>
                        </div>
                    </div>
                </form>
                
            </div>
        </div>

    <!--  <div class="wperp-panel wperp-panel-primary">
            <div class="wperp-panel-heading">
                
            </div>
        </div> -->

        <div class="wperp-table-responsive">
            <!-- Start .wperp-crm-table -->
            <div class="table-container">
                <table class="wperp-table wperp-form-table">
                    <thead>
                        <tr>
                            <td scope="col" class="col--check">Product/Service</td>
                            <th scope="col" class="column-primary">Qty</th>
                            <th scope="col">Unit Price</th>
                            <th scope="col">Discount</th>
                            <th scope="col">Tax(%)</th>
                            <th scope="col">Tax Amount</th>
                            <th scope="col">Amount</th>
                            <th scope="col" class="col--actions"></th>
                        </tr>
                    </thead>
                    <tbody id="test">
                        <tr>
                            <th scope="row" class="col--check">
                                <select name="pen-holder" id="pen-holder" class="wperp-form-field wperp-is-select2">
                                    <option value="0">Select</option>
                                    <option value="1">Pen Holder</option>
                                    <option value="2">Pen Holder</option>
                                </select>
                            </th>
                            <td class="col--qty column-primary">
                                <input type="number" name="qty" id="qty" class="wperp-form-field" value="1">
                            </td>
                            <td class="col--uni_price" data-colname="Unit Price">
                                <input type="text" name="unit-price" id="unit-price" class="wperp-form-field" value="5000">
                            </td>
                            <td class="col--discount" data-colname="Discount">
                                <div class="wperp-has-addon">
                                    <input type="text" name="discount" id="discount" class="wperp-form-field" value="1">
                                    <span class="wperp-addon">%</span>
                                </div>
                            </td>
                            <td class="col--penholder" data-colname="Tax(%)">
                                <div class="wperp-custom-select">
                                    <select name="pen-holder" id="pen-holder" class="wperp-form-field">
                                        <option value="0">Select</option>
                                        <option value="1">Gov</option>
                                        <option value="2">Private</option>
                                    </select>
                                    <i class="flaticon-arrow-down-sign-to-navigate"></i>
                                </div>
                            </td>
                            <td class="col--tax-amount" data-colname="Tax Amount">
                                <input type="text" name="tax-amount" id="tax-amount" class="wperp-form-field" value="$240.00">
                            </td>
                            <td class="col--amount" data-colname="Amount">
                                <input type="text" name="amount" id="amount" class="wperp-form-field" value="$24022">
                            </td>
                            <td class="col--actions delete-row" data-colname="Action">
                                <span class="wperp-btn"><i class="flaticon-trash"></i></span>
                            </td>
                        </tr>
                        <tr class="total-amount-row">
                            <td colspan="6" class="text-right">
                                <span>Total Amount = </span>
                            </td>
                            <td><input type="text" class="" value="123456" readonly ></td>
                            <td></td>
                        </tr>
                        <tr class="add-new-line">
                            <td colspan="9" style="text-align: left;">
                                <button class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add Line</button>
                            </td>
                        </tr>
                        <tr class="add-attachment-row" >
                            <td colspan="9" style="text-align: left;">
                                <div class="attachment-container">
                                    <label class="col--attachement">Attachment</label>
                                    <div class="attachment-placeholder">
                                        To attach <input type="file" id="attachment" name="attachment" class="display-none"> <label for="attachment">Select files</label> from your computer
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"><button class="wperp-btn btn--default">Cancel</button></td>
                            <td colspan="5" style="text-align: right;">
                                <div class="wperp-has-dropdown">
                                    <button class="wperp-btn btn--primary">Submit for approval</button>
                                    <div class="dropdown-menu">
                                        <span>something</span>
                                    </div>
                                </div>
                                <button class="wperp-btn btn--default wperp-dropdown-trigger">Cancel</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div id="wperp-invoice-modal" class="wperp-modal wperp-invoice-modal wperp-custom-scroll" role="dialog">
                <div class="wperp-modal-dialog">
                    <div class="wperp-modal-content">
                        <div class="wperp-modal-header">
                            <h4>
                                Invoice
                            </h4>
                            <div class="d-print-none">
                                <a href="#" class="wperp-btn btn--default print-btn">
                                    <i class="flaticon-printer-1"></i>
                                    &nbsp; Print
                                </a>
                                <!-- todo: more action has some dropdown and will implement later please consider as planning -->
                                <a href="#" class="wperp-btn btn--default">
                                    <i class="flaticon-settings-work-tool"></i>
                                    &nbsp; More Action
                                </a>
                            </div>
                        </div>
                        <div class="wperp-modal-body">
                            <div class="wperp-invoice-panel">
                                <div class="invoice-header">
                                    <div class="invoice-logo">
                                        <img src="assets/images/dummy-logo.png" alt="logo name">
                                    </div>
                                    <div class="invoice-address">
                                        <address>
                                            <strong>Amazon Limited</strong><br>
                                            983 Aiden Roads Suite 062<br>
                                            Address Line 2<br>
                                            1483 Theresafort Afyon<br>
                                            Turkey
                                        </address>
                                    </div>
                                </div>

                                <div class="invoice-body">
                                    <h4>Invoice</h4>
                                    <div class="wperp-row">
                                        <div class="wperp-col-sm-6">
                                            <h5>Bill to:</h5>
                                            <div class="persons-info">
                                                <strong>Md Ashraf Hossain</strong><br>
                                                983 Aiden Roads Suite 062<br>
                                                Address Line 2<br>
                                                1483 Theresafort Afyon<br>
                                                Turkey
                                            </div>
                                        </div>
                                        <div class="wperp-col-sm-6">
                                            <table class="invoice-info">
                                                <tr>
                                                    <th>Invoice No.</th>
                                                    <td>INV-0001</td>
                                                </tr>
                                                <tr>
                                                    <th>Invoice Date:</th>
                                                    <td>17-10-2018</td>
                                                </tr>
                                                <tr>
                                                    <th>Due Date:</th>
                                                    <td>17-10-2018</td>
                                                </tr>
                                                <tr>
                                                    <th>Amount Due:</th>
                                                    <td>17-10-2018</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="wperp-invoice-table">
                                    <table class="wperp-table wperp-form-table invoice-table">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th>City</th>
                                                <th>Unit Price</th>
                                                <th>Discount</th>
                                                <th>Tax</th>
                                                <th>Tax Amount</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th>Buy Fabrics</th>
                                                <td>10</td>
                                                <td>$1500.00</td>
                                                <td>10%</td>
                                                <td>0%</td>
                                                <td>$0.00</td>
                                                <td>$15,000.00</td>
                                            </tr>
                                            <tr>
                                                <th>Buy Fabrics</th>
                                                <td>10</td>
                                                <td>$1500.00</td>
                                                <td>10%</td>
                                                <td>0%</td>
                                                <td>$0.00</td>
                                                <td>$15,000.00</td>
                                            </tr>
                                            <tr>
                                                <th>Buy Fabrics</th>
                                                <td>10</td>
                                                <td>$1500.00</td>
                                                <td>10%</td>
                                                <td>0%</td>
                                                <td>$0.00</td>
                                                <td>$15,000.00</td>
                                            </tr><tr>
                                                <th>Buy Fabrics</th>
                                                <td>10</td>
                                                <td>$1500.00</td>
                                                <td>10%</td>
                                                <td>0%</td>
                                                <td>$0.00</td>
                                                <td>$15,000.00</td>
                                            </tr><tr>
                                                <th>Buy Fabrics</th>
                                                <td>10</td>
                                                <td>$1500.00</td>
                                                <td>10%</td>
                                                <td>0%</td>
                                                <td>$0.00</td>
                                                <td>$15,000.00</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="7">
                                                    <ul>
                                                        <li><span>Subtotal:</span> $15,000.00</li>
                                                        <li><span>Total:</span> $15,000.00</li>
                                                        <li><span>Total Related Payments:</span> $15,000.00</li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                            </div>
                            <div class="invoice-attachments d-print-none">
                                <h4>Attachments</h4>
                                <a class="attachment-item" href="#">
                                    <img src="assets/images/img-thumb.png" alt="image name">
                                    <div class="attachment-meta">
                                        <span>File name with extension</span><br>
                                        <span class="text-muted">file size</span>
                                    </div>
                                </a>
                                <a class="attachment-item" href="#">
                                    <img src="assets/images/doc-thumb.png" alt="image name">
                                    <div class="attachment-meta">
                                        <span>File name with extension</span><br>
                                        <span class="text-muted">file size</span>
                                    </div>
                                </a>
                                <a class="attachment-item" href="#">
                                    <img src="assets/images/pdf-thumb.png" alt="image name">
                                    <div class="attachment-meta">
                                        <span>File name with extension</span><br>
                                        <span class="text-muted">file size</span>
                                    </div>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        <!-- End .wperp-crm-table -->
    </div>
</template>

<script>
import InvoiceCustomers from './InvoiceCustomers.vue'

export default {
    name: 'InvoiceCreate',

    components: {
        InvoiceCustomers
    }
}
</script>

<style scoped>

</style>