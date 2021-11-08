<template>
    <div class="wperp-container purchase-create">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 v-if="orderToPurchase()">{{ __('Convert into Purchase', 'erp') }}</h2>
                    <h2 v-else class="content-header__title">{{ editMode ? __('Edit', 'erp') : __('New', 'erp') }} {{ page_title }}</h2>
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
                        <tr class="inline-edit-row">
                            <td scope="col" class="col--product">{{ __('Product/Service', 'erp') }}</td>
                            <th scope="col">{{ __('Qty', 'erp') }}</th>
                            <th scope="col">{{ __('Unit Price', 'erp') }}</th>
                            <th scope="col">{{ __('Amount', 'erp') }}</th>
                            <th scope="col">{{ __('VAT', 'erp') }}</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(line, index) in transactionLines" :key="index" class="inline-edit-row">
                                <th scope="row" class="col--products with-multiselect product-select">
                                    <multi-select v-model="line.product" :options="products" @input="setLineData(line)" />
                                </th>
                                <td class="col--qty">
                                    <input min="0" type="number"
                                        v-model="line.qty"
                                        @keyup="lineUpdate(index)"
                                        name="qty"
                                        class="wperp-form-field" :required="!!line.product">
                                </td>
                                <td class="col--uni_price" :data-colname="__('Unit Price', 'erp')">
                                    <input min="0" type="number" v-model="line.unitPrice"
                                        @keyup="lineUpdate(index)"
                                        step="0.01"
                                        class="wperp-form-field text-right" :required="!!line.product">
                                </td>
                                <td class="col--amount" :data-colname="__('Amount', 'erp')">
                                    <input type="number" min="0" step="0.01" v-model="line.amount" class="wperp-form-field text-right" readonly>
                                </td>
                                <td class="col--tax" :data-colname="__('Tax', 'erp')">
                                    <input type="checkbox"  @change="disableLineTax(index)" v-model="line.applyTax"  class="wperp-form-field">

                                </td>
                                <td class="col--actions delete-row" :data-colname="__('Action', 'erp')">
                                    <span class="wperp-btn" @click="removeRow(index)"><i class="flaticon-trash"></i></span>
                                </td>
                            </tr>
                            <tr class="add-new-line">
                                <td colspan="9" style="text-align: left;">
                                    <button @click.prevent="addLine" class="wperp-btn btn--primary add-line-trigger">
                                        <i class="flaticon-add-plus-button"></i>{{ __('Add Line', 'erp') }}
                                    </button>
                                </td>
                            </tr>

                            <tr class="tax-rate-row inline-edit-row">
                                <td colspan="3" class="text-right with-multiselect">
                                    <multi-select v-model="taxRate"
                                                :options="taxZones"
                                                class="tax-rates"
                                                :placeholder="__('Select Purchase Vat Zone', 'erp')" />
                                </td>
                                <td><input type="text" class="wperp-form-field" :value="moneyFormat(taxTotalAmount)" readonly></td>
                                <td></td>
                            </tr>

                            <tr class="total-amount-row inline-edit-row">
                                <td colspan="3" class="text-right">
                                    <span>{{ __('Total Amount', 'erp') }} = </span>
                                </td>
                                <td><input type="text"  v-model="totalAmount" class="wperp-form-field text-right" readonly></td>
                                <td></td>
                            </tr>

                            <tr class="wperp-form-group inline-edit-row">
                                <td colspan="9" style="text-align: left;">
                                    <label>{{ __('Particulars', 'erp') }}</label>
                                    <textarea v-model="particulars" rows="4" maxlength="250" class="wperp-form-field display-flex" :placeholder="__('Particulars', 'erp')"></textarea>
                                </td>
                            </tr>
                            <tr class="inline-edit-row">
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
                            <tr class="add-attachment-row inline-edit-row" >
                                <td colspan="9" style="text-align: left;">
                                    <div class="attachment-container">
                                        <label class="col--attachement">{{ __('Attachment', 'erp') }}</label>
                                        <file-upload v-model="attachments" url="/invoices/attachments"/>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="inline-edit-row">
                                <td v-if="orderToPurchase()" colspan="9" style="text-align: right;">
                                    <combo-button :options="[{ id: 'update', text: __('Save Conversion', 'erp') }]" />
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
    import SelectVendors from 'admin/components/people/SelectVendors.vue';
    import ShowErrors from 'admin/components/base/ShowErrors.vue';
    import MultiSelect from 'admin/components/select/MultiSelect.vue';
    export default {
        name: 'PurchaseCreate',
        components: {
            Datepicker,
            FileUpload,
            ComboButton,
            SelectVendors,
            ShowErrors,
            MultiSelect
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
                    { id: 'save', text: __('Save', 'erp') },
                    // {id: 'send_create', text: __('Create and Send', 'erp')},
                    { id: 'new_create', text: __('Save and New', 'erp') },
                    { id: 'draft', text: __('Save as Draft', 'erp') }
                ],
                updateButtons: [
                    { id: 'update', text: __('Update', 'erp') },
                    // {id: 'send_update', text: __('Update and Send', 'erp')},
                    { id: 'new_update', text: __('Update and New', 'erp') },
                    { id: 'draft', text: __('Save as Draft', 'erp') }
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
                page_title      : '',
                taxRates        : [],
                taxRate         : '',
            };
        },
        watch: {
            'basic_fields.vendor'() {
                if (!this.editMode) {
                    this.getvendorData();
                }
            },
            products() {
                if (this.$route.params.id) {
                    this.transactionLines.map(item => {
                        let product = this.products.filter(p => {
                            return p.id === item.product_id
                        })
                        item.product = product[0]
                        item.applyTax = parseFloat(item.tax) > 0
                        item.taxAmount = item.tax ? parseFloat(item.tax) : 0
                        item.tax_rate = parseFloat(item.tax_rate)
                        item.unitPrice = parseFloat(item.price)
                        item.tax_cat_id = product.length ? product[0].tax_cat_id : null
                    })
                }
            },
            taxRates() {
                if (this.$route.params.id) {
                    let rate = this.taxZones.filter( item=>  parseInt(item.id) === this.taxRate )
                    this.taxRate = rate[0]
                }
            }
        },
        mounted(){

        },
        computed: {
            ...mapState({ actionType: state => state.combo.btnID }),
            totalAmount(){
                let total = 0
                this.transactionLines.forEach(item => {
                    if(item.qty && item.unitPrice){
                        total += parseInt(item.qty) * parseFloat( item.unitPrice )
                    }
                })
                return total + this.taxTotalAmount;
            },
            taxTotalAmount(){
                if(!this.taxRate) return 0 ;
                let rates = this.taxRates.filter(item => {
                    return this.taxRate.id == item.tax_rate_id
                })
                let totalTax = 0
                this.transactionLines.map(item => {
                    if(item.product && item.qty && item.unitPrice ){
                        if(item.applyTax && item.tax_cat_id && rates.length) {
                            let taxRate =  rates.filter( r =>  r.sales_tax_category_id ==  item.tax_cat_id)
                            taxRate = taxRate.length ? taxRate[0].tax_rate : 0
                            item.taxAmount  =  ((item.qty * item.unitPrice) * taxRate) / 100;
                            item.tax_rate  =  taxRate;
                            totalTax += item.taxAmount
                        }
                    }
                })
                return totalTax ;
            },
            taxZones(){
                let zones = []
                let id = ''
                this.taxRates.forEach( item => {
                    zones[item.tax_rate_id] = {
                        id                    : item.tax_rate_id,
                        name                  : item.tax_rate_name,
                        tax_rate              : item.tax_rate,
                        agency_id             : item.agency_id,
                        tax_cat_id            : item.sales_tax_category_id,
                    }
                })
                return zones;
            }
        },
        created() {
            if (this.$route.name === 'PurchaseOrderCreate') {
                this.page_title = __( 'Purchase Order', 'erp' );
                this.purchase_order = 1;
            } else {
                this.page_title = __( 'Purchase', 'erp' );
                if (this.$route.query.convert) {
                    this.purchase_order = 1;
                } else {
                    this.purchase_order = 0;
                }
            }
            this.prepareDataLoad();
        },
        methods: {
            setLineData(line){
                line.qty  = 1

                if (this.$route.params.id) {
                    line.unitPrice = parseFloat(line.product.cost_price);
                } else {
                    line.unitPrice = parseFloat(line.product.unitPrice);
                }
                line.amount     =  line.qty * line.unitPrice
                line.tax_cat_id = line.product.tax_cat_id
                if(parseInt(line.product.tax_cat_id)){
                    line.applyTax  = true
                    line.taxAmount = 0
                }
                // this.$forceUpdate();
            },
            lineUpdate(index){
                let line = this.transactionLines[index]
                line.amount =  parseInt(line.qty) * parseFloat( line.unitPrice )
                this.$set(this.transactionLines, index, line)
            },
            disableLineTax(index){
                let line = this.transactionLines[index]
                line.taxAmount = !line.applyTax ? 0 : line.taxAmount
                this.$set(this.transactionLines, index, line)
            },
            removeRow(index){
                this.transactionLines.splice(index, 1) ;
            },

            async prepareDataLoad() {
                /**
                 * ----------------------------------------------
                 * check if editing
                 * -----------------------------------------------
                 */
                if (this.$route.params.id) {
                    this.editMode  = true;
                    this.voucherNo = this.$route.params.id;
                    const [request] = await Promise.all([
                        HTTP.get(`/purchases/${this.$route.params.id}`)
                    ]);
                    const canEdit = Boolean(Number(request.data.editable));
                    if (!canEdit) {
                        this.showAlert('error', 'Can\'t edit');
                        return;
                    }
                    const purchase_data = request.data;
                    if (purchase_data) {
                        this.getProducts(purchase_data.vendor_id);
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
                this.transactionLines.map( item => {
                    let product =  this.products.filter( p => { return p.id == item.product_id})
                    item.product = product[0]
                    item.applyTax = parseFloat(item.tax) > 0
                    item.taxAmount = item.tax ? parseFloat(item.tax) : 0
                    item.tax_rate =  parseFloat(item.tax_rate)
                    item.unitPrice = parseFloat(item.price)
                    item.tax_cat_id = product.length ? product[0].tax_cat_id : null
                })
                this.particulars                  = purchase.particulars;
                this.attachments                  = purchase.attachments;
                this.taxRate                      = parseInt(purchase.tax_zone_id)    // for set is temporary. when tax zone loaded then set tax rate object
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
            getProducts(vendor_id) {
                this.products = [];
                if (!vendor_id) {
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
                            id               : element.id,
                            name             : element.name,
                            unitPrice        : element.cost_price,
                            tax_cat_id       : parseInt(element.tax_cat_id) || null,
                            product_type_name: element.product_type_name
                        });
                    });
                    this.getTaxRates();
                    this.$store.dispatch('spinner/setSpinner', false);
                }).catch(error => {
                    this.$store.dispatch('spinner/setSpinner', false);
                    throw error;
                });
            },
            getTaxRates(){
                HTTP.get('/taxes/summary').then((response) => {
                    this.taxRates = response.data
                })
            },

            getvendorData() {
                const vendor_id = this.basic_fields.vendor.id;
                if (!vendor_id) {
                    this.basic_fields.billing_address = '';
                    return;
                }
                HTTP.get(`/people/${vendor_id}`).then(response => {
                    const billing = response.data;
                    let street_1    = billing.street_1 ? billing.street_1 + ',' : '';
                    let street_2    = billing.street_2 ? billing.street_2 : '';
                    let city        = billing.city ? billing.city : '';
                    let state       = billing.state ? billing.state + ',' : '';
                    let postal_code = billing.postal_code ? billing.postal_code : '';
                    let country     = billing.country ? billing.country : '';
                    const address = `${street_1} ${street_2} \n${city} \n${state} ${postal_code} \n${country}`;
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
                    if (Object.prototype.hasOwnProperty.call(line, 'product')) {
                        lineItems.push({
                            product_id: line.product.id,
                            qty       : line.qty,
                            unit_price: line.unitPrice,
                            item_total: line.amount,
                            tax_cat_id: line.tax_cat_id,
                            apply_tax: line.applyTax,
                            tax_amount: line.taxAmount,
                        });
                    }
                });
                return lineItems;
            },
            updatePurchase(requestData) {
                HTTP.put(`/purchases/${this.voucherNo}`, requestData).then(res => {
                    this.$store.dispatch('spinner/setSpinner', false);
                    let message = __( 'Purchase Updated!', 'erp' );
                    if (this.orderToPurchase()) {
                        message = __( 'Conversion Successful!', 'erp' );
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
                    convert        : this.$route.query.convert,
                    tax_rate       : this.taxRate
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
                    this.form_errors.push(__('Vendor Name is required.', 'erp'));
                }
                if (!this.basic_fields.trn_date) {
                    this.form_errors.push(__('Transaction Date is required.', 'erp'));
                }
                if (!this.basic_fields.due_date) {
                    this.form_errors.push(__('Due Date is required.', 'erp'));
                }
                if (!this.totalAmount) {
                    this.form_errors.push(__('Total amount can\'t be zero.', 'erp'));
                }
                if (this.noFulfillLines(this.transactionLines, 'product')) {
                    this.form_errors.push(__('Please select a product.', 'erp'));
                }
            }
        }
    };
</script>

<style lang="less" scoped>
    .purchase-create {
        .dropdown {
            width: 100%;
        }

        .col--product {
            min-width: 500px;

            @media screen and ( max-width: 782px ) {
                min-width: 60%;
            }
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

    .wperp-form-table {
        .col--tax {
            input {
                width: initial;
                padding: 0 !important;
                border-color: rgba(26, 158, 212, 0.45);
            }
        }
    }
</style>
