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

        <form action="" method="post" @submit.prevent="SubmitForApproval">

            <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
                <div class="wperp-panel-body">

                    <show-errors :error_msgs="form_errors" ></show-errors>

                    <form action="#" class="wperp-form" method="post">
                        <div class="wperp-row">
                            <div class="wperp-col-sm-4">
                                <select-vendors v-model="basic_fields.vendor"></select-vendors>
                            </div>
                            <div class="wperp-col-sm-4">
                                <div class="wperp-form-group">
                                    <label>Transaction Date<span class="wperp-required-sign">*</span></label>
                                    <datepicker v-model="basic_fields.trn_date"></datepicker>
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
                        <tr>
                            <td>
                                <div class="attachment-item" :key="index" v-for="(file, index) in attachments">
                                    <img :src="erp_acct_assets + '/images/file-thumb.png'">
                                    <span class="remove-file" @click="removeFile(index)">&#10007;</span>

                                    <div class="attachment-meta">
                                        <h3>{{ getFileName(file) }}</h3>
                                    </div>
                                </div>
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
                                <combo-button v-if="editMode" :options="updateButtons" />
                                <combo-button v-else :options="createButtons" />
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </form>
        <!-- End .wperp-crm-table -->
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker from 'admin/components/base/Datepicker.vue'
    import FileUpload from 'admin/components/base/FileUpload.vue'
    import ComboButton from 'admin/components/select/ComboButton.vue';
    import PurchaseRow from 'admin/components/purchase/PurchaseRow.vue'
    import SelectVendors from 'admin/components/people/SelectVendors.vue'
    import ShowErrors from 'admin/components/base/ShowErrors.vue'

    export default {
        name: 'PurchaseCreate',

        components: {
            HTTP,
            Datepicker,
            FileUpload,
            ComboButton,
            PurchaseRow,
            SelectVendors,
            ShowErrors
        },

        data() {
            return {
                basic_fields: {
                    vendor: '',
                    trn_date: '',
                    due_date: '',
                    billing_address: ''
                },

                createButtons: [
                    {id: 'save', text: 'Create Purchase'},
                    {id: 'send_create', text: 'Create and Send'},
                    {id: 'new_create', text: 'Create and New'},
                ],

                updateButtons: [
                    {id: 'update', text: 'Update Purchase'},
                    {id: 'send_update', text: 'Update and Send'},
                    {id: 'new_update', text: 'Update and New'},
                ],

                form_errors     : [],
                editMode        : false,
                voucherNo       : 0,
                products        : [],
                attachments     : [],
                transactionLines: [],
                finalTotalAmount: 0,
                erp_acct_assets : erp_acct_var.acct_assets,
                isWorking       : false,
            }
        },

        watch: {
            'basic_fields.vendor'() {
                this.getvendorAddress();
            }
        },

        created() {
            this.prepareDataLoad();

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

            async prepareDataLoad() {
                /**
                 * ----------------------------------------------
                 * check if editing
                 * -----------------------------------------------
                 */
                if ( this.$route.params.id ) {
                    this.editMode = true;
                    this.voucherNo = this.$route.params.id;

                    let response = await HTTP.get(`/purchases/${this.$route.params.id}`);
                    this.setDataForEdit( response.data );
                } else {
                    /**
                     * ----------------------------------------------
                     * create a new purchase
                     * -----------------------------------------------
                     */
                    this.getProducts();

                    this.basic_fields.trn_date = erp_acct_var.current_date;
                    this.basic_fields.due_date = erp_acct_var.current_date;
                    this.transactionLines.push({}, {}, {});
                }
            },

            setDataForEdit(purchase) {
                this.basic_fields.vendor          = { id: parseInt(purchase.vendor_id), name: purchase.vendor_name };
                this.basic_fields.billing_address = purchase.billing_address;
                this.basic_fields.trn_date        = purchase.trn_date;
                this.basic_fields.due_date        = purchase.due_date;
                this.status                       = purchase.status;
                this.transactionLines             = purchase.line_items;
                this.attachments                  = purchase.attachments;
            },

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
                let vendor_id = this.basic_fields.vendor.id;

                if ( ! vendor_id ) {
                    this.basic_fields.billing_address = '';
                    return;
                }

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
                    if ( element.qty ) {
                        finalAmount += parseFloat(element.amount);
                    }
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
                        item_total: line.amount,
                    });
                });

                return lineItems;
            },

            updatePurchase(requestData) {
                HTTP.put(`/purchases/${this.voucherNo}`, requestData).then(res => {
                    this.showAlert('success', 'Purchase Updated!');
                }).then(() => {
                    this.isWorking = false;
                    this.reset = true;

                    if ('update' == this.actionType) {
                        this.$router.push({name: 'Purchase'});
                    } else if ('new_update' == this.actionType) {
                        this.resetFields();
                    }
                });
            },

            createPurchase(requestData) {
                HTTP.post('/purchases', requestData).then(res => {
                    this.showAlert('success', 'Purchase Created!');
                }).then(() => {
                    this.isWorking = false;
                    this.reset = true;

                    if ('save' == this.actionType) {
                        this.$router.push({name: 'Purchase'});
                    } else if ('new_create' == this.actionType) {
                        this.resetFields();
                    }
                });
            },

            SubmitForApproval() {
                this.validateForm();

                if ( this.form_errors.length ) {
                    return;
                }

                this.isWorking = true;

                let requestData = {
                    vendor_id      : this.basic_fields.vendor.id,
                    vendor_name    : this.basic_fields.vendor.name,
                    trn_date       : this.basic_fields.trn_date,
                    due_date       : this.basic_fields.due_date,
                    billing_address: this.basic_fields.billing_address,
                    line_items     : this.formatLineItems(),
                    attachments    : this.attachments,
                    type           : 'purchase',
                    status         : 3
                };

                if ( this.editMode ) {
                    this.updatePurchase(requestData);
                } else {
                    this.createPurchase(requestData);
                }
            },

            validateForm() {
                this.form_errors = [];

                if ( !this.basic_fields.vendor.hasOwnProperty('id') ) {
                    this.form_errors.push('Vendor Name is required.');
                }

                if ( !this.basic_fields.trn_date ) {
                    this.form_errors.push('Transaction Date is required.');
                }

                if ( !this.basic_fields.due_date ) {
                    this.form_errors.push('Due Date is required.');
                }

                window.setInterval(() => {
                    window.scrollTo(0, 0)
                }, 30)
            },

        }

    }
</script>
