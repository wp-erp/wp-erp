<template>
    <div class="app-money-transfer">
        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Transfer Money', 'erp') }}</h2>
                    <router-link class="wperp-btn btn--primary" :to="{ name: 'NewTransfer'}">{{ __('Add new', 'erp') }}</router-link>
                </div>
            </div>
        </div>
        <!-- End .header-section -->
        <list-table
            tableClass="wperp-table table-striped table-dark widefat table2 money-transfer-list"
            action-column="actions"
            :columns="columns"
            :rows="transfer_list">
            <template slot="voucher" slot-scope="data">
                <strong>
                    <router-link :to="{ name: 'SingleTransfer', params: { id: data.row.id }}">
                        #{{ data.row.voucher }}
                    </router-link>
                </strong>
            </template>
        </list-table>
    </div>

</template>

<script>
import HTTP from 'admin/http';
import ListTable from 'admin/components/list-table/ListTable.vue';

export default {
    name: 'Transfers',

    components: {
        ListTable
    },

    data() {
        return {
            transferFrom  : { balance : 0 },
            transferTo    : { balance : 0 },
            accounts      : [],
            fa            : [],
            ta            : [],
            transferdate  : erp_acct_var.current_date, /* global erp_acct_var */
            particulars   : '',
            amount        : '',
            money_transfer: false,
            transfer_list : [],
            columns       : {
                voucher: { label: 'Voucher No' },
                ac_from: { label: 'Account From' },
                amount : { label: 'Amount' },
                ac_to  : { label: 'Account To' }
            }
        };
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.get_transfer_list();
    },

    methods: {
        get_transfer_list() {
            HTTP.get('/accounts/list').then(res => {
                this.transfer_list = res.data;
                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        }
    }
};
</script>

<style lang="less">
    .app-money-transfer {
        .table-container {
            width: 600px;
        }

        .check-column {
            padding: 20px !important;
        }

        .actions {
            text-align: right;
        }

        .col--actions {
            float: right !important;
        }
    }
</style>
