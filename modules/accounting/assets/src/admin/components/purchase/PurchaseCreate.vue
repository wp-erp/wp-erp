<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">New Purchase</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
            <div class="wperp-panel-body">
                <form action="#" class="wperp-form" method="post">
                    <div class="wperp-row">
                        <div class="wperp-col-sm-4">
                            <select-vendors v-model="basic_fields.vendor"></select-vendors>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label>Transaction Date<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.trans_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label>Due Date<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.due_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-xs-12">
                            <label>Billing Address</label>
                            <textarea v-model="basic_fields.billing_address" rows="4" class="wperp-form-field" placeholder="Type here"></textarea>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <div class="wperp-table-responsive">
            <!-- Start .wperp-crm-table -->
            <div class="table-container">
                <table class="wperp-table wperp-form-table">
                    <thead>
                    <tr>
                        <td scope="col" class="col--check">Product/Service</td>
                        <th scope="col" class="column-primary">Qty</th>
                        <th scope="col">Unit Price</th>
                        <th scope="col">Amount</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                    </thead>
                    <tbody id="test">
                    <purchase-row
                        :line="line"
                        :products="products"
                        :key="index"
                        v-for="(line, index) in transactionLines"
                    ></purchase-row>

                    <tr class="total-amount-row">
                        <td colspan="3" class="text-right">
                            <span>Total Amount = </span>
                        </td>
                        <td><input type="text" v-model="finalTotalAmount" readonly></td>
                        <td></td>
                    </tr>
                    <tr class="add-new-line">
                        <td colspan="9" style="text-align: left;">
                            <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add Line</button>
                        </td>
                    </tr>
                    <tr class="add-attachment-row" >
                        <td colspan="9" style="text-align: left;">
                            <div class="attachment-container">
                                <label class="col--attachement">Attachment</label>
                                <file-upload v-model="attachments" url="/invoices/attachments"/>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="9" style="text-align: right;">
                            <submit-button text="Create Purchase" @click.native="SubmitForApproval" :working="isWorking"></submit-button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- End .wperp-crm-table -->
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker from 'admin/components/base/Datepicker.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import PurchaseRow from 'admin/components/purchase/PurchaseRow.vue'
    import SelectVendors from 'admin/components/people/SelectVendors.vue'

    export default {
        name: 'PurchaseCreate',

        components: {
            HTTP,
            Datepicker,
            FileUpload,
            SubmitButton,
            PurchaseRow,
            SelectVendors
        },

        data() {
            return {
                basic_fields: {
                    vendor: '',
                    trans_date: erp_acct_var.current_date,
                    due_date: erp_acct_var.current_date,
                    billing_address: ''
                },

                products: [],
                attachments: [],
                transactionLines: [{}],
                finalTotalAmount: 0,

                isWorking: false,
            }
        },

        watch: {
            'basic_fields.vendor'() {
                this.getvendorAddress();
            }
        },

        created() {
            this.getProducts();

            this.$root.$on('remove-row', index => {
                if ( this.transactionLines.length < 2 ) {
                    return;
                }
                this.$delete(this.transactionLines, index);
                this.updateFinalAmount();
            });

            this.$root.$on('total-updated', amount => {
                this.updateFinalAmount();
            });
        },

        methods: {

            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
                this.getProducts();
            },

            getProducts() {
                this.products = [];
                HTTP.get('/products').then((response) => {
                    response.data.forEach(element => {
                        this.products.push({
                            id: element.id,
                            name: element.name,
                            unitPrice: element.cost_price
                        });
                    });
                });
            },

            getvendorAddress() {
                if ( ! this.basic_fields.vendor.hasOwnProperty('id') ){
                    return;
                }

                let vendor_id = this.basic_fields.vendor.id;

                HTTP.get(`/people/${vendor_id}`).then(response => {
                    let billing = response.data;

                    let address = `Street: ${billing.street_1} ${billing.street_2} \nCity: ${billing.city} \nState: ${billing.state} \nCountry: ${billing.country}`;

                    this.basic_fields.billing_address = address;
                });
            },

            addLine() {
                this.transactionLines.push({});
            },

            updateFinalAmount() {
                let finalAmount = 0;

                this.transactionLines.forEach(element => {
                    finalAmount += parseFloat(element.totalAmount);
                });

                this.finalTotalAmount = finalAmount.toFixed(2);

            },

            formatLineItems() {
                var lineItems = [];

                this.transactionLines.forEach(line => {
                    lineItems.push({
                        product_id: line.selectedProduct.id,
                        product_type: 'service',
                        qty: line.qty,
                        unit_price: line.unitPrice,
                        item_total: line.totalAmount,
                    });
                });

                return lineItems;
            },

            SubmitForApproval() {
                if ( this.basic_fields.vendor.length == 0 ) {
                    this.$swal({
                        position: 'center',
                        type: 'error',
                        title: 'Select a Vendor',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    return;
                }

                this.isWorking = true;

                HTTP.post('/purchases', {
                    vendor_id: this.basic_fields.vendor.id,
                    vendor_name: this.basic_fields.vendor.name,
                    trn_date: this.basic_fields.trans_date,
                    due_date: this.basic_fields.due_date,
                    billing_address: this.basic_fields.billing_address,
                    line_items: this.formatLineItems(),
                    attachments: this.attachments,
                    type: 'purchase',
                    status: 3,
                    // trn_by: 'cash',
                    // ref : ' ',
                    // particulars: ''
                }).then(res => {
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Purchase Created!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }).then(() => {
                    this.isWorking = false;
                    this.resetData();

                });

            },

        }

    }
</script>
