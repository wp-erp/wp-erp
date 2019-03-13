<template>
    <div class="app-employees">
        <h2 class="add-new-employee">
            <span>Employees</span>
            <!-- {{data.row.employee}} -->
        </h2>
        <list-table
            tableClass="wp-ListTable widefat fixed employee-list"
            action-column="actions"
            :columns="columns"
            :rows="row_data"
            :bulk-actions="bulkActions"
            :total-items="paginationData.totalItems"
            :total-pages="paginationData.totalPages"
            :per-page="paginationData.perPage"
            :current-page="paginationData.currentPage"
            @pagination="goToPage"
            :actions="actions"
            @action:click="onActionClick"
            @bulk:click="onBulkAction">
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
    import HTTP from 'admin/http'
    import ListTable from 'admin/components/list-table/ListTable.vue'

    export default {
        name: 'Employees',

        components: {
            ListTable
        },

        data () {
            return {
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                        img: erp_acct_var.erp_assets + '/images/trash.png'
                    }
                ],
                columns: {
                    'employee': {label: 'Name'},
                    'designation': {label: 'Designation'},
                    'email': {label: 'Email'},
                    'phone': {label: 'Phone'},
                    'actions': {label: 'Actions'}
                },
                rows: [],
                paginationData: {
                    totalItems: 0,
                    totalPages: 0,
                    perPage: 10,
                    currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
                },
                actions : [
                    { key: 'edit', label: 'Edit' },
                    { key: 'trash', label: 'Delete' }
                ]
            };
        },
        created() {
            this.$store.dispatch( 'spinner/setSpinner', true );
            this.$on('modal-close', function() {
                this.showModal = false;
            });

            this.fetchItems();
        },

        computed: {
            row_data(){
                let items = this.rows;
                items.map( item => {
                    item.employee = item.full_name;
                    item.email = item.user_email;
                    item.designation = item.designation.title;
                } );
                return items;
            }
        },

        methods: {
            fetchItems(){
                this.rows = [];
                HTTP.get('/employees', {
                    params: {
                        per_page: this.paginationData.perPage,
                        page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                        include: 'designation'
                    }
                }).then( (response) => {
                    this.rows = response.data;
                    this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                    this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);

                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch((error) => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                })
                .then(() => {
                    //ready
                });
            },

            onActionClick(action, row, index) {

                switch ( action ) {
                    case 'trash':
                        if ( confirm('Are you sure to delete?') ) {
                            HTTP.delete('employees/' + row.id).then( response => {
                                this.$delete(this.rows, index);
                            });
                        }
                        break;

                    case 'edit':
                        //TODO
                        break;

                    default :

                }
            },

            onBulkAction(action, items) {
                if ( 'trash' === action ) {
                    if ( confirm('Are you sure to delete?') ) {
                        HTTP.delete('employees/delete/' + items.join(',')).then(response => {
                            let toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                            if ( toggleCheckbox.checked ) {
                                // simulate click event to remove checked state
                                toggleCheckbox.click();
                            }

                            this.fetchItems();
                        });
                    }
                }
            },

            goToPage(page) {
                let queries = Object.assign({}, this.$route.query);
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
        .add-new-employee {
            align-items: center;
            display: flex;
            span {
                font-size: 18px;
                font-weight: bold;
            }
            a {
                background: #1a9ed4;
                border-radius: 3px;
                color: #fff;
                font-size: 12px;
                height: 29px;
                line-height: 29px;
                margin-left: 13px;
                text-align: center;
                text-decoration: none;
                width: 135px;
            }
        }
        .employee-list {
            border-radius: 3px;
            tbody {
                background: #FAFAFA;
            }
            tfoot th,
            thead th {
                color: #1A9ED4;
                font-weight: bold;
            }
            th ul,
            th li {
                margin: 0;
            }
            th li {
                display: flex;
                align-items: center;
                img {
                    width: 14px;
                    padding-right: 5px;
                }
            }
            .column.title {
                &.selected {
                    color: #1A9ED4;
                }
                a {
                    color: #222;
                    font-weight: normal;
                    &:hover {
                        color: #1A9ED4;
                    }
                }
            }
            .check-column input {
                border-color: #E7E7E7;
                box-shadow: none;
                border-radius: 3px;
                &:checked {
                    background: #1ABC9C;
                    border-color: #1ABC9C;
                    border-radius: 3px;
                    &:before {
                        color: #fff;
                    }
                }
            }
            .row-actions {
                padding-left: 20px;
            }
        }
        .widefat {
            tfoot td,
            tbody th {
                line-height: 2.5em;
            }
            tbody td {
                line-height: 3em;
            }
        }
    }
</style>
