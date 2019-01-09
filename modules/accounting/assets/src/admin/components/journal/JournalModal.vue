<template>
    <div class="wperp-modal wperp-modal-open wperp-printable-modal" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <div class="wperp-modal-header">
                    <h1>
                        Journal
                    </h1>
                    <span class="modal-close"><i class="flaticon-close"></i></span>
                    <div class="d-print-none buttons-wrapper">
                        <a href="#" class="wperp-btn btn--default print-btn wperp-hidden-sm">
                            <i class="flaticon-printer-1"></i>
                            &nbsp; Print
                        </a>
                        <!-- todo: more action has some dropdown and will implement later please consider as planning -->
                        <div class="wperp-has-dropdown">
                            <a href="#" class="wperp-btn btn--default dropdown-trigger m0">
                                <i class="flaticon-settings-work-tool"></i>
                                &nbsp; More Action
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="#">Option1</a></li>
                                <li><a href="#">Option2</a></li>
                                <li><a href="#">Option3</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="wperp-modal-body pb-30">
                    <div class="wperp-invoice-panel">
                        <div class="invoice-body">
                            <h4 class="modal-subtitle">Journal</h4>
                            <ul class="wperp-list-unstyled">
                                <li>
                                    <strong>Voucher No:</strong>
                                    <span>30214</span>
                                </li>
                                <li>
                                    <strong>Voucher Date:</strong>
                                    <span>17-11-2018</span>
                                </li>
                                <li>
                                    <strong>Ref. No:</strong>
                                    <span>30214</span>
                                </li>
                            </ul>
                        </div>

                        <div class="wperp-invoice-table ">
                            <table class="wperp-table wperp-form-table invoice-table">
                                <thead>
                                <tr>
                                    <td class="col--check">SL.</td>
                                    <th class="column-primary">Account Name</th>
                                    <th>Description</th>
                                    <th>Debit</th>
                                    <th class="text-right">Credit</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th class="col--check">01</th>
                                    <td class="column-primary">Salary<button type="button" class="wperp-toggle-row"><span class="screen-reader-text">Show more details</span></button></td>
                                    <td data-colname="Description">Lorem ipsom dolor site amit.</td>
                                    <td data-colname="Debit">$200.00</td>
                                    <td data-colname="Credit">$0.00</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="hide-sm">
                                    <td colspan="3" class="text-right"><strong>Total = </strong></td>
                                    <td data-colname="Debit">$10000.00</td>
                                    <td data-colname="Credit">$10000.00</td>
                                </tr>
                                <tr class="show-sm is-row-expanded total-debit-credit">
                                    <th class="text-right column-primary"><strong>Total</strong></th>
                                    <td data-colname="Debit">$10000.00</td>
                                    <td data-colname="Credit">$10000.00</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- particulars -->
                        <div class="particulars pl-30 pr-30">
                            <h4 class="mb-5 mt-10">Particulars</h4>
                            <span>Lorem ipsum dolor sit amen, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.</span>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "JournalModal"
    }
</script>

<style scoped>

</style>
