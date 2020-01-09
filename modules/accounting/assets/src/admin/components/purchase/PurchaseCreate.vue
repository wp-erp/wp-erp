<template>
    <div class="wperp-container purchase-create">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 v-if="orderToPurchase()">Convert into Purchase</h2>
                    <h2 v-else class="content-header__title">{{ editMode ? 'Edit' : 'New' }} {{ page_title }}</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <form action="" method="post" @submit.prevent="SubmitForApproval">

            <div class="wperp-panel wperp-panel-default" style="padding-bottom: 0;">
                <div class="wperp-panel-body">

                    <show-errors :error_msgs="form_errors"></show-errors>

                    <div class="wperp-row">
                        <div class="wperp-col-sm-4">
                            <select-vendors v-model="basic_fields.vendor"></select-vendors>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label>{{ __('Transaction Date', 'erp') }}<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.trn_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-form-group">
                                <label>{{ __('Due Date', 'erp') }}<span class="wperp-required-sign">*</span></label>
                                <datepicker v-model="basic_fields.due_date"></datepicker>
                            </div>
                        </div>
                        <div class="wperp-col-sm-6">
                            <label>{{ __('Reference No', 'erp') }}</label>
                            <input type="text" v-model="basic_fields.ref" rows="4" class="wperp-form-field">
                        </div>
                        <div class="wperp-col-sm-6">
                            <label>{{ __('Billing Address', 'erp') }}</label>
                            <textarea v-model="basic_fields.billing_address" rows="4" class="wperp-form-field" :placeholder="__('Type here', 'erp')"></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <div class="wperp-table-responsive">
                <!-- Start .wperp-form-table -->
                <div class="table-container">
                    <table class="wperp-table wperp-form-table">
                        <thead>
                        <tr>
                            <td scope="col" class="col--product">{{ __('Product/Service', 'erp') }}</td>
                            <th scope="col">{{ __('Qty', 'erp') }}</th>
                            <th scope="col">{{ __('Unit Price', 'erp') }}</th>
                            <th scope="col">{{ __('Amount', 'erp') }}</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <purchase-row
                            :line="line"
                            :products="products"
                            :key="index"
                            v-for="(line, index) in transactionLines"
                        ></purchase-row>
                        <tr class="add-new-line">
                            <td colspan="9" style="text-align: left;">
                                <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger">
                                    <i class="flaticon-add-plus-button"></i>{{ __('Add Line', 'erp') }}
                                </button>
                            </td>
                        </tr>

                        <tr class="total-amount-row">
                            <td colspan="3" class="text-right">
                                <span>{{ __('Total Amount', 'erp') }} = </span>
                            </td>
                            <td><input type="text" v-model="finalTotalAmount" class="wperp-form-field text-right" readonly></td>
                            <td></td>
                        </tr>

                        <tr class="wperp-form-group">
                            <td colspan="9" style="text-align: left;">
                                <label>{{ __('Particulars', 'erp') }}</label>
                                <textarea v-model="particulars" rows="4" maxlength="250" class="wperp-form-field display-flex" :placeholder="__('Particulars', 'erp')"></textarea>
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
                                    <label class="col--attachement">{{ __('Attachment', 'erp') }}</label>
                                    <file-upload v-model="attachments" url="/invoices/attachments"/>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td v-if="orderToPurchase()" colspan="9" style="text-align: right;">
                                <combo-button :options="[{ id: 'update', text: 'Save Conversion' }]" />
                            </td>
                            <td v-else colspan="9" style="text-align: right;">
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
    import { mapState } from 'vuex';

    import HTTP from 'admin/http';
    import Datepicker from 'admin/components/base/Datepicker.vue';
    import FileUpload from 'admin/components/base/FileUpload.vue';
    import ComboButton from 'admin/components/select/ComboButton.vue';
    import PurchaseRow from 'admin/components/purchase/PurchaseRow.vue';
    import SelectVendors from 'admin/components/people/SelectVendors.vue';
    import ShowErrors from 'admin/components/base/ShowErrors.vue';

    export default {
        name: 'PurchaseCreate',

        components: {
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
                    vendor         : '',
                    trn_date       : '',
                    due_date       : '',
                    ref            : '',
                    billing_address: ''
                },

                createButtons: [
                    { id: 'save', text: 'Save' },
                    // {id: 'send_create', text: 'Create and Send'},
                    { id: 'new_create', text: 'Save and New' },
                    { id: 'draft', text: 'Save as Draft' }
                ],

                updateButtons: [
                    { id: 'update', text: 'Update Purchase' },
                    // {id: 'send_update', text: 'Update and Send'},
                    { id: 'new_update', text: 'Update and New' },
                    { id: 'draft', text: 'Save as Draft' }
                ],

                form_errors     : [],
                editMode        : false,
                voucherNo       : 0,
                products        : [],
                particulars     : '',
                attachments     : [],
                transactionLines: [],
                finalTotalAmount: 0,
                erp_acct_assets : erp_acct_var.acct_assets, /* global erp_acct_var */
                isWorking       : false,
                purchase_title  : '',
                purchase_order  : 0,
                page_title      : ''
            };
        },

        watch: {
            'basic_fields.vendor'() {
                if (!this.editMode) {
                    this.getvendorData();
                }
            }
        },

        computed: {
            ...mapState({ actionType: state => state.combo.btnID })
        },

        created() {
            if (this.$route.name === 'PurchaseOrderCreate') {
                this.page_title = 'Purchase Order';
                this.purchase_order = 1;
            } else {
                this.page_title = 'Purchase';

                if (this.$route.query.convert) {
                    this.purchase_order = 1;
                } else {
                    this.purchase_order = 0;
                }
            }

            this.prepareDataLoad();

            this.$root.$on('remove-row', index => {
                if (this.transactionLines.length < 2) {
                    return;
                }
                this.$delete(this.transactionLines, index);
                this.updateFinalAmount();
            });

            this.$root.$on('total-updated', amount => {
                this.updateFinalAmount();
            });

            // initialize combo button id with `update`
            this.$store.dispatch('combo/setBtnID', 'update');
        },

        methods: {

            async prepareDataLoad() {
                /**
                 * ----------------------------------------------
                 * check if editing
                 * -----------------------------------------------
                 */
                if (this.$route.params.id) {
                    this.editMode = true;
                    this.voucherNo = this.$route.params.id;

                    const [request] = await Promise.all([
                        HTTP.get(`/purchases/${this.$route.params.id}`)
                    ]);

                    const canEdit = Boolean(Number(request.data.editable));

                    if (!canEdit) {
                        this.showAlert('error', 'Can\'t edit');
                        return;
                    }

                    let purchase_data = request.data;

                    if ( purchase_data ) {
                        this.getProducts( purchase_data.vendor_id );
                    }

                    this.setDataForEdit(request.data);

                    // initialize combo button id with `update`
                    this.$store.dispatch('combo/setBtnID', 'update');
                } else {
                    /**
                     * ----------------------------------------------
                     * create a new purchase
                     * -----------------------------------------------
                     */
                    this.basic_fields.trn_date = erp_acct_var.current_date;
                    this.basic_fields.due_date = erp_acct_var.current_date;
                    this.transactionLines.push({}, {}, {});

                    // initialize combo button id with `save`
                    this.$store.dispatch('combo/setBtnID', 'save');
                }
            },

            setDataForEdit(purchase) {
                this.basic_fields.vendor          = { id: parseInt(purchase.vendor_id), name: purchase.vendor_name };
                this.basic_fields.billing_address = purchase.billing_address;
                this.basic_fields.trn_date        = purchase.date;
                this.basic_fields.ref             = purchase.ref;
                this.basic_fields.due_date        = purchase.due_date;
                this.status                       = purchase.status;
                this.transactionLines             = purchase.line_items;
                this.particulars                  = purchase.particulars;
                this.attachments                  = purchase.attachments;
            },

            resetData() {
                this.basic_fields = {
                    vendor         : { id: null, name: null },
                    trn_date       : erp_acct_var.current_date,
                    due_date       : erp_acct_var.current_date,
                    ref            : '',
                    billing_address: ''
                };

                this.form_errors      = [];
                this.particulars      = '';
                this.attachments      = [];
                this.transactionLines = [];
                this.finalTotalAmount = 0;
                this.isWorking        = false;

                this.$store.dispatch('combo/setBtnID', 'save');
            },

            getProducts( vendor_id ) {
                this.products = [];
                if ( !vendor_id ) {
                    vendor_id = this.basic_fields.vendor.id;
                }

                this.$store.dispatch('spinner/setSpinner', true);

                HTTP.get(`vendors/${vendor_id}/products`, {
                    params: {
                        number: -1
                    }
                }).then((response) => {
                    response.data.forEach(element => {
                        this.products.push({
                            id       : element.id,
                            name     : element.name,
                            unitPrice: element.cost_price
                        });
                    });

                    this.$store.dispatch('spinner/setSpinner', false);
                }).catch(error => {
                    this.$store.dispatch('spinner/setSpinner', false);
                    throw error;
                });
            },

            getvendorData() {
                const vendor_id = this.basic_fields.vendor.id;

                if (!vendor_id) {
                    this.basic_fields.billing_address = '';
                    return;
                }

                HTTP.get(`/people/${vendor_id}`).then(response => {
                    const billing = response.data;

                    const address = `Street: ${billing.street_1} ${billing.street_2} \nCity: ${billing.city} \nState: ${billing.state} \nCountry: ${billing.country}`;

                    this.basic_fields.billing_address = address;
                });

                this.getProducts();
            },

            orderToPurchase() {
                const purchase_order = 1;

                return purchase_order === this.purchase_order && this.$route.query.convert;
            },

            addLine() {
                this.transactionLines.push({});
            },

            updateFinalAmount() {
                let finalAmount = 0;

                this.transactionLines.forEach(element => {
                    if (element.qty) {
                        finalAmount += parseFloat(element.amount);
                    }
                });

                this.finalTotalAmount = finalAmount.toFixed(2);
            },

            formatLineItems() {
                var lineItems = [];

                this.transactionLines.forEach(line => {
                    if (Object.prototype.hasOwnProperty.call(line, 'selectedProduct')) {
                        lineItems.push({
                            product_id: line.selectedProduct.id,
                            qty       : line.qty,
                            unit_price: line.unitPrice,
                            item_total: line.amount
                        });
                    }
                });

                return lineItems;
            },

            updatePurchase(requestData) {
                HTTP.put(`/purchases/${this.voucherNo}`, requestData).then(res => {
                    this.$store.dispatch('spinner/setSpinner', false);

                    let message = 'Purchase Updated!';

                    if (this.orderToPurchase()) {
                        message = 'Conversion Successful!';
                    }

                    this.showAlert('success', message);
                }).then(() => {
                    this.$store.dispatch('spinner/setSpinner', false);
                    this.isWorking = false;
                    this.reset = true;

                    if (this.actionType === 'update' || this.actionType === 'draft') {
                        this.$router.push({ name: 'Purchases' });
                    } else if (this.actionType === 'new_update') {
                        this.resetFields();
                    }
                });
            },

            createPurchase(requestData) {
                HTTP.post('/purchases', requestData).then(res => {
                    this.$store.dispatch('spinner/setSpinner', false);
                    this.showAlert('success', this.page_title + ' Created!');
                }).catch(error => {
                    this.$store.dispatch('spinner/setSpinner', false);
                    throw error;
                }).then(() => {
                    if (this.actionType === 'save' || this.actionType === 'draft') {
                        this.$router.push({ name: 'Purchases' });
                    } else if (this.actionType === 'new_create') {
                        this.resetFields();
                    }
                });
            },

            SubmitForApproval() {
                this.validateForm();

                if (this.form_errors.length) {
                    window.scrollTo({
                        top: 10,
                        behavior: 'smooth'
                    });
                    return;
                }

                this.isWorking = true;
                this.$store.dispatch('spinner/setSpinner', true);

                let trn_status = null;
                if (this.actionType === 'draft') {
                    trn_status = 1;
                } else {
                    trn_status = 2;
                }

                const requestData = {
                    vendor_id      : this.basic_fields.vendor.id,
                    vendor_name    : this.basic_fields.vendor.name,
                    trn_date       : this.basic_fields.trn_date,
                    due_date       : this.basic_fields.due_date,
                    ref            : this.basic_fields.ref,
                    billing_address: this.basic_fields.billing_address,
                    line_items     : this.formatLineItems(),
                    particulars    : this.particulars,
                    attachments    : this.attachments,
                    type           : 'purchase',
                    status         : trn_status,
                    purchase_order : this.purchase_order,
                    convert        : this.$route.query.convert
                };

                if (this.editMode) {
                    this.updatePurchase(requestData);
                } else {
                    this.createPurchase(requestData);
                }
            },

            validateForm() {
                this.form_errors = [];

                if (!Object.prototype.hasOwnProperty.call(this.basic_fields.vendor, 'id')) {
                    this.form_errors.push('Vendor Name is required.');
                }

                if (!this.basic_fields.trn_date) {
                    this.form_errors.push('Transaction Date is required.');
                }

                if (!this.basic_fields.due_date) {
                    this.form_errors.push('Due Date is required.');
                }

                if (!this.finalTotalAmount) {
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
    .purchase-create {
        .dropdown {
            width: 100%;
        }

        .col--product {
            min-width: 500px;
        }

        .col--qty {
            width: 120px;
        }

        .col--qty input {
            width: 100% !important;
        }

        .col--uni_price,
        .col--amount {
            width: 200px;
        }
    }
</style>
