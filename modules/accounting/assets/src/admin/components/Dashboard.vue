<template>
    <div class="home">
        <div class="erp accounting-dashboard">
            <h2><span>{{ msg }}</span></h2>
            <div class="erp-acc-dashboard-1">
                <div class="erp-acc-dashboard-1-col">
                    <MetaBox :title="title1" :closable="false">
                        <h1 slot="metabox-content">Metabox Content</h1>
                    </MetaBox>
                </div>
                <div class="erp-acc-dashboard-1-col">
                    <MetaBox :title="title2" :closable="false">
                        <h1 slot="metabox-content">Metabox Content2</h1>
                    </MetaBox>
                </div>
            </div>
            <div class="erp-acc-dashboard-2">
                <div class="erp-acc-dashboard-2-col">
                    <MetaBox :title="title3" :closable="false">
                        <h1 slot="metabox-content">
                            <table>
                                <tr>
                                    <td>Coming Due</td>
                                    <td class="price">$0.00</td>
                                </tr>
                                <tr>
                                    <td>1-30 days overdue</td>
                                    <td class="price">$0.00</td>
                                </tr>
                                <tr>
                                    <td>31-60 days overdue</td>
                                    <td class="price">$0.00</td>
                                </tr>
                                <tr>
                                    <td>61-90 days overdue</td>
                                    <td class="price">$0.00</td>
                                </tr>
                                <tr>
                                    <td>&gt; 90 days overdue</td>
                                    <td class="price">$0.00</td>
                                </tr>
                            </table>
                        </h1>
                    </MetaBox>
                </div>
                <div class="erp-acc-dashboard-2-col">
                    <MetaBox :title="title4" :closable="false">
                        <div slot="metabox-content">
                            <table>
                                <tr>
                                    <td>Coming Due</td>
                                    <td class="price">$0.00</td>
                                </tr>
                                <tr>
                                    <td>1-30 days overdue</td>
                                    <td class="price">$0.00</td>
                                </tr>
                                <tr>
                                    <td>31-60 days overdue</td>
                                    <td class="price">$0.00</td>
                                </tr>
                                <tr>
                                    <td>61-90 days overdue</td>
                                    <td class="price">$0.00</td>
                                </tr>
                                <tr>
                                    <td>&gt; 90 days overdue</td>
                                    <td class="price">$0.00</td>
                                </tr>
                            </table>
                        </div>
                    </MetaBox>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MetaBox from './WP/MetaBox.vue'

    export default {

        name: 'Dashboard',

        components: {
            MetaBox
        },

        data () {
            return {
                title1: 'Income & Expenses',
                title2: 'Bank Accounts',
                title3: 'Invoices owed to you',
                title4: 'Bills to pay',
                closable: true,
                msg: 'Accounting'
            }
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    .erp-acc-dashboard-1 {
        display: flex;
    }

    .erp-acc-dashboard-1-col {
        margin: 5px;
        flex: 50%;
    }

    .erp-acc-dashboard-2 {
        display: flex;
    }

    .erp-acc-dashboard-2-col {
        margin: 5px;
        flex: 50%;
    }

    h1 {
        padding: 0px;
        line-height: 29px;
    }

    .erp.accounting-dashboard .postbox.bank-balance .inside {
        padding-bottom: 0;
        margin-bottom: 0;
    }
    .erp.accounting-dashboard .postbox.bank-balance ul {
        margin-bottom: 0;
    }
    .erp.accounting-dashboard .postbox.bank-balance li {
        margin-bottom: 6px;
        padding: 0 10px 5px 10px;
    }
    .erp.accounting-dashboard .postbox.bank-balance li.total {
        background-color: #f5f5f5;
        border-top: 1px solid #eee;
        color: #777;
        padding: 10px 20px 12px 20px;
        margin: 0 -12px 0 -12px;
        font-weight: bold;
    }

    .erp.accounting-dashboard .postbox.bank-balance span.price {
        float: right;
    }

    .erp.accounting-dashboard .postbox table {
        width: 100%;
        word-wrap: break-word;
        border-spacing: 0;
    }

    .erp.accounting-dashboard .postbox table tbody tr:nth-child(odd) {
        background-color: #f9f9f9;
    }

    .erp.accounting-dashboard .postbox table td {
        padding: 8px 10px;
        vertical-align: top;
        color: #555;
        line-height: 1.5em;
        font-size: 13px;
    }

    .erp.accounting-dashboard .postbox table td.price {
        text-align: right;
    }

    tr:nth-child(odd) {
        background-color: #f2f2f2
    }

    td.price {
        text-align: right;
        margin-right: 2px;
    }
</style>
