<template>
    <tr>
        <th scope="row" class="col--check with-multiselect prodcut-select">
            <multi-select v-model="line.selectedProduct" :options="products" />
        </th>
        <td class="col--qty column-primary">
            <input type="number" :class="{'has-err': errors.first('qty')}"
                v-validate="'required'"
                v-model="line.qty"
                @keyup="calculateAmount"
                name="qty"
                class="wperp-form-field" required>
        </td>
        <td class="col--uni_price" data-colname="Unit Price">
            <input type="text" v-model="line.unitPrice" @keyup="calculateAmount" class="wperp-form-field">
        </td>
        <td class="col--discount" data-colname="Discount">
            <div class="wperp-has-addon">
                <input type="text" v-model="line.discount" @keyup="calculateAmount" class="wperp-form-field">
                <span class="wperp-addon">%</span>
            </div>
        </td>
        <td class="col--tax-rate" data-colname="Tax Rate(%)">
            <input type="text" v-model="line.taxRate" class="wperp-form-field" readonly>
        </td>
        <td class="col--tax-amount" data-colname="Tax Amount">
            <input type="text" v-model="line.taxAmount" class="wperp-form-field" readonly>
        </td>
        <td class="col--amount" data-colname="Amount">
            <input type="text" v-model="line.totalAmount" class="wperp-form-field" readonly>
        </td>
        <td class="col--actions delete-row" data-colname="Action">
            <span class="wperp-btn" @click="removeRow"><i class="flaticon-trash"></i></span>
        </td>
    </tr>
</template>

<script>
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
                default: () => {
                    return {
                        qty: 1,
                        selectedProduct: [],
                        unitPrice: 0,
                        discount: 0,
                        agencyId: 0,
                        taxRate: 0,
                        taxAmount: 0,
                        totalAmount: 0
                    };
                }
            }
        },

        components: {
            MultiSelect
        },

        watch: {
            'line.selectedProduct'() {
                this.setProductInfo();
            }
        },

        methods: {
            calculateAmount() {
            
                let field = this.line;

                let amount = parseInt(field.qty) * parseFloat(field.unitPrice);
                let discount = parseFloat(field.discount);
                let taxAmount = ( amount * parseFloat(field.taxRate) ) / 100;

                field.totalAmount = amount;

                if ( isNaN(amount) ) {
                    field.totalAmount = 0;
                    return;
                }

                if ( discount ) {
                    discount = (amount * discount) / 100;
                    amount = amount - discount;                   
                }

                if ( taxAmount ) {
                    amount = amount + taxAmount;
                }

                field.totalAmount = amount.toFixed(2);
                field.taxAmount = taxAmount.toFixed(2);

                this.$root.$emit('total-updated', field.totalAmount);
                this.$forceUpdate();
            },

            setProductInfo() {                
                let product_id = this.line.selectedProduct.id;

                if ( ! product_id ) return;

                let product = this.products.find(element => {
                    return element.id == product_id;
                });

                this.line.unitPrice = parseFloat(product.sale_price);
                this.line.product_type_name = this.line.selectedProduct.product_type_name;
                this.line.agencyId = this.line.selectedProduct.agency_id;
                this.line.taxRate = this.line.selectedProduct.tax_rate;
            },

            removeRow() {
                this.$root.$emit('remove-row', this.$vnode.key)
            }
        }
    }
</script>

<style lang="less">
    .with-multiselect {
        &.prodcut-select {
            .multiselect__tags {
                min-height: 43px;
                padding: 3px 30px 0 8px;
            }

            .multiselect__placeholder {
                margin: 8px 0;
            }

            .multiselect__select {
                height: 41px;
            }

            .multiselect__single {
                line-height: 37px;
            }
        }
    }
</style>
