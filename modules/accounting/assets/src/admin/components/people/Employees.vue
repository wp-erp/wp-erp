<template>
    <div class="app-employees">
        <h2 class="add-new-people">
            <span>{{ __( 'Employees', 'erp' ) }}</span>

            <span class="erp-help-tip .erp-tips"
                :title="__( 'The Employee list is coming from HR. You can not create an employee here. To create a new employee, go to HR > People > Employees.', 'erp' )" />
        </h2>

        <list-table
            tableClass="wperp-table table-striped table-dark"
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
                <router-link :to="{ name: 'EmployeeDetails', params: { id: data.row.people_id } }">{{data.row.employee}}</router-link>
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
                    label: __('Move to Trash', 'erp'),
                    img: erp_acct_var.erp_assets + '/images/trash.png' /* global erp_acct_var */
                }
            ],
            columns: {
                employee: { label: __('Name', 'erp'), isColPrimary: true },
                designation: { label: __('Designation', 'erp') },
                department: { label: __('Department', 'erp') },
                email: { label: __('Email', 'erp') },
                phone: { label: __('Phone', 'erp') }
            },
            rows: [],
            paginationData: {
                totalItems: 0,
                totalPages: 0,
                perPage: 20,
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
                item.designation = item.designation;
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

<style lang="less">
    .app-employees {
        .erp-help-tip {
            font-size: 1.1em;
            bottom   : 0.2rem;
        }

        @media (min-width: 783px) {
            .wperp-table tbody tr {
                td:last-child,
                tr:last-child {
                    text-align: left !important;
                }
            }
        }
    }
</style>
