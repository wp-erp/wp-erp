<template>
    <tr>
        <th scope="row" class="col--check with-multiselect prodcut-select">
            <multi-select v-model="rowFields.selectedProduct" :options="products" />
        </th>
        <td class="col--qty column-primary">
            <input type="number" v-model="rowFields.qty" @keyup="calculateAmount" class="wperp-form-field">
        </td>
        <td class="col--uni_price" data-colname="Unit Price">
            <input type="text" v-model="rowFields.unitPrice" @keyup="calculateAmount" class="wperp-form-field">
        </td>
        <td class="col--discount" data-colname="Discount">
            <div class="wperp-has-addon">
                <input type="text" v-model="rowFields.discount" @keyup="calculateAmount" class="wperp-form-field">
                <span class="wperp-addon">%</span>
            </div>
        </td>
        <td class="col--penholder" data-colname="Tax(%)">
            <div class="wperp-custom-select">
                <select name="pen-holder" id="pen-holder" class="wperp-form-field">
                    <option value="0">Select</option>
                    <option value="1">Gov</option>
                    <option value="2">Private</option>
                </select>
                <i class="flaticon-arrow-down-sign-to-navigate"></i>
            </div>
        </td>
        <td class="col--tax-amount" data-colname="Tax Amount">
            <input type="text" name="tax-amount" id="tax-amount" class="wperp-form-field" value="$240.00">
        </td>
        <td class="col--amount" data-colname="Amount">
            <input type="text" v-model="rowFields.totalAmount" readonly class="wperp-form-field">
        </td>
        <td class="col--actions delete-row" data-colname="Action">
            <span class="wperp-btn"><i class="flaticon-trash"></i></span>
        </td>
    </tr>
</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/Select/MultiSelect.vue'

    export default {
        name: 'TransactionRow',

        props: {
            products: {
                type: Array,
                default: () => []
            }
        },

        components: {
            MultiSelect
        },

        data() {
            return {
                rowFields: {
                    selectedProduct: [],
                    qty: 1,
                    unitPrice: 0,
                    discount: 0,
                    totalAmount: 0
                }
            }
        },

        watch: {
            'rowFields.selectedProduct'() {
                this.getSalePrice();
            }
        },

        methods: {
            calculateAmount() {
                let field = this.rowFields;
                let amount = parseFloat(field.qty) * parseFloat(field.unitPrice);
                let discount = parseFloat(field.discount);

                if ( isNaN(amount) ) {
                    this.rowFields.totalAmount = 0;
                    return;
                }

                if ( discount ) {
                    amount = (amount * parseFloat(field.discount)) / 100;
                    console.log(amount);
                    
                }

                field.totalAmount = amount;
            },

            getSalePrice() {
                let product_id = this.rowFields.selectedProduct.id;

                if ( ! product_id ) return;

                HTTP.get(`/products/${product_id}`).then((response) => {
                    this.unitPrice = response.data.sale_price;
                });
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