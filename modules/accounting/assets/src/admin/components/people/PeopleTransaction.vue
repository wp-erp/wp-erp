<template>
    <div>
        <div class="content-header-section separator wperp-has-border-top">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Transactions', 'erp') }}</h2>
                </div>
                <div class="wperp-col">
                    <form class="wperp-form form--inline">
                        <div :class="['wperp-has-dropdown', {'dropdown-opened': showFilters}]">
                            <a class="wperp-btn btn--default dropdown-trigger filter-button" @click.prevent="toggleFilter">
                                <span><i class="flaticon-search-segment"></i>{{ __('Filters', 'erp') }}</span>
                                <i class="flaticon-arrow-down-sign-to-navigate"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right wperp-filter-container">
                                <div class="wperp-panel wperp-panel-default wperp-filter-panel">
                                    <h3>{{ __('Filter', 'erp') }}</h3>
                                    <div class="wperp-panel-body">
                                        <h3>{{ __('Date', 'erp') }}</h3>
                                        <div class="form-fields">
                                            <div class="start-date has-addons">
                                                <datepicker v-model="filters.start_date"></datepicker>
                                                <span class="flaticon-calendar"></span>
                                            </div>
                                            <span class="label-to">{{ __('To', 'erp') }}</span>
                                            <div class="end-date has-addons">
                                                <datepicker v-model="filters.end_date"></datepicker>
                                                <span class="flaticon-calendar"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wperp-panel-footer">
                                        <input type="reset" :value="__( 'Cancel', 'erp' )" class="wperp-btn btn--default" @click="toggleFilter">
                                        <input type="submit" :value="__( 'Submit', 'erp' )" class="wperp-btn btn--primary" @click.prevent="filterList">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wperp-import-wrapper display-inline-block">
                            <a class="wperp-btn btn--default" href="#" title="Import"><span class="flaticon-import"></span></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="wperp-transactions-section wperp-section">
            <div class="table-container">
                <list-table
                    tableClass="wperp-table people-trns-table table-striped table-dark widefat"
                    action-column="actions"
                    :columns="columns"
                    :rows="rows"
                    :actions="actions"
                    :showCb="false"
                    @action:click="onActionClick"
                    >
                    <template slot="voucher_no" slot-scope="data">
                        <strong>
                            <router-link :to="{ name: 'DynamicTrnLoader', params: { id: data.row.voucher_no }}">
                                <span v-if="data.row.voucher_no">#{{ data.row.voucher_no }}</span>
                            </router-link>
                        </strong>
                    </template>
                    <template slot="debit" slot-scope="data">
                      <span v-if="data.row.debit">{{ moneyFormat( data.row.debit ) }}</span> <span v-else>-</span>
                    </template>
                    <template slot="credit" slot-scope="data">
                        <span v-if="data.row.credit"> {{ moneyFormat( data.row.credit ) }} </span> <span v-else>-</span>
                    </template>
                </list-table>
            </div>
        </div>
    </div>
</template>

<script>
import ListTable from 'admin/components/list-table/ListTable.vue';
import Datepicker from 'admin/components/base/Datepicker.vue';

export default {
    name: 'PeopleTransaction',
    components: {
        ListTable,
        Datepicker
    },
    props: ['rows'],

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
                trn_date   : { label: __('Transaction Date', 'erp'), isColPrimary: true },
                created_at : { label: __('Created At', 'erp') },
                voucher_no : { label: __('Voucher No', 'erp') },
                particulars: { label: __('Particulars', 'erp') },
                debit      : { label: __('Debit', 'erp') },
                credit     : { label: __('Credit', 'erp') },
                balance    : { label: __('Balance', 'erp') }
            },
            actions : [
                { key: 'edit', label: __('Edit', 'erp') },
                { key: 'trash', label: __('Delete', 'erp') }
            ],
            showFilters: false,
            filters: {
                start_date: '',
                end_date: ''
            }
        };
    },

    methods: {
        toggleFilter() {
            this.showFilters = !this.showFilters;
        },

        filterList() {
            this.toggleFilter();
            this.$root.$emit('people-transaction-filter', this.filters);
        },

        onActionClick(action, row, index) {
            switch (action) {
            case 'trash':
                if (confirm(__('Are you sure to delete?', 'erp'))) {
                    this.$root.$emit('delete-transaction', row.id);
                }
                break;

            case 'edit':
                this.showModal = true;
                this.people = row;
                break;
            }
        }
    }

};
</script>

<style lang="less">
    .people-trns-table tbody tr td:last-child {
        text-align: left !important;
    }
    .open-dropdown-menu {
        visibility: visible !important;
        opacity: 1 !important;
    }
</style>
