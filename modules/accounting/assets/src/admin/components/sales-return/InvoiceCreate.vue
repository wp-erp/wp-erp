<template>
    <div class="wperp-container invoice-create">



        <div class="wperp-modal-dialog sales-single" >

            <div class="wperp-modal-content">

                <!-- Start .header-section -->
                <div class="content-header-section separator">
                    <div class="wperp-row wperp-between-xs">
                        <div class="wperp-col">
                            <h2 v-if="estimateToInvoice()">{{ __('Convert into Invoice', 'erp') }}</h2>
                            <h2 v-else class="content-header__title">{{ __('Sales Return', 'erp') }}</h2>
                        </div>
                    </div>
                </div>


                <div class="wperp-modal-body">

                    <form action="" method="post" @submit.prevent="searchVoucher">
                        <!-- End .header-section -->
                        <div class="wperp-row">
                            <div class="wperp-form-group wperp-col-sm-8">
                                <label>Voucher Number</label>
                                <input type="text" v-model="voucher_no" class="wperp-form-field" placeholder="Sales Voucher Number">
                            </div>
                            <div class="wperp-form-group wperp-col-sm-4">
                                <button type="submit" class="wperp-btn btn--primary voucher-search">Search</button>
                            </div>
                        </div>
                    </form>


                    <div class="wperp-invoice-panel" v-if="this.invoice.id">

                        <div class="invoice-body">
                            <h4> Sales Invoice</h4>
                            <div class="wperp-row">
                                <div class="wperp-col-sm-6">
                                    <h5>{{ __('Bill to', 'erp') }}:</h5>
                                    <div class="persons-info">
                                        <strong>{{ invoice.customer_name }}</strong><br>
                                        {{ invoice.billing_address }}
                                    </div>
                                </div>
                                <div class="wperp-col-sm-6">
                                    <table class="invoice-info">
                                        <tr>
                                            <th>{{ __('Voucher No', 'erp') }}:</th>
                                            <td>#{{ invoice.voucher_no }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Transaction Date', 'erp') }}:</th>
                                            <td>{{ invoice.trn_date }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Due Date', 'erp') }}:</th>
                                            <td>{{ invoice.due_date }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Created At', 'erp') }}:</th>
                                            <td>{{ invoice.created_at }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Amount Due', 'erp') }}:</th>
                                            <!--<td>{{ moneyFormat( invoice.total_due ) }}</td>-->
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="wperp-invoice-table">
                            <table class="wperp-table wperp-form-table invoice-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Sl', 'erp') }}.</th>
                                    <th>{{ __('Product', 'erp') }}</th>
                                    <th>{{ __('Qty', 'erp') }}</th>
                                    <th>{{ __('Unit Price', 'erp') }}</th>
                                    <th>{{ __('Amount', 'erp') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr :key="index" v-for="(detail, index) in invoice.line_items">
                                    <th>{{ index + 1 }}</th>
                                    <th>{{ detail.name }}</th>
                                    <td>{{ detail.qty }}</td>
                                    <td>{{ moneyFormat(detail.unit_price) }}</td>
                                    <td>{{ moneyFormat(detail.item_total) }}</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td class="wperp-invoice-amounts" colspan="7">
                                        <ul>
                                            <li><span>{{ __('Subtotal', 'erp') }}:</span> {{ moneyFormat(invoice.amount)
                                                }}
                                            </li>
                                            <li><span>{{ __('Discount', 'erp') }}:</span> (-) {{
                                                moneyFormat(invoice.discount)
                                                }}
                                            </li>
                                            <li><span>{{ __('Tax', 'erp') }}:</span> (+) {{ moneyFormat(invoice.tax) }}
                                            </li>
                                            <!--  <li><span>{{ __('Total', 'erp') }}:</span> {{ moneyFormat( total ) }}</li>-->
                                        </ul>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import {mapState} from 'vuex';


export default {
    name: 'InvoiceCreate',

    components: {
    },

    data() {
        return {
            voucher_no: '',
            invoice: {line_items: []},
            basic_fields: {
                customer: '',
                trn_date: '',
                due_date: '',
                billing_address: ''
            },
            voucher_found: false,

            createButtons: [
                {id: 'save', text: 'Save'},
                // {id: 'send_create', text: 'Create and Send'},
                {id: 'new_create', text: 'Save and New'},
                {id: 'draft', text: 'Save as Draft'}
            ],

            updateButtons: [
                {id: 'update', text: 'Update'},
                // {id: 'send_update', text: 'Update and Send'},
                {id: 'new_update', text: 'Update and New'},
                {id: 'draft', text: 'Save as Draft'}
            ],

            extraFields: window.acct.hooks.applyFilters('acctInvoiceExtraFields', []),
            editMode: false,
            voucherNo: 0,
            discountType: 'discount-percent',
            discount: 0,
            status: null,
            taxRate: null,
            taxSummary: null,
            products: [],
            particulars: '',
            attachments: [],
            transactionLines: [],
            taxRates: [],
            taxTotalAmount: 0,
            finalTotalAmount: 0,
            inv_title: '',
            inv_type: {},
            erp_acct_assets: erp_acct_var.acct_assets,
            form_errors: []
        };
    },

    watch: {
        'basic_fields.customer'() {
            if (!this.editMode) {
                this.getCustomerAddress();
            }
        },

        taxRate(newVal) {
            // this.$store.dispatch('sales/setTaxRateID', newVal.id);
        },

        discount() {
            this.discountChanged();
        },

        discountType() {
            this.discountChanged();
        },

        invoiceTotalAmount() {
            this.discountChanged();
        }
    },

    computed: {
        ...mapState({invoiceTotalAmount: state => state.sales.invoiceTotalAmount}),
        ...mapState({actionType: state => state.combo.btnID})
    },

    created() {

    },

    methods: {
        async searchVoucher() {
            let Voucher = await getRequest('invoices/' + this.voucher_no, {name: 'alemran'})
            if (Voucher) {
                this.invoice = Voucher
            }
        },
        async prepareDataLoad() {
            /**
             * ----------------------------------------------
             * check if editing
             * -----------------------------------------------
             */
            if (this.$route.params.id) {
                this.editMode = true;
                this.voucherNo = this.$route.params.id;

                /**
                 * Duplicates of
                 *? this.getProducts()
                 *? this.getTaxRates()
                 * load products and taxes, before invoice load
                 */
                const [request1, request2] = await Promise.all([
                    HTTP.get('/products', {
                        params: {
                            number: -1
                        }
                    }),
                    HTTP.get('/taxes/summary')
                ]);
                const request3 = await HTTP.get(`/invoices/${this.$route.params.id}`);

                if (!request3.data.line_items.length) {
                    this.showAlert('error', 'Invoice does not exists!');
                    return;
                }

                const canEdit = Boolean(Number(request3.data.editable));

                if (!canEdit) {
                    this.showAlert('error', 'Can\'t edit');
                    return;
                }

                this.products = request1.data;
                this.taxSummary = request2.data;
                this.taxRates = this.getUniqueTaxRates(request2.data);
                this.setDataForEdit(request3.data);

                // initialize combo button id with `update`
                this.$store.dispatch('combo/setBtnID', 'update');
            } else {
                /**
                 * ----------------------------------------------
                 * create a new invoice
                 * -----------------------------------------------
                 */
                this.getProducts();
                this.getTaxRates();

                this.basic_fields.trn_date = erp_acct_var.current_date;
                this.basic_fields.due_date = erp_acct_var.current_date;
                this.transactionLines.push({}, {}, {});

                // initialize combo button id with `save`
                this.$store.dispatch('combo/setBtnID', 'save');
            }
        },

        setDataForEdit(invoice) {
            this.basic_fields.customer = {id: parseInt(invoice.customer_id), name: invoice.customer_name};
            this.basic_fields.billing_address = invoice.billing_address;
            this.basic_fields.trn_date = invoice.trn_date;
            this.basic_fields.due_date = invoice.due_date;
            this.status = invoice.status;
            this.transactionLines = invoice.line_items;
            this.taxTotalAmount = invoice.tax;
            this.finalTotalAmount = invoice.debit;
            this.particulars = invoice.particulars;
            this.attachments = invoice.attachments;
            this.discountType = invoice.discount_type;

            if (invoice.discount_type === 'discount-percent') {
                this.discount = (parseFloat(invoice.discount) * 100) / parseFloat(invoice.amount);
            } else {
                this.discount = invoice.discount;
            }

            this.taxRate = {
                id: parseInt(invoice.tax_rate_id),
                name: this.getTaxRateNameByID(invoice.tax_rate_id)
            };

            if (invoice.estimate === '1') {
                this.inv_title = 'Estimate';
                this.inv_type = {id: 1, name: 'Estimate'};
                this.finalTotalAmount = parseFloat(invoice.amount) +
                    parseFloat(invoice.tax) - parseFloat(this.discount);
            }
        },

        estimateToInvoice() {
            const estimate = 1;

            return estimate === this.inv_type.id && this.$route.query.convert;
        },

        getProducts() {
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get('/products', {
                params: {
                    number: -1
                }
            }).then(response => {
                this.products = response.data;

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        getCustomerAddress() {
            const customer_id = this.basic_fields.customer.id;

            if (!customer_id) {
                this.basic_fields.billing_address = '';
                return;
            }

            HTTP.get(`/people/${customer_id}`).then(response => {
                const billing = response.data;

                let street_1 = billing.street_1 ? billing.street_1 + ',' : '';
                let street_2 = billing.street_2 ? billing.street_2 : '';
                let city = billing.city ? billing.city : '';
                let state = billing.state ? billing.state + ',' : '';
                let postal_code = billing.postal_code ? billing.postal_code : '';
                let country = billing.country ? billing.country : '';

                const address = `${street_1} ${street_2} \n${city} \n${state} ${postal_code} \n${country}`;

                this.basic_fields.billing_address = address;
            });
        },

        discountChanged() {
            let discount = this.discount;

            if (this.discountType === 'discount-percent') {
                discount = (this.invoiceTotalAmount * discount) / 100;
            }

            this.$store.dispatch('sales/setDiscount', discount);
        },

        getTaxRates() {
            HTTP.get('/taxes/summary').then(response => {
                this.taxSummary = response.data;

                this.taxRates = this.getUniqueTaxRates(this.taxSummary);
            });
        },

        getTaxRateNameByID(id) {
            // Array.find()
            const taxRate = this.taxRates.find(rate => {
                return rate.id === parseInt(id);
            });

            if (taxRate) {
                return taxRate.name;
            }

            return null;
        },

        getUniqueTaxRates(taxes) {
            return Array.from(new Set(taxes.map(tax => tax.tax_rate_id))).map(tax_rate_id => {
                const tax = taxes.find(tax => tax.tax_rate_id === tax_rate_id);

                if (tax.default) {
                    // set default tax rate name for invoice
                    this.taxRate = {id: tax_rate_id, name: tax.tax_rate_name};
                    this.$store.dispatch('sales/setTaxRateID', tax_rate_id);
                }

                return {
                    id: tax_rate_id,
                    name: tax.tax_rate_name
                };
            });
        },

        addLine() {
            this.transactionLines.push({});
        },

        updateFinalAmount() {
            let taxAmount = 0;
            let totalDiscount = 0;
            let totalAmount = 0;

            this.transactionLines.forEach(element => {
                if (element.qty) {
                    taxAmount += parseFloat(element.taxAmount);
                    totalDiscount += isNaN(element.discount) ? 0.00 : parseFloat(element.discount);
                    totalAmount += parseFloat(element.amount);
                }
            });

            this.$store.dispatch('sales/setInvoiceTotalAmount', totalAmount);

            const finalAmount = (totalAmount - totalDiscount) + taxAmount;

            this.taxTotalAmount = taxAmount.toFixed(2);
            this.finalTotalAmount = finalAmount.toFixed(2);
        },

        formatLineItems() {
            var lineItems = [];

            this.transactionLines.forEach(line => {
                if (line.qty) {
                    lineItems.push({
                        product_id: line.selectedProduct.id,
                        product_type_name: line.selectedProduct.product_type_name,
                        tax_cat_id: line.taxCatID,
                        qty: line.qty,
                        unit_price: line.unitPrice,
                        tax: line.taxAmount,
                        tax_rate: line.taxRate,
                        discount: line.discount,
                        item_total: line.amount
                    });
                }
            });

            return lineItems;
        },

        updateInvoice(requestData) {
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.put(`/invoices/${this.voucherNo}`, requestData).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);

                let message = 'Invoice Updated!';

                if (this.estimateToInvoice()) {
                    message = 'Conversion Successful!';
                }

                this.showAlert('success', message);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                if (this.actionType === 'update' || this.actionType === 'draft') {
                    this.$router.push({name: 'Sales'});
                } else if (this.actionType === 'new_update') {
                    this.resetFields();
                }
            });
        },

        createInvoice(requestData) {
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.post('/invoices', requestData).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', this.inv_title + ' Created!');
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                if (this.actionType === 'save' || this.actionType === 'draft') {
                    this.$router.push({name: 'Sales'});
                } else if (this.actionType === 'new_create') {
                    this.resetFields();
                }
            });
        },

        submitInvoiceForm() {
            this.validateForm();

            if (this.form_errors.length) {
                window.scrollTo({
                    top: 10,
                    behavior: 'smooth'
                });
                return;
            }

            this.isWorking = true;

            if (this.actionType === 'draft') {
                this.status = 1;
            } else {
                this.status = 2;
            }

            const requestData = window.acct.hooks.applyFilters('requestData', {
                customer_id: this.basic_fields.customer.id,
                date: this.basic_fields.trn_date,
                due_date: this.basic_fields.due_date,
                billing_address: this.basic_fields.billing_address,
                discount_type: this.discountType,
                tax_rate_id: this.taxRate !== null ? this.taxRate.id : null,
                line_items: this.formatLineItems(),
                attachments: this.attachments,
                particulars: this.particulars,
                type: 'invoice',
                status: parseInt(this.status),
                estimate: this.inv_type.id,
                convert: this.$route.query.convert
            });

            if (this.editMode) {
                this.updateInvoice(requestData);
            } else {
                this.createInvoice(requestData);
            }
        },

        removeFile(index) {
            this.$delete(this.attachments, index);
        },

        resetFields() {
            // why can't we use `form.reset()` ?

            this.basic_fields.customer = {id: null, name: null};
            this.basic_fields.trn_date = erp_acct_var.current_date;
            this.basic_fields.due_date = erp_acct_var.current_date;
            this.basic_fields.billing_address = '';
            this.particulars = '';
            this.attachments = [];
            this.transactionLines = [];
            this.discountType = 'discount-percent';
            this.discount = 0;
            this.taxTotalAmount = 0;
            this.finalTotalAmount = 0;
            this.isWorking = false;

            this.transactionLines.push({}, {}, {});

            this.$store.dispatch('combo/setBtnID', 'save');
        },

        validateForm() {
            this.form_errors = [];

            if (!Object.prototype.hasOwnProperty.call(this.basic_fields.customer, 'id')) {
                this.form_errors.push('Customer Name is required.');
            }

            if (!this.basic_fields.trn_date) {
                this.form_errors.push('Transaction Date is required.');
            }

            if (!this.basic_fields.due_date) {
                this.form_errors.push('Due Date is required.');
            }

            if (!parseFloat(this.finalTotalAmount)) {
                this.form_errors.push('Total amount can\'t be zero.');
            }

            if (this.noFulfillLines(this.transactionLines, 'selectedProduct')) {
                this.form_errors.push('Please select a product.');
            }
        }
    }

};
</script>

<style lang="less">
tr.padded {
    height: 50px;
}

.discount-rate-row {
    select {
        width: 235px;
        height: 34px;
    }

    input {
        width: 130px !important;
    }
}

.tax-rate-row {
    .tax-rates {
        width: 235px;
        float: right;
    }
}

.attachment-item {
    box-shadow: 0 0 0 1px rgba(76, 175, 80, 0.3);
    padding: 3px;
    position: relative;
    height: 58px;
    margin: 10px 0;

    .remove-file {
        position: absolute;
        top: -10px;
        right: -10px;
        font-size: 13px;
        color: #fff;
        cursor: pointer;
        background: #f44336;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        text-align: center;
    }

    img {
        float: left;
    }
}

.attachment-meta {
    h3 {
        margin-left: 50px;
        text-align: left;
        line-height: 2;
    }
}

.invoice-create {
    .dropdown {
        width: 100%;
    }

    .col--products {
        width: 400px;
    }

    .col--qty {
        width: 80px;
    }

    .col--unit-price {
        width: 120px;
    }

    .col--amount {
        width: 200px;
    }

    .col--tax {
        text-align: center;
        width: 100px;
    }

    .product-select {
        .with-multiselect .multiselect__select,
        .with-multiselect .multiselect__tags {
            min-height: 33px !important;
            margin-top: 4px;
        }

        .with-multiselect .multiselect__placeholder {
            margin-top: 3px;
        }
    }

    .invoice-create .erp-help-tip {
        color: #2f4f4f;
        font-size: 1.2em;
    }
}

.voucher-search {
    margin-top: 30px;
}
</style>
