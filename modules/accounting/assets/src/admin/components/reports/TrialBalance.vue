<template>
    <div>
        <h4>Trial Balance</h4>

         <list-table
            tableClass="wperp-table table-striped table-dark widefat"
            :columns="columns"
            :rows="rows">
            <template slot="debit" slot-scope="data">
                {{ Math.sign(data.row.balance) === 1 ? data.row.balance : '' }}
            </template>
            <template slot="credit" slot-scope="data">
                {{ Math.sign(data.row.balance) === -1 ? Math.abs(data.row.balance) : '' }}
            </template>
        </list-table>

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import ListTable from 'admin/components/list-table/ListTable.vue'

    export default {
        name: 'TrialBalance',

        components: {
            ListTable
        },

        data() {
            return {
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                        img: erp_acct_var.erp_assets + '/images/trash.png'
                    }
                ],
                columns: {
                    'name': { label: 'Account Name' },
                    'debit': { label: 'Debit Total' },
                    'credit': { label: 'Credit Total' }
                },
                rows: []
            }
        },

        created() {
            this.fetchItems();
        },

        methods: {
            fetchItems() {
                this.rows = [];

                HTTP.get( '/reports/trial-balance').then( (response) => {
                    this.rows = response.data;
                })
                .catch((error) => {
                    console.log(error);
                })
                .then( () => {
                    //ready
                } );
            }
        }
    }
</script>

<style scoped>

</style>