<template>
    <div class="content-header-section separator wperp-has-border-top">
        <div class="wperp-row wperp-between-xs">
            <div class="wperp-col">
                <h2 class="content-header__title">{{ __('Transactions', 'erp') }}</h2>
            </div>
            <div class="wperp-col">
                <form class="wperp-form form--inline" action="" @submit.prevent="filterList">
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
                                    <br>
                                    <h3>{{ __('Status', 'erp') }}</h3>
                                    <div class="form-fields">
                                        <simple-select
                                            v-model="filters.status"
                                            :options="statuses"
                                        >
                                        </simple-select>
                                    </div>
                                </div>
                                <div class="wperp-panel-footer">
                                    <input type="reset" value="Cancel" class="wperp-btn btn--default" @click="toggleFilter">
                                    <input type="submit" value="Submit" class="wperp-btn btn--primary">
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
</template>

<script>
import HTTP from 'admin/http';
import Datepicker from 'admin/components/base/Datepicker.vue';
import SimpleSelect from 'admin/components/select/SimpleSelect.vue';

export default {
    name: 'TransactionsFilter',

    components: {
        Datepicker,
        SimpleSelect
    },

    data() {
        return {
            showFilters: false,
            filters: {
                start_date: '',
                end_date: '',
                status: ''
            },
            statuses: []
        };
    },

    created() {
        HTTP.get('/transactions/statuses').then(response => {
            this.statuses = response.data;
        }).catch(error => {
            throw error;
        });

        this.$root.$on('SimpleSelectChange', (data) => {
            const status = this.statuses.find(o => o.id === data.selected);
            this.filters.status = parseInt(status.id);
        });
    },

    methods: {
        toggleFilter() {
            this.showFilters = !this.showFilters;
        },

        filterList() {
            this.toggleFilter();

            this.$root.$emit('transactions-filter', this.filters);
        }

    }
};
</script>
