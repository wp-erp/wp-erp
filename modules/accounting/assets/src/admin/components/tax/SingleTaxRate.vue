<template>
    <div class="app-customers single-tax-rate">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ $route.params.name }}</h2>

                    <a class="wperp-btn btn--primary" @click.prevent="addNewLine = true">
                        <span>{{ __('Add New Line', 'erp') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <tax-rate-line-add v-if="addNewLine" @close="addNewLine = false" />

        <tax-rate-line-edit v-if="showModal"
            :tax_id="tax_id"
            :row_data="row_data"
            @close="showModal = false" />

        <div class="table-container">
            <list-table
                tableClass="wp-ListTable widefat fixed tax-rate-list wperp-table table-striped table-dark"
                action-column="actions"
                :columns="columns"
                :rows="rows"
                :showCb="false"
                :total-items="paginationData.totalItems"
                :total-pages="paginationData.totalPages"
                :per-page="paginationData.perPage"
                :current-page="paginationData.currentPage"
                @pagination="goToPage"
                :actions="actions"
                @action:click="onActionClick">
            </list-table>
        </div>

    </div>
</template>

<script>
import HTTP from 'admin/http';
import ListTable from 'admin/components/list-table/ListTable.vue';
import TaxRateLineAdd from 'admin/components/tax/TaxRateLineAdd.vue';
import TaxRateLineEdit from 'admin/components/tax/TaxRateLineEdit.vue';

export default {
    name: 'SingleTaxRate',

    components: {
        ListTable,
        TaxRateLineAdd,
        TaxRateLineEdit
    },

    data() {
        return {
            tax_id                : null,
            row_id                : null,
            row_data              : null,
            modalParams           : null,
            taxrate               : {},
            buttonTitle           : '',
            pageTitle             : '',
            url                   : '',
            singleUrl             : '',
            isActiveOptionDropdown: false,
            singleTaxRateModal    : false,
            showModal             : false,
            addNewLine            : false,
            is_update             : false,
            columns               : {
                component_name: { label: 'Component' },
                agency_name   : { label: 'Agency' },
                tax_cat_name  : { label: 'Tax Category' },
                tax_rate      : { label: 'Tax Rate' },
                actions       : { label: 'Actions' }
            },
            rows: [],
            paginationData: {
                totalItems : 0,
                totalPages : 0,
                perPage    : 10,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            },
            actions: [
                { key: 'edit', label: 'Edit', iconClass: 'flaticon-edit' },
                { key: 'trash', label: 'Delete', iconClass: 'flaticon-trash' }
            ]
        };
    },

    created() {
        this.fetchItems();

        if (this.$route.name === 'EditSingleTaxRate') {
            this.is_update = true;
        }
    },

    methods: {

        fetchItems() {
            this.rows = [];

            this.tax_id = this.$route.params.id;
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get(`/taxes/${this.tax_id}`, {
                params: {
                    per_page: this.paginationData.perPage,
                    page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page
                }
            }).then((response) => {
                this.taxrate                   = response.data;
                this.rows                      = response.data.tax_components;
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
                name: 'PaginateTaxZones',
                params: { page: page },
                query: queries
            });

            this.fetchItems();
        },

        onActionClick(action, row, index) {
            switch (action) {
            case 'trash':
                if (confirm('Are you sure to delete?')) {
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.delete('/taxes/' + this.tax_id + '/line-delete/' + row.db_id).then(response => {
                        this.$delete(this.rows, index);
                        this.$store.dispatch('spinner/setSpinner', false);
                        this.showAlert('success', 'Deleted !');
                    });
                }
                break;

            case 'edit':
                this.row_id = row.id;
                this.row_data = this.rows[index];
                this.showModal = true;
                break;
            }
        }
    }
};
</script>
<style lang="less">
    .erp-acct-tax-menus {
        margin-left: 240px;
    }

    .combo-box {
        margin-right: 10px !important;
    }

    .single-tax-rate {
        th.column.actions {
            float: left !important;
        }
        .col--actions {
            float: left !important;
        }
    }
</style>
