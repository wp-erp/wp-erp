<template>
    <div class="app-employees">
        <h2 class="add-new-people">
            <span>{{ __('Employees', 'erp') }}</span>
        </h2>
        <list-table
            action-column="actions"
            :columns="columns"
            :rows="row_data"
            :total-items="paginationData.totalItems"
            :total-pages="paginationData.totalPages"
            :per-page="paginationData.perPage"
            :current-page="paginationData.currentPage"
            @pagination="goToPage"
            :showCb="false">
            <template slot="title" slot-scope="data">
                <strong><a href="#">{{ data.row.title }}</a></strong>
            </template>
            <template slot="employee" slot-scope="data">
                <router-link :to="{ name: 'EmployeeDetails', params: { id: data.row.id } }">{{data.row.employee}}</router-link>
            </template>
        </list-table>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import ListTable from 'admin/components/list-table/ListTable.vue';

export default {
    name: 'Employees',

    components: {
        ListTable
    },

    data() {
        return {
            bulkActions: [
                {
                    key: 'trash',
                    label: 'Move to Trash',
                    img: erp_acct_var.erp_assets + '/images/trash.png' /* global erp_acct_var */
                }
            ],
            columns: {
                employee: { label: 'Name' },
                designation: { label: 'Designation' },
                email: { label: 'Email' },
                phone: { label: 'Phone' }
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
        this.$store.dispatch('spinner/setSpinner', true);
        this.$on('modal-close', function() {
            this.showModal = false;
        });

        this.fetchItems();
    },

    computed: {
        row_data() {
            const items = this.rows;
            items.map(item => {
                item.employee = item.full_name;
                item.designation = item.designation.title;
            });
            return items;
        }
    },

    methods: {
        fetchItems() {
            this.rows = [];
            HTTP.get('/employees', {
                params: {
                    per_page: this.paginationData.perPage,
                    page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                    include: 'designation'
                }
            }).then((response) => {
                this.rows = response.data;
                this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch((error) => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        goToPage(page) {
            const queries = Object.assign({}, this.$route.query);
            this.paginationData.currentPage = page;
            this.$router.push({
                name: 'PaginateEmployees',
                params: { page: page },
                query: queries
            });

            this.fetchItems();
        }
    }

};
</script>
