<template>
    <div class="wperp-modal wperp-modal-open wperp-invoice-modal wperp-custom-scroll" role="dialog">
        <div class="wperp-modal-dialog" v-click-outside="outside" @click="inside">
            <div class="wperp-modal-content">
                <div class="wperp-modal-header">
                    <h4>
                        Purchase
                    </h4>
                    <div class="d-print-none">
                        <a href="#" class="wperp-btn btn--default print-btn">
                            <i class="flaticon-printer-1"></i>
                            &nbsp; Print
                        </a>
                        <!-- todo: more action has some dropdown and will implement later please consider as planning -->
                        <a href="#" class="wperp-btn btn--default">
                            <i class="flaticon-settings-work-tool"></i>
                            &nbsp; More Action
                        </a>
                    </div>
                </div>
                <div class="wperp-modal-body">
                    <div class="wperp-invoice-panel">
                        <div class="Purchase-header">
                            <div class="Purchase-logo">
                                <img :src="acct_var.acct_assets + '/images/dummy-logo.png'" alt="logo name">
                            </div>
                            <div class="Purchase-address">
                                <address>
                                    <strong>Amazon Limited</strong><br>
                                    983 Aiden Roads Suite 062<br>
                                    Address Line 2<br>
                                    1483 Theresafort Afyon<br>
                                    Turkey
                                </address>
                            </div>
                        </div>

                        <div class="Purchase-body" v-if="Purchase != null">
                            <h4>Purchase</h4>
                            <div class="wperp-row">
                                <div class="wperp-col-sm-6">
                                    <h5>Bill to:</h5>
                                    <div class="persons-info" v-if="Purchase.billing_address">
                                        <strong>{{ Purchase.customer_name }}</strong><br>
                                        {{ Purchase.billing_address.address }} <br>
                                        {{ Purchase.billing_address.state }} <br>
                                        {{ Purchase.billing_address.city }} <br>
                                        {{ Purchase.billing_address.country }}
                                    </div>
                                </div>
                                <div class="wperp-col-sm-6">
                                    <table class="Purchase-info">
                                        <tr>
                                            <th>Purchase No.</th>
                                            <td>INV-0001</td>
                                        </tr>
                                        <tr>
                                            <th>Purchase Date:</th>
                                            <td>{{ Purchase.created_at }}</td>
                                        </tr>
                                        <tr>
                                            <th>Due Date:</th>
                                            <td>{{ Purchase.due_date }}</td>
                                        </tr>
                                        <tr>
                                            <th>Amount Due:</th>
                                            <td>17-10-2018</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="wperp-invoice-table">
                            <table class="wperp-table wperp-form-table Purchase-table">
                                <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>City</th>
                                    <th>Unit Price</th>
                                    <th>Discount</th>
                                    <th>Tax</th>
                                    <th>Tax Amount</th>
                                    <th>Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th>Buy Fabrics</th>
                                    <td>10</td>
                                    <td>$1500.00</td>
                                    <td>10%</td>
                                    <td>0%</td>
                                    <td>$0.00</td>
                                    <td>$15,000.00</td>
                                </tr>
                                <tr>
                                    <th>Buy Fabrics</th>
                                    <td>10</td>
                                    <td>$1500.00</td>
                                    <td>10%</td>
                                    <td>0%</td>
                                    <td>$0.00</td>
                                    <td>$15,000.00</td>
                                </tr>
                                <tr>
                                    <th>Buy Fabrics</th>
                                    <td>10</td>
                                    <td>$1500.00</td>
                                    <td>10%</td>
                                    <td>0%</td>
                                    <td>$0.00</td>
                                    <td>$15,000.00</td>
                                </tr><tr>
                                    <th>Buy Fabrics</th>
                                    <td>10</td>
                                    <td>$1500.00</td>
                                    <td>10%</td>
                                    <td>0%</td>
                                    <td>$0.00</td>
                                    <td>$15,000.00</td>
                                </tr><tr>
                                    <th>Buy Fabrics</th>
                                    <td>10</td>
                                    <td>$1500.00</td>
                                    <td>10%</td>
                                    <td>0%</td>
                                    <td>$0.00</td>
                                    <td>$15,000.00</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="7">
                                        <ul>
                                            <li><span>Subtotal:</span> $15,000.00</li>
                                            <li><span>Total:</span> $15,000.00</li>
                                            <li><span>Total Related Payments:</span> $15,000.00</li>
                                        </ul>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                    <div class="Purchase-attachments d-print-none">
                        <h4>Attachments</h4>
                        <a class="attachment-item" href="#">
                            <img :src="acct_var.acct_assets + '/images/img-thumb.png'" alt="image name">
                            <div class="attachment-meta">
                                <span>File name with extension</span><br>
                                <span class="text-muted">file size</span>
                            </div>
                        </a>
                        <a class="attachment-item" href="#">
                            <img :src="acct_var.acct_assets + '/images/doc-thumb.png'" alt="image name">
                            <div class="attachment-meta">
                                <span>File name with extension</span><br>
                                <span class="text-muted">file size</span>
                            </div>
                        </a>
                        <a class="attachment-item" href="#">
                            <img :src="acct_var.acct_assets + '/images/pdf-thumb.png'" alt="image name">
                            <div class="attachment-meta">
                                <span>File name with extension</span><br>
                                <span class="text-muted">file size</span>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http';

    export default {
        name: 'PurchaseModal',

        data() {
            return {
                Purchase: [],
                acct_var: erp_acct_var
            }
        },

        created() {
            let Purchase_id = 1;

            HTTP.get(`/Purchases/${Purchase_id}`).then((response) => {
                console.log(response.data);

                this.Purchase = response.data;
            });
        },

        methods: {
            inside() {},

            outside() {
                this.$root.$emit('purchase-modal-close');
            }
        },

    }
</script>

<style scoped>

</style>
