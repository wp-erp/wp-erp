<template>
    <div class="app-customers">
        <div class="people-header">
            <h2 class="add-new-people">
                <span>{{ pageTitle }}</span>
                <a href="" id="erp-customer-new" @click.prevent="showModal = true">{{ __('Add New', 'erp') }} {{ buttonTitle }}</a>
            </h2>

            <!-- top search bar -->
            <people-search v-model="search" />
        </div>

        <people-modal v-if="showModal" :people.sync="people" :title="buttonTitle" @close="showModal = false" />

        <list-table
            tableClass="wperp-table people-table table-striped table-dark "
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
                    <router-link :to="{ name: singleUrl, params: { id: data.row.id, route: url}}">
                        {{data.row.customer}}
                    </router-link>
                </strong>
            </template>
        </list-table>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import PeopleSearch from 'admin/components/people/PeopleSearch.vue';
import ListTable from 'admin/components/list-table/ListTable.vue';
import PeopleModal from 'admin/components/people/PeopleModal.vue';

export default {
    name: 'People',

    components: {
        PeopleSearch,
        ListTable,
        PeopleModal
    },

    data() {
        return {
            people: null,
            bulkActions: [
                {
                    key: 'trash',
                    label: 'Move to Trash',
                    iconClass: 'flaticon-trash'
                }
            ],
            columns: {
                customer: { label: 'Name', isColPrimary: true },
                company : { label: 'Company' },
                email   : { label: 'Email' },
                phone   : { label: 'Phone' },
                actions : { label: 'Actions' }
            },
            rows: [],
            paginationData: {
                totalItems : 0,
                totalPages : 0,
                perPage    : 20,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            },
            actions : [
                { key: 'edit', label: 'Edit', iconClass: 'flaticon-edit' },
                { key: 'trash', label: 'Delete', iconClass: 'flaticon-trash' }
            ],
            search: '',
            showModal             : false,
            buttonTitle           : '',
            pageTitle             : '',
            url                   : '',
            singleUrl             : '',
            isActiveOptionDropdown: false
        };
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', true);

        this.$on('modal-close', () => {
            this.showModal = false;
            this.people = null;
        });

        this.$root.$on('peopleUpdate', () => {
            this.showModal = false;
            this.fetchItems();
        });

        this.buttonTitle = (this.$route.name.toLowerCase() === 'customers') ? 'Customer' : 'Vendor';
        this.pageTitle   = this.$route.name;
        this.url         = this.$route.name.toLowerCase();
        this.singleUrl   = (this.url === 'customers') ? 'CustomerDetails' : 'VendorDetails';

        this.fetchItems();
    },

    computed: {
        row_data() {
            const items = this.rows;
            items.map(item => {
                item.customer = item.first_name + ' ' + item.last_name;
            });
            return items;
        }
    },

    watch: {
        search(newVal, oldVal) {
            this.$store.dispatch('spinner/setSpinner', true);
            this.fetchItems();
        }
    },

    methods: {
        fetchItems() {
            this.rows = [];
            HTTP.get(this.url, {
                params: {
                    per_page: this.paginationData.perPage,
                    page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                    search: this.search
                }
            })
                .then((response) => {
                    this.rows = response.data;
                    this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                    this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                    this.$store.dispatch('spinner/setSpinner', false);
                })
                .catch((error) => {
                    this.$store.dispatch('spinner/setSpinner', false);
                    throw error;
                });
        },

        onActionClick(action, row, index) {
            switch (action) {
            case 'trash':
                if (confirm('Are you sure to delete?')) {
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.delete(this.url + '/' + row.id).then(response => {
                        this.$delete(this.rows, index);

                        this.$store.dispatch('spinner/setSpinner', false);
                        this.showAlert('success', 'Deleted !');
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    });
                }
                break;

            case 'edit':
                this.showModal = true;
                this.people = row;
                break;

            default :
                break;
            }
        },

        onBulkAction(action, items) {
            if (action === 'trash') {
                if (confirm('Are you sure to delete?')) {
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.delete(this.url + '/delete/' + items.join(',')).then(response => {
                        const toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                        if (toggleCheckbox.checked) {
                            // simulate click event to remove checked state
                            toggleCheckbox.click();
                        }

                        this.fetchItems();
                        this.$store.dispatch('spinner/setSpinner', false);
                        this.showAlert('success', 'Deleted !');
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    });
                }
            }
        },

        goToPage(page) {
            const queries = Object.assign({}, this.$route.query);
            this.paginationData.currentPage = page;

            this.$router.push({
                name: this.url === 'customers' ? 'PaginateCustomers' : 'PaginateVendors',
                params: { page: page },
                query: queries
            });

            this.fetchItems();
        }
    }

};
</script>
<style lang="less">
    .app-customers {
        .people-header {
            display: flex;
            align-items: center;

            .add-new-people {
                align-items: center;
                display: flex;
                width: 50%;
                margin: 0;
                padding: 0;
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
                    width: 150px;
                }
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
        .people-table {
            border-radius: 3px;
            tbody {
                background: #FAFAFA;
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
            .col--actions {
                float: left !important;
            }
            .row-actions {
                padding-left: 20px !important;
            }
        }
        .check-column {
            padding: 20px !important;
        }
    }
</style>
