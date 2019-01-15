<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Tax Rates</h2>
                    <a href="invoice.html" id="erp-customer-new" class="wperp-btn btn--primary" data-modal="wperp-modal-content" @click.prevent="showModal = true">
                        <i class="flaticon-add-plus-button"></i>
                        <span>Add New</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="table-container">
            <list-table
                tableClass="wp-ListTable widefat fixed tax-rate-list"
                action-column="actions"
                :columns="columns"
                :rows="row_data"
                :total-items="paginationData.totalItems"
                :total-pages="paginationData.totalPages"
                :per-page="paginationData.perPage"
                :current-page="paginationData.currentPage"
                @pagination="goToPage">
                <template slot="l_id" slot-scope="data">
                    <strong><a href="#" @click.prevent="showJournalModal(data.row.l_id)">{{ data.row.l_id }}</a></strong>
                </template>

            </list-table>
	    </div>

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import ListTable from 'admin/components/list-table/ListTable.vue'
    import NewTax    from 'admin/components/tax/NewTax.vue';

    export default {
        name: 'TaxRates',

        components: {
            ListTable,
            NewTax
        },

        data () {
            return {
                taxModal: false,
                modalParams: null,
                columns: {
                    'l_id': {label: 'ID'},
                    'l_date': {label: 'Date'},
                    'l_particulars': {label: 'Particulars'},
                    'amount': {label: 'Amount'},
                },
                rows: [],
                paginationData: {
                    totalItems: 0,
                    totalPages: 0,
                    perPage: 10,
                    currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
                }
            };
        },

        created() {
            this.$on('tax-modal-close', function() {
                this.taxModal = false;
            });

            this.fetchItems();
        },

        computed: {
            row_data(){
                let items = this.rows;
                items.map( item => {
                    item.l_id = item.id;
                    item.l_date = item.trn_date;
                    item.l_particulars = item.particulars;
                    item.amount = item.total;
                } );
                return items;
            }
        },

        methods: {
            fetchItems(){
                this.rows = [];
                HTTP.get('journals', {
                    params: {
                        per_page: this.paginationData.perPage,
                        page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                        include: 'designation'
                    }
                })
                    .then( (response) => {
                        this.rows = response.data;
                        this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                        this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                    .then( () => {
                        //ready
                    } );
            },

            goToPage(page) {
                let queries = Object.assign({}, this.$route.query);
                this.paginationData.currentPage = page;
                this.$router.push({
                    name: 'PaginateJournals',
                    params: { page: page },
                    query: queries
                });

                this.fetchItems();
            },

            newJournal() {
                this.$router.push('journals/new');
            },

            showJournalModal(journal_id) {
                this.$router.push({ name: 'SingleJournal', params: { id: journal_id } })
            },
        }
   	}
</script>
<style lang="less">
    
</style>
