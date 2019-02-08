<template>
    <tr>
        <th scope="row" class="col--product with-multiselect prodcut-select">
            <multi-select v-model="line.selectedProduct" :options="products" />
        </th>
        <td class="col--qty column-primary">
            <input type="number" :class="{'has-err': errors.first('qty')}"
                v-validate="'required'"
                v-model="line.qty"
                @keyup="respondAtChange"
                name="qty"
                class="wperp-form-field" required>
        </td>
        <td class="col--uni_price" data-colname="Unit Price">
            <input type="number" min="0" step="0.01" v-model="line.unitPrice" @keyup="respondAtChange" class="wperp-form-field">
        </td>
        <td class="col--amount" data-colname="Amount">
            <input type="number" min="0" step="0.01" v-model="line.amount" class="wperp-form-field" readonly>
        </td>
        <td class="col--tax" data-colname="Tax">
            <input type="checkbox" v-model="line.applyTax" @change="respondAtChange" class="wperp-form-field">

            <span style="color:blueviolet" v-text="line.taxAmount"></span>
            <span style="color:#f44336" v-text="line.discount"></span>
        </td>
        <td class="col--actions delete-row" data-colname="Action">
            <span class="wperp-btn" @click="removeRow"><i class="flaticon-trash"></i></span>
        </td>
    </tr>
</template>

<script>
    import { mapState, mapActions } from 'vuex'

    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'

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
            },
        },

        components: {
            MultiSelect
        },

        data() {
            return {
                taxRate: 0,
                taxAmount: 0,
                taxCatID: 0
            }
        },

        watch: {
            'line.selectedProduct'() {
                this.setProductInfo();
            },
            
            taxRateID() {
                // if ( this.line.qty ) {
                    this.getTaxRate();
                    this.respondAtChange();
                // }
            },

            discount() {
                this.respondAtChange();
            },

            invoiceTotalAmount() {
                this.calculateDiscount();
            }
        },

        computed: mapState({
            taxRateID: state => state.sales.taxRateID,
            discount: state => state.sales.discount,
            invoiceTotalAmount: state => state.sales.invoiceTotalAmount
        }),

        methods: {
            respondAtChange() {
                this.calculateTax();
                this.calculateAmount();
                this.calculateDiscount();
            },

            getAmount() {
                // Reset Amount
                if ( ! this.line.qty ) {
                    this.line.qty = 0;
                }

                if ( ! this.line.qty || ! this.line.unitPrice ) {                    
                    this.line.discount = 0;
                    this.line.taxAmount = 0;
                    this.line.amount = 0;

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
                let taxInfo = this.taxSummary.find(tax => {
                    if ( tax.sales_tax_category_id == this.line.taxCatID && tax.tax_rate_id == this.taxRateID ) {
                        return tax;
                    }
                });

                this.taxRate = 0;

                if (taxInfo) {
                    this.taxRate = parseFloat(taxInfo.tax_rate);
                }
            },

            calculateDiscount() {
                let amount = this.getAmount();

                if ( ! amount ) return;

                let discount = (this.discount * amount) / this.invoiceTotalAmount;

                this.line.discount = discount.toFixed(2);
            },

            calculateTax() {
                let amount = this.getAmount();

                if ( ! amount ) return;

                let taxAmount = (amount * this.taxRate) / 100;

                this.line.taxAmount = 0;

                // If tax checkbox is checked
                if (this.line.applyTax) {
                    this.line.taxAmount = taxAmount.toFixed(2);
                }
            },

            calculateAmount() {
                let amount = this.getAmount();

                if ( ! amount ) return;

                this.line.amount = amount;
                
                // Send amount to parent for total calculation
                this.$root.$emit('total-updated', amount);
                this.$forceUpdate();
            },

            setProductInfo() {                
                let product_id = this.line.selectedProduct.id;

                if ( ! product_id ) return;

                // Get full selected product object by selected product ID
                let product = this.products.find(element => {
                    return element.id == product_id;
                });

                this.line.qty = 1;
                this.line.taxCatID = this.line.selectedProduct.tax_cat_id;
                this.line.applyTax = true;
                this.line.unitPrice = parseFloat(product.sale_price);
                this.line.product_type_name = this.line.selectedProduct.product_type_name;

                this.getTaxRate();
                this.respondAtChange();
            },

            removeRow() {
                this.$root.$emit('remove-row', this.$vnode.key)
            }
        }
    }
</script>

<style lang="less">
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
