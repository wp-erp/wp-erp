<template>
    <tr>
        <td scope="row" class="col--check">#001</td>
        <td class="col--qty column-primary">$500.00</td>
        <td class="col--uni_price" data-colname="Unit Price">$240.00</td>
        <td class="col--amount" data-colname="Discount">
            <input type="text" name="amount" id="amount" class="text-right" value="000000" />
        </td>
        <td class="delete-row">
            <a href="#"><i class="flaticon-trash"></i></a>
        </td>
    </tr>
</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'

    export default {
        name: 'RecPaymentTrnRow',

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

</style>
