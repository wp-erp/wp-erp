<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Tax Payments', 'erp') }}</h2>
                    <a class="wperp-btn btn--primary" @click.prevent="addTaxPayment">
                        <span>{{ __('New Tax Payment', 'erp') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="table-container">
            <list-table
                tableClass="wp-ListTable widefat fixed tax-records-list wperp-table table-striped table-dark"
                action-column="actions"
                :columns="columns"
                :rows="row_data"
                :total-items="paginationData.totalItems"
                :total-pages="paginationData.totalPages"
                :per-page="paginationData.perPage"
                :current-page="paginationData.currentPage"
                @pagination="goToPage"
                :actions="actions"
                :showCb="false"
                :bulk-actions="bulkActions"
                @action:click="onActionClick"
                @bulk:click="onBulkAction">

                <template slot="voucher_no" slot-scope="data">
                    <strong>
                        <router-link :to="{ name: 'PayTaxSingle', params: { id: data.row.voucher_no }}">
                            #{{ data.row.voucher_no }}
                        </router-link>
                    </strong>
                </template>
            </list-table>
        </div>

    </div>
</template>

<script>
import HTTP from 'admin/http';
import ListTable from 'admin/components/list-table/ListTable.vue';

export default {
    name: 'TaxRecords',

    components: {
        ListTable
    },

    data() {
        return {
            voucher_no: 0,
            agency_id: 0,
            trn_date: '',
            tax_period: '',
            amount: 0,
            modalParams: null,
            columns: {
                voucher_no: { label: 'Voucher No' },
                agency_id : { label: 'Agency' },
                trn_date  : { label: 'Date' },
                // 'tax_period': {label: 'Tax Period'},
                amount    : { label: 'Amount' },
                actions   : { label: 'Actions' }
            },
            rows: [],
            paginationData: {
                totalItems: 0,
                totalPages: 0,
                perPage: 20,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            },
            actions : [
                { key: 'edit', label: 'Edit', iconClass: 'flaticon-edit' },
                { key: 'trash', label: 'Delete', iconClass: 'flaticon-trash' }
            ],
            bulkActions: [
                {
                    key: 'trash',
                    label: 'Move to Trash',
                    iconClass: 'flaticon-trash'
                }
            ],
            taxes                 : [{}],
            buttonTitle           : '',
            pageTitle             : '',
            url                   : '',
            singleUrl             : '',
            isActiveOptionDropdown: false,
            showModal             : false
        };
    },

    created() {
        this.pageTitle = this.$route.name;
        this.url       = this.$route.name.toLowerCase();

        this.fetchItems();
    },

    computed: {
        row_data() {
            return this.rows;
        }
    },

    methods: {
        close() {
            this.showModal = false;
        },
        fetchItems() {
            this.rows = [];
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get('taxes/tax-records', {
                params: {
                    per_page: this.paginationData.perPage,
                    page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page
                }
            })
                .then((response) => {
                    this.rows = response.data;
                    this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                    this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                    this.$store.dispatch('spinner/setSpinner', false);
                })
                .catch(error => {
                    this.$store.dispatch('spinner/setSpinner', false);
                    throw error;
                });
        },

        goToPage(page) {
            const queries = Object.assign({}, this.$route.query);
            this.paginationData.currentPage = page;
            this.$router.push({
                name: 'PaginateTaxRates',
                params: { page: page },
                query: queries
            });

            this.fetchItems();
        },

        addTaxPayment() {
            this.$router.push('/settings/pay-tax');
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
                this.taxes = row;
                break;

            default :
                break;
            }
        },

        onBulkAction(action, items) {
            if (action === 'trash') {
                if (confirm('Are you sure to delete?')) {
                    this.$store.dispatch('spinner/setSpinner', true);

                    HTTP.delete('taxes/delete/' + items.join(',')).then(response => {
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
        }
    }
};
</script>
<style lang="less">
    .tax-records-list {
        .col--actions {
            text-align: left !important;
        }
    }
</style>
