<template>
    <div class="app-customers">
        <div class="people-header">
            <h2 class="add-new-people">
                <span>{{ pageTitle }}</span>
                <a href="" id="erp-customer-new" @click.prevent="showModal = true">{{ __('Add New', 'erp') }} {{ buttonTitle }}</a>
            </h2>

            <div class="erp-btn-group">
                <button @click.prevent="showImportModal = true">{{ __( 'Import', 'erp' ) }}</button>
                <button @click.prevent="showExportModal = true">{{ __( 'Export', 'erp' ) }}</button>
            </div>

            <!-- top search bar -->
            <people-search v-model="search" />
        </div>

        <people-modal v-if="showModal" :people.sync="people" :title="buttonTitle" @close="showModal = false" />

        <import-modal v-if="showImportModal" :title="importTitle" :type="url" @close="showImportModal = false" />

        <export-modal v-if="showExportModal" :title="exportTitle" :type="url" @close="showExportModal = false" />

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
import ImportModal from 'admin/components/people/ImportModal.vue';
import ExportModal from 'admin/components/people/ExportModal.vue';

export default {
    name: 'People',

    components: {
        PeopleSearch,
        ListTable,
        PeopleModal,
        ImportModal,
        ExportModal,
    },

    data() {
        return {
            people: null,
            bulkActions: [
                {
                    key: 'trash',
                    label: __('Move to Trash', 'erp'),
                    iconClass: 'flaticon-trash'
                }
            ],
            columns: {
                customer: { label: __('Name', 'erp'), isColPrimary: true },
                company : { label: __('Company', 'erp') },
                email   : { label: __('Email', 'erp') },
                phone   : { label: __('Phone', 'erp') },
                actions : { label: __('Actions', 'erp') }
            },
            rows: [],
            paginationData: {
                totalItems : 0,
                totalPages : 0,
                perPage    : 20,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            },
            actions : [
                { key: 'edit', label: __('Edit', 'erp'), iconClass: 'flaticon-edit' },
                { key: 'trash', label: __('Delete', 'erp'), iconClass: 'flaticon-trash' }
            ],
            search: '',
            showModal             : false,
            showImportModal       : false,
            showExportModal       : false,
            buttonTitle           : '',
            importTitle           : '',
            exportTitle           : '',
            pageTitle             : '',
            url                   : '',
            singleUrl             : '',
            isActiveOptionDropdown: false
        };
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', true);

        this.$on('modal-close', () => {
            this.showModal       = false;
            this.showImportModal = false;
            this.showExportModal = false;
            this.people          = null;
        });

        this.$root.$on('peopleUpdate', () => {
            this.showModal = false;
            this.fetchItems();
        });

        this.$root.$on('imported-people', () => {
            this.showImportModal = false;
            this.fetchItems();
        });

        this.buttonTitle = (this.$route.name.toLowerCase() === 'customers') ? __('Customer', 'erp') : __('Vendor', 'erp');
        this.importTitle = (this.$route.name.toLowerCase() === 'customers') ? __('Import Customers', 'erp') : __('Import Vendors', 'erp');
        this.exportTitle = (this.$route.name.toLowerCase() === 'customers') ? __('Export Customers', 'erp') : __('Export Vendors', 'erp');
        this.pageTitle   = (this.$route.name.toLowerCase() === 'customers') ? __('Customers', 'erp') : __('Vendors', 'erp');
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
                if (confirm(__('Are you sure to delete?', 'erp'))) {
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.delete(this.url + '/' + row.id).then(response => {
                        if ( response.status !== 204 ) {
                            this.$store.dispatch('spinner/setSpinner', false);
                            this.showAlert('error', response.data.data[0].message);
                            // or loop through the erros and show a list
                            return;
                        }

                        this.$delete(this.rows, index);
                        this.$store.dispatch('spinner/setSpinner', false);
                        this.showAlert('success', 'Deleted !');

                        this.fetchItems();
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
                if (confirm(__('Are you sure to delete?', 'erp'))) {
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.delete(this.url + '/delete/' + items.join(',')).then(response => {
                        if ( response.status !== 204 ) {
                            this.$store.dispatch('spinner/setSpinner', false);
                            this.showAlert('error', response.data.data[0].message);

                            return;
                        }

                        const toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                        if (toggleCheckbox.checked) {
                            // simulate click event to remove checked state
                            toggleCheckbox.click();
                        }

                        this.fetchItems();
                        this.$store.dispatch('spinner/setSpinner', false);
                        this.showAlert('success', __('Deleted !', 'erp'));
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
                width: 65%;
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

                    @media (max-width: 782px) and (min-width: 768px) {
                        margin-right: 18rem;
                        margin-bottom: 3px;
                        max-width: 120px;
                    }

                    @media (max-width: 767px) and (min-width: 707px) {
                        margin-right: 16rem;
                        margin-bottom: 3px;
                    }

                    @media (max-width: 706px) and (min-width: 651px) {
                        margin-right: 14rem;
                        margin-bottom: 3px;
                    }

                    @media (max-width: 650px) {
                        margin-right: 12rem;
                        margin-bottom: 3px;
                    }
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
            @media (min-width: 783px) {
                .col--actions {
                    float: left !important;
                }
            }
            .row-actions {
                padding-left: 20px !important;
                text-align: left !important;
            }
        }
        .check-column {
            padding: 20px !important;
        }
    }

    .search-btn {
        @media (max-width: 650px) {
            display: none;
        }
    }

    .people-search {
        @media (max-width: 479px) {
            margin-top: 20px;
        }
    }

    .erp-btn-group {
        display: inline-flex;
        position: absolute;
        right: 17.5rem;

        &:after {
            content: "";
            clear: both;
            display: table;
        }

        @media (max-width: 782px) {
            right: 17rem;
            margin-top: 23px;
        }

        @media (max-width: 650px) {
            right: 8.5rem;
        }

        button {
            padding: 5px 15px;
            border: 0.3px solid rgb(226, 226, 226);
            background-color: #fff;
            color: rgba(0,0,0,0.6);
            font-size: 12px;
            font-weight: 400;
            text-decoration: none;
            line-height: inherit;
            cursor: pointer;

            @media (max-width: 479px) {
                padding: 5px;
            }

            &:last-child {
                border-top-right-radius: 3.5px;
                border-bottom-right-radius: 3.5px;
            }

            &:first-child {
                border-top-left-radius: 3.5px;
                border-bottom-left-radius: 3.5px;
            }

            :not(:last-child) {
                border-right: none;
            }

            &:hover {
                background-color: #1A9ED4;
                color: #fff;
            }
        }
    }
</style>
