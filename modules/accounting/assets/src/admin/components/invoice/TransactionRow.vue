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
                class="wperp-form-field">
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
        <td class="col--penholder" data-colname="Tax(%)">
            <div class="wperp-custom-select">
                <select name="pen-holder" class="wperp-form-field">
                    <option value="0">Select</option>
                    <option value="1">Gov</option>
                    <option value="2">Private</option>
                </select>
                <i class="flaticon-arrow-down-sign-to-navigate"></i>
            </div>
        </td>
        <td class="col--tax-amount" data-colname="Tax Amount">
            <input type="text" v-model="line.taxAmount" @keyup="calculateAmount" class="wperp-form-field">
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
        name: 'TransactionRow',

        props: {
            products: {
                type: Array,
                default: () => []
            },

            line: {
                type: Object,
                default: () => {
                    return {
                        qty: 0,
                        selectedProduct: [],
                        unitPrice: 0,
                        discount: 0,
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
                this.getSalePrice();
            }
        },

        methods: {
            calculateAmount() {
                let field = this.line;

                let amount = parseFloat(field.qty) * parseFloat(field.unitPrice);
                let discount = parseFloat(field.discount);
                let taxAmount = parseFloat(field.taxAmount);

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
                    amount = amount - taxAmount;
                }

                field.totalAmount = amount.toFixed(2);

                this.$root.$emit('total-updated', field.totalAmount);
                this.$forceUpdate();
            },

            getSalePrice() {
                let product_id = this.line.selectedProduct.id;

                if ( ! product_id ) return;

                HTTP.get(`/products/${product_id}`).then((response) => {
                    this.unitPrice = response.data.sale_price;
                });
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
