<template>
    <div class="app-customers">
        <h2 class="add-new-customer">
            <span>{{ pageTitle }}</span>
            <a href="" id="erp-customer-new" @click.prevent="showModal = true">+ Add New {{ buttonTitle }}</a>
        </h2>
        <people-modal v-if="showModal" :people.sync="people" :countries="countries" :state="states" :title="buttonTitle"></people-modal>
        <list-table
            tableClass="wperp-table table-striped table-dark widefat"
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
            <template slot="customer" slot-scope="data">
                <strong>
                    <router-link :to="{ name: 'CustomerDetails', params: { id: data.row.id }}">
                        {{data.row.customer}}
                    </router-link>
                </strong>
            </template>

        </list-table>

    </div>
</template>

<script>
    import ListTable from 'admin/components/list-table/ListTable.vue'
    import PeopleModal from './PeopleModal.vue'
    import HTTP from 'admin/http.js'
    export default {
        name: 'People',

        components: {
            ListTable,
            PeopleModal
        },

        data () {
            return {
                people: null,
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                        img: erp_acct_var.erp_assets + '/images/trash.png'
                    }
                ],
                columns: {
                    'customer': { label: 'Name' },
                    'company': { label: 'Company' },
                    'email': { label: 'Email' },
                    'phone': { label: 'Phone' },
                    'expense': { label: 'Expense' },
                    'actions': { label: 'Actions' }
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
                ],
                showModal: false,
                countries: [],
                states:[],
                buttonTitle: '',
                pageTitle: '',
                url: '',
            };
        },
        created() {
            this.$on('modal-close', function() {
                this.showModal = false;
                this.people = null;
            });

            this.getCountries();
            this.buttonTitle = ( this.$route.name.toLowerCase() == 'customers' ) ? 'customer' : 'vendor';
            this.pageTitle   = this.$route.name;
            this.url    =  this.$route.name.toLowerCase();
            this.fetchItems();

        },

        computed: {
            row_data(){
                let items = this.rows;
                items.map( item => {
                    item.customer = item.first_name + ' ' + item.last_name;
                    //TODO remove after api update for expense
                    item.expense = '55555';
                } );
                return items;
            }
        },

        methods: {
            fetchItems(){
                this.rows = [];
                HTTP.get( this.url , {
                    params: {
                        per_page: this.paginationData.perPage,
                        page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page
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
            getCountries() {
                HTTP.get( 'customers/country' ).then( response => {
                    let country = response.data.country;
                    let states   = response.data.state;
                    for ( let x in country ) {
                        if( states[x] == undefined) {
                            states[x] = [];
                        }
                        this.countries.push( { id: x, name: country[x], state: states[x] });
                    }
                    for ( let state in states ) {
                        for ( let x in states[state] ) {
                            this.states.push({ id: x, name: states[state][x] });
                        }
                    }
                } );
            },
            onActionClick(action, row, index) {

                switch ( action ) {
                    case 'trash':
                        if ( confirm('Are you sure to delete?') ) {
                            HTTP.delete( this.url + '/' + row.id).then( response => {
                                this.$delete(this.rows, index);
                            });
                        }
                        break;

                    case 'edit':
                        this.showModal = true;
                        this.people = row;
                        break;
                    default :


                }
            },

            onBulkAction(action, items) {
                if ( 'trash' === action ) {
                    if ( confirm('Are you sure to delete?') ) {
                        HTTP.delete( this.url + '/delete/' + items.join(',')).then(response => {
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
                    name: 'PaginateCustomers',
                    params: { page: page },
                    query: queries
                });

                this.fetchItems();
            },
        },


    };
</script>
<style lang="less">
    .app-customers {
        .add-new-customer {
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
        .customer-list {
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
