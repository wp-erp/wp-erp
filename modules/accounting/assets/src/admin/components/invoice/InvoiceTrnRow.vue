<template>
    <tr>
        <th scope="row" class="col--products with-multiselect product-select">
            <multi-select v-model="line.selectedProduct" :options="products" @input="setProductInfo" />
        </th>
        <td class="col--qty column-primary">
            <input type="number"
                v-model="line.qty"
                @keyup="respondAtChange"
                name="qty"
                class="wperp-form-field" :required="line.selectedProduct ? true : false">
        </td>
        <td class="col--uni_price" :data-colname="__('Unit Price', 'erp')">
            <input type="number" min="0" step="0.01"
                v-model="line.unitPrice"
                @keyup="respondAtChange" class="wperp-form-field" :required="line.selectedProduct ? true : false">
        </td>
        <td class="col--amount" :data-colname="__('Amount', 'erp')">
            <input type="number" min="0" step="0.01" v-model="line.amount" class="wperp-form-field" readonly>
        </td>
        <td class="col--tax" :data-colname="__('Tax', 'erp')">
            <input type="checkbox" v-model="line.applyTax" @change="respondAtChange" class="wperp-form-field">

            <template v-if="'1' == debugMode">
                <span style="color:blueviolet" v-text="line.taxAmount"></span>
                <span style="color:#f44336" v-text="line.discount"></span>
            </template>
        </td>
        <td class="col--actions delete-row" :data-colname="__('Action', 'erp')">
            <span class="wperp-btn" @click="removeRow"><i class="flaticon-trash"></i></span>
        </td>
    </tr>
</template>

<script>
import { mapState } from 'vuex';

import MultiSelect from 'admin/components/select/MultiSelect.vue';

export default {
    name: 'InvoiceTrnRow',

    props: {
        products: {
            type: Array,
            default: () => []
        },

        line: {
            type: Object,
            default: () => {}
        },

        taxSummary: {
            type: Array,
            default: () => []
        }
    },

    components: {
        MultiSelect
    },

    data() {
        return {
            taxRate  : 0,
            taxAmount: 0,
            taxCatID : 0,
            debugMode: erp_acct_var.erp_debug_mode /* global erp_acct_var */
        };
    },

    watch: {
        taxRateID() {
            this.getTaxRate();
            this.respondAtChange();
        },

        discount() {
            this.respondAtChange();
        },

        invoiceTotalAmount() {
            this.calculateDiscount();
        }
    },

    computed: mapState({
        taxRateID         : state => state.sales.taxRateID,
        discount          : state => state.sales.discount,
        invoiceTotalAmount: state => state.sales.invoiceTotalAmount
    }),

    created() {
        // check if editing
        if (this.$route.params.id) {
            this.prepareRowEdit(this.line);
            this.getTaxRate();
        }
    },

    methods: {
        prepareRowEdit(row) {
            // format invoice data which comes from database, to mactch with line items
            row.selectedProduct = { id: parseInt(row.product_id), name: row.name };
            row.taxCatID  = row.tax_cat_id;
            row.unitPrice = parseFloat(row.unit_price);
            row.applyTax  = true;
            row.taxAmount = row.tax;
            row.amount    = parseInt(row.qty) * parseFloat(row.unit_price);
        },

        respondAtChange() {
            this.calculateDiscount();
            this.calculateTax();
            this.calculateAmount();
        },

        getAmount() {
            // lots of reset
            if (!this.line.qty) {
                this.line.qty = 0;
            }

            if (!this.line.qty || !this.line.unitPrice) {
                this.line.discount  = 0;
                this.line.taxAmount = 0;
                this.line.amount    = 0;

                return false;
            }

            // Set Amount
            return parseInt(this.line.qty) * parseFloat(this.line.unitPrice);
        },

        getTaxRate() {
            /**
                 * |-------------------------------------------------------------------------
                 * * taxSummary: ( props ) The tax summary result from database
                 * * tax: Every item in taxSummary ( loop )
                 * * this.line: Think it is as `product` in every row
                 * * taxRateID: Selected value from `Tax Rate Dropdown` dropdown
                 * |-------------------------------------------------------------------------
                 */
            const taxInfo = this.taxSummary.find(tax => {
                if (tax.sales_tax_category_id === this.line.taxCatID && tax.tax_rate_id === this.taxRateID) {
                    return tax;
                }
            });

            this.taxRate = 0;

            if (taxInfo) {
                this.taxRate = parseFloat(taxInfo.tax_rate);
            }

            this.line.taxRate = this.taxRate;
        },

        calculateDiscount() {
            const amount = this.getAmount();
            if (!amount) return;

            const disAmount = this.discount * amount;

            let discount = 0;

            if (disAmount) {
                discount = disAmount / this.invoiceTotalAmount;
            }

            this.line.discount = discount;
        },

        calculateTax() {
            const amount = this.getAmount();
            if (!amount) return;

            const taxAmount = ((amount - this.line.discount) * this.taxRate) / 100;

            this.line.taxAmount = 0;

            // If tax checkbox is checked
            if (this.line.applyTax) {
                this.line.taxAmount = taxAmount.toFixed(2);
            }
        },

        calculateAmount() {
            const amount = this.getAmount();
            if (!amount) return;

            this.line.amount = amount;

            // Send amount to parent for total calculation
            this.$root.$emit('total-updated', amount);
            this.$forceUpdate(); // why? should use computed? or vue.set()?
        },

        setProductInfo() {
            if (!this.line.selectedProduct) {
                return;
            }

            const product_id = this.line.selectedProduct.id;

            if (!product_id) return;

            // Get full selected product object by selected product ID
            const product = this.products.find(element => {
                return element.id === product_id;
            });

            this.line.qty               = 1;
            this.line.taxCatID          = product.tax_cat_id;
            this.line.unitPrice         = parseFloat(product.sale_price);
            this.line.product_type_name = product.product_type_name;

            if (product.tax_cat_id) {
                this.line.applyTax = true;
            }

            this.getTaxRate();
            this.respondAtChange();
        },

        removeRow() {
            this.$root.$emit('remove-row', this.$vnode.key);
        }
    }
};
</script>

<style lang="less" scoped>
    .wperp-form-table {
        .col--tax {
            input {
                width: initial;
                padding: 0 !important;
                border-color: rgba(26, 158, 212, 0.45);
            }
        }
    }
    .product-select {
        font-weight: normal !important;
    }
</style>
