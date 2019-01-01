<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Expenses Transactions</h2>
                    <combo-box :options="pages" :hasUrl="true" placeholder="New Transaction" />
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <expenses-stats />

        <!-- Start .header-section -->
        <div class="content-header-section separator wperp-has-border-top">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Transactions</h2>
                </div>
                <div class="wperp-col">
                    <form class="wperp-form form--inline">
                        <div class="wperp-has-dropdown">
                            <a class="wperp-btn btn--default dropdown-trigger filter-button">
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
                                                <input class="form-field" type="date" placeholder="16-11-18">
                                                <span class="flaticon-calendar"></span>
                                            </div>
                                            <span class="label-to">To</span>
                                            <div class="end-date has-addons">
                                                <input class="form-field" type="date" placeholder="16-11-18">
                                                <span class="flaticon-calendar"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wperp-panel-footer">
                                        <input type="reset" value="Cancel" class="wperp-btn btn--default">
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
        <!-- End .header-section -->

        <!-- Start transactions section -->
        <div class="wperp-transactions-section wperp-section">
            <!-- Start .wperp-crm-table -->
            <div class="table-container">
                <div class="bulk-action">
                    <a href="#"><i class="flaticon-trash"></i>Trash</a>
                    <a href="#" class="dismiss-bulk-action"><i class="flaticon-close"></i></a>
                </div>
                <table class="wperp-table table-striped table-dark widefat table2">
                    <thead>
                        <tr>
                            <td scope="col" class="col--check">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input id="cb-select-all" class="form-check-input" type="checkbox" value="">
                                        <span class="form-check-sign">
                                            <span class="check"></span>
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <th scope="col" class="col--name column-primary">Date</th>
                            <th scope="col" class="col--email">Type</th>
                            <th scope="col" class="col--phone">Ref</th>
                            <th scope="col" class="col--life-stage">Customer</th>
                            <th scope="col" class="col--owner">Due Date</th>
                            <th scope="col" class="col--cd">Due</th>
                            <th scope="col" class="col--total">Total</th>
                            <th scope="col" class="col--status">Status</th>
                            <th scope="col" class="col--actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row" class="col--check">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox">
                                        <span class="form-check-sign">
                                            <span class="check"></span>
                                        </span>
                                    </label>
                                </div>
                            </th>
                            <td class="col--name column-primary">26-09-18
                                <button type="button" class="wperp-toggle-row">
                                    <span class="screen-reader-text">Show more details</span>
                                </button>
                            </td>
                            <td class="col--email" data-colname="Type">Invoice</td>
                            <td class="col--phone" data-colname="Ref">012</td>
                            <td class="col--life-stage subscriber" data-colname="Customer">Rizvi Amin khan</td>
                            <td class="col--owner" data-colname="Due Date">06-09-2018</td>
                            <td class="col--cd" data-colname="Due">$24,000.00</td>
                            <td class="col--cd" data-colname="Total">$24,000.00</td>
                            <td class="col--cd" data-colname="Status">Recieved</td>
                            <td class="col--actions">
                                <div class="wperp-has-dropdown dropdown right--middle">
                                    <a href="#" class="dropdown-trigger"><i class="flaticon-menu"></i></a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="#"><i class="flaticon-printer"></i>Print</a></li>
                                        <li><a href="#"><i class="flaticon-sent-mail"></i>Send</a></li>
                                        <li><a href="#"><i class="flaticon-alarm-clock"></i>Send Reminder</a></li>
                                        <li><a href="#"><i class="flaticon-share"></i>Share Invoice</a></li>
                                        <li><a href="#"><i class="flaticon-link-symbol"></i>Print Packing Link</a></li>
                                        <li><a href="#"><i class="flaticon-edit"></i>View/Edit</a></li>
                                        <li><a href="#"><i class="flaticon-copy-content"></i>Copy</a></li>
                                        <li><a href="#"><i class="flaticon-trash"></i>Delete</a></li>
                                        <li><a href="#"><i class="flaticon-delete"></i>Void</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="col--check">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox">
                                        <span class="form-check-sign">
                                        <span class="check"></span>
                                    </span>
                                    </label>
                                </div>
                            </th>
                            <td class="col--name column-primary">26-09-18
                                <button type="button" class="wperp-toggle-row">
                                    <span class="screen-reader-text">Show more details</span>
                                </button>
                            </td>
                            <td class="col--email" data-colname="Email Address">Invoice</td>
                            <td class="col--phone" data-colname="Phone">012</td>
                            <td class="col--life-stage subscriber" data-colname="Life Stage">Rizvi Amin khan</td>
                            <td class="col--owner" data-colname="Owner">06-09-2018</td>
                            <td class="col--cd" data-colname="Created At">$24,000.00</td>
                            <td class="col--cd" data-colname="Created At">$24,000.00</td>
                            <td class="col--cd" data-colname="Created At">Recieved</td>
                            <td class="col--actions">
                                <div class="wperp-has-dropdown dropdown right--middle">
                                    <a href="#" class="dropdown-trigger"><i class="flaticon-menu"></i></a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="#"><i class="flaticon-printer"></i>Print</a></li>
                                        <li><a href="#"><i class="flaticon-sent-mail"></i>Send</a></li>
                                        <li><a href="#"><i class="flaticon-alarm-clock"></i>Send Reminder</a></li>
                                        <li><a href="#"><i class="flaticon-share"></i>Share Invoice</a></li>
                                        <li><a href="#"><i class="flaticon-link-symbol"></i>Print Packing Link</a></li>
                                        <li><a href="#"><i class="flaticon-edit"></i>View/Edit</a></li>
                                        <li><a href="#"><i class="flaticon-copy-content"></i>Copy</a></li>
                                        <li><a href="#"><i class="flaticon-trash"></i>Delete</a></li>
                                        <li><a href="#"><i class="flaticon-delete"></i>Void</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
        <!-- End transactions section -->

        <!-- End .wperp-crm-table -->
    </div>
</template>

<script>
import 'assets/js/plugins/chart.min';
import 'assets/js/status_chart';

import ComboBox from 'admin/components/select/ComboBox.vue';
import ExpensesStats from 'admin/components/expenses/ExpensesStats.vue';
import PurchaseCreate from 'admin/components/purchase/PurchaseCreate.vue';

export default {
    name: 'Expenses',

    components: {
        ExpensesStats,
        ComboBox,
    },

    data() {
        return {
            pages: [
                { namedRoute: 'PurchaseCreate', name: 'Purchases' },
                { namedRoute: 'DummyComponent', name: 'Pay Purchases' },
                { namedRoute: 'BillCreate', name: 'Bills' },
                { namedRoute: 'PayBillCreate', name: 'Pay Bills' },
            ],
        };
    },

    created() {
        // this.$root.$on('comboSelected', (selected) => {
        //     console.log(selected);
        // });
    }
};
</script>

<style scoped>
</style>
