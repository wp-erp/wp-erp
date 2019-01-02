<template>
    <tr>
        <th scope="row" class="col--check with-multiselect prodcut-select">
            <multi-select v-model="line.selectedProduct" :options="products" />
        </th>
        <td class="col--qty column-primary">
            <input min="0" type="number" :class="{'has-err': errors.first('qty')}"
                   v-validate="'required'"
                   v-model="line.qty"
                   @keyup="calculateAmount"
                   name="qty"
                   class="wperp-form-field">
        </td>
        <td class="col--uni_price" data-colname="Unit Price">
            <input min="0" type="number" v-model="line.unitPrice" @keyup="calculateAmount" class="wperp-form-field">
        </td>
        <td class="col--amount" data-colname="Amount">
            <input type="number" v-model="line.totalAmount" class="wperp-form-field" readonly>
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
        name: 'PurchaseRow',

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
                this.line.qty = 1;
                this.line.unitPrice = this.line.selectedProduct.unitPrice;
                this.calculateAmount();
            }
        },

        methods: {
            calculateAmount() {
                let field = this.line;

                let amount = parseFloat(field.qty) * parseFloat(field.unitPrice);

                field.totalAmount = amount;

                if ( isNaN(amount) ) {
                    field.totalAmount = 0;
                    return;
                }

                field.totalAmount = amount.toFixed(2);

                this.$root.$emit('total-updated', field.totalAmount);
                this.$forceUpdate();
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
