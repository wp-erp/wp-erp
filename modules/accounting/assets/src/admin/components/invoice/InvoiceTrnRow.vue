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
            <input type="number" v-model="line.unitPrice" @keyup="respondAtChange" class="wperp-form-field">
        </td>
        <td class="col--amount" data-colname="Amount">
            <input type="number" v-model="line.totalAmount" class="wperp-form-field" readonly>
        </td>
        <td class="col--tax" data-colname="Tax">
            <input type="checkbox" v-model="line.applyTax" @change="respondAtChange" class="wperp-form-field">
            <span style="color:blueviolet">{{ line.taxAmount }}</span>
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
                if ( this.line.qty ) {
                    this.getTaxRate();
                    this.respondAtChange();
                }
            }
        },

        computed: mapState({
            taxRateID: state => state.sales.taxRateID
        }),

        methods: {
            respondAtChange() {
                this.calculateTax();
                this.calculateAmount()
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

            calculateTax() {                
                let amount = parseInt(this.line.qty) * parseFloat(this.line.unitPrice);
                let taxAmount = (amount * this.taxRate) / 100;

                this.line.taxAmount = 0;

                if (this.line.applyTax) {
                    this.line.taxAmount = taxAmount.toFixed(2);
                }
            },

            calculateAmount() {
                let field = this.line;

                let amount = parseInt(field.qty) * parseFloat(field.unitPrice);
                let discount = parseFloat(field.discount);

                field.totalAmount = amount;

                if ( isNaN(amount) ) {
                    field.totalAmount = 0;
                    return;
                }

                if ( discount ) {
                    discount = (amount * discount) / 100;
                    amount = amount - discount;                   
                }

                field.totalAmount = amount.toFixed(2);

                this.$root.$emit('total-updated', field.totalAmount);
                this.$forceUpdate();
            },

            setProductInfo() {                
                let product_id = this.line.selectedProduct.id;

                if ( ! product_id ) return;

                let product = this.products.find(element => {
                    return element.id == product_id;
                });

                this.line.qty = 1;
                this.line.taxCatID = this.line.selectedProduct.tax_cat_id;
                this.line.applyTax = true;
                this.line.unitPrice = parseFloat(product.sale_price);
                this.line.product_type_name = this.line.selectedProduct.product_type_name;

                this.getTaxRate();
                this.calculateTax();
                this.calculateAmount();
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
