<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Receive Payment</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
            <div class="wperp-panel-body">
                <form action="#" class="wperp-form" method="post">
                    <div class="wperp-row">
                        <div class="wperp-col-sm-3">
                            <div class="wperp-form-group">
                                <invoice-customers v-model="basic_fields.customer"></invoice-customers>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3">
                            <div class="wperp-form-group">
                                <label for="reference">Reference<span class="wperp-required-sign">*</span></label>
                                <input type="text" v-model="basic_fields.trn_ref"></input>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3">
                            <div class="wperp-form-group">
                                <label for="payment_date">Payment Date<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.payment_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-sm-3">
                            <label for="deposit_to">Deposit to</label>
                            <select  v-model="basic_fields.deposit_to" name="deposit-to" class="wperp-form-field">
                                <option value="0">-Select-</option>
                                <option value="1">Cash</option>
                                <option value="2">Bank</option>
                            </select>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <div class="wperp-table-responsive">
            <!-- Start .wperp-crm-table -->
            <div class="table-container">
                <table class="wperp-table wperp-form-table wperp-invoice-table">
                    <thead>
                    <tr>
                        <td scope="col" class="col--check">Invoice ID</td>
                        <th scope="col" class="column-primary">Total</th>
                        <th scope="col">Due</th>
                        <th scope="col">Amount</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row" class="col--check">#001</th>
                        <td class="col--qty column-primary">$500.00</td>
                        <td class="col--uni_price" data-colname="Unit Price">$240.00</td>
                        <td class="col--amount" data-colname="Total">
                            <input type="text" name="amount" class="text-right" value="000000" />
                        </td>
                        <td class="delete-row">
                            <a href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="col--check">#001</th>
                        <td class="col--qty column-primary">$500.00</td>
                        <td class="col--uni_price" data-colname="Unit Price">$240.00</td>
                        <td class="col--amount" data-colname="Total">
                            <input type="text" name="amount" class="text-right" value="000000" />
                        </td>
                        <td class="delete-row">
                            <a href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="col--check">#001</th>
                        <td class="col--qty column-primary">$500.00</td>
                        <td class="col--uni_price" data-colname="Unit Price">$240.00</td>
                        <td class="col--amount" data-colname="Total">
                            <input type="text" name="amount" class="text-right" value="000000" />
                        </td>
                        <td class="delete-row">
                            <a href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="total-amount-row">
                        <td class="text-right pr-0" colspan="3">Total Amount</td>
                        <td class="text-right"><input type="text" name="amount" id="amount" class="text-right" value="000000" readonly disabled /></td>
                        <td class="text-right"></td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- memo section -->
        <div class="wperp-panel wperp-panel-default">
            <div class="wperp-panel-body">
                <div class="wperp-form-group">
                    <label for="memo">Memo</label>
                    <textarea name="memo" id="memo" rows="4" class="wperp-form-field display-flex" placeholder="Internal Information"></textarea>
                </div>
                <div class="wperp-form-group">
                    <div class="attachment-container attachment-style2">
                        <label class="col--attachement">Attachment</label>
                        <div class="attachment-preview">
                            <img src="assets/images/img-thumb.png" alt="attachment image">
                            <i class="flaticon-close remove-attachment"></i>
                        </div>
                        <div class="attachment-placeholder">
                            To attach <input type="file" id="attachment" name="attachment" class="display-none"> <label class="mt-0" for="attachment">Select files</label> from your computer
                        </div>
                    </div>
                </div>
                <div class="buttons-wrapper">
                    <div class="buttons-wrapper text-right">
                        <button class="wperp-btn btn--primary" type="submit">Pay</button>
                        <button class="wperp-btn btn--default" type="reset">Cancel</button>
                    </div>
                </div>
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
                                    <img :src="acct_var.acc_aaset_url + '/images/dummy-logo.png'" alt="logo name">
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
                                <img :src="acct_var.acc_aaset_url + '/images/img-thumb.png'" alt="image name">
                                <div class="attachment-meta">
                                    <span>File name with extension</span><br>
                                    <span class="text-muted">file size</span>
                                </div>
                            </a>
                            <a class="attachment-item" href="#">
                                <img :src="acct_var.acc_aaset_url + '/images/doc-thumb.png'" alt="image name">
                                <div class="attachment-meta">
                                    <span>File name with extension</span><br>
                                    <span class="text-muted">file size</span>
                                </div>
                            </a>
                            <a class="attachment-item" href="#">
                                <img :src="acct_var.acc_aaset_url + '/images/pdf-thumb.png'" alt="image name">
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
    import HTTP from 'admin/http';
    import Datepicker from 'admin/components/base/Datepicker.vue';
    import FileUpload from 'admin/components/base/FileUpload.vue';
    import RecPaymentTrnRow from 'admin/components/RecPayment/RecPaymentTrnRow.vue';
    import InvoiceCustomers from 'admin/components/invoice/InvoiceCustomers.vue';

    export default {
        name: 'RecPaymentCreate',

        components: {
            HTTP,
            Datepicker,
            FileUpload,
            RecPaymentTrnRow,
            InvoiceCustomers
        },

        data() {
            return {
                basic_fields: {
                    customer: '',
                    trn_ref: '',
                    payment_date: '',
                    deposit_to: ''
                },

                products: [],
                transactionLines: [{}],
                finalTotalAmount: 0,

                acct_var: erp_acct_var
            }
        },

        watch: {
            'basic_fields.customer'() {
                this.getCustomerAddress();
            }
        },

        created() {
            this.getProducts();

            this.$root.$on('remove-row', index => {
                this.$delete(this.transactionLines, index);
                this.updateFinalAmount();
            });

            this.$root.$on('total-updated', amount => {
                this.updateFinalAmount();
            });
        },

        methods: {
            getProducts() {
                HTTP.get('/products').then((response) => {
                    response.data.forEach(element => {
                        this.products.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                });
            },

            getCustomerAddress() {
                let customer_id = this.basic_fields.customer.id;

                HTTP.get(`/customers/${customer_id}`).then((response) => {
                    // add more info
                    this.basic_fields.billing_address = `
                    Street: ${response.data.billing.street_1} ${response.data.billing.street_2},
                    City: ${response.data.billing.city},
                `;
                });
            },

            addLine() {
                this.transactionLines.push({});
            },

            updateFinalAmount() {
                let finalAmount = 0;

                this.transactionLines.forEach(element => {
                    finalAmount += element.totalAmount;
                });

                this.finalTotalAmount = parseFloat(finalAmount).toFixed(2);
            }
        }

    }
</script>

<style lang="less">

</style>
