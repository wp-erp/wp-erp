<template>
    <div class="content-header-section separator wperp-has-border-top">
        <div class="wperp-row wperp-between-xs">
            <div class="wperp-col">
                <h2 class="content-header__title">Transactions</h2>
            </div>
            <div class="wperp-col">
                <form class="wperp-form form--inline">
                    <div :class="['wperp-has-dropdown', {'dropdown-opened': showFilters}]">
                        <a class="wperp-btn btn--default dropdown-trigger filter-button" @click.prevent="toggleFilter">
                            <span><i class="flaticon-search-segment"></i>Filters</span>
                            <i class="flaticon-arrow-down-sign-to-navigate"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right wperp-filter-container">
                            <div class="wperp-panel wperp-panel-default wperp-filter-panel">
                                <h3>Filter</h3>
                                <div class="wperp-panel-body">
                                    <h3>Date</h3>
                                    <div class="form-fields">
                                        <div class="start-date has-addons">
                                            <datepicker v-model="filters.start_date"></datepicker>
                                            <span class="flaticon-calendar"></span>
                                        </div>
                                        <span class="label-to">To</span>
                                        <div class="end-date has-addons">
                                            <datepicker v-model="filters.end_date"></datepicker>
                                            <span class="flaticon-calendar"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="wperp-panel-footer">
                                    <input type="reset" value="Cancel" class="wperp-btn btn--default" @click="toggleFilter">
                                    <input type="submit" value="Submit" class="wperp-btn btn--primary" @click.prevent="filterList">
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
    import Datepicker from 'admin/components/base/Datepicker.vue';

    export default {
        name: 'TransactionsFilter',

        components: {
            Datepicker,
        },

        data() {
            return {
                showFilters: false,
                filters: {
                    start_date: '',
                    end_date: ''
                }
            }
        },

        methods: {
            toggleFilter() {
                this.showFilters = !this.showFilters;
            },

            filterList() {
                this.toggleFilter();

                this.$root.$emit('sales-filter', this.filters);
            }
        }
    }
</script>

<style scoped>

</style>
