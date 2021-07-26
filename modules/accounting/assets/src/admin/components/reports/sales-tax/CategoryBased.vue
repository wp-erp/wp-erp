<template>
    <div class="sales-tax-report">
        <h2 class="title-container">
            <span>{{ __( 'Sales Tax Report (Category Based)', 'erp' ) }}</span>
            
            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'SalesTaxReportOverview' }">
                {{ __( 'Back', 'erp' ) }}
            </router-link>
        </h2>

        <form @submit.prevent="getReport" class="query-options no-print">
            <div class="wperp-date-group">
                <div class="with-multiselect">
                    <multi-select v-model="taxCategory" :options="taxCategories"/>
                </div>

                <datepicker v-model="startDate" />
                
                <datepicker v-model="endDate" />
                
                <button class="wperp-btn btn--primary add-line-trigger" type="submit">
                    {{ __( 'Filter', 'erp' ) }}
                </button>
            </div>

            <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                <i class="flaticon-printer-1"></i>
                &nbsp; {{ __( 'Print', 'erp' ) }}
            </a>
        </form>

        <ul class="report-header" v-if="null !== taxCategory">
            <li>
                <strong>{{ __( 'Category Name', 'erp' ) }}:</strong>
                <em> {{ taxCategory.name }}</em>
            </li>
            
            <li>
                <strong>{{ __( 'Currency', 'erp' ) }}:</strong>
                <em> {{ symbol }}</em>
            </li>
            
            <li v-if="startDate && endDate">
                <strong>{{ __('For the period of (Transaction date)', 'erp') }}:</strong>
                <em> {{ formatDate( startDate ) }}</em> to <em>{{ formatDate( endDate ) }}</em>
            </li>
        </ul>

        <list-table
            tableClass="wperp-table table-striped table-dark widefat sales-tax-table"
            :columns="columns"
            :rows="taxes"
            :showCb="false">
            
            <template slot="voucher_no" slot-scope="data">
                <strong>
                    <router-link :to="{ name: 'SalesSingle', params: { id: data.row.voucher_no, type: 'invoice' } }">
                        <span v-if="data.row.voucher_no">#{{ data.row.voucher_no }}</span>
                    </router-link>
                </strong>
            </template>
            
            <template slot="tax_amount" slot-scope="data">
                {{ moneyFormat( parseFloat( data.row.tax_amount ) ) }}
            </template>
            
            <template slot="tfoot">
                <tr class="tfoot">
                    <td></td>
                    <td>{{ __( 'Total', 'erp' ) }} =</td>
                    <td>{{ moneyFormat( totalTax ) }}</td>
                </tr>
            </template>
        </list-table>
    </div>
</template>

